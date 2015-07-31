<?php
/**
* Language file for Quotes
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

define("NBILL_QUOTES_TITLE", "Quotations");
define("NBILL_QUOTES_INTRO", "You can create custom quotes here for existing or potential clients. Quote records can either be created by an administrator, or automatically when someone fills in a quote request form. When a quote is accepted, an order and/or invoice record can be generated automatically based on the quote.");
define("NBILL_INVOICE_NUMBER_QU", "Quote Number");
define("NBILL_INVOICE_DATE_QU", "Quote Date");
define("NBILL_FIRST_ITEM_QU", "First Item on Quote");
define("NBILL_EDIT_INVOICE_QU", "Edit Quote");
define("NBILL_EMAIL_NOW_QU", "Send this quote to the contact by e-mail now");
define("NBILL_BILLING_NAME_REQUIRED_QU", "Please enter a contact name");
define("NBILL_NEW_INVOICE_QU", "New Quote");
define("NBILL_INVOICE_DETAILS_QU", "Quote Details");
define("NBILL_INSTR_INVOICE_NUMBER_QU", "<strong>Note:</strong> Leave blank if adding a new quote - the component will automatically assign the next available quote number.");
define("NBILL_INSTR_BILLING_NAME_QU", "The name of the person and/or company being quoted.");
define("NBILL_BILLING_NAME_QU", "Client Name");
define("NBILL_BILLING_ADDRESS_QU", "Client Address");
define("NBILL_BILLING_COUNTRY_QU", "Country");
define("NBILL_INVOICE_ITEMS_QU", "Quote Items");
define("NBILL_INSTR_VENDOR_NAME_QU", "Enter the name that you want to appear on your quotes");
define("NBILL_INSTR_VENDOR_ADDRESS_QU", "Enter the address that you want to appear on your quotes");
define("NBILL_INVOICE_RECORD_LIMIT_WARNING_QU", "WARNING! As there are %s or more contacts in your database, only the first %s have been loaded into the above list. If the contact you require is not here, please use the 'create new quote' icon on the contact list (the appropriate record will then be selected here automatically).");
define("NBILL_INVOICE_TOTAL_THIS_PAGE_QU", "Total for all quotes shown on THIS page:");
define("NBILL_INVOICE_TOTAL_ALL_PAGES_QU", "Total for ALL quotes on ALL pages in the selected date range:");
define("NBILL_QUOTE_STATUS", "Quote Status");
define("NBILL_INSTR_QUOTE_STATUS", "NEW means that the client or potential client has requested a quote but you have not yet finalised the price (any items you may add to the quote are not visible to the user). <br />ON HOLD means you are awaiting further information from the client (any items you have added are not visible to the user). <br />QUOTED means you have supplied the price (any items you added to the quote, including the total, are visible to the user). <br />ACCEPTED means the user has accepted the entire quote - typically this would result in one or more order or invoice records being generated. <br />PART ACCEPTED means they have accepted one or more items, but not all items on the quote - typically this would result in one or more order or invoice records being generated.<br />REJECTED means the user has rejected the entire quote.<br />WITHDRAWN means that an administrator has decided that the quote should no longer be available to the client (it is effectively 'unpublished').");
define("NBILL_QUOTE_CLIENT_REQUIRED", "Please select a client (or potential client)");
define("NBILL_QUOTE_CORRESPONDENCE", "Quote Corre");
define("NBILL_INSTR_QUOTE_CORRESPONDENCE", "The data that was entered by the user on the quote request form (if applicable), as well as any <span class=\"word-breakable\">correspondence</span> requesting or supplying further information. Anything entered here is always visible to the user.");
define("NBILL_QUOTE_PAY_FREQ", "Payment Frequency");
define("NBILL_QUOTE_AUTO_RENEW", "Auto Renew?");
define("NBILL_QUOTE_RELATING_TO", "Relating to");
define("NBILL_QUOTE_UNIQUE_INVOICE", "Unique Invoice?");
define("NBILL_QUOTE_ITEM_MANDATORY", "Mandatory Item?");
define("NBILL_QUOTE_IS_ITEM_ACCEPTED", "Item Accepted?");
define("NBILL_QUOTE_SAVED", "The Quote has been saved. ");
define("NBILL_QUOTE_ON_HOLD_MESSAGE_INTRO", "The status of the quote is '" . (defined('NBILL_STATUS_QUOTE_ON_HOLD') ? NBILL_STATUS_QUOTE_ON_HOLD : 'On Hold') . "'. If you would like to send a message to the client requesting further information, please enter your message below and click on send. The message will also be stored on the quote record.");
define("NBILL_QUOTE_QUOTED_MESSAGE_INTRO", "The status of the quote is '" . (defined('NBILL_STATUS_QUOTE_QUOTED') ? NBILL_STATUS_QUOTE_QUOTED : 'Quoted') . "'. If you would like to send the quote to the client now, please enter or amend the message below and click on send.");
define("NBILL_QUOTE_GENERATE_INTRO", "One or more items on this quote are marked as accepted. If you would like to generate order and/or invoice records for these items, please confirm the details below and click on 'Generate'.");
define("NBILL_NO_ACCEPTED_ITEMS", "No quote items are marked as accepted. You cannot generate any orders or invoices for this quote.");
define("NBILL_QUOTE_CREATE_ORDER", "Create Order?");
define("NBILL_QUOTE_CREATE_INVOICE", "Create Invoice?");
define("NBILL_QUOTE_GENERATE_RECORDS", "Generate");
define("NBILL_QUOTE_GENERATE_ABORT", "Abort");
define("NBILL_QUOTE_ORDER_NEXT_DUE", "Next Due Date");
define("NBILL_QUOTE_PAY_TO_ACCEPT", "Payment Required to Accept?");
define("NBILL_INSTR_QUOTE_PAY_TO_ACCEPT", "Whether or not the client must go through the payment process before they are allowed accept any part of the quote. If the selected payment plan (specified above) indicates a deferred payment, no payment will be taken and the relevant order/invoice records will be created immediately, if applicable.");
define("NBILL_QUOTE_AUTO_CREATE_ORDERS", "Auto Create Order(s)?");
define("NBILL_INSTR_QUOTE_AUTO_CREATE_ORDERS", "Whether or not to automatically create order record(s) for each item on the quote that is accepted by the client. If Payment is required to accept the quote (specified above), this will only happen once payment has been confirmed by the payment gateway, or if an administrator generates the record(s).");
define("NBILL_QUOTE_AUTO_CREATE_INVOICES", "Auto Create Invoice(s)?");
define("NBILL_INSTR_QUOTE_AUTO_CREATE_INVOICES", "Whether or not to automatically create invoice record(s) for each item on the quote that is accepted by the client. If Payment is required to accept the quote (specified above), this will only happen once payment has been confirmed by the payment gateway, or if an administrator generates the record(s).");
define("NBILL_QUOTE_AUTO_CREATE_INCOME", "Auto Create Income?");
define("NBILL_INSTR_QUOTE_AUTO_CREATE_INCOME", "Whether or not to automatically create income record(s) for each item on the quote that is accepted and paid for by the client. This will only happen once payment has been confirmed by the payment gateway.");
define("NBILL_QUOTE_RECORDS_GENERATED", "%s Order record(s) and %s Invoice record(s) Generated.");
define("NBILL_QUOTE_RECORDS_GENERATED_ERRORS", "WARNING! The following error(s) occurred: ");
define("NBILL_QUOTE_GENERATE_WARNING_ORDERS", "WARNING! The following order record(s) have already been generated based on this quote: ");
define("NBILL_QUOTE_GENERATE_WARNING_INVOICES", "WARNING! The following invoice record(s) have already been generated based on this quote: ");
define("NBILL_WARNING_QUOTE_ACCEPTED", "WARNING! This quote has already been accepted. Are you sure you want to change it?");
define("NBILL_WARNING_QUOTE_PART_ACCEPTED", "WARNING! This quote has already been partially accepted. Are you sure you want to change it?");

//Version 2.0.9
define("NBILL_QUOTE_PAY_FREQ_CHANGED", "WARNING! You have changed the payment frequency but the price will not be changed automatically. Please check that the price you are quoting is still correct.");

//Version 2.1.0
define("NBILL_QUOTE_ADMIN_AWAITING_PAYMENT", "(Awaiting Payment)");
define("NBILL_QUOTE_PAID_OFFLINE", "Click to register offline payment");
define("NBILL_QUOTE_PAY_OFFLINE_GENERATE", "This will generate order and/or invoice records for all accepted items on this quote and prompt you to enter the amount received. Are you sure?");
define("NBILL_QUOTE_OFFLINE_PAID_INVOICES_GENERATED", "%s invoice(s) generated");
define("NBILL_QUOTE_HTML_INTRO", "Quote Introduction");
define("NBILL_INSTR_QUOTE_HTML_INTRO", "You can enter any introductory text you like here, for example to explain the scope of the work.");
define("NBILL_QUOTE_SHOW_WARNING", "Show Warning?");
define("NBILL_INSTR_QUOTE_SHOW_WARNING", "Whether or not to show a javascript prompt warning that this is a legally binding contract and asking the user to confirm before accepting this quote.");
define("NBILL_QUOTE_ACCEPTED_BUT_AWAITING_PAYMENT", "Awaiting Payment");

//Version 2.2.0
define("NBILL_QUOTE_NO_INVOICE_GENERATED", "An invoice could not be generated for this quote. As a result, the quote status has been updated (pending items are now marked as accepted), but the receipt has not been recorded. You may need to create invoice and/or income records manually for this transaction.");

//Version 2.3.0
define("NBILL_QUOTE_ORDERS_IF_RECURRING", "Only if recurring");
define("NBILL_QUOTE_ACCEPT_REDIRECT", "Quote Accept Redirect");
define("NBILL_INSTR_QUOTE_ACCEPT_REDIRECT", "If you want to redirect to a certain page when this quote is accepted (or partially accepted), enter the full URL here (note: this redirect will not happen if payment is required to accept the quote AND the client selects to pay offline, as in that case the quote is not marked as accepted until payment is recorded by an administrator).");

//Version 2.4.0
define("NBILL_DOC_SECTION_QUOTE_ATOMIC", "Atomic? ");
define("NBILL_DOC_SECTION_QUOTE_ATOMIC_HELP", "Whether or not ALL items in the section must be accepted or rejected as a whole");
define("NBILL_QUOTE_TOTAL_ACCEPTED", "Accepted Total");
define("NBILL_QUOTE_ACCEPTED_TOTAL_DISCOUNTED", " (approx)");
define("NBILL_INSTR_DOC_DISCOUNT_QU", "Discount to apply to the whole quote, and any orders/invoices that are generated based on it (Note: the discount will only be applied if the discount rules are met - if the discount specified here has a voucher code, the customer will be prompted to enter it when paying for the quote online).");