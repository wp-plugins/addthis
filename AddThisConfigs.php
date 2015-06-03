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

if (!class_exists('AddThisConfigs')) {
    Class AddThisConfigs {

        protected $cmsInterface;
        protected $configs = null;
        protected $changedConfigs = false;
        protected $defaultConfigs = array(
            'above'                        => 'large_toolbox',
            'above_custom_more'            => '',
            'above_custom_preferred'       => '',
            'above_custom_services'        => '',
            'above_custom_size'            => '',
            'above_custom_string'          => '',
            'above_sharing'                => 'above-enable-smart-sharing',
            'addthis_508'                  => '',
            'addthis_above_enabled'        => false,
            'addthis_addressbar'           => false,
            'addthis_aftertitle'           => false,
            'addthis_append_data'          => true,
            'addthis_asynchronous_loading' => true,
            'addthis_beforecomments'       => false,
            'addthis_below_enabled'        => false,
            'addthis_bitly'                => false,
            'addthis_brand'                => '',
            'addthis_config_json'          => '',
            'addthis_header_background'    => '',
            'addthis_header_color'         => '',
            'addthis_language'             => '',
            'addthis_plugin_controls'      => 'AddThis',
            'addthis_profile'              => '',
            'addthis_share_json'           => '',
            'addthis_sidebar_count'        => '5',
            'addthis_sidebar_enabled'      => false,
            'addthis_sidebar_position'     => 'left',
            'addthis_twitter_template'     => '',
            'addthis_environment'          => '',
            'atversion'                    => ADDTHIS_ATVERSION,
            'atversion_update_status'      => 0,
            'below'                        => 'large_toolbox',
            'below_custom_more'            => '',
            'below_custom_preferred'       => '',
            'below_custom_services'        => '',
            'below_custom_size'            => '',
            'below_custom_string'          => '',
            'below_sharing'                => 'below-enable-smart-sharing',
            'credential_validation_status' => 0,
            'data_ga_property'             => '',
            'location'                     => 'below',
            'style'                        => addthis_style_default ,
            'toolbox'                      => '',
        );

        public function __construct($cmsInterface) {
            $this->cmsInterface = $cmsInterface;
        }

        public function getDefaultConfigs() {
            $defaultConfigs = array();

            // add all share button location template settings to default to true
            $locationContentTypeLocationFields = $this->getFieldsForContentTypeSharingLocations();

            foreach ($locationContentTypeLocationFields as $field) {
                $optionName = $field['fieldName'];
                $defaultValue = $field['default'];
                settype($defaultValue, $field['dataType']);
                $defaultConfigs[$optionName] = $defaultValue;
            }

            $defaultConfigs = array_merge(
                $this->defaultConfigs,
                $defaultConfigs,
                $this->cmsInterface->getDefaultConfigs()
            );

            return $defaultConfigs;
        }

        public function getConfigs() {
            if ($this->cmsInterface->isUpgrade()) {
                $this->configs = $this->cmsInterface->upgradeConfigs();
            }

            $this->configs = $this->setDefaultConfigs();
            return $this->configs;
        }


        public function saveConfigs($configs = null) {
            if (!is_array($this->configs)) {
                $this->configs = $this->getConfigs();
            }

            if (is_array($configs) && is_array($this->configs)) {
                $this->configs = array_merge($this->configs, $configs);
            } elseif (is_array($configs) && !is_array($this->configs)) {
                $this->configs = $configs;
            }

            if (!is_null($this->configs)) {
                $this->configs = $this->cmsInterface->saveConfigs($this->configs);
            }

            $this->changedConfigs = false;
            return $this->configs;
        }

        private function setDefaultConfigs() {
            $this->configs = $this->cmsInterface->getConfigs(true);

            if (!is_array($this->configs)) {
                $this->configs = $this->getDefaultConfigs();
                $this->changedConfigs = true;
            } else {
                foreach($this->getDefaultConfigs() as $fieldName => $defaultValue) {
                    if (!isset($this->configs[$fieldName])) {
                        $this->configs[$fieldName] = $defaultValue;
                        $this->changedConfigs = true;
                    }
                }
            }

            if ($this->changedConfigs) {
                $this->configs = $this->saveConfigs();
            }

            return $this->configs;
        }

        public function getFieldsForContentTypeSharingLocations($requestedContentType = null, $requestedLocation = null) {
            $buttonLocations = $this->cmsInterface->getSharingButtonLocations();
            $contentTypes = $this->cmsInterface->getContentTypes();
            $fields = array();

            foreach ($buttonLocations as $location) {
                if ($requestedLocation !== null && $requestedLocation !== $location) {
                    continue;
                }

                foreach ($contentTypes as $template) {
                    if ($requestedContentType !== null && $requestedContentType !== $template['fieldName']) {
                        continue;
                    }

                    if ($location == 'sidebar' && $template['fieldName'] == 'excerpts') {
                        continue;
                    }

                    $fieldName = "addthis_" . $location . "_showon_" . $template['fieldName'];
                    $variableName = $location . "_" . $template['fieldName'];
                    $displayName = $template['displayName'];
                    $explanation = $template['explanation'];

                    $fieldInfo = array(
                        'fieldName'    => $fieldName,
                        'variableName' => $variableName,
                        'displayName'  => $displayName,
                        'explanation'  => $explanation,
                        'location'     => $location,
                        'template'     => $template,
                        'fieldType'    => 'checkbox',
                        'dataType'     => 'boolean',
                        'default'      => true,
                    );
                    $fields[] = $fieldInfo;
                }
            }

            return $fields;
        }

        public function getProfileId() {
            $this->getConfigs();
            if(   isset($this->configs['addthis_profile'])
               && !empty($this->configs['addthis_profile'])
            ) {
                return $this->configs['addthis_profile'];
            }

            return '';
        }

        public function getAnonymousProfileId() {
            $this->getConfigs();
            if(   !isset($this->configs['addthis_anonymous_profile'])
               || !$this->configs['addthis_anonymous_profile']
            ) {
                $prefix = $this->cmsInterface->getAnonymousProfileIdPrefix();
                $url = $this->cmsInterface->getHomepageUrl();
                $postfix = hash_hmac('md5', $url, 'addthis');
                $this->configs['addthis_anonymous_profile'] = $prefix . '-' . $postfix;
                $this->saveConfigs();
            }

            return $this->configs['addthis_anonymous_profile'];
        }


        public function getUsableProfileId() {
            if ($this->getProfileId()) {
                return $this->getProfileId();
            }

            return $this->getAnonymousProfileId();
        }
    }
}