<?php

use Illuminate\Support\Facades\Route;
use Waynelogic\FilamentCms\Http\Controllers\ImageResizerController;

Route::get('api/resize/{path}', [ImageResizerController::class, 'show'])
    ->where('path', '.*')
    ->name('resize');
