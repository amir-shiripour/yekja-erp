<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class UiComponentsTest extends TestCase
{
    public function test_all_15_ui_components_render_cleanly_without_errors(): void
    {
        // 1. Copy Button
        $copyHtml = Blade::render('<x-ui.copy-button text="09123456789" label="کپی شماره" />');
        $this->assertStringContainsString('09123456789', $copyHtml);
        $this->assertStringContainsString('کپی شماره', $copyHtml);

        // 2. Delete Button
        $deleteHtml = Blade::render('<x-ui.delete-button wire:click="delete(1)" label="حذف" />');
        $this->assertStringContainsString('handleClick($event)', $deleteHtml);
        $this->assertStringContainsString('wire:click="delete(1)"', $deleteHtml);

        // 3. Color Picker
        $colorHtml = Blade::render('<x-ui.color-picker label="رنگ برند" name="brand_color" value="#0d9488" />');
        $this->assertStringContainsString('#0d9488', $colorHtml);
        $this->assertStringContainsString('brand_color', $colorHtml);

        // 4. File Upload
        $uploadHtml = Blade::render('<x-ui.file-upload label="آپلود فیش" name="receipt" />');
        $this->assertStringContainsString('آپلود فیش', $uploadHtml);
        $this->assertStringContainsString('livewire-upload-progress', $uploadHtml);

        // 5. Context Menu
        $ctxHtml = Blade::render('
            <x-ui.context-menu>
                <div>سطر تستی</div>
                <x-slot:items>
                    <button type="button">ویرایش</button>
                </x-slot:items>
            </x-ui.context-menu>
        ');
        $this->assertStringContainsString('سطر تستی', $ctxHtml);
        $this->assertStringContainsString('ویرایش', $ctxHtml);

        // 6. Empty State
        $emptyHtml = Blade::render('<x-ui.empty-state title="هیچ فاکتوری یافت نشد" action-label="ثبت فاکتور" action-url="/invoices/create" />');
        $this->assertStringContainsString('هیچ فاکتوری یافت نشد', $emptyHtml);
        $this->assertStringContainsString('ثبت فاکتور', $emptyHtml);

        // 7. Price Input
        $priceHtml = Blade::render('<x-ui.price-input label="مبلغ" name="amount" value="1500000" />');
        $this->assertStringContainsString('تومان', $priceHtml);
        $this->assertStringContainsString('numberToPersianWords', $priceHtml);

        // 8. National ID Input
        $nidHtml = Blade::render('<x-ui.national-id-input label="کد ملی" name="national_id" />');
        $this->assertStringContainsString('کد ملی', $nidHtml);
        $this->assertStringContainsString('maxlength="10"', $nidHtml);

        // 9. IBAN Input
        $ibanHtml = Blade::render('<x-ui.iban-input label="شماره شبا" name="iban" />');
        $this->assertStringContainsString('IR', $ibanHtml);
        $this->assertStringContainsString('شماره شبا', $ibanHtml);

        // 10. Card-to-Card
        $c2cHtml = Blade::render('<x-ui.card-to-card card-number="6037997123456789" owner="شرکت تست" amount="250000" />');
        $this->assertStringContainsString('بانک ملی ایران', $c2cHtml);
        $this->assertStringContainsString('6037 9971 2345 6789', $c2cHtml);
        $this->assertStringContainsString('شرکت تست', $c2cHtml);

        // 11. Payment Methods
        $pmHtml = Blade::render('<x-ui.payment-methods name="payment_method" :wallet-balance="500000" />');
        $this->assertStringContainsString('درگاه پرداخت اینترنتی', $pmHtml);
        $this->assertStringContainsString('کارت به کارت', $pmHtml);
        $this->assertStringContainsString('چک صیادی', $pmHtml);
        $this->assertStringContainsString('کیف پول', $pmHtml);

        // 12. OTP Input
        $otpHtml = Blade::render('<x-ui.otp-input label="کد تایید" length="5" name="otp" />');
        $this->assertStringContainsString('otp_4', $otpHtml);
        $this->assertStringContainsString('کد تایید', $otpHtml);

        // 13, 14, 15. Menu Notch, Mobile Dock, Command Palette
        $notchHtml = Blade::render('<x-ui.menu-notch />');
        $this->assertStringContainsString('Ctrl+K', $notchHtml);

        $dockHtml = Blade::render('<x-ui.dock />');
        $this->assertStringContainsString('خانه', $dockHtml);
        $this->assertStringContainsString('جستجو', $dockHtml);

        $paletteHtml = Blade::render('<x-ui.command-palette />');
        $this->assertStringContainsString('جستجوی سریع صفحات', $paletteHtml);

        // 16. Alert
        $alertHtml = Blade::render('<x-ui.alert type="success" title="تایید شد" dismissible>عملیات انجام شد</x-ui.alert>');
        $this->assertStringContainsString('تایید شد', $alertHtml);
        $this->assertStringContainsString('عملیات انجام شد', $alertHtml);

        // 17. Progress
        $progressHtml = Blade::render('<x-ui.progress :value="75" label="تکمیل پروفایل" color="emerald" />');
        $this->assertStringContainsString('تکمیل پروفایل', $progressHtml);
        $this->assertStringContainsString('75%', $progressHtml);

        // 18. Toast
        $toastHtml = Blade::render('<x-ui.toast position="bottom-left" />');
        $this->assertStringContainsString('@toast.window', $toastHtml);

        // 19. Badge
        $badgeHtml = Blade::render('<x-ui.badge variant="emerald" dot pulse>آنلاین</x-ui.badge>');
        $this->assertStringContainsString('آنلاین', $badgeHtml);
        $this->assertStringContainsString('animate-ping', $badgeHtml);

        // 20. Price Display
        $priceDisplayHtml = Blade::render('<x-ui.price :amount="1500000" :original-amount="2000000" />');
        $this->assertStringContainsString('1,500,000', $priceDisplayHtml);
        $this->assertStringContainsString('25%', $priceDisplayHtml);

        // 21. Password Input
        $pwHtml = Blade::render('<x-ui.password-input label="رمز عبور جدید" name="password" show-strength />');
        $this->assertStringContainsString('رمز عبور جدید', $pwHtml);
        $this->assertStringContainsString('Caps Lock', $pwHtml);

        // 22. Card Number Input
        $cardInputHtml = Blade::render('<x-ui.card-number-input label="شماره کارت" name="card_number" />');
        $this->assertStringContainsString('شماره کارت', $cardInputHtml);
        $this->assertStringContainsString('بانک ملی ایران', $cardInputHtml);
    }

    public function test_user_dashboard_renders_cleanly_without_modifying_core_layout(): void
    {
        $unique = uniqid('ui_', true);
        $user = User::create([
            'name'     => 'UI Tester ' . $unique,
            'email'    => $unique . '@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get(route('user.dashboard'));
        $response->assertStatus(200);
        $response->assertDontSee('جستجوی سریع صفحات، ماژول‌ها و تنظیمات...');
    }
}
