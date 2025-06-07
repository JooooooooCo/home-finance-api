<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetSpecificCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('budget_specific_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('budget_secondary_category_id');
            $table->unsignedInteger('cost_center_id');
            $table->unsignedInteger('specific_category_id');
            $table->decimal('percentage', 5, 2)->unsigned();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('budget_secondary_category_id')->references('id')->on('budget_secondary_categories');
            $table->foreign('cost_center_id')->references('id')->on('cost_centers');
            $table->foreign('specific_category_id')->references('id')->on('specific_categories');
        });
    }

    public function down()
    {
        Schema::dropIfExists('budget_specific_categories');
    }
}
