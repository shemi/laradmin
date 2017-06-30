<?php

$namespacePrefix = '\\'.config('laradmin.controllers.namespace').'\\';

Route::get('login', ["uses" => "{$namespacePrefix}AuthController@showLoginForm", "as" => "login"]);
Route::post('login', ["uses" => "{$namespacePrefix}AuthController@login", "as" => "post.login"]);

Route::get('/', ["uses" => "{$namespacePrefix}DashboardController@index", "as" => "dashboard"]);

Route::group([
    "as" => "api.",
    "namespace" => "{$namespacePrefix}Api\\",
    "prefix" => "api/v1"],
    function() {

    Route::get("/", ['uses' => 'ApiController@base', 'as' => 'base']);

});