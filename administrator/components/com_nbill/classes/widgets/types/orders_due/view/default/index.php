<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

nbf_common::load_language("orders");
?>
<div class="nbill-widget-orders-due">
    <p><?php echo NBILL_WIDGETS_ORDERS_DUE_DEFAULT_DESC; ?></p>
    <div id="orders_div_<?php echo $this->widget->id; ?>" class="nbill-orders-due"<?php echo $this->widget->height ? ' style="height:' . rtrim($this->widget->height, '%pxemt') . 'px"' : ''; ?>>
        <table class="orders-due-table">
            <tr>
                <th class="responsive-cell">
                    <?php echo NBILL_ORDER_NO; ?>
                </th>
                <th class="responsive-cell optional">
                    <?php echo NBILL_ORDER_PRODUCT_NAME; ?>
                </th>
                <th class="responsive-cell">
                    <?php echo NBILL_NEXT_DUE_DATE; ?>
                </th>
                <th class="responsive-cell priority">
                    <?php echo NBILL_CLIENT_NAME; ?>
                </th>
                <th class="responsive-cell optional">
                    <?php echo NBILL_ORDER_RELATING_TO; ?>
                </th>
                <th class="numeric responsive-cell">
                    <?php echo NBILL_ORDER_TOTAL; ?>
                </th>
            </tr>
            <?php
            if (!$this->widget->records || count($this->widget->records) == 0) {
                ?>
                <tr>
                    <td colspan="6"><?php echo NBILL_WIDGETS_ORDERS_DUE_NONE; ?></td>
                </tr>
                <?php
            } else {
                foreach ($this->widget->records as $record)
                {
                    ?>
                    <tr>
                        <td class="responsive-cell">
                            <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=orders&task=edit&cid=<?php echo $record->id; ?>"><?php echo $record->order_no; ?></a>
                        </td>
                        <td class="responsive-cell optional">
                            <?php if ($record->product_id) { ?>
                                <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=products&task=edit&cid=<?php echo $record->product_id; ?>">
                            <?php }
                            echo $record->product_name;
                            if ($record->product_id) { ?>
                                </a>
                            <?php } ?>
                        </td>
                        <td class="responsive-cell">
                            <?php echo date(nbf_common::get_date_format(), $record->next_due_date); ?>
                        </td>
                        <td class="responsive-cell priority">
                            <?php if ($record->client_id) { ?>
                                <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=clients&task=edit&cid=<?php echo $record->client_id; ?>">
                            <?php }
                            echo $record->client_name;
                            if ($record->product_id) { ?>
                                </a>
                            <?php } ?>
                        </td>
                        <td class="responsive-cell optional word-breakable">
                            <?php echo $record->relating_to; ?>
                        </td>
                        <td class="numeric responsive-cell">
                            <?php echo $record->currency_symbol . format_number($record->total_gross); ?>
                        </td>
                    </tr>
                    <?php
                }
            } ?>
        </table>
    </div>
</div>