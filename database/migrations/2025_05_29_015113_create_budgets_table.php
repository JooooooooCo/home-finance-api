<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetsTable extends Migration
{
    public function up()
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cost_center_id');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cost_center_id')->references('id')->on('cost_centers');
        });
    }

    public function down()
    {
        Schema::dropIfExists('budgets');
    }
}
