<?php
/**
* Language file for the website front end features
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Front-End
define("NBILL_MAIN_MENU", "Main");
define("NBILL_ABOUT_TAX_EXEMPTION_CODE", "If your company is based within the EU, please enter your VAT number.");
define("NBILL_ORDER_NUMBER", "Order Number(s)");
define("NBILL_ORDER_DATE", "Date");
define("NBILL_PRICE", "Order Value");
@define("NBILL_PAY_FREQUENCY", "Payment Frequency");
define("NBILL_VIEW_INVOICES", "View Invoices");
define("NBILL_VIEW_INVOICES_ALT", "View all invoices that have been generated for this order");
define("NBILL_NEW_ORDER_INTRO", "Please select what you wish to order from the list");
define("NBILL_OR_NEW_QUOTE_INTRO", "...or select to request a quote");
define("NBILL_NEW_QUOTE_INTRO", "Please select the type of quote you want to request from the list");
define("NBILL_PLACE_ORDER", "Click here to place an order");
define("NBILL_CLIENT_NEW_ORDER", "Add a New Order");
define("NBILL_CLIENT_NEW_QUOTE", "Request a New Quote");
define("NBILL_ERR_FORM_NOT_DEFINED", "You are not able to access this form. The form might have been deleted, there may be pre-requisites for ordering this product, or it might only be available to certain users. Please make sure you are logged in, and try again. If you feel you should be able to access this form and the problem persists, contact a system administrator.");
define("NBILL_ERR_MANDATORY_FIELD", "Please supply a value for all mandatory fields (mandatory fields are marked with an asterisk *)");
define("NBILL_EMAILS_DONT_MATCH", "The email addresses you entered do not match! Please check carefully and try again.");
define("NBILL_EMAIL_NOT_VALID", "The email address you entered does not appear to be valid. Please try again.");
define("NBILL_DETAILS_SAVED", "Details Saved.");
define("NBILL_ERR_USER_EXISTS", "There is already a registered user with that user name and/or e-mail address. Please select a unique user name, or if you are already a registered user, please log in before placing an order.");
define("NBILL_ERROR_SAVING_ORDER", "There was a problem while attempting to save an order to the database. The order will have to be added manually by an Administrator");
define("NBILL_ERR_COULD_NOT_SAVE_PENDING_ORDER", "An error has occurred, and your order details could not be saved. We apologise for the inconvenience - please contact us.");
define("NBILL_ERR_PENDING_ORDER_NOT_FOUND", "An attempt was made to load a pending order (pending order id: %s), but the pending order record could not be loaded. All processing for this order has been aborted - a user record, client record, order, and invoice may need to be manually created by an Administrator.");
define("NBILL_ERR_NO_PRODUCTS_ORDERED", "Warning - an order form has been processed, but no products were found - therefore, no order has been created for it.");
define("NBILL_ERR_NO_INVOICE_GENERATED", "Warning - this order form is flagged to automatically create an invoice, but an invoice could not be generated. An invoice may need to be created manually by an Administrator.");
define("NBILL_EMAIL_NEW_PENDING_ORDER", "New PENDING Order - %s");
define("NBILL_EMAIL_PENDING_ORDER_INTRO", "A new order has been placed on %s, and is awaiting payment confirmation. When payment confirmation is received (or when an Administrator goes into " . NBILL_BRANDING_NAME . " and activates the pending order), an order record will be created for it.");
define("NBILL_EMAIL_NEW_ORDER", "New Order - %s");
define("NBILL_EMAIL_ORDER_INTRO", "This e-mail is to confirm that the following order has been placed on %s");
define("NBILL_DOWNLOAD", "Download");
define("NBILL_ERR_DOWNLOAD_FAILED", "Sorry, the file could not be downloaded. This might be due to your session timing out, in which case please login and try again.");
define("NBILL_ERR_DOWNLOAD_EXPIRED", "Sorry, the file is no longer available for download. This product can only be downloaded for up to %s days after the order was placed.");
define("NBILL_ERR_DOWNLOAD_NOT_FOUND", "Sorry, the file could not be found! Please contact an administrator.");
define("NBILL_CANCEL_ORDER", "Cancel Order");
define("NBILL_CANCEL_ORDER_INTRO", "To cancel this order, please let us know the reason for cancellation and confirm that you wish to cancel using the form below. If you are currently making regularly scheduled payments to us, we will cancel your future payments if it is possible for us to do so. If we do not have authority to cancel your payments (eg. if you pay by bank standing order), please make your own arrangements to cancel the payments.");
define("NBILL_CONFIRM_CANCELLATION", "I confirm that I wish to cancel the above order.");
define("NBILL_PLEASE_CONFIRM_CANCELLATION", "You did not check the box to confirm that you want to cancel. If you want to cancel this order, please check the box before clicking on the 'Cancel Order' button.");
define("NBILL_ORDER_NOT_FOUND", "The specified order could not be found.");
define("NBILL_ORDER_CANCELLED_SUCCESS", "The Specified Order Has Been Cancelled.");
define("NBILL_EMAIL_ORDER_CANCELLED", "Order Cancelled on %s");
define("NBILL_EMAIL_ORDER_CANCELLED_INTRO", "The following order on %s has been cancelled by the client. Please ensure any scheduled recurring payments for this order are also cancelled immediately. No further invoices will be produced for this order.");
define("NBILL_CONFIRM_PENDING_DELETE", "The selected order is marked as Pending. This means it has not yet been processed. If you cancel this order, it will be permanently deleted. If you are sure you want to permanently delete this pending order, please click on the 'Cancel Order' button below. Otherwise, click on the link to return to your order list without deleting anything.");
define("NBILL_RETURN_TO_ORDERS", "Return to Order List");
define("NBILL_INVOICE_STATUS", "Status");
define("NBILL_INVOICE_PART_PAID", "Part Paid");
define("NBILL_INVOICE_UNPAID", "Unpaid");
define("NBILL_INVOICE_REFUNDED", "Refunded");
define("NBILL_INVOICE_PART_REFUNDED", "Part Refunded");
define("NBILL_UPGRADE_MEMBERSHIP", "Upgrade Membership");
define("NBILL_MY_PROFILE_DESC", "Update your personal details");
define("NBILL_MY_QUOTES_DESC", "A list of the quotes you have requested from us.");
define("NBILL_MY_ORDERS_DESC", "A list of the orders you have placed with us.");
define("NBILL_MY_INVOICES_DESC", "A list of your invoices.");
define("NBILL_UPGRADE_MEMBERSHIP_DESC", "Increase your membership level to access more of this site's features");
define("NBILL_EXPIRY_DATE_FE", "Expiry Date");
define("NBILL_EMAIL_CONFIRM", "Confirm");
define("NBILL_WARN_ORDER_NOT_PROCESSED", "Warning - this order form has been processed, but nothing was actually ordered. Therefore there will be no order record available in " . NBILL_BRANDING_NAME . " for this form submission.");
define("NBILL_PAY_INVOICE", "Pay This Invoice");
define("NBILL_CURRENT_USER_GROUP", "Your current membership level is '%s'.");
define("NBILL_CANNOT_PAY_INVOICE_ONLINE", "Sorry, you cannot pay this invoice online at the moment."); //This would probably only be used if someone tries to change the invoice id to pay someone else's invoice
define("NBILL_POST_ERROR", "Sorry, an error occurred whilst posting the form. Please contact us for assistance.");
define("NBILL_WARN_CLIENT_EMAIL_NOT_SENT", "WARNING! The client has NOT been sent an e-mail confirming this order.");
define("NBILL_FORM_TIMEOUT", "Sorry, your session has timed-out. Please login again.");
define("NBILL_NO_FORMS", "There are no forms available to you.");
define("NBILL_PDF_INVOICE", "Display PDF Invoice");
define("NBILL_PDF_QUOTE", "Display PDF Quote");
define("NBILL_HTML_QUOTE", "Display HTML Quote");
define("NBILL_QUOTE_VIEW_DETAILS", "View Quote Details");
define("NBILL_PENDING_ORDER_PAY_NOW", "Pay Now");
define("NBILL_PENDING_ORDER_NOT_FOUND", "Sorry, either the pending order record could not be found, or there was no payment gateway set up for the order form at the time the order was placed. It will not be possible to pay for this order online.");
@define("NBILL_PENDING", "Pending");
define("NBILL_FE_PRODUCT", "Product");
define("NBILL_FE_ORDER_STATUS", "Status");
define("NBILL_FE_NEW_ORDER", "New Order");
define("NBILL_FE_NEW_QUOTE", "Request New Quote");
define("NBILL_FE_QUOTE_NUMBER", "Quote No.");
define("NBILL_FE_QUOTE_DATE", "Date");
define("NBILL_FE_QUOTE_FIRST_ITEM", "First Item on Quote");
define("NBILL_QUOTE_NEW_NO_DESC", "New Quote - Under Review");
define("NBILL_QUOTE_AWAITING_REPLY", "Awaiting further information - %s to reply");
define("NBILL_FE_QUOTE_TOTAL_NET", "Net");
define("NBILL_FE_QUOTE_TOTAL_GROSS", "Gross");
define("NBILL_FE_QUOTE_STATUS", "Status");
define("NBILL_FE_INVOICE_NUMBER", "Invoice No.");
define("NBILL_FE_INVOICE_DATE", "Date");
define("NBILL_FE_FIRST_ITEM", "First Item on Invoice");
define("NBILL_FE_TOTAL_NET", "Net");
define("NBILL_FE_TOTAL_GROSS", "Gross");
define("NBILL_FE_TOTAL_OUTSTANDING", "Outstanding");
define("NBILL_FE_INVOICE_PAID", "Paid");
define("NBILL_CHECKED", "Selected");
define("NBILL_UNCHECKED", "Not Selected");
define("NBILL_ORDER_SUMMARY_NET_TOTAL", "Net Total");
define("NBILL_ORDER_SUMMARY_TAX_TOTAL", "Tax");
define("NBILL_ORDER_SUMMARY_SHIPPING_FEES", "Shipping Fees");
define("NBILL_ORDER_SUMMARY_SHIPPING_TOTAL", "Shipping Total");
define("NBILL_ORDER_SUMMARY_SHIPPING_TAX", "Shipping Tax");
define("NBILL_ORDER_SUMMARY_AMOUNT_TO_PAY", "Amount to pay");
define("NBILL_ORDER_SUMMARY_AMOUNT_TO_PAY_TODAY", "Amount to pay today");
define("NBILL_ORDER_SUMMARY_AMOUNT_TO_PAY_REGULAR", "Amount to pay %s");
define("NBILL_ORDER_SUMMARY_AMOUNT_TO_PAY_REGULAR_INSTALLMENTS", "Amount to pay: %s %s payments of");
define("NBILL_ORDER_SUMMARY_AMOUNT_TO_PAY_REGULAR_THEREAFTER", "Followed by %s%s payments of");
define("NBILL_ORDER_SUMMARY_PAYMENT_PLAN", "Payment Plan: %s");
define("NBILL_SHIPPING_LIST_FOR", "Shipping for %s");
define("NBILL_SHIPPING_LIST", "Select Shipping");
define("NBILL_ERR_SELECT_SHIPPING", "Please select a shipping service from the list");
define("NBILL_PASSWORD_HELP", "Please specify a password which you will use to login to this site (to enable you to access your quotes, orders, and invoices online).");
define("NBILL_PAYMENT_DETAILS", "Payment Details:");
define("NBILL_EMAIL_ORDER_PRODUCTS", "Product(s) ordered:");
define("NBILL_EMAIL_TOTAL_ORDER_VALUE", "Total Order Value: %s");
define("NBILL_DOMAIN_AVAILABLE", "Domain name %s is currently available.");
define("NBILL_DOMAIN_UNAVAILABLE", "Domain name %s is already registered.");
define("NBILL_DOMAIN_RETURNED_NOTHING", "Sorry, we are currently unable to determine whether or not the selected domain is available. Please try again later.");
define("NBILL_DOMAIN_NOT_FOUND", "Sorry, the domain checking component could not be found. We cannot determine whether or not the selected domain is available.");
define("NBILL_DOMAIN_TLD_NOT_SUPPORTED", "Sorry, we are currently unable to check the availability of domains that end with '.%s'.");
define("NBILL_NOT_LOGGED_IN", "You are not logged in.");
define("NBILL_LOGIN_INTRO", "The feature you have requested requires that you be logged in. Please enter your username and password below to login - you will then be redirected to the page you requested.");
define("NBILL_ORDER_RENEW_ORDER_NOT_FOUND", "Sorry, no qualifying order was found - there is nothing to renew. If you think this is wrong, please contact us.");
define("NBILL_ORDER_RENEW_TITLE", "Renew Order");
define("NBILL_ORDER_RENEW_INTRO", "To renew this order, please check that the details below are correct and click on 'Submit'.");
define("NBILL_ORDER_PRODUCT", "Product");
define("NBILL_ORDER_RENEW_WARNING", "Please do not submit this form if you already have a recurring payment schedule set up for this order.");
define("NBILL_FILE_TOO_BIG", "You tried to upload a file that is too large. The maximum size allowed is %s KB");
define("NBILL_FILE_TYPE_NOT_ALLOWED", "You tried to upload a file of the wrong type. Only the following file types are allowed: %s");
define("NBILL_FILE_UPLOAD_FAILED", "Sorry, a file you tried to upload could not be saved. Please try with a different file name.");
define("NBILL_FE_SECURITY_IMAGE_CHANGE", "[Change letter code]");
define("NBILL_ERR_SECURITY_IMAGE_WRONG", "Sorry, the security image code you entered was incorrect. Please try again.");

/* Version 1.1.4 */
//////define("NBILL_FORM_NEXT", "Next >>");
//////define("NBILL_FORM_PREV", "<< Previous");
define("NBILL_FORM_SUBMIT", "Submit");
define("NBILL_RENEW_SUBMIT", "Submit");

