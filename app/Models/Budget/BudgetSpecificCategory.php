<?php

namespace App\Models\Budget;

use App\Models\CostCenter;
use App\Traits\TenantScoped;
use App\Traits\TenantAttributeTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\SpecificCategory;

class BudgetSpecificCategory extends Model
{
  use TenantAttributeTrait, TenantScoped;

  protected $table = 'budget_specific_categories';

  protected $fillable = [
    'budget_secondary_category_id',
    'cost_center_id',
    'specific_category_id',
    'percentage',
  ];
  
  public function getPercentageAttribute($value)
  {
    return (int) $value;
  }

  public function secondaryCategory()
  {
    return $this->belongsTo(BudgetSecondaryCategory::class, 'budget_secondary_category_id');
  }

  public function costCenter()
  {
    return $this->belongsTo(CostCenter::class);
  }

  public function specificCategory()
  {
    return $this->belongsTo(SpecificCategory::class);
  }
}
