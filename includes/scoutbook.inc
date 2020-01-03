<?php
/****************************************************************************/
/* Common functions                                                         */
/****************************************************************************/
function ptn_scoutbook_get_patrol_list(){
	global $wpdb;
  $values = $wpdb->get_col("SELECT DISTINCT meta_value FROM $wpdb->usermeta WHERE meta_key = 'patrol' ORDER BY meta_value");
	foreach ($values as $ptn_scouttroop_patrol){
		$ptn_scouttroop_patrol = trim($ptn_scouttroop_patrol);
		if (!empty($ptn_scouttroop_patrol)){
			$patrol_list[$ptn_scouttroop_patrol] = $ptn_scouttroop_patrol;
		}
	}
	return( $patrol_list);
}

function ptn_scoutbook_first_last_init($name){
	$name_arr = array_reverse(preg_split("/[\s,]+/",$name));
  $name = implode(' ',$name_arr);
	if (!is_user_logged_in()){
		$name_array = preg_split("/[\s,]+/",$name);
		$name = $name_array[0].' '.$name_array[1][0];
	}
	return $name;
}


function ptn_scoutbook_format_telephone($phone_number){
    $cleaned = preg_replace('/[^[:digit:]]/', '', $phone_number);
    preg_match('/(\d{3})(\d{3})(\d{4})/', $cleaned, $matches);
    return "({$matches[1]}) {$matches[2]}-{$matches[3]}";
}
function ptn_scoutbook_clean_data($text_to_clean){
  $text_to_clean = rtrim($text_to_clean);
  $text_to_clean = str_replace( chr( 194 ) . chr( 160 ), '', $text_to_clean );
  $text_to_clean = trim($text_to_clean);
  return $text_to_clean;
}
function ptn_scoutbook_get_image_tag($postID, $metafield, $size){
	$ptn_scoutbook_img = get_post_meta($postID, $metafield, true);
	if (!empty($ptn_scoutbook_img)){
		$ptn_scoutbook_img_url = plugins_url($plugin_name).'/'.PTN_SCOUTBOOK_PLUGIN_NAME.'/assets\/'.$ptn_scoutbook_img;
		if (!empty($size)){
			$ptn_scoutbook_img_url .= '-'.$size;
		}
		$ptn_scoutbook_img_url .= '.png"';
		$ptn_scoutbook_img_tag = '<img src="'.$ptn_scoutbook_img_url;
		$ptn_scoutbook_img_tag .= ' alt="'.$ptn_scoutbook_img.'"';
		$ptn_scoutbook_img_tag .= ' /><p>'.$ptn_scoutbook_img.'</p>';
		return $ptn_scoutbook_img_tag;
	}else{
		return;
	}
}



?>
