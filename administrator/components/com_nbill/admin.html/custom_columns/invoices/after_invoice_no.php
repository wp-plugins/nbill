<?php
/* This is an example of how to add a custom column to a back-end page.
*  To implement this example, delete the slash and astersik /* on line 6
*/

/*

class nbill_admin_invoices_after_invoice_no
{
    public static function render_header($rows)
    {
        nbf_common::load_language('invoices');
        ?>
        <th class="sectiontableheader"><?php echo NBILL_REFERENCE; ?></th>
        <?php
    }
    public static function render_row($row)
    {
        ?>
        <td><?php echo @$row->reference; ?></td>
        <?php
    }
}
//*/