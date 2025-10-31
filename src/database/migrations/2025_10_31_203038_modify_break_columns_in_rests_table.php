<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyBreakColumnsInRestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rests', function (Blueprint $table) {
            // break_start, break_end を DATETIME → TIME に変更
            $table->time('break_start')->nullable()->change();
            $table->time('break_end')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rests', function (Blueprint $table) {
            // 元に戻す（必要に応じて）
            $table->dateTime('break_start')->nullable()->change();
            $table->dateTime('break_end')->nullable()->change();
        });
    }
}
