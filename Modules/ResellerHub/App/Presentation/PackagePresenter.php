<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Presentation;

use Modules\ResellerHub\App\Enums\PackageColor;
use Modules\ResellerHub\App\Enums\PackagePriceType;
use Modules\ResellerHub\App\Models\ResellerPackage;


final class PackagePresenter
{
    public function priceTypeLabel(ResellerPackage $package): string
    {
        if ($package->isFree()) {
            return PackagePriceType::FREE->label();
        }

        return $package->getPriceTypeEnum()->label();
    }

    public function colorHex(ResellerPackage $package): string
    {
        $raw = trim((string)($package->getAttribute('color') ?? ''));

        if ($raw === '') {
            return PackageColor::INDIGO->hex();
        }

        if (str_starts_with($raw, '#')) {
            return $raw;
        }

        if (preg_match('/^[a-fA-F0-9]{6}$/', $raw)) {
            return '#' . $raw;
        }

        return PackageColor::fromOrDefault($raw)->hex();
    }

    public function backgroundStyle(ResellerPackage $package): string
    {
        return "background-color: {$this->colorHex($package)}; color: #ffffff;";
    }

    public function lightBackgroundStyle(ResellerPackage $package): string
    {
        $hex = ltrim($this->colorHex($package), '#');

        if (strlen($hex) !== 6) {
            return 'background-color: rgba(79, 70, 229, 0.12); color: #4f46e5; border-color: rgba(79, 70, 229, 0.25);';
        }

        $red = (int)hexdec(substr($hex, 0, 2));
        $green = (int)hexdec(substr($hex, 2, 2));
        $blue = (int)hexdec(substr($hex, 4, 2));

        return "background-color: rgba({$red}, {$green}, {$blue}, 0.12); color: {$this->colorHex($package)}; border-color: rgba({$red}, {$green}, {$blue}, 0.25);";
    }
}
