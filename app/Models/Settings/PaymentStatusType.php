<?php

namespace App\Models\Settings;

use App\Models\CostCenter;
use App\Traits\TenantScoped;
use App\Traits\TenantAttributeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentStatusType extends Model
{
    public const PAID = 1;
    public const PENDING = 2;
    public const TRANSFERED = 3;

    use SoftDeletes, TenantAttributeTrait, TenantScoped;

    protected $table = 'payment_status_types';

    protected $fillable = ['name', 'cost_center_id'];

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }
}
