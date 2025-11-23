<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveBreakColumnsFromAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'break_start')) {
                $table->dropColumn('break_start');
            }
            if (Schema::hasColumn('attendances', 'break_end')) {
                $table->dropColumn('break_end');
            }
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
            $table->timestamp('break_start')->nullable();
            $table->timestamp('break_end')->nullable();
        });
    }
}
