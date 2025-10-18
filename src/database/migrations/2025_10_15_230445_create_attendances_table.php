<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            // 従業員（usersテーブル）への外部キー
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // 打刻関連
            $table->timestamp('clock_in_time')->nullable();
            $table->timestamp('break_start')->nullable();
            $table->timestamp('break_end')->nullable();
            $table->timestamp('clock_out_time')->nullable();
            $table->enum( 'status', [ 'none','working', 'on_break', 'left'])->default('none');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
