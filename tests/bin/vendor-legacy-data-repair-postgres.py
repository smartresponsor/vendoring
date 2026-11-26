"""Real PostgreSQL regression; only a dedicated owned Pass 9 test cluster."""
import argparse
import json
import pathlib
import subprocess
import uuid

parser = argparse.ArgumentParser()
parser.add_argument("--socket", required=True)
parser.add_argument("--cluster", required=True)
args = parser.parse_args()
expected_socket = "/var/lib/console-mcp/database-sandboxes/cmcp_test_vendoring_pass9/socket"
assert args.socket == expected_socket
assert args.cluster.isdecimal()
base = ["psql", "-X", "-w", "-h", args.socket, "-p", "55432", "-v", "ON_ERROR_STOP=1", "-qAt"]
root = pathlib.Path(__file__).resolve().parents[2]
repair = (root / "migrations/repair/vendor_legacy_data.sql").read_text()

def run(db, sql, expected=True):
    result = subprocess.run(base + ["-d", db], input=sql, text=True, capture_output=True, timeout=30)
    if (result.returncode == 0) != expected:
        raise AssertionError(f"{db}: exit={result.returncode}, stderr={result.stderr}")
    return result.stdout.strip(), result.stderr

assert run("postgres", "SELECT system_identifier FROM pg_control_system();")[0] == args.cluster

tables = ["vendor_catalog_category_banner", "vendor_catalog_category_html_block", "vendor_catalog_category_pin", "vendor_service"]
fixture = "CREATE TABLE public.vendor(id integer PRIMARY KEY); INSERT INTO public.vendor VALUES (42),(77);"
for t in tables:
    fixture += f"CREATE TABLE public.{t}(id integer PRIMARY KEY, code varchar(255), payload json NOT NULL, object_slug text NOT NULL);"
    fixture += f"""INSERT INTO public.{t} VALUES (1,'old-code','{{"unmapped":1,"unmapped":2,"extra":[1,2]}}','keep-slug');"""
mapping = """CREATE TEMP TABLE vendoring_legacy_mapping(
source_table text, source_id integer, source_sha256 text, evidence text, target jsonb
) ON COMMIT PRESERVE ROWS;"""
targets = [
    {"category_id":"cat-1","title":"Title","content":"Body","published":True,"published_at":"2026-01-02T03:04:05"},
    {"category_id":"cat-2","html":"<p>content</p>","published":False,"published_at":None},
    {"category_id":"cat-3","record_id":"record-7","position":2},
    {"category_id":"service-category","vendor_id":42},
]
def maprow(t, target, where=""):
    return f"""INSERT INTO pg_temp.vendoring_legacy_mapping
SELECT '{t}',id,encode(sha256(convert_to(jsonb_build_array(to_jsonb(s),s.payload::text)::text,'UTF8')),'hex'),
'test-source-reference','{json.dumps(target)}'::jsonb FROM public.{t} s {where};"""
mapping += "".join(maprow(t,v) for t,v in zip(tables,targets))
snapshot = "SELECT jsonb_build_array(" + ",".join(f"(SELECT jsonb_agg(to_jsonb(s) ORDER BY id) FROM public.{t} s)" for t in tables) + ")::text;"
def newdb(suffix):
    db = "cmcp_test_pass9_" + suffix + "_" + uuid.uuid4().hex[:8]
    run("postgres", "CREATE DATABASE " + db + ";")
    run(db, fixture)
    return db

