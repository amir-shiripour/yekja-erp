<?php

namespace Modules\ResellerHub\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ResellerHub\App\Enums\InvoiceItemType;

/**
 * @property int $id
 * @property int $invoice_id
 * @property string $item_type
 * @property string|null $item_key
 * @property string $title
 * @property string|null $description
 * @property int $duration_months
 * @property int $quantity
 * @property int $unit_price
 * @property int $discount_amount
 * @property int $total_price
 * @property array|null $meta
 * @property-read ResellerInvoice $invoice
 * @property-read InvoiceItemType|null $type_enum
 * @property-read string $type_label
 * @property-read string $type_badge_class
 * @property-read string $icon_badge_class
 * @property-read string $row_bg_class
 * @method static ResellerInvoiceItem create(array $attributes = [])
 * @method static Builder|ResellerInvoiceItem where(string|array|\Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 */
class ResellerInvoiceItem extends Model
{
    public const TYPE_PACKAGE = InvoiceItemType::PACKAGE->value;
    public const TYPE_PACKAGE_UPGRADE = InvoiceItemType::PACKAGE_UPGRADE->value;
    public const TYPE_MODULE = InvoiceItemType::MODULE->value;
    public const TYPE_SETUP_FEE = InvoiceItemType::SETUP_FEE->value;
    public const TYPE_CUSTOM_DOMAIN = InvoiceItemType::CUSTOM_DOMAIN->value;
    public const TYPE_WHITE_LABEL = InvoiceItemType::WHITE_LABEL->value;
    public const TYPE_CUSTOM = InvoiceItemType::CUSTOM->value;

    protected $table = 'reseller_invoice_items';

    protected $fillable = [
        'invoice_id',
        'item_type',
        'item_key',
        'title',
        'description',
        'duration_months',
        'quantity',
        'unit_price',
        'discount_amount',
        'total_price',
        'meta',
    ];

    protected $casts = [
        'duration_months' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'discount_amount' => 'integer',
        'total_price' => 'integer',
        'meta' => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ResellerInvoice::class, 'invoice_id');
    }

    public function getTypeEnumAttribute(): ?InvoiceItemType
    {
        return InvoiceItemType::fromOrDefault($this->item_type);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type_enum?->label() ?? ($this->item_type ?: 'سفارشی');
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return $this->type_enum?->badgeClasses() ?? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
    }

    public function getIconBadgeClassAttribute(): string
    {
        return $this->type_enum?->iconBadgeClasses() ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300';
    }

    public function getRowBgClassAttribute(): string
    {
        return $this->type_enum?->rowBgClass() ?? '';
    }

    public function getDurationTextAttribute(): string
    {
        if ($this->duration_months > 0) {
            return reseller_duration_label($this->duration_months);
        }

        return reseller_fa_num($this->quantity ?: 1) . ' عدد';
    }
}
