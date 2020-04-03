<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Account extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'xs_account';

    protected $primaryKey = 'account_id';

    protected $fillable = [
        'account_id', 'account_order_money', 'account_return', 'account_withdraw', 'account_deposit', 'account_assistant', 'account_user_withdraw',
    ];

    protected $appends = ['balance'];

    public const CREATED_AT = 'create_time';

    public const UPDATED_AT = 'update_time';

    public function getBalanceAttribute()
    {
        return $balance = $this->account_order_money + $this->account_deposit + $this->account_assistant - $this->account_withdraw - $this->account_user_withdraw;
    }
}