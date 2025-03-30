<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SeedDefaultTransactionTypes extends Migration
{
    public function up(): void {
      DB::table('transaction_types')->insert([
        ['id' => 1, 'name' => 'Despesa', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 2, 'name' => 'Receita', 'created_at' => now(), 'updated_at' => now()],
      ]);
    }
  
    public function down(): void {
      DB::table('transaction_types')->whereIn('id', [1, 2])->delete();
    }
}
