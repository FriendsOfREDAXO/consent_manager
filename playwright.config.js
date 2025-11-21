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
  // reporters: include junit output to test-results for workflow upload, and github reporter for GitHub checks
  reporter: [
    ['list'],
    ['github'],
    ['junit', { outputFile: 'test-results/junit-results.xml' }],
    ['html', { open: 'never' }]
  ]
});
