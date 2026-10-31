<?php

namespace Modules\SmsReseller\App\Services;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\Document;
use Modules\Accounting\App\Models\FundAccount;
use Modules\Accounting\App\Models\Transaction;
use Modules\Accounting\App\Services\AccountingEngine;
use Modules\SmsReseller\App\Models\SmsResellerPanelPayment;
use Modules\SmsReseller\App\Models\SmsResellerSetting;
use Modules\SmsReseller\App\Models\SmsResellerUserPayment;
use Modules\Wallet\App\Models\Wallet;
use Morilog\Jalali\Jalalian;

class SmsResellerReconciliationService
{
    protected SmsResellerApiService $apiService;
    protected AccountingEngine $accountingEngine;

    public function __construct(SmsResellerApiService $apiService, AccountingEngine $accountingEngine)
    {
        $this->apiService = $apiService;
        $this->accountingEngine = $accountingEngine;
    }


    public function analyzeReconciliation(): array
    {
        $setting = SmsResellerSetting::first();

        $userPayments = SmsResellerUserPayment::where('state', 'واریز شده')->get();
        $totalUserCount = $userPayments->count();
        $totalUserAmount = (float) $userPayments->sum('amount');
        $totalUserTax = (float) $userPayments->sum('tax');
        $totalUserIncome = (float) $userPayments->sum('total_amount');


        $panelPayments = SmsResellerPanelPayment::where('state', 'واریز شده')->get();
        $totalPanelCount = $panelPayments->count();
        $totalPanelAmount = (float) $panelPayments->sum('amount');
        $totalPanelTax = (float) $panelPayments->sum('tax');
        $totalPanelCost = (float) $panelPayments->sum('total_amount');

        $calculatedProfit = $totalUserIncome - $totalPanelCost;

        $onlineWalletBalance = 0;
        $smsRemainingCount = null;
        $panelOwnerName = null;
        $operatorTariffs = [];

        try {
            $creditInfo = $this->apiService->getCurrentCredit();
            if (!empty($creditInfo['result'])) {
                $res = $creditInfo['result'];
                $onlineWalletBalance = (float) ($res['credit'] ?? 0);
                $smsRemainingCount = (int) ($res['smsCount'] ?? 0);
                $panelOwnerName = trim(($res['firstName'] ?? '') . ' ' . ($res['lastName'] ?? ''));
                $operatorTariffs = $res['userSmsPrice'] ?? [];
            }
        } catch (\Throwable $e) {}

        $sourceDocs = DB::table('accounting_source_documents')
            ->where('module', 'smsreseller')
            ->get();

        $userIncomeDocIds = $sourceDocs->where('event_type', 'user_income_payment')->pluck('sourceable_id')->all();
        $panelExpenseDocIds = $sourceDocs->where('event_type', 'panel_expense_payment')->pluck('sourceable_id')->all();
        $cumulativeReconciliationDoc = $sourceDocs->where('event_type', 'cumulative_reconciliation')->first();

        $unrecordedUserPayments = $userPayments->whereNotIn('id', $userIncomeDocIds);
        $unrecordedPanelPayments = $panelPayments->whereNotIn('id', $panelExpenseDocIds);

        $unrecordedUserIncomeSum = (float) $unrecordedUserPayments->sum('total_amount');
        $unrecordedPanelCostSum = (float) $unrecordedPanelPayments->sum('total_amount');

        $allSmsDocIds = $sourceDocs->pluck('document_id')->all();
        $accountingIncomeTotal = 0;
        $accountingExpenseTotal = 0;
        $accountingTaxTotal = 0;

        if (!empty($allSmsDocIds)) {
            $txs = Transaction::whereIn('document_id', $allSmsDocIds)->with('category')->get();
            foreach ($txs as $tx) {
                if ($tx->category?->type === 'income') {
                    $accountingIncomeTotal += (float) $tx->credit;
                } elseif ($tx->category?->type === 'expense') {
                    $accountingExpenseTotal += (float) $tx->debit;
                } elseif ($tx->category?->type === 'liability') {
                    $accountingTaxTotal += (float) ($tx->credit - $tx->debit);
                }
            }
        }

        $walletFundBalance = null;
        if ($setting?->default_wallet_fund_account_id) {
            $walletFund = FundAccount::find($setting->default_wallet_fund_account_id);
            if ($walletFund) {
                $walletFundBalance = (float) $walletFund->current_balance;
            }
        }

        $issues = [];

        if (!$setting?->default_income_category_id || !$setting?->default_expense_category_id || !$setting?->default_fund_account_id) {
            $issues[] = [
                'type' => 'warning',
                'title' => 'عدم انتخاب سرفصل‌های پیش‌فرض حسابداری',
                'message' => 'لطفاً به تب تنظیمات بروید و سرفصل‌های پیش‌فرض درآمد، هزینه و حساب بانکی را تعیین فرمایید تا ثبت اسناد اتوماتیک انجام گیرد.',
                'action_url' => route('user.smsreseller.settings'),
                'action_label' => 'رفتن به تنظیمات',
            ];
        }

        if ($unrecordedUserPayments->count() > 0) {
            $issues[] = [
                'type' => 'info',
                'title' => "تعداد {$unrecordedUserPayments->count()} پرداخت کاربران ثبت سند نشده است",
                'message' => "مجموعاً مبلغ " . number_format($unrecordedUserIncomeSum) . " ریال دریافتی از کاربران هنوز به عنوان سند درآمد در دفاتر حسابداری ثبت نگردیده است.",
                'action_url' => route('user.smsreseller.user_payments'),
                'action_label' => 'مشاهده و ثبت اسناد درآمد',
            ];
        }

        if ($unrecordedPanelPayments->count() > 0) {
            $issues[] = [
                'type' => 'info',
                'title' => "تعداد {$unrecordedPanelPayments->count()} شارژ پنل ثبت سند نشده است",
                'message' => "مجموعاً مبلغ " . number_format($unrecordedPanelCostSum) . " ریال شارژ پنل هنوز به عنوان سند مرکب هزینه در دفاتر حسابداری ثبت نگردیده است.",
                'action_url' => route('user.smsreseller.panel_payments'),
                'action_label' => 'مشاهده و ثبت اسناد هزینه',
            ];
        }

        $discrepancyAmount = abs($unrecordedUserIncomeSum - $unrecordedPanelCostSum);
        $isBalanced = ($unrecordedUserPayments->count() === 0 && $unrecordedPanelPayments->count() === 0);

        return [
            'totalUserCount' => $totalUserCount,
            'totalUserIncome' => $totalUserIncome,
            'totalUserAmount' => $totalUserAmount,
            'totalUserTax' => $totalUserTax,
            'totalPanelCount' => $totalPanelCount,
            'totalPanelCost' => $totalPanelCost,
            'totalPanelAmount' => $totalPanelAmount,
            'totalPanelTax' => $totalPanelTax,
            'calculatedProfit' => $calculatedProfit,
            'onlineWalletBalance' => $onlineWalletBalance,
            'smsRemainingCount' => $smsRemainingCount,
            'panelOwnerName' => $panelOwnerName,
            'operatorTariffs' => $operatorTariffs,
            'walletFundBalance' => $walletFundBalance,
            'unrecordedUserCount' => $unrecordedUserPayments->count(),
            'unrecordedUserSum' => $unrecordedUserIncomeSum,
            'unrecordedPanelCount' => $unrecordedPanelPayments->count(),
            'unrecordedPanelSum' => $unrecordedPanelCostSum,
            'accountingIncomeTotal' => $accountingIncomeTotal,
            'accountingExpenseTotal' => $accountingExpenseTotal,
            'discrepancyAmount' => $discrepancyAmount,
            'isBalanced' => $isBalanced,
            'cumulativeReconciliationDoc' => $cumulativeReconciliationDoc,
            'issues' => $issues,
            'unrecordedUserPayments' => $unrecordedUserPayments->take(10),
            'unrecordedPanelPayments' => $unrecordedPanelPayments->take(10),
        ];
    }


