<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    public function index()
    {
        $timesheets = Timesheet::orderBy('date', 'desc')->get();
        $totalHours = $timesheets->sum('hours_worked');
        
        // Calculate overtime and undertime totals
        $totalOvertime = $timesheets->where('status', 'overtime')->sum('variance_hours');
        $totalUndertime = abs($timesheets->where('status', 'undertime')->sum('variance_hours'));
        $netVariance = $totalOvertime - $totalUndertime;
        
        // Calculate expected hours (8 hours per entry)
        $expectedHours = $timesheets->count() * 8;
        
        return view('timesheets.index', compact('timesheets', 'totalHours', 'totalOvertime', 'totalUndertime', 'netVariance', 'expectedHours'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'time_in' => 'required',
            'time_out' => 'required',
        ]);

        $calculation = Timesheet::calculateHours($validated['time_in'], $validated['time_out']);

        Timesheet::create([
            'date' => $validated['date'],
            'time_in' => $validated['time_in'],
            'time_out' => $validated['time_out'],
            'hours_worked' => $calculation['hours'],
            'status' => $calculation['status'],
            'variance_hours' => $calculation['variance'],
        ]);

        return redirect()->route('timesheets.index')->with('success', 'Entry added successfully!');
    }

    public function update(Request $request, Timesheet $timesheet)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'time_in' => 'required',
            'time_out' => 'required',
        ]);

        $calculation = Timesheet::calculateHours($validated['time_in'], $validated['time_out']);

        $timesheet->update([
            'date' => $validated['date'],
            'time_in' => $validated['time_in'],
            'time_out' => $validated['time_out'],
            'hours_worked' => $calculation['hours'],
            'status' => $calculation['status'],
            'variance_hours' => $calculation['variance'],
        ]);

        return redirect()->route('timesheets.index')->with('success', 'Entry updated successfully!');
    }

    public function destroy(Timesheet $timesheet)
    {
        $timesheet->delete();
        return redirect()->route('timesheets.index')->with('success', 'Entry deleted successfully!');
    }

    public function exportCsv()
    {
        $timesheets = Timesheet::orderBy('date', 'desc')->get();
        $totalHours = $timesheets->sum('hours_worked');

        $filename = 'timesheet_' . date('Y-m-d') . '.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        fputcsv($handle, ['Date', 'Time In', 'Time Out', 'Hours Worked', 'Status', 'Variance']);

        foreach ($timesheets as $timesheet) {
            $varianceText = '';
            if ($timesheet->variance_hours > 0) {
                $varianceText = '+' . $timesheet->variance_hours . ' hrs';
            } elseif ($timesheet->variance_hours < 0) {
                $varianceText = $timesheet->variance_hours . ' hrs';
            } else {
                $varianceText = '0 hrs';
            }

            fputcsv($handle, [
                $timesheet->date->format('M d, Y'),
                \Carbon\Carbon::parse($timesheet->time_in)->format('h:i:s A'),
                \Carbon\Carbon::parse($timesheet->time_out)->format('h:i:s A'),
                $timesheet->hours_worked,
                strtoupper($timesheet->status),
                $varianceText
            ]);
        }

        fputcsv($handle, ['', '', '', 'TOTAL:', $totalHours, '']);

        fclose($handle);
        exit;
    }
}