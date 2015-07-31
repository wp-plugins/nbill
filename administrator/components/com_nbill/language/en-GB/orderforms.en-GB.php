<?php
/**
* Language file for the Order Forms feature
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Order Forms
define("NBILL_ORDER_FORMS_TITLE", "Order Forms");
define("NBILL_ORDER_FORMS_INTRO", "Order forms allow your users to order products through your website front end. You can have whatever fields you like on a form, and associate one or more of the fields with one or more products (you can cause a different product to be ordered depending on the value a user enters for a field). When using this feature for the first time, please refer to the %s");
define("NBILL_ORDER_FORMS_DOC_LINK", "online documentation.");
define("NBILL_FORM_PUBLISHED", "Published?");
define("NBILL_INSTR_FORM_PUBLISHED", "Whether or not to display this form on the website front end");
define("NBILL_FORM_TITLE", "Form Title");
define("NBILL_PUBLISHED", "Published");
define("NBILL_EDIT_ORDER_FORM", "Edit Form");
define("NBILL_NEW_ORDER_FORM", "New Form");
define("NBILL_FORM_DETAILS", "Form Details");
define("NBILL_FORM_TAB_DETAILS", "Details");
define("NBILL_FORM_TAB_FIELDS", "Editor");
define("NBILL_FORM_TAB_ORDER_VALUES", "Order");
define("NBILL_FORM_TAB_ADVANCED", "Advanced");
define("NBILL_INSTR_FORM_TITLE", "The name of the form.");
define("NBILL_FORM_LOGGED_IN_ONLY", "Logged-in Users Only?");
define("NBILL_INSTR_FORM_LOGGED_IN_ONLY", "Whether to restrict this form to be available only to registered users who are logged in.");
define("NBILL_FORM_PREREQ_PRODUCTS", "Prerequisite Products");
define("NBILL_INSTR_FORM_PREREQ_PRODUCTS", "If the user must already have a certain product before they can use this order form (eg. for an upgrade), specify the prerequisite product(s) here. If more than one product is selected, the user will be able to access this order form if they already have ANY one of the prerequisite products.");
define("NBILL_FORM_PREREQ_CATS", "Categories");
define("NBILL_FORM_PREREQ_CAT_PROD", "Products");
define("NBILL_FORM_PREREQ_SELECTED_PROD", "Selected Product(s)");
define("NBILL_FORM_EMAIL_CLIENT_PENDING", "E-Mail Pending Order to Client?");
define("NBILL_INSTR_FORM_EMAIL_CLIENT_PENDING", "Whether to send the client a confirmation e-mail when the form is submitted (before the order is created).");
define("NBILL_FORM_EMAIL_CLIENT", "E-Mail Confirmation to Client?");
define("NBILL_INSTR_FORM_EMAIL_CLIENT", "Whether to send the client a confirmation e-mail when the order is created.");
define("NBILL_FORM_EMAIL_ADMIN_PENDING", "E-Mail Pending Order to Admin?");
define("NBILL_INSTR_FORM_EMAIL_ADMIN_PENDING", "Whether to send the administrator a confirmation e-mail when a PENDING order is created.");
define("NBILL_FORM_EMAIL_ADMIN", "E-Mail Confirmation to Admin?");
define("NBILL_INSTR_FORM_EMAIL_ADMIN", "Whether to send the administrator a confirmation e-mail when an order is created.");
define("NBILL_FORM_VALIDATION_CODE", "Validation Code");
define("NBILL_INSTR_FORM_VALIDATION_CODE", "PHP code to evaluate when validating the submitted form. Assign a value to \$error_message to reject the form submission. Add the field name(s) of the fields that failed validation to the \$fields_in_error[] array to have those fields highlighted when the form is re-displayed. If that doesn't make sense to you, just leave this blank.");
define("NBILL_FORM_PROCESS_CODE", "Process Code");
define("NBILL_INSTR_FORM_PROCESS_CODE", "PHP code to evaluate when the form is posted back but not submitted (eg. if you have your own custom button on the form which needs to look something up or perform some other process). If that doesn't make sense to you, just leave this blank.");
define("NBILL_FORM_SUBMIT_CODE", "Submit Code");
define("NBILL_INSTR_FORM_SUBMIT_CODE", "PHP code to evaluate when the form is submitted and has passed validation successfully. If that doesn't make sense to you, just leave this blank.");
define("NBILL_FORM_PENDING_UNTIL_PAID", "Pending Until Paid?");
define("NBILL_INSTR_FORM_PENDING_UNTIL_PAID", "Whether to create a PENDING order (not a real one) when the form is submitted. Pending orders can be activated by an administrator, or by a payment gateway extension (when it detects that payment has been successful). Recommended to set this to `yes` so that you only get order records for items that have been paid for.");
define("NBILL_FORM_AUTO_CREATE_ORDERS", "Auto Create Order(s)?");
define("NBILL_INSTR_FORM_AUTO_CREATE_ORDERS", "Whether or not to automatically create order records when the form is submitted. If 'Pending Until Paid' is set to 'Yes', this will only happen after online payment has been completed - subject to payment gateway supporting this feature - or when the pending order is activated by an administrator.");
define("NBILL_FORM_AUTO_CREATE_USER", "Auto Create User?");
define("NBILL_INSTR_FORM_AUTO_CREATE_USER", "Whether or not to automatically attempt to create a user record for the new contact when any orders for the form are activated (if applicable). If 'Pending Until Paid' is set to 'Yes', this will only happen after online payment has been completed - subject to payment gateway supporting this feature - or when the pending order is activated by an administrator.");
define("NBILL_FORM_AUTO_CREATE_INVOICE", "Auto Create Invoice?");
define("NBILL_INSTR_FORM_AUTO_CREATE_INVOICE", "Whether or not to automatically generate an invoice for the order when any orders for the form are activated. If 'Pending Until Paid' is set to 'Yes', this will only happen after online payment has been completed - subject to payment gateway supporting this feature - or when the pending order is activated by an administrator.");
define("NBILL_FORM_AUTO_CREATE_INCOME", "Auto Create Income Item?");
define("NBILL_INSTR_FORM_AUTO_CREATE_INCOME", "Whether or not to automatically generate an income record for the payment. This will only happen after online payment has been completed, subject to payment gateway supporting this feature.");
define("NBILL_PAYMENT_GATEWAY", "Payment Gateway");
define("NBILL_INSTR_PAYMENT_GATEWAY", "Select the payment gateway to direct to after the form is submitted (NOTE: This can be overridden by a form field [eg. if you want to allow the user to select a payment gateway from a list] or using the value on the 'Order' tab).");
define("NBILL_ORDER_VALUES_INTRO", "There are certain values that need to be populated in order to create a new order. These values can either be defined explicitly by an administrator (by typing values directly in the boxes below), or they can be picked up from the values entered by the end user when they fill in the form. To pick up values from the form, you can specify which form field relates to which value on the 'Fields' tab (by editing the field in question, and selecting an order value from the dropdown list). Alternatively, you can use a token value here in the format ##field_id##, where field_id is the id number of the field that holds the value (see the 'id' column on the field list on the 'Fields' tab).");
define("NBILL_FORM_RELATING_TO", "Relating to");
define("NBILL_INSTR_FORM_RELATING_TO", "Either a static value for what the order relates to, or a token representing a field value (eg. ##23##).");
define("NBILL_FORM_SHIPPING_ID", "Shipping ID");
define("NBILL_INSTR_FORM_SHIPPING_ID", "Either a static value for the shipping service to use (the ID number as displayed on the %s, not the name), or a token representing a field value (eg. ##23##).");
define("NBILL_SHIPPING_LIST", "shipping list");
define("NBILL_FORM_TAX_EXEMPTION_CODE", "Tax Exemption Code");
define("NBILL_INSTR_FORM_TAX_EXEMPTION_CODE", "Either a static value to enter as the tax exemption code (unlikely to be useful!), or a token representing a field value (eg. ##23##).");
define("NBILL_FORM_PAYMENT_FREQUENCY", "Payment Frequency Code");
define("NBILL_INSTR_FORM_PAYMENT_FREQUENCY", "Either a static CODE value for the payment frequency ('AA'=One-off; 'BB'=Weekly; 'BX'=Four-weekly; 'CC'=Monthly; 'DD'=Quarterly; 'DX'=Semi-annually; 'EE'=Annually; 'FF'=Biannually; 'GG'=5-Yearly; 'HH'=10-Yearly; 'XX'=Not Applicable), or a token representing a field value (eg. ##23##).");
define("NBILL_FORM_CURRENCY", "Currency");
define("NBILL_INSTR_FORM_CURRENCY", "Either a static value for the currency (eg. GBP; USD) or a token representing a field value (eg. ##23##). If left blank, the component will try to use the vendor's default currency.");
define("NBILL_FORM_UNIQUE_INVOICE", "Unique Invoice?");
define("NBILL_INSTR_FORM_UNIQUE_INVOICE", "Either a static value to indicate whether the order should always have its own unique invoice (0 for 'No', 1 for 'Yes'), or a token representing a field value (eg. ##23##).");
define("NBILL_FORM_AUTO_RENEW", "Auto Renew?");
define("NBILL_INSTR_FORM_AUTO_RENEW", "Either a static value to indicate whether or not to automatically renew the order at the end of each payment cycle, or a token representing a field value (eg. ##23##). Enter 1 for 'yes', or 0 for 'no' (if this is set to 0, the order will be treated as a one-off payment [but using the price from the relevant payment frequency] and no recurring invoices will be generated, nor will any recurring payments be taken - the user will need to make the next payment manually in order to renew. Use the reminders feature if you want a reminder and payment link to be sent to the user at renewal time).");
define("NBILL_FORM_EXPIRY_DATE", "Expiry Date (%s)");
define("NBILL_INSTR_FORM_EXPIRY_DATE", "Either a static value to indicate the date on which the order will expire, or a token representing a field value (eg. ##23##). If left blank, the order will not expire. Use the format %s. PLEASE NOTE: Not all payment gateways support expiry dates. If your payment gateway does not support expiry dates, you will need to cancel the payments with the PSP yourself. Use the reminders feature if you want to receive e-mail notification of an expiry.");
define("NBILL_CREATE_MENU_ITEM", "Create Menu Item");
define("NBILL_CREATE_MENU_ITEM_HELP", "Select a menu (if you have more than one), and click the button to create a menu item which links to this order form. You can then re-position/edit/delete the menu item using the menu manager.");
define("NBILL_CREATE", "Create");
define("NBILL_CREATE_MENU_NAME", "Please enter the text you want to display for the menu item link.");
define("NBILL_MENU_ITEM_CREATED", "Menu Item Created Successfully.");
define("NBILL_MENU_ITEM_NOT_CREATED", "Sorry, the Menu Item could not be created.");
define("NBILL_MENU_ITEM_TO_EDIT", "to edit the Menu Item.");
define("NBILL_FORM_ORDER_GATEWAY", "Payment Gateway");
define("NBILL_INSTR_FORM_ORDER_GATEWAY", "Either a static value for the payment gateway to use, or a token representing a field value (eg. ##23##). The value here defaults to the value entered on the 'Details' tab, and if amended here, overrides that value.");
define("NBILL_FORM_SHOW_LOGIN_BOX", "Show Login Box?");
define("NBILL_INSTR_FORM_SHOW_LOGIN_BOX", "Whether or not to display a login box if the user is not logged in.");
define("NBILL_FORM_ADMIN_EMAIL_ADDR", "Admin E-Mail Address");
define("NBILL_INSTR_FORM_ADMIN_EMAIL_ADDR", "E-Mail address of administrator if different from vendor email address (leave blank to use vendor email)");
define("NBILL_FORM_FIELD_MERGED", "Columns merged");
define("NBILL_FORM_FIELD_NOT_MERGED", "Columns not merged");
define("NBILL_ERR_FLD_NAME_IS_RESERVED_WORD", "Sorry, '%s' is a reserved word that is used by " . NBILL_BRANDING_NAME . " - you cannot use it as a field name.");
define("NBILL_FORM_AUTO_HANDLE_SHIPPING", "Automatically Handle Shipping?");
define("NBILL_INSTR_FORM_AUTO_HANDLE_SHIPPING", "Whether or not to automatically select and apply the most appropriate shipping rate(s), or offer the relevant choices to the user if more than one rate could be applied (NOTE: offering the user a choice will only happen if there is a summary table field on the form).");
define("NBILL_FORM_USE_EMAIL_ADDRESS", "Use E-Mail Address as Username?");
define("NBILL_INSTR_FORM_USE_EMAIL_ADDRESS", "Whether or not to use the e-mail address as the username for new users created by this form (thus not requiring the user to select a username). The username will be picked up from whichever field is mapped to the contact e-mail address, UNLESS another field is mapped to username (in which case, this setting will have no effect). WARNING! This might require a hack to the CMS to allow hyphens in usernames, and to increase the username field length. See " . NBILL_BRANDING_NAME . " documentation for more information.");

//Order tab
define("NBILL_FORM_VOUCHER_CODE", "Discount Voucher Code");
define("NBILL_INSTR_FORM_VOUCHER_CODE", "Either a static value for the discount voucher to apply, or a token representing a field value (eg. ##23##).");

//Advanced tab
define("NBILL_ORDER_CREATION_CODE", "Order Creation Code");
define("NBILL_INSTR_ORDER_CREATION_CODE", "PHP code to evaluate when an order is created or processed for this order form (if `Pending until paid` is set to `yes`, this will only happen after payment has been received, or the pending order is activated by an administrator). This code will be called once for each order that is created (one order will be created for each different product) - use the \$product_id variable to identify which product an order record is being created for, and \$client_id to identify the client record. The order ID is also available in \$order_id. You should not rely on any other data being available (eg. if you need the client data, get it from the database, not the \$_POST values). If supported by the payment gateway, the code will also be called on each recurring payment (if the call is in relation to a recurring payment, the variable \$recurring will be set to true - so you can conditionally skip code execution for recurring payments if required). If that doesn't make sense to you, just leave this blank.");
define("NBILL_ORDER_FORM_TITLE_REQUIRED", "Please enter a title for this form.");
define("NBILL_FORM_DISQUAL_PRODUCTS", "Disqualifying Products");
define("NBILL_FORM_DISQUAL_CATS", "Categories");
define("NBILL_FORM_DISQUAL_CAT_PROD", "Products");
define("NBILL_FORM_DISQUAL_SELECTED_PROD", "Selected Product(s)");
define("NBILL_INSTR_FORM_DISQUAL_PRODUCTS", "If the user must NOT already have a certain product before they can use this order form (eg. if they already have it and are only allowed one, such as for user subscriptions), specify the disqualifying product(s) here. If more than one product is selected, the user will NOT be able to access this order form if they already have ANY one of the disqualifying products.");
define("NBILL_JAVASCRIPT_FUNCTIONS", "Javascript Functions");
define("NBILL_INSTR_JAVASCRIPT_FUNCTIONS", "Enter any Javascript that you want to be inserted into the &lt;head&gt; section. Please do not include the &lt;script&gt; tags - just the actual function definitions.");
define("NBILL_UPLOAD_PATH", "Upload Path");
define("NBILL_INSTR_UPLOAD_PATH", "Enter the full path name where you want to store files that are uploaded by users (folder must be writable by the user that PHP runs under). After you have entered a valid, writable path here, you must click on either 'Apply' or 'Save' before the file upload field type will become available for use on this form.");
define("NBILL_UPLOAD_MAX_SIZE", "Upload Max File Size");
define("NBILL_INSTR_UPLOAD_MAX_SIZE", "Enter the maximum file size (in Kilobytes) that can be uploaded.");
define("NBILL_UPLOAD_ALLOWED_TYPES", "Allowed File Types");
define("NBILL_INSTR_UPLOAD_ALLOWED_TYPES", "Enter a pipe (|) delimited list of field types that are allowed to be uploaded (eg. .jpg|.bmp|.gif). Leave blank to allow any file type to be uploaded.");
define("NBILL_ATTACH_TO_EMAIL", "Attach to Admin E-Mail?");
define("NBILL_INSTR_ATTACH_TO_EMAIL", "Whether or not to attach uploaded files to the confirmation e-mail that gets sent to the administrator (if applicable).");

//Version 1.2.0
define("NBILL_ORDER_FORM_LINK", "You can link to this form from your website content using the following URL: %s");
define("NBILL_ORDER_FORM_LINK_PREPOP", "Note: You can also pre-populate fields on your form by adding URL parameters. For example, if you have a field on your form called 'message', you could add &quot;&amp;message=hello%20world&quot; (without the quotes) to the end of the above URL to pre-populate that field with the value 'hello world'.");
define("NBILL_ORDER_FORM_THANK_YOU", "Thank You Message");
define("NBILL_INSTR_ORDER_FORM_THANK_YOU", "If the user is not redirected elsewhere by the payment gateway (or by your own redirect setting, above), this is the message that will be displayed on successful submission of the order form. The transaction data placeholders mentioned above (in the description of the 'order complete redirect' setting) can also be used in the HTML code specified here (for example to include a tracking pixel for an affiliate system).");
define("NBILL_FORM_DEFAULT_THANK_YOU", "Thank you for your order.");
define("NBILL_ORDER_FORM_DUPLICATE_PRODUCTS", "WARNING! You have assigned a product to a field TWICE on the order form(s) highlighted below. This will result in the product being ordered twice. You should only assign a product on EITHER the field properties pane OR the options popup, not both.");

//Version 1.2.1
define("NBILL_ORDER_FORM_FIELD_RESERVED_WORD", "Sorry, `%s` is a reserved word with a special meaning. Please select a different name for this field.");
define("NBILL_ORDER_FORM_THANK_YOU_REDIRECT", "Order Complete Redirect");
define("NBILL_INSTR_ORDER_FORM_THANK_YOU_REDIRECT", "If you want to redirect the user to another page when an order is submitted instead of displaying the thank you message defined below, please enter a URL here. NOTE: This will have no effect if the payment gateway performs its own redirect. You can use the following placeholders to represent transaction data which can be passed in the URL (for use when integrating with affiliate tracking programs or to provide a custom payment confirmation screen): ##TX_ID## (transaction ID); ##AMOUNT## (amount of payment); ##CURRENCY## (currency used); ##ORDER_ID## (order record ID number, if known); ##ORDER_NO## (order number, if known). A transaction ID will always exist, but an order record might not.");
define("NBILL_ORDER_FORM_EXPIRE_AFTER", "Number of Billing Cycles");
define("NBILL_INSTR_ORDER_FORM_EXPIRE_AFTER", "If you want to automatically calculate the expiry date for the order based on a certain number of payment cycles, enter the number of payments here, or leave blank if you don't want to automatically calculate an expiry date. For example, to bill monthly for 6 months then stop, set the payment frequency to monthly, and enter 6 here. To pick up the number of billing cycles from a field, enter the field ID surrounded by double-hashes, eg. ##23##");

//Version 1.2.5 - Note to translators
//Line 203 of original English language file amended (there is no longer a quantity setting on the order tab, so if the value is left blank it now just defaults to 1)

//Version 2.0.0
define("NBILL_FORM_EDITOR_INTRO", "Drag and drop to position fields, and/or use the up and down arrows at the top right. Hold down Ctrl or Shift to select more than one field or to de-select fields. Click on a field to edit its properties (on the right hand pane). When viewed in your website front-end, the form might be affected by styling rules in your template, so it might look a little different than it does here (you might need to make allowances for your website styling rules - eg. by leaving extra space). It is very important to check how your form looks in a number of different browsers!");
define("NBILL_FORM_ORDERING", "Ordering");
define("NBILL_FORM_DEFAULT_TITLE", "Form %s");
define("NBILL_FORM_SAVE_FAILED", "Sorry, a problem occurred whilst saving the form - it is not known whether the form was saved successfully or not. This could be due to a transient communication problem between your browser and the web server. Please try again.");
define("NBILL_ORDER_FORM_LINK_PREPOP_ORDER_SUFFIX", " This could be useful for example where there are several products that can be ordered on the form, and you want to pre-select the product to order depending on which article the link appears in.");
define("NBILL_ORDER_FORM_PRE_CALCULATE", "Pre-Calculate Code");
define("NBILL_INSTR_ORDER_FORM_PRE_CALCULATE", "PHP code to evaluate immediately before the order totals are calculated (on any summary table fields as well as after the form is submitted). You can manipulate the \$orders array to influence the order total calculation and thereby control how much is charged. If that doesn't make sense to you, just leave this blank.");
define("NBILL_ORDER_FORM_POST_CALCULATE", "Post-Calculate Code");
define("NBILL_INSTR_ORDER_FORM_POST_CALCULATE", "PHP code to evaluate immediately after the order totals are calculated (on any summary table fields as well as after the form is submitted). You can adjust the values held on the \$standard_totals object to change how much is charged (use with caution, as the calculated amounts will still be used for the resulting invoice(s) - in most cases it is better to use the pre calculate event). If that doesn't make sense to you, just leave this blank.");

//Verison 2.0.2
define("NBILL_FORM_LEGACY_RENDERER", "Use Legacy Renderer");
define("NBILL_INSTR_FORM_LEGACY_RENDERER", "If you set this to `yes`, the fields will be rendered in a table instead of being absolutely positioned. This means the fields might not be positioned exactly where you put them in the editor, but it will look a lot closer to the way it would have appeared in previous versions of " . NBILL_BRANDING_NAME . ". Where forms have been migrated from a previous version, they might need tweaking before they display correctly. By setting this option to `yes`, they are unlikely to need any tweaking. Other than that, you might want to try setting this to `yes` if you are having trouble with the display of your form, but generally it is best left at `no`.");
define("NBILL_FORM_LEGACY_TABLE_BORDER", "Legacy Table Border?");
define("NBILL_INSTR_FORM_LEGACY_TABLE_BORDER", "In previous versions of " . NBILL_BRANDING_NAME . ", you could specify that the table containing your fields should have a border. This generally looks rubbish, is not semantic, and was probably a bad idea, but the option is included here for the benefit of those who are migrating from a previous version and wish to keep it. This option will only take effect if `Use Legacy Renderer` is set to `yes`.");
define("NBILL_FORM_LEGACY_RENDERER_WARNING", "WARNING! This form uses the legacy renderer, so your fields might not appear exactly as you define them here, but should look very similar to how they did in previous versions of " . NBILL_BRANDING_NAME . ". You can turn the legacy renderer on or off on the 'Details' tab, below.");

//Version 2.0.9
define("NBILL_ORDER_FORM_UNMAPPED", "WARNING! You have specified that the form(s) highlighted below should automatically create order records, but none of the fields on the form are mapped to any client values. You need to either: 1) Make the form available to logged-in users only (so no mapping is required); 2) Change the form to NOT automatically create order records (not usually recommended unless you have some custom code to make the form do something else) or 3) Map one or more fields to a client value (on the processing tab of the field properties pane).");

//Version 2.1.1
define("NBILL_ORDER_FORM_UNAVAILABLE", "Form Unavailable Message");
define("NBILL_INSTR_ORDER_FORM_UNAVAILABLE", "Message to show if this form is not available (eg. if there are prerequisite products which the client does not have)");
define("NBILL_FORM_POST_PROCESS_CODE", "Post Process Code");
define("NBILL_INSTR_FORM_POST_PROCESS_CODE", "PHP code to evaluate after ALL other processing for this form is complete (ie. after the invoice has been generated and (where an online payment was made) marked as paid, if applicable - if `Pending until paid` is set to `yes`, this will only happen after payment has been received, or the pending order is activated by an administrator). This code will only be executed once per order form submission (unlike the order creation code, which is evaluated once for each product ordered and also on renewal). Available variables include the following (if applicable): \$client_id, \$order_ids[], \$document_ids[] (array of invoice IDs), \$transaction_id (income ID) - please load any other data you need from the database. If that doesn't make sense to you, just leave this blank.");

//Version 2.2.0
define("NBILL_FORM_ALWAYS_SHOW", "Always show on form list?");
define("NBILL_INSTR_FORM_ALWAYS_SHOW", "Whether or not to show this form even if it is not available to the user (eg. because they are not logged in, or do not have a necessary prerequisite product). If this is set to 'yes', the form will always show up on the list of forms (as long as it is published), but if not available to the user, the link will be greyed out, and the 'form unavailable message' as defined above will be shown if the user hovers their mouse over the link.");
define("NBILL_FORM_UPLOAD_PATH_WARNING", "WARNING! It is recommended to ensure ALL file types are uploaded to an area of your account that is NOT publicly accessible (but still writable by the user PHP is running under).");
define("NBILL_FORM_UPLOAD_TYPE_WARNING", "WARNING! If you allow executable files to be uploaded (eg. .php, .pl), this could be a serious security risk. You should limit the file types to just those that you absolutely need to allow.");

//Version 2.4.0
define("NBILL_ORDER_FORM_OFFLINE_PAYMENT_REDIRECT", "Offline Payment Redirect");
define("NBILL_INSTR_ORDER_FORM_OFFLINE_PAYMENT_REDIRECT", "If an offline payment method is selected, you can optionally redirect the user to another page when the form is processed (eg. an article telling the customer how to pay). This will only take effect if 'pending until paid' is set to 'yes' (otherwise, the order will be completed immediately and the order complete redirect or thank you message will be used instead).");

//Version 3.0.0
define("NBILL_LARGE_SCREEN_WITH_POINTER_REQUIRED", "Sorry, the form editor has a large drag and drop interface and can only be used on screens with at least 900 pixels width, and a pointing device with hover capabilities (such as a mouse). Please use a desktop or laptop computer (or resize your browser) to define the fields of your form (you can still access the other tabs, above, on a mobile device though).");
define("NBILL_FORM_GUESTS_ONLY", "Guests Only?");
define("NBILL_INSTR_FORM_GUESTS_ONLY", "Whether to restrict this form to be available only to users who are NOT logged in.");
define("NBILL_FORM_VIEW_IN_FE", "View form in front end (opens in a new window)");