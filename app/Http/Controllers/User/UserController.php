<?php
namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;


class UserController extends Controller
{
    public function register(Request $request){
        $data = file_get_contents('php://input');
        //$data = "b2psU9mDIyBaEAQN4xSqy7b2y+fUKal++oaCLTXpyjkW2hSnQyiVwWNqf2gFKmgwkPer+6JxXJq4sMKhQWv4Cisvn0N2mffWzlp6EC7NEsr/WyAEvNCcmdLyErh3nlsy/2sjl3BMBvPTJB0e2YLouL4pSXz4hd2MlcGNEfK0mZBTkLXIrxQZr6La+nF2MiRm0gC1PZ2pBLKd02rk12T9mxlCG7gUrt0WX7i2EhX7u1EdORzmWeMXH7uk/8NIRZ7sOHelsGfPCZmaleZeu034LqYHeAuOZTuDLvwYxiANupGx/745goYKivXtckJr32c5tD4iuoeDBZ+HCSZTUyeGKQ==";
        //var_dump($data);
        $data_info = base64_decode($data);
        $public_key = openssl_get_publickey('file://'.storage_path('app/keys/public.pem'));
        openssl_public_decrypt($data_info,$dec_data,$public_key);

        //var_dump($dec_data);
        //echo "</br>";
        $info = json_decode($dec_data,true);
        //var_dump($info);
        $name = $info['name'];
        //var_dump($name);exit;
        $email = $info['email'];
        $user_pwd = $info['user_pwd'];
        //var_dump($info);exit;
        if(empty($name)){
            $response = [
                'error' => 50005,
                'msg'   => '账号不能为空'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
        if(empty($email)){
            $response = [
                'error' => 50006,
                'msg'   => '邮箱不能为空'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
        if(empty($user_pwd)){
            $response = [
                'error' => 50005,
                'msg'   => '请输入密码'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

        $e = DB::table('user_info')->where('email',$email)->first();
        if($e){
            $response = [
                'error' => 50002,
                'msg'   => '该邮箱已被注册'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
        $n = DB::table('user_info')->where('name',$name)->first();
        if($n){
            $response = [
                'error' => 50003,
                'msg'   => '该用户名已被注册'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

        $pass = password_hash($user_pwd,PASSWORD_BCRYPT);
        //var_dump($hash);
        $data = [
            'name'=>$name,
            'email'=>$email,
            'user_pwd'=>$pass,
            'add_time'=>time()
        ];
        $res = DB::table('user_info')->insertGetId($data);
        if($res){
            $response = [
                'error'=>0,
                'msg'=>'注册成功'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }


        //file_put_contents('/tmp/demo.log',$dec_data,FILE_APPEND);
    }


    public function logindo(Request $request){
        //header("Access-Control-Allow-Origin: *");
        $email = $request->input('user_email');
        $password = $request->input('user_pwd');
        //echo $email;exit;
        $passInfo = DB::table('user_info')->where('email',$email)->first();
        //var_dump($passInfo);exit;
        if(empty($email)){
            $response = [
                'error'=>50004,
                'msg'=>'邮箱不能为空'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
        if(empty($password)){
            $response = [
                'error'=>50005,
                'msg'=>'密码不能为空'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
        if(empty($passInfo)){
            $response = [
                'error'=>50005,
                'msg'=>'请输入正确的email或密码'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
        $pass = $passInfo->user_pwd;
        $uid = $passInfo->uid;
        //var_dump($pass);exit;
        $user_pass = password_verify($password,$pass);
        //var_dump($user_pass);exit;
        if($user_pass == false){
            $response = [
                'error'=>50005,
                'msg'=>'请输入正确的email或密码'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $token = sha1(Str::random(10).md5(time()).$uid);
            $response = [
                'error'=>0,
                'msg'=>'登陆成功',
                'token'=>$token
            ];

            $id = Redis::incr('id');
            $hsetkey = "id_{$id}";
            $keylist = "H:user_login";
            Redis::hset($hsetkey,'id',$id);
            Redis::hset($hsetkey,'user_id',$uid);
            Redis::hset($hsetkey,'token',$token);
            Redis::hset($hsetkey,'createtime',time());
            Redis::lpush($keylist,$hsetkey);

            //var_dump($token);exit;

        }


        die(json_encode($response,JSON_UNESCAPED_UNICODE));

        //var_dump($data);exit;

    }

}
