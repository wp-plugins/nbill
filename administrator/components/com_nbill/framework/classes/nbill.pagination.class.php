<?php
/**
* Handles pagination for lists of records
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* Pagination object to store details about where we are within a list and render the appropriate HTML links and up/down buttons
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_pagination
{
    /** @var integer Total number of records */
    public $record_count;
    /** @var integer How many records to skip (on previous pages) */
    public $list_offset;
    /** @var integer How many records to show per page */
    public $records_per_page;
    /** @var integer The page number we are currently on (calculated) */
    public $page_no;
    /** @var integer Total number of pages (calculated) */
    public $page_count;

    /**
    * Initialise the pagination of a list, using $_REQUEST or (failing that) $_SERVER variables to get the current list offset
    * @param string $type Type of record (so we can store the position of different lists in different session variables)
    * @param integer $record_count Total number of records to be paginated
    */
    public function __construct($type, $record_count)
    {
        $this->record_count = $record_count;

        //Try to get list offset from postback
        $this->list_offset = intval(nbf_common::get_param($_REQUEST, 'list_offset'));
        if (!isset($_REQUEST['list_offset']))
        {
            //If not changed during postback, use the stored session value (or default to zero)
            $this->list_offset = intval(nbf_common::get_param($_SESSION, 'list_offset_' . $type));
        }
        //Store the offset in session in case we go somewhere else and come back to this list
        $_SESSION['list_offset_' . $type] = $this->list_offset;

        //Try to get the number of records per page from postback
        $this->records_per_page = nbf_common::get_param($_REQUEST, 'records_per_page');
        if (!$this->records_per_page)
        {
            //If not changed during postback, use the stored session value
            $this->records_per_page = nbf_common::get_param($_SESSION, 'records_per_page');
        }
        if (!$this->records_per_page)
        {
            //If not changed during postback, and no stored session value, get the default from the CMS interop object
            $this->records_per_page = nbf_cms::$interop->records_per_page;
        }
        if ($this->records_per_page == 'all') {
            $this->records_per_page = 2147483647;
        }
        $this->records_per_page = intval($this->records_per_page);

        if (!$this->records_per_page)
        {
            //Should never happen, but if it does, we don't want division by zero problems, so default to 50
            $this->records_per_page = 50;
        }
        //Store the number of records per page in session so we don't have to reset it each time
        $_SESSION['records_per_page'] = $this->records_per_page;

        $this->_calculate_page_no();
    }

    /**
    * Internal function to calculate the current page number
    */
    private function _calculate_page_no()
    {
        $this->page_no = floor($this->list_offset / $this->records_per_page) + 1;
        $this->page_count = ceil($this->record_count / $this->records_per_page);
    }

    /**
    * Construct the HTML to show a list of page links and allow the option to change the number of records per page
    * @return string The HTML for the page footer
    */
    public function render_page_footer($form_id = null)
    {
        $this->_calculate_page_no();

        if ($form_id !== null)
        {
            $form_elem = "document.getElementById('$form_id')";
        }
        else
        {
            $form_elem = 'document.adminForm';
        }

        //Render Start link
        if ($this->page_no > 1)
        {
            $links = '<a href="#" onclick="document.getElementById(\'list_offset\').value=\'0\';' . $form_elem . '.submit();return false;">' . str_replace("<", "&lt;", NBILL_PAGE_START) . '</a>';
        }
        else
        {
            $links = str_replace("<", "&lt;", NBILL_PAGE_START);
        }
        //Render Previous link
        if ($this->page_no > 1)
        {
            $links .= '&nbsp;<a href="#" onclick="document.getElementById(\'list_offset\').value=\'' . ((($this->page_no - 1) * $this->records_per_page) - $this->records_per_page) . '\';' . $form_elem . '.submit();return false;">' . str_replace(">", "&gt;", NBILL_PAGE_PREVIOUS) . '</a>';
        }
        else
        {
            $links .= '&nbsp;' . str_replace("<", "&lt;", NBILL_PAGE_PREVIOUS);
        }
        $links .= '&nbsp;';

        //Render a link for each page with a line break every 50 pages
        for ($i = 1; $i <= $this->page_count; $i++)
        {
            if ($this->page_no != $i)
            {
                $links .= ' <a href="#" onclick="document.getElementById(\'list_offset\').value=\'' . ($this->records_per_page * ($i - 1)) . '\';' . $form_elem . '.submit();return false;">' . $i . '</a>';
            }
            else
            {
                $links .= ' ' . $i;
            }
            if ($i % 50 == 0)
            {
                $links .= '<br />';
            }
        }

        $links .= '&nbsp;';
        //Render Next link
        if ($this->page_no < $this->page_count)
        {
            $links .= '&nbsp;<a href="#" onclick="document.getElementById(\'list_offset\').value=\'' . ($this->list_offset + $this->records_per_page) . '\';' . $form_elem . '.submit();return false;">' . str_replace(">", "&gt;", NBILL_PAGE_NEXT) . '</a>';
        }
        else
        {
            $links .= '&nbsp;' . str_replace(">", "&gt;", NBILL_PAGE_NEXT);
        }
        //Render End link
        if ($this->page_no < $this->page_count)
        {
            $links .= '&nbsp;<a href="#" onclick="document.getElementById(\'list_offset\').value=\'' . ($this->page_count - 1) * $this->records_per_page . '\';' . $form_elem . '.submit();return false;">' . str_replace(">", "&gt;", NBILL_PAGE_END) . '</a>';
        }
        else
        {
            $links .= '&nbsp;' . str_replace(">", "&gt;", NBILL_PAGE_END);
        }

        $footer = '<table class="adminlist table table-striped" style="margin-left:auto;margin-right:auto;">
        <tr>
            <th style="text-align:center">
                ' . $links . '
                <input type="hidden" name="list_offset" id="list_offset" value="' . nbf_common::get_param($_REQUEST, 'list_offset') . '" />
            </th>
        </tr>
        <tr>
            <td style="text-align:center">
                ' . NBILL_DISPLAY . '&nbsp;
                <select name="records_per_page" id="records_per_page" onchange="' . $form_elem . '.submit();" style="width:auto;">
                    <option value="5"' . ($this->records_per_page == 5 ? ' selected="selected"' : '') . '>5</option>
                    <option value="10"' . ($this->records_per_page == 10 ? ' selected="selected"' : '') . '>10</option>
                    <option value="15"' . ($this->records_per_page == 15 ? ' selected="selected"' : '') . '>15</option>
                    <option value="20"' . ($this->records_per_page == 20 ? ' selected="selected"' : '') . '>20</option>
                    <option value="25"' . ($this->records_per_page == 25 ? ' selected="selected"' : '') . '>25</option>
                    <option value="30"' . ($this->records_per_page == 30 ? ' selected="selected"' : '') . '>30</option>
                    <option value="50"' . ($this->records_per_page == 50? ' selected="selected"' : '') . '>50</option>
                    <option value="100"' . ($this->records_per_page == 100 ? ' selected="selected"' : '') . '>100</option>
                    <option value="200"' . ($this->records_per_page == 200 ? ' selected="selected"' : '') . '>200</option>
                    <option value="300"' . ($this->records_per_page == 300 ? ' selected="selected"' : '') . '>300</option>
                    <option value="all"' . ($this->records_per_page == 'all' || $this->records_per_page == 2147483647 ? ' selected="selected"' : '') . '>All</option>
                </select>&nbsp;' . NBILL_RESULTS_PER_PAGE . '&nbsp;&nbsp;
                ' . sprintf(NBILL_RESULTS_SHOWING, ($this->record_count > 0 ? $this->list_offset + 1 : 0), ($this->list_offset + $this->records_per_page < $this->record_count ? $this->list_offset + $this->records_per_page : $this->record_count), $this->record_count) . '
            </td>
        </tr>
        </table>';

        return $footer;
    }

    /**
    * Display a clickable icon to move a record up
    * @param integer $row_index Position of record within the current page
    * @param boolean $show_icon Whether or not to show the icon (if not, a non-breaking space is returned, to keep the containing table layout clean)
    */
    function order_up_arrow($row_index, $show_icon = true)
    {
        $output = "";
        if ($show_icon && ($row_index > 0 || $this->list_offset + $row_index > 0))
        {
            $output = '<a href="#" title="' . NBILL_MOVE_UP . '" onclick="for(var i=0; i<' . ($this->records_per_page > $this->record_count ? $this->record_count : $this->records_per_page) . ';i++) {if (document.getElementById(\'cb\' + i)){document.getElementById(\'cb\' + i).checked=false}};document.getElementById(\'cb' . $row_index . '\').checked=true;document.adminForm.task.value=\'orderup\';document.adminForm.submit();return false;">';
            $output .= '<img src="' . nbf_cms::$interop->nbill_site_url_path . '/images/move_up.png" alt="' . NBILL_MOVE_UP . '" border="0" />';
            $output .= '</a>';
        }
        else
        {
            $output = "&nbsp;";
        }
        return $output;
    }

    /**
    * Display a clickable icon to move a record down
    * @param integer $row_index Position of record within the current page
    * @param integer $rows_on_page Number of records shown on the current page
    * @param boolean $show_icon Whether or not to show the icon (if not, a non-breaking space is returned, to keep the containing table layout clean)
    */
    function order_down_arrow($row_index, $rows_on_page, $show_icon = true)
    {
        $output = "";
        if ($show_icon && ($row_index < $rows_on_page - 1 || $this->list_offset + $row_index < $this->record_count - 1))
        {
            $output = '<a href="#" title="' . NBILL_MOVE_DOWN . '" onclick="for(var i=0; i<' . ($this->records_per_page > $this->record_count ? $this->record_count : $this->records_per_page) . ';i++) {if(document.getElementById(\'cb\' + i)){document.getElementById(\'cb\' + i).checked=false}};document.getElementById(\'cb' . $row_index . '\').checked=true;document.adminForm.task.value=\'orderdown\';document.adminForm.submit();return false;">';
            $output .= '<img src="' . nbf_cms::$interop->nbill_site_url_path . '/images/move_down.png" alt="' . NBILL_MOVE_DOWN . '" border="0" />';
            $output .= '</a>';
        }
        else
        {
            $output = "&nbsp;";
        }
        return $output;
    }
}