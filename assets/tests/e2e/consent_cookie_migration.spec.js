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

  // 2b) also set a JSON cookie using an older major version (<4)
  await page.evaluate(() => {
    document.cookie = 'consent_manager=' + encodeURIComponent(JSON.stringify({ consents: [], version: 3, cachelogid: Date.now(), consentid: 'legacy-v3' })) + '; path=/;';
  });
  await page.click('#show');
  const output_v3 = await page.locator('#output').textContent();
  expect(output_v3).toContain('consent_manager=');
  expect(output_v3).toContain('legacy-v3');

  // 3) simulate a future/changed add-on major version (5) and call inline setCookieData -> should clear old cookies
  await page.evaluate(() => { window.consent_manager_parameters = { version: '5' }; });

  // 3) call inline setCookieData -> should clear old cookies and write new JSON consent cookie
  await page.click('#inline-set');
  await page.waitForTimeout(250); // small wait for cookie write

  const result1 = await page.locator('#result').textContent();
  // new cookie payload should be present
  expect(result1).toContain('consent_manager={');
  expect(result1).not.toContain('INVALID_PAYLOAD');

  // 4) clear again and then use frontend save (simulate clicking saveConsent fallback)
  await page.click('#clear');
  // simulate frontend expecting version 5
  await page.evaluate(() => { window.consent_manager_parameters = { version: '5' }; });
  await page.click('#frontend-save');
  await page.waitForTimeout(250);
  // read document.cookie directly to avoid page-specific UI timing issues
  const cookieString = await page.evaluate(() => document.cookie || '');
  expect(cookieString).toContain('consent_manager=');
});
