<?php
/**
* HTML output for global configuration page
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* Show the configuration editor
*/
class nBillConfig
{
    public static function showConfig($row, $license_key, $user_groups, $email_options_xref, $xref_default_start_date, $ftp_address, $ftp_port, $ftp_username, $ftp_password, $ftp_root, $ftp_success, $ftp_message)
    {
	    $task = nbf_common::get_param($_REQUEST, 'task');
        $nb_database = nbf_cms::$interop->database;
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.client.php");

        //See if nBill 1.2.x tables exist
        $offer_migration = false;
        $sql = "SELECT software_version FROM #__inv_version WHERE id = 1"; //Show tables does not work in J1.5 legacy
        $nb_database->setQuery($sql);
        $existing_tables = @$nb_database->loadResult();

        if ($existing_tables)
        {
            //Earliest allowed is 1.2.8
            $sql = "SELECT software_version FROM #__inv_version WHERE id = 1";
            $nb_database->setQuery($sql);
            if ($nb_database->loadResult() >= "1.2.8")
            {
                $offer_migration = true;
            }
        }

        nbf_cms::$interop->add_html_header('<script type="text/javascript" src="' . nbf_cms::$interop->nbill_site_url_path . '/js/jscolor/jscolor.js"></script>');
        ?>
	    <script type="text/javascript" language="javascript">
	    function nbill_submit_task(task_name)
	    {
		    document.adminForm.task.value=task_name;
            document.adminForm.submit();
	    }
	    function clear_tables()
	    {
		    if (confirm('<?php echo strtolower(nbf_version::$suffix) == 'lite' ? NBILL_CFG_CONFIRM_CLEAR_LITE : NBILL_CFG_CONFIRM_CLEAR; ?>'))
		    {
			    nbill_submit_task('cleartables');
		    }
	    }
	    function delete_tables()
	    {
		    if (confirm('<?php echo NBILL_CFG_CONFIRM_DELETE; ?>'))
		    {
			    nbill_submit_task('deletetables');
		    }
	    }
        <?php
        echo get_prompt_js();
        ?>
        //Need a few globals for asynchronous processing of data migration from v 1.2.x
        var abort = false; //Global used to abort synchronisation processing
        var maxdate = new Array(); //Global used to store sync date (rather than passing back and forth)
        var record_pointer = 0;
        var percentage = 0;
        var complete = 0;
        var message = '';
        var task_name = 'start';
        var task_title = '<?php echo NBILL_CFG_MIGRATE_DATA; ?>';

        function migrate_data()
        {
            if (confirm('<?php echo sprintf(NBILL_MIGRATE_DELETE_WARNING, nbf_version::$nbill_version_no); ?>'))
            {
                do_migrate('<?php echo NBILL_CFG_MIGRATE_DATA; ?>');
            }
        }

        function do_migrate(task_title)
        {
            IEprompt('', '', draw_progress_bar(percentage, task_title));
            complete = 0;
            message = '';
            abort = false;
            submit_ajax_request('migrate_data', 'taskname=' + task_name + '&pointer=' + record_pointer, function(result){migrate_ajax_callback(result);}, false);
            return;
        }

        function migrate_ajax_callback(ajax_result)
        {
            ajax_result = ajax_result.split('#!#');
            if (ajax_result.length == 6)
            {
                message = urldecode(ajax_result[5]);
                if (message.length == 0)
                {
                    record_pointer = ajax_result[0];
                    task_name = ajax_result[1];
                    percentage = ajax_result[3];
                }
                complete = ajax_result[2];
                task_title = ajax_result[4];
                if (task_title.length == 0)
                {
                    task_title = '<?php echo NBILL_CFG_MIGRATE_DATA; ?>'
                }
                if (message.length == 0 && !abort)
                {
                    if (complete == 0)
                    {
                        do_migrate(task_title);
                        return;
                    }
                }
                else if (abort)
                {
                    abort_migrate();
                }
            }
            else
            {
                //Something went wrong!
                abort = true;
            }

            if (message.length == 0 && abort)
            {
                message = '<?php echo NBILL_MIGRATE_ABORTED; ?>'
            }
            else
            {
                //Completed successfully
                IEprompt('', '', draw_progress_bar(100, task_title));
                if(message.length == 0)
                {
                    message = '<?php echo sprintf(NBILL_MIGRATE_SUCCESS, '<a href="' . nbf_cms::$interop->admin_page_prefix . '&action=anomaly">' . NBILL_MNU_ANOMALY . '</a>'); ?>';
                }
                document.getElementById('migrate_retry_message').style.display = 'none';
            }
            if (message.length > 0)
            {
                document.getElementById('migrate_message').innerHTML = message;

                // clear out the dialog box
                _dialogPromptID.style.display='none';
                // clear out the screen
                _blackoutPromptID.style.display='none';
                //Show dropdowns
                IEshow_dropdowns();

                if (abort || complete == 0)
                {
                    alert(message);
                    document.getElementById('migrate_retry_message').innerHTML = '<?php echo str_replace('>', '\\>', str_replace('<', '\\<', NBILL_MIGRATE_RETRY)); ?>'.replace('%s', 'do_migrate(\'' + task_title + '\')');
                    document.getElementById('migrate_retry_message').style.display = 'block';
                }
                document.getElementById('migrate_message').style.display = 'block';
                return;
            }
        }

        function draw_progress_bar(percentage, task_name)
        {
            percentage = percentage > 100 ? 100 : percentage;
            percentage = percentage < 0 ? 0 : percentage;
            var html = '<div style="margin-top: 5px;width:300px;height:100px;text-align:center;padding:0px;margin-left:auto;margin-right:auto;">';
            html += '<p><strong>' + task_name + ' - ' + parseInt(percentage) + '%</strong></p>';
            html += '<div style="border:solid 1px #666666;background-color:#ffffff;width:200px;height:30px;margin-left:auto;margin-right:auto;text-align:left;">';
            html += '<div style="background-color:#000099;width:' + parseInt(percentage * 2) + 'px;height:30px;"></';
            html += 'div>';
            html += '<div style="margin-top: 10px;margin-left:auto;margin-right:auto;width:50px;"><input type="button" id="abort_migrate" style="width:50px;" value="<?php echo NBILL_MIGRATE_ABORT; ?>" onclick="if (confirm(\'<?php echo NBILL_MIGRATE_ABORT_SURE; ?>\')){abort_migrate();}" /></';
            html += 'div></';
            html += 'div></';
            html += 'div>';
            return html;
        }

        function abort_migrate()
        {
            var innerhtml = '<div style="width:100%;height:100%;text-align:center;"><div style="margin:auto;width:auto;padding:10px;"><h3><?php echo NBILL_MIGRATE_ABORTING; ?></h3></';
            innerhtml += 'div></';
            innerhtml += 'div>'
            _dialogPromptID.innerHTML = innerhtml
            abort = true;
        }

        <?php
        /*
        This copyright notice applies to the following URLEncode javascript function ONLY:
        Copyright (c) 2007 & 2008 cass-hacks.com

        Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

           1. Redistributions of source code must retain the above copyright notice, this list of conditions, and the following disclaimer.
           2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution, and in the same place and form as other copyright, license and disclaimer information.
           3. The end-user documentation included with the redistribution, if any, must include the following acknowledgment: "This product includes software developed by Cass-hacks.com (http://cass-hacks.com/) and its contributors", in the same place and form as other third-party acknowledgments. Alternately, this acknowledgment may appear in the software itself, in the same form and location as other such third-party acknowledgments.
           4. Except as contained in this notice, the name of Cass-hacks.com shall not be used in advertising or otherwise to promote the sale, use or other dealings in this Software without prior written authorization from Cass-hacks.com.

        THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE XFREE86 PROJECT, INC OR ITS CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
        */
        ?>
        function URLEncode (clearString)
        {
          var output = '';
          var x = 0;
          clearString = clearString.toString();
          var regex = /(^[a-zA-Z0-9_.]*)/;
          while (x < clearString.length) {
            var match = regex.exec(clearString.substr(x));
            if (match != null && match.length > 1 && match[1] != '') {
                output += match[1];
              x += match[1].length;
            } else {
              if (clearString[x] == ' ')
                output += '+';
              else {
                var charCode = clearString.charCodeAt(x);
                var hexVal = charCode.toString(16);
                output += '%' + ( hexVal.length < 2 ? '0' : '' ) + hexVal.toUpperCase();
              }
              x++;
            }
          }
          return output;
        }

        function urldecode (str)
        {
            // Decodes URL-encoded string
            //
            // version: 909.322
            // discuss at: http://phpjs.org/functions/urldecode
            // +   original by: Philip Peterson
            // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // +      input by: AJ
            // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // +   improved by: Brett Zamir (http://brett-zamir.me)
            // +      input by: travc
            // +      input by: Brett Zamir (http://brett-zamir.me)
            // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
            // +   improved by: Lars Fischer
            // +      input by: Ratheous
            // +   improved by: Orlando
            // %        note 1: info on what encoding functions to use from: http://xkr.us/articles/javascript/encode-compare/
            // *     example 1: urldecode('Kevin+van+Zonneveld%21');
            // *     returns 1: 'Kevin van Zonneveld!'
            // *     example 2: urldecode('http%3A%2F%2Fkevin.vanzonneveld.net%2F');
            // *     returns 2: 'http://kevin.vanzonneveld.net/'
            // *     example 3: urldecode('http%3A%2F%2Fwww.google.nl%2Fsearch%3Fq%3Dphp.js%26ie%3Dutf-8%26oe%3Dutf-8%26aq%3Dt%26rls%3Dcom.ubuntu%3Aen-US%3Aunofficial%26client%3Dfirefox-a');
            // *     returns 3: 'http://www.google.nl/search?q=php.js&ie=utf-8&oe=utf-8&aq=t&rls=com.ubuntu:en-US:unofficial&client=firefox-a'

            var hash_map = {}, ret = str.toString(), unicodeStr='', hexEscStr='';

            var replacer = function (search, replace, str) {
                var tmp_arr = [];
                tmp_arr = str.split(search);
                return tmp_arr.join(replace);
            };

            // The hash_map is identical to the one in urlencode.
            hash_map["'"]   = '%27';
            hash_map['(']   = '%28';
            hash_map[')']   = '%29';
            hash_map['*']   = '%2A';
            hash_map['~']   = '%7E';
            hash_map['!']   = '%21';
            hash_map['%20'] = '+';
            hash_map['\u00DC'] = '%DC';
            hash_map['\u00FC'] = '%FC';
            hash_map['\u00C4'] = '%D4';
            hash_map['\u00E4'] = '%E4';
            hash_map['\u00D6'] = '%D6';
            hash_map['\u00F6'] = '%F6';
            hash_map['\u00DF'] = '%DF';
            hash_map['\u20AC'] = '%80';
            hash_map['\u0081'] = '%81';
            hash_map['\u201A'] = '%82';
            hash_map['\u0192'] = '%83';
            hash_map['\u201E'] = '%84';
            hash_map['\u2026'] = '%85';
            hash_map['\u2020'] = '%86';
            hash_map['\u2021'] = '%87';
            hash_map['\u02C6'] = '%88';
            hash_map['\u2030'] = '%89';
            hash_map['\u0160'] = '%8A';
            hash_map['\u2039'] = '%8B';
            hash_map['\u0152'] = '%8C';
            hash_map['\u008D'] = '%8D';
            hash_map['\u017D'] = '%8E';
            hash_map['\u008F'] = '%8F';
            hash_map['\u0090'] = '%90';
            hash_map['\u2018'] = '%91';
            hash_map['\u2019'] = '%92';
            hash_map['\u201C'] = '%93';
            hash_map['\u201D'] = '%94';
            hash_map['\u2022'] = '%95';
            hash_map['\u2013'] = '%96';
            hash_map['\u2014'] = '%97';
            hash_map['\u02DC'] = '%98';
            hash_map['\u2122'] = '%99';
            hash_map['\u0161'] = '%9A';
            hash_map['\u203A'] = '%9B';
            hash_map['\u0153'] = '%9C';
            hash_map['\u009D'] = '%9D';
            hash_map['\u017E'] = '%9E';
            hash_map['\u0178'] = '%9F';
            hash_map['\u00C6'] = '%C3%86';
            hash_map['\u00D8'] = '%C3%98';
            hash_map['\u00C5'] = '%C3%85';

            for (unicodeStr in hash_map) {
                hexEscStr = hash_map[unicodeStr]; // Switch order when decoding
                ret = replacer(hexEscStr, unicodeStr, ret); // Custom replace. No regexing
            }

            // End with decodeURIComponent, which most resembles PHP's encoding functions
            ret = decodeURIComponent(ret);

            return ret;
        }
        </script>

	    <table class="adminheading" style="width:auto;">
	    <tr>
		    <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "config"); ?>>
			    <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_CONFIG_TITLE; ?>
		    </th>
	    </tr>
	    </table>
	    <div class="nbill-message-ie-padding-bug-fixer"></div>
        <div id="migrate_message" class="nbill-message" style="display:none;"></div>
        <div id="migrate_retry_message" class="nbill-message" style="display:none;"></div>
	    <?php if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
	    {
		    echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
	    }

