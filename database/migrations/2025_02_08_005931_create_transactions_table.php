<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('transaction_type_id');
            $table->unsignedInteger('payment_type_id')->nullable();
            $table->unsignedInteger('payment_status_id');
            $table->date('purchase_date');
            $table->date('due_date');
            $table->date('payment_date')->nullable();
            $table->decimal('amount', 10, 2)->unsigned();
            $table->unsignedInteger('current_installment');
            $table->unsignedInteger('total_installments');
            $table->unsignedInteger('primary_category_id');
            $table->unsignedInteger('secondary_category_id');
            $table->unsignedInteger('specific_category_id');
            $table->text('description')->nullable();
            $table->text('primary_note')->nullable();
            $table->text('secondary_note')->nullable();
            $table->text('spending_average')->nullable();
            $table->boolean('is_real')->default(true);
            $table->boolean('is_reconciled')->default(false);
            $table->timestamps();
            $table->unsignedInteger('cost_center_id');

            $table->foreign('transaction_type_id')->references('id')->on('transaction_types');
            $table->foreign('payment_type_id')->references('id')->on('payment_types');
            $table->foreign('payment_status_id')->references('id')->on('payment_status_types');
            $table->foreign('primary_category_id')->references('id')->on('primary_categories');
            $table->foreign('secondary_category_id')->references('id')->on('secondary_categories');
            $table->foreign('specific_category_id')->references('id')->on('specific_categories');
            $table->foreign('cost_center_id')->references('id')->on('cost_centers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}