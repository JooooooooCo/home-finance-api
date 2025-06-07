<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetPrimaryCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('budget_primary_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('budget_id');
            $table->unsignedInteger('cost_center_id');
            $table->unsignedInteger('primary_category_id');
            $table->decimal('percentage', 5, 2)->unsigned();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('budget_id')->references('id')->on('budgets');
            $table->foreign('cost_center_id')->references('id')->on('cost_centers');
            $table->foreign('primary_category_id')->references('id')->on('primary_categories');
        });
    }

    public function down()
    {
        Schema::dropIfExists('budget_primary_categories');
    }
}
