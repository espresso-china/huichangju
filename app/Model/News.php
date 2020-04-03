<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * Class Goods.
 *
 * @package namespace App\Model;
 */
class News extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'cms_news';

    protected $primaryKey = 'id';

    protected $fillable = [
        'title', 'description', 'thumb', 'classify_id', 'status', 'content', 'sort', 'url', 'listorder'
    ];

    protected $appends = ['classify_name'];

    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';

    public const DELETED_AT = 'delete_time';

    public function getClassifyNameAttribute()
    {
        return $this->classify ? $this->classify->title : '';
    }

    public function classify()
    {
        return $this->hasOne('\App\Model\Classify', 'id', 'classify_id');
    }
}

