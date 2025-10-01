<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'time_in', 'time_out', 'hours_worked', 'status', 'variance_hours'];

    protected $casts = [
        'date' => 'date',
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
}