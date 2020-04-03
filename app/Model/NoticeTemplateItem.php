<?php
/**
 * Created by PhpStorm.
 * User: tacker
 * Date: 2019/3/25
 * Time: 11:56 AM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class NoticeTemplateItem extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'sys_notice_template_item';

    protected $primaryKey = 'id';

    protected $fillable = [
        'item_name', 'show_name', 'replace_name', 'type_ids', 'order',
    ];

    protected $appends = [];

    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';
}