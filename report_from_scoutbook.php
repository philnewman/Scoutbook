<?php

/* Debugging */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* Wordpress required */
ini_set("include_path", '/home/troop/php:' . ini_get("include_path")  );
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );
include_once plugin_dir_path(__FILE__)."includes/scoutbook.inc";


//function ptn_scoutbook_get_roster($choice){
function ptn_scoutbook_get_roster($choice, $ptn_scoutbook_troop_id){
 // $ptn_scoutbook_troop_id = get_option('sb-troopid');
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
//    curl_setopt($ch, CURLOPT_POSTFIELDS, "Action=Login&Email=philnewman&Password=Stella2012!&CSRF=" . $csrf);
/*  
  curl_setopt($ch, CURLOPT_POSTFIELDS, "Action=Login&Email=" . urlencode($ptn_scoutbook_troop_login) .
         "&Password=" . urlencode($ptn_scoutbook_troop_pwd) . "&CSRF=" . $csrf);
*/

echo $ptn_scoutbook_troop_login.' '.$ptn_scoutbook_troop_pwd;

  curl_setopt($ch, CURLOPT_POSTFIELDS, "Action=Login&Email=" . urlencode($ptn_scoutbook_troop_login) .
          "&Password=" . urlencode($ptn_scoutbook_troop_pwd) . "&CSRF=" . $csrf ."ReCaptchaResponse=03AOLTBLTI0YrYMxg1L5yyMh5oM2srFLAA-z16LDlaWBRU0lIUam-MoIlxDy9tbgrMfs91GgRf21x9Pf9xqYTfjn6gO2qubouz1bL6sf2lU0TTRtA38L1udCbfcpWI1CjusYLhhDPgWHZ7q_cPr1r3QKQVNHSQQSA68wJuOUFxZj37b0PtlYRg84Z9XSJZuTr2u72L3BIL5aHajBILI0Ihq0nEruvK1txd0x_W2_hQFxSp3hjl66lRlBNTkqeMwQtJs4aYVXr_hLZGBhpPUTi98me_Yv4eToiqRyLFD0PuxkAZuWUNYzWzxOMWi0NfeA9KYqrkyu_Uy4Cp");

 
 
 
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  $response = curl_exec($ch) or die("Login request failed");

  // check if login succeeded
  if (!strpos($response, "mobile_refreshPage()")) {
      die("Login failed");
  }
  // now you are logged in, with the session cookies stored under curl!

  // get roster
  curl_setopt($ch, CURLOPT_URL, "https://www.scoutbook.com/mobile/dashboard/reports/roster.asp?Action=Print&DenID=&PatrolID=&UnitID=");
  curl_setopt($ch, CURLOPT_POST, 1);


if ($choice == "Adults"){
  /* Adults */
  $url = "UnitID=".$ptn_scoutbook_troop_id ."&ShowLeaders=1&ShowMBC=1&ShowMBCBadges=1&ShowPhone=1&ShowEmail=1&ShowDenPatrol=1&ShowPositions=1";
  curl_setopt($ch, CURLOPT_POSTFIELDS, $url);
}  
if ($choice == "Scouts"){
  /* Scouts */
  $url = "UnitID=".$ptn_scoutbook_troop_id."&ShowScouts=1&ShowPhone=1&ShowEmail=1&ShowDenPatrol=1&ShowPositions=1";
  curl_setopt($ch, CURLOPT_POSTFIELDS, $url);
}  
  
if ($choice =="Ranks"){
    curl_setopt($ch, CURLOPT_URL, "https://www.scoutbook.com/mobile/dashboard/admin/roster.asp?UnitID=".$ptn_scoutbook_troop_id);
  curl_setopt($ch, CURLOPT_POST, 0);
}
  
  

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
  //print $dom->saveXML();
   
  $tables = $dom->getElementsByTagName('table');  

  $adult_leader = array();
  
  $rows = $tables->item(0)->getElementsByTagName('tr');   
  
  $ldr_row = 0;
  
  foreach ($rows as $row){
    $cols = $row->getElementsByTagName('td');
    $i = 0;
    foreach ($cols as $node) {
      if ($i == 0){
        $adult_leader[$ldr_row]['name'] = trim($node->nodeValue);
      }
      if ($i == 1){
        $innerHTML .= $node->ownerDocument->saveXML( $node );  
        $innerHTML = trim($innerHTML);
        $myArray = preg_split('/<br[^>]*>/i', $innerHTML);
        $string = preg_replace('~[\r\n\t]+~', '', $myArray);
        $string = preg_replace('/[\x00-\x1F\x7F]/', '', $string);
        foreach ($string as $phil){
          $phil = strip_tags($phil);
          if (!empty($phil)){
            $adult_leader[$ldr_row]['positions'][] = $phil;              
          }
        }
        unset($string, $myArray, $innerHTML);
      }
      if ($i == 2){
        $innerHTML .= $node->ownerDocument->saveXML( $node );  
        $meritbadges = explode(',',$innerHTML);
        $string = preg_replace('~[\r\n\t]+~', '', $meritbadges);
        $adult_leader[$ldr_row]['meritbadges'] = $string;
        unset($innerHTML, $string);
      }
      if ($i == 3){
        $innerHTML .= $node->ownerDocument->saveXML( $node );  
        $myArray = preg_split('/<br[^>]*>/i', $innerHTML);
        $string = preg_replace('~[\r\n\t]+~', '', $myArray);
        $adult_leader[$ldr_row]['phone'] = $string; 
        unset($string, $myArray, $innerHTML);
      }
      if ($i == 4){
        $adult_leader[$ldr_row]['email'] = $node->nodeValue;
      }
      $i++;
    }
    $ldr_row++;
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
function compare($s1, $s2) {
    $i = 0;
    while ($s1[$i]) {
        if ($s1[$i] != $s2[$i]) return array($s1[$i], $s2[$i]);
        $i++;
    }
}
function ptn_scoutbook_build_scout_array($html){
  $dom = new DOMDocument();
  $internalErrors = libxml_use_internal_errors(true);
  $dom->loadHTML($html);
  $tables = $dom->getElementsByTagName('table');  

  $scout = array();
  
  $rows = $tables->item(0)->getElementsByTagName('tr');   
  
  $scout_row = 0;
  
  foreach ($rows as $row){
    $cols = $row->getElementsByTagName('td');
    $i = 0;
    foreach ($cols as $node) {
      if ($i == 0){
        $scout[$scout_row]['name'] = $node->nodeValue;
      }
      if ($i == 1){
        $scout[$scout_row]['patrol'] = $node->nodeValue;
      }
      if ($i == 2){
        $innerHTML .= $node->ownerDocument->saveXML( $node );  
        $innerHTML = trim($innerHTML);
        $myArray = preg_split('/<br[^>]*>/i', $innerHTML);
        $string = preg_replace('~[\r\n\t]+~', '', $myArray);
        $string = preg_replace('/[\x00-\x1F\x7F]/', '', $string);
        foreach ($string as $phil){
          $phil = strip_tags($phil);
          $phil = trim($phil, '&#13;');
          if (!empty($phil)){
            $scout[$scout_row]['leadership'][] = $phil;
          }
        }
        unset($string, $myArray, $innerHTML);
      }
      if ($i == 3){
        $scout[$scout_row]['phone'] = $node->nodeValue;
      }
      $i++;
    }
    $scout_row++;
  }
  return $scout;
}


function ptn_scoutbook_build_rank_array($html){

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
  
    $scout_rank_arr[$i]['username'] = $x[1].$x[2];
    $rank = ptn_scoutbook_clean_data($rank);
    $scout_rank_arr[$i]['rank'] = $rank;
    $i++;
  }
  return $scout_rank_arr;
}
/**
*
* Loads  from array into WP custom content type(s)
*
*
**/
function ptn_scoutbook_load_posts($ptn_scoutbook_array,$post_type){
  foreach($ptn_scoutbook_array as $scout){
/* 
Need to test if already exists as user.
*/ 
  $full_name = explode(',',$scout['name']);
  $user_login = $full_name[1].$full_name[0];
  $user_login = preg_replace('/\s+/', '', $user_login);
 
  $role = 'subscriber';      
  switch ($post_type){
    case "scouts":
        if (is_array($scout['leadership'])){
          $search_array = array("Patrol Leader", "Webmaster", "Senior Patrol Leader", "Assistant Senior Patrol Leader", "Junior Assistant Scoutmaster");
          $result = array_intersect($search_array, $scout['leadership']);
         if (!empty($result)){
            $role = 'editor';
          }
        }
        break;
      
    case "adults":
        $search_array = array("Assistant Scoutmaster", "Committee Chairman", "Scoutmaster");
        $result = array_intersect($search_array, $scout['positions']);
        if (!empty($result)){
          $role = 'editor';
        }
        break;
  }
          
  $userdata = array(
    'user_login'  =>  $user_login,
    'user_nicename' => $scout['name'],
    'display_name' => $scout['name'],
    'first_name' => $full_name[1],
    'last_name' => $full_name[0],
    'user_url'    =>  $website, // is this required?
    'user_email' => $scout['email'],
    'user_pass' => $user_login,
    'role' => $role,
  );
  $user = get_user_by('login', $user_login);
  if($user)
  {
    $userdata['ID'] = $user->ID;
    echo 'Updating '.$user->first_name.$user->last_name.' Role: '.$role.'</br>';

// PTN - 22/May - removed this update as I think it's causing password resets and emails.
 //   wp_update_user($userdata); 

   $user->set_role( $role );
   switch ($post_type){
          case "scouts":
            
            echo $user->ID.' '.$user->first_name.' '.$user->last_name.' '.$scout['patrol'].'</p>';
            if (!empty($scout['patrol'])){
              update_user_meta( $user->ID, 'Patrol', $scout['patrol'], TRUE );
            }
            if (!empty($scout['leadership'])){
              update_user_meta( $user->ID, 'Leadership', $scout['leadership'], TRUE );
            }
            update_user_meta( $user->ID, 'MemberType', 'youth', TRUE );
            break;
          case "adults":
              update_user_meta( $user->ID, 'Positions', $scout['positions'], TRUE );
              update_user_meta( $user->ID, 'MeritBadges', $scout['meritbadges'], TRUE );
              update_user_meta( $user->ID, 'Phone', $scout['phone'], TRUE );
              update_user_meta( $user->ID, 'MemberType', 'adult', TRUE );
            break;
    }
  }else{
    $user = wp_insert_user( $userdata ) ;
       echo 'Inserting '.$scout['name'].' Role: '.$role.'</br>';
  }
  }
}

function ptn_scoutbook_load_ranks($ptn_scoutbook_array){
  foreach($ptn_scoutbook_array as $scout){
    $user = get_user_by('login', $scout['username']);
    if ($user){
      update_user_meta($user->ID, 'Rank', $scout['rank']);
    }
  }
}


/* Get Troop IDs */
  $ptn_scoutbook_troop_arr = array();
  $ptn_scoutbook_troop_arr[] = get_option('sb-troopid');
  $ptn_scoutbook_troop_arr[] = get_option('sb-troopid_girls');

foreach ($ptn_scoutbook_troop_arr as $ptn_troop_id){

    echo $ptn_troop_id;
   
  /*Adults*/
  $ptn_scoutbook_adult_roster = ptn_scoutbook_get_roster("Adults", $ptn_troop_id);
  $ptn_scoutbook_adult_roster = str_replace("\r",'',$ptn_scoutbook_adult_roster) ;
  $ptn_scoutbook_adult_array = ptn_scoutbook_build_adult_array($ptn_scoutbook_adult_roster);
  ptn_scoutbook_load_posts($ptn_scoutbook_adult_array, 'adults');

  /* Scouts */
  $ptn_scoutbook_scout_roster = ptn_scoutbook_get_roster("Scouts", $ptn_troop_id);
  $ptn_scoutbook_scout_roster = str_replace("\r",'',$ptn_scoutbook_scout_roster) ;
  $ptn_scoutbook_scout_array = ptn_scoutbook_build_scout_array($ptn_scoutbook_scout_roster);
  ptn_scoutbook_load_posts($ptn_scoutbook_scout_array, 'scouts');

  /* Ranks */ 
  $ptn_scoutbook_ranks = ptn_scoutbook_get_roster("Ranks", $ptn_troop_id);
  $ptn_scoutbook_rank_array = ptn_scoutbook_build_rank_array($ptn_scoutbook_ranks);
  ptn_scoutbook_load_ranks($ptn_scoutbook_rank_array);

}  
  
?>
