import { test, expect, request } from '@playwright/test';

function suffix() {
  return Math.random().toString(16).slice(2);
}

test.describe('Vendoring RC Playwright surfaces', () => {

  test('API documentation surface', async ({ page }) => {
    await page.goto('/api/doc');
    await expect(page).toHaveTitle(/Vendoring API/);
    await expect(page.locator('body')).toContainText('Vendoring API');
  });

  test('runtime status endpoint', async ({ request }) => {
    const res = await request.get('/api/vendor/runtime/status/42?tenantId=tenant-1&currency=USD');
    expect(res.status()).toBe(200);
    const json = await res.json();
    expect(json.data).toBeDefined();
    expect(json.data.tenantId).toBe('tenant-1');
  });

  test('release baseline endpoint', async ({ request }) => {
    const res = await request.get('/api/vendor/release/baseline/42?tenantId=tenant-1&currency=USD');
    expect(res.status()).toBe(200);
    const json = await res.json();
    expect(json.data).toBeDefined();
    expect(json.data.status).toBeDefined();
  });

  test('transaction full flow', async ({ request }) => {
    const idSuffix = suffix();
    const vendorId = `pw-vendor-${idSuffix}`;
    const orderId = `pw-order-${idSuffix}`;

    const create = await request.post('/api/vendor/transaction', {
      data: {
        vendorId,
        orderId,
        projectId: 'pw-project',
        amount: '12.00',
      },
    });

    expect(create.status()).toBe(201);
    const created = await create.json();
    expect(created.status).toBe('pending');

    const list = await request.get(`/api/vendor/transaction/${vendorId}`);
    expect(list.status()).toBe(200);
    const listJson = await list.json();

    const found = listJson.data.find((r: any) => r.orderId === orderId);
    expect(found).toBeTruthy();

    const update = await request.post(`/api/vendor/transaction/status/${created.id}`, {
      data: { status: 'authorized' },
    });

    expect(update.status()).toBe(200);
    const updated = await update.json();
    expect(updated.status).toBe('authorized');
  });

  test('duplicate transaction contract', async ({ request }) => {
    const idSuffix = suffix();
    const payload = {
      vendorId: `dup-${idSuffix}`,
      orderId: `dup-order-${idSuffix}`,
      projectId: 'pw-project',
      amount: '33.00',
    };

    const first = await request.post('/api/vendor/transaction', { data: payload });
    expect(first.status()).toBe(201);

    const second = await request.post('/api/vendor/transaction', { data: payload });
    expect(second.status()).toBe(409);
    const json = await second.json();
    expect(json.error).toBe('duplicate_transaction');
  });

  test('error surfaces', async ({ request }) => {
    const malformed = await request.post('/api/vendor/transaction', { data: undefined });
    expect(malformed.status()).toBe(400);

    const missing = await request.post('/api/vendor/transaction/status/999', {
      data: {},
    });
    expect(missing.status()).toBe(404);
  });

});
