<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterBudgetSpecificCategoriesAddCascade extends Migration
{
    public function up()
    {
        Schema::table('budget_specific_categories', function (Blueprint $table) {
            $table->dropForeign(['budget_secondary_category_id']);

            $table->foreign('budget_secondary_category_id')
                ->references('id')->on('budget_secondary_categories')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('budget_specific_categories', function (Blueprint $table) {
            $table->dropForeign(['budget_secondary_category_id']);

            $table->foreign('budget_secondary_category_id')
                ->references('id')->on('budget_secondary_categories');
        });
    }
}
