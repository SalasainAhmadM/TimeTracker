<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('timesheets', function (Blueprint $table) {
        $table->string('cutoff_period')->nullable()->after('variance_hours');
        $table->boolean('is_archived')->default(false)->after('cutoff_period');
        $table->index('cutoff_period');
        $table->index('is_archived');
    });
}

public function down()
{
    Schema::table('timesheets', function (Blueprint $table) {
        $table->dropColumn(['cutoff_period', 'is_archived']);
    });
}
};
