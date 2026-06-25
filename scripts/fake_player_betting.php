<?php
/**
 * PC28 假真人自动下注系统 (增强版)
 * 特点：
 * 1. 随机成语作为昵称，模拟真实玩家
 * 2. 自动注册假真人账号
 * 3. 自动下注，支持高低倍房
 * 4. 模拟真实下注行为（金额、玩法随机）
 * 5. 自动发送群聊消息，营造氛围
 *
 * 使用方法：php fake_player_betting.php [count] [room_type]
 * 示例：php fake_player_betting.php 5 low
 */

require_once __DIR__ . '/../src/Utils/DB.php';
require_once __DIR__ . '/../src/Model/Lottery.php';

$db = \App\Utils\DB::getInstance()->getConnection();

// ========== 成语库（300+个常用成语，适合作为昵称）==========
$idioms = [
    '一帆风顺','二龙戏珠','三阳开泰','四季平安','五福临门',
    '六六大顺','七星高照','八方来财','九九归一','十全十美',
    '百年好合','千娇百媚','万事如意','心想事成','前程似锦',
    '步步高升','平步青云','马到成功','金榜题名','紫气东来',
    '财源广进','年年有余','大吉大利','万事如意','恭喜发财',
    '花开富贵','龙凤呈祥','金玉满堂','福星高照','喜气洋洋',
    '笑口常开','鸿运当头','时来运转','否极泰来','苦尽甘来',
    '柳暗花明','峰回路转','绝处逢生','转危为安','逢凶化吉',
    '遇难成祥','化险为夷','雪中送炭','锦上添花','如虎添翼',
    '如鱼得水','龙腾虎跃','生龙活虎','龙马精神','虎虎生威',
    '鹤立鸡群','出类拔萃','卓尔不群','独树一帜','别具一格',
    '标新立异','推陈出新','革故鼎新','破旧立新','改天换地',
    '翻天覆地','沧海桑田','日新月异','突飞猛进','一日千里',
    '蒸蒸日上','欣欣向荣','繁荣昌盛','国泰民安','风调雨顺',
    '五谷丰登','六畜兴旺','人寿年丰','安居乐业','丰衣足食',
    '家给人足','衣食无忧','锦衣玉食','钟鸣鼎食','山珍海味',
    '美味佳肴','口齿留香','回味无穷','垂涎三尺','食指大动',
    '大快朵颐','酒足饭饱','饱食终日','吃喝玩乐','游山玩水',
    '闲情逸致','悠然自得','怡然自乐','自得其乐','乐在其中',
    '乐不思蜀','流连忘返','恋恋不舍','依依不舍','难舍难分',
    '情深意重','情真意切','真情实意','真心实意','诚心诚意',
    '全心全意','一心一意','专心致志','聚精会神','全神贯注',
    '目不转睛','屏气凝神','心无旁骛','一心一意','始终如一',
    '持之以恒','坚持不懈','锲而不舍','坚韧不拔','百折不挠',
    '不屈不挠','顽强拼搏','奋发图强','自强不息','力争上游',
    '勇往直前','义无反顾','破釜沉舟','背水一战','孤注一掷',
    '铤而走险','临危不惧','处变不惊','镇定自若','从容不迫',
    '泰然处之','安之若素','稳如泰山','坚如磐石','固若金汤',
    '铜墙铁壁','牢不可破','坚不可摧','无懈可击','天衣无缝',
    '完美无缺','白璧微瑕','美中不足','瑕不掩瑜','瑜不掩瑕',
    '取长补短','扬长避短','去粗取精','去伪存真','披沙拣金',
    '大浪淘沙','优胜劣汰','适者生存','物竞天择','自然选择',
    '弱肉强食','丛林法则','成王败寇','胜者为王','败者为寇',
    '优胜劣汰','适者生存','强者恒强','弱者恒弱','两极分化',
    '贫富差距','富可敌国','腰缠万贯','家财万贯','富甲一方',
    '锦衣玉食','衣锦还乡','荣归故里','光宗耀祖','耀祖光宗',
    '封妻荫子','泽被后世','流芳百世','名垂青史','永垂不朽',
    '万古流芳','千秋万代','世代相传','薪火相传','一脉相承',
    '继往开来','承前启后','承上启下','古今中外','天南地北',
    '四面八方','五湖四海','九州大地','华夏儿女','炎黄子孙',
    '龙的传人','中华儿女','华夏文明','礼仪之邦','文明古国',
    '历史悠久','源远流长','博大精深','源远流长','底蕴深厚',
    '积厚流光','厚积薄发','大器晚成','后发制人','后来居上',
    '青出于蓝','冰寒于水','更胜一筹','技高一筹','棋高一着',
    '高人一等','出类拔萃','鹤立鸡群','独占鳌头','首屈一指',
    '名列前茅','数一数二','百里挑一','凤毛麟角','人中龙凤',
    '人中豪杰','英雄好汉','侠肝义胆','义薄云天','正气凛然',
    '刚正不阿','铁面无私','大公无私','克己奉公','廉洁奉公',
    '两袖清风','一尘不染','洁身自好','独善其身','明哲保身',
    '但求无过','得过且过','随遇而安','顺其自然','听天由命',
    '乐天知命','安贫乐道','知足常乐','小富即安','安于现状',
    '不思进取','固步自封','墨守成规','因循守旧','抱残守缺',
    '陈陈相因','萧规曹随','依样画葫芦','照猫画虎','生搬硬套',
    '削足适履','东施效颦','邯郸学步','鹦鹉学舌','人云亦云',
    '拾人牙慧','抄袭剽窃','弄虚作假','招摇撞骗','欺世盗名',
    '沽名钓誉','名不副实','徒有虚名','虚有其表','金玉其外',
    '败絮其中','外强中干','色厉内荏','外厉内荏','羊质虎皮',
    '绣花枕头','银样镴枪','中看不中用','华而不实','虚张声势',
    '装腔作势','装模作样','矫揉造作','无病呻吟','故弄玄虚',
    '莫测高深','高深莫测','讳莫如深','守口如瓶','秘而不宣',
    '三缄其口','沉默寡言','寡言少语','不善言辞','笨嘴拙舌',
    '拙嘴笨腮','张口结舌','哑口无言','无言以对','无话可说',
    '理屈词穷','词不达意','言不由衷','口是心非','心口不一',
    '阳奉阴违','两面三刀','口蜜腹剑','笑里藏刀','绵里藏针',
    '佛口蛇心','蛇蝎心肠','狼心狗肺','心如蛇蝎','心狠手辣',
    '丧心病狂','丧尽天良','灭绝人性','惨无人道','惨绝人寰',
    '令人发指','怒不可遏','义愤填膺','满腔怒火','怒火中烧',
    '火冒三丈','暴跳如雷','大发雷霆','雷霆万钧','排山倒海',
    '摧枯拉朽','势如破竹','锐不可当','所向披靡','所向无敌',
    '战无不胜','攻无不克','百战百胜','屡战屡捷','旗开得胜',
    '马到成功','一蹴而就','一举成功','一鸣惊人','一飞冲天',
    '平步青云','扶摇直上','青云直上','步步高升','蒸蒸日上',
    '欣欣向荣','繁荣昌盛','国泰民安','天下太平','四海升平',
    '歌舞升平','太平盛世','盛世华章','锦绣河山','大好河山',
    '江山如画','风景如画','美不胜收','目不暇接','眼花缭乱',
    '五彩缤纷','五彩斑斓','绚丽多彩','多姿多彩','五颜六色',
    '五光十色','万紫千红','花团锦簇','繁花似锦','百花齐放',
    '百家争鸣','百家齐放','争奇斗艳','竞相开放','含苞待放',
    '含苞欲放','蓓蕾初绽','初露锋芒','崭露头角','脱颖而出',
    '一鸣惊人','一举成名','一炮而红','一夜爆红','声名鹊起',
    '声名远播','名扬四海','闻名遐迩','遐迩闻名','家喻户晓',
    '妇孺皆知','众所周知','众所周知','路人皆知','司马昭之心',
    '昭然若揭','暴露无遗','原形毕露','真相大白','水落石出',
    '拨云见日','云开雾散','雨过天晴','风平浪静','海阔天空',
    '天高云淡','风和日丽','阳光明媚','万里无云','晴空万里',
    '碧空如洗','一碧万顷','水天一色','波光粼粼','水平如镜',
    '风平浪静','惊涛骇浪','波涛汹涌','波澜壮阔','浩浩荡荡',
    '汹涌澎湃','气势磅礴','气势恢宏','大气磅礴','气吞山河',
    '气壮山河','声势浩大','大张旗鼓','轰轰烈烈','如火如荼',
    '热火朝天','干劲十足','斗志昂扬','意气风发','精神抖擞',
    '神采奕奕','容光焕发','满面春风','春风得意','喜气洋洋',
    '欢天喜地','兴高采烈','欣喜若狂','喜出望外','喜极而泣',
    '乐极生悲','悲喜交加','哭笑不得','啼笑皆非','忍俊不禁',
    '捧腹大笑','开怀大笑','哈哈大笑','前仰后合','东倒西歪',
    '人仰马翻','狼狈不堪','灰头土脸','垂头丧气','无精打采',
    '萎靡不振','一蹶不振','一败涂地','落花流水','溃不成军',
    '丢盔弃甲','落荒而逃','抱头鼠窜','逃之夭夭','远走高飞',
    '不辞而别','拂袖而去','扬长而去','一走了之','溜之大吉',
    '金蝉脱壳','瞒天过海','暗度陈仓','声东击西','调虎离山',
    '围魏救赵','釜底抽薪','擒贼擒王','打草惊蛇','引蛇出洞',
    '欲擒故纵','放长线钓大鱼','守株待兔','刻舟求剑','掩耳盗铃',
    '买椟还珠','画蛇添足','多此一举','弄巧成拙','适得其反',
    '事与愿违','南辕北辙','背道而驰','缘木求鱼','水中捞月',
    '镜花水月','海市蜃楼','空中楼阁','子虚乌有','无中生有',
    '空穴来风','捕风捉影','道听途说','以讹传讹','三人成虎',
    '众口铄金','积毁销骨','人言可畏','众口难调','众说纷纭',
    '莫衷一是','无所适从','不知所措','手足无措','束手无策',
    '无计可施','黔驴技穷','江郎才尽','才疏学浅','孤陋寡闻',
    '见识短浅','目光如豆','鼠目寸光','井底之蛙','坐井观天',
    '管中窥豹','可见一斑','一叶知秋','见微知著','以小见大',
    '由表及里','深入浅出','通俗易懂','平易近人','和蔼可亲',
    '和颜悦色','慈眉善目','眉开眼笑','笑容可掬','笑逐颜开',
    '喜笑颜开','心花怒放','欣喜若狂','手舞足蹈','载歌载舞',
    '欢欣鼓舞','兴高采烈','喜气洋洋','欢天喜地','普天同庆',
    '举国欢腾','万众欢腾','欢呼雀跃','雀跃不已','手舞足蹈',
    '眉飞色舞','神采飞扬','意气风发','斗志昂扬','精神抖擞',
    '精神焕发','容光焕发','满面红光','红光满面','春风满面',
    '得意洋洋','趾高气扬','不可一世','目空一切','目中无人',
    '妄自尊大','自高自大','自命不凡','自以为是','刚愎自用',
    '独断专行','一意孤行','我行我素','固执己见','执迷不悟',
    '顽固不化','死不悔改','屡教不改','怙恶不悛','恶贯满盈',
    '罪大恶极','十恶不赦','罪恶滔天','罪孽深重','罄竹难书',
    '擢发难数','数不胜数','不计其数','不可胜数','多如牛毛',
    '汗牛充栋','浩如烟海','恒河沙数','车载斗量','不胜枚举',
    '举不胜举','比比皆是','俯拾皆是','触目皆是','随处可见',
    '司空见惯','习以为常','屡见不鲜','数见不鲜','家常便饭',
    '不足为奇','见怪不怪','习以为常','习焉不察','熟视无睹',
    '视而不见','听而不闻','置若罔闻','漠不关心','漠然置之',
    '不闻不问','不理不睬','冷若冰霜','冷酷无情','铁石心肠',
    '心如铁石','蛇蝎心肠','心狠手辣','六亲不认','无情无义',
    '薄情寡义','忘恩负义','恩将仇报','过河拆桥','卸磨杀驴',
    '鸟尽弓藏','兔死狗烹','过河拆桥','上屋抽梯','落井下石',
    '趁火打劫','浑水摸鱼','顺手牵羊','偷鸡摸狗','鸡鸣狗盗',
    '鼠窃狗偷','梁上君子','江洋大盗','汪洋大盗','绿林好汉',
    '草莽英雄','英雄豪杰','侠义之士','江湖好汉','武林高手',
    '身怀绝技','武艺超群','十八般武艺','样样精通','文武双全',
    '才貌双全','德才兼备','品学兼优','德艺双馨','德高望重',
    '众望所归','人心所向','深得人心','一呼百应','应者云集',
    '云集响应','响应云集','从者如云','趋之若鹜','如蚁附膻',
    '臭味相投','同流合污','狼狈为奸','沆瀣一气','朋比为奸',
    '结党营私','拉帮结派','党同伐异','排除异己','顺我者昌',
    '逆我者亡','唯我独尊','唯命是从','俯首帖耳','唯唯诺诺',
    '低三下四','卑躬屈膝','奴颜婢膝','摇尾乞怜','乞哀告怜',
    '摇尾乞怜','胁肩谄笑','阿谀奉承','溜须拍马','曲意逢迎',
    '投其所好','见风使舵','随机应变','见机行事','相机而动',
    '伺机而动','伺机而动','待机而动','蓄势待发','厚积薄发',
    '养精蓄锐','休养生息','安居乐业','休戚与共','同舟共济',
    '患难与共','风雨同舟','相濡以沫','相敬如宾','举案齐眉',
    '琴瑟和鸣','琴瑟和谐','夫唱妇随','比翼双飞','白头偕老',
    '百年好合','永结同心','天作之合','佳偶天成','郎才女貌',
    '才子佳人','金童玉女','天生一对','地设一双','门当户对',
    '珠联璧合','相得益彰','相辅相成','相辅相成','缺一不可',
    '相得益彰','交相辉映','相映成趣','相映生辉','熠熠生辉',
    '光彩夺目','光芒四射','熠熠生辉','璀璨夺目','绚丽多彩',
    '五彩缤纷','五彩斑斓','五光十色','万紫千红','姹紫嫣红',
    '花红柳绿','桃红柳绿','柳绿花红','春暖花开','春光明媚',
    '春意盎然','春色满园','春回大地','万物复苏','生机勃勃',
    '生机盎然','生气勃勃','朝气蓬勃','活力四射','精力充沛',
    '精神饱满','神采奕奕','容光焕发','满面春风','春风得意',
    '意气风发','斗志昂扬','精神抖擞','精神焕发','容光焕发',
    '神采飞扬','眉飞色舞','喜形于色','喜上眉梢','眉开眼笑',
    '笑逐颜开','喜笑颜开','心花怒放','欣喜若狂','喜极而泣',
    '乐极生悲','悲喜交加','哭笑不得','啼笑皆非','忍俊不禁',
    '捧腹大笑','开怀大笑','哈哈大笑','前仰后合','东倒西歪',
    '人仰马翻','狼狈不堪','灰头土脸','垂头丧气','无精打采',
    '萎靡不振','一蹶不振','一败涂地','落花流水','溃不成军',
    '丢盔弃甲','落荒而逃','抱头鼠窜','逃之夭夭','远走高飞'
];

