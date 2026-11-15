<?php

namespace Modules\Market\Entities;

use Illuminate\Database\Eloquent\Model;

class VendorDocument extends Model
{
    protected $table = 'market_vendor_documents';

    protected $fillable = [
        'vendor_id', 'type', 'file_path', 'status', 'rejection_reason'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function getUrlAttribute(): string
    {
        return route('user.market.vendors.document', $this->id);
    }
}
