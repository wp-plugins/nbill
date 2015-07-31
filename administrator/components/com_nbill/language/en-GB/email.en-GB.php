<?php
/**
* Language file for text that appears in E-Mails.
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//General
define("NBILL_EM_GREETING", "Dear %s,");
define("NBILL_EM_REGARDS", "Regards,");
define("NBILL_CLIENT_GENERIC", "Client");
define("NBILL_EM_EXISTING_CLIENT", "%s (Existing Client)");

//Invoices
define("NBILL_EM_INVOICE_SUBJECT", "Invoice %s from %s");
define("NBILL_EMAIL_INVOICE_INTRO", "E-mail Invoice to Client");
define("NBILL_EM_NEW_INVOICE", "New Invoice");
define("NBILL_EM_EMBEDDED_INVOICE_INTRO", "The following invoice has been generated for you:");
define("NBILL_EM_NEW_INVOICE_PAR_1", "This e-mail is to inform you that a new invoice has been generated for you.");
define("NBILL_EM_NEW_INVOICE_PAR_1_ATTACHED", "The attached invoice has been generated for you. If your reader can't open the attachment, please save the file and open it from your computer.");
define("NBILL_EM_NEW_INVOICE_PAR_2", "You can see all of your invoices online by logging in at %s. If you no longer wish to receive your invoices by e-mail, simply log in and click on `My Profile` in the `My Account` area to set your preferences.");
define("NBILL_EM_NEW_INVOICE_PAR_2_USERNAME", "You can see all of your invoices online by logging in at %s (your username is %s). If you no longer wish to receive your invoices by e-mail, simply log in and click on `My Profile` in the `My Account` area to set your preferences.");

//Credit notes
define("NBILL_EM_CREDIT_SUBJECT", "Credit Note %s from %s");
define("NBILL_EMAIL_CREDIT_INTRO", "E-mail Credit Note to Client");
define("NBILL_EM_NEW_CREDIT", "New Credit Note");
define("NBILL_EM_EMBEDDED_CREDIT_INTRO", "The following credit note has been generated for you:");
define("NBILL_EM_NEW_CREDIT_PAR_1", "This e-mail is to inform you that a new credit note has been generated for you.");
define("NBILL_EM_NEW_CREDIT_PAR_1_ATTACHED", "The attached credit note has been generated for you. If your reader can't open the attachment, please save the file and open it from your computer.");
define("NBILL_EM_NEW_CREDIT_PAR_2", "You can see all of your credit notes online by logging in at %s.");
define("NBILL_EM_NEW_CREDIT_PAR_2_USERNAME", "You can see all of your credit notes online by logging in at %s (your username is %s).");

//Quotes
define("NBILL_EM_QUOTE_SUBJECT", "Quotation %s from %s");
define("NBILL_EM_QUOTE_ON_HOLD_SUBJECT", "Your Quote %s - Request for further information from %s");
define("NBILL_EMAIL_QUOTE_INTRO", "E-mail Quote to Client");
define("NBILL_EM_NEW_QUOTE", "New Quote");
define("NBILL_EM_EMBEDDED_QUOTE_INTRO", "The following quote has been generated for you:");
define("NBILL_EM_NEW_QUOTE_PAR_1", "Thank you for requesting a quotation. To view your quote online and accept, partially accept, or reject it, please visit: %s.");
define("NBILL_EM_NEW_QUOTE_PAR_1_ATTACHED", "Thank you for requesting a quotation. Please find attached our quotation in accordance with your stated requirements. If your reader can't open the attachment, please save the file and open it from your computer. To view this quote online and accept, partially accept, or reject it, please visit: %s.");
define("NBILL_EM_NEW_QUOTE_PAR_2", "You can see all of your quotes online by logging in at %s.");
define("NBILL_EM_NEW_QUOTE_PAR_2_USERNAME", "You can see all of your quotes online by logging in at %s (your username is %s).");
define("NBILL_EM_NEW_QUOTE_PAR_3", "PLEASE DO NOT REPLY DIRECTLY TO THIS E-MAIL - If you wish to respond, please visit %s (this is so that your response can be recorded on the quotation document).");
define("NBILL_EM_NEW_QUOTE_CORRESPONDENCE_INTRO", "This quote is based on the following correspondence:");
define("NBILL_EM_NEW_QUOTE_REQUEST", "Quote Request Acknowledgement");
define("NBILL_EM_NEW_QUOTE_REQUEST_SUBJECT", "New Quote Request: %s - %s (%s)");
define("NBILL_EM_NEW_QUOTE_REQUEST_PAR_1", "This e-mail is to acknowledge that the following details have been submitted in order to request a quote on %s. We will review your requirements and contact you with our quotation as soon as possible.");
define("NBILL_EM_NEW_QUOTE_REQUEST_ADMIN", "Administrator Notification - quote request for user %s");

//Pending Orders
define("NBILL_EM_NEW_PENDING", "Pending Order Acknowledgement");
define("NBILL_EM_NEW_PENDING_SUBJECT", "New PENDING Order: %s - %s (%s)");
define("NBILL_EM_NEW_PENDING_PAR_1", "This e-mail is to acknowledge that the following order has been provisionally placed on %s. This order will not be processed until we have received payment or the order is manually activated by us.");
define("NBILL_EM_NEW_PENDING_ADMIN", "Administrator Notification - pending order for user %s");

//Orders
define("NBILL_EM_NEW_ORDER", "Order Confirmation");
define("NBILL_EM_NEW_ORDER_SUBJECT", "New Order: %s - %s (%s)");
define("NBILL_EM_NEW_ORDER_PAR_1", "This e-mail is to acknowledge that the following order has been confirmed on %s.");
define("NBILL_EM_NEW_ORDER_ADMIN", "Administrator Notification - new order for user %s");

/****************************************************************
* You will probably not need to modify anything below this line *
****************************************************************/

