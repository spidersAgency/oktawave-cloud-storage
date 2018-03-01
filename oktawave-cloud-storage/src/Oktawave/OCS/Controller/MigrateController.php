<?php
/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/OktawaveOCS.php';

/**
 * Set OCS migration page.
 *
 * @author Rafał Lorenz <rlorenz@octivi.com>
 */
class Oktawave_OCS_Controller_MigrateController
{
    /**
     * Migrate page callback.
     */
    public function migratePage()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        ?>
        <div class="wrap">
            <h2>Migrate to OCS</h2>
            <?php
            if (!Oktawave_OCS_OktawaveOCS::isConfigured()) {
                echo sprintf(__('<h3><b>First you need to configure your OCS account <a href="%s/wp-admin/admin.php?page=oktawave_ocs_settings">here</a>.</b></h3>', 'ocs_oktawave'), get_site_url());
            }
        ?>
            <h3>To complete migration process, please follow steps:</h3>
            <h4><b>1.</b> Upload files to Oktawave OCS</h4>
            <div style="height:30px;">
                <div class="goal" style="width:300px;height:30px;background:#A9D0F5;border:solid 1px #555;">
                    <div class="migrate_progress" style="text-align:right;vertical-align: middle;line-height: 30px;height:30px;background:#3366FF;color:white;width:0%;">
                        <b>0%</b>
                    </div>
                </div>
                <div class="migrate_stats" style="margin-left:10px;display:none;position: relative;top: -23px;">
                    <p class="bytes"><span id="size">-/- <b>MB</b></span><span id="speed">(0 MB/s)</span></p>
                    <p class="count">-/- <b>files</b></p>
                </div>
            </div>
            <p class="submit">
                <button id="migrate_button" title="Migrate" type="button" class="button button-primary <?php
                if (!Oktawave_OCS_OktawaveOCS::isConfigured()) {
                    echo 'button-primary-disabled';
                }
        ?>" onclick="javascript:migrateFiles();
                                return false;" style="float: left;">
                    Run upload process
                </button>
                <span id="migrate_spinner" class="spinner" style="float: left;"></span>
            </p>
            <p class="submit">
                <button id="skip_migrate_button" title="Skip file upload process" type="button" class="button button-primary <?php
                if (!Oktawave_OCS_OktawaveOCS::isConfigured() || Oktawave_OCS_OktawaveOCS::isMigrated()) {
                    echo 'button-primary-disabled';
                }
        ?>" onclick="javascript:skipMigrateFiles();
                                return false;" style="float: left;" placehol>
                    Skip upload
                </button>
            </p>
            <div style="clear:both;"><span class="description">If you've manually uploaded files you can skip step 1 with this button.</span></div>
            <h4><b>2.</b> Replace media files' URLs in pages</h4>
            <div style="height:30px;">
                <div class="goal" style="width:300px;height:30px;background:#A9D0F5;border:solid 1px #555;">
                    <div class="replace_progress" style="text-align:right;vertical-align: middle;line-height: 30px;height:30px;background:#3366FF;color:white;width:0%;">
                        <b>0%</b>
                    </div>
                </div>
                <div class="replace_stats" style="margin-left:10px;display:none;position: relative;top: -5px;">
                    <p class="processed">-/- <b>pages</b></p>
                </div>
            </div>
            <p class="submit">
                <button id="replace_button" title="Replace" type="button" class="button button-primary <?php
                if (!Oktawave_OCS_OktawaveOCS::isConfigured()) {
                    echo 'button-primary-disabled';
                }
        ?>" onclick="javascript:replaceFiles();
                                return false;" style="float: left;">
                    Run replace process
                </button>
                <span id="replace_spinner" class="spinner" style="float: left;"></span>
            </p>
        </div>
        <script>
            var processed = 0;
            var totalBytes = 0;
            var totalCount = 0;
            var count = 0;
            var bytes = 0;
            var migratedNow = false;
            var currentTimeSeconds = 0;
            var migrateButtonPause = false;
            var migrateButtonResume = false;

