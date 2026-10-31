<?php

namespace Modules\ResellerHub\App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Modules\ResellerHub\App\Helpers\ResellerHelper;

trait HasInvoiceSourceScopes
{
    /** Sources that unambiguously mean "recorded by the reseller, not the customer". */
    public const MANUAL_SOURCES = ['manual', 'manual_reseller', 'manual_provision'];

    public const ONLINE_PREFIXES = ['portal_', 'online_', 'dashboard_', 'website_', 'upgrade'];

    public function isOnlinePurchase(): bool
    {
        $src = $this->source ?: ($this->meta['source'] ?? null);

        if (!empty($src)) {
            if (in_array($src, self::MANUAL_SOURCES, true)) {
                return false;
            }

            if (ResellerHelper::isOnlineSource($src)) {
                return true;
            }
        }

        return !empty($this->meta['is_upgrade']);
    }

    /**
     * Get human-readable source label.
     */
    public function getSourceLabelAttribute(): string
    {
        if ($this->isOnlinePurchase()) {
            return $this->meta['source_label'] ?? 'خرید آنلاین از سایت';
        }

        return 'ثبت دستی نماینده';
    }

    /**
     * Get badge styling classes based on source.
     */
    public function getSourceBadgeAttribute(): string
    {
        if ($this->isOnlinePurchase()) {
            return 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200/80 dark:border-purple-800/80';
        }

        return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border-gray-200/60 dark:border-gray-700';
    }

    public function scopeOnline(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereIn('reseller_invoices.source', ResellerHelper::ONLINE_SOURCES);

            foreach (self::ONLINE_PREFIXES as $prefix) {
                $q->orWhere('reseller_invoices.source', 'like', $prefix . '%');
            }
        });
    }

    /**
     * Scope query to manually-sourced invoices.
     */
    public function scopeManual(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('reseller_invoices.source')
                ->orWhere(function (Builder $inner) {
                    $inner->whereNotIn('reseller_invoices.source', ResellerHelper::ONLINE_SOURCES);

                    foreach (self::ONLINE_PREFIXES as $prefix) {
                        $inner->where('reseller_invoices.source', 'not like', $prefix . '%');
                    }
                });
        });
    }
}
