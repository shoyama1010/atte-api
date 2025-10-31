<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBeforeAfterTimesToCorrectionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('correction_requests', function (Blueprint $table) {
            $table->time('before_clock_in')->nullable()->after('request_type');
            $table->time('before_clock_out')->nullable();
            $table->time('before_break_start')->nullable();
            $table->time('before_break_end')->nullable();
            $table->time('after_clock_in')->nullable();
            $table->time('after_clock_out')->nullable();
            $table->time('after_break_start')->nullable();
            $table->time('after_break_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('correction_requests', function (Blueprint $table) {
            $table->dropColumn([
                'before_clock_in',
                'before_clock_out',
                'before_break_start',
                'before_break_end',
                'after_clock_in',
                'after_clock_out',
                'after_break_start',
                'after_break_end'
            ]);
        });
    }
}
