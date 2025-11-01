<?php

namespace App\Models\CashFlow;

use App\Enums\TransactionType;
use App\Enums\PaymentStatus;
use App\Models\CostCenter;
use App\Traits\TenantScoped;
use App\Models\Settings\PaymentType;
use App\Traits\TenantAttributeTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\PrimaryCategory;
use App\Models\Settings\SpecificCategory;
use App\Models\Settings\SecondaryCategory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
  use SoftDeletes, TenantAttributeTrait, TenantScoped;

  protected $table = 'transactions';

  protected $fillable = [
    'payment_type_id',
    'status',
    'purchase_date',
    'due_date',
    'payment_date',
    'amount',
    'current_installment',
    'total_installments',
    'primary_category_id',
    'secondary_category_id',
    'specific_category_id',
    'description',
    'primary_note',
    'secondary_note',
    'spending_average',
    'is_real',
    'is_reconciled',
    'created_at',
    'updated_at',
    'cost_center_id',
    'type',
  ];

  protected $casts = [
    'type' => TransactionType::class,
    'status' => PaymentStatus::class,
  ];

  public function costCenter()
  {
    return $this->belongsTo(CostCenter::class);
  }

  public function paymentType()
  {
    return $this->belongsTo(PaymentType::class);
  }


  public function primaryCategory()
  {
    return $this->belongsTo(PrimaryCategory::class);
  }

  public function secondaryCategory()
  {
    return $this->belongsTo(SecondaryCategory::class);
  }

  public function specificCategory()
  {
    return $this->belongsTo(SpecificCategory::class);
  }
}
