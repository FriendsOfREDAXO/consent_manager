const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './assets/tests/e2e',
  timeout: 30 * 1000,
  retries: 0,
  use: {
    headless: true,
    viewport: { width: 1200, height: 800 },
    actionTimeout: 10_000,
  },
  projects: [
    { name: 'chromium', use: { browserName: 'chromium' } },
  ],
});
