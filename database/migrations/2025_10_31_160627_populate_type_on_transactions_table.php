<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PopulateTypeOnTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            UPDATE transactions
            SET type = CASE
                WHEN transaction_type_id = 2 THEN 'income'
                WHEN transaction_type_id = 1 THEN 'expense'
                ELSE 'expense' -- default to expense if unknown
            END
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No need to reverse data migration as it's a one-time population
    }
}
