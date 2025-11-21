const { test, expect } = require('@playwright/test');

test('malformed cookie or malformed event payload must not crash the page', async ({ page }) => {
  // keep track of page errors
  const errors = [];
  page.on('pageerror', (err) => errors.push(err.message));

  // Inject minimal global params before any page scripts run so the page
  // can't throw a "consent_manager_parameters is not defined" error.
  await page.addInitScript(() => {
    window.consent_manager_parameters = window.consent_manager_parameters || {
      version: '5',
      domain: window.location.hostname,
      consentid: 'test-consent',
      cachelogid: Date.now(),
    };
  });

  await page.goto('http://localhost:8000/assets/tests/consent_cookie_migration_test.html');

  // set a broken cookie
  await page.evaluate(() => {
    document.cookie = 'consent_manager=INVALID_PAYLOAD; path=/;';
  });

  // dispatch consent_manager-saved with a malformed detail string
  await page.evaluate(() => {
    document.dispatchEvent(new CustomEvent('consent_manager-saved', { detail: '}{ not json' }));
  });

  // give scripts a moment to execute
  await page.waitForTimeout(200);

  // dispatch consent_manager-saved with a non-string payload (Edge cases)
  await page.evaluate(() => {
    document.dispatchEvent(new CustomEvent('consent_manager-saved', { detail: null }));
  });

  await page.waitForTimeout(200);

  // Fail on any uncaught page errors
  expect(errors).toEqual([]);
});
