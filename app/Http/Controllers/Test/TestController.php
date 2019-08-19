<?php
namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class TestController extends Controller
{
    public function login(){
        $base64 = file_get_contents('php://input');
        //return $base64;exit;
//        var_dump($base64);exit;

        $enc_data = base64_decode($base64);

        $public_key = openssl_get_publickey('file://'.storage_path('app/keys/public.pem'));
        openssl_public_decrypt($enc_data,$dec_data,$public_key);
        echo "</br>";
        $dec_data = json_decode($dec_data);
        $name = $dec_data->name;
        $user_pwd = $dec_data->user_pwd;

        $res = DB::table('user')->where('name',$name)->first();
        if($res){
            $user_pwd1 = $res->user_pwd;
            if($user_pwd != $user_pwd1){
                $response = [
                    'error'=>40008,
                    'msg'=>'请输入正确的账号或密码'
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }else{
                $token = sha1(Str::random(10).md5(time()));
                $updatedata = [
                    'token'=>$token,
                    'update_time'=>time()
                ];
                $res = DB::table('user')->where('name',$name)->update($updatedata);
                if($res){
                    $response = [
                        'error'=>0,
                        'msg'=>'登陆成功',
                        'data'=>[
                            'token'=>$token
                        ]
                    ];

                    $key = "H:userlogin_id";
                    Redis::set($key,$token);
                    Redis::expire($key,86400*3);

                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }else{
                    $response = [
                        'error'=>40009,
                        'msg'=>'登陆失败'
                    ];
                    die(json_encode($response,JSON_UNESCAPED_UNICODE));
                }
            }
        }else{
            $response = [
                'error'=>40008,
                'msg'=>'请输入正确的账号或密码'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    /**城市天气接口*/
    public function weather(Request $request){
        $city = $request->input('city');
        $info = DB::table('weather')->where('city',$city)->first();
        if($info){
            var_dump($info);
        }else{
            $url = "http://api.k780.com/?app=weather.future&weaid=$city&&appkey=42890&sign=3c066a58e687cb3ef0e98464aebc8d8b&format=json";
            $weather = file_get_contents($url);
            $weather = json_decode($weather);
            //var_dump($weather);exit;
            $result = $weather->result;
            var_dump($result[0]);
            $weaid = $result[0]->weaid;
            $week = $result[0]->week;
            $city = $result[0]->citynm;
            $cityid = $result[0]->cityid;
            $weather = $result[0]->weather;
            $wind = $result[0]->wind;
            $winp = $result[0]->winp;
            $temperature = $result[0]->temperature;
            $res = DB::table('weather')->where('city',$city)->first();
            //var_dump($res);exit;
            if($res){
                $data = [
                    'weaid'=>$weaid,
                    'week'=>$week,
                    'city'=>$city,
                    'cityid'=>$cityid,
                    'weather'=>$weather,
                    'wind'=>$wind,
                    'winp'=>$winp,
                    'temperature'=>$temperature,
                    'time'=>time()
                ];
                $res = DB::table('weather')->where('city',$weaid)->update($data);
            }else{
                $data = [
                    'weaid'=>$weaid,
                    'week'=>$week,
                    'city'=>$city,
                    'cityid'=>$cityid,
                    'weather'=>$weather,
                    'wind'=>$wind,
                    'winp'=>$winp,
                    'temperature'=>$temperature,
                    'time'=>time()
                ];
                $res = DB::table('weather')->insertGetId($data);
            }
        }
    }
}