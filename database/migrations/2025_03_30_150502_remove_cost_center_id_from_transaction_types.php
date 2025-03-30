<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCostCenterIdFromTransactionTypes extends Migration
{
    public function up(): void {
      Schema::table('transaction_types', function (Blueprint $table) {
        $table->dropForeign('transaction_types_cost_center_id_foreign');
        $table->dropColumn('cost_center_id');
      });
    }
  
    public function down(): void {
      Schema::table('transaction_types', function (Blueprint $table) {
        $table->unsignedInteger('cost_center_id');
        $table->foreign('cost_center_id')->references('id')->on('cost_centers');
      });
    }
}
