// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

import fs from 'fs';
import path from 'path';

const repoRoot = process.cwd();
const srcRoot = path.join(repoRoot, 'src');
const reportDir = path.join(repoRoot, '.report');

const MAX_ISSUE = 5000;
const issues = [];

function pushIssue(kind, file, message) {
  if (issues.length >= MAX_ISSUE) return;
  issues.push({ kind, file, message });
}

function isDir(p) {
  try { return fs.statSync(p).isDirectory(); } catch { return false; }
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
      if (it.isDirectory()) {
        // skip vendor dirs if present
        if (it.name === 'vendor' || it.name === 'node_modules') continue;
        stack.push(full);
        continue;
      }
      if (!it.isFile()) continue;
      out.push(full);
    }
  }
  return out;
}

function readHead(file, maxBytes = 8192) {
  try {
    const fd = fs.openSync(file, 'r');
    const buf = Buffer.alloc(maxBytes);
    const n = fs.readSync(fd, buf, 0, maxBytes, 0);
    fs.closeSync(fd);
    return buf.slice(0, n).toString('utf8');
  } catch {
    return '';
  }
}

function rel(p) {
  return path.relative(repoRoot, p).split(path.sep).join('/');
}

function detectNamespace(head) {
  const m = head.match(/\bnamespace\s+([^;\s]+)\s*;/m);
  return m ? m[1].trim() : null;
}

function scanPhpFile(file) {
  const r = rel(file);

  // path-based guards
  if (r.includes('src/src/')) pushIssue('path', r, 'Nested src/src detected (PSR-4 breaker).');

  const repeat = r.match(/\/(Controller|Service|Entity|Repository|Domain|Http|Bridge|Interface|Infrastructure)\/(\1)\//);
  if (repeat) pushIssue('path', r, `Repeated segment '${repeat[1]}/${repeat[2]}' detected.`);

  if (r.includes('/vendor-current/')) pushIssue('archive', r, 'archive dir vendor-current is inside src/.');
  if (/\/[0-9]{2,4}[_-]vendor-/.test(r)) pushIssue('archive', r, 'phase/archive directory is inside src/.');
  if (r.includes('/vendor-sketch-')) pushIssue('archive', r, 'vendor-sketch directory is inside src/.');

  // namespace-based guards
  const head = readHead(file);
  const ns = detectNamespace(head);
  if (!ns) {
    pushIssue('namespace', r, 'No namespace declaration found.');
    return;
  }

  if (ns.startsWith('SmartResponsor\\')) {
    pushIssue('namespace', r, `Non-app namespace detected: ${ns}. (Prefer App\\* inside Symfony runtime repo)`);
  }

  if (!ns.startsWith('App\\') && !ns.startsWith('SmartResponsor\\')) {
    pushIssue('namespace', r, `Unexpected namespace root: ${ns}.`);
  }

  // quick heuristic: file path after src/ should match namespace after App\
  if (ns.startsWith('App\\')) {
    const after = r.startsWith('src/') ? r.slice(4) : r;
    const expectedPrefix = after.split('/').slice(0, -1).join('\\');
    const expected = expectedPrefix ? `App\\${expectedPrefix}` : 'App';
    if (!ns.startsWith(expected)) {
      pushIssue('ns-path', r, `Namespace does not follow path. ns='${ns}' expectedPrefix='${expected}'.`);
    }
  }
}

function scan() {
  if (!isDir(srcRoot)) {
    console.error('src/ not found.');
    process.exit(2);
  }

  const all = walk(srcRoot).filter(p => p.endsWith('.php'));

  // global directory guards
  const archiveDirs = [
    path.join(srcRoot, 'src'),
    path.join(srcRoot, 'Controller', 'Vendor', 'vendor-current'),
    path.join(srcRoot, 'Domain', 'Vendor', 'vendor-current'),
  ];
  for (const d of archiveDirs) {
    if (isDir(d)) pushIssue('path', rel(d), 'Forbidden directory present.');
  }

  for (const f of all) scanPhpFile(f);

  fs.mkdirSync(reportDir, { recursive: true });
  const outJson = path.join(reportDir, 'vendor-canon-scan.json');
  const outTxt = path.join(reportDir, 'vendor-canon-scan.txt');

  const byKind = {};
  for (const it of issues) byKind[it.kind] = (byKind[it.kind] || 0) + 1;

  const header = {
    repoRoot,
    scanned_php: all.length,
    issue_total: issues.length,
    issue_by_kind: byKind,
  };

  fs.writeFileSync(outJson, JSON.stringify({ header, issues }, null, 2), 'utf8');

  const lines = [];
  lines.push(`vendor-canon-scan`);
  lines.push(`scanned_php=${all.length}`);
  lines.push(`issue_total=${issues.length}`);
  for (const [k, v] of Object.entries(byKind)) lines.push(`issue_${k}=${v}`);
  lines.push('');
  for (const it of issues.slice(0, 200)) {
    lines.push(`[${it.kind}] ${it.file} :: ${it.message}`);
  }
  if (issues.length > 200) lines.push(`... (${issues.length - 200} more)`);
  fs.writeFileSync(outTxt, lines.join('\n'), 'utf8');

  if (issues.length > 0) {
    console.error(`vendor-canon-scan failed: ${issues.length} issue(s). See .report/vendor-canon-scan.txt`);
    process.exit(1);
  }

  console.log('vendor-canon-scan ok');
}

scan();
