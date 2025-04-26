<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionType extends Model
{
    public const EXPENSE = 1;
    public const REVENUE = 2;

    use SoftDeletes;

    protected $table = 'transaction_types';

    protected $fillable = ['name'];
}
