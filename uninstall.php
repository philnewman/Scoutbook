<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
// Delete options sb-troopid, sb-trooplogin, sb-trooppwd
delete_option('sb-troopid');
delete_option('sb-trooplogin');
delete_option('sb-trooppwd');

$wpdb->delete( 'wp_usermeta', array( 'ID' => 'patrol' ) );
$wpdb->delete( 'wp_usermeta', array( 'ID' => 'Positions' ) );
$wpdb->delete( 'wp_usermeta', array( 'ID' => 'Rank' ) );
$wpdb->delete( 'wp_usermeta', array( 'ID' => 'Leadership' ) );
$wpdb->delete( 'wp_usermeta', array( 'ID' => 'MemberType' ) );