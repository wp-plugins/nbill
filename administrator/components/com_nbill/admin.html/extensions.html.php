<?php
/**
* HTML output for nBill Extensions Installer
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillExtensions
{
	public static function showInstaller($rows, $pagination, $date_format)
	{
		?>
		<table class="adminheading" style="width:auto;">
		<tr class="nbill-admin-heading">
            <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "extensions"); ?>>
			<?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_EXTENSION_INSTALL_NEW; ?>
			</th>
		</tr>
		</table>

		<div class="nbill-message-ie-padding-bug-fixer"></div>
		<div style="text-align:center;color:#ff0000;font-size:1.7em;"><?php echo NBILL_EXTENSION_INSTALL_WARNING; ?></div>
        <br />
		<?php
		if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
		{
			echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
		}
		?>

		<form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" enctype="multipart/form-data">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="extensions" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">

        <div class="rounded-table">
		    <table class="adminform">
		    <tr>
			    <th align="left">
			    Upload Zip File
			    </th>
		    </tr>
		    <tr>
			    <td>
			    Zip File: <input class="text_area" name="zipfile" type="file" size="70" /><input class="button btn" type="submit" value="Upload File &amp; Install" />
			    </td>
		    </tr>
		    </table>

		    <table class="adminheading" style="width:auto;">
		    <tr>
			    <th <?php echo sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "extensions"); ?>>
				    <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL . ": " . NBILL_EXTENSIONS_INSTALLED; ?>
			    </th>
		    </tr>
		    </table>
        </div>
        <br />

        <div class="rounded-table">
		    <table class="adminlist table">
			    <tr>
				    <th class="selector">
				    #
				    </th>
				    <th class="selector">
					    <input type="checkbox" name="toggle" value="" onclick="for(var i=0; i<<?php echo count($rows); ?>;i++) {document.getElementById('cb' + i).checked=this.checked;} document.adminForm.box_checked.value=this.checked;" />
				    </th>
				    <th class="title">
					    <?php echo NBILL_EXTENSION_NAME; ?>
				    </th>
				    <th class="title">
					    <?php echo NBILL_EXTENSION_TYPE; ?>
				    </th>
				    <th class="title responsive-cell optional">
					    <?php echo NBILL_EXTENSION_DATE_CREATED; ?>
				    </th>
				    <th class="title">
					    <?php echo NBILL_EXTENSION_DATE_INSTALLED; ?>
				    </th>
				    <th class="title">
					    <?php echo NBILL_EXTENSION_VERSION; ?>
				    </th>
				    <th class="title responsive-cell optional word-breakable">
					    <?php echo NBILL_EXTENSION_AUTHOR; ?>
				    </th>
				    <th class="title responsive-cell priority word-breakable">
					    <?php echo NBILL_EXTENSION_URL; ?>
				    </th>
			    </tr>
			    <?php
				    for ($i=0, $n=count($rows); $i < $n; $i++)
				    {
					    $row = $rows[$i];
					    echo "<tr>";
					    echo "<td class=\"selector\">";
					    echo $pagination->list_offset + $i + 1;
					    $checked = nbf_html::id_checkbox($i, $row->id);
					    echo "</td><td class=\"selector\">$checked</td>";
					    echo "<td class=\"list-value\">" . $row->extension_title . "</td>";
					    echo "<td class=\"list-value\">" . nbf_common::nb_ucwords(nbf_common::nb_strtolower($row->extension_type)) . "</td>";
					    echo "<td class=\"list-value responsive-cell optional\">" . $row->extension_date . "</td>";
					    echo "<td class=\"list-value\">" . nbf_common::nb_date($date_format, $row->date_installed) . "</td>";
					    echo "<td class=\"list-value\">" . $row->version . "</td>";
					    echo "<td class=\"list-value responsive-cell optional word-breakable\">";
					    if (nbf_common::nb_strlen($row->author_email) > 0)
					    {
						    echo "<a href=\"mailto:" . $row->author_email . "\">";
					    }
					    echo $row->author_name;
					    if (nbf_common::nb_strlen($row->author_email) > 0)
					    {
						    echo " (" . $row->author_email . ")</a>";
					    }
					    echo "</td>";
					    $url = $row->author_website;
					    if (substr($url, 0, 7) != "http://" && substr($url, 0, 8) != "https://")
					    {
						    $url = "http://" . $url;
					    }
					    echo "<td class=\"list-value responsive-cell priority word-breakable\"><a target=\"_blank\" href=\"" . $url . "\">" . $row->author_website . "</a></td>";
					    echo "</tr>";
				    }
			    ?>
			    <tr class="nbill_tr_no_highlight"><td colspan="9" class="nbill-page-nav-footer"><?php echo $pagination->render_page_footer(); ?></td></tr>
			    </table>
            </div>
		</form>
		<?php
	}

	public static function showSuccess($message)
	{
        $stop = true;
        ?>
        <div class="nbill-message"><?php echo nbf_globals::$message; ?></div>
		<p><a href="<?php echo nbf_cms::$interop->admin_page_prefix . "&action=extensions"; ?>"><?php echo NBILL_CLICK_HERE; ?></a> <?php echo NBILL_EXTENSION_RETURN_TO_LIST; ?></p>
        <?php
	}
}