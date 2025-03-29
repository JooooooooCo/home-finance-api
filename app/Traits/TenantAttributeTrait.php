<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait TenantAttributeTrait
{
    public static function bootTenantAttributeTrait()
    {
        static::creating(function ($model) {
            $model->cost_center_id = Auth::user()->tenant_id;
        });


        static::updating(function ($model) {
            if ($model->isDirty('cost_center_id')) {
                throw new \Exception('Não é permitido alterar o cost_center_id.');
            }

            $model->where('cost_center_id', Auth::user()->tenant_id);
        });

        static::retrieved(function ($model) {
            $model->where('cost_center_id', Auth::user()->tenant_id);
        });
    }
}
