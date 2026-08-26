<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 第三方手机型号查询接口配置
    |--------------------------------------------------------------------------
    |
    | 对接的第三方 API 基础配置
    |
    */

    // 第三方 API 基础地址
    'base_url' => env('PHONE_MODEL_API_BASE', 'http://tdyh.flow2me.com:8888'),

    // 接口路径
    'path' => '/complaint/getPhoneModel/{id}/{cid}/{code}/{type}',

    // 技术提供的合作方参数
    'id' => env('PHONE_MODEL_API_ID', ''),
    'cid' => env('PHONE_MODEL_API_CID', ''),
    'code' => env('PHONE_MODEL_API_CODE', ''),

    // 服务类型(固定值)
    'type' => env('PHONE_MODEL_API_TYPE', 10001),

    // HTTP 请求超时时间(秒)
    'timeout' => env('PHONE_MODEL_API_TIMEOUT', 60),

    // 单次允许查询的最大手机号数量
    'max_phones' => 10000,

];
