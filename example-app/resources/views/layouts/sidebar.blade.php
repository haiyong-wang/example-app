@php
    $current = $activeMenu ?? '';
@endphp
<!-- 左侧菜单（两级导航：一级点击展开/收起，默认收起；当前页面所在分组自动展开） -->
<style>
    /* ===== 两级导航样式（作用于侧边栏内部） ===== */
    #app-menu { padding: 8px 0; }

    /* 一级导航 */
    #app-menu .menu-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 20px;
        color: #303133;
        cursor: pointer;
        border-left: 3px solid transparent;
        text-decoration: none;
        user-select: none;
    }
    #app-menu .menu-item:hover { background: #f5f7fa; }
    #app-menu .menu-item.active {
        background: #ecf5ff;
        color: #409eff;
        border-left-color: #409eff;
    }
    #app-menu .menu-group-title { cursor: pointer; }
    #app-menu .menu-group-title .caret {
        font-size: 12px;
        color: #c0c4cc;
        transition: transform .2s ease;
    }
    #app-menu .menu-group.open > .menu-group-title .caret { transform: rotate(90deg); }
    #app-menu .menu-group-title:hover .caret { color: #909399; }

    /* 二级导航（与一级有明显视觉区别：缩进、更小字号、浅色背景胶囊） */
    #app-menu .sub-menu {
        display: none;
        padding: 2px 0 6px;
    }
    #app-menu .menu-group.open > .sub-menu { display: block; }
    #app-menu .sub-item {
        display: block;
        margin: 2px 12px 2px 28px;
        padding: 7px 12px;
        font-size: 13px;
        color: #606266;
        border-radius: 4px;
        text-decoration: none;
    }
    #app-menu .sub-item:hover { background: #f5f7fa; color: #409eff; }
    #app-menu .sub-item.active {
        background: #ecf5ff;
        color: #409eff;
        font-weight: 500;
    }
</style>

<aside class="sidebar">
    <div class="sidebar-logo">⚡ 焦皮的大项目</div>
    <nav class="menu" id="app-menu">
        <a class="menu-item @if($current === 'home') active @endif" href="{{ url('/') }}">首页</a>

        <div class="menu-group @if($current === 'daily') open @endif">
            <div class="menu-item menu-group-title">
                数据中心
                <span class="caret">▸</span>
            </div>
            <div class="sub-menu">
                <a class="sub-item @if($current === 'daily') active @endif" href="{{ url('/reports/daily') }}">机型筛选日报</a>
            </div>
        </div>

        <div class="menu-group @if($current === 'tools' || $current === 'qrcode') open @endif">
            <div class="menu-item menu-group-title">
                实用工具
                <span class="caret">▸</span>
            </div>
            <div class="sub-menu">
                <a class="sub-item @if($current === 'tools' || $current === 'qrcode') active @endif" href="{{ url('/tools') }}">工具导航</a>
            </div>
        </div>

        <div class="menu-group @if($current === 'games' || $current === 'woodfish' || $current === 'whack' || $current === 'clock' || $current === 'prism') open @endif">
            <div class="menu-item menu-group-title">
                摸鱼游戏
                <span class="caret">▸</span>
            </div>
            <div class="sub-menu">
                <a class="sub-item @if($current === 'games') active @endif" href="{{ url('/games') }}">小游戏</a>
            </div>
        </div>
    </nav>
</aside>

<script>
(function () {
    var menu = document.getElementById('app-menu');
    if (!menu) return;
    var groups = menu.querySelectorAll('.menu-group');
    Array.prototype.forEach.call(groups, function (group) {
        var title = group.querySelector('.menu-group-title');
        if (!title) return;
        title.addEventListener('click', function () {
            group.classList.toggle('open');
        });
    });
})();
</script>