        if ($row->id === false)
        {
            //Database tables have been deleted
            return;
        }

	    if (nbf_upgrader::$new_version_available) {
		    $new_version = "v" . nbf_upgrader::$latest_version;
		    echo "<div class=\"nbill-message\">" . sprintf(NBILL_NEW_VERSION_AVAILABLE, $new_version);
		    if (nbf_upgrader::$latest_auto)
		    {
			    echo " " . sprintf(NBILL_TO_UPGRADE_NOW, "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=configuration&task=update_now\">" . NBILL_CLICK_HERE . "</a>");
		    }
		    echo "</div>";
		    if (nbf_common::nb_strlen(nbf_upgrader::$latest_description) > 0)
		    {
			    echo "<div style=\"border:solid 1px #cccccc;margin-top:5px;font-weight:bold;color:#666666;width:80%\">" . nbf_upgrader::$latest_description . "</div>";
		    }
	    } else if (nbf_upgrader::$old_version_checker) {
            echo "<div class=\"nbill-message\">" . NBILL_OLD_VERSION_CHECKER . "</div>";
        } else if ($task == "check_version" || $task == "update_now")
	    {
		    if ($task == "check_version" || nbf_common::nb_strlen($message) == 0) //If we've just upgraded, no need to say there is no update available!
		    {
			    if (nbf_upgrader::$unable_to_check_version) {
				    echo "<div class=\"nbill-message\">" . NBILL_UNABLE_TO_CHECK_VERSION . (strlen(trim(nbf_upgrader::$latest_description)) > 0 ? ' ' . NBILL_ERROR_MESSAGE . nbf_upgrader::$latest_description : '') . "</div>";
			    } else {
				    echo "<div class=\"nbill-message\">" . NBILL_NO_NEW_VERSION_AVAILABLE . "</div>";
			    }
		    }
	    } ?>