//Version 1.2.0
/* NBILL_ORDER_NUMBER on line 26 changed (for plurality) */
define("NBILL_CANNOT_RENEW_PAY_FREQS_DIFFER", "Sorry, these orders cannot all be renewed together because they have different payment frequencies. You can only renew multiple orders simultaneously if the payment frequency is the same for each.");
define("NBILL_CANNOT_RENEW_AUTO_RENEW_DIFFERS", "Sorry, these orders cannot all be renewed together because at least one is set to auto-renew, and at least one is set not to auto-renew. You can only renew multiple orders simultaneously if they are all set to auto-renew, or all set not to auto-renew.");
define("NBILL_CANNOT_RENEW_CURRENCY_DIFFERS", "Sorry, these orders cannot all be renewed together because they have different currencies. You can only renew multiple orders simultaneously if the currency is the same for each.");
define("NBILL_RENEW", "Renew");
define("NBILL_DUE", "Due");
define("NBILL_EXPIRED", "Expired");
define("NBILL_SELECT_GATEWAY", "Choose Payment Method");
define("NBILL_INVOICE_PAY_NOW", "Pay Invoice(s)");
define("NBILL_INVOICE_AMOUNT", "Amount");
define("NBILL_INVOICE_TOTAL", "Total");
if (!defined("_LOST_PASSWORD")) {define("_LOST_PASSWORD", "Lost Password?");}
define("NBILL_TRACKING", "Tracking");
define("NBILL_TRACK_THIS_PARCEL", "Track this Shipment");

