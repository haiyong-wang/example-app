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

    /* 让侧边栏内容区可滚动、底部用户区固定 */
    .sidebar { display: flex; flex-direction: column; }
    #app-menu { flex: 1; overflow-y: auto; }

    /* 底部用户区 */
    .sidebar-user {
        padding: 12px;
        border-top: 1px solid #ebeef5;
        background: #fafbfc;
    }
    .user-chip {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        padding: 6px 4px;
    }
    .user-chip .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f2b84b, #e89b26);
        color: #fff;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .user-chip .user-meta { display: flex; flex-direction: column; min-width: 0; }
    .user-chip .user-name {
        font-size: 13px;
        font-weight: 600;
        color: #303133;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .user-chip .user-sub { font-size: 11px; color: #909399; margin-top: 2px; }
    .logout-form { margin-top: 8px; }
    .logout-btn {
        display: block;
        width: 100%;
        padding: 7px 0;
        text-align: center;
        border: 1px solid #ebeef5;
        border-radius: 4px;
        background: #fff;
        color: #606266;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        transition: all .2s;
    }
    .logout-btn:hover { border-color: #f56c6c; color: #f56c6c; }
    .logout-btn.link { display: inline-block; width: 48%; }
    .logout-btn.link.primary { background: #409eff; border-color: #409eff; color: #fff; }
    .logout-btn.link.primary:hover { background: #66b1ff; border-color: #66b1ff; color: #fff; }
    .guest-box .guest-tip {
        font-size: 12px;
        color: #909399;
        text-align: center;
        margin-bottom: 8px;
        display: flex;
        justify-content: center;
        gap: 6px;
    }
</style>

<aside class="sidebar">
    <div class="sidebar-logo">🐟 憨憨专属摸鱼网站</div>
    <nav class="menu" id="app-menu">
        <a class="menu-item @if($current === 'home') active @endif" href="{{ url('/') }}">首页</a>

        <a class="menu-item @if($current === 'slack') active @endif" href="{{ url('/slack') }}">摸鱼时长</a>

{{--        <div class="menu-group @if($current === 'daily') open @endif">--}}
{{--            <div class="menu-item menu-group-title">--}}
{{--                数据中心--}}
{{--                <span class="caret">▸</span>--}}
{{--            </div>--}}
{{--            <div class="sub-menu">--}}
{{--                <a class="sub-item @if($current === 'daily') active @endif" href="{{ url('/reports/daily') }}">机型筛选日报</a>--}}
{{--            </div>--}}
{{--        </div>--}}

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

    <!-- 底部用户区 -->
    <div class="sidebar-user">
        @auth
            <a class="user-chip" href="{{ url('/') }}">
                <span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                <span class="user-meta">
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <span class="user-sub">憨憨日常摸鱼中</span>
                </span>
            </a>
            <form method="POST" action="{{ url('/logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn">退出登录</button>
            </form>
        @else
            <div class="guest-box">
                <p class="guest-tip">登录后即可继续快乐摸鱼</p>
                <a class="logout-btn link" href="{{ url('/login') }}">登录</a>
                <a class="logout-btn link primary" href="{{ url('/register') }}">注册</a>
            </div>
        @endauth
    </div>
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
