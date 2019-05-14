<?php
namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test(){
        echo $_GET;"</br>";
        $base64 = file_get_contents('php://input');
        echo $base64;exit;
//        $base64 = $_COOKIE['data'];
        $method = 'AES-256-CBC';
        $key = "abcdefg";
        $option = OPENSSL_RAW_DATA;
        $iv = 'djadjlajdlajdjkl';

        $data = base64_decode($base64);
        $dec_str = openssl_decrypt($data,$method,$key,$option,$iv);
        //var_dump($dec_str);exit;
        $dec_str = json_decode($dec_str,JSON_UNESCAPED_UNICODE);
        $response = [
            'error'=>0,
            'msg'=>'ok',
            'data'=>$dec_str
        ];
        file_put_contents('/tmp/demo.log',$dec_str,FILE_APPEND);
    }


    //ajax请求
    public function ajaxTest(){
        //header("Access-Control-Allow-Origin: *");//http://lumen.1809a.com
        echo '1';
    }
}