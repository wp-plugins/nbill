<?php
/**
* HTML output for main menu in front end.
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

class nBillFrontEndMain
{
	public static function show_main_menu()
	{
		$nb_database = nbf_cms::$interop->database;
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		}

        if (nbf_frontend::get_display_option("access"))
		{
			?>
			<div class="nbill-access-level">
				<?php echo sprintf(NBILL_CURRENT_USER_GROUP, "<strong>" . nbf_cms::$interop->user->group_name . "</strong>"); ?>
			</div>
		<?php
        } ?>
		<ul class="nbill-main-menu-logged-in">
			<?php if (nbf_frontend::get_display_option("profile")) {?>
			<li id="nbill-my-profile"><a href="<?php $url = nbf_cms::$interop->process_url(nbf_cms::$interop->site_page_prefix . '&action=profile&task=update' . nbf_cms::$interop->site_page_suffix);
				if (@$_SERVER['HTTPS'] || @$_SERVER['SERVER_PORT'] == 443) echo str_replace("http://", "https://", $url); else echo $url; ?>"><?php echo NBILL_MY_PROFILE; ?></a> - <?php echo NBILL_MY_PROFILE_DESC; ?></li>
			<?php }
            
			if (nbf_frontend::get_display_option("invoices")) {?>
				<li id="nbill-my-invoices"><a href="<?php $url = nbf_cms::$interop->process_url(nbf_cms::$interop->site_page_prefix . '&action=invoices&task=view' . nbf_cms::$interop->site_page_suffix);
				if (@$_SERVER['HTTPS'] || @$_SERVER['SERVER_PORT'] == 443) echo str_replace("http://", "https://", $url); else echo $url; ?>"><?php echo NBILL_MY_INVOICES; ?></a> - <?php echo NBILL_MY_INVOICES_DESC; ?></li>
			<?php }
            

            //Add any extension links
            $sql = "SELECT #__nbill_extensions_links.* FROM #__nbill_extensions_links INNER JOIN #__nbill_extensions ON #__nbill_extensions_links.extension_name = #__nbill_extensions.extension_name WHERE #__nbill_extensions_links.published = 1 ORDER BY ordering";
            $nb_database->setQuery($sql);
            $extlinks = $nb_database->loadObjectList();
            if (!$extlinks)
            {
                $extlinks = array();
            }
            if ($extlinks)
            {
                $link_no = 0;
                foreach ($extlinks as $link) {
                    $link_no++;
                    $link->url = str_replace('[NBILL_FE]', nbf_cms::$interop->site_page_prefix, nbf_common::parse_and_execute_code($link->link_url));
                    $link->text = nbf_common::parse_and_execute_code($link->link_text);
                    $link->description = nbf_common::parse_and_execute_code($link->link_description);
                    if (substr($link->url, 0, 7) == "http://" || (substr($link->url, 0, 8) == "https://" || substr($link->url, 0, 4) == "www."))
                    {
                        $link_url = $link->url;
                    }
                    else
                    {
                        $link_url = nbf_cms::$interop->process_url($link->url . nbf_cms::$interop->site_page_suffix);
                    }
                    ?>
                    <li id="nbill-ext-link-<?php echo $link_no; ?>"><a href="<?php echo $link_url; ?>"><?php echo $link->text; ?></a>
                    <?php if (nbf_common::nb_strlen($link->description) > 0) {echo " - " . $link->description;} ?>
                    </li>
                    <?php
                }
            }

			//Add any additional links
			$sql = "SELECT * FROM #__nbill_additional_links ORDER BY ordering";
			$nb_database->setQuery($sql);
			$links = $nb_database->loadObjectList();
			if (!$links) {
				$links = array();
			}
			if ($links) {
                $link_no = 0;
				foreach ($links as $link) {
                    $link_no++;
					if (substr($link->url, 0, 7) == "http://" || (substr($link->url, 0, 8) == "https://" || substr($link->url, 0, 4) == "www.")) {
						$link_url = $link->url;
					} else {
						$link_url = nbf_cms::$interop->process_url($link->url);
					}
					?>
					<li id="nbill-additional-<?php echo $link_no; ?>"><a href="<?php echo $link_url; ?>"><?php echo $link->text; ?></a>
					<?php if (nbf_common::nb_strlen($link->description) > 0) {echo " - " . $link->description;} ?>
					</li>
				<?php }
			}

            if (nbf_frontend::get_display_option("logout")) { ?>
                <li id="nbill-logout"><a href="<?php $url = nbf_cms::$interop->get_logout_link();
                if (@$_SERVER['HTTPS'] || @$_SERVER['SERVER_PORT'] == 443) echo str_replace("http://", "https://", $url); else echo $url; ?>"><?php echo NBILL_LOGOUT; ?></a></li>
                <?php }
			?>
		</ul>
	<?php
	}

    public static function show_warning_message($action, $task, $record_id, $message, $cancel_url, $other_params = array())
    {
        ?>
        <div id="nbill_form_container">
            <form action="<?php echo nbf_cms::$interop->fe_form_action; ?>" method="post" name="confirm_form" id="confirm_form" enctype="multipart/form-data">
            <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
            <input type="hidden" name="<?php echo nbf_cms::$interop->component_name; ?>" value="my-account" />
            <input type="hidden" name="Itemid" value="<?php echo intval(@$_REQUEST['Itemid']); ?>" />
            <input type="hidden" name="action" value="<?php echo $action; ?>" />
            <input type="hidden" name="task" value="<?php echo $task; ?>" />
            <input type="hidden" name="cid" value="<?php echo $record_id; //not intval as it may be comma separated - will be sanitised on postback anyway ?>" />
            <input type="hidden" name="id" value="<?php echo $record_id; ?>" />
            <?php
            foreach ($other_params as $key=>$value)
            {
                ?>
                <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" />
                <?php
            }
            ?>
            <div class="message nbill-message"><?php echo $message; ?></div>
            <input type="submit" class="button btn" name="cancel_warning" value="<?php echo NBILL_CANCEL; ?>" onclick="window.location='<?php echo $cancel_url; ?>';return false;" />
            <input type="submit" class="button btn" name="submit_warning" value="<?php echo NBILL_PROCEED_ANYWAY; ?>" />
            </form>
        </div>
        <?php
    }
}