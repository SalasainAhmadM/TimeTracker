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
        return view('timesheets.index', compact('timesheets', 'totalHours'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'time_in' => 'required',
            'time_out' => 'required',
        ]);

        $hoursWorked = Timesheet::calculateHours($validated['time_in'], $validated['time_out']);

        Timesheet::create([
            'date' => $validated['date'],
            'time_in' => $validated['time_in'],
            'time_out' => $validated['time_out'],
            'hours_worked' => $hoursWorked,
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

        $hoursWorked = Timesheet::calculateHours($validated['time_in'], $validated['time_out']);

        $timesheet->update([
            'date' => $validated['date'],
            'time_in' => $validated['time_in'],
            'time_out' => $validated['time_out'],
            'hours_worked' => $hoursWorked,
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

        fputcsv($handle, ['Date', 'Time In', 'Time Out', 'Hours Worked (excluding 12-1pm lunch)']);

        foreach ($timesheets as $timesheet) {
            fputcsv($handle, [
                $timesheet->date->format('M d, Y'),
                \Carbon\Carbon::parse($timesheet->time_in)->format('h:i A'),
                \Carbon\Carbon::parse($timesheet->time_out)->format('h:i A'),
                $timesheet->hours_worked
            ]);
        }

        fputcsv($handle, ['', '', 'TOTAL:', $totalHours]);

        fclose($handle);
        exit;
    }
}