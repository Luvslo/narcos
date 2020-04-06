<?php

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

$condMiddlewares = ['auth', 'is.allowed.access', 'user.has.character', 'log.user.activity'];
$condMiddlewares = App::isLocal() ? array_filter($condMiddlewares, 'notVerified') : $condMiddlewares;

Auth::routes(['verify' => true]);

// The routing group for this application. All controller should reside in this group (except for the Character controller).
Route::group(['middleware' => $condMiddlewares], function () {
    Route::get('/', 'HomeController@index');

    Route::view('/introduction', 'navigation.introduction')->name('introduction');
    Route::view('/documentation', 'navigation.documentation')->name('documentation');
    Route::get('/reset/password', 'PasswordController@now');

    Route::get('/profile/delete', 'ProfileController@deleteProfile');
    Route::get('/profile/{character}', 'ProfileController@getProfile');
    Route::post('/profile', 'ProfileController@updateProfile');

    Route::get('/messages/inbox', 'MessageController@getInbox');
    Route::get('/messages/outbox', 'MessageController@getOutbox');
    Route::get('/messages/compose/{character?}/{subject?}', 'MessageController@getCompose');
    Route::post('/messages/compose', 'MessageController@postCompose');
    Route::post('/messages/delete/all', 'MessageController@postDeleteAll');
    Route::get('/messages/delete/{id}', 'MessageController@postDelete');
    
    Route::get('/banking', 'BankController@getIndex');
    Route::post('/banking', 'BankController@postIndex');

    Route::get('/travel', 'TravelController@getIndex');
    Route::post('/travel', 'TravelController@postIndex');

    Route::get('/store', 'StoreController@getIndex');
    Route::post('/store/transport', 'StoreController@postTransport');
    Route::post('/store/weaponry', 'StoreController@postWeaponry');
    Route::post('/store/bullets', 'StoreController@postBullets');
    Route::post('/store/hide', 'StoreController@postHide');

    Route::get('/online-players', 'PlayersController@getOnlineIndex');
    Route::get('/all-players', 'PlayersController@getAllIndex');

    Route::view('/stats', 'menu.stats.index');

    Route::get('/trivial-crime', 'CrimeController@getIndex');
    Route::post('/trivial-crime', 'CrimeController@postIndex');

    Route::get('/organized-crime', 'OrganizedCrimeController@getIndex');
    Route::get('/organized-crime/join/{secret}', 'OrganizedCrimeController@getJoin');
    Route::post('/organized-crime/invite/{position}', 'OrganizedCrimeController@postInvite');
    Route::post('/organized-crime/remove/{character}', 'OrganizedCrimeController@postRemove');
    Route::post('/organized-crime/attempt', 'OrganizedCrimeController@postAttempt');

    Route::get('/narcotics-trade', 'NarcoticsController@getIndex');
    Route::post('/narcotics-trade/trade/{narcotic}', 'NarcoticsController@postTrade');

    Route::get('/kill', 'KillController@getIndex');
    Route::post('/kill', 'KillController@postIndex');

    Route::get('/numbers-game', 'NumbersGameController@getIndex');
    Route::post('/numbers-game', 'NumbersGameController@postIndex');

    Route::get('/roulette', 'RouletteController@getIndex');
    Route::post('/roulette', 'RouletteController@postIndex');

    Route::get('/map', 'MapController@getIndex');
    Route::post('/map/edit/{tile}', 'MapController@postEditTile');
    Route::post('/map/buy/{tile}', 'MapController@postTile');
    Route::get('/map/{tile}', 'MapController@getTile');
});

// SPECIAL CASES

// Character creation and such is the one state an user can land in when it has an account but no player yet. Therefore
// its the only controller outside the routing group above where all the regular middlewares apply. This is a special case.
// Still want to add middleware to this Controller, see the actual file.
Route::get('/character', 'CharacterController@index');
Route::get('/character/create', 'CharacterController@getCreate');
Route::post('/character/create', 'CharacterController@postCreate')->name('character.create');
Route::get('/character/death', 'CharacterController@getDeath');
Route::post('/character/death', 'CharacterController@postDeath')->name('character.release');

// Also a special case; the banned case
Route::view('/banned', 'banned')->middleware('is.not.allowed.access');
