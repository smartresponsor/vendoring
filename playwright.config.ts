import {defineConfig, devices} from '@playwright/test';

const host = process.env.HOST ?? '127.0.0.1';
const port = process.env.PORT ?? '18000';
const baseURL = `http://${host}:${port}`;

export default defineConfig({
    testDir: './tests/e2e',
    timeout: 30_000,
    expect: {
        timeout: 10_000,
    },
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    reporter: [
        ['list'],
        ['html', {outputFolder: 'playwright-report', open: 'never'}],
    ],
    use: {
        baseURL,
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },
    webServer: {
        command: 'composer server:run',
        url: `${baseURL}/api/doc`,
        reuseExistingServer: true,
        timeout: 60_000,
    },
    projects: [
        {
            name: 'chromium',
            use: {...devices['Desktop Chrome']},
        },
    ],
});
