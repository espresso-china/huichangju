<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Activity extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'cms_activity';

    protected $primaryKey = 'activity_id';

    protected $fillable = [
        'thumb', 'originator', 'title', 'start_time', 'end_time', 'description','address','join','status','sort','content'
    ];

    protected $appends = [];

    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';

}

