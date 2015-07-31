<?php
/**
* Server-side processing for client and supplier AJAX functions
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

function get_contacts()
{
	$nb_database = nbf_cms::$interop->database;

	$sql = "SELECT count(*) FROM #__nbill_contact WHERE CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name) LIKE '%" . nbf_common::get_param($_REQUEST, 'contact_name') . "%'";
	$nb_database->setQuery($sql);
	$count = $nb_database->loadResult();
	if ($count)
	{
		if ($count > nbf_globals::$record_limit)
		{
			echo sprintf(NBILL_AJAX_TOO_MANY, $count, nbf_globals::$record_limit);
		}
		//Load results
		$sql = "SELECT id, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS `name`, email_address, town, country FROM #__nbill_contact
                WHERE CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name) LIKE
				'%" . nbf_common::get_param($_REQUEST, 'contact_name') . "%'
				ORDER BY `name`
				LIMIT " . nbf_globals::$record_limit;
		$nb_database->setQuery($sql);
		$contacts = $nb_database->loadObjectList();
		?>
		<hr />
		<div style="margin-top: 5px;">
			<?php if ($count <= nbf_globals::$record_limit) {echo sprintf(NBILL_AJAX_RESULT_COUNT, count($contacts));} ?>
			<p style="font-weight:bold;"><?php echo NBILL_AJAX_SELECT_CONTACTS; ?></p>
            <div class="rounded-table">
			    <table cellpadding="3" cellspacing="0" border="0" class="adminlist table">
				    <tr>
					    <th class="selector"><?php echo NBILL_AJAX_SELECT; ?></th>
					    <th class="title"><?php echo NBILL_AJAX_NAME; ?></th>
					    <th class="title responsive-cell"><?php echo NBILL_AJAX_EMAIL; ?></th>
					    <th class="title responsive-cell optional"><?php echo NBILL_AJAX_LOCATION; ?></th>
				    </tr>
				    <?php foreach ($contacts as $contact)
				    { ?>
					    <tr>
						    <td class="selector"><input type="checkbox" name="assign_contact_<?php echo $contact->id; ?>" /></td>
						    <td class="list-value"><a target="_blank" href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=contacts&task=edit&cid=<?php echo intval($contact->id); ?>"><?php echo $contact->name ? $contact->name : NBILL_AJAX_NAME_UNKNOWN; ?></a></td>
						    <td class="list-value responsive-cell"><?php echo $contact->email_address; ?></td>
						    <td class="list-value responsive-cell optional"><?php
						    if (nbf_common::nb_strlen($contact->town) > 0)
						    {
							    echo $contact->town;
							    if (nbf_common::nb_strlen($contact->country) > 0)
							    {
								    echo ", ";
							    }
						    }
						    echo $contact->country; ?></td>
					    </tr>
				    <?php }?>
			    </table>
            </div>
		</div>
		<?php
	}
	else
	{
		echo NBILL_AJAX_NO_RESULTS;
	}
}

function check_email()
{
    $nb_database = nbf_cms::$interop->database;
    $in_use = false;
    $contact_name = "";
    if (nbf_common::nb_strlen(nbf_common::get_param($_REQUEST, 'email')) > 0)
    {
        $sql = "SELECT id, TRIM(CONCAT_WS(' ', #__nbill_contact.first_name, #__nbill_contact.last_name)) AS `name` FROM #__nbill_contact WHERE `id` != " . intval(nbf_common::get_param($_REQUEST, 'contact_id')) . " AND `email_address` = '" . nbf_common::get_param($_REQUEST, 'email') . "'";
        $nb_database->setQuery($sql);
        $contact = null;
        $nb_database->loadObject($contact);
        if ($contact && $contact->id)
        {
            $in_use = true;
            $contact_name = $contact->name;
            if (nbf_common::nb_strlen($contact_name) == 0)
            {
                $contact_name = NBILL_CONTACT_ID . ": " . $contact->id;
            }
        }
    }
    if ($in_use)
    {
        echo $contact_name;
    }
}