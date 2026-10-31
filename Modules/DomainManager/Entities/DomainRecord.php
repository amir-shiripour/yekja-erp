<?php

namespace Modules\DomainManager\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Clients\Entities\Client;
use Modules\Services\App\Http\Models\Order;

class DomainRecord extends Model
{
    protected $table = 'domain_records';

    protected $fillable = [
        'domain_reseller_id',
        'client_id',
        'service_order_id',
        'order_id',
        'domain_name',
        'status',
        'expiry_date',
        'creation_date',
        'nameservers',
        'privacy_protected',
        'auth_code',
        'raw_details',
        'synced_at'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'creation_date' => 'date',
        'nameservers' => 'json',
        'privacy_protected' => 'boolean',
        'raw_details' => 'json',
        'synced_at' => 'datetime'
    ];

    /**
     * Relationship to reseller
     */
    public function reseller()
    {
        return $this->belongsTo(DomainReseller::class, 'domain_reseller_id');
    }

    /**
     * Relationship to client
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Relationship to CRM Service Order
     */
    public function serviceOrder()
    {
        return $this->belongsTo(Order::class, 'service_order_id');
    }
}
