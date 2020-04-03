<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Province extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'sys_province';

    protected $primaryKey = 'province_id';

    protected $fillable = [
        'area_id', 'province_name',
    ];

    protected $appends = [];

    public $timestamps = false;

/*    public const CREATED_AT = 'created_time';

    public const UPDATED_AT = 'update_time';*/
}
