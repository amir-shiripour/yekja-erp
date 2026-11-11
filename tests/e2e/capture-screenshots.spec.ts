import { test } from '@playwright/test';
import path from 'path';

const outDir = 'C:/Users/ASUS/.gemini/antigravity/brain/4ccea370-7888-428b-bdb6-d90783290f92/screenshots';

test.describe('Visual Screenshot Capture', () => {

  test('capture desktop screenshots', async ({ page }, testInfo) => {
    if (testInfo.project.name !== 'desktop') return;

    // 1. Dashboard
    await page.goto('/_e2e/login?redirect=/reseller/dashboard');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: path.join(outDir, 'desktop-dashboard.png') });

    // 2. Tenants Index
    await page.goto('/_e2e/login?redirect=/reseller/tenants');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: path.join(outDir, 'desktop-tenants.png') });

    // 3. Create Wizard
    await page.goto('/_e2e/login?redirect=/reseller/tenants/create');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: path.join(outDir, 'desktop-create-wizard.png') });

    // 4. Invoices Index
    await page.goto('/_e2e/login?redirect=/reseller/invoices');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: path.join(outDir, 'desktop-invoices.png') });
  });

  test('capture mobile screenshots', async ({ page }, testInfo) => {
    if (testInfo.project.name !== 'mobile') return;

    // 1. Mobile Dashboard
    await page.goto('/_e2e/login?redirect=/reseller/dashboard');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: path.join(outDir, 'mobile-dashboard.png') });

    // 2. Mobile Tenants (Stacked Cards)
    await page.goto('/_e2e/login?redirect=/reseller/tenants');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: path.join(outDir, 'mobile-tenants-cards.png') });

    // 3. Mobile Create Wizard (Sticky Bottom & Stepper)
    await page.goto('/_e2e/login?redirect=/reseller/tenants/create');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: path.join(outDir, 'mobile-create-wizard.png') });

    // 4. Mobile Invoices (Cards)
    await page.goto('/_e2e/login?redirect=/reseller/invoices');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: path.join(outDir, 'mobile-invoices-cards.png') });
  });

});
