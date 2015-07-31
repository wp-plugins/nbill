<?php
/**
* Class file just containing static methods relating to sending emails.
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Ensure required language file is present
nbf_common::load_language("email");

/**
* Static functions relating to the sending emails
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_email
{
    /**
    * Load up the defaults for the given document and show the email form
    * @param int $document_id Primary key
    */
    public static function show_email_form_for_document($document_id, $intro_prefix = "", $client_credit = array())
    {
        $nb_database = nbf_cms::$interop->database;

        //Initialise
        $intro = $intro_prefix;
        $from = "";
        $to = "";
        $cc = "";
        $bcc = "";
        $quote_history = false;
        $attachment = false;
        $default_attachment = 0;
        $default_subject = "";
        $use_template = true;
        $language_params = "";

        $basic_live_site = str_replace("https://", "http://", nbf_cms::$interop->live_site);
        $vendor_id = 1;

        //Load document data
        $document = null;
        $sql = "SELECT #__nbill_document.id, #__nbill_document.document_type, #__nbill_document.document_no,
                        #__nbill_entity_contact.email_invoice_option, #__nbill_document.vendor_id,
                        #__nbill_vendor.invoice_email_template_name, #__nbill_vendor.credit_email_template_name,
                        #__nbill_vendor.quote_email_template_name, #__nbill_vendor.po_email_template_name,
                        #__nbill_vendor.admin_email, #__nbill_contact.email_address, #__nbill_document.status,
                        #__nbill_entity.company_name, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS `name`, #__nbill_entity.default_language, " .
                        nbf_cms::$interop->cms_database_enum->table_user . "." . nbf_cms::$interop->cms_database_enum->column_user_username . " AS username
                        FROM #__nbill_document
                        INNER JOIN #__nbill_vendor ON #__nbill_document.vendor_id = #__nbill_vendor.id
                        LEFT JOIN #__nbill_entity ON #__nbill_document.entity_id = #__nbill_entity.id
                        LEFT JOIN #__nbill_entity_contact ON #__nbill_entity.id = #__nbill_entity_contact.entity_id AND #__nbill_entity_contact.contact_id = #__nbill_entity.primary_contact_id
                        LEFT JOIN #__nbill_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id
                        LEFT JOIN " . nbf_cms::$interop->cms_database_enum->table_user . " ON #__nbill_contact.user_id = " . nbf_cms::$interop->cms_database_enum->table_user . "." . nbf_cms::$interop->cms_database_enum->column_user_id . "
                        WHERE #__nbill_document.id = " . intval($document_id);
        $nb_database->setQuery($sql);
        $nb_database->loadObject($document);
        if ($document)
        {
            $vendor_id = $document->vendor_id;
            $from = $document->admin_email;
            $to = $document->email_address;
            if ($document->default_language && $document->default_language != nbf_cms::$interop->language)
            {
                $language_params = "&nbill_lang=" . $document->default_language . "&nbill_lang_temp=1";
            }
            //Load any CC addresses
            $sql = "SELECT #__nbill_contact.email_address
                    FROM #__nbill_document
                    INNER JOIN #__nbill_entity ON #__nbill_document.entity_id = #__nbill_entity.id
                    INNER JOIN #__nbill_entity_contact ON #__nbill_entity.id = #__nbill_entity_contact.entity_id AND #__nbill_entity_contact.contact_id != #__nbill_entity.primary_contact_id
                    INNER JOIN #__nbill_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id
                    WHERE #__nbill_entity_contact.allow_";
            switch ($document->document_type)
            {
                case "QU":
                    $sql .= "quotes";
                    break;
                case "PO":
                    $sql .= "purchase_orders";
                    break;
                case "IN":
                case "CR":
                default:
                    $sql .= "invoices";
                    break;
            }
            $sql .= " = 1 AND #__nbill_document.id = " . intval($document_id);
            $nb_database->setQuery($sql);
            $cc = $nb_database->loadResultArray();

            switch ($document->document_type)
            {
                case "IN":
                case "CR":
                    $default_subject = sprintf($document->document_type == "CR" ? nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_CREDIT_SUBJECT') : nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_INVOICE_SUBJECT'), $document->document_no, nbf_cms::$interop->site_name);
                    $intro = "<h2>" . ($document->document_type == "CR" ? nbf_common::parse_translation($document->default_language, "email", 'NBILL_EMAIL_CREDIT_INTRO') : nbf_common::parse_translation($document->default_language, "email", 'NBILL_EMAIL_INVOICE_INTRO')) . "</h2>";
                    $attachment = true;
                    $use_template = false;
                    switch ($document->email_invoice_option)
                    {
                        case "AB": //Plain text plus HTML attachment
                        case "EE": //Template plus HTML attachment
                            $default_attachment = 1;
                            break;
                        case "AC": //Plain text plus PDF attachment
                        case "FF": //Template plus PDF attachment
                            $default_attachment = 2;
                            break;
                        case "BB": //Embed
                            $default_attachment = 3;
                            break;
                        default:
                            $default_attachment = 0;
                            break;
                    }
                    switch ($document->email_invoice_option)
                    {
                        case "DD":
                        case "EE":
                        case "FF":
                            $use_template = true;
                            break;
                    }
                    break;
                case "QU":
                    $intro = "<h2>" . nbf_common::parse_translation($document->default_language, "email", 'NBILL_EMAIL_QUOTE_INTRO') . "</h2>";
                    switch ($document->status)
                    {
                        case "BB": //On hold
                            $intro .= "<p>" . nbf_common::parse_translation($document->default_language, "email", 'NBILL_QUOTE_ON_HOLD_MESSAGE_INTRO') . "</p>";
                            $default_subject = sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_QUOTE_ON_HOLD_SUBJECT'), $document->document_no, nbf_cms::$interop->site_name);
                            break;
                        case "CC": //Quoted
                        default:
                            $intro .= "<p>" . nbf_common::parse_translation($document->default_language, "email", 'NBILL_QUOTE_QUOTED_MESSAGE_INTRO') . "</p>";
                            $default_subject = sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_QUOTE_SUBJECT'), $document->document_no, nbf_cms::$interop->site_name);
                            $attachment = true;
                            switch ($document->email_invoice_option)
                            {
                                case "AC": //Plain text plus PDF attachment
                                case "FF": //Template plus PDF attachment
                                    $default_attachment = 2;
                                    break;
                                default:
                                    $default_attachment = 1; //For quotes, we attach by default
                                    break;
                            }
                            break;
                    }
                    $quote_history = true;
                    break;
            }
        }

        //Show the form
        self::show_email_form($document_id, $intro, $from, $to, $cc, $bcc, $quote_history, $attachment, $default_attachment, $default_subject, $use_template, $language_params, $client_credit);
    }

    /**
    * Renders the form for sending an email
    * @param mixed $intro
    * @param mixed $from
    * @param mixed $to
    * @param mixed $cc
    * @param mixed $bcc
    * @param mixed $quote_history
    * @param mixed $attachment
    * @param mixed $default_attachment
    * @param mixed $default_subject
    * @param mixed $use_template
    */
    public static function show_email_form($document_id, $intro, $from, $to, $cc, $bcc, $quote_history, $attachment, $default_attachment, $default_subject, $use_template, $language_params = "", $client_credit = array())
    {
        $message_link = html_entity_decode(nbf_cms::$interop->admin_popup_page_prefix) . "&action=email&task=get_message&hide_billing_menu=1&document_id=$document_id";
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.client.php");
        nbf_cms::$interop->add_html_header('<script type="text/javascript" src="' . nbf_cms::$interop->nbill_site_url_path . '/js/admin.js/base64.js"></script>');
        ?>
        <script type="text/javascript">
        function set_default_message()
        {
            if (document.getElementById('attach_email_embed') && document.getElementById('attach_email_embed').checked)
            {
                document.getElementById('no_template').checked = true;
                document.getElementById('template').disabled = true;
                document.getElementById('no_template').disabled = true;
                if (document.getElementById('include_history'))
                {
                    document.getElementById('no_history').checked = true;
                    document.getElementById('include_history').disabled = true;
                    document.getElementById('no_history').disabled = true; //Will be included in embedded message, no need to duplicate
                }
            }
            else
            {
                document.getElementById('template').disabled = false;
                document.getElementById('no_template').disabled = false;
                if (document.getElementById('include_history'))
                {
                    document.getElementById('include_history').disabled = false;
                    document.getElementById('no_history').disabled = false;
                }
            }

            if (document.getElementById('template').checked)
            {
                if ((document.getElementById('attach_email_none') && document.getElementById('attach_email_none').checked))
                {
                    //Template without attachment
                    document.getElementById('ifr_email_message').src = '<?php echo $message_link; ?>&message_type=template_notify<?php echo $language_params; ?>#<?php echo uniqid(); ?>';
                }
                else
                {
                    //Template with attachment
                    document.getElementById('ifr_email_message').src = '<?php echo $message_link; ?>&message_type=template_attach<?php echo $language_params; ?>#<?php echo uniqid(); ?>';
                }
            }
            else
            {
                if (document.getElementById('attach_email_none') == null || document.getElementById('attach_email_none').checked)
                {
                    //Plain text without attachment
                    document.getElementById('ifr_email_message').src = '<?php echo $message_link; ?>&message_type=notify<?php echo $language_params; ?>#<?php echo uniqid(); ?>';
                }
                else if (document.getElementById('attach_email_embed').checked)
                {
                    //Embedded
                    document.getElementById('ifr_email_message').src = '<?php echo $message_link; ?>&message_type=embed<?php echo $language_params; ?>#<?php echo uniqid(); ?>';
                }
                else
                {
                    //Plain text with attachment
                    document.getElementById('ifr_email_message').src = '<?php echo $message_link; ?>&message_type=attach<?php echo $language_params; ?>#<?php echo uniqid(); ?>';
                }
            }
        }

        function send_email()
        {
            //If sending fails, we will not be offering to apply credit again
            var apply_credit = document.getElementById('apply_client_credit').checked ? '1' : '0';
            document.getElementById('apply_client_credit').checked = false;
            if (document.getElementById('nbill_client_credit_message')) {
                document.getElementById('nbill_client_credit_message').style.display = 'none';
            }

            var email_from = encodeURIComponent(document.getElementById('message_from').value);
            var email_to = encodeURIComponent(document.getElementById('message_to').value);
            var email_cc = encodeURIComponent(document.getElementById('message_cc').value);
            var email_bcc = encodeURIComponent(document.getElementById('message_bcc').value);
            var email_subject = encodeURIComponent(document.getElementById('message_subject').value);
            var attach = 'none';
            if (document.getElementById('attach_email_html') && document.getElementById('attach_email_html').checked)
            {
                attach = 'html';
            }
            if (document.getElementById('attach_email_pdf') && document.getElementById('attach_email_pdf').checked)
            {
                attach = 'pdf';
            }
            if (document.getElementById('attach_email_embed') && document.getElementById('attach_email_embed').checked)
            {
                attach = 'embed';
            }
            var template = document.getElementById('template').checked ? 1 : 0;
            var history = (document.getElementById('include_history') && document.getElementById('include_history').checked) ? 1 : 0;
            var elem = window.frames['ifr_email_message'].document.getElementById('email_message')
            if (window.frames['ifr_email_message']['wysiwyg_' + elem.id] && window.frames['ifr_email_message']['wysiwyg_' + elem.id].nicInstances)
            {
                for (index = 0; index < window.frames['ifr_email_message'].nicEditors.editors.length; index++) {
                    for (instance_index = 0; instance_index < window.frames['ifr_email_message'].nicEditors.editors[index].nicInstances.length; instance_index++) {
                        window.frames['ifr_email_message'].nicEditors.editors[index].nicInstances[instance_index].saveContent();
                    }
                }
            }
            var message = base64_encode(encodeURIComponent(elem.value));
            show_wait_message(500, null);
            setTimeout(function(){send_email_do_sjax(email_from, email_to, email_cc, email_bcc, email_subject, attach, template, history, message, apply_credit);}, 50);
        }

        function send_email_do_sjax(email_from, email_to, email_cc, email_bcc, email_subject, attach, template, history, message, apply_credit)
        {
            var result = submit_sjax_request('send_document_email', 'document_id=<?php echo intval($document_id); ?>&email_from=' + email_from + '&email_to=' + email_to + '&email_cc=' + email_cc + '&email_bcc=' + email_bcc + '&email_subject=' + email_subject + '&email_attach=' + attach + '&email_template=' + template + '&email_history=' + history + '&email_message=' + message + '&apply_client_credit=' + apply_credit);
            show_email_result(result);
        }

        function show_email_result(output)
        {
            if (output.length == 0)
            {
                output = '<div class="nbill-message"><?php echo NBILL_EMAIL_SEND_INTERRUPTED; ?></';
                output += 'div><br /><div align="center" style="font-weight:bold;font-size:10pt;"><';
                output += 'a href="#" onclick="adminForm.submit();return false;"><?php echo NBILL_CONTINUE; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<';
                output += 'a href="#" onclick="document.getElementById(\'results\').style.display=\'none\';document.getElementById(\'email_form\').style.display=\'\';window.frames[\'ifr_email_message\'].location.reload();return false;"><?php echo NBILL_TRY_AGAIN; ?></a></';
                output += 'div>';
            }
            document.getElementById('email_form').style.display = 'none';
            document.getElementById('results').style.display = '';
            document.getElementById('results').innerHTML = output;
        }
        </script>
        <div id="results" style="display:none;"></div>
        <div id="email_form">

            <?php
            if ($client_credit && count($client_credit) > 0) {
                ?>
                <div class="message nbill-message" id="nbill_client_credit_message">
                    <?php
                    echo sprintf(NBILL_INVOICE_CLIENT_CREDIT_PROMPT, $client_credit['total_credit'], $client_credit['credit_to_apply'], $client_credit['invoice_new_total'], $client_credit['credit_new_balance']);
                    ?>
                    <br /><br />
                    <input type="checkbox" name="apply_client_credit" id="apply_client_credit" style="display:inline" onclick="var opt_embed = document.getElementById('attach_email_embed');opt_attach = document.getElementById('attach_email_html');if (this.checked) {if (opt_embed.checked){opt_attach.checked = true;set_default_message();};opt_embed.disabled = true;} else {opt_embed.disabled = false;}" /><label for="apply_client_credit" style="display:inline;"><?php echo APPLY_CLIENT_CREDIT; ?></label>
                </div>
                <?php
            }
            else {
                ?><input type="hidden" name="apply_client_credit" id="apply_client_credit" value="0" /><?php
            } ?>

            <?php if (nbf_cms::$interop->show_gzip_warning()) { ?><div class="nbill-message"><?php $url = nbf_cms::$interop->get_gzip_config_url(); echo nbf_common::nb_strlen($url) > 0 ? sprintf(NBILL_GZIP_WARNING_URL, $url) : NBILL_GZIP_WARNING; ?></div><?php } ?>
            <p><?php echo $intro; ?></p>
            <table cellpadding="3" cellspacing="0" border="0">
                <tr>
                    <td style="vertical-align:top"><?php echo NBILL_EMAIL_MESSAGE_FROM; ?></td><td style="vertical-align:top"><input type="text" name="message_from" id="message_from" value="<?php echo $from; ?>" /></td>
                </tr>
                <tr>
                    <td style="vertical-align:top"><?php echo NBILL_EMAIL_MESSAGE_TO; ?></td><td style="vertical-align:top"><input type="text" name="message_to" id="message_to" value="<?php echo is_array($to) ? implode(";", $to) : $to ?>" /></td>
                </tr>
                <tr>
                    <td style="vertical-align:top"><?php echo NBILL_EMAIL_MESSAGE_CC; ?></td><td style="vertical-align:top"><input type="text" name="message_cc" id="message_cc" value="<?php echo is_array($cc) ? implode(";", $cc) : $cc ?>" /></td>
                </tr>
                <tr>
                    <td style="vertical-align:top"><?php echo NBILL_EMAIL_MESSAGE_BCC; ?></td><td style="vertical-align:top"><input type="text" name="message_bcc" id="message_bcc" value="<?php echo is_array($bcc) ? implode(";", $bcc) : $bcc ?>" /></td>
                </tr>
                <tr>
                    <td style="vertical-align:top"><?php echo NBILL_EMAIL_MESSAGE_SUBJECT; ?></td><td style="vertical-align:top"><input type="text" name="message_subject" id="message_subject" value="<?php echo $default_subject; ?>" /></td>
                </tr>
                <?php
                if ($attachment)
                { ?>
                    <tr>
                        <td style="vertical-align:top"><?php echo NBILL_EMAIL_INCLUDE_DOCUMENT; ?></td>
                        <td style="vertical-align:top">
                            <label class="nbill_form_label"><input type="radio" class="nbill_form_input" name="attach_email" id="attach_email_html" value="html"<?php echo $default_attachment == 1 ? " checked=\"checked\"" : ""; ?> onclick="set_default_message();" /><?php echo NBILL_EMAIL_MESSAGE_ATTACH; ?></label><br />
                            <?php if (nbf_common::pdf_writer_available()) { ?>
                            <label class="nbill_form_label"><input type="radio" class="nbill_form_input" name="attach_email" id="attach_email_pdf" value="pdf"<?php echo $default_attachment == 2 ? " checked=\"checked\"" : ""; ?> onclick="set_default_message();" /><?php echo NBILL_EMAIL_MESSAGE_ATTACH_PDF; ?></label><br />
                            <?php } ?>
                            <label class="nbill_form_label"><input type="radio" class="nbill_form_input" name="attach_email" id="attach_email_none" value="none"<?php echo $default_attachment == 0 ? " checked=\"checked\"" : ""; ?> onclick="set_default_message();" /><?php echo NBILL_EMAIL_MESSAGE_NO_ATTACH; ?></label><br />
                            <label class="nbill_form_label"><input type="radio" class="nbill_form_input" name="attach_email" id="attach_email_embed" value="embed"<?php echo $default_attachment == 3 ? " checked=\"checked\"" : ""; ?> onclick="set_default_message();" /><?php echo NBILL_EMAIL_MESSAGE_EMBED; ?></label>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td style="vertical-align:top"><?php echo NBILL_EMAIL_MESSAGE_USE_TEMPLATE; ?></td>
                    <td style="vertical-align:top">
                        <label class="nbill_form_label"><input type="radio" class="nbill_form_input" name="use_template" id="template" value="yes"<?php echo $use_template ? " checked=\"checked\"" : ""; ?> onclick="set_default_message();" /><?php echo NBILL_YES; ?></label>
                        <label class="nbill_form_label"><input type="radio" class="nbill_form_input" name="use_template" id="no_template" value="no"<?php echo !$use_template ? " checked=\"checked\"" : ""; ?> onclick="set_default_message();" /><?php echo NBILL_NO; ?></label>
                    </td>
                </tr>
                <?php if ($quote_history)
                { ?>
                <tr>
                    <td style="vertical-align:top"><?php echo NBILL_QUOTE_SHOW_HISTORY; ?></td>
                    <td style="vertical-align:top">
                        <label class="nbill_form_label"><input class="nbill_form_input" type="radio" name="include_history" id="include_history" value="yes" checked="checked" /><?php echo NBILL_YES; ?></label>
                        <label class="nbill_form_label"><input class="nbill_form_input" type="radio" name="include_history" id="no_history" value="no" /><?php echo NBILL_NO; ?></label>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td style="vertical-align:top"><?php echo NBILL_EMAIL_MESSAGE; ?></td>
                    <td style="vertical-align:top">
                        <iframe name="ifr_email_message" id="ifr_email_message" src="about:blank" style="border:solid 1px #cccccc;" frameborder="0" scrolling="auto"><?php echo NBILL_IFRAMES_REQUIRED; ?></iframe>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="right" style="text-align:right;vertical-align:top;">
                        <input type="button" name="send_message" class="button btn" value="<?php echo NBILL_EMAIL_SEND; ?>" onclick="send_email();" style="font-size:10pt;font-weight:bold;" />
                        &nbsp;
                        <input type="submit" name="cancel_message" class="button btn" value="<?php echo NBILL_EMAIL_CANCEL; ?>" style="font-size:10pt;font-weight:bold;" />
                    </td>
                </tr>
            </table>
        </div>
        <script type="text/javascript">
            set_default_message();
        </script>
        <?php
    }

    /**
    * Get the message to show if embedded HTML invoice is selected
    * @param object $document
    * @param string $contact_name
    * @return string
    */
    public static function get_embedded_message($document)
    {
        $contact_name = nbf_common::nb_strlen(@$document->name) > 0 ? $document->name : (nbf_common::nb_strlen(@$document->billing_name) > 0 ? $document->billing_name : @$document->company_name);
        $embedded_message = sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_GREETING'), $contact_name) . "<br /><br />";
        $basic_live_site = str_replace("https://", "http://", nbf_cms::$interop->live_site);

        switch ($document->document_type)
        {
            case "IN":
                $embedded_message .= str_replace("\n", "<br />", nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_EMBEDDED_INVOICE_INTRO'));
                if (nbf_common::nb_strlen($document->username) == 0)
                {
                    $footer = str_replace("\n", "<br />", sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_INVOICE_PAR_2'), "<a href=\"$basic_live_site\">$basic_live_site</a>", nbf_cms::$interop->site_name));
                }
                else
                {
                    $footer = str_replace("\n", "<br />", sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_INVOICE_PAR_2_USERNAME'), "<a href=\"$basic_live_site\">$basic_live_site</a>", $document->username, nbf_cms::$interop->site_name));
                }
                $footer .= "<br /><br />" . str_replace("\n", "<br />", nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_REGARDS ')). "<br />" . nbf_cms::$interop->site_name;
                break;
            case "CR":
                $embedded_message .= str_replace("\n", "<br />", nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_EMBEDDED_CREDIT_INTRO'));
                if (nbf_common::nb_strlen($document->username) == 0)
                {
                    $footer = str_replace("\n", "<br />", sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_CREDIT_PAR_2'), "<a href=\"$basic_live_site\">$basic_live_site</a>", nbf_cms::$interop->site_name));
                }
                else
                {
                    $footer = str_replace("\n", "<br />", sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_CREDIT_PAR_2_USERNAME'), "<a href=\"$basic_live_site\">$basic_live_site</a>", $document->username, nbf_cms::$interop->site_name));
                }
                $footer .= "<br /><br />" . str_replace("\n", "<br />", nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_REGARDS')) . "<br />" . nbf_cms::$interop->site_name;
                break;
            case "QU":
                $url = nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . "&action=quotes&task=view&cid=" . $document->id . nbf_cms::$interop->site_page_suffix;
                $link = "<a href=\"$url\">$url</a>";
                if (nbf_common::nb_strpos(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_EMBEDDED_QUOTE_INTRO'), "%s") !== false)
                {
                    $embedded_message .= str_replace("\n", "<br />", sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_EMBEDDED_QUOTE_INTRO'), $link));
                }
                else
                {
                    $embedded_message .= str_replace("\n", "<br />", nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_EMBEDDED_QUOTE_INTRO'));
                }
                if (nbf_common::nb_strlen($document->username) == 0)
                {
                    $footer = str_replace("\n", "<br />", sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_QUOTE_PAR_2'), "<a href=\"$basic_live_site\">$basic_live_site</a>", nbf_cms::$interop->site_name));
                }
                else
                {
                    $footer = str_replace("\n", "<br />", sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_QUOTE_PAR_2_USERNAME'), "<a href=\"$basic_live_site\">$basic_live_site</a>", $document->username, nbf_cms::$interop->site_name));
                }
                if (nbf_common::nb_strlen(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_EMBEDDED_QUOTE_PAR_1')) > 0)
                {
                    if (nbf_common::nb_strpos(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_EMBEDDED_QUOTE_PAR_1'), "%s") !== false)
                    {
                        $footer .= "<br /><br />" . str_replace("\n", "<br />", sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_EMBEDDED_QUOTE_PAR_1'), $link));
                    }
                    else
                    {
                        $footer .= "<br /><br />" . str_replace("\n", "<br />", nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_EMBEDDED_QUOTE_PAR_1'));
                    }
                }
                $footer .= "<br /><br />" . str_replace("\n", "<br />", nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_REGARDS')) . "<br />" . nbf_cms::$interop->site_name;
                break;
        }
        $ob_on = ob_get_length() !== false;
        $save_ob = array();
        if ($ob_on)
        {
            while($save_ob[] = @ob_get_clean());
        }
        $task = "silent";
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.html/invoices.html.php");
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/invoices.php");
        $footer = "<div align=\"left\" style=\"text-align:left\">$footer</div>";
        $embedded_message = printPreviewPopup(array($document->id), false, true, str_replace("\n", "<br />", $embedded_message) . "<br /><hr /><br />", "<br /><hr /><br />" . str_replace("\n", "<br />", $footer));
        //Strip out any javascript from the template so we don't trip the XSS protection (we won't need javascript in an embedded message anyway)
        $embedded_message = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $embedded_message);

        if (strpos($embedded_message, '<!DOCTYPE html') !== false && strpos($embedded_message, '<html>') !== false && $ob_on)
        {
            for ($i = count($save_ob) - 1; $i>=0; $i--)
            {
                if ($save_ob[$i] !== false)
                {
                    ob_start();
                    echo $save_ob[$i];
                }
            }
        }

        return $embedded_message;
    }

    /**
    * Get the message to show if plain text with invoice attachment is selected
    * @param object $document
    * @param string $contact_name
    * @return string
    */
    public static function get_attach_message($document)
    {
        $contact_name = nbf_common::nb_strlen(@$document->name) > 0 ? $document->name : (nbf_common::nb_strlen(@$document->billing_name) > 0 ? $document->billing_name : @$document->company_name);
        $attach_message = sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_GREETING'), $contact_name) . "\n\n";
        $basic_live_site = str_replace("https://", "http://", nbf_cms::$interop->live_site);

        switch ($document->document_type)
        {
            case "IN":
                $attach_message .= nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_INVOICE_PAR_1_ATTACHED') . "\n\n";
                if (nbf_common::nb_strlen($document->username) == 0)
                {
                    $attach_message .= sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_INVOICE_PAR_2'), $basic_live_site, nbf_cms::$interop->site_name);
                }
                else
                {
                    $attach_message .= sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_INVOICE_PAR_2_USERNAME'), $basic_live_site, $document->username, nbf_cms::$interop->site_name);
                }
                $attach_message .= "\n\n" . nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_REGARDS'). "\n" . nbf_cms::$interop->site_name;
                break;
            case "CR":
                $attach_message .= nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_CREDIT_PAR_1_ATTACHED'). "\n\n";
                if (nbf_common::nb_strlen($document->username) == 0)
                {
                    $attach_message .= sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_CREDIT_PAR_2'), $basic_live_site, nbf_cms::$interop->site_name);
                }
                else
                {
                    $attach_message .= sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_CREDIT_PAR_2_USERNAME'), $basic_live_site, $document->username, nbf_cms::$interop->site_name);
                }
                $attach_message .= "\n\n" . nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_REGARDS'). "\n" . nbf_cms::$interop->site_name;
                break;
            case "QU":
                switch ($document->status)
                {
                    case "BB": //On hold
                        //Message must be supplied by administrator
                        $attach_message = "";
                        break;
                    case "CC": //Quoted
                    default:
                        $attach_message .= sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_QUOTE_PAR_1_ATTACHED'), nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . "&action=quotes&task=view&id=" . $document->id . nbf_cms::$interop->site_page_suffix);
                        if (nbf_common::nb_strlen($document->username) == 0)
                        {
                            $attach_message .= sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_QUOTE_PAR_2'), $basic_live_site, nbf_cms::$interop->site_name);
                        }
                        else
                        {
                            $attach_message .= sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_QUOTE_PAR_2_USERNAME'), $basic_live_site, $document->username, nbf_cms::$interop->site_name);
                        }
                        $attach_message .= "\n\n" . nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_REGARDS'). "\n" . nbf_cms::$interop->site_name;
                        break;
                }
                break;
        }
        return $attach_message;
    }

    /**
    * Get the message to show if plain text notification (no attachment) is selected
    * @param object $document
    * @param string $contact_name
    * @return string
    */
    public static function get_notify_message($document)
    {
        $contact_name = nbf_common::nb_strlen(@$document->name) > 0 ? $document->name : (nbf_common::nb_strlen(@$document->billing_name) > 0 ? $document->billing_name : @$document->company_name);
        $notify_message = sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_GREETING'), $contact_name) . "\n\n";
        $basic_live_site = str_replace("https://", "http://", nbf_cms::$interop->live_site);

        switch ($document->document_type)
        {
            case "IN":
                $notify_message = sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_GREETING'), $contact_name) . "\n\n" . nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_INVOICE_PAR_1') . "\n\n";
                if (nbf_common::nb_strlen($document->username) == 0)
                {
                    $notify_message .= sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_INVOICE_PAR_2'), $basic_live_site, nbf_cms::$interop->site_name);
                }
                else
                {
                    $notify_message .= sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_INVOICE_PAR_2_USERNAME'), $basic_live_site, $document->username, nbf_cms::$interop->site_name);
                }
                $notify_message .= "\n\n" . nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_REGARDS') . "\n" . nbf_cms::$interop->site_name;
                break;
            case "CR":
                $notify_message = sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_GREETING'), $contact_name) . "\n\n" . nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_CREDIT_PAR_1') . "\n\n";
                if (nbf_common::nb_strlen($document->username) == 0)
                {
                    $notify_message .= sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_CREDIT_PAR_2'), $basic_live_site, nbf_cms::$interop->site_name);
                }
                else
                {
                    $notify_message .= sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_CREDIT_PAR_2_USERNAME'), $basic_live_site, $document->username, nbf_cms::$interop->site_name);
                }
                $notify_message .= "\n\n" . nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_REGARDS') . "\n" . nbf_cms::$interop->site_name;
                break;
            case "QU":
                switch ($document->status)
                {
                    case "BB": //On hold
                        //Message must be supplied by administrator
                        $notify_message = "";
                        break;
                    case "CC": //Quoted
                    default:
                        $notify_message .= sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_QUOTE_PAR_1'), nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . "&action=quotes&task=view&id=" . $document->id . nbf_cms::$interop->site_page_suffix);
                        if (nbf_common::nb_strlen($document->username) == 0)
                        {
                            $notify_message .= sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_QUOTE_PAR_2'), $basic_live_site, nbf_cms::$interop->site_name);
                        }
                        else
                        {
                            $notify_message .= sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_QUOTE_PAR_2_USERNAME'), $basic_live_site, $document->username, nbf_cms::$interop->site_name);
                        }
                        $notify_message .= "\n\n" . nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_REGARDS') . "\n" . nbf_cms::$interop->site_name;
                        break;
                }
                break;
        }
        return $notify_message;
    }

    /**
    * Get the message to show if HTML template with attachment is selected
    * @param object $document
    * @param string $contact_name
    * @return string
    */
    public static function get_template_attach_message($document, $for_editor = false)
    {
        $contact_name = nbf_common::nb_strlen(@$document->name) > 0 ? $document->name : (nbf_common::nb_strlen(@$document->billing_name) > 0 ? $document->billing_name : @$document->company_name);
        switch ($document->document_type)
        {
            case "QU":
                switch ($document->status)
                {
                    case "BB": //On hold
                        //Message must be supplied by administrator
                        return "";
                }
        }
        $vendor_id = $document->vendor_id;
        $document_id = $document->id;
        $template_contents = "";
        $template_col = "invoice_email_template_name";
        switch ($document->document_type)
        {
            case "CR":
                $template_col = "credit_email_template_name";
                break;
            case "QU":
                $template_col = "quote_email_template_name";
                break;
            case "PO":
                $template_col = "po_email_template_name";
                break;
        }
        $file_name = nbf_cms::$interop->nbill_fe_base_path . "/email_templates/" . $document->$template_col . "_attach.php";
        if (file_exists($file_name))
        {
            ob_start();
            include($file_name);
            $template_contents = ob_get_clean();
            if ($for_editor)
            {
                //Grab any CSS (we will have to output it in the body so it can be rendered properly in the editor)
                $css = "";
                $start_pos = nbf_common::nb_strpos($template_contents, "<style");
                if ($start_pos)
                {
                    $end_pos = nbf_common::nb_strpos($template_contents, "</style>", $start_pos) + 8;
                    $css = nbf_common::nb_substr($template_contents, $start_pos, $end_pos - $start_pos);
                }
                //Extract the stuff between the body tags
                $start_pos = nbf_common::nb_strpos($template_contents, ">", nbf_common::nb_strpos($template_contents, "<body")) + 1;
                $end_pos = nbf_common::nb_strpos($template_contents, "</body>", $start_pos);
                $template_contents = $css . nbf_common::nb_substr($template_contents, $start_pos, $end_pos - $start_pos);
            }
        }
        else
        {
            $file_name = nbf_cms::$interop->nbill_fe_base_path . "/email_templates/" . $document->$template_col . ".php";
            if (file_exists($file_name))
            {
                ob_start();
                include($file_name);
                $template_contents = ob_get_clean();
                if ($for_editor)
                {
                    //Grab any CSS (we will have to output it in the body so it can be rendered properly in the editor)
                    $css = "";
                    $start_pos = nbf_common::nb_strpos($template_contents, "<style");
                    if ($start_pos)
                    {
                        $end_pos = nbf_common::nb_strpos($template_contents, "</style>", $start_pos) + 8;
                        $css = nbf_common::nb_substr($template_contents, $start_pos, $end_pos - $start_pos);
                    }
                    //Extract the stuff between the body tags
                    $start_pos = nbf_common::nb_strpos($template_contents, ">", nbf_common::nb_strpos($template_contents, "<body")) + 1;
                    $end_pos = nbf_common::nb_strpos($template_contents, "</body>", $start_pos);
                    $template_contents = $css . nbf_common::nb_substr($template_contents, $start_pos, $end_pos - $start_pos);
                }
            }
        }
        if (nbf_common::nb_strlen($template_contents) == "")
        {
            $template_contents = str_replace("\n", "<br />", self::get_attach_message($document, $contact_name));
        }
        return $template_contents;
    }

    /**
    * Get the message to show if HTML template notification (no attachment) is selected
    * @param object $document
    * @param string $contact_name
    * @return string
    */
    public static function get_template_notify_message($document, $for_editor = false)
    {
        $contact_name = nbf_common::nb_strlen(@$document->name) > 0 ? $document->name : (nbf_common::nb_strlen(@$document->billing_name) > 0 ? $document->billing_name : @$document->company_name);
        $vendor_id = $document->vendor_id;
        $document_id = $document->id;
        $template_contents = "";
        $template_col = "invoice_email_template_name";
        switch ($document->document_type)
        {
            case "CR":
                $template_col = "credit_email_template_name";
                break;
            case "QU":
                $template_col = "quote_email_template_name";
                break;
            case "PO":
                $template_col = "po_email_template_name";
                break;
        }
        $file_name = nbf_cms::$interop->nbill_fe_base_path . "/email_templates/" . $document->$template_col . ".php";
        if (file_exists($file_name))
        {
            ob_start();
            include($file_name);
            $template_contents = ob_get_clean();
            if ($for_editor)
            {
                //Grab any CSS (we will have to output it in the body so it can be rendered properly in the editor)
                $css = "";
                $start_pos = nbf_common::nb_strpos($template_contents, "<style");
                if ($start_pos)
                {
                    $end_pos = nbf_common::nb_strpos($template_contents, "</style>", $start_pos) + 8;
                    $css = nbf_common::nb_substr($template_contents, $start_pos, $end_pos - $start_pos);
                }
                //Extract the stuff between the body tags
                $start_pos = nbf_common::nb_strpos($template_contents, ">", nbf_common::nb_strpos($template_contents, "<body")) + 1;
                $end_pos = nbf_common::nb_strpos($template_contents, "</body>", $start_pos);
                $template_contents = $css . nbf_common::nb_substr($template_contents, $start_pos, $end_pos - $start_pos);
            }
        }
        if (nbf_common::nb_strlen($template_contents) == "")
        {
            $template_contents = str_replace("\n", "<br />", self::get_notify_message($document, $contact_name));
        }
        return $template_contents;
    }

    public static function email_document($document_id, $from = "", $to = "", $cc = "", $bcc = "", $subject = "", $message = "", $email_option = "", $attachments = array())
    {
        $nb_database = nbf_cms::$interop->database;

        $basic_live_site = str_replace("https://", "http://", nbf_cms::$interop->live_site);
        nbf_globals::$message = ""; //Clear out any old errors
        $mailsent = false;
        $failure = false;
        $suppress_attach = false; //Cannot attach quotes that are new or on hold
        $html = 0;
        $at_least_one_sent = false;

        //Load document data
        $document = null;
        $sql = "SELECT #__nbill_document.id, #__nbill_document.document_type, #__nbill_document.document_no,
                        #__nbill_document.entity_id, #__nbill_document.vendor_id, #__nbill_document.uploaded_files,
                        #__nbill_entity_contact.email_invoice_option, #__nbill_document.billing_name,
                        #__nbill_vendor.invoice_email_template_name, #__nbill_vendor.credit_email_template_name,
                        #__nbill_vendor.quote_email_template_name, #__nbill_vendor.po_email_template_name,
                        #__nbill_vendor.admin_email, #__nbill_vendor.vendor_name, #__nbill_contact.email_address, #__nbill_document.status,
                        #__nbill_entity.company_name, #__nbill_entity.default_language, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS `name`, " .
                        nbf_cms::$interop->cms_database_enum->table_user . "." . nbf_cms::$interop->cms_database_enum->column_user_username . " AS username
                        FROM #__nbill_document
                        INNER JOIN #__nbill_vendor ON #__nbill_document.vendor_id = #__nbill_vendor.id
                        LEFT JOIN #__nbill_entity ON #__nbill_document.entity_id = #__nbill_entity.id
                        LEFT JOIN #__nbill_entity_contact ON #__nbill_entity.id = #__nbill_entity_contact.entity_id AND #__nbill_entity_contact.contact_id = #__nbill_entity.primary_contact_id
                        LEFT JOIN #__nbill_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id
                        LEFT JOIN " . nbf_cms::$interop->cms_database_enum->table_user . " ON #__nbill_contact.user_id = " . nbf_cms::$interop->cms_database_enum->table_user . "." . nbf_cms::$interop->cms_database_enum->column_user_id . "
                        WHERE #__nbill_document.id = " . intval($document_id);
        $nb_database->setQuery($sql);
        $nb_database->loadObject($document);

        if (nbf_common::nb_strlen($subject) == 0)
        {
            switch ($document->document_type)
            {
                case "CR":
                    $subject = sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_CREDIT_SUBJECT'), $document->document_no, nbf_cms::$interop->site_name);
                    break;
                case "QU":
                    switch ($document->status)
                    {
                        case "BB":
                            $subject = sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_QUOTE_ON_HOLD_SUBJECT'), $document->document_no, nbf_cms::$interop->site_name);
                            //Fall through
                        case "AA":
                            $suppress_attach = true;
                            break;
                        case "QU":
                        default:
                            $subject = sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_QUOTE_SUBJECT'), $document->document_no, nbf_cms::$interop->site_name);
                            break;
                    }
                    break;
                default:
                    $subject = sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_INVOICE_SUBJECT'), $document->document_no, nbf_cms::$interop->site_name);
                    break;
            }
        }

        $admin_email = nbf_common::nb_strlen($from) > 0 ? $from : $document->admin_email;
        $admin_name = $document->vendor_name;

        $bcc = explode(";", $bcc);

        $email_invoice_queue = array();
        if (nbf_common::nb_strlen($to) > 0)
        {
            foreach (explode(";", $to) as $to_address)
            {
                $contact = new stdClass();
                $contact->email_address = $to_address;
                $contact->is_primary_contact = true;
                $email_invoice_queue[$email_option][] = $contact;
            }
            foreach (explode(";", $cc) as $cc_address)
            {
                $contact = new stdClass();
                $contact->email_address = $cc_address;
                $contact->is_primary_contact = false;
                $email_invoice_queue[$email_option][] = $contact;
            }
        }
        else
        {
            //Load all the contacts for the related client - check if they are allowed to see the invoice, how they like their invoices, and send
            $sql = "SELECT #__nbill_contact.email_address, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS `name`, #__nbill_entity.company_name,
                        #__nbill_entity.primary_contact_id = #__nbill_contact.id AS is_primary_contact,
                        #__nbill_entity_contact.allow_invoices, #__nbill_entity_contact.email_invoice_option, " .
                        nbf_cms::$interop->cms_database_enum->table_user . "." . nbf_cms::$interop->cms_database_enum->column_user_username  ." AS username
                        FROM #__nbill_entity
                        INNER JOIN #__nbill_entity_contact ON #__nbill_entity.id = #__nbill_entity_contact.entity_id
                        LEFT JOIN #__nbill_contact ON #__nbill_entity_contact.contact_id = #__nbill_contact.id
                        INNER JOIN #__nbill_document ON #__nbill_document.entity_id = #__nbill_entity.id
                        LEFT JOIN " . nbf_cms::$interop->cms_database_enum->table_user . " ON #__nbill_contact.user_id = " .
                        nbf_cms::$interop->cms_database_enum->table_user . "." . nbf_cms::$interop->cms_database_enum->column_user_id . "
                        WHERE #__nbill_document.id = " . intval($document_id);
            $nb_database->setQuery($sql);
            $contacts = $nb_database->loadObjectList();
            foreach ($contacts as $contact)
            {
                if ($contact->allow_invoices)
                {
                    if (!isset($email_invoice_queue[$contact->email_invoice_option]))
                    {
                        $email_invoice_queue[$contact->email_invoice_option] = array();
                    }
                    $email_invoice_queue[$contact->email_invoice_option][] = $contact;
                }
            }
        }

        foreach ($email_invoice_queue as $format_option=>$contacts)
        {
            if (count($contacts) > 0)
            {
                //Get to/cc addresses
                $to = array();
                $cc = array();
                $contact_name = "";
                foreach ($contacts as $contact)
                {
                    if ($contact->is_primary_contact)
                    {
                        $to = array_merge($to, explode(";", $contact->email_address));
                    }
                    else
                    {
                        $cc = array_merge($cc, explode(";", $contact->email_address));
                    }
                }
                if (count($to) == 0)
                {
                    $to = $cc;
                    $cc = array();
                }

                if (nbf_common::nb_strlen($message) == 0)
                {
                    switch ($format_option)
                    {
                        case "AA":
                            $message = "";
                            continue;
                        case "BB":
                            //Send HTML e-mail with embedded document
                            if ($suppress_attach)
                            {
                                $message = self::get_notify_message($document);
                            }
                            $message = self::get_embedded_message($document);
                            $html = 1;
                            break;
                        case "AB":
                        case "AC":
                            //Send plain text email with HTML or PDF attachment
                            $message = self::get_attach_message($document);
                            if ($suppress_attach)
                            {
                                $message = self::get_notify_message($document);
                            }
                            break;
                        case "CC":
                            //Send plain text notification
                            $message = self::get_notify_message($document);
                            break;
                        case "DD":
                            //Send HTML notification
                            $message = self::get_template_notify_message($document);
                            $html = 1;
                            break;
                        case "EE":
                        case "FF":
                        default:
                            //Send HTML email with attachment
                            $message = self::get_template_attach_message($document);
                            $html = 1;
                            break;
                    }
                }
                else
                {
                    switch ($format_option)
                    {
                        case "BB":
                        case "DD":
                        case "EE":
                        case "FF":
                            $html = 1;
                            break;
                    }
                }

                $attachment = "";
                if (!$suppress_attach)
                {
                    switch ($format_option)
                    {
                        case "AB":
                        case "AC":
                        case "EE":
                        case "FF":
                            //Sort out attachment
                            $id_array[] = $document_id;
                            //If output buffering is on, we will need to put it back on afterwards...
                            $ob_on = ob_get_length() !== false;
                            $task = "silent";
                            include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.html/invoices.html.php");
                            include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/invoices.php");

                            switch ($format_option)
                            {
                                case "AC":
                                case "FF":
                                    //HTML2PS PDF Writer will ONLY output to its own /out/ directory
                                    $attachment = '';
                                    if (defined("OUTPUT_FILE_DIRECTORY"))
                                    {
                                        $attachment = OUTPUT_FILE_DIRECTORY . $document->document_no . ".pdf";
                                    }
                                    else
                                    {
                                        $generator = 'dompdf';
                                        $path_to_pdfwriter = nbf_common::get_path_to_pdf_writer($generator);
                                        if ($generator != 'dompdf') {
                                            $attachment = $path_to_pdfwriter . "/out/" . $document->document_no . ".pdf";
                                        } else {
                                            $attachment = nbf_cms::$interop->site_temp_path;
                                            if (nbf_file::is_folder_writable($attachment)) {
                                                $attachment .= "/" . preg_replace("/[^A-Za-z0-9]/", "_", $document->document_no) . ".pdf";
                                            } else {
                                                trigger_error(sprintf(NBILL_TEMP_NOT_WRITABLE, $attachment), E_USER_WARNING);
                                                $attachment = "";
                                            }
                                        }
                                    }
                                    //$invoice_filename = preg_replace("/[^A-Za-z0-9]/", "_", $document->document_no);
                                    $invoice_html = printPDFPopup($id_array, true, true, $attachment);
                                    break;
                                default:
                                    $attachment = nbf_cms::$interop->site_temp_path;
                                    if (nbf_file::is_folder_writable($attachment))
                                    {
                                        $attachment .= "/" . preg_replace("/[^A-Za-z0-9]/", "_", $document->document_no) . ".html";
                                    }
                                    else
                                    {
                                        trigger_error(sprintf(NBILL_TEMP_NOT_WRITABLE, $attachment), E_USER_WARNING);
                                        $attachment = "";
                                    }
                                    if ($attachment)
                                    {
                                        $invoice_html = printPreviewPopup($id_array, false, true, "", "");
                                        $handle = fopen($attachment, "w");
                                        if (!fwrite($handle, $invoice_html))
                                        {
                                            trigger_error(sprintf(NBILL_NO_WRITE_ACCESS, $attachment), E_USER_WARNING);
                                        }
                                        fclose($handle);
                                    }
                                    break;
                            }
                            if ($ob_on)
                            {
                                ob_start();
                            }
                    }
                }

                //Attach any related files that are attached to this document
                $files = $attachments;
                $files[] = $attachment;
                if (nbf_common::nb_strlen($document->uploaded_files) > 0)
                {
                    $file_uploads = explode("\n", str_replace("\r", "", $document->uploaded_files));
                    foreach ($file_uploads as $file_upload)
                    {
                        if (file_exists($file_upload))
                        {
                            $files[] = $attachment;
                        }
                    }
                }

                //Send email
                if (nbf_common::nb_strlen($message) > 0)
                {
                    $mailsent = nbf_cms::$interop->send_email($admin_email, $admin_name, $to, $subject, $message, $html, $cc, $bcc, $files);
                }

                //After send:
                if (nbf_common::nb_strlen($attachment) > 0)
                {
                    if (nbf_file::is_folder_writable(dirname($attachment)))
                    {
                        @unlink($attachment);
                    }
                }

                if (nbf_common::nb_strlen($message) > 0)
                {
                    $status = ""; //Empty string = unknown error (OK = ok, other = other error message)
                    if (!$mailsent)
                    {
                        $failure = true;
                        $status = nbf_globals::$message;
                    }
                    else
                    {
                        $status = "OK";
                        $at_least_one_sent = true;
                    }

                    //Insert entry in email log
                    $sql = "INSERT INTO #__nbill_email_log (`type`, `entity_id`, `document_id`, `from`, `to`, `cc`, `bcc`, `timestamp`, `status`, `subject`, `message`, `html`)
                                VALUES ('" . $document->document_type . "', " . intval($document->entity_id) . ", " . intval($document->id) . ", '" . $admin_email . "', '" . implode(";", $to) . "', '" . implode(";", $cc) . "',
                                '" . implode(";", $bcc) . "', " . nbf_common::nb_time() . ", '" . $nb_database->getEscaped($status) . "', '" . $nb_database->getEscaped($subject) . "', '" . $nb_database->getEscaped($message) . "', " . intval($html) . ")";
                    $nb_database->setQuery($sql);
                    $nb_database->query();

                    //Delete any log entries more than 1 year old
                    $sql = "DELETE FROM #__nbill_email_log WHERE timestamp < " . nbf_common::nb_strtotime("- 1 Year");
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                }

                //Re-initialise for the next loop
                $message = "";
                $html = 0;
                $attachment = "";
            }
        }

        if ($failure)
        {
            //Record error
            if (count($to) == 0)
            {
                $sql = "UPDATE #__nbill_document SET email_sent = -1 WHERE id = " . intval($document_id);
            }
            else
            {
                $sql = "UPDATE #__nbill_document SET email_sent = -2 WHERE id = " . intval($document_id);
            }
            $nb_database->setQuery($sql);
            $nb_database->query();
        }
        else
        {
            if ($at_least_one_sent)
            {
                $sql = "UPDATE #__nbill_document SET email_sent = " . nbf_common::nb_time() . " WHERE id = " . intval($document_id);
                $nb_database->setQuery($sql);
                $nb_database->query();
            }
        }

        return !$failure;
    }

    
}