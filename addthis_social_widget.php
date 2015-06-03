<?php
/**
 * Plugin Name: AddThis Sharing Buttons
 * Plugin URI: http://www.addthis.com
 * Description: Use the AddThis suite of website tools which includes sharing, following, recommended content, and conversion tools to help you make your website smarter. With AddThis, you can see how your users are engaging with your content, provide a personalized experience for each user and encourage them to share, subscribe or follow.
 * Version: 5.0
 * Author: The AddThis Team
 * Author URI: http://www.addthis.com/
 * License: GPL2
 *
 * +--------------------------------------------------------------------------+
 * | Copyright (c) 2008-2015 AddThis, LLC                                     |
 * +--------------------------------------------------------------------------+
 * | This program is free software; you can redistribute it and/or modify     |
 * | it under the terms of the GNU General Public License as published by     |
 * | the Free Software Foundation; either version 2 of the License, or        |
 * | (at your option) any later version.                                      |
 * |                                                                          |
 * | This program is distributed in the hope that it will be useful,          |
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
 * | GNU General Public License for more details.                             |
 * |                                                                          |
 * | You should have received a copy of the GNU General Public License        |
 * | along with this program; if not, write to the Free Software              |
 * | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA |
 * +--------------------------------------------------------------------------+
 */

if (!defined('ADDTHIS_INIT')) define('ADDTHIS_INIT', 1);
else return;

define( 'addthis_style_default' , 'fb_tw_p1_sc');
define( 'ADDTHIS_PRODUCT_VERSION' , 'wpp-5.0');
define( 'ADDTHIS_ATVERSION', '300');
define( 'ADDTHIS_ATVERSION_MANUAL_UPDATE', -1);
define( 'ADDTHIS_ATVERSION_AUTO_UPDATE', 0);
define( 'ADDTHIS_ATVERSION_REVERTED', 1);
define( 'ENABLE_ADDITIONAL_PLACEMENT_OPTION', 0);

require_once('AddThisWordPressConnector.php');
require_once('AddThisConfigs.php');

$cmsConnector = new AddThisWordPressConnector();
$addThisConfigs = new AddThisConfigs($cmsConnector);
$addthis_options = $addThisConfigs->getConfigs();

require_once('addthis_settings_functions.php');

/**
 * Show Plugin activation notice on first installation*
 */
function pluginActivationNotice()
{
    $run_once = get_option('addthis_run_once');

    if (!$run_once) {
        wp_enqueue_style(
            'addThisStylesheet',
            plugins_url('css/style.css', __FILE__)
        );
        $html = '<div class="addthis_updated wrap">';
        $html .= '<span>'.
                    'Congrats! You\'ve Installed AddThis Sharing Buttons'.
                  '</span>';
        $html .= '<span><a class="addthis_configure" href="'
                . 'options-general.php?page=addthis_social_widget' .
                '">Configure it now</a> >></span>';
        $html .= '</div><!-- /.updated -->';
        echo '<style>div#message.updated{ display: none; }</style>';
        echo $html;

        update_option('addthis_run_once', true);
    }
}

/**
 * Make sure the option gets added on registration
 * @since 2.0.6
 */
add_action('admin_notices', 'pluginActivationNotice');
function addthis_activation_hook(){
    $cmsConnector = new AddThisWordPressConnector();
    $addThisConfigs = new AddThisConfigs($cmsConnector);
    $options = $addThisConfigs->getConfigs();
    $addThisConfigs->saveConfigs($options);
}

register_activation_hook( __FILE__, 'addthis_activation_hook' );

if (   isset($_POST['addthis_plugin_controls'])
    && $_POST['addthis_plugin_controls'] != $addthis_options['addthis_plugin_controls']
) {
    if($_POST['addthis_plugin_controls'] != 'AddThis') {
        $addthis_options['addthis_plugin_controls'] = 'WordPress';
    } else {
        $addthis_options['addthis_plugin_controls'] = 'AddThis';
    }
    $addthis_options = $addThisConfigs->saveConfigs($addthis_options);
}

add_action('wp_head', 'addthis_minimal_css');
function addthis_minimal_css() {
    wp_enqueue_style( 'output', _addthis_css_location_base() . 'output.css' );
}

