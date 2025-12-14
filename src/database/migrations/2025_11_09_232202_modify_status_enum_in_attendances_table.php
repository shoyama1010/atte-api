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
            // ✅ ENUM に none を追加し、既存のステータスも統一
            DB::statement("
                ALTER TABLE attendances
                MODIFY COLUMN status ENUM(
                    'none',
                    'working',
                    'on_break',
                    'working_after_break',
                    'left',
                    'editable'
                ) NOT NULL DEFAULT 'none'
            ");
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
            DB::statement("
                ALTER TABLE attendances
                MODIFY COLUMN status ENUM(
                    'working',
                    'on_break',
                    'working_after_break',
                    'left',
                    'editable'
                ) NOT NULL DEFAULT 'working'
            ");
        });
    }
}