//Version 1.2.1
define("NBILL_FILE_UPLOAD_FAILED_REASON", "Sorry, a file you tried to upload could not be saved. The following reason was reported: ");
define("NBILL_ERR_UPLOAD_ERR_INI_SIZE", "The file was larger than file size limit imposed by the PHP configuration file (php.ini) on this server.");
define("NBILL_UPLOAD_ERR_FORM_SIZE", "PHP reports that the file was larger than file size limit imposed by the MAX_FILE_SIZE directive (Note: This error sometimes occurs even if the file is smaller than the limit. If this is happening, the site owner should remove the MAX_FILE_SIZE directive from the form).");
define("NBILL_UPLOAD_ERR_PARTIAL", "The upload was interrupted and only part of the file was received.");
define("NBILL_UPLOAD_ERR_NO_FILE", "The upload was interrupted, and none of the data in the file was received.");
define("NBILL_UPLOAD_ERR_NO_TMP_DIR", "PHP was unable to locate a temporary directory in which to save the file.");
define("NBILL_UPLOAD_ERR_CANT_WRITE", "PHP was unable to write to the temporary directory to save the file.");
define("NBILL_INVOICE_ALREADY_PAID", "Invoice %s has already been paid. You cannot pay it again!");
define("NBILL_RETURN_TO_MY_INVOICES", " to return to %s");
define("NBILL_CANNOT_RENEW_CANCELLED", "Sorry, you cannot renew an order that has been cancelled. Please place a new order instead.");

