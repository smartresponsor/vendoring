import { mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import path from 'node:path';
import http from 'node:http';
import { spawn, spawnSync } from 'node:child_process';

const root = process.cwd();
const host = '127.0.0.1';
const port = 18091;
const baseUrl = `http://${host}:${port}`;

const playwrightModule = await importPlaywright();
if (playwrightModule === null) {
  console.log('playwright smoke skipped: playwright package is not installed');
  process.exit(0);
}

const tempDir = mkdtempSync(path.join(tmpdir(), 'vendoring-playwright-'));
const sqlitePath = path.join(tempDir, 'playwright.sqlite');
const server = await bootServer(sqlitePath);

try {
  await waitForHttp(`${baseUrl}/ops/vendor-transactions/vendor-playwright`);
  await runBrowserScenario(playwrightModule.chromium, `${baseUrl}/ops/vendor-transactions/vendor-playwright`);
  console.log('playwright chromium operator smoke passed');
} finally {
  server.kill('SIGTERM');
  rmSync(tempDir, { recursive: true, force: true });
}

async function importPlaywright() {
  try {
    return await import('playwright');
  } catch {
    return null;
  }
}

async function bootServer(sqlitePath) {
  const schemaSql = `
CREATE TABLE IF NOT EXISTS vendor_transaction (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  vendor_id VARCHAR(64) NOT NULL,
  order_id VARCHAR(64) NOT NULL,
  project_id VARCHAR(64) NULL,
  amount NUMERIC(12,2) NOT NULL,
  status VARCHAR(64) NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL
);`;

  const init = spawnSync('php', [
    '-r',
    `$pdo=new PDO("sqlite:${sqlitePath}");$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$pdo->exec(${JSON.stringify(schemaSql)});`,
  ], { cwd: root });

  if (init.status !== 0) {
    throw new Error(`Unable to initialize sqlite schema for playwright smoke: ${init.stderr?.toString() ?? ''}`);
  }

  const env = {
    ...process.env,
    APP_ENV: 'test',
    APP_DEBUG: '0',
    VENDOR_DSN: `sqlite:///${sqlitePath}`,
  };

  const server = spawn('php', ['-S', `${host}:${port}`, '-t', 'public'], {
    cwd: root,
    env,
    stdio: 'ignore',
  });

  return server;
}

async function waitForHttp(url) {
  for (let attempt = 0; attempt < 30; attempt += 1) {
    const ok = await new Promise((resolve) => {
      const req = http.get(url, (res) => {
        res.resume();
        resolve((res.statusCode ?? 500) < 500);
      });
      req.on('error', () => resolve(false));
    });
    if (ok) {
      return;
    }
    await new Promise((resolve) => setTimeout(resolve, 250));
  }

  throw new Error(`HTTP server did not become ready at ${url}`);
}

async function runBrowserScenario(chromium, url) {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage();

  try {
    await page.goto(url, { waitUntil: 'domcontentloaded' });
    await page.getByRole('heading', { name: 'Vendor transaction operator surface' }).waitFor();

    await page.locator('input[name$="[vendorId]"]').fill('vendor-playwright');
    await page.locator('input[name$="[orderId]"]').fill('order-playwright-1');
    await page.locator('input[name$="[amount]"]').fill('10.50');
    await page.getByRole('button', { name: 'Create' }).click();

    await page.waitForURL((targetUrl) => targetUrl.toString().includes('message=Transaction+created.'));
    await page.getByRole('cell', { name: 'order-playwright-1' }).waitFor();
  } finally {
    await browser.close();
  }
}