if ($addthis_options['addthis_plugin_controls'] == "AddThis") {
    require_once 'addthis-for-wordpress.php';
    new Addthis_Wordpress(isset($upgraded), $addThisConfigs);
} else {

    // Show old version of the plugin till upgrade button is clicked

    // Add settings link on plugin page
    function your_plugin_settings_link($links) {
      $settings_link = '<a href="options-general.php?page=addthis_social_widget">Settings</a>';
      array_push($links, $settings_link);
      return $links;
    }

    $plugin = plugin_basename(__FILE__);
    add_filter("plugin_action_links_$plugin", 'your_plugin_settings_link' );


    // Setup our shared resources early
    // addthis_addjs.php is a standard class shared by the various AddThis plugins to make it easy for us to include our bootstrapping JavaScript only once. Priority should be lowest for Share plugin.
    add_action('init', 'addthis_early', 0);
    function addthis_early(){
        global $addthis_addjs;
        global $addThisConfigs;
        if (!isset($addthis_addjs)){
            require('includes/addthis_addjs_new.php');
            $addthis_addjs = new AddThis_addjs($addThisConfigs);
        }
    }

    $addthis_settings = array();
    $addthis_settings['isdropdown'] = 'true';
    $addthis_settings['customization'] = '';
    $addthis_settings['menu_type'] = 'dropdown';
    $addthis_settings['language'] = 'en';
    $addthis_settings['fallback_username'] = '';
    $addthis_settings['style'] = 'share';
    $addthis_settings['atversion'] = ADDTHIS_ATVERSION;
    $addthis_settings['placement'] = ENABLE_ADDITIONAL_PLACEMENT_OPTION;

    $addthis_languages = array(''=>'Automatic','af'=>'Afrikaaner', 'ar'=>'Arabic', 'zh'=>'Chinese', 'cs'=>'Czech', 'da'=>'Danish', 'nl'=>'Dutch', 'en'=>'English', 'fa'=>'Farsi', 'fi'=>'Finnish', 'fr'=>'French', 'ga'=>'Gaelic', 'de'=>'German', 'el'=>'Greek', 'he'=>'Hebrew', 'hi'=>'Hindi', 'it'=>'Italian', 'ja'=>'Japanese', 'ko'=>'Korean', 'lv'=>'Latvian', 'lt'=>'Lithuanian', 'no'=>'Norwegian', 'pl'=>'Polish', 'pt'=>'Portugese', 'ro'=>'Romanian', 'ru'=>'Russian', 'sk'=>'Slovakian', 'sl'=>'Slovenian', 'es'=>'Spanish', 'sv'=>'Swedish', 'th'=>'Thai', 'ur'=>'Urdu', 'cy'=>'Welsh', 'vi'=>'Vietnamese');

    $addthis_menu_types = array('static', 'dropdown', 'toolbox');

    $addthis_styles = array(
        'share'          => array('img'=>'lg-share-%lang%.gif', 'w'=>125, 'h'=>16),
        'bookmark'       => array('img'=>'lg-bookmark-en.gif',  'w'=>125, 'h'=>16),
        'addthis'        => array('img'=>'lg-addthis-en.gif',   'w'=>125, 'h'=>16),
        'share-small'    => array('img'=>'sm-share-%lang%.gif', 'w'=>83,  'h'=>16),
        'bookmark-small' => array('img'=>'sm-bookmark-en.gif',  'w'=>83,  'h'=>16),
        'plus'           => array('img'=>'sm-plus.gif',         'w'=>16,  'h'=>16)
    );

    $addthis_options = get_option('addthis_settings');
    $atversion = is_array($addthis_options) && array_key_exists('atversion_reverted', $addthis_options) && $addthis_options['atversion_reverted'] == 1 ? $addthis_options['atversion'] : ADDTHIS_ATVERSION;

    $addthis_new_styles = array(
        'large_toolbox' => array(
            'src' =>  '
                <div class="addthis_toolbox addthis_default_style addthis_32x32_style" %1$s >
                    <a class="addthis_button_preferred_1"></a>
                    <a class="addthis_button_preferred_2"></a>
                    <a class="addthis_button_preferred_3"></a>
                    <a class="addthis_button_preferred_4"></a>
                    <a class="addthis_button_compact"></a>
                    <a class="addthis_counter addthis_bubble_style"></a>
                </div>',
            'img' => 'toolbox-large.png',
            'name' => 'Large Toolbox',
            'above' => 'hidden ',
            'below' => 'hidden'
        ),
        'small_toolbox' => array(
            'src' => '
                <div class="addthis_toolbox addthis_default_style addthis_" %1$s >
                    <a class="addthis_button_preferred_1"></a>
                    <a class="addthis_button_preferred_2"></a>
                    <a class="addthis_button_preferred_3"></a>
                    <a class="addthis_button_preferred_4"></a>
                    <a class="addthis_button_compact"></a>
                    <a class="addthis_counter addthis_bubble_style"></a>
                </div>',
            'img' => 'toolbox-small.png',
            'name' => 'Small Toolbox',
            'above' => 'hidden ',
            'below' => ''
        ),
        'fb_tw_p1_sc' => array(
            'src' => '
                <div class="addthis_toolbox addthis_default_style " %1$s  >
                    <a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
                    <a class="addthis_button_tweet"></a>
                    <a class="addthis_button_pinterest_pinit"></a>
                    <a class="addthis_counter addthis_pill_style"></a>
                </div>',
            'img' => 'horizontal_share_rect.png',
            'name' => 'Like, Tweet, +1, Share',
            'above' => '',
            'below' => '',
        ),
        'button' => array(
            'src' => '
                <div>
                    <a class="addthis_button" href="//addthis.com/bookmark.php?v='.$atversion.'" %1$s>
                        <img src="//cache.addthis.com/cachefly/static/btn/v2/lg-share-en.gif" width="125" height="16" alt="Bookmark and Share" style="border:0"/>
                    </a>
                </div>',
            'img' => 'horizontal_share.png',
            'name' => 'Classic Share Button',
            'above' => 'hidden ',
            'below' => 'hidden'
        )
    );


    //add_filter('the_title', 'at_title_check');
    /**
     * @deprecated
     * @todo Add _deprecated_function notice.
     */
    function at_title_check($title)
    {

        global $addthis_did_filters_added;

        if (!isset ($addthis_did_filters_added) || $addthis_did_filters_added != true)
        {
            addthis_add_content_filters();
            add_filter('the_content', 'addthis_script_to_content');
        }

        return $title;
    }


    /**
     * @deprecated
     * @todo Add _deprecated_function notice.
     */
    function addthis_script_to_content($content)
    {
        global $addthis_did_script_output;

        if (!isset($addthis_did_script_output) )
        {
            $addthis_did_script_output = true;
            $content .= addthis_output_script(true);
        }
        return $content ;
    }


    add_filter('language_attributes', 'addthis_language_attributes');
    function addthis_language_attributes($input)
    {
        return $input . ' xmlns:fb="http://ogp.me/ns/fb#" xmlns:addthis="http://www.addthis.com/help/api-spec" ';
    }


    /**
     * Converts our old many options in to one beautiful array
     *
     */

     // Caution:  Using this filter to disable upgrades may have unexpected consequences.
    if ( apply_filters( 'at_do_options_upgrades', '__return_true') || apply_filters( 'addthis_do_options_upgrades', '__return_true')   )
    {
        function addthis_options_200()
        {
            global $current_user;
            global $addThisConfigs;

            $user_id = $current_user->ID;
            $addthis_new_options = array();

            if ($asynchronous_loading = get_option('addthis_asynchronous_loading'))
                $addthis_new_options['addthis_asynchronous_loading'] = $asynchronous_loading;

            if ($append_data = get_option('addthis_append_data'))
                $addthis_new_options['addthis_append_data'] = $append_data;

            // populate variables for share button location template settings
            $locationTemplateFields = $addThisConfigs->getFieldsForContentTypeSharingLocations();
            foreach ($locationTemplateFields as $field) {
                $optionName = $field['fieldName'];
                $variableName = $field['variableName'];

                if ($$variableName = get_option($optionName)) {
                    $addthis_new_options[$optionName] = $$variableName;
                }
            }

            if ($aftertitle = get_option('addthis_aftertitle'))
                $addthis_new_options['addthis_aftertitle'] = $aftertitle;

            if ($beforecomments = get_option('addthis_beforecomments'))
                $addthis_new_options['addthis_beforecomments'] = $beforecomments;

            $addthis_new_options['below'] = 'none';

            if ($header_background = get_option('addthis_header_background'))
                $addthis_new_options['addthis_header_background'] = $header_background;
            if ($header_color = get_option('addthis_header_color'))
                $addthis_new_options['addthis_header_color'] = $header_color;
            if ($brand = get_option('addthis_brand'))
                $addthis_new_options['addthis_brand'] = $brand;
            if ($language = get_option('addthis_language'))
                $addthis_new_options['addthis_language'] = $language;

            //version check
            if ($atversion = get_option('atversion'))
                $addthis_new_options['atversion'] = $atversion;


            // Above is new, set it to none
            $addthis_new_options['above'] = 'none';

            // Save option
            add_option('addthis_settings', $addthis_new_options);

            // if the option saved, delete the old options

            delete_option('addthis_asynchronous_loading');
            delete_option('addthis_fallback_username');
            delete_option('addthis_product');
            delete_option('addthis_isdropdown');
            delete_option('addthis_menu_type');
            delete_option('addthis_append_data');
            delete_option('addthis_aftertitle');
            delete_option('addthis_beforecomments');
            delete_option('addthis_style');
            delete_option('addthis_header_background');
            delete_option('addthis_header_color');
            delete_option('addthis_brand');
            delete_option('addthis_language');
            delete_option('atversion');

            // delete each share button location template settings
            foreach ($locationTemplateFields as $field) {
                $optionName = $field['fieldName'];
                delete_option($optionName);
            }

            // old options that are no longer used, to clean up after ourshelves
            if (false) {
                $deprecatedFields = _addthis_deprecated_fields();
                foreach ($deprecatedFields as $field) {
                    delete_option($field);
                }
            }

            global $current_user;
            $user_id = $current_user->ID;

            add_user_meta($user_id, 'addthis_nag_updated_options', 'true', true);
        }

        function addthis_options_240()
        {
            global $addThisConfigs;
            $options = $addThisConfigs->getConfigs();

            // Add An option for the AT Version
            $options['atversion'] = ADDTHIS_ATVERSION;

            //$options['wpfooter'] = false;
            $addThisConfigs->saveConfigs($options);
        }
    }

    function addthis_add_for_check_footer() {

    }

    function addthis_check_footer() {

    }


    /**
    * Generates unique IDs
    */
    function cuid()
    {
        $base = get_option('home');
        $cuid = hash_hmac('md5', $base, 'addthis');
        return $cuid;
    }

    define('ADDTHIS_FALLBACK_USERNAME', 'wp-'.cuid() );

    /**
    * Returns major.minor WordPress version.
    */
    function addthis_get_wp_version() {
        return (float)substr(get_bloginfo('version'),0,3);
    }

    /**
    * For templates, we need a wrapper for printing out the code on demand.
    */
    function addthis_print_widget($url = null, $title = null, $style = addthis_style_default) {
        global $addthis_styles, $addthis_new_styles;
        global $addThisConfigs;
        $styles = array_merge($addthis_styles, $addthis_new_styles);

        $options = $addThisConfigs->getConfigs();

        $identifier = addthis_get_identifier($url, $title);

        echo "\n<!-- AddThis Custom -->\n";
        if (!is_array($style) && isset($addthis_new_styles[$style])) {
            echo sprintf($addthis_new_styles[$style]['src'], $identifier);
        } elseif ($style == 'above') {
            $above = addthis_display_widget_above($styles, $url, $title, $options);
            echo sprintf($above, $identifier);
        } elseif ($style == 'below') {
            $below = addthis_display_widget_below($styles, $url, $title, $options);
            echo sprintf($below, $identifier);
        } elseif (is_array($style))
            echo addthis_custom_toolbox($style, $url, $title);
        echo "\n<!-- End AddThis Custom -->\n";
    }

    /*
    * Generates the addthis:url and addthis:title attributes
    */

    function addthis_get_identifier($url = null, $title = null)
    {

        if (! is_null($url) )
            $identifier =  "addthis:url='$url' ";
        if (! is_null($title) )
            $identifier .= "addthis:title='$title'";

        if (! isset($identifier) )
            $identifier = '';

        return $identifier;
    }

    /**
    * Options is an array that contains
    * size - either 16 or 32.  Defaults to 16
    * services - comma sepperated list of services
    * preferred - number of Prefered services to be displayed after listed services
    * more - bool to show or not show the more icon at the end
    *
    * @param $options array
    */

    function addthis_custom_toolbox($options, $url, $title)
    {
        $identifier = addthis_get_identifier($url, $title);

        $outerClasses = 'addthis_toolbox addthis_default_style';

        if (isset($options['size']) && $options['size'] == '32')
            $outerClasses .= ' addthis_32x32_style';

        if (isset($options['type']) && $options['type'] != 'custom_string') {
            $button = '<div class="'.$outerClasses.'" '.$identifier.' >';

            if (isset($options['services']) ) {
                $services = explode(',', $options['services']);
                foreach ($services as $service)
                {
                    $service = trim($service);
                    if ($service == 'more' || $service == 'compact') {
                        if (isset($options['type']) && $options['type'] != 'fb_tw_p1_sc') {
                            $button .= '<a class="addthis_button_compact"></a>';
                        }
                    }
                    else if ($service == 'counter') {
                        if (isset($options['type']) && $options['type'] == 'fb_tw_p1_sc') {
                            $button .= '<a class="addthis_counter addthis_pill_style"></a>';
                        }
                        else {
                            $button .= '<a class="addthis_counter addthis_bubble_style"></a>';
                        }
                    }
                    else if ($service == 'google_plusone') {
                        $button .= '<a class="addthis_button_google_plusone" g:plusone:size="medium"></a>';
                    }
                    else
                        $button .= '<a class="addthis_button_'.strtolower($service).'"></a>';
                }
            }

            if (isset($options['preferred']) && is_numeric($options['preferred']))
            {
                for ($a = 1; $a <= $options['preferred']; $a++)
                {
                    $button .= '<a class="addthis_button_preferred_'.$a.'"></a>';
                }
            }

            if (isset($options['more']) && $options['more'] == true)
            {
                    $button .= '<a class="addthis_button_compact"></a>';
            }

            if (isset($options['counter']) && ($options['counter'] != "") && ($options['counter'] !== false))
            {
                if ($options['counter'] === true)
                {  //no style was specified
                   $button .= '<a class="addthis_counter"></a>';
                }
                else
                {  //a specific style was specified such as pill_style or bubble_style
                    $button .= '<a class="addthis_counter addthis_'.$options['counter'].'"></a>';
                }
            }

            $button .= '</div>';
        }

        return $button;
    }

    /**
    * Adds AddThis CSS to page. Only used for admin dashboard in WP 2.7 and higher.
    */
    function addthis_print_style() {
        wp_enqueue_style( 'addthis' );
    }

    /**
    * Adds AddThis script to page. Only used for admin dashboard in WP 2.7 and higher.
    */
    function addthis_print_script() {
        wp_enqueue_script( 'addthis' );
    }

    add_action('admin_notices', 'addthis_admin_notices');

    function addthis_admin_notices(){
        if (! current_user_can('manage_options') ||( defined('ADDTHIS_NO_NOTICES') && ADDTHIS_NO_NOTICES == true ) )
            return;

        global $current_user ;
        $user_id = $current_user->ID;
        $options = get_option('addthis_settings');

        if (!$options && ! get_user_meta($user_id, 'addthis_ignore_notices')) {
            echo '<div class="updated addthis_setup_nag"><p>';
            printf(__('Configure the AddThis plugin to enable users to share your content around the web.<br /> <a href="%1$s">Configuration options</a> | <a href="%2$s" id="php_below_min_nag-no">Ignore this notice</a>'),
                admin_url('options-general.php?page=' .  basename(__FILE__) ),
                '?addthis_nag_ignore=0');
            echo "</p></div>";
        } elseif ((get_user_meta($user_id, 'addthis_nag_updated_options'))) {
            echo '<div class="updated addthis_setup_nag"><p>';
            printf( __('We have updated the options for the AddThis plugin.  Check out the <a href="%1$s">AddThis settings page</a> to see the new styles and options.<br /> <a href="%1$s">See New Options</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="%2$s">Ignore this notice</a>'),
            admin_url('options-general.php?page=' . basename(__FILE__) ),
            '?addthis_nag_updated_ignore=0');
            echo "</p></div>";
        }
    }
    add_action('admin_init', 'addthis_nag_ignore');

    function addthis_nag_ignore()
    {
        global $current_user;
        $user_id = $current_user->ID;

        if (isset($_GET['addthis_nag_ignore']) && '0' == $_GET['addthis_nag_ignore'])
            add_user_meta($user_id, 'addthis_ignore_notices', 'true', true);
        if (isset($_GET['addthis_nag_updated_ignore']) && '0' == $_GET['addthis_nag_updated_ignore'])
            delete_user_meta($user_id, 'addthis_nag_updated_options', 'true');
    }

    function addthis_plugin_useragent($userAgent)
    {
        global $cmsConnector;
        return $userAgent . 'ATV/' . $cmsConnector->getPluginVersion();
    }

    function addthis_render_dashboard_widget_holder()
    {
         echo '<p class="widget-loading hide-if-no-js">' . __( 'Loading&#8230;' ) . '</p><p class="describe hide-if-js">' . __('This widget requires JavaScript.') . '</p>';
    }

    add_action('wp_ajax_at_save_transient', 'addthis_save_transient');

    function addthis_save_transient() {
        global $wpdb; // this is how you get access to the database


        parse_str($_POST['value'], $values);

        // verify nonce (or die).
        $nonce = $values['_wpnonce'];
        if (!wp_verify_nonce($nonce, 'addthis-options')) {
            die('Security check');
        }

        // Parse Post data
        $option_array = addthis_parse_options($values);

        // Set Transient
        if (get_transient('addthis_settings') !==  false) {
            delete_transient('addthis_settings');
        }

        $eh = set_transient('addthis_settings', $option_array, 120);

        print_r($option_array);

        die();
    }

    function addthis_save_settings($input)
    {
        global $addThisConfigs;
        $options_array = $addThisConfigs->getConfigs();

        // if special, do special, else
        if (   isset($input['addthis_csr_confirmation'])
            && $input['addthis_csr_confirmation'] == 'true'
        ) {
            if (   isset($input['addthis_profile'])
                && wp_verify_nonce($_POST['pubid_nonce'], 'update_pubid')
            ) {
                $options_array['addthis_profile'] = $input['addthis_profile'];
            }
        } else {
            $options_array = addthis_parse_options($input);
        }

        return $options_array;
    }


    /**
     * goes through all the options, sanitizing, verifying and returning for storage what needs to be there
     */
    function addthis_parse_options($data)
    {
        global $addthis_styles, $addthis_new_styles;
        global $addThisConfigs;

        $styles = array_merge($addthis_styles, $addthis_new_styles);
        $below_custom_styles = $above_custom_styles = '';
        $options = $addThisConfigs->getConfigs();

        if ( isset($data['above_sharing']))
            $options['above_sharing'] = $data['above_sharing'];
        if ( isset($data['below_sharing']))
            $options['below_sharing'] = $data['below_sharing'];

        if ( isset ($data['show_below']) )
            $options['below'] = 'none';
        elseif (isset($data['below'], $styles[$data['below']]) )
            $options['below'] = $data['below'];
        elseif ($data['below'] == 'disable')
            $options['below'] = $data['below'];
        elseif ($data['below'] == 'none')
        {
            $options['below'] = 'none';
        }
        elseif ($data['below'] == 'custom')
        {
            $options['below_do_custom_services'] = isset($data['below_do_custom_services']) ;
            $options['below_do_custom_preferred'] = isset($data['below_do_custom_preferred']) ;

            $options['below'] = 'custom';
            $options['below_custom_size'] =  ( $data['below_custom_size'] == '16' || $data['below_custom_size'] == 32 ) ? $data['below_custom_size'] : '' ;
            $options['below_custom_services'] = sanitize_text_field( $data['below_custom_services'] );
            $options['below_custom_preferred'] = sanitize_text_field( $data['below_custom_preferred'] );
            $options['below_custom_more'] = isset($data['below_custom_more']);
        }
        elseif ($data['below'] == 'custom_string')
        {
            $options['below'] = 'custom_string';
            if (strpos($data['below_custom_string'], "style=") != false) {
                $custom_style = explode('style=', $data['below_custom_string']);
                $custom_style = explode('>', $custom_style[1]);
                $custom_style = explode(' ', $custom_style[0]);
                $below_custom_styles = " style=$custom_style[0]";
            }
            $options['below_custom_string'] = addthis_kses($data['below_custom_string'], $below_custom_styles);
        }

        if ( isset($data['wpfooter']))
            $options['wpfooter'] = (bool) $data['wpfooter'];

        if ( isset ($data['show_above']) )
            $options['above'] = 'none';
        elseif ( isset($styles[$data['above']]) )
            $options['above'] = $data['above'];
        elseif ($data['above'] == 'disable')
            $options['above'] = $data['above'];
        elseif ($data['above'] == 'none')
        {
            $options['above'] = 'none';
        }
        elseif ($data['above'] == 'custom')
        {

            $options['above_do_custom_services'] = isset($data['above_do_custom_services']) ;
            $options['above_do_custom_preferred'] = isset($data['above_do_custom_preferred']) ;
            $options['above'] = 'custom';
            $options['above_custom_size'] =  ( $data['above_custom_size'] == '16' || $data['above_custom_size'] == 32 ) ? $data['above_custom_size'] : '' ;
            $options['above_custom_services'] = sanitize_text_field( $data['above_custom_services'] );
            $options['above_custom_preferred'] = (int) $data['above_custom_preferred'] ;
            $options['above_custom_more'] = isset($data['above_custom_more']);
        }
        elseif ($data['above'] == 'custom_string')
        {
            //[addthis_twitter_template]
            if ( isset ($data['addthis_twitter_template']) && strlen($data['addthis_twitter_template'])  != 0  ) {
                 //Parse the first twitter username to be used with via
                 $options['addthis_twitter_template'] = get_first_twitter_username(sanitize_text_field($data['addthis_twitter_template']));
            }

            $options['above'] = 'custom_string';
            if (strpos($data['above_custom_string'], "style=") != false) {
                $custom_style = explode('style=', $data['above_custom_string']);
                $custom_style = explode('>', $custom_style[1]);
                $custom_style = explode(' ', $custom_style[0]);
                $above_custom_styles = " style=$custom_style[0]";
            }
            $options['above_custom_string'] = addthis_kses($data['above_custom_string'], $above_custom_styles);

        }

        if (isset($data['addthis_profile'])) {
            $options['addthis_profile'] = sanitize_text_field($data['addthis_profile']);
        }

        if ( isset($data['above_sharing']))
            $options['above_sharing'] = $data['above_sharing'];
        if ( isset($data['below_sharing']))
            $options['below_sharing'] = $data['below_sharing'];

        if ( isset ($data['show_below']) )
            $options['below'] = 'none';
        elseif ( isset($styles[$data['below']]) )
            $options['below'] = $data['below'];
        elseif ($data['below'] == 'disable')
            $options['below'] = $data['below'];
        elseif ($data['below'] == 'none')
        {
            $options['below'] = 'none';
        }
        elseif ($data['below'] == 'custom')
        {
            $options['below_do_custom_services'] = isset($data['below_do_custom_services']) ;
            $options['below_do_custom_preferred'] = isset($data['below_do_custom_preferred']) ;

            $options['below'] = 'custom';
            $options['below_custom_size'] =  ( $data['below_custom_size'] == '16' || $data['below_custom_size'] == 32 ) ? $data['below_custom_size'] : '' ;
            $options['below_custom_services'] = sanitize_text_field( $data['below_custom_services'] );
            $options['below_custom_preferred'] = sanitize_text_field( $data['below_custom_preferred'] );
            $options['below_custom_more'] = isset($data['below_custom_more']);
        }
        elseif ($data['below'] == 'custom_string')
        {
            $options['below'] = 'custom_string';
            if (strpos($data['below_custom_string'], "style=") != false) {
                $custom_style = explode('style=', $data['below_custom_string']);
                $custom_style = explode('>', $custom_style[1]);
                $custom_style = explode(' ', $custom_style[0]);
                $below_custom_styles = " style=$custom_style[0]";
            }
            $options['below_custom_string'] = addthis_kses($data['below_custom_string'], $below_custom_styles);
        }

        // All the checkbox fields
        $checkboxFields = array(
            'addthis_508',
            'addthis_above_enabled',
            'addthis_addressbar',
            'addthis_aftertitle' ,
            'addthis_append_data',
            'addthis_append_data',
            'addthis_asynchronous_loading',
            'addthis_beforecomments',
            'addthis_below_enabled',
            'addthis_bitly',
            'addthis_sidebar_enabled',
        );

        // add all share button location template settings to list of checkbox fields
        $locationTemplateFields = $addThisConfigs->getFieldsForContentTypeSharingLocations();
        foreach ($locationTemplateFields as $field) {
            $optionName = $field['fieldName'];
            $checkboxFields[] = $optionName;
        }

        foreach ($checkboxFields as $field)
        {
            if (isset($data[$field]) && $data[$field]) {
                $options[$field] = true;
            } else {
                $options[$field] = false;
            }
        }

        if (isset($data['data_ga_property'])) {
            $options['data_ga_property'] = sanitize_text_field($data['data_ga_property']);
        }

        //[addthis_twitter_template]
        if ( isset ($data['addthis_twitter_template'])) {
             //Parse the first twitter username to be used with via
             $options['addthis_twitter_template'] = get_first_twitter_username(sanitize_text_field($data['addthis_twitter_template']));
        }

        if (isset ($data['addthis_bitly']) && strlen($data['addthis_bitly']) != 0 )
            $options['addthis_bitly'] = sanitize_text_field($data['addthis_bitly']);


        //[addthis_brand] =>
        if ( isset ($data['addthis_brand']) && strlen($data['addthis_brand'])  != 0  )
            $options['addthis_brand'] = sanitize_text_field($data['addthis_brand']);

        //[addthis_language] =>
        if ( isset ($data['addthis_language']))
            $options['addthis_language'] = sanitize_text_field($data['addthis_language']);


        //[atversion]=>
        if ( isset ($data['atversion']))
            $options['atversion'] = sanitize_text_field($data['atversion']);

        //[atversion_update_status]=>
        if ( isset ($data['atversion_update_status']))
            $options['atversion_update_status'] = sanitize_text_field($data['atversion_update_status']);

        if ( isset ($data['credential_validation_status']))
            $options['credential_validation_status'] = sanitize_text_field($data['credential_validation_status']);

        if ( isset ($data['addthis_header_background']) && strlen($data['addthis_header_background']) != 0 )
        {
            if (! strpos($data['addthis_header_background'], '#') === 0)
                $options['addthis_header_background'] =  '#' . sanitize_text_field($data['addthis_header_background']);
            else
                $options['addthis_header_background'] =  sanitize_text_field($data['addthis_header_background']);
        }

        if ( isset ($data['addthis_header_color']) && strlen($data['addthis_header_color']) != 0 )
        {
            if (! strpos($data['addthis_header_color'], '#') === 0)
                $options['addthis_header_color'] =  '#' . sanitize_text_field($data['addthis_header_color']);
            else
                $options['addthis_header_color'] =  sanitize_text_field($data['addthis_header_color']);
        }

        if (isset($data['addthis_config_json'])) {
            $options['addthis_config_json'] = sanitize_text_field($data['addthis_config_json']);
        }

        if (isset($data['addthis_share_json'])) {
            $options['addthis_share_json'] = sanitize_text_field($data['addthis_share_json']);
        }

        if (isset ($data['above_chosen_list']) && strlen($data['above_chosen_list']) != 0)
        {
            $options['above_chosen_list'] = sanitize_text_field($data['above_chosen_list']);
        }
        else {
            $options['above_chosen_list'] = "";
        }

        if (isset ($data['below_chosen_list']) && strlen($data['below_chosen_list']) != 0)
        {
            $options['below_chosen_list'] = sanitize_text_field($data['below_chosen_list']);
        }
        else {
            $options['below_chosen_list'] = "";
        }

        if(isset ($data['addthis_sidebar_position'])){
            $options['addthis_sidebar_position'] = $data['addthis_sidebar_position'];
        }

        if(isset ($data['addthis_sidebar_count'])){
            $options['addthis_sidebar_count'] = $data['addthis_sidebar_count'];
        }

        if(isset ($data['addthis_sidebar_theme'])){
            $options['addthis_sidebar_theme'] = $data['addthis_sidebar_theme'];
        }

        if(isset($data['addthis_environment'])){
            $options['addthis_environment'] = $data['addthis_environment'];
        }

        if(isset ($data['addthis_plugin_controls'])){
            $options['addthis_plugin_controls'] = $data['addthis_plugin_controls'];
        }

       return $options;
    }


    /**
    * Formally registers AddThis settings. Only called in WP 2.7+.
    */
    function register_addthis_settings() {
        register_setting('addthis', 'addthis_settings', 'addthis_save_settings');
    }

    /*
     * Used to make sure excerpts above the head aren't displayed wrong
    */
    function addthis_add_content_filters()
    {
        global $addthis_did_filters_added;
        global $addThisConfigs;
        $addthis_did_filters_added = true;

        $options = $addThisConfigs->getConfigs();

        if ( ! empty( $options) ){
            if (_addthis_excerpt_buttons_enabled()) {
                add_filter('get_the_excerpt', 'addthis_display_social_widget_excerpt', 11);
            }

            if ( isset($options['addthis_aftertitle']) && $options['addthis_aftertitle'] == true)
                add_filter('the_title', 'addthis_display_after_title', 11);

            add_filter('the_content', 'addthis_display_social_widget', 15);

        }
    }

   /**
    * Adds WP filter so we can append the AddThis button to post content.
    */
    function addthis_init()
    {
        global $addThisConfigs;
        add_action('wp_head', 'addthis_add_content_filters');

        if (   addthis_get_wp_version() >= 2.7
            || apply_filters('at_assume_latest', __return_false())
            || apply_filters('addthis_assume_latest', __return_false())
        ) {
            if (is_admin()) {
                add_action('admin_init', 'register_addthis_settings');
            }
        }

        $options = $addThisConfigs->getConfigs();
        $script_location = _addthis_js_location_base() . 'addthis.js' ;
        $style_location = _addthis_css_location_base() . 'addthis.css'   ;

        wp_register_style('addthis', $style_location);
        wp_register_script('addthis', $script_location, array('jquery-ui-tabs'));

        add_action('admin_print_styles-index.php', 'addthis_print_style');
        add_action('admin_print_scripts-index.php', 'addthis_print_script');

        add_filter('admin_menu', 'addthis_admin_menu');

        if ( apply_filters( 'at_do_options_upgrades', '__return_true') || apply_filters( 'addthis_do_options_upgrades', '__return_true')   )
        {
            if (   get_option('addthis_product') !== false
                && !is_array($options)
            ) {
                addthis_options_200();
            }

            // Upgrade to 240 and add at 300
            if (!isset($options['atversion']) || empty($options['atversion'])) {
                addthis_options_240();
            }
        }
        add_action( 'addthis_widget', 'addthis_print_widget', 10, 3);
    }

    /**
     * Places our options into a global associative array.
     * @refactor
     */
    function addthis_set_addthis_settings()
    {
        global $addthis_settings;
        $product = get_option('addthis_product');


        $style = get_option('addthis_style');
        if (strlen($style) == 0) $style = 'share';
        $addthis_settings['style'] = $style;

        $addthis_settings['menu_type'] = get_option('addthis_menu_type');

        $addthis_settings['fallback_username'] = get_option('addthis_fallback_username');

        $language = get_option('addthis_language');
        $addthis_settings['language'] = $language;

        $atversion = get_option('atversion');
        $addthis_settings['atversion'] = $atversion;

        $advopts = array('brand', 'append_data', 'language', 'header_background', 'header_color');
        $addthis_settings['customization'] = '';
        for ($i = 0; $i < count($advopts); $i++)
        {
            $opt = $advopts[$i];
            $val = get_option("addthis_$opt");
            if (isset($val) && strlen($val)) $addthis_settings['customization'] .= "var addthis_$opt = '$val';";
        }
    }

    add_action('widgets_init', 'addthis_widget_init');

    function addthis_widget_init()
    {
        require_once('addthis_sidebar_widget.php');
        //require_once('addthis_content_feed_widget.php');
        register_widget('AddThisSidebarWidget');
        //register_widget('AddThisContentFeedWidget');
    }

    function addthis_sidebar_widget($args)
    {
        extract($args);
        echo $before_widget;
        echo $before_title . $after_title . addthis_social_widget('', true);
        echo $after_widget;
    }

    // essentially replace wp_trim_excerpt until we have something better to use here
    function addthis_remove_tag($content, $text = '')
    {
        global $addThisConfigs;
        $options = $addThisConfigs->getConfigs();

        $raw_excerpt = $text;
        if ( '' == $text ) {

            $text = get_the_content('');
            $text = strip_shortcodes( $text );

            remove_filter('the_content', 'addthis_display_social_widget', 15);

            $text = apply_filters('the_content', $text);

            add_filter('the_content', 'addthis_display_social_widget', 15);

            $text = str_replace(']]>', ']]&gt;', $text);

            // 3.3 and earlier
            if (! function_exists('wp_trim_words'))
                $text = strip_tags($text);
            $excerpt_length = apply_filters('excerpt_length', 55);
            $excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');

            // 3.3 and later
            if (function_exists('wp_trim_words'))
            {
                $text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
            }
            else
            {
                $words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
                if ( count($words) > $excerpt_length ) {
                    array_pop($words);
                    $text = implode(' ', $words);
                    $text = $text . $excerpt_more;
                } else {
                    $text = implode(' ', $words);
                }
            }
            if (!_addthis_excerpt_buttons_enabled()) {
                return $text;
            }
            return addthis_display_social_widget($text, false, false);
        }
        else
        {
            return $content;
        }
    }

    /**
     * so named because it is added "later then the standard filter and all the WP internal filters"
     */
    function addthis_late_widget($link_text)
    {
        global $addThisConfigs;
        remove_filter('get_the_excerpt', 'addthis_late_widget');
        $options = $addThisConfigs->getConfigs();

        if (!_addthis_excerpt_buttons_enabled()) {
            return $link_text;
        }

        global $addthis_styles, $addthis_new_styles;
        $styles = array_merge($addthis_styles, $addthis_new_styles);

        $url = get_permalink();
        $title = get_the_title();
        $url_above = '';
        $url_below =  "addthis:url='$url' ";
        $url_below .=  "addthis:title='". esc_attr( $title) ." '";

        if (has_excerpt() && ! is_attachment() && isset($options['below']) && $options['below'] == 'custom')
        {
            $belowOptions['size'] = $options['below_custom_size'];
            if ($options['below_do_custom_services'])
                $belowOptions['services'] = $options['below_custom_services'];
            if ($options['below_do_custom_preferred'])
                $belowOptions['preferred'] = $options['below_custom_preferred'];
            $belowOptions['more'] = $options['below_custom_more'];
            return $link_text . apply_filters('addthis_below_content',  addthis_custom_toolbox($belowOptions, $url, $title) );
        }

        elseif ( isset ($styles[$options['below']]) && has_excerpt() && ! is_attachment()   )
        {
            $below = apply_filters('addthis_below_content', $styles[$options['below']]['src']);
        }
        else
        {
            $below = apply_filters('addthis_below_content','' );
        }
        return  $link_text . sprintf($below, $url_below);
    }


    function addthis_display_social_widget_excerpt($content)
    {
        // I don't think has_excerpt() is always necessarily true when calling "get_the_excerpt()",
        // but since this function is only as a get_the_excerpt() filter, we should probably
        // not care whether or not an excerpt is there since the caller obviously wants one.
        // needs testing/understanding.

        if (has_excerpt() && _addthis_excerpt_buttons_enabled()) {
            return addthis_display_social_widget($content, true, true);
        } else {
            return $content;
        }
    }

    function addthis_display_widget_above($styles, $url, $title, $options) {
        $above = '';
        if ($options['addthis_above_enabled'] == true){
            if (isset($styles[$options['above']])) {
                if (isset($options['above_chosen_list']) && strlen($options['above_chosen_list']) != 0) {
                    if (isset($options['above']) && $options['above'] == 'large_toolbox') {
                        $aboveOptions['size'] = '32';
                    } elseif (isset($options['above']) && $options['above'] == 'small_toolbox') {
                        $aboveOptions['size'] = '16';
                    }
                    $aboveOptions['type'] = $options['above'];
                    $aboveOptions['services'] = $options['above_chosen_list'];
                    $above = apply_filters('addthis_above_content', addthis_custom_toolbox($aboveOptions, $url, $title));
                } else {
                    $above = apply_filters('addthis_above_content', $styles[$options['above']]['src']);
                }
            } elseif ($options['above'] == 'custom') {
                $aboveOptions['size'] = $options['above_custom_size'];
                if ($options['above_do_custom_services'])
                    $aboveOptions['services'] = $options['above_custom_services'];
                if ($options['above_do_custom_preferred'])
                    $aboveOptions['preferred'] = $options['above_custom_preferred'];
                $aboveOptions['more'] = $options['above_custom_more'];
                $above = apply_filters('addthis_above_content', addthis_custom_toolbox($aboveOptions, $url, $title));
            } elseif ($options['above'] == 'custom_string') {
                $custom = preg_replace('/<\s*div\s*/', '<div %1$s ', $options['above_custom_string']);
                $above = apply_filters('addthis_above_content', $custom);
            }
        }
        return $above;
    }

    function addthis_display_widget_below($styles, $url, $title, $options) {
        $below = '';
        if ($options['addthis_below_enabled'] == true){
            if (isset($styles[$options['below']])) {
                if (isset($options['below_chosen_list']) && strlen($options['below_chosen_list']) != 0) {
                    if (isset($options['below']) && $options['below'] == 'large_toolbox') {
                        $belowOptions['size'] = '32';
                    } elseif (isset($options['below']) && $options['below'] == 'small_toolbox') {
                        $belowOptions['size'] = '16';
                    }
                    $belowOptions['type'] = $options['below'];
                    $belowOptions['services'] = $options['below_chosen_list'];
                    $below = apply_filters('addthis_above_content', addthis_custom_toolbox($belowOptions, $url, $title));
                } else {
                    $below = apply_filters('addthis_below_content', $styles[$options['below']]['src']);
                }
            } elseif ($options['below'] == 'custom') {
                $belowOptions['size'] = $options['below_custom_size'];
                $belowOptions['services'] = $options['below_custom_services'];
                $belowOptions['preferred'] = $options['below_custom_preferred'];
                $belowOptions['more'] = $options['below_custom_more'];
                $below = apply_filters('addthis_below_content', addthis_custom_toolbox($belowOptions, $url, $title));
            } elseif ($options['below'] == 'custom_string') {
                $custom = preg_replace('/<\s*div\s*/', '<div %1$s ', $options['below_custom_string']);
                $below = apply_filters('addthis_below_content', $custom);
            }
        }
        return $below;
    }

    function addthis_display_social_widget($content, $filtered = true, $below_excerpt = false)
    {
        global $addthis_styles, $addthis_new_styles, $post;
        global $addThisConfigs;

        $styles = array_merge($addthis_styles, $addthis_new_styles);
        $options = $addThisConfigs->getConfigs();

        $templateType = _addthis_determine_template_type();

        // get configs for this template type
        if (is_string($templateType)) {
            $fieldList = $addThisConfigs->getFieldsForContentTypeSharingLocations($templateType);
            foreach ($fieldList as $key => $field) {
                $fieldList[$field['location']] = $field;
                unset($fieldList[$key]);
            }

            $aboveFieldName = $fieldList['above']['fieldName'];
            $belowFieldName = $fieldList['below']['fieldName'];
            $displayAbove = (isset($options[$aboveFieldName]) && $options[$aboveFieldName] == true ) ? true : false;
            $displayBelow = (isset($options[$belowFieldName]) && $options[$belowFieldName] == true ) ? true : false;
        } else {
            $displayAbove = false;
            $displayBelow = false;
        }

        if ( $templateType === 'home' ) {
            $templateIsAnExcerpt = (boolean)(strpos($post->post_content, '<!--more-->') != false);
            if ($templateIsAnExcerpt) {
                if ($displayAbove && !_addthis_excerpt_buttons_enabled_above()) {
                    $displayAbove = false;
                }

                if ($displayBelow && !_addthis_excerpt_buttons_enabled_below()) {
                    $displayBelow = false;
                }
            }
        }

        $custom_fields = get_post_custom($post->ID);
        if (   isset($custom_fields['addthis_exclude'])
            && $custom_fields['addthis_exclude'][0] ==  'true'
        ) {
            $displayAbove = false;
            $displayBelow = false;
        }

        $displayAbove = apply_filters('addthis_post_exclude', $displayAbove);
        $displayBelow = apply_filters('addthis_post_exclude', $displayBelow);

        remove_filter('wp_trim_excerpt', 'addthis_remove_tag', 9, 2);
        remove_filter('get_the_excerpt', 'addthis_late_widget');
        $url = get_permalink();
        $title = get_the_title();
        $url_above =  "addthis:url='$url' ";
        $url_above .= "addthis:title='". esc_attr( $title) ." '";
        $url_below =  "addthis:url='$url' ";
        $url_below .= "addthis:title='". esc_attr( $title) ." '";

        // Still here?  Well let's add some social goodness
        if (   isset($options['above'])
            && $options['above'] != 'none'
            && $options['above'] != 'disable'
            && $displayAbove
        ) {
            $above = addthis_display_widget_above($styles, $url, $title, $options);
        } elseif ($displayAbove) {
            $above = apply_filters('addthis_above_content', '');
        } else {
            $above = '';
        }

        if (   isset($options['below'])
            && $options['below'] != 'none'
            && $options['below'] != 'disable'
            && $displayBelow
            && ! $below_excerpt
        ) {
            $below = addthis_display_widget_below($styles, $url, $title, $options);
        } elseif (   $below_excerpt
                  && $displayBelow
                  && $options['below'] != 'none'
        ) {
            $below = apply_filters('addthis_below_content','' );


            if (_addthis_excerpt_buttons_enabled()) {
                add_filter('get_the_excerpt', 'addthis_late_widget', 14);
            }
        } elseif ($displayBelow) {
            $below = apply_filters('addthis_below_content', '');
        } else {
            $below = '';
        }

        $at_flag = get_post_meta( $post->ID, '_at_widget', TRUE );
        if ($at_flag !== '0') {
            if ($displayAbove && isset($above)) {
                $content = sprintf($above, $url_above) . $content;
            }
            if ($displayBelow && isset($below)) {
                $content = $content . sprintf($below, $url_below);
            }
        }

        if (($displayAbove || $displayBelow) && $filtered) {
            add_filter('wp_trim_excerpt', 'addthis_remove_tag', 11, 2);
        }

        return $content;
    }

    add_action('wp_head', 'addthis_register_script_in_addjs', 20);

    function addthis_register_script_in_addjs(){
        global $addthis_addjs;
        $script = addthis_output_script(true, true);
        $addthis_addjs->addToScript($script);

        $addthis_sidebar = addthis_sidebar_script();
        $addthis_addjs->addAfterScript($addthis_sidebar);
    }

    //add_action('wp_footer', 'addthis_output_script');

    /**
     * Check to see if our Javascript has been outputted yet.  If it hasn't, return it.  Else, return false.
     *
     * @return mixed
    */
    function addthis_output_script($return = false, $justConfig = false )
    {
        global $addThisConfigs;
        $options = $addThisConfigs->getConfigs();

        $script = "\n<!-- AddThis Button Begin -->\n"
                 .'<script type="text/javascript">'
                 ."var addthis_product = '".ADDTHIS_PRODUCT_VERSION."';\n";


        $pub = $options['addthis_profile'];
        if (empty($options['addthis_profile'])) {
            $pub = 'wp-'.cuid();
        }
        $pub = urlencode($pub);

        $addthis_config = array();
        $addthis_share = array();

        $addthis_config["data_track_clickback"] = (isset($options['addthis_append_data']) && $options['addthis_append_data'] == true);

        if ( isset($options['data_ga_property']) ){
            $addthis_config['data_ga_property'] = $options['data_ga_property'];
            $addthis_config['data_ga_social'] = true;
        }

        $addthis_config["data_track_addressbar"] = (isset($options['addthis_addressbar']) && $options['addthis_addressbar'] == true);

        if ( isset($options['addthis_language']) && strlen($options['addthis_language']) == 2)
            $addthis_config['ui_language'] = $options['addthis_language'];

        if ( isset($options['atversion']))
            $addthis_config['ui_atversion'] = isset($options['atversion_update_status']) && $options['atversion_update_status'] == ADDTHIS_ATVERSION_REVERTED ? $options['atversion'] : ADDTHIS_ATVERSION;

        if ( isset($options['addthis_header_background']) )
            $addthis_config['ui_header_background'] = $options['addthis_header_background'];

        if ( isset($options['addthis_header_color']) )
            $addthis_config['ui_header_color'] = $options['addthis_header_color'];

        if ( isset($options['addthis_brand']) )
            $addthis_config['ui_cobrand'] = $options['addthis_brand'];

        if (isset($options['addthis_508']) && $options['addthis_508'] == true)
            $addthis_config['ui_508_compliant'] = true;

        $addthis_config = apply_filters('addthis_config_js_var', $addthis_config);
        $addthis_config_json = array_key_exists('addthis_config_json', $options) ? $options['addthis_config_json'] : '';
        $script = merge_config_with_json_config($script, $addthis_config, $addthis_config_json);

        if (isset($options['addthis_twitter_template'])){
            //The following twitter template translation is deprecated and replaced with via
            //$addthis_share['templates']['twitter'] =  esc_js($options['addthis_twitter_template']);
            $addthis_share['passthrough']['twitter']['via'] = esc_js(get_first_twitter_username($options['addthis_twitter_template']));

        }
        if (isset($options['addthis_bitly']) && $options['addthis_bitly']) {
            $addthis_share['url_transforms']['shorten']['twitter'] = 'bitly';
            $addthis_share['shorteners']['bitly'] = new stdClass();
        }

        if ($justConfig)
        {
            $return = '';
            $share = apply_filters('addthis_share_js_var', $addthis_share );
            if ( isset( $options['addthis_share_json'] ) && $options['addthis_share_json'] != '') {
                $addthis_json_share = array_key_exists('addthis_share_json', $options) ? $options['addthis_share_json'] : '';
                $return .= merge_share_with_json_share($addthis_share, $addthis_json_share);
            }
            else
            {
                if (! empty($share) )
                    $return .= 'if (typeof(addthis_share) == "undefined"){ addthis_share = ' . json_encode( apply_filters('addthis_share_js_var', $addthis_share ) ) .';}';
            }
            $return .= "\n";

            $return = merge_config_with_json_config($return, $addthis_config, $addthis_config_json);

            $return .= "\n";

            if(   isset($options['addthis_plugin_controls'])
               && $options['addthis_plugin_controls'] != "AddThis"
            ) {
                $return .= "addthis_config.ignore_server_config = true;";
                $return .= "\n";
            }

            return $return;
        }


        if ( isset( $options['addthis_share_json'] ) && $options['addthis_share_json'] != '')
            $script .= merge_share_with_json_share($addthis_share, $addthis_json_share);
        else
            $script .= 'if (typeof(addthis_share) == "undefined"){ addthis_share = ' . json_encode( apply_filters('addthis_share_js_var', $addthis_share ) ) .';}';
        $script .= '</script>';


        $script .= '<script type="text/javascript" src="//s7.addthis.com/js/'.$atversion.'/addthis_widget.js#pubid='.$pub.'"></script>';


        if ( ! is_admin() && ! is_feed() )
            echo $script;
        elseif ($return == true && ! is_admin() && ! is_feed() )
            return $script;
    }

    function addthis_sidebar_script(){
        global $addThisConfigs;
        $options = $addThisConfigs->getConfigs();
        $return = '';

        if ($options['addthis_sidebar_enabled'] != true) {
            return $return;
        }

        $templateType = _addthis_determine_template_type();

        if (is_string($templateType)) {
            $fieldList = $addThisConfigs->getFieldsForContentTypeSharingLocations($templateType, 'sidebar');
            $fieldName = $fieldList[0]['fieldName'];
            $display = (isset($options[$fieldName]) && $options[$fieldName]) ? true : false;
        } else {
            echo "templateType wasn't a string :-( <br />\n";
            $display = false;
        }

        if (!$display) {
            return $return;
        }

        $return .= "
            (function() {
                var at_interval = setInterval(function () {
                    if(window.addthis) {
                        clearInterval(at_interval);
                        addthis.layers(
                        {
                            'theme' : '".strtolower($options['addthis_sidebar_theme'])."',
                            'share' : {
                                'position' : '".$options['addthis_sidebar_position']."',
                                'numPreferredServices' : ".$options['addthis_sidebar_count']."
                            }
                        }
                        );
                    }
                },1000)
            }());";

        return $return;
    }

    /*
     * Merge the AddThis settings with that given using JSON format
     * @param String $appendString - The string to build and return the script
     * @param array $addthis_config - The setting array for AddThis config
     * @param String $addthis_json_config - The JSON String
     * @return String $appendString - The string to build and return the script
     */
    function merge_config_with_json_config($append_string, $addthis_config, $addthis_json_config) {
        if ( isset( $addthis_json_config ) &&   trim($addthis_json_config) != '') {
            $addthis_config_json_list = json_decode($addthis_json_config, true);
            if (! empty ($addthis_config_json_list) && ! empty ($addthis_config)) {
                foreach($addthis_config_json_list as $key_json => $json_config_value) {
                        $addthis_config[$key_json] = $json_config_value;
                }
            }
        }
        if (! empty ($addthis_config) )
            $append_string .= 'var addthis_config = '. json_encode($addthis_config) .';';
        return $append_string;
    }

    /*
     * Merge the AddThis settings with that given using JSON format
    * @param String $appendString - The string to build and return the script
    * @param array $addthis_share - The setting array for AddThis share
    * @param String $addthis_json_share - The JSON String
    * @return String $appendString - The string to build and return the script
    */
    function merge_share_with_json_share($addthis_share, $addthis_json_share) {
        $append_string = '';
        if ( isset( $addthis_json_share ) &&   trim($addthis_json_share) != '') {
            $addthis_share_json_list = json_decode($addthis_json_share, true);
            if (! empty ($addthis_share_json_list) && ! empty ($addthis_share)) {
                foreach($addthis_share_json_list as $key_json => $json_share_value) {
                    $addthis_share[$key_json] = $json_share_value;
                }
            }
        }
        if (! empty ($addthis_share) )
            $append_string = 'if (typeof(addthis_share) == "undefined"){ addthis_share = '. json_encode($addthis_share) .';}';
        return $append_string;
    }

    /**
    * Appends AddThis button to post content.
    */
    function addthis_social_widget($content, $onSidebar = false, $url = null, $title = null)
    {
        addthis_set_addthis_settings();
        global $addthis_settings;

        // add nothing to RSS feed or search results; control adding to static/archive/category pages
        if (!$onSidebar)
        {
            if (   $addthis_settings['sidebar_only']
                || is_feed()
                || is_search()
                || is_home()
                || is_page()
                || is_archive()
                || is_category()
            ) {
                return $content;
            }
        }

        $pub = $addthis_settings['addthis_profile'];
        if (!$pub) {
            $pub = 'wp-'.cuid();
        }
        $pub = urlencode($pub);

        $link  = !is_null($url) ? $url : ($onSidebar ? get_bloginfo('url') : get_permalink());
        $title = !is_null($title) ? $title : ($onSidebar ? get_bloginfo('title') : the_title('', '', false));

        $content .= "\n<!-- AddThis Button BEGIN -->\n"
                    .'<script type="text/javascript">'
                    ."\n//<!--\n"
                    ."var addthis_product = '".ADDTHIS_PRODUCT_VERSION."';\n";


        if (strlen($addthis_settings['customization']))
        {
            $content .= ($addthis_settings['customization']) . "\n";
        }

        if ($addthis_settings['menu_type'] === 'dropdown')
        {
            $content .= <<<EOF
//-->
</script>
<div class="addthis_container"><a href="//www.addthis.com/bookmark.php?v='.$atversion.'&amp;username=$pub" class="addthis_button" addthis:url="$link" addthis:title="$title">
EOF;
            $content .= ($addthis_settings['language'] == '' ? '' /* no hardcoded image -- we'll choose the language automatically */ : addthis_get_button_img()) . '</a><script type="text/javascript" src="//s7.addthis.com/js/'.$atversion.'/addthis_widget.js#username='.$pub.'"></script></div>';
        }
        else if ($addthis_settings['menu_type'] === 'toolbox')
        {
            $content .= "\n//-->\n</script>\n";
            $content .= <<<EOF
<div class="addthis_container addthis_toolbox addthis_default_style" addthis:url="$link" addthis:title="$title"><a href="//www.addthis.com/bookmark.php?v='.$atversion.'&amp;username=$pub" class="addthis_button_compact">Share</a><span class="addthis_separator">|</span>
EOF;
            $content .= '<script type="text/javascript" src="//s7.addthis.com/js/'.$atversion.'/addthis_widget.js#username='.$pub.'"></script></div>';
        }
        else
        {
            $link = urlencode($link);
            $title = urlencode($title);
            $content .= <<<EOF
//-->
</script>
<div class="addthis_container"><a href="//www.addthis.com/bookmark.php?v='.$atversion.'&amp;username=$pub" onclick="window.open('//www.addthis.com/bookmark.php?v='.$atversion.'&amp;username=$pub&amp;url=$link&amp;title=$title', 'ext_addthis', 'scrollbars=yes,menubar=no,width=620,height=520,resizable=yes,toolbar=no,location=no,status=no'); return false;" title="Bookmark using any bookmark manager!" target="_blank">
EOF;
            $content .= addthis_get_button_img() . '</a></div>';
        }
        $content .= "\n<!-- AddThis Button END -->";

        return $content;
    }

    /**
    * Generates img tag for share/bookmark button.
    */
    function addthis_get_button_img( $btnStyle = false )
    {
        global $addthis_settings;
        global $addthis_styles;
        global $addThisConfigs;
        $options = $addThisConfigs->getConfigs();

        $language = $options['language'];

        if ($btnStyle == false)
            $btnStyle = $addthis_settings['style'];
        if ($addthis_settings['language'] != 'en')
        {
            // We use a translation of the word 'share' for all verbal buttons
            switch ($btnStyle)
            {
                case 'bookmark':
                case 'addthis':
                case 'bookmark-sm':
                    $btnStyle = 'share';
            }
        }

        if (!isset($addthis_styles[$btnStyle])) $btnStyle = 'share';
        $btnRecord = $addthis_styles[$btnStyle];
        $btnUrl = (strpos(trim($btnRecord['img']), '//') !== 0 ? "//s7.addthis.com/static/btn/v2/" : "") . $btnRecord['img'];

        if (strpos($btnUrl, '%lang%') !== false)
        {
            $btnUrl = str_replace('%lang%', strlen($language) ? $language : 'en', $btnUrl);
        }
        $btnWidth = $btnRecord['w'];
        $btnHeight = $btnRecord['h'];
        return <<<EOF
<img src="$btnUrl" width="$btnWidth" height="$btnHeight" style="border:0" alt="Bookmark and Share"/>
EOF;
    }

    function addthis_options_page_scripts()
    {
        $jsRootUrl = _addthis_js_location_base();
        $imgRootUrl = _addthis_image_location_base();

        if (   addthis_get_wp_version() >= 3.2
            || apply_filters('at_assume_latest', __return_false() )
            || apply_filters('addthis_assume_latest', __return_false() )
        ) {
            $optionsJsUrl = $jsRootUrl . 'options-page.32.js';
        } else {
            $optionsJsUrl = $jsRootUrl . 'options-page.js';
        }

        wp_enqueue_script(
            'addthis_options_page_script',
            $optionsJsUrl,
            array('jquery-ui-tabs', 'thickbox')
        );
        wp_enqueue_script('addthis_core', $jsRootUrl . 'core-1.1.1.js');
        wp_enqueue_script('addthis_lr', $jsRootUrl . 'lr.js');
        wp_enqueue_script('addthis_qtip_script', $jsRootUrl . 'jquery.qtip.min.js');
        wp_enqueue_script('addthis_ui_script', $jsRootUrl . 'jqueryui.sortable.js');
        wp_enqueue_script('addthis_selectbox', $jsRootUrl . 'jquery.selectBoxIt.min.js');
        wp_enqueue_script('', $jsRootUrl . 'jquery.messagebox.js');
        wp_enqueue_script('', $jsRootUrl . 'jquery.atjax.js');
        wp_enqueue_script('addthis_lodash_script', $jsRootUrl . 'lodash-0.10.0.js');
    	wp_enqueue_script('addthis_services_script', $jsRootUrl . 'gtc-sharing-personalize.js');
        wp_enqueue_script('addthis_service_script', $jsRootUrl . 'gtc.cover.js');
        wp_localize_script(
            'addthis_services_script',
            'addthis_params',
            array('img_base' => $imgRootUrl)
        );
        wp_localize_script(
            'addthis_options_page_script',
            'addthis_option_params',
            array('wp_ajax_url'=> admin_url('admin-ajax.php'),
                'addthis_validate_action' => 'validate_addthis_api_credentials',
                'img_base' => $imgRootUrl)
        );
    }

    function addthis_options_page_style()
    {
        $cssRootUrl = _addthis_css_location_base();
        wp_enqueue_style('addthis_options_page_style', $cssRootUrl . 'options-page.css');
        wp_enqueue_style('addthis_general_style', $cssRootUrl . 'style.css');
        wp_enqueue_style('thickbox');
        wp_enqueue_style('addthis_services_style', $cssRootUrl . 'gtc.sharing-personalize.css');
        wp_enqueue_style('addthis_bootstrap_style', $cssRootUrl . 'bootstrap.css');
        wp_enqueue_style('addthis_widget', 'https://ct1.addthis.com/static/r07/widget114.css');
        wp_enqueue_style('addthis_widget_big', 'https://ct1.addthis.com/static/r07/widgetbig056.css');
    }

    function addthis_admin_menu()
    {
        $addthis = add_options_page('AddThis Plugin Options', 'AddThis Sharing Buttons', 'manage_options', basename(__FILE__), 'addthis_plugin_options_php4');
        add_action('admin_print_scripts-' . $addthis, 'addthis_options_page_scripts');
        add_action('admin_print_styles-' . $addthis, 'addthis_options_page_style');
    }

    function addthis_plugin_options_php4() {
        global $current_user;
        $user_id = $current_user->ID;
        global $addThisConfigs;
        $options = $addThisConfigs->getConfigs();

        if (get_user_meta($user_id, 'addthis_nag_updated_options') )
            delete_user_meta($user_id, 'addthis_nag_updated_options', 'true');

        ?>
        <div class="wrap">
            <h2 class='placeholder'>&nbsp;</h2>

            <form  id="addthis_settings" method="post" action="options.php">
                <?php
                    // use the old-school settings style in older versions of wordpress
                    if (   addthis_get_wp_version() >= 2.7
                        || apply_filters('at_assume_latest', __return_false() )
                        || apply_filters('addthis_assume_latest', __return_false() )
                    ) {
                        settings_fields('addthis');
                    } else {
                        wp_nonce_field('update-options');
                    }
                ?>

                <div class="Header">
                    <h1><em>AddThis</em> Sharing Buttons</h1>

                    <span class="preview-save-btns">
                        <?php echo _addthis_settings_buttons(); ?>
                    </span>
                </div>

                <?php
                        if (_addthis_is_csr_form()) {
                            // Get Confirmation form
                            echo addthis_profile_id_csr_confirmation();
                        } else {
                            addthis_wordpress_mode_tabs();
                        }
                ?>

                <div class="Btn-container-end">
                    <?php echo _addthis_settings_buttons(); ?>
                </div>

            </form>
        </div>
    <?php
    }
    add_action('init', 'addthis_init');

    function addthis_wordpress_mode_tabs() {
        global $addthis_styles;
        global $addthis_languages;
        global $addThisConfigs;
        $options = $addThisConfigs->getConfigs();

        $version_notification_content = _addthis_version_notification(
            $options['atversion_update_status'],
            $options['atversion']
        );

        ?>
        <div class="Main-content" id="tabs">
            <ul class="Tabbed-nav">
                <li class="Tabbed-nav-item"><a href="#tabs-1">Sharing Tools</a></li>
                <li class="Tabbed-nav-item"><a href="#tabs-2">Advanced Options</a></li>
            </ul>

            <div id="tabs-1">
                <?php echo $version_notification_content; ?>
                <input
                    type="hidden"
                    value="<?php echo $options['atversion']?>"
                    name="addthis_settings[atversion]"
                    id="addthis_atversion_hidden" />
                <input
                    type="hidden"
                    value="<?php echo $options['atversion_update_status']?>"
                    name="addthis_settings[atversion_update_status]"
                    id="addthis_atversion_update_status" />
                <input
                    type="hidden"
                    value="<?php echo $options['credential_validation_status']?>"
                    name="addthis_settings[credential_validation_status]"
                    id="addthis_credential_validation_status" />

                <div class="Card" id="Card-above-post">
                    <div class="Card-hd">
                        <div class="at-tool-toggle">
                            <input
                                type="checkbox"
                                value="true"
                                name="addthis_settings[addthis_above_enabled]"
                                class="addthis-toggle-cb"
                                id="addthis_above_enabled"
                                style="display:none;" <?php echo ( $options['addthis_above_enabled']  != false ? 'checked="checked"' : ''); ?>/>
                            <div
                                id="addthis_above_enabled_switch"
                                class="switch <?php echo ( $options['addthis_above_enabled']  != false ? 'switchOn' : ''); ?>">
                            </div>
                        </div>
                        <h3 class="Card-hd-title">Sharing Buttons Above Content</h3>
                        <ul class="Tabbed-nav">
                            <li class="Tabbed-nav-item"><a href="#top-1">Style</a></li>
                            <li class="Tabbed-nav-item"><a href="#top-2">Options</a></li>
                        </ul>
                    </div>
                    <div class="addthis_above_enabled_overlay" >
                        <div  class="Card-bd">
                            <div id="top-1">
                                <?php _addthis_choose_icons('above', $options ); ?>
                            </div>
                            <div id="top-2">
                                <?php _addthis_print_template_checkboxes('above') ?>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="Card" id="Card-below-post">
                    <div class="Card-hd">
                        <div class="at-tool-toggle">
                            <input
                                type="checkbox"
                                value="true"
                                name="addthis_settings[addthis_below_enabled]"
                                class="addthis-toggle-cb"
                                id="addthis_below_enabled"
                                style="display:none;" <?php echo ( $options['addthis_below_enabled'] != false ? 'checked="checked"' : ''); ?>/>
                            <div
                                id="addthis_below_enabled_switch"
                                class="switch <?php echo ( $options['addthis_below_enabled'] != false ? 'switchOn' : ''); ?>">
                            </div>
                        </div>
                        <h3 class="Card-hd-title">Sharing Buttons Below Content</h3>
                        <ul class="Tabbed-nav">
                            <li class="Tabbed-nav-item"><a href="#bottom-1">Style</a></li>
                            <li class="Tabbed-nav-item"><a href="#bottom-2">Options</a></li>
                        </ul>
                    </div>
                    <div class="addthis_below_enabled_overlay">
                        <div class="Card-bd">
                            <div id="bottom-1">
                                <?php _addthis_choose_icons('below', $options ); ?>
                            </div>
                            <div id="bottom-2">
                                <?php _addthis_print_template_checkboxes('below') ?>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="Card"  id="Card-side-sharing">
                    <div class="Card-hd">
                        <div class="at-tool-toggle">
                            <input
                                type="checkbox"
                                value="true"
                                name="addthis_settings[addthis_sidebar_enabled]"
                                class="addthis-toggle-cb" id="addthis_sidebar_enabled"
                                style="display:none;" <?php echo ( $options['addthis_sidebar_enabled'] != false ? 'checked="checked"' : ''); ?>/>
                            <div
                                id="addthis_sidebar_enabled_switch"
                                class="switch <?php echo ( $options['addthis_sidebar_enabled']  != false ? 'switchOn' : ''); ?>">
                            </div>
                        </div>
                        <h3 class="Card-hd-title">Sharing Sidebar</h3>
                        <ul class="Tabbed-nav">
                            <li class="Tabbed-nav-item"><a href="#side-1">Style</a></li>
                            <li class="Tabbed-nav-item"><a href="#side-2">Options</a></li>
                        </ul>
                    </div>
                    <div class="addthis_sidebar_enabled_overlay">
                        <div class="Card-bd">
                            <div id="side-1">
                                <p>These buttons will appear on the side of the page, along the edge.</p>
                                <img src="<?php echo _addthis_image_location_base() . 'sidebar_theme_light.png'; ?>" />
                                <ul>
                                    <li>
                                        <strong>Position</strong><br />
                                        <label for="addthis_sidebar_position">Left</label>
                                        <input
                                            type="radio"
                                            id="addthis_sidebar_position"
                                            name="addthis_settings[addthis_sidebar_position]"
                                            value="left" <?php echo ( $options['addthis_sidebar_position'] == 'left' ? 'checked="checked"' : ''); ?>/>
                                        <br />
                                        <label for="addthis_sidebar_position">Right</label>
                                        <input
                                            type="radio"
                                            id="addthis_sidebar_position"
                                            name="addthis_settings[addthis_sidebar_position]"
                                            value="right" <?php echo ( $options['addthis_sidebar_position']  == 'right' ? 'checked="checked"' : ''); ?>/></td>
                                    </li>
                                </ul>
                            </div>
                            <div id="side-2">
                                <?php _addthis_print_template_checkboxes('sidebar') ?>
                                <ul>
                                    <li>
                                        <label for="addthis_sidebar_count">
                                            <strong>Buttons</strong>
                                            <span class="at-wp-tooltip" tooltip="The number of social sharing buttons to show in the side sharing tool.">&quest;</span>
                                        </label>
                                        <select id="addthis_sidebar_count" name="addthis_settings[addthis_sidebar_count]">
                                            <?php
                                                for($i=1;$i<7;$i++){
                                                    echo '<option value="'.$i.'"'.($options['addthis_sidebar_count'] == $i? " selected='selected'":"").'>'.$i.'</option>';
                                                }
                                            ?>
                                        </select>
                                    </li>
                                    <li>
                                        <label for="addthis_sidebar_theme">
                                            <strong>Theme</strong>
                                            <span class="at-wp-tooltip" tooltip="You can select the background color that better matches the look of your site for the expand/minimize arrow on the side sharing tool.">&quest;</span>
                                        </label>
                                        <select id="addthis_sidebar_theme" name="addthis_settings[addthis_sidebar_theme]">
                                            <?php
                                                $themes = array("Transparent","Light","Gray","Dark");
                                                foreach ($themes as $theme) {
                                                    echo '<option value="'.$theme.'"'.($options['addthis_sidebar_theme'] == $theme ? " selected='selected'":"").'>'.$theme.'</option>';
                                                }
                                            ?>
                                        </select>
                                        <br />
                                        <img src="<?php echo _addthis_image_location_base() . 'sidebar_theme_light.png'; ?>" id="sbpreview_Light"/>
                                        <img src="<?php echo _addthis_image_location_base() . 'sidebar_theme_gray.png'; ?>" id="sbpreview_Gray"/>
                                        <img src="<?php echo _addthis_image_location_base() . 'sidebar_theme_dark.png'; ?>" id="sbpreview_Dark"/>
                                        <img src="<?php echo _addthis_image_location_base() . 'sidebar_theme_light.png'; ?>" id="sbpreview_Transparent"/>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="tabs-2">
                <?php echo $version_notification_content?>

                <div class="Card">
                    <div class="Card-hd">
                        <h3 class="Card-hd-title">Tracking</h3>
                    </div>
                    <div class="Card-bd">
                        <ul class="Card-option-list">
                            <li>
                                <ul>
                                    <li>
                                        <input
                                            id="addthis_append_data"
                                            type="checkbox"
                                            name="addthis_settings[addthis_append_data]"
                                            value="true" <?php echo $options['addthis_append_data'] == true ? 'checked="checked"' : ''; ?>/>
                                        <label for="addthis_append_data">
                                            <strong><?php _e("Clickbacks", 'addthis_trans_domain' ); ?></strong> (Recommended)
                                        </label>
                                        <span class="at-wp-tooltip" tooltip="AddThis will use this to track how many people come back to your content via links shared with AddThis buttons. This data will be available to you at addthis.com.">&quest;</span>
                                    </li>
                                    <li>
                                        <input
                                            type="checkbox"
                                            id="addthis_addressbar"
                                            name="addthis_settings[addthis_addressbar]"
                                            value="true" <?php echo ($options['addthis_addressbar']  == true ? 'checked="checked"' : ''); ?>/>
                                        <label for="addthis_addressbar">
                                            <strong><?php _e("Address bar shares", 'addthis_trans_domain' ); ?></strong>
                                        </label>
                                        <span class="at-wp-tooltip" tooltip="AddThis will append a code to your site’s URLs (except for the homepage) to track when a visitor comes to your site from a link someone copied out of their browser's address bar.">&quest;</span>
                                    </li>
                                    <li>
                                        <input
                                            id="addthis_bitly"
                                            type="checkbox"
                                            name="addthis_settings[addthis_bitly]"
                                            value="true" <?php echo ($options['addthis_bitly'] == true ? 'checked="checked"' : ''); ?>/>
                                        <label for="addthis_bitly">
                                            <strong><?php _e("Bitly URL shortening for Twitter", 'addthis_trans_domain' ); ?></strong>
                                        </label>
                                        <span class="at-wp-tooltip" tooltip="Your Bitly login and key will need to be setup with your profile on addthis.com before Bitly will begin working with WordPress.">&quest;</span>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <label for="data_ga_property">
                                    <strong><?php _e("Google Analytics property ID", 'addthis_trans_domain' ); ?></strong>
                                </label>
                                <input
                                    id="data_ga_property"
                                    type="text"
                                    name="addthis_settings[data_ga_property]"
                                    value="<?php echo $options['data_ga_property'] ?>"/>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="Card">
                    <div class="Card-hd">
                        <h3 class="Card-hd-title">Display Options</h3>
                    </div>
                    <div class="Card-bd">
                        <ul class="Card-option-list">
                            <li>
                                <ul>
                                    <li>
                                        <input
                                            id="addthis_asynchronous_loading"
                                            type="checkbox"
                                            name="addthis_settings[addthis_asynchronous_loading]"
                                            value="true" <?php echo ($options['addthis_asynchronous_loading'] == true ? 'checked="checked"' : ''); ?>/>
                                        <label for="addthis_asynchronous_loading">
                                            <strong><?php _e("Asynchronous loading", 'addthis_trans_domain' ); ?></strong> (Recommended)
                                        </label>
                                        <span class="at-wp-tooltip" tooltip="When checked, your site will load before AddThis sharing buttons are added. If unchecked, your site will not load until AddThis buttons (and AddThis JavaScript) have been loaded by your vistors.">&quest;</span>
                                    </li>
                                    <li>
                                        <input
                                            id="addthis_508"
                                            type="checkbox"
                                            name="addthis_settings[addthis_508]" v
                                            alue="true" <?php echo ($options['addthis_508'] == true ? 'checked="checked"' : ''); ?>/>
                                        <label for="addthis_508">
                                            <strong><?php _e("Enhanced accessibility", 'addthis_trans_domain' ); ?></strong>
                                        </label>
                                        <span class="at-wp-tooltip" tooltip="Also known as 508 compliance. If checked, clicking an AddThis sharing button will open a new window to a page that is keyboard navigable for people with disabilities.">&quest;</span>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <label id="addthis_twitter_template">
                                    <strong><?php _e("Twitter via", 'addthis_trans_domain' ); ?></strong>
                                    <span class="at-wp-tooltip" tooltip="When a visitor uses an AddThis button to send a tweet about your page, this will be used within Twitter to identify through whom they found your page. You would usually enter a twitter handle here. For example, Twitter could show a tweet came from jsmith via AddThis.">&quest;</span>
                                </label>
                                <input
                                    id="addthis_twitter_template"
                                    type="text"
                                    name="addthis_settings[addthis_twitter_template]"
                                    value="<?php echo get_first_twitter_username($options['addthis_twitter_template']) ; ?>" />
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="Card">
                    <div class="Card-hd">
                        <h3 class="Card-hd-title">Additional Options</h3>
                    </div>
                    <div class="Card-bd">
                        <ul class="Card-option-list">
                            <li>
                                <p class="Card-description">
                                    For more details on the following options, see <a href="//support.addthis.com/customer/portal/articles/381263-addthis-client-api">our customization documentation</a>.
                                    Important: AddThis optimizes displayed services based on popularity and language, and personalizes the list for
                                    each user. You may decrease sharing by overriding these features.
                                </p>
                            </li>
                            <li>
                                <label for="addthis_language">
                                    <strong><?php _e("Language", 'addthis_trans_domain' ); ?></strong>
                                </label>
                                <select id="addthis_language" name="addthis_settings[addthis_language]">
                                    <?php
                                        $curlng = $options['addthis_language'];
                                        foreach ($addthis_languages as $lng=>$name)
                                        {
                                            echo "<option value=\"$lng\"". ($lng == $curlng ? " selected='selected'":""). ">$name</option>";
                                        }
                                    ?>
                                </select>
                            </li>
                            <li>
                                <h4><strong>Global Advanced API Configuration</strong></h4>
                                <ul>
                                    <li>
                                        <label for="addthis_config_json">
                                            <?php _e("addthis_config values (json format)", 'addthis_trans_domain' ); ?>
                                        </label>
                                        <br/>
                                        <small>ex:- <i>{ "services_exclude": "print" }</i></small>
                                        <div><p>For more information, please see the AddThis documentation on <a href="http://support.addthis.com/customer/portal/articles/1337994-the-addthis_config-variable">the addthis_config variable</a>.</p></div>
                                        <textarea
                                            id="addthis_config_json"
                                            rows='3'
                                            cols='60'
                                            type="text"
                                            name="addthis_settings[addthis_config_json]"
                                            id="addthis-config-json"/><?php echo $options['addthis_config_json']; ?></textarea>
                                        <span id="config-error" class="hidden error_text">Invalid JSON format</span>
                                    </li>
                                    <li>
                                        <label for="addthis_share_json">
                                            <?php _e("addthis_share values (json format)", 'addthis_trans_domain' ); ?>
                                        </label>
                                        <br/>
                                        <small>ex:- <i>{ "url" : "http://www.yourdomain.com", "title" : "Custom Title" }</i></small>
                                        <div><p>For more information, please see the AddThis documentation on <a href="http://support.addthis.com/customer/portal/articles/1337996-the-addthis_share-variable">the addthis_share variable</a>.</p></div>
                                        <textarea
                                            id="addthis_share_json"
                                            rows='3'
                                            cols='60'
                                            type="text"
                                            name="addthis_settings[addthis_share_json]"
                                            id="addthis-share-json"/><?php echo $options['addthis_share_json']; ?></textarea>
                                        <span id="share-error" class="hidden error_text">Invalid JSON format</span>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>

                <?php echo _addthis_profile_id_card($options['credential_validation_status']); ?>

                <?php echo _addthis_mode_card(); ?>
            </div>
        </div>
        <?php
    }

    /* 2.9 compatability functions
     */

    if (! function_exists('get_user_meta'))
    {
        function get_user_meta($userid, $metakey, $ignored='')
        {
            $userdata = get_userdata($userid);
            if (isset($userdata->{$metakey}) )
                return $userdata->{$metakey};
            else
                return false;
        }

    }
    if (! function_exists('delete_user_meta'))
    {
        function delete_user_meta($userid, $metakey, $ignored = '')
        {
            return delete_usermeta($userid, $metakey);
        }
    }

    if (! function_exists('add_user_meta'))
    {
        function add_user_meta($userid, $metakey, $metavalue)
        {
            return update_usermeta($userid, $metakey, $metavalue);
        }
    }
    if (! function_exists('get_home_url'))
    {
        function get_home_url()
        {
            return get_option( 'home' );
        }
    }

    if (! function_exists('__return_false'))
    {
        function __return_false()
        {
            return false;
        }
    }

    if (! function_exists('__return_true'))
    {
        function __return_true()
        {
            return true;
        }
    }

    if (! function_exists('esc_textarea'))
    {
        function esc_textarea($text)
        {
             $safe_text = htmlspecialchars( $text, ENT_QUOTES );
             return $safe_text;
        }

    }

    /**
     * Parse for the first twitter username in the given string
     * @param String $raw_twitter_username Raw string containing twitter usernames
     * @return String twitter username
     */
    if (! function_exists('get_first_twitter_username'))
    {
        function get_first_twitter_username($raw_twitter_username)
        {
            $twitter_username = '';
            preg_match_all('/@(\w+)\b/i', $raw_twitter_username, $twitter_via_matches);
            if (count($twitter_via_matches[1]) == 0) {
                //To handle strings without @
                preg_match_all('/(\w+)\b/i', $raw_twitter_username, $twitter_via_refined_matches);
                if (count($twitter_via_refined_matches[1]) > 0) {
                   $twitter_username = $twitter_via_refined_matches[1][0];
                }
            } else {
                $twitter_username = $twitter_via_matches[1][0];
            }
            return $twitter_username;
        }
    }

    // check for pro user
    function at_share_is_pro_user() {
        global $addThisConfigs;
        $isPro = false;
        $options = $addThisConfigs->getConfigs();

        if (!empty($options['addthis_profile'])) {
            $request = wp_remote_get( "http://q.addthis.com/feeds/1.0/config.json?pubid=" . $options['addthis_profile'] );
            $server_output = wp_remote_retrieve_body( $request );
            $array = json_decode($server_output);
            // check for pro user
            if (is_array($array) && array_key_exists('_default',$array)) {
                $isPro = true;
            } else {
                $isPro = false;
            }
        }
        return $isPro;
    }

    require_once('addthis_post_metabox.php');

    function addthis_deactivation_hook()
    {
        if (get_option('addthis_run_once')) {
            delete_option('addthis_run_once');
        }
    }

    // Deactivation
    register_deactivation_hook(__FILE__, 'addthis_deactivation_hook');
}

