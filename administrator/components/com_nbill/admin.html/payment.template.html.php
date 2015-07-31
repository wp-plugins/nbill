<?php
/**
* HTML output for income feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <title><?php echo NBILL_EXPENDITURE_PAYMENT_TITLE; ?></title>
        <base href="<?php echo nbf_cms::$interop->live_site; ?>">
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo nbf_cms::$interop->char_encoding; ?>" />
        <style type="text/css">
        body
        {
            font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif;
        }
        td, p
        {
            font-size: 10pt;
            text-align: left;
            padding: 5px;
        }
        th
        {
            font-size: 10pt;
            font-weight: bold;
            text-align:left;
            padding: 5px;
            background-color:#<?php echo $heading_bg_colour; ?>;
            color:#<?php echo $heading_fg_colour; ?>;
            text-shadow: 1px 1px 1px #000000;
        }
        h1, h2
        {
            color: #<?php echo $title_colour; ?>
        }
        table.nbill-payment-table
        {
            border-collapse: collapse;
        }
        .nbill-payment-table td, .nbill-payment-table th
        {
            border: solid 1px #cccccc;
        }
        </style>
    </head>
    <body>
    <table cellpadding="10" cellspacing="0" border="0" width="100%">
        <tr>
            <td width="15%" align="left">
                <?php if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . $row->vendor_id . ".gif"))
                { ?>
                    <img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/vendors/<?php echo $row->vendor_id; ?>.gif" alt="Logo" /><?php
                }
                else if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . $row->vendor_id . ".png"))
                { ?>
                    <img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/vendors/<?php echo $row->vendor_id; ?>.png" alt="Logo" /><?php
                }
                else
                {
                    echo "&nbsp;";
                }?>
            </td>
            <td valign="middle" class="vendor" style="text-align:right">
                <h2 style="margin-bottom:3px;"><?php echo $vendor_name; ?></h2>
                <?php echo str_replace("\n", "<br />", $vendor_address); ?>
                <h1><?php echo NBILL_EXPENDITURE_PAYMENT_TITLE; ?></h1>
            </td>
        </tr>
    </table>

    <p><?php echo NBILL_EXPENDITURE_PAYMENT_INTRO; ?></p>
    <table cellpadding="0" cellspacing="3" border="0" class="nbill-payment-table" width="100%">
        <tr>
            <th style="width:150px;"><?php echo NBILL_PAYMENT_DATE; ?></th>
            <td><?php echo nbf_common::nb_date(nbf_common::get_date_format(), $row->date); ?></td>
        </tr>
        <tr>
            <th><?php echo NBILL_PAID_TO; ?></th>
            <td><?php echo $row->name; ?></td>
        </tr>
        <tr>
            <th><?php echo NBILL_PAYMENT_METHOD; ?></th>
            <td><?php echo $row->pay_method_name; ?></td>
        </tr>
        <tr>
            <th><?php echo NBILL_PAYMENT_NO; ?></th>
            <td><?php echo $row->transaction_no ? $row->transaction_no : NBILL_EXPENDITURE_NOT_YET_ASSIGNED; ?></td>
        </tr>
        <tr>
            <th><?php echo NBILL_AMOUNT_PAID; ?></th>
            <td><strong><?php echo format_number($row->amount, 'currency_grand', null, null, null, $row->currency); ?></strong></td>
        </tr>
        <?php if (count($documents) == 0 && strlen($row->for) > 0)
        { ?>
            <tr>
                <th><?php echo NBILL_EXPENDITURE_PAID_FOR; ?></th>
                <td><?php echo $row->for; ?></td>
            </tr><?php
        } ?>
    </table>
    <?php if (count($documents) > 0)
    {
        $invoice_list = array();
        foreach ($documents as $document)
        {
            $invoice_list[] = sprintf(NBILL_EXPENDITURE_RE_CREDIT, $document->document_no, nbf_common::nb_date(nbf_common::get_date_format(), $document->document_date));
        }
        ?>
        <br />
        <table cellpadding="3" cellspacing="0" border="0">
            <tr>
                <td style="vertical-align:top">
                    <?php echo NBILL_EXPENDITURE_RE_CREDITS; ?>
                </td>
                <td style="vertical-align:top">
                    <?php echo implode("<br />", $invoice_list); ?>
                </td>
            </tr>
        </table>
    <?php } ?>
    <p><?php echo NBILL_EXPENDITURE_PAID; ?></p>
    </body>
</html>