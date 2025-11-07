<?php
/*
Plugin Name: Old Post Warning
Plugin URI: http://www.ptm.ro/
Version: 0.2-20251107
Author: Serban Paun
Author URI: http://serban.ro/
License: GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Description: This plugin will show a custom notice above every single post, whenever a post is older than 1 year.

Old Post Warning is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Old Post Warning is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Old Post Warning. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
*/

add_filter( 'the_content', 'oldpostwarning');


register_activation_hook(__FILE__, function() {
    // Set default value of 365 days if no value exists
    if (get_option('opw_nodays') === false) {
        update_option('opw_nodays', 365);
    }
    
    $days = get_option('opw_nodays', 365);
    $default_warning = "THIS POST IS OLDER THAN $days";
    if (get_option('opw_warning') === false) {
        update_option('opw_warning', $default_warning);
    }
});


function oldpostwarning( $content ) {

    if ( is_single() )
    $today = current_time('Y-m-d', $gmt = 0);
    $postdate = get_the_date ('Y-m-d');
    $todayts = strtotime($today);
    $postdatets = strtotime($postdate);
    $diff = ($todayts - $postdatets)/60/60/24;
    $days = get_option('opw_nodays', 365);

    // Make sure $days is a valid number between 1 and 366
    if (empty($days) || !is_numeric($days) || intval($days) < 1 || intval($days) > 366) {
        $days = 365;
    } else {
        $days = intval($days);
    }

    if ( $diff > $days ) {
    $diff = '<div class="oldpostwarning1">'.get_option('opw_warning').'</div><br />';
    } else { $diff = NULL; }
    $content = $diff . $content;
    return $content;

}

add_action('admin_menu', 'opw_my_admin_menu');

function opw_my_admin_menu() {
    add_menu_page('Old Post Warning - Plugin options', 'Old Post Warning', 'manage_options', 'opw-options','opw_plugin_options');
    add_action( 'admin_init', 'opw_settings' );
 }

function opw_settings() {
    register_setting( 'opw-group', 'opw_warning' );
    register_setting( 'opw-group', 'opw_nodays' );
 }

// RICH EDITOR
add_action('admin_print_scripts', 'opw_do_jslibs' );
add_action('admin_print_styles', 'opw_do_css' );

function opw_do_css()
{
    wp_enqueue_style('thickbox');
}

function opw_do_jslibs()
{
    wp_enqueue_script('editor');
    wp_enqueue_script('thickbox');
    add_action( 'admin_head', 'wp_tiny_mce' );
}
// END RICH EDITOR

function opw_plugin_options() { ?>

<div class="wrap">
<h2>Old Post Warning - Plugin Options</h2>
    <form method="post" action="options.php">
        <?php settings_fields( 'opw-group' ); ?>
        <h3>Text warning to show on old posts:</h3>
	<div id="opwtext">
	<?php
	// the_editor(get_option('opw_warning'), 'opw_warning',false,false);
    $days = get_option('opw_nodays', 365);

    // Trim to remove whitespace and check if empty or not a valid number
    if (empty($days) || !is_numeric($days) || intval($days) < 1 || intval($days) > 366) {
        $days = 365;
    } else {
        $days = intval($days);
    }
    $default_warning = "THIS POST IS OLDER THAN $days";
    if (get_option('opw_warning') === false) {
        update_option('opw_warning', $default_warning);
    }
    wp_editor(
        get_option('opw_warning', $default_warning),
        'opw_warning',
        array(
            'media_buttons' => true,
            'textarea_name' => 'opw_warning',
            'textarea_rows' => 5,
            'teeny' => true,
            'quicktags' => false
        )
    );
?>

    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p></div>
    <h3>Number of days after which a post is considered old:</h3>
    <input type="number" min="1" max="365" name="opw_nodays" value="<?php echo $days; ?>" />
    <p class="submit">
</form>
</div>

<?php } ?>
