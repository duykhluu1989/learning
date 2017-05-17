<?php

Route::group(['namespace' => 'Backend'], function() {

    Route::group(['middleware' => 'guest'], function() {

        Route::get('login', 'UserController@login');

        Route::post('login', ['middleware' => 'throttle:5,30', 'uses' => 'UserController@login']);

    });

    Route::group(['middleware' => ['auth', 'access']], function() {

        Route::get('logout', 'UserController@logout');

        Route::get('/', 'HomeController@home');

        Route::match(['get', 'post'], 'account/edit', 'UserController@editAccount');

    });

    Route::group(['middleware' => ['auth', 'access', 'permission']], function() {

        Route::get('user', 'UserController@adminUser');

        Route::get('userStudent', 'UserController@adminUserStudent');

        Route::match(['get', 'post'], 'user/create', 'UserController@createUser');

        Route::match(['get', 'post'], 'user/{id}/edit', 'UserController@editUser');

        Route::get('role', 'RoleController@adminRole');

        Route::match(['get', 'post'], 'role/create', 'RoleController@createRole');

        Route::match(['get', 'post'], 'role/{id}/edit', 'RoleController@editRole');

        Route::get('role/{id}/delete', 'RoleController@deleteRole');

        Route::get('role/controlDelete', 'RoleController@controlDeleteRole');

        Route::match(['get', 'post'], 'setting', 'SettingController@adminSetting');

    });

});
