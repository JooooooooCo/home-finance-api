<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostCenterUser extends Model
{
    use SoftDeletes;
    protected $table = 'cost_center_user';

    protected $fillable = [
        'cost_center_id',
        'user_id'
    ];
}
