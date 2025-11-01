<?php

namespace App\Models\Settings;

use App\Enums\TransactionType;
use App\Models\CostCenter;
use App\Traits\TenantScoped;
use App\Models\CashFlow\Transaction;
use App\Traits\TenantAttributeTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\SubCategory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
  use SoftDeletes, TenantAttributeTrait, TenantScoped;

  protected $table = 'categories';

  protected $fillable = [
    'name',
    'cost_center_id',
    'type',
  ];

  protected $casts = [
    'type' => TransactionType::class,
  ];  

  public function costCenter()
  {
    return $this->belongsTo(CostCenter::class);
  }

  public function subCategories()
  {
    return $this->hasMany(SubCategory::class, 'category_id');
  }

  public function transactions()
  {
    return $this->hasMany(Transaction::class, 'category_id');
  }
}