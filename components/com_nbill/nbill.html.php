<?php
/**
* HTML output for general usage throughout the front end.
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillFrontEnd
{
    public static function load_stylesheet()
    {
        $css = file_get_contents(nbf_cms::$interop->nbill_fe_base_path . "/style/nbill_default.css");
        $css = str_replace("url('", "url('" . nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_popup_page_prefix . "&action=show_image" . nbf_cms::$interop->site_page_suffix . "&file_name=", $css);
        nbf_cms::$interop->add_html_header('<style type="text/css">' . $css . '</style>');
    }

    public static function show_header()
    {
        ?>
        <h1><?php echo NBILL_MY_ACCOUNT; ?></h1>
        <?php
    }

	public static function show_expiry_message($referrer)
	{
        $nb_database = nbf_cms::$interop->database;
        $referrer = @base64_decode($referrer);
		?>
		<div class="message"><?php echo nbf_globals::$message; ?></div>

		<p align="center">
            <?php
            //Show renewal link, if user not blocked/deleted or renewal allowed without being logged in
            if (nbf_common::get_param($_REQUEST, 'order_id'))
            {
                $allow_renew = true;
                if (nbf_frontend::get_display_option("login_to_pay_order"))
                {
                    $sql = "SELECT expiry_level FROM #__nbill_product INNER JOIN #__nbill_orders ON #__nbill_product.id = #__nbill_orders.product_id WHERE #__nbill_orders.id = " . intval(nbf_common::get_param($_REQUEST, 'order_id'));
                    $nb_database->setQuery($sql);
                    if ($nb_database->loadResult() < 0)
                    {
                        $allow_renew = false;
                    }
                }
                if ($allow_renew)
                {
                    ?><a href="<?php echo nbf_cms::$interop->process_url(nbf_cms::$interop->site_page_prefix . "&action=orders&task=renew&order_id=" . intval(nbf_common::get_param($_REQUEST, 'order_id')) . nbf_cms::$interop->site_page_suffix); ?>"><?php echo NBILL_RENEW_NOW; ?></a>&nbsp;<?php
                }
            }
            ?>
			<a href="<?php echo $referrer;?>"><?php echo NBILL_CONTINUE; ?></a>

			<?php
			//Add home page link if that isn't where we were going anyway
			if (nbf_cms::$interop->process_url("index.php") != $referrer)
			{
				?>
				&nbsp;<a href="<?php echo nbf_cms::$interop->process_url("index.php");?>"><?php echo NBILL_CONTINUE_HOME; ?></a>
				<?php
			}
			?>
		</p>
		<?php
	}

	public static function display_login_box($request_values, $show_title = true, $show_intro = true)
	{
		?>
		<form action="<?php echo nbf_cms::$interop->site_page_prefix; ?>" method="post" name="billing_login" id="billing_login">
			<input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
            <input type="hidden" name="<?php echo nbf_cms::$interop->component_name; ?>" value="my-account" />
			<input type="hidden" name="action" value="login" />
			<input type="hidden" name="task" value="billing_login" />
			<input type="hidden" name="nb_request_values" value="<?php echo $request_values; ?>" />
            <div align="center" id="nbill-login-box-container" style="text-align:center">
            <?php
            if (nbf_common::get_param($_REQUEST, 'failure_message'))
            {
                ?><div class="nbill-message"><?php echo nbf_common::get_param($_REQUEST, 'failure_message'); ?></div><?php
            }
            if ($show_title) { ?><p><strong><?php echo NBILL_NOT_LOGGED_IN; ?></strong></p><?php }
            if ($show_intro) { ?><p align="left"><?php echo NBILL_LOGIN_INTRO; ?></p><?php }
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control.base.php");
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control.oo.php");
            $login_control = new nbf_field_control_oo(null, "generic");
            $login_control->show_title = false;
            ?><div style="margin-left:auto;margin-right:auto;"><?php
                $login_control->render_control();
            ?></div>
            </div>
		</form>
		<?php
	}

    /**
    * HTML to output immediately before rendering the nBill administrator in the front end (if enabled)
    */
    public static function pre_admin()
    {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
            <meta http-equiv="content-type" content="text/html; charset=<?php echo nbf_cms::$interop->char_encoding; ?>" />
            <link rel="stylesheet" type="text/css" href="<?php echo nbf_cms::$interop->nbill_site_url_path ?>/style/nbill_admin_via_fe.css" />
        </head>
        <body id="nbill_admin_body">
            <div class="fe_admin_container">
                <?php if (!nbf_common::get_param($_REQUEST, 'nbill_admin_fe_no_control_bar'))
                { ?>
                    <div class="fe_admin_control_bar">
                        <div class="pre_admin_left">
                            <a href="<?php echo nbf_cms::$interop->site_page_prefix . nbf_cms::$interop->site_page_suffix; ?>"><?php echo NBILL_FE_ADMIN_RETURN; ?></a>
                        </div>
                        <div class="pre_admin_centre">
                            <?php if (nbf_cms::$interop->user->id) { ?>
                                <a href="<?php echo nbf_cms::$interop->get_logout_link(nbf_common::get_requested_page(true)); ?>"><?php echo NBILL_LOGOUT; ?></a>
                            <?php }
                            else
                            {
                                echo "&nbsp;";
                            } ?>
                        </div>
                        <div class="pre_admin_right">
                            <a href="<?php echo nbf_cms::$interop->site_page_prefix . nbf_cms::$interop->site_page_suffix; ?>" target="_blank"><?php echo NBILL_FE_ADMIN_OPEN; ?></a>
                        </div>
                    </div>
                    <div class="fe_admin_content">
                    <?php
                }
    }

    /**
    * HTML to output immediately after rendering the nBill administrator in the front end (if enabled)
    */
    public static function post_admin()
    {
        ?></div>
        </div>
        </body>
        </html><?php
    }

    /**
    * HTML to output when a guest accesses administrator in the front end (if enabled)
    */
    public static function show_admin_welcome()
    {
        ?>
        <table class="adminheading" style="width:auto;">
        <tr>
            <th align="left" valign="bottom" style="background-image: url('<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/large/logo_full_small.gif');background-repeat:no-repeat;width:100%;height:69px;padding-left:130px;">
                <span style="font-size: 14px;">v <?php $v = new nbf_version(); echo $v->get_short_version(); ?></span>
            </th>
            <th width="200px" align="right" valign="middle" style="font-size:18pt;font-weight:bold;vertical-align:middle;padding-left: 40px; background-image:url('<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/medium/help.gif');background-repeat:no-repeat;background-position: left center;"><a href="http://<?php echo NBILL_BRANDING_DOCUMENTATION ?>" target="_blank"><?php echo NBILL_HELP; ?></a></th>
        </tr>
        </table>
        <p style="font-weight:bold;"><?php echo NBILL_WELCOME; ?></p>
        <p><?php echo NBILL_FE_ADMIN_WELCOME_LOGIN; ?></p>
        <p>&nbsp;</p>
        <?php
        nbill_show_login_box(false, false);
    }

    /**
    * HTML to output when a logged in user tries to access administrator in the front end but is not permitted
    */
    public static function show_admin_denied()
    { ?>
        <h3><?php echo NBILL_FE_ADMIN_ACCESS_DENIED; ?></h3>
        <p><?php echo NBILL_FE_ADMIN_ACCESS_DENIED_DESC; ?></p>
        <?php
    }
}