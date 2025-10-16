<?php

namespace App\Http\Controllers;

use App\Models\Timesheet;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TimesheetController extends Controller
{
    public function index()
    {
        // Get current cut-off period info
        $currentPeriod = Timesheet::getCurrentCutoffPeriod();
        $dateRange = Timesheet::getCutoffDateRange($currentPeriod);
        $currentPeriodLabel = $dateRange['label'];
        
        // Get only active timesheets from the CURRENT period
        $timesheets = Timesheet::where('is_archived', false)
            ->where('cutoff_period', $currentPeriod)
            ->orderBy('date', 'desc')
            ->get();
        
        $totalHours = $timesheets->sum('hours_worked');
        
        // Calculate overtime and undertime totals
        $totalOvertime = $timesheets->where('status', 'overtime')->sum('variance_hours');
        $totalUndertime = abs($timesheets->where('status', 'undertime')->sum('variance_hours'));
        $netVariance = $totalOvertime - $totalUndertime;
        
        // Calculate expected hours (8 hours per entry)
        $expectedHours = $timesheets->count() * 8;
        
        // Get archived periods for the Archives button
        $archivedPeriods = Timesheet::getArchivedPeriods();
        
        return view('timesheets.index', compact(
            'timesheets', 
            'totalHours', 
            'totalOvertime', 
            'totalUndertime', 
            'netVariance', 
            'expectedHours',
            'currentPeriodLabel',
            'currentPeriod',
            'archivedPeriods'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'time_in' => 'required',
            'time_out' => 'required',
        ]);

        $calculation = Timesheet::calculateHours($validated['time_in'], $validated['time_out']);

        // Get cut-off period for this date
        $cutoffPeriod = Timesheet::getCurrentCutoffPeriod($validated['date']);

        Timesheet::create([
            'date' => $validated['date'],
            'time_in' => $validated['time_in'],
            'time_out' => $validated['time_out'],
            'hours_worked' => $calculation['hours'],
            'status' => $calculation['status'],
            'variance_hours' => $calculation['variance'],
            'cutoff_period' => $cutoffPeriod,
            'is_archived' => false,
        ]);

        return redirect()->route('timesheets.index')->with('success', 'Entry added successfully!');
    }

    public function update(Request $request, Timesheet $timesheet)
    {
        // Check if timesheet is archived
        if ($timesheet->is_archived) {
            return redirect()->route('timesheets.index')
                ->with('error', 'Cannot edit archived entries. Please contact administrator if changes are needed.');
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'time_in' => 'required',
            'time_out' => 'required',
        ]);

        $calculation = Timesheet::calculateHours($validated['time_in'], $validated['time_out']);
        
        // Update cut-off period if date changed
        $cutoffPeriod = Timesheet::getCurrentCutoffPeriod($validated['date']);

        $timesheet->update([
            'date' => $validated['date'],
            'time_in' => $validated['time_in'],
            'time_out' => $validated['time_out'],
            'hours_worked' => $calculation['hours'],
            'status' => $calculation['status'],
            'variance_hours' => $calculation['variance'],
            'cutoff_period' => $cutoffPeriod,
        ]);

        return redirect()->route('timesheets.index')->with('success', 'Entry updated successfully!');
    }

    public function destroy(Timesheet $timesheet)
    {
        // Check if timesheet is archived
        if ($timesheet->is_archived) {
            return redirect()->route('timesheets.index')
                ->with('error', 'Cannot delete archived entries. Please contact administrator if deletion is needed.');
        }

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

        fputcsv($handle, ['Date', 'Time In', 'Time Out', 'Hours Worked', 'Status', 'Variance', 'Cut-Off Period', 'Archived']);

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
                $varianceText,
                $timesheet->cutoff_period ?? 'N/A',
                $timesheet->is_archived ? 'Yes' : 'No'
            ]);
        }

        fputcsv($handle, ['', '', '', 'TOTAL:', $totalHours, '', '', '']);

        fclose($handle);
        exit;
    }

    /**
     * Process current cut-off period
     */
    public function processCutoff()
    {
        try {
            // Process the current cut-off period
            $summary = Timesheet::processCutoffEnd();
            
            return redirect()->route('timesheets.index')
                ->with('success', 'Cut-off period processed successfully!')
                ->with('cutoff_summary', $summary);
        } catch (\Exception $e) {
            return redirect()->route('timesheets.index')
                ->with('error', 'Failed to process cut-off: ' . $e->getMessage());
        }
    }

    /**
     * View archived cut-off periods
     */
    public function archives()
    {
        $archivedPeriods = Timesheet::getArchivedPeriods();
        
        return view('timesheets.archives', compact('archivedPeriods'));
    }

    /**
     * View specific archived period
     */
    public function showArchive($period)
    {
        $timesheets = Timesheet::getByPeriod($period);
        
        if ($timesheets->isEmpty()) {
            return redirect()->route('timesheets.archives')
                ->with('error', 'No records found for this period.');
        }
        
        $dateRange = Timesheet::getCutoffDateRange($period);
        $totalHours = $timesheets->sum('hours_worked');
        $totalOvertime = $timesheets->where('status', 'overtime')->sum('variance_hours');
        $totalUndertime = abs($timesheets->where('status', 'undertime')->sum('variance_hours'));
        $netVariance = $totalOvertime - $totalUndertime;
        $expectedHours = $timesheets->count() * 8;
        
        return view('timesheets.archive-detail', compact(
            'timesheets',
            'period',
            'dateRange',
            'totalHours',
            'totalOvertime',
            'totalUndertime',
            'netVariance',
            'expectedHours'
        ));
    }

    /**
     * Export archived period to CSV
     */
    public function exportArchive($period)
    {
        $timesheets = Timesheet::getByPeriod($period);
        $dateRange = Timesheet::getCutoffDateRange($period);
        $totalHours = $timesheets->sum('hours_worked');

        $filename = 'timesheet_archive_' . $period . '.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Header
        fputcsv($handle, ['Timesheet Archive - ' . $dateRange['label']]);
        fputcsv($handle, ['']);
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

        fputcsv($handle, ['']);
        fputcsv($handle, ['', '', 'TOTAL:', $totalHours . ' hrs']);

        fclose($handle);
        exit;
    }
}