<?php


namespace app\api\model;


use app\lib\enum\CommonEnum;
use think\Model;

class TicketV extends Model
{
    public static function ticketsForCMS($company_id, $page, $size, $time_begin, $time_end, $key)
    {
        $time_end = addDay(1, $time_end);
        $list = self::where('company_id', $company_id)
            ->where('state', '<', CommonEnum::STATE_IS_DELETE)
            ->where(function ($query) use ($key) {
                if (strlen($key)) {
                    $query->where('name', 'like', '%' . $key . '%');
                }
            })
            ->where(function ($query) use ($time_begin, $time_end) {
                if (strlen($time_begin) && strlen($time_end)) {
                    $query->whereBetweenTime('create_time', $time_begin, $time_end);

                }
            })
            ->hidden(['u_id', 'source'])
            ->order('create_time desc')
            ->paginate($size, false, ['page' => $page]);
        return $list;
    }

}