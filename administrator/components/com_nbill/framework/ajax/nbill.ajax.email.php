<?php
/**
* Server-side processing for email AJAX functions
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function send_document_email()
{
    $nb_database = nbf_cms::$interop->database;
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.email.class.php");
    $return = '<div class="nbill-message">';
    $result = false;
    $document_id = intval(nbf_common::get_param($_REQUEST, 'document_id'));
    $from = str_replace(' ', '+', urldecode(nbf_common::get_param($_REQUEST, 'email_from')));
    $to = str_replace(' ', '+', urldecode(nbf_common::get_param($_REQUEST, 'email_to')));
    $cc = str_replace(' ', '+', urldecode(nbf_common::get_param($_REQUEST, 'email_cc')));
    $bcc = str_replace(' ', '+', urldecode(nbf_common::get_param($_REQUEST, 'email_bcc')));
    $subject = urldecode(nbf_common::get_param($_REQUEST, 'email_subject'));
    $attach = nbf_common::get_param($_REQUEST, 'email_attach');
    $history = nbf_common::get_param($_REQUEST, 'email_history');
    $template = nbf_common::get_param($_REQUEST, 'email_template');
    $message = urldecode(base64_decode(nbf_common::get_param($_REQUEST, 'email_message', null, true, false, true)));
    //nicEdit returns utf-8, so convert if nec
    if (nbf_cms::$interop->char_encoding == "iso-8859-1")
    {
        $message = utf8_decode($message);
    }
    $orig_message = $message;

    //Apply client credit amount if applicable
    if (defined('NBILL_ADMIN') && nbf_common::get_param($_REQUEST, 'apply_client_credit')) {
        $client_credit = null;
        $sql = "SELECT #__nbill_client_credit.*,
                        #__nbill_document.total_net AS invoice_total_net,
                        #__nbill_document.total_tax AS invoice_total_tax,
                        #__nbill_document.total_gross AS invoice_total_gross,
                        #__nbill_currency.symbol
                FROM #__nbill_client_credit
                INNER JOIN #__nbill_document ON #__nbill_document.entity_id = #__nbill_client_credit.entity_id
                    AND #__nbill_document.vendor_id = #__nbill_client_credit.vendor_id
                    AND #__nbill_document.currency = #__nbill_client_credit.currency
                INNER JOIN #__nbill_currency ON #__nbill_document.currency = #__nbill_currency.code
                WHERE #__nbill_document.id = " . intval($document_id) . "
                AND #__nbill_document.document_type = 'IN'
                AND (#__nbill_client_credit.net_amount > 0 OR #__nbill_client_credit.tax_amount > 0)
                AND #__nbill_document.email_sent = 0
                AND #__nbill_document.total_gross > 0";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($client_credit);
        if ($client_credit) {
            $total_credit = float_add($client_credit->net_amount, $client_credit->tax_amount);
            $credit_to_apply_net = format_number($total_credit > $client_credit->invoice_total_gross ? $client_credit->invoice_total_net : $client_credit->net_amount);
            $credit_to_apply_tax = format_number($total_credit > $client_credit->invoice_total_gross ? $client_credit->invoice_total_tax : $client_credit->tax_amount);
            $credit_to_apply_gross = format_number($total_credit > $client_credit->invoice_total_gross ? $client_credit->invoice_total_gross : $total_credit);
            if ($credit_to_apply_gross) {
                nbf_common::load_language("invoices");
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.payment.class.php");
                $error = "";
                $item_to_add = new stdClass();
                $item_to_add->vendor_id = $client_credit->vendor_id;
                $item_to_add->entity_id = $client_credit->entity_id;
                $item_to_add->nominal_ledger_code = $client_credit->ledger_code;
                $item_to_add->product_description = defined('NBILL_INVOICE_CLIENT_CREDIT_BALANCE_DESC') && strlen(NBILL_INVOICE_CLIENT_CREDIT_BALANCE_DESC) > 0 ? (sprintf(NBILL_INVOICE_CLIENT_CREDIT_BALANCE_DESC, $client_credit->description, $client_credit->symbol . float_subtract($total_credit, $credit_to_apply_gross))) : $client_credit->description;
                $item_to_add->detailed_description = '';
                $item_to_add->net_price_per_unit = 0- $credit_to_apply_net;
                $item_to_add->no_of_units = 1;
                $item_to_add->discount_amount = 0;
                $item_to_add->discount_description = '';
                $item_to_add->net_price_for_item = 0- $credit_to_apply_net;
                $item_to_add->tax_rate_for_item = $client_credit->tax_rate;
                $item_to_add->tax_for_item = 0- $credit_to_apply_tax;
                $item_to_add->shipping_id = 0;
                $item_to_add->shipping_for_item = 0;
                $item_to_add->tax_rate_for_shipping = 0;
                $item_to_add->tax_for_shipping = 0;
                $item_to_add->gross_price_for_item = 0- $credit_to_apply_gross;
                $item_to_add->product_code = '';
                $item_to_add->section_name = '';
                $item_to_add->section_discount_title = '';
                $item_to_add->section_discount_percent = 0;
                $item_to_add->quote_item_accepted = 0;
                nbf_payment::add_item_to_document($document_id, $item_to_add, $error);
                nbf_payment::refresh_document_totals($document_id);
                if (!$error) {
                    //Remove from client credit
                    $sql = "UPDATE #__nbill_client_credit SET
                                net_amount = net_amount - " . $credit_to_apply_net . ",
                                tax_amount = tax_amount - " . $credit_to_apply_tax . "
                            WHERE vendor_id = " . intval($client_credit->vendor_id) . "
                            AND entity_id = " . intval($client_credit->entity_id);
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                    nbf_globals::$message = $nb_database->_errorMsg;
                }
                else {
                    nbf_globals::$message = $error;
                }
            }
        }
    }

    if (nbf_common::nb_strlen($message) > 0)
    {
        if (nbf_common::nb_strlen($to) > 0)
        {
            if ($template)
            {
                //Strip any style tags and reapply the remainder to the template as the body section
                $start_pos = nbf_common::nb_strpos($message, "<style");
                if ($start_pos !== false)
                {
                    $end_pos = nbf_common::nb_strpos($message, "</style>", $start_pos) + 8;
                    $message = nbf_common::nb_substr($message, 0, $start_pos) . nbf_common::nb_substr($message, $end_pos);
                }
                //Load the template
                $document = null;
                $sql = "SELECT #__nbill_document.id, #__nbill_document.vendor_id, #__nbill_document.document_type,
                                #__nbill_vendor.invoice_email_template_name, #__nbill_vendor.credit_email_template_name,
                                #__nbill_vendor.quote_email_template_name, #__nbill_vendor.po_email_template_name, #__nbill_entity.default_language,
                                " . nbf_cms::$interop->cms_database_enum->table_user . "." . nbf_cms::$interop->cms_database_enum->column_user_username  ." AS username
                                FROM #__nbill_document
                                INNER JOIN #__nbill_vendor ON #__nbill_document.vendor_id = #__nbill_vendor.id
                                INNER JOIN #__nbill_entity_contact ON #__nbill_document.entity_id = #__nbill_entity_contact.entity_id
                                LEFT JOIN #__nbill_entity ON #__nbill_entity_contact.entity_id = #__nbill_document.entity_id
                                LEFT JOIN #__nbill_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id
                                LEFT JOIN " . nbf_cms::$interop->cms_database_enum->table_user . " ON #__nbill_contact.user_id = " .
                                nbf_cms::$interop->cms_database_enum->table_user . "." . nbf_cms::$interop->cms_database_enum->column_user_id . "
                                WHERE #__nbill_document.id = $document_id";
                $nb_database->setQuery($sql);
                $nb_database->loadObject($document);
                if ($document)
                {
                    switch ($document->document_type)
                    {
                        case "CR":
                            $template_name = $document->credit_email_template_name;
                            break;
                        case "QU":
                            $template_name = $document->quote_email_template_name;
                            break;
                        case "PO":
                            $template_name = $document->po_email_template_name;
                            break;
                        case "IN":
                        default:
                            $template_name = $document->invoice_email_template_name;
                            break;
                    }
                    $template_contents = "";
                    //Initialise variables that can be used in templates so they don't throw notices
                    $vendor_id = $document->vendor_id;
                    $document_id = $document->id;
                    $contact_name = ""; //Don't need to supply a value as we already have the message body
                    switch ($attach)
                    {
                        case "html":
                        case "pdf":
                            if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/email_templates/" . $template_name . "_attach.php"))
                            {
                                ob_start();
                                include(nbf_cms::$interop->nbill_fe_base_path . "/email_templates/" . $template_name . "_attach.php");
                                $template_contents = ob_get_clean();
                                break;
                            }
                            //else fall through
                        default:
                            if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/email_templates/" . $template_name . ".php"))
                            {
                                ob_start();
                                include(nbf_cms::$interop->nbill_fe_base_path . "/email_templates/" . $template_name . ".php");
                                $template_contents = ob_get_clean();
                            }
                            break;
                    }
                    if (nbf_common::nb_strlen($template_contents) > 0)
                    {
                        $start_pos = nbf_common::nb_strpos($template_contents, ">", nbf_common::nb_strpos($template_contents, "<body")) + 1;
                        if ($start_pos !== false)
                        {
                            $end_pos = nbf_common::nb_strpos($template_contents, "</body>", $start_pos);
                            $message = nbf_common::nb_substr($template_contents, 0, $start_pos) . $message . nbf_common::nb_substr($template_contents, $end_pos);
                        }
                    }
                }
            }

            if ($history)
            {
                $sql = "SELECT correspondence FROM #__nbill_document WHERE document_type = 'QU' AND id = $document_id";
                $nb_database->setQuery($sql);
                $correspondence = $nb_database->loadResult();
                if (nbf_common::nb_strlen(trim($correspondence)) > 0)
                {
                    $correspondence = '<br /><br /><hr />' . NBILL_EM_NEW_QUOTE_CORRESPONDENCE_INTRO . '<br />' . $correspondence;
                    if (!$template)
                    {
                        //Strip any styling before we convert to plain text
                        $start_pos = nbf_common::nb_strpos($correspondence, "<style");
                        if ($start_pos !== false)
                        {
                            $end_pos = nbf_common::nb_strpos($correspondence, "</style>", $start_pos) + 8;
                            $correspondence = nbf_common::nb_substr($correspondence, 0, $start_pos) . nbf_common::nb_substr($correspondence, $end_pos);
                        }
                        //Convert to plain text
                        $correspondence = str_replace("<br />", "\n", $correspondence);
                        $correspondence = str_replace("<hr />", str_repeat("-", 50) . "\n", $correspondence);
                        $correspondence = str_replace("<tr>", "", $correspondence);
                        $correspondence = str_replace("</tr>", "\n", $correspondence);
                        $correspondence = str_replace("</td><td>", ": ", $correspondence);
                        $correspondence = strip_tags($correspondence);
                    }
                    $message .= $correspondence;
                }
            }

            //Work out email option code
            switch ($attach)
            {
                case "embed":
                    $email_option = "BB"; //Embed
                    break;
                case "html":
                    if ($template)
                    {
                        $email_option = "EE"; //Template HTML attachment
                    }
                    else
                    {
                        $email_option = "AB"; //Plain HTML attachment
                    }
                    break;
                case "pdf":
                    if ($template)
                    {
                        $email_option = "FF"; //Template PDF attachment
                    }
                    else
                    {
                        $email_option = "AC"; //Plain PDF attachment
                    }
                    break;
                case "none";
                default:
                    if ($template)
                    {
                        $email_option = "DD"; //Template notification
                    }
                    else
                    {
                        $email_option = "CC"; //Plain notification
                    }
                    break;
            }

            //Save history to quote, if applicable
            if ($history)
            {
                $mail_header = '<em>' . sprintf(NBILL_EMAIL_MESSAGE_TIMESTAMP, nbf_common::nb_date(nbf_common::get_date_format() . " h:i:s")) . '</em><table cellpadding="3" cellspacing="0" border="0">';
                $mail_header .= '<tr><td style="font-weight:bold;vertical-align:top;">' . NBILL_EMAIL_MESSAGE_FROM . '</td><td>' . $from . '</td></tr>';
                $mail_header .= '<tr><td style="font-weight:bold;vertical-align:top;">' . NBILL_EMAIL_MESSAGE_TO . '</td><td>' . $to . '</td></tr>';
                $mail_header .= '<tr><td style="font-weight:bold;vertical-align:top;">' . NBILL_EMAIL_MESSAGE_CC . '</td><td>' . $cc . '</td></tr>';
                $mail_header .= '<tr><td style="font-weight:bold;vertical-align:top;">' . NBILL_EMAIL_MESSAGE_SUBJECT . '</td><td>' . $subject . '</td></tr></table><br />';
                if (!$template && $email_option != 'BB')
                {
                    //Convert plain text to HTML
                    $orig_message = str_replace("\n", "<br />", $orig_message);
                }
                //Don't append if this is a duplication
                $sql = "SELECT id FROM #__nbill_document WHERE id = " . intval($document_id) . " AND RIGHT(correspondence, " . nbf_common::nb_strlen(stripslashes($orig_message)) . ") = '$orig_message'";
                $nb_database->setQuery($sql);
                $add_corre = !($nb_database->loadResult());
                if ($add_corre)
                {
                    $sql = "UPDATE #__nbill_document SET correspondence = CONCAT(correspondence, '<br /><br /><hr />" . $mail_header . $orig_message . "') WHERE id = $document_id";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                }
            }

            $result = nbf_email::email_document($document_id, $from, $to, $cc, $bcc, $subject, $message, $email_option);
            if ($result)
            {
                if ($history)
                {
                    $return .= NBILL_EMAIL_QUOTE_DOC_SENT; //Note that history saved
                }
                else
                {
                    $return .= NBILL_EMAIL_DOC_SENT;
                }
            }
            else
            {
                if ($history)
                {
                    $return .= NBILL_EMAIL_QUOTE_DOC_NOT_SENT; //Note that history saved
                    if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
                    {
                        $return .= '<br /><br />' . nbf_globals::$message;
                    }
                }
                else
                {
                    if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
                    {
                        $return .= NBILL_EMAIL_DOC_NOT_SENT . " " . nbf_globals::$message;
                    }
                    else
                    {
                        $return .= NBILL_EMAIL_DOC_NOT_SENT . " " . NBILL_EMAIL_DOC_NOT_SENT_UNKNOWN;
                    }
                }
            }
        }
        else
        {
            //No recipient!
            $return .= NBILL_EMAIL_NO_RECIPIENT;
        }
    }
    else
    {
        //No message!
        $return .= NBILL_EMAIL_NO_MESSAGE;
    }
    $return .= '</div><br /><div align="center" style="font-weight:bold;font-size:10pt;"><a href="#" onclick="adminForm.submit();return false;">' . NBILL_CONTINUE . '</a>';
    if (!$result)
    {
        $return .= '&nbsp;&nbsp;|&nbsp;&nbsp;<a href="#" onclick="document.getElementById(\'results\').style.display=\'none\';document.getElementById(\'email_form\').style.display=\'\';window.frames[\'ifr_email_message\'].location.reload();return false;">' . NBILL_TRY_AGAIN . '</a>';
    }
    $return .= '</div>';
    echo $return;
}