	    <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm" style="clear:both;">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="configuration" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
	    <?php nbf_html::add_filters(); ?>
        <?php
        $tab_settings = new nbf_tab_group();
        $tab_settings->start_tab_group("admin_settings");
        $tab_settings->add_tab_title("basic", NBILL_ADMIN_TAB_BASIC);
        $tab_settings->add_tab_title("advanced", NBILL_ADMIN_TAB_ADVANCED);
        ob_start();
        ?>
        <div class="rounded-table">
        <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-configuration">
	    <tr>
		    <th colspan="2"><?php echo NBILL_CONFIG_TITLE; ?></th>
	    </tr>
        <?php
        echo nbf_html::show_admin_setting_yes_no($row, 'disable_email', 'CFG_', '', $row->disable_email ? 'style="background-color:#ffcccc;"' : '');
        ?>
	    <tr id="nbill-admin-tr-error-email">
		    <td class="nbill-setting-caption">
			    <?php echo NBILL_CFG_ERROR_EMAIL; ?>
		    </td>
		    <td class="nbill-setting-value">
			    <input type="text" name="error_email" value="<?php echo $row->error_email; ?>" class="inputbox" /><?php
                nbf_html::show_static_help(NBILL_CFG_INSTR_ERROR_EMAIL, "error_email_help"); ?>
		    </td>
	    </tr>
        <!-- Custom Fields Placeholder -->
        <?php
        echo nbf_html::show_admin_setting_yes_no($row, 'default_electronic', 'CFG_');
        ?>
	    <tr id="nbill-admin-tr-date-format">
		    <td class="nbill-setting-caption">
			    <?php echo NBILL_CFG_DATE_FORMAT; ?>
		    </td>
		    <td class="nbill-setting-value">
			    <input type="text" name="date_format" value="<?php echo $row->date_format; ?>" class="inputbox" /><?php
                nbf_html::show_static_help(NBILL_CFG_INSTR_DATE_FORMAT, "date_format_help"); ?>
		    </td>
	    </tr>
        <tr id="nbill-admin-tr-list-start-date">
            <td class="nbill-setting-caption">
                <?php echo NBILL_CFG_LIST_START_DATE; ?>
            </td>
            <td class="nbill-setting-value">
                <?php
                    //Create a dropdown of default start date options
                    $type_list = array();
                    foreach ($xref_default_start_date as $start_date)
                    {
                        $start_date_list[] = nbf_html::list_option($start_date->code, $start_date->description);
                    }
                    echo nbf_html::select_list($start_date_list, "default_start_date", 'class="inputbox" id="default_start_date"', $row->default_start_date);
                ?>
                <?php nbf_html::show_static_help(NBILL_CFG_INSTR_LIST_START_DATE, "default_start_date_help"); ?>
            </td>
        </tr>
        <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
        <tr id="nbill-admin-tr-license-key">
            <td class="nbill-setting-caption">
                <?php echo NBILL_CFG_LICENSE_KEY; ?>
            </td>
            <td class="nbill-setting-value">
                <input type="text" name="license_key" value="<?php echo $license_key; ?>" class="inputbox"<?php echo nbf_cms::$interop->demo_mode ? ' disabled="disabled"' : '' ?> />
                <?php
                $eula_link = "<a target=\"_blank\" href=\"http://" . NBILL_BRANDING_EULA . "\">" . NBILL_CFG_EULA . "</a>";
                nbf_html::show_static_help(sprintf(NBILL_CFG_INSTR_LICENSE_KEY, $eula_link), "license_key_help"); ?>
            </td>
        </tr>
        <?php } ?>
        <tr id="nbill-admin-tr-ssl">
            <td class="nbill-setting-caption">
                <?php echo NBILL_CFG_SWITCH_TO_SSL; ?>
            </td>
            <td class="nbill-setting-value">
                <?php echo nbf_html::yes_or_no_options("switch_to_ssl", "", $row->switch_to_ssl); ?>
                <?php nbf_html::show_static_help(NBILL_CFG_INSTR_SWITCH_TO_SSL, "switch_to_ssl_help"); ?>
            </td>
        </tr>
        <tr id="nbill-admin-tr-all-ssl">
            <td class="nbill-setting-caption">
                <?php echo NBILL_CFG_ALL_PAGES_SSL; ?>
            </td>
            <td class="nbill-setting-value">
                <?php echo nbf_html::yes_or_no_options("all_pages_ssl", "", $row->all_pages_ssl); ?>
                <?php nbf_html::show_static_help(NBILL_CFG_INSTR_ALL_PAGES_SSL, "all_pages_ssl_help"); ?>
            </td>
        </tr>
        <tr id="nbill-admin-tr-email-invoice-options">
            <td class="nbill-setting-caption">
                <?php echo NBILL_EMAIL_INVOICE_OPTIONS; ?>
            </td>
            <td class="nbill-setting-value">
                <?php
                $email_options = array();
                foreach ($email_options_xref as $option_code)
                {
                    if ($option_code->code == "AC" || $option_code->code == "FF")
                    {
                        //Only allow PDFs if the PDF generator is installed
                        if (!nbf_common::pdf_writer_available())
                        {
                            continue;
                        }
                    }
                    $email_options[] = nbf_html::list_option($option_code->code, $option_code->description . "<br />");
                }
                echo nbf_html::radio_list($email_options, "email_invoice_option", $row->email_invoice_option); ?>
                <input type="hidden" name="old_email_invoice_option" id="old_email_invoice_option" value="<?php echo $row->email_invoice_option; ?>" />
                <?php nbf_html::show_static_help(NBILL_INSTR_EMAIL_INVOICE_OPTIONS, "email_invoice_option_help"); ?>
            </td>
        </tr>
        <tr id="nbill-admin-tr-title-colour">
            <td class="nbill-setting-caption">
                <?php echo NBILL_CFG_TITLE_COLOUR; ?>
            </td>
            <td class="nbill-setting-value">
                <input type="text" name="title_colour" id="title_colour" value="<?php echo $row->title_colour; ?>" class="color inputbox">
                <?php nbf_html::show_static_help(NBILL_CFG_INSTR_TITLE_COLOUR, "title_colour_help"); ?>
            </td>
        </tr>
        <tr id="nbill-admin-tr-heading-bg-colour">
            <td class="nbill-setting-caption">
                <?php echo NBILL_CFG_HEADING_BG_COLOUR; ?>
            </td>
            <td class="nbill-setting-value">
                <input type="text" name="heading_bg_colour" id="heading_bg_colour" value="<?php echo $row->heading_bg_colour; ?>" class="color inputbox">
                <?php nbf_html::show_static_help(NBILL_CFG_INSTR_HEADING_BG_COLOUR, "heading_bg_colour_help"); ?>
            </td>
        </tr>
        <tr id="nbill-admin-tr-heading-fg-colour">
            <td class="nbill-setting-caption">
                <?php echo NBILL_CFG_HEADING_FG_COLOUR; ?>
            </td>
            <td class="nbill-setting-value">
                <input type="text" name="heading_fg_colour" id="heading_fg_colour" value="<?php echo $row->heading_fg_colour; ?>" class="color inputbox">
                <?php nbf_html::show_static_help(NBILL_CFG_INSTR_HEADING_FG_COLOUR, "heading_fg_colour_help"); ?>
            </td>
        </tr>

        </table>
        </div>
        <?php
        $tab_settings->add_tab_content("basic", ob_get_clean());
        ob_start();
        ?>

        <div class="rounded-table">
        <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
        <tr>
            <th colspan="2"><?php echo NBILL_CONFIG_TITLE; ?></th>
        </tr>

        <tr id="nbill-admin-tr-timezone">
            <td class="nbill-setting-caption">
                <?php echo NBILL_CFG_TIMEZONE; ?>
            </td>
            <td class="nbill-setting-value">
                <input type="text" name="timezone" value="<?php echo $row->timezone; ?>" class="inputbox" />
                <?php nbf_html::show_static_help(NBILL_CFG_INSTR_TIMEZONE, "timezone_help"); ?>
                <br />Current time: <?php $time = new DateTime(); echo $time->format('H:i') . 'h'; ?><br />
                (Timezone: <?php echo nBillConfigurationService::getInstance()->getConfig()->getCurrentTimezone(); ?>)
            </td>
        </tr>

        <?php
        nbf_html::show_admin_setting_textbox($row, "locale", "CFG_");
        nbf_html::show_admin_setting_textbox($row, "precision_decimal", "CFG_", 'numeric');
        nbf_html::show_admin_setting_textbox($row, "precision_quantity", "CFG_", 'numeric');
        nbf_html::show_admin_setting_textbox($row, "precision_tax_rate", "CFG_", 'numeric');
        nbf_html::show_admin_setting_textbox($row, "precision_currency", "CFG_", 'numeric');
        nbf_html::show_admin_setting_textbox($row, "precision_currency_line_total", "CFG_", 'numeric');
        nbf_html::show_admin_setting_textbox($row, "precision_currency_grand_total", "CFG_", 'numeric');
        nbf_html::show_admin_setting_textbox($row, "thousands_separator", "CFG_", 'numeric');
        nbf_html::show_admin_setting_textbox($row, "decimal_separator", "CFG_", 'numeric');
        nbf_html::show_admin_setting_textbox($row, "currency_format", "CFG_");
        nbf_html::show_admin_setting_yes_no($row, "negative_in_brackets", "CFG_");
        nbf_html::show_admin_setting_yes_no($row, "never_hide_quantity", "CFG_");
        ?>

        <tr id="nbill-admin-tr-default-user-group">
            <td class="nbill-setting-caption">
                <?php echo NBILL_CFG_DEFAULT_USER_GROUP; ?>
            </td>
            <td class="nbill-setting-value">
                <?php
                //Create a dropdown of access levels
                $access_list = array();
                $group_id_col = nbf_cms::$interop->cms_database_enum->column_user_group_id;
                $group_name_col = nbf_cms::$interop->cms_database_enum->column_user_group_name;
                $first_level = 0;
                if (count($user_groups) > 0)
                {
                    $first_level = intval($user_groups[0]->level);
                }
                foreach ($user_groups as $user_group)
                {
                    $access_list[] = nbf_html::list_option($user_group->$group_id_col, str_repeat("...", (($user_group->level - $first_level) < 0 ? 0 : ($user_group->level - $first_level))) . $user_group->$group_name_col);
                }
                if (nbf_cms::$interop->multi_user_group)
                {
                    $selected_access = explode(",", $row->default_user_groups);
                }
                else
                {
                    $selected_access = $row->default_user_groups;
                }

                echo nbf_html::select_list($access_list, "default_user_groups" . (nbf_cms::$interop->multi_user_group ? "[]" : ""), 'class="inputbox" id="user_group"' . (nbf_cms::$interop->multi_user_group ? ' multiple="multiple"' : "") . (nbf_cms::$interop->demo_mode ? ' disabled="disabled"' : ''), $selected_access);
                ?>
                <?php nbf_html::show_static_help(NBILL_CFG_INSTR_DEFAULT_USER_GROUP, "default_user_groups_help"); ?>
            </td>
        </tr>
	    <tr id="nbill-admin-tr-list-users">
		    <td class="nbill-setting-caption">
			    <?php echo NBILL_CFG_LIST_USERS; ?>
		    </td>
		    <td class="nbill-setting-value">
			    <?php echo nbf_html::yes_or_no_options("select_users_from_list", "", $row->select_users_from_list); ?>
                <?php nbf_html::show_static_help(NBILL_CFG_INSTR_LIST_USERS, "select_users_from_list_help"); ?>
		    </td>
	    </tr>
        <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
	    <tr id="nbill-admin-tr-cron-token">
		    <td class="nbill-setting-caption">
			    <?php echo NBILL_CFG_CRON_TOKEN; ?>
		    </td>
		    <td class="nbill-setting-value">
			    <input type="text" name="cron_auth_token" value="<?php echo $row->cron_auth_token; ?>" class="inputbox" />
                <?php nbf_html::show_static_help(NBILL_CFG_INSTR_CRON_TOKEN, "cron_auth_token_help"); ?>
		    </td>
	    </tr>
        <?php }
        if (strpos(strtolower(nbf_cms::$interop->cms_name), 'joomla') !== false) { ?>
	        <tr id="nbill-admin-tr-default-menu-item">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_CFG_DEFAULT_MENU_ITEM; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="default_itemid" value="<?php echo $row->default_itemid; ?>" class="inputbox numeric" />
                    <?php nbf_html::show_static_help(NBILL_CFG_INSTR_DEFAULT_MENU_ITEM, "default_itemid_help"); ?>
                </td>
            </tr>
            <tr id="nbill-admin-tr-redirect-to-menu-item">
                <td class="nbill-setting-caption">
                    <?php echo NBILL_CFG_REDIRECT_TO_ITEMID; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php echo nbf_html::yes_or_no_options("redirect_to_itemid", "", $row->redirect_to_itemid); ?>
                    <?php nbf_html::show_static_help(NBILL_CFG_INSTR_REDIRECT_TO_ITEMID, "redirect_to_itemid_help"); ?>
                </td>
            </tr>
        <?php
        }
        //if (!nbf_cms::$interop->hide_ftp_details)
        if (false)
        {
            ?>
            <tr>
                <td colspan="2"><hr /></td>
            </tr>
            <?php
            if (nbf_common::nb_strlen($ftp_message) > 0)
            { ?>
            <tr>
                <td>&nbsp;</td>
                <td><span style="font-weight:bold;color:#<?php echo $ftp_success ? "00cc00" : "dd0000"; ?>"><?php echo $ftp_message; ?></span></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td class="nbill-setting-caption">
                    <a name="ftp_details"></a>
                    <?php echo NBILL_CFG_FTP_ADDRESS; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="ftp_address" value="<?php echo $ftp_address; ?>" />
                    <?php nbf_html::show_static_help(NBILL_CFG_INSTR_FTP_ADDRESS, "ftp_address_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_CFG_FTP_PORT; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="ftp_port" value="<?php echo $ftp_port; ?>" />
                    <?php nbf_html::show_static_help(NBILL_CFG_INSTR_FTP_PORT, "ftp_port_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_CFG_FTP_USERNAME; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="ftp_username" value="<?php echo $ftp_username; ?>" />
                    <?php nbf_html::show_static_help(NBILL_CFG_INSTR_FTP_USERNAME, "ftp_username_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_CFG_FTP_PASSWORD; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="password" name="ftp_password" value="<?php echo $ftp_password; ?>" />
                    <?php nbf_html::show_static_help(NBILL_CFG_INSTR_FTP_PASSWORD, "ftp_password_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_CFG_FTP_ROOT; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="ftp_root" value="<?php echo $ftp_root; ?>" />
                    <?php nbf_html::show_static_help(NBILL_CFG_INSTR_FTP_ROOT, "ftp_root_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">&nbsp;</td>
                <td class="nbill-setting-value"><input type="submit" name="test_ftp" value="<?php echo NBILL_CFG_TEST_FTP; ?>" /></td>
            </tr>
        <?php } ?>
        <tr>
            <td colspan="2"><hr /></td>
        </tr>
	    <tr id="nbill-admin-tr-version-auto-check">
		    <td class="nbill-setting-caption">
			    <?php echo NBILL_CFG_VERSION_AUTO_CHECK; ?>
		    </td>
		    <td class="nbill-setting-value">
			    <?php echo nbf_html::yes_or_no_options("version_auto_check", "", $row->version_auto_check); ?>
                <?php nbf_html::show_static_help(NBILL_CFG_INSTR_VERSION_AUTO_CHECK, "version_auto_check_help"); ?>
		    </td>
	    </tr>
	    <!--<tr id="nbill-admin-tr-auto-update">
		    <td class="nbill-setting-caption">
			    <?php echo NBILL_CFG_AUTO_UPDATE; ?>
		    </td>
		    <td class="nbill-setting-value">
			    <?php echo nbf_html::yes_or_no_options("auto_update", "", $row->auto_update); ?>
                <?php nbf_html::show_static_help(NBILL_CFG_INSTR_AUTO_UPDATE, "auto_update_help"); ?>
		    </td>
	    </tr>-->
	    <tr id="nbill-admin-tr-version-check">
		    <td class="nbill-setting-caption">
			    <?php echo NBILL_CFG_CHECK_VERSION; ?>
		    </td>
		    <td class="nbill-setting-value">
			    <input type="button" class="button btn" name="check_version" id="check_version"<?php echo nbf_cms::$interop->demo_mode ? " disabled=\"disabled\"" : ""; ?>  value="<?php echo NBILL_CFG_BTN_CHECK_VERSION; ?>" onclick="nbill_submit_task('check_version');" />
                <?php nbf_html::show_static_help(NBILL_CFG_INSTR_CHECK_VERSION, "check_version_help"); ?>
		    </td>
	    </tr>
	    <!--<tr id="nbill-admin-tr-update-now">
		    <td class="nbill-setting-caption">
			    <?php echo NBILL_CFG_UPDATE_NOW; ?>
		    </td>
		    <td class="nbill-setting-value">
			    <input type="button" class="button btn" name="update_now" id="update_now"<?php echo nbf_cms::$interop->demo_mode ? " disabled=\"disabled\"" : ""; ?>  value="<?php echo NBILL_CFG_BTN_UPDATE_NOW; ?>" onclick="nbill_submit_task('update_now');" />
                <?php nbf_html::show_static_help(NBILL_CFG_INSTR_UPDATE_NOW, "update_now_help"); ?>
		    </td>
	    </tr>-->
        <?php nbf_html::show_admin_setting_yes_no($row, "auto_check_eu_vat_rates", "CFG_"); ?>
        <?php nbf_html::show_admin_setting_textbox($row, "api_url_eu_vat_rates", "CFG_", '', true); ?>
        <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
        <?php nbf_html::show_admin_setting_yes_no($row, "geo_ip_lookup", "CFG_"); ?>
        <?php nbf_html::show_admin_setting_textbox($row, "api_url_geo_ip", "CFG_", '', true); ?>
        <?php nbf_html::show_admin_setting_yes_no($row, "geo_ip_fail_on_mismatch", "CFG_"); ?>
        <?php } ?>
	    <tr>
		    <td colspan="2"><hr /></td>
	    </tr>
	    <tr id="nbill-admin-tr-database-functions">
		    <td class="nbill-setting-caption">
			    <?php echo NBILL_CFG_DATABASE_FUNCTIONS; ?>
		    </td>
		    <td class="nbill-setting-value">
			    <input type="button" class="button btn" name="cleartables" id="cleartables" value="<?php echo NBILL_CFG_CLEAR_TABLES; ?>" <?php if (nbf_cms::$interop->demo_mode) {echo "disabled=\"disabled\" ";} ?>onclick="clear_tables();" />
			    <br />
			    <input type="button" class="button btn" name="deletetables" id="deletetables" value="<?php echo NBILL_CFG_DELETE_TABLES; ?>" <?php if (nbf_cms::$interop->demo_mode) {echo "disabled=\"disabled\" ";} ?>onclick="delete_tables();" />
                <?php
                if ($offer_migration)
                { ?>
                    <input type="button" class="button btn" name="migrate_1_2" id="migrate_1_2" value="<?php echo NBILL_CFG_MIGRATE_1_2; ?>" <?php if (nbf_cms::$interop->demo_mode) {echo "disabled=\"disabled\" ";} ?>onclick="migrate_data();" />
                <?php }
                ?>
                <?php nbf_html::show_static_help(strtolower(nbf_version::$suffix) == 'lite' ? NBILL_CFG_INSTR_DATABASE_FUNCTIONS_LITE : NBILL_CFG_INSTR_DATABASE_FUNCTIONS, "database_functions_help"); ?>
		    </td>
	    </tr>
        <?php
        nbf_html::show_admin_setting_yes_no($row, "use_legacy_document_editor", "CFG_");
        nbf_html::show_admin_setting_yes_no($row, "edit_products_in_documents", "CFG_");
         ?>
	    </table>
        </div>
        <?php
        $tab_settings->add_tab_content("advanced", ob_get_clean());
        $tab_settings->end_tab_group();
        ?>

	    </form>
	    <?php
        if (nbf_common::get_param($_REQUEST, 'task') == 'auto_migrate')
        {
            //We have already confirmed - just do it!
            ?>
            <script type="text/javascript">
                setTimeout('do_migrate(\'<?php echo NBILL_CFG_MIGRATE_DATA; ?>\')', 250);
            </script>
            <?php
        }
        if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST, 'ftp_message')) > 0)
        {
            ?>
            <script type="text/javascript">
            alert('<?php echo urldecode(nbf_common::get_param($_REQUEST, 'ftp_message')); ?>');
            </script>
            <?php
        }
    }

    
}