//Version 1.2.3
define("NBILL_PENDING_ID", "Pending Order ID");
define("NBILL_EMAIL_IN_USE", "This e-mail address is already registered. If you have forgotten your password, please click on the lost password link to get a new one emailed to you").
define("NBILL_INVALID_USERNAME", "Please enter a valid username.  No spaces, more than 2 characters and containing only the characters 0-9, a-z, or A-Z");
define("NBILL_USERNAME_IN_USE", "This username is already registered. Please choose another username, or if you are already registered, login first. If you have forgotten your password, please click on the lost password link to get a new one emailed to you.");

//Version 1.2.7
define("NBILL_ORDER_RENEWED_SUCCESS", "Thank you, your order was renewed successfully."); //Only shown for free product renewals (eg. 100% discount or free non-auto-renewing subscription)
define("NBILL_CANNOT_RENEW_ADVANCE", "Sorry, you cannot renew more than %s nbf_common::nb_time(s) in advance.");

//Version 2.0.1
define("NBILL_VALUES_DONT_MATCH", "The values you entered in the highlighted fields below do not match! Please try again.");
define("NBILL_LOGIN_FAILED", "Login Failed. Please check your username and password and try again.");
define("NBILL_FORM_SHIPPING_INTRO", "<p>Please choose a shipping service.</p>");
define("NBILL_RECALCULATE", "Recalculate");
define("NBILL_USER_SELECT_CLIENT", "Please select a client record:");
define("NBILL_INVOICE_SUMMARY_TOTALS_TITLE", "Invoice Summary Totals");
define("NBILL_QUOTE_SUMMARY_TOTALS_TITLE", "Quote Summary Totals");
define("NBILL_ORDER_SUMMARY_TOTALS_TITLE", "Order Summary Totals");
define("NBILL_UPLOAD_ERR_FILE_EXISTS", "Unable to generate a unique filename to save the file.");
define("NBILL_UPLOAD_PATH_NOT_WRITABLE", "PHP was unable to write to the upload directory to save the file.");
define("NBILL_UPLOAD_COPY_FAILED", "Failed to copy file from temp directory to upload directory.");
define("NBILL_FILE_DELETE", "Delete File");
define("NBILL_PAY_INVOICE_TITLE", "Pay Invoice(s)");
define("NBILL_PAY_INVOICE_INTRO", "To pay the invoice(s) listed below, please check that the details are correct and click on 'Submit'.");
define("NBILL_INVOICE_INSTALLMENTS_ALREADY_RUNNING", "You already have a recurring payment schedule set up for invoice %s to repay it in installments. As such, you cannot pay the invoice manually, as that would result in overpayment. If your existing recurring payment schedule has failed or been cancelled, please contact us so that we can lift this restriction and allow you to pay the invoice.");
define("NBILL_INVOICE_CURRENCY_MISMATCH", "The selected invoices are not all for the same currency. You cannot pay invoices for different currencies in a single transaction - please pay each one separately.");
define("NBILL_QUOTE_REQUEST_NO_QUOTE_NO_EMAIL", "Sorry, there was a problem processing your quote request. The system was unable to create a new quote document, nor to send email confirmation. Please contact us.");
define("NBILL_QUOTE_REQUEST_QUOTE_NO_EMAIL", "Thank you, your quote request was registered on our system, but there was a problem sending confirmation by e-mail. If you entered the wrong e-mail address, please contact us so we can update our records.");
define("NBILL_QUOTE_REQUEST_EMAIL_NO_QUOTE", "The quote document could not be saved. Please create a new quote manually.");
define("NBILL_FE_QUOTE_INTRO", "Click on a quote number to accept or reject part or all of a quote, or to send us more information.");
define("NBILL_FE_QUOTE_TITLE", "Quote %s, dated %s. Quote Status: %s.");
define("NBILL_FE_QUOTE_SHOW_CORRE", "Show Previous Correspondence");
define("NBILL_FE_QUOTE_HIDE_CORRE", "Hide Previous Correspondence");
define("NBILL_FE_QUOTE_REPLY_INTRO", "Please submit the following form if you would like to reply or add any further information to this quote.");
define("NBILL_FE_QUOTE_EMAIL_DEFAULT_SUBJECT", "Re: Quote no. %s");
define("NBILL_FE_QUOTE_ITEM_MANDATORY", "(Mandatory)");
define("NBILL_QUOTE_ITEM_ACCEPTED_ACTION", "Accept / Reject");
define("NBILL_QUOTE_CLICK_TO_ACCEPT", "Click to accept this item");
define("NBILL_QUOTE_CLICK_TO_REJECT", "Click to reject this item");
define("NBILL_QUOTE_SUBMIT_WARNING", "Warning: By submitting this form, you are entering a legally binding contract to purchase any items marked as accepted.");
define("NBILL_QUOTE_ACCEPT_ALL_WARNING", "Warning: By submitting this form, you are entering a legally binding contract to purchase ALL the items listed.");
define("NBILL_QUOTE_REJECT_ALL", "Reject All");
define("NBILL_QUOTE_REJECT", "Reject");
define("NBILL_QUOTE_REJECT_WARNING", "Are you sure you want to REJECT this quote?");
define("NBILL_QUOTE_ACCEPT_ALL", "Accept All");
define("NBILL_QUOTE_ACCEPT_SELECTED", "Confirm Selection(s)");
define("NBILL_QUOTE_REJECTED_SUCCESSFULLY", "You have successfully rejected this quote. Thank you for letting us know.");
define("NBILL_QUOTE_PART_REJECTED_SUCCESSFULLY", "You have successfully rejected the outstanding items on this quote. Thank you for letting us know.");
define("NBILL_QUOTE_REJECTED_SUBJECT", "Quote Rejected on %s");
define("NBILL_QUOTE_REJECTED_MESSAGE", "This is just to inform you that quote number %s has been rejected by the client. Sorry it didn't work out!");
define("NBILL_QUOTE_ACCEPTED_SUCCESSFULLY", "Thank you - you have successfully accepted this quote.");
define("NBILL_QUOTE_ACCEPTED_INVOICE_GENERATED", " A new invoice has been generated for you.");
define("NBILL_QUOTE_NOTHING_NEW", "No new items were accepted. No action has been taken.");
define("NBILL_QUOTE_AWAITING_PAYMENT", "WARNING! You have accepted one or more quote items, but have not yet paid for all of them. The following accepted quote(s) cannot be enacted until payment has been confirmed: ");
define("NBILL_QUOTE_AWAITING_ACTION", "Quote %s: <a href=\"%s\">Pay Now</a> | <a href=\"%s\">Reject Remaining Items</a> | <a href=\"%s\">View Quote Details</a>");
define("NBILL_CANNOT_PAY_QUOTE_ONLINE", "Sorry, you cannot pay for this quote online at the moment."); //Never likely to happen, unless someone is trying to hack!
define("NBILL_PAY_QUOTE_TITLE", "Pay for Quote(s)");
define("NBILL_PAY_QUOTE_INTRO", "To pay for the accepted quote amounts listed below, please check that the details are correct and click on 'Submit'.");
define("NBILL_PAY_QUOTE_INTRO_ADDITIONAL_PAYMENT_REQD", "Please note, due to the different payment frequencies involved in this quote, an additional payment will need to be made after this one. You will be prompted to make the additional payment after this one is completed.");

