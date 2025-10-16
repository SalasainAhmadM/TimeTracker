<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 
        'time_in', 
        'time_out', 
        'hours_worked', 
        'status', 
        'variance_hours',
        'cutoff_period',
        'is_archived'
    ];

    protected $casts = [
        'date' => 'date',
        'is_archived' => 'boolean',
    ];

    public static function calculateHours($timeIn, $timeOut)
    {
        if (!$timeIn || !$timeOut) return ['hours' => 0, 'status' => 'on-time', 'variance' => 0];

        // Parse times with seconds - they come in H:i:s format
        $start = Carbon::createFromFormat('H:i:s', $timeIn, 'Asia/Manila');
        $end = Carbon::createFromFormat('H:i:s', $timeOut, 'Asia/Manila');
        
        // Calculate total minutes
        $totalMinutes = $start->diffInMinutes($end);
        
        // Check if lunch break (12:00-13:00) falls within work hours
        $lunchStart = Carbon::createFromFormat('H:i:s', '12:00:00', 'Asia/Manila');
        $lunchEnd = Carbon::createFromFormat('H:i:s', '13:00:00', 'Asia/Manila');
        
        // Set lunch times to same date as work times
        $lunchStart->setDate($start->year, $start->month, $start->day);
        $lunchEnd->setDate($start->year, $start->month, $start->day);
        
        if ($start->lessThan($lunchEnd) && $end->greaterThan($lunchStart)) {
            $overlapStart = $start->greaterThan($lunchStart) ? $start : $lunchStart;
            $overlapEnd = $end->lessThan($lunchEnd) ? $end : $lunchEnd;
            $lunchMinutes = $overlapStart->diffInMinutes($overlapEnd);
            $totalMinutes -= $lunchMinutes;
        }
        
        $hoursWorked = round($totalMinutes / 60, 2);
        
        // Standard work hours is 8 hours
        $standardHours = 8.0;
        $variance = round($hoursWorked - $standardHours, 2);
        
        if ($hoursWorked < $standardHours) {
            $status = 'undertime';
        } elseif ($hoursWorked > $standardHours) {
            $status = 'overtime';
        } else {
            $status = 'on-time';
        }
        
        return [
            'hours' => $hoursWorked,
            'status' => $status,
            'variance' => $variance
        ];
    }

    /**
     * Get the current cut-off period based on date
     * Returns format: "YYYY-MM-1" for 1-15 or "YYYY-MM-2" for 16-end
     */
    public static function getCurrentCutoffPeriod($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now('Asia/Manila');
        $day = $date->day;
        
        // Bi-monthly cut-off: 1-15 and 16-end of month
        $period = $day <= 15 ? 1 : 2;
        
        return $date->format('Y-m') . '-' . $period;
    }

    /**
     * Get date range for a specific cut-off period
     */
    public static function getCutoffDateRange($cutoffPeriod)
    {
        // Parse cutoff period (e.g., "2025-01-1" or "2025-01-2")
        list($year, $month, $period) = explode('-', $cutoffPeriod);
        
        $startDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila');
        
        if ($period == 1) {
            // First period: 1st to 15th
            $endDate = Carbon::create($year, $month, 15, 23, 59, 59, 'Asia/Manila');
        } else {
            // Second period: 16th to end of month
            $startDate->day(16);
            $endDate = $startDate->copy()->endOfMonth();
        }
        
        return [
            'start' => $startDate,
            'end' => $endDate,
            'label' => $startDate->format('M d') . ' - ' . $endDate->format('M d, Y')
        ];
    }

    /**
     * Process cut-off end - Archive current period and calculate totals
     * 
     * @param string|null $cutoffPeriod Specific period or current if null
     * @return array Summary of the cut-off period
     */
    public static function processCutoffEnd($cutoffPeriod = null)
    {
        // Get the cut-off period
        if (!$cutoffPeriod) {
            $cutoffPeriod = self::getCurrentCutoffPeriod();
        }
        
        // Get date range for this period
        $dateRange = self::getCutoffDateRange($cutoffPeriod);
        
        // Get all timesheets for this period that aren't archived yet
        $timesheets = self::whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->where('is_archived', false)
            ->orderBy('date', 'asc')
            ->get();
        
        // Calculate totals
        $totalHours = $timesheets->sum('hours_worked');
        $totalOvertime = $timesheets->where('status', 'overtime')->sum('variance_hours');
        $totalUndertime = abs($timesheets->where('status', 'undertime')->sum('variance_hours'));
        $netVariance = $totalOvertime - $totalUndertime;
        $workDays = $timesheets->count();
        $expectedHours = $workDays * 8;
        
        // Archive all timesheets in this period
        self::whereBetween('date', [$dateRange['start'], $dateRange['end']])
            ->update([
                'cutoff_period' => $cutoffPeriod,
                'is_archived' => true
            ]);
        
        // Return summary
        return [
            'cutoff_period' => $cutoffPeriod,
            'date_range' => $dateRange['label'],
            'start_date' => $dateRange['start']->format('Y-m-d'),
            'end_date' => $dateRange['end']->format('Y-m-d'),
            'work_days' => $workDays,
            'total_hours' => round($totalHours, 2),
            'expected_hours' => $expectedHours,
            'total_overtime' => round($totalOvertime, 2),
            'total_undertime' => round($totalUndertime, 2),
            'net_variance' => round($netVariance, 2),
            'archived_count' => $timesheets->count()
        ];
    }

    /**
     * Get timesheets for a specific cut-off period
     */
    public static function getByPeriod($cutoffPeriod)
    {
        return self::where('cutoff_period', $cutoffPeriod)
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Get all cut-off periods (archived)
     */
    public static function getArchivedPeriods()
    {
        return self::where('is_archived', true)
            ->select('cutoff_period')
            ->distinct()
            ->orderBy('cutoff_period', 'desc')
            ->pluck('cutoff_period')
            ->map(function($period) {
                $dateRange = self::getCutoffDateRange($period);
                return [
                    'period' => $period,
                    'label' => $dateRange['label']
                ];
            });
    }

    /**
     * Get active (non-archived) timesheets
     */
    public static function getActive()
    {
        return self::where('is_archived', false)
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Refresh cut-off periods - Assign correct period to all timesheets
     */
    public static function refreshCutoffPeriods()
    {
        $timesheets = self::all();
        
        foreach ($timesheets as $timesheet) {
            $period = self::getCurrentCutoffPeriod($timesheet->date);
            $timesheet->update(['cutoff_period' => $period]);
        }
        
        return [
            'updated_count' => $timesheets->count(),
            'message' => 'All timesheet cut-off periods have been refreshed'
        ];
    }
}