            function replaceFile(offset) {
                jQuery.post(
                        "<?php echo admin_url('admin-ajax.php');
        ?>",
                        {
                            'action': 'ocs_run_on_posts',
                            'offset': offset,
                            'security': "<?php echo wp_create_nonce('run_on_posts_nonce');
        ?>"
                        },
                function(response) {
                    if (response.success) {
                        data = response.data;
                        processed += data.processed;

                        jQuery("#ocs_notice").remove();

                        setReplaceProgress(countPercentage(processed, data.total));
                        setReplaceProgressCounters(processed, data.total);

                        if (data.total > processed) {
                            replaceFile(processed);
                        } else {
                            jQuery("#replace_button").text("Replace success");
                            jQuery("#replace_spinner").hide();
                            jQuery("#ocs_notice").remove();
                            jQuery('.wrap h2').append('<div id="ocs_notice" class="updated"><p>Successful modified URLs in ' + processed + ' posts.</p></div>');
                        }

                    } else {
                        jQuery("#replace_button").removeClass("button-primary-disabled");
                        jQuery("#replace_spinner").hide();
                        jQuery("#ocs_notice").remove();
                        jQuery('.wrap h2').append('<div id="ocs_notice" class="error"><p>' + response.data + '</p></div>');
                    }
                }
                );
            }

            var migrate = function migrateFile(offset) {
                if (migrateButtonPause) {
                    jQuery.post(
                            "<?php echo admin_url('admin-ajax.php');
        ?>",
                            {
                                'action': 'ocs_migrate_files',
                                'offset': offset,
                                'security': "<?php echo wp_create_nonce('migrate_files_nonce');
        ?>"
                            },
                    function(response) {
                        if (response.success) {
                            data = response.data;
                            bytes += data.bytes;
                            count += data.count;

                            jQuery("#ocs_notice").remove();

                            if (totalCount >= count && !migratedNow) {
                                setMigrateProgress(countPercentage(bytes, totalBytes));
                                setMigrateProgressCounters(count, bytes);

                                if (totalCount === count) {
                                    migratedNow = true;
                                }

                                migrate(count);
                            } else {
                                migrateButtonPause = false;
                                migrateButtonResume = false;
                                jQuery("#migrate_button").addClass("button-primary-disabled");
                                jQuery("#migrate_button").text("Upload success");
                                jQuery("#migrate_spinner").hide();
                                jQuery("#ocs_notice").remove();
                                jQuery('.wrap h2').append('<div id="ocs_notice" class="updated"><p>Successful uploaded ' + count + ' files to Oktawave OCS</p></div>');
                            }

                        } else {
                            migrateButtonPause = false;
                            migrateButtonResume = false;
                            jQuery("#migrate_button").text("Run upload process");
                            jQuery("#migrate_spinner").hide();
                            jQuery("#ocs_notice").remove();
                            jQuery('.wrap h2').append('<div id="ocs_notice" class="error"><p>Error! An error occurred, please try again.</p></div>');
                        }
                    }
                    );
                }
            };

            function getFilesStats(migrate) {
                displayProgressCounters();
                jQuery.post(
                        "<?php echo admin_url('admin-ajax.php');
        ?>",
                        {
                            'action': 'ocs_get_files_stats',
                            'security': "<?php echo wp_create_nonce('get_files_stats_nonce');
        ?>"
                        },
                function(response) {
                    if (response.success) {
                        data = response.data;
                        totalBytes = data.bytes;
                        totalCount = data.count;
                        migrate(count);
                    }
                }
                );
            }

            function countPercentage(current, max) {
                if (max === 0) {
                    return 100;
                } else {
                    return Math.round(current / max * 100);
                }
            }

            function setReplaceProgress(value) {
                jQuery('.replace_progress').width(value + '%');
                jQuery('.replace_progress b').text(value + '%');
            }

            function setMigrateProgress(value) {
                jQuery('.migrate_progress').width(value + '%');
                jQuery('.migrate_progress b').text(value + '%');
            }

            function displayProgressCounters() {
                jQuery('.migrate_stats').css("display", "inline-block");
                jQuery('.goal').first().css("display", "inline-block");
                jQuery('.goal').first().css("float", "left");
            }

