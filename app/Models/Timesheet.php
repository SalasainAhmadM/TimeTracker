<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'time_in', 'time_out', 'hours_worked'];

    protected $casts = [
        'date' => 'date',
    ];

    public static function calculateHours($timeIn, $timeOut)
    {
        if (!$timeIn || !$timeOut) return 0;

        // Parse times - they come in 24-hour format from HTML input
        $start = Carbon::createFromFormat('H:i', $timeIn, 'Asia/Manila');
        $end = Carbon::createFromFormat('H:i', $timeOut, 'Asia/Manila');
        
        // Calculate total minutes
        $totalMinutes = $start->diffInMinutes($end);
        
        // Check if lunch break (12:00-13:00) falls within work hours
        $lunchStart = Carbon::createFromFormat('H:i', '12:00', 'Asia/Manila');
        $lunchEnd = Carbon::createFromFormat('H:i', '13:00', 'Asia/Manila');
        
        // Set lunch times to same date as work times
        $lunchStart->setDate($start->year, $start->month, $start->day);
        $lunchEnd->setDate($start->year, $start->month, $start->day);
        
        if ($start->lessThan($lunchEnd) && $end->greaterThan($lunchStart)) {
            $overlapStart = $start->greaterThan($lunchStart) ? $start : $lunchStart;
            $overlapEnd = $end->lessThan($lunchEnd) ? $end : $lunchEnd;
            $lunchMinutes = $overlapStart->diffInMinutes($overlapEnd);
            $totalMinutes -= $lunchMinutes;
        }
        
        return round($totalMinutes / 60, 2);
    }
}