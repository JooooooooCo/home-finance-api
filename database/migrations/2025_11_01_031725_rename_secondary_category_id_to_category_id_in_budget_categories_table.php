<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameSecondaryCategoryIdToCategoryIdInBudgetCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_categories', function (Blueprint $table) {
            $table->renameColumn('secondary_category_id', 'category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_categories', function (Blueprint $table) {
            $table->renameColumn('category_id', 'secondary_category_id');
        });
    }
}