            function setMigrateProgressCounters(countValue, bytesValue) {
                var seconds = ((new Date().getTime() / 1000) - currentTimeSeconds).toFixed();
                var bytesValuePerSec = 0;

                if (seconds !== 0) {
                    bytesValuePerSec = bytesValue / seconds;
                } else {
                    bytesValuePerSec = bytesValue;
                }

                var processedMB = bytesToMB(bytesValuePerSec);

                if (processedMB > 1) {
                    speedTxt = ' (' + processedMB + ' MB/s)';
                } else {
                    speedTxt = ' (' + bytesToKB(bytesValuePerSec) + ' KB/s)';
                }

                jQuery('#speed').html(speedTxt);
                jQuery('#size').html(bytesToMB(bytesValue) + '/' + bytesToMB(totalBytes) + ' <b>MB</b>');
                jQuery('.count').html(countValue + '/' + totalCount + ' <b>files</b>');
            }

            function displayReplaceCounters() {
                jQuery('.replace_stats').css("display", "inline-block");
                jQuery('.goal:eq(1)').css("display", "inline-block");
                jQuery('.goal:eq(1)').css("float", "left");
            }

            function setReplaceProgressCounters(value, max) {
                jQuery('.processed').html(value + '/' + max + ' <b>pages</b>');
            }

            function migrateFiles() {
        <?php if (Oktawave_OCS_OktawaveOCS::isConfigured()) {
    ?>
                    if (!migrateButtonPause && !migrateButtonResume) {
                        if (!jQuery("#migrate_button").hasClass("button-primary-disabled")) {
                            jQuery("#migrate_spinner").show();
                            jQuery("#migrate_button").text("Pause");
                            currentTimeSeconds = new Date().getTime() / 1000;
                            migratedNow = false;
                            migrateButtonPause = true;
                            totalBytes = 0;
                            totalCount = 0;
                            count = 0;
                            bytes = 0;
                            setMigrateProgress(0);
                            setMigrateProgressCounters(0, 0);
                            getFilesStats(migrate);
                        }
                    } else {
                        if (migrateButtonPause) {
                            migrateButtonPause = false;
                            migrateButtonResume = true;
                            jQuery("#migrate_button").text("Resume");
                            jQuery("#migrate_spinner").hide();
                            jQuery('.migrate_stats').hide();
                        } else if (migrateButtonResume) {
                            migrateButtonPause = true;
                            migrateButtonResume = false;
                            currentTimeSeconds = new Date().getTime() / 1000;
                            jQuery("#migrate_button").text("Pause");
                            jQuery("#migrate_spinner").show();
                            jQuery('.migrate_stats').show();
                            migrate(count);
                        }
                    }
        <?php

}
        ?>
            }

            function skipMigrateFiles() {
        <?php if (Oktawave_OCS_OktawaveOCS::isConfigured()) {
    ?>
            <?php if (!Oktawave_OCS_OktawaveOCS::isMigrated()) {
    ?>
                        if (!migrateButtonPause && !migrateButtonResume) {
                            if (!jQuery("#skip_migrate_button").hasClass("button-primary-disabled")) {
                                jQuery("#skip_migrate_button").addClass("button-primary-disabled");
                                jQuery("#migrate_button").addClass("button-primary-disabled");

                                jQuery.post(
                                        "<?php echo admin_url('admin-ajax.php');
    ?>",
                                        {
                                            'action': 'ocs_skip_upload_files',
                                            'security': "<?php echo wp_create_nonce('skip_upload_files_nonce');
    ?>"
                                        },
                                function(response) {
                                    if (response.success) {
                                        jQuery("#ocs_notice").remove();
                                        jQuery('.wrap h2').append('<div id="ocs_notice" class="updated"><p>Successful skiped upload files process</p></div>');
                                    }
                                }
                                );
                            }
                        }<?php

}
}
        ?>

            }

            function replaceFiles() {
        <?php if (Oktawave_OCS_OktawaveOCS::isConfigured()) {
    ?>
                    if (!jQuery("#replace_button").hasClass("button-primary-disabled")) {
                        jQuery("#replace_button").addClass("button-primary-disabled");
                        jQuery("#replace_spinner").show();
                        displayReplaceCounters();
                        processed = 0;
                        replaceFile(processed);
                    }
        <?php

}
        ?>
            }

            function bytesToMB(bytes) {
                return (bytes / Math.pow(1024, 2)).toFixed(1);
            }

            function bytesToKB(bytes) {
                return (bytes / Math.pow(1024, 1)).toFixed(1);
            }
        </script>
        <?php

    }
}
