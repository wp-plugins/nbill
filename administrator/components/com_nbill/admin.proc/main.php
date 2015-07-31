<?php
/**
* Main processing file for nBill administrator home page
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');$nb_database = nbf_cms::$interop->database;

if (nbf_common::get_param($_REQUEST, 'task') == 'delete_ledger_guesses')
{
    $sql = "DELETE FROM #__nbill_ledger_breakdown_guesses";
    $nb_database->setQuery($sql);
    $nb_database->query();
}

if (nbf_common::get_param($_GET, 'disable_version_check') == 1)
{
    $sql = "UPDATE #__nbill_configuration SET version_auto_check = 0, auto_update = 0 WHERE id = 1";
    $nb_database->setQuery($sql);
    $nb_database->query();
}
else
{
    if (nbf_common::get_param($_GET, 'enable_version_check') == 1)
    {
        $sql = "UPDATE #__nbill_configuration SET version_auto_check = 1, auto_update = 0 WHERE id = 1";
        $nb_database->setQuery($sql);
        $nb_database->query();
        nBillConfigurationService::getInstance()->getConfig()->version_auto_check = true;
    }
    //Version check
    nbf_upgrader::check_version();
}

if (nbf_common::get_param($_GET, 'disable_eu_vat_rate_check') == 1) {
    $sql = "UPDATE #__nbill_configuration SET auto_check_eu_vat_rates = 0 WHERE id = 1";
    $nb_database->setQuery($sql);
    $nb_database->query();
} else {
    if (nbf_common::get_param($_GET, 'enable_eu_vat_rate_check') == 1) {
        $sql = "UPDATE #__nbill_configuration SET auto_check_eu_vat_rates = 1 WHERE id = 1";
        $nb_database->setQuery($sql);
        $nb_database->query();
        nBillConfigurationService::getInstance()->getConfig()->auto_check_eu_vat_rates = true;
    }
    //Tax rate check
    $affected_orders = array();
    $config_service = nBillConfigurationService::getInstance();
    $config = $config_service->getConfig();
    $number_factory = new nBillNumberFactory($config);
    $skipped_ids = array();
    if ($config->eu_tax_rate_refresh_timestamp < time() - 86400 || nbf_common::get_param($_GET, 'force_tax_rate_refresh') == 1) {
        $tax_mapper = new nBillTaxMapper($nb_database, $number_factory);
        $tax_service = new nBillTaxService($tax_mapper, $config);
        $sql = "SELECT id FROM #__nbill_vendor ORDER BY id";
        $nb_database->setQuery($sql);
        $vendors = $nb_database->loadObjectList();
        $tax_service->checkTaxRates(nbf_common::get_param($_GET, 'force_tax_rate_refresh') == 1);
        foreach ($vendors as $vendor)
        {
            $skipped_ids = $tax_service->refreshEuTaxRecords($vendor->id, $affected_orders);
            if (count($skipped_ids) > 0) {
                //Reset timestamp in case there are more
                $config->eu_tax_rate_refresh_timestamp = time() - 86401;
                $config_service->saveConfig($config);

                //Redirect to tax feature to prompt user to update order amounts
                $tax_rate = $tax_service->prepareNewTaxRate($skipped_ids[0]);
                $_POST['action'] = 'vat';
                $_POST['task'] = 'apply';
                $_POST['id'] = intval($tax_rate->id);
                $_POST['vendor_id'] = intval($tax_rate->vendor_id);
                $_POST['tax_name'] = $tax_rate->tax_name;
                $_POST['tax_abbreviation'] = $tax_rate->tax_abbreviation;
                $_POST['country_code'] = $tax_rate->country_code;
                $_POST['tax_zone'] = $tax_rate->tax_zone;
                $_POST['tax_reference_desc'] = $tax_rate->tax_reference_desc;
                $_POST['tax_rate'] = $tax_rate->tax_rate->format();
                $_POST['online_exempt'] = $tax_rate->online_exempt ? '1' : '0';
                $_POST['exempt_with_ref_no'] = $tax_rate->exempt_with_ref_no ? '1' : '0';
                $_POST['electronic_delivery'] = $tax_rate->electronic_delivery ? '1' : '0';
                $_POST['pay_inst_' . intval($tax_rate->vendor_id)] = $tax_rate->payment_instructions;
                $_POST['sml_prt_' . intval($tax_rate->vendor_id)] = $tax_rate->small_print;
                $_POST['auto_vat_rate_change'] = 1;
                foreach ($_POST as $key=>$value)
                {
                    $_REQUEST[$key] = $value;
                }
                $task = 'apply';
                ?><div id="nbill-toolbar-container"></div><?php
                nbf_common::load_language('vat');
                include_once(nbf_cms::$interop->nbill_admin_base_path . '/admin.html/vat.html.php');
                include(nbf_cms::$interop->nbill_admin_base_path . '/admin.proc/vat.php');
                return;
            }
        }
        $config_service->saveConfig($config); //Update timestamp so we don't check more than once a day
    }
}

//See if nBill 1.2.x tables exist, and if so, whether we have already upgraded from them or not
$offer_migration = false;
if (!nbf_common::get_param($_REQUEST, 'nbill_admin_via_fe')) //In case error reporting is set to high, avoid DB errors that occur when nBill 1 tables are not present.
{
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
            $sql = "SELECT upgraded_from FROM #__nbill_version WHERE id = 1";
            $nb_database->setQuery($sql);
            $upgraded_from = $nb_database->loadResult();
            $version_1_2_999 = new nbf_version("1.2.999");
            if ($version_1_2_999->compare(">", $upgraded_from))
            {
                $offer_migration = true;
            }
        }
    }
}

//See if we have any ledger breakdown guesses to nag about
$sql = "SELECT #__nbill_ledger_breakdown_guesses.transaction_id, #__nbill_ledger_breakdown_guesses.transaction_type,
            #__nbill_transaction.transaction_no FROM #__nbill_ledger_breakdown_guesses
            INNER JOIN #__nbill_transaction ON #__nbill_ledger_breakdown_guesses.transaction_id = #__nbill_transaction.id
            ORDER BY #__nbill_transaction.transaction_type DESC, #__nbill_transaction.date, #__nbill_transaction.transaction_no";
$nb_database->setQuery($sql);
$guesses = $nb_database->loadObjectList();

if (nbf_common::get_param($_REQUEST, 'disabled') || (strlen(nbf_common::get_param($_REQUEST, 'action')) > 0 && !file_exists(nbf_cms::$interop->nbill_admin_base_path . '/admin.proc/' . nbf_common::get_param($_REQUEST, 'action') . '.php')))
{
    $lite_message = "";
    switch (nbf_common::get_param($_REQUEST, 'action'))
    {
        case "ledger":
            $lite_message = "You can create nominal ledger codes to categorise your transactions for reporting purposes.<br /><br />";
            break;
        case "shipping":
            $lite_message = "Shipping rates can be defined which can be applied to orders automatically, or the user can be allowed to choose a shipping method. If the courier provides a parcel tracking service, this can also be integrated.<br /><br />";
            break;
        case "discounts":
            $lite_message = "Discount records enable you to specify rules for automatically adding discounts to orders and invoices. Discounts can be product-specific or global, and can be based on voucher codes, order values, quantities, prerequisite products, etc.<br /><br />";
            break;
        case "fees":
            $lite_message = "Fee records enable you to specify rules for automatically adding fees to orders and invoices. Fees can be product-specific or global, and can be based on voucher codes, order values, quantities, prerequisite products, etc.<br /><br />";
            break;
        case "payment_plans":
            $lite_message = "Payment plans enable you to specify how payments can be made - they can be split into installments, deposit + final payment, deposit + installments, or even allow the user to choose what payments to make and when.<br /><br />";
            break;
        case "reminders":
            $lite_message = "Reminders allow you to send an e-mail to the client when a recurring payment is due, when an order is due for renewal or about to expire, or when an invoice is overdue.<br /><br />";
            break;
        case "backup":
            $lite_message = "The backup feature enables you to take a backup of your database tables. You can choose to backup your entire database, or just the tables relating to nBill. Likewise, you can restore a backup of your entire database or just the nBill tables.<br /><br />";
            break;
        case "housekeeping":
            $lite_message = "The housekeeping feature enables you to delete all (or selected) records that are over a certain age. In some countries it may be a legal requirement to keep certain records for a certain length of time and/or to delete certain records after a certain length of time or when they are no longer needed.<br /><br />";
            break;
        case "quote_request":
            $lite_message = "You can build forms which allow users to request a quote online. You can choose which fields appear on the form so that you capture whatever data you need in order to provide a quote.<br /><br />";
            break;
        case "orderforms":
            $lite_message = "Order forms allow you to take orders online through your website. You can choose which fields appear on the form so that you capture whatever data you need to complete the order. Payment can be taken online, and an order, invoice, and income record generated automatically.<br /><br />";
            break;
        case "pending":
            $lite_message = "When an order form is submitted, a pending order is created. The pending order is 'activated' (turned into a live order) when online payment is received. If payment is made offline, an administrator can activate the pending order manually.<br /><br />";
            break;
        case "user_admin":
            $lite_message = "This feature enables you to nominate individual users who should be allowed to access nBill's administration features via your website front-end (so you can give them access to nBill without giving them access to the rest of your website administration features).<br /><br />";
            break;
        case "potential_clients":
            $lite_message = "When somebody fills in a quote request form, a 'potential client' record is created. They are 'promoted' to a normal client record if the quote is accepted. This keeps your client list under control, as there might be a large number of individuals who request a quote but never go on to order anything.<br /><br />";
            break;
        case "quotes":
            $lite_message = "You can provide itemised quotes which the client can accept, reject, or partially accept. Correspondence between you and the client can be recorded on the quote record.<br /><br />";
            break;
        case "suppliers":
            $lite_message = "When recording expenditure, you can store details about your suppliers here so that you don't have to type in the information every time you make a payment.<br /><br />";
            break;
        case "orders":
            $lite_message = "Order records can be used to store details about which products have been ordered by which clients and to indicate if and when invoices should be generated automatically (eg. for recurring subscription payments).<br /><br />";
            break;
        case "expenditure":
            $lite_message = "You can manually add expenditure information so that you can report on all of your income and outgoings using the reports menu.<br /><br />";
            break;
        case "tx_search":
            $lite_message = "The transaction search feature allows you to tie up records between your payment service provider and your orders, invoices, and income records.<br /><br />";
            break;
        case "transactions":
            $lite_message = "The transaction report lists all of your income and expenditure transactions for a given date range, along with the totals (net, tax, and gross)<br /><br />";
            break;
        case "ledger_report":
            $lite_message = "This report shows the total amount that goes into each nominal ledger code so you can see all of your income and expenditure by category.<br /><br />";
            break;
        case "taxsummary":
            $lite_message = "The tax summary report shows the total taxable and non taxable amounts for a given period and the tax due. You can 'drill-down' to see which transactions go into each total.<br /><br />";
            break;
        case "snapshot":
            $lite_message = "This report shows which invoices were outstanding on a given date (useful for year end accounting purposes).<br /><br />";
            break;
        case "anomaly":
            $lite_message = "The anomaly report brings your attention to any records that seem unusual (eg. if the tax amount does not quite match the tax rate, or payment was made more than a month in advance, or an overpayment was made, etc.) so that you can investigate and correct if necessary.<br /><br />";
            break;
        case "db_upgrade":
        case "":
            //No need for a message
            break;
    }
    if (strlen($lite_message) > 0) {
        nbf_globals::$message = $lite_message . "This feature is not available in the this edition of " . NBILL_BRANDING_NAME . ". You will need to <a target=\"_blank\" href=\"http://www.nbill.co.uk/\">upgrade to the standard edition</a> to use this feature.";
    }
}

nbf_common::load_language("widgets");
nBillMain::main($offer_migration, $guesses);