<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/1/25
 * Time: 10:44 AM
 */

namespace App\Api\Controllers\Admin;

use App\Model\Attachment;
use App\Repositories\AttachmentRepository;
use Illuminate\Http\Request;
use File, Image;
use Illuminate\Support\Facades\Storage;

class UploadFileController extends BaseController
{
    private $attachment;

    public function __construct(AttachmentRepository $attachment)
    {

        parent::__construct();
        $this->attachment = $attachment;
    }


    public function save(Request $request)
    {
        $file = $request->file('file');
        $type = $request->input('type');
        $types = array_keys(Attachment::getTypes());

        if (!in_array($type, $types)) {
            return response()->json(['success' => false, 'msg' => '类型非法']);
        }
        if (empty($file)) {
            return response()->json(['success' => false, 'msg' => '空文件']);
        }
        if (!$file->isValid()) {
           // return response()->json(['success' => false, 'msg' => '文件上传出错']);
        }
        $extension = $file->getClientOriginalExtension();
        if ($type == 'shop') {
            $types = explode(',', 'jpg,png,gif,jpeg,pdf,doc,docx,mp3');//定义检查的文件类型
        } else {
            $types = explode(',', env('ALLOW_UPLOAD_FILE_SUFFIX', 'jpg,png,gif,jpeg'));//定义检查的文件类型
        }

        if (!in_array(strtolower($extension), $types)) {
            return response()->json(['success' => false, 'msg' => '文件类型非法']);
        }
        \Log::info($request->hasFile('audio'));
        if ($request->hasFile('file')) {
            $tmpPath = 'uploads' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . date('Ymd', time()) . DIRECTORY_SEPARATOR;
            $uploadDir = public_path($tmpPath); //上传地址

            $disk = Storage::disk('qiniu');
            $qiniu_url = $disk->put($type, $file);
            $qiniu_url = env('QINIU_DOMAIN', 'http://fm.0755fc.com/') . $qiniu_url;
            //. '?imageMogr2/auto-orient/thumbnail/800x800/blur/1x0/quality/100'

            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir . 'small', 0777, true);
                File::makeDirectory($uploadDir . 'large', 0777, true);
                File::makeDirectory($uploadDir . 'orig', 0777, true);
            }
            $newName = md5(date('ymdhis')) . "." . $extension;
            $attachment = [
                'filesize' => $file->getClientSize(),
                'original_filename' => $file->getClientOriginalName()
            ];
            $orig = $tmpPath . 'orig' . DIRECTORY_SEPARATOR . $newName;
            $big = $tmpPath . 'large' . DIRECTORY_SEPARATOR . $newName;
            $small = $tmpPath . 'small' . DIRECTORY_SEPARATOR . $newName;
            //移动文件
            $result = $file->move($uploadDir . 'orig', $newName);
            $is_img = strpos($result->getMimeType(), 'image') !== false ? 1 : 0;
            if ($is_img) {
                Image::make($result)->widen(800)->save($big);
                Image::make($result)->widen(400)->save($small);
            }

            if ($result) {
                $attachment += [
                    'basename' => $result->getBasename(),
                    'filename' => $result->getFilename(),
                    'filepath' => $tmpPath,
                    'fileext' => $result->getExtension(),
                    'mime' => $result->getMimeType(),
                    'isimage' => $is_img,
                    'url' => $orig,
                    'qiniu_url' => $qiniu_url,
                    'ip' => $request->ip(),
                    'created_at' => time(),
                    'updated_at' => time()
                ];
                $aid = $this->attachment->store($attachment);
                $attachment += [
                    'aid' => $aid,
                    'large' => $big,
                    'small' => $small
                ];
                return response()->json(['success' => true, 'data' => $attachment, 'msg' => '上传成功']);
            } else {
                return response()->json(['success' => false, 'msg' => '文件写入失败']);
            }
        } else {
            return response()->json(['success' => false, 'msg' => '文件为空']);
        }
    }

    public function ImageSave(Request $request)
    {
        $file = $request->file('file');
        $type = 'edit';

        if (empty($file)) {
            return response()->json(['success' => false, 'msg' => '空文件']);
        }
        if (!$file->isValid()) {
            return response()->json(['success' => false, 'msg' => '文件上传出错']);
        }
        $extension = $file->getClientOriginalExtension();
        $types = explode(',', env('ALLOW_UPLOAD_FILE_SUFFIX', 'jpg,png,gif'));//定义检查的文件类型

        if (!in_array(strtolower($extension), $types)) {
            return response()->json(['success' => false, 'msg' => '文件类型非法']);
        }

        if ($request->hasFile('file')) {
            $tmpPath = 'uploads' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . date('Ymd', time()) . DIRECTORY_SEPARATOR;
            $uploadDir = public_path($tmpPath); //上传地址

            $disk = Storage::disk('qiniu');
            $qiniu_url = $disk->put($type, $file);
            $qiniu_url = env('QINIU_DOMAIN', 'http://fm.0755fc.com/') . $qiniu_url ;
            //. '?imageMogr2/auto-orient/thumbnail/800x800/blur/1x0/quality/100'

            if (!File::exists($uploadDir)) {
                File::makeDirectory($uploadDir . 'small', 0777, true);
                File::makeDirectory($uploadDir . 'large', 0777, true);
                File::makeDirectory($uploadDir . 'orig', 0777, true);
            }
            $newName = md5(date('ymdhis')) . "." . $extension;
            $attachment = [
                'filesize' => $file->getClientSize(),
                'original_filename' => $file->getClientOriginalName()
            ];
            $orig = $tmpPath . 'orig' . DIRECTORY_SEPARATOR . $newName;
            $big = $tmpPath . 'large' . DIRECTORY_SEPARATOR . $newName;
            $small = $tmpPath . 'small' . DIRECTORY_SEPARATOR . $newName;
            //移动文件
            $result = $file->move($uploadDir . 'orig', $newName);
            $is_img = strpos($result->getMimeType(), 'image') !== false ? 1 : 0;
            if ($is_img) {
                Image::make($result)->widen(800)->save($big);
                Image::make($result)->widen(400)->save($small);
            }

            if ($result) {
                $attachment += [
                    'basename' => $result->getBasename(),
                    'filename' => $result->getFilename(),
                    'filepath' => $tmpPath,
                    'fileext' => $result->getExtension(),
                    'mime' => $result->getMimeType(),
                    'isimage' => $is_img,
                    'url' => $orig,
                    'ip' => $request->ip(),
                    'created_at' => time(),
                    'updated_at' => time()
                ];
                $this->attachment->store($attachment);

                $data = [
                    "src" => asset($qiniu_url)
                ];

                return response()->json(['code' => 0, 'data' => $data, 'msg' => '上传成功']);
            } else {
                return response()->json(['code' => 1, 'msg' => '文件写入失败']);
            }
        } else {
            return response()->json(['code' => 1, 'msg' => '文件为空']);
        }
    }

    public function VideoSave(Request $request)
    {
        $file = $request->file('file');
        $type = 'edit';

        if (empty($file)) {
            return response()->json(['success' => false, 'msg' => '空文件']);
        }
        if (!$file->isValid()) {
            return response()->json(['success' => false, 'msg' => '文件上传出错']);
        }
        $extension = $file->getClientOriginalExtension();
        $types = explode(',', 'mp4,flv,avi,rm,rmvb');//定义检查的文件类型

        if (!in_array(strtolower($extension), $types)) {
            return response()->json(['success' => false, 'msg' => '文件类型非法']);
        }

        if ($request->hasFile('file')) {
            $tmpPath = 'uploads' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . date('Ymd', time()) . DIRECTORY_SEPARATOR;
            $uploadDir = public_path($tmpPath); //上传地址

            $newName = md5(date('ymdhis')) . "." . $extension;
            $attachment = [
                'filesize' => $file->getClientSize(),
                'original_filename' => $file->getClientOriginalName()
            ];
            $video = $tmpPath . 'video' . DIRECTORY_SEPARATOR . $newName;
            //移动文件
            $result = $file->move($uploadDir . 'video', $newName);
            $is_img = 0;


            if ($result) {
                $attachment += [
                    'basename' => $result->getBasename(),
                    'filename' => $result->getFilename(),
                    'filepath' => $tmpPath,
                    'fileext' => $result->getExtension(),
                    'mime' => $result->getMimeType(),
                    'isimage' => $is_img,
                    'url' => $video,
                    'ip' => $request->ip(),
                    'created_at' => time(),
                    'updated_at' => time()
                ];
                $this->attachment->store($attachment);


                $data = [
                    "src" => asset($video)
                ];

                return response()->json(['code' => 0, 'data' => $data, 'msg' => '上传成功']);
            } else {
                return response()->json(['code' => 1, 'msg' => '文件写入失败']);
            }
        } else {
            return response()->json(['code' => 1, 'msg' => '文件为空']);
        }
    }

    public function AudioSave(Request $request){
        $file = $request->file('file');
        $type = 'audio';

        if (empty($file)) {
            return response()->json(['success' => false, 'msg' => '空文件']);
        }
        if (!$file->isValid()) {
          //  return response()->json(['success' => false, 'msg' => '文件上传出错']);
        }
        $extension = $file->getClientOriginalExtension();
        $types = explode(',', 'mp3');//定义检查的文件类型

        if (!in_array(strtolower($extension), $types)) {
            return response()->json(['success' => false, 'msg' => '文件类型非法']);
        }


        $tmpPath = 'uploads' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . date('Ymd', time()) . DIRECTORY_SEPARATOR;
        $uploadDir = public_path($tmpPath); //上传地址



        $newName = md5(date('ymdhis')) . "." . $extension;
        $attachment = [
            'filesize' => $file->getClientSize(),
            'original_filename' => $file->getClientOriginalName()
        ];
        $audio = $tmpPath . 'audio' . DIRECTORY_SEPARATOR . $newName;
        //移动文件
       // $result = $file->move($uploadDir . 'audio', $newName);

        $disk = Storage::disk('qiniu');
        $qiniu_url = $disk->put($type, $file);
        $qiniu_url = env('QINIU_DOMAIN', 'http://fm.0755fc.com/') . $qiniu_url ;

        $is_img = 0;


        if ($qiniu_url) {
            $attachment += [
                'basename' => $file->getBasename(),
                'filename' => $file->getFilename(),
                'filepath' => $tmpPath,
                'fileext' => $extension,
                'mime' => $file->getMimeType(),
                'isimage' => $is_img,
                'url' => $qiniu_url,
                'qiniu_url' => $qiniu_url,
                'ip' => $request->ip(),
                'created_at' => time(),
                'updated_at' => time()
            ];
            $this->attachment->store($attachment);




            return response()->json(['success' => true, 'data' => $attachment, 'msg' => '上传成功']);
        } else {
            return response()->json(['code' => 1, 'msg' => '文件写入失败']);
        }

    }
}
