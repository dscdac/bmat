<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::post("/add_channel", array("as"=>"api.add_channel", "uses"=>"ApiController@addChannel"));
Route::post("/add_performer", array("as"=>"api.add_performer", "uses"=>"ApiController@addPerformer"));
Route::post("/add_song", array("as"=>"api.add_song", "uses"=>"ApiController@addSong"));
Route::post("/add_play", array("as"=>"api.add_play", "uses"=>"ApiController@addPlay"));

Route::get("/get_song_plays", array("as"=>"api.get_song_plays", "uses"=>"ApiController@getSongPlays"));
Route::get("/get_channel_plays", array("as"=>"api.get_channel_plays", "uses"=>"ApiController@getChannelPlays"));
Route::get("/get_top", array("as"=>"api.get_top", "uses"=>"ApiController@getTop"));
