<?php

namespace App\Http\Controllers;

/**
 * 产品中心控制器
 *
 * 提供产品中心页面，展示手机等产品信息
 *
 * @package App\Http\Controllers
 */
class ProductController extends Controller
{
    /**
     * 产品中心页面
     *
     * GET /products
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 默认展示的手机产品
        $products = [
            [
                'name'    => 'Apple iPhone 15 Pro',
                'brand'   => 'Apple',
                'series'  => 'iPhone 15 系列',
                'model'   => 'A3104',
                'color'   => '原色钛金属',
                'storage' => '256GB',
                'price'   => '8999',
                'status'  => '在售',
                'desc'    => '搭载 A17 Pro 芯片，采用钛金属机身，配备专业级三摄系统与灵动岛设计，支持 USB-C 接口。',
                'tags'    => ['A17 Pro', '钛金属', '灵动岛', '5G'],
            ],
        ];

        return view('products.index', [
            'products' => $products,
        ]);
    }
}
