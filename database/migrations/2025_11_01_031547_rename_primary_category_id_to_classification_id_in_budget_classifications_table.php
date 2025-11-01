<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePrimaryCategoryIdToClassificationIdInBudgetClassificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('budget_classifications', function (Blueprint $table) {
            $table->renameColumn('primary_category_id', 'classification_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('budget_classifications', function (Blueprint $table) {
            $table->renameColumn('classification_id', 'primary_category_id');
        });
    }
}
