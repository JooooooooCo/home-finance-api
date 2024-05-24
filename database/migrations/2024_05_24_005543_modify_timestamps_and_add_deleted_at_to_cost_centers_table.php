<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyTimestampsAndAddDeletedAtToCostCentersTable extends Migration
{
    public function up()
    {
        Schema::table('cost_centers', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });

        Schema::table('cost_centers', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('cost_centers', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });

        Schema::table('cost_centers', function (Blueprint $table) {
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });
    }
}