function _addthis_profile_setup_url() {
    $pubName = get_bloginfo('name');

    if (!preg_match('/^[A-Za-z0-9 _\-\(\)]*$/', $pubName)) {
        // if title not match, get domain
        $url     = get_option('siteurl');
        $urlobj  = parse_url($url);
        $domain  = $urlobj['host'];

        if (preg_match(
            '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i',
            $domain, $regs
        )) {
            $domainArray = explode(".", $regs['domain']);
            $pubName     = $domainArray[0];
        } else {
            $pubName = '';
        }
        $pubName  = str_replace('.', '', $pubName);
    }

    if (!preg_match('/^[A-Za-z0-9 _\-\(\)]*$/', $pubName) || $pubName == '') {
        // if domain not match, get loggedin username
        $currentUser = wp_get_current_user();
        $pubName = $currentUser->user_login;
    }

    $profileSetupUrl = 'https://www.addthis.com/settings/plugin-pubs'
        . '?cms=wp&pubname='
        . urlencode($pubName)
        . '&wp_redirect='
        . str_replace(
            '.',
            '%2E',
            urlencode(admin_url("options-general.php?page=addthis_social_widget"))
        );

    return $profileSetupUrl;
}

function _addthis_analytics_url() {
    global $addThisConfigs;
    $addthis_options = $addThisConfigs->getConfigs();
    $analyticsUrl = 'https://www.addthis.com/dashboard#analytics/' . $addthis_options['addthis_profile'];
    return $analyticsUrl;
}

