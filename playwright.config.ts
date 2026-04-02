import { defineConfig, devices } from '@playwright/test';

const host = process.env.HOST ?? '127.0.0.1';
const port = process.env.PORT ?? '18000';
const baseURL = `http://${host}:${port}`;

export default defineConfig({
  testDir: './tests/e2e',
  timeout: 30_000,
  use: {
    baseURL,
    trace: 'retain-on-failure',
  },
  webServer: {
    command: 'composer server:run',
    url: `${baseURL}/healthz`,
    reuseExistingServer: true,
    timeout: 60_000,
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
