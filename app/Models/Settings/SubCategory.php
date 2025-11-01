<?php

namespace App\Models\Settings;

use App\Models\CostCenter;
use App\Traits\TenantScoped;
use App\Models\CashFlow\Transaction;
use App\Traits\TenantAttributeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCategory extends Model
{
  use SoftDeletes, TenantAttributeTrait, TenantScoped;

  protected $table = 'sub_categories';

  protected $fillable = [
    'name',
    'category_id',
    'cost_center_id',
  ];

  public function costCenter()
  {
    return $this->belongsTo(CostCenter::class);
  }

  public function category()
  {
    return $this->belongsTo(Category::class);
  }

  public function transactions()
  {
    return $this->hasMany(Transaction::class, 'sub_category_id');
  }
}