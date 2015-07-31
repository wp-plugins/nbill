<?php
/**
* HTML output for general use in the administrator
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* Outputs the main toolbar and menu
*/
class nb_main_html
{
    static function start_container()
    {
        if (nbf_globals::$popup) {
            ?>
            <!DOCTYPE html>
                <html>
                <head>
                    <title><?php echo NBILL_BRANDING_NAME; ?></title>
                    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
                    <meta http-equiv="content-type" content="text/html; charset=<?php echo nbf_cms::$interop->char_encoding; ?>" />
                    <link rel="stylesheet" type="text/css" href="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/style/nbill_admin_via_fe.css" />
                    <link rel="stylesheet" type="text/css" href="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/style/admin/nbill_admin.css" />
                    <link rel="stylesheet" type="text/css" href="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/style/admin/nbill_admin_popup_responsive.css" />
                    <link rel="stylesheet" type="text/css" href="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/style/admin/widgets.css" />
                    <link rel="stylesheet" type="text/css" href="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/style/nbill_tabs.css" />
                    <?php
                    $template = nBillConfigurationService::getInstance()->getConfig()->admin_custom_stylesheet;
                    if (file_exists(nbf_cms::$interop->nbill_fe_base_path . '/style/admin/custom/template.css')) {
                        ?><link rel="stylesheet" type="text/css" href="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/style/admin/custom/template.css" /><?php
                    }
                    if (file_exists(nbf_cms::$interop->nbill_fe_base_path . '/style/admin/colours/' . $template)) {
                        ?><link rel="stylesheet" type="text/css" href="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/style/admin/colours/<?php echo $template; ?>" /><?php
                    } ?>
                </head>
                <body id="nbill_admin_body">
            <?php
        } else {
            nbf_cms::$interop->add_html_header('<link rel="stylesheet" href="' . nbf_cms::$interop->nbill_site_url_path . '/style/admin/nbill_admin.css" type="text/css" />');
            nbf_cms::$interop->add_html_header('<link rel="stylesheet" href="' . nbf_cms::$interop->nbill_site_url_path . '/style/admin/nbill_admin_responsive.css" type="text/css" />');
        }
        ?><div id="nbill_main_container" style="text-align:left;"><?php
    }

    static function end_container($template)
    {
        ?></div><?php
        if (nbf_globals::$popup) {
            ?>
            </body></html>
            <?php
            exit;
        } else {
            //We do this at the end so that the custom styling rules can override the defaults
            if (file_exists(nbf_cms::$interop->nbill_fe_base_path . '/style/admin/custom/template.css')) {
                nbf_cms::$interop->add_html_header('<link rel="stylesheet" type="text/css" href="' . nbf_cms::$interop->nbill_site_url_path . '/style/admin/custom/template.css" />');
            }
            if (file_exists(nbf_cms::$interop->nbill_fe_base_path . '/style/admin/colours/' . $template)) {
                nbf_cms::$interop->add_html_header('<link rel="stylesheet" type="text/css" href="' . nbf_cms::$interop->nbill_site_url_path . '/style/admin/colours/' . $template . '" />');
            }
        }
    }

	static function show_toolbar($ext_menu_tabs = array())
	{
        //Show extension menu tabs, if applicable
        if (count($ext_menu_tabs))
        {
            ?>
            <div id="nbill-extension-tabs">
            <?php
            $selected_found = false;
            foreach ($ext_menu_tabs as $ext_menu_tab)
            {
                $selected = false;
                $expected_url = str_replace(nbf_cms::$interop->admin_page_prefix, '[NBILL_ADMIN]', str_replace('/administrator/', '', nbf_common::get_requested_page()));
                $possible_url = str_replace(nbf_cms::$interop->admin_page_prefix, '[NBILL_ADMIN]', nbf_cms::$interop->admin_page_prefix . '&action=' . nbf_common::get_param($_REQUEST, 'action') . "&sub_action=" . nbf_common::get_param($_REQUEST, 'sub_action'));
                if (!$selected_found && ($expected_url == $ext_menu_tab->url || $possible_url == substr($ext_menu_tab->url, 0, strlen($possible_url))))
                {
                    $selected = true;
                    $selected_found = true;
                }
                ?>
                <div style="float:left;">
                    <div class="nbill-ext-tab<?php if ($selected) {echo '-selected';} ?>"<?php if (!$selected) { ?> onmouseover="this.style.backgroundPosition='right';" onmouseout="this.style.backgroundPosition='left';"<?php } ?>>
                        <a href="<?php echo str_replace("[NBILL_ADMIN]", nbf_cms::$interop->admin_page_prefix, $ext_menu_tab->url); ?>">
                            <img src="<?php echo str_replace("[NBILL_FE]/images/icons/", nbf_cms::$interop->nbill_site_url_path . "/images/icons/", $ext_menu_tab->image); ?>" alt="<?php echo stripslashes(@constant($ext_menu_tab->text)); ?>" title="<?php echo stripslashes(@constant($ext_menu_tab->description)); ?>" align="middle" border="0" />
                            <span class="nbill-ext-tab-text"><?php echo stripslashes(@constant($ext_menu_tab->text)); ?></span>
                        </a>
                    </div>
                </div>
                <?php
            }
            ?>
            </div>
            <?php
        }

		//Check if there is anything to display
		ob_start();
		require_once(nbf_cms::$interop->nbill_admin_base_path . "/nbill.toolbar.html.php");
		require(nbf_cms::$interop->nbill_admin_base_path . "/nbill.toolbar.php");
		$toolbar_output = ob_get_contents();
		@ob_end_clean();
		if (nbf_common::nb_strlen(trim($toolbar_output)) > 0)
		{
			//Extra wrapping div needed to stop IE6 jumping about
			?>
			<div id="nbill-toolbar-container">
			<?php
				echo $toolbar_output;
			?>
			</div>
			<?php
		}
	}

