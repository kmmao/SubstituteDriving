<?php


namespace app\api\service;


use app\api\model\DriverT;
use app\api\model\OrderT;
use app\api\model\WalletRecordV;
use app\lib\enum\CommonEnum;
use app\lib\enum\DriverEnum;
use app\lib\enum\OrderEnum;
use app\lib\exception\AuthException;
use app\lib\exception\SaveException;
use app\lib\exception\UpdateException;
use zml\tp_tools\Redis;

class DriverService
{
    public function save($params)
    {
        $params['pwd'] = sha1($params['pwd']);
        $params['admin_id'] = Token::getCurrentUid();
        $params['state'] = CommonEnum::STATE_IS_OK;
        $driver = DriverT::create($params);
        if (!$driver) {
            throw new SaveException();
        }

    }

    public function drivers($page, $size, $time_begin, $time_end, $username, $account, $online)
    {

        $drivers = WalletRecordV::drivers($page, $size, $time_begin, $time_end, $username, $account, $online);
        return $drivers;

    }

    public function online($params)
    {
        $type = Token::getCurrentTokenVar('type');
        if ($type !== "driver") {
            throw new AuthException();
        }
        $id = Token::getCurrentUid();
        $this->prefixDriverState($params['line'], $id);
        $res = DriverT::update(['online' => $params['online']], ['id' => $id]);
        if (!$res) {
            throw new UpdateException();
        }

    }

    private function checkNoCompleteOrder($id)
    {
        $count = OrderT::where('d_id', $id)
            ->where('state', OrderEnum::ORDER_ING)
            ->count();
        return $count;

    }

    private function prefixDriverState($line_type, $d_id)
    {
        //处理司机状态
        //1.上线-添加进入未接单
        //2.下线-需要检测当前时候否有进行中的订单；清除接单三大状态
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379, 60);
        if ($line_type == DriverEnum::ONLINE) {
            $redis->sAdd('driver_order_no', $d_id);
        } else {
            if ($this->checkNoCompleteOrder($d_id)) {
                throw new UpdateException(['您还有订单进行中，不能下线']);
            }

            $redis->sRem('driver_order_ing', $d_id);
            $redis->sRem('driver_order_no', $d_id);
            $redis->sRem('driver_order_receive', $d_id);
        }


    }

    /**
     * 订单完成修改司机接单状态
     * 接单中->未接单
     */
    public function handelDriveStateByComplete($d_id)
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379, 60);
        $redis->sRem('driver_order_receive', $d_id);
        $redis->sAdd('driver_order_no', $d_id);

    }
}