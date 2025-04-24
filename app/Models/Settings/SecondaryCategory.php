<?php

namespace App\Models\Settings;

use App\Models\CostCenter;
use App\Traits\TenantScoped;
use App\Models\TransactionType;
use App\Models\CashFlow\Transaction;
use App\Traits\TenantAttributeTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\SpecificCategory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SecondaryCategory extends Model
{
  use SoftDeletes, TenantAttributeTrait, TenantScoped;

  protected $table = 'secondary_categories';

  protected $fillable = [
    'name',
    'transaction_type_id',
    'cost_center_id',
  ];

  public function costCenter()
  {
    return $this->belongsTo(CostCenter::class);
  }

  public function transactionType()
  {
    return $this->belongsTo(TransactionType::class);
  }

  public function specificCategories()
  {
    return $this->hasMany(SpecificCategory::class);
  }

  public function transactions()
  {
    return $this->hasMany(Transaction::class);
  }
}
