<?php
require_once 'header.php';
$page_title = '市井觀點';
define("NUM_OF_LIST_ARTICLE", 3); // number of articles to be displayed at a time
define("TOPIC_TYPE", "主題"); // distinguish from "類別"
define("TOPIC_STATUS_OPEN", 0); // topic opening for comment
define("TOPIC_STATUS_ACTIVE", "1"); // active topic
define("TOPIC_STATUS_EFFECTIVE", 2); // effective topics
define("TOPIC_STATUS_CLOSED", 3); // topics closed

$op = isset($_REQUEST['op']) ? filter_var($_REQUEST['op']) : '';
$sn = isset($_REQUEST['sn']) ? (int) $_REQUEST['sn'] : 0;
$topic_sn = isset($_REQUEST['topic_sn']) ? (int) $_REQUEST['topic_sn'] : 0;
switch ($op) {
    default:
        if (!$topic_sn and !$sn) {
            $op = 'list_point';
            list_point();
        } elseif ($topic_sn) {
            $op = 'show_point';
            show_point($topic_sn);
        } else {
            $op = 'show_article';
            show_article($sn);
        }
        break;
}
require_once 'footer.php';
/*************函數區**************/
// This function retrieve out all opened topics and last three articles related to active topic.
function list_point()
{
    global $db, $smarty;
    require_once 'HTMLPurifier/HTMLPurifier.auto.php';
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);

    $topic_list = array();
    $all = array();
    $topic_sn = 0 ;
    // find out the active topic and stored as the first array element.
    $sql = "SELECT * FROM `topic` WHERE `topic_type`='主題' and `topic_status`= '1'"  ;
    // die($sql);
    $result = $db->query($sql) or die($db->error);
    if ($data = $result->fetch_assoc()) {
        $topic_list[] = $data;
        $topic_sn = $data['topic_sn']; //the topic_sn of active topic
    }
 
// print_r($topic_list);die();
    // Retrieve out three last articles related to active topic from article table
    $topic_list_three = array();
    $sql = "SELECT * FROM `article` WHERE `topic_sn`='{$topic_sn}' ORDER BY `update_time` DESC ";
    $result = $db->query($sql) or die($db->error);
    $i=0;
    $have_more = 0;
    while ($data = $result->fetch_assoc()) {
        if($i <= 2){
        $topic_list_three[$i] = $data;
        $i++;
        } else {
          $have_more = 1;
        }
    }

    // Retrieve out all opened topics behind active topic
    $topic_history = array();    
    $sql = "SELECT * FROM `topic` WHERE `topic_type`='主題' and `topic_status`= '2'";
    $result = $db->query($sql) or die($db->error);
    $i = 1;
    while ($data = $result->fetch_assoc()) {
        $data['topic_description'] = $purifier->purify($data['topic_description']);
        $topic_history[$i] = $data;
        $topic_history[$i]['summary'] = mb_substr(strip_tags($data['topic_description']), 0, 90);
        $i++;
    }            

    $smarty->assign('topic_list', $topic_list);
    $smarty->assign('topic_list_three', $topic_list_three);
    $smarty->assign('topic_history', $topic_history);    
    $smarty->assign('have_more', $have_more);
    $smarty->assign('topic_sn', $topic_sn);
     
}
// list out active topic and its related articles
function show_point($topic_sn)
{
    global $db, $smarty;
    $all = array();
    $topic_list=array();
    
    require_once 'HTMLPurifier/HTMLPurifier.auto.php';
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);
    // Retrieve out active topic
    $sql = "SELECT * FROM `topic` WHERE `topic_sn`=$topic_sn";
    $result = $db->query($sql) or die($db->error);
    if ($data = $result->fetch_assoc()) {
        $data['topic_description'] = $purifier->purify($data['topic_description']);
        $topic = $data;
    }
    // Retrieve out all related articles
    $sql = "SELECT * FROM `article` WHERE `topic_sn`=$topic_sn ORDER by `update_time` DESC";
    $result = $db->query($sql) or die($db->error);
    $i = 0;
    while ($data = $result->fetch_assoc()) {
        $data['content'] = $purifier->purify($data['content']);
        $all[$i] = $data;
        $i++;
    }
    // var_export($topic);
    // var_export($all);
    // die();
    $smarty->assign('topic_list', $topic_list);
    $smarty->assign('all', $all);
}
