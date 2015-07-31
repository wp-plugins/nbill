<?php
/**
* HTML output for invoice list in front end.
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

require_once(nbill_framework_locator::find_framework() . "/classes/nbill.html.class.php");
require_once(nbf_cms::$interop->nbill_fe_base_path . "/pages/invoices/invoices.custom.html.php");

class nBillFrontEndInvoices
{
	public static function show_invoices($rows, $first_product_description, $date_format, $orders)
	{
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		}
        $css = file_get_contents(nbf_cms::$interop->nbill_fe_base_path . "/calendar/dhtmlgoodies_calendar/dhtmlgoodies_calendar.css");
        nbf_cms::$interop->add_html_header('<style type="text/css">' . $css . '</style>');
		echo "<script type=\"text/javascript\">" . "\n/*<![CDATA[*/\nvar pathToImages = '" . nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_popup_page_prefix . "&action=show_image&file_name=../calendar/images/';" . "\n/*]]>*/\n</script>";
		$js = file_get_contents(nbf_cms::$interop->nbill_fe_base_path . "/calendar/dhtmlgoodies_calendar/dhtmlgoodies_calendar.js");
        nbf_cms::$interop->add_html_header('<script type="text/javascript">' . "\n/*<![CDATA[*/\n" . $js . "\n/*]]>*/\n" . '</script>');
        $pay_invoice_col = false;
		?>

		<form action="<?php echo nbf_cms::$interop->fe_form_action; ?>" method="post" name="invoices" id="invoices">
            <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
            <input type="hidden" name="<?php echo nbf_cms::$interop->component_name; ?>" value="my-account" />
			<input type="hidden" name="action" value="invoices" />
			<input type="hidden" name="task" value="view" />
            <input type="hidden" name="Itemid" value="<?php echo isset($_REQUEST['Itemid']) && $_REQUEST['Itemid'] ? intval($_REQUEST['Itemid']) : (isset($GLOBALS['Itemid']) && $GLOBALS['Itemid'] ? intval($GLOBALS['Itemid']) : ""); ?>" />
			<?php
            //Output the main body of the page
            $fe_invoices = new nbill_fe_invoices_custom();
            $fe_invoices->show_invoice_list($rows, $first_product_description, $date_format, $orders);
            ?>
		</form>
		<?php
	}

	public static function print_preview($invoices, $invoice_items, $currency, $date_format, $tax_info, $shipping, $pdf)
	{
		include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.html/invoices.html.php");
		return @nBillFrontEndInvoice::printPreview($invoices, $invoice_items, $currency, $date_format, $tax_info, $shipping, $pdf);
	}

	public static function show_invoice_payment_summary($document_ids, $select_gateway, $gateways, $default_gateway, $invoice_details, $invoice_summary_total, $voucher_available = false)
	{
        $fe_invoices = new nbill_fe_invoices_custom();

        //Load the stylesheet
        $css = file_get_contents(nbf_cms::$interop->nbill_fe_base_path . "/style/nbill_forms.css");
        nbf_cms::$interop->add_html_header('<style type="text/css">' . $css . '</style>');

        if (nbf_cms::$interop->user->id && nbf_frontend::get_display_option("pathway"))
        {
            $fe_invoices->show_pathway(true);
        }

        if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
        {
            echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
        }
        echo "<h2 class=\"componentheading\">" . NBILL_PAY_INVOICE_TITLE . "</h2>";
        echo "<p align=\"left\" class=\"nbill-renew-intro\">" . NBILL_PAY_INVOICE_INTRO . "</p>";
		?>
		<form action="<?php echo nbf_cms::$interop->fe_form_action; ?>" method="post" name="select_gateway" id="select_gateway">
            <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
            <input type="hidden" name="<?php echo nbf_cms::$interop->component_name; ?>" value="my-account" />
			<input type="hidden" name="action" value="invoices" />
            <input type="hidden" name="Itemid" value="<?php echo isset($_REQUEST['Itemid']) && $_REQUEST['Itemid'] ? intval($_REQUEST['Itemid']) : (isset($GLOBALS['Itemid']) && $GLOBALS['Itemid'] ? intval($GLOBALS['Itemid']) : ""); ?>" />
			<input type="hidden" name="task" value="pay" />
			<input type="hidden" name="document_ids" value="<?php echo implode(",", $document_ids); ?>" />
            <?php
            //Output the main body of the page
            $fe_invoices->show_invoice_payment_summary($select_gateway, $gateways, $default_gateway, $invoice_details, $invoice_summary_total, $voucher_available);
            ?>
		</form>
		<?php
	}

    public static function show_message($message = "", $paying_invoice = false)
    {
        $fe_invoices = new nbill_fe_invoices_custom();
        $fe_invoices->show_pathway($paying_invoice);
        $fe_invoices->show_message($message);
    }

    public static function invoice_already_paid($document_id, $document_no)
    {
        //Output the main body of the page
        $fe_invoices = new nbill_fe_invoices_custom();
        $fe_invoices->show_pathway(true);
        $fe_invoices->invoice_already_paid($document_id, $document_no);
    }
}