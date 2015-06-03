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

require_once('AddThisCmsConnectorInterface.php');

if (!class_exists('AddthisWordpressConnector')) {
    Class AddthisWordpressConnector implements AddThisCmsConnectorInterface {

        static $settingsVariableName = 'addthis_settings';
        static $pluginVersion = '5.0';
        protected $configs = null;

        protected $defaultConfigs = array(
            'addthis_plugin_controls'      => 'WordPress',
        );

        public $simpleConfigUpgradeMappings = array(
            array(
                'current' => array('addthis_above_showon_home', 'addthis_below_showon_home'),
                'deprecated' => array('addthis_showonhome'),
            ),
            array(
                'current' => array('addthis_above_showon_pages', 'addthis_below_showon_pages'),
                'deprecated' => array('addthis_showonpages'),
            ),
            array(
                'current' => array('addthis_above_showon_categories', 'addthis_below_showon_categories'),
                'deprecated' => array('addthis_showoncats'),
            ),
            array(
                'current' => array('addthis_above_showon_archives', 'addthis_below_showon_archives'),
                'deprecated' => array('addthis_showonarchives'),
            ),
            array(
                'current' => array('addthis_above_showon_posts', 'addthis_below_showon_posts'),
                'deprecated' => array('addthis_showonposts'),
            ),
            array(
                'current' => array('addthis_addressbar'),
                'deprecated' => array('addthis_copytracking2', 'addthis_copytracking1'),
            ),
            array(
                'current' => array('addthis_profile'),
                'deprecated' => array('profile', 'pubid'),
            ),
        );

        static function getPluginVersion() {
            return self::$pluginVersion;
        }

        public function getDefaultConfigs() {
            return $this->defaultConfigs;
        }

        public function getConfigs($cache = false) {
            if ($this->isPreviewMode()) {
                $this->configs = get_transient(self::$settingsVariableName);
            } elseif (!$cache || is_null($this->configs)) {
                $this->configs = get_option(self::$settingsVariableName);
            }

            if (!is_array($this->configs)) {
                $this->configs = null;
            }

            return $this->configs;
        }

        public function saveConfigs($configs = null) {
            if (is_null($configs)) {
                $configs = $this->configs;
            }

            if (!is_null($configs)) {
                update_option(self::$settingsVariableName, $configs);
                $this->configs = $this->getConfigs();
            }

            return $this->configs;
        }

        /**
         * checks if you're in preview mode
         * @return boolean true if in preview, false otherwise
         */
        public function isPreviewMode() {
            if (isset($_GET['preview']) && $_GET['preview'] == 1) {
                return true;
            }

            return false;
        }

        public function getSharingButtonLocations() {
            $types = array(
                'above',
                'below',
                'sidebar',
            );
            return $types;
        }

        /**
         * Returns an array of template options generlized without location info
         * @return array[] an array of associative arrays
         */

        public function getContentTypes() {
            $options = array(
                array(
                    'fieldName'    => 'home',
                    'displayName'  => 'Homepage',
                    'explanation'  => 'Includes both the blog post index page (home.php or index.php) and any static page set to be your front page under Settings->Reading->Front page displays.',
                ),
                array(
                    'fieldName'    => 'posts',
                    'displayName'  => 'Posts',
                    'explanation'  => 'Also known as articles or blog posts.',
                ),
                array(
                    'fieldName'    => 'pages',
                    'displayName'  => 'Pages',
                    'explanation'  => 'Often used to present static information about yourself or your site where the date published is less revelant than with posts.',
                ),
                array(
                    'fieldName'    => 'archives',
                    'displayName'  => 'Archives',
                    'explanation'  => 'A Category, Tag, Author or Date based view.',
                ),
                array(
                    'fieldName'    => 'categories',
                    'displayName'  => 'Categories',
                    'explanation'  => 'A view that displays costs filled under a specific category.',
                ),
                array(
                    'fieldName'    => 'excerpts',
                    'displayName'  => 'Excerpts',
                    'explanation'  => 'A condensed description of your post or page. These are often displayed in search results, RSS feeds, and sometimes on Archive or Category views. Important: Excerpts will only work some of the time with some themes, depending on how that theme retrieves your content.',
                ),
            );
            return $options;
        }

        public function isUpgrade() {
            $this->getConfigs(true);
            if (   !isset($this->configs['addthis_plugin_version'])
                || $this->configs['addthis_plugin_version'] != self::$pluginVersion
            ) {
                return true;
            }

            return false;
        }

        public function upgradeConfigs() {
            $this->getConfigs(true);
            if (is_null($this->configs)) {
                return $this->configs;
            }

            $this->configs['addthis_plugin_version'] = self::$pluginVersion;

            foreach ($this->simpleConfigUpgradeMappings as $configUpgradeMapping) {
                foreach ($configUpgradeMapping['current'] as $currentFieldName) {
                    foreach ($configUpgradeMapping['deprecated'] as $deprecatedFieldName) {
                        $this->getFromPreviousConfig($deprecatedFieldName, $currentFieldName);
                    }
                }
            }

            // if AddThis above button was enabled
            if (   !isset($this->configs['addthis_above_enabled'])
                && isset($this->configs['above'])
            ) {
                if ($this->configs['above'] == 'none' || $this->configs['above'] == 'disable') {
                    $this->configs['addthis_above_enabled'] = false;
                } else {
                    $this->configs['addthis_above_enabled'] = true;
                }
            }

            // if AddThis below button was enabled
            if (   !isset($this->configs['addthis_below_enabled'])
                && isset($this->configs['below'])
            ) {
                if ($this->configs['below'] == 'none' || $this->configs['below'] == 'disable') {
                    $this->configs['addthis_below_enabled'] = false;
                } else {
                    $this->configs['addthis_below_enabled'] = true;
                }
            }

            if (   isset($this->configs['addthis_for_wordpress'])
                && $this->configs['addthis_for_wordpress']
                && !isset($this->configs['addthis_plugin_controls'])
            ) {
                $this->configs['addthis_plugin_controls'] = "AddThis";
            }

            $this->saveConfigs();
            return $this->configs;
        }

        private function getFromPreviousConfig($deprecatedFieldName, $currentFieldName) {
            // if we don't have this value, get from a the depricated field
            if (   is_array($this->configs)
                && isset($this->configs[$deprecatedFieldName])
                && !empty($this->configs[$deprecatedFieldName])
                && !isset($this->configs[$currentFieldName])
            ) {
                $deprecatedValue = $this->configs[$deprecatedFieldName];
                $this->configs[$currentFieldName] = $deprecatedValue;
            }
        }
    }
}