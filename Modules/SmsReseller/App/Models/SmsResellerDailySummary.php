<?php

namespace Modules\SmsReseller\App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsResellerDailySummary extends Model
{
    protected $table = 'sms_reseller_daily_summaries';

    protected $fillable = [
        'jalali_date',
        'jalali_ym',
        'jalali_year',
        'gregorian_date',
        'user_income_amount',
        'user_income_tax',
        'user_income_total',
        'user_payments_count',
        'panel_charge_amount',
        'panel_charge_tax',
        'panel_charge_total',
        'panel_charges_count',
        'user_credit_tx_count',
        'user_credit_increase_sum',
        'user_credit_decrease_sum',
        'net_profit',
    ];

    protected $casts = [
        'gregorian_date' => 'date',
        'user_income_amount' => 'decimal:2',
        'user_income_tax' => 'decimal:2',
        'user_income_total' => 'decimal:2',
        'panel_charge_amount' => 'decimal:2',
        'panel_charge_tax' => 'decimal:2',
        'panel_charge_total' => 'decimal:2',
        'user_credit_increase_sum' => 'decimal:2',
        'user_credit_decrease_sum' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'user_payments_count' => 'integer',
        'panel_charges_count' => 'integer',
        'user_credit_tx_count' => 'integer',
    ];
}
