<?php
/**
* HTML output for nBill administrator home page
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillMain
{
    public static function main($offer_migration = false, $ledger_guesses = null)
	{
        //Show home page
        nbf_cms::$interop->add_html_header("<link rel=\"stylesheet\" href=\"" . nbf_cms::$interop->nbill_site_url_path . "/style/admin/home.css\" type=\"text/css\" />");
		$date_format = nbf_common::get_date_format();
		$img_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/large";

        
		?>
		<div class="nbill-help-link">
            <a href="http://<?php echo NBILL_BRANDING_DOCUMENTATION ?>" target="_blank">
                <img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/medium/help.gif" alt="<?php echo NBILL_HELP; ?>" border="0" />
                <?php echo NBILL_HELP; ?>
            </a>
        </div>
        <table class="adminheading" style="width:auto;">
		<tr>
			<th valign="bottom" style="background-image: url('<?php echo $img_path; ?>/logo_full_small.gif');background-repeat:no-repeat;width:100%;height:69px;padding-left:130px;">
				<span class="main-dashboard-version">v <?php echo nbf_version::$nbill_version_no; ?></span>
                <span class="main-dashboard-text"><?php echo NBILL_MAIN_DASHBOARD; ?></span>
                <span class="main-dashboard-config">
                    <a href="javascript:void(0);" onclick="showBox('<?php echo nbf_cms::$interop->admin_popup_page_prefix; ?>&hide_billing_menu=1&action=widgets&task=main_config', extract_and_execute_js);return false;" title="<?php echo NBILL_WIDGETS_DASHBOARD_CONFIG; ?>">
                        <img border="0" src="<?php echo nbf_cms::$interop->nbill_site_url_path ?>/images/widget_config.png" alt="<?php echo NBILL_WIDGETS_DASHBOARD_CONFIG; ?>" />
                    </a>
                </span>
			</th>
		</tr>

		<?php
        if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<tr><td align=\"left\"><div class=\"nbill-message\">" . nbf_globals::$message . "</div></td></tr>";
		}
        $config = nBillConfigurationService::getInstance()->getConfig();
        if (!$config->version_auto_check && !isset($_COOKIE['nbill_no_nag_version_check'])) {
            ?>
            <tr><td><div class="nbill-message" id="nbill_version_check_warning"><?php echo sprintf(NBILL_WARNING_VERSION_CHECK_OFF, nbf_cms::$interop->admin_page_prefix . '&enable_version_check=1', 'javascript:document.cookie =\'nbill_no_nag_version_check=1; expires=Fri, 31 Dec 2099 20:47:11 UTC; path=/\';document.getElementById(\'nbill_version_check_warning\').style.display=\'none\';alert(\'' . NBILL_VERSION_CHECKING_OFF_CONFIRM . '\');'); ?></div></td></tr>
            <?php
        }
        if (!$config->auto_check_eu_vat_rates && !isset($_COOKIE['nbill_no_nag_vat_rates'])) {
            ?>
            <tr><td><div class="nbill-message" id="nbill_vat_check_warning"><?php echo sprintf(NBILL_WARNING_VAT_RATE_CHECK_OFF, nbf_cms::$interop->admin_page_prefix . '&enable_eu_vat_rate_check=1', 'javascript:document.cookie =\'nbill_no_nag_vat_rates=1; expires=Fri, 31 Dec 2099 20:47:11 UTC; path=/\';document.getElementById(\'nbill_vat_check_warning\').style.display=\'none\';alert(\'' . NBILL_VAT_RATE_CHECKING_OFF_CONFIRM . '\');'); ?></div></td></tr>
            <?php
        }
        if (nbf_common::nb_strlen(nbf_globals::$message) == 0 || nbf_globals::$message == sprintf(NBILL_MIGRATE_SUCCESS_WITH_GUESSES, '<a href="' . nbf_cms::$interop->admin_page_prefix . '&action=anomaly">' . NBILL_MNU_ANOMALY . '</a>'))
        {
            if ($ledger_guesses && count($ledger_guesses) > 0)
            {
                $ledger_guess_message = NBILL_LEDGER_GUESSES . "<br />";
                foreach ($ledger_guesses as $ledger_guess)
                {
                    if (nbf_common::nb_strlen($ledger_guess_message) > (nbf_common::nb_strlen(NBILL_LEDGER_GUESSES) + 10))
                    {
                        $ledger_guess_message .= "; ";
                    }
                    if ($ledger_guess->transaction_type == "EX")
                    {
                        $ledger_guess_message .= '<a href="' . nbf_cms::$interop->admin_page_prefix . '&action=expenditure&task=edit&cid=' . $ledger_guess->transaction_id .'&guessed=1">' . NBILL_PAYMENT_NO . " " . $ledger_guess->transaction_no . '</a>';
                    }
                    else
                    {
                        $ledger_guess_message .= '<a href="' . nbf_cms::$interop->admin_page_prefix . '&action=income&task=edit&cid=' . $ledger_guess->transaction_id .'&guessed=1">' . NBILL_RECEIPT_NO . " " . $ledger_guess->transaction_no . '</a>';
                    }
                }
                $ledger_guess_message .= "<div style=\"text-align:right\"><a href=\"#\" onclick=\"if (confirm('" . NBILL_LEDGER_GUESSES_DELETE_SURE . "')){window.location='" . nbf_cms::$interop->admin_page_prefix . "&action=main&task=delete_ledger_guesses'}\">" . NBILL_LEDGER_GUESSES_DELETE . "</a></div>";
                echo "<tr><td align=\"left\"><div class=\"nbill-message\">" . $ledger_guess_message . "</div></td></tr>";
            }
        }
		?>
		</table>
		<div class="nbill-message-ie-padding-bug-fixer"></div>
        <!--[if IE 6]>
        <div class="nbill-message"><?php echo NBILL_IE6_NOT_SUPPORTED; ?></div>
        <![endif]-->
        <?php
		if (nbf_upgrader::$new_version_available)
		{
			$new_version = "v" . nbf_upgrader::$latest_version;
			echo "<div class=\"nbill-message\">" . sprintf(NBILL_NEW_VERSION_AVAILABLE, $new_version);
			if (nbf_upgrader::$latest_auto)
			{
				echo " " . sprintf(NBILL_TO_UPGRADE_NOW, "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=configuration&task=update_now&return=home\">" . NBILL_CLICK_HERE . "</a>");
			}
			echo "</div>";
            if (nbf_common::nb_strlen(nbf_upgrader::$latest_description) > 0) {
				echo "<div style=\"border:solid 1px #cccccc;margin-top:5px;font-weight:bold;color:#666666;width:100%padding:5px;\">" . nbf_upgrader::$latest_description . "</div>";
			}
		} else if (nbf_upgrader::$old_version_checker) {
            echo "<div class=\"nbill-message\">" . NBILL_OLD_VERSION_CHECKER . "</div>";
        } else if (nbf_upgrader::$unable_to_check_version) {
            echo "<div class=\"nbill-message\">" . NBILL_UNABLE_TO_CHECK_VERSION . (strlen(trim(nbf_upgrader::$latest_description)) > 0 ? ' ' . NBILL_ERROR_MESSAGE . nbf_upgrader::$latest_description : '') . "</div>";
        } else if ($offer_migration && nbf_common::nb_strlen(nbf_globals::$message) == 0) {
            echo "<div class=\"nbill-message\">" . sprintf(NBILL_MIGRATE, nbf_cms::$interop->admin_page_prefix . "&action=configuration&task=auto_migrate&return=home", "return confirm('" . sprintf(NBILL_MIGRATE_DELETE_WARNING, nbf_version::$nbill_version_no) . "');", NBILL_CLICK_HERE, nbf_version::$nbill_version_no) . "</div>";
        }
        ?>
        <div id="nbill_dashboard">
        <?php
        nbf_cms::$interop->add_html_header('<link rel="stylesheet" type="text/css" href="' . nbf_cms::$interop->nbill_site_url_path . '/style/admin/widgets.css" />');
        nbf_cms::$interop->add_html_header('<link rel="stylesheet" type="text/css" href="' . nbf_cms::$interop->nbill_site_url_path . '/js/tinybox2/style.css" />');
        nbf_cms::$interop->add_html_header('<script type="text/javascript" src="' . nbf_cms::$interop->nbill_site_url_path . '/js/tinybox2/tinybox.js"></script>');
        nbf_cms::$interop->add_html_header('<script type="text/javascript" src="' . nbf_cms::$interop->nbill_site_url_path . '/js/widgets/js_in_ajax.js"></script>');
        nbf_cms::$interop->add_html_header('<script type="text/javascript" src="' . nbf_cms::$interop->nbill_site_url_path . '/js/widgets/config.js"></script>');
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.client.php");
        $_REQUEST['task'] = '';
        include(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/widgets.php");
        ?>
        </div>

        <?php
        
	}
}