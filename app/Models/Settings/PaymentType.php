<?php

namespace App\Models\Settings;

use App\Models\CostCenter;
use App\Traits\TenantScoped;
use App\Traits\TenantAttributeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentType extends Model
{
    use SoftDeletes, TenantAttributeTrait, TenantScoped;

    protected $table = 'payment_types';

    protected $fillable = ['name', 'cost_center_id'];

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }
}
