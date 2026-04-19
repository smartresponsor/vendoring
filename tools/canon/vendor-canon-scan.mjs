// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

import fs from 'fs';
import path from 'path';

const repoRoot = process.cwd();
const srcRoot = path.join(repoRoot, 'src');
const reportDir = path.join(repoRoot, '.report');

const MAX_ISSUE = 5000;
const MAX_SCANNED_FILES = 9000;
const issues = [];
const repoIgnores = new Set(['.git', 'vendor', 'node_modules', '.idea']);

function pushIssue(kind, file, message) {
  if (issues.length >= MAX_ISSUE) return;
  issues.push({ kind, file, message });
}

function isDir(p) {
  try { return fs.statSync(p).isDirectory(); } catch { return false; }
}

function walk(dir, ignores = new Set()) {
  const out = [];
  const stack = [dir];
  while (stack.length) {
    const cur = stack.pop();
    let items;
    try { items = fs.readdirSync(cur, { withFileTypes: true }); } catch { continue; }
    for (const it of items) {
      const full = path.join(cur, it.name);
      if (it.isDirectory()) {
        if (ignores.has(it.name)) continue;
        stack.push(full);
        continue;
      }
      if (!it.isFile()) continue;
      out.push(full);
    }
  }
  return out;
}

function scanRepoPath(file) {
  const r = rel(file);
  const seg = r.split('/');

  if (seg[0].startsWith('.')) return;

  for (let i = 1; i < seg.length - 1; i += 1) {
    if (seg[i].startsWith('.')) {
      pushIssue('dot-folder', r, `Dot-folder '${seg[i]}' is only allowed at repository root.`);
      break;
    }
  }

  if (r.includes('/Legacy/') || r.includes('/legacy/')) {
    pushIssue('legacy', r, 'Legacy-tail path is forbidden.');
  }
}


function shouldScanForForbiddenNamespaceChain(file) {
  const r = rel(file);
  if (r.startsWith('tools/canon/')) return false;

  const roots = ['src/', 'config/', 'tests/', 'public/', 'bin/', 'composer.json'];
  return roots.some((root) => r === root || r.startsWith(root));
}

function scanForbiddenNamespaceChain(file) {
  if (!shouldScanForForbiddenNamespaceChain(file)) return;

  const r = rel(file);
  const head = readHead(file, 65536);
  if (/Smartresponsor\\/i.test(head) || /Smartresponsor\//i.test(head)) {
    pushIssue('namespace-chain', r, 'Forbidden namespace chain found. Use App\\Vendoring\\* only.');
  }
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
  if (r.startsWith('src/Port/')) pushIssue('architecture', r, 'src/Port is forbidden.');
  if (r.startsWith('src/Adaptor/')) pushIssue('architecture', r, 'src/Adaptor is forbidden.');

  // namespace-based guards
  const head = readHead(file);
  if (head.includes('TODO')) {
    pushIssue('todo', r, 'TODO marker is forbidden.');
  }
  const ns = detectNamespace(head);
  if (!ns) {
    pushIssue('namespace', r, 'No namespace declaration found.');
    return;
  }

  if (/^Smartresponsor(?:\\|$)/i.test(ns)) {
    pushIssue('namespace', r, `Forbidden namespace root: ${ns}. Use only App namespace rooted at src/.`);
  }

  const isAppNamespace = ns === 'App\\Vendoring' || ns.startsWith('App\\Vendoring\\');
  if (!isAppNamespace) {
    pushIssue('namespace', r, `Unexpected namespace root: ${ns}.`);
  }

  // quick heuristic: file path after src/ should match namespace after App\Vendoring\
  if (ns === 'App\\Vendoring' || ns.startsWith('App\\Vendoring\\')) {
    const after = r.startsWith('src/') ? r.slice(4) : r;
    const expectedPrefix = after.split('/').slice(0, -1).join('\\');
    const expected = expectedPrefix ? `App\\Vendoring\\${expectedPrefix}` : 'App\\Vendoring';
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

  const allRepoFiles = walk(repoRoot, repoIgnores)
    .map(rel)
    .sort()
    .slice(0, MAX_SCANNED_FILES)
    .map((r) => path.join(repoRoot, r));
  const all = allRepoFiles.filter((p) => rel(p).startsWith('src/') && p.endsWith('.php'));

  // global directory guards
  const archiveDirs = [
    path.join(srcRoot, 'src'),
    path.join(srcRoot, 'Controller', 'Vendor', 'vendor-current'),
    path.join(srcRoot, 'Domain', 'Vendor', 'vendor-current'),
  ];
  for (const d of archiveDirs) {
    if (isDir(d)) pushIssue('path', rel(d), 'Forbidden directory present.');
  }

  for (const f of allRepoFiles) {
    scanRepoPath(f);
    scanForbiddenNamespaceChain(f);
  }
  for (const f of all) scanPhpFile(f);

  fs.mkdirSync(reportDir, { recursive: true });
  const outJson = path.join(reportDir, 'vendor-canon-scan.json');
  const outTxt = path.join(reportDir, 'vendor-canon-scan.txt');

  const byKind = {};
  for (const it of issues) byKind[it.kind] = (byKind[it.kind] || 0) + 1;

  const header = {
    repoRoot,
    scanned_files: allRepoFiles.length,
    scanned_php: all.length,
    issue_total: issues.length,
    issue_by_kind: byKind,
  };

  fs.writeFileSync(outJson, JSON.stringify({ header, issues }, null, 2), 'utf8');

  const lines = [];
  lines.push(`vendor-canon-scan`);
  lines.push(`scanned_files=${allRepoFiles.length}`);
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
