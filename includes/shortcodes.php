<?php
/* Shortcodes */
/**
*
*
**/
function ptn_scoutbook_patrol_list_func($arg){

	$patrol_list = ptn_scoutbook_get_patrol_list();
	$return_string = "";
	if (!empty($patrol_list)){
		$return_string .= '<ul class="ptn_scouttroop_patrol_list">';
		foreach($patrol_list as $patrol){
			if ($arg == 'ARRAY'){
				$return_array[] = $patrol;
			}else{
        $domain = 'http://'.$_SERVER['SERVER_NAME'];
        $patrol_img = plugins_url('scoutbook').'/assets\/'.trim(strtolower($patrol)).'-small.png';
        $return_string .= '<p><img style="height:20px" src="'.$patrol_img.'"><a href="'.$domain.'/patrol-directory/?patrol='.$patrol.'">'.$patrol.'</a></p>';
			}
		}
	}
	if ($arg != 'ARRAY'){
		$return_string .= '</ul>';
		return $return_string;
	}
	return $return_array;
}
/**
*
*
**/
function ptn_scoutbook_name_by_rank(){
     global $wpdb;

  $ranks_order = array(' ',"Scout", "Tenderfoot", "Second Class", "First Class", "Star", "Life", "Eagle");
	$ranks_order_count = count($ranks_order);
  
  foreach ($ranks_order as $rank){
  $query = "
    SELECT user_id, display_name, user_email
    FROM wp_usermeta
    INNER JOIN wp_users ON user_id = ID
    WHERE meta_key = 'Rank'
    AND meta_value = '".$rank."'
    ORDER BY 2";
    
    $rank_list = $wpdb->get_results($query, OBJECT);  
    $i = 0;
    foreach ($rank_list as $scout){      
        $rank_table[$i++][$rank] = ptn_scoutbook_first_last_init($scout->display_name);

    }
  }
  echo '<ul class="scoutbook_ul">';
  for ($i=0; $i< $ranks_order_count; $i++){
    $rank_img = plugins_url('scoutbook').'/assets/'.$ranks_order[$i].'-small.png';
    echo '<li class="scoutbook_li"><img src="'.$rank_img.'" alt="'.$ranks_orders[$i].'"/></li>';
  }
  echo '</ul>';
  for ($i=0; $i < (count($rank_table)); $i++){
    echo '<ul class="scoutbook_ul">';
    foreach ($ranks_order as $rank){
        echo '<li class="scoutbook_li">'.$rank_table[$i][$rank].'</p></li>';
      }
      echo '</ul>';
  }
}
/**
*
*
**/
function ptn_scoutbook_patrol_directory(){
  
	$patrol = $_SERVER['QUERY_STRING'];
	if (wp_is_mobile()){
		$img_size = 'mobile' ;
	} else {
		$img_size = 'small' ;
	}
 ?>
 <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
 	<header class="entry-header">
 		<?php $the_patrol = $_SERVER['QUERY_STRING']; ?>
 		<?php $the_patrol = $_GET['patrol']; ?>
    <?php $phil_patrol = $the_patrol; ?>
 		<?php $the_patrol = str_replace('Patrol','', $the_patrol);?>
		<?php $the_patrol = str_replace('_',' ',$the_patrol);?>
		<?php $patrol_img = plugins_url('scoutbook').'/assets\/'.trim(strtolower($the_patrol)).' patrol-small.png'; ?>
		<img src="<?php echo $patrol_img; ?>" width="63px">
 		<h2><?php echo ucwords($the_patrol); ?></h2>
 	</header><!-- .entry-header -->
 	<div class="entry-content">

     <?php

    $args = array(
	//    'role'         => 'Youth',
      'fields'       => 'all_with_meta',
      'meta_key'     => 'Patrol',
      'meta_value'   => $phil_patrol,
      'meta_compare' => 'like',
    );
    $scouts = get_users($args);
    
    foreach ($scouts as $scout){
      $rank = get_user_meta($scout->ID, 'Rank');
      $leadership = get_user_meta($scout->ID, 'Leadership');
      $scout->rank = $rank[0];
      $scout->leadership = $leadership[0];
      $scout->rank_img = plugins_url('scoutbook').'/assets/'.$rank[0].'-'.$img_size.'.png';
      $scout->leadership_img = plugins_url('scoutbook').'/assets/'.$leadership[0].'-'.$img_size.'.png';
    }

   foreach($scouts as $scout){
      echo '<ul style="width:100%; display:table; table-layout:fixed; border-collapse:collapse; margin-top:-10px;">';
      echo '<li style="display:table-cell; text-align:center; border: 1px solid red; vertical-align;middle"><img src="'.$scout->rank_img.'" alt="'.$scout->rank.'"/></li>';
      echo '<li style="display:table-cell; text-align:center; border: 1px solid red; vertical-align;middle">'.ptn_scoutbook_first_last_init($scout->display_name).'</li>';
      echo '<li style="display:table-cell; text-align:center; border: 1px solid red; vertical-align;middle"><img src="'.$scout->leadership_img.'" alt="'.$scout->leadership.'"/></li>';
      echo '</ul>';
    }
}

