<?php

Route::get('/media/{id}', 'FrenchFrogs\App\Http\Controllers\MediaController@show')->name('media-show');
Route::get('/media/dl/{id}', 'FrenchFrogs\App\Http\Controllers\MediaController@download')->name('media-dl');

