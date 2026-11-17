const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests/browser',
  fullyParallel: false,
  workers: 1,
  retries: 0,
  timeout: 30000,
  reporter: [['list'], ['html', { outputFolder: 'build/playwright-report', open: 'never' }]],
  use: {
    baseURL: process.env.TEST_BASE_URL || 'http://127.0.0.1:8000',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [
    { name: 'chromium-mobile', use: { ...devices['iPhone 13'], browserName: 'chromium' } },
    { name: 'chromium-tablet', use: { ...devices['iPad (gen 7)'], browserName: 'chromium' } },
    { name: 'chromium-desktop', use: { browserName: 'chromium', viewport: {width:1366,height:768} } },
  ],
});
