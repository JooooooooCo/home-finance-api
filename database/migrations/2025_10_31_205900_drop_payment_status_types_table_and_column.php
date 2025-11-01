<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropPaymentStatusTypesTableAndColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['payment_status_id']);
            $table->dropColumn('payment_status_id');
        });

        Schema::dropIfExists('payment_status_types');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('payment_status_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('cost_center_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cost_center_id')->references('id')->on('cost_centers');
            $table->index('cost_center_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_status_id')->after('payment_type_id');
            $table->foreign('payment_status_id')->references('id')->on('payment_status_types');
        });
    }
}

