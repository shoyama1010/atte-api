<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAfterRestsToCorrectionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('correction_requests', function (Blueprint $table) {
            $table->json('after_rests')->nullable()->comment('複数休憩のJSONデータ');
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
            if (Schema::hasColumn('correction_requests', 'after_rests')) {
                $table->dropColumn('after_rests');
            }
        });
    }
}
