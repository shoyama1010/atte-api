<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModifyStatusEnumInAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            // ✅ ENUM に pending, approved, editable を追加
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('working','rest','finished','pending','editable','approved') DEFAULT 'editable'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('working','rest','finished') DEFAULT 'working'");
        });
    }
}