//Version 2.1.0
define("NBILL_QUOTE_PAY_OFFLINE", "Please contact us to arrange payment by cash, cheque, or bank transfer. The quote will be held in a pending state until we have received your payment. Thank you.");
define("NBILL_QUOTE_NOT_FOUND", "Sorry, that quote could not be found.");
define("NBILL_CHOOSE_LANGUAGE", "Choose language");
define("NBILL_CHOOSE_LANGUAGE_HELP", "Whatever language you select here will be used for all of your billing information, including invoices and e-mails that are sent to you from our billing system. Your selection does not take effect until you click on `Submit` below.");
define("NBILL_QUOTE_ACCEPTED_AWAITING_PAYMENT_SUBJECT", "Quote Accepted - Awaiting Payment on %s");
define("NBILL_QUOTE_ACCEPTED_AWAITING_PAYMENT_BODY", "Quote %s has been accepted, or partially accepted by the client and is awaiting payment. Once payment is confirmed, the relevant order and invoice records will be created automatically. The following items have been accepted: \n\n%s");
define("NBILL_QUOTE_ACCEPTED_COMPLETED_SUBJECT", "Quote Accepted on %s");
define("NBILL_QUOTE_ACCEPTED_COMPLETED_BODY", "Quote %s has been accepted, or partially accepted by the client and the relevant order and invoice records have been created automatically. The following items have been accepted: \n\n%s");
define("NBILL_QUOTE_ITEM_ACCEPTED", "Accepted");
define("NBILL_QUOTE_ITEM_REJECTED", "Rejected");
define("NBILL_QUOTE_ITEM_AWAITING", "Awaiting Payment");