    public function recordCumulativeReconciliation(
        int $incomeCatId,
        int $expenseCatId,
        ?int $taxCatId,
        int $fundAccountId,
        ?string $docDate = null,
        ?string $description = null,
        ?int $walletId = null
    ): Document {
        $userPayments = SmsResellerUserPayment::where('state', 'واریز شده')->get();
        $panelPayments = SmsResellerPanelPayment::where('state', 'واریز شده')->get();

        $sourceDocs = DB::table('accounting_source_documents')
            ->where('module', 'smsreseller')
            ->get();

        $recordedUserIds = $sourceDocs->where('event_type', 'user_income_payment')->pluck('sourceable_id')->all();
        $recordedPanelIds = $sourceDocs->where('event_type', 'panel_expense_payment')->pluck('sourceable_id')->all();

        $pendingUsers = $userPayments->whereNotIn('id', $recordedUserIds);
        $pendingPanels = $panelPayments->whereNotIn('id', $recordedPanelIds);

        $totalUserAmount = (float) $pendingUsers->sum('amount');
        $totalUserTax = (float) $pendingUsers->sum('tax');
        $totalUserIncome = (float) $pendingUsers->sum('total_amount');

        $totalPanelAmount = (float) $pendingPanels->sum('amount');
        $totalPanelTax = (float) $pendingPanels->sum('tax');
        $totalPanelCost = (float) $pendingPanels->sum('total_amount');

        $fundAccount = FundAccount::findOrFail($fundAccountId);
        $fundCatId = $fundAccount->category_id ?: Category::where('type', 'asset')->first()->id;

        $docDateFormatted = $docDate ?: now()->format('Y-m-d');
        if (str_contains($docDateFormatted, '/')) {
            try {
                $docDateFormatted = Jalalian::fromFormat('Y/m/d', $docDateFormatted)->toCarbon()->format('Y-m-d');
            } catch (\Throwable $e) {}
        }

        $desc = $description ?: "سند تجمیعی هماهنگ‌سازی و رفع مغایرت سوابق تاریخی ریسیلر پیامک (دریافتی: " . number_format($totalUserIncome) . " ریال | شارژ: " . number_format($totalPanelCost) . " ریال)";

        $rows = [];

        if ($totalUserAmount > 0) {
            $rows[] = [
                'category_id' => $incomeCatId,
                'fund_account_id' => null,
                'debit' => 0,
                'credit' => $totalUserAmount,
                'description' => "مجموع درآمد تاریخی واریزی کاربران پیامک ({$pendingUsers->count()} پرداخت)",
            ];
        }

        if ($totalUserTax > 0 && $taxCatId) {
            $rows[] = [
                'category_id' => $taxCatId,
                'fund_account_id' => null,
                'debit' => 0,
                'credit' => $totalUserTax,
                'description' => "مالیات بر ارزش افزوده دریافتی از کاربران پیامک",
            ];
        }

        if ($totalPanelAmount > 0) {
            $rows[] = [
                'category_id' => $expenseCatId,
                'fund_account_id' => null,
                'debit' => $totalPanelAmount,
                'credit' => 0,
                'description' => "مجموع هزینه تاریخی شارژ پنل اصلی پیامک ({$pendingPanels->count()} شارژ)",
            ];
        }

        if ($totalPanelTax > 0 && $taxCatId) {
            $rows[] = [
                'category_id' => $taxCatId,
                'fund_account_id' => null,
                'debit' => $totalPanelTax,
                'credit' => 0,
                'description' => "مالیات بر ارزش افزوده پرداختی شارژ پنل پیامک",
            ];
        }

        $wallet = $walletId && class_exists(Wallet::class)
            ? Wallet::with('holder')->find($walletId)
            : null;
        $walletHolderInfo = '';
        if ($wallet && $wallet->holder) {
            $holder = $wallet->holder;
            $holderName = $holder->name ?? ($holder->full_name ?? ($holder->username ?? 'کاربر'));
            $holderPhone = $holder->phone ?? ($holder->mobile ?? '');
            $walletHolderInfo = $holderName . ($holderPhone ? " ({$holderPhone})" : '');
        }

        $fundTargetName = $walletHolderInfo ? "کیف پول کاربر {$walletHolderInfo}" : "حساب {$fundAccount->name}";

        $netDiff = $totalUserIncome - $totalPanelCost;
        if ($netDiff > 0) {
            $rows[] = [
                'category_id' => $fundCatId,
                'fund_account_id' => $fundAccount->id,
                'debit' => $netDiff,
                'credit' => 0,
                'description' => "خالص سود و مازاد واریزی به {$fundTargetName}",
            ];
        } elseif ($netDiff < 0) {
            $rows[] = [
                'category_id' => $fundCatId,
                'fund_account_id' => $fundAccount->id,
                'debit' => 0,
                'credit' => abs($netDiff),
                'description' => "تامین هزینه شارژ پنل از {$fundTargetName}",
            ];
        }

        if (empty($rows)) {
            throw new \Exception('هیچ داده یا رکوردی برای صدور سند تجمیعی وجود ندارد.');
        }

        $document = $this->accountingEngine->recordMultiLineDocument($rows, $desc, null, $docDateFormatted);

        $snapshotPayload = [
            'user_count' => $pendingUsers->count(),
            'user_income' => $totalUserIncome,
            'panel_count' => $pendingPanels->count(),
            'panel_cost' => $totalPanelCost,
            'net_profit' => $netDiff,
            'document_number' => $document->document_number,
        ];
        if ($wallet) {
            $snapshotPayload['wallet_id'] = $wallet->id;
            $snapshotPayload['wallet_holder'] = $walletHolderInfo;
            $snapshotPayload['wallet_balance'] = (float) $wallet->balance;
        }

        DB::table('accounting_source_documents')->insert([
            'document_id' => $document->id,
            'sourceable_type' => SmsResellerSetting::class,
            'sourceable_id' => 1,
            'module' => 'smsreseller',
            'event_type' => 'cumulative_reconciliation',
            'snapshot' => json_encode($snapshotPayload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($pendingUsers as $uPay) {
            DB::table('accounting_source_documents')->insert([
                'document_id' => $document->id,
                'sourceable_type' => SmsResellerUserPayment::class,
                'sourceable_id' => $uPay->id,
                'module' => 'smsreseller',
                'event_type' => 'user_income_payment',
                'snapshot' => json_encode([
                    'part_of_cumulative' => true,
                    'document_number' => $document->document_number,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($pendingPanels as $pPay) {
            DB::table('accounting_source_documents')->insert([
                'document_id' => $document->id,
                'sourceable_type' => SmsResellerPanelPayment::class,
                'sourceable_id' => $pPay->id,
                'module' => 'smsreseller',
                'event_type' => 'panel_expense_payment',
                'snapshot' => json_encode([
                    'part_of_cumulative' => true,
                    'document_number' => $document->document_number,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $document;
    }
}
