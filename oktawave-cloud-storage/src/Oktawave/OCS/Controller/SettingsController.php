<?php
/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/OktawaveOCS.php';
require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/Helper/Message.php';

/**
 * Set OCS settings page.
 *
 * @author Rafał Lorenz <rlorenz@octivi.com>
 */
class Oktawave_OCS_Controller_SettingsController
{
    /**
     * Holds the values to be used in the fields callbacks.
     */
    private $options;

    /**
     * Settings page callback.
     */
    public function settingsPage()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $this->options = get_option('ocs_settings');
        ?>
        <div class="wrap">
            <h2>Oktawave OCS</h2>
            <?php settings_errors();
        ?>
            <form method="post" action="options.php">
                <?php
                settings_fields('ocs_general_settings');
        do_settings_sections('ocs_general_settings');
        settings_fields('ocs_media_settings');
        do_settings_sections('ocs_media_settings');

        submit_button();
        ?>
            </form>
        </div>
        <script>
            function initPage() {
                var tbody = jQuery('.form-table tbody').first();
                var img = document.createElement("img");

                img.id = 'OCSLoginHelpImage';
                img.alt = '';
                img.src = '<?php echo plugins_url('oktawave-ocs/images/ocs_sample.png');
        ?>';
                img.style.display = 'inline-block';
                img.style.width = '30%';
                img.style.maxWidth = '547px';
                img.style.marginLeft = '10px';

                img.onclick = function() {
                    window.open('<?php echo plugins_url('oktawave-ocs/images/ocs_sample.png');
        ?>', "_blank", "toolbar=no, scrollbars=yes, resizable=yes, width=975, height=665");
                };

                tbody.css({"display": "inline-block", "float": "left"});
                tbody.after(img);
            }

            function checkAccount() {
                jQuery("#check_button").addClass("button-primary-disabled");
                jQuery("#check_spinner").show();
                var params = {
                    username: jQuery('#username').val(),
                    password: jQuery('#password').val(),
                    bucket: jQuery('#bucket').val()
                };
                jQuery.post(
                        "<?php echo admin_url('admin-ajax.php');
        ?>",
                        {
                            'action': 'ocs_check_account',
                            'security': "<?php echo wp_create_nonce('check_account_nonce');
        ?>",
                            'data': params
                        },
                function(response) {
                    jQuery("#check_button").removeClass("button-primary-disabled");
                    jQuery("#check_spinner").hide();
                    if (response.success) {
                        storageUrl = response.data;

                        var bucket = jQuery('#bucket').val();
                        var baseMediaURL = jQuery("#media_url");

                        var url = storageUrl.split("://");

                        baseMediaURL.val("<?php
        if (is_ssl()) {
            echo 'https://';
        } else {
            echo 'http://';
        }
        ?>" + url[1] + '/' + bucket + '/');

                        jQuery("#ocs_notice").remove();
                        jQuery('.wrap h2').append('<div id="ocs_notice" class="updated"><p>Success! Updated Media URL\'s fields.</p></div>');
                    } else {
                        jQuery("#ocs_notice").remove();
                        jQuery('.wrap h2').append('<div id="ocs_notice" class="error"><p>' + response.data + '</p></div>');
                    }
                }
                );
            }

            function testSampleImage() {
                jQuery("#test_sample_image").addClass("button-primary-disabled");
                jQuery("#test_spinner").show();
                var params = {
                    username: jQuery('#username').val(),
                    password: jQuery('#password').val(),
                    bucket: jQuery('#bucket').val(),
                    url: jQuery('#media_url').val()
                };
                jQuery.post(
                        "<?php echo admin_url('admin-ajax.php');
        ?>",
                        {
                            'action': 'ocs_test_sample_image',
                            'security': "<?php echo wp_create_nonce('test_sample_image_nonce');
        ?>",
                            'data': params
                        },
                function(response) {
                    jQuery("#test_sample_image").removeClass("button-primary-disabled");
                    jQuery("#test_spinner").hide();
                    if (response.success) {
                        url = response.data;

                        jQuery("#ocs_notice").remove();
                        jQuery('.wrap h2').append('<div id="ocs_notice" class="updated"><p>Success! Files are publicly available</p></div>');
                        jQuery('#test_image_description').html('<a href="' + url + '"><img src="' + url + '" alt></img></a>');
                    } else {
                        jQuery("#ocs_notice").remove();
                        jQuery('.wrap h2').append('<div id="ocs_notice" class="error"><p>' + response.data + '</p></div>');
                        jQuery('#test_image_description').html('');
                    }
                }
                );
            }

            initPage();
        </script>
        <?php

    }

    /**
     * Register Settings.
     */
    public function pagesInit()
    {
        register_setting(
                'ocs_general_settings', // Option group
                'ocs_settings', // Option name
                array($this, 'sanitize')    // Sanitize
        );

        register_setting(
                'ocs_media_settings', // Option group
                'ocs_settings', // Option name
                array($this, 'sanitize')    // Sanitize
        );

        add_settings_section(
                'general-settings', // ID
                'General Settings', // Title
                array($this, 'printGeneralSettingsInfo'), // Callback
                'ocs_general_settings'                     // Page
        );

        add_settings_section(
                'media-url-settings', // ID
                'Media URL\'s', // Title
                array($this, 'printMediaURLsSettingsInfo'), // Callback
                'ocs_media_settings'                     // Page
        );

        add_settings_field(
                'username', 'Username', array($this, 'usernameFieldCallback'), 'ocs_general_settings', 'general-settings'
        );

        add_settings_field(
                'password', 'Password', array($this, 'passwordFieldCallback'), 'ocs_general_settings', 'general-settings'
        );

        add_settings_field(
                'bucket', 'Bucket Name', array($this, 'bucketFieldCallback'), 'ocs_general_settings', 'general-settings'
        );

        add_settings_field(
                'check_button', '', array($this, 'checkButtonFieldCallback'), 'ocs_general_settings', 'general-settings'
        );

        add_settings_field(
                'media_url', 'Base Media URL', array($this, 'media_urlFieldCallback'), 'ocs_media_settings', 'media-url-settings'
        );

        add_settings_field(
                'test_sample_image', 'Sample image from your Oktawave OCS', array($this, 'testSampleImageCallback'), 'ocs_media_settings', 'media-url-settings'
        );
    }

    /**
     * Sanitize each setting field as needed.
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $input = $this->validate($input);
        $newInput = array();

        if (isset($input['username'])) {
            $newInput['username'] = sanitize_text_field($input['username']);
        }

        if (isset($input['password'])) {
            $newInput['password'] = sanitize_text_field($input['password']);
        }

        if (isset($input['bucket'])) {
            $newInput['bucket'] = sanitize_text_field($input['bucket']);
        }

        if (isset($input['media_url'])) {
            $newInput['media_url'] = esc_url_raw($input['media_url']);
        }

        return $newInput;
    }

    /**
     * Validate setting field as needed.
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function validate($input)
    {
        if (isset($input['username']) && isset($input['password']) && isset($input['bucket'])) {
            $output = get_option('ocs_settings');

            $username = $input['username'];
            $password = $input['password'];
            $bucket = $input['bucket'];
            $url = $input['media_url'];

            try {
                $checkUrl = Oktawave_OCS_OktawaveOCS::checkAccount($username, $password, $bucket);

                if ($checkUrl !== false) {
                    $response = Oktawave_OCS_OktawaveOCS::testSampleImage($username, $password, $bucket, $url);

                    if (array_key_exists('error', $response)) {
                        add_settings_error('ocs_settings', 'invalid_account_data', $response['error'], 'error');
                    } else {
                        $output['username'] = $input['username'];
                        $output['password'] = $input['password'];
                        $output['bucket'] = $input['bucket'];
                        $output['media_url'] = $input['media_url'];

                        update_option('ocs_settings_isconfigured', true);
                    }
                } else {
                    add_settings_error('ocs_settings', 'invalid_account_data', __('Configuration for specified user and bucket already set in other site!', 'ocs_oktawave'), 'error');

                    return $output;
                }
            } catch (Oktawave_OCS_Exception_HttpException $e) {
                $messageHelper = new Oktawave_OCS_Helper_Message();
                $message = $messageHelper->getError($e->getHttpCode());

                add_settings_error('ocs_settings', 'invalid_account_data', $message, 'error');
            }

            return $output;
        }

        return $input;
    }

    /**
     * Print the General Settings Section text.
     */
    public function printGeneralSettingsInfo()
    {
        print __('Enter your account information below:', 'ocs_oktawave');
    }

    /**
     * Print the Media URLs Section text.
     */
    public function printMediaURLsSettingsInfo()
    {
    }

    /**
     * Get the settings option array and print one of its values.
     */
    public function usernameFieldCallback()
    {
        printf(
                '<input type="text" id="username" size="30" placeholder="account:user" name="ocs_settings[username]" value="%s" />', isset($this->options['username']) ? esc_attr($this->options['username']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values.
     */
    public function passwordFieldCallback()
    {
        printf(
                '<input type="password" id="password" size="30" name="ocs_settings[password]" value="%s" />', isset($this->options['password']) ? esc_attr($this->options['password']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values.
     */
    public function bucketFieldCallback()
    {
        printf(
                '<input type="text" id="bucket" size="30" placeholder="wordpress" name="ocs_settings[bucket]" value="%s" />', isset($this->options['bucket']) ? esc_attr($this->options['bucket']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values.
     */
    public function testSampleImageCallback()
    {
        printf(
                '<div><p class="submit">
                    <button id="test_sample_image" title="Test Sample Image" type="button" class="button button-primary" onclick="javascript:testSampleImage();
                            return false;" style="float: left;">
                        Test Sample Image
                    </button>
                    <span id="test_spinner" class="spinner" style="float: left;"></span>
                </p></div>
                <br/>
                <div id="test_image_description">
                </div>'
        );
    }

    /**
     * Get the settings option array and print one of its values.
     */
    public function checkButtonFieldCallback()
    {
        printf(
                '<p class="submit">
                    <button id="check_button" title="Check account" type="button" class="button button-primary" onclick="javascript:checkAccount();
                            return false;" style="float: left;">
                        Check Account
                    </button>
                    <span id="check_spinner" class="spinner" style="float: left;"></span>
                </p>
                <br/>
                <span class="description" style="float: left;">After filling account information press the Check button to validate your settings.</span>'
        );
    }

    /**
     * Get the settings option array and print one of its values.
     */
    public function media_urlFieldCallback()
    {
        printf(
                '<p><input type="text" size="80" id="media_url" name="ocs_settings[media_url]" value="%s" /></p>
                <span class="description">Set your media URL</span>', isset($this->options['media_url']) ? esc_attr($this->options['media_url']) : ''
        );
    }
}
