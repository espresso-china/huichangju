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


//H5站点
Route::namespace('H5')->prefix('h5')->group(function () {
    Route::any('/wechat/oauth-callback', ['as' => 'wechat.oauth.callback', 'uses' => 'WeChatController@oauthCallback']);

    Route::post('/wechat/getUserInfo', ['as' => 'wechat.member.getUserInfo', 'uses' => 'WeChatController@getUserInfo']);
    Route::namespace('Customer')->prefix('customer')->group(function () {
        Route::any('/member/my-profile', ['as' => 'h5.customer.member.my_profile', 'uses' => 'MemberController@myProfile']);
        Route::any('/member/my-order', ['as' => 'h5.customer.member.my_order', 'uses' => 'MemberController@myOrder']);

    });
    Route::any('/', ['as' => 'h5.hotel.index', 'uses' => 'HotelController@index']);
    Route::any('/lists', ['as' => 'h5.hotel.lists', 'uses' => 'HotelController@lists']);
    Route::any('/info/{id}', ['as' => 'h5.hotel.info', 'uses' => 'HotelController@info']);

});