    static function show_responsive_menu($menus)
    {
        nbf_cms::$interop->add_html_header('<link rel="stylesheet" href="' . nbf_cms::$interop->nbill_site_url_path . '/style/admin/main_menu.css" type="text/css" />');
        ?>
        <div class="nbill-menu-container">
            <div class="nbill-quick-links">
                <?php echo NBILL_MNU_QUICK_LINKS; ?>
                <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=clients" title="<?php echo NBILL_MNU_CLIENTS_DESC; ?>"><?php echo NBILL_MNU_CLIENTS; ?></a>
                | <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=products" title="<?php echo NBILL_MNU_PRODUCTS_DESC; ?>"><?php echo NBILL_MNU_PRODUCTS; ?></a>
                <?php if (strtolower(nbf_version::$suffix) != 'lite') { ?>
                | <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=orders" title="<?php echo NBILL_MNU_ORDERS_DESC; ?>"><?php echo NBILL_MNU_ORDERS; ?></a>
                <?php } ?>
                | <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=invoices" title="<?php echo NBILL_MNU_INVOICES_DESC; ?>"><?php echo NBILL_MNU_INVOICES; ?></a>
                | <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=income" title="<?php echo NBILL_MNU_INCOME_DESC; ?>"><?php echo NBILL_MNU_INCOME; ?></a>
            </div>
            <div class="nbill-logo-icon">
                <a href="http://<?php echo NBILL_BRANDING_WEBSITE; ?>" target="_blank" ><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/logo-icon-16x25.png" alt="<?php echo NBILL_BRANDING_NAME; ?>" border="0" /></a>
            </div>
            <div class="nbill-responsive-menu-header">
                <a href="javascript:void(0);" onclick="var mnu=document.getElementById('nbill_menu_list');if(getComputedStyle(mnu, null).display=='none'){mnu.style.display='block';}else{mnu.removeAttribute('style');}this.blur();return false;">
                    <img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/responsive_menu.png" />
                    <span class="nbill-responsive-menu-header-caption"><?php echo sprintf(NBILL_RESPONSIVE_MENU, NBILL_BRANDING_NAME); ?></span>
                </a>
            </div>
            <ul class="nbill-menu-list" id="nbill_menu_list">
                <?php foreach ($menus as $menu_item)
                {
                    if ($menu_item['parent_id'] == -1) { //Top level
                        self::output_menu_item($menu_item, $menus);
                    }
                } ?>
            </ul>
        </div>
        <?php
    }

    static function output_menu_item($menu_item, $menus)
    {
        $is_parent = false;
        foreach ($menus as $child)
        {
            if ($child['parent_id'] == $menu_item['id']) {
                $is_parent = true;
                break;
            }
        }
        ?>
        <li class="nbill-menu-item <?php if ($is_parent) {echo ' parent-menu';} ?>" id="nbill-menu-item-<?php echo $menu_item['id']; ?>">
            <span class="nbill-menu-item-content">
                <?php if (strlen($menu_item['url']) > 0 && !$is_parent) { ?>
                    <a href="<?php echo $menu_item['url']; ?>" title="<?php echo $menu_item['description']; ?>" onclick="this.blur();">
                <?php } else if ($is_parent) {
                    //No reliable CSS support for onclick, so use javascript (this is to allow sub-menu display on touch screen devices)
                    ?>
                    <a class="sub-menu-opener" href="javascript:void(0);" onclick="var sub=document.getElementById('nbill-sub-menu-<?php echo $menu_item['id']; ?>');var fl=window.getComputedStyle(document.getElementById('nbill-menu-item-1')).getPropertyValue('float');if(fl=='left'){sub.removeAttribute('style');this.blur();return false;}if(sub.style.display=='block'){sub.removeAttribute('style');}else{sub.style.display='block'};this.blur();return false;">
                    <?php
                }
                if (strlen($menu_item['image']) > 0) { ?>
                    <img class="nbill-menu-item-image" src="<?php echo $menu_item['image']; ?>" alt="<?php echo $menu_item['description']; ?>" />
                <?php } ?>
                <span class="nbill-menu-item-caption">
                    <?php echo $menu_item['text']; ?>
                </span>
                <?php
                if (strlen($menu_item['url']) > 0 || $is_parent) { ?>
                    </a>
                    <?php
                } ?>
            </span>
            <?php
            if ($is_parent) {
                ?>
                <ul class="nbill-sub-menu-list level-<?php echo ($menu_item['depth'] + 1); ?>" id="nbill-sub-menu-<?php echo $menu_item['id']; ?>">
                <?php
                foreach ($menus as $child)
                {
                    if ($child['parent_id'] == $menu_item['id']) {
                        self::output_menu_item($child, $menus);
                    }
                } ?>
                </ul><?php
            }
            ?>
        </li>
        <?php
    }

