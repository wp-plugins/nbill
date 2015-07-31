<?php
/**
* This gateway was developed by and is copyright of Netshine Software Limited.
* Sections of code may be copyrighted to other parties (eg. where sample code was used
* from the Paypal documentation). All parts (of this gateway only) written by
* Netshine Software Limited are licensed for use in any way you wish, as long
* as this copyright message remains intact, and without any guarantee of any sort -
* use at your own risk.
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nbillPaypalFunctions
{
    public static function showIntro($selected_tab, $gateway_id)
    {
        if ($gateway_id) {
            $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/large/"; ?>
            <div id="nbill-toolbar-container">
                <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
                <tr valign="middle" align="center">
                    <td>
                        <a class="nbill-toolbar" href="<?php echo nbf_cms::$interop->admin_page_prefix ?>&action=gateway&task=edit&cid=<?php echo $gateway_id; ?>">
                            <img src="<?php echo $image_path ; ?>payment.gif" width="32" height="32" alt="<?php echo NBILL_PAYPAL_GATEWAY_SETTINGS; ?>" align="middle" border="0" />
                            <br /><?php echo NBILL_PAYPAL_GATEWAY_SETTINGS;?></a>
                    </td>
                </tr>
                </table>
            </div>
        <?php } ?>

        <h4><?php echo NBILL_PAYPAL_FUNCTIONS_TITLE; ?></h4>
        <p><?php echo NBILL_PAYPAL_FUNCTIONS_INTRO; ?></p>

        <div id="nbill-extension-tabs">
            <div style="float:left;">
                <div <?php if ($selected_tab == 'invoices') { ?>class="nbill-ext-tab-selected"<?php } else { ?>onmouseout="this.style.backgroundPosition='left';" onmouseover="this.style.backgroundPosition='right';" class="nbill-ext-tab" style="background-position: left center;"<?php } ?>>
                    <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=gateway&task=functions&gateway=paypal&sub_task=invoices">
                        <img border="0" align="middle" title="<?php echo NBILL_PAYPAL_PREAPP_INVOICES; ?>" alt="<?php echo NBILL_MNU_INVOICES; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path ?>/images/icons/invoices.gif">
                        <span class="nbill-ext-tab-text"><?php echo NBILL_PAYPAL_FUNCTIONS_INVOICES; ?></span>
                    </a>
                </div>
            </div>
            <div style="float:left;">
                <div <?php if ($selected_tab == 'clients') { ?>class="nbill-ext-tab-selected"<?php } else { ?>onmouseout="this.style.backgroundPosition='left';" onmouseover="this.style.backgroundPosition='right';" class="nbill-ext-tab" style="background-position: left center;"<?php } ?>>
                    <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=gateway&task=functions&gateway=paypal&sub_task=clients">
                        <img border="0" align="middle" title="<?php echo NBILL_PAYPAL_PREAPP_CLIENTS; ?>" alt="<?php echo NBILL_MNU_CLIENTS; ?>" src="<?php echo nbf_cms::$interop->nbill_site_url_path ?>/images/icons/clients.gif">
                        <span class="nbill-ext-tab-text"><?php echo NBILL_PAYPAL_FUNCTIONS_CLIENTS; ?></span>
                    </a>
                </div>
            </div>
        </div>
        <hr style="border:solid 1px #cccccc;margin-top:0px;" />
        <?php
    }

    public static function showPreAuths($rows, $pagination)
    {
        ?>
        <script type="text/javascript" src="<?php echo nbf_cms::$interop->nbill_site_url_path;?>/nbill_overlib_mini.js"></script>

        <script type="text/javascript">
        function nbill_pp_submit_task(task_name) {
            document.getElementById('process').value = task_name;
            document.adminForm.submit();
        }
        </script>

        <?php $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/"; ?>
        <div id="nbill-toolbar-container">
            <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
            <tr valign="middle" align="center">
                <!-- New button -->
                <td>
                    <a class="nbill-toolbar" href="#" onclick="nbill_pp_submit_task('new');return false;">
                        <img src="<?php echo $image_path ; ?>add.png" alt="<?php echo NBILL_TB_NEW; ?>" align="middle" border="0" />
                        <br /><?php echo NBILL_TB_NEW;?></a>
                </td>
                <!-- Spacer -->
                <td>&nbsp;</td>
                <!-- Delete button -->
                <td>
                    <a class="nbill-toolbar" href="#" onclick="nbill_pp_submit_task('delete');return false;">
                        <img src="<?php echo $image_path ; ?>delete.png" alt="<?php echo NBILL_TB_DELETE; ?>" align="middle" border="0" />
                        <br /><?php echo NBILL_TB_DELETE;?></a>
                </td>
            </tr>
            </table>
        </div>

        <table class="adminheading" style="width:auto;">
        <tr>
            <th align="left" <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, 'pp_preauth'); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_PAYPAL_FUNCTIONS_CLIENTS_DESC; ?>
            </th>
        </tr>
        </table>

        <div class="nbill-message-ie-padding-bug-fixer"></div>
        <?php
        if (nbf_common::nb_strlen(nbf_globals::$message) > 0) {
            echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
        }
        ?>

        <p><?php echo NBILL_PAYPAL_PREAPP_CLIENTS_INTRO; ?></p>

        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
        <input type="hidden" name="task" value="functions" />
        <input type="hidden" name="gateway" value="paypal" />
        <input type="hidden" name="sub_task" value="clients" />
        <input type="hidden" name="process" id="process" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0" />

        <?php
        //Display filter for client name
        echo "<p align=\"left\">";
        $client_search = nbf_common::get_param($_REQUEST,'client_search');
        $client_user_search = nbf_common::get_param($_REQUEST,'client_user_search');
        $client_email_search = nbf_common::get_param($_REQUEST,'client_email_search');
        echo NBILL_CLIENT . " <input type=\"text\" name=\"client_search\" value=\"" . $client_search . "\" />&nbsp; ";
        echo NBILL_CLIENT_USER . " <input type=\"text\" name=\"client_user_search\" value=\"" . $client_user_search . "\" />&nbsp; ";
        echo NBILL_EMAIL_ADDRESS . " <input type=\"text\" name=\"client_email_search\" value=\"" . $client_email_search . "\" />";
        echo "&nbsp;&nbsp;<input type=\"submit\" class=\"button btn\" name=\"dosearch\" value=\"" . NBILL_GO . "\" />";
        echo "</p>";
        ?>

        <table class="adminlist table table-striped">
        <tr>
            <th align="center" style="text-align:center">
            #
            </th>
            <th align="center" style="text-align:center">
                <input type="checkbox" name="check_all" value="" onclick="for(var i=0; i<<?php echo count($rows); ?>;i++) {document.getElementById('cb' + i).checked=this.checked;} document.adminForm.box_checked.value=this.checked;" />
            </th>
            <th align="left" class="title">
                <?php echo NBILL_PAYPAL_PREAPP_RESOURCE_ID; ?>
            </th>
            <th align="left" class="title">
                <?php echo NBILL_CLIENT_NAME; ?>
            </th>
            <th align="left" class="title">
                <?php echo NBILL_CLIENT_USER; ?>
            </th>
            <th align="left" class="title">
                <?php echo NBILL_EMAIL_ADDRESS; ?>
            </th>
            <th align="left" class="title">
                <?php echo NBILL_PAYPAL_PREAPP_DATE; ?>
            </th>
            <th align="left" class="title">
                <?php echo NBILL_PAYPAL_PREAPP_MAX_AMOUNT; ?>
            </th>
            <th align="left" class="title">
                <?php echo NBILL_PAYPAL_PREAPP_STATUS; ?>
            </th>
        </tr>
        <?php
            for ($i=0, $n=count( $rows ); $i < $n; $i++) {
                $row = &$rows[$i];
                echo "<tr>";
                echo "<td align=\"center\" style=\"text-align:center\">";
                echo $pagination->list_offset + $i + 1;
                $checked = nbf_html::id_checkbox($i, $row->id);
                echo "</td><td align=\"center\" style=\"text-align:center\">$checked</td>";
                echo "<td align=\"left\">" . $row->resource_id . "</td>";
                echo "<td align=\"left\">";
                if ($row->entity_id) {
                    echo '<a href="' . nbf_cms::$interop->admin_page_prefix . '&action=clients&task=edit&cid=' . $row->entity_id . '&return=' . base64_encode(nbf_cms::$interop->admin_page_prefix . '&action=gateway&task=functions&gateway=paypal&sub_task=clients') . '">';
                }
                $client_name = $row->company_name;
                if (nbf_common::nb_strlen($row->name) > 0) {
                    if (nbf_common::nb_strlen($row->company_name) > 0) {
                        $client_name .= " (";
                    }
                    $client_name .= $row->name;
                    if (nbf_common::nb_strlen($row->company_name) > 0) {
                        $client_name .= ")";
                    }
                }
                echo $client_name;
                if ($row->entity_id) {
                    echo "</a>";
                }
                echo "</td>";
                echo "<td align=\"left\">$row->username";
                echo "</td>";
                echo "<td align=\"left\"><a href=\"mailto:" . $row->email_address . "\">" . $row->email_address . "</a></td>";
                echo "<td align=\"left\">" . nbf_common::nb_date(nbf_common::get_date_format(), $row->created_date) . "</td>";
                echo "<td align=\"left\">" . $row->currency . ' ' . format_number($row->amount) . "</td>";
                echo "<td align=\"left\">" . ($row->created_date < strtotime('-1 year') ? 'EXPIRED' : $row->status) . "</td>";
                echo "</tr>";
            }
        ?>
        <tr class="nbill_tr_no_highlight"><td colspan="10" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
        </table>

        </form>
        <?php
    }

    public static function showClients($rows, $pagination)
    {
        ?>
        <script type="text/javascript" src="<?php echo nbf_cms::$interop->nbill_site_url_path;?>/nbill_overlib_mini.js"></script>

        <script type="text/javascript">
        function nbill_pp_submit_task(task_name) {
            document.getElementById('process').value = task_name;
            document.adminForm.submit();
        }
        </script>

        <?php $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/"; ?>
        <div id="nbill-toolbar-container">
            <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
            <tr valign="middle" align="center">
                <!-- Back button -->
                <td>
                    <a class="nbill-toolbar" href="#" onclick="nbill_pp_submit_task('back');return false;">
                        <img src="<?php echo $image_path ; ?>back.png" alt="<?php echo NBILL_TB_BACK; ?>" align="middle" border="0" />
                        <br /><?php echo NBILL_TB_BACK;?>
                    </a>
                </td>
                <!-- Spacer -->
                <td>&nbsp;</td>
                <!-- Invite button -->
                <td>
                    <a class="nbill-toolbar" href="#" onclick="nbill_pp_submit_task('invite');return false;">
                        <img src="<?php echo $image_path ; ?>pp_invite.png" alt="<?php echo NBILL_PAYPAL_TB_INVITE; ?>" align="middle" border="0" />
                        <br /><?php echo NBILL_PAYPAL_TB_INVITE;?>
                    </a>
                </td>
            </tr>
            </table>
        </div>

        <table class="adminheading" style="width:auto;">
        <tr>
            <th align="left" <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, 'clients'); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_PAYPAL_FUNCTIONS_CLIENTS_DESC; ?>
            </th>
        </tr>
        </table>

        <div class="nbill-message-ie-padding-bug-fixer"></div>
        <?php
        if (nbf_common::nb_strlen(nbf_globals::$message) > 0) {
            echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
        }
        ?>

        <p><?php echo NBILL_PAYPAL_NEW_PREAPP_INTRO; ?></p>

        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
        <input type="hidden" name="task" value="functions" />
        <input type="hidden" name="sub_task" value="clients" />
        <input type="hidden" name="gateway" value="paypal" />
        <input type="hidden" name="process" id="process" value="new" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0" />

        <?php
        //Display filter for client name
        echo "<p align=\"left\">";
        $client_search = nbf_common::get_param($_REQUEST,'client_search');
        $client_user_search = nbf_common::get_param($_REQUEST,'client_user_search');
        $client_email_search = nbf_common::get_param($_REQUEST,'client_email_search');
        $pp_client_filter = nbf_common::get_param($_REQUEST, 'pp_client_filter');
        echo NBILL_CLIENT . " <input type=\"text\" name=\"client_search\" value=\"" . $client_search . "\" style=\"width:160px;\" />&nbsp; ";
        echo NBILL_CLIENT_USER . " <input type=\"text\" name=\"client_user_search\" value=\"" . $client_user_search . "\" style=\"width:160px;\" />&nbsp; ";
        echo NBILL_EMAIL_ADDRESS . " <input type=\"text\" name=\"client_email_search\" value=\"" . $client_email_search . "\" style=\"width:160px;\" />&nbsp; ";
        echo NBILL_PAYPAL_CLIENT_FILTER;
        ?>
        <select name="pp_client_filter" id="pp_client_filter" style="width:160px;" onchange="document.adminForm.submit();">
            <option value="uninvited"<?php if (nbf_common::get_param($_REQUEST, 'pp_client_filter') == 'uninvited') {echo ' selected="selected"';} ?>><?php echo NBILL_PAYPAL_FILTER_UNINVITED; ?></option>
            <option value="unauthorised"<?php if (nbf_common::get_param($_REQUEST, 'pp_client_filter') == 'unauthorised') {echo ' selected="selected"';} ?>><?php echo NBILL_PAYPAL_FILTER_UNAUTHORISED; ?></option>
            <option value="unaccepted"<?php if (nbf_common::get_param($_REQUEST, 'pp_client_filter') == 'unaccepted') {echo ' selected="selected"';} ?>><?php echo NBILL_PAYPAL_FILTER_UNACCEPTED; ?></option>
            <option value="authorised"<?php if (nbf_common::get_param($_REQUEST, 'pp_client_filter') == 'authorised') {echo ' selected="selected"';} ?>><?php echo NBILL_PAYPAL_FILTER_AUTHORISED; ?></option>
            <option value="all"<?php if (nbf_common::get_param($_REQUEST, 'pp_client_filter') == 'all') {echo ' selected="selected"';} ?>><?php echo NBILL_PAYPAL_FILTER_ALL; ?></option>
        </select>
        <?php
        echo "&nbsp;&nbsp;<input type=\"submit\" class=\"button btn\" name=\"dosearch\" value=\"" . NBILL_GO . "\" />";
        echo "</p>";
        ?>

        <table class="adminlist table table-striped">
        <tr>
            <th align="center" style="text-align:center">
            #
            </th>
            <th align="center" style="text-align:center">
                <input type="checkbox" name="check_all" value="" onclick="for(var i=0; i<<?php echo count($rows); ?>;i++) {document.getElementById('cb' + i).checked=this.checked;} document.adminForm.box_checked.value=this.checked;" />
            </th>
            <th align="left" class="title">
                <?php echo NBILL_CLIENT_NAME; ?>
            </th>
            <th align="left" class="title">
                <?php echo NBILL_CLIENT_USER; ?>
            </th>
            <th align="left" class="title">
                <?php echo NBILL_EMAIL_ADDRESS; ?>
            </th>
            <th align="left" class="title">
                <?php echo NBILL_WEBSITE; ?>
            </th>
            <th align="left" class="title">
                <?php echo NBILL_TELEPHONE; ?>
            </th>
        </tr>
        <?php
            for ($i=0, $n=count( $rows ); $i < $n; $i++) {
                $row = &$rows[$i];
                $link = nbf_cms::$interop->admin_page_prefix . "&action=" . nbf_common::get_param($_REQUEST, 'action') . "&gateway=paypal&task=functions&sub_task=clients&process=invite&cid=$row->id&client_search=$client_search&client_user_search=$client_user_search&client_email_search=$client_email_search&pp_client_filter=$pp_client_filter";
                echo "<tr>";
                echo "<td align=\"center\" style=\"text-align:center\">";
                echo $pagination->list_offset + $i + 1;
                $checked = nbf_html::id_checkbox($i, $row->id);
                echo "</td><td align=\"center\" style=\"text-align:center\">$checked</td>";
                echo "<td align=\"left\">";
                $client_name = $row->company_name;
                if (nbf_common::nb_strlen($row->name) > 0) {
                    if (nbf_common::nb_strlen($row->company_name) > 0) {
                        $client_name .= " (";
                    }
                    $client_name .= $row->name;
                    if (nbf_common::nb_strlen($row->company_name) > 0) {
                        $client_name .= ")";
                    }
                }
                echo "<div style=\"float:left\">";
                if (!$row->invitation_id) {
                    echo "<a href=\"$link\" title=\"" . NBILL_EDIT_CLIENT . "\">";
                }
                echo $client_name;
                if ($row->invitation_id) {
                    echo ' - <strong>' . sprintf(NBILL_PAYPAL_INVITATION_SENT, nbf_common::nb_date(nbf_common::get_date_format(), $row->invitation_sent)) . '</strong> [<a href="' . nbf_cms::$interop->admin_page_prefix . '&action=gateway&gateway=paypal&task=functions&sub_task=clients&process=resend_invite&id=' . intval($row->invitation_id) . '&client_search=' . $client_search . '&client_user_search=' . $client_user_search . '&client_email_search=' . $client_email_search . '&pp_client_filter=' . $pp_client_filter . '">' . NBILL_PAYPAL_INVITATION_RESEND . '</a>]';
                } else {
                    echo "</a>";
                }
                echo "</div>";
                ?>
                <?php
                echo "</td>";
                echo "<td align=\"left\">$row->username";
                echo "</td>";
                echo "<td align=\"left\"><a href=\"mailto:" . $row->email_address . "\">" . $row->email_address . "</a></td>";
                if (nbf_common::nb_strlen($row->website_url) > 0 && substr($row->website_url, 0, 7) != "http://") {
                    $url = "http://" . $row->website_url;
                } else {
                    $url = $row->website_url;
                }
                echo "<td align=\"left\"><a href=\"" . $url . "\" target=\"_blank\">" . $row->website_url . "</a></td>";
                echo "<td align=\"left\">" . $row->telephone . "</td>";
                echo "</tr>";
            }
        ?>
        <tr class="nbill_tr_no_highlight"><td colspan="9" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
        </table>

        </form>
        <?php
    }

    public static function inviteClient($invitation_id, $multi_client, $client_id, $client_name, $contact_first_name, $contact_last_name, $default_vendor_name, $from, $to, $contact_email, $max_amount, $payment_count, $preapp_desc, $currencies, $default_vendor_currency)
    {
        $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/"; ?>
        <div id="nbill-toolbar-container">
            <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
            <tr valign="middle" align="center">
                <!-- Back button -->
                <td>
                    <a class="nbill-toolbar" href="#" onclick="nbill_pp_submit_task('abort_invite');return false;">
                        <img src="<?php echo $image_path ; ?>back.png" alt="<?php echo NBILL_TB_BACK; ?>" align="middle" border="0" />
                        <br /><?php echo NBILL_TB_BACK;?></a>
                </td>
            </tr>
            </table>
        </div>

        <table class="adminheading" style="width:auto;">
        <tr>
            <th align="left" <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, 'clients'); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . sprintf(NBILL_PAYPAL_FUNCTIONS_CLIENT_INVITE, $client_name); ?>
            </th>
        </tr>
        </table>

        <?php
        $message_link = html_entity_decode(nbf_cms::$interop->admin_popup_page_prefix) . "&action=gateway&gateway=paypal&task=functions&sub_task=clients&process=get_message&client_id=" . ($multi_client ? 'multi' : intval($client_id)) . "&hide_billing_menu=1";
        ?>

        <script type="text/javascript">
        function nbill_pp_submit_task(task_name)
        {
            document.getElementById('process').value = task_name;
            document.adminForm.submit();
        }
        function send_email()
        {
            var elem = window.frames['ifr_email_message'].document.getElementById('email_message')
            if (window.frames['ifr_email_message'][elem.id] && window.frames['ifr_email_message'][elem.id].nicInstances) {
                for(var i=0;i<window.frames['ifr_email_message'][elem.id].nicInstances.length;i++){window.frames['ifr_email_message'][elem.id].nicInstances[i].saveContent();}
            }
            var message = encodeURIComponent(elem.value);
            document.getElementById('html_message').value=message;
            nbill_pp_submit_task('send_invitation');
        }
        </script>

        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
        <input type="hidden" name="task" value="functions" />
        <input type="hidden" name="sub_task" value="clients" />
        <input type="hidden" name="gateway" value="paypal" />
        <input type="hidden" name="process" id="process" value="" />
        <input type="hidden" name="cid" id="cid" value="<?php echo $multi_client ? implode(",", nbf_common::get_param($_REQUEST, 'cid')) : intval($client_id); ?>" />
        <input type="hidden" name="multi_client" id="multi_client" value="<?php echo $multi_client ? '1' : '0'; ?>" />
        <input type="hidden" name="invitation_id" id="invitation_id" value="<?php echo $invitation_id; ?>" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0" />
        <input type="hidden" name="html_message" id="html_message" value="" />
        <input type="hidden" name="client_search" id="client_search" value="<?php echo nbf_common::get_param($_REQUEST, 'client_search'); ?>" />
        <input type="hidden" name="client_user_search" id="client_user_search" value="<?php echo nbf_common::get_param($_REQUEST, 'client_user_search'); ?>" />
        <input type="hidden" name="client_email_search" id="client_email_search" value="<?php echo nbf_common::get_param($_REQUEST, 'client_email_search'); ?>" />
        <input type="hidden" name="pp_client_filter" id="pp_client_filter" value="<?php echo nbf_common::get_param($_REQUEST, 'pp_client_filter'); ?>" />
        <input type="hidden" name="client_name" value="<?php echo $client_name; ?>" />
        <input type="hidden" name="first_name" value="<?php echo $contact_first_name; ?>" />
        <input type="hidden" name="last_name" value="<?php echo $contact_last_name; ?>" />

        <div id="email_form">
            <?php if (nbf_cms::$interop->show_gzip_warning()) { ?><div class="nbill-message"><?php $url = nbf_cms::$interop->get_gzip_config_url(); echo nbf_common::nb_strlen($url) > 0 ? sprintf(NBILL_GZIP_WARNING_URL, $url) : NBILL_GZIP_WARNING; ?></div><?php } ?>
            <p><?php echo NBILL_PAYPAL_PREAPP_INVITE_INTRO; ?></p>

            <table cellpadding="3" cellspacing="0" border="0">
                <tr>
                    <td><?php echo NBILL_PAYPAL_MAX_AMOUNT; ?></td>
                    <td>
                        <select name="currency" id="currency">
                            <?php foreach ($currencies as $currency) {
                                ?>
                                <option value="<?php echo $currency; ?>"<?php if ($currency == $default_vendor_currency) { echo ' selected="selected"'; } ?>><?php echo $currency; ?></option>
                                <?php
                            } ?>
                        </select>
                        <input type="text" name="max_amount" id="max_amount" value="<?php echo format_number($max_amount, 2); ?>" style="width:150px;" />
                        <?php echo NBILL_PAYPAL_MAX_PAYMENT_COUNT; ?>
                        <input type="text" name="payment_count" id="payment_count" value="<?php echo $payment_count; ?>" style="width: 100px;" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo NBILL_PAYPAL_PREAPP_DESCRIPTION; ?>
                    </td>
                    <td colspan="2">
                        <textarea name="description" id="description" style="width:250px;"><?php echo $preapp_desc; ?></textarea>
                    </td>
                </tr>
            </table>
            <hr style="border:solid 1px #cccccc;" />
            <table cellpadding="3" cellspacing="0" border="0">
                <tr>
                    <td><?php echo NBILL_EMAIL_MESSAGE_FROM; ?></td><td><input type="text" name="message_from" id="message_from" value="<?php echo $from; ?>" style="width:250px;" />
                    &nbsp;
                    <?php echo NBILL_EMAIL_MESSAGE_FROM_NAME; ?><input type="text" name="message_from_name" id="message_from_name" value="<?php echo $default_vendor_name; ?>" style="width:250px;" /></td>
                </tr>
                <tr>
                    <td><?php echo NBILL_EMAIL_MESSAGE_TO; ?></td><td><input type="text" name="message_to" id="message_to" value="<?php echo $multi_client ? NBILL_PAYPAL_MULTIPLE_RECIPIENTS : (is_array($to) ? implode(";", $to) : $to) ?>" style="width:250px;" <?php echo $multi_client ? 'disabled="disabled" ' : '';?>/><input type="hidden" name="contact_email" id="contact_email" value="<?php echo $multi_client ? NBILL_PAYPAL_MULTIPLE_RECIPIENTS : (is_array($contact_email) ? implode(";", $contact_email) : $contact_email) ?>" /></td>
                </tr>
                <tr<?php if ($multi_client) {echo ' style="display:none;"';} ?>>
                    <td><?php echo NBILL_EMAIL_MESSAGE_CC; ?></td><td><input type="text" name="message_cc" id="message_cc" value="" style="width:250px;" /></td>
                </tr>
                <tr<?php if ($multi_client) {echo ' style="display:none;"';} ?>>
                    <td><?php echo NBILL_EMAIL_MESSAGE_BCC; ?></td><td><input type="text" name="message_bcc" id="message_bcc" value="" style="width:250px;" /></td>
                </tr>
                <tr>
                    <td><?php echo NBILL_EMAIL_MESSAGE_SUBJECT; ?></td><td><input type="text" name="message_subject" id="message_subject" value="<?php echo sprintf(NBILL_PAYPAL_PREAPP_INVITE_SUBJECT, $default_vendor_name); ?>" style="width:250px;" /></td>
                </tr>

                <tr>
                    <td><?php echo NBILL_EMAIL_MESSAGE; ?></td>
                    <td>
                        <iframe name="ifr_email_message" id="ifr_email_message" src="about:blank" style="width:775px;height:450px;border:solid 1px #cccccc;" frameborder="0" scrolling="auto"><?php echo NBILL_IFRAMES_REQUIRED; ?></iframe>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="right" valign="top" style="text-align:right;vertical-align:top;">
                        <input type="button" name="send_message" class="button btn" value="<?php echo NBILL_EMAIL_SEND; ?>" onclick="send_email();" style="font-size:10pt;font-weight:bold;" />
                        &nbsp;
                        <input type="button" name="cancel_message" class="button btn" value="<?php echo NBILL_EMAIL_CANCEL; ?>" style="font-size:10pt;font-weight:bold;" onclick="nbill_pp_submit_task('abort_invite');" />
                    </td>
                </tr>
            </table>
        </div>
        </form>

        <script type="text/javascript">
            document.getElementById('ifr_email_message').src = '<?php echo $message_link; ?>#<?php echo uniqid(); ?>';
        </script>
        <?php
    }

    public static function showInvoices($rows, $pagination, $first_product_description, $cfg_date_format, $page_totals, $sum_totals)
    {
        nbf_html::load_calendar();
        ?>

        <script type="text/javascript">
        function nbill_pp_submit_task(task_name)
        {
            document.getElementById('process').value = task_name;
            document.adminForm.submit();
        }
        </script>

        <?php
        $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/"; ?>
        <div id="nbill-toolbar-container">
            <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
            <tr valign="middle" align="center">
                <!-- Process button -->
                <td>
                    <a class="nbill-toolbar" href="#" onclick="nbill_pp_submit_task('collect_payment');return false;">
                        <img src="<?php echo $image_path ; ?>generate.png" alt="<?php echo NBILL_PAYPAL_TB_PROCESS; ?>" align="middle" border="0" />
                        <br /><?php echo NBILL_PAYPAL_TB_PROCESS;?></a>
                </td>
            </tr>
            </table>
        </div>

        <table class="adminheading" style="width:auto;">
        <tr>
            <th align="left" <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, 'invoices'); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_PAYPAL_PAYABLE_INVOICES; ?>
            </th>
        </tr>
        </table>

        <div class="nbill-message-ie-padding-bug-fixer"></div>
        <?php
        if (nbf_common::nb_strlen(nbf_globals::$message) > 0) {
            echo "<div class=\"nbill-message\">" . str_replace("\n\n", "<br /><br />", nbf_globals::$message) . "</div>";
        } ?>

        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
        <input type="hidden" name="task" value="functions" />
        <input type="hidden" name="sub_task" value="invoices" />
        <input type="hidden" name="gateway" value="paypal" />
        <input type="hidden" name="process" id="process" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">

        <p align="left"><?php echo NBILL_PAYPAL_PAYABLE_INVOICES_INTRO; ?></p>

        <p align="left">
        <?php
            echo "&nbsp;&nbsp;" . NBILL_INVOICE_NUMBER; ?>&nbsp;<input type="text" name="nbill_no_search" value="<?php echo nbf_common::get_param($_POST,'nbill_no_search'); ?>" size="10" />
            <?php echo "&nbsp;&nbsp;" . NBILL_CLIENT;?>&nbsp;<input type="text" name="client_search" value="<?php echo nbf_common::get_param($_POST,'client_search'); ?>" size="18" />
            <?php echo "&nbsp;&nbsp;" . NBILL_DOC_DESCRIPTION;?>&nbsp;<input type="text" name="description_search" value="<?php echo nbf_common::get_param($_POST,'description_search'); ?>" size="18" />
            <?php echo "&nbsp;&nbsp;" . NBILL_DATE_RANGE; $cal_date_format = nbf_common::get_date_format(true); ?>
            <span style="white-space:nowrap"><input type="text" name="search_date_from" id="search_date_from" size="10" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_from'); ?>" <?php if (nbf_common::get_param($_REQUEST, 'show_all')) {echo "disabled=\"disabled\"";} ?> />
            <input type="button" name="search_date_from_cal" id="search_date_from_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.search_date_from,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_REQUEST, 'show_all')) {echo "disabled=\"disabled\"";} ?> /></span>
            <?php echo NBILL_TO; ?>
            <span style="white-space:nowrap"><input type="text" name="search_date_to" id="search_date_to" size="10" maxlength="19" value="<?php echo nbf_common::get_param($_REQUEST,'search_date_to'); ?>" <?php if (nbf_common::get_param($_REQUEST, 'show_all')) {echo "disabled=\"disabled\"";} ?> />
            <input type="button" name="search_date_to_cal" id="search_date_to_cal" class="button btn" value="..." onclick="displayCalendar(document.adminForm.search_date_to,'<?php echo $cal_date_format; ?>',this);" <?php if (nbf_common::get_param($_REQUEST, 'show_all')) {echo "disabled=\"disabled\"";} ?> /></span>
            <input type="submit" class="button btn" name="<?php echo nbf_common::get_param($_REQUEST, 'show_all') ? 'show_reset' : 'show_all'; ?>" value="<?php echo nbf_common::get_param($_REQUEST, 'show_all') ? NBILL_INVOICE_SHOW_RESET : NBILL_INVOICE_SHOW_ALL; ?>" />
            <?php if (nbf_common::get_param($_REQUEST, 'show_all')) {echo "<input type=\"hidden\" name=\"show_all\" value=\"1\" />";} ?>
            <input type="submit" class="button btn" name="dosearch" id="dosearch" value="<?php echo NBILL_GO; ?>" />
        </p>

        <table class="adminlist table table-striped">
        <tr>
            <th align="center">
            #
            </th>
            <th align="center">
                <input type="checkbox" name="check_all" value="" onclick="for(var i=0; i<<?php echo count($rows); ?>;i++) {document.getElementById('cb' + i).checked=this.checked;} document.adminForm.box_checked.value=this.checked;" />
            </th>
            <th align="left" class="title" colspan="2">
                <?php echo NBILL_INVOICE_NUMBER; ?>
            </th>
            <th align="left" class="title">
                <?php echo NBILL_CLIENT . "/" . NBILL_BILLING_NAME; ?>
            </th>
            <th align="left" class="title">
                <?php echo NBILL_INVOICE_DATE; ?>
            </th>
            <th align="left" class="title">
                <?php echo NBILL_FIRST_ITEM; ?>
            </th>
            <th class="title" align="right" style="text-align:right;white-space:nowrap;">
                <?php echo NBILL_TOTAL_NET; ?>
            </th>
            <th class="title" align="right" style="text-align:right;white-space:nowrap;">
                <?php echo NBILL_TOTAL_TAX; ?>
            </th>
            <th class="title" align="right" style="text-align:right;white-space:nowrap;">
                <?php echo NBILL_TOTAL_GROSS; ?>
            </th>
            <th class="title" align="center" width="10%" style="text-align:center">
                <?php echo NBILL_INVOICE_PAY_STATUS; ?>
            </th>
        </tr>
        <?php
        $total_net = 0;
        $total_tax = 0;
        $total_gross = 0;
        for ($i=0, $n=count( $rows ); $i < $n; $i++) {
            $row = &$rows[$i];

            if ($row->payment_pending_until) {
                $img = 'pp_pending.png';
                $alt = sprintf(NBILL_PAYPAL_INVOICE_PENDING, nbf_common::nb_date(nbf_common::get_date_format(), $row->payment_pending_until));
            } else {
                $img = 'icons/' . ($row->paid_in_full ? 'tick.png' : ($row->partial_payment ? 'partial.png' : 'cross.png'));
                $alt = $row->paid_in_full ? NBILL_DOCUMENT_PAID : ($row->partial_payment ? NBILL_DOCUMENT_PART_PAID : NBILL_DOCUMENT_NOT_PAID);
            }
            //$link = nbf_cms::$interop->admin_page_prefix . "&action=" . nbf_common::get_param($_REQUEST, 'action') . "&task=edit&cid=$row->id&search_date_from=$search_date_from&search_date_to=$search_date_to&vendor_filter=" . nbf_globals::$vendor_filter . "&category_filter_" . nbf_globals::$vendor_filter . "=$category_filter&client_search=$client_search&product_search=$product_search&nbill_no_search=$nbill_no_search";

            $total_net += $row->total_net;
            $total_tax += $row->total_tax;
            $total_gross += $row->total_gross;

            echo "<tr>";
            echo "<td align=\"center\">";
            echo $pagination->list_offset + $i + 1;
            $checked = nbf_html::id_checkbox($i, $row->id);
            echo "</td><td align=\"center\" style=\"text-align:center;\">";
            if ($row->payment_pending_until) {
                echo '<span style="display:none;">'; //Don't want to bill for the same invoice twice (still output checkbox to avoid js errors, but will not be processed even if checked)
            }
            echo $checked;
            if ($row->payment_pending_until) {
                echo '</span>';
            }
            echo "</td>";
            echo "<td align=\"left\"><span style=\"white-space:nowrap;\">" . $row->document_no . "</span></td>";
            echo "<td align=\"left\" style=\"white-space:nowrap; min-width:90px;";
            echo "\"><div style=\"white-space:nowrap;float:left;\">";
            echo "<a href=\"#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=invoices&task=printpreviewpopup&hidemainmenu=1&items=" . $row->id . "', '" . uniqid() . "', 'width=700,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;\" title=\"" . NBILL_PRINT . "\"><img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/preview.gif\" alt=\"" . NBILL_PRINT . "\" border=\"0\" style=\"vertical-align:middle;\" /></a>";
            if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/pdfwriter/nbill_to_pdf.php") || file_exists(nbf_cms::$interop->nbill_fe_base_path . "/pdfwriter/nbill_to_pdf.php")) {
                echo "&nbsp;&nbsp;<a href=\"#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=invoices&task=pdfpopup&hidemainmenu=1&items=" . $row->id . "', '" . uniqid() . "', 'width=800,height=500,resizable=yes,scrollbars=yes,toolbar=yes,location=no,directories=no,status=yes,menubar=yes,copyhistory=no');return false;\" title=\"" . NBILL_PDF . "\"><img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/pdf.gif\" alt=\"" . NBILL_PDF . "\" border=\"0\" style=\"vertical-align:middle;\" /></a>";
            }
            echo "</div>";
            echo "</td>";
            echo "<td align=\"left\">";
            $billing_name = $row->billing_name;
            if ($row->entity_id > 0 && (nbf_common::nb_strlen($row->company_name) > 0 || nbf_common::nb_strlen($row->contact_name) > 0)) {
                $return_url = base64_encode(nbf_cms::$interop->admin_page_prefix . "&action=gateway&task=functions&gateway=paypal");
                $billing_name = "<a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=" . ($row->is_client ? "clients" : ($row->is_supplier ? "suppliers" : "potential_clients")) . "&task=edit&cid=" . $row->entity_id . "&return=" . $return_url . "\">";
                if (nbf_common::nb_strlen($row->company_name) > 0) {
                    $billing_name .= $row->company_name;
                    if (nbf_common::nb_strlen($row->contact_name) > 0) {
                        $billing_name .= " (" . $row->contact_name . ")";
                    }
                } else {
                    $billing_name .= $row->contact_name;
                }
                $billing_name .= "</a>";
            }
            echo $billing_name;
            echo "</td>";
            echo "<td align=\"left\">" . nbf_common::nb_date($cfg_date_format, $row->document_date) . "</td>";
            $first_desc = "";
            $section_found = false;
            foreach ($first_product_description as $descriptions) {
                if ($descriptions->document_id == $row->id) {
                    if ($descriptions->section_name) {
                        $first_desc = $descriptions->section_name;
                        $section_found = true;
                        break;
                    }
                }
            }
            if (!$section_found) {
                foreach ($first_product_description as $descriptions) {
                    if ($descriptions->document_id == $row->id) {
                        $first_desc = $descriptions->product_description;
                        break;
                    }
                }
            }
            echo "<td align=\"left\" width=\"30%\">" . $first_desc . "</td>";
            echo "<td align=\"right\" style=\"text-align:right;white-space:nowrap;\">" . $row->currency . " " . format_number($row->total_net) . "</td>";
            echo "<td align=\"right\" style=\"text-align:right;white-space:nowrap;\">" . $row->currency . " " . format_number($row->total_tax) . "</td>";
            echo "<td align=\"right\" style=\"text-align:right;white-space:nowrap;\">" . $row->currency . " " . format_number($row->total_gross) . "</td>";
            echo "<td width=\"10%\" align=\"center\" style=\"text-align:center;\">";
            echo "<img src=\"" . nbf_cms::$interop->nbill_site_url_path . "/images/$img\" border=\"0\" alt=\"$alt\" title=\"$alt\" />";
            echo "</td>";
            echo "</tr>";
        }
        ?>
        <tr>
            <td colspan="7" style="font-weight:bold"><?php echo @constant("NBILL_INVOICE_TOTAL_THIS_PAGE$doc_suffix"); ?></td>
            <td align="right" style="font-weight:bold;text-align:right;white-space:nowrap;">
                <?php for ($i=0; $i<count($page_totals); $i++) {
                    if ($i>0){echo "<br />";}
                    echo $page_totals[$i]->currency . " " . format_number($page_totals[$i]->total_net_page, null, true, false);
                }?>
            </td>
            <td align="right" style="font-weight:bold;text-align:right;white-space:nowrap;">
                <?php for ($i=0; $i<count($page_totals); $i++) {
                    if ($i>0){echo "<br />";}
                    echo $page_totals[$i]->currency . " " . format_number($page_totals[$i]->total_tax_page, null, true, false);
                }?>
            </td>
            <td align="right" style="font-weight:bold;text-align:right;white-space:nowrap;">
                <?php for ($i=0; $i<count($page_totals); $i++) {
                    if ($i>0){echo "<br />";}
                    echo $page_totals[$i]->currency . " " . format_number($page_totals[$i]->total_gross_page, null, true, false);
                }?>
            </td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td colspan="7" style="background-color:#dddddd;font-weight:bold"><?php echo @constant("NBILL_INVOICE_TOTAL_ALL_PAGES$doc_suffix"); ?></td>
            <td align="right" style="background-color:#dddddd;font-weight:bold;text-align:right;white-space:nowrap;">
                <?php for ($i=0; $i<count($sum_totals); $i++) {
                    if ($i>0){echo "<br />";}
                    echo $sum_totals[$i]->currency . " " . format_number($sum_totals[$i]->total_net_all, null, true, false);
                }?>
            </td>
            <td align="right" style="background-color:#dddddd;font-weight:bold;text-align:right;white-space:nowrap;">
                <?php for ($i=0; $i<count($sum_totals); $i++) {
                    if ($i>0){echo "<br />";}
                    echo $sum_totals[$i]->currency . " " . format_number($sum_totals[$i]->total_tax_all, null, true, false);
                }?>
            </td>
            <td align="right" style="background-color:#dddddd;font-weight:bold;text-align:right;white-space:nowrap;">
                <?php for ($i=0; $i<count($sum_totals); $i++) {
                    if ($i>0){echo "<br />";}
                    echo $sum_totals[$i]->currency . " " . format_number($sum_totals[$i]->total_gross_all, null, true, false);
                }?>
            </td>
            <td style="background-color:#dddddd;">&nbsp;</td>
        </tr>
        <tr class="nbill_tr_no_highlight"><td colspan="11" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
        </table>

        </form>
        <?php
    }

    public static function takeInvoicePayment($payment_docs)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/ajax/nbill.ajax.client.php");
        ?>
        <script type="text/javascript">
        function nbill_pp_submit_task(task_name)
        {
            document.getElementById('process').value = task_name;
            document.adminForm.submit();
        }

        var process_started = false;
        var invoices = [];
        var do_process = false;
        var current_invoice = null;

        function start_invoice_processing()
        {
            document.getElementById('nbill_pp_process_start').disabled = true;
            document.getElementById('nbill_pp_process_cancel').disabled = false;
            if (!process_started) {
                process_started = true;
                //Populate list of invoices
                <?php
                foreach ($payment_docs as $invoice) { ?>
                    var invoice = [];
                    invoice.push('<?php echo $invoice->id; ?>');
                    invoice.push('<?php echo $invoice->entity_id; ?>');
                    invoice.push(document.getElementById('amount_<?php echo $invoice->id; ?>').value);
                    invoices.push(invoice);
                    <?php
                } ?>
            }
            do_process = true;
            process_invoices();
        }
        function cancel_invoice_processing()
        {
            document.getElementById('nbill_pp_process_start').disabled = false;
            document.getElementById('nbill_pp_process_cancel').disabled = true;
            do_process = false;
            alert('<?php echo NBILL_PAYPAL_PROCESS_CANCELLED; ?>');
        }
        function process_invoices()
        {
            //Get next invoice (if there are any left)...
            if (do_process && invoices.length > 0) {
                this_invoice = invoices.shift();
                if (this_invoice.length == 3) {
                    document.getElementById('invoice_' + this_invoice[0] + '_status').innerHTML = '<img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/pp_processing.gif" alt="<?php echo NBILL_PAYPAL_PROCESSING; ?>" title="<?php echo NBILL_PAYPAL_PROCESSING; ?>" />&nbsp;' + '<?php echo NBILL_PAYPAL_PROCESSING; ?>';
                    params = 'invoice_id=' + this_invoice[0] + '&entity_id=' + this_invoice[1] + '&amount=' + this_invoice[2];
                    current_invoice = this_invoice[0];
                    submit_ajax_request('paypal.process_invoice', params, process_invoices_ajax_callback, false);
                } else {
                    process_invoices(); //Carry on with the next one
                }
            } else {
                do_process = false;
            }
        }

        function process_invoices_ajax_callback(result)
        {
            //Update status on list
            var invoice_result = result.split('#!#');
            if (invoice_result.length == 3) {
                switch (invoice_result[1]) {
                    case 'success':
                        if (document.getElementById('invoice_' + invoice_result[0] + '_status')) {
                            document.getElementById('invoice_' + invoice_result[0] + '_status').innerHTML = '<img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/email-sent.gif" alt="<?php echo NBILL_PAYPAL_STATUS_SUCCESS_ALT; ?>" title="<?php echo NBILL_PAYPAL_STATUS_SUCCESS_ALT; ?>" />&nbsp;' + '<?php echo NBILL_PAYPAL_STATUS_SUCCESS; ?>';
                        }
                        break;
                    case 'failure':
                        if (document.getElementById('invoice_' + invoice_result[0] + '_status')) {
                            document.getElementById('invoice_' + invoice_result[0] + '_status').innerHTML = '<img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file_delete.png" alt="' + '<?php echo NBILL_PAYPAL_STATUS_FAILURE_ALT; ?>'.replace('%s', invoice_result[2]) + '" title="' + '<?php echo NBILL_PAYPAL_STATUS_FAILURE_ALT; ?>'.replace('%s', invoice_result[2]) + '" />&nbsp;' + '<?php echo NBILL_PAYPAL_STATUS_FAILURE; ?>';
                        }
                        break;
                    case 'aborted':
                        if (document.getElementById('invoice_' + invoice_result[0] + '_status')) {
                            document.getElementById('invoice_' + invoice_result[0] + '_status').innerHTML = '<img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/email-fail.gif" alt="<?php echo NBILL_PAYPAL_STATUS_ABORTED_ALT; ?>" title="<?php echo NBILL_PAYPAL_STATUS_ABORTED_ALT; ?>" />&nbsp;' + '<?php echo NBILL_PAYPAL_STATUS_ABORTED; ?>';
                        }
                        break;
                }
            } else {
                if (document.getElementById('invoice_' + current_invoice + '_status')) {
                    document.getElementById('invoice_' + current_invoice + '_status').innerHTML = '<img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/file_delete.png" alt="' + '<?php echo NBILL_PAYPAL_STATUS_FAILURE_ALT; ?>'.replace('%s', '<?php echo NBILL_PAYPAL_FAILURE_NO_RESPONSE; ?>') + '" title="' + '<?php echo NBILL_PAYPAL_STATUS_FAILURE_ALT; ?>'.replace('%s', '<?php echo NBILL_PAYPAL_FAILURE_NO_RESPONSE; ?>') + '" />&nbsp;' + '<?php echo NBILL_PAYPAL_STATUS_FAILURE; ?>';
                }
            }

            if (invoices.length > 0) {
                process_invoices();
            } else {
                document.getElementById('nbill_pp_process_start').disabled = true;
                document.getElementById('nbill_pp_process_cancel').disabled = true;
                do_process = false;
                alert('<?php echo NBILL_PAYPAL_PROCESS_COMPLETE; ?>');
            }
        }
        </script>
        <?php

        $image_path = nbf_cms::$interop->nbill_site_url_path . "/images/icons/toolbar/"; ?>
        <div id="nbill-toolbar-container">
            <table cellpadding="0" cellspacing="0" border="0" id="toolbar">
            <tr valign="middle" align="center">
                <!-- Process button -->
                <td>
                    <a class="nbill-toolbar" href="#" onclick="nbill_pp_submit_task('');return false;">
                        <img src="<?php echo $image_path ; ?>back.png" alt="<?php echo NBILL_TB_BACK; ?>" align="middle" border="0" />
                        <br /><?php echo NBILL_TB_BACK;?></a>
                </td>
            </tr>
            </table>
        </div>

        <table class="adminheading" style="width:auto;">
        <tr>
            <th align="left" <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, 'invoices'); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_PAYPAL_PAYABLE_INVOICES; ?>
            </th>
        </tr>
        </table>

        <div class="nbill-message-ie-padding-bug-fixer"></div>
        <?php
        if (nbf_common::nb_strlen(nbf_globals::$message) > 0) {
            echo "<div class=\"nbill-message\">" . str_replace("\n\n", "<br /><br />", nbf_globals::$message) . "</div>";
        } ?>

        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="<?php echo nbf_common::get_param($_REQUEST, 'action'); ?>" />
        <input type="hidden" name="task" value="functions" />
        <input type="hidden" name="sub_task" value="invoices" />
        <input type="hidden" name="gateway" value="paypal" />
        <input type="hidden" name="process" id="process" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">

        <p align="left"><?php echo NBILL_PAYPAL_PAYABLE_INVOICES_PROCESS_INTRO; ?></p>

        <table class="adminlist table table-striped" style="width:500px;">
            <tr>
                <td style="padding-bottom:15px;" colspan="3">
                    <input type="button" class="button btn" id="nbill_pp_process_start" value="<?php echo NBILL_PAYPAL_START; ?>" onclick="start_invoice_processing();" />&nbsp;
                    <input type="button" class="button btn" id="nbill_pp_process_cancel" value="<?php echo NBILL_PAYPAL_CANCEL; ?>" disabled="disabled" onclick="cancel_invoice_processing();" />
                </td>
            </tr>
            <tr>
                <th><?php echo NBILL_PAYPAL_INVOICE_NO; ?></th>
                <th><?php echo NBILL_PAYPAL_AMOUNT; ?></th>
                <th><?php echo NBILL_PAYPAL_STATUS; ?></th>
            </tr>
            <?php
            foreach ($payment_docs as $document) { ?>
                <tr>
                    <td><?php echo NBILL_PAYPAL_INVOICE . ' ' . $document->document_no; ?></td>
                    <td><?php echo $document->currency; ?><input type="text" id="amount_<?php echo $document->id; ?>" value="<?php echo $document->total_gross; ?>" style="width:120px;" /></td>
                    <td id="invoice_<?php echo $document->id; ?>_status"><?php echo NBILL_PAYPAL_STATUS_NONE; ?></td>
                </tr><?php
            }
            ?>
        </table>
        <?php
    }
}