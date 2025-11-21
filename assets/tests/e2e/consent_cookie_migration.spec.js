const { test, expect } = require('@playwright/test');

test('consent cookie migration: clears legacy cookies before writing new cookie (inline & frontend)', async ({ page }) => {
  // Open the manual test harness
  await page.goto('http://localhost:8000/assets/tests/consent_cookie_migration_test.html');

  // Ensure page loaded
  await expect(page).toHaveTitle(/Consent Manager/);

  // 1) set legacy cookies
  await page.click('#set-old');

  // 2) show cookies and assert legacy cookies exist
  await page.click('#show');
  const output = await page.locator('#output').textContent();
  expect(output).toContain('consent_manager=INVALID_PAYLOAD');
  expect(output).toContain('consent_manager_old=1');
  expect(output).toContain('consent_manager_v2=some-old-format');

  // 3) call inline setCookieData -> should clear old cookies and write new JSON consent cookie
  await page.click('#inline-set');
  await page.waitForTimeout(250); // small wait for cookie write

  const result1 = await page.locator('#result').textContent();
  // new cookie payload should be present
  expect(result1).toContain('consent_manager={');
  expect(result1).not.toContain('INVALID_PAYLOAD');

  // 4) clear again and then use frontend save (simulate clicking saveConsent fallback)
  await page.click('#clear');
  await page.click('#frontend-save');
  await page.waitForTimeout(250);
  const result2 = await page.locator('#result').textContent();
  expect(result2).toContain('consent_manager=');
});