function _addthis_tools_url() {
    global $addThisConfigs;
    $addthis_options = $addThisConfigs->getConfigs();
    $toolsUrl = 'https://www.addthis.com/settings/plugin-pubs?cms=wp&pubid=' . $addthis_options['addthis_profile'];
    return $toolsUrl;
}

function _addthis_profile_id_card($credential_validation_status = false) {
    global $addThisConfigs;
    $addthis_options = $addThisConfigs->getConfigs();

    $fieldId = 'addthis_profile';
    $noPubIdDescription = 'To begin tracking analytics on social shares from your site, use the button below to set up an AddThis account at addthis.com and create a profile for your site. This process will require an email address.';
    $noPubIdButtonText = "AddThis profile setup";
    $pubIdDescription = 'To see analytics on social shares from your site, use the button below. It will take you to Analytics on addthis.com.';
    $pubIdButtonText = "Your analytics on addthis.com";

    $securitySnippet = '';
    if ($credential_validation_status == 1) {
        $securitySnippet = '<span class="success_text">&#10004; Valid AddThis Profile ID</span>';
    }

    // because i can't figure out how to bring these two in line... :-(
    if ($addthis_options['addthis_plugin_controls'] != "AddThis") {
        $fieldName = 'addthis_settings[addthis_profile]';
        $security = '';
    } else {
        $fieldName = 'pubid';
        $security = wp_nonce_field('update_' . $fieldName, $fieldName . '_nonce');
    }

    if (empty($addthis_options['addthis_profile'])) {
        $description = $noPubIdDescription;
        $buttonUrl = _addthis_profile_setup_url();
        $buttonText = $noPubIdButtonText;
        $alternatePath = '<p>Alternately, you can input your profile id manually below.</p>';
        $target = '';

    } else {
        $description = $pubIdDescription;
        $buttonUrl = _addthis_analytics_url();
        $buttonText = $pubIdButtonText;
        $alternatePath = '';
        $target = 'target="_blank"';
    }

    $html = '
        <div class="Card">
            <div class="Card-hd">
                <h3 class="Card-hd-title">AddThis Analytics</h3>
            </div>
            <div class="Card-bd">
                <div class="addthis_description">
                    ' . $description . '
                </div>
                <div class="Btn-container-card">
                    <a
                        class="Btn Btn-blue"
                        ' . $target . '
                        href="' . $buttonUrl . '"> ' . $buttonText . ' &#8594;
                    </a>
                </div>
                ' . $alternatePath . '
                <label for="' . $fieldId . '">
                    <strong>AddThis Profile ID</strong>
                </label>
                <ul class="Profile-widget">
                    <li>
                        <input
                            id="' . $fieldId . '"
                            type="text"
                            name="' . $fieldName . '"
                            value="' . $addthis_options['addthis_profile'] . '"
                            autofill="off"
                            autocomplete="off"
                        />
                        '
                        . $security
                        . '
                    </li>
                    <li>
                        ' . $securitySnippet . '
                    </li>
                </ul>
            </div>
        </div>
    ';

    return $html;
}

