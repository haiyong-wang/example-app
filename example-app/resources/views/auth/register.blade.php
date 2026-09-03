<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册 - {{ config('app.name', '憨憨专属摸鱼网站') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #1c2431 0%, #2d3f66 55%, #3a5f8a 100%);
            color: #2c3e50;
            font-size: 14px;
        }
        a { text-decoration: none; }

        .auth-wrap { width: 100%; max-width: 420px; }
        .brand { text-align: center; color: #fff; margin-bottom: 24px; }
        .brand .fish { font-size: 44px; line-height: 1; }
        .brand h1 { font-size: 22px; font-weight: 700; margin-top: 8px; letter-spacing: 1px; }
        .brand p { font-size: 13px; opacity: .8; margin-top: 6px; }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 32px 30px 26px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, .25);
        }
        .card-title { font-size: 18px; font-weight: 700; color: #303133; margin-bottom: 4px; }
        .card-sub { font-size: 12px; color: #909399; margin-bottom: 22px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; color: #606266; margin-bottom: 6px; font-weight: 500; }
        .form-group input {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            border: 1px solid #dcdfe6;
            border-radius: 6px;
            font-size: 14px;
            color: #303133;
            outline: none;
            transition: all .2s;
        }
        .form-group input:focus { border-color: #409eff; box-shadow: 0 0 0 2px rgba(64, 158, 255, .15); }

        .btn {
            width: 100%;
            height: 44px;
            border: none;
            border-radius: 6px;
            background: linear-gradient(135deg, #f2b84b, #e89b26);
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            box-shadow: 0 6px 16px rgba(232, 155, 38, .3);
            margin-top: 6px;
        }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 10px 20px rgba(232, 155, 38, .4); }
        .btn:active { transform: translateY(0); }

        .foot { text-align: center; margin-top: 18px; font-size: 13px; color: #909399; }
        .foot a { color: #e89b26; font-weight: 600; }
        .foot a:hover { text-decoration: underline; }

        .errors {
            background: #fef0f0;
            border: 1px solid #fde2e2;
            color: #f56c6c;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .errors ul { list-style: none; }
        .errors li { padding: 2px 0; }
        .home-link { display: block; text-align: center; margin-top: 18px; font-size: 13px; color: rgba(255,255,255,.75); }
        .home-link:hover { color: #fff; text-decoration: underline; }
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="brand">
        <div class="fish">🐟</div>
        <h1>{{ config('app.name', '憨憨专属摸鱼网站') }}</h1>
        <p>加入憨憨俱乐部，摸鱼更快乐</p>
    </div>

    <div class="card">
        <div class="card-title">创建账号</div>
        <div class="card-sub">只需 30 秒，马上开摸</div>

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/register') }}">
            @csrf
            <div class="form-group">
                <label for="name">摸鱼昵称</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="想个拉风的昵称" required autofocus>
            </div>
            <div class="form-group">
                <label for="email">邮箱</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="signature">摸鱼宣言（选填）</label>
                <input type="text" id="signature" name="signature" value="{{ old('signature') }}" placeholder="例如：只要摸得快，领导就看不见">
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" placeholder="至少 6 位" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="password_confirmation">确认密码</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="再输入一次" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn">立即注册</button>
        </form>

        <div class="foot">
            已经有账号了？<a href="{{ url('/login') }}">去登录</a>
        </div>
    </div>

    <a class="home-link" href="{{ url('/') }}">← 返回首页</a>
</div>
</body>
</html>
