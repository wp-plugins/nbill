<?php
/**
* Language file for Orders
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Orders
define("NBILL_ORDERS_TITLE", "Orders");
define("NBILL_ORDERS_INTRO", "This is a list of the products or services that are associated with your clients. <strong>Note:</strong> If you just want to create a one-off invoice, you can do so from the <a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=invoices\">invoice summary screen</a>. However, if you want to set up a recurring invoice (eg. once a month), you must create an order for it here. You can generate invoices for selected orders using the 'Generate' toolbar button. To generate invoices for ALL outstanding orders, use the button available on the <a href=\"" . nbf_cms::$interop->admin_page_prefix . "&action=invoices\">invoice summary screen</a>.");
define("NBILL_ORDER_START_DATE", "Date");
define("NBILL_ORDER_RELATING_TO", "Relating To");
define("NBILL_ORDER_NO", "Order Number");
define("NBILL_ORDER_PRODUCT_NAME", "Item Ordered");
define("NBILL_CANCELLED", "Cancelled");
define("NBILL_ORDER_NET_PRICE_REQUIRED", "Please enter the net price.");
define("NBILL_ORDER_START_DATE_REQUIRED", "Please specify the date this order was started.");
define("NBILL_PAY_FREQUENCY_REQUIRED", "Please specify the payment frequency.");
define("NBILL_NEXT_DUE_DATE_REQUIRED", "Please specify the next date that payment is due.");
define("NBILL_EDIT_ORDER", "Edit Order");
define("NBILL_NEW_ORDER", "New Order");
define("NBILL_SELECT_PRODUCT", "Select Product");
define("NBILL_ORDER_STATUS", "Order Status");
define("NBILL_ORDER_SHIPPING", "Shipping Service");
define("NBILL_ORDER_PAYMENT_FREQUENCY", "Payment Frequency");
define("NBILL_ORDER_CURRENCY", "Currency");
define("NBILL_ORDER_NET_PRICE", "Net Price");
define("NBILL_ORDER_TAX_AMOUNT", "Total Tax");
define("NBILL_ORDER_QUANTITY", "Quantity");
define("NBILL_ORDER_TOTAL_SHIPPING", "Total Shipping");
define("NBILL_ORDER_SHIPPING_TAX", "Tax on Shipping");
define("NBILL_START_DATE", "Start Date");
define("NBILL_END_DATE", "End Date (if applicable)");
define("NBILL_LAST_DUE_DATE", "Last Due Date");
define("NBILL_NEXT_DUE_DATE", "Next Due Date");
define("NBILL_UNIQUE_INVOICE", "Unique Invoice?");
define("NBILL_AUTO_EMAIL_INVOICE", "Auto E-Mail Invoice?");
define("NBILL_ORDER_CANCELLED", "Order Cancelled?");
define("NBILL_CANCELLATION_REASON", "Cancellation Reason");
define("NBILL_IS_ONLINE", "Online Order?");
define("NBILL_AUTO_RENEW", "Auto-Renew?");
define("NBILL_INSTR_ORDER_NO", "<strong>Note:</strong> Leave blank if adding a new order - the component will automatically assign the next available order number.");
define("NBILL_INSTR_SELECT_PRODUCT", "Select a Category/Product from the lists (you can override the text and price displayed on the invoice if you wish by using the 'Invoice Details' section below).");
define("NBILL_INSTR_ORDER_STATUS", "Indicate the status of the order.");
define("NBILL_INSTR_ORDER_SHIPPING", "Select from the list (you can optionally override the description and amount for shipping in the 'Invoice Details' section below).");
define("NBILL_INSTR_ORDER_PAYMENT_FREQUENCY", "Indicate the selected payment frequency for this order.");
define("NBILL_INSTR_ORDER_CURRENCY", "The only currencies that will be listed here are those in which a price has been defined for the selected product.");
define("NBILL_INSTR_ORDER_QUANTITY", "Enter number of products ordered. Net price and calculated tax will be multiplied by this value. Calculated shipping will be multiplied by this value unless shipping price is fixed per invoice. (You can override tax and shipping amounts below if you wish.)");
define("NBILL_INSTR_RELATING_TO", "For your own reference (eg. if this order is for website hosting, you could enter the domain name that the hosting account will be used for).");
define("NBILL_INSTR_START_DATE", "Enter the date this order was made (Note: The date format used here may be different to the date format you specified on the configuration screen. The date format specified in configuration will be used on all reports and invoices though).");
define("NBILL_INSTR_END_DATE", "<strong>Note:</strong> Only enter a date here if this order relates to a recurring invoice AND the service to which it relates has been cancelled. Invoices will NOT be generated for any orders after the end date.");
define("NBILL_INSTR_LAST_DUE_DATE", "The date the last payment for this item was due (if applicable)");
define("NBILL_INSTR_NEXT_DUE_DATE", "The date payment for this item is due (if this is a recurring payment, this will be the date the next payment becomes due - otherwise, just the due date for payment of the order).");
define("NBILL_INSTR_UNIQUE_INVOICE", "Whether this order's invoice(s) should be kept separate from any other orders for this client.");
define("NBILL_INSTR_AUTO_EMAIL_INVOICE", "Whether or not to automatically e-mail the client when the invoice for this order is generated.");
define("NBILL_INSTR_ORDER_CANCELLED", "If this order is cancelled, you can indicate it here, and optionally enter a reason below. No invoices will be generated for cancelled orders. Don't forget to enter the end date above.");
define("NBILL_INSTR_CANCELLATION_REASON", "Reason for cancellation of this order (for your own information only).");
define("NBILL_INSTR_IS_ONLINE", "Whether this is an online order (may affect whether tax is applied or not, depending on how you set up your tax rates).");
define("NBILL_INSTR_AUTO_RENEW", "If you select 'yes', the next due date will be updated every time an invoice is produced. If you select 'no', the next due date will not be updated, and therefore no further invoices will be generated automatically (selecting 'no' effectively makes this a one-off payment regardless of the payment frequency, and is intended for use in circumstances where the client needs to take some action in order to renew).");
define("NBILL_ORDER_INVOICE_INTRO", "The following values will appear on the invoice(s) generated for this order. You can override the values here by manually typing over them if you need to. However, please be aware that if you make any selections above (eg. change the product selected), your manually overridden values may be lost as these figures will be recalculated. Also, if another order appears on the same invoice, and has a shipping price which is set to a fixed price per invoice, any shipping value shown here will not be included on the invoice (unless this happens to be the order with the fixed shipping price on it). If you want to ensure that the shipping value on this order is charged regardless of any other orders this client may have made, select 'yes' for the 'Unique Invoice' setting above.");
define("NBILL_ORDER_TOTAL", "Order Total");
define("NBILL_GENERATE", "Generate");
define("NBILL_PRODUCT", "Product");
define("NBILL_EXPIRY_DATE", "Expiry Date (if applicable)");
define("NBILL_CANCELLATION_DATE", "Cancellation Date (if applicable)");
define("NBILL_INSTR_EXPIRY_DATE", "Enter the date this order will expire (no further invoices will be generated for the order after the expiry date, however if an invoice is due ON the expiry date, it WILL be generated).");
define("NBILL_INSTR_CANCELLATION_DATE", "<strong>Note:</strong> Only enter a date here if this order relates to a recurring invoice AND the service to which it relates has been cancelled. Invoices will NOT be generated for any orders that have a cancellation date.");
define("NBILL_SHOW_INVOICES_FOR_ORDER", "Show all invoices for this order");
define("NBILL_CANNOT_ORDER_SUB_WITHOUT_USER", "ERROR! You cannot order a user subscription for this client because the client does not have an associated user id. Assign a user to the client first, then create the subscription order.");
define("NBILL_ORDER_VOUCHER_CODE", "Voucher Code");
define("NBILL_INSTR_ORDER_VOUCHER_CODE", "If you enter a voucher code here which relates to a discount that you have defined, the discount amount will be applied for every invoice that is generated for this order. If the value you enter here does not match a discount record, it will just be ignored.");
define("NBILL_ORDER_VOUCHER_DISCOUNT_NOT_SHOWN", "NOTE: Any discounts (from either voucher codes or client credit amounts) will not be shown here, but will be applied at the time the invoice is generated.");
define("NBILL_ORDER_FORM_FIELD_VALUES", "Order Form Field Values");
define("NBILL_INSTR_ORDER_FORM_FIELD_VALUES", "Values entered by the user on the order form at the time the order was placed (this will usually be populated automatically when an order is created based on a submitted order form, but you can type in your own value if you wish).");
define("NBILL_CLIENT_REQUIRED", "Please select a Client record.");
define("NBILL_ORDER_PRODUCT_NAME_REQUIRED", "Please select a product.");
define("NBILL_ORDER_SHOW_PAYLINK", "Show Payment Link?");
define("NBILL_INSTR_ORDER_SHOW_PAYLINK", "Whether or not to show a 'Pay This Invoice' link next to invoices for this order. If you select 'Show' here, and there are other orders on the same invoice, they must all allow the payment link to be shown for this to take effect. This setting can be overridden at the invoice level. The value of the global setting depends on the display options (you can set a payment frequency threshold - see the display options screen for more information). The payment link will only be shown if the invoice is unpaid, a default gateway is specified on the vendor record, and the gross total on the invoice is greater than zero.");
define("NBILL_ORDER_PAYLINK_USE_GLOBAL", "Use Global");
define("NBILL_ORDER_PAYLINK_SHOW", "Show");
define("NBILL_ORDER_PAYLINK_HIDE", "Hide");
define("NBILL_ORDER_DISCOUNT_TITLE", "Discounts used on this order");
define("NBILL_ORDER_DISCOUNT_INTRO", "You can optionally specify that certain discounts are applicable to this Order. If more than one discount is applicable, use the Priority value to indicate which one to evaluate first (a discount with a priority of 0 will be applied before a discount with a priority of 1). Priorities assigned here override any priorities set on the discounts themselves. To control the properties of the discount (eg. amount, whether it is exclusive, etc.), edit the discount record via the discounts page. NOTE: Just because a discount is present here does not mean it will be applied to all invoices for this order (eg. if the discount definition stipulates that it does not apply to recurring payments or has a date restriction).");
define("NBILL_ORDER_DISCOUNT", "Discount");
define("NBILL_ORDER_DISCOUNT_ORDERING", "Priority");
define("NBILL_ADD_ORDER_DISCOUNT", "Add Discount");
define("NBILL_DELETE_DISCOUNT", "Delete Discount");
define("NBILL_PLEASE_SELECT_DISCOUNT", "Please select a Discount to add.");
define("NBILL_ORDER_DISCOUNT_DUPLICATION", "This Discount has already been added.");
define("NBILL_ORDER_PAYLINK", "Order Payment Schedule");
define("NBILL_ORDER_PAYLINK_PROMPT", "If you need to set up a new payment schedule for this order (eg. to renew a subscription [where 'auto-renew' is set to 'no' on the order] or if the client's credit card expired and their original payment schedule was cancelled by the PSP), you can send the following link to the client: ");
define("NBILL_ORDER_PAYLINK_WARNING", "WARNING! The client record MUST be associated with a user record for this to work, as they will be prompted to login before paying.");

/* Version 1.1.4 */
define("NBILL_NO_DATE_ENTERED", "A valid date was not entered. No action taken.");

