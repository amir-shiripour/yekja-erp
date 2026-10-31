<?php

namespace Modules\SmsReseller\App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsResellerPanelPayment extends Model
{
    protected $table = 'sms_reseller_panel_payments';

    protected $fillable = [
        'hash',
        'account_name',
        'amount',
        'tax',
        'total_amount',
        'state',
        'type',
        'jalali_date',
        'jalali_ym',
        'gregorian_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'gregorian_date' => 'date',
    ];
}
