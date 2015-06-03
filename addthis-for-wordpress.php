<?php
/**
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

$pathParts = pathinfo(__FILE__);

$path = $pathParts['dirname'];

if (!defined('ADDTHIS_ATVERSION')) {
    define('ADDTHIS_ATVERSION', '300');
}

define('ADDTHIS_CSS_PATH', 'css/style.css');
define('ADDTHIS_JS_PATH', 'js/addthis-for-wordpress.js');
define('ADDTHIS_SETTINGS_PAGE_ID', 'addthis_social_widget');
define('ADDTHIS_PLUGIN_FILE', $path.'/addthis_social_widget.php');
define('ADDTHIS_PUBNAME_LIMIT', 255);

require_once('addthis_settings_functions.php');

class Addthis_Wordpress
{
    const ADDTHIS_PROFILE_SETTINGS_PAGE = 'https://www.addthis.com/settings/publisher';
    const ADDTHIS_SITE_URL = 'https://www.addthis.com/settings/plugin-pubs';
    const ADDTHIS_SITE_URL_WITH_PUB = 'https://www.addthis.com/dashboard#gallery';
    const ADDTHIS_SITE_URL_ANALYTICS = 'https://www.addthis.com/dashboard#analytics';
    const ADDTHIS_REFERER  = 'www.addthis.com';

    /** PHP $_GET Variables * */
    private $_getVariables;

    /** PHP $_POST Variables * */
    private $_postVariables;

    /** check upgrade or fresh installation **/
    private $_upgrade;

    /** Addthis Profile id **/
    private $_pubid;

    /** Addthis Settings **/
    private $_options;

    private $addThisConfigs;

    /**
     * Initializes the plugin.
     *
     * @param boolean $upgrade check upgrade or fresh installation
     *
     * @return null
     * */
    public function __construct($upgrade, $addThisConfigs)
    {
        $this->addThisConfigs = $addThisConfigs;
        // Save async load settings via ajax request
        add_action( 'wp_ajax_at_async_loading', array($this, 'addthisAsyncLoading'));
        $this->_upgrade = $upgrade;
        $this->_getVariables = $_GET;
        $this->_postVariables = $_POST;
        $this->_options = $this->addThisConfigs->getConfigs();

        $this->_pubid = null;
        if (   isset($this->_options)
            && isset($this->_options['addthis_profile'])
            && !empty($this->_options['addthis_profile'])
        ) {
            $this->_pubid = $this->_options['addthis_profile'];
        }

        include_once 'addthis-toolbox.php';
        new Addthis_ToolBox;

        add_action('admin_menu', array($this, 'addthisWordpressMenu'));

        // Deactivation
        register_deactivation_hook(
            ADDTHIS_PLUGIN_FILE,
            array($this, 'pluginDeactivation')
        );

        // Settings link in plugins page
        $plugin = 'addthis/addthis_social_widget.php';
        add_filter(
            "plugin_action_links_$plugin",
            array($this, 'addSettingsLink')
        );
    }

    /*
     * Function to add settings link in plugins page
     *
     * @return null
     */
    public function addSettingsLink($links)
    {
        $settingsLink = '<a href="'.self::getSettingsPageUrl().'">Settings</a>';
        array_push($links, $settingsLink);
        return $links;
    }

    /**
     * Functions to execute on plugin deactivation
     *
     * @return null
     */
    public function pluginDeactivation()
    {
        if (get_option('addthis_run_once')) {
            delete_option('addthis_run_once');
        }
    }

    /**
     * Adds sub menu page to the WP settings menu
     *
     * @return null
     */
    public function addthisWordpressMenu()
    {
        add_options_page(
            'AddThis Sharing Buttons',
            'AddThis Sharing Buttons',
            'manage_options',
            ADDTHIS_SETTINGS_PAGE_ID,
            array($this, 'addthisWordpressOptions')
        );
    }

    /**
     * Manages the WP settings page
     *
     * @return null
     */
    public function addthisWordpressOptions()
    {
        if (!current_user_can('manage_options')) {
            wp_die(
                __('You do not have sufficient permissions to access this page.')
            );
        }

        $updateResult = null;

        if ($this->_checkAddPubid()) {
            $updateResult = $this->updateSettings($this->_postVariables);
        }
        wp_enqueue_script(
            'addThisScript',
            plugins_url(ADDTHIS_JS_PATH, __FILE__)
        );
        wp_enqueue_script('atTabs',plugins_url('js/options-page.32.js', __FILE__));
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_style(
            'addThisStylesheet',
            plugins_url(ADDTHIS_CSS_PATH, __FILE__)
        );
        wp_enqueue_style('attabStyles',plugins_url('css/options-page.css', __FILE__));
        echo $this->_getHTML($updateResult);
    }

    /**
     *  Updates addthis profile id
     *
     *  @param string $pubId Addthis public id
     *
     *  @return string
     */
    public function updateSettings($settings)
    {
        if(isset($settings['pubid'])){
            $this->_options['addthis_profile'] = sanitize_key($settings['pubid']);
        }

        if(isset($settings['addthis_settings']['addthis_environment'])){
            $this->_options['addthis_environment'] = sanitize_key($settings['addthis_settings']['addthis_environment']);
        }

        if(    isset($settings['addthis_plugin_controls'])
            && $settings['addthis_plugin_controls'] == "WordPress"
        ) {
            $this->_options['addthis_plugin_controls'] = $settings['addthis_plugin_controls'];
        }

        if(isset($settings['async_loading'])){
            $this->_options['addthis_asynchronous_loading'] = intval($settings['async_loading']);
        }
        $this->_options = $this->addThisConfigs->saveConfigs($this->_options);

        $this->_pubid = $this->_options['addthis_profile'];

        return "<div class='addthis_updated wrap' style='margin-top:50px;width:95%'>".
                    "AddThis Profile Settings updated successfully!!!".
               "</div>";
    }

    /**
     *  Get addthis profile id
     *
     *  @return string
     */
    public static function getPubid()
    {
        global $addThisConfigs;
        $settings = $addThisConfigs->getConfigs();
        if (!empty($settings['addthis_profile'])) {
            return $settings['addthis_profile'];
        } else {
            return null;
        }
    }

    /**
     *  Get referer url
     *
     *  @return string
     */
    private function _getReferelUrl()
    {
        $referer = '';
        if (isset($_SERVER['HTTP_REFERER'])) {
            $parse   = parse_url($_SERVER['HTTP_REFERER']);
            $referer = $parse['host'];
        }

//        return $referer;
        return self::ADDTHIS_REFERER;
    }

    /**
     *  Check if there is an addthis profile id return from addthis.com
     *
     *  @return boolean
     */
    private function _checkPubidFromAddThis()
    {
        $referer = $this->_getReferelUrl();
        $successReturn = isset ($this->_getVariables['pubid']) &&
                         isset ($this->_getVariables['complete']) &&
                         $this->_getVariables['complete'] == 'true' &&
                         $referer == self::ADDTHIS_REFERER;

        return $successReturn;
    }

    /**
     *  Check if there is request to add addthis profile id
     *
     *  @return boolean
     */
    private function _checkAddPubid()
    {
        $successReturn = isset ($this->_postVariables['pubid'])
                         && isset ($this->_postVariables['submit'])
                         && isset( $this->_postVariables['pubid_nonce'] )
                         && wp_verify_nonce( $this->_postVariables['pubid_nonce'], 'update_pubid' );

        return $successReturn;
    }

    /**
     *  Check if there is request to update async loading
     *
     *  @return boolean
     */
    private function _checkAsyncLoading()
    {
        $successReturn = isset ($this->_postVariables['async_loading']);

        return $successReturn;
    }

    public function addthisAsyncLoading()
    {
        if (current_user_can( 'manage_options' ) && $this->_checkAsyncLoading()) {
            $updateResult = $this->updateSettings($this->_postVariables);
        }
        die; //exit from the ajax request
    }

    /**
     *  Check pubid from addthis failure
     *
     *  @return boolean
     */
    private function _checkAddPubidFailure()
    {
        $referer = $this->_getReferelUrl();
        $successReturn = (isset ($this->_getVariables['complete']) &&
                         $this->_getVariables['complete'] != 'true') ||
                         (isset ($this->_getVariables['complete']) &&
                         $referer !== self::ADDTHIS_REFERER);

        return $successReturn;
    }

    /**
     * Get the HTML for addthis settings page
     *
     * @param string $updateResult Updated message
     *
     * @return string
     */
    private function _getHTML($updateResult)
    {
        $html = '
            <div class="wrap">
                <form
                    id="addthis-form"
                    method="post"
                    action="'.self::getSettingsPageUrl().'"
                >
                    <div class="Header">
                        <h1><em>AddThis</em> Sharing Buttons</h1>';
        if (!_addthis_is_csr_form()) {
            $html .= '<span class="preview-save-btns">' . _addthis_settings_buttons(false) . '</span>';
        }

        $html .= '</div>';

        if ($this->_upgrade && !$this->_pubid) {
            $html .= $this->_getupdateSuccessMessage();
        }

        if ($this->_checkAddPubidFailure()) {
            $html .= $this->_getPubIdFromAddthisFailureMessage();
        }

        if ($updateResult) {
            $html .= $updateResult;
        }

        if ($this->_checkPubidFromAddThis()
            || (isset($this->_getVariables['advanced_settings'])
            && ($this->_getVariables['advanced_settings'] == 'true'))
        ) {
            // Get Confirmation form
            $html .= addthis_profile_id_csr_confirmation('pubid');
        } else {
            $html .= $this->_getAddThisLinkButton();
        }

        if (!_addthis_is_csr_form()) {
            $html .= '
                    <div class="Btn-container-end">
                        ' . _addthis_settings_buttons(false) . '
                    </div>
                </form>';
        }

        return $html;
    }

    /**
     * Get pubid failure message
     *
     * @return <string>
     */
    private static function _getPubIdFromAddthisFailureMessage()
    {
        return "<div class='addthis_error wrap'>".
                        "Failed to add AddThis Profile ID".
                   "</div>";
    }

    /**
     * Get Update Success Message when updating from old plugin
     *
     * @return null
     */
    private function _getupdateSuccessMessage()
    {
        return "<div class='addthis_updated wrap'>".
                    "Click on the link below to finish setting up your AddThis tools.".
               "</div>";
    }

    /**
     * Get Link to addthis site
     *
     * @return string
     */
    private function _getAddThisLinkButton()
    {
        $noPubIdDescription = 'To configure sharing tools for your site, use the button below to set up an AddThis account at addthis.com, create a profile for your site and begin adding sharing tools. This process will require an email address.';
        $noPubIdButtonText = "AddThis profile setup";
        $noPubIdCardTitle = 'You\'re almost done!';

        $pubIdDescription = 'To configure sharing tools for your site, use the button below. It will take you to Tools on addthis.com';
        $pubIdCardTitle = 'Setup AddThis Tools';
        $pubIdButtonText = "Configure AddThis Tools";

        if (empty($this->_pubid)) {
            // if they don't have a profile yet, default to setup
            $tabOrder = array(
                'tabs-1' => 'Setup',
                'tabs-2' => 'Advanced Options',
            );

            $sharingToolsCardTitle = $noPubIdCardTitle;
            $sharingToolsDescription = $noPubIdDescription;
            $sharingToolsButtonUrl = _addthis_profile_setup_url();
            $sharingToolsButtonText = $noPubIdButtonText;
            $target = '';
        } else {
            // else default to profile
            $tabOrder = array(
                'tabs-1' => 'Sharing Tools',
                'tabs-2' => 'Advanced Options',
            );

            $sharingToolsCardTitle = $pubIdCardTitle;
            $sharingToolsDescription = $pubIdDescription;
            $sharingToolsButtonUrl = _addthis_tools_url();
            $sharingToolsButtonText = $pubIdButtonText;
            $target = 'target="_blank"';
        }

        $tabsHtml = '';
        foreach ($tabOrder as $href => $title) {
            $tabsHtml .= '<li class="Tabbed-nav-item"><a href="#' . $href . '">' . $title . '</a></li>';
        }

        $html = '
            <div class="Main-content" id="tabs">
                <ul class="Tabbed-nav">
                    ' . $tabsHtml . '
                </ul>
                <div id="tabs-1">
                    <div class="Card" id="Card-side-sharing" style="height:320px">
                        <div>
                            <h3 class="Card-hd-title">
                                ' . $sharingToolsCardTitle . '
                            </h3>
                        </div>
                        <div class="addthis_seperator">&nbsp;</div>
                        <div class="Card-bd">
                            <div class="addthis_description">
                                Beautiful simple website tools designed to help you get likes, get shares, get follows and get discovered.
                            </div>
                            <p>' . $sharingToolsDescription . '</p>
                            <a
                                class="Btn Btn-blue"
                                ' . $target . '
                                href="' . $sharingToolsButtonUrl . '">' . $sharingToolsButtonText . ' &#8594;
                            </a>
                            <p class="addthis_support">
                                If you don\'t see your tools after configuring them in the dashboard, please contact
                                <a href="http://support.addthis.com/">AddThis Support</a>
                            </p>
                        </div>
                    </div>
                </div>
                <div id="tabs-2">
                    ' . _addthis_profile_id_card() . '
                    ' . _addthis_mode_card() . '
                </div>
            </div>';

        return $html;
    }

    /**
     * Get the plugin's settings page url
     *
     * @return string
     */
    public static function getSettingsPageUrl()
    {
        return admin_url("options-general.php?page=" . ADDTHIS_SETTINGS_PAGE_ID);
    }

    /**
     * Get the wp domain
     *
     * @return string
     */
    public static function getDomain()
    {
        $url     = get_option('siteurl');
        $urlobj  = parse_url($url);
        $domain  = $urlobj['host'];
        return $domain;
    }

}

// Setup our shared resources early
// addthis_addjs.php is a standard class shared by the various AddThis plugins
// to make it easy for us to include our bootstrapping JavaScript only once.
// Priority should be lowest for Share plugin.
add_action('init', 'Addthis_Wordpress_early', 0);

/**
 * Include addthis js widget
 *
 * @global AddThis_addjs $addthis_addjs
 * @return null
 */
function Addthis_Wordpress_early()
{
    global $addthis_addjs;
    global $addThisConfigs;

    if (!isset($addthis_addjs)) {
        include 'includes/addthis_addjs_new.php';
        $addthis_addjs = new AddThis_addjs($addThisConfigs);
    }
}
