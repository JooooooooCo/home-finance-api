<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
  public function up(): void
  {
    DB::statement('ALTER TABLE cost_center_user ADD PRIMARY KEY (cost_center_id, user_id)');
  }

  public function down(): void
  {
    DB::statement('ALTER TABLE cost_center_user DROP PRIMARY KEY');
  }
};
