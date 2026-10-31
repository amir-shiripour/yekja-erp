<?php

declare(strict_types=1);

namespace Modules\ResellerHub\Tests\Unit;

use Carbon\Carbon;
use InvalidArgumentException;
use Modules\ResellerHub\App\Enums\PackageDiscountType;
use Modules\ResellerHub\App\Enums\PackagePriceType;
use Modules\ResellerHub\App\Models\ResellerPackage;
use Modules\ResellerHub\App\Services\Pricing\PriceResolver;
use Modules\ResellerHub\App\Support\CurrencyContext;
use Modules\ResellerHub\App\Support\Money;
use Modules\ResellerHub\Tests\TestCase;

/**
 * Unit tests for Money value object and PriceResolver service.
 *
 * Pure unit tests: no database connection or external dependencies needed.
 */
class MoneyAndPricingTest extends TestCase
{


    public function test_money_creation_and_normalization(): void
    {
        $m1 = Money::of(100000, 'toman');
        $this->assertSame(100000, $m1->amount);
        $this->assertSame('toman', $m1->currency);

        $m2 = Money::of('50000', 'IRT');
        $this->assertSame(50000, $m2->amount);
        $this->assertSame('toman', $m2->currency);

        $m3 = Money::of(200000, 'rial');
        $this->assertSame(200000, $m3->amount);
        $this->assertSame('rial', $m3->currency);

        $m4 = Money::of(null);
        $this->assertSame(0, $m4->amount);
        $this->assertSame('toman', $m4->currency);
    }

    public function test_money_addition_and_subtraction(): void
    {
        $m1 = Money::of(1000, 'toman');
        $m2 = Money::of(500, 'toman');

        $sum = $m1->add($m2);
        $this->assertSame(1500, $sum->amount);
        $this->assertSame('toman', $sum->currency);

        $diff = $m1->subtract($m2);
        $this->assertSame(500, $diff->amount);
        $this->assertSame('toman', $diff->currency);

        $this->assertSame(1000, $m1->amount);
        $this->assertSame(500, $m2->amount);
    }

    public function test_money_rejects_cross_currency_arithmetic(): void
    {
        $toman = Money::of(1000, 'toman');
        $rial = Money::of(1000, 'rial');

        $this->expectException(InvalidArgumentException::class);
        $toman->add($rial);
    }

    public function test_money_multiplication_and_percentage(): void
    {
        $m = Money::of(1000, 'toman');

        $mult = $m->multiply(3);
        $this->assertSame(3000, $mult->amount);

        $pct = $m->percentage(15.0);
        $this->assertSame(150, $pct->amount);
    }

    public function test_money_allocation_preserves_total(): void
    {
        $total = Money::of(100, 'toman');
        $allocated = $total->allocate(3);

        $this->assertCount(3, $allocated);
        $this->assertSame(100, array_sum(array_map(fn(Money $m) => $m->amount, $allocated)));

        $weighted = $total->allocateByWeights([1, 1, 1]);
        $this->assertCount(3, $weighted);
        $this->assertSame(100, array_sum(array_map(fn(Money $m) => $m->amount, $weighted)));
    }

    public function test_currency_context_default_fallback(): void
    {
        $currency = CurrencyContext::forReseller(null);
        $this->assertSame('toman', $currency);
    }


    public function test_price_resolver_subscription_raw_prices(): void
    {
        $package = new ResellerPackage();
        $package->price_type = PackagePriceType::SUBSCRIPTION->value;
        $package->monthly_price = 100000;
        $package->quarterly_price = 280000;
        $package->semi_annual_price = 540000;
        $package->yearly_price = 1000000;

        $resolver = new PriceResolver();

        $this->assertSame(100000, $resolver->rawPrice($package, 1));
        $this->assertSame(280000, $resolver->rawPrice($package, 3));
        $this->assertSame(540000, $resolver->rawPrice($package, 6));
        $this->assertSame(1000000, $resolver->rawPrice($package, 12));
    }

    public function test_price_resolver_free_and_onetime_packages(): void
    {
        $free = new ResellerPackage();
        $free->price_type = PackagePriceType::FREE->value;

        $resolver = new PriceResolver();
        $this->assertSame(0, $resolver->rawPrice($free, 12));
        $this->assertSame(0, $resolver->finalPrice($free, 12));

        $oneTime = new ResellerPackage();
        $oneTime->price_type = PackagePriceType::ONE_TIME->value;
        $oneTime->one_time_price = 500000;

        $this->assertSame(500000, $resolver->rawPrice($oneTime, 1));
        $this->assertSame(500000, $resolver->finalPrice($oneTime, 1));
    }

    public function test_price_resolver_custom_period_discounts(): void
    {
        $package = new ResellerPackage();
        $package->price_type = PackagePriceType::SUBSCRIPTION->value;
        $package->yearly_price = 1000000;
        $package->period_discounts = [
            '12' => [
                'has_custom' => true,
                'discount_value' => 200000,
            ],
        ];

        $resolver = new PriceResolver();

        $this->assertTrue($resolver->hasCustomDiscount($package, 12));
        $this->assertSame(200000, $resolver->discountAmount($package, 12));
        $this->assertSame(800000, $resolver->finalPrice($package, 12));
    }

    public function test_price_resolver_global_discount(): void
    {
        $package = new ResellerPackage();
        $package->price_type = PackagePriceType::SUBSCRIPTION->value;
        $package->monthly_price = 100000;
        $package->discount_type = PackageDiscountType::AMOUNT->value;
        $package->discount_value = 10000; // 10,000 toman discount
        $package->discount_starts_at = Carbon::now()->subDay();
        $package->discount_expires_at = Carbon::now()->addDay();

        $resolver = new PriceResolver();

        $this->assertTrue($resolver->hasGlobalActiveDiscount($package));
        $this->assertSame(10000, $resolver->discountAmount($package, 1));
        $this->assertSame(90000, $resolver->finalPrice($package, 1));
    }

    public function test_price_resolver_upgrade_over_detection(): void
    {
        $basic = new ResellerPackage();
        $basic->price_type = PackagePriceType::SUBSCRIPTION->value;
        $basic->monthly_price = 100000;
        $basic->yearly_price = 1000000;

        $pro = new ResellerPackage();
        $pro->price_type = PackagePriceType::SUBSCRIPTION->value;
        $pro->monthly_price = 200000;
        $pro->yearly_price = 2000000;

        $resolver = new PriceResolver();

        $this->assertTrue($resolver->isUpgradeOver($pro, $basic));
        $this->assertFalse($resolver->isUpgradeOver($basic, $pro));
        $this->assertFalse($resolver->isUpgradeOver($basic, $basic));
    }
}