/* Version 1.1.4 SP1 */
define("NBILL_ORDER_WARNING_QTY_ZERO", "WARNING! The quantity is currently set to zero. This means the order value will also be zero. Are you sure you want to continue?");

//Version 1.2.0
define("NBILL_ORDER_ORDER_VALUE", "Net Value");
define("NBILL_ORDER_ORDER_VALUE_HELP", "This is the total value of the order BEFORE tax, discounts, and shipping fees are applied.");
define("NBILL_ORDER_TRACKING_ID", "Parcel Tracking ID");
define("NBILL_INSTR_ORDER_TRACKING_ID", "If you have a parcel tracking URL set up on the appropriate shipping record, you can assign a tracking ID here that will be combined with that URL to create a link in the website front-end to allow a user to track their package online (subject to this being allowed by the rules specified on the display options page).");

//Version 1.2.1
define("NBILL_ORDER_SHOW_ALL", "Show All");
define("NBILL_ORDER_SHOW_RESET", "Reset Date Range");

//Version 1.2.6
define("NBILL_ORDERS_RECORD_LIMIT_WARNING", "WARNING! As there are %s or more clients in your database, only the first %s records have been loaded into the above list. If the client you require is not here, you can either click on 'Show All' (below), or select the 'create new order' icon on the client list (the appropriate record will then be selected here automatically).");

