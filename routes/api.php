<?php
//use Illuminate\Http\Request;

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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

$api = app('Dingo\Api\Routing\Router');

//管理后台
$api->version('v1', function ($api) {

    //$api->group(['namespace' => 'App\Api\Controllers', 'middleware' => ['client.changeAuthUserModel']], function ($api) {
    $api->group(['namespace' => 'App\Api\Controllers\Admin'], function ($api) {

        //登录授权
        $api->post('user/login', 'AuthController@authenticate');

        //同意协议
        $api->post('user/agree/rule', 'AuthController@agreeRule');

        //图形验证码
        $api->any('common/captcha', 'CommonController@captcha')->name('common.captcha');

        //发送短信验证码
        $api->any('common/send-sms-code', 'CommonController@sendSmsCode')->name('common.send_sms_code');

        //验证短信验证码
        $api->any('common/user-forget', 'CommonController@userForget')->name('common.user_forget');

        //重置用户密码
        $api->any('common/reset-user-pass', 'CommonController@resetUserPass')->name('common.reset_user_pass');

        //用户鉴权
        $api->group(['middleware' => ['admin_refresh_token', 'auth.admin']], function ($api) {




            $api->any('member/account-lists', 'MemberController@accountList')->name('admin.member.account.list');

            //通知消息
            $api->any('notice/lists', 'NoticeController@noticeList')->name('admin.notice.index');
            $api->any('notice/template/lists', 'NoticeController@noticeTemplateList')->name('admin.notice.template.index');
            $api->any('notice/template/save', 'NoticeController@noticeTemplateSave')->name('admin.notice.template.save');

            //管理员
            $api->any('user/list', 'AuthController@userList')->name('admin.user.index');//获取管理员列表
            $api->any('user/delete', 'AuthController@userDelete')->name('admin.user.delete');
            $api->any('user/register', 'AuthController@register')->name('admin.user.add');
            $api->any('user/me', 'AuthController@AuthenticatedUser')->name('admin.user.info');//获取用户信息
            $api->any('user/changeStatus', 'AuthController@changeAdminStatus')->name('admin.user.status');//更改管理员状态

            //角色权限列表
            $api->any('roles/lists', 'AuthController@roleList')->name('admin.roles.index');
            $api->any('roles/add', 'AuthController@roleAdd')->name('admin.roles.add');
            $api->any('roles/info', 'AuthController@roleInfo')->name('admin.roles.info');
            $api->any('roles/delete', 'AuthController@roleDelete')->name('admin.roles.delete');
            $api->any('roles/allRoles', 'AuthController@allRoles')->name('admin.roles.index');

            //只允许平台用户操作
            $api->group(['middleware' => ['auth.sys.user']], function ($api) {
                //会员
                $api->any('member/list', 'MemberController@memberList')->name('admin.member.index');
                $api->any('member/suggest/lists', 'MemberController@suggestList')->name('admin.member.suggest.list');
                $api->any('member/suggest/edit', 'MemberController@suggestEdit')->name('admin.member.suggest.edit');
                $api->any('member/search/lists', 'MemberController@searchList')->name('admin.member.search.list');
                $api->any('member/search/edit', 'MemberController@searchEdit')->name('admin.member.search.edit');
                $api->any('member/search/delete', 'MemberController@searchDelete')->name('admin.member.search.delete');

                //权限
                $api->any('permissions/lists', 'AuthController@permissionList')->name('admin.permissions.index');
                $api->any('permissions/allPermission', 'AuthController@allPermission')->name('admin.permissions.index');
                $api->any('permissions/menus', 'AuthController@menus')->name('admin.permissions.index');
                $api->any('permissions/info', 'AuthController@permissionInfo')->name('admin.permissions.info');
                $api->any('permissions/add', 'AuthController@permissionAdd')->name('admin.permissions.add');
                $api->any('permissions/delete', 'AuthController@permissionDelete')->name('admin.permissions.delete');
                $api->any('permissions/changeStatus', 'AuthController@changePermissionStatus')->name('admin.permissions.status');





                //资讯
                $api->any('news/lists', 'NewsController@newsList')->name('admin.news.index');
                $api->any('news/info', 'NewsController@newsInfo')->name('admin.news.info');
                $api->any('news/add', 'NewsController@newsAdd')->name('admin.news.add');
                $api->any('news/delete', 'NewsController@newsDelete')->name('admin.news.delete');
                $api->any('news/classify', 'NewsController@newsClassify')->name('admin.news.add');

                $api->any('news/lists/status', 'NewsController@setStatusEdit')->name('admin.news.status');
                $api->any('news/lists/cancel/status', 'NewsController@cancelStatusEdit')->name('admin.news.status');

                $api->any('news/lists/sort', 'NewsController@setSortEdit')->name('admin.news.sort');
                $api->any('news/lists/cancel/sort', 'NewsController@cancelSortEdit')->name('admin.news.sort');

                $api->any('classify/lists', 'NewsController@classifyList')->name('admin.news.classify.index');
                $api->any('classify/add', 'NewsController@classifyAdd')->name('admin.news.classify.add');
                $api->any('classify/delete', 'NewsController@classifyDelete')->name('admin.news.classify.delete');

                $api->any('classify/lists/status', 'NewsController@setClassifyStatusEdit')->name('admin.news.classify.status');
                $api->any('classify/lists/cancel/status', 'NewsController@cancelClassifyStatusEdit')->name('admin.news.classify.status');

                $api->any('news/activity/lists', 'NewsController@activityList')->name('admin.activity.index');
                $api->any('news/activity/add', 'NewsController@activityAdd')->name('admin.activity.add');
                $api->any('news/activity/delete', 'NewsController@activityDelete')->name('admin.activity.delete');
                $api->any('news/activity/join', 'NewsController@activityJoin')->name('admin.activity.join');

                $api->any('activity/lists/status', 'NewsController@setActivityStatusEdit')->name('admin.activity.status');
                $api->any('activity/lists/cancel/status', 'NewsController@cancelActivityStatusEdit')->name('admin.activity.status');

                $api->any('hotel/lists', 'HotelController@hotels')->name('admin.hotel.lists');
                $api->any('hotel/add', 'HotelController@Add')->name('admin.hotel.save');
                $api->any('hotel/info', 'HotelController@Info')->name('admin.hotel.info');
                $api->any('hotel/delete', 'HotelController@Delete')->name('admin.hotel.delete');
                //下拉框
                $api->any('hotel/province', 'HotelController@Province')->name('admin.hotel.province');
                $api->any('hotel/city', 'HotelController@City')->name('admin.hotel.city');
                $api->any('hotel/district', 'HotelController@District')->name('admin.hotel.district');
                $api->any('hotel/screens', 'HotelController@Screens')->name('admin.hotel.screens');
                $api->any('hotel/stars', 'HotelController@Stars')->name('admin.hotel.stars');
                //焦点图
                $api->any('focus/lists', 'SysController@FocusList')->name('admin.focus.list');
                $api->any('focus/lists/status', 'SysController@setFocusStatusEdit')->name('admin.focus.status');
                $api->any('focus/lists/cancel/status', 'SysController@cancelFocusStatusEdit')->name('admin.focus.status');
                $api->any('focus/add', 'SysController@FocusAdd')->name('admin.focus.add');
                $api->any('focus/type', 'SysController@FocusType')->name('admin.focus.type');
                $api->any('focus/delete', 'SysController@FocusDelete')->name('admin.focus.delete');


                //地区管理
                $api->any('system/province/lists', 'SysController@provinceList')->name('admin.region.list');
                $api->any('system/province/add', 'SysController@provinceAdd')->name('admin.region.add');
                $api->any('system/province/delete', 'SysController@provinceDelete')->name('admin.region.delete');

                $api->any('system/city/lists', 'SysController@cityList')->name('admin.region.list');
                $api->any('system/city/add', 'SysController@cityAdd')->name('admin.region.add');
                $api->any('system/city/delete', 'SysController@cityDelete')->name('admin.region.delete');

                $api->any('system/district/lists', 'SysController@districtList')->name('admin.region.list');
                $api->any('system/city/province/lists', 'SysController@cityProvinceList')->name('admin.region.list');
                $api->any('system/district/add', 'SysController@districtAdd')->name('admin.region.add');
                $api->any('system/district/delete', 'SysController@districtDelete')->name('admin.region.delete');

                //网站设置
                $api->any('system/website/configs', 'SysController@websiteConfigs')->name('admin.system.configs');
                $api->any('system/website/configs-save', 'SysController@websiteConfigsSave')->name('admin.system.configs');
            });

            //只允许商家用户操作
            $api->group(['middleware' => ['auth.shop.user']], function ($api) {


            });

        });

        //通用 不鉴权
        $api->group(['middleware' => ['admin_refresh_token']], function ($api) {

            //后台菜单
            $api->any('user/menus', 'AuthController@userMenusData');

            $api->any('user/session', 'AuthController@AuthenticatedUser');  //登录授权的用户信息

            $api->any('user/profile', 'AuthController@AuthenticatedUserProfile');  //登录授权的用户信息

            $api->any('user/save-profile', 'AuthController@AuthenticatedUserProfileSave');  //登录授权的用户信息

            $api->any('user/save-pwd', 'AuthController@AuthenticatedUserPwdSave');  //登录授权的用户信息


            //文件上传
            $api->any('upload/save', 'UploadFileController@save')->name('admin.upload.save');
            $api->any('upload/image-save', 'UploadFileController@ImageSave')->name('admin.upload.image.save');
            $api->any('upload/video-save', 'UploadFileController@VideoSave')->name('admin.upload.video.save');
            $api->any('upload/audio-save', 'UploadFileController@AudioSave')->name('admin.upload.audio.save');



            $api->any('common/dashboard', 'CommonController@dashboard')->name('common.dashboard');

            $api->any('common/banks', 'CommonController@banks')->name('common.banks');
            $api->any('common/bank/branch', 'CommonController@bankBranch')->name('common.banks');

            $api->any('notice/template/codes', 'NoticeController@noticeTemplateCodes');
            $api->any('notice/template/items', 'NoticeController@noticeTemplateItems');

        });

    });

});

//APP
$api->version('v1', function ($api) {

    $api->group(['namespace' => 'App\Api\Controllers\App', 'prefix' => 'v1'], function ($api) {

        $api->group(['middleware' => [], 'namespace' => 'Customer', 'prefix' => 'customer'], function ($api) {
            $api->any('member/send-sms-code', 'MemberController@sendSmsCode');

            $api->any('member/register', 'MemberController@register');
            $api->any('member/register-phone', 'MemberController@registerByPhone');

            $api->any('member/get-user-info', 'MemberController@getUserInfo');
            $api->any('member/edit-user-info', 'MemberController@editUserInfo');
            $api->any('member/logs', 'MemberController@MemberLogs');
            $api->any('member/shop-collect', 'MemberController@ShopCollect');
            $api->any('member/goods-collect', 'MemberController@GoodsCollect');
            $api->any('member/logs-list', 'MemberController@GoodsLogsList');
            $api->any('member/post-suggest', 'MemberController@postSuggest');
            $api->any('member/get-order-message', 'MemberController@getOrderMessage');




        });

        $api->group(['middleware' => [], 'namespace' => 'Promoter', 'prefix' => 'promoter'], function ($api) {

        });

    });

});
