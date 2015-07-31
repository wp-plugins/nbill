<?php
/**
* HTML output for payment plans feature
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillPaymentPlans
{
    public static function showPlans($rows, $pagination)
    {
        ?>
        <table class="adminheading" style="width:auto;">
        <tr>
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "payment_plans"); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_PAYMENT_PLANS_TITLE; ?>
            </th>
        </tr>
        </table>

        <div class="nbill-message-ie-padding-bug-fixer"></div>
        <?php if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
        {
            echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
        }
        ?>
        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="payment_plans" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">

        <p align="left">
            <?php echo NBILL_PAYMENT_PLANS_INTRO; ?>
        </p>

        <div class="rounded-table">
            <table class="adminlist">
            <tr>
                <th class="selector">
                #
                </th>
                <th class="selector">
                    <input type="checkbox" name="check_all" value="" onclick="for(var i=0; i<<?php echo count($rows); ?>;i++) {document.getElementById('cb' + i).checked=this.checked;} document.adminForm.box_checked.value=this.checked;" />
                </th>
                <th class="title">
                    <?php echo NBILL_PAYMENT_PLAN_NAME; ?>
                </th>
                <th class="title responsive-cell priority">
                    <?php echo NBILL_PAYMENT_PLAN_TYPE; ?>
                </th>
                <th class="title responsive-cell optional">
                    <?php echo NBILL_PAYMENT_PLAN_PERCENTAGE; ?>
                </th>
                <th class="title">
                    <?php echo NBILL_PAYMENT_PLAN_QUOTE_DEFAULT; ?>
                </th>
                <th class="title">
                    <?php echo NBILL_PAYMENT_PLAN_INVOICE_DEFAULT; ?>
                </th>
            </tr>
            <?php
                for ($i=0, $n=count( $rows ); $i < $n; $i++)
                {
                    $row = &$rows[$i];
                    $link = nbf_cms::$interop->admin_page_prefix . "&action=payment_plans&task=edit&cid=$row->id";

                    $img_quote = $row->quote_default ? 'tick.png' : 'cross.png';
                    $alt_quote = $row->quote_default ? NBILL_PLAN_DEFAULT_YES : NBILL_PLAN_DEFAULT_NO;
                    $img_invoice = $row->invoice_default ? 'tick.png' : 'cross.png';
                    $alt_invoice = $row->invoice_default ? NBILL_PLAN_DEFAULT_YES : NBILL_PLAN_DEFAULT_NO;

                    echo "<tr>";
                    echo "<td class=\"selector\">";
                    echo $pagination->list_offset + $i + 1;
                    $checked = nbf_html::id_checkbox($i, $row->id);
                    echo "</td><td class=\"selector\">$checked</td>";
                    echo "<td class=\"list-value\"><a href=\"$link\" title=\"" . NBILL_EDIT_PAYMENT_PLAN . "\">" . $row->plan_name . "</a></td>";
                    echo "<td class=\"list-value responsive-cell priority\">" . @constant($row->plan_type_desc) . "</td>";
                    echo "<td class=\"list-value responsive-cell optional\">" . format_number($row->deposit_percentage) . "</td>";
                    echo "<td class=\"selector\">";
                    if (!$row->quote_default)
                    {
                        echo "<a href=\"#\" onclick=\"for(var i=0; i<" . count($rows) . ";i++) {document.getElementById('cb' + i).checked=false};document.getElementById('cb$i').checked=true;document.adminForm.task.value='make_quote_default';document.adminForm.submit();return false;\">";
                    }
                    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/$img_quote\" border=\"0\" alt=\"$alt_quote\" />";
                    if (!$row->quote_default)
                    {
                        echo "</a>";
                    }
                    echo "</td>";
                    echo "<td class=\"selector\">";
                    if (!$row->invoice_default)
                    {
                        echo "<a href=\"#\" onclick=\"for(var i=0; i<" . count($rows) . ";i++) {document.getElementById('cb' + i).checked=false};document.getElementById('cb$i').checked=true;document.adminForm.task.value='make_invoice_default';document.adminForm.submit();return false;\">";
                    }
                    echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/icons/$img_invoice\" border=\"0\" alt=\"$alt_invoice\" />";
                    if (!$row->invoice_default)
                    {
                        echo "</a>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
            ?>
            <tr class="nbill_tr_no_highlight"><td colspan="8" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
            </table>
        </div>
        </form>
        <?php
    }

    /**
    * Edit a payment plan (or create a new one)
    */
    public static function editPlan($plan_id, $row, $plan_types, $currencies, $payment_frequencies, $durations)
    {
        ?>
        <script language="javascript" type="text/javascript">
        <?php nbf_html::add_js_validation_numeric(); ?>

        function nbill_submit_task(task_name)
        {
            var form = document.adminForm;
            if (task_name == 'cancel')
            {
                form.task.value=task_name;
                form.submit();
                return;
            }

            // do field validation
            if (form.plan_name.value == "" && form.plan_name.value == "")
            {
                alert('<?php echo NBILL_PAYMENT_PLAN_NAME_REQUIRED; ?>');
            }
            else if (form.deposit_percentage.value == "" && form.deposit_amount.value == "")
            {
                alert('<?php echo NBILL_PAYMENT_PLAN_AMOUNT_REQUIRED; ?>');
            }
            else if (form.deposit_percentage.value.length > 0 && !IsNumeric(form.deposit_percentage.value, true))
            {
                alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_PAYMENT_PLAN_PERCENTAGE); ?>');
            }
            else if (!IsNumeric(form.deposit_amount.value, true))
            {
                alert('<?php echo sprintf(NBILL_NUMERIC_ONLY, NBILL_PAYMENT_PLAN_AMOUNT); ?>');
            }
            else
            {
                document.adminForm.task.value=task_name;
                document.adminForm.submit();
            }
        }
        </script>

        <table class="adminheading" style="width:auto;">
        <tr>
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "payment_plans"); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
                <?php $plan_name = $row->plan_name;
                echo $row->id ? NBILL_EDIT_PAYMENT_PLAN . " '$plan_name'" : NBILL_NEW_PAYMENT_PLAN; ?>
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
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="payment_plans" />
        <input type="hidden" name="task" value="edit" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
        <input type="hidden" name="id" value="<?php echo $plan_id; ?>" />
        <?php nbf_html::add_filters(); ?>

        <div class="rounded-table">
            <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
            <tr>
                <th colspan="2"><?php echo NBILL_PAYMENT_PLAN_DETAILS; ?></th>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_PAYMENT_PLAN_NAME; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="plan_name" id="plan_name" value="<?php echo str_replace("\"", "&quot;", $row->plan_name); ?>" class="inputbox" style="width:160px" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_PAYMENT_PLAN_NAME, "plan_name_help"); ?>
                </td>
            </tr>
            <!-- Custom Fields Placeholder -->
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_PAYMENT_PLAN_TYPE; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    $plan_type_list = array();
                    foreach ($plan_types as $plan_type)
                    {
                        $plan_type_list[] = nbf_html::list_option($plan_type->code, $plan_type->description);
                    }
                    echo nbf_html::select_list($plan_type_list, "plan_type", "class=\"inputbox\" id=\"plan_type\"", $row->plan_type); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_PAYMENT_PLAN_TYPE, "plan_type_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_PAYMENT_PLAN_PERCENTAGE; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="deposit_percentage" value="<?php echo format_number($row->deposit_percentage); ?>" class="inputbox numeric" /> %
                    <?php nbf_html::show_static_help(NBILL_INSTR_PAYMENT_PLAN_PERCENTAGE, "deposit_percentage_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_PAYMENT_PLAN_AMOUNT; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    $currency_list = array();
                    foreach ($currencies as $currency)
                    {
                        $currency_list[] = nbf_html::list_option($currency['code'], $currency['code']);
                    }
                    echo nbf_html::select_list($currency_list, "currency", "id=\"currency\" style=\"width:60px;\"", $row->currency);
                    ?>
                    <input type="text" name="deposit_amount" id="deposit_amount" value="<?php echo format_number($row->deposit_amount, 'currency_grand'); ?>" class="inputbox numeric" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_PAYMENT_PLAN_AMOUNT, "deposit_amount_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_PAYMENT_PLAN_INSTALLMENT_FREQUENCY; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php
                    $frequency_list = array();
                    $frequency_list[] = nbf_html::list_option("-1", NBILL_NOT_APPLICABLE);
                    foreach ($payment_frequencies as $pay_freq)
                    {
                        $frequency_list[] = nbf_html::list_option($pay_freq->code, $pay_freq->description);
                    }
                    echo nbf_html::select_list($frequency_list, "installment_frequency", "class=\"inputbox\" id=\"installment_frequency\"", $row->installment_frequency); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_PAYMENT_PLAN_INSTALLMENT_FREQUENCY, "installment_frequency_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_PAYMENT_PLAN_NO_OF_INSTALLMENTS; ?>
                </td>
                <td class="nbill-setting-value">
                    <input type="text" name="no_of_installments" value="<?php echo $row->no_of_installments; ?>" class="inputbox" />
                    <?php nbf_html::show_static_help(NBILL_INSTR_PAYMENT_PLAN_NO_OF_INSTALLMENTS, "no_of_installments_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_PAYMENT_PLAN_QUOTE_DEFAULT; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php echo nbf_html::yes_or_no_options("quote_default", "", $row->quote_default); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_PAYMENT_PLAN_QUOTE_DEFAULT, "quote_default_help"); ?>
                </td>
            </tr>
            <tr>
                <td class="nbill-setting-caption">
                    <?php echo NBILL_PAYMENT_PLAN_INVOICE_DEFAULT; ?>
                </td>
                <td class="nbill-setting-value">
                    <?php echo nbf_html::yes_or_no_options("invoice_default", "", $row->invoice_default); ?>
                    <?php nbf_html::show_static_help(NBILL_INSTR_PAYMENT_PLAN_INVOICE_DEFAULT, "invoice_default_help"); ?>
                </td>
            </tr>
            </table>
        </div>
        </form>
        <?php
    }
}