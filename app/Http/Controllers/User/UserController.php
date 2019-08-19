<?php
namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;


class UserController extends Controller
{
    /**注册*/
    public function register(Request $request){
//        header("Access-Control-Allow-Origin:*");
        $name = $request->input('user_name');
        //var_dump($name);exit;
        $email = $request->input('user_email');
        $user_pwd = $request->input('user_pwd');
        $user_pwd1 = $request->input('user_pwd1');
        if($user_pwd != $user_pwd1){
            $response = [
                'error'=>50001,
                'msg'=>'两次输入密码不相同'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
        $data = [
            'name'=>$name,
            'email'=>$email,
            'user_pwd'=>$user_pwd
        ];
        $json_str = json_encode($data,JSON_UNESCAPED_UNICODE);
        //加密
        $private = openssl_pkey_get_private('file://'.storage_path('app/keys/private.pem'));
        openssl_private_encrypt($json_str,$enc_data,$private);
        $enc_data = base64_encode($enc_data);
        //var_dump($enc_data);
        $url = "http://clinet.wh6636.cn/regAdd";
        //var_dump($url);exit;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$enc_data);
        curl_setopt($ch,CURLOPT_HTTPHEADER,['Content-Type:text/plain']);

        $info = curl_exec($ch);
        //$code= curl_errno($ch);
        //var_dump($code);exit;
        curl_close($ch);
        return $info;

        //file_put_contents('/tmp/demo.log',$dec_data,FILE_APPEND);
    }


    /**登陆*/
    public function logindo(Request $request){
        //header("Access-Control-Allow-Origin: *");
        $email = $request->input('email');
        $password = $request->input('pwd');
        $data = [
            'email'=>$email,
            'pwd'=>$password
        ];
        //var_dump($data);exit;
        $json_str = json_encode($data,JSON_UNESCAPED_UNICODE);
        $private = openssl_pkey_get_private('file://'.storage_path('app/keys/private.pem'));
        openssl_private_encrypt($json_str,$enc_data,$private);
        //var_dump($enc_data);exit;
        $url = "http://client.1809a.com/logindo";
        //var_dump($url);exit;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$enc_data);
        curl_setopt($ch,CURLOPT_HTTPHEADER,['Content-Type:text/plain']);

        $info = curl_exec($ch);
        //$code= curl_errno($ch);
        //var_dump($code);exit;
        curl_close($ch);
        return $info;

        //var_dump($data);exit;

    }

    /**个人中心*/
    public function center(){
        echo "已授权";
    }

}