//Admin
define("NBILL_EMAIL_MESSAGE_FROM", "From");
define("NBILL_EMAIL_MESSAGE_TO", "To");
define("NBILL_EMAIL_MESSAGE_CC", "CC");
define("NBILL_EMAIL_MESSAGE_BCC", "BCC");
define("NBILL_EMAIL_MESSAGE_SUBJECT", "Subject");
define("NBILL_EMAIL_INCLUDE_DOCUMENT", "Include the actual document?");
define("NBILL_EMAIL_MESSAGE_ATTACH", "Attach HTML document to message");
define("NBILL_EMAIL_MESSAGE_ATTACH_PDF", "Attach PDF document to message");
define("NBILL_EMAIL_MESSAGE_NO_ATTACH", "Do not attach document to message");
define("NBILL_EMAIL_MESSAGE_EMBED", "Embed the document in the message");
define("NBILL_EMAIL_MESSAGE_USE_TEMPLATE", "Use HTML Template?");
define("NBILL_EMAIL_MESSAGE_TIMESTAMP", "Message sent %s");
define("NBILL_QUOTE_SHOW_HISTORY", "Include Message History?");
define("NBILL_EMAIL_MESSAGE", "Message:");
define("NBILL_EMAIL_SEND", "Send");
define("NBILL_EMAIL_CANCEL", "Abort");

//Results
define("NBILL_EMAIL_QUOTE_DOC_SENT", "Message added to quote and e-mail sent successfully");
define("NBILL_EMAIL_QUOTE_DOC_NOT_SENT", "WARNING! The message was added to the quote, but the e-mail failed to send. Please send an e-mail manually.");
define("NBILL_EMAIL_NO_RECIPIENT", "No recipient was specified, so no e-mail was sent");
define("NBILL_EMAIL_NO_MESSAGE", "No message was entered, so no e-mail was sent");
define("NBILL_EMAIL_DOC_SENT", "Message sent successfully");
define("NBILL_EMAIL_DOC_NOT_SENT", "The message could not be sent.");
define("NBILL_EMAIL_DOC_NOT_SENT_UNKNOWN", "Please check your mail configuration settings, and make sure all 'from' and 'to' e-mail addresses are valid.");
define("NBILL_EMAIL_SEND_INTERRUPTED", "The connection to the server was interrupted whilst attempting to send the e-mail. Please check the email log to verify whether or not the e-mail was sent.");

//Version 2.1.0
define("NBILL_EM_EMBEDDED_QUOTE_PAR_1", "To view your quote online and accept, partially accept, or reject it, please visit: %s.");

//Version 2.1.1
define("NBILL_EMAIL_MESSAGE_FROM_NAME", "From Name");