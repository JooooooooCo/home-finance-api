<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameBudgetPrimaryCategoryIdToBudgetClassificationIdInBudgetSecondaryCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_secondary_categories', function (Blueprint $table) {
            $table->renameColumn('budget_primary_category_id', 'budget_classification_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_secondary_categories', function (Blueprint $table) {
            $table->renameColumn('budget_classification_id', 'budget_primary_category_id');
        });
    }
}
