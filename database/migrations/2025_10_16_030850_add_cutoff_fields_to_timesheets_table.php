<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Timesheet;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get existing columns
        $columns = Schema::getColumnListing('timesheets');
        
        Schema::table('timesheets', function (Blueprint $table) use ($columns) {
            // Add status field if it doesn't exist
            if (!in_array('status', $columns)) {
                $table->string('status')->default('on-time')->after('hours_worked');
            }
            
            // Add variance hours field if it doesn't exist
            if (!in_array('variance_hours', $columns)) {
                $table->decimal('variance_hours', 5, 2)->default(0)->after('hours_worked');
            }
            
            // Add cutoff period field if it doesn't exist
            if (!in_array('cutoff_period', $columns)) {
                $table->string('cutoff_period')->nullable()->after('hours_worked');
            }
            
            // Add is_archived field if it doesn't exist
            if (!in_array('is_archived', $columns)) {
                $table->boolean('is_archived')->default(false)->after('hours_worked');
            }
        });

        // Add indexes if they don't exist
        $this->addIndexIfNotExists('timesheets', 'cutoff_period');
        $this->addIndexIfNotExists('timesheets', 'is_archived');
        $this->addIndexIfNotExists('timesheets', 'date');

        // Update existing records with calculated values
        $this->updateExistingRecords();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            // Drop indexes if they exist
            $this->dropIndexIfExists('timesheets', 'timesheets_cutoff_period_index');
            $this->dropIndexIfExists('timesheets', 'timesheets_is_archived_index');
            $this->dropIndexIfExists('timesheets', 'timesheets_date_index');
        });

        // Get existing columns
        $columns = Schema::getColumnListing('timesheets');
        
        Schema::table('timesheets', function (Blueprint $table) use ($columns) {
            // Drop columns if they exist
            $columnsToDrop = [];
            
            if (in_array('status', $columns)) {
                $columnsToDrop[] = 'status';
            }
            if (in_array('variance_hours', $columns)) {
                $columnsToDrop[] = 'variance_hours';
            }
            if (in_array('cutoff_period', $columns)) {
                $columnsToDrop[] = 'cutoff_period';
            }
            if (in_array('is_archived', $columns)) {
                $columnsToDrop[] = 'is_archived';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Update existing records with calculated values
     */
    private function updateExistingRecords(): void
    {
        // Get all existing timesheets
        $timesheets = DB::table('timesheets')->get();

        foreach ($timesheets as $timesheet) {
            // Recalculate hours and status
            $calculation = Timesheet::calculateHours($timesheet->time_in, $timesheet->time_out);
            
            // Get cutoff period for this date
            $cutoffPeriod = Timesheet::getCurrentCutoffPeriod($timesheet->date);
            
            // Update the record
            DB::table('timesheets')
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

    /**
     * Add index if it doesn't exist
     */
    private function addIndexIfNotExists(string $table, string $column): void
    {
        $indexName = "{$table}_{$column}_index";
        
        // For SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list({$table})");
            $indexExists = collect($indexes)->contains('name', $indexName);
            
            if (!$indexExists) {
                DB::statement("CREATE INDEX {$indexName} ON {$table} ({$column})");
            }
        } else {
            // For MySQL/PostgreSQL
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes($table);
            
            if (!isset($indexes[$indexName])) {
                Schema::table($table, function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
            }
        }
    }

    /**
     * Drop index if it exists
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        // For SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list({$table})");
            $indexExists = collect($indexes)->contains('name', $indexName);
            
            if ($indexExists) {
                DB::statement("DROP INDEX {$indexName}");
            }
        } else {
            // For MySQL/PostgreSQL
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes($table);
            
            if (isset($indexes[$indexName])) {
                Schema::table($table, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }
        }
    }
};