//Version 2.1.1
define("NBILL_QUOTE_NOT_YET_AVAILABLE", "Your quote is currently being prepared. We will notify you when it is available to view. Thank you for your patience.");
define("NBILL_QUOTE_NOT_YET_AVAILABLE_ON_HOLD", "Your quote is not currently available to view because it has been put 'on hold' while we await further information. Click on '" . NBILL_FE_QUOTE_SHOW_CORRE . "' below to see any messages we have left for you, and/or use the form below to contact us or add further information.");
define("NBILL_FE_RELATING_TO", "Relating To");
define("NBILL_CHANGE", "Change...");
define("NBILL_UPDATE", "Update");
define("NBILL_PAY_FREQ_CHANGED", "Payment frequency has been changed from %s to %s. The order has NOT yet been renewed - check the details below and submit to proceed with renewal.");

//Version 2.2.0
define("NBILL_QUOTE_SORRY_WITHDRAWN", "Sorry, this quote has been withdrawn.");

//Version 2.3.0
define("NBILL_FE_SUPPLIER", "Supplier");

//Version 2.4.0
define("NBILL_QUOTE_REJECT_OS_WARNING", "Are you sure you want to REJECT the remaining items on this quote?");
define("NBILL_FE_DOC_DISCOUNT_VOUCHER", "Promotional Code (if applicable)");
define("NBILL_FE_DOC_DISCOUNT_APPLY", "Apply");

