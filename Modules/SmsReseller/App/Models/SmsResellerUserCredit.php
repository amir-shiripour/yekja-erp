<?php

namespace Modules\SmsReseller\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Clients\Entities\Client;

class SmsResellerUserCredit extends Model
{
    protected $table = 'sms_reseller_user_credits';

    protected $fillable = [
        'hash',
        'user_name',
        'user_id',
        'amount',
        'current_credit',
        'increase',
        'type',
        'jalali_date',
        'jalali_ym',
        'gregorian_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'current_credit' => 'decimal:2',
        'increase' => 'boolean',
        'gregorian_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'user_name', 'phone')
            ->orWhere('username', $this->user_name);
    }
}
