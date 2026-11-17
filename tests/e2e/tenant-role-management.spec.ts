import { test, expect } from '@playwright/test';

test.describe('Tenant Role & Permissions Management E2E', () => {

  test('tenant admin sees roles menu in sidebar and can create custom role', async ({ page }) => {
    // 1. Log in as tenant admin (user_id=10, tenant auto810592)
    const res = await page.goto('/_e2e/login?user_id=10&redirect=/auto810592/user/dashboard');
    expect(res?.status()).toBe(200);
    await page.waitForLoadState('networkidle');

    // 2. Verify sidebar contains 'نقش‌ها و دسترسی‌ها'
    const rolesLink = page.locator('a[href*="/user/roles"]').first();
    await expect(rolesLink).toBeVisible();

    // 3. Click to navigate to /user/roles
    await rolesLink.click();
    await page.waitForLoadState('networkidle');

    // 4. Verify roles index page
    await expect(page.locator('h1')).toContainText('مدیریت نقش‌ها');
    const createBtn = page.locator('a[href*="/user/roles/create"]').first();
    await expect(createBtn).toBeVisible();
    await expect(createBtn).toContainText('افزودن نقش جدید');

    // 5. Navigate to create role page
    await createBtn.click();
    await page.waitForLoadState('networkidle');
    await expect(page.locator('h1')).toContainText('ایجاد نقش جدید');

    // 6. Fill role details
    const roleDisplayName = 'پذیرش شیفت صبح';
    await page.fill('input[name="display_name"]', roleDisplayName);

    // 7. Check at least one permission checkbox
    const firstCheckbox = page.locator('input[type="checkbox"][name="permissions[]"]').first();
    if (await firstCheckbox.isVisible()) {
      await firstCheckbox.check();
    }

    // 8. Submit form
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    // 9. Verify redirect back to roles index and custom role exists with badge
    await expect(page.locator('h1')).toContainText('مدیریت نقش‌ها');
    await expect(page.locator('body')).toContainText(roleDisplayName);
    await expect(page.locator('body')).toContainText('سفارشی سازمان');

    // 10. Verify system template role shows badge 'عمومی سامانه'
    await expect(page.locator('body')).toContainText('عمومی سامانه');
  });

});
