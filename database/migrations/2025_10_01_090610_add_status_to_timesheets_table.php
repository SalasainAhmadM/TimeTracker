<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->string('status')->nullable()->after('hours_worked'); // overtime, undertime, on-time
            $table->decimal('variance_hours', 5, 2)->nullable()->after('status'); // +/- hours
        });
    }

    public function down()
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->dropColumn(['status', 'variance_hours']);
        });
    }
};