//Version 2.0.0
//Version 2.0.1
define("NBILL_ORDER_FORM_FILE_UPLOADS", "Uploaded File");
define("NBILL_INSTR_ORDER_FORM_FILE_UPLOADS", "File that was uploaded by the client when they submitted the order form that was used to create this order record.");
define("NBILL_ORDER_FILE_NOT_FOUND", "File %s not found.");

//Version 2.1.0
define("NBILL_ORDER_GOTO_CLIENT", "Go to Client record");
define("NBILL_ORDER_MULTI_STATUS_UPDATE", "Multiple Status Update");
define("NBILL_ORDER_SET_STATUS_TO", "Set all selected records to:");
define("NBILL_ORDER_MULTI_STATUS_SELECT", "Please select a status from the dropdown list");
define("NBILL_ORDER_MULTI_STATUS_SELECT_RECORDS", "Please check the box next to one or more records from the list of orders below");
define("NBILL_ORDER_MULTI_STATUS_SURE", "You are about to change the status of ALL of the selected orders. Are you sure you want to continue?");
define("NBILL_ORDER_MULTI_STATUS_COMPLETE", "Status has been updated to '%s' on %s order record(s)");

//Version 2.1.1
define("NBILL_ORDER_CUSTOM_LEDGER_CODE", "Custom Ledger Code");
define("NBILL_ORDER_CUSTOM_TAX_RATE", "Custom Tax Rate");
define("NBILL_ORDER_CUSTOM_SETTINGS_SHOW", "+ Show Custom Settings");
define("NBILL_ORDER_CUSTOM_SETTINGS_HIDE", "+ Hide Custom Settings");
define("NBILL_ORDER_CUSTOM_SETTINGS_WARN", "(Only modify these values if you want to override the normal values or if there is no product selected, otherwise leave them blank)");
define("NBILL_ORDERS_CUSTOM_TAX_CHANGE_TITLE", "Custom Tax Rate Change");
define("NBILL_ORDERS_CUSTOM_TAX_CHANGE_INTRO", "You have updated a custom tax rate. Your changes have been saved, however, " . NBILL_BRANDING_NAME . " has detected %s order records that are using the same tax rate. Please select what action to take below.");
define("NBILL_ORDERS_CUSTOM_TAX_CHANGE_INTRO_AUTO", "%s order(s) which are set to auto-renew also currently hold a custom tax rate of %s;. Select one of the following options and click on the 'Save' toolbar button to update these records, or on 'Cancel' to continue without updating any orders.");
define("NBILL_ORDERS_CUSTOM_TAX_CHANGE_INTRO_MANUAL", "%s order(s) which are NOT set to auto-renew also currently hold a custom tax rate of %s. Select one of the following options and click on the 'Save' toolbar button to update these records, or on 'Cancel' to continue without updating any orders.");
define("NBILL_ORDERS_CUSTOM_RECALC_TAX_AND_NET", "Update custom tax rate from %s to %s, then re-calculate the tax and net amounts, keeping the gross amount the same");
define("NBILL_ORDERS_CUSTOM_RECALC_TAX_AND_GROSS", "Update custom tax rate from %s to %s, then re-calculate the tax and gross amounts, keeping the net amount the same");
define("NBILL_ORDERS_CUSTOM_NO_RECALC", "Update custom tax rate from %s to %s, but do not recalculate amounts (NOT RECOMMENDED)");
define("NBILL_ORDERS_CUSTOM_DO_NOTHING", "Do not update any of these orders");
define("NBILL_ORDERS_CUSTOM_RECOMMENDED", "(Recommended)");
define("NBILL_ORDER_CUSTOM_SHOW_ROWS", "+ Show Orders (allows selection of which order records to update)");
define("NBILL_ORDER_CUSTOM_HIDE_ROWS", "- Hide order records");
define("NBILL_ORDER_CUSTOM_UPDATED", "%s order(s) updated");
define("NBILL_USE_GLOBAL_RATE", "Global Tax Rate");

