<?php

use App\Http\Controllers\TimesheetController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TimesheetController::class, 'index'])->name('timesheets.index');
Route::post('/timesheets', [TimesheetController::class, 'store'])->name('timesheets.store');
Route::put('/timesheets/{timesheet}', [TimesheetController::class, 'update'])->name('timesheets.update');
Route::delete('/timesheets/{timesheet}', [TimesheetController::class, 'destroy'])->name('timesheets.destroy');
Route::get('/timesheets/export', [TimesheetController::class, 'exportCsv'])->name('timesheets.export');

// Export
Route::get('/timesheets/export', [TimesheetController::class, 'exportCsv'])->name('timesheets.export');

// Cut-off management
Route::post('/timesheets/cutoff', [TimesheetController::class, 'processCutoff'])->name('timesheets.cutoff');

// Archives
Route::get('/timesheets/archives', [TimesheetController::class, 'archives'])->name('timesheets.archives');
Route::get('/timesheets/archives/{period}', [TimesheetController::class, 'showArchive'])->name('timesheets.archive.show');
Route::get('/timesheets/archives/{period}/export', [TimesheetController::class, 'exportArchive'])->name('timesheets.archive.export');