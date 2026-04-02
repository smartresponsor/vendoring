import { expect, test } from '@playwright/test';

test('local dev home page renders', async ({ page }) => {
  await page.goto('/');

  await expect(page).toHaveTitle(/Vendoring Local Dev/);
  await expect(page.getByRole('heading', { name: 'Vendoring Local Dev' })).toBeVisible();
  await expect(page.getByText('Local runtime is up.')).toBeVisible();
});

test('health endpoint returns ok', async ({ request }) => {
  const response = await request.get('/healthz');

  expect(response.ok()).toBeTruthy();
  const payload = await response.json();

  expect(payload).toMatchObject({
    status: 'ok',
  });
  expect(typeof payload.appEnv).toBe('string');
});