function scoutbook_committee_directory(){
	?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php
    	if (!wp_is_mobile()){
    		echo '<header class="entry-header"></header>';
    	}
    ?>
    	<div class="entry-content">
    <?php
 global $wpdb;
  
 $query = "
    SELECT user_id, display_name, user_email
    FROM wp_usermeta
    INNER JOIN wp_users ON user_id = ID
    WHERE meta_key = 'Positions'
    AND meta_value like '%Comm%'
    ORDER BY 2";
  $list = $wpdb->get_results($query, OBJECT);  

  foreach ($list as $comm){
    $positions = get_user_meta($comm->user_id, 'Positions', TRUE);
    $phone = get_user_meta($comm->user_id, 'Phone', TRUE);

     echo '<ul class="scoutbook_ul">';
     echo '<li class="scoutbook_li">'.ptn_scoutbook_first_last_init($comm->display_name).'</li>';    
     echo '<li class="scoutbook_li">';
      if (is_array($positions)){
        foreach($positions as $position){
          echo $position;
          echo nl2br("\n");
        } }
        echo '</li>';    
    
    if (is_user_logged_in()){
    echo '<li class="scoutbook_li">
    <a href="mailto:'.$comm->user_email.'?Subject=Troop 351 Committee Contact" target="_blank">'.$comm->user_email.'</a></li>';
        echo '<li class="scoutbook_li">';
        foreach($phone as $nbr){
          $nbr = strip_tags($nbr);
          $tel =  preg_replace("/[^0-9,.()-]/", "", $nbr);
          echo'<a href="tel:'.$tel.'"">'.$nbr.'</a>';
          echo nl2br("\n");
        }
        echo '</li>';
    }else{
      echo '<li class="scoutbook_li">Login for email</li>';
      echo '<li class="scoutbook_li">Login for phone</li>';
    }
      echo '</ul>';
  }
}
function scoutbook_meritbadgecounselor_directory(){
	?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php
    	if (!wp_is_mobile()){
    		echo '<header class="entry-header"></header>';
    	}
    ?>
    	<div class="entry-content">
    <?php
 global $wpdb;
  
 $query = "
    SELECT user_id, display_name, user_email
    FROM wp_usermeta
    INNER JOIN wp_users ON user_id = ID
    WHERE meta_key = 'Positions'
    AND meta_value like '%merit%'
    ORDER BY 2";
  $list = $wpdb->get_results($query, OBJECT);  

  foreach ($list as $mbc){
    
    $mbc_data = get_userdata($mbc->user_id);
    $merit_badges = get_user_meta($mbc->user_id, 'MeritBadges', TRUE);
    $phone = get_user_meta($mbc->user_id, 'Phone', TRUE);

     echo '<ul class="scoutbook_ul">';
     echo '<li class="scoutbook_li">'.ptn_scoutbook_first_last_init($mbc->display_name).'</li>';    
     echo '<li class="scoutbook_li">';
        foreach($merit_badges as $merit_badge){
          echo $merit_badge;
          echo nl2br("\n");
        }
        echo '</li>';
    
    if (is_user_logged_in()){
    echo '<li class="scoutbook_li">
    <a href="mailto:'.$mbc->user_email.'?Subject=Merit Badge" target="_blank">'.$mbc->user_email.'</a></li>';
        echo '<li class="scoutbook_li">';
        foreach($phone as $nbr){
          $nbr = strip_tags($nbr);
          $tel =  preg_replace("/[^0-9,.()-]/", "", $nbr);
          echo'<a href="tel:'.$tel.'"">'.$nbr.'</a>';
          echo nl2br("\n");
        }
        echo '</li>';
    }else{
      echo '<li class="scoutbook_li">Login for email</li>';
      echo '<li class="scoutbook_li">Login for phone</li>';
    }
      echo '</ul>';
  }
}

