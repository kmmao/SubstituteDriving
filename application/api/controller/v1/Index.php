<?php

namespace app\api\controller\v1;

use app\api\model\DriverT;
use app\api\model\MiniPushT;
use app\api\model\OrderPushT;
use app\api\model\OrderRevokeT;
use app\api\model\OrderT;
use app\api\model\StartPriceT;
use app\api\model\TimeIntervalT;
use app\api\model\WaitPriceT;
use app\api\model\WeatherT;
use app\api\service\DriverService;
use app\api\service\GatewayService;
use app\api\service\LogService;
use app\api\service\OrderService;
use app\api\service\SendSMSService;
use app\lib\enum\CommonEnum;
use app\lib\enum\OrderEnum;
use zml\tp_aliyun\SendSms;
use zml\tp_tools\Redis;
use function GuzzleHttp\Psr7\str;

class Index
{
    public function index()
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379, 60);
        $lat = '30.937638346354';
        $lng = '117.84915581597';
        $list = $this->redis->rawCommand('georadius',
            'drivers_tongling', $lat, $lng, 100000, 'km', 'WITHDIST', 'WITHCOORD');
        print_r($list);
    }

    public function sendMessage($name)
    {

        //send_message/send_message_success/send_message_fail
        $lenth = Redis::instance()->llen($name);

        var_dump($lenth);
        print_r(Redis::instance()->lRanges($name, 0, 10000));

    }

    public function test()
    {
        $u_id = 8;
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379, 60);
        $redis->rawCommand('zrem', 'drivers_tongling', $u_id);
        //2.新增新的实时地理位置
        $ret = $redis->rawCommand('geoadd', 'drivers_tongling', "117.83648925781", "30.94262559678", $u_id);
        //3.保存司机位置名称信息
        $location_data = [
            'username' => "测试司机",
            'phone' => "18956225230",
            'lat' => "30.94262559678",
            'lng' => "117.83648925781",
            'citycode' => 123,
            'city' => "铜陵",
            'district' => "铜官区",
            'street' => "谢龙路",
            'addr' => "大学生创业园",
            'locationdescribe' => "大学生创业园"
        ];

        $redis->rPush("driver:$u_id:location", json_encode($location_data));
    }

    private function prefixFee()
    {
        $wait = WaitPriceT::find();
        $wait_msg = "  免费等候" . $wait->free . "分钟，等候超出" . $wait->free . "分钟后每1分钟加收" . $wait->price . "元。";
        $fee_msg = "";
        $interval = TimeIntervalT::select();
        $start = StartPriceT::where('type', 1)->select();
        if (!empty($interval)) {
            foreach ($interval as $k => $v) {
                $fee_msg .= "  时间：(" . $v->time_begin . "-" . $v->time_end . ")" . "起步价" . $v->price . "元";
                $d = 0;
                foreach ($start as $k2 => $v2) {

                    if ($k2 == 0) {
                        $fee_msg .= "（" . $v2->distance . "公里内包含" . $v2->distance . "公里）;" . "\n";
                    } else if ($k2 == 1) {
                        $d += $v2->distance;
                        $fee_msg .= "超出起步里程后," . $v2->distance . "公里内包含" . $v2->distance . "公里,加收" . $v2->price . "元；";
                    } else {
                        $fee_msg .= "超出起步里程" . $d . "公里后," . $v2->distance . "公里内包含" . $v2->distance . "公里,加收" . $v2->price . "元；";
                        $d += $v2->distance;
                    }

                }
                $fee_msg .= "\n";
            }
        }

        return "资费标准：\n" . $fee_msg . $wait_msg;
    }

    public function initDriverStatus()
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379, 60);
        $drivers = DriverT::all();
        foreach ($drivers as $k => $v) {
            $redis->sRem('driver_order_ing', $v['id']);
            $redis->sAdd('driver_order_no', $v['id']);
        }

    }

    public function send($client_id)
    {
        //  var_dump(Gateway::sendToClient($client_id, 1));
    }


    public function locationAdd($lat, $lng, $d_id)
    {


        /*  $redis = new \Redis();
          $redis->connect('127.0.0.1', 6379, 60);

          $ret = $redis->rawCommand('geoadd', 'drivers_tongling', $lat, $lng, $d_id);
          var_dump($ret);*/

    }

    public function location($d_id)
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379, 60);
        print_r($redis->rawCommand('geopos', 'drivers_tongling', $d_id));
    }

    public function deleteLocation($d_id)
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379, 60);
        var_dump($redis->rawCommand('zrem', 'drivers_tongling', $d_id));
    }

    public function radius($lat, $lng, $type)
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379, 60);
        //查询所有司机并按距离排序
        //$list = $redis->rawCommand('georadius', 'drivers_tongling');
        $list = $redis->rawCommand('georadius', 'drivers_tongling', $lng, $lat, '1000000', 'km', $type);
        print_r($list);
    }

    public function zset()
    {
        $dis = 1.1;
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379, 60);
        /*        var_dump($redis->zScore('order:distance', 'o:2'));
                // $res = $redis->zAdd('order:distance', 0, 'o:2');
                //var_dump($res);
                $distance = $redis->zScore('order:distance', 'o:2');
                var_dump($distance);
                $redis->zIncrBy('order:distance', $dis, 'o:2');
                $distance = $redis->zScore('order:distance', 'o:2');
                var_dump($distance);
                $distance = $redis->zScore('order:distance', 'o:1');
                var_dump($distance);*/


    }

}
