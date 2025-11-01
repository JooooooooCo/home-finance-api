<?php

namespace App\Models\Budget;

use App\Models\CostCenter;
use App\Traits\TenantScoped;
use App\Traits\TenantAttributeTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\Classification;

class BudgetClassification extends Model
{
  use TenantAttributeTrait, TenantScoped;

  protected $table = 'budget_classifications';
  
  protected $fillable = [
    'budget_id',
    'cost_center_id',
    'classification_id',
    'percentage',
  ];
  
  public function getPercentageAttribute($value)
  {
    return (int) $value;
  }

  public function budget()
  {
    return $this->belongsTo(Budget::class);
  }

  public function costCenter()
  {
    return $this->belongsTo(CostCenter::class);
  }

  public function classification()
  {
    return $this->belongsTo(Classification::class);
  }

  public function categories()
  {
    return $this->hasMany(BudgetCategory::class, 'budget_classification_id');
  }
}