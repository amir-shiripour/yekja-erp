<?php

namespace Modules\ResellerHub\App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasSubAccountSearch
{
    /**
     * Apply search filter to a reseller invoice query.
     *
     * @param Builder $query
     * @param string|null $search
     * @return Builder
     */
    protected function applyInvoiceSearch(Builder $query, ?string $search): Builder
    {
        $s = trim((string)$search);
        if ($s === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($s) {
            $q->where('reseller_invoices.invoice_number', 'like', '%' . $s . '%')
                ->orWhere('reseller_invoices.sub_account_name', 'like', '%' . $s . '%')
                ->orWhere('reseller_invoices.sub_account_mobile', 'like', '%' . $s . '%')
                ->orWhere('reseller_invoices.sub_account_email', 'like', '%' . $s . '%')
                ->orWhereHas('tenant', function (Builder $tq) use ($s) {
                    $tq->where('name', 'like', '%' . $s . '%')
                        ->orWhereHas('subAccount', function (Builder $sq) use ($s) {
                            $sq->where('name', 'like', '%' . $s . '%')
                                ->orWhere('mobile', 'like', '%' . $s . '%');
                        });
                });
        });
    }

    /**
     * Apply search filter to a reseller order query.
     *
     * @param Builder $query
     * @param string|null $search
     * @return Builder
     */
    protected function applyOrderSearch(Builder $query, ?string $search): Builder
    {
        $s = trim((string)$search);
        if ($s === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($s) {
            $q->where('reseller_orders.order_number', 'like', '%' . $s . '%')
                ->orWhere('reseller_orders.sub_account_name', 'like', '%' . $s . '%')
                ->orWhere('reseller_orders.sub_account_mobile', 'like', '%' . $s . '%')
                ->orWhere('reseller_orders.sub_account_email', 'like', '%' . $s . '%')
                ->orWhereHas('tenant', function (Builder $tq) use ($s) {
                    $tq->where('name', 'like', '%' . $s . '%')
                        ->orWhere('subdomain', 'like', '%' . $s . '%');
                })
                ->orWhereHas('invoice', function (Builder $iq) use ($s) {
                    $iq->where('invoice_number', 'like', '%' . $s . '%');
                });
        });
    }

    /**
     * Apply search filter to a tenant query in reseller context.
     *
     * @param Builder $query
     * @param string|null $search
     * @return Builder
     */
    protected function applyTenantSearch(Builder $query, ?string $search): Builder
    {
        $s = trim((string)$search);
        if ($s === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($s) {
            $q->where('name', 'like', '%' . $s . '%')
                ->orWhere('subdomain', 'like', '%' . $s . '%')
                ->orWhere('custom_domain', 'like', '%' . $s . '%')
                ->orWhereHas('subAccount', function (Builder $sq) use ($s) {
                    $sq->where('name', 'like', '%' . $s . '%')
                        ->orWhere('mobile', 'like', '%' . $s . '%')
                        ->orWhere('email', 'like', '%' . $s . '%')
                        ->orWhere('company_name', 'like', '%' . $s . '%');
                })
                ->orWhereHas('users', function (Builder $oq) use ($s) {
                    $oq->where('name', 'like', '%' . $s . '%')
                        ->orWhere('mobile', 'like', '%' . $s . '%')
                        ->orWhere('email', 'like', '%' . $s . '%');
                });
        });
    }

    /**
     * Apply search filter to a reseller sub-account query.
     *
     * @param Builder $query
     * @param string|null $search
     * @return Builder
     */
    protected function applySubAccountSearch(Builder $query, ?string $search): Builder
    {
        $s = trim((string)$search);
        if ($s === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($s) {
            $q->where('name', 'like', '%' . $s . '%')
                ->orWhere('email', 'like', '%' . $s . '%')
                ->orWhere('mobile', 'like', '%' . $s . '%')
                ->orWhere('company_name', 'like', '%' . $s . '%')
                ->orWhereHas('tenant', function (Builder $tq) use ($s) {
                    $tq->where('name', 'like', '%' . $s . '%')
                        ->orWhere('subdomain', 'like', '%' . $s . '%');
                });
        });
    }

    /**
     * Apply online/manual source filter to an invoice, order, or sub-account query.
     *
     * @param Builder $query
     * @param string|null $source
     * @return Builder
     */
    protected function applySourceFilter(Builder $query, ?string $source): Builder
    {
        $s = trim((string)$source);
        if ($s === 'online') {
            return $query->online();
        }

        if ($s === 'manual') {
            return $query->manual();
        }

        return $query;
    }
}

