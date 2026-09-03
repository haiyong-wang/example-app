<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ctrl = new App\Http\Controllers\ToolsController();

// 1) GET 表单页
$get = new Illuminate\Http\Request();
$resp = $ctrl->fortune($get);
$html = $resp->render();
file_put_contents(__DIR__ . '/_t_get.html', $html);

// 2) POST 校验失败：年份越界
$post = new Illuminate\Http\Request();
$post->merge(['year' => 1800, 'month' => 5, 'hometown' => '']);
$resp2 = $ctrl->fortune($post);
$html2 = $resp2->render();
file_put_contents(__DIR__ . '/_t_err.html', $html2);

// 3) POST 校验失败：月份非法
$post3 = new Illuminate\Http\Request();
$post3->merge(['year' => 2000, 'month' => 13, 'hometown' => '']);
$resp3 = $ctrl->fortune($post3);
$html3 = $resp3->render();
file_put_contents(__DIR__ . '/_t_err2.html', $html3);

// 4) POST 成功
$post4 = new Illuminate\Http\Request();
$post4->merge(['_token' => csrf_token(), 'year' => 1995, 'month' => 6, 'hometown' => '河南省郑州市']);
$resp4 = $ctrl->fortune($post4);
$html4 = $resp4->render();
file_put_contents(__DIR__ . '/_t_ok.html', $html4);

echo "all rendered: get=" . strlen($html) . " err=" . strlen($html2) . " err2=" . strlen($html3) . " ok=" . strlen($html4);