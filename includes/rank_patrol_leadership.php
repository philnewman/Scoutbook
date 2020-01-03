<?php 
function ptn_scoutbook_rank_patrol_leadership(){
  if (current_user_can('webmaster')){
  ptn_scoutbook_rpl_post();
  echo ptn_scoutbook_rpl();
}else{
    wp_redirect("https://troop351.org");
    }
}
function ptn_scoutbook_rpl_post(){
  
    if(isset($_POST["id"])){
      $scout_count = count($_POST["id"]);
      for ($i=0; $i <= $scout_count; $i++){
        update_user_meta( $_POST["id"][$i], 'Patrol', $_POST["patrol"][$i]);
        update_user_meta( $_POST["id"][$i], 'Rank', $_POST["rank"][$i] );
        update_user_meta( $_POST["id"][$i], 'Leadership', $_POST["leadership"][$i] );
      }
    }
}

function ptn_scoutbook_rpl(){
  $scout_rpl_arr = array();
  $scout_rpl_rank_arr = array('','Scout', 'Tenderfoot', 'Second Class', 'First Class', 'Star', 'Life', 'Eagle');
  $scout_rpl_patrol_arr = array('',
   'Artemis Patrol',
   'Cerberus Patrol', 
   'Chimera Patrol',
   'Cobra Patrol',
   'Hippogriff Patrol',
   'Hydras Patrol',
   'Kraken Patrol',
   'Phoenix Patrol',
   'Valkyrie Patrol');
  $scout_rpl_leadership_arr = array('',
  'Senior Patrol Leader',
  'Assistant Senior Patrol Leader',
  'Patrol Leader',
  'Assistant Patrol Leader', 
  'Leave No Trace Trainer', 
  'Junior Assistant Scoutmaster',
  'Scribe',
  'Chaplain\'s Aide',
  'Bugler',
  'Webmaster',
  'Den Chief',
  'Troop Guide',
  'Quartermaster',
  'Historian',
  'OA Representative');
  $i=0;
  $args = array(
	'meta_key'     => 'MemberType',
  'meta_value'   => 'youth',
  'orderby' => 'login',
  );
  $youth_arr = get_users($args);
  foreach ($youth_arr as $youth){
    $parts = preg_split('/(?=[A-Z])/', $youth->user_login, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($parts as $part){
      $scout_rpl_arr[$i]['name'] .= $part.' ';
    }
    $scout_rpl_arr[$i]['id'] = $youth->ID;
    $scout_rpl_arr[$i]['patrol'] = get_user_meta($youth->ID,'Patrol',true);
    $scout_rpl_arr[$i]['rank'] = get_user_meta($youth->ID,'Rank',true);
    $scout_rpl_arr[$i]['leadership'] = get_user_meta($youth->ID,'Leadership',true);
    $i++;
  }
    
 $ptn_scoutbook_rpl_form =  '<form action="" method="POST">';
 $ptn_scoutbook_rpl_form .=  '<table>';
   $ptn_scoutbook_rpl_form .=  '<th>Scout</th>';
   $ptn_scoutbook_rpl_form .=  '<th>Rank</th>';
   $ptn_scoutbook_rpl_form .=  '<th>Patrol</th>';
   $ptn_scoutbook_rpl_form .=  '<th>Leadership</th>';
   foreach ($scout_rpl_arr as $scout){
     $ptn_scoutbook_rpl_form .=  '<tr>';

     $ptn_scoutbook_rpl_form .=  '<td><input type="hidden" name="id[]" value="'.$scout["id"].'" readonly '.$scout["id"].'/><input type="text" name="name[]" value="'.$scout["name"].'" readonly '.$scout["name"].'/></td>';
     //Rank
     $ptn_scoutbook_rpl_form .= '<td><select name=rank[]">';
     foreach ($scout_rpl_rank_arr as $rank){
       $ptn_scoutbook_rpl_form .= '<option value="'.$rank.'"';
       if ($scout["rank"] == $rank){
         $ptn_scoutbook_rpl_form .= ' selected';
       }
       $ptn_scoutbook_rpl_form .= '>'.$rank.'</option>';
     }
     $ptn_scoutbook_rpl_form .= '</select></td>';
     
     //Patrol
     $ptn_scoutbook_rpl_form .= '<td><select name="patrol[]">';
     foreach ($scout_rpl_patrol_arr as $patrol){
       $ptn_scoutbook_rpl_form .= '<option value="'.$patrol.'"';
       if ($scout["patrol"] == $patrol){
         $ptn_scoutbook_rpl_form .= ' selected';
       }
       $ptn_scoutbook_rpl_form .= '>'.$patrol.'</option>';
     }
     $ptn_scoutbook_rpl_form .= '</select></td>';

     //Leadership
     $ptn_scoutbook_rpl_form .= '<td><select name="leadership[]">';
     foreach ($scout_rpl_leadership_arr as $leadership){
       $ptn_scoutbook_rpl_form .= '<option value="'.$leadership.'"';
       if ($scout["leaderhip"] == $leadership){
         $ptn_scoutbook_rpl_form .= ' selected';
       }
       $ptn_scoutbook_rpl_form .= '>'.$leadership.'</option>';
     }
     $ptn_scoutbook_rpl_form .= '</select></td>';
   }
 $ptn_scoutbook_rpl_form .= '</table>';
 wp_nonce_field( 'posting new rpl', 'scoutbook-rpl-post' );
 $ptn_scoutbook_rpl_form .=  '<a href=".">Cancel</a>  '; 
 $ptn_scoutbook_rpl_form .=  '<input type="submit" value="submit" name="submit_btn">';
 $ptn_scoutbook_rpl_form .=  '</form>';

return $ptn_scoutbook_rpl_form;
  
} 
  
  /*

  -- webmaster role 
  -- limit this page to only webmaster and admin roles
  
  */

?>