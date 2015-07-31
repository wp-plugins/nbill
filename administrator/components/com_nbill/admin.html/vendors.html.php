<?php
/**
* HTML output for vendors
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillVendors
{
	public static function showVendors($rows, $pagination)
	{
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "vendors"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_VENDORS_TITLE; ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		} ?>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
		<p align="left"><?php echo NBILL_VENDOR_INTRO; ?></p>

        <div class="rounded-table">
            <table class="adminlist">
            <tr class="nbill-admin-title-row">
                <th class="selector">
			    #
			    </th>
                <th class="selector">
                    <input type="checkbox" name="check_all" value="" onclick="for(var i=0; i<<?php echo count($rows); ?>;i++) {document.getElementById('cb' + i).checked=this.checked;} document.adminForm.box_checked.value=this.checked;" />
			    </th>
			    <th class="title">
				    <?php echo NBILL_VENDOR_NAME; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_VENDOR_COUNTRY; ?>
			    </th>
			    <th class="title">
				    <?php echo NBILL_NEXT_NBILL_NO; ?>
			    </th>
		    </tr>
		    <?php
			    for ($i=0, $n=count( $rows ); $i < $n; $i++)
			    {
				    $row = &$rows[$i];
				    $link = nbf_cms::$interop->admin_page_prefix . "&action=vendors&task=edit&cid=$row->id";
				    echo "<tr>";
				    echo "<td class=\"selector\">";
				    echo $pagination->list_offset + $i + 1;
				    $checked = nbf_html::id_checkbox($i, $row->id);
				    echo "</td><td class=\"selector\">$checked</td>";
				    echo "<td class=\"list-value\"><a href=\"$link\" title=\"" . NBILL_EDIT_VENDOR . "\">" . $row->vendor_name . "</a></td>";
				    echo "<td class=\"list-value\">" . $row->vendor_country . "</td>";
				    echo "<td class=\"list-value\">" . $row->next_invoice_no . "</td>";
				    echo "</tr>";
			    }
		    ?>
		    <tr class="nbill_tr_no_highlight"><td colspan="5" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
		    </table>
        </div>

		<input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
		<input type="hidden" name="action" value="vendors" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="box_checked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0">
		</form>
		<?php
	}

	/**
	* Edit a vendor (or create a new one)
	*/
	public static function editVendor($vendor_id, $row, $country_codes, $currency_codes, $templates, $email_templates, $gateways, $use_posted_values = false, $master_connect = false, $master_vendors = array(), $test_connection = false)
	{
		nbf_cms::$interop->init_editor();
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.client.php");
		?>
		<script language="javascript" type="text/javascript">
		function nbill_submit_task(task_name)
		{
			var form = document.adminForm;
			if (task_name == 'cancel')
            {
				document.adminForm.task.value=task_name;
                document.adminForm.submit();
				return;
			}

			// do field validation
			if (form.vendor_name.value == "")
			{
				alert('<?php echo NBILL_VENDOR_NAME_REQUIRED; ?>');
			}
			else if (form.vendor_address.value == "")
			{
				alert('<?php echo NBILL_VENDOR_ADDRESS_REQUIRED; ?>');
			}
			else if (form.vendor_country.value == "")
			{
				alert('<?php echo NBILL_VENDOR_COUNTRY_REQUIRED; ?>');
			}
			else if (form.vendor_currency.value == "")
			{
				alert('<?php echo NBILL_VENDOR_CURRENCY_REQUIRED; ?>');
			}
			else if (form.admin_email == "")
			{
				alert('<?php echo NBILL_VENDOR_EMAIL_REQUIRED; ?>');
			}
			else
			{
				document.adminForm.task.value=task_name;
                document.adminForm.submit();
			}
		}

        //Need a few globals for asynchronous processing of master db update
        var abort = false;
        var maxdate = new Array();
        var records_inserted = 0;
        var records_updated = 0;
        var record_pointer = 0;
        var percentage = 0;
        var complete = 0;
        var message = '';
        var task_name = 'start';

        <?php
        echo get_prompt_js();
        ?>
        function sync_click()
        {
            if (document.getElementById('use_master_db1').checked)
            {
                if (confirm('<?php echo NBILL_SYNC_ARE_YOU_SURE; ?>'))
                {
                    IEprompt('<?php echo NBILL_SYNC_UP_TO;?>', '<?php echo nbf_common::nb_date("Y/m/d", nbf_common::nb_time()); ?>');
                }
            }
            else
            {
                alert('<?php echo str_replace("'", "\\'", NBILL_MASTER_DB_NOT_IN_USE); ?>');
            }
        }

        function promptCallback(date_entered)
        {
            records_inserted = 0;
            records_updated = 0;
            percentage = 0;
            record_pointer = 0;
            maxdate = date_entered.split('/');
            do_vendor_sync('<?php echo NBILL_SYNC_VENDOR; ?>');
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
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "vendors"); ?>>
				<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
				<?php echo $row->id ? NBILL_EDIT_VENDOR . " '$row->vendor_name'" : NBILL_NEW_VENDOR; ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>\n";
		} ?>
        <div id="vendor_sync_retry" style="display:none;" class="nbill-message"></div>
		<form enctype="multipart/form-data" action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="MAX_FILE_SIZE" value="30720" />
		<input type="hidden" name="id" value="<?php echo $vendor_id;?>" />
		<?php nbf_html::add_filters(); ?>

        <?php
        $tab_settings = new nbf_tab_group();
        $tab_settings->start_tab_group("admin_settings");
        $tab_settings->add_tab_title("basic", NBILL_ADMIN_TAB_BASIC);
        $tab_settings->add_tab_title("advanced", NBILL_ADMIN_TAB_ADVANCED);
        ob_start();
        ?>

        <div class="rounded-table">
		<table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-vendor">
        <?php if ($row->invoice_no_locked) { ?>
        <tr>
            <td colspan="2">
                <br /><strong>
                <?php echo NBILL_INVOICE_NO_LOCKED; ?></strong>&nbsp;<input type="submit" name="unlock-invoice-no" onclick="nbill_submit_task('unlock-invoice');return false;" value="<?php echo NBILL_UNLOCK; ?>" />
            </td>
        </tr>
        <?php } ?>
        <?php if ($row->order_no_locked) { ?>
        <tr>
            <td colspan="2">
                <br /><strong>
                <?php echo NBILL_ORDER_NO_LOCKED; ?></strong>&nbsp;<input type="submit" name="unlock-order-no" onclick="nbill_submit_task('unlock-order');return false;" value="<?php echo NBILL_UNLOCK; ?>" />
            </td>
        </tr>
        <?php } ?>
        <?php if ($row->receipt_no_locked) { ?>
        <tr>
            <td colspan="2">
                <br /><strong>
                <?php echo NBILL_RECEIPT_NO_LOCKED; ?></strong>&nbsp;<input type="submit" name="unlock-receipt-no" onclick="nbill_submit_task('unlock-receipt');return false;" value="<?php echo NBILL_UNLOCK; ?>" />
            </td>
        </tr>
        <?php } ?>
        <?php if ($row->payment_no_locked) { ?>
        <tr>
            <td colspan="2">
                <br /><strong>
                <?php echo NBILL_PAYMENT_NO_LOCKED; ?></strong>&nbsp;<input type="submit" name="unlock-payment-no" onclick="nbill_submit_task('unlock-payment');return false;" value="<?php echo NBILL_UNLOCK; ?>" />
            </td>
        </tr>
        <?php } ?>
        <?php if ($row->credit_no_locked) { ?>
        <tr>
            <td colspan="2">
                <br /><strong>
                <?php echo NBILL_CREDIT_NO_LOCKED; ?></strong>&nbsp;<input type="submit" name="unlock-credit-no" onclick="nbill_submit_task('unlock-credit');return false;" value="<?php echo NBILL_UNLOCK; ?>" />
            </td>
        </tr>
        <?php } ?>
        <?php if ($row->quote_no_locked) { ?>
        <tr>
            <td colspan="2">
                <br /><strong>
                <?php echo NBILL_QUOTE_NO_LOCKED; ?></strong>&nbsp;<input type="submit" name="unlock-quote-no" onclick="nbill_submit_task('unlock-quote');return false;" value="<?php echo NBILL_UNLOCK; ?>" />
            </td>
        </tr>
        <?php } ?>
		<tr>
			<th colspan="2"><?php echo NBILL_VENDOR_DETAILS; ?></th>
		</tr>
		<tr id="nbill-admin-tr-vendor-name">
			<td class="nbill-setting-caption">
				<?php echo NBILL_VENDOR_NAME; ?>
			</td>
			<td class="nbill-setting-value">
				<input type="text" name="vendor_name" id="vendor_name" value="<?php echo $use_posted_values ? str_replace("\"", "&quot;", nbf_common::get_param($_POST,'vendor_name', null, true)) : str_replace("\"", "&quot;", $row->vendor_name); ?>" class="inputbox" style="width:160px" />
                <?php nbf_html::show_static_help(NBILL_INSTR_VENDOR_NAME, "vendor_name_help"); ?>
			</td>
		</tr>
        <!-- Custom Fields Placeholder -->
        <tr id="nbill-admin-tr-admin-email">
            <td class="nbill-setting-caption">
                <?php echo NBILL_ADMIN_EMAIL; ?>
            </td>
            <td class="nbill-setting-value">
                <input type="text" name="admin_email" id="admin_email" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'admin_email', null, true) : $row->admin_email; ?>" class="inputbox" style="width:160px" />
                <?php nbf_html::show_static_help(NBILL_INSTR_ADMIN_EMAIL, "admin_email_help"); ?>
            </td>
        </tr>

		<tr id="nbill-admin-tr-vendor-logo">
			<td class="nbill-setting-caption">
				<?php echo NBILL_VENDOR_LOGO; ?>
			</td>
			<td class="nbill-setting-value">
				<input type="file" name="vendor_logo" id="vendor_logo" value="" class="inputbox" style="width:160px"<?php if (nbf_cms::$interop->demo_mode) {echo ' disabled="disabled"';} ?> />
				<?php
                $vendor_logo_file = nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . $vendor_id . ".png";
                if (!file_exists($vendor_logo_file))
                {
                    $vendor_logo_file = nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . $vendor_id . ".gif";
                }
				if (file_exists($vendor_logo_file))
				{
					//Have to use a table below because DIVs do not autosize according to their content
					?>
					<input type="hidden" name="delete_vendor_logo" id="delete_vendor_logo" value="" />
					<br /><table cellpadding="0" cellspacing="0" border="0"><tr><td style="padding: 5px; border:solid 1px #dddddd; background-color: #ffffff;"><img id="logo_preview" src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/vendors/<?php echo basename($vendor_logo_file); ?>" alt="<?php echo NBILL_VENDOR_LOGO; ?>" /></td><td><input type="button" name="delete_logo" id="delete_logo" class="button btn" value="<?php echo NBILL_DELETE_LOGO; ?>" onclick="document.getElementById('logo_preview').style.display='none';document.getElementById('delete_logo').style.display='none';document.getElementById('delete_vendor_logo').value=1;"<?php if (nbf_cms::$interop->demo_mode) {echo ' disabled="disabled"';} ?> /></td></tr></table>
				<?php }
				?>
                <?php nbf_html::show_static_help(NBILL_INSTR_VENDOR_LOGO, "vendor_logo_help"); ?>
			</td>
		</tr>
		<tr id="nbill-admin-tr-vendor-address">
			<td class="nbill-setting-caption">
				<?php echo NBILL_VENDOR_ADDRESS; ?>
			</td>
			<td class="nbill-setting-value">
				<textarea name="vendor_address" id="vendor_address" class="inputbox" rows="4" cols="20"><?php echo $use_posted_values ? nbf_common::get_param($_POST,'vendor_address', "", true) : trim($row->vendor_address); ?></textarea>
                <?php nbf_html::show_static_help(NBILL_INSTR_VENDOR_ADDRESS, "vendor_address_help"); ?>
			</td>
		</tr>
		<tr id="nbill-admin-tr-vendor-country">
			<td class="nbill-setting-caption">
				<?php echo NBILL_VENDOR_COUNTRY; ?>
			</td>
			<td class="nbill-setting-value">
				<?php
					$vendor_country = array();
					foreach ($country_codes as $country_code)
					{
						$vendor_country[] = nbf_html::list_option($country_code['code'], $country_code['description']);
					}
					echo nbf_html::select_list($vendor_country, "vendor_country", 'class="inputbox" id="vendor_country"', $use_posted_values ? nbf_common::get_param($_POST,'vendor_country', null, true) : $row->vendor_country );
				?>
                <?php nbf_html::show_static_help(NBILL_INSTR_VENDOR_COUNTRY, "vendor_country_help"); ?>
			</td>
		</tr>
		<tr id="nbill-admin-tr-vendor-currency">
			<td class="nbill-setting-caption">
				<?php echo NBILL_VENDOR_CURRENCY; ?>
			</td>
			<td class="nbill-setting-value">
				<?php
					$vendor_currency = array();
					foreach ($currency_codes as $currency_code)
					{
						$vendor_currency[] = nbf_html::list_option($currency_code['code'], $currency_code['description']);
					}
					echo nbf_html::select_list($vendor_currency, "vendor_currency", 'id="vendor_currency" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST,'vendor_currency', null, true) : $row->vendor_currency );
				?>
                <?php nbf_html::show_static_help(NBILL_INSTR_VENDOR_CURRENCY, "vendor_currency_help"); ?>
			</td>
		</tr>
		<tr id="nbill-admin-tr-default-vendor">
			<td class="nbill-setting-caption">
				<?php echo NBILL_VENDOR_DEFAULT; ?>
			</td>
			<td class="nbill-setting-value">
				<?php echo nbf_html::yes_or_no_options("default_vendor", "", $use_posted_values ? nbf_common::get_param($_POST, 'default_vendor', null, true) : $row->default_vendor); ?>
                <?php nbf_html::show_static_help(NBILL_INSTR_VENDOR_DEFAULT, "default_vendor_help"); ?>
			</td>
		</tr>

        <tr id="nbill-admin-tr-tax-reference-no">
            <td class="nbill-setting-caption">
                <?php echo NBILL_TAX_REFERENCE_NO; ?>
            </td>
            <td class="nbill-setting-value">
                <input type="text" name="tax_reference_no" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST, 'tax_reference_no', null, true) : $row->tax_reference_no; ?>" class="inputbox" />
                <?php nbf_html::show_static_help(NBILL_INSTR_TAX_REFERENCE_NO, "tax_reference_no_help"); ?>
            </td>
        </tr>

        <tr id="nbill-admin-tr-default-gateway">
            <td class="nbill-setting-caption">
                <?php echo NBILL_DEFAULT_GATEWAY; ?>
            </td>
            <td class="nbill-setting-value">
                <?php
                    $gateway_list = array();
                    $gateway_list[] = nbf_html::list_option(-1, NBILL_NOT_APPLICABLE);
                    foreach ($gateways as $gateway)
                    {
                        $gateway_list[] = nbf_html::list_option($gateway->gateway_id, $gateway->display_name);
                    }
                    echo nbf_html::select_list($gateway_list, "default_gateway", 'id="default_gateway" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST,'default_gateway', null, true) : $row->default_gateway );
                ?>
                <?php nbf_html::show_static_help(NBILL_INSTR_DEFAULT_GATEWAY, "default_gateway_help"); ?>
            </td>
        </tr>
		<tr id="nbill-admin-tr-next-invoice-no">
			<td class="nbill-setting-caption">
				<?php echo NBILL_NEXT_NBILL_NO; ?>
			</td>
			<td class="nbill-setting-value">
				<?php $nextno = $use_posted_values ? nbf_common::get_param($_POST,'next_invoice_no', null, true) : $row->next_invoice_no;
				if (nbf_common::nb_strlen($nextno) == 0)
				{
					$nextno = "0001";
				}?>
				<input type="text" name="next_invoice_no" value="<?php echo $nextno; ?>" class="inputbox" />
                <input type="hidden" name="next_invoice_no_orig" value="<?php echo $nextno; ?>" />
                <?php nbf_html::show_static_help(NBILL_INSTR_NEXT_INVOICE_NO, "next_invoice_no_help"); ?>
			</td>
		</tr>
        <?php  ?>
		<tr id="nbill-admin-tr-next-receipt-no">
			<td class="nbill-setting-caption">
				<?php echo NBILL_NEXT_RECEIPT_NO; ?>
			</td>
			<td class="nbill-setting-value">
				<?php $nextno = $use_posted_values ? nbf_common::get_param($_POST,'next_receipt_no', null, true) : $row->next_receipt_no;
				if (nbf_common::nb_strlen($nextno) == 0)
				{
					$nextno = "0001";
				}?>
				<input type="text" name="next_receipt_no" value="<?php echo $nextno; ?>" class="inputbox" />
                <input type="hidden" name="next_receipt_no_orig" value="<?php echo $nextno; ?>" />
                <?php nbf_html::show_static_help(NBILL_INSTR_NEXT_RECEIPT_NO, "next_receipt_no_help"); ?>
			</td>
		</tr>
        <?php  ?>
        <tr id="nbill-admin-tr-next-credit-no">
			<td class="nbill-setting-caption">
				<?php echo NBILL_NEXT_CREDIT_NO; ?>
			</td>
			<td class="nbill-setting-value">
				<?php $nextno = $use_posted_values ? nbf_common::get_param($_POST,'next_credit_no', null, true) : $row->next_credit_no;
				if (nbf_common::nb_strlen($nextno) == 0)
				{
					$nextno = "CR-0001";
				}?>
				<input type="text" name="next_credit_no" value="<?php echo $nextno; ?>" class="inputbox" />
                <input type="hidden" name="next_credit_no_orig" value="<?php echo $nextno; ?>" />
                <?php nbf_html::show_static_help(NBILL_INSTR_NEXT_CREDIT_NO, "next_credit_no_help"); ?>
			</td>
		</tr>
        <?php  ?>
        </table>
        </div>
        <?php
        $tab_settings->add_tab_content("basic", ob_get_clean());
        ob_start();
        ?>

        <div class="rounded-table">
        <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform" id="nbill-admin-table-vendors-advanced">
        <tr>
            <th colspan="2"><?php echo NBILL_VENDOR_DETAILS; ?></th>
        </tr>
        <tr id="nbill-admin-tr-vendor-templates">
            <td colspan="2">
                <div style="padding: 5px; border: solid 1px #999999; background-color:#fff;margin:3px;">
                    <strong><?php echo NBILL_TEMPLATES_TITLE; ?></strong>
                    <p><?php echo sprintf(NBILL_TEMPLATES_INTRO, '<span class="word-breakable inline-block">' . nbf_cms::$interop->nbill_fe_base_path . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . '</span>', '<span class="word-breakable inline-block">' . nbf_cms::$interop->nbill_fe_base_path . DIRECTORY_SEPARATOR . "email_templates" . DIRECTORY_SEPARATOR) . '</span>'; ?></p>
                    <table cellpadding="3" cellspacing="0" border="0" id="nbill-admin-table-vendor-templates">
                        <tr id="nbill-admin-tr-invoice-template">
                            <td class="nbill-setting-caption">
                                <?php echo NBILL_INVOICE_TEMPLATE; ?>
                            </td>
                            <td class="nbill-setting-value">
                                <?php
                                    $template_list = array();
                                    foreach ($templates as $template)
                                    {
                                        if (substr($template, 0, 7) != 'credit_' && substr($template, 0, 6) != 'quote_' && substr($template, 0, 9) != 'delivery_')
                                        {
                                            $template_list[] = nbf_html::list_option($template, $template);
                                        }
                                    }
                                    echo nbf_html::select_list($template_list, "invoice_template_name", 'id="invoice_template_name" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST,'invoice_template_name', null, true) : $row->invoice_template_name );
                                ?>
                                <?php nbf_html::show_static_help(NBILL_INSTR_INVOICE_TEMPLATE, "invoice_template_name_help"); ?>
                            </td>
                        </tr>
                        <tr id="nbill-admin-tr-email-template">
                            <td class="nbill-setting-caption">
                                <?php echo NBILL_INVOICE_EMAIL_TEMPLATE;
                                $email_template_list = array();
                                foreach ($email_templates as $email_template)
                                {
                                    if (substr($email_template, 0, 13) != 'credit_email_'
                                        && substr($email_template, 0, 12) != 'order_email_'
                                        && substr($email_template, 0, 14) != 'pending_email_'
                                        && substr($email_template, 0, 12) != 'quote_email_'
                                        && substr($email_template, 0, 20) != 'quote_request_email_')
                                    {
                                        $email_template_list[] = nbf_html::list_option($email_template, $email_template);
                                    }
                                }
                                ?>
                            </td>
                            <td class="nbill-setting-value">
                                <?php echo nbf_html::select_list($email_template_list, "invoice_email_template_name", 'id="invoice_email_template_name" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST,'invoice_email_template_name', null, true) : $row->invoice_email_template_name ); ?>
                                <?php nbf_html::show_static_help(NBILL_INSTR_INVOICE_EMAIL_TEMPLATE, "invoice_email_template_name_help"); ?>
                            </td>
                        </tr>
                        <tr id="nbill-admin-tr-credit-template">
                            <td class="nbill-setting-caption">
                                <?php echo NBILL_CREDIT_TEMPLATE; ?>
                            </td>
                            <td class="nbill-setting-value">
                                <?php
                                $template_list = array();
                                foreach ($templates as $template)
                                {
                                    if (substr($template, 0, 8) != 'invoice_' && substr($template, 0, 6) != 'quote_' && substr($template, 0, 9) != 'delivery_')
                                    {
                                        $template_list[] = nbf_html::list_option($template, $template);
                                    }
                                }
                                echo nbf_html::select_list($template_list, "credit_template_name", 'id="credit_template_name" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST,'credit_template_name', null, true) : $row->credit_template_name);
                                ?>
                                <?php nbf_html::show_static_help(NBILL_INSTR_CREDIT_TEMPLATE, "credit_template_name_help"); ?>
                            </td>
                        </tr>
                        <tr id="nbill-admin-tr-credit-email-template">
                            <td class="nbill-setting-caption">
                                <?php echo NBILL_CREDIT_EMAIL_TEMPLATE; ?>
                            </td>
                            <td class="nbill-setting-value">
                                <?php
                                $email_template_list = array();
                                $email_template_list[] = nbf_html::list_option("", NBILL_NOT_APPLICABLE);
                                foreach ($email_templates as $email_template)
                                {
                                    if (substr($email_template, 0, 14) != 'invoice_email_'
                                        && substr($email_template, 0, 12) != 'order_email_'
                                        && substr($email_template, 0, 14) != 'pending_email_'
                                        && substr($email_template, 0, 12) != 'quote_email_'
                                        && substr($email_template, 0, 20) != 'quote_request_email_')
                                    {
                                        $email_template_list[] = nbf_html::list_option($email_template, $email_template);
                                    }
                                }
                                echo nbf_html::select_list($email_template_list, "credit_email_template_name", 'id="credit_email_template_name" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST,'credit_email_template_name', null, true) : $row->credit_email_template_name);
                                ?>
                                <?php nbf_html::show_static_help(NBILL_INSTR_CREDIT_EMAIL_TEMPLATE, "credit_email_template_name_help"); ?>
                            </td>
                        </tr>
                        <?php  ?>
                        <tr id="nbill-admin-tr-delivery-template">
                            <td class="nbill-setting-caption">
                                <?php echo NBILL_DELIVERY_TEMPLATE; ?>
                            </td>
                            <td class="nbill-setting-value">
                                <?php
                                    $template_list = array();
                                    foreach ($templates as $template)
                                    {
                                        if (substr($template, 0, 7) != 'credit_' && substr($template, 0, 6) != 'quote_' && substr($template, 0, 8) != 'invoice_')
                                        {
                                            $template_list[] = nbf_html::list_option($template, $template);
                                        }
                                    }
                                    echo nbf_html::select_list($template_list, "delivery_template_name", 'id="delivery_template_name" class="inputbox"', $use_posted_values ? nbf_common::get_param($_POST,'delivery_template_name', null, true) : $row->delivery_template_name );
                                ?>
                                <?php nbf_html::show_static_help(NBILL_INSTR_DELIVERY_TEMPLATE, "invoice_delivery_name_help"); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        <tr id="nbill-admin-tr-add-remittance">
            <td class="nbill-setting-caption">
                <?php echo NBILL_ADD_REMITTANCE; ?>
            </td>
            <td class="nbill-setting-value">
                <?php echo nbf_html::yes_or_no_options("show_remittance", "", $use_posted_values ? nbf_common::get_param($_POST, 'show_remittance', null, true) : $row->show_remittance); ?>
                <?php nbf_html::show_static_help(NBILL_INSTR_ADD_REMITTANCE, "show_remittance_help"); ?>
            </td>
        </tr>
        <tr id="nbill-admin-tr-add-paylink">
            <td class="nbill-setting-caption">
                <?php echo NBILL_ADD_PAYLINK; ?>
            </td>
            <td class="nbill-setting-value">
                <?php echo nbf_html::yes_or_no_options("show_paylink", "", $use_posted_values ? nbf_common::get_param($_POST, 'show_paylink', null, true) : $row->show_paylink); ?>
                <?php nbf_html::show_static_help(NBILL_INSTR_ADD_PAYLINK, "show_paylink_help"); ?>
            </td>
        </tr>
        <tr id="nbill-admin-tr-paper-size">
            <td class="nbill-setting-caption">
                <?php echo NBILL_PAPER_SIZE; ?>
            </td>
            <td class="nbill-setting-value">
                <input type="text" name="paper_size" value="<?php echo $use_posted_values ? nbf_common::get_param($_POST,'paper_size', null, true) : $row->paper_size; ?>" class="inputbox" style="width:160px" />
                <?php nbf_html::show_static_help(NBILL_INSTR_PAPER_SIZE, "paper_size_help"); ?>
            </td>
        </tr>
        <tr id="nbill-admin-tr-auto-create-income">
            <td class="nbill-setting-caption">
                <?php echo NBILL_AUTO_CREATE_INCOME; ?>
            </td>
            <td class="nbill-setting-value">
                <?php echo nbf_html::yes_or_no_options("auto_create_income", "", $use_posted_values ? nbf_common::get_param($_POST, 'auto_create_income', null, true) : $row->auto_create_income); ?>
                <?php nbf_html::show_static_help(NBILL_INSTR_AUTO_CREATE_INCOME, "auto_create_income_help"); ?>
            </td>
        </tr>
        <?php  ?>
		<tr id="nbill-admin-tr-default-pay-instr">
			<td class="nbill-setting-caption">
				<?php echo NBILL_DEFAULT_PAYMENT_INSTR; ?>
			</td>
			<td class="nbill-setting-value">
				<?php echo nbf_cms::$interop->render_editor("payment_instructions", "editor1", $use_posted_values ? nbf_common::get_param($_POST,'payment_instructions', null, true) : $row->payment_instructions); ?>
				<?php nbf_html::show_static_help(NBILL_INSTR_DEFAULT_PAYMENT_INSTR, "payment_instructions_help"); ?>
			</td>
		</tr>
        <tr id="nbill-admin-tr-invoice-pay-instr">
            <td class="nbill-setting-caption">
                <?php echo NBILL_INVOICE_PAY_INST; ?>
            </td>
            <td class="nbill-setting-value">
                <?php echo nbf_cms::$interop->render_editor("invoice_offline_pay_inst", "editor_invoice_payinst", $use_posted_values ? nbf_common::get_param($_POST,'invoice_offline_pay_inst', null, true) : $row->invoice_offline_pay_inst); ?>
                <?php nbf_html::show_static_help(NBILL_INSTR_INVOICE_PAY_INST, "invoice_offline_pay_inst_help"); ?>
            </td>
        </tr>
        <?php  ?>
		<tr id="nbill-admin-tr-default-small-print">
			<td class="nbill-setting-caption">
				<?php echo NBILL_DEFAULT_SMALL_PRINT; ?>
			</td>
			<td class="nbill-setting-value">
				<?php echo nbf_cms::$interop->render_editor("small_print", "editor2", $use_posted_values ? nbf_common::get_param($_POST,'small_print', null, true) : $row->small_print); ?>
                <?php nbf_html::show_static_help(NBILL_INSTR_DEFAULT_SMALL_PRINT, "small_print_help"); ?>
			</td>
		</tr>
		<tr id="nbill-admin-tr-credit-small-print">
			<td class="nbill-setting-caption">
				<?php echo NBILL_CREDIT_SMALL_PRINT; ?>
			</td>
			<td class="nbill-setting-value">
                <?php echo nbf_cms::$interop->render_editor("credit_small_print", "editor3", $use_posted_values ? nbf_common::get_param($_POST,'credit_small_print', null, true) : $row->credit_small_print); ?>
                <?php nbf_html::show_static_help(NBILL_INSTR_INVOICE_SMALL_PRINT_CR, "credit_small_print_help"); ?>
			</td>
		</tr>
        <?php  ?>
        <tr id="nbill-admin-tr-delivery-small-print">
            <td class="nbill-setting-caption">
                <?php echo NBILL_DELIVERY_SMALL_PRINT; ?>
            </td>
            <td class="nbill-setting-value">
                <?php echo nbf_cms::$interop->render_editor("delivery_small_print", "editor5", $use_posted_values ? nbf_common::get_param($_POST,'delivery_small_print', null, true) : $row->delivery_small_print); ?>
                <?php nbf_html::show_static_help(NBILL_INSTR_INVOICE_SMALL_PRINT_DE, "delivery_small_print_help"); ?>
            </td>
        </tr>
        <?php  ?>
        </table>
        </div>

		<br />
        <?php
        

        $tab_settings->add_tab_content("advanced", ob_get_clean());
        $tab_settings->end_tab_group();
        ?>
        <a name="nbill_master_db" />
		<input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
		<input type="hidden" name="action" value="vendors" />
		<input type="hidden" name="task" value="edit" />
		<input type="hidden" name="box_checked" value="0" />
		<input type="hidden" name="hidemainmenu" value="0">

		</form>
		<?php
	}
}