//Version 2.5.0
define("NBILL_DEFAULT_INVOICE_OFFLINE_PAY_INST", "Please refer to the payment instructions on the invoice to arrange payment by bank transfer or through the mail. Thank you.");

//Version 2.5.2
define("NBILL_RECORD_PENDING_ORDER", "pending order");
define("NBILL_RECORD_ORDER_RENEWAL", "order renewal");
define("NBILL_RECORD_INVOICE", "invoice");
define("NBILL_RECORD_QUOTE", "quote");
define("NBILL_PENDING_PAYMENT_WARNING", "WARNING! A payment has already been authorised for this %1\$s and is expected to be complete on or before %2\$s. If you authorise another payment, you could end up paying twice! If you are sure the previous payment will not be completed, you can proceed anyway.");
define("NBILL_PROCEED_ANYWAY", "Proceed Anyway");
define("NBILL_INVOICE_DISCOUNTED_ZERO_PAYMENT", "Thank you, your invoice has been marked as paid.");
define("NBILL_INVOICE_DISCOUNTED_ZERO_PAYMENT_NOTES", "Invoice discounted - nothing to pay");

//Version 2.7.0
define("NBILL_ERR_DOWNLOAD_UNAVAVAILABLE_PLEASE_RENEW", "This download is no longer available as your order expired on %s.");
define("NBILL_HTML_INVOICE", "Display HTML Invoice");

//Version 3.0.0
define("NBILL_FE_DUE_DATE", "Due Date");
define("NBILL_GEO_IP_FAIL_FIELD", "Sorry your request cannot be processed because the country associated with your IP address (%1\$s) does not match your billing country (%2\$s). Please verify that your address details have been entered correctly.");
define("NBILL_GEO_IP_FAIL_ENTITY", "Sorry your request cannot be processed because the country associated with your IP address (%1\$s) does not match the billing country on your profile (%2\$s). Please verify that your address details have been entered correctly under 'My Profile'.");
define("NBILL_FE_ORDER_NO", "Order Number");
define("NBILL_FE_QUANTITY", "Quantity");
define("NBILL_ORDER_LAST_DUE_DATE", "Last Due Date");
define("NBILL_ORDER_NEXT_DUE_DATE", "Next Due Date");
define("NBILL_FE_FORM_FIELD_VALUES", "The following values were entered on the order form for this order.");
define("NBILL_FE_DOWNLOADS", "Downloads");

//Version 3.0.5
define("NBILL_FE_QUOTE_NO_CORRE", "There is no previous correspondence to display.");