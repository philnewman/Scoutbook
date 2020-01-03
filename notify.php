<?php

/* Debugging */
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* Wordpress required */
ini_set("include_path", '/home/troop/php:' . ini_get("include_path")  );
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );
/*-------------------*/

function ptn_scoutbook_get_future_events(){
  global $wpdb;
  $to = 'all@troop351.org';
//$to = 'phil.newman@gmail.com';
  $results = $wpdb->get_results("

    SELECT {$wpdb->prefix}posts.ID, 
           {$wpdb->prefix}posts.post_title, 
           {$wpdb->prefix}posts.post_content, 
           {$wpdb->prefix}ai1ec_events.venue, 
           {$wpdb->prefix}ai1ec_events.start, 
           {$wpdb->prefix}ai1ec_events.end,
           {$wpdb->prefix}ai1ec_events.allday
    FROM {$wpdb->prefix}posts 
    INNER JOIN {$wpdb->prefix}ai1ec_events
    ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}ai1ec_events.post_id 
    AND FROM_UNIXTIME({$wpdb->prefix}ai1ec_events.start) >= now()
    INNER JOIN {$wpdb->prefix}postmeta
    ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id 
    AND {$wpdb->prefix}postmeta.meta_key = 'send_notification_emails'
    AND {$wpdb->prefix}postmeta.meta_value = 1
    WHERE {$wpdb->prefix}posts.post_status = 'publish' ", OBJECT);

  foreach($results as $result){
    $permalink = get_permalink($result->ID);
    $notification_days = get_post_meta($result->ID, 'notification_days');
    foreach($notification_days as $notification_day){
      $notification_date = strtotime('-'.$notification_day.'days', $result->start);
      $notification_date =  date("Y-m-d", $notification_date);
      
      // Determine if a notification should be send today
      if ($notification_date == date("Y-m-d")){
        if ($result->allday){
          $date_format = 'M d, Y';
        } else {
          $date_format = 'M d, Y g:iA';
        }
        // Build and send email reminder
        $subject = 'TROOP 351 EVENT REMINDER: '.$result->post_title;
        $message = "<b>Event:</b> ".$result->post_title. '<br />';
        $message .= "<b>Where:</b> ".$result->venue.'<br />';
        $message .= "<b>When:</b> ".date_i18n($date_format, $result->start).' - '.date_i18n($date_format,$result->end).'<br />';
        $message .= "<b>Link:</b> ".$permalink.'<br />';
        $message .= "<b>Details:</b> ".$result->post_content;
        $message .= '<br /><strong>(1) Look Good - (2) Have Fun - (3) Safety First</strong>';
        $headers[] = 'From: Troop 351 Events <webmaster@troop351.org>';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';

        $mailResult = false;
        $mailResult = wp_mail($to, $subject, $message, $headers);
      }
    }
  }
}

date_default_timezone_set(get_option('timezone_string'));
ptn_scoutbook_get_future_events();