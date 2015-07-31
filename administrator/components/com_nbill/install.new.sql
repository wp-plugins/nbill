##########
INSERT INTO `#__nbill_configuration` (`id`, `error_email`, `date_format`, `select_users_from_list`, `cron_auth_token`, `email_invoice_option`) VALUES ('1', '', 'd/m/Y', '1', '4r7jw5gth', 'EE');
##########
INSERT INTO `#__nbill_currency` (`id`, `code`, `description`, `symbol`) VALUES ('1', 'GBP', 'British Pounds (Sterling)', '&#163;');
##########
INSERT INTO `#__nbill_currency` (`id`, `code`, `description`, `symbol`) VALUES ('2', 'USD', 'US Dollars', '&#36;');
##########
INSERT INTO `#__nbill_currency` (`id`, `code`, `description`, `symbol`) VALUES ('3', 'EUR', 'Euros', '&#8364;');
##########
INSERT INTO `#__nbill_currency` (`id`, `code`, `description`, `symbol`) VALUES ('4', 'CAD', 'Canadian Dollars', '&#36;');
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('admin_via_fe', 0);
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('due_date', 0);
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('generate_early', 0);
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('quote_date', '5');
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('quote_first_item', '2');
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('quote_net', '4');
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('invoice_link', '2');
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('order_date', '4');
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('expiry_date', '0');
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('product', '2');
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('order_value', '4');
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('frequency', '3');
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('renew_link', '5');
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('document_date', '4');
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('due_date_on_list', '5');
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('first_item', '2');
##########
INSERT INTO `#__nbill_display_options` (`name`, `value`) VALUES ('net', '3');
##########
INSERT INTO `#__nbill_license` (`id`, `license_key`, `verifier`) VALUES (1, '', '1302091848p7s57c4288');
##########
INSERT INTO `#__nbill_menu` VALUES (1, -1, 1, 'NBILL_MNU_DASHBOARD', 'NBILL_MNU_HOME_DESC', '', '[NBILL_ADMIN]', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (2, -1, 2, 'NBILL_MNU_CONFIG', 'NBILL_MNU_CONFIG_DESC', '', '', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (3, 2, 0, 'NBILL_MNU_GLOBAL_CONFIG', 'NBILL_MNU_GLOBAL_CONFIG_DESC', '[NBILL_FE]/images/icons/config.gif', '[NBILL_ADMIN]&action=configuration', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (4, -1, 4, 'NBILL_MNU_BILLING', 'NBILL_MNU_BILLING_DESC', '', '', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (5, 2, 2, 'NBILL_MNU_VENDOR', 'NBILL_MNU_VENDOR_DESC', '[NBILL_FE]/images/icons/vendors.gif', '[NBILL_ADMIN]&action=vendors&task=view', 1, 1);
##########
INSERT INTO `#__nbill_menu` VALUES (6, 2, 1, 'NBILL_MNU_CURRENCIES', 'NBILL_MNU_CURRENCIES_DESC', '[NBILL_FE]/images/icons/currencies.gif', '[NBILL_ADMIN]&action=currency', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (7, 2, 3, 'NBILL_MNU_SALES_TAX', 'NBILL_MNU_SALES_TAX_DESC', '[NBILL_FE]/images/icons/tax.gif', '[NBILL_ADMIN]&action=vat', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (8, 2, 5, 'NBILL_MNU_SHIPPING', 'NBILL_MNU_SHIPPING_DESC', '[NBILL_FE]/images/icons/shipping.gif', '[NBILL_ADMIN]&action=shipping', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (9, 2, 4, 'NBILL_MNU_NOMINAL_LEDGER', 'NBILL_MNU_NOMINAL_LEDGER_DESC', '[NBILL_FE]/images/icons/ledger.gif', '[NBILL_ADMIN]&action=ledger', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (10, 4, 4, 'NBILL_MNU_CLIENTS', 'NBILL_MNU_CLIENTS_DESC', '[NBILL_FE]/images/icons/clients.gif', '[NBILL_ADMIN]&action=clients', 1, 1);
##########
INSERT INTO `#__nbill_menu` VALUES (11, 4, 6, 'NBILL_MNU_PRODUCT_CATS', 'NBILL_MNU_PRODUCT_CATS_DESC', '[NBILL_FE]/images/icons/categories.gif', '[NBILL_ADMIN]&action=categories', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (12, 4, 7, 'NBILL_MNU_PRODUCTS', 'NBILL_MNU_PRODUCTS_DESC', '[NBILL_FE]/images/icons/products.gif', '[NBILL_ADMIN]&action=products', 1, 1);
##########
INSERT INTO `#__nbill_menu` VALUES (13, 4, 8, 'NBILL_MNU_ORDERS', 'NBILL_MNU_ORDERS_DESC', '[NBILL_FE]/images/icons/orders.gif', '[NBILL_ADMIN]&action=orders', 1, 1);
##########
INSERT INTO `#__nbill_menu` VALUES (14, 4, 9, 'NBILL_MNU_INVOICES', 'NBILL_MNU_INVOICES_DESC', '[NBILL_FE]/images/icons/invoices.gif', '[NBILL_ADMIN]&action=invoices', 1, 1);
##########
INSERT INTO `#__nbill_menu` VALUES (15, -1, 5, 'NBILL_MNU_ACCOUNTING', 'NBILL_MNU_ACCOUNTING_DESC', '', '', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (16, 15, 1, 'NBILL_MNU_INCOME', 'NBILL_MNU_INCOME_DESC', '[NBILL_FE]/images/icons/income.gif', '[NBILL_ADMIN]&action=income', 1, 1);
##########
INSERT INTO `#__nbill_menu` VALUES (17, 15, 2, 'NBILL_MNU_EXPENDITURE', 'NBILL_MNU_EXPENDITURE_DESC', '[NBILL_FE]/images/icons/expenditure.gif', '[NBILL_ADMIN]&action=expenditure', 1, 1);
##########
INSERT INTO `#__nbill_menu` VALUES (18, -1, 6, 'NBILL_MNU_REPORTS', 'NBILL_MNU_REPORTS_DESC', '', '', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (19, 4, 10, 'NBILL_MNU_CREDIT_NOTES', 'NBILL_MNU_CREDIT_NOTES_DESC', '[NBILL_FE]/images/icons/credits.gif', '[NBILL_ADMIN]&action=credits&task=view', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (20, 15, 3, 'NBILL_MNU_AUDIT_LOG', 'NBILL_MNU_AUDIT_LOG_DESC', '[NBILL_FE]/images/icons/audit.gif', '[NBILL_ADMIN]&action=audit&task=view', 0, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (21, -1, 3, 'NBILL_MNU_FRONT_END', 'NBILL_MNU_FRONT_END_DESC', '', '', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (22, 21, 4, 'NBILL_MNU_ORDER_FORMS', 'NBILL_MNU_ORDER_FORMS_DESC', '[NBILL_FE]/images/icons/forms.gif', '[NBILL_ADMIN]&action=orderforms&task=view', 1, 1);
##########
INSERT INTO `#__nbill_menu` VALUES (23, 21, 5, 'NBILL_MNU_GATEWAYS', 'NBILL_MNU_GATEWAYS_DESC', '[NBILL_FE]/images/icons/payment.gif', '[NBILL_ADMIN]&action=gateway', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (24, 2, 10, 'NBILL_MNU_BACKUP_RESTORE', 'NBILL_MNU_BACKUP_RESTORE_DESC', '[NBILL_FE]/images/icons/backup.gif', '[NBILL_ADMIN]&action=backup', 1, 1);
##########
INSERT INTO `#__nbill_menu` VALUES (25, 21, 6, 'NBILL_MNU_PENDING_ORDERS', 'NBILL_MNU_PENDING_ORDERS_DESC', '[NBILL_FE]/images/icons/pending.gif', '[NBILL_ADMIN]&action=pending', 1, 1);
##########
INSERT INTO `#__nbill_menu` VALUES (26, 21, 1, 'NBILL_MNU_DISPLAY_OPTIONS', 'NBILL_MNU_DISPLAY_OPTIONS_DESC', '[NBILL_FE]/images/icons/display.gif', '[NBILL_ADMIN]&action=display', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (27, 2, 7, 'NBILL_MNU_DISCOUNTS', 'NBILL_MNU_DISCOUNTS_DESC', '[NBILL_FE]/images/icons/discounts.gif', '[NBILL_ADMIN]&action=discounts', 1, 1);
##########
INSERT INTO `#__nbill_menu` VALUES (28, 18, 3, 'NBILL_MNU_TAX_SUMMARY', 'NBILL_MNU_TAX_SUMMARY_DESC', '[NBILL_FE]/images/icons/summary.gif', '[NBILL_ADMIN]&action=taxsummary', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (29, -1, 7, 'NBILL_MNU_EXTENSIONS', 'NBILL_MNU_EXTENSIONS_DESC', '', '[NBILL_ADMIN]&action=extensions', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (30, 29, 1, 'NBILL_MNU_EXTENSIONS_INSTALL', 'NBILL_MNU_EXTENSIONS_INSTALL_DESC', '[NBILL_FE]/images/icons/extensions.gif', '[NBILL_ADMIN]&action=extensions', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (31, 4, 5, 'NBILL_MNU_SUPPLIERS', 'NBILL_MNU_SUPPLIERS_DESC', '[NBILL_FE]/images/icons/suppliers.gif', '[NBILL_ADMIN]&action=suppliers', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (32, 4, 1, 'NBILL_MNU_CONTACTS', 'NBILL_MNU_CONTACTS_HELP', '[NBILL_FE]/images/icons/contacts.gif', '[NBILL_ADMIN]&action=contacts', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (33, 2, 11, 'NBILL_MNU_IO', 'NBILL_MNU_IO_DESC', '[NBILL_FE]/images/icons/io.gif', '', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (34, 33, 1, 'NBILL_MNU_IO_CLIENTS', 'NBILL_MNU_IO_CLIENTS_DESC', '[NBILL_FE]/images/icons/clients.gif', '[NBILL_ADMIN]&action=io&task=clients', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (35, 21, 2, 'NBILL_MNU_PROFILE_FIELDS', 'NBILL_MNU_PROFILE_FIELDS_DESC', '[NBILL_FE]/images/icons/profile_fields.gif', '[NBILL_ADMIN]&action=profile_fields', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (36, 18, 1, 'NBILL_MNU_TRANSACTION_REPORT', 'NBILL_MNU_TRANSACTION_REPORT_DESC', '[NBILL_FE]/images/icons/transactions.gif', '[NBILL_ADMIN]&action=transactions', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (37, 18, 2, 'NBILL_MNU_LEDGER_REPORT', 'NBILL_MNU_LEDGER_REPORT_DESC', '[NBILL_FE]/images/icons/ledger_report.gif', '[NBILL_ADMIN]&action=ledger_report', 1, 1);
##########
INSERT INTO `#__nbill_menu` VALUES (38, 2, 9, 'NBILL_MNU_REMINDERS', 'NBILL_MNU_REMINDERS_DESC', '[NBILL_FE]/images/icons/reminders.gif', '[NBILL_ADMIN]&action=reminders', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (39, 15, 3, 'NBILL_MNU_TX_SEARCH', 'NBILL_MNU_TX_SEARCH_HELP', '[NBILL_FE]/images/icons/tx_search.gif', '[NBILL_ADMIN]&action=tx_search', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (40, 18, 4, 'NBILL_MNU_SNAPSHOT', 'NBILL_MNU_SNAPSHOT_HELP', '[NBILL_FE]/images/icons/snapshot.gif', '[NBILL_ADMIN]&action=snapshot', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (41, 18, 5, 'NBILL_MNU_ANOMALY', 'NBILL_MNU_ANOMALY_HELP', '[NBILL_FE]/images/icons/anomaly.gif', '[NBILL_ADMIN]&action=anomaly', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (42 , 15, 4, 'NBILL_MNU_RECONCILE', 'NBILL_MNU_RECONCILE_HELP', '[NBILL_FE]/images/icons/reconcile.gif', '[NBILL_ADMIN]&action=reconcile', 0, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (43, 21, 3, 'NBILL_MNU_QUOTE_REQUEST', 'NBILL_MNU_QUOTE_REQUEST_DESC', '[NBILL_FE]/images/icons/quote_request.gif', '[NBILL_ADMIN]&action=quote_request&task=view', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (44, 4, 2, 'NBILL_MNU_POTENTIAL_CLIENTS', 'NBILL_MNU_POTENTIAL_CLIENTS_DESC', '[NBILL_FE]/images/icons/potential_clients.gif', '[NBILL_ADMIN]&action=potential_clients', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (45, 4, 3, 'NBILL_MNU_QUOTES', 'NBILL_MNU_QUOTES_DESC', '[NBILL_FE]/images/icons/quotes.gif', '[NBILL_ADMIN]&action=quotes', 1, 1);
##########
INSERT INTO `#__nbill_menu` VALUES (46, 2, 8, 'NBILL_MNU_PAYMENT_PLANS', 'NBILL_MNU_PAYMENT_PLANS_DESC', '[NBILL_FE]/images/icons/payment_plans.gif', '[NBILL_ADMIN]&action=payment_plans', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (47, -1, 8, 'NBILL_MNU_HELP', 'NBILL_MNU_HELP_DESC', '', '', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (48, 47, 1, 'NBILL_MNU_HELP_ABOUT', 'NBILL_MNU_HELP_ABOUT_DESC', '[NBILL_FE]/images/icons/about.gif', '[NBILL_ABOUT]', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (49, 47, 2, 'NBILL_MNU_HELP_DOCUMENTATION', 'NBILL_MNU_HELP_DOCUMENTATION_DESC', '[NBILL_FE]/images/icons/documentation.gif', '[NBILL_DOCUMENTATION]', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (50, 47, 3, 'NBILL_MNU_HELP_REGISTRATION', 'NBILL_MNU_HELP_REGISTRATION_DESC', '[NBILL_FE]/images/icons/registration.gif', '[NBILL_ADMIN]&action=registration', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (51, 47, 4, 'NBILL_MNU_HELP_SUPPORT', 'NBILL_MNU_HELP_SUPPORT_DESC', '[NBILL_FE]/images/icons/support.gif', '[NBILL_SUPPORT]', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (52, 18, 6, 'NBILL_MNU_EMAIL_LOG', 'NBILL_MNU_EMAIL_LOG_HELP', '[NBILL_FE]/images/icons/email_log.gif', '[NBILL_ADMIN]&action=email_log', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (53, 2, 7, 'NBILL_MNU_FEES', 'NBILL_MNU_FEES_DESC', '[NBILL_FE]/images/icons/fees.gif', '[NBILL_ADMIN]&action=fees', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (54, 2, 13, 'NBILL_MNU_HOUSEKEEPING', 'NBILL_MNU_HOUSEKEEPING_DESC', '[NBILL_FE]/images/icons/housekeeping.gif', '[NBILL_ADMIN]&action=housekeeping', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (55 , 21, 7, 'NBILL_MNU_USER_ADMIN', 'NBILL_MNU_USER_ADMIN_DESC', '[NBILL_FE]/images/icons/user_admin.gif', '[NBILL_ADMIN]&action=user_admin', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (56, 15, 4, 'NBILL_MNU_SUPPORTING_DOCS', 'NBILL_MNU_SUPPORTING_DOCS_DESC', '[NBILL_FE]/images/icons/supporting_docs.gif', '[NBILL_ADMIN]&action=supporting_docs', 1, 0);
##########
INSERT INTO `#__nbill_menu` VALUES (57, 2, 14, 'NBILL_MNU_TRANSLATION', 'NBILL_MNU_TRANSLATION_DESC', '[NBILL_FE]/images/icons/translation.png', '[NBILL_ADMIN]&action=translation', 1, 0);
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('1', 'paypal', 'business', 'info@email.com', 'NBILL_PAYPAL_EMAIL', 'NBILL_PAYPAL_EMAIL_HELP', '1', '1', '1', 'varchar');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('3', 'paypal', 'sandbox', '0', 'NBILL_PAYPAL_SANDBOX', 'NBILL_PAYPAL_SANDBOX_HELP', '1', '1', '2', 'boolean');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('2', 'paypal', 'sra', '1', 'NBILL_PAYPAL_REATTEMPT', 'NBILL_PAYPAL_REATTEMPT_HELP', '0', '1', '3', 'boolean');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('6', 'paypal', 'notify_url', '[NBILL_FE_PAGE_PREFIX]&action=gateway&task=ipn&gateway=paypal', 'NBILL_PAYPAL_IPN_URL', 'NBILL_PAYPAL_IPN_URL_HELP', '0', '1', '6', 'varchar');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('8', 'paypal', 'add_debug_info', '0', 'NBILL_PAYPAL_ADD_DEBUG_INFO', 'NBILL_PAYPAL_ADD_DEBUG_INFO_HELP', '0', '1', '8', 'boolean');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('9', 'paypal', 'gateway_description', 'NBILL_PAYPAL_DESC', '', '', '0', '0', '9', 'varchar');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('12', 'paypal', 'verify_callback', '1', 'NBILL_PAYPAL_VERIFY_CALLBACK', 'NBILL_PAYPAL_VERIFY_CALLBACK_HELP', '0', '1', '11', 'boolean');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('13', 'paypal', 'success_url', '', 'NBILL_PAYPAL_SUCCESS_URL', 'NBILL_PAYPAL_SUCCESS_URL_HELP', '0', '1', '12', 'varchar');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('14', 'paypal', 'failure_url', '', 'NBILL_PAYPAL_FAILURE_URL', 'NBILL_PAYPAL_FAILURE_URL_HELP', '0', '1', '13', 'varchar');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('4', 'paypal', 'ssl_cipher', '', 'NBILL_PAYPAL_SSL_CIPHER', 'NBILL_PAYPAL_SSL_CIPHER_HELP', '0', '1', '14', 'varchar');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10005', 'paypal', 'api_info', '', '', 'NBILL_PAYPAL_API_INFO_HELP', '0', '0', '15', 'label');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10010', 'paypal', 'api_sandbox', '1', 'NBILL_PAYPAL_API_SANDBOX', 'NBILL_PAYPAL_API_SANDBOX_HELP', '0', '1', '16', 'boolean');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10015', 'paypal', 'default_max_amount', '100.00', 'NBILL_PAYPAL_DEFAULT_MAX_AMOUNT', 'NBILL_PAYPAL_DEFAULT_MAX_AMOUNT_HELP', '0', '1', '17', 'decimal');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10020', 'paypal', 'default_payment_count', '15', 'NBILL_PAYPAL_DEFAULT_PAYMENT_COUNT', 'NBILL_PAYPAL_DEFAULT_PAYMENT_COUNT_HELP', '0', '1', '18', 'integer');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10025', 'paypal', 'api_sandbox_user', '', 'NBILL_PAYPAL_API_SANDBOX_USER', 'NBILL_PAYPAL_API_SANDBOX_USER_HELP', '0', '1', '19', 'string');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10030', 'paypal', 'api_sandbox_password', '', 'NBILL_PAYPAL_API_SANDBOX_PASSWORD', 'NBILL_PAYPAL_API_SANDBOX_PASSWORD_HELP', '0', '1', '20', 'string');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10035', 'paypal', 'api_sandbox_signature', '', 'NBILL_PAYPAL_API_SANDBOX_SIGNATURE', 'NBILL_PAYPAL_API_SANDBOX_SIGNATURE_HELP', '0', '1', '21', 'string');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10040', 'paypal', 'api_sandbox_appid', 'APP-80W284485P519543T', 'NBILL_PAYPAL_API_SANDBOX_APPID', 'NBILL_PAYPAL_API_SANDBOX_APPID_HELP', '0', '1', '22', 'string');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10045', 'paypal', 'api_user', '', 'NBILL_PAYPAL_API_USER', 'NBILL_PAYPAL_API_USER_HELP', '0', '1', '23', 'string');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10050', 'paypal', 'api_password', '', 'NBILL_PAYPAL_API_PASSWORD', 'NBILL_PAYPAL_API_PASSWORD_HELP', '0', '1', '24', 'string');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10055', 'paypal', 'api_signature', '', 'NBILL_PAYPAL_API_SIGNATURE', 'NBILL_PAYPAL_API_SIGNATURE_HELP', '0', '1', '25', 'string');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`) VALUES ('10060', 'paypal', 'api_appid', '', 'NBILL_PAYPAL_API_APPID', 'NBILL_PAYPAL_API_APPID_HELP', '0', '1', '26', 'string');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`, `options`) VALUES (10065, 'paypal', 'confirm_signups', '1', 'NBILL_PAYPAL_CONFIRM_SIGNUPS', 'NBILL_PAYPAL_CONFIRM_SIGNUPS_HELP', 0, 1, 27, 'boolean', '');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`, `options`) VALUES (10070, 'paypal', 'preapp_thankyou', 'Thank you for authorising us to charge your Paypal account. Your instruction has been received successfully.', 'NBILL_PAYPAL_DEFAULT_PREAPP_THANKS', 'NBILL_PAYPAL_DEFAULT_PREAPP_THANKS_HELP', 0, 1, 28, 'text', '')
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`, `options`) VALUES (10075, 'paypal', 'preapp_success_url', '', 'NBILL_PAYPAL_PREAPP_SUCCESS_URL', 'NBILL_PAYPAL_PREAPP_SUCCESS_URL_HELP', 0, 1, 29, 'string', '');
##########
INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`, `data_type`, `options`) VALUES (10080, 'paypal', 'preapp_failure_url', '', 'NBILL_PAYPAL_PREAPP_FAILURE_URL', 'NBILL_PAYPAL_PREAPP_FAILURE_URL_HELP', 0, 1, 30, 'string', '');
##########
INSERT INTO `#__nbill_payment_gateway_config` (`gateway_id`, `display_name`, `ordering`, `published`) VALUES ('offline', 'NBILL_ARRANGE_OFFLINE', '0', '0');
##########
INSERT INTO `#__nbill_payment_gateway_config` (`gateway_id`,  `display_name`, `ordering`, `published`) VALUES ('paypal', 'Paypal', '1', '1');
##########
INSERT INTO `#__nbill_payment_plans` (`id`, `plan_name`, `plan_type`, `currency`, `deposit_amount`, `deposit_percentage`, `installment_frequency`, `no_of_installments`, `quote_default`, `invoice_default`) VALUES (1, 'Payment In Full', 'AA', '', 0.00, 0.00, '', 0, 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (1, 1, 'OO', 'NBILL_CORE_login', '', 1, 'style=\"border: solid 1px #cccccc;margin-left:auto;margin-right:auto;margin-bottom:10px;\"', '', '', '<div class=\"nbill-login-box-outer\"><div class=\"nbill-login-box-inner\">$$return defined(\"NBILL_NOT_YET_REGISTERED\") ? NBILL_NOT_YET_REGISTERED : \"New Client? Please fill in your details below.\";$$</div></div>', '', 0, '', '', 0, '', '', 1, 0);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (2, 2, 'AA', 'NBILL_CORE_first_name', '* NBILL_FIRST_NAME', 0, '', '', '', '', '', 1, '', '', 0, '', 'first_name', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (3, 3, 'AA', 'NBILL_CORE_last_name', '* NBILL_LAST_NAME', 0, '', '', '', '', '', 1, '', '', 0, '', 'last_name', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`, `include_on_forms`) VALUES (4, 4, 'EE', 'NBILL_CORE_add_name_to_invoice', 'NBILL_PRIMARY_CONTACT_NAME', 0, '', 'NBILL_CLIENT_ADD_NAME_TO_INVOICE', '', '', '', 0, '', '', 0, 'add_name_to_invoice', '', 1, 1, 0);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (5, 5, 'AA', 'NBILL_CORE_company_name', 'NBILL_COMPANY_NAME', 0, '', '', '', '', '', 0, '', '', 0, 'company_name', '', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (6, 6, 'AA', 'NBILL_CORE_address_1', '* NBILL_ADDRESS_1', 0, '', '', '', '', '', 1, '', '', 0, 'address_1', 'address_1', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (7, 7, 'AA', 'NBILL_CORE_address_2', 'NBILL_ADDRESS_2', 0, '', '', '', '', '', 0, '', '', 0, 'address_2', 'address_2', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (8, 8, 'AA', 'NBILL_CORE_address_3', 'NBILL_ADDRESS_3', 0, '', '', '', '', '', 0, '', '', 0, 'address_3', 'address_3', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (9, 9, 'AA', 'NBILL_CORE_town', '* NBILL_TOWN', 0, '', '', '', '', '', 1, '', '', 0, 'town', 'town', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (10, 10, 'AA', 'NBILL_CORE_state', 'NBILL_STATE', 0, '', '', '', '', '', 0, '', '', 0, 'state', 'state', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (11, 11, 'AA', 'NBILL_CORE_postcode', '* NBILL_POSTCODE', 0, '', '', '', '', '', 1, '', '', 0, 'postcode', 'postcode', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (12, 12, 'BB', 'NBILL_CORE_country', '* NBILL_COUNTRY', 0, '', '', '', '', CONCAT('$$$sql="SELECT vendor_country FROM #', '__nbill_vendor WHERE default_vendor = 1";nbf_cms::$interop->database->setQuery($sql);return nbf_cms::$interop->database->loadResult();$$'), 1, '', 'country_codes', 0, 'country', 'country', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (13, 13, 'EE', 'NBILL_CORE_shipping_same', 'NBILL_ADDRESS_SHIPPING', 0, 'onclick="var nbc=document.getElementsByClassName(\'nbill_control\');for(var i=0;i<nbc.length;i++){if(nbc[i].name&&nbc[i].name.substr(0,24)==\'ctl_NBILL_CORE_shipping_\'){nbc[i].disabled=this.checked}}"', 'NBILL_ADDRESS_SAME_AS_BILLING', '', '', 'On', 0, '', '', 0, 'same_as_billing', 'same_as_billing', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (14, 14, 'AA', 'NBILL_CORE_shipping_address_1', 'NBILL_SHIPPING_ADDRESS_1', 0, 'disabled="disabled"', '', '', '<script type="text/javascript">if(document.getElementsByName(\'ctl_NBILL_CORE_shipping_same\')[0].checked===false){document.getElementsByName(\'ctl_NBILL_CORE_shipping_address_1\')[0].disabled=false;}</script>', '', 0, '', '', 0, 'shipping_address_1', 'shipping_address_1', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (15, 15, 'AA', 'NBILL_CORE_shipping_address_2', 'NBILL_SHIPPING_ADDRESS_2', 0, 'disabled="disabled"', '', '', '<script type="text/javascript">if(document.getElementsByName(\'ctl_NBILL_CORE_shipping_same\')[0].checked===false){document.getElementsByName(\'ctl_NBILL_CORE_shipping_address_2\')[0].disabled=false;}</script>', '', 0, '', '', 0, 'shipping_address_2', 'shipping_address_2', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (16, 16, 'AA', 'NBILL_CORE_shipping_address_3', 'NBILL_SHIPPING_ADDRESS_3', 0, 'disabled="disabled"', '', '', '<script type="text/javascript">if(document.getElementsByName(\'ctl_NBILL_CORE_shipping_same\')[0].checked===false){document.getElementsByName(\'ctl_NBILL_CORE_shipping_address_3\')[0].disabled=false;}</script>', '', 0, '', '', 0, 'shipping_address_3', 'shipping_address_3', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (17, 17, 'AA', 'NBILL_CORE_shipping_town', '* NBILL_SHIPPING_TOWN', 0, 'disabled="disabled"', '', '', '<script type="text/javascript">if(document.getElementsByName(\'ctl_NBILL_CORE_shipping_same\')[0].checked===false){document.getElementsByName(\'ctl_NBILL_CORE_shipping_town\')[0].disabled=false;}</script>', '', 0, '', '', 0, 'shipping_town', 'shipping_town', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (18, 18, 'AA', 'NBILL_CORE_shipping_state', 'NBILL_SHIPPING_STATE', 0, 'disabled="disabled"', '', '', '<script type="text/javascript">if(document.getElementsByName(\'ctl_NBILL_CORE_shipping_same\')[0].checked===false){document.getElementsByName(\'ctl_NBILL_CORE_shipping_state\')[0].disabled=false;}</script>', '', 0, '', '', 0, 'shipping_state', 'shipping_state', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (19, 19, 'AA', 'NBILL_CORE_shipping_postcode', 'NBILL_SHIPPING_POSTCODE', 0, 'disabled="disabled"', '', '', '<script type="text/javascript">if(document.getElementsByName(\'ctl_NBILL_CORE_shipping_same\')[0].checked===false){document.getElementsByName(\'ctl_NBILL_CORE_shipping_postcode\')[0].disabled=false;}</script>', '', 0, '', '', 0, 'shipping_postcode', 'shipping_postcode', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (20, 20, 'BB', 'NBILL_CORE_shipping_country', 'NBILL_SHIPPING_COUNTRY', 0, 'disabled="disabled"', '', '', '<script type="text/javascript">if(document.getElementsByName(\'ctl_NBILL_CORE_shipping_same\')[0].checked===false){document.getElementsByName(\'ctl_NBILL_CORE_shipping_country\')[0].disabled=false;}</script>', '$$$sql="SELECT vendor_country FROM #__nbill_vendor WHERE default_vendor = 1";nbf_cms::$interop->database->setQuery($sql);return nbf_cms::$interop->database->loadResult();$$', 0, '', 'country_codes', 0, 'shipping_country', 'shipping_country', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (21, 21, 'CC', 'NBILL_CORE_email_address', '* NBILL_EMAIL_ADDRESS', 0, '', '', '', '', '', 1, '', '', 1, '', 'email_address', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`, `include_on_forms`) VALUES (22, 22, 'AA', 'NBILL_CORE_email_address_2', 'NBILL_EMAIL_ADDRESS_2', 0, '', '', '', '', '', 0, '', '', 1, '', 'email_address_2', 1, 1, 0);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (23, 23, 'AA', 'NBILL_CORE_telephone', 'NBILL_TELEPHONE', 0, '', '', '', '', '', 0, '', '', 0, '', 'telephone', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`, `include_on_forms`) VALUES (24, 24, 'AA', 'NBILL_CORE_telephone_2', 'NBILL_TELEPHONE_2', 0, '', '', '', '', '', 0, '', '', 0, '', 'telephone_2', 1, 1, 0);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`, `include_on_forms`) VALUES (25, 25, 'AA', 'NBILL_CORE_mobile', 'NBILL_MOBILE', 0, '', '', '', '', '', 0, '', '', 0, '', 'mobile', 1, 1, 0);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`, `include_on_forms`) VALUES (26, 26, 'AA', 'NBILL_CORE_fax', 'NBILL_FAX', 0, '', '', '', '', '', 0, '', '', 0, '', 'fax', 1, 1, 0);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`, `include_on_forms`) VALUES (27, 27, 'AA', 'NBILL_CORE_tax_exemption_code', 'NBILL_TAX_EXEMPTION_CODE', 0, '', '', '', '', '', 0, 'NBILL_TAX_EXEMPTION_CODE_HELP', '', 0, 'tax_exemption_code', '', 1, 1, 0);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (28, 28, 'AA', 'NBILL_CORE_username', '* NBILL_USERNAME', 0, '', '', '', '', '', 1, 'NBILL_USERNAME_HELP', '', 0, '', 'username', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`) VALUES (29, 29, 'CP', 'NBILL_CORE_password', '* NBILL_PASSWORD', 0, '', '', '', '', '', 1, '', '', 1, '', 'password', 1, 1);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`, `include_on_forms`) VALUES (30, 30, 'EE', 'NBILL_CORE_reminder_emails', 'NBILL_EMAIL_REMINDERS', 0, '', 'NBILL_EMAIL_REMINDERS_CHKBOX', '', '', 'On', 0, '', '', 0, 'reminder_emails', '', 1, 1, 0);
##########
INSERT INTO `#__nbill_profile_fields` (`id`, `ordering`, `field_type`, `name`, `label`, `merge_columns`, `attributes`, `checkbox_text`, `pre_field`, `post_field`, `default_value`, `required`, `help_text`, `xref`, `confirmation`, `entity_mapping`, `contact_mapping`, `published`, `show_on_summary`, `include_on_forms`) VALUES (31, 31, 'DD', 'NBILL_CORE_email_invoice_option', 'NBILL_EMAIL_INVOICE_OPTIONS', 0, '', '', '', '', 'AB', 0, '', 'email_invoice', 0, 'email_invoice_option', '', 1, 1, 0);
##########
INSERT INTO `#__nbill_vendor` (`id`, `vendor_name`, `vendor_address`, `vendor_country`, `vendor_currency`, `default_vendor`, `next_invoice_no`, `invoice_no_locked`, `next_order_no`, `order_no_locked`, `next_receipt_no`, `receipt_no_locked`, `next_payment_no`, `payment_no_locked`, `next_credit_no`, `credit_no_locked`, `next_quote_no`, `quote_no_locked`, `next_po_no`, `po_no_locked`, `payment_instructions`, `small_print`, `invoice_template_name`, `credit_template_name`, `quote_template_name`, `po_template_name`, `admin_email`, `paper_size`, `default_gateway`, `auto_create_income`, `suppress_receipt_nos`, `quote_default_intro`, `quote_offline_pay_inst`, `credit_small_print`, `quote_small_print`) VALUES ('1', 'Your Company Name', 'Address Line 1\nTown\nPostcode', 'GB', 'GBP', 1, '0001', '0', '0001', '0', '0001', '0', '0001', '0', 'CR-0001', '0', 'QU-0001', '0', 'PO-0001', '0', 'Enter payment instructions here (eg. bank details).', 'Enter any legal jargon here.', 'invoice_default', 'credit_default', 'quote_default', 'po_default', 'info@email.com', 'A4', 'paypal', '1', '1', '', '', '', '');
##########
INSERT INTO `#__nbill_version` (`id`, `software_version`, `service_pack`) VALUES ('1', '3.1.1', '0');
##########
INSERT INTO `#__nbill_widgets` (`id`, `title`, `show_title`, `published`, `configurable`, `params`, `width`, `widget_type`, `ordering`) VALUES (1, '', 1, 1, 1, '', '99%', 'html', 0);
##########
INSERT INTO `#__nbill_widgets` (`id`, `title`, `show_title`, `published`, `configurable`, `params`, `width`, `widget_type`, `ordering`) VALUES (2, '', 1, 1, 1, '', '49%', 'sales_graph', 1);
##########
INSERT INTO `#__nbill_widgets` (`id`, `title`, `show_title`, `published`, `configurable`, `params`, `width`, `widget_type`, `ordering`) VALUES (3, '', 1, 1, 1, '', '49%', 'orders_due', 2);
##########
INSERT INTO `#__nbill_widgets` (`id`, `title`, `show_title`, `published`, `configurable`, `params`, `width`, `widget_type`, `ordering`) VALUES (4, '', 1, 1, 1, '', 'auto', 'links', 3);
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AF', 'AFGHANISTAN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AX', 'ALAND ISLANDS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AL', 'ALBANIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('DZ', 'ALGERIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AS', 'AMERICAN SAMOA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AD', 'ANDORRA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AO', 'ANGOLA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AI', 'ANGUILLA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AQ', 'ANTARCTICA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AG', 'ANTIGUA AND BARBUDA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AR', 'ARGENTINA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AM', 'ARMENIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AW', 'ARUBA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AU', 'AUSTRALIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AT', 'AUSTRIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AZ', 'AZERBAIJAN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BS', 'BAHAMAS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BH', 'BAHRAIN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BD', 'BANGLADESH');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BB', 'BARBADOS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BY', 'BELARUS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BE', 'BELGIUM');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BZ', 'BELIZE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BJ', 'BENIN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BM', 'BERMUDA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BT', 'BHUTAN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BO', 'BOLIVIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BA', 'BOSNIA AND HERZEGOVINA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BW', 'BOTSWANA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BV', 'BOUVET ISLAND');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BR', 'BRAZIL');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('IO', 'BRITISH INDIAN OCEAN TERRITORY');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BN', 'BRUNEI DARUSSALAM');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BG', 'BULGARIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BF', 'BURKINA FASO');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('BI', 'BURUNDI');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('KH', 'CAMBODIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CM', 'CAMEROON');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CA', 'CANADA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CV', 'CAPE VERDE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('KY', 'CAYMAN ISLANDS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CF', 'CENTRAL AFRICAN REPUBLIC');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TD', 'CHAD');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CL', 'CHILE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CN', 'CHINA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CX', 'CHRISTMAS ISLAND');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CC', 'COCOS (KEELING) ISLANDS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CO', 'COLOMBIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('KM', 'COMOROS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CG', 'CONGO');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CD', 'CONGO, DEMOCRATIC REPUBLIC');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CK', 'COOK ISLANDS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CR', 'COSTA RICA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CI', 'COTE D\'IVOIRE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('HR', 'CROATIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CU', 'CUBA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CY', 'CYPRUS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CZ', 'CZECH REPUBLIC');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('DK', 'DENMARK');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('DJ', 'DJIBOUTI');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('DM', 'DOMINICA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('DO', 'DOMINICAN REPUBLIC');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('EC', 'ECUADOR');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('EG', 'EGYPT');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SV', 'EL SALVADOR');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GQ', 'EQUATORIAL GUINEA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('ER', 'ERITREA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('EE', 'ESTONIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('ET', 'ETHIOPIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('FK', 'FALKLAND ISLANDS (MALVINAS)');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('FO', 'FAROE ISLANDS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('FJ', 'FIJI');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('FI', 'FINLAND');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('FR', 'FRANCE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GF', 'FRENCH GUIANA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('PF', 'FRENCH POLYNESIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TF', 'FRENCH SOUTHERN TERRITORIES');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GA', 'GABON');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GM', 'GAMBIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GE', 'GEORGIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('DE', 'GERMANY');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GH', 'GHANA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GI', 'GIBRALTAR');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GR', 'GREECE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GL', 'GREENLAND');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GD', 'GRENADA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GP', 'GUADELOUPE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GU', 'GUAM');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GT', 'GUATEMALA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GN', 'GUINEA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GW', 'GUINEA-BISSAU');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GY', 'GUYANA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('HT', 'HAITI');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('HM', 'HEARD ISLAND/MCDONALD ISLANDS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('VA', 'HOLY SEE (VATICAN CITY STATE)');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('HN', 'HONDURAS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('HK', 'HONG KONG');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('HU', 'HUNGARY');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('IS', 'ICELAND');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('IN', 'INDIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('ID', 'INDONESIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('IR', 'IRAN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('IQ', 'IRAQ');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('IE', 'IRELAND');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('IL', 'ISRAEL');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('IT', 'ITALY');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('JM', 'JAMAICA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('JP', 'JAPAN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('JO', 'JORDAN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('KZ', 'KAZAKHSTAN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('KE', 'KENYA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('KI', 'KIRIBATI');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('KP', 'KOREA, DEMOCRATIC REPUBLIC');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('KR', 'KOREA, REPUBLIC OF');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('KW', 'KUWAIT');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('KG', 'KYRGYZSTAN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('LA', 'LAO DEMOCRATIC REPUBLIC');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('LV', 'LATVIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('LB', 'LEBANON');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('LS', 'LESOTHO');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('LR', 'LIBERIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('LY', 'LIBYAN ARAB JAMAHIRIYA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('LI', 'LIECHTENSTEIN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('LT', 'LITHUANIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('LU', 'LUXEMBOURG');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MO', 'MACAO');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MK', 'MACEDONIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MG', 'MADAGASCAR');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MW', 'MALAWI');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MY', 'MALAYSIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MV', 'MALDIVES');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('ML', 'MALI');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MT', 'MALTA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MH', 'MARSHALL ISLANDS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MQ', 'MARTINIQUE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MR', 'MAURITANIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MU', 'MAURITIUS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('YT', 'MAYOTTE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MX', 'MEXICO');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('FM', 'MICRONESIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MD', 'MOLDOVA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MC', 'MONACO');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MN', 'MONGOLIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MS', 'MONTSERRAT');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MA', 'MOROCCO');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MZ', 'MOZAMBIQUE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MM', 'MYANMAR');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('NA', 'NAMIBIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('NR', 'NAURU');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('NP', 'NEPAL');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('NL', 'NETHERLANDS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AN', 'NETHERLANDS ANTILLES');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('NC', 'NEW CALEDONIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('NZ', 'NEW ZEALAND');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('NI', 'NICARAGUA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('NE', 'NIGER');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('NG', 'NIGERIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('NU', 'NIUE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('NF', 'NORFOLK ISLAND');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('MP', 'NORTHERN MARIANA ISLANDS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('NO', 'NORWAY');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('OM', 'OMAN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('PK', 'PAKISTAN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('PW', 'PALAU');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('PS', 'PALESTINIAN TERRITORY');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('PA', 'PANAMA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('PG', 'PAPUA NEW GUINEA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('PY', 'PARAGUAY');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('PE', 'PERU');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('PH', 'PHILIPPINES');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('PN', 'PITCAIRN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('PL', 'POLAND');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('PT', 'PORTUGAL');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('PR', 'PUERTO RICO');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('QA', 'QATAR');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('RE', 'REUNION');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('RO', 'ROMANIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('RU', 'RUSSIAN FEDERATION');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('RW', 'RWANDA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SH', 'SAINT HELENA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('KN', 'SAINT KITTS AND NEVIS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('LC', 'SAINT LUCIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('PM', 'SAINT PIERRE AND MIQUELON');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('VC', 'ST VINCENT AND THE GRENADINES');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('WS', 'SAMOA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SM', 'SAN MARINO');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('ST', 'SAO TOME AND PRINCIPE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SA', 'SAUDI ARABIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SN', 'SENEGAL');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CS', 'SERBIA AND MONTENEGRO');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SC', 'SEYCHELLES');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SL', 'SIERRA LEONE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SG', 'SINGAPORE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SK', 'SLOVAKIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SI', 'SLOVENIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SB', 'SOLOMON ISLANDS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SO', 'SOMALIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('ZA', 'SOUTH AFRICA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GS', 'SOUTH GEORGIA/SANDWICH ISLES');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('ES', 'SPAIN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('LK', 'SRI LANKA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SD', 'SUDAN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SR', 'SURINAME');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SJ', 'SVALBARD AND JAN MAYEN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SZ', 'SWAZILAND');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SE', 'SWEDEN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('CH', 'SWITZERLAND');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('SY', 'SYRIAN ARAB REPUBLIC');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TW', 'TAIWAN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TJ', 'TAJIKISTAN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TZ', 'TANZANIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TH', 'THAILAND');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TL', 'TIMOR-LESTE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TG', 'TOGO');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TK', 'TOKELAU');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TO', 'TONGA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TT', 'TRINIDAD AND TOBAGO');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TN', 'TUNISIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TR', 'TURKEY');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TM', 'TURKMENISTAN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TC', 'TURKS AND CAICOS ISLANDS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('TV', 'TUVALU');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('UG', 'UGANDA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('UA', 'UKRAINE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('AE', 'UNITED ARAB EMIRATES');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('GB', 'UNITED KINGDOM');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('US', 'UNITED STATES');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('UM', 'US MINOR OUTLYING ISLANDS');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('UY', 'URUGUAY');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('UZ', 'UZBEKISTAN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('VU', 'VANUATU');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('VE', 'VENEZUELA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('VN', 'VIET NAM');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('VG', 'VIRGIN ISLANDS, BRITISH');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('VI', 'VIRGIN ISLANDS, U.S.');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('WF', 'WALLIS AND FUTUNA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('EH', 'WESTERN SAHARA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('YE', 'YEMEN');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('ZM', 'ZAMBIA');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('ZW', 'ZIMBABWE');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('EU', 'European Union');
##########
INSERT INTO `#__nbill_xref_country_codes` (`code`, `description`) VALUES ('WW', 'Worldwide');
##########
INSERT INTO `#__nbill_xref_default_start_date` (`code`, `description`) VALUES ('AA', 'NBILL_CFG_START_DATE_CURRENT_ONLY');
##########
INSERT INTO `#__nbill_xref_default_start_date` (`code`, `description`) VALUES ('BB', 'NBILL_CFG_START_DATE_CURRENT_LAST');
##########
INSERT INTO `#__nbill_xref_default_start_date` (`code`, `description`) VALUES ('CC', 'NBILL_CFG_START_DATE_QUARTER');
##########
INSERT INTO `#__nbill_xref_default_start_date` (`code`, `description`) VALUES ('DD', 'NBILL_CFG_START_DATE_SEMI');
##########
INSERT INTO `#__nbill_xref_default_start_date` (`code`, `description`) VALUES ('EE', 'NBILL_CFG_START_DATE_YEAR');
##########
INSERT INTO `#__nbill_xref_default_start_date` (`code`, `description`) VALUES ('FF', 'NBILL_CFG_START_DATE_FIVE');
##########
INSERT INTO `#__nbill_xref_default_start_date` (`code`, `description`) VALUES ('GG', 'NBILL_CFG_START_DATE_ALL');
##########
INSERT INTO `#__nbill_xref_email_invoice` (`code`, `description`) VALUES ('AA', 'NBILL_NO_EMAIL');
##########
INSERT INTO `#__nbill_xref_email_invoice` (`code`, `description`) VALUES ('BB', 'NBILL_EMAIL_INVOICE');
##########
INSERT INTO `#__nbill_xref_email_invoice` (`code`, `description`) VALUES ('CC', 'NBILL_EMAIL_NOTIFICATION');
##########
INSERT INTO `#__nbill_xref_email_invoice` (`code`, `description`) VALUES ('AB', 'NBILL_EMAIL_INVOICE_ATTACH');
##########
INSERT INTO `#__nbill_xref_email_invoice` (`code`, `description`) VALUES ('AC', 'NBILL_EMAIL_INVOICE_PDF');
##########
INSERT INTO `#__nbill_xref_email_invoice` (`code`, `description`) VALUES ('DD', 'NBILL_EMAIL_TEMPLATE');
##########
INSERT INTO `#__nbill_xref_email_invoice` (`code`, `description`) VALUES ('EE', 'NBILL_EMAIL_TEMPLATE_ATTACH');
##########
INSERT INTO `#__nbill_xref_email_invoice` (`code`, `description`) VALUES ('FF', 'NBILL_EMAIL_TEMPLATE_PDF');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (1, 'AT', 'AUSTRIA');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (2, 'BE', 'BELGIUM');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (3, 'CY', 'CYPRUS');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (4, 'CZ', 'CZECH REPUBLIC');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (5, 'DK', 'DENMARK');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (6, 'EE', 'ESTONIA');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (7, 'FI', 'FINLAND');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (8, 'FR', 'FRANCE');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (9, 'DE', 'GERMANY');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (10, 'GR', 'GREECE');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (11, 'HU', 'HUNGARY');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (12, 'IE', 'IRELAND');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (13, 'IT', 'ITALY');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (14, 'LV', 'LATVIA');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (15, 'LT', 'LITHUANIA');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (16, 'LU', 'LUXEMBOURG');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (17, 'MT', 'MALTA');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (18, 'PL', 'POLAND');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (19, 'PT', 'PORTUGAL');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (20, 'SK', 'SLOVAKIA');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (21, 'SI', 'SLOVENIA');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (22, 'ES', 'SPAIN');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (23, 'SE', 'SWEDEN');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (24, 'NL', 'THE NETHERLANDS');
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (25, 'RO', 'ROMANIA') ;
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (26, 'BG', 'BULGARIA') ;
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (27, 'GB', 'UNITED KINGDOM') ;
##########
INSERT INTO `#__nbill_xref_eu_country_codes` (`id`, `code`, `description`) VALUES (28, 'HR', 'CROATIA');
##########
INSERT INTO `#__nbill_xref_field_type` (`code`, `description`) VALUES ('AA', 'NBILL_FLD_TEXTBOX');
##########
INSERT INTO `#__nbill_xref_field_type` (`code`, `description`) VALUES ('BB', 'NBILL_FLD_DROPDOWN');
##########
INSERT INTO `#__nbill_xref_field_type` (`code`, `description`) VALUES ('CC', 'NBILL_FLD_EMAIL');
##########
INSERT INTO `#__nbill_xref_field_type` (`code`, `description`) VALUES ('CP', 'NBILL_FLD_PASSWORD');
##########
INSERT INTO `#__nbill_xref_field_type` (`code`, `description`) VALUES ('DD', 'NBILL_FLD_RADIOLIST');
##########
INSERT INTO `#__nbill_xref_field_type` (`code`, `description`) VALUES ('EE', 'NBILL_FLD_CHECKBOX');
##########
INSERT INTO `#__nbill_xref_field_type` (`code`, `description`) VALUES ('FF', 'NBILL_FLD_TEXTAREA');
##########
INSERT INTO `#__nbill_xref_field_type` (`code`, `description`) VALUES ('GG', 'NBILL_FLD_NUMERIC');
##########
INSERT INTO `#__nbill_xref_field_type` (`code`, `description`) VALUES ('GZ', 'NBILL_FLD_DATE');
##########
INSERT INTO `#__nbill_xref_field_type` (`code`, `description`) VALUES ('HH', 'NBILL_FLD_HIDDEN');
##########
INSERT INTO `#__nbill_xref_field_type` (`code`, `description`) VALUES ('LL', 'NBILL_FLD_LABEL');
##########
INSERT INTO `#__nbill_xref_field_type` (`code`, `description`) VALUES ('MM', 'NBILL_FLD_JAVASCRIPT_BUTTON');
##########
INSERT INTO `#__nbill_xref_field_type` (`code`, `description`) VALUES ('NN', 'NBILL_FLD_SUBMIT_BUTTON');
##########
INSERT INTO `#__nbill_xref_field_type` (`code`, `description`) VALUES ('JJ', 'NBILL_FLD_DOMAIN_LOOKUP');
##########
INSERT INTO `#__nbill_xref_field_type` (`code` , `description`) VALUES ('KK', 'NBILL_FLD_FILE_UPLOAD');
##########
INSERT INTO `#__nbill_xref_field_type` (`code` , `description`) VALUES ('OO', 'NBILL_FLD_LOGIN');
##########
INSERT INTO `#__nbill_xref_field_type` (`code` , `description`) VALUES ('PP', 'NBILL_FLD_SUMMARY');
##########
INSERT INTO `#__nbill_xref_field_type` (`code` , `description`) VALUES ('SS', 'NBILL_FLD_CAPTCHA');
##########
INSERT INTO `#__nbill_xref_order_status` (`code`, `description`) VALUES ('AA', 'NBILL_STATUS_PENDING');
##########
INSERT INTO `#__nbill_xref_order_status` (`code`, `description`) VALUES ('BB', 'NBILL_STATUS_PROCESSING');
##########
INSERT INTO `#__nbill_xref_order_status` (`code`, `description`) VALUES ('CC', 'NBILL_STATUS_DISPATCHED');
##########
INSERT INTO `#__nbill_xref_order_status` (`code`, `description`) VALUES ('DD', 'NBILL_STATUS_COMPLETED');
##########
INSERT INTO `#__nbill_xref_order_status` (`code`, `description`) VALUES ('EE', 'NBILL_STATUS_CANCELLED');
##########
INSERT INTO `#__nbill_xref_quote_status` (`code`, `description`) VALUES ('AA', 'NBILL_STATUS_QUOTE_NEW');
##########
INSERT INTO `#__nbill_xref_quote_status` (`code`, `description`) VALUES ('BB', 'NBILL_STATUS_QUOTE_ON_HOLD');
##########
INSERT INTO `#__nbill_xref_quote_status` (`code`, `description`) VALUES ('CC', 'NBILL_STATUS_QUOTE_QUOTED');
##########
INSERT INTO `#__nbill_xref_quote_status` (`code`, `description`) VALUES ('DD', 'NBILL_STATUS_QUOTE_ACCEPTED');
##########
INSERT INTO `#__nbill_xref_quote_status` (`code`, `description`) VALUES ('EE', 'NBILL_STATUS_QUOTE_PART_ACCEPTED');
##########
INSERT INTO `#__nbill_xref_quote_status` (`code`, `description`) VALUES ('FF', 'NBILL_STATUS_QUOTE_REJECTED');
##########
INSERT INTO `#__nbill_xref_quote_status` (`code`, `description`) VALUES ('GG', 'NBILL_STATUS_QUOTE_WITHDRAWN');
##########
INSERT INTO `#__nbill_xref_pay_frequency` (`code`, `description`) VALUES ('BB', 'NBILL_WEEKLY');
##########
INSERT INTO `#__nbill_xref_pay_frequency` (`code`, `description`) VALUES ('BX', 'NBILL_FOUR_WEEKLY');
##########
INSERT INTO `#__nbill_xref_pay_frequency` (`code`, `description`) VALUES ('CC', 'NBILL_MONTHLY');
##########
INSERT INTO `#__nbill_xref_pay_frequency` (`code`, `description`) VALUES ('DD', 'NBILL_QUARTERLY');
##########
INSERT INTO `#__nbill_xref_pay_frequency` (`code`, `description`) VALUES ('DX', 'NBILL_SEMI_ANNUALLY');
##########
INSERT INTO `#__nbill_xref_pay_frequency` (`code`, `description`) VALUES ('EE', 'NBILL_ANNUALLY');
##########
INSERT INTO `#__nbill_xref_pay_frequency` (`code`, `description`) VALUES ('FF', 'NBILL_BIANNUALLY');
##########
INSERT INTO `#__nbill_xref_pay_frequency` (`code`, `description`) VALUES ('GG', 'NBILL_FIVE_YEARLY');
##########
INSERT INTO `#__nbill_xref_pay_frequency` (`code`, `description`) VALUES ('HH', 'NBILL_TEN_YEARLY');
##########
INSERT INTO `#__nbill_xref_pay_frequency` (`code`, `description`) VALUES ('AA', 'NBILL_ONE_OFF');
##########
INSERT INTO `#__nbill_xref_pay_frequency` (`code`, `description`) VALUES ('XX', 'NBILL_NOT_APPLICABLE');
##########
INSERT INTO `#__nbill_xref_payment_method` (`code`, `description`) VALUES ('BB', 'NBILL_CHEQUE');
##########
INSERT INTO `#__nbill_xref_payment_method` (`code`, `description`) VALUES ('DD', 'NBILL_DIRECT_DEBIT');
##########
INSERT INTO `#__nbill_xref_payment_method` (`code`, `description`) VALUES ('FF', 'NBILL_STANDING_ORDER');
##########
INSERT INTO `#__nbill_xref_payment_method` (`code`, `description`) VALUES ('EE', 'NBILL_BANK_TRANSFER');
##########
INSERT INTO `#__nbill_xref_payment_method` (`code`, `description`) VALUES ('CC', 'NBILL_CREDIT_CARD');
##########
INSERT INTO `#__nbill_xref_payment_method` (`code`, `description`) VALUES ('GG', 'NBILL_ONLINE_AGENCY');
##########
INSERT INTO `#__nbill_xref_payment_method` (`code`, `description`) VALUES ('AA', 'NBILL_CASH');
##########
INSERT INTO `#__nbill_xref_payment_method` (`code`, `description`) VALUES ('XX', 'NBILL_OTHER');
##########
INSERT INTO `#__nbill_xref_plan_type` (`code`, `description`) VALUES ('AA', 'NBILL_UP_FRONT');
##########
INSERT INTO `#__nbill_xref_plan_type` (`code`, `description`) VALUES ('BB', 'NBILL_INSTALLMENTS');
##########
INSERT INTO `#__nbill_xref_plan_type` (`code`, `description`) VALUES ('CC', 'NBILL_DEPOSIT_AND_FINAL');
##########
INSERT INTO `#__nbill_xref_plan_type` (`code`, `description`) VALUES ('DD', 'NBILL_DEPOSIT_AND_INSTALLMENTS');
##########
INSERT INTO `#__nbill_xref_plan_type` (`code`, `description`) VALUES ('DX', 'NBILL_DEPOSIT_THEN_USER_CONTROLLED');
##########
INSERT INTO `#__nbill_xref_plan_type` (`code`, `description`) VALUES ('EE', 'NBILL_DEFERRED_AND_FINAL');
##########
INSERT INTO `#__nbill_xref_plan_type` (`code`, `description`) VALUES ('FF', 'NBILL_USER_CONTROLLED');
##########
INSERT INTO `#__nbill_xref_reminder_type` (`code`, `description`) VALUES ('AA', 'NBILL_REMINDER_PAYMENT_DUE');
##########
INSERT INTO `#__nbill_xref_reminder_type` (`code`, `description`) VALUES ('BB', 'NBILL_REMINDER_ORDER_EXPIRY');
##########
INSERT INTO `#__nbill_xref_reminder_type` (`code`, `description`) VALUES ('CC', 'NBILL_REMINDER_RENEWAL_DUE');
##########
INSERT INTO `#__nbill_xref_reminder_type` (`code`, `description`) VALUES ('DD', 'NBILL_REMINDER_INVOICE_OVERDUE');
##########
INSERT INTO `#__nbill_xref_reminder_type` (`code`, `description`) VALUES ('XX', 'NBILL_REMINDER_USER_DEFINED');
##########
