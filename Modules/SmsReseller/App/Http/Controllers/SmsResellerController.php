<?php

namespace Modules\SmsReseller\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\FundAccount;
use Modules\Accounting\App\Services\AccountingEngine;
use Modules\Clients\Entities\Client;
use Modules\SmsReseller\App\Models\SmsResellerDailySummary;
use Modules\SmsReseller\App\Models\SmsResellerPanelPayment;
use Modules\SmsReseller\App\Models\SmsResellerSetting;
use Modules\SmsReseller\App\Models\SmsResellerUserCredit;
use Modules\SmsReseller\App\Models\SmsResellerUserPayment;
use Modules\SmsReseller\App\Http\Requests\RecordAccountingDocumentRequest;
use Modules\SmsReseller\App\Http\Requests\RecordCumulativeReconciliationRequest;
use Modules\SmsReseller\App\Http\Requests\RecordPanelExpenseDocumentRequest;
use Modules\SmsReseller\App\Http\Requests\RecordUserIncomeDocumentRequest;
use Modules\SmsReseller\App\Http\Requests\SaveMarginPricesRequest;
use Modules\SmsReseller\App\Http\Requests\SaveSettingsRequest;
use Modules\SmsReseller\App\Services\SmsResellerApiService;
use Modules\SmsReseller\App\Services\SmsResellerReconciliationService;
use Modules\SmsReseller\App\Services\SmsResellerSyncService;
use Modules\Wallet\App\Models\Wallet;
use Morilog\Jalali\Jalalian;
use Throwable;

class SmsResellerController extends Controller
{

