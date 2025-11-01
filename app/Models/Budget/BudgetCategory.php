<?php

namespace App\Models\Budget;

use App\Models\CostCenter;
use App\Traits\TenantScoped;
use App\Traits\TenantAttributeTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\Category;

class BudgetCategory extends Model
{
  use TenantAttributeTrait, TenantScoped;

  protected $table = 'budget_categories';

  protected $fillable = [
    'budget_classification_id',
    'cost_center_id',
    'category_id',
    'percentage',
  ];
  
  public function getPercentageAttribute($value)
  {
    return (int) $value;
  }

  public function classification()
  {
    return $this->belongsTo(BudgetClassification::class, 'budget_classification_id');
  }

  public function costCenter()
  {
    return $this->belongsTo(CostCenter::class);
  }

  public function category()
  {
    return $this->belongsTo(Category::class);
  }

  public function subCategories()
  {
    return $this->hasMany(BudgetSubCategory::class, 'budget_category_id');
  }
}