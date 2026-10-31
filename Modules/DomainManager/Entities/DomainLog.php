<?php

namespace Modules\DomainManager\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class DomainLog extends Model
{
    protected $table = 'domain_logs';

    protected $fillable = [
        'domain_reseller_id',
        'user_id',
        'domain_name',
        'action',
        'description',
        'status'
    ];

    public function reseller()
    {
        return $this->belongsTo(DomainReseller::class, 'domain_reseller_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper to log an event
     */
    public static function log($action, $description, $domainName = null, $resellerId = null, $status = 'success')
    {
        return self::create([
            'domain_reseller_id' => $resellerId,
            'user_id' => auth()->id(),
            'domain_name' => $domainName,
            'action' => $action,
            'description' => $description,
            'status' => $status
        ]);
    }
}
