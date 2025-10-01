<?php

use App\Http\Controllers\TimesheetController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TimesheetController::class, 'index'])->name('timesheets.index');
Route::post('/timesheets', [TimesheetController::class, 'store'])->name('timesheets.store');
Route::put('/timesheets/{timesheet}', [TimesheetController::class, 'update'])->name('timesheets.update');
Route::delete('/timesheets/{timesheet}', [TimesheetController::class, 'destroy'])->name('timesheets.destroy');
Route::get('/timesheets/export', [TimesheetController::class, 'exportCsv'])->name('timesheets.export');