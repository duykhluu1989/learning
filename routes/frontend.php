<?php

Route::group(['namespace' => 'Frontend', 'middleware' => ['setVisitStartTime', 'visitorCount', 'setReferral', 'locale']], function() {

    Route::group(['middleware' => 'guest'], function() {

        Route::post('login', ['middleware' => 'throttle:5,30', 'uses' => 'UserController@login']);

        Route::post('loginWithFacebook', ['middleware' => 'throttle:5,30', 'uses' => 'UserController@loginWithFacebook']);

        Route::post('register', ['middleware' => 'throttle:5,30', 'uses' => 'UserController@register']);

        Route::post('retrievePassword', ['middleware' => 'throttle:5,30', 'uses' => 'UserController@retrievePassword']);

        Route::get('loginWithToken/{token}', ['middleware' => 'throttle:5,30', 'uses' => 'UserController@loginWithToken']);

        Route::post('registerTeacher', ['middleware' => 'throttle:5,30', 'uses' => 'UserController@registerTeacher']);

    });

    Route::group(['middleware' => ['auth', 'access']], function() {

        Route::get('logout', 'UserController@logout');

        Route::match(['get', 'post'], 'account', 'UserController@editAccount');

        Route::get('account/order', 'UserController@adminOrder');

        Route::get('account/course', 'UserController@adminCourse');

        Route::match(['get', 'post'], 'order', 'OrderController@placeOrder');

        Route::post('discount', 'OrderController@useDiscountCode');

        Route::get('thankYou', 'OrderController@thankYou');

        Route::get('learnCourseNow/{id}/{slug}', 'OrderController@learnCourseNow');

        Route::get('course/{id}/{slug}/item/{number}', 'CourseController@detailCourseItem');

        Route::get('source/{token}', 'CourseController@getSource');

        Route::post('reviewCourse/{id}/{slug}', 'CourseController@reviewCourse');

        Route::group(['middleware' => ['collaboratorAccess']], function() {

            Route::match(['get', 'post'], 'collaborator', 'CollaboratorController@editCollaborator');

            Route::get('collaborator/course', 'CollaboratorController@adminCourse');

            Route::get('collaborator/generateCoupon', 'CollaboratorController@generateDiscount');

            Route::get('collaborator/course/{id}/getLink', 'CollaboratorController@getLinkCourse');

            Route::get('collaborator/transaction', 'CollaboratorController@adminCollaboratorTransaction');

        });

    });

    Route::post('registerCollaborator', ['middleware' => 'throttle:5,30', 'uses' => 'UserController@registerCollaborator']);

    Route::get('district', 'OrderController@getListDistrict');

    Route::get('/', 'HomeController@home');

    Route::get('language/{locale}', 'HomeController@language');

    Route::post('refreshCsrfToken', 'HomeController@refreshCsrfToken');

    Route::get('category/{id}/{slug}/{sort?}', 'CourseController@detailCategory');

    Route::get('previewCourse/{id}/{slug}', 'CourseController@previewCourse');

    Route::get('course/{sort?}', 'CourseController@adminCourse');

    Route::get('course/{id}/{slug}', 'CourseController@detailCourse');

    Route::get('course/{id}/{slug}/review', 'CourseController@detailCourseReview');

    Route::get('newCourseAndNews', 'CourseController@newCourseAndNews');

    Route::get('search', 'CourseController@searchCourse');

    Route::get('cart', 'OrderController@editCart');

    Route::get('cart/addItem', 'OrderController@addCartItem');

    Route::get('cart/deleteItem', 'OrderController@deleteCartItem');

    Route::get('page/{id}/{slug}', 'PageController@detailPage');

    Route::get('expert', 'ArticleController@adminExpert');

    Route::get('expert/{id}', 'ArticleController@detailExpert');

    Route::get('article/{id}/{slug}', 'ArticleController@detailArticle');

    Route::get('news', 'NewsController@adminCategory');

    Route::get('certificate', 'CertificateController@adminCertificate');

    Route::post('registerCertificate', 'CertificateController@registerCertificate');

    Route::get('advice/{type?}', 'CaseAdviceController@adminCaseAdvice');

    Route::get('advice/{id}/{slug}', 'CaseAdviceController@detailCaseAdvice');

});