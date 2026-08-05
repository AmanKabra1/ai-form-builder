<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\ImportController;
use App\Livewire\FormBuilder;
use App\Livewire\FormSubmit;
use App\Livewire\ImportPreview;
use App\Livewire\SubmissionsList;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('forms.index'))->middleware('auth');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard',                   fn() => redirect()->route('forms.index'))->name('dashboard');
    Route::get('/forms',                       [FormController::class, 'index'])->name('forms.index');
    Route::get('/forms/create',                FormBuilder::class)->name('forms.create');
    Route::get('/forms/{formId}/edit',         FormBuilder::class)->name('forms.edit');
    Route::delete('/forms/{form}',             [FormController::class, 'destroy'])->name('forms.destroy');
    Route::get('/forms/{form}/submissions',    SubmissionsList::class)->name('forms.submissions');
    Route::get('/forms/{form}/export',         [FormController::class, 'exportCsv'])->name('forms.export');
    Route::get('/forms/{form}/ai-status',      [FormController::class, 'aiStatus'])->name('forms.ai-status');
    Route::post('/forms/{form}/rollback/{version}', [FormController::class, 'rollback'])->name('forms.rollback');
    Route::get('/import',                      ImportPreview::class)->name('forms.import');
    Route::view('/profile', 'profile')->name('profile');
});

// Public form fill (no auth required)
Route::get('/f/{slug}', FormSubmit::class)->name('forms.fill');

require __DIR__ . '/auth.php';
