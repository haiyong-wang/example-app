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

        // ---------- 文案池（扩充版） ----------
        // 每个维度按档位 0(差)~4(好) 分组，每档多条句子；
        // 展示时先由分数落到档位，再在档位内用稳定哈希取一条，
        // 保证同一输入结果固定，同时让句子库足够大、同档也不再只有一句。
        $moneyTexts = [  // 财运
            [ // 档0 score < 40
                '财帛宫暗淡，多靠稳扎稳打积财，切忌投机冒进。',
                '正财不显，家底平平，宜节俭度日、少做风险之注。',
                '财星无力，求财需靠苦功，切莫轻信一夜暴富之说。',
            ],
            [ // 档1 40 ~ 54
                '求财之路平顺，正财为主，开源之余亦要守住本心。',
                '财源尚可但无偏财，稳中求进方能积少成多。',
                '财帛平平却有章法，量入为出，日子自可宽裕。',
            ],
            [ // 档2 55 ~ 69
                '财星得位，理财有道，中年后常有意外之喜。',
                '正财顺遂，偶有偏财点缀，善用其财则家业渐丰。',
                '聚财有方，财运稳步上行，宜早立理财之志。',
            ],
            [ // 档3 70 ~ 84
                '财库丰盈，正偏财皆有收获，是为聚财之命。',
                '财气通达，眼光独到，投资置业多能落袋为安。',
                '财帛宫光显，正偏两旺，财来有源、去亦有度。',
            ],
            [ // 档4 score >= 85
                '天生财缘旺盛，长袖善舞，屡有横财相伴。',
                '财星照命，一生衣禄无忧，钱财如江河奔涌不竭。',
                '命带聚宝盆，财帛宫满，富足之命、金玉盈门。',
            ],
        ];
        $loveTexts = [  // 情感
            [ // 档0
                '情路多有波折，宜慢热交心，勿以貌取人。',
                '姻缘星暗淡，好事多磨，切莫因一时执念误了良缘。',
                '情关难渡，易有聚散离合，需修心养性以待正缘。',
            ],
            [ // 档1
                '感情细腻而内敛，需主动沟通方能细水长流。',
                '桃花不旺但贵在真诚，缘到自然相知相惜。',
                '情愫暗生却羞于表露，鼓起勇气方不辜负良缘。',
            ],
            [ // 档2
                '桃花星时明时暗，缘来缘去皆有其时，顺其自然。',
                '感情平稳之中略带涟漪，用心经营自能开花结果。',
                '待人以诚，情缘不请自来，姻缘在天、相处在人。',
            ],
            [ // 档3
                '夫妻宫和顺，深情长伴，白头可期。',
                '红鸾照命，姻缘和美，良人相伴一生和顺。',
                '情缘深厚，能得佳偶，伉俪情深、家和万事兴。',
            ],
            [ // 档4
                '桃花运炽热，情场得意，唯防多情反被多情误。',
                '红鸾星动、桃花满枝，人见人爱，众星捧月之相。',
                '情缘极佳，天作之合，一生缠绵、恩爱有加。',
            ],
        ];
        $careerTexts = [  // 事业
            [ // 档0
                '事业之途始于微末，贵在持之以恒，迟开的花朵亦芬芳。',
                '前途多有阻滞，宜厚积薄发，勿轻言放弃。',
                '时运未至，事业多磨，守拙务实方有出头之日。',
            ],
            [ // 档1
                '谋事虽多波折，然贵人有助，屡败屡战终见柳暗花明。',
                '事业根基尚浅，胜在勤勉，步步为营可望渐入佳境。',
                '能力有余而机遇未显，耐心等待，自有水到渠成之时。',
            ],
            [ // 档2
                '中规中矩，稳中有进，宜守成而后图发展。',
                '职业顺遂平稳，勤恳务实，循序而进亦能小有所成。',
                '做事有条不紊，得同僚信赖，事业稳中有升。',
            ],
            [ // 档3
                '才思敏捷，得伯乐提携，事业如日中天。',
                '仕途商场皆有贵人相扶，志向远大，前景可期。',
                '能力出众、人脉得用，事业蒸蒸日上、声名渐起。',
            ],
            [ // 档4
                '天时地利俱佳，能成一番事业，名望渐隆。',
                '事业运极旺，气贯长虹，建功立业、指日可待。',
                '命带将星，主掌一方，功成名就、前程不可限量。',
            ],
        ];
        $healthTexts = [  // 健康/寿命
            [ // 档0
                '素体易感风寒，宜早睡早起、注重养生以固本培元。',
                '体气偏弱，忌过劳透支，四季调养切莫大意。',
                '小病不断，起居饮食皆须留意，以防邪气侵体。',
            ],
            [ // 档1
                '体魄尚可，然操劳过度易伤神，切记劳逸结合。',
                '底子不差，唯少运动、多思虑，宜动养结合。',
                '健康平平，重在持之以恒的作息与适度锻炼。',
            ],
            [ // 档2
                '精气神充盈，少病少灾，作息规律自可延年。',
                '身体康健，中气十足，坚持锻炼则福泽绵长。',
                '体质稳健，小恙不扰，起居有常自得安康。',
            ],
            [ // 档3
                '天生一副硬朗身骨，寿元绵长，唯忌烟酒无度。',
                '筋骨强健、精神矍铄，调养得宜可望高寿。',
                '气血调和，百病难侵，是少有的康健之格。',
            ],
            [ // 档4
                '气血充盈、福泽深厚，此乃福寿双全之相。',
                '先天禀赋极佳，元气充沛，寿元绵长、安康到老。',
                '身心俱健，无病无灾，老而弥坚、享颐年之乐。',
            ],
        ];
        $socialTexts = [  // 人缘
            [ // 档0
                '性情内敛寡言，知己虽少，然交心者皆可托付。',
                '不善周旋，易得罪人而不自知，宜多换位思考。',
                '人缘清淡，宜以诚示人，日久自见真朋友。',
            ],
            [ // 档1
                '待人真诚，朋友不多却情谊长久。',
                '相交不广但情义深，守得住分寸、留得住人心。',
                '性情稍显拘谨，多一分主动便多一分善缘。',
            ],
            [ // 档2
                '圆融通达，左右逢源，常为人际之中枢。',
                '待人和气、进退有度，人缘不俗，处处有照应。',
                '言语得体、乐于助人，朋友圈小而稳固。',
            ],
            [ // 档3
                '性情爽朗，四海皆友，走到哪里都不乏助力。',
                '交友甚广，贵人环绕，遇事常有人挺身相助。',
                '能说会道又重情义，人脉渐广、口碑日隆。',
            ],
            [ // 档4
                '贵气随身，众人拥戴，人脉即是最大的财富。',
                '众望所归，一呼百应，贵人之运随身常伴。',
                '人缘极旺、八方来助，行走江湖处处逢源。',
            ],
        ];

        // 在对应档位句池内，按稳定哈希挑一条，保证同输入固定、库大而不雷同
        $pickInLevel = function ($dimSalt, array $pool, $lvl) use ($hash) {
            $rows = $pool[$lvl] ?? $pool[count($pool) - 1];
            return $rows[$hash($dimSalt) % count($rows)];
        };

        $fortuneText = $pickInLevel(20, $moneyTexts, $level($fortune));
        $loveText    = $pickInLevel(21, $loveTexts, $level($love));
        $careerText  = $pickInLevel(22, $careerTexts, $level($career));
        $healthText  = $pickInLevel(23, $healthTexts, $level($health));
        $socialText  = $pickInLevel(24, $socialTexts, $level($social));

        // 总评
        $overallScore = (int) round(($star * 0.2 + $fortune * 0.2 + $career * 0.2 + $health * 0.2 + $love * 0.1 + $social * 0.1));
        $overallLevel = $level($overallScore);
        $overallTexts = [ // 总评句库（每档多条）
            [ // 档0
                '命途虽曲，贵在坚韧。守得云开见月明，此命晚景渐佳。',
                '起点虽低，然愈挫愈勇，天道酬勤、后福可期。',
                '格局平平，但胜在踏实，熬过困顿便是云淡风轻。',
            ],
            [ // 档1
                '一生平淡中自有福气，但行好事莫问前程。',
                '福虽不厚却细水长流，平顺之中自有安稳可倚。',
                '中平之命，知足常乐，勤恳持家亦可安享余年。',
            ],
            [ // 档2
                '命格中上，循序而进，福泽自会水到渠成。',
                '命途渐入佳境，稳中向好，福运随年岁而丰。',
                '运势平稳上扬，把握时机，自能步步登高。',
            ],
            [ // 档3
                '命局不凡，只要善用其势，必能鹏程万里。',
                '格局出众、贵人相助，心怀大志则前路光明。',
                '福星高照，诸事顺遂，稍加努力便可出人头地。',
            ],
            [ // 档4
                '天选之才，气运所钟，乃人中龙凤、大成之命。',
                '命格贵显，财官双美，一生福禄双全、贵不可言。',
                '气运冲霄，天地人和，此命贵为万中无一之格。',
            ],
        ];
        $overallText = $pickInLevel(25, $overallTexts, $overallLevel);

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
            'fortuneText' => $fortuneText,
            'loveText'    => $loveText,
            'careerText'  => $careerText,
            'healthText'  => $healthText,
            'socialText'  => $socialText,
            'overallText' => $overallText,
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
