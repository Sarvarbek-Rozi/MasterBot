<?php

use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

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

Route::get('/', 'TelegramController@setWebhook');
Route::get('/getWebhook', 'TelegramController@getWebhook');

Route::post(Telegram::getAccessToken(), 'TelegramController@action')->name('telegram')->middleware(\App\Http\Middleware\OnlyMessage::class);
//Route::post(Telegram::getAccessToken(), 'TelegramController@action')->name('telegram');
