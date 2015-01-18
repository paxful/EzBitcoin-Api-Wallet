<?php
Route::get('/', function()  {
	return View::make('hello');
});
Route::get('{guid}/balance',                array('as' => 'balance',                'uses' => 'ApiController@balance'));
Route::get('{guid}/address-balance',        array('as' => 'addressBalance',         'uses' => 'ApiController@addressBalance')); // not implemented
Route::get('{guid}/validate-transaction',   array('as' => 'validateTransaction',    'uses' => 'ApiController@validateTransaction'));
Route::get('{guid}/validate-address',       array('as' => 'validateAddress',        'uses' => 'ApiController@validateAddress'));
Route::get('{guid}/new-address',            array('as' => 'newAddress',             'uses' => 'ApiController@newAddress'));
Route::get('{guid}/tx-confirmations',       array('as' => 'txConfirmations',        'uses' => 'ApiController@txConfirmations'));
Route::get('{guid}/payment',                array('as' => 'payment',                'uses' => 'ApiController@payment'));

Route::get('callback',      array('as' => 'callback',       'uses' => 'ApiController@callback'));
Route::get('receive',       array('as' => 'receive',        'uses' => 'ApiController@receive'));
Route::get('blocknotify',   array('as' => 'blocknotify',    'uses' => 'ApiController@blocknotify'));