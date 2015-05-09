<?php defined('IN_IA') or exit('Access Denied');
function tbzs_config() {

    return array(
        "allow"=>1,        //是否允许用户自行设置图片存储方式
        "upload"=>1,     //系统默认图片存储图片存储方式 0 本地 1 千牛
        "access_key"=>"DlJaBDwE1xEnmTOI6O21mx86KmHW8TK-QuYFil9l", //系统默认七牛 access_key，可自行设置
        "secret_key"=>"7jgNV6AtKt-VBC-61EanD_pVqKIJju1qbjecQMEA",//系统默认七牛 secret_key, 可自行设置
        "bucket"=>"tbzs-test"//系统默认七牛 bucket, 可自行设置
    );
    
}
