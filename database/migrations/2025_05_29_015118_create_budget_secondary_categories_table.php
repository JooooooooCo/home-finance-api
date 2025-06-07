<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetSecondaryCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('budget_secondary_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('budget_primary_category_id');
            $table->unsignedInteger('cost_center_id');
            $table->unsignedInteger('secondary_category_id');
            $table->decimal('percentage', 5, 2)->unsigned();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('budget_primary_category_id')->references('id')->on('budget_primary_categories');
            $table->foreign('cost_center_id')->references('id')->on('cost_centers');
            $table->foreign('secondary_category_id')->references('id')->on('secondary_categories');
        });
    }

    public function down()
    {
        Schema::dropIfExists('budget_secondary_categories');
    }
}
