<?php

Route::get('/media/{id}', 'FrenchFrogs\Media\Http\Controllers\MediaController@show')->name('media-show');
Route::get('/media/dl/{id}', 'FrenchFrogs\Media\Http\Controllers\MediaController@download')->name('media-dl');

Route::controller('schedule', 'FrenchFrogs\Scheduler\Http\Controllers\ScheduleController');

