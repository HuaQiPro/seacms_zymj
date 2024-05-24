<?php
/******************************
海洋CMS
功能：资源发布zyAPI模块
版本：1.0
开发：海洋
******************************/
header( "Access-Control-Allow-Origin: *" ); //允许任何域名跨域访问

require_once(dirname(__FILE__)."/../include/common.php");
require_once(dirname(__FILE__)."/../include/main.class.php");
require_once(dirname(__FILE__)."/../data/config.cache.inc.php");

$ids = isset($_REQUEST['ids']) ? addslashes($_REQUEST['ids']) : '';
$postfix = isset($_REQUEST['fix']) ? addslashes($_REQUEST['fix']) : '';

$data = array();

if (empty($ids)) {
    $data = array(
        "code" => 0
    );
    // 将数组编码为JSON字符串
    $json = json_encode($data);
    // 输出JSON字符串
    echo $json;
    exit;
}




if (!empty($ids)) {
    $ids = addslashes($ids);
    $sql = "select d.*,p.body as v_playdata,t.tname from sea_data d left join `sea_type` t on t.tid=d.tid left join `sea_playdata` p on p.v_id=d.v_id where d.v_recycled=0 AND d.v_id in (" . $ids . ")";
}

$dsql->SetQuery($sql);
$dsql->Execute('video_c');

while ($row = $dsql->GetObject('video_c')) {
    if ($ids == $row->v_id) {
        $data = array(
            "copy" => "超级播放器苹果cms接口，作者QQ602524950",
            "code" => 1
        );
    }

    $data['name'] = $row->v_name;
    $data['hits'] = $row->v_hit;
    $data['year'] = $row->v_publishyear;
    $data['class'] = $row->tname;
    $data['area'] = $row->v_publisharea;
    $data['remarks'] = $row->v_note;
    $allplayurl = $row->v_playdata;
    $tempurl = getplayurl($allplayurl, $postfix);
    $data['url'][] = $tempurl;
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    echo $json;
}

function getFlagByPostfix($xml, $postfix)
{
    foreach ($xml->player as $player) {
        if ((string)$player['postfix'] === $postfix) {
            return (string)$player['flag'];
        }
    }
    return null;
}
function getplayurl($urls, $postfix)
{
    $urls = str_replace('$', '|10086|', $urls);
    $arr1 = explode("|10086||10086||10086|", $urls);

    $zzt = count($arr1);
    $playerKindsfile = dirname(__FILE__)."/../data/admin/playerKinds.xml";
    $xml = simplexml_load_file($playerKindsfile);
    if (!$xml) {
        $xml = simplexml_load_string(file_get_contents($playerKindsfile));
    }
    $z = array();
	$flag = getFlagByPostfix($xml, $postfix);
    $z["$flag"] = $postfix;
    $url_data = [
        "from" => "$flag",
        "url"  => [],
        "sid"  => "1",
        "count"=> 0 // 初始化循环计数器
    ];

    foreach ($arr1 as $v) {
        
        
        $arr2 = explode("|10086||10086|", $v);
        for ($i = 0; $i < $zzt; $i++) {
            $f = $arr2['0'];
            $flag = $z["$f"];

            // 检查后缀是否与$postfix相等
			
            if ($postfix == $flag) {
				$url_data['url'] = array();
                // 根据分隔符分割字符串
                $currentLoop = 1; // 重新初始化当前循环计数器
                $subArr = explode("#", $arr2['1']);
                foreach ($subArr as $item) {
                    // 提取集数和 URL
                    preg_match('/([^|]+)\|.*?\|([^|]+)/', $item, $matches);
                    if (isset($matches[1]) && isset($matches[2])) {
                        // 添加当前集数和对应的 URL，并更新循环计数器
						$encrypted_url = $matches[2];
                        $url_data['url'][] = [
                            'name' => $matches[1],
							'url'  => $encrypted_url,
                            'nid'  => $currentLoop,
                        ];
                        $currentLoop++;
                    }
                }
            }
        }
    }

    // 更新总循环计数器
    $url_data['count'] = $currentLoop;

    return $url_data;
}




header('Content-Type: application/json; charset=utf-8');
