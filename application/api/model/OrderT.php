<?php


namespace app\api\model;


use app\lib\enum\CommonEnum;
use app\lib\enum\OrderEnum;
use think\Model;

class OrderT extends Model
{

    public function getCancelTypeAttr($value)
    {
        if ($value) {
            $data = ['mini' => '乘客', 'driver' => '司机', 'manager' => '管理员'];
            return $data[$value];
        }

    }

    public function getFromAttr($value)
    {
        if ($value) {
            $data = [1 => '小程序下单', 2 => '司机自主简单', 3 => '管理员自主建单', 4 => '公众号下单'];
            return $data[$value];
        }

    }

    public function user()
    {
        return $this->belongsTo('UserT', 'u_id', 'id');

    }

    public function ticket()
    {
        return $this->belongsTo('TicketUserT', 't_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo('DriverT', 'd_id', 'id');
    }

    public static function getOrder($o_id)
    {
        $order = self::where('id', $o_id)
            ->with('user')
            ->find();
        return $order;
    }

    public static function miniOrders($u_id, $page, $size)
    {
        $list = self::where('u_id', $u_id)
            ->where('state', '<', OrderEnum::ORDER_CANCEL)
            ->field('id,start,end,state,create_time')
            ->order('create_time desc')
            ->paginate($size, false, ['page' => $page]);
        return $list;
    }

    public static function getDriverOrders($d_id, $page, $size, $time_begin, $time_end)
    {
        $list = self::where('d_id', $d_id)
            ->whereIn('state', '4,5')
            ->where(function ($query) use ($time_begin, $time_end) {
                if (strlen($time_begin) && strlen($time_end)) {
                    $query->whereBetweenTime('create_time', $time_begin, $time_end);
                }
            })
            ->field('id,d_id,superior_id,null as superior,2 as transfer ,from,state,start,end,name,money,cancel_type,cancel_remark,create_time')
            ->order('create_time desc')
            ->paginate($size, false, ['page' => $page])->toArray();
        return $list;
    }

    public static function managerOrders($page, $size, $driver, $time_begin, $time_end)
    {
        $list = self::whereIn('state', '4,5')
            ->where(function ($query) use ($driver) {
                if (strlen($driver)) {
                    $query->where('username', 'like', '%' . $driver . '%');
                }
            })
            ->where(function ($query) use ($time_begin, $time_end) {
                if (strlen($time_begin) && strlen($time_end)) {
                    $query->whereBetweenTime('create_time', $time_begin, $time_end);
                }
            })
            ->field('id,d_id,superior_id,null as superior,2 as transfer ,from,state,start,end,name,money,cancel_type,cancel_remark,create_time')
            ->order('create_time desc')
            ->paginate($size, false, ['page' => $page])->toArray();
        return $list;
    }


    public static function members($driver, $time_begin, $time_end)
    {
        $members = $list = self::where('state', OrderEnum::ORDER_COMPLETE)
            ->where(function ($query) use ($driver) {
                if (strlen($driver)) {
                    $query->where('username', 'like', '%' . $driver . '%');
                }
            })
            ->where(function ($query) use ($time_begin, $time_end) {
                if (strlen($time_begin) && strlen($time_end)) {
                    $query->whereBetweenTime('create_time', $time_begin, $time_end);
                }
            })
            ->group('phone')
            ->count('phone');

        return $members;


    }

    public static function orderCount($driver, $time_begin, $time_end)
    {
        $counts = $list = self::where('state', OrderEnum::ORDER_COMPLETE)
            ->where(function ($query) use ($driver) {
                if (strlen($driver)) {
                    $query->where('username', 'like', '%' . $driver . '%');
                }
            })
            ->where(function ($query) use ($time_begin, $time_end) {
                if (strlen($time_begin) && strlen($time_end)) {
                    $query->whereBetweenTime('create_time', $time_begin, $time_end);
                }
            })
            ->count('phone');

        return $counts;


    }

    public static function driverOrderCount($d_id, $time_begin, $time_end)
    {
        $counts = $list = self::where('state', OrderEnum::ORDER_COMPLETE)
            ->where('d_id', $d_id)
            ->where(function ($query) use ($time_begin, $time_end) {
                if (strlen($time_begin) && strlen($time_end)) {
                    $query->whereBetweenTime('create_time', $time_begin, $time_end);
                }
            })
            ->count('phone');

        return $counts;


    }

    public static function ordersMoney($driver, $time_begin, $time_end)
    {
        $money = $list = self::where('state', OrderEnum::ORDER_COMPLETE)
            ->where(function ($query) use ($driver) {
                if (strlen($driver)) {
                    $query->where('username', 'like', '%' . $driver . '%');
                }
            })
            ->where(function ($query) use ($time_begin, $time_end) {
                if (strlen($time_begin) && strlen($time_end)) {
                    $query->whereBetweenTime('create_time', $time_begin, $time_end);
                }
            })
            ->field('sum(money+ticket_money) as all_money,sum(ticket_money) as ticket_money ')
            ->find();

        return $money;

    }

    public static function driverOrdersMoney($d_id, $time_begin, $time_end)
    {
        $money = $list = self::where('d_id', $d_id)
            ->where('state', OrderEnum::ORDER_COMPLETE)
            ->where(function ($query) use ($time_begin, $time_end) {
                if (strlen($time_begin) && strlen($time_end)) {
                    $query->whereBetweenTime('create_time', $time_begin, $time_end);
                }
            })
            ->field('sum(money+ticket_money) as all_money,sum(ticket_money) as ticket_money ')
            ->find();

        return $money;

    }

    public static function recordsOfConsumption($page, $size, $phone)
    {
        $list = self::where('phone', $phone)
            ->where('state', OrderEnum::ORDER_COMPLETE)
            ->field('create_time,start,end,money')
            ->order('create_time desc')
            ->paginate($size, false, ['page' => $page])->toArray();
        return $list;
    }


    public static function ConsumptionMoney($phone)
    {
        $money = $list = self::where('phone', $phone)
            ->where('state', OrderEnum::ORDER_COMPLETE)
            ->sum('money');

        return $money;

    }

    public static function ConsumptionCount($phone)
    {
        $count = $list = self::where('phone', $phone)
            ->where('state', OrderEnum::ORDER_COMPLETE)
            ->count('id');
        return $count;

    }

    public static function currentOrders($page, $size)
    {
        $list = self::whereIn('state', OrderEnum::ORDER_NO . "," . OrderEnum::ORDER_ING)
            ->with(['driver'=>function ($query) {
                $query->field('id,username');
            }])
            ->field('id,d_id,superior_id,null as superior,2 as transfer ,from,state,start,end,begin,name,create_time')
            ->order('create_time desc')
            ->paginate($size, false, ['page' => $page])->toArray();
        return $list;

    }


}