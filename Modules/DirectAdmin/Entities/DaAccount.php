<?php

namespace Modules\DirectAdmin\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Clients\Entities\Client;
use Modules\Services\App\Http\Models\Order;

class DaAccount extends Model
{
    protected $table = 'da_accounts';

    protected $fillable = [
        'da_server_id',
        'client_id',
        'order_id',
        'client_service_id',
        'username',
        'domain',
        'email',
        'package',
        'ip',
        'suspended',
        'disk_usage_bytes',
        'disk_limit_bytes',
        'bandwidth_usage_bytes',
        'bandwidth_limit_bytes',
        'domains_count',
        'email_accounts_count',
        'mysql_databases_count',
        'status',
        'date_created',
        'raw_config',
        'synced_at',
    ];

    protected $casts = [
        'suspended' => 'boolean',
        'disk_usage_bytes' => 'integer',
        'disk_limit_bytes' => 'integer',
        'bandwidth_usage_bytes' => 'integer',
        'bandwidth_limit_bytes' => 'integer',
        'domains_count' => 'integer',
        'email_accounts_count' => 'integer',
        'mysql_databases_count' => 'integer',
        'date_created' => 'date',
        'raw_config' => 'array',
        'synced_at' => 'datetime',
    ];

    public function server()
    {
        return $this->belongsTo(DaServer::class, 'da_server_id');
    }

    /**
     * Relationship to client
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Relationship to service order
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