    public function index(Request $request, SmsResellerSyncService $syncService)
    {
        if (SmsResellerUserPayment::count() === 0 && SmsResellerPanelPayment::count() === 0) {
            $syncService->syncAll();
        }

        $period = $request->get('period', 'all');
        $customFromDate = $request->get('from_date');
        $customToDate = $request->get('to_date');

        $query = SmsResellerDailySummary::query();

        $currentJalali = Jalalian::now();
        $todayJalali = $currentJalali->format('Y/m/d');

        if (!empty($customFromDate) || !empty($customToDate)) {
            $period = 'custom';
            if (!empty($customFromDate) && !empty($customToDate)) {
                $fromClean = explode(' ', trim($customFromDate))[0];
                $toClean = explode(' ', trim($customToDate))[0];
                $query->whereBetween('jalali_date', [$fromClean, $toClean]);
            } elseif (!empty($customFromDate)) {
                $fromClean = explode(' ', trim($customFromDate))[0];
                $query->where('jalali_date', '>=', $fromClean);
            } else {
                $toClean = explode(' ', trim($customToDate))[0];
                $query->where('jalali_date', '<=', $toClean);
            }
        } elseif ($period === 'day') {
            $query->where('jalali_date', $todayJalali);
        } elseif ($period === 'week') {
            $weekStart = $currentJalali->subDays(7)->format('Y/m/d');
            $query->where('jalali_date', '>=', $weekStart);
        } elseif ($period === 'month') {
            $currentYm = $currentJalali->format('Y/m');
            $query->where('jalali_ym', $currentYm);
        } elseif ($period === 'year') {
            $currentYear = $currentJalali->format('Y');
            $query->where('jalali_year', $currentYear);
        }

        $summaryStats = (clone $query)->selectRaw('
            COALESCE(SUM(user_income_amount), 0) as total_user_amount,
            COALESCE(SUM(user_income_tax), 0) as total_user_tax,
            COALESCE(SUM(user_income_total), 0) as total_user_total,
            COALESCE(SUM(user_payments_count), 0) as total_user_payments_count,
            COALESCE(SUM(panel_charge_amount), 0) as total_panel_amount,
            COALESCE(SUM(panel_charge_tax), 0) as total_panel_tax,
            COALESCE(SUM(panel_charge_total), 0) as total_panel_total,
            COALESCE(SUM(panel_charges_count), 0) as total_panel_charges_count,
            COALESCE(SUM(user_credit_tx_count), 0) as total_credit_tx_count,
            COALESCE(SUM(user_credit_increase_sum), 0) as total_credit_increase_sum,
            COALESCE(SUM(user_credit_decrease_sum), 0) as total_credit_decrease_sum,
            COALESCE(SUM(net_profit), 0) as total_profit
        ')->first();

        $monthNames = [
            '01' => 'فروردین', '02' => 'اردیبهشت', '03' => 'خرداد',
            '04' => 'تیر', '05' => 'مرداد', '06' => 'شهریور',
            '07' => 'مهر', '08' => 'آبان', '09' => 'آذر',
            '10' => 'دی', '11' => 'بهمن', '12' => 'اسفند'
        ];

        $currentYm = $currentJalali->format('Y/m');

        $monthlyReports = SmsResellerDailySummary::select('jalali_ym')
            ->selectRaw('
                SUM(user_income_amount) as user_amount,
                SUM(user_income_tax) as user_tax,
                SUM(user_income_total) as user_total,
                SUM(user_payments_count) as user_count,
                SUM(panel_charge_amount) as panel_amount,
                SUM(panel_charge_tax) as panel_tax,
                SUM(panel_charge_total) as panel_total,
                SUM(panel_charges_count) as panel_count,
                SUM(net_profit) as net_profit,
                SUM(user_credit_tx_count) as credit_tx_count
            ')
            ->groupBy('jalali_ym')
            ->orderBy('jalali_ym', 'desc')
            ->paginate(12)
            ->through(function ($row) use ($monthNames, $currentYm) {
                $ym = $row->jalali_ym;
                $parts = explode('/', $ym);
                $year = $parts[0] ?? '';
                $monthNum = $parts[1] ?? '';
                $monthTitle = ($monthNames[$monthNum] ?? $monthNum) . ' ' . $year;

                return [
                    'month_key' => $ym,
                    'month_title' => $monthTitle,
                    'is_current_month' => ($ym === $currentYm),
                    'user_amount' => (float)$row->user_amount,
                    'user_tax' => (float)$row->user_tax,
                    'user_total' => (float)$row->user_total,
                    'user_count' => (int)$row->user_count,
                    'panel_amount' => (float)$row->panel_amount,
                    'panel_tax' => (float)$row->panel_tax,
                    'panel_total' => (float)$row->panel_total,
                    'panel_count' => (int)$row->panel_count,
                    'credit_tx_count' => (int)$row->credit_tx_count,
                    'net_profit' => (float)$row->net_profit,
                ];
            });

        $lastSync = SmsResellerUserPayment::latest('updated_at')->value('updated_at');

        return view('smsreseller::user.dashboard.index', compact(
            'period',
            'customFromDate',
            'customToDate',
            'summaryStats',
            'monthlyReports',
            'lastSync'
        ));
    }

    public function syncData(SmsResellerSyncService $syncService)
    {
        try {
            $res = $syncService->syncAll();
            return redirect()->route('user.smsreseller.index')
                ->with('success', "همگام‌سازی با موفقیت انجام شد ({$res['user_payments']} پرداخت کاربر و {$res['panel_payments']} شارژ پنل بروزرسانی گردید).");
        } catch (Throwable $e) {
            return back()->with('error', 'خطا در همگام‌سازی داده‌ها: ' . $e->getMessage());
        }
    }

    public function recordAccountingDocument(RecordAccountingDocumentRequest $request, AccountingEngine $accountingEngine)
    {
        $validated = $request->validated();
        $type = $validated['type'] ?? (!empty($validated['expense_category_id']) ? 'expense' : 'income');

        if ($type === 'expense') {
            return $this->processPanelExpenseDocument($validated, $accountingEngine);
        }

        return $this->processUserIncomeDocument($validated, $accountingEngine);
    }

    public function recordPanelExpenseDocument(RecordPanelExpenseDocumentRequest $request, AccountingEngine $accountingEngine)
    {
        return $this->processPanelExpenseDocument($request->validated(), $accountingEngine);
    }

    protected function processPanelExpenseDocument(array $data, AccountingEngine $accountingEngine)
    {
        $amount = (float) ($data['amount'] ?? 0);
        $tax = (float) ($data['tax'] ?? 0);
        $totalAmount = (float) ($data['total_amount'] ?? ($amount + $tax));

        $expenseCatId = $data['expense_category_id'] ?? null;
        $taxCatId = ($data['tax_category_id'] ?? null) ?: Category::where('title', 'like', '%مالیات%')->where('type', 'liability')->first()?->id;
        $fundAccountId = $data['fund_account_id'] ?? null;
        $fundAccount = FundAccount::findOrFail($fundAccountId);

        $docDate = now()->format('Y-m-d');
        if (!empty($data['document_date'])) {
            try {
                $rawDate = trim($data['document_date']);
                $datePart = explode(' ', $rawDate)[0];
                if (str_contains($datePart, '/')) {
                    $docDate = Jalalian::fromFormat('Y/m/d', $datePart)->toCarbon()->format('Y-m-d');
                } else {
                    $docDate = $datePart;
                }
            } catch (Throwable $e) {}
        }

        $description = (!empty($data['description'])) ? $data['description'] : "سند مرکب هزینه شارژ پنل پیامک به مبلغ " . number_format($totalAmount) . " ریال";

        $rows = [];

        $rows[] = [
            'category_id' => $expenseCatId,
            'fund_account_id' => null,
            'debit' => $amount,
            'credit' => 0,
            'description' => $description . " - هزینه شارژ پنل",
        ];

        if ($tax > 0 && $taxCatId) {
            $rows[] = [
                'category_id' => $taxCatId,
                'fund_account_id' => null,
                'debit' => $tax,
                'credit' => 0,
                'description' => $description . " - مالیات بر ارزش افزوده",
            ];
        }

        $walletId = !empty($data['wallet_id']) ? (int) $data['wallet_id'] : null;
        $wallet = ($walletId && class_exists(Wallet::class))
            ? Wallet::with('holder')->find($walletId)
            : null;
        $walletHolderInfo = '';
        if ($wallet && $wallet->holder) {
            $holder = $wallet->holder;
            $holderName = $holder->name ?? ($holder->full_name ?? ($holder->username ?? 'کاربر'));
            $holderPhone = $holder->phone ?? ($holder->mobile ?? '');
            $walletHolderInfo = $holderName . ($holderPhone ? " ({$holderPhone})" : '');
        }

        $fundTargetDesc = $walletHolderInfo
            ? "پرداخت از کیف پول کاربر {$walletHolderInfo}"
            : "پرداخت از حساب {$fundAccount->name}";

        $fundCatId = $fundAccount->category_id ?: Category::where('type', 'asset')->first()->id;
        $rows[] = [
            'category_id' => $fundCatId,
            'fund_account_id' => $fundAccount->id,
            'debit' => 0,
            'credit' => $totalAmount,
            'description' => $description . " - " . $fundTargetDesc,
        ];

        try {
            $document = $accountingEngine->recordMultiLineDocument($rows, $description, null, $docDate);

            $paymentId = $data['payment_id'] ?? null;
            if ($paymentId) {
                $snapshotPayload = [
                    'amount' => $amount,
                    'tax' => $tax,
                    'total_amount' => $totalAmount,
                    'document_number' => $document->document_number,
                ];
                if ($wallet) {
                    $snapshotPayload['wallet_id'] = $wallet->id;
                    $snapshotPayload['wallet_holder'] = $walletHolderInfo;
                    $snapshotPayload['wallet_balance_at_record'] = (float) $wallet->balance;
                }

                DB::table('accounting_source_documents')->insert([
                    'document_id' => $document->id,
                    'sourceable_type' => SmsResellerPanelPayment::class,
                    'sourceable_id' => $paymentId,
                    'module' => 'smsreseller',
                    'event_type' => 'panel_expense_payment',
                    'snapshot' => json_encode($snapshotPayload),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return redirect()->back()
                ->with('success', "سند مرکب هزینه شارژ پنل با موفقیت در حسابداری ثبت شد. (شماره سند: {$document->document_number})");
        } catch (Throwable $e) {
            return back()->with('error', 'خطا در ثبت سند هزینه: ' . $e->getMessage());
        }
    }


    public function recordUserIncomeDocument(RecordUserIncomeDocumentRequest $request, AccountingEngine $accountingEngine)
    {
        return $this->processUserIncomeDocument($request->validated(), $accountingEngine);
    }

    protected function processUserIncomeDocument(array $data, AccountingEngine $accountingEngine)
    {
        $amount = (float) ($data['amount'] ?? 0);
        $tax = (float) ($data['tax'] ?? 0);
        $totalAmount = (float) ($data['total_amount'] ?? ($amount + $tax));

        $incomeCatId = $data['income_category_id'] ?? null;
        $taxCatId = ($data['tax_category_id'] ?? null) ?: Category::where('title', 'like', '%مالیات%')->where('type', 'liability')->first()?->id;
        $fundAccountId = $data['fund_account_id'] ?? null;
        $fundAccount = FundAccount::findOrFail($fundAccountId);

        $docDate = now()->format('Y-m-d');
        if (!empty($data['document_date'])) {
            try {
                $rawDate = trim($data['document_date']);
                $datePart = explode(' ', $rawDate)[0];
                if (str_contains($datePart, '/')) {
                    $docDate = Jalalian::fromFormat('Y/m/d', $datePart)->toCarbon()->format('Y-m-d');
                } else {
                    $docDate = $datePart;
                }
            } catch (Throwable $e) {}
        }

        $description = (!empty($data['description'])) ? $data['description'] : "سند درآمد حاصل از شارژ پیامک به مبلغ " . number_format($totalAmount) . " ریال";

        $walletId = !empty($data['wallet_id']) ? (int) $data['wallet_id'] : null;
        $wallet = ($walletId && class_exists(Wallet::class))
            ? Wallet::with('holder')->find($walletId)
            : null;
        $walletHolderInfo = '';
        if ($wallet && $wallet->holder) {
            $holder = $wallet->holder;
            $holderName = $holder->name ?? ($holder->full_name ?? ($holder->username ?? 'کاربر'));
            $holderPhone = $holder->phone ?? ($holder->mobile ?? '');
            $walletHolderInfo = $holderName . ($holderPhone ? " ({$holderPhone})" : '');
        }

        $fundTargetDesc = $walletHolderInfo
            ? "واریز به کیف پول کاربر {$walletHolderInfo}"
            : "واریز به حساب {$fundAccount->name}";

        $rows = [];

        $fundCatId = $fundAccount->category_id ?: Category::where('type', 'asset')->first()->id;
        $rows[] = [
            'category_id' => $fundCatId,
            'fund_account_id' => $fundAccount->id,
            'debit' => $totalAmount,
            'credit' => 0,
            'description' => $description . " - " . $fundTargetDesc,
        ];

        $rows[] = [
            'category_id' => $incomeCatId,
            'fund_account_id' => null,
            'debit' => 0,
            'credit' => $amount,
            'description' => $description . " - درآمد فروش پیامک",
        ];

        if ($tax > 0 && $taxCatId) {
            $rows[] = [
                'category_id' => $taxCatId,
                'fund_account_id' => null,
                'debit' => 0,
                'credit' => $tax,
                'description' => $description . " - مالیات بر ارزش افزوده",
            ];
        }

        try {
            $document = $accountingEngine->recordMultiLineDocument($rows, $description, null, $docDate);

            $paymentId = $data['payment_id'] ?? null;
            if ($paymentId) {
                $snapshotPayload = [
                    'amount' => $amount,
                    'tax' => $tax,
                    'total_amount' => $totalAmount,
                    'document_number' => $document->document_number,
                ];
                if ($wallet) {
                    $snapshotPayload['wallet_id'] = $wallet->id;
                    $snapshotPayload['wallet_holder'] = $walletHolderInfo;
                    $snapshotPayload['wallet_balance_at_record'] = (float) $wallet->balance;
                }

                DB::table('accounting_source_documents')->insert([
                    'document_id' => $document->id,
                    'sourceable_type' => SmsResellerUserPayment::class,
                    'sourceable_id' => $paymentId,
                    'module' => 'smsreseller',
                    'event_type' => 'user_income_payment',
                    'snapshot' => json_encode($snapshotPayload),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return redirect()->back()
                ->with('success', "سند درآمد با موفقیت در حسابداری ثبت شد. (شماره سند: {$document->document_number})");
        } catch (Throwable $e) {
            return back()->with('error', 'خطا در ثبت سند درآمد: ' . $e->getMessage());
        }
    }


    public function userCredits(Request $request)
    {
        $page = max((int) $request->get('page', 1), 1);
        $size = max((int) $request->get('size', 15), 1);

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $username = $request->get('username');
        $type = $request->get('type');
        $increase = $request->get('increase');

        $query = SmsResellerUserCredit::query();

        if (!empty($username)) {
            $query->where('user_name', 'like', "%{$username}%");
        }

        if (!empty($fromDate)) {
            $fromClean = explode(' ', trim($fromDate))[0];
            $query->where('jalali_date', '>=', $fromClean);
        }

        if (!empty($toDate)) {
            $toClean = explode(' ', trim($toDate))[0] . ' 23:59:59';
            $query->where('jalali_date', '<=', $toClean);
        }

        if (!empty($type) && $type !== 'all') {
            $query->where('type', $type);
        }

        if (!empty($increase) && $increase !== 'all') {
            $isIncrease = ($increase === '1' || $increase === 'true' || $increase === 'افزایش');
            $query->where('increase', $isIncrease);
        }

        $totalIncreaseAmount = (float) (clone $query)->where('increase', true)->sum('amount');
        $totalDecreaseAmount = (float) (clone $query)->where('increase', false)->sum('amount');
        $netAmount = $totalIncreaseAmount - $totalDecreaseAmount;
        $transactions = $query->orderBy('jalali_date', 'desc')->orderBy('id', 'desc')->paginate($size);
        $availableTypes = SmsResellerUserCredit::distinct()->pluck('type')->filter()->values()->all();

        return view('smsreseller::user.credits.index', compact(
            'transactions',
            'availableTypes',
            'totalIncreaseAmount',
            'totalDecreaseAmount',
            'netAmount'
        ));
    }

    public function userPayments(Request $request)
    {
        $page = max((int) $request->get('page', 1), 1);
        $size = max((int) $request->get('size', 15), 1);

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $username = $request->get('username');
        $state = $request->get('state', 'واریز شده');
        $type = $request->get('type');

        $query = SmsResellerUserPayment::query();

        if (!empty($username)) {
            $query->where('user_name', 'like', "%{$username}%");
        }

        if (!empty($fromDate)) {
            $fromClean = explode(' ', trim($fromDate))[0];
            $query->where('jalali_date', '>=', $fromClean);
        }

        if (!empty($toDate)) {
            $toClean = explode(' ', trim($toDate))[0] . ' 23:59:59';
            $query->where('jalali_date', '<=', $toClean);
        }

        if (!empty($state) && $state !== 'all') {
            $query->where('state', $state);
        }

        if (!empty($type) && $type !== 'all') {
            $query->where('type', $type);
        }

        $depositedQuery = (clone $query)->where('state', 'واریز شده');
        $sumAmount = (float) (clone $depositedQuery)->sum('amount');
        $sumTax = (float) (clone $depositedQuery)->sum('tax');
        $depositedCount = (clone $depositedQuery)->count();
        $payments = $query->orderBy('jalali_date', 'desc')->orderBy('id', 'desc')->paginate($size);
        $availableStates = SmsResellerUserPayment::distinct()->pluck('state')->filter()->values()->all();
        $availableTypes = SmsResellerUserPayment::distinct()->pluck('type')->filter()->values()->all();
        $paymentIds = $payments->pluck('id')->all();
        $sourceDocs = DB::table('accounting_source_documents')
            ->where('sourceable_type', SmsResellerUserPayment::class)
            ->whereIn('sourceable_id', $paymentIds)
            ->get()
            ->keyBy('sourceable_id');

        $setting = SmsResellerSetting::first();
        $incomeCategories = Category::where('type', 'income')->where('status', true)->get();
        $liabilityCategories = Category::where('type', 'liability')->where('status', true)->get();
        $fundAccounts = FundAccount::where('status', true)->get();

        $defaultIncomeCat = $setting?->defaultIncomeCategory ?: Category::where('title', 'like', '%فروش%')->orWhere('title', 'like', '%خدمات%')->where('type', 'income')->first();
        $defaultTaxCat = $setting?->defaultTaxCategory ?: Category::where('title', 'like', '%مالیات%')->where('type', 'liability')->first();
        $defaultFundAccount = $setting?->defaultFundAccount ?: FundAccount::where('name', 'like', '%زرین%')->orWhere('name', 'like', '%بانک%')->orWhere('name', 'like', '%کیف پول%')->first();
        $userWalletsList = $this->getUserWalletsList();

        return view('smsreseller::user.payments.index', compact(
            'payments',
            'availableStates',
            'availableTypes',
            'sumAmount',
            'sumTax',
            'depositedCount',
            'sourceDocs',
            'incomeCategories',
            'liabilityCategories',
            'fundAccounts',
            'defaultIncomeCat',
            'defaultTaxCat',
            'defaultFundAccount',
            'userWalletsList'
        ));
    }

    public function panelPayments(Request $request)
    {
        $page = max((int) $request->get('page', 1), 1);
        $size = max((int) $request->get('size', 15), 1);

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $state = $request->get('state', 'واریز شده');
        $type = $request->get('type');

        $query = SmsResellerPanelPayment::query();

        if (!empty($fromDate)) {
            $fromClean = explode(' ', trim($fromDate))[0];
            $query->where('jalali_date', '>=', $fromClean);
        }

        if (!empty($toDate)) {
            $toClean = explode(' ', trim($toDate))[0] . ' 23:59:59';
            $query->where('jalali_date', '<=', $toClean);
        }

        if (!empty($state) && $state !== 'all') {
            $query->where('state', $state);
        }

        if (!empty($type) && $type !== 'all') {
            $query->where('type', $type);
        }

        $depositedQuery = (clone $query)->where('state', 'واریز شده');
        $sumAmount = (float) (clone $depositedQuery)->sum('amount');
        $sumTax = (float) (clone $depositedQuery)->sum('tax');
        $depositedCount = (clone $depositedQuery)->count();
        $payments = $query->orderBy('jalali_date', 'desc')->orderBy('id', 'desc')->paginate($size);

        $availableStates = SmsResellerPanelPayment::distinct()->pluck('state')->filter()->values()->all();
        $availableTypes = SmsResellerPanelPayment::distinct()->pluck('type')->filter()->values()->all();
        $paymentIds = $payments->pluck('id')->all();
        $sourceDocs = DB::table('accounting_source_documents')
            ->where('sourceable_type', SmsResellerPanelPayment::class)
            ->whereIn('sourceable_id', $paymentIds)
            ->get()
            ->keyBy('sourceable_id');

        $setting = SmsResellerSetting::first();
        $expenseCategories = Category::where('type', 'expense')->where('status', true)->get();
        $liabilityCategories = Category::where('type', 'liability')->where('status', true)->get();
        $fundAccounts = FundAccount::where('status', true)->get();

        $defaultExpenseCat = $setting?->defaultExpenseCategory ?: Category::where('title', 'like', '%عمومی%')->orWhere('title', 'like', '%اداری%')->where('type', 'expense')->first();
        $defaultTaxCat = $setting?->defaultTaxCategory ?: Category::where('title', 'like', '%مالیات%')->where('type', 'liability')->first();
        $defaultFundAccount = $setting?->defaultFundAccount ?: FundAccount::where('name', 'like', '%بانک%')->orWhere('name', 'like', '%صندوق%')->first();
        $userWalletsList = $this->getUserWalletsList();

        return view('smsreseller::user.panel_payments.index', compact(
            'payments',
            'availableStates',
            'availableTypes',
            'sumAmount',
            'sumTax',
            'depositedCount',
            'sourceDocs',
            'expenseCategories',
            'liabilityCategories',
            'fundAccounts',
            'defaultExpenseCat',
            'defaultTaxCat',
            'defaultFundAccount',
            'userWalletsList'
        ));
    }

    public function reconciliation(SmsResellerReconciliationService $reconciliationService)
    {
        $analysis = $reconciliationService->analyzeReconciliation();
        $setting = SmsResellerSetting::first();

        $incomeCategories = Category::where('type', 'income')->where('status', true)->get();
        $expenseCategories = Category::where('type', 'expense')->where('status', true)->get();
        $liabilityCategories = Category::where('type', 'liability')->where('status', true)->get();
        $fundAccounts = FundAccount::where('status', true)->get();

        $defaultIncomeCat = $setting?->defaultIncomeCategory ?: Category::where('title', 'like', '%فروش%')->orWhere('title', 'like', '%خدمات%')->where('type', 'income')->first();
        $defaultExpenseCat = $setting?->defaultExpenseCategory ?: Category::where('title', 'like', '%عمومی%')->orWhere('title', 'like', '%اداری%')->where('type', 'expense')->first();
        $defaultTaxCat = $setting?->defaultTaxCategory ?: Category::where('title', 'like', '%مالیات%')->where('type', 'liability')->first();
        $defaultFundAccount = $setting?->defaultFundAccount ?: FundAccount::where('name', 'like', '%بانک%')->orWhere('name', 'like', '%کیف پول%')->first();
        $userWalletsList = $this->getUserWalletsList();

        return view('smsreseller::user.reconciliation.index', compact(
            'analysis',
            'setting',
            'incomeCategories',
            'expenseCategories',
            'liabilityCategories',
            'fundAccounts',
            'defaultIncomeCat',
            'defaultExpenseCat',
            'defaultTaxCat',
            'defaultFundAccount',
            'userWalletsList'
        ));
    }


    public function recordCumulativeReconciliation(RecordCumulativeReconciliationRequest $request, SmsResellerReconciliationService $reconciliationService)
    {
        $validated = $request->validated();

        try {
            $document = $reconciliationService->recordCumulativeReconciliation(
                (int) $validated['income_category_id'],
                (int) $validated['expense_category_id'],
                !empty($validated['tax_category_id']) ? (int) $validated['tax_category_id'] : null,
                (int) $validated['fund_account_id'],
                $validated['document_date'] ?? null,
                $validated['description'] ?? null,
                !empty($validated['wallet_id']) ? (int) $validated['wallet_id'] : null
            );

            return redirect()->route('user.smsreseller.reconciliation')
                ->with('success', "سند تجمیعی رفع مغایرت با موفقیت صادر شد و تمام سوابق با حسابداری هماهنگ گردیدند. (شماره سند: {$document->document_number})");
        } catch (Throwable $e) {
            return back()->with('error', 'خطا در صدور سند تجمیعی: ' . $e->getMessage());
        }
    }


    public function userProfitability(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $crmStatus = $request->get('crm_status', 'all');
        $sortBy = $request->get('sort_by', 'income_desc');
        $paymentsQuery = SmsResellerUserPayment::where('state', 'واریز شده')
            ->select('user_name')
            ->selectRaw('
                COUNT(*) as payments_count,
                SUM(amount) as sum_amount,
                SUM(tax) as sum_tax,
                SUM(total_amount) as sum_total_amount,
                MAX(jalali_date) as last_payment_date
            ')
            ->groupBy('user_name');

        $userPayments = $paymentsQuery->get()->keyBy('user_name');
        $usageQuery = SmsResellerUserCredit::select('user_name')
            ->selectRaw('
                SUM(CASE WHEN increase = 0 THEN amount ELSE 0 END) as gross_sends,
                SUM(CASE WHEN increase = 1 AND (type LIKE "%برگشت%") THEN amount ELSE 0 END) as total_refunds,
                SUM(CASE WHEN increase = 1 AND (type LIKE "%اصلاحیه%" OR type LIKE "%انتقال%") THEN amount ELSE 0 END) as manual_gifts,
                COUNT(CASE WHEN increase = 0 THEN 1 END) as send_count,
                MAX(jalali_date) as last_usage_date
            ')
            ->groupBy('user_name');

        $userUsage = $usageQuery->get()->keyBy('user_name');

        $allUsernames = $userPayments->keys()->merge($userUsage->keys())->unique()->values();

        $cleanKeys = [];
        foreach ($allUsernames as $uName) {
            $digits = preg_replace('/\D/', '', (string)$uName);
            if ($digits) {
                $cleanKeys[] = $digits;
                $cleanKeys[] = ltrim($digits, '0');
                $cleanKeys[] = '0' . ltrim($digits, '0');
            }
        }
        $cleanKeys = array_values(array_unique(array_filter($cleanKeys)));

        $clients = Client::where(function ($q) use ($cleanKeys, $allUsernames) {
            $q->whereIn('phone', $cleanKeys)
              ->orWhereIn('username', $cleanKeys)
              ->orWhereIn('phone', $allUsernames)
              ->orWhereIn('username', $allUsernames);
        })->get();

        $clientMap = [];
        foreach ($clients as $client) {
            if ($client->phone) {
                $cDigits = preg_replace('/\D/', '', $client->phone);
                $clientMap[$cDigits] = $client;
                $clientMap[ltrim($cDigits, '0')] = $client;
                $clientMap['0' . ltrim($cDigits, '0')] = $client;
                $clientMap[$client->phone] = $client;
            }
            if ($client->username) {
                $uDigits = preg_replace('/\D/', '', $client->username);
                $clientMap[$uDigits] = $client;
                $clientMap[ltrim($uDigits, '0')] = $client;
                $clientMap['0' . ltrim($uDigits, '0')] = $client;
                $clientMap[$client->username] = $client;
            }
        }

        $totalSystemGrossIncome = (float) SmsResellerUserPayment::where('state', 'واریز شده')->sum('total_amount');
        $totalSystemPanelCost = (float) SmsResellerPanelPayment::where('state', 'واریز شده')->sum('total_amount');
        $historicalProfitRatio = $totalSystemGrossIncome > 0
            ? (($totalSystemGrossIncome - $totalSystemPanelCost) / $totalSystemGrossIncome)
            : 0.3374;

        $totalSystemIncome = (float) $userPayments->sum('sum_total_amount');
        $userList = collect();

        foreach ($allUsernames as $uName) {
            $p = $userPayments->get($uName);
            $u = $userUsage->get($uName);

            $digits = preg_replace('/[^\d]/', '', (string)$uName);
            $matchedClient = $clientMap[$digits] ?? ($clientMap[ltrim($digits, '0')] ?? ($clientMap['0' . ltrim($digits, '0')] ?? ($clientMap[$uName] ?? null)));

            $totalIncome = $p ? (float) $p->sum_total_amount : 0.0;
            $amount = $p ? (float) $p->sum_amount : 0.0;
            $tax = $p ? (float) $p->sum_tax : 0.0;
            $paymentsCount = $p ? (int) $p->payments_count : 0;
            $lastPaymentDate = $p ? $p->last_payment_date : null;
            $netProfit = $totalIncome * $historicalProfitRatio;
            $estimatedCost = $totalIncome - $netProfit;
            $netMarginPercent = $totalIncome > 0 ? ($netProfit / $totalIncome) * 100 : 0;

            $grossSends = $u ? (float) $u->gross_sends : 0.0;
            $totalRefunds = $u ? (float) $u->total_refunds : 0.0;
            $manualGifts = $u ? (float) $u->manual_gifts : 0.0;
            $netConsumed = $grossSends - $totalRefunds;
            $sendCount = $u ? (int) $u->send_count : 0;
            $lastUsageDate = $u ? $u->last_usage_date : null;

            $sharePercentage = $totalSystemIncome > 0 ? ($totalIncome / $totalSystemIncome) * 100 : 0;

            if ($search !== '') {
                $matchSearch = str_contains($uName, $search) ||
                    ($matchedClient && (str_contains($matchedClient->full_name, $search) || str_contains($matchedClient->phone, $search)));
                if (!$matchSearch) {
                    continue;
                }
            }

            if ($crmStatus === 'matched' && !$matchedClient) {
                continue;
            } elseif ($crmStatus === 'unmatched' && $matchedClient) {
                continue;
            }

            $userList->push([
                'user_name' => $uName,
                'client' => $matchedClient,
                'total_income' => $totalIncome,
                'amount' => $amount,
                'tax' => $tax,
                'net_profit' => $netProfit,
                'estimated_cost' => $estimatedCost,
                'net_margin_percent' => $netMarginPercent,
                'payments_count' => $paymentsCount,
                'gross_sends' => $grossSends,
                'total_refunds' => $totalRefunds,
                'manual_gifts' => $manualGifts,
                'net_consumed' => $netConsumed,
                'send_count' => $sendCount,
                'last_payment_date' => $lastPaymentDate,
                'last_usage_date' => $lastUsageDate,
                'share_percentage' => $sharePercentage,
            ]);
        }

        if ($sortBy === 'payments_desc') {
            $userList = $userList->sortByDesc('payments_count')->values();
        } elseif ($sortBy === 'usage_desc') {
            $userList = $userList->sortByDesc('consumed_amount')->values();
        } elseif ($sortBy === 'recent') {
            $userList = $userList->sortByDesc('last_payment_date')->values();
        } elseif ($sortBy === 'net_profit_desc') {
            $userList = $userList->sortByDesc('net_profit')->values();
        } else {
            $userList = $userList->sortByDesc('total_income')->values();
        }

        $topCustomer = $userList->first();
        $topCustomerName = '---';
        $topCustomerAmount = 0;
        $topCustomerNetProfit = 0;
        if ($topCustomer) {
            $topCustomerName = $topCustomer['client'] ? $topCustomer['client']->full_name . " ({$topCustomer['user_name']})" : $topCustomer['user_name'];
            $topCustomerAmount = $topCustomer['total_income'];
            $topCustomerNetProfit = $topCustomer['net_profit'];
        }

        $kpis = [
            'total_customers_count' => $userList->count(),
            'matched_crm_count' => $userList->whereNotNull('client')->count(),
            'total_income_sum' => (float) $userList->sum('total_income'),
            'total_net_profit_sum' => (float) $userList->sum('net_profit'),
            'total_estimated_cost_sum' => (float) $userList->sum('estimated_cost'),
            'overall_profit_ratio' => $historicalProfitRatio * 100,
            'total_payments_count' => (int) $userList->sum('payments_count'),
            'avg_customer_income' => $userList->count() > 0 ? (float) ($userList->sum('total_income') / $userList->count()) : 0,
            'avg_customer_profit' => $userList->count() > 0 ? (float) ($userList->sum('net_profit') / $userList->count()) : 0,
            'top_customer_name' => $topCustomerName,
            'top_customer_amount' => $topCustomerAmount,
            'top_customer_profit' => $topCustomerNetProfit,
        ];

        $page = max((int) $request->get('page', 1), 1);
        $perPage = 15;
        $paginatedItems = $userList->slice(($page - 1) * $perPage, $perPage)->values();

        $paginatedUsers = new LengthAwarePaginator(
            $paginatedItems,
            $userList->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('smsreseller::user.profitability.index', compact(
            'paginatedUsers',
            'kpis'
        ));
    }

    public function userProfitabilityShow(string $username, Request $request)
    {
        $username = urldecode($username);

        $cleanDigits = preg_replace('/[^\d]/', '', $username);
        $client = Client::where(function ($q) use ($username, $cleanDigits) {
            $q->where('phone', $username)
              ->orWhere('username', $username);
            if (!empty($cleanDigits)) {
                $q->orWhere('phone', $cleanDigits)
                  ->orWhere('phone', ltrim($cleanDigits, '0'))
                  ->orWhere('phone', '0' . ltrim($cleanDigits, '0'))
                  ->orWhere('username', $cleanDigits);
            }
        })->first();

        $paymentsQuery = SmsResellerUserPayment::where('user_name', $username);
        $depositedQuery = (clone $paymentsQuery)->where('state', 'واریز شده');

        $totalIncome = (float) (clone $depositedQuery)->sum('total_amount');
        $totalAmount = (float) (clone $depositedQuery)->sum('amount');
        $totalTax = (float) (clone $depositedQuery)->sum('tax');
        $paymentsCount = (clone $depositedQuery)->count();
        $allPaymentsCount = (clone $paymentsQuery)->count();
        $lastPayment = (clone $depositedQuery)->orderBy('jalali_date', 'desc')->first();
        $firstPayment = (clone $depositedQuery)->orderBy('jalali_date', 'asc')->first();

        $payments = (clone $paymentsQuery)->orderBy('jalali_date', 'desc')->orderBy('id', 'desc')->paginate(10, ['*'], 'payments_page');

        $creditsQuery = SmsResellerUserCredit::where('user_name', $username);

        $grossSends = (float) (clone $creditsQuery)->where('increase', false)->sum('amount');
        $sendCount = (clone $creditsQuery)->where('increase', false)->count();

        $totalRefunds = (float) (clone $creditsQuery)->where('increase', true)->where('type', 'like', '%برگشت%')->sum('amount');
        $refundCount = (clone $creditsQuery)->where('increase', true)->where('type', 'like', '%برگشت%')->count();

        $manualGifts = (float) (clone $creditsQuery)->where('increase', true)->where(function($q) {
            $q->where('type', 'like', '%اصلاحیه%')
              ->orWhere('type', 'like', '%انتقال%')
              ->orWhere('type', 'like', '%هدیه%');
        })->sum('amount');
        $giftCount = (clone $creditsQuery)->where('increase', true)->where(function($q) {
            $q->where('type', 'like', '%اصلاحیه%')
              ->orWhere('type', 'like', '%انتقال%')
              ->orWhere('type', 'like', '%هدیه%');
        })->count();

        $netConsumed = $grossSends - $totalRefunds;

        $latestCreditRecord = (clone $creditsQuery)->orderBy('id', 'desc')->first();
        $currentCredit = $latestCreditRecord ? (float) $latestCreditRecord->current_credit : 0.0;

        $credits = (clone $creditsQuery)->orderBy('jalali_date', 'desc')->orderBy('id', 'desc')->paginate(15, ['*'], 'credits_page');

        $totalSystemGrossIncome = (float) SmsResellerUserPayment::where('state', 'واریز شده')->sum('total_amount');
        $totalSystemPanelCost = (float) SmsResellerPanelPayment::where('state', 'واریز شده')->sum('total_amount');
        $historicalProfitRatio = $totalSystemGrossIncome > 0
            ? (($totalSystemGrossIncome - $totalSystemPanelCost) / $totalSystemGrossIncome)
            : 0.3374;

        $netProfit = $totalIncome * $historicalProfitRatio;
        $estimatedCost = $totalIncome - $netProfit;
        $netMarginPercent = $totalIncome > 0 ? ($netProfit / $totalIncome) * 100 : 0;

        $allRanks = SmsResellerUserPayment::where('state', 'واریز شده')
            ->select('user_name', DB::raw('SUM(total_amount) as sum_total'))
            ->groupBy('user_name')
            ->orderByDesc('sum_total')
            ->pluck('user_name')
            ->values()
            ->all();

        $userRankIndex = array_search($username, $allRanks);
        $userRank = $userRankIndex !== false ? $userRankIndex + 1 : null;
        $totalRankedUsers = count($allRanks);

        $paymentIds = $payments->pluck('id')->all();
        $sourceDocs = DB::table('accounting_source_documents')
            ->where('sourceable_type', SmsResellerUserPayment::class)
            ->whereIn('sourceable_id', $paymentIds)
            ->get()
            ->keyBy('sourceable_id');

        $setting = SmsResellerSetting::first();
        $incomeCategories = Category::where('type', 'income')->where('status', true)->get();
        $liabilityCategories = Category::where('type', 'liability')->where('status', true)->get();
        $fundAccounts = FundAccount::where('status', true)->get();

        $defaultIncomeCat = $setting?->defaultIncomeCategory ?: Category::where('title', 'like', '%فروش%')->orWhere('title', 'like', '%خدمات%')->where('type', 'income')->first();
        $defaultTaxCat = $setting?->defaultTaxCategory ?: Category::where('title', 'like', '%مالیات%')->where('type', 'liability')->first();
        $defaultFundAccount = $setting?->defaultFundAccount ?: FundAccount::where('name', 'like', '%بانک%')->orWhere('name', 'like', '%کیف پول%')->first();
        $userWalletsList = $this->getUserWalletsList();

        return view('smsreseller::user.profitability.show', compact(
            'username',
            'client',
            'totalIncome',
            'totalAmount',
            'totalTax',
            'paymentsCount',
            'allPaymentsCount',
            'lastPayment',
            'firstPayment',
            'grossSends',
            'sendCount',
            'totalRefunds',
            'refundCount',
            'manualGifts',
            'giftCount',
            'netConsumed',
            'currentCredit',
            'netProfit',
            'estimatedCost',
            'netMarginPercent',
            'userRank',
            'totalRankedUsers',
            'payments',
            'credits',
            'sourceDocs',
            'incomeCategories',
            'liabilityCategories',
            'fundAccounts',
            'defaultIncomeCat',
            'defaultTaxCat',
            'defaultFundAccount',
            'userWalletsList'
        ));
    }

    public function marginCalculator(SmsResellerApiService $apiService)
    {
        $setting = SmsResellerSetting::first();
        $storedSellingPrices = $setting?->operator_selling_prices ?? [];
        $creditInfo = $apiService->getCurrentCredit();
        $buyPrices = $creditInfo['result']['userSmsPrice'] ?? [];
        $onlineBalance = (float) ($creditInfo['result']['credit'] ?? 0);
        $smsCount = (int) ($creditInfo['result']['smsCount'] ?? 0);

        $opLabels = [
            'op_1000' => 'اپراتور ۱۰۰۰ (ثابت/پیشنهادی)',
            'op_2000' => 'اپراتور ۲۰۰۰',
            'op_3000' => 'اپراتور ۳۰۰۰ (معتبر شرکتی)',
            'op_50002' => 'اپراتور ۵۰۰۰۲',
            'op_Simcard' => 'سیم‌کارت (عبور از بلک‌لیست)',
            'op_Avanak' => 'آوانک (پیامک صوتی هوشمند)',
            'op_9000' => 'اپراتور ۹۰۰۰ (خدماتی)',
            'op_IdehPayam' => 'ایده پیام',
            'op_AryanTel' => 'آرین تل',
            'op_LBS' => 'پیامک زنده مکانی (LBS دکل مخابرات)',
            'op_Asanak' => 'آسانک',
            'op_Bale' => 'پیام‌رسان بله',
        ];

        if (empty($buyPrices)) {
            $buyPrices = [
                'op_1000' => 1350,
                'op_2000' => 1429,
                'op_3000' => 1429,
                'op_50002' => 1350,
                'op_Simcard' => 1573,
                'op_Avanak' => 2000,
                'op_9000' => 1573,
                'op_IdehPayam' => 1573,
                'op_AryanTel' => 1573,
                'op_LBS' => 9684,
                'op_Asanak' => 1573,
                'op_Bale' => 1573,
            ];
        }

        $margins = [];
        $totalMarginPercentSum = 0;
        $validOpCount = 0;
        $maxMargin = -999;
        $mostProfitableOpTitle = '---';

        foreach ($buyPrices as $opKey => $buyPrice) {
            if ($opKey === 'userId') continue;

            $buyPrice = (float) $buyPrice;
            $sellingPrice = isset($storedSellingPrices[$opKey]) && (float)$storedSellingPrices[$opKey] > 0
                ? (float) $storedSellingPrices[$opKey]
                : round($buyPrice * 1.25);

            $profitPerSms = $sellingPrice - $buyPrice;
            $marginPercent = $sellingPrice > 0 ? ($profitPerSms / $sellingPrice) * 100 : 0;

            $title = $opLabels[$opKey] ?? $opKey;

            $margins[$opKey] = [
                'key' => $opKey,
                'title' => $title,
                'buy_price' => $buyPrice,
                'selling_price' => $sellingPrice,
                'profit_per_sms' => $profitPerSms,
                'margin_percent' => $marginPercent,
            ];

            $totalMarginPercentSum += $marginPercent;
            $validOpCount++;

            if ($marginPercent > $maxMargin) {
                $maxMargin = $marginPercent;
                $mostProfitableOpTitle = $title;
            }
        }

        $kpis = [
            'avg_margin_percent' => $validOpCount > 0 ? ($totalMarginPercentSum / $validOpCount) : 0,
            'most_profitable_op_title' => $mostProfitableOpTitle,
            'most_profitable_op_margin' => $maxMargin > -999 ? $maxMargin : 0,
        ];

        return view('smsreseller::user.margins.index', compact(
            'margins',
            'kpis',
            'onlineBalance',
            'smsCount'
        ));
    }

    public function saveMarginPrices(SaveMarginPricesRequest $request)
    {
        $rawPrices = $request->input('selling_prices', []);
        $sellingPrices = [];

        foreach ($rawPrices as $opKey => $val) {
            $clean = preg_replace('/\D/', '', (string)$val);
            $sellingPrices[$opKey] = $clean !== '' ? (float)$clean : 0;
        }

        $setting = SmsResellerSetting::first() ?? new SmsResellerSetting();
        $setting->operator_selling_prices = $sellingPrices;
        $setting->save();

        return redirect()->route('user.smsreseller.margin_calculator')
            ->with('success', 'نرخ‌های فروش و حاشیه سود اپراتورها با موفقیت ذخیره و بروزرسانی شد.');
    }


    public function settings()
    {
        $setting = SmsResellerSetting::first() ?? new SmsResellerSetting();

        $incomeCategories = Category::where('type', 'income')->where('status', true)->get();
        $expenseCategories = Category::where('type', 'expense')->where('status', true)->get();
        $liabilityCategories = Category::where('type', 'liability')->where('status', true)->get();
        $fundAccounts = FundAccount::where('status', true)->get();

        $defaultIncomeCat = $setting->defaultIncomeCategory ?: Category::where('title', 'like', '%فروش%')->orWhere('title', 'like', '%خدمات%')->where('type', 'income')->first();
        $defaultExpenseCat = $setting->defaultExpenseCategory ?: Category::where('title', 'like', '%عمومی%')->orWhere('title', 'like', '%اداری%')->where('type', 'expense')->first();
        $defaultTaxCat = $setting->defaultTaxCategory ?: Category::where('title', 'like', '%مالیات%')->where('type', 'liability')->first();
        $defaultFundAccount = $setting->defaultFundAccount ?: FundAccount::where('name', 'like', '%بانک%')->first();
        $defaultWalletFundAccount = $setting->defaultWalletFundAccount ?: FundAccount::where('name', 'like', '%کیف پول%')->first();

        $lastSync = SmsResellerUserPayment::latest('updated_at')->value('updated_at');
        $totalPayments = SmsResellerUserPayment::count();
        $totalPanelCharges = SmsResellerPanelPayment::count();
        $totalSummaries = SmsResellerDailySummary::count();

        return view('smsreseller::user.settings.index', compact(
            'setting',
            'incomeCategories',
            'expenseCategories',
            'liabilityCategories',
            'fundAccounts',
            'defaultIncomeCat',
            'defaultExpenseCat',
            'defaultTaxCat',
            'defaultFundAccount',
            'defaultWalletFundAccount',
            'lastSync',
            'totalPayments',
            'totalPanelCharges',
            'totalSummaries'
        ));
    }

    public function saveSettings(SaveSettingsRequest $request)
    {
        $validated = $request->validated();

        $setting = SmsResellerSetting::first() ?? new SmsResellerSetting();
        $setting->api_key = $validated['api_key'];
        $setting->base_url = $validated['base_url'];
        $setting->default_income_category_id = $validated['default_income_category_id'] ?? null;
        $setting->default_expense_category_id = $validated['default_expense_category_id'] ?? null;
        $setting->default_tax_category_id = $validated['default_tax_category_id'] ?? null;
        $setting->default_fund_account_id = $validated['default_fund_account_id'] ?? null;
        $setting->default_wallet_fund_account_id = $validated['default_wallet_fund_account_id'] ?? null;
        $setting->save();

        return redirect()->route('user.smsreseller.settings')
            ->with('success', 'تنظیمات و سرفصل‌های پیش‌فرض با موفقیت ذخیره شد.');
    }

    /**
     * Get all active wallets with their holders formatted for dropdowns.
     */
    protected function getUserWalletsList(): array
    {
        if (!class_exists(Wallet::class)) {
            return [];
        }

        try {
            $wallets = Wallet::with('holder')
                ->where('is_active', true)
                ->get();

            return $wallets->map(function ($w) {
                $holder = $w->holder;
                $name = 'نامشخص';
                $phone = '';
                $typeLabel = 'کاربر';
                $typeBadge = 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
                $isClient = false;

                if ($holder) {
                    $name = $holder->name ?? ($holder->full_name ?? ($holder->username ?? 'کاربر'));
                    $phone = $holder->phone ?? ($holder->mobile ?? '');
                    if ($holder instanceof Client || str_contains(get_class($holder), 'Client')) {
                        $typeLabel = 'مشتری CRM';
                        $typeBadge = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50';
                        $isClient = true;
                    } else {
                        $typeLabel = 'کاربر سیستم';
                        $typeBadge = 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/50';
                    }
                }

                return [
                    'id' => (string) $w->id,
                    'name' => (string) $name,
                    'phone' => (string) $phone,
                    'holder_id' => $w->holder_id,
                    'holder_type' => (string) $w->holder_type,
                    'type_label' => $typeLabel,
                    'type_badge' => $typeBadge,
                    'is_client' => $isClient,
                    'balance' => (float) ($w->balance ?? 0),
                    'currency' => (string) ($w->currency ?? 'IRR'),
                ];
            })->values()->all();
        } catch (Throwable $e) {
            return [];
        }
    }
}
