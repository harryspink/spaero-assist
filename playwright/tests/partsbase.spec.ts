import { test, expect } from '@playwright/test';

// Add type declaration for process.env
declare const process: {
  env: {
    [key: string]: string | undefined;
  };
};

test.describe("PartsBase", () => {
  test("logs in and searches", async ({ page, browser }) => {
    test.setTimeout(120_000);
    
    // Configure browser for container environment
    browser.contexts()[0].setDefaultNavigationTimeout(60000);
    
    await page.setViewportSize({ width: 1634, height: 897 });
    
    await page.goto('https://www.partsbase.com/landing/login');

    // Fill in login credentials
    await page.getByRole('textbox', { name: 'Username' }).fill('xsaviation');
    await page.getByRole('textbox', { name: 'Password' }).fill('mylesxs');

    // Wait for navigation *during* login click
    await Promise.all([
      page.getByRole('button', { name: 'Login' }).click(),
    ]);

    // Wait for and click the "Search" link with reduced timeout
    await page.waitForTimeout(5000);
    
    page.getByRole('link', { name: 'Search' }).click(),
    
    await page.waitForTimeout(5000);

    // Get the search term from the environment variable
    const searchTerm = process.env.SEARCH_TERM || '201042685';
    console.log(`Using search term: ${searchTerm}`);
    
    // Fill in search query
    page.locator(`[name="crossReference"]`).first().click();
    console.log('Filling search query with term:', searchTerm);
    await page.locator('textarea').fill(searchTerm);
    await page.getByRole('button', { name: 'Search' }).click();
    console.log('Clicked search button, waiting for results...');

    await page.locator('.progress').waitFor({ state: 'visible', timeout: 5000 }).catch(() => {});
    await page.locator('.progress').waitFor({ state: 'hidden', timeout: 30000 });

    const result = await Promise.race([
      page.waitForSelector('.table-result tbody', { timeout: 30000 }),
      page.waitForSelector('text=No PMA data available for', { timeout: 30000 }), 
    ]);
    console.log('Search results received');
    
  const tableVisible = await page.locator('.table-result tbody').isVisible();
    if (tableVisible) {
      const data = await page.evaluate(() => {
        const table = document.querySelector('.table-result tbody');
        const rows = Array.from(table?.querySelectorAll('tr') || []);
        return rows.map(row => {
          const cells = Array.from(row.querySelectorAll('td'));
          return cells.map(cell => cell.innerText.trim());
        });
      });
    
      // Print JSON to stdout (so PHP can capture it)
      console.log(JSON.stringify({ success: true, data }));
    } else {
      console.log(JSON.stringify({ success: false, message: 'No PMA data available' }));
    }
  });
});
