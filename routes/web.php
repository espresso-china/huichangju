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

//首页
Route::get('/', ['as' => 'front.home.index', 'uses' => 'HomeController@index']);
Route::get('/show/{id}', ['as' => 'front.home.info', 'uses' => 'HomeController@Info']);
Route::any('/search', ['as' => 'front.home.search', 'uses' => 'HomeController@Search']);


//H5站点
Route::namespace('H5')->prefix('h5')->group(function () {

    Route::any('/wechat/oauth-callback', ['as' => 'wechat.oauth.callback', 'uses' => 'WeChatController@oauthCallback']);

    Route::post('/wechat/getUserInfo', ['as' => 'wechat.member.getUserInfo', 'uses' => 'WeChatController@getUserInfo']);
    Route::namespace('Customer')->prefix('customer')->group(function () {

        Route::any('/member/my-profile', ['as' => 'h5.customer.member.my_profile', 'uses' => 'MemberController@myProfile']);
        Route::any('/member/my-order', ['as' => 'h5.customer.member.my_order', 'uses' => 'MemberController@myOrder']);
        //砍价
        Route::any('/member/bargain/rebate', ['as' => 'h5.customer.member.bargain.rebate', 'uses' => 'MemberController@rebate']);
        Route::any('/member/bargain/entry', ['as' => 'h5.customer.member.bargain.entry', 'uses' => 'MemberController@entry']);
        Route::any('/member/bargain/info', ['as' => 'h5.customer.member.bargain.info', 'uses' => 'MemberController@entryInfo']);
        Route::any('/member/bargain/list', ['as' => 'h5.customer.member.bargain.list', 'uses' => 'MemberController@entryList']);
        Route::any('/member/bargain/rule', ['as' => 'h5.customer.member.bargain.rule', 'uses' => 'MemberController@entryRule']);
        //用户注册
        Route::any('/member/register/index', ['as' => 'h5.customer.member.register.index', 'uses' => 'MemberController@register']);

        //活动中心
        Route::any('/activity/list', ['as' => 'h5.customer.activity.list', 'uses' => 'MemberController@activity']);
        Route::any('/activity/info', ['as' => 'h5.customer.activity.info', 'uses' => 'MemberController@activityInfo']);
        Route::any('/activity/signup', ['as' => 'h5.customer.activity.signup', 'uses' => 'MemberController@activitySignup']);

        //成为团长
        Route::any('/apply/index', ['as' => 'h5.customer.apply.index', 'uses' => 'MemberController@applyIndex']);

        //在线登记
        Route::any('/franchisee/rules', ['as' => 'h5.customer.franchisee.rules', 'uses' => 'MemberController@rules']);
        Route::any('/franchisee/apply', ['as' => 'h5.customer.franchisee.apply', 'uses' => 'MemberController@apply']);
        Route::any('/franchisee/confirm', ['as' => 'h5.customer.franchisee.confirm', 'uses' => 'MemberController@confirm']);
        //设置
        Route::any('/member/set', ['as' => 'h5.customer.member.set', 'uses' => 'MemberController@set']);
        Route::any('/member/about/suggest', ['as' => 'h5.customer.member.about.suggest', 'uses' => 'MemberController@suggest']);
        Route::any('/member/about/abouts', ['as' => 'h5.customer.member.about.abouts', 'uses' => 'MemberController@abouts']);

        Route::any('/member/register-by-phone', ['as' => 'h5.customer.member.register_by_phone', 'uses' => 'MemberController@registerByPhone']);
        Route::any('/member/send-sms-code', ['as' => 'h5.customer.member.send_sms_code', 'uses' => 'MemberController@sendSmsCode']);

        Route::any('/order/submit-goods-order', ['as' => 'h5.customer.order.submit_goods_order', 'uses' => 'OrderController@submitGoodsOrder']);
        Route::any('/order/get-order-prepay', ['as' => 'h5.customer.order.get_order_prepay', 'uses' => 'OrderController@getOrderPrePay']);
        Route::any('/order/pay-goods-order', ['as' => 'h5.customer.order.pay_goods_order', 'uses' => 'OrderController@payGoodsOrder']);

    });

    Route::namespace('Promoter')->prefix('promoter')->group(function () {
        Route::any('/promoter/create-promoter-record', ['as' => 'h5.promoter.create_promoter_record', 'uses' => 'PromoterController@createPromoterRecord']);

    });

    Route::any('/goods/info/{id}', ['as' => 'h5.goods.info', 'uses' => 'GoodsController@info']);
    Route::any('/goods/danmu/{id}', ['as' => 'h5.goods.danmu', 'uses' => 'GoodsController@danmu']);
    Route::any('/goods/getSkuInfo', ['as' => 'h5.customer.goods.get-sku-info', 'uses' => 'GoodsController@getSkuInfo']);
});