db = newdb("happy")
run(db, mapping + repair)
assert run(db, "SELECT code,first_title,middle_title,published,published_at,object_slug FROM public.vendor_catalog_category_banner;")[0] == "cat-1|Title|Body|t|2026-01-02 03:04:05|keep-slug"
assert run(db, "SELECT code,middle_title,published,published_at FROM public.vendor_catalog_category_html_block;")[0] == "cat-2|<p>content</p>|f|"
assert run(db, "SELECT code,record_id,position FROM public.vendor_catalog_category_pin;")[0] == "cat-3|record-7|2"
assert run(db, "SELECT vendor_id,category_id,code,payload::text FROM public.vendor_service;")[0] == '42|service-category|old-code|{"unmapped":1,"unmapped":2,"extra":[1,2]}'
assert run(db, "SELECT count(*),count(*) FILTER (WHERE original_payload = '{\"unmapped\":1,\"unmapped\":2,\"extra\":[1,2]}' AND original_row->>'object_slug'='keep-slug') FROM vendoring_legacy_preservation.row_archive;")[0] == "4|4"
assert run(db, "SELECT count(*) FROM information_schema.columns WHERE table_schema='public' AND table_name LIKE 'vendor_catalog_category_%' AND column_name='payload';")[0] == "0"
assert run(db, "SELECT count(*) FROM pg_constraint WHERE conname='fk_vendor_service_vendor';")[0] == "1"
assert run(db, "SELECT count(*) FROM pg_indexes WHERE schemaname='public' AND indexname='uniq_vendor_service_vendor_category';")[0] == "1"
run(db, "INSERT INTO public.vendor_service VALUES (2,'a','{}','x',42,'service-category');", expected=False)
run(db, "INSERT INTO public.vendor_service VALUES (2,'a','{}','x',99,'another');", expected=False)
empty_mapping = mapping.split("INSERT INTO")[0]
run(db, empty_mapping + repair)
assert run(db, "SELECT count(*) FROM vendoring_legacy_preservation.row_archive;")[0] == "4"
print("PASS nonempty mapping all four tables, raw duplicate-key JSON, typed values, ownership constraints, rerun")

cases = [
    ("missing", "", "DELETE FROM pg_temp.vendoring_legacy_mapping WHERE source_table='vendor_service';", "Mapping coverage mismatch"),
    ("stale", "", "UPDATE pg_temp.vendoring_legacy_mapping SET source_sha256=repeat('0',64) WHERE source_table='vendor_service';", "Missing or stale mapping"),
    ("duplicate", "", "INSERT INTO pg_temp.vendoring_legacy_mapping SELECT * FROM pg_temp.vendoring_legacy_mapping WHERE source_table='vendor_service';", "Duplicate mapping row"),
    ("owner", "", """UPDATE pg_temp.vendoring_legacy_mapping SET target='{"category_id":"x","vendor_id":99}' WHERE source_table='vendor_service';""", "Unverified service owner"),
    ("fields", "", """UPDATE pg_temp.vendoring_legacy_mapping SET target='{"category_id":"x","record_id":"a","position":-1}' WHERE source_table='vendor_catalog_category_pin';""", "Invalid pin mapping"),
    ("publication", "", """UPDATE pg_temp.vendoring_legacy_mapping SET target=jsonb_set(target,'{published_at}','null') WHERE source_table='vendor_catalog_category_banner';""", "Invalid publication decision"),
    ("mixed", "ALTER TABLE public.vendor_catalog_category_pin ADD record_id varchar(255);", "", "Mixed legacy/canonical"),
    ("collision", "INSERT INTO public.vendor_service VALUES (2,'old-code','{}','other');", "", "is duplicated."),
]
for suffix, setup, change, message in cases:
    db = newdb(suffix)
    if setup:
        run(db, setup)
    before = run(db, snapshot)[0]
    _, error = run(db, mapping + change + repair, expected=False)
    assert message in error, error
    assert run(db, snapshot)[0] == before, suffix
    assert run(db, "SELECT to_regnamespace('vendoring_legacy_preservation') IS NULL;")[0] == "t"
    assert run(db, "SELECT count(*) FROM information_schema.columns WHERE table_schema='public' AND table_name='vendor_service' AND column_name IN ('vendor_id','category_id');")[0] == "0"
    print("PASS atomic rollback " + suffix)
print("PASS9_POSTGRES_REGRESSION_PASS 9 scenarios")