//Version 3.0.0
define("NBILL_ORDERS_ELECTRONIC_CHANGE_TITLE", "Electronic Delivery Change");
define("NBILL_ORDERS_ELECTRONIC_CHANGE_INTRO", "You have changed the electronic delivery status of a product. Your changes have been saved, however, " . NBILL_BRANDING_NAME . " has detected %s order records for clients within the EU that are currently using a tax rate which is not compatible with the electronic delivery status of the product (sales of electronically delivered products within the EU must charge tax at the rate prevailing in the country of the customer - non-electronically delivered products should charge at the rate prevailing in the country of the seller). Please select what action to take below.");
define("NBILL_ORDERS_ELECTRONIC_CHANGE_INTRO_AUTO", "%s order(s) which are set to auto-renew hold an incompatible tax rate. Select one of the following options and click on the 'Save' toolbar button to update these records, or on 'Cancel' to continue without updating any orders.");
define("NBILL_ORDERS_ELECTRONIC_CHANGE_INTRO_MANUAL", "%s order(s) which are NOT set to auto-renew hold an incompatible tax rate. Select one of the following options and click on the 'Save' toolbar button to update these records, or on 'Cancel' to continue without updating any orders.");
define("NBILL_ORDERS_ELECTRONIC_RECALC_TAX_AND_NET", "Re-calculate the tax and net amounts, keeping the gross amount the same");
define("NBILL_ORDERS_ELECTRONIC_RECALC_TAX_AND_GROSS", "Re-calculate the tax and gross amounts, keeping the net amount the same");
define("NBILL_ORDERS_ELECTRONIC_DO_NOTHING", "Do not update any of these orders");

//Version 3.0.5
define("NBILL_ORDERS_AUTO_CREATE_INVOICE", "Auto Create Invoice?");
define("NBILL_ORDERS_AUTO_CREATE_INVOICE_HELP", "Whether or not to generate invoices for this order.");
define("NBILL_ORDERS_AUTO_CREATE_INCOME", "Auto Create Income?");
define("NBILL_ORDERS_AUTO_CREATE_INCOME_HELP", "Whether or not to generate income records when payments are received for this order.");
define("NBILL_ORDERS_GATEWAY_TXN_ID", "Gateway Transaction ID");
define("NBILL_ORDERS_GATEWAY_TXN_ID_HELP", "Transaction reference number used by the payment gateway for this order. You should normally leave this alone, as it will be handled automatically, but in rare cases where payments need to be reassigned to a different order record, it can be updated.");