function _addthis_mode_card() {
    global $addThisConfigs;
    $addthis_options = $addThisConfigs->getConfigs();

    $wordPressChecked = '';
    $addThisChecked = '';
    $checked = 'checked="checked"';
    if ($addthis_options['addthis_plugin_controls'] != 'AddThis') {
        $wordPressChecked = $checked;
        $fieldName = 'addthis_settings[addthis_plugin_controls]';
        $fieldId = 'addthis_plugin_controls';
        $tbody = '<tbody></tbody>';
    } else {
        $addThisChecked = $checked;
        $fieldName = 'addthis_plugin_controls';
        $fieldId = 'downgrade-plugin';
        $tbody = '
            <tbody>
                <tr>
                    <th role="rowheader" scope="row">Sharing buttons above content</td>
                    <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                    <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th role="rowheader" scope="row">Sharing buttons below content</td>
                     <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                     <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th role="rowheader" scope="row">Sharing sidebar</td>
                     <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                     <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th role="rowheader" scope="row">Mobile toolbar</td>
                    <td>
                        <span class="at-hidden-cell-content">Does not have this feature</span>
                    </td>
                    <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th role="rowheader" scope="row">Newsletter sharing buttons</td>
                    <td>
                        <span class="at-hidden-cell-content">Does not have this feature</span>
                    </td>
                    <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th role="rowheader" scope="row">Follow header</td>
                    <td>
                        <span class="at-hidden-cell-content">Does not have this feature</span>
                    </td>
                    <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th role="rowheader" scope="row">Horizontal follow buttons</td>
                    <td><span class="at-hidden-cell-content">Does not have this feature</span></td>
                    <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th role="rowheader" scope="row">Vertical follow buttons</td>
                    <td><span class="at-hidden-cell-content">Does not have this feature</span></td>
                    <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th role="rowheader" scope="row">What\'s next</td>
                    <td><span class="at-hidden-cell-content">Does not have this feature</span></td>
                    <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th role="rowheader" scope="row">Recommended content footer</td>
                    <td><span class="at-hidden-cell-content">Does not have this feature</span></td>
                    <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th role="rowheader" scope="row">Horizontal recommended content</td>
                    <td><span class="at-hidden-cell-content">Does not have this feature</span></td>
                    <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th role="rowheader" scope="row">Vertical recommended content</td>
                    <td><span class="at-hidden-cell-content">Does not have this feature</span></td>
                    <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th role="rowheader" scope="row">Access to additional Pro tools for upgraded accounts</td>
                    <td><span class="at-hidden-cell-content">Does not have this feature</span></td>
                    <td>
                        <span class="at-icon-check">
                            <span class="at-icon-fallback-text">Has this Feature</span>
                        </span>
                    </td>
                </tr>
            </tbody>
        ';
    }

    $debugHtml = '';
    if (isset($_GET['debug']) || !empty($addthis_options["addthis_environment"])) {
        $debugHtml = '
            <label for="addthis_environment">
                <strong>Environment (test/dev/local)</strong>
            </label>
            <input
                type="textbox"
                id="addthis_environment"
                name="addthis_settings[addthis_environment]"
                value="' . $addthis_options["addthis_environment"] . '"
            />
        ';
    }

    $html =  '
        <div class="Card">
            <div class="Card-hd">
                <h3 class="Card-hd-title">I want to control my plugin from...</h3>
            </div>
            <div class="Card-bd">
                <p class="Card-description">Regardless of your choice, analytics for shares of your site will be available to you on the AddThis website after you set up your AddThis profile ID.</p>
                <table class="at-comparison-table">
                    <thead>
                        <th role="columnheader" scope="col">
                            <span class="at-hidden-cell-content">Feature List Column</span>
                        </th>
                        <th role="columnheader" scope="col">
                            <input type="radio" id="wordpress-option" name="' . $fieldName . '"  value="WordPress" ' . $wordPressChecked . ' />
                            <label for="wordpress-option">
                                <strong>WordPress</strong>
                            </label>
                        </th>
                        <th role="columnheader" scope="col">
                            <input type="radio" id="addthis-option" name="' . $fieldName . '" value="AddThis"' . $addThisChecked . '/>
                            <label for="addthis-option">
                                <strong>AddThis.com Dashboard</strong>
                            </label>
                        </th>
                    </thead>
                    ' . $tbody . '
                </table>
                <p>To see your choice reflected on these screens, save your changes.</p>
                ' . $debugHtml . '
            </div>
        </div>';
    return $html;
}

