<?php


// DATATABLE
Route::get('/ff/datatable/{token}', 'TableController@datatable')->name('datatable');
Route::get('/ff/datatable/{token}/export', 'TableController@export')->name('datatable.export');
Route::post('/ff/datatable/{token}', 'TableController@edit');

// FORM
Route::post('/ff/form/{token}', 'FormController@modal')->name('form.modal');
