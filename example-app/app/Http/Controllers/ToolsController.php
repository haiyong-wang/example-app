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
                'key'     => 'fortune',
                'name'    => '八字算命',
                'desc'    => '输入出生年月与籍贯，测算财运、情感、寿命等',
                'emoji'   => '🔮',
                'color'   => 'purple',
                'url'     => '/tools/fortune',
                'ready'   => true,
            ],
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

    /**
     * 八字算命工具页
     *
     * GET  /tools/fortune       展示输入表单
     * POST /tools/fortune       提交出生信息并展示测算结果
     *
     * @return \Illuminate\View\View
     */
    public function fortune(\Illuminate\Http\Request $request)
    {
        // 首次进入只展示表单
        if ($request->isMethod('GET')) {
            return view('tools.fortune', [
                'result'      => null,
                'input'       => null,
                'fieldErrors' => [],
            ]);
        }

        // 收集并校验输入
        $year     = (int) $request->input('year');
        $month    = (int) $request->input('month');
        $hometown = trim((string) $request->input('hometown', ''));
        $name     = trim((string) $request->input('name', ''));

        $errors = [];
        if ($year < 1900 || $year > 2100) {
            $errors['year'] = '请填写 1900 - 2100 之间的出生年份';
        }
        if ($month < 1 || $month > 12) {
            $errors['month'] = '请选择正确的出生月份';
        }
        if ($name === '' || mb_strlen($name) > 20) {
            $errors['name'] = '请输入姓名（20 字以内）';
        }
        if (!empty($errors)) {
            return view('tools.fortune', [
                'result'      => null,
                'input'       => ['year' => $year, 'month' => $month, 'hometown' => $hometown, 'name' => $name],
                'fieldErrors' => $errors,
            ]);
        }

        $input  = compact('year', 'month', 'hometown', 'name');
        $result = $this->divine($year, $month, $hometown, $name);

        return view('tools.fortune', ['result' => $result, 'input' => $input]);
    }

    /**
     * 生成命理测算结果（娱乐向，结果由出生年月+姓名确定性生成）
     *
     * @param int    $year     出生年份
     * @param int    $month    出生月份
     * @param string $hometown 籍贯地址
     * @param string $name     姓名
     * @return array
     */
    private function divine($year, $month, $hometown, $name)
    {
        // ---------- 基础信息 ----------
        // 生肖（以农历年为界近似，公历年份对应当年生肖）
        $zodiacs = ['鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪'];
        $zodiac = $zodiacs[(($year - 1900) % 12 + 12) % 12];

        // 天干地支年柱
        $stems = ['甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'];
        $branches = ['子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'];
        // 干支纪年：1984 年为甲子，公元年换算干支序号 = (year - 4) % 60
        $idx = (($year - 4) % 60 + 60) % 60;
        $stem = $stems[$idx % 10];
        $branch = $branches[$idx % 12];
        $stemBranch = $stem . $branch;

        // 出生季节（按月）与对应五行
        $seasons = [
            1 => ['季' => '冬', '五行' => '水', '意象' => '深冬'],
            2 => ['季' => '春', '五行' => '木', '意象' => '初春'],
            3 => ['季' => '春', '五行' => '木', '意象' => '仲春'],
            4 => ['季' => '春', '五行' => '木', '意象' => '暮春'],
            5 => ['季' => '夏', '五行' => '火', '意象' => '初夏'],
            6 => ['季' => '夏', '五行' => '火', '意象' => '仲夏'],
            7 => ['季' => '夏', '五行' => '火', '意象' => '盛夏'],
            8 => ['季' => '秋', '五行' => '金', '意象' => '初秋'],
            9 => ['季' => '秋', '五行' => '金', '意象' => '仲秋'],
            10 => ['季' => '秋', '五行' => '金', '意象' => '深秋'],
            11 => ['季' => '冬', '五行' => '水', '意象' => '初冬'],
            12 => ['季' => '冬', '五行' => '水', '意象' => '隆冬'],
        ];
        $season = $seasons[$month];

        // 天干对应五行
        $stemElement = ['甲' => '木', '乙' => '木', '丙' => '火', '丁' => '火', '戊' => '土',
                        '己' => '土', '庚' => '金', '辛' => '金', '壬' => '水', '癸' => '水'];
        $stemFive = $stemElement[$stem];

        // 籍贯方位（粗略按行政区关键词匹配）
        $directions = [
            '北' => ['水', ['北京', '河北', '山西', '内蒙古', '黑龙江', '吉林', '辽宁', '天津', '宁夏', '陕西']],
            '南' => ['火', ['广东', '广西', '海南', '福建', '云南', '贵州', '湖南', '江西', '台湾', '香港', '澳门']],
            '东' => ['木', ['上海', '江苏', '浙江', '安徽', '山东']],
            '西' => ['金', ['新疆', '西藏', '青海', '甘肃', '四川', '重庆']],
            '中' => ['土', ['河南', '湖北', '重庆']],
        ];
        $homeDir = '中';
        $homeFive = '土';
        if ($hometown !== '') {
            foreach ($directions as $dir => $item) {
                foreach ($item[1] as $kw) {
                    if (mb_strpos($hometown, $kw) !== false) {
                        $homeDir = $dir;
                        $homeFive = $item[0];
                        break 2;
                    }
                }
            }
        }

        // ---------- 确定性"命数"打分（同一输入结果永远一致） ----------
        // 姓名编码：取前两字的 Unicode 码点求和，使姓名真正影响结果
        $nameCode = 0;
        foreach (preg_split('//u', $name, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
            $nameCode += (int) hexdec(substr(bin2hex(mb_convert_encoding($ch, 'UCS-2BE', 'UTF-8')), 0, 4));
        }
        $seed = ($year * 100) + $month + (mb_strlen($hometown) * 7) + $nameCode;
        $seed = (int) (abs((int) ($seed)) % 1000000);

        // 计算各宫分数（0-100），用伪随机但有规律的方式生成
        $hash = function ($salt) use ($seed) {
            $n = ($seed * 9301 + $salt * 49297) % 233280;
            $v = ($n % 10000) / 100; // 0~99.99
            return (int) round($v);
        };

        $star = $hash(1);      // 命主根基
        $fortune = $hash(2);   // 财运
        $love = $hash(3);      // 情感
        $career = $hash(4);    // 事业
        $health = $hash(5);    // 健康 / 寿命
        $social = $hash(6);    // 人缘

        // 五行、季节对方位形成"生克"加成微调，让籍贯真正影响结果
        $woodFence = $this->elementScore($fortune, $season['五行'], $homeFive);

        // 文案池：按分数档位取评语
        $level = function ($score) {
            if ($score >= 85) return 4;
            if ($score >= 70) return 3;
            if ($score >= 55) return 2;
            if ($score >= 40) return 1;
            return 0;
        };

        $element = $this->pick($hash, 10, [
            '命理五行以' . $stemFive . '为主、生于' . $season['意象'] . '的' . $season['五行'] . '月，气息沉稳，根基初定。',
            '日月相参，' . $stemBranch . '之命盘星曜有序，一生起伏有章可循。',
            '五行流转中' . $homeFive . '气驻守，故为人朴实持重，行事有恒。',
        ]);

        // 财运
        $moneyTexts = [
            '财帛宫暗淡，多靠稳扎稳打积财，切忌投机冒进。',
            '求财之路平顺，正财为主，开源之余亦要守住本心。',
            '财星得位，理财有道，中年后常有意外之喜。',
            '财库丰盈，正偏财皆有收获，是为聚财之命。',
            '天生财缘旺盛，长袖善舞，屡有横财相伴。',
        ];
        // 情感
        $loveTexts = [
            '情路多有波折，宜慢热交心，勿以貌取人。',
            '感情细腻而内敛，需主动沟通方能细水长流。',
            '桃花星时明时暗，缘来缘去皆有其时，顺其自然。',
            '夫妻宫和顺，深情长伴，白头可期。',
            '桃花运炽热，情场得意，唯防多情反被多情误。',
        ];
        // 事业
        $careerTexts = [
            '事业之途始于微末，贵在持之以恒，迟开的花朵亦芬芳。',
            '谋事虽多波折，然贵人有助，屡败屡战终见柳暗花明。',
            '中规中矩，稳中有进，宜守成而后图发展。',
            '才思敏捷，得伯乐提携，事业如日中天。',
            '天时地利俱佳，能成一番事业，名望渐隆。',
        ];
        // 健康/寿命
        $healthTexts = [
            '素体易感风寒，宜早睡早起、注重养生以固本培元。',
            '体魄尚可，然操劳过度易伤神，切记劳逸结合。',
            '精气神充盈，少病少灾，作息规律自可延年。',
            '天生一副硬朗身骨，寿元绵长，唯忌烟酒无度。',
            '气血充盈、福泽深厚，此乃福寿双全之相。',
        ];
        // 人缘
        $socialTexts = [
            '性情内敛寡言，知己虽少，然交心者皆可托付。',
            '待人真诚，朋友不多却情谊长久。',
            '圆融通达，左右逢源，常为人际之中枢。',
            '性情爽朗，四海皆友，走到哪里都不乏助力。',
            '贵气随身，众人拥戴，人脉即是最大的财富。',
        ];

        // 总评
        $overallScore = (int) round(($star * 0.2 + $fortune * 0.2 + $career * 0.2 + $health * 0.2 + $love * 0.1 + $social * 0.1));
        $overallLevel = $level($overallScore);
        $overallTexts = [
            '命途虽曲，贵在坚韧。守得云开见月明，此命晚景渐佳。',
            '一生平淡中自有福气，但行好事莫问前程。',
            '命格中上，循序而进，福泽自会水到渠成。',
            '命局不凡，只要善用其势，必能鹏程万里。',
            '天选之才，气运所钟，乃人中龙凤、大成之命。',
        ];

        $result = [
            'year'        => $year,
            'month'       => $month,
            'hometown'    => $hometown,
            'name'        => $name,
            'zodiac'      => $zodiac,
            'stemBranch'  => $stemBranch,
            'stemFive'    => $stemFive,
            'season'      => $season['季'],
            'seasonFive'  => $season['五行'],
            'seasonWord'  => $season['意象'],
            'homeDir'     => $homeDir,
            'homeFive'    => $homeFive,
            'hasHometown' => $hometown !== '',
            'star'        => $star,
            'fortune'     => $fortune,
            'love'        => $love,
            'career'      => $career,
            'health'      => $health,
            'social'      => $social,
            'overall'     => $overallScore,
            'overallLevel'=> $overallLevel,
            'elementText' => $element,
            'fortuneText' => $moneyTexts[$level($fortune)],
            'loveText'    => $loveTexts[$level($love)],
            'careerText'  => $careerTexts[$level($career)],
            'healthText'  => $healthTexts[$level($health)],
            'socialText'  => $socialTexts[$level($social)],
            'overallText' => $overallTexts[$overallLevel],
            'birthLine'   => $name . ' · 生于 ' . $year . ' 年 ' . $month . ' 月',
        ];

        return $result;
    }

    /**
     * 从文案池中按稳定的哈希取一条
     */
    private function pick(\Closure $hash, $salt, array $pool)
    {
        return $pool[$hash($salt) % count($pool)];
    }

    /**
     * 五行生克简单加成（娱乐向）：生我者加分，克我者减分
     */
    private function elementScore($score, $seasonFive, $homeFive)
    {
        // 简化生克关系
        $generates = ['木' => '火', '火' => '土', '土' => '金', '金' => '水', '水' => '木'];
        $delta = 0;
        if ($generates[$homeFive] ?? '' === $seasonFive) {
            $delta = 6;
        } elseif (($generates[$seasonFive] ?? '') === $homeFive) {
            $delta = 4;
        }
        return min(100, $score + $delta);
    }
}
