<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>二维码生成 - {{ config('app.name', 'Laravel') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif;
            background: #f5f6fa;
            color: #2c3e50;
            font-size: 14px;
            min-height: 100vh;
        }
        a { text-decoration: none; color: inherit; }

        .layout { display: flex; min-height: 100vh; }

        /* 左侧菜单 */
        .sidebar {
            width: 200px;
            background: #fff;
            border-right: 1px solid #ebeef5;
            flex-shrink: 0;
        }
        .sidebar-logo {
            padding: 18px 20px;
            border-bottom: 1px solid #ebeef5;
            font-weight: bold;
            font-size: 16px;
            color: #303133;
        }
        .menu { padding: 8px 0; }
        .menu-item {
            display: block;
            padding: 12px 20px;
            color: #303133;
            cursor: pointer;
            border-left: 3px solid transparent;
        }
        .menu-item:hover { background: #f5f7fa; }
        .menu-item.active {
            background: #ecf5ff;
            color: #409eff;
            border-left-color: #409eff;
        }
        .menu-section {
            padding: 10px 20px 6px;
            color: #909399;
            font-size: 12px;
        }

        /* 右侧主体 */
        .main { flex: 1; min-width: 0; }

        /* 顶部 */
        .topbar {
            background: #fff;
            padding: 10px 20px;
            border-bottom: 1px solid #ebeef5;
            font-size: 13px;
            color: #606266;
        }
        .topbar .sep { color: #c0c4cc; margin: 0 6px; }

        /* Tab */
        .tabs {
            background: #fff;
            padding: 0 20px;
            border-bottom: 1px solid #ebeef5;
            display: flex;
        }
        .tab {
            padding: 14px 16px;
            margin-right: 4px;
            color: #606266;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }
        .tab.active {
            color: #409eff;
            border-bottom-color: #409eff;
        }
        .tab-content { padding: 20px; }

        /* 页面标题 */
        .page-title {
            background: #fff;
            padding: 16px 20px;
            font-weight: 500;
            font-size: 15px;
            border-bottom: 1px solid #ebeef5;
        }

        /* 卡片 */
        .card {
            background: #fff;
            border: 1px solid #ebeef5;
            border-radius: 4px;
            margin-bottom: 16px;
        }
        .card-title {
            padding: 14px 20px;
            border-bottom: 1px solid #ebeef5;
            font-weight: 500;
            font-size: 14px;
            color: #303133;
            position: relative;
            padding-left: 28px;
        }
        .card-title::before {
            content: "";
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 14px;
            background: #409eff;
            border-radius: 2px;
        }
        .card-body { padding: 20px; }

        /* 表单 */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; color: #606266; margin-bottom: 6px; }
        .form-group textarea, .form-group input[type="text"] {
            width: 100%;
            max-width: 640px;
            padding: 10px 12px;
            border: 1px solid #dcdfe6;
            border-radius: 4px;
            font-size: 14px;
            outline: none;
            font-family: inherit;
            color: #303133;
            resize: vertical;
        }
        .form-group textarea:focus, .form-group input[type="text"]:focus { border-color: #409eff; }
        .form-row {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .form-row .item { display: flex; align-items: center; gap: 6px; }
        .form-row .item label { font-size: 13px; color: #606266; }
        .form-row select {
            padding: 6px 10px;
            border: 1px solid #dcdfe6;
            border-radius: 4px;
            background: #fff;
            font-size: 13px;
            outline: none;
            color: #606266;
        }

        .btn {
            display: inline-block;
            padding: 9px 22px;
            border: 1px solid #409eff;
            background: #409eff;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover { background: #66b1ff; }
        .btn.plain {
            background: #fff;
            color: #606266;
            border-color: #dcdfe6;
        }
        .btn.plain:hover { color: #409eff; border-color: #c6e2ff; }
        .btn[disabled] { background: #a0cfff; border-color: #a0cfff; cursor: not-allowed; }

        .qrcode-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            border: 1px dashed #ebeef5;
            border-radius: 4px;
            background: #fafbfc;
            min-height: 220px;
            flex-direction: column;
            gap: 12px;
        }
        .qrcode-wrap .placeholder { color: #c0c4cc; font-size: 13px; }

        .download-row { display: flex; justify-content: center; gap: 12px; margin-top: 16px; }
    </style>
</head>
<body>
<div class="layout">

    @include('layouts.sidebar', ['activeMenu' => 'qrcode'])

    <!-- 右侧主体 -->
    <main class="main">

        <!-- 顶部面包屑 -->
        <div class="topbar">
            实用工具
            <span class="sep">/</span>
            二维码生成
        </div>

        <div class="tab-content">

            <!-- 输入卡片 -->
            <div class="card">
                <div class="card-title">输入内容</div>
                <div class="card-body">
                    <div class="form-group">
                        <label>二维码内容（网址 / 文本 / 联系方式等）</label>
                        <textarea id="qrcode-text" rows="3" placeholder="例如：https://www.example.com 或 你好，二维码"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="item">
                            <label>尺寸</label>
                            <select id="qrcode-size">
                                @foreach ([200, 256, 320, 400] as $s)
                                    <option value="{{ $s }}" @if($s == 256) selected @endif>{{ $s }}×{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="item">
                            <label>纠错级别</label>
                            <select id="qrcode-ec">
                                <option value="M" selected>M (默认)</option>
                                <option value="L">L (低)</option>
                                <option value="Q">Q (较高)</option>
                                <option value="H">H (高)</option>
                            </select>
                        </div>
                        <div style="margin-left:auto;">
                            <button type="button" class="btn" id="btn-generate">生成二维码</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 结果卡片 -->
            <div class="card">
                <div class="card-title">二维码预览</div>
                <div class="card-body">
                    <div class="qrcode-wrap" id="qrcode-area">
                        <span class="placeholder">输入内容后点击"生成二维码"</span>
                    </div>
                    <div class="download-row">
                        <button type="button" class="btn" id="btn-download" style="display:none;">下载 PNG</button>
                        <button type="button" class="btn plain" id="btn-clear">清空</button>
                    </div>
                </div>
            </div>

        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
(function () {
    var textInput = document.getElementById('qrcode-text');
    var sizeSelect = document.getElementById('qrcode-size');
    var ecSelect = document.getElementById('qrcode-ec');
    var area = document.getElementById('qrcode-area');
    var btnGenerate = document.getElementById('btn-generate');
    var btnDownload = document.getElementById('btn-download');
    var btnClear = document.getElementById('btn-clear');

    // 保存二维码容器，便于重新生成/下载
    var qrContainer = null;

    function generate() {
        var text = (textInput.value || '').trim();
        if (!text) {
            alert('请先输入需要生成二维码的内容');
            return;
        }
        if (typeof QRCode === 'undefined') {
            alert('二维码库加载失败，请检查网络后刷新页面重试');
            return;
        }

        // 清理旧的二维码
        area.innerHTML = '';
        qrContainer = document.createElement('div');
        area.appendChild(qrContainer);

        var size = parseInt(sizeSelect.value, 10) || 256;

        new QRCode(qrContainer, {
            text: text,
            width: size,
            height: size,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel[ecSelect.value] || QRCode.CorrectLevel.M
        });

        btnDownload.style.display = 'inline-block';
    }

    function download() {
        var img = area.querySelector('img');
        if (img) {
            var a = document.createElement('a');
            a.href = img.src;
            a.download = 'qrcode.png';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    }

    function clearAll() {
        textInput.value = '';
        area.innerHTML = '<span class="placeholder">输入内容后点击"生成二维码"</span>';
        qrContainer = null;
        btnDownload.style.display = 'none';
    }

    btnGenerate.addEventListener('click', generate);
    btnDownload.addEventListener('click', download);
    btnClear.addEventListener('click', clearAll);

    // 回车(未换行时)快捷生成
    textInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            generate();
        }
    });
})();
</script>
</body>
</html>
