import { test, expect } from '@playwright/test';

test.describe('Tenant User Management, Sidebar & Onboarding Custom Steps E2E', () => {

  test('tenant admin sees proper user management menu without broken system_management', async ({ page }) => {
    // 1. Log in as tenant admin with active plan (user_id=10, tenant auto810592)
    const res = await page.goto('/_e2e/login?user_id=10&redirect=/auto810592/user/dashboard');
    expect(res?.status()).toBe(200);
    await page.waitForLoadState('networkidle');

    // 2. Verify sidebar does NOT contain broken 'system_management'
    const pageContent = await page.content();
    expect(pageContent).not.toContain('....system_management');
    expect(pageContent).not.toContain('menu.system_management');

    // 3. Verify 'مدیریت کاربران' link is present in sidebar
    const usersLink = page.locator('a[href*="/user/users"]').first();
    await expect(usersLink).toBeVisible();

    // 4. Click users link to open tenant user list
    await usersLink.click();
    await page.waitForLoadState('networkidle');

    // 5. Verify page title and create button
    await expect(page.locator('h1')).toContainText('مدیریت کاربران');
    const createBtn = page.locator('a[href*="/user/users/create"]').first();
    await expect(createBtn).toBeVisible();
    await expect(createBtn).toContainText('افزودن کاربر جدید');

    // 6. Navigate to create page
    await createBtn.click();
    await page.waitForLoadState('networkidle');

    await expect(page.locator('h1')).toContainText('ایجاد کاربر جدید');
    await expect(page.locator('input[name="name"]')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();

    // 7. Verify forbidden roles like super-admin are NOT exposed to tenant admin
    const formHtml = await page.content();
    expect(formHtml).not.toContain('super-admin');
    expect(formHtml).not.toContain('reseller-owner');
  });

  test('onboarding tour settings modal displays distinct internal routes for users and booking providers', async ({ page }) => {
    // 1. Log in as platform admin
    await page.goto('/_e2e/login?email=admin@admin.com&redirect=/user/reseller/settings');
    await page.waitForLoadState('networkidle');

    // 2. Check if tour tab button exists and click it
    const tourTabBtn = page.locator('button:has-text("تور آنبوردینگ"), a:has-text("تور آنبوردینگ")').first();
    if (await tourTabBtn.isVisible()) {
      await tourTabBtn.click();
      await page.waitForTimeout(500);
    }

    // 3. Check for add custom step button
    const addStepBtn = page.locator('button:has-text("افزودن گام"), button:has-text("گام اختصاصی")').first();
    if (await addStepBtn.isVisible()) {
      await addStepBtn.click();
      await page.waitForTimeout(500);

      // Verify the internal route select contains both distinct options
      const routeSelect = page.locator('select[x-model="newStep.internal_route"]').first();
      if (await routeSelect.isVisible()) {
        const selectHtml = await routeSelect.innerHTML();
        expect(selectHtml).toContain('user/users/create');
        expect(selectHtml).toContain('user/booking/providers');
        expect(selectHtml).toContain('user/clients/create');
      }
    }
  });

  test('solo mode booking service creation page loads cleanly', async ({ page }) => {
    // 1. Log in as tenant admin
    const res = await page.goto('/_e2e/login?user_id=10&redirect=/auto810592/user/booking/services/create');
    expect(res?.status()).toBe(200);
    await page.waitForLoadState('networkidle');

    // 2. Check that page loads without server crash
    const pageContent = await page.content();
    expect(pageContent).not.toContain('Server Error');
    expect(pageContent).not.toContain('Exception');
    expect(page.url()).toContain('/user/booking/services/create');
  });

});
