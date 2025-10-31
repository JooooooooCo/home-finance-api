<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveTransactionTypeIdFromSecondaryCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('secondary_categories', function (Blueprint $table) {
            $table->dropForeign(['transaction_type_id']);
            $table->dropColumn('transaction_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('secondary_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('transaction_type_id')->after('name');
            $table->foreign('transaction_type_id')->references('id')->on('transaction_types');
        });
    }
}