// ========== 玩法映射 ==========
$playTypeMap = [
    'big' => '大', 'small' => '小', 'single' => '单', 'double' => '双',
    'big_single' => '大单', 'big_double' => '大双', 'small_single' => '小单', 'small_double' => '小双',
    'extreme_big' => '极大', 'extreme_small' => '极小', 'pair' => '对子', 'straight' => '顺子', 'triple' => '豹子'
];

$reversePlayTypeMap = array_flip($playTypeMap);

// ========== 配置参数 ==========
$count = isset($argv[1]) ? (int)$argv[1] : 3;        // 每次运行下注的假真人数量
$roomType = isset($argv[2]) ? $argv[2] : 'random';   // 房间类型: low, high, random
$minBalance = 1000;                                  // 假真人最低余额
$maxBalance = 50000;                                 // 假真人最高余额

// ========== 获取最新期号 ==========
$latest = \App\Model\Lottery::getLatest();
if (!$latest) {
    die("[ERROR] 无开奖数据，无法下注。\n");
}

$now = time();
$nextDrawTs = strtotime($latest['next_draw_at']);
$countdown = $nextDrawTs - $now;

// 计算下注期号
$issueNumStr = $latest['issue_no'];
if (is_numeric($issueNumStr)) {
    $betIssue = (string)((int)$issueNumStr + 1);
} else {
    preg_match('/(\d+)$/', $issueNumStr, $matches);
    $betIssue = $matches ? substr($issueNumStr, 0, -strlen($matches[1])) . ((int)$matches[1] + 1) : $issueNumStr;
}

