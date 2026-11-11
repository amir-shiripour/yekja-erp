import { test } from '@playwright/test';
import path from 'path';

const outDir = 'C:/Users/ASUS/.gemini/antigravity/brain/4ccea370-7888-428b-bdb6-d90783290f92/screenshots';

test.describe('Subaccount Create Mobile View', () => {
    test.use({ viewport: { width: 375, height: 812 } });

    test('capture subaccount create page on mobile', async ({ page }) => {
        await page.goto('/_e2e/login?redirect=/reseller/sub-accounts/create');
        await page.waitForLoadState('networkidle');

        // Capture top
        await page.screenshot({ path: path.join(outDir, 'subaccount-create-top-mobile.png') });

        // Scroll to form actions / bottom
        const submitBtn = page.locator('button[type="submit"]').first();
        if (await submitBtn.count() > 0) {
            await submitBtn.scrollIntoViewIfNeeded();
            await page.waitForTimeout(300);
            await page.screenshot({ path: path.join(outDir, 'subaccount-create-bottom-mobile.png') });
        }
    });
});
