<?php
/**
* Language file for Credit Notes
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

define("NBILL_ORDER_FORMS_TITLE_QUOTE", "Quote Request Forms");
define("NBILL_ORDER_FORMS_INTRO_QUOTE", "Quote request forms allow your users to ask you for a quotation for some work, through your website front end. You can have whatever fields you like on a quote request form, to gather all the information you need to give an accurate price. When a quote request form is submitted, a new quote record is created. Once you have manually added the price to the quote record, it will then be available to the user to either accept or reject. If they accept, the quote details will be used to generate a new order record.");
define("NBILL_EDIT_ORDER_FORM_QUOTE", "Edit Quote Request Form");
define("NBILL_NEW_ORDER_FORM_QUOTE", "New Quote Request Form");
define("NBILL_ORDER_FORM_THANK_YOU_REDIRECT_QUOTE", "Quote Request Complete Redirect");
define("NBILL_INSTR_ORDER_FORM_THANK_YOU_REDIRECT_QUOTE", "If you want to redirect the user to another page when a the quote request form is submitted instead of displaying the thank you message defined below, please enter a URL here.");
define("NBILL_INSTR_ORDER_FORM_THANK_YOU_QUOTE", "If the user is not redirected elsewhere by your redirect setting, above, this is the message that will be displayed on successful submission of the quote request form.");
define("NBILL_FORM_DEFAULT_THANK_YOU_QUOTE", "Thank you for requesting a quote. Your details have been submitted successfully, and we will contact you as soon as possible.");
define("NBILL_INSTR_FORM_EMAIL_CLIENT_QUOTE", "Whether to send the client a confirmation e-mail when the quote form is submitted.");
define("NBILL_INSTR_FORM_EMAIL_ADMIN_QUOTE", "Whether to send the administrator a confirmation e-mail when the quote form is submitted (RECOMMENDED!).");
define("NBILL_INSTR_FORM_AUTO_CREATE_USER_QUOTE", "Whether or not to automatically attempt to create a user record for the new contact when the quote record is created.");
define("NBILL_INSTR_FORM_PREREQ_PRODUCTS_QUOTE", "If the user must already have a certain product before they can use this quote request form, specify the prerequisite product(s) here. If more than one product is selected, the user will be able to access this quote request form if they already have ANY one of the prerequisite products.");
define("NBILL_INSTR_FORM_DISQUAL_PRODUCTS_QUOTE", "If the user must NOT already have a certain product before they can use this quote request form, specify the disqualifying product(s) here. If more than one product is selected, the user will NOT be able to access this order form if they already have ANY one of the disqualifying products.");
define("NBILL_INSTR_PAYMENT_PLAN_QUOTE", "The payment plan to assign to the new quote record.");
define("NBILL_FORM_PENDING_UNTIL_PAID_QUOTE", "Payment Required to Accept?");
define("NBILL_INSTR_FORM_PENDING_UNTIL_PAID_QUOTE", "Whether or not the client must go through the payment process before they are allowed to accept any part of the quote that is created based on submission of this quote request form. This can be manually adjusted by the administrator before finalising the quote.");
define("NBILL_FORM_AUTO_CREATE_ORDERS_QUOTE", "Auto Create Order(s) on Accept?");
define("NBILL_INSTR_FORM_AUTO_CREATE_ORDERS_QUOTE", "Whether or not to automatically create order record(s) for each item on the quote that is accepted by the client (for quotes that are created based on submission of this form). This can be manually adjusted by the administrator before finalising the quote.");
define("NBILL_FORM_AUTO_CREATE_INVOICE_QUOTE", "Auto Create Invoice(s) on Accept?");
define("NBILL_INSTR_FORM_AUTO_CREATE_INVOICE_QUOTE", "Whether or not to automatically create invoice record(s) for each item on the quote that is accepted by the client (for quotes that are created based on submission of this form). This can be manually adjusted by the administrator before finalising the quote.");
define("NBILL_FORM_AUTO_CREATE_INCOME_QUOTE", "Auto Create Income on Accept?");
define("NBILL_INSTR_FORM_AUTO_CREATE_INCOME_QUOTE", "Whether or not to automatically create income record(s) for each item on the quote that is and paid for accepted by the client (for quotes that are created based on submission of this form). This can be manually adjusted by the administrator before finalising the quote.");
define("NBILL_FORM_QUOTE_ALLOW_OFFLINE", "Allow Offline Payment?");
define("NBILL_INSTR_FORM_QUOTE_ALLOW_OFFLINE", "Whether or not to allow the user to choose to pay offline (eg. send a bank transfer) rather than handing over to a payment gateway. If 'payment required to accept' is set to 'yes', and payment is made offline, the administrator will have to manually change the quote status to 'accepted' when the payment is received so that the order and invoice record(s) can be generated.");

//Version 2.3.0
define("NBILL_FORM_QUOTE_ORDERS_IF_RECURRING", "Only if recurring");
define("NBILL_FORM_DEFAULT_QUOTE_ACCEPT_REDIRECT", "Default Quote Accept Redirect");
define("NBILL_INSTR_FORM_DEFAULT_QUOTE_ACCEPT_REDIRECT", "URL to redirect to by default when quotes based on this form are accepted (can be overridden on the quote record itself if required). Note: this redirect will not happen if payment is required to accept the quote AND the client selects to pay offline, as in that case the quote will not be marked as accepted until payment is recorded by an administrator.");

//Version 2.4.0
define("NBILL_ORDER_FORM_OFFLINE_PAYMENT_REDIRECT_QUOTE", "Offline Payment Redirect");
define("NBILL_INSTR_ORDER_FORM_OFFLINE_PAYMENT_REDIRECT_QUOTE", "If an offline payment method is selected when the customer accepts the resulting quote online, you can optionally redirect the user to another page on acceptance (eg. an article telling the customer how to pay). This will only take effect if 'payment required to accept' is set to 'yes' (otherwise, the quote accept redirect or thank you message will be used instead).");