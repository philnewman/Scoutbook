<?php
/**
 * Plugin Name: Scoutbook
 * Plugin URI: http://troop351.org/scouttroop-wordpress-plugin/
 * Description: This plugin 'scrapes' scoutbook.com and adds scouts to local site as custom post types.
 *   Patrol and rank are included as well.  Short codes allow for rank table and patrol listings.
 * Version: 2.0
 * Author: Phil Newman
 * Author URI: http://getyourphil.net
 * License: GPL3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.en.html
 **/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/*
if ( ! function_exists( 'wp_password_change_notification' ) ) :
    function wp_password_change_notification( $user ) {
        return;
    }
endif;
*/

function ptn_scoutbook_load_css_and_js(){
  $scoutbook_file_dir = plugin_dir_url(__FILE__);
  $scoutbook_js_file = $scoutbook_file_dir.'js/add_to_email.js';

   wp_enqueue_script( 'ptn-scoutbook-add_to_email-js' , $scoutbook_js_file, array('jquery'), '1.0', true );
   wp_localize_script('ptn-scoutbook-add_to_email-js', 'scoutbook', array('ajax_url' => admin_url( 'admin-ajax.php' )));
}
add_action( 'init', 'ptn_scoutbook_load_css_and_js' );

wp_enqueue_style('scoutbook_css',plugin_dir_url(__FILE__).'styles/scoutbook.css');

include_once plugin_dir_path(__FILE__)."includes/shortcodes.php";
include_once plugin_dir_path(__FILE__)."includes/rank_patrol_leadership.php";

include_once plugin_dir_path(__FILE__)."includes/scoutbook.inc";


define ("PTN_SCOUTBOOK_PLUGIN_NAME", "scoutbook");
/****************************************************************************/
/* Settings Page                                                            */
/****************************************************************************/
	add_action( 'admin_menu', 'wporg_custom_admin_menu' );

	function wporg_custom_admin_menu() {
	    add_options_page(
	        'Scoutbook',
	        'Scoutbook',
	        'manage_options',
	        'scoutbook-plugin',
	        'scoutbook_settings_page'
	    );
	}

	function scoutbook_settings_page() {
		?>
		    <div class="wrap">
				<?php $scout_img=plugins_url('scoutbook').'/assets\/icon-128x128.png'; ?>
		        <h2><img src="<?php echo $scout_img ?>" height="50px"/>Scoutbook Settings</h2>
		        <form method="post" action="options.php">
		            <?php wp_nonce_field('update-options') ?>
              
		            <p><strong>Scoutbook Boys Troop ID:</strong><br />
					This number is found in the URL from scoutbook.com's dashboard.
					<?php $scoutbook_img=plugins_url('scoutbook').'/assets\/Scoutbook Troop ID.png'; ?>
					<input type="number" name="sb-troopid" size="6" value="<?php echo get_option('sb-troopid'); ?>" />
                  
		      <p><strong>Scoutbook Girls Troop ID:</strong><br />
					This number is found in the URL from scoutbook.com's dashboard.
					<?php $scoutbook_img=plugins_url('scoutbook').'/assets\/Scoutbook Troop ID.png'; ?>
					<input type="number" name="sb-troopid_girls" size="6" value="<?php echo get_option('sb-troopid_girls'); ?>" />
					<img src="<?php echo $scoutbook_img?>" width="500px" />
		            </p>
                <p><strong>Scoutbook Login:</strong><br />
		                <input type="scoutbook_login" name="sb-trooplogin" size="50" value="<?php echo get_option('sb-trooplogin'); ?>" />
		            </p>
                <p><strong>Scoutbook Password:</strong><br />
                    <input type="password" name="sb-trooppwd" size="50" value="<?php echo get_option('sb-trooppwd'); ?>" />
                </p>
		            <p><input type="submit" name="Submit" value="Save" /></p>
		            <input type="hidden" name="action" value="update" />
		            <input type="hidden" name="page_options" value="sb-troopid, sb-troopid_girls, sb-trooplogin, sb-trooppwd" />
		        </form>
		    </div>
		<?php
		}

/****************************************************************************/
/* Create custom roles                                                      */
/****************************************************************************/
   function scoutbook_add_roles_on_plugin_activation() {
       add_role( 'youth', 'Youth');
       add_role( 'adult', 'Adult');
   }
 //  register_activation_hook( __FILE__, 'scoutbook_add_roles_on_plugin_activation' );

/****************************************************************************/
/* Add users to admin bar                                                   */
/****************************************************************************/ 
add_action('admin_bar_menu', 'add_toolbar_items', 100);
function add_toolbar_items($admin_bar){
    $admin_bar->add_menu( array(
        'id'    => '351-user-mgmt',
        'title' => 'Users',
        'href'  => '#',
        'meta'  => array(
            'title' => __('My Item'),            
        ),
    ));
    $admin_bar->add_menu( array(
        'id'    => 'unapproved-users',
        'parent' => '351-user-mgmt',
        'title' => 'View Unapproved Users',
        'href'  => 'https://troop351.org/wp-admin/users.php?role=wpau_unapproved',
        'meta'  => array(
            'title' => __('View Unapproved Users'),
            'target' => '',
            'class' => 'my_menu_item_class'
        ),
    ));
    $admin_bar->add_menu( array(
        'id'    => 'user-list',
        'parent' => '351-user-mgmt',
        'title' => 'Users',
        'href'  => 'https://troop351.org/wp-admin/users.php',
        'meta'  => array(
            'title' => __('Users'),
            'target' => '',
            'class' => 'my_menu_item_class'
        ),        
    ));
}

/****************************************************************************/
/* Add troop relationship to registration form                              */
/****************************************************************************/ 
//1. Add a new form element...
add_action( 'register_form', 'myplugin_register_form' );
function myplugin_register_form() {

    $relationship = ( ! empty( $_POST['relationship'] ) ) ? sanitize_text_field( $_POST['relationship'] ) : '';
        
        ?>
        <p>
            <label for="relationship"><?php _e( 'Troop351.org is a private website for Scouts, Families, Leaders and Alumni of BSA Troop 351 in Portland, OR. </br>Please describe your connection:', 'mydomain' ) ?><br />
            <input type="textarea" rows="4" columns="10" name="relationship" id="relationship" class="input" value="<?php echo esc_attr(  $relationship  ); ?>" size="25" /></label>
        </p>
        <?php
    }

    //2. Add validation. In this case, we make sure first_name is required.
    add_filter( 'registration_errors', 'myplugin_registration_errors', 10, 3 );
    function myplugin_registration_errors( $errors, $sanitized_user_login, $user_email ) {
        
        if ( empty( $_POST['relationship'] ) || ! empty( $_POST['relationship'] ) && trim( $_POST['relationship'] ) == '' ) {
        $errors->add( 'relationship_error', sprintf('<strong>%s</strong>: %s',__( 'ERROR', 'mydomain' ),__( 'You must include an explanation of your relationship with Troop 351.', 'mydomain' ) ) );

        }
        return $errors;
    }

    //3. Finally, save our extra registration user meta.
    add_action( 'user_register', 'myplugin_user_register' );
    function myplugin_user_register( $user_id ) {
        if ( ! empty( $_POST['relationship'] ) ) {
            update_user_meta( $user_id, 'relationship', sanitize_text_field( $_POST['relationship'] ) );
        }
    }
    
/****************************************************************************/
/* Add troop relationship to admin form                                     */
/****************************************************************************/     
 function yoursite_manage_users_columns( $columns ) {

    // $columns is a key/value array of column slugs and names
    $columns[ 'custom_field' ] = 'Relationship';

    return $columns;
}

add_filter( 'manage_users_columns', 'yoursite_manage_users_columns', 10, 1 );

function yoursite_manage_users_custom_column( $output, $column_key, $user_id ) {

    switch ( $column_key ) {
        case 'custom_field':
            $value = get_user_meta( $user_id, 'relationship', true );

            return $value;
            break;
        default: break;
    }

    // if no column slug found, return default output value
    return $output;
}

add_action( 'manage_users_custom_column', 'yoursite_manage_users_custom_column', 10, 3 );   
/*function disable_password_reset() { return false; }
add_filter ( 'allow_password_reset', 'disable_password_reset' );
*/
/****************************************************************************/
/* Require First and Last Name on registration                              */
/****************************************************************************/   
add_filter('user_profile_update_errors', 'ptn_scoutbook_require_names', 10,3);
function ptn_scoutbook_require_names($errors, $update, $user){
  // Use the $_POST variable to check required fields

  if( empty($_POST['first_name']) )
    // add an error message to the WP_Errors object 
    $errors->add( 'first_name_required',__('First name is required, please add one before saving.') );

  if( empty($_POST['last_name']) )
    // add an error message to the WP_Errors object 
    $errors->add( 'last_name_required',__('Last name is required, please add one before saving.') );
}
/****************************************************************************/
/* Redirect new registrants to record  First and Last Name on registration  */
/****************************************************************************/  
//add_action('user_register','ptn_scoutbook_complete_profile');
//redirects user to registration form
function ptn_scoutbook_complete_profile() {
     wp_redirect(get_option('siteurl') . '/registration-step-2/');
     exit();
}
/****************************************************************************/
/* Add relationship to new user registration email to admin                 */
/****************************************************************************/  
add_filter( 'wp_new_user_notification_email_admin', 'custom_wp_new_user_notification_email', 10, 3 );

function custom_wp_new_user_notification_email( $wp_new_user_notification_email, $user, $blogname ) {
    $wp_new_user_notification_email['subject'] = sprintf( '[%s] New user %s registered.', $blogname, $user->user_login );
    $wp_new_user_notification_email['message'] = sprintf( "%s ( %s ) has registered on  %s.", $user->user_login, $user->user_email, $blogname );
    $relationship = get_user_meta($user->ID, 'relationship', TRUE);
    $wp_new_user_notification_email['message'] .= "\nRelationship: ".$relationship;
    $wp_new_user_notification_email['message'] .="\nhttps://troop351.org/wp-admin/users.php?role=wpau_unapproved";
    return $wp_new_user_notification_email;
}