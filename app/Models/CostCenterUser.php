<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostCenterUser extends Model
{
    protected $table = 'cost_center_user';

    protected $fillable = [
        'cost_center_id',
        'user_id'
    ];
}
