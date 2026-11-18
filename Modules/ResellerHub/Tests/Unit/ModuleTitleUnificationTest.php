<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use App\Models\Module;
use Illuminate\Support\Facades\File;
use Modules\ResellerHub\App\Helpers\ResellerHelper;
use Modules\ResellerHub\App\Services\Support\ModuleTitleResolver;
use Modules\ResellerHub\Tests\TestCase;

class ModuleTitleUnificationTest extends TestCase
{
    protected ModuleTitleResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = app(ModuleTitleResolver::class);
    }

    /**
     * Test that every active system module resolves strictly to Persian with zero English.
     */
    public function test_all_system_modules_resolve_to_pure_persian_titles(): void
    {
        $modules = Module::all();

        foreach ($modules as $m) {
            $slug = $m->slug;
            $title = ResellerHelper::getModuleTitle($slug);

            $this->assertNotEmpty($title, "Module [{$slug}] resolved to an empty title.");
            $this->assertMatchesRegularExpression('/[\x{0600}-\x{06FF}]/u', $title, "Module [{$slug}] title [{$title}] must contain Persian characters.");
            $this->assertDoesNotMatchRegularExpression('/[a-zA-Z]/', $title, "Module [{$slug}] title [{$title}] must not contain English characters.");
        }
    }

    /**
     * Test that common slug variations (underscores, aliases, compound words) resolve to clean Persian.
     */
    public function test_common_slug_variations_and_aliases_resolve_to_persian(): void
    {
        $testSlugs = [
            'client_calls' => 'تماس‌های مشتریان',
            'clientcalls' => 'تماس‌های مشتریان',
            'sms_reseller' => 'ریسیلر پیامک',
            'smsreseller' => 'ریسیلر پیامک',
            'domain_manager' => 'مدیریت دامنه',
            'domainmanager' => 'مدیریت دامنه',
            'contract_forge' => 'طراحی قرارداد',
            'contractforge' => 'طراحی قرارداد',
            'content_forge' => 'مدیریت محتوا',
            'contentforge' => 'مدیریت محتوا',
            'smart_bot' => 'داشبورد دستیار هوشمند',
            'smartbot' => 'داشبورد دستیار هوشمند',
            'online_booking' => 'نوبت‌دهی آنلاین',
            'onlinebooking' => 'نوبت‌دهی آنلاین',
            'direct_admin' => 'دایرکت ادمین',
            'directadmin' => 'دایرکت ادمین',
            'crm' => 'سامانه ارتباط با مشتری',
            'core' => 'سامانه ارتباط با مشتری',
            'appointments' => 'نوبت‌ها',
            'invoices' => 'فاکتورها',
            'warehouses' => 'انبارها',
            'stores' => 'شعب و فروشگاه‌ها',
            'products' => 'محصولات',
        ];

        foreach ($testSlugs as $slug => $expected) {
            $title = ResellerHelper::getModuleTitle($slug);

            $this->assertNotEmpty($title, "Slug variation [{$slug}] resolved to empty.");
            $this->assertEquals($expected, $title, "Slug variation [{$slug}] did not resolve to expected Persian title [{$expected}]. Got: [{$title}].");
            $this->assertDoesNotMatchRegularExpression('/[a-zA-Z]/', $title, "Slug [{$slug}] title [{$title}] must not contain English characters.");
        }
    }

    /**
     * Test that packages/show.blade.php defines $modulesMap and uses ResellerHelper::getModuleTitle.
     */
    public function test_packages_show_blade_uses_dynamic_module_title_resolver(): void
    {
        $path = module_path('ResellerHub', 'resources/views/user/packages/show.blade.php');
        $this->assertFileExists($path);

        $content = File::get($path);
        $this->assertStringContainsString('$modulesMap = ($availableModules ?? collect())->keyBy(\'slug\');', $content);
        $this->assertStringContainsString('ResellerHelper::getModuleTitle($slug)', $content);
        $this->assertStringNotContainsString('$title = $mod ? $mod->title : $slug;', $content);
    }

    /**
     * Test that packages/index.blade.php uses ResellerHelper::getModuleTitle.
     */
    public function test_packages_index_blade_uses_dynamic_module_title_resolver(): void
    {
        $path = module_path('ResellerHub', 'resources/views/user/packages/index.blade.php');
        $this->assertFileExists($path);

        $content = File::get($path);
        $this->assertStringContainsString('ResellerHelper::getModuleTitle($slug)', $content);
        $this->assertStringNotContainsString('{{ $modulesMap[$slug]->title ?? $slug }}', $content);
    }

    /**
     * Test that ModuleCapabilityCatalogService resolves module titles using ResellerHelper.
     */
    public function test_module_capability_catalog_service_uses_reseller_helper(): void
    {
        $path = module_path('ResellerHub', 'App/Services/ModuleCapabilityCatalogService.php');
        $this->assertFileExists($path);

        $content = File::get($path);
        $this->assertStringContainsString('$title = ResellerHelper::getModuleTitle($slug);', $content);
    }
}
