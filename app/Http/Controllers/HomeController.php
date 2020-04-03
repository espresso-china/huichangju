<?php

namespace App\Http\Controllers;

use App\Helpers\ResultHelper;
use App\Helpers\SettingHelper;
use App\Helpers\SmsHelper;
use App\Helpers\WeChatHelper;
use App\Repositories\HotelRepository;
use App\Repositories\NewsRepository;
use Illuminate\Http\Request;
use Carbon\Carbon;
use EasyWeChat\Foundation\Application;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    private $news,$hotel;

    public function __construct(NewsRepository $news,HotelRepository $hotel)
    {
        $this->news = $news;
        $this->hotel = $hotel;
    }

    public function index()
    {

        return view('front.home.index', $this->view_data);
    }

    public function newsInfo($id)
    {
        $this->view_data['info'] = $info = $this->news->find($id);
        if($info->url){
            return redirect($info->url);
        }
        return view('front.news.info', $this->view_data);
    }

    public function about(){
        $this->view_data['content'] =SettingHelper::getConfigValue('ABOUT_US');
        return view('front.mini.info', $this->view_data);
    }

    public function rule(){
        $this->view_data['content'] =SettingHelper::getConfigValue('USE_RULE');
        return view('front.mini.rule', $this->view_data);
    }

    public function franchisee(){
        $this->view_data['content'] =SettingHelper::getConfigValue('ABOUT_US');
        return view('front.mini.franchisee', $this->view_data);
    }

    public function cooperation(){
        return view('front.mini.cooperation', $this->view_data);
    }

    public function map(){
        return view('front.mini.map', $this->view_data);
    }
    public function mapInfo(){
        return view('front.mini.mapInfo', $this->view_data);
    }
    public function pintuanRule(){
        return view('front.mini.pintuan_rule', $this->view_data);
    }

    public function assemble(Request $request)
    {
        //return view('front.assemble.index', $this->view_data);
        return redirect()->to('/h5/goods/info')->withInput($request->input());
    }

}
