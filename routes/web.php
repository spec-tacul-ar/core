<?php

use Spectacular\Core\Http\Controllers\ExportController;
use Spectacular\Core\Http\Middleware\TidyHtml;
use Illuminate\Support\Facades\Route;

Route::controller(ExportController::class)->group(function () {
    Route::get('export/{project}/html', 'html')->name('export.html')->middleware(TidyHtml::class);
    Route::get('export/{project}/markdown', 'markdown')->name('export.markdown');
    Route::get('export/{project}/json', 'json')->name('export.json');
});

Route::view('/{any?}', 'app')->where('any', '.*');