function _addthis_is_csr_form() {
    global $addThisConfigs;
    $options = $addThisConfigs->getConfigs();

    if (   isset($_GET['complete'], $_GET['pubid'])
        && $_GET['complete'] == 'true'
        && $_GET['pubid'] != $options['addthis_profile']
    ) {
        return true;
    }

    return false;
}

function _addthis_settings_buttons($includePreview = true) {
    $html = '';
    if (_addthis_is_csr_form()) {
        return $html;
    }

    if($includePreview) {
        $stylesheet = get_option('stylesheet');
        $template = get_option('template');
        $previewLink = esc_url(get_option('home') . '/');
        if (is_ssl()) {
            $previewLink = str_replace('http://', 'https://', $previewLink);
        }
        $queryArgs = array(
            'preview' => 1,
            'template' => $template,
            'stylesheet' => $stylesheet,
            'preview_iframe' => true,
            'TB_iframe' => 'true'
        );
        $previewLink = htmlspecialchars(add_query_arg($queryArgs, $previewLink));

        $previewHtml = '
        <a
            href="'.$previewLink.'"
            class="Btn thickbox thickbox-preview" >
                Preview
        </a>';
        $html .= $previewHtml;
    }

    $saveHtml = '
    <input
        type="submit"
        name="submit"
        value="Save Changes"
        class="Btn Btn-blue addthis-submit-button"
    />';

    $html .= $saveHtml;

    return $html;
}

