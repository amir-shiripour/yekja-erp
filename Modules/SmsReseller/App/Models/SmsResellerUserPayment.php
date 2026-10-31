<?php

namespace Modules\SmsReseller\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Clients\Entities\Client;

class SmsResellerUserPayment extends Model
{
    protected $table = 'sms_reseller_user_payments';

    protected $fillable = [
        'hash',
        'user_name',
        'user_id',
        'bank',
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

    public function client()
    {
        return $this->belongsTo(Client::class, 'user_name', 'phone')
            ->orWhere('username', $this->user_name);
    }
}