	static function show_main_menu($message, $menus)
	{
        self::show_responsive_menu($menus);
        return;
	}

    public static function show_about_box()
    {
        $loopbreaker = 0;
        while (ob_get_length() !== false)
        {
            $loopbreaker++;
            @ob_end_clean();
            if ($loopbreaker > 15)
            {
                break;
            }
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
            <meta http-equiv="content-type" content="text/html; charset=<?php echo nbf_cms::$interop->char_encoding; ?>" />
            <title><?php echo NBILL_BRANDING_NAME; ?></title>
            <style>
            p, td
            {
                font-size: 8pt;
                font-family: sans-serif;
            }
            </style>
        </head>
        <body>
            <table cellpadding="3" cellspacing="0" border="0">
                <tr>
                    <td style="width:100px"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path; ?>/images/icons/large/logo_full_small.gif" alt="<?php echo NBILL_BRANDING_NAME; ?>" /></td>
                    <td><h2><?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?> <?php echo nbf_version::$nbill_version_no; ?></h2></td>
                </tr>
                <tr>
                    <?php
                    if (class_exists("nbill_custom_branding") && nbill_custom_branding::$product_name == NBILL_BRANDING_NAME)
                    {
                        ?>
                        <td colspan="2"><h3>&copy;<?php echo nbf_common::nb_date("Y"); ?> Netshine Software Limited, and licensed to <?php echo NBILL_BRANDING_COMPANY; ?>. All Rights Reserved.</h3>
                        <p>License: <a href="http://<?php echo NBILL_BRANDING_EULA; ?>" target="_blank"><?php echo NBILL_BRANDING_EULA; ?></a></p>
                        <p>This component was developed by Netshine Software Limited (<a href="http://www.netshinesoftware.com/">www.netshinesoftware.com</a>). Use of this
                        software is entirely at your own risk.</p>
                        </td><?php
                    }
                    else
                    {
                        
                            ?>
                            <tr>
                                <td colspan="2"><h3></h3>&copy;<?php echo date("Y"); ?> Netshine Software Limited. All Rights Reserved.</h3>
                                <p>License: <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPL v2</a></p>
                                <p>This 'Lite' edition is free software, released under the open source GPL license, version 2.</p>
                                <p>The standard edition is NOT free, nor open source - it is released under a different (proprietary) license. See <a target="_blank" href="http://www.nbill.co.uk/">www.nbill.co.uk</a> for more information.</p>
                                <p>This component was developed by Netshine Software Limited (<a href="http://www.netshinesoftware.com/" target="_blank">www.netshinesoftware.com</a>). Use of this
                                software is entirely at your own risk.</p>
                                </td>
                            </tr>
                            <?php
                            
                    } ?>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:center"><a href="#" onclick="window.close();return false;"><?php echo NBILL_CLOSE_WINDOW; ?></a></td>
                </tr>
            </table>
        </body>
        </html>
        <?php
        exit;
    }

    public static function show_email_message_editor($default_message, $html)
    {
        if ($html)
        {
            nbf_cms::$interop->init_editor(true);
            echo nbf_cms::$interop->render_editor("email_message", "email_message", $default_message, '', true);
        }
        else
        {
            ?>
            <textarea name="email_message" id="email_message" style="border:none;"><?php echo $default_message; ?></textarea>
            <?php
        }
    }

    public static function show_email_message($correspondence)
    {
        ?>
        <div><?php echo $correspondence; ?></div>
        <?php
    }
}

if (!function_exists("get_prompt_js"))
{
	function get_prompt_js()
	{
		return <<<JS_ALT_PROMPT
		/*/////////////////////////////////////////////////////////
		// Usage IEprompt("dialog descriptive text", "default starting value");
		//
		// IEprompt will call promptCallback(val)
		// Where val is the user's input or null if the dialog was canceled.
		///////////////////////////////////////////////////////////

		///////////////////////////////////////////////////////////
		// This source code has been released into the public domain
		// January 14th, 2007.
		// You may use it and modify it freely without compensation
		// and without the need to tell everyone where you got it.
		///////////////////////////////////////////////////////////		///////////////////////////////////////////////////////////
		// You must create a promptCallback(val) function to handle
		// the user input. If you don't this script will fail and
		// Bunnies will die.
		///////////////////////////////////////////////////////////		///////////////////////////////////////////////////////////
		// These are global scope variables, they should remain global.
		///////////////////////////////////////////////////////////*/
		var _dialogPromptID=null;
		var _blackoutPromptID=null;
		/////////////////////////////////////////////////////////

		function IEhide_dropdowns()
		{
			for (var i=0; i<document.adminForm.elements.length; i++)
			{
				if (document.adminForm.elements[i].type == "select-one" || document.adminForm.elements[i].type == "select-multiple")
				{
					document.adminForm.elements[i].style.visibility = "hidden";
				}
			}
		}
		function IEshow_dropdowns()
		{
			for (var i=0; i<document.adminForm.elements.length; i++)
			{
				if (document.adminForm.elements[i].type == "select-one" || document.adminForm.elements[i].type == "select-multiple")
				{
					document.adminForm.elements[i].style.visibility = "visible";
				}
			}
		}

		function IEprompt(innertxt,def,innerhtml)
		{
			that=this;   // A workaround to javascript's oop idiosyncracies.

			this.wrapupPrompt = function (cancled)
			{
				// wrapupPrompt is called when the user enters or cancels the box.
				val=document.getElementById('iepromptfield').value;
				// clear out the dialog box
				_dialogPromptID.style.display='none';
				// clear out the screen
				_blackoutPromptID.style.display='none';
				//Show dropdowns
				IEshow_dropdowns();
				// clear out the text field
				document.getElementById('iepromptfield').value = '';
				// if the cancel button was pushed, force value to null.
				if (cancled) { val = '' }
				// call the user's function
				promptCallback(val);
		  	    return false;
			}

			//if def wasn't actually passed, initialize it to null
			if (def==undefined) { def=''; }

			IEhide_dropdowns();
			scroll(0,0);
			if (_dialogPromptID==null)
			{
				// Check to see if we've created the dialog divisions.
				// This block sets up the divisons
				// Get the body tag in the dom
				var tbody = document.getElementsByTagName("body")[0];
				// create a new division
				tnode = document.createElement('div');
				// name it
				tnode.id='IEPromptBox';
				// attach the new division to the body tag
				tbody.appendChild(tnode);
				// and save the element reference in a global variable
				_dialogPromptID=document.getElementById('IEPromptBox');
				// Create a new division (blackout)
				tnode = document.createElement('div');
				// name it.
				tnode.id='promptBlackout';
				// attach it to body.
				tbody.appendChild(tnode);
				// And get the element reference
				_blackoutPromptID=document.getElementById('promptBlackout');
				// assign the styles to the blackout division.
				_blackoutPromptID.style.opacity='.9';
				_blackoutPromptID.style.position='absolute';
				_blackoutPromptID.style.top='0px';
				_blackoutPromptID.style.left='0px';
				_blackoutPromptID.style.backgroundColor='#555555';
				_blackoutPromptID.style.filter='alpha(opacity=90)';
				_blackoutPromptID.style.height=(document.body.offsetHeight<screen.height) ? screen.height+'px' : document.body.offsetHeight+20+'px';
				_blackoutPromptID.style.display='block';
				_blackoutPromptID.style.zIndex='998';
				// assign the styles to the dialog box
				_dialogPromptID.style.border='2px solid blue';
				_dialogPromptID.style.backgroundColor='#DDDDDD';
				_dialogPromptID.style.position='absolute';
				_dialogPromptID.style.width='330px';
				_dialogPromptID.style.zIndex='999';
			}
			// This is the HTML which makes up the dialog box, it will be inserted into
			// innerHTML later. We insert into a temporary variable because
			// it's very, very slow doing multiple innerHTML injections, it's much
			// more efficient to use a variable and then do one LARGE injection.
            if (innerhtml && innerhtml.length > 0)
            {
                var tmp=innerhtml;
            }
            else
            {
			    var tmp = '<div style="width: 100%; background-color: blue; color: white; ';
			    tmp += 'font-family: verdana; font-size: 10pt; font-weight: bold; height: 20px">Input Required</';
                tmp += 'div>';
			    tmp += '<div style="padding: 10px">'+innertxt + '<BR><BR>';
			    tmp += '<form action="" onsubmit="return that.wrapupPrompt()">';
			    tmp += '<input id="iepromptfield" name="iepromptdata" type="text" style="width:90%;" value="'+def+'">';
			    tmp += '<br><br><center>';
			    tmp += '<input type="submit" value="&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;">';
			    tmp += '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
			    tmp += '<input type="button" onclick="that.wrapupPrompt(true)" value="&nbsp;Cancel&nbsp;">';
			    tmp += '</form></';
                tmp += 'div>';
            }
			/*// Stretch the blackout division to fill the entire document
			// and make it visible. Because it has a high z-index it should
			// make all other elements on the page unclickable.*/
			_blackoutPromptID.style.height=(document.body.offsetHeight<screen.height) ? screen.height+'px' : document.body.offsetHeight+20+'px';
			_blackoutPromptID.style.width='100%';
			_blackoutPromptID.style.display='block';
			// Insert the tmp HTML string into the dialog box.
			// Then position the dialog box on the screen and make it visible.
			_dialogPromptID.innerHTML=tmp;
			_dialogPromptID.style.top='100px'; //parseInt(document.documentElement.scrollTop+(screen.height/3))+'px';
			_dialogPromptID.style.left=parseInt((document.body.offsetWidth-315)/2)+'px';
			_dialogPromptID.style.display='block';
			// Give the dialog box's input field the focus.
			if (document.getElementById('iepromptfield'))
            {
                document.getElementById('iepromptfield').focus();
            }
		}
JS_ALT_PROMPT;
	}
}

//This has to be in an unencoded file as there are buggy loaders out there that freeze up on this.
function get_menu_hierarchy()
{
	$nb_database = nbf_cms::$interop->database;

	//Return array with menu items ordered by hierarchy
	$sql = "SELECT *, -1 AS main_menu_parent_id FROM #__nbill_menu WHERE published = 1";
	$sql .= " ORDER BY parent_id, ordering";
	$nb_database->setQuery($sql);
	$menus = $nb_database->loadObjectList();
	if (!$menus)
	{
		$menus = array();
	}

	$ordering = 1;

	//Check for extension menus
	$sql = "SELECT main_menu_parent_id, ordering, text, description, image, url,
					CONCAT('e', parent_id) AS parent_id, CONCAT('e', id) AS id, extension_name
					FROM #__nbill_extensions_menu
					WHERE published = 1
					ORDER BY main_menu_parent_id, parent_id, ordering";
	$nb_database->setQuery($sql);
	$ext_menus = $nb_database->loadObjectList();
	if (!$ext_menus)
	{
		$ext_menus = array(); //If we have not yet upgraded the database from 1.1.4 SP1
	}

    //Close any gaps in the ordering
    $current_order = 1;
    $prev_parent = 'n/a';
    for ($menu_index = 0; $menu_index < count($ext_menus); $menu_index++)
    {
        if ($ext_menus[$menu_index]->parent_id != $prev_parent)
        {
            $current_order = 1;
            $prev_parent = $ext_menus[$menu_index]->parent_id;
        }
        $ext_menus[$menu_index]->ordering = $current_order;
        $current_order++;
    }

	//Make sure any necessary language files are included
	foreach ($ext_menus as $ext_menu)
	{
        nbf_common::load_language($ext_menu->extension_name);
	}

	//Find the root items
	foreach ($menus as $menu)
	{
		foreach ($ext_menus as $ext_menu)
		{
			if ($ext_menu->main_menu_parent_id == -1 && $ext_menu->parent_id == 'e-1'
					&& $ordering == $ext_menu->ordering)
			{
				$menu_info["id"] = $ext_menu->id;
				if (nbf_common::nb_strlen(@constant($ext_menu->text)) > 0)
				{
					$menu_info["text"] = stripslashes(constant($ext_menu->text));
				}
				else
				{
					$menu_info["text"] = stripslashes($ext_menu->text);
				}
				if (nbf_common::nb_strlen(@constant($ext_menu->description)) > 0)
				{
					$menu_info["description"] = str_replace("'", "`", stripslashes(constant($ext_menu->description)));
				}
				else
				{
					$menu_info["description"] = str_replace("'", "`", stripslashes($ext_menu->description));
				}
				$menu_info["ordering"] = $ordering;
				//Only increment $ordering if there are no other items with the same ordering value
				$other_item_found = false;
				foreach ($ext_menus as $other_item)
				{
					if ($other_item->main_menu_parent_id == -1 && $other_item->parent_id == 'e-1'
							&& $ordering == $other_item->ordering && $other_item->id != $ext_menu->id)
					{
						//Don't increment
						$other_item_found = true;
						break;
					}
				}
				if (!$other_item_found)
				{
					$ordering++;
				}
				$menu_info["parent_id"] = -1;
                $menu_info["image"] = str_replace("[NBILL_FE]", nbf_cms::$interop->nbill_site_url_path, $ext_menu->image);
                $menu_info["url"] = str_replace("[NBILL_ADMIN]&action=tx_search", "#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=tx_search&hide_billing_menu=1', '" . nbf_common::nb_time() . "', 'menubar=no,width=600,height=400,toolbar=no,top=100,left=100');", $ext_menu->url);
                $menu_info["url"] = str_replace("[NBILL_ADMIN]", nbf_cms::$interop->admin_page_prefix, $menu_info['url']);
                $menu_info["url"] = str_replace("[NBILL_ABOUT]", "#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=about&hide_billing_menu=1', '" . nbf_common::nb_time() . "', 'menubar=no,width=430,height=360,toolbar=no,top=100,left=100,scrollbars=yes');", $menu_info["url"]);
                $menu_info["url"] = str_replace("[NBILL_DOCUMENTATION]", "http://" . NBILL_BRANDING_DOCUMENTATION . "\" target=\"_blank", $menu_info["url"]);
                $menu_info["url"] = str_replace("[NBILL_SUPPORT]", "http://" . NBILL_BRANDING_SUPPORT_URL . "\" target=\"_blank", $menu_info["url"]);
                $menu_info["disabled"] = false;
                $action_start = strpos($menu_info["url"], "&action=");
                if ($action_start !== false)
                {
                    $action = substr($menu_info["url"], $action_start + 8);
                    $action = substr($action, 0, (strpos($action, "&") === false ? strlen($action) : strpos($action, "&")));
                    if ($action!="about" && $action!="registration" && !file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/$action.php"))
                    {
                        $menu_info["disabled"] = true;
                    }
                }
                $menu_info["depth"] = 0;
				$newmenus[] = $menu_info;
				if (nbf_common::nb_strlen($menu_info["id"]) > 0)
				{
					//Order the menu items according to hierarchy
					add_child_menus($menu_info["id"], $ext_menus, $newmenus, 1);
				}
			}
		}
		if ($menu->parent_id == -1)
		{
			$menu_info = array();

			$menu_info["id"] = $menu->id;
			if (nbf_common::nb_strlen(@constant($menu->text)) > 0)
			{
				$menu_info["text"] = stripslashes(constant($menu->text));
			}
			else
			{
				$menu_info["text"] = stripslashes($menu->text);
			}
			if (nbf_common::nb_strlen(@constant($menu->description)) > 0)
			{
				$menu_info["description"] = str_replace("'", "`", stripslashes(constant($menu->description)));
			}
			else
			{
				$menu_info["description"] = str_replace("'", "`", stripslashes($menu->description));
			}
			$menu_info["ordering"] = $ordering;
			$ordering++;
			$menu_info["parent_id"] = -1;
			$menu_info["image"] = str_replace("[NBILL_FE]", nbf_cms::$interop->nbill_site_url_path, $menu->image);
            $menu_info["url"] = str_replace("[NBILL_ADMIN]&action=tx_search", "#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=tx_search&hide_billing_menu=1', '" . nbf_common::nb_time() . "', 'menubar=no,width=600,height=400,toolbar=no,top=100,left=100,scrollbars=yes');", $menu->url);
			$menu_info["url"] = str_replace("[NBILL_ADMIN]", nbf_cms::$interop->admin_page_prefix, $menu_info['url']);
            $menu_info["url"] = str_replace("[NBILL_ABOUT]", "#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=about&hide_billing_menu=1', '" . nbf_common::nb_time() . "', 'menubar=no,width=430,height=360,toolbar=no,top=100,left=100');", $menu_info["url"]);
            $menu_info["url"] = str_replace("[NBILL_DOCUMENTATION]", "http://" . NBILL_BRANDING_DOCUMENTATION . "\" target=\"_blank", $menu_info["url"]);
            $menu_info["url"] = str_replace("[NBILL_SUPPORT]", "http://" . NBILL_BRANDING_SUPPORT_URL . "\" target=\"_blank", $menu_info["url"]);
            $menu_info["disabled"] = false;
            $action_start = strpos($menu_info["url"], "&action=");
            if ($action_start !== false)
            {
                $action = substr($menu_info["url"], $action_start + 8);
                $action = substr($action, 0, (strpos($action, "&") === false ? strlen($action) : strpos($action, "&")));
                if ($action!="about" && $action!="registration" && !file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/$action.php"))
                {
                    $menu_info["disabled"] = true;
                }
            }
			$menu_info["depth"] = 0;
			$newmenus[] = $menu_info;
			if (nbf_common::nb_strlen($menu_info["id"]) > 0)
			{
				//Order the menu items according to hierarchy
				add_child_menus($menu_info["id"], $menus, $newmenus, 1, $ext_menus);
			}
			if ($menu->id == 29) //Extensions
			{
				//Check for payment gateway functions
                $paypal_gateway = new stdClass();
                $paypal_gateway->extension_name = 'paypal';
                $paypal_gateway->gateway_id = 'paypal';
                $paypal_gateway->display_name = 'Paypal';
                $gateway_extensions[] = $paypal_gateway;
				$nb_database = nbf_cms::$interop->database;
				$sql = "SELECT #__nbill_extensions.extension_name, #__nbill_extensions.gateway_id, #__nbill_payment_gateway_config.display_name FROM #__nbill_extensions
                        INNER JOIN #__nbill_payment_gateway_config ON #__nbill_extensions.gateway_id = #__nbill_payment_gateway_config.gateway_id
                        WHERE #__nbill_extensions.gateway_id != ''";
				$nb_database->setQuery($sql);
				$gateway_extensions = array_merge($gateway_extensions, $nb_database->loadObjectList());
				$i = 0;
				if ($gateway_extensions)
				{
					foreach ($gateway_extensions as $gateway)
					{
						//Check if there is a functions file
						$gateway_id = $gateway->gateway_id;
						if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.$gateway_id/$gateway_id.functions.php"))
						{
                            //If gateway display name is too long, try shortening it by looking for a case insensitive match to gateway ID
                            $display_name = $gateway->display_name;
                            if (nbf_common::nb_strlen($display_name) > 20)
                            {
                                $id_pos = strpos(nbf_common::nb_strtolower($display_name), nbf_common::nb_strtolower($gateway->gateway_id));
                                if ($id_pos !== false)
                                {
                                    $display_name = substr($display_name, $id_pos, strlen($gateway->gateway_id)); //Should correct any capitalisation issues
                                }
                                else
                                {
                                    $display_name = nbf_common::nb_substr($display_name, 0, 19) . "...";
                                }
                            }
							$i++;
							$menu_info = array();
							$menu_info["id"] = $gateway_id;
							$menu_info["text"] = stripslashes(sprintf(NBILL_MNU_GATEWAY_FUNCTIONS, $display_name));
							$menu_info["description"] = str_replace("'", "`", stripslashes(sprintf(NBILL_MNU_GATEWAY_FUNCTIONS_DESC, $display_name)));
							$menu_info["ordering"] = 1 + $i;
							$menu_info["parent_id"] = $menu->id;
                            if (file_exists(nbf_cms::$interop->nbill_admin_base_path . '/admin.gateway/admin.' . $gateway_id . '/' . $gateway_id . '.png')) {
                                $menu_info["image"] = nbf_cms::$interop->nbill_admin_url_path . '/admin.gateway/admin.' . $gateway_id . '/' . $gateway_id . '.png';
                            } else {
							    $menu_info["image"] = nbf_cms::$interop->nbill_site_url_path . "/images/icons/payment.gif";
                            }
							$menu_info["url"] = nbf_cms::$interop->admin_page_prefix . "&action=gateway&task=functions&gateway=$gateway_id";
                            $menu_info["disabled"] = false;
							$menu_info["depth"] = 1;
							$newmenus[] = $menu_info;
						}
					}
				}
			}
		}
	}

	return $newmenus;
}

function add_child_menus($menu_id, $menu_source_array, &$menu_target_array, $level, $ext_menus = array())
{
	//Add any children of $menu_id found in the source array to the target array
	//$level tells us the number of parents between where we are and the root
	if (count($menu_source_array) > 0)
	{
		$ordering = 1;
		for ($i = 0; $i < count($menu_source_array); $i++)
		{
			foreach ($ext_menus as $ext_menu)
			{
				if ($ext_menu->main_menu_parent_id == $menu_id && $ext_menu->parent_id == 'e-1' && $ext_menu->ordering == $ordering)
				{
					$menu_info = array();
					$menu_info["id"] = $ext_menu->id;
					if (nbf_common::nb_strlen(@constant($ext_menu->text)) > 0)
					{
						$menu_info["text"] = stripslashes(constant($ext_menu->text));
					}
					else
					{
						$menu_info["text"] = stripslashes($ext_menu->text);
					}
					$menu_info["ordering"] = $ordering;
					//Only increment $ordering if there are no other items with the same ordering value
					$other_item_found = false;
					foreach ($ext_menus as $other_item)
					{
						if ($other_item->main_menu_parent_id == $menu_id && $other_item->parent_id == 'e-1'
								&& $ordering == $other_item->ordering && $other_item->id != $ext_menu->id)
						{
							//Don't increment
							$other_item_found = true;
							break;
						}
					}
					if (!$other_item_found)
					{
						$ordering++;
					}
					if (nbf_common::nb_strlen(@constant($ext_menu->description)) > 0)
					{
						$menu_info["description"] = str_replace("'", "`", stripslashes(constant($ext_menu->description)));
					}
					else
					{
						$menu_info["description"] = str_replace("'", "`", stripslashes($ext_menu->description));
					}
                    $menu_info["image"] = str_replace("[NBILL_FE]", nbf_cms::$interop->nbill_site_url_path, $ext_menu->image);
                    $menu_info["url"] = str_replace("[NBILL_ADMIN]&action=tx_search", "#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=tx_search&hide_billing_menu=1', '" . nbf_common::nb_time() . "', 'menubar=no,width=600,height=400,toolbar=no,top=100,left=100,scrollbars=yes');", $ext_menu->url);
                    $menu_info["url"] = str_replace("[NBILL_ADMIN]", nbf_cms::$interop->admin_page_prefix, $menu_info['url']);
                    $menu_info["url"] = str_replace("[NBILL_ABOUT]", "#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=about&hide_billing_menu=1', '" . nbf_common::nb_time() . "', 'menubar=no,width=430,height=360,toolbar=no,top=100,left=100');", $menu_info["url"]);
                    $menu_info["url"] = str_replace("[NBILL_DOCUMENTATION]", "http://" . NBILL_BRANDING_DOCUMENTATION . "\" target=\"_blank", $menu_info["url"]);
                    $menu_info["url"] = str_replace("[NBILL_SUPPORT]", "http://" . NBILL_BRANDING_SUPPORT_URL . "\" target=\"_blank", $menu_info["url"]);
                    $menu_info["disabled"] = false;
                    $action_start = strpos($menu_info["url"], "&action=");
                    if ($action_start !== false)
                    {
                        $action = substr($menu_info["url"], $action_start + 8);
                        $action = substr($action, 0, (strpos($action, "&") === false ? strlen($action) : strpos($action, "&")));
                        if ($action!="about" && $action!="registration" && !file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/$action.php"))
                        {
                            $menu_info["disabled"] = true;
                        }
                    }
                    $menu_info["depth"] = $level;
					$menu_info["parent_id"] = $ext_menu->main_menu_parent_id;
					$menu_target_array[] = $menu_info;
					add_child_menus($ext_menu->id, $ext_menus, $menu_target_array, $level + 1);
				}
			}

			$source_menu = $menu_source_array[$i];
			if ($source_menu->parent_id == $menu_id && $source_menu->main_menu_parent_id == -1)
			{
				$menu_info = array();
				$menu_info["id"] = $source_menu->id;
				if (nbf_common::nb_strlen(@constant($source_menu->text)) > 0)
				{
					$menu_info["text"] = stripslashes(constant($source_menu->text));
				}
				else
				{
					$menu_info["text"] = stripslashes($source_menu->text);
				}
				$menu_info["ordering"] = $ordering;
				$ordering++;
				if (nbf_common::nb_strlen(@constant($source_menu->description)) > 0)
				{
					$menu_info["description"] = str_replace("'", "`", stripslashes(constant($source_menu->description)));
				}
				else
				{
					$menu_info["description"] = str_replace("'", "`", stripslashes($source_menu->description));
				}
                $menu_info["image"] = str_replace("[NBILL_FE]", nbf_cms::$interop->nbill_site_url_path, $source_menu->image);
                $menu_info["url"] = str_replace("[NBILL_ADMIN]&action=tx_search", "#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=tx_search&hide_billing_menu=1', '" . nbf_common::nb_time() . "', 'menubar=no,width=680,height=420,toolbar=no,top=100,left=100,scrollbars=yes');", $source_menu->url);
                $menu_info["url"] = str_replace("[NBILL_ADMIN]", nbf_cms::$interop->admin_page_prefix, $menu_info['url']);
                $menu_info["url"] = str_replace("[NBILL_ABOUT]", "#\" onclick=\"window.open('" . nbf_cms::$interop->admin_popup_page_prefix . "&action=about&hide_billing_menu=1', '" . nbf_common::nb_time() . "', 'menubar=no,width=430,height=360,toolbar=no,top=100,left=100');", $menu_info["url"]);
                $menu_info["url"] = str_replace("[NBILL_DOCUMENTATION]", "http://" . NBILL_BRANDING_DOCUMENTATION . "\" target=\"_blank", $menu_info["url"]);
                $menu_info["url"] = str_replace("[NBILL_SUPPORT]", "http://" . NBILL_BRANDING_SUPPORT_URL . "\" target=\"_blank", $menu_info["url"]);
                $menu_info["disabled"] = false;
                $action_start = strpos($menu_info["url"], "&action=");
                if ($action_start !== false)
                {
                    $action = substr($menu_info["url"], $action_start + 8);
                    $action = substr($action, 0, (strpos($action, "&") === false ? strlen($action) : strpos($action, "&")));
                    if ($action!="about" && $action!="registration" && !file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.proc/$action.php"))
                    {
                        $menu_info["disabled"] = true;
                        $disabled_image = str_replace('.gif', '_disabled.gif', $menu_info['image']);
                        $disabled_image = str_replace('.png', '_disabled.png', $disabled_image);
                        if (file_exists(nbf_cms::$interop->nbill_fe_base_path . '/images/icons/' . basename($disabled_image))) {
                            $menu_info["image"] = $disabled_image;
                        }
                    }
                }
                $menu_info["depth"] = $level;
				$menu_info["parent_id"] = $source_menu->parent_id;
				$menu_target_array[] = $menu_info;
				add_child_menus($source_menu->id, $menu_source_array, $menu_target_array, $level + 1);
			}
		}
	}
}