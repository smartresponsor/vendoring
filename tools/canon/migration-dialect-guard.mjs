// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

import fs from 'fs';
import path from 'path';

const repoRoot = process.cwd();
const migRoot = path.join(repoRoot, 'migrations');
const reportDir = path.join(repoRoot, 'build', 'reports', 'canon');

const allowedTop = new Set([
  'MigrationPg',
  'MigrationMy',
  'MigrationGeneric',
  'MigrationSqlite',
  'MigrationMs',
]);

const issues = [];

function rel(p) {
  return path.relative(repoRoot, p).split(path.sep).join('/');
}

function walk(dir) {
  const out = [];
  const stack = [dir];
  while (stack.length) {
    const cur = stack.pop();
    let items;
    try { items = fs.readdirSync(cur, { withFileTypes: true }); } catch { continue; }
    for (const it of items) {
      const full = path.join(cur, it.name);
      if (it.isDirectory()) { stack.push(full); continue; }
      if (it.isFile()) out.push(full);
    }
  }
  return out;
}

function push(file, msg) {
  issues.push({ file, msg });
}

function main() {
  if (!fs.existsSync(migRoot)) {
    // migrations folder is optional in skeleton packs
    return;
  }

  const files = walk(migRoot);
  for (const f of files) {
    if (!f.endsWith('.sql')) continue;

    const rp = rel(f);
    const parts = rp.split('/');
    // migrations/<Top>/<rest...>
    const top = parts.length >= 2 ? parts[1] : null;
    if (!top || !allowedTop.has(top)) {
      push(rp, `SQL file must be placed under migrations/<${Array.from(allowedTop).join('|')}>/.`);
      continue;
    }

    // optional: basic dialect hints
    const sql = fs.readFileSync(f, 'utf8');
    if (top === 'MigrationPg' && /\bAUTO_INCREMENT\b/i.test(sql)) push(rp, 'AUTO_INCREMENT found in MigrationPg (likely MySQL syntax).');
    if (top === 'MigrationMy' && /\bSERIAL\b/i.test(sql)) push(rp, 'SERIAL found in MigrationMy (likely PostgreSQL syntax).');
  }

  fs.mkdirSync(reportDir, { recursive: true });
  const outJson = path.join(reportDir, 'migration-dialect-guard.json');
  const outTxt = path.join(reportDir, 'migration-dialect-guard.txt');

  fs.writeFileSync(outJson, JSON.stringify({ scanned_sql: files.filter(f => f.endsWith('.sql')).length, issues }, null, 2), 'utf8');

  const lines = [];
  lines.push(`migration-dialect-guard`);
  lines.push(`issue_total=${issues.length}`);
  lines.push('');
  for (const it of issues.slice(0, 200)) lines.push(`${it.file} :: ${it.msg}`);
  if (issues.length > 200) lines.push(`... (${issues.length - 200} more)`);
  fs.writeFileSync(outTxt, lines.join('\n'), 'utf8');

  if (issues.length > 0) {
    console.error(`migration-dialect-guard failed: ${issues.length} issue(s). See build/reports/canon/migration-dialect-guard.txt`);
    process.exit(1);
  }

  console.log('migration-dialect-guard ok');
}

main();
