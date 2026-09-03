<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>八字算命 - {{ config('app.name', 'Laravel') }}</title>
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

        .tab-content { padding: 20px; }

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
            background: linear-gradient(180deg, #a262e6, #bb85ee);
            border-radius: 2px;
        }
        .card-body { padding: 20px; }

        /* 表单 */
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block; font-size: 13px; color: #606266; margin-bottom: 6px;
        }
        .form-row {
            display: flex;
            align-items: flex-end;
            gap: 16px;
            flex-wrap: wrap;
        }
        .form-row .field { flex: 1; min-width: 150px; }
        .form-group select, .form-group input[type="text"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dcdfe6;
            border-radius: 4px;
            font-size: 14px;
            outline: none;
            color: #303133;
            background: #fff;
            font-family: inherit;
        }
        .form-group select:focus, .form-group input[type="text"]:focus { border-color: #a262e6; }
        .err-msg { color: #f56c6c; font-size: 12px; margin-top: 4px; }

        .btn {
            display: inline-block;
            padding: 10px 34px;
            border: 1px solid #a262e6;
            background: linear-gradient(135deg, #a262e6, #bb85ee);
            color: #fff;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            letter-spacing: 2px;
            box-shadow: 0 4px 14px rgba(162, 98, 230, .25);
        }
        .btn:hover { filter: brightness(1.05); box-shadow: 0 6px 18px rgba(162, 98, 230, .35); }
        .btn-tip { font-size: 12px; color: #909399; margin-top: 10px; }

        /* 测算结果 */
        .f-banner {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 18px;
            padding: 8px 2px 18px;
            border-bottom: 1px dashed #ebeef5;
            margin-bottom: 18px;
        }
        .f-banner .f-orb {
            width: 64px; height: 64px; border-radius: 50%;
            background: linear-gradient(135deg, #a262e6, #d88dee);
            color: #fff; font-size: 32px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 6px 16px rgba(162, 98, 230, .35);
            flex-shrink: 0;
        }
        .f-banner .f-main { min-width: 0; }
        .f-banner .f-title { font-size: 18px; font-weight: 600; color: #303133; }
        .f-banner .f-sub { font-size: 13px; color: #909399; margin-top: 6px; line-height: 1.7; }

        .f-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 18px;
        }
        .f-cell {
            border: 1px solid #ebeef5;
            border-radius: 6px;
            padding: 16px;
            background: #fafbfc;
        }
        .f-cell .f-cell-label { font-size: 12px; color: #909399; margin-bottom: 8px; }
        .f-cell .f-cell-value { font-size: 18px; font-weight: 600; color: #303133; }
        .f-cell .f-cell-tag {
            display: inline-block; margin-top: 6px;
            font-size: 11px; color: #a262e6;
            background: rgba(162,98,230,.1);
            border: 1px solid rgba(162,98,230,.25);
            padding: 2px 8px; border-radius: 10px;
        }

        /* 运势明细 */
        .f-item { margin-bottom: 16px; }
        .f-item:last-child { margin-bottom: 0; }
        .f-item .f-head {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 6px;
        }
        .f-item .f-name { font-size: 14px; color: #303133; font-weight: 500; }
        .f-item .f-score { font-size: 13px; color: #a262e6; font-weight: 600; }
        .f-bar {
            height: 8px; border-radius: 4px; background: #ebeef5; overflow: hidden;
        }
        .f-bar > i {
            display: block; height: 100%; border-radius: 4px;
            background: linear-gradient(90deg, #a262e6, #d88dee);
            transition: width .6s ease;
        }
        .f-text { font-size: 13px; color: #606266; line-height: 1.7; margin-top: 8px; }

        /* 总评 */
        .overall {
            background: linear-gradient(135deg, #faf5ff, #f0e6ff);
            border: 1px solid rgba(162,98,230,.2);
            border-radius: 8px;
            padding: 20px;
        }
        .overall .o-label { font-size: 13px; color: #a262e6; margin-bottom: 10px; font-weight: 600; }
        .overall .o-text { font-size: 15px; color: #303133; line-height: 1.8; }
        .overall .o-stars { margin-top: 12px; color: #e6a23c; font-size: 18px; letter-spacing: 2px; }
        .overall .o-star-num { font-size: 13px; color: #909399; margin-left: 6px; }

        .disclaimer { font-size: 12px; color: #c0c4cc; text-align: center; margin-top: 8px; }

        @media (max-width: 900px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid #ebeef5; }
        }
    </style>
</head>
<body>
<div class="layout">

    @include('layouts.sidebar', ['activeMenu' => 'fortune'])

    <!-- 右侧主体 -->
    <main class="main">

        <!-- 顶部面包屑 -->
        <div class="topbar">
            实用工具
            <span class="sep">/</span>
            八字算命
        </div>

        <div class="tab-content">

            @php
                $input = $input ?? null;
                $fieldErrors = $fieldErrors ?? [];
            @endphp

            <!-- 输入卡片 -->
            <div class="card">
                <div class="card-title">输入生辰信息</div>
                <div class="card-body">
                    <form method="POST" action="{{ url('/tools/fortune') }}">
                        @csrf
                        <div class="form-row">
                            <div class="field form-group">
                                <label>姓名 <span style="color:#f56c6c;">*</span></label>
                                <input type="text" name="name"
                                       value="{{ $input['name'] ?? '' }}"
                                       placeholder="请输入姓名" maxlength="20">
                                @if(!empty($fieldErrors['name']))
                                    <div class="err-msg">{{ $fieldErrors['name'] }}</div>
                                @endif
                            </div>
                            <div class="field form-group">
                                <label>出生年份</label>
                                <select name="year">
                                    @php($curYear = (int) date('Y'))
                                    @for ($y = 1900; $y <= $curYear; $y++)
                                        <option value="{{ $y }}" @if(($input['year'] ?? 0) == $y) selected @endif>{{ $y }} 年</option>
                                    @endfor
                                </select>
                                @if(!empty($fieldErrors['year']))
                                    <div class="err-msg">{{ $fieldErrors['year'] }}</div>
                                @endif
                            </div>
                            <div class="field form-group">
                                <label>出生月份</label>
                                <select name="month">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" @if(($input['month'] ?? 0) == $m) selected @endif>{{ $m }} 月</option>
                                    @endfor
                                </select>
                                @if(!empty($fieldErrors['month']))
                                    <div class="err-msg">{{ $fieldErrors['month'] }}</div>
                                @endif
                            </div>
                            <div class="field form-group" style="flex: 2; min-width: 220px;">
                                <label>籍贯地址（省 / 市，选填可更准）</label>
                                <input type="text" name="hometown"
                                       value="{{ $input['hometown'] ?? '' }}"
                                       placeholder="例如：河南省郑州市 或 广东广州">
                            </div>
                        </div>
                        <button type="submit" class="btn">🔮 开始测算</button>
                        <div class="btn-tip">同一出生信息的结果是固定的，仅作娱乐参考。</div>
                    </form>
                </div>
            </div>

            <!-- 结果区 -->
            @if ($result)
            <div class="card">
                <div class="card-title">命盘解读</div>
                <div class="card-body">

                    <!-- 命盘概要 -->
                    <div class="f-banner">
                        <div class="f-orb">🔮</div>
                        <div class="f-main">
                            <div class="f-title">{{ $result['birthLine'] }} · {{ $result['stemBranch'] }}年 · 生肖{{ $result['zodiac'] }}</div>
                            <div class="f-sub">
                                命格综合评分 <strong style="color:#a262e6;">{{ $result['overall'] }}</strong> 分
                                @if($result['hasHometown'])
                                    · 籍贯 {{ $result['hometown'] }}
                                @else
                                    · 未填籍贯（方位按"中"土论）
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 基础信息 -->
                    <div class="f-grid">
                        <div class="f-cell">
                            <div class="f-cell-label">天干地支</div>
                            <div class="f-cell-value">{{ $result['stemBranch'] }} 年</div>
                            <span class="f-cell-tag">{{ $result['stemFive'] }} 性</span>
                        </div>
                        <div class="f-cell">
                            <div class="f-cell-label">生肖</div>
                            <div class="f-cell-value">属{{ $result['zodiac'] }}</div>
                            <span class="f-cell-tag">{{ $result['season'] }} 生</span>
                        </div>
                        <div class="f-cell">
                            <div class="f-cell-label">出生季节</div>
                            <div class="f-cell-value">{{ $result['seasonWord'] }}</div>
                            <span class="f-cell-tag">{{ $result['seasonFive'] }} 月令</span>
                        </div>
                        <div class="f-cell">
                            <div class="f-cell-label">籍贯方位</div>
                            <div class="f-cell-value">{{ $result['homeDir'] }} 方</div>
                            <span class="f-cell-tag">{{ $result['homeFive'] }} 气</span>
                        </div>
                    </div>

                    <!-- 大师判词 -->
                    <div class="f-item">
                        <div class="f-text" style="background:#fafbfc;border:1px solid #ebeef5;border-radius:6px;padding:14px 16px;">
                            <strong style="color:#303133;">大师判词：</strong>{{ $result['elementText'] }}
                        </div>
                    </div>

                    <!-- 各项运势 -->
                    <div class="f-item">
                        <div class="f-head"><span class="f-name">💰 财运</span><span class="f-score">{{ $result['fortune'] }} 分</span></div>
                        <div class="f-bar"><i style="width: {{ $result['fortune'] }}%;"></i></div>
                        <div class="f-text">{{ $result['fortuneText'] }}</div>
                    </div>

                    <div class="f-item">
                        <div class="f-head"><span class="f-name">💞 情感</span><span class="f-score">{{ $result['love'] }} 分</span></div>
                        <div class="f-bar"><i style="width: {{ $result['love'] }}%;"></i></div>
                        <div class="f-text">{{ $result['loveText'] }}</div>
                    </div>

                    <div class="f-item">
                        <div class="f-head"><span class="f-name">🚀 事业</span><span class="f-score">{{ $result['career'] }} 分</span></div>
                        <div class="f-bar"><i style="width: {{ $result['career'] }}%;"></i></div>
                        <div class="f-text">{{ $result['careerText'] }}</div>
                    </div>

                    <div class="f-item">
                        <div class="f-head"><span class="f-name">❤️ 健康 · 寿命</span><span class="f-score">{{ $result['health'] }} 分</span></div>
                        <div class="f-bar"><i style="width: {{ $result['health'] }}%;"></i></div>
                        <div class="f-text">{{ $result['healthText'] }}</div>
                    </div>

                    <div class="f-item">
                        <div class="f-head"><span class="f-name">🤝 人缘</span><span class="f-score">{{ $result['social'] }} 分</span></div>
                        <div class="f-bar"><i style="width: {{ $result['social'] }}%;"></i></div>
                        <div class="f-text">{{ $result['socialText'] }}</div>
                    </div>

                    <!-- 总评 -->
                    <div class="overall">
                        <div class="o-label">✒️ 大师一言</div>
                        <div class="o-text">{{ $result['overallText'] }}</div>
                        <div class="o-stars">
                            @for ($s = 1; $s <= 5; $s++)
                                @if ($s <= $result['overallLevel'] + 1) ★ @else ☆ @endif
                            @endfor
                            <span class="o-star-num">（{{ $result['overall'] }} / 100）</span>
                        </div>
                    </div>

                    <div class="disclaimer">※ 本测算仅供娱乐，切勿迷信。命运始终掌握在自己手中。</div>

                </div>
            </div>
            @endif

        </div>

    </main>
</div>
</body>
</html>
