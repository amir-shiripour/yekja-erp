import { test } from '@playwright/test';
import path from 'path';

const outDir = 'C:/Users/ASUS/.gemini/antigravity/brain/4ccea370-7888-428b-bdb6-d90783290f92/screenshots';

test.describe('Mobile Wizard Visual Verification', () => {
    test.use({ viewport: { width: 375, height: 812 } });

    test('capture mobile create and edit wizard step 1', async ({ page }) => {
        // 1. Create Wizard
        await page.goto('/_e2e/login?redirect=/reseller/tenants/create');
        await page.waitForLoadState('networkidle');

        // Scroll to appearance section
        const customBtn = page.locator('[aria-label="ظاهر اختصاصی"]').first();
        await customBtn.scrollIntoViewIfNeeded();
        await page.screenshot({ path: path.join(outDir, 'verified-create-appearance-mobile.png') });

        // Click custom white-label button
        await customBtn.click();
        await page.waitForTimeout(600);

        // Capture mockup window
        const mockupHeading = page.locator('text=پیش‌نمایش').first();
        await mockupHeading.scrollIntoViewIfNeeded();
        await page.waitForTimeout(400);
        await page.screenshot({ path: path.join(outDir, 'verified-create-mockup-mobile.png') });

        // Scroll to bottom footer
        const nextBtn = page.locator('button:has-text("مرحله بعدی: انتخاب خریدار سامانه")').first();
        await nextBtn.scrollIntoViewIfNeeded();
        await page.waitForTimeout(300);
        await page.screenshot({ path: path.join(outDir, 'verified-create-footer-mobile.png') });

        // 2. Edit Wizard
        await page.goto('/_e2e/login?redirect=/reseller/tenants/1/edit');
        await page.waitForLoadState('networkidle');

        // Scroll to appearance section in edit wizard
        const editCustomBtn = page.locator('[aria-label="ظاهر اختصاصی"]').first();
        await editCustomBtn.scrollIntoViewIfNeeded();
        await page.screenshot({ path: path.join(outDir, 'verified-edit-appearance-mobile.png') });

        // Click custom white-label in edit wizard
        await editCustomBtn.click();
        await page.waitForTimeout(600);

        // Capture mockup in edit wizard
        const editMockupHeading = page.locator('text=پیش‌نمایش').first();
        await editMockupHeading.scrollIntoViewIfNeeded();
        await page.waitForTimeout(400);
        await page.screenshot({ path: path.join(outDir, 'verified-edit-mockup-mobile.png') });

        // Footer in edit wizard
        const editNextBtn = page.locator('button:has-text("مرحله بعدی: اطلاعات کاربری سامانه")').first();
        await editNextBtn.scrollIntoViewIfNeeded();
        await page.waitForTimeout(300);
        await page.screenshot({ path: path.join(outDir, 'verified-edit-footer-mobile.png') });
    });
});
