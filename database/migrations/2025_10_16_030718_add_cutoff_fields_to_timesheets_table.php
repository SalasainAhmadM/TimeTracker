<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Timesheet;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            // Add status field
            $table->string('status')->default('on-time')->after('hours_worked');
            
            // Add variance hours field
            $table->decimal('variance_hours', 5, 2)->default(0)->after('status');
            
            // Add cutoff period field
            $table->string('cutoff_period')->nullable()->after('variance_hours');
            
            // Add is_archived field
            $table->boolean('is_archived')->default(false)->after('cutoff_period');
            
            // Add indexes for better query performance
            $table->index('cutoff_period');
            $table->index('is_archived');
            $table->index('date');
        });

        // Update existing records with calculated values
        $this->updateExistingRecords();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->dropIndex(['cutoff_period']);
            $table->dropIndex(['is_archived']);
            $table->dropIndex(['date']);
            
            $table->dropColumn([
                'status',
                'variance_hours',
                'cutoff_period',
                'is_archived'
            ]);
        });
    }

    /**
     * Update existing records with calculated values
     */
    private function updateExistingRecords(): void
    {
        // Get all existing timesheets
        $timesheets = \DB::table('timesheets')->get();

        foreach ($timesheets as $timesheet) {
            // Recalculate hours and status
            $calculation = Timesheet::calculateHours($timesheet->time_in, $timesheet->time_out);
            
            // Get cutoff period for this date
            $cutoffPeriod = Timesheet::getCurrentCutoffPeriod($timesheet->date);
            
            // Update the record
            \DB::table('timesheets')
                ->where('id', $timesheet->id)
                ->update([
                    'hours_worked' => $calculation['hours'],
                    'status' => $calculation['status'],
                    'variance_hours' => $calculation['variance'],
                    'cutoff_period' => $cutoffPeriod,
                    'is_archived' => false
                ]);
        }
    }
};