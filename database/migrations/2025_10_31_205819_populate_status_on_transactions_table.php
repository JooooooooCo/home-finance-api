<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PopulateStatusOnTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('transactions')
            ->where('payment_status_id', 1)
            ->update(['status' => 'paid']);

        DB::table('transactions')
            ->where('payment_status_id', 2)
            ->update(['status' => 'pending']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
