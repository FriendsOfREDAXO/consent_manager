const { test, expect } = require('@playwright/test');

test('malformed cookie or malformed event payload must not crash the page', async ({ page }) => {
  // keep track of page errors
  const errors = [];
  page.on('pageerror', (err) => errors.push(err.message));

  await page.goto('http://localhost:8000/assets/tests/consent_cookie_migration_test.html');

  // ensure basic global params are present to avoid unrelated page errors
  await page.evaluate(() => {
    window.consent_manager_parameters = window.consent_manager_parameters || {};
    window.consent_manager_parameters.version = window.consent_manager_parameters.version || '5';
    window.consent_manager_parameters.domain = window.consent_manager_parameters.domain || window.location.hostname;
    window.consent_manager_parameters.consentid = window.consent_manager_parameters.consentid || 'test-consent';
    window.consent_manager_parameters.cachelogid = window.consent_manager_parameters.cachelogid || Date.now();
  });

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
