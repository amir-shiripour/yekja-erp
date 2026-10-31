<?php

namespace Modules\SmsReseller\App\Services;

use Illuminate\Support\Facades\DB;
use Modules\SmsReseller\App\Models\SmsResellerDailySummary;
use Modules\SmsReseller\App\Models\SmsResellerPanelPayment;
use Modules\SmsReseller\App\Models\SmsResellerUserCredit;
use Modules\SmsReseller\App\Models\SmsResellerUserPayment;
use Morilog\Jalali\Jalalian;
use Throwable;

class SmsResellerSyncService
{
    protected SmsResellerApiService $apiService;

    public function __construct(SmsResellerApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function syncAll(): array
    {
        $userPaymentsCount = $this->syncUserPayments();
        $panelPaymentsCount = $this->syncPanelPayments();
        $userCreditsCount = $this->syncUserCredits();
        $this->rebuildDailySummaries();

        return [
            'user_payments' => $userPaymentsCount,
            'panel_payments' => $panelPaymentsCount,
            'user_credits' => $userCreditsCount,
        ];
    }

    public function syncUserPayments(): int
    {
        $items = $this->apiService->getAllResellerPayments();
        $rows = [];

        foreach ($items as $item) {
            $createDate = trim($item['createDate'] ?? '');
            if (empty($createDate)) continue;

            $datePart = explode(' ', $createDate)[0];
            $ym = substr($datePart, 0, 7);
            $gregorian = null;
            try {
                $gregorian = Jalalian::fromFormat('Y/m/d', $datePart)->toCarbon()->format('Y-m-d');
            } catch (Throwable $e) {}

            $amount = (float) str_replace(',', '', (string)($item['amount'] ?? 0));
            $tax = (float) str_replace(',', '', (string)($item['tax'] ?? 0));
            $total = $amount + $tax;

            $hash = md5(($item['userId'] ?? '') . '|' . ($item['userName'] ?? '') . '|' . $amount . '|' . $tax . '|' . ($item['state'] ?? '') . '|' . $createDate);

            $rows[] = [
                'hash' => $hash,
                'user_name' => $item['userName'] ?? null,
                'user_id' => $item['userId'] ?? null,
                'bank' => $item['bank'] ?? null,
                'amount' => $amount,
                'tax' => $tax,
                'total_amount' => $total,
                'state' => $item['state'] ?? 'واریز شده',
                'type' => $item['type'] ?? 'پرداخت اینترنتی',
                'jalali_date' => $createDate,
                'jalali_ym' => $ym,
                'gregorian_date' => $gregorian,
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            SmsResellerUserPayment::upsert(
                $chunk,
                ['hash'],
                ['user_name', 'user_id', 'bank', 'amount', 'tax', 'total_amount', 'state', 'type', 'jalali_date', 'jalali_ym', 'gregorian_date', 'updated_at']
            );
        }

        return count($rows);
    }

    public function syncPanelPayments(): int
    {
        $items = $this->apiService->getAllPanelPayments();
        $rows = [];

        foreach ($items as $item) {
            $createDate = trim($item['createDate'] ?? '');
            if (empty($createDate)) continue;

            $datePart = explode(' ', $createDate)[0];
            $ym = substr($datePart, 0, 7);
            $gregorian = null;
            try {
                $gregorian = Jalalian::fromFormat('Y/m/d', $datePart)->toCarbon()->format('Y-m-d');
            } catch (Throwable $e) {}

            $amount = (float) str_replace(',', '', (string)($item['amount'] ?? 0));
            $tax = (float) str_replace(',', '', (string)($item['tax'] ?? 0));
            $total = $amount + $tax;

            $hash = md5(($item['accountName'] ?? '') . '|' . $amount . '|' . $tax . '|' . ($item['state'] ?? '') . '|' . $createDate);

            $rows[] = [
                'hash' => $hash,
                'account_name' => $item['accountName'] ?? null,
                'amount' => $amount,
                'tax' => $tax,
                'total_amount' => $total,
                'state' => $item['state'] ?? 'واریز شده',
                'type' => $item['type'] ?? 'پرداخت اینترنتی',
                'jalali_date' => $createDate,
                'jalali_ym' => $ym,
                'gregorian_date' => $gregorian,
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            SmsResellerPanelPayment::upsert(
                $chunk,
                ['hash'],
                ['account_name', 'amount', 'tax', 'total_amount', 'state', 'type', 'jalali_date', 'jalali_ym', 'gregorian_date', 'updated_at']
            );
        }

        return count($rows);
    }

    public function syncUserCredits(): int
    {
        $items = $this->apiService->getAllResellerCredits(15000);
        $rows = [];

        foreach ($items as $item) {
            $createDate = trim($item['createDate'] ?? '');
            if (empty($createDate)) continue;

            $datePart = explode(' ', $createDate)[0];
            $ym = substr($datePart, 0, 7);
            $gregorian = null;
            try {
                $gregorian = Jalalian::fromFormat('Y/m/d', $datePart)->toCarbon()->format('Y-m-d');
            } catch (Throwable $e) {}

            $amount = (float) str_replace(',', '', (string)($item['amount'] ?? 0));
            $currentCredit = (float) str_replace(',', '', (string)($item['currentCredit'] ?? 0));
            $increase = !empty($item['increase']);

            $hash = md5(($item['userId'] ?? '') . '|' . ($item['userName'] ?? '') . '|' . $amount . '|' . ($increase ? '1' : '0') . '|' . $createDate);

            $rows[] = [
                'hash' => $hash,
                'user_name' => $item['userName'] ?? null,
                'user_id' => $item['userId'] ?? null,
                'amount' => $amount,
                'current_credit' => $currentCredit,
                'increase' => $increase,
                'type' => $item['type'] ?? '',
                'jalali_date' => $createDate,
                'jalali_ym' => $ym,
                'gregorian_date' => $gregorian,
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            SmsResellerUserCredit::upsert(
                $chunk,
                ['hash'],
                ['user_name', 'user_id', 'amount', 'current_credit', 'increase', 'type', 'jalali_date', 'jalali_ym', 'gregorian_date', 'updated_at']
            );
        }

        return count($rows);
    }

    public function rebuildDailySummaries(): void
    {
        $userStats = DB::table('sms_reseller_user_payments')
            ->select(
                DB::raw("SUBSTRING(jalali_date, 1, 10) as jdate"),
                DB::raw("SUM(amount) as user_amount"),
                DB::raw("SUM(tax) as user_tax"),
                DB::raw("SUM(total_amount) as user_total"),
                DB::raw("COUNT(*) as user_count")
            )
            ->where('state', 'واریز شده')
            ->groupBy('jdate')
            ->get()
            ->keyBy('jdate');

        $panelStats = DB::table('sms_reseller_panel_payments')
            ->select(
                DB::raw("SUBSTRING(jalali_date, 1, 10) as jdate"),
                DB::raw("SUM(amount) as panel_amount"),
                DB::raw("SUM(tax) as panel_tax"),
                DB::raw("SUM(total_amount) as panel_total"),
                DB::raw("COUNT(*) as panel_count")
            )
            ->where('state', 'واریز شده')
            ->groupBy('jdate')
            ->get()
            ->keyBy('jdate');

        $creditStats = DB::table('sms_reseller_user_credits')
            ->select(
                DB::raw("SUBSTRING(jalali_date, 1, 10) as jdate"),
                DB::raw("COUNT(*) as credit_tx_count"),
                DB::raw("SUM(CASE WHEN increase = 1 THEN amount ELSE 0 END) as credit_inc_sum"),
                DB::raw("SUM(CASE WHEN increase = 0 THEN amount ELSE 0 END) as credit_dec_sum")
            )
            ->groupBy('jdate')
            ->get()
            ->keyBy('jdate');

        $allDates = $userStats->keys()
            ->merge($panelStats->keys())
            ->merge($creditStats->keys())
            ->unique()
            ->filter(fn($d) => strlen(trim((string)$d)) >= 10);

        $summaries = [];
        foreach ($allDates as $jdate) {
            $jdate = trim((string)$jdate);
            $ym = substr($jdate, 0, 7);
            $year = substr($jdate, 0, 4);
            $gregorian = null;
            try {
                $gregorian = Jalalian::fromFormat('Y/m/d', $jdate)->toCarbon()->format('Y-m-d');
            } catch (Throwable $e) {
                continue;
            }

            $u = $userStats[$jdate] ?? null;
            $p = $panelStats[$jdate] ?? null;
            $c = $creditStats[$jdate] ?? null;

            $userTotal = (float) ($u ? $u->user_total : 0);
            $panelTotal = (float) ($p ? $p->panel_total : 0);
            $netProfit = $userTotal - $panelTotal;

            $summaries[] = [
                'jalali_date' => $jdate,
                'jalali_ym' => $ym,
                'jalali_year' => $year,
                'gregorian_date' => $gregorian,
                'user_income_amount' => (float) ($u ? $u->user_amount : 0),
                'user_income_tax' => (float) ($u ? $u->user_tax : 0),
                'user_income_total' => $userTotal,
                'user_payments_count' => (int) ($u ? $u->user_count : 0),
                'panel_charge_amount' => (float) ($p ? $p->panel_amount : 0),
                'panel_charge_tax' => (float) ($p ? $p->panel_tax : 0),
                'panel_charge_total' => $panelTotal,
                'panel_charges_count' => (int) ($p ? $p->panel_count : 0),
                'user_credit_tx_count' => (int) ($c ? $c->credit_tx_count : 0),
                'user_credit_increase_sum' => (float) ($c ? $c->credit_inc_sum : 0),
                'user_credit_decrease_sum' => (float) ($c ? $c->credit_dec_sum : 0),
                'net_profit' => $netProfit,
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($summaries, 200) as $chunk) {
            SmsResellerDailySummary::upsert($chunk, ['jalali_date'], [
                'jalali_ym', 'jalali_year', 'gregorian_date',
                'user_income_amount', 'user_income_tax', 'user_income_total', 'user_payments_count',
                'panel_charge_amount', 'panel_charge_tax', 'panel_charge_total', 'panel_charges_count',
                'user_credit_tx_count', 'user_credit_increase_sum', 'user_credit_decrease_sum',
                'net_profit', 'updated_at'
            ]);
        }
    }
}