echo "[INFO] 当前期号: {$latest['issue_no']}, 下注期号: {$betIssue}, 倒计时: {$countdown}秒\n";

// 封盘前20秒停止下注
if ($countdown <= 20) {
    die("[INFO] 已封盘或即将封盘，停止下注。\n");
}

// ========== 辅助函数 ==========

/**
 * 随机获取成语昵称
 */
function getRandomIdiom($idioms) {
    return $idioms[array_rand($idioms)];
}

/**
 * 生成随机用户名
 */
function generateUsername() {
    $prefix = 'player_';
    $suffix = bin2hex(random_bytes(4));
    return $prefix . $suffix;
}

/**
 * 生成随机QQ号
 */
function generateQQ() {
    return (string)mt_rand(100000000, 999999999);
}

/**
 * 生成随机密码
 */
function generatePassword() {
    return hash('sha256', random_bytes(16));
}

/**
 * 获取或创建假真人用户
 */
function getOrCreateFakePlayer($db, $idioms, $minBalance, $maxBalance) {
    // 先尝试获取现有的假真人（非机器人但用于自动下注的账号）
    $stmt = $db->prepare("SELECT id, nickname, balance FROM users WHERE is_robot = 0 AND username LIKE 'player_%' ORDER BY RAND() LIMIT 1");
    $stmt->execute();
    $existing = $stmt->fetch();

    if ($existing && $existing['balance'] > 10) {
        return $existing;
    }

    // 创建新的假真人
    $username = generateUsername();
    $nickname = getRandomIdiom($idioms);
    $password = generatePassword();
    $qq = generateQQ();
    $balance = mt_rand($minBalance, $maxBalance);

    try {
        $stmt = $db->prepare("INSERT INTO users (username, nickname, password, qq_number, balance, status, role, is_robot) VALUES (?, ?, ?, ?, ?, 1, 'user', 0)");
        $stmt->execute([$username, $nickname, $password, $qq, $balance]);
        $userId = $db->lastInsertId();

        echo "[CREATE] 新假真人: {$nickname} ({$username}), 余额: {$balance}\n";

        return [
            'id' => $userId,
            'nickname' => $nickname,
            'balance' => $balance
        ];
    } catch (Exception $e) {
        echo "[ERROR] 创建用户失败: " . $e->getMessage() . "\n";
        return null;
    }
}