function scoutbook_scoutmaster_directory(){
	?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php
    	if (!wp_is_mobile()){
    		echo '<header class="entry-header"></header>';
    	}
    ?>
    	<div class="entry-content">
    <?php
 global $wpdb;
  
 $query = "
    SELECT user_id, display_name, user_email
    FROM wp_usermeta
    INNER JOIN wp_users ON user_id = ID
    WHERE meta_key = 'Positions'
    AND meta_value like '%Scoutmaster%'
    ORDER BY 2";
  $list = $wpdb->get_results($query, OBJECT);  

  foreach ($list as $sm){
    $phone = get_user_meta($sm->user_id, 'Phone', TRUE);
     echo '<ul class="scoutbook_ul">';
     echo '<li class="scoutbook_li">'.ptn_scoutbook_first_last_init($sm->display_name).'</li>';    
    if (is_user_logged_in()){
    echo '<li class="scoutbook_li">
    <a href="mailto:'.$sm->user_email.'?Subject=Merit Badge" target="_blank">'.$sm->user_email.'</a></li>';
        foreach($phone as $nbr){
          $nbr = strip_tags($nbr);
          $tel =  preg_replace("/[^0-9,.()-]/", "", $nbr);
          echo'<a href="tel:'.$tel.'"">'.$nbr.'</a>';
          echo nl2br("\n");
        }
        echo '</li>';
    }else{
      echo '<li class="scoutbook_li">Login for email</li>';
      echo '<li class="scoutbook_li">Login for phone</li>';
    }
      echo '</ul>';
  }
}

function scoutbook_add_to_email_list(){
    $sub_email_form = '<input type="email" id="scoutbook_email_to_add" name="scoutbook_email_to_add" placeholder="Email Address" /><p>';
    $sub_email_form .= '<button id="scoutbook_add_to_email">Add to Email List</button>';
    $sub_email_fomr .= '<div id="add_email_success" style="display:none">SUCCESS</div>';
    return $sub_email_form;
}





add_shortcode ('scoutmasterdirectory', 'scoutbook_scoutmaster_directory');
add_shortcode ('committeedirectory', 'scoutbook_committee_directory');
add_shortcode ('meritbadgecounselordirectory', 'scoutbook_meritbadgecounselor_directory');
add_shortcode( 'scoutbook_patroldirectory', 'ptn_scoutbook_patrol_directory');
add_shortcode('patrol_list', 'ptn_scoutbook_patrol_list_func');
add_shortcode( 'scoutbook_scoutbyrank', 'ptn_scoutbook_name_by_rank');
add_shortcode('email_signup', 'scoutbook_add_to_email_list');
add_shortcode('rank_patrol_leadership', 'ptn_scoutbook_rank_patrol_leadership');

?>
