<?php
/* Debugging */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* Wordpress required */
ini_set("include_path", '/home/troop/php:' . ini_get("include_path")  );
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );
require_once "Services/Mailman.php";

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
  
  	  
  curl_setopt($ch, CURLOPT_URL,"https://www.scoutbook.com/mobile/dashboard/admin/unit.asp?UnitID=25739&Action=ExportAdults");
  curl_setopt($ch, CURLOPT_POST, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
  $response = curl_exec($ch) or die("Get Roster request failed");
  
  $response_arr = explode("\n", $response);
  
  $headers = array_shift($response_arr);
  $headers = str_replace("\"","",$headers);
  $headers_arr = explode(",",$headers);


foreach ($response_arr as &$value) {
  $arr = array();
  foreach (explode(', ', $value) as $el) {
    $el = str_replace("\"","", $el);
    $row =  explode(',', $el);
    $value = $row;
  }
}

    foreach ($response_arr as &$value){
        if (count($headers_arr) == count($value)){
            $new[] = array_combine($headers_arr, $value);
        }
    }
  
  return $new;
}

/*******************************************
*
*
*
********************************************/
function scoutbook_adults($scoutbook_adults){

	$scoutmasters_list = new Services_Mailman('http://troop351.org/mailman/admin','scoutmasters_troop351.org','Stella12');
	$committee_list    = new Services_Mailman('http://troop351.org/mailman/admin','committee_troop351.org','Stella12');
	$all_list          = new Services_Mailman('http://troop351.org/mailman/admin','all_troop351.org','Stella12');
	
	
	foreach ($scoutbook_adults as $scoutbook_adult){
	    
	    if ((in_array("Assistant Scoutmaster", $scoutbook_adult)) || (in_array("Scoutmaster", $scoutbook_adult)) ){
	        echo 'Adding '.$scoutbook_adult["First Name"].' '.$scoutbook_adult["Last Name"]. ' to scoutmaster list.*** </br>';
	        try{
				$scoutmasters_list->subscribe($scoutbook_adult['Email']);
			} catch(Services_Mailman_Exception $e){}
	    }
	    if (in_array("Committee Member", $scoutbook_adult)) {
	        echo 'Adding '.$scoutbook_adult["First Name"].' '.$scoutbook_adult["Last Name"]. ' to committee list.*** </br>';
	        try{
				$committee_list->subscribe($scoutbook_adult['Email']);
			} catch(Services_Mailman_Exception $e){}
	    }
/*	    if (in_array("Merit Badge Counselor", $scoutbook_adult)) {
	        echo $scoutbook_adult["First Name"].' '.$scoutbook_adult["Last Name"]. ' is a merit badge counselor.*** </br>';
	    }
*/
        echo 'Adding '.$scoutbook_adult["First Name"].' '.$scoutbook_adult["Last Name"]. ' to all list.*** </br>';
        try{
			$all_list->subscribe($scoutbook_adult['Email']);
		} catch(Services_Mailman_Exception $e){}
	}
}

$scoutbook_adults_arr = ptn_scoutbook_get_roster();
scoutbook_adults($scoutbook_adults_arr);


?>
