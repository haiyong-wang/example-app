<?php

namespace App\Http\Controllers;

/**
 * 实用工具控制器
 *
 * 提供独立于产品/报表之外的"实用工具"导航页与各个工具页面
 *
 * @package App\Http\Controllers
 */
class ToolsController extends Controller
{
    /**
     * 工具导航首页
     *
     * GET /tools
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // 工具清单（后续每新增一个工具，在这里加一项即可自动出现在首页）
        $tools = [
            [
                'key'     => 'qrcode',
                'name'    => '二维码生成',
                'desc'    => '将网址、文本等内容一键生成二维码',
                'emoji'   => '🔲',
                'color'   => 'blue',
                'url'     => '/tools/qrcode',
                'ready'   => true,
            ],
            [
                'key'     => 'wps-to-pdf',
                'name'    => 'WPS 转 PDF',
                'desc'    => 'Word / Excel / PPT 文档转 PDF',
                'emoji'   => '📄',
                'color'   => 'green',
                'url'     => '#',
                'ready'   => false,
            ],
            [
                'key'     => 'img-to-pdf',
                'name'    => '图片转 PDF',
                'desc'    => '多张图片合并导出为一个 PDF',
                'emoji'   => '🖼️',
                'color'   => 'orange',
                'url'     => '#',
                'ready'   => false,
            ],
            [
                'key'     => 'compress',
                'name'    => '图片压缩',
                'desc'    => 'PNG / JPG 图片在线压缩',
                'emoji'   => '🗜️',
                'color'   => 'purple',
                'url'     => '#',
                'ready'   => false,
            ],
        ];

        return view('tools.index', [
            'tools' => $tools,
        ]);
    }

    /**
     * 二维码生成工具页
     *
     * GET /tools/qrcode
     *
     * @return \Illuminate\View\View
     */
    public function qrcode()
    {
        return view('tools.qrcode');
    }
}
