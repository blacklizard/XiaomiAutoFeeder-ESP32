<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feeders', function (Blueprint $table) {
            $table->timestamp('drier_replaced_at')->nullable()->after('schedule_synced');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feeders', function (Blueprint $table) {
            $table->dropColumn('drier_replaced_at');
        });
    }
};
