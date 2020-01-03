<?php

/* Debugging */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* Wordpress required */
ini_set("include_path", '/home/troop/php:' . ini_get("include_path")  );
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );
include_once plugin_dir_path(__FILE__)."includes/scoutbook.inc";


function ptn_scoutbook_get_roster(){

  $ptn_scoutbook_troop_id = get_option('sb-troopid');
  $ptn_scoutbook_troop_login = get_option('sb-trooplogin');
  $ptn_scoutbook_troop_pwd = get_option('sb-trooppwd');

  // create a curl handle with cookie management enabled
  $ch = curl_init("https://www.scoutbook.com/mobile/login.asp");
  curl_setopt($ch, CURLOPT_COOKIEFILE, "");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

  // get login page
  $response = curl_exec($ch) or die("Failed to fetch login page");

  // extract CSRF token
  $pos = strpos($response, "name=\"CSRF\"") or die("Failed to locate CSRF token");
  $startpos = strpos($response, "\"", $pos + 11) or die("Failed to locate CSRF token");
  $endpos = strpos($response, "\"", $startpos + 1) or die("Failed to locate CSRF token");
  $csrf = substr($response, $startpos + 1, $endpos - $startpos - 1);

  // send login request
  curl_setopt($ch, CURLOPT_URL, "https://www.scoutbook.com/mobile/login.asp");
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "Action=Login&Email=" . urlencode($ptn_scoutbook_troop_login) .
          "&Password=" . urlencode($ptn_scoutbook_troop_pwd) . "&CSRF=" . $csrf);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  $response = curl_exec($ch) or die("Login request failed");

  // check if login succeeded
  if (!strpos($response, "mobile_refreshPage()")) {
      die("Login failed");
  }
  // now you are logged in, with the session cookies stored under curl!

  // get roster
  curl_setopt($ch, CURLOPT_URL, "https://www.scoutbook.com/mobile/dashboard/admin/roster.asp?UnitID=".$ptn_scoutbook_troop_id);
  curl_setopt($ch, CURLOPT_POST, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  $response = curl_exec($ch) or die("Get Roster request failed");

  // check if roster read succeeded
  $response = curl_exec($ch) or die("Failed to fetch login page");

  return ($response);
}
/**
*
* Based on the roster page, determine the names and positions of each adult. Return as an array.
*
*
**/
function ptn_scoutbook_build_adult_array($html){

  $dom = new DOMDocument();
  $internalErrors = libxml_use_internal_errors(true);
  $dom->loadHTML($html);
  libxml_use_internal_errors($internalErrors);
  $xpath = new DOMXPath($dom);

  $i = 0;
foreach($xpath->query('//a[@class="ui-link"]') as $name){
  $adultID = $name->getAttribute('href');
  $start = strpos($adultID, "=") + 1;
  $length = strpos($adultID, "&") - $start;
  $adult_leader[$i]['scoutid'] = substr($adultID, $start, $length);
  $adult_leader[$i++]['name'] = trim($name->nodeValue);
  }
  $i=0;
  foreach($xpath->query('//div[@class="positions text-orange"]') as $position){
    $positions = preg_replace('/\s+/', ' ',$position->nodeValue);
    $adult_leader[$i++]['positions'] = $positions;
  }
  return $adult_leader;
}
/**
*
* Based on the roster page, determine the names, ranks, patrols and leadership postions of each scout. Return as an array.
*
* The page is not at all semantic, so a few gyrations of string manipulation is required to build the array.
*
**/
function ptn_scoutbook_build_scout_array($html){

  $html_dom = new DOMDocument();
  $html_dom->preserveWhiteSpace = false;
  $internalErrors = libxml_use_internal_errors(true);
  $html_dom->loadHTML($html);
  libxml_use_internal_errors($internalErrors);
  $x_path = new DOMXPath($html_dom);

  $nodes = $x_path->query('//li[@data-scoutuserid]');
  $i=0;
  foreach ($nodes as $node){
    $scout = trim($node->nodeValue);
    $scout = preg_replace('/\s+/', ' ', $node->nodeValue);
    $name_rank = explode("CURRENT RANK:", $scout);

    $x = explode(" ",$name_rank[0]);
    $x = array_filter($x);

    $rank_arr = explode(' ',$name_rank[1]);
    switch($rank_arr[1])
    {
      case "No":
        $rank = "";
        break;
      case "First":
      case "Second":
        $rank = $rank_arr[1].' Class';
        break;
      default:
        $rank = $rank_arr[1];
    }

    $scout_arr[$i]['name'] = $x[1].' '.$x[2];
    $rank = ptn_scoutbook_clean_data($rank);
    $scout_arr[$i]['rank'] = $rank;

    $patrol_ldrshp = str_replace($scout_arr[$i]['name'], "",$scout);
    $patrol_ldrshp = substr($patrol_ldrshp, 0, strpos($patrol_ldrshp, 'CURRENT RANK:'));
    $patrol_ldrshp = explode(",", $patrol_ldrshp);
    $patrol  =  preg_replace('/\s+/u', ' ', $patrol_ldrshp[0]);
    $patrol = ptn_scoutbook_clean_data($patrol);

    $scout_arr[$i]['patrol'] = $patrol;
    if (! isset($patrol_ldrshp[1])){
      $leadership = null;
    }else{
      $leadership = str_replace($scout_arr[$i]['patrol'], '', $patrol_ldrshp[1]);
    }
    $leadership = ptn_scoutbook_clean_data($leadership);
    $scout_arr[$i]['leadership'] = $leadership;
    $scout_arr[$i++]['scoutid'] = $node->getAttribute("data-scoutuserid");
  }

  return $scout_arr;
}
/**
*
* Remove from Troop351.org
*
*
**/
function ptn_scoutbook_delete_posts($scout_arr, $post_type){
  global $wpdb;

$dele_array = array_column($scout_arr, 'scoutid');

$query = 'DELETE a,b,c
  FROM wp_posts a
  LEFT JOIN wp_term_relationships b
      ON (a.ID = b.object_id)
  LEFT JOIN wp_postmeta c
      ON (a.ID = c.post_id)
    WHERE a.post_type = %s
    AND a.ID NOT IN (' . implode($dele_array) .')';

$result= $wpdb->query($wpdb->prepare($query,$post_type));
}
/**
*
* Loads  from array into WP custom content type(s)
*
*
**/
function ptn_scoutbook_load_posts($ptn_scoutbook_array,$post_type){
  foreach($ptn_scoutbook_array as $scout){

    /* ORIG
      $new_scout = array(
        'import_id' =>  $scout['scoutid'],
        'post_title' => $scout['name'],
        'post_type' => $post_type,
        'post_status' => 'publish'
      );
      $post_id = wp_insert_post($new_scout, true);
  */
/* 
Need to test if already exists as user.
*/  $user = get_user_by('login', $scout['name']);
  if (empty($user)){
  $user_login = preg_replace('/\s+/', '', $scout['name']);
  $full_name = explode(' ',$scout['name']);
        
  switch ($post_type){
    case "scouts":
       $role = 'youth';
        break;
    case "adults":
       $role = 'adult';
        break;
  }
          
  $userdata = array(
    'user_login'  =>  $user_login,
    'user_nicename' => $scout['name'],
    'display_name' => $scout['name'],
    'first_name' => $full_name[0],
    'last_name' => $full_name[1],
    'user_url'    =>  $website, // is this required?
//    'user_pass'   =>  NULL  // When creating an user, `user_pass` is expected. // Set password on insert
    'user_pass' => $user_login,
    'role' => $role,
  );
  $user_id = wp_insert_user( $userdata ) ;
 }    
    
    if ($user_id){
        switch ($post_type){
          case "scouts":
            /* ORIG
            add_post_meta($post_id, 'Rank', $scout['rank']);
            add_post_meta($post_id, 'Patrol', $scout['patrol']);
            add_post_meta($post_id, 'Leadership', $scout['leadership']);
            */
            add_user_meta( $user_id, 'Rank', $scout['rank'], TRUE );
            add_user_meta( $user_id, 'Patrol', $scout['patrol'], TRUE );
            add_user_meta( $user_id, 'Leadership', $scout['leadership'], TRUE );
            add_user_meta( $user_id, 'MemberType', 'youth', TRUE );
            break;
          case "adults":
            /* ORIG
            add_post_meta($post_id, 'Positions', $scout['positions']);
            */ 
              add_user_meta( $user_id, 'Positions', $scout['positions'], TRUE );
              add_user_meta( $user_id, 'MemberType', 'adult', TRUE );
            break;
        }
    }
  }
}


$ptn_scoutbook_roster_source = ptn_scoutbook_get_roster();
$ptn_scoutbook_adult_array = ptn_scoutbook_build_adult_array($ptn_scoutbook_roster_source);
$ptn_scoutbook_scout_array = ptn_scoutbook_build_scout_array($ptn_scoutbook_roster_source);

// ptn_scoutbook_delete_posts($ptn_scoutbook_adult_array, 'adults');
ptn_scoutbook_load_posts($ptn_scoutbook_adult_array, 'adults');
// ptn_scoutbook_delete_posts($ptn_scoutbook_scout_array, 'scouts');
ptn_scoutbook_load_posts($ptn_scoutbook_scout_array, 'scouts');

?>
