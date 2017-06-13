<?php

$namespacePrefix = '\\'.config('laradmin.controllers.namespace').'\\';

Route::get('login', ["uses" => "{$namespacePrefix}LaradminAuthController@showLoginForm", "as" => "login"]);
Route::post('login', ["uses" => "{$namespacePrefix}LaradminAuthController@login", "as" => "post.login"]);

