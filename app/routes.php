<?php
Route::get('/', function()  {
	return View::make('hello');
});
Route::get('api', 'ApiController@getIndex');
Route::get('api/{guid}/balance',                array('as' => 'balance',                'uses' => 'ApiController@balance'));
Route::get('api/{guid}/core-balance',           array('as' => 'core-balance',           'uses' => 'ApiController@coreBalance'));
Route::get('api/{guid}/address-balance',        array('as' => 'addressBalance',         'uses' => 'ApiController@addressBalance')); // not implemented
Route::get('api/{guid}/validate-transaction',   array('as' => 'validateTransaction',    'uses' => 'ApiController@validateTransaction'));
Route::get('api/{guid}/validate-address',       array('as' => 'validateAddress',        'uses' => 'ApiController@validateAddress'));
Route::get('api/{guid}/new-address',            array('as' => 'newAddress',             'uses' => 'ApiController@newAddress'));
Route::get('api/{guid}/tx-confirmations',       array('as' => 'txConfirmations',        'uses' => 'ApiController@txConfirmations'));
Route::get('api/{guid}/payment',                array('as' => 'payment',                'uses' => 'ApiController@payment'));
Route::any('api/{guid}/sendmany',               array('as' => 'sendmany',               'uses' => 'ApiController@sendmany'));
Route::any('api/{guid}/list-unspent',           array('as' => 'listUnspent',            'uses' => 'ApiController@listUnspent'));

Route::get('api/callback',      array('as' => 'callback',       'uses' => 'ApiController@callback'));
Route::get('api/receive',       array('as' => 'receive',        'uses' => 'ApiController@receive'));
Route::get('api/blocknotify',   array('as' => 'blocknotify',    'uses' => 'ApiController@blocknotify'));