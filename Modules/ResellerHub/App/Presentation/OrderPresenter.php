<?php

declare(strict_types=1);

namespace Modules\ResellerHub\App\Presentation;

use Modules\ResellerHub\App\Enums\BillingCycle;
use Modules\ResellerHub\App\Enums\OrderProvisionStatus;
use Modules\ResellerHub\App\Enums\OrderStatus;
use Modules\ResellerHub\App\Models\ResellerOrder;

/**
 * View-model for order status, provisioning state and source badges.
 */
final class OrderPresenter
{
    public function statusEnum(ResellerOrder $order): ?OrderStatus
    {
        $status = $order->getAttribute('status');

        return $status instanceof OrderStatus
            ? $status
            : OrderStatus::fromOrDefault((string)$status);
    }

    public function statusLabel(ResellerOrder $order): string
    {
        return $this->statusEnum($order)?->label() ?? (string)$order->getAttribute('status');
    }

    public function statusBadgeClasses(ResellerOrder $order): string
    {
        return $this->statusEnum($order)?->badgeClasses()
            ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700';
    }

    /**
     * @return array{bg: string, dot: string, color: string, label: string}
     */
    public function statusBadge(ResellerOrder $order): array
    {
        $enum = $this->statusEnum($order);

        if ($enum) {
            return $enum->badgeConfig();
        }

        return [
            'bg' => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700',
            'dot' => 'bg-gray-400',
            'color' => '#6b7280',
            'label' => (string)$order->getAttribute('status'),
        ];
    }

    public function statusBadgeColor(ResellerOrder $order): string
    {
        return $this->statusBadge($order)['bg'] . ' border';
    }

    public function barColor(ResellerOrder $order): string
    {
        return $this->statusEnum($order)?->barColor() ?? 'bg-gray-400';
    }

    public function iconBoxClass(ResellerOrder $order): string
    {
        return $this->statusEnum($order)?->iconBoxClass()
            ?? 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700';
    }

    public function provisioningStatusEnum(ResellerOrder $order): ?OrderProvisionStatus
    {
        $status = $order->getAttribute('provisioning_status');

        return $status instanceof OrderProvisionStatus
            ? $status
            : OrderProvisionStatus::tryFrom((string)$status);
    }

    public function provisioningLabel(ResellerOrder $order): string
    {
        return $this->provisioningStatusEnum($order)?->label()
            ?? (string)$order->getAttribute('provisioning_status');
    }

    public function sourceLabel(ResellerOrder $order): string
    {
        return $order->isOnlinePurchase() ? 'خرید آنلاین از سایت' : 'ثبت دستی نماینده';
    }

    public function sourceBadge(ResellerOrder $order): string
    {
        return $order->isOnlinePurchase()
            ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/80'
            : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border-gray-200/60 dark:border-gray-700';
    }

    public function billingCycleLabel(ResellerOrder $order): string
    {
        $cycle = (string)$order->getAttribute('billing_cycle');

        if ($cycle === '') {
            return 'سالانه';
        }

        return BillingCycle::fromPeriodKey($cycle)->label();
    }
}
