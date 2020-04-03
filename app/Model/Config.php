<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Config extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'sys_config';

    protected $primaryKey = 'id';

    protected $fillable = [
        'instance_id', 'key', 'value', 'desc', 'is_use'
    ];

    protected $appends = [];

    protected $dateFormat = 'U';

    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'modify_time';
}
