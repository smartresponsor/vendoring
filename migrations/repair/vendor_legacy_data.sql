-- Explicit, offline legacy data repair. Run in one psql session with ON_ERROR_STOP.
-- Operator supplies pg_temp.vendoring_legacy_mapping; see the integration contract.
BEGIN;
SET LOCAL lock_timeout = '5s';
SET LOCAL search_path = pg_catalog, public;
DO $repair$
DECLARE
    t text;
    r record;
    m record;
    legacy text[] := ARRAY[]::text[];
    cols text[];
    expected_keys text[];
    fingerprint text;
    decision jsonb;
    source_count bigint;
    mapping_count bigint;
BEGIN
    IF to_regclass('pg_temp.vendoring_legacy_mapping') IS NULL THEN
        RAISE EXCEPTION 'Explicit legacy mapping table is required';
    END IF;
    -- Block concurrent writes and ownership changes for the complete transaction.
    LOCK TABLE public.vendor, public.vendor_catalog_category_banner,
        public.vendor_catalog_category_html_block, public.vendor_catalog_category_pin,
        public.vendor_service IN ACCESS EXCLUSIVE MODE;
    FOREACH t IN ARRAY ARRAY['vendor_catalog_category_banner','vendor_catalog_category_html_block','vendor_catalog_category_pin','vendor_service']
    LOOP
        SELECT array_agg(column_name::text) INTO cols FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = t;
        IF t = 'vendor_service' THEN
            IF NOT ('payload' = ANY(cols) AND 'code' = ANY(cols)) THEN
                RAISE EXCEPTION 'Unexpected vendor_service schema';
            END IF;
            IF 'vendor_id' = ANY(cols) AND 'category_id' = ANY(cols) THEN
                -- Already-adopted data is not rewritten by this tool.
                IF EXISTS (SELECT 1 FROM public.vendor_service s LEFT JOIN public.vendor v ON v.id = s.vendor_id
                    WHERE v.id IS NULL OR s.category_id IS NULL OR btrim(s.category_id) = '') THEN
                    RAISE EXCEPTION 'Incomplete existing vendor_service ownership';
                END IF;
                CONTINUE;
            END IF;
            IF 'vendor_id' = ANY(cols) OR 'category_id' = ANY(cols) THEN
                RAISE EXCEPTION 'Partial vendor_service adoption; explicit reconciliation required';
            END IF;
        ELSIF NOT ('payload' = ANY(cols)) THEN
            IF t = 'vendor_catalog_category_pin' AND NOT (ARRAY['code','record_id','position'] <@ cols OR ARRAY['object_code','record_id','position'] <@ cols) THEN
                RAISE EXCEPTION 'Unexpected canonical pin schema';
            ELSIF t <> 'vendor_catalog_category_pin' AND NOT (
                ARRAY['code','first_title','middle_title','last_title','published','published_at'] <@ cols OR
                ARRAY['object_code','object_first_title','object_middle_title','object_last_title','object_published','object_published_at'] <@ cols) THEN
                RAISE EXCEPTION 'Unexpected canonical publication schema: %', t;
            END IF;
            CONTINUE;
        ELSIF NOT ('code' = ANY(cols)) OR cols && ARRAY['object_code','first_title','middle_title','last_title','published','published_at',
            'object_first_title','object_middle_title','object_last_title','object_published','object_published_at','record_id','position'] THEN
            RAISE EXCEPTION 'Mixed legacy/canonical catalog schema: %', t;
        END IF;
        legacy := array_append(legacy,t);
    END LOOP;
    IF EXISTS (SELECT 1 FROM pg_temp.vendoring_legacy_mapping x
        WHERE x.source_table IS NULL OR x.source_id IS NULL OR x.source_id < 1 OR x.source_sha256 IS NULL
        OR x.source_sha256 !~ '^[0-9a-f]{64}$' OR x.evidence IS NULL OR btrim(x.evidence) = ''
        OR x.target IS NULL OR jsonb_typeof(x.target) <> 'object' OR NOT (x.source_table = ANY(legacy))) THEN
        RAISE EXCEPTION 'Invalid or unexpected mapping row';
    END IF;
    IF EXISTS (SELECT 1 FROM pg_temp.vendoring_legacy_mapping GROUP BY source_table, source_id HAVING count(*) <> 1) THEN
        RAISE EXCEPTION 'Duplicate mapping row';
    END IF;
    IF cardinality(legacy) = 0 THEN
        RETURN;
    END IF;
    -- Deliberately fail if a preservation schema already exists; no overwrite or reuse.
    CREATE SCHEMA vendoring_legacy_preservation;
    REVOKE ALL ON SCHEMA vendoring_legacy_preservation FROM PUBLIC;
    CREATE TABLE vendoring_legacy_preservation.row_archive (
        source_table text NOT NULL, source_id integer NOT NULL, source_sha256 text NOT NULL,
        original_row jsonb NOT NULL, original_payload text NOT NULL, evidence text NOT NULL,
        decision jsonb NOT NULL, PRIMARY KEY (source_table, source_id)
    );
    REVOKE ALL ON TABLE vendoring_legacy_preservation.row_archive FROM PUBLIC;

    FOREACH t IN ARRAY legacy LOOP
        EXECUTE format('SELECT count(*) FROM public.%I', t) INTO source_count;
        SELECT count(*) INTO mapping_count FROM pg_temp.vendoring_legacy_mapping x WHERE x.source_table = t;
        IF source_count <> mapping_count THEN
            RAISE EXCEPTION 'Mapping coverage mismatch: %', t;
        END IF;
        IF t = 'vendor_service' THEN
            expected_keys := ARRAY['vendor_id','category_id'];
            EXECUTE 'ALTER TABLE public.vendor_service ADD vendor_id integer, ADD category_id varchar(255)';
        ELSIF t = 'vendor_catalog_category_pin' THEN
            expected_keys := ARRAY['category_id','record_id','position'];
            EXECUTE format('ALTER TABLE public.%I ADD record_id varchar(255), ADD position integer',t);
        ELSE
            expected_keys := CASE WHEN t = 'vendor_catalog_category_banner' THEN
                ARRAY['category_id','title','content','published','published_at'] ELSE
                ARRAY['category_id','html','published','published_at'] END;
            EXECUTE format('ALTER TABLE public.%I ADD first_title varchar(255), ADD middle_title text, ADD last_title text, ADD published boolean, ADD published_at timestamp(0) without time zone',t);
        END IF;
        FOR r IN EXECUTE format('SELECT id, payload::text AS raw_payload, to_jsonb(s) AS row_data FROM public.%I s ORDER BY id',t)
        LOOP
            -- New NULL columns were added above; exclude them from the original fingerprint.
            decision := r.row_data - CASE WHEN t = 'vendor_service' THEN ARRAY['vendor_id','category_id']
                WHEN t = 'vendor_catalog_category_pin' THEN ARRAY['record_id','position']
                ELSE ARRAY['first_title','middle_title','last_title','published','published_at'] END;
            fingerprint := encode(sha256(convert_to(jsonb_build_array(decision,r.raw_payload)::text,'UTF8')),'hex');
            SELECT * INTO m FROM pg_temp.vendoring_legacy_mapping x WHERE x.source_table=t AND x.source_id=r.id;
            IF NOT FOUND OR m.source_sha256 <> fingerprint OR r.raw_payload IS NULL THEN
                RAISE EXCEPTION 'Missing or stale mapping: % id %',t,r.id;
            END IF;
            IF NOT (m.target ?& expected_keys) OR (SELECT count(*) FROM jsonb_object_keys(m.target)) <> cardinality(expected_keys)
                OR jsonb_typeof(m.target->'category_id') <> 'string' OR btrim(m.target->>'category_id') = ''
                OR length(m.target->>'category_id') > (CASE WHEN t='vendor_service' THEN 255 ELSE 190 END) THEN
                RAISE EXCEPTION 'Invalid target category or fields: % id %',t,r.id;
            END IF;
            INSERT INTO vendoring_legacy_preservation.row_archive VALUES
                (t,r.id,fingerprint,decision,r.raw_payload,m.evidence,m.target);
            IF t = 'vendor_service' THEN
                IF jsonb_typeof(m.target->'vendor_id') <> 'number' OR (m.target->>'vendor_id') !~ '^[1-9][0-9]{0,9}$'
                    OR NOT EXISTS (SELECT 1 FROM public.vendor WHERE id=(m.target->>'vendor_id')::bigint) THEN
                    RAISE EXCEPTION 'Unverified service owner: id %',r.id;
                END IF;
                UPDATE public.vendor_service SET vendor_id=(m.target->>'vendor_id')::integer,
                    category_id=m.target->>'category_id' WHERE id=r.id;
            ELSIF t = 'vendor_catalog_category_pin' THEN
                IF jsonb_typeof(m.target->'record_id') <> 'string' OR btrim(m.target->>'record_id') = ''
                    OR length(m.target->>'record_id') > 255 OR jsonb_typeof(m.target->'position') <> 'number'
                    OR (m.target->>'position') !~ '^[0-9]{1,10}$' THEN
                    RAISE EXCEPTION 'Invalid pin mapping: id %',r.id;
                END IF;
                UPDATE public.vendor_catalog_category_pin SET code=m.target->>'category_id',
                    record_id=m.target->>'record_id',position=(m.target->>'position')::integer WHERE id=r.id;
            ELSE
                IF jsonb_typeof(m.target->'published') <> 'boolean'
                    OR ((m.target->>'published')::boolean AND (jsonb_typeof(m.target->'published_at') <> 'string'
                        OR (m.target->>'published_at') !~ '^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}$'))
                    OR (NOT (m.target->>'published')::boolean AND jsonb_typeof(m.target->'published_at') <> 'null') THEN
                    RAISE EXCEPTION 'Invalid publication decision: % id %',t,r.id;
                END IF;
                IF t = 'vendor_catalog_category_banner' THEN
                    IF jsonb_typeof(m.target->'title') <> 'string' OR length(m.target->>'title') > 255
                        OR jsonb_typeof(m.target->'content') <> 'string' THEN
                        RAISE EXCEPTION 'Invalid banner mapping: id %',r.id;
                    END IF;
                    UPDATE public.vendor_catalog_category_banner SET code=m.target->>'category_id',
                        first_title=m.target->>'title',middle_title=m.target->>'content',
                        published=(m.target->>'published')::boolean,published_at=(m.target->>'published_at')::timestamp WHERE id=r.id;
                ELSE
                    IF jsonb_typeof(m.target->'html') <> 'string' THEN
                        RAISE EXCEPTION 'Invalid HTML block mapping: id %',r.id;
                    END IF;
                    UPDATE public.vendor_catalog_category_html_block SET code=m.target->>'category_id',
                        middle_title=m.target->>'html',published=(m.target->>'published')::boolean,
                        published_at=(m.target->>'published_at')::timestamp WHERE id=r.id;
                END IF;
            END IF;
        END LOOP;
        IF t = 'vendor_service' THEN
            ALTER TABLE public.vendor_service ALTER vendor_id SET NOT NULL, ALTER category_id SET NOT NULL;
            -- Install the same ownership constraints expected by the existing adoption migration.
            ALTER TABLE public.vendor_service ADD CONSTRAINT fk_vendor_service_vendor FOREIGN KEY (vendor_id) REFERENCES public.vendor(id) ON DELETE CASCADE;
            CREATE UNIQUE INDEX uniq_vendor_service_vendor_category ON public.vendor_service(vendor_id,category_id);
            CREATE INDEX idx_vendor_service_category ON public.vendor_service(category_id);
        ELSE
            EXECUTE format('ALTER TABLE public.%I ALTER code TYPE varchar(190), DROP COLUMN payload',t);
            IF t = 'vendor_catalog_category_pin' THEN
                ALTER TABLE public.vendor_catalog_category_pin ALTER record_id SET NOT NULL, ALTER position SET NOT NULL;
            ELSE
                EXECUTE format('ALTER TABLE public.%I ALTER published SET NOT NULL',t);
            END IF;
        END IF;
    END LOOP;
END
$repair$;
COMMIT;
