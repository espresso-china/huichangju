<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class ActivityJoin extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'cms_activity_join';

    protected $primaryKey = 'id';

    protected $fillable = [
        'uid', 'activity_id','contacts','phone','number'
    ];

    protected $appends = ['member_name','activity_title'];

    public const CREATED_AT = 'create_time';

    public function getActivityTitleAttribute(){
        return $this->activity?$this->activity->title:'';
    }

    public function getMemberNameAttribute(){
        return $this->member?$this->member->member_name:'';
    }

    public function activity()
    {
        return $this->hasOne('\App\Model\Activity', 'activity_id', 'activity_id');
    }

    public function member()
    {
        return $this->hasOne('\App\Model\Member', 'uid', 'uid');
    }

}

