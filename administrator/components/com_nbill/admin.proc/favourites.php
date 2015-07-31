<?php
/**
* Main processing file for favourites
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');switch ($task)
{
    case "silent":
        break;
    case "apply":
        saveFavourites();
        showFavourites();
        break;
    case "save":
        saveFavourites();
        showMain();
        break;
    case "reset":
        resetFavourites();
        //Fall through
    case "cancel":
        showMain();
        break;
    default:
        showFavourites();
        break;
}

function showMain()
{
    if (nbf_globals::$message)
    {
        $message = nbf_common::get_param(array('message'=>nbf_globals::$message), 'message');
        nbf_common::redirect(nbf_cms::$interop->admin_page_prefix . "&message=$message");
    }
    else
    {
        nbf_common::redirect(nbf_cms::$interop->admin_page_prefix);
    }
}

function showFavourites()
{
    $nb_database = nbf_cms::$interop->database;
    $sql = "SELECT * FROM #__nbill_menu WHERE parent_id > 0 AND image LIKE '[NBILL_FE]/images/icons/%' AND url LIKE '[NBILL_ADMIN]%' AND published = 1 ORDER BY parent_id, ordering";
    $nb_database->setQuery($sql);
    $menu_items = $nb_database->loadObjectList();

    $sql = "SELECT * FROM #__nbill_extensions_menu WHERE image LIKE '[NBILL_FE]/images/icons/%' AND url LIKE '[NBILL_ADMIN]%' AND published = 1 ORDER BY extension_name, parent_id, ordering";
    $nb_database->setQuery($sql);
    $extension_menu_items = $nb_database->loadObjectList();

    nBillFavourites::showFavourites($menu_items, $extension_menu_items);
}

function saveFavourites()
{
    $nb_database = nbf_cms::$interop->database;

    $sql = "SELECT id, favourite FROM #__nbill_menu WHERE parent_id > 0 AND image LIKE '[NBILL_FE]/images/icons/%' AND url LIKE '[NBILL_ADMIN]%' ORDER BY parent_id, ordering";
    $nb_database->setQuery($sql);
    $menu_items = $nb_database->loadObjectList();

    foreach ($menu_items as $menu_item)
    {
        $selected = (nbf_common::get_param($_REQUEST, 'favourite_' . $menu_item->id)) ? 1 : 0;
        if ($menu_item->favourite != $selected)
        {
            $sql = "UPDATE #__nbill_menu SET favourite = $selected WHERE id = " . $menu_item->id;
            $nb_database->setQuery($sql);
            $nb_database->query();
        }
    }

    $sql = "SELECT * FROM #__nbill_extensions_menu WHERE image LIKE '[NBILL_FE]/images/icons/%' AND url LIKE '[NBILL_ADMIN]%' AND published = 1 ORDER BY extension_name, parent_id, ordering";
    $nb_database->setQuery($sql);
    $menu_items = $nb_database->loadObjectList();

    foreach ($menu_items as $menu_item)
    {
        $selected = (nbf_common::get_param($_REQUEST, 'ext_favourite_' . $menu_item->id)) ? 1 : 0;
        if ($menu_item->favourite != $selected)
        {
            $sql = "UPDATE #__nbill_extensions_menu SET favourite = $selected WHERE id = '" . $menu_item->id . "'";
            $nb_database->setQuery($sql);
            $nb_database->query();
        }
    }
}

function resetFavourites()
{
    $nb_database = nbf_cms::$interop->database;

    $sql = "UPDATe #__nbill_menu SET favourite = 0";
    $nb_database->setQuery($sql);
    $nb_database->query();

    $sql = "UPDATe #__nbill_extensions_menu SET favourite = 0";
    $nb_database->setQuery($sql);
    $nb_database->query();

    $sql = "UPDATE #__nbill_menu SET favourite = 1 WHERE text IN ('NBILL_MNU_VENDOR', 'NBILL_MNU_CLIENTS', 'NBILL_MNU_PRODUCTS',
            'NBILL_MNU_ORDERS', 'NBILL_MNU_INVOICES', 'NBILL_MNU_INCOME', 'NBILL_MNU_EXPENDITURE', 'NBILL_MNU_ORDER_FORMS',
            'NBILL_MNU_BACKUP_RESTORE', 'NBILL_MNU_PENDING_ORDERS', 'NBILL_MNU_DISCOUNTS', 'NBILL_MNU_LEDGER_REPORT',
            'NBILL_MNU_QUOTES')";
    $nb_database->setQuery($sql);
    $nb_database->query();
}