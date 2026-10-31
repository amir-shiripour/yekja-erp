<?php

namespace Modules\ResellerHub\App\Models;

use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\ResellerHub\App\Helpers\JalaliDateHelper;
use Throwable;

/**
 * @property int $id
 * @property int $invoice_id
 * @property int|null $user_id
 * @property int $amount
 * @property string $method
 * @property string|null $gateway
 * @property Carbon|null $paid_at
 * @property string|null $transaction_id
 * @property string|null $notes
 * @property string $status
 * @property-read ResellerInvoice $invoice
 * @property-read User|null $user
 * @method static ResellerInvoicePayment create(array $attributes = [])
 * @method static Builder|ResellerInvoicePayment where(string|array|Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method static Builder|ResellerInvoicePayment forInvoiceAndTransaction(int $invoiceId, string $transactionId)
 */
class ResellerInvoicePayment extends Model
{

    public const STATUS_PAID = 'paid';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PENDING = 'pending';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELED = 'canceled';

    /** Statuses that actually represent collected money. */
    public const SETTLED_STATUSES = [self::STATUS_PAID, self::STATUS_COMPLETED];

    protected $table = 'reseller_invoice_payments';

    protected $fillable = [
        'invoice_id',
        'user_id',
        'amount',
        'method',
        'gateway',
        'paid_at',
        'transaction_id',
        'idempotency_key',
        'notes',
        'status',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'integer',
    ];

    public function setTransactionIdAttribute(?string $value): void
    {
        $this->attributes['transaction_id'] = ($value !== null && trim((string)$value) !== '') ? trim((string)$value) : null;
    }

    protected static function booted(): void
    {
        static::saving(static function (self $payment): void {
            if ($payment->transaction_id !== null && trim((string)$payment->transaction_id) === '') {
                $payment->transaction_id = null;
            }
        });
    }

    /**
     * Check if a gateway transaction reference is already settled on ANY other invoice.
     * Prevents cross-invoice replay attacks.
     */
    public static function isTransactionSettledElsewhere(string $gateway, string $transactionId, ?int $currentInvoiceId = null): bool
    {
        $cleanId = trim($transactionId);
        if ($cleanId === '') {
            return false;
        }

        $query = static::query()
            ->where('gateway', $gateway)
            ->where('transaction_id', $cleanId)
            ->whereIn('status', self::SETTLED_STATUSES);

        if ($currentInvoiceId !== null) {
            $query->where('invoice_id', '!=', $currentInvoiceId);
        }

        return $query->exists();
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ResellerInvoice::class, 'invoice_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to find a payment by transaction reference and invoice ID.
     *
     * @param Builder $query
     * @param int $invoiceId
     * @param string $transactionId
     * @return Builder
     */
    public function scopeForInvoiceAndTransaction(Builder $query, int $invoiceId, string $transactionId): Builder
    {
        return $query->where('invoice_id', $invoiceId)->where('transaction_id', $transactionId);
    }

    /**
     * Cache for payment settings to avoid repeated queries in the same request.
     */
    protected static ?array $cachedSettings = null;

    /**
     * Decode and format raw payment method/gateway strings to human-readable Persian names.
     */
    public static function formatMethodName(?string $method, ?string $gateway = null): string
    {
        if (empty($method) && empty($gateway)) {
            return '—';
        }

        $raw = trim((string)($method ?: $gateway));
        $lower = strtolower($raw);

        // Load and cache settings
        if (self::$cachedSettings === null) {
            try {
                self::$cachedSettings = Cache::remember('resellerhub_payment_methods_settings', 1800, function () {
                    return DB::table('settings')
                        ->whereIn('key', ['pos_devices', 'bank_transfer_accounts', 'installment_types'])
                        ->pluck('value', 'key')
                        ->toArray();
                });
            } catch (Throwable) {
                self::$cachedSettings = [];
            }
        }

        if (str_starts_with($lower, 'pos-') || str_starts_with($lower, 'pos_') || $lower === 'pos') {
            $posId = ($lower === 'pos') ? null : preg_replace('/^pos[-_]/i', '', $raw);
            if ($posId) {
                $posDevices = json_decode(self::$cachedSettings['pos_devices'] ?? '[]', true) ?: [];
                foreach ($posDevices as $device) {
                    if (isset($device['id']) && (string)$device['id'] === (string)$posId) {
                        return 'کارتخوان ' . ($device['name'] ?? '');
                    }
                }
            }
            return 'دستگاه کارتخوان (POS)';
        }

        if (str_starts_with($lower, 'transfer-') || str_starts_with($lower, 'transfer_') || $lower === 'transfer' || $lower === 'card_to_card') {
            $accId = ($lower === 'transfer' || $lower === 'card_to_card') ? null : preg_replace('/^transfer[-_]/i', '', $raw);
            if ($accId) {
                $bankAccounts = json_decode(self::$cachedSettings['bank_transfer_accounts'] ?? '[]', true) ?: [];
                foreach ($bankAccounts as $account) {
                    if (isset($account['id']) && (string)$account['id'] === (string)$accId) {
                        $bankName = $account['bank_name'] ?? ($account['name'] ?? '');
                        return 'کارت به کارت (' . ($bankName ?: 'بانک') . ')';
                    }
                }
            }
            return 'کارت به کارت / حواله بانکی';
        }

        if ($lower === 'wallet') {
            return 'کیف پول مشتری';
        }

        if (in_array($lower, ['online', 'gateway', 'zarinpal', 'zibal', 'behpardakht', 'payir', 'idpay', 'nextpay', 'asanpardakht', 'parsian', 'pasargad', 'sadad', 'mellat', 'saman', 'sep'])) {
            $names = [
                'zarinpal' => 'زرین‌پال',
                'zibal' => 'زیبال',
                'behpardakht' => 'به‌پرداخت ملت',
                'mellat' => 'به‌پرداخت ملت',
                'saman' => 'سامان کیش (سپ)',
                'sep' => 'سامان کیش (سپ)',
                'parsian' => 'پارسیان',
                'pasargad' => 'پاسارگاد',
                'sadad' => 'سداد ملی',
                'payir' => 'پی‌آی‌آر',
                'idpay' => 'آیدی‌پی',
                'nextpay' => 'نکست‌پی',
                'asanpardakht' => 'آسان‌پرداخت (آپ)',
                'online' => 'درگاه پرداخت آنلاین',
                'gateway' => 'درگاه پرداخت آنلاین',
            ];
            return 'درگاه پرداخت ' . ($names[$lower] ?? $raw);
        }

        if ($lower === 'cod') {
            return 'پرداخت در محل (COD)';
        }

        return $raw;
    }

    public function getJalaliPaidAtAttribute(): ?string
    {
        return JalaliDateHelper::formatDate($this->paid_at);
    }

    public function getMethodLabelAttribute(): string
    {
        return self::formatMethodName($this->method, $this->gateway);
    }
}