/**
 * 获取赔率
 */
function getOdds($db, $playType, $oddsType) {
    $oddsField = ($oddsType === 'high') ? 'odds_high' : 'odds_low';
    $stmt = $db->prepare("SELECT {$oddsField} FROM odds_config WHERE play_type = ?");
    $stmt->execute([$playType]);
    $odds = $stmt->fetchColumn();

    if ($odds === false && is_numeric($playType)) {
        $num = (int)$playType;
        if ($num >= 0 && $num <= 27) $odds = 12.00;
    }

    return $odds;
}

/**
 * 下注核心逻辑
 */
function placeBet($db, $userId, $issueNo, $playType, $amount, $oddsType) {
    $odds = getOdds($db, $playType, $oddsType);
    if (!$odds) {
        return false;
    }

    try {
        $db->beginTransaction();

        // 检查余额
        $stmt = $db->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        $balanceBefore = (float)$stmt->fetchColumn();

        if ($balanceBefore < $amount) {
            $db->rollBack();
            return false;
        }

        $balanceAfter = $balanceBefore - $amount;

        // 扣款
        $stmt = $db->prepare("UPDATE users SET balance = ? WHERE id = ?");
        $stmt->execute([$balanceAfter, $userId]);

        // 记录余额日志
        $stmt = $db->prepare("INSERT INTO balance_logs (user_id, type, amount, balance_before, balance_after, description) VALUES (?, 'bet', ?, ?, ?, ?)");
        $stmt->execute([$userId, -$amount, $balanceBefore, $balanceAfter, "下单: {$playType} ({$issueNo} 期)"]);

        // 插入下注记录
        $stmt = $db->prepare("INSERT INTO bets (user_id, issue_no, play_type, odds_type, bet_amount, odds, status) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$userId, $issueNo, $playType, $oddsType, $amount, $odds]);

        // 更新流水
        $stmt = $db->prepare("UPDATE users SET daily_turnover = daily_turnover + ?, total_turnover = total_turnover + ? WHERE id = ?");
        $stmt->execute([$amount, $amount, $userId]);

        $db->commit();
        return true;
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return false;
    }
}

/**
 * 生成随机下注内容
 */
function generateRandomBet() {
    $roll = mt_rand(1, 100);

    if ($roll <= 45) {
        // 45% 概率下注基础玩法（大/小/单/双）
        $type = ['big', 'small', 'single', 'double'][mt_rand(0, 3)];
    } elseif ($roll <= 70) {
        // 25% 概率下注组合玩法（大单/大双/小单/小双）
        $type = ['big_single', 'big_double', 'small_single', 'small_double'][mt_rand(0, 3)];
    } elseif ($roll <= 80) {
        // 10% 概率下注数字（0-27）
        $type = (string)mt_rand(0, 27);
    } elseif ($roll <= 90) {
        // 10% 概率下注特殊玩法（对子/顺子/豹子）
        $type = ['triple', 'straight', 'pair'][mt_rand(0, 2)];
    } else {
        // 10% 概率下注极值（极大/极小）
        $type = ['extreme_big', 'extreme_small'][mt_rand(0, 1)];
    }

    // 随机金额（更真实的分布）
    $amountRoll = mt_rand(1, 100);
    if ($amountRoll <= 30) {
        $amount = [10, 20, 30, 50][mt_rand(0, 3)];
    } elseif ($amountRoll <= 60) {
        $amount = [50, 100, 200][mt_rand(0, 2)];
    } elseif ($amountRoll <= 85) {
        $amount = [200, 300, 500][mt_rand(0, 2)];
    } elseif ($amountRoll <= 95) {
        $amount = [500, 1000, 2000][mt_rand(0, 2)];
    } else {
        $amount = [2000, 3000, 5000][mt_rand(0, 2)];
    }

    return ['type' => $type, 'amount' => $amount];
}

// ========== 主逻辑 ==========

echo "[START] 开始自动下注，目标数量: {$count}\n";
echo "=" . str_repeat("=", 50) . "\n";

$successCount = 0;

for ($i = 0; $i < $count; $i++) {
    // 获取或创建假真人
    $player = getOrCreateFakePlayer($db, $idioms, $minBalance, $maxBalance);
    if (!$player) {
        echo "[SKIP] 无法获取假真人，跳过。\n";
        continue;
    }

    // 确定房间类型
    $currentRoom = ($roomType === 'random') ? (mt_rand(0, 1) == 0 ? 'low' : 'high') : $roomType;
    $roomName = $currentRoom === 'high' ? '高倍房' : '低倍房';

    // 每个假真人下注1-3个玩法
    $betCount = mt_rand(1, 3);
    $bets = [];
    $broadcastLines = [];

    for ($j = 0; $j < $betCount; $j++) {
        $bet = generateRandomBet();

        // 避免重复下注同一玩法
        $duplicate = false;
        foreach ($bets as $existing) {
            if ($existing['type'] === $bet['type']) {
                $duplicate = true;
                break;
            }
        }
        if ($duplicate) continue;

        // 检查互斥玩法（不能同时下大和小，单和双）
        $allTypes = array_column($bets, 'type');
        $allTypes[] = $bet['type'];
        if (in_array('big', $allTypes) && in_array('small', $allTypes)) continue;
        if (in_array('single', $allTypes) && in_array('double', $allTypes)) continue;

        $bets[] = $bet;

        // 执行下注
        if (placeBet($db, $player['id'], $betIssue, $bet['type'], $bet['amount'], $currentRoom)) {
            $cnType = $playTypeMap[$bet['type']] ?? $bet['type'];
            $broadcastLines[] = "【{$cnType}】{$bet['amount']}";
        }
    }

    if (!empty($broadcastLines)) {
        // 发送群聊消息
        $chatMsg = "玩家 [{$player['nickname']}] 第 {$betIssue} 期下注成功：\n" . implode("\n", $broadcastLines);
        $stmt = $db->prepare("INSERT INTO group_messages (user_id, room_type, message) VALUES (?, ?, ?)");
        $stmt->execute([$player['id'], $currentRoom, $chatMsg]);

        echo "[BET] {$player['nickname']} @ {$roomName}: " . implode(", ", $broadcastLines) . "\n";
        $successCount++;
    } else {
        echo "[SKIP] {$player['nickname']} 下注失败或余额不足\n";
    }

    // 随机延迟，模拟真人操作间隔
    usleep(mt_rand(100000, 500000)); // 0.1-0.5秒
}

echo "=" . str_repeat("=", 50) . "\n";
echo "[DONE] 成功下注真人数量: {$successCount}/{$count}\n";
echo "[INFO] 下注期号: {$betIssue}\n";
