<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameSpecificCategoryIdToSubCategoryIdInBudgetSubCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_sub_categories', function (Blueprint $table) {
            $table->renameColumn('specific_category_id', 'sub_category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_sub_categories', function (Blueprint $table) {
            $table->renameColumn('sub_category_id', 'specific_category_id');
        });
    }
}
