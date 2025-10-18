<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return to_route('people.index');
});

Route::controller(App\Http\Controllers\PersonController::class)->group(function () {
    Route::get('/people', 'index')->name('people.index');
    Route::post('/people', 'store')->name('people.store');
    Route::delete('/people/{id}', 'delete')->name('people.delete');
    Route::post('/people/move-descendants', 'moveDescendants')->name('people.moveDescendants');
    Route::get('/people/level/{id}', 'getLevelPerson')->name('people.getLevelPerson');
    Route::get('/people/depth', 'getMaxDepth')->name('people.getMaxDepth');
    Route::get('/people/descendants/{id}', 'getAmountDescendants')->name('people.getAmountDescendants');
    Route::get('/people/dfs/{id}', 'getDFS')->name('people.getDFS');
    Route::get('/people/bfs/{id}', 'getBFS')->name('people.getBFS');
});