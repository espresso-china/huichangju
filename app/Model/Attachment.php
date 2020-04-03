<?php
/**
 * Created by PhpStorm.
 * User: Espresso
 * Date: 2019-02-18
 * Time: 14:27
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    public $timestamps = true;

    protected $dateFormat = 'U';

    protected $table = 'sys_attachment';

    const TYPE_IS_NORMAL = 'normal';     //普通
    const TYPE_IS_NEWS = 'news';    //商品


    protected $fillable = [
        'original_filename', 'basename', 'filename',
        'filepath', 'filesize', 'fileext','shopid',
        'height', 'width', 'isimage', 'ip', 'mime','qiniu_url'
    ];

    public static function getTypes($type = '')
    {
        $types = [
            self::TYPE_IS_NORMAL => '普通图片',
            self::TYPE_IS_NEWS=>'新闻图片',
        ];
        if ($type === '') {
            return $types;
        } else {
            return isset($types[$type]) ? $types[$type] : '';
        }
    }
}