/**
 * Get HTML for new users with confirmation
 *
 * @return string
 */
function addthis_profile_id_csr_confirmation($fieldName = 'addthis_settings[addthis_profile]')
{
    if (isset($_GET['advanced_settings'])) {
        $html  = '
            <div>
                Here you can manually set your AddThis Profile ID - you can get this from your
                <a target="_blank" href="https://www.addthis.com/settings/publisher">
                    Profile Settings
                </a>
            </div>';
    } else {
        $html  = '
            <h2>You\'re almost done!</h2>
            <div>
                It\'s time to connect your AddThis account with Wordpress.
            </div>';
    }
    $html .= '
        <form id="addthis-form" method="post" action="'.admin_url("options-general.php?page=addthis_social_widget").'">
            <div class="addthis_pub_id">
                <div class="icons wp_div">
                <img src="'.plugins_url('images/wordpress.png', __FILE__).'">
                <span>Your WordPress Site:</span>
                <input
                    type="text"
                    value="' . get_bloginfo('name') . '"
                    name="pub_id"
                    readonly=true
                    onfocus="this.blur()"/>
            </div>
            <div class="icons arrow_div">
                <img src="'.plugins_url('images/arrow_right.png', __FILE__).'">
                <img src="'.plugins_url('images/arrow_left.png', __FILE__).'">
            </div>
            <div class="icons addthis_div">
                <img src="'.plugins_url('images/addthis.png', __FILE__).'">
                <span>AddThis Profile ID:</span>';

    if (isset($_GET['pubid'])) {
        $pubId = $_GET['pubid'];
    } else {
        $pubId = $this->_pubid;
    }

    $html .=  '
                <input
                    type="text"
                    value="'.$pubId.'"
                    name="'.$fieldName.'"
                    id="addthis_profile" >
                <input
                    type="hidden"
                    value="true"
                    name="addthis_settings[addthis_csr_confirmation]"
            </div>
        </div>';

    $submitButtonValue = "Confirm and Save Changes";

    $html .= '
            <input
                type="submit"
                value="'.$submitButtonValue.'"
                name="submit"
                class="addthis_confirm_button">
            <button
                class="addthis_cancel_button"
                type="button"
                onclick="window.location=\''.admin_url("options-general.php?page=addthis_social_widget").'\';return false;">
                Cancel
            </button>
                ' . wp_nonce_field( 'update_pubid', 'pubid_nonce' ) . '
        </form>';

        return $html;
    }