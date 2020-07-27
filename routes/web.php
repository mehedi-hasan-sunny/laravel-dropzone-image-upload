<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('ImageUpload');
});
Route::post('image-upload', 'ImageController@upload')->name('image.upload');
Route::get('image-list', 'ImageController@list')->name('image.list');
Route::post('image-remove', 'ImageController@remove')->name('image.remove');

