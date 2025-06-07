<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterBudgetSecondaryCategoriesAddCascade extends Migration
{
    public function up()
    {
        Schema::table('budget_secondary_categories', function (Blueprint $table) {
            $table->dropForeign(['budget_primary_category_id']);

            $table->foreign('budget_primary_category_id')
                ->references('id')->on('budget_primary_categories')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('budget_secondary_categories', function (Blueprint $table) {
            $table->dropForeign(['budget_primary_category_id']);

            $table->foreign('budget_primary_category_id')
                ->references('id')->on('budget_primary_categories');
        });
    }
}
