const { test, expect } = require('@playwright/test');

test('User registration and login E2E', async ({ page }) => {

    // 1. Regisztrációs oldal
    await page.goto('http://localhost:3000/register');

    await page.fill('#username', 'e2e_testuser');
    await page.fill('#email', 'e2e_test@example.com');
    await page.fill('#password', 'Teszt123');
    await page.click('#registerBtn');

    // Sikeres regisztráció üzenet
    await expect(page.locator('.success')).toContainText("Sikeres regisztráció");

    // 2. Login
    await page.goto('http://localhost:3000/login');
    await page.fill('#email', 'e2e_test@example.com');
    await page.fill('#password', 'Teszt123');
    await page.click('#loginBtn');

    // 3. Dashboard megnyílt?
    await expect(page).toHaveURL(/dashboard/);
});
