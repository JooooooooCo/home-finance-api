<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameBudgetSecondaryCategoryIdToBudgetCategoryIdInBudgetSpecificCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_specific_categories', function (Blueprint $table) {
            $table->renameColumn('budget_secondary_category_id', 'budget_category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_specific_categories', function (Blueprint $table) {
            $table->renameColumn('budget_category_id', 'budget_secondary_category_id');
        });
    }
}
