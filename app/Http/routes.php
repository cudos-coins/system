<?php

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use CC\User;

// public routes.
Route::group(['namespace' => 'API', 'prefix' => 'api'], function()
{
    Route::get('/', 'WelcomeController@index');

    Route::post('/access_token', ['as' => 'api.access_token.store', 'uses' => 'AccessController@store']);
});

// protected api routes.
Route::group(['middleware' => 'access.token', 'namespace' => 'API', 'prefix' => 'api'], function()
{
    Route::model('api_keys', 'CC\\APIKey');
    Route::model('transactions', 'CC\\Transaction');

    Route::bind('users', function ($value)
    {
        if (!$user = User::whereAliasOrId($value, $value)->first())
        {
            throw new NotFoundHttpException;
        } // if

        return $user;
    });

    Route::put('access_token', ['as' => 'api.access_token.update', 'uses' => 'AccessController@update']);

    Route::resource('api_keys', 'APIKeysController', ['only' => ['destroy', 'index', 'store', 'show']]);

    #Route::resource('reservations', 'ReservationsController');
    #Route::resource('reservations.confirm', 'Reservations\\ConfirmController');
    Route::resource('transactions', 'TransactionsController');
    Route::resource('transactions.confirm', 'Transactions\\ConfirmController', ['only' => 'store']);
    Route::resource('users', 'UsersController');
    Route::resource('users.coins', 'Users\\CoinsController');

    Route::delete('users/{users}/like', ['as' => 'api.users.like.destroy', 'uses' => 'Users\\LikeController@destroy']);
    Route::resource('users.like', 'Users\\LikeController', ['only' => ['store']]);
});
