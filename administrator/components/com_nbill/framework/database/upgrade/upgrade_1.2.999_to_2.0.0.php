<?php
/**
* Upgrade script from version 1.2.6 to 2.0.0
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* Performs any data manipulation required when upgrading (structural changes are handled automatically by comparing the schema files with the database)
*/
function upgrade_1_2_999_to_2_0_0()
{
    $task_name = nbf_common::get_param($_REQUEST, 'taskname');
    $pointer = nbf_common::get_param($_REQUEST, 'pointer');
    $complete = false;
    $message = "";

    $nb_database = nbf_cms::$interop->database;
    $nb_database->skip_compat_processing = true;

    switch ($task_name)
    {
        case 'start':
        case '':
            if ($pointer <= 0)
            {
                //Clear down any existing data in nBill 2.0 tables
                $sql = "SELECT license_key FROM #__nbill_license WHERE id = 1";
                $nb_database->setQuery($sql);
                $license_key = $nb_database->loadResult();
                $nb_database->delete_tables();
                include_once(nbf_cms::$interop->nbill_admin_base_path . "/install.new.php");
                new_db_install();
                $sql = "UPDATE #__nbill_license SET license_key = '$license_key' WHERE id = 1";
                $nb_database->setQuery($sql);
                $nb_database->query();
                $sql = "REPLACE INTO `#__nbill_display_options` (`name`, `value`) SELECT `name`, `value` FROM `#__inv_display_options`;";
                $nb_database->setQuery($sql);
                $nb_database->query();
            }

            //Copy data from nBill 1.x tables
            $sql = array();
            $sql[] = "INSERT INTO `#__nbill_account_expiry` (`user_id`, `expiry_date`, `expiry_level`, `redirect`, `order_id`) SELECT `user_id`, `expiry_date`, `expiry_level`, `redirect`, `order_id` FROM `#__inv_account_expiry`;";
            $sql[] = "INSERT INTO `#__nbill_additional_links` (`ordering`, `url`, `text`, `description`) SELECT `ordering`, `url`, `text`, `description` FROM `#__inv_additional_links`;";
            $sql[] = "INSERT INTO `#__nbill_shipping` (`id`,`vendor_id`,`country`,`code`,`service`,`is_taxable`,`tax_rate_if_different`,`is_fixed_per_invoice`,`nominal_ledger_code`,`parcel_tracking_url`) SELECT `id`,`vendor_id`,`country`,`code`,`service`,`is_taxable`,`tax_rate_if_different`,`is_fixed_per_invoice`,`nominal_ledger_code`,`parcel_tracking_url` FROM `#__inv_carriage`;";
            $sql[] = "INSERT INTO `#__nbill_shipping_price` (`vendor_id`, `shipping_id`, `currency_code`, `net_price_per_unit`) SELECT `vendor_id`, `carriage_id`, `currency_code`, `net_price_per_unit` FROM `#__inv_carriage_price`;";
            $sql[] = "INSERT INTO `#__nbill_entity` (`id`, `primary_contact_id`, `is_client`, `is_supplier`, `reference`, `company_name`, `address_1`, `address_2`, `address_3`, `town`, `state`, `country`, `postcode`, `website_url`, `default_currency`, `tax_zone`, `tax_exemption_code`, `notes`, `last_updated`, custom_fields) SELECT `id`, `id` AS `primary_contact_id`, 1 AS `is_client`, 0 AS `is_supplier`, `client_reference`, `company_name`, `address_1`, `address_2`, `address_3`, `town`, `state`, `country`, `postcode`, `website_url`, `default_currency`, `tax_zone`, `tax_exemption_code`, `notes`, " . nbf_common::nb_time() . " AS last_updated, '' AS custom_fields FROM `#__inv_client`;";
            $sql[] = "INSERT INTO `#__nbill_contact` (`id`, `user_id`, `first_name`, `last_name`, `address_1`, `address_2`, `address_3`, `town`, `state`, `country`, `postcode`, `email_address`, `telephone`, `telephone_2`, `mobile`, `fax`, `notes`, `last_updated`, custom_fields) SELECT `id`, `user_id`, SUBSTRING_INDEX(`contact_name`, ' ', 1), SUBSTRING(`contact_name`, LENGTH(SUBSTRING_INDEX(`contact_name`, ' ', 1)) + 2), `address_1`, `address_2`, `address_3`, `town`, `state`, `country`, `postcode`, `email_address`, `telephone`, `telephone_2`, `mobile`, `fax`, `notes`, " . nbf_common::nb_time() . " AS last_updated, '' AS custom_fields FROM `#__inv_client`;";
            $sql[] = "REPLACE INTO `#__nbill_configuration` (`id`, `error_email`, `date_format`, `select_users_from_list`, `cron_auth_token`, `version_auto_check`, `auto_update`, `default_start_date`, `switch_to_ssl`) SELECT `id`, `error_email`, `date_format`, `select_users_from_list`, `cron_auth_token`, `version_auto_check`, `auto_update`, `default_start_date`, `switch_to_ssl` FROM `#__inv_configuration`;";
            $sql[] = "REPLACE INTO `#__nbill_currency` (`id`, `code`, `description`, `symbol`) SELECT `id`, `code`, `description`, `symbol` FROM `#__inv_currency`;";
            $sql[] = "INSERT INTO `#__nbill_discounts` (`id`, `vendor_id`, `discount_name`, `display_name`, `time_limited`, `start_date`, `end_date`, `global`, `shipping_discount`, `logged_in_only`, `percentage`, `amount`, `exclusive`, `priority`, `voucher`, `recurring`, `add_to_renewals`, `available`, `unavailable_when_used`, `prerequisite_products`, `disqualifying_products`, `notes`) SELECT `id`, `vendor_id`, `discount_name`, `display_name`, `time_limited`, `start_date`, `end_date`, `global`, `carriage_discount`, IF(`user_level` = 0, 0, 1) AS `logged_in_only`, `percentage`, `amount`, `exclusive`, `priority`, `voucher`, `recurring`, `add_to_renewals`, `available`, `unavailable_when_used`, `prerequisite_products`, `disqualifying_products`, `notes` FROM `#__inv_discounts`;";
            $sql[] = "INSERT INTO `#__nbill_discount_currency_amount` (`discount_id`, `currency`, `amount`, `min_order_value`) SELECT `discount_id`, `currency`, `amount`, `min_order_value` FROM `#__inv_discount_currency_amount`;";
            $sql[] = "INSERT INTO `#__nbill_transaction` (`id`, `transaction_type`, `transaction_no`, `vendor_id`, `document_ids`, `entity_id`, `name`, `address`, `tax_reference`, `for`, `method`, `currency`, `amount`, `date`, `tax_rate_1`, `tax_amount_1`, `tax_rate_2`, `tax_amount_2`, `tax_rate_3`, `tax_amount_3`, `reference`, `notes`, `no_summary`) SELECT `id`, 'EX' AS `transaction_type`, `payment_no`, `vendor_id`, `invoice_ids`, IF(`supplier_id` <= 0,`supplier_id`,`supplier_id` + (SELECT id FROM #__inv_client ORDER BY id DESC LIMIT 1)) AS `supplier_id`, `supplier_name`, `payee_address`, `tax_reference`, `for`, `method`, `currency`, `amount`, `date`, `tax_rate_1`, `tax_amount_1`, `tax_rate_2`, `tax_amount_2`, `tax_rate_3`, `tax_amount_3`, `reference`, `notes`, `no_summary` FROM `#__inv_expenditure`;";
            $sql[] = "INSERT INTO `#__nbill_transaction_ledger` (`id`, `vendor_id`, `transaction_id`, `nominal_ledger_code`, `currency`, `gross_amount`) SELECT `id`, `vendor_id`, `expenditure_id`, TRIM(`nominal_ledger_code`), `currency`, `amount` FROM `#__inv_expenditure_ledger` WHERE #__inv_expenditure_ledger.expenditure_id != 0;";
            $sql[] = "INSERT INTO `#__nbill_extensions` (`id`, `extension_type`, `extension_name`, `extension_title`, `extension_description`, `extension_date`, `date_installed`, `version`, `copyright`, `author_name`, `author_email`, `author_website`, `file_path_admin`, `file_path_frontend`, `setup_filename`, `gateway_id`) SELECT `id`, `extension_type`, `extension_name`, `extension_title`, `extension_description`, `extension_date`, `date_installed`, `version`, `copyright`, `author_name`, `author_email`, `author_website`, `file_path_admin`, `file_path_frontend`, `setup_filename`, `gateway_id` FROM `#__inv_extensions` WHERE `extension_type` = 'gateway';";
            $sql[] = "INSERT INTO `#__nbill_gateway_tx` (`id`, `pending_order_id`, `document_ids`, `net_amount`, `tax_amount`, `shipping_amount`, `shipping_tax_amount`, `user_ip`, `vendor_id`, `form_id`, `other_params`, `turn_on_auto_renew`, `last_updated`) SELECT `id`, `pending_order_id`, `invoice_ids`, `net_amount`, `tax_amount`, `carriage_amount`, `carriage_tax_amount`, `user_ip`, `vendor_id`, `form_id`, `other_params`, `turn_on_auto_renew`, " . nbf_common::nb_time() . " AS last_updated FROM `#__inv_gateway_tx`;";
            $sql[] = "INSERT INTO `#__nbill_transaction` (`id`, `transaction_type`, `transaction_no`, `vendor_id`, `document_ids`, `name`, `for`, `method`, `currency`, `amount`, `tax_rate_1`, `tax_amount_1`, `tax_rate_2`, `tax_amount_2`, `tax_rate_3`, `tax_amount_3`, `date`, `reference`, `notes`, `no_summary`, `g_tx_id`) SELECT #__inv_income.`id` + IF((SELECT id FROM #__inv_expenditure ORDER BY id DESC LIMIT 1), (SELECT id FROM #__inv_expenditure ORDER BY id DESC LIMIT 1), 0) AS `id`, 'IN' AS `transaction_type`, `receipt_no`, `vendor_id`, `invoice_ids`, `received_from`, `for`, `method`, `currency`, `amount`, `tax_rate_1`, `tax_amount_1`, `tax_rate_2`, `tax_amount_2`, `tax_rate_3`, `tax_amount_3`, `date`, `reference`, `notes`, `no_summary`, `g_tx_id` FROM `#__inv_income`;";
            $sql[] = "INSERT INTO `#__nbill_transaction_ledger` (`id`, `vendor_id`, `transaction_id`, `nominal_ledger_code`, `currency`, `gross_amount`) SELECT `id` + IF((SELECT id FROM #__inv_expenditure_ledger ORDER BY id DESC LIMIT 1), (SELECT id FROM #__inv_expenditure_ledger ORDER BY id DESC LIMIT 1), 0), `vendor_id`, `income_id` + IF((SELECT id FROM #__inv_expenditure ORDER BY id DESC LIMIT 1), (SELECT id FROM #__inv_expenditure ORDER BY id DESC LIMIT 1), 0), TRIM(`nominal_ledger_code`), `currency`, `amount` FROM `#__inv_income_ledger` WHERE #__inv_income_ledger.income_id != 0;";
            $sql[] = "INSERT INTO `#__nbill_document` (`id`, `vendor_id`, `entity_id`, `document_no`, `vendor_name`, `vendor_address`, `billing_name`, `billing_address`, `reference`, `document_date`, `tax_abbreviation`, `tax_desc`, `tax_no`, `tax_exemption_code`, `currency`, `total_net`, `total_tax`, `total_shipping`, `total_shipping_tax`, `total_gross`, `payment_instructions`, `small_print`, `paid_in_full`, `partial_payment`, `refunded_in_full`, `partial_refund`, `notes`, `email_sent`, `written_off`, `date_written_off`, `claim_tax_back`, `document_type`, `show_invoice_paylink`, quote_intro, correspondence, uploaded_files) SELECT `id`, `vendor_id`, `client_id`, `invoice_no`, `vendor_name`, `vendor_address`, `billing_name`, `billing_address`, `reference`, `invoice_date`, `tax_abbreviation`, `tax_desc`, `tax_no`, `tax_exemption_code`, `currency`, `total_net`, `total_tax`, `total_carriage`, `total_carriage_tax`, `total_gross`, `payment_instructions`, `small_print`, `paid_in_full`, `partial_payment`, `refunded_in_full`, `partial_refund`, `notes`, `email_sent`, `written_off`, `date_written_off`, `claim_tax_back`, IF(`is_credit_note`, 'CR', 'IN') AS `document_type`, `show_invoice_paylink`, '' AS quote_intro, '' AS correspondence, '' AS uploaded_files FROM `#__inv_invoice`;";
            $sql[] = "INSERT INTO `#__nbill_document_items` (`id`, `vendor_id`, `document_id`, `entity_id`, `nominal_ledger_code`, `product_description`, `net_price_per_unit`, `no_of_units`, `discount_amount`, `discount_description`, `net_price_for_item`, `tax_for_item`, `shipping_id`, `shipping_for_item`, `tax_for_shipping`, `gross_price_for_item`, `product_code`, detailed_description) SELECT `id`, `vendor_id`, `invoice_id`, `client_id`, `nominal_ledger_code`, `product_description`, `net_price_per_unit`, `no_of_units`, `discount_amount`, `discount_description`, `net_price_for_item`, `tax_for_item`, `carriage_id`, `carriage_for_item`, `tax_for_carriage`, `gross_price_for_item`, `product_code`, '' AS detailed_description FROM `#__inv_invoice_items`;";
            $sql[] = "INSERT IGNORE INTO `#__nbill_menu` (`id`, `parent_id`, `ordering`, `text`, `description`, `image`, `url`, `published`) SELECT `id`, `parent_id`, `ordering`, `text`, `description`, `image`, `url`, `published` FROM `#__inv_menu`;";
            $sql[] = "INSERT INTO `#__nbill_nominal_ledger` (`id`, `vendor_id`, `code`, `description`) SELECT `id`, `vendor_id`, `code`, `description` FROM `#__inv_nominal_ledger`;";
            $sql[] = "INSERT INTO `#__nbill_orders` (`id`, `order_no`, `vendor_id`, `client_id`, `product_id`, `product_name`, `net_price`, `total_tax_amount`, `quantity`, `relating_to`, `shipping_id`, `shipping_service`, `total_shipping_price`, `total_shipping_tax`, `is_online`, `tax_exemption_code`, `start_date`, `payment_frequency`, `auto_renew`, `currency`, `last_due_date`, `next_due_date`, `unique_invoice`, `cancellation_reason`, `order_status`, `expiry_date`, `cancellation_date`, `notes`, `gateway_txn_id`, `auto_create_invoice`, `auto_create_income`, `discount_voucher`, `form_field_values`, `form_total_order_value`, `show_invoice_paylink`, `parcel_tracking_id`, uploaded_files) SELECT `id`, `order_no`, `vendor_id`, `client_id`, `product_id`, `product_name`, `net_price`, `total_tax_amount`, `quantity`, `relating_to`, `carriage_id`, `carriage_service`, `total_carriage_price`, `total_carriage_tax`, `is_online`, `tax_exemption_code`, `start_date`, `payment_frequency`, `auto_renew`, `currency`, `last_due_date`, `next_due_date`, `unique_invoice`, `cancellation_reason`, `order_status`, `expiry_date`, `cancellation_date`, `notes`, `gateway_txn_id`, `auto_create_invoice`, `auto_create_income`, `discount_voucher`, `form_field_values`, `form_total_order_value`, `show_invoice_paylink`, `parcel_tracking_id`, '' AS uploaded_files FROM `#__inv_orders`;";
            $sql[] = "INSERT INTO `#__nbill_orders_discounts` (`order_id`, `discount_id`, `vendor_id`, `ordering`) SELECT `order_id`, `discount_id`, `vendor_id`, `ordering` FROM `#__inv_orders_discounts`;";
            $sql[] = "INSERT INTO `#__nbill_orders_document` (`order_id`, `document_item_id`, `document_id`) SELECT `order_id`, `invoice_item_id`, `invoice_id` FROM `#__inv_orders_invoice`;";
            $sql[] = "INSERT INTO `#__nbill_order_form` (`id`, `vendor_id`, `published`, `title`, `logged_in_users_only`, `prerequisite_products`, `disqualifying_products`, `email_confirmation_to_client`, `email_admin`, `email_admin_pending`, `admin_email_to`, `validation_code`, `process_code`, `pre_calculate_code`, post_calculate_code, submit_code, `auto_create_orders`, `auto_create_user`, `use_email_address`, `auto_create_invoice`, `auto_create_income`, `relating_to`, `shipping_id`, `tax_exemption_code`, `payment_frequency`, `currency`, `unique_invoice`, `auto_renew`, `expiry_date`, `expire_after`, `payment_gateway`, `pending_until_paid`, `discount_voucher_code`, `order_creation_code`, `auto_handle_shipping`, `javascript_functions`, `upload_path`, `allowed_types`, `max_upload_size`, `attach_to_email`, `thank_you_redirect`, `thank_you_message`, `ordering`, form_unavailable_message, after_processing_code) SELECT `id`, `vendor_id`, `published`, `title`, `logged_in_users_only`, `prerequisite_products`, `disqualifying_products`, `email_confirmation_to_client`, `email_admin`, `email_admin_pending`, `admin_email_to`, `validation_code`, `process_code`, CONCAT(`pre_submit_code`, CONCAT('\n', `submit_code`)), '' AS post_calculate_code, '' AS submit_code, `auto_create_orders`, `auto_create_user`, `use_email_address`, `auto_create_invoice`, `auto_create_income`, `relating_to`, `carriage_id`, `tax_exemption_code`, `payment_frequency`, `currency`, `unique_invoice`, `auto_renew`, `expiry_date`, `expire_after`, `payment_gateway`, `pending_until_paid`, `discount_voucher_code`, `order_creation_code`, `auto_handle_carriage`, `javascript_functions`, `upload_path`, `allowed_types`, `max_upload_size`, `attach_to_email`, `thank_you_redirect`, `thank_you_message`, `ordering`, '' AS form_unavailable_message, '' AS after_processing_code FROM `#__inv_order_form`;";
            $sql[] = "INSERT INTO `#__nbill_order_form_fields_options` (`id`, `form_id`, `field_id`, `ordering`, `option_value`, `option_description`, `related_product_cat`, `related_product`, `related_product_quantity`) SELECT `id`, `form_id`, `field_id`, `ordering`, `option_value`, `option_description`, `related_product_cat`, `related_product`, `related_product_quantity` FROM `#__inv_order_form_fields_options`;";
            $sql[] = "DELETE FROM #__nbill_payment_gateway";
            $sql[] = "INSERT INTO `#__nbill_payment_gateway` (`id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering`) SELECT `id`, `gateway_id`, `g_key`, `g_value`, `label`, `help_text`, `required`, `admin_can_edit`, `ordering` FROM `#__inv_payment_gateway`;";
            $sql[] = "DELETE FROM #__nbill_payment_gateway_config";
            $sql[] = "INSERT INTO #__nbill_payment_gateway_config (gateway_id, display_name, published, ordering) VALUES ('offline', 'NBILL_ARRANGE_OFFLINE', 0, 0)";
            $sql[] = "REPLACE INTO `#__nbill_payment_gateway_config` (`gateway_id`, `display_name`, `published`) SELECT `gateway_id`, `display_name`, `published` FROM `#__inv_payment_gateway_config`;";
            $query = "SELECT gateway_id FROM #__inv_payment_gateway_config ORDER BY gateway_id";
            $nb_database->setQuery($query);
            $gateways = $nb_database->loadObjectList();
            $ordering = 1;
            foreach ($gateways as $gateway)
            {
                $sql[] = "UPDATE #__nbill_payment_gateway_config SET ordering = $ordering WHERE gateway_id = '" . $gateway->gateway_id . "'";
                $ordering++;
            }
            $sql[] = "INSERT INTO `#__nbill_pending_orders` (`id`, `timestamp`, `vendor_id`, `form_id`, `user_id`, `client_id`, `existing_client`, `tax_exemption_code`, `relating_to`, `shipping_id`, `payment_frequency`, `currency`, `unique_invoice`, `auto_renew`, `expiry_date`, `normal_tax_rate`, `total_gross`, `regular_total_gross`, `orders`, `posted_values`, `auto_email_invoice`, `discount_voucher_code`, `client_name`, `ext_order_activation_code`) SELECT `id`, `timestamp`, `vendor_id`, `form_id`, `user_id`, `client_id`, `existing_client`, `tax_exemption_code`, `relating_to`, `carriage_id`, `payment_frequency`, `currency`, `unique_invoice`, `auto_renew`, `expiry_date`, `normal_tax_rate`, `total_gross`, `regular_total_gross`, `orders`, `posted_values`, `auto_email_invoice`, `discount_voucher_code`, `client_name`, '' AS ext_order_activation_code FROM `#__inv_pending_orders`;";
            $sql[] = "INSERT INTO `#__nbill_product` (`id`, `vendor_id`, `category`, `product_code`, `nominal_ledger_code`, `name`, `description`, `image`, `is_freebie`, `is_taxable`, `requires_shipping`, `shipping_services`, `shipping_units`, `auto_fulfil_orders`, `existing_clients_only`, `is_downloadable`, `download_location`, `no_of_days_available`, `download_link_text`, `download_location_2`, `download_link_text_2`, `download_location_3`, `download_link_text_3`, `email_downloads`, `email_downloads_message`, `is_sub`, `user_group`, `expiry_level`, `expiry_redirect`, `allow_global_discounts`) SELECT `id`, `vendor_id`, `category`, `product_code`, `nominal_ledger_code`, `name`, `description`, `image`, `is_freebie`, `is_taxable`, `requires_carriage`, `carriage_services`, `carriage_units`, `auto_fulfil_orders`, `existing_clients_only`, `is_downloadable`, `download_location`, `no_of_days_available`, `download_link_text`, `download_location_2`, `download_link_text_2`, `download_location_3`, `download_link_text_3`, `email_downloads`, `email_downloads_message`, `is_sub`, `access_level`, `expiry_level`, `expiry_redirect`, `allow_global_discounts` FROM `#__inv_product`;";
            $sql[] = "REPLACE INTO `#__nbill_product_category` (`id`, `vendor_id`, `parent_id`, `ordering`, `name`, `description`) SELECT `id`, `vendor_id`, `parent_id`, `ordering`, `name`, `description` FROM `#__inv_product_category`;";
            $sql[] = "INSERT INTO `#__nbill_product_discount` (`product_id`,`discount_id`,`vendor_id`,`priority`,`quantity_required`,`multiply`,`offset`) SELECT `product_id`,`discount_id`,`vendor_id`,`priority`,`quantity_required`,`multiply`,`offset` FROM `#__inv_product_discount`;";
            $sql[] = "INSERT INTO `#__nbill_product_price` (`vendor_id`, `product_id`, `currency_code`, `net_price_setup_fee`, `net_price_one_off`, `net_price_weekly`, `net_price_four_weekly`, `net_price_monthly`, `net_price_quarterly`, `net_price_semi_annually`, `net_price_annually`, `net_price_biannually`, `net_price_five_years`, `net_price_ten_years`) SELECT `vendor_id`, `product_id`, `currency_code`, `net_price_setup_fee`, `net_price_one_off`, `net_price_weekly`, `net_price_four_weekly`, `net_price_monthly`, `net_price_quarterly`, `net_price_semi_annually`, `net_price_annually`, `net_price_biannually`, `net_price_five_years`, `net_price_ten_years` FROM `#__inv_product_price`;";
            $sql[] = "INSERT INTO `#__nbill_reminders` (`id`, `vendor_id`, `reminder_name`, `reminder_type`, `active`, `admin`, `starting_from`, `number_of_units`, `units`, `send_after`, `email_text`, `client_id`, `filter`) SELECT `id`, `vendor_id`, `reminder_name`, `reminder_type`, `active`, `admin`, `starting_from`, `number_of_units`, `units`, `send_after`, `email_text`, `client_id`, `filter` FROM `#__inv_reminders`;";
            $sql[] = "INSERT INTO `#__nbill_entity` (`id`, `primary_contact_id`, `is_client`, `is_supplier`, `reference`, `company_name`, `address_1`, `address_2`, `address_3`, `town`, `state`, `country`, `postcode`, `website_url`, `default_currency`, `tax_exemption_code`, `notes`) SELECT #__inv_supplier.`id` + (SELECT id FROM #__inv_client ORDER BY id DESC LIMIT 1) AS `id`, #__inv_supplier.`id` + (SELECT id FROM #__inv_client ORDER BY id DESC LIMIT 1) AS `primary_contact_id`, 0 AS `is_client`, 1 AS `is_supplier`, `reference`, `company_name`, `address_1`, `address_2`, `address_3`, `town`, `state`, `country`, `postcode`, `website_url`, `default_currency`, `tax_reference`, `notes` FROM `#__inv_supplier`;";
            $sql[] = "INSERT INTO `#__nbill_contact` (`id`, `user_id`, `first_name`, `last_name`, `address_1`, `address_2`, `address_3`, `town`, `state`, `country`, `postcode`, `email_address`, `telephone`, `telephone_2`, `mobile`, `fax`, `notes`) SELECT #__inv_supplier.`id` + (SELECT id FROM #__inv_client ORDER BY id DESC LIMIT 1) AS `id`, 0 AS `user_id`, SUBSTRING_INDEX(`contact_name`, ' ', 1), SUBSTRING(`contact_name`, LENGTH(SUBSTRING_INDEX(`contact_name`, ' ', 1)) + 2), `address_1`, `address_2`, `address_3`, `town`, `state`, `country`, `postcode`, `email_address`, `telephone`, `telephone_2`, `mobile`, `fax`, `notes` FROM `#__inv_supplier`;";
            $sql[] = "INSERT INTO `#__nbill_tax` (`id`, `vendor_id`, `country_code`, `tax_zone`, `tax_name`, `tax_abbreviation`, `tax_reference_desc`, `tax_rate`, `online_exempt`, `payment_instructions`, `small_print`, `exempt_with_ref_no`) SELECT `id`, `vendor_id`, `country_code`, `tax_zone`, `tax_name`, `tax_abbreviation`, `tax_reference_desc`, `tax_rate`, `online_exempt`, `payment_instructions`, `small_print`, `exempt_with_ref_no` FROM `#__inv_tax`;";
            $sql[] = "REPLACE INTO `#__nbill_vendor` (`id`, `vendor_name`, `vendor_address`, `vendor_country`, `vendor_currency`, `next_invoice_no`, `invoice_no_locked`, `next_order_no`, `order_no_locked`, `next_receipt_no`, `receipt_no_locked`, `next_payment_no`, `payment_no_locked`, `next_credit_no`, `credit_no_locked`, `payment_instructions`, `small_print`, `credit_small_print`, `show_remittance`, `show_paylink`, `admin_email`, `paper_size`, `default_gateway`, `auto_create_income`, `suppress_receipt_nos`, `suppress_payment_nos`, `suppress_generation_buttons`, `use_master_db`, `master_host`, `master_username`, `master_password`, `master_dbname`, `master_table_prefix`, `master_vendor_id`) SELECT `id`, `vendor_name`, `vendor_address`, `vendor_country`, `vendor_currency`, `next_invoice_no`, `invoice_no_locked`, `next_order_no`, `order_no_locked`, `next_receipt_no`, `receipt_no_locked`, `next_payment_no`, `payment_no_locked`, `next_credit_no`, `credit_no_locked`, `payment_instructions`, `small_print`, `credit_small_print`, `show_remittance`, `show_paylink`, `admin_email`, `paper_size`, `default_gateway`, `auto_create_income`, `suppress_receipt_nos`, `suppress_payment_nos`, `suppress_generation_buttons`, `use_master_db`, `master_host`, `master_username`, `master_password`, `master_dbname`, `master_table_prefix`, `master_vendor_id` FROM `#__inv_vendor`;";
            $query = "SELECT vendor_id, tax_reference_no FROM #__inv_tax WHERE tax_reference_no != '' GROUP BY vendor_id";
            $nb_database->setQuery($query);
            $tax_refs = $nb_database->loadObjectList();
            if ($tax_refs) {
                foreach ($tax_refs as $tax_ref)
                {
                    $sql[] = "UPDATE #__nbill_vendor SET tax_reference_no = '" . $tax_ref->tax_reference_no . "' WHERE id = " . intval($tax_ref->vendor_id);
                }
            }
            $query = "SELECT #__inv_tax.id, #__inv_tax.payment_instructions, #__inv_tax.small_print, #__inv_vendor.payment_instructions AS vendor_pay_inst, #__inv_vendor.small_print AS vendor_small_print FROM #__inv_tax INNER JOIN #__inv_vendor ON #__inv_tax.vendor_id = #__inv_vendor.id";
            $nb_database->setQuery($query);
            $tax_refs = $nb_database->loadObjectList();
            if ($tax_refs) {
                foreach ($tax_refs as $tax_ref)
                {
                    if (str_replace(' ', '', strip_tags($tax_ref->payment_instructions)) == str_replace(' ', '', strip_tags($tax_ref->vendor_pay_inst))) {
                        $sql[] = "UPDATE #__nbill_tax SET payment_instructions = NULL where id = " . intval($tax_ref->id);
                    }
                    if (str_replace(' ', '', strip_tags($tax_ref->small_print)) == str_replace(' ', '', strip_tags($tax_ref->vendor_small_print))) {
                        $sql[] = "UPDATE #__nbill_tax SET small_print = NULL where id = " . intval($tax_ref->id);
                    }
                }
            }

            $sql[] = "REPLACE INTO `#__nbill_xref_country_codes` (`id`, `code`, `description`) SELECT `id`, `code`, `description` FROM `#__inv_xref_country_codes`;";
            $sql[] = "REPLACE INTO `#__nbill_xref_default_start_date` (`code`, `description`) SELECT `code`, `description` FROM `#__inv_xref_default_start_date`;";
            $sql[] = "REPLACE INTO `#__nbill_xref_email_invoice` (`code`, `description`) SELECT `code`, `description` FROM `#__inv_xref_email_invoice`;";
            $sql[] = "REPLACE INTO `#__nbill_xref_eu_country_codes` (`code`, `description`) SELECT `code`, `description` FROM `#__inv_xref_eu_country_codes`;";
            $sql[] = "REPLACE INTO `#__nbill_xref_field_type` (`code`, `description`) SELECT `code`, `description` FROM `#__inv_xref_field_type`;";
            $sql[] = "REPLACE INTO `#__nbill_xref_order_status` (`code`, `description`) SELECT `code`, `description` FROM `#__inv_xref_order_status`;";
            $sql[] = "REPLACE INTO `#__nbill_xref_payment_method` (`code`, `description`) SELECT `code`, `description` FROM `#__inv_xref_payment_method`;";
            $sql[] = "REPLACE INTO `#__nbill_xref_pay_frequency` (`code`, `description`) SELECT `code`, `description` FROM `#__inv_xref_pay_frequency`;";
            $sql[] = "REPLACE INTO `#__nbill_xref_reminder_type` (`code`, `description`) SELECT `code`, `description` FROM `#__inv_xref_reminder_type`;";
            $sql[] = "INSERT INTO `#__nbill_entity_contact` (`entity_id`, `contact_id`, `email_invoice_option`, `reminder_emails`) SELECT #__nbill_entity.`id`, #__nbill_entity.`primary_contact_id`, #__inv_client.email_invoice_option, #__inv_client.reminder_emails FROM #__nbill_entity LEFT JOIN #__inv_client ON #__nbill_entity.id = #__inv_client.id;";

            //Convert
            $sql[] = "UPDATE `#__nbill_menu` set text = replace(text, 'INV_', 'NBILL_'), description = replace(description, 'INV_', 'NBILL_'), image = replace(image, 'INV_', 'NBILL_'), url = replace(url, 'INV_', 'NBILL_');";
            $sql[] = "UPDATE `#__nbill_menu` set image = replace(image, '../components/com_netinvoice', '[NBILL_FE]'), url = replace(url, 'index2.php?option=com_netinvoice', '[NBILL_ADMIN]');";
            $sql[] = "UPDATE `#__nbill_xref_default_start_date` set description = replace(description, 'INV_', 'NBILL_');";
            $sql[] = "UPDATE `#__nbill_xref_email_invoice` set description = replace(description, 'INV_', 'NBILL_');";
            $sql[] = "UPDATE `#__nbill_xref_field_type` set description = replace(description, 'INV_', 'NBILL_');";
            $sql[] = "UPDATE `#__nbill_xref_field_type` SET description = 'NBILL_DOMAIN_LOOKUP' WHERE description = 'NBILL_JWHOIS_LOOKUP'";
            $sql[] = "UPDATE `#__nbill_xref_order_status` set description = replace(description, 'INV_', 'NBILL_');";
            $sql[] = "UPDATE `#__nbill_xref_payment_method` set description = replace(description, 'INV_', 'NBILL_');";
            $sql[] = "UPDATE `#__nbill_xref_pay_frequency` set description = replace(description, 'INV_', 'NBILL_');";
            $sql[] = "UPDATE `#__nbill_xref_reminder_type` set description = replace(description, 'INV_', 'NBILL_');";
            $sql[] = "UPDATE `#__nbill_payment_gateway` set g_value = replace(g_value, 'INV_PAYPAL', 'NBILL_PAYPAL'), label = replace(label, 'INV_PAYPAL', 'NBILL_PAYPAL'), help_text = replace(help_text, 'INV_PAYPAL', 'NBILL_PAYPAL');";
            $sql[] = "UPDATE `#__nbill_payment_gateway` set g_value = replace(g_value, 'index.php?option=com_netinvoice', '" . nbf_cms::$interop->site_page_prefix . "') WHERE gateway_id != 'paypal';";
            $sql[] = "UPDATE `#__nbill_payment_gateway` set g_value = replace(g_value, 'index.php?option=com_netinvoice', '[NBILL_FE_PAGE_PREFIX]') WHERE gateway_id = 'paypal';";
            $sql[] = "UPDATE `#__nbill_configuration` SET email_invoice_option = 'EE' WHERE email_invoice_option = 'AB'";
            $sql[] = "UPDATE `#__nbill_entity_contact` SET email_invoice_option = 'EE' WHERE email_invoice_option = 'AB'";

            $sql = array_merge($sql, convert_forms());
            $sql = array_merge($sql, add_client_credit());

            for ($i = $pointer; $i < count($sql) && $i - $pointer < 100; $i++)
            {
                $nb_database->setQuery($sql[$i]);
                $nb_database->query();
                if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                {
                    nbf_globals::$db_errors[] = $nb_database->_errorMsg . " (SQL=" . $nb_database->_sql . ")";
                }
            }
            $pointer = $i;
            $percentage = count($sql) > 0 ? format_number(($pointer / count($sql)) * 100, 1) : 0;
            if ($pointer >= count($sql))
            {
                $pointer = 0;
                $task_name = "address";
                $percentage = 0;
            }
            break;

        case "address":
            //Load the tax records
            $sql = "SELECT #__nbill_tax.*, #__nbill_vendor.tax_reference_no FROM #__nbill_tax INNER JOIN #__nbill_vendor ON #__nbill_tax.vendor_id = #__nbill_vendor.id";
            $nb_database->setQuery($sql);
            $tax_info = $nb_database->loadObjectList();
            if (!$tax_info)
            {
                $tax_info = array();
            }
            //Load the shipping records
            $sql = "SELECT * FROM #__nbill_shipping";
            $nb_database->setQuery($sql);
            $shipping = $nb_database->loadObjectList();
            if (!$shipping)
            {
                $shipping = array();
            }

            //Update invoice and transaction tables to separate out the country from the rest of the billing address, if applicable, and store tax rate on invoice items
            $query = "SELECT code, description FROM #__nbill_xref_country_codes";
            $nb_database->setQuery($query);
            $countries = $nb_database->loadAssocList("description");

            //Get the count so we can work out the percentage
            $count_query = "SELECT COUNT(*) FROM #__nbill_document";
            $nb_database->setQuery($count_query);
            $count = intval($nb_database->loadResult());
            $count_query = "SELECT COUNT(*) FROM #__nbill_transaction WHERE #__nbill_transaction.address > ''";
            $nb_database->setQuery($count_query);
            $count += intval($nb_database->loadResult());

            //Get the records
            $query = "SELECT id, billing_address AS address, 'INV' AS record_type FROM #__nbill_document
                    UNION ALL
                    SELECT id, address, 'TX' AS record_type FROM #__nbill_transaction WHERE #__nbill_transaction.address > ''";
            $nb_database->setQuery($query);
            if ($pointer > 0)
            {
                $nb_database->_pointer = $pointer;
            }

            $more = $nb_database->loadObjectListChunked($invoices_and_txs, null, 100);
            $pointer = $nb_database->_pointer; //Remember where we are
            if ($invoices_and_txs && count($invoices_and_txs))
            {
                foreach ($invoices_and_txs as $invoice_or_tx)
                {
                    $add = explode("\n", $invoice_or_tx->address);
                    if (count($add) > 0)
                    {
                        $country_name = "";
                        $orig_country_name = $add[count($add) - 1];
                        if (array_key_exists(nbf_common::nb_strtoupper($orig_country_name), $countries))
                        {
                            $country_name = $nb_database->getEscaped($countries[nbf_common::nb_strtoupper($orig_country_name)]['code']);
                        }
                        else
                        {
                            foreach($countries as $country)
                            {
                                if ($country['code'] == nbf_common::nb_strtoupper($orig_country_name))
                                {
                                    $country_name = $orig_country_name;
                                    break;
                                }
                            }
                        }
                        if (nbf_common::nb_strlen($country_name) > 0)
                        {
                            if ($invoice_or_tx->record_type == 'INV')
                            {
                                $sql = "UPDATE #__nbill_document SET billing_country = '$country_name', billing_address = '" . $nb_database->getEscaped(str_replace("\n" . $orig_country_name, "", $invoice_or_tx->address)) . "' WHERE id = " . intval($invoice_or_tx->id);
                            }
                            else
                            {
                                $sql = "UPDATE #__nbill_transaction SET country = '$country_name', address = '" . $nb_database->getEscaped(str_replace("\n" . $orig_country_name, "", $invoice_or_tx->address)) . "' WHERE id = " . intval($invoice_or_tx->id);
                            }
                            $nb_database->setQuery($sql);
                            $nb_database->query();
                            if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                            {
                                nbf_globals::$db_errors[] = $nb_database->_errorMsg . " (SQL=" . $nb_database->_sql . ")";
                            }
                        }
                    }
                    if ($invoice_or_tx->record_type == 'INV')
                    {
                        //Work out the tax for each invoice item
                        $invoice = null;
                        $sql = "SELECT #__nbill_document.*, #__nbill_document.id AS document_id, #__nbill_xref_eu_country_codes.code AS in_eu, #__nbill_vendor.vendor_country
                                FROM #__nbill_document
                                LEFT JOIN #__nbill_xref_eu_country_codes ON #__nbill_document.billing_country = #__nbill_xref_eu_country_codes.code
                                INNER JOIN #__nbill_vendor ON #__nbill_document.vendor_id = #__nbill_vendor.id
                                WHERE #__nbill_document.id = " . $invoice_or_tx->id;
                        $nb_database->setQuery($sql);
                        $nb_database->loadObject($invoice);
                        $sql = "SELECT * FROM #__nbill_document_items WHERE document_id = " . $invoice_or_tx->id;
                        $nb_database->setQuery($sql);
                        $invoice_items = $nb_database->loadObjectList();
                        $tax_rates = array();
                        $tax_amounts = array();
                        $tax_name = "";
                        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.tax.class.php");
                        nbf_tax::get_tax_rates($invoice, $invoice_items, $shipping, $tax_info, $tax_name, $tax_rates, $tax_amounts, true);
                        foreach ($tax_rates[$invoice->document_id] as $tax_rate_key=>$tax_rate_item)
                        {
                            foreach ($invoice_items as $invoice_item)
                            {
                                if ($invoice_item->id == substr($tax_rate_key, 0, nbf_common::nb_strpos($tax_rate_key, "_")))
                                {
                                    $sql = "UPDATE #__nbill_document_items SET tax_rate_for_" . substr($tax_rate_key, nbf_common::nb_strpos($tax_rate_key, "_") + 1) . " = '$tax_rate_item' WHERE id = " . $invoice_item->id;
                                    $nb_database->setQuery($sql);
                                    $nb_database->query();
                                    if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                                    {
                                        nbf_globals::$db_errors[] = $nb_database->_errorMsg . " (SQL=" . $nb_database->_sql . ")";
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $percentage = $count > 0 ? format_number(($pointer / $count) * 100, 1) : 0;
            if (!$more)
            {
                $pointer = 0;
                $task_name = "transactions";
                $percentage = 0;
            }
            break;

        case "transactions":
            //Get the offset (difference between IDs in old table and new table = highest expenditure id)
            $sql = "SELECT id FROM #__inv_expenditure ORDER BY id DESC LIMIT 1";
            $nb_database->setQuery($sql);
            $income_id_offset = intval($nb_database->loadResult());

            //Get the count so we can work out the percentage
            $count_query = "SELECT COUNT(*) FROM #__inv_income WHERE invoice_ids != '' AND invoice_ids != '0'";
            $nb_database->setQuery($count_query);
            $count = intval($nb_database->loadResult());
            $count_query = "SELECT COUNT(*) FROM #__inv_expenditure WHERE invoice_ids != '' AND invoice_ids != '0'";
            $nb_database->setQuery($count_query);
            $count += intval($nb_database->loadResult());

            //Populate the new document_transaction table, based on the invoice_ids field in the income/expenditure tables
            $query = "SELECT id + $income_id_offset AS id, invoice_ids, date, amount, tax_amount_1, tax_rate_1, tax_amount_2, tax_rate_2, tax_amount_3, tax_rate_3 FROM #__inv_income WHERE invoice_ids != '' AND invoice_ids != '0'
                        UNION ALL
                        SELECT id, invoice_ids, date, amount, tax_amount_1, tax_rate_1, tax_amount_2, tax_rate_2, tax_amount_3, tax_rate_3 FROM #__inv_expenditure WHERE invoice_ids != '' AND invoice_ids != '0'";
            $nb_database->setQuery($query);
            $query = $nb_database->_sql; //Will need to reset it on each loop
            $more = true;
            if ($pointer > 0)
            {
                $nb_database->_pointer = $pointer;
            }

            $more = $nb_database->loadObjectListChunked($old_transactions, null, 100);
            $pointer = $nb_database->_pointer; //Remember where we are
            if ($old_transactions && count($old_transactions))
            {
                foreach ($old_transactions as $old_tx)
                {
                    //Check whether amount >= total of all invoices - if so, each invoice paid in full
                    $document_sql = "SELECT id, total_net + total_shipping AS net_amount, total_tax + total_shipping_tax AS tax_amount, total_gross, billing_address, billing_country, entity_id, tax_exemption_code FROM #__nbill_document WHERE id IN (" . $old_tx->invoice_ids . ")";
                    $nb_database->setQuery($document_sql);
                    $documents = $nb_database->loadObjectList();
                    if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                    {
                        nbf_globals::$db_errors[] = $nb_database->_errorMsg . " (SQL=" . $nb_database->_sql . ")";
                    }
                    $document_total = 0;
                    if ($documents && count($documents))
                    {
                        for($i=0; $i<count($documents); $i++)
                        {
                            //Less any amount already marked as paid
                            $existing_sql = "SELECT amount, tax_amount_1, tax_amount_2, tax_amount_3 FROM #__nbill_transaction
                                    INNER JOIN #__nbill_document_transaction ON #__nbill_transaction.id = #__nbill_document_transaction.transaction_id
                                    WHERE #__nbill_document_transaction.document_id = " . $documents[$i]->id;
                            $nb_database->setQuery($existing_sql);
                            $existing_txs = $nb_database->loadObjectList();
                            if ($existing_txs && count($existing_txs))
                            {
                                foreach ($existing_txs as $existing_tx)
                                {
                                    $existing_tax_amt = $existing_tx->tax_amount_1 + $existing_tx->tax_amount_2 + $existing_tx->tax_amount_3;
                                    $documents[$i]->net_amount -= ($existing_tx->amount - $existing_tax_amt);
                                    $documents[$i]->tax_amount -= $existing_tax_amt;
                                    $documents[$i]->total_gross -= $existing_tx->amount;
                                }
                            }
                            $document_total += $documents[$i]->total_gross;
                        }
                        $unpaid_amount = ($document_total - $old_tx->amount) / count($documents);

                        foreach ($documents as $document)
                        {
                            //Use REPLACE instead of INSERT in case we have to re-do any due to an earlier error
                            $sql = "REPLACE INTO #__nbill_document_transaction (document_id, transaction_id, date, net_amount, tax_rate_1, tax_amount_1, tax_rate_2, tax_amount_2, tax_rate_3, tax_amount_3, gross_amount) VALUES ";
                            $sql .= "(" . intval($document->id) . ", " . intval($old_tx->id) . ", " . intval($old_tx->date) . ", ";
                            $total_net = $document->net_amount;
                            $total_tax = $document->tax_amount;
                            $total_gross = $document->total_gross;
                            $tax_rate_1 = 0;
                            $tax_amount_1 = 0;
                            $tax_rate_2 = 0;
                            $tax_amount_2 = 0;
                            $tax_rate_3 = 0;
                            $tax_amount_3 = 0;
                            //Cannot rely on document record to hold the correct tax rate, so try to find the rate on the transaction item
                            $tax_amount_1 = $total_tax;
                            if (format_number(float_add($total_net, format_number(($total_net / 100) * $old_tx->tax_rate_1))) == format_number($total_gross))
                            {
                                $tax_rate_1 = $old_tx->tax_rate_1;
                            }
                            else if (format_number(float_add($total_net, format_number(($total_net / 100) * $old_tx->tax_rate_2))) == format_number($total_gross))
                            {
                                $tax_rate_1 = $old_tx->tax_rate_2;
                            }
                            else if (format_number(float_add($total_net, format_number(($total_net / 100) * $old_tx->tax_rate_3))) == format_number($total_gross))
                            {
                                $tax_rate_1 = $old_tx->tax_rate_3;
                            }
                            else
                            {
                                //Try gross to net calculations
                                if (float_cmp(format_number(($total_gross / (100 + $old_tx->tax_rate_1)) * 100), format_number($total_net)))
                                {
                                    $tax_rate_1 = $old_tx->tax_rate_1;
                                }
                                else if (float_cmp(format_number(($total_gross / (100 + $old_tx->tax_rate_2)) * 100), format_number($total_net)))
                                {
                                    $tax_rate_1 = $old_tx->tax_rate_2;
                                }
                                else if (float_cmp(format_number(($total_gross / (100 + $old_tx->tax_rate_3)) * 100), format_number($total_net)))
                                {
                                    $tax_rate_1 = $old_tx->tax_rate_3;
                                }
                                else
                                {
                                    //Default to tax rate 1 - the total is probably made up of several items which when totalled do not exactly equal the tax rate
                                    $tax_rate_1 = $old_tx->tax_rate_1;
                                }
                            }
                            if ($unpaid_amount > 0.01 || $unpaid_amount < -0.01) //Underpayment OR Overpayment
                            {
                                $total_gross = format_number($total_gross - $unpaid_amount);
                                $total_tax = format_number(($total_gross / (100 + $tax_rate_1)) * $tax_rate_1);
                                $total_net = format_number($total_gross - $total_tax);
                            }
                            if (float_cmp(float_add($old_tx->tax_amount_1, float_add($old_tx->tax_amount_2, $old_tx->tax_amount_3)), $total_tax))
                            {
                                $tax_rate_1 = $old_tx->tax_rate_1;
                                $tax_amount_1 = $old_tx->tax_amount_1;
                                $tax_rate_2 = $old_tx->tax_rate_2;
                                $tax_amount_2 = $old_tx->tax_amount_2;
                                $tax_rate_3 = $old_tx->tax_rate_3;
                                $tax_amount_3 = $old_tx->tax_amount_3;
                            }
                            if (nbf_common::nb_strlen($total_net) == 0) {$total_net = "0";}
                            if (nbf_common::nb_strlen($tax_rate_1) == 0) {$tax_rate_1 = "0";}
                            if (nbf_common::nb_strlen($tax_amount_1) == 0) {$tax_amount_1 = "0";}
                            if (nbf_common::nb_strlen($tax_rate_2) == 0) {$tax_rate_2 = "0";}
                            if (nbf_common::nb_strlen($tax_amount_2) == 0) {$tax_amount_2 = "0";}
                            if (nbf_common::nb_strlen($tax_rate_3) == 0) {$tax_rate_3 = "0";}
                            if (nbf_common::nb_strlen($tax_amount_3) == 0) {$tax_amount_3 = "0";}
                            if (nbf_common::nb_strlen($total_gross) == 0) {$total_gross = "0";}
                            $sql .= "$total_net, $tax_rate_1, $tax_amount_1, $tax_rate_2, $tax_amount_2, $tax_rate_3, $tax_amount_3, $total_gross)";

                            $tx_address_sql = "UPDATE #__nbill_transaction SET address = '" . $nb_database->getEscaped($document->billing_address) . "', country = '" . $nb_database->getEscaped($document->billing_country) . "', entity_id = " . $document->entity_id . ", tax_reference = '" . $nb_database->getEscaped($document->tax_exemption_code) . "' WHERE id = " . intval($old_tx->id);
                            $nb_database->setQuery($tx_address_sql);
                            $nb_database->query();
                            if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                            {
                                nbf_globals::$db_errors[] = $nb_database->_errorMsg . " (SQL=" . $nb_database->_sql . ")";
                            }
                            $nb_database->setQuery($sql);
                            $nb_database->query();
                            if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                            {
                                nbf_globals::$db_errors[] = $nb_database->_errorMsg . " (SQL=" . $nb_database->_sql . ")";
                            }
                        }
                    }
                }
            }
            $percentage = $count > 0 ? format_number(($pointer / $count) * 100, 1) : 0;
            if (!$more)
            {
                $pointer = 0;
                $task_name = "ledger";
                $percentage = 0;
            }
            break;

        case "ledger":
            //Get the count so we can work out the percentage
            $count_query = "SELECT COUNT(*) FROM #__nbill_transaction_ledger
                    INNER JOIN #__nbill_transaction ON #__nbill_transaction.id = #__nbill_transaction_ledger.transaction_id
                    WHERE #__nbill_transaction_ledger.gross_amount != 0";
            $nb_database->setQuery($count_query);
            $count = intval($nb_database->loadResult());

            //Work out the tax breakdown for the nominal ledger
            $query = "SELECT #__nbill_transaction_ledger.id, #__nbill_transaction.tax_rate_1, #__nbill_transaction.tax_rate_2,
                    #__nbill_transaction.tax_rate_3, #__nbill_transaction.tax_amount_1, #__nbill_transaction.tax_amount_2,
                    #__nbill_transaction.tax_amount_3, #__nbill_transaction.amount, #__nbill_transaction_ledger.gross_amount,
                    #__nbill_transaction.currency, #__nbill_transaction_ledger.transaction_id, #__nbill_transaction.transaction_type
                    FROM #__nbill_transaction_ledger
                    INNER JOIN #__nbill_transaction ON #__nbill_transaction.id = #__nbill_transaction_ledger.transaction_id
                    WHERE #__nbill_transaction_ledger.gross_amount != 0";
            $nb_database->setQuery($query);
            if ($pointer > 0)
            {
                $nb_database->_pointer = $pointer;
            }

            $more = $nb_database->loadObjectListChunked($ledger_txs, null, 100);
            $pointer = $nb_database->_pointer;
            if ($ledger_txs && count($ledger_txs))
            {
                foreach ($ledger_txs as $ledger_tx)
                {
                    $tax = null;
                    $rate = null;
                    $guess = false;
                    if ($ledger_tx->amount == $ledger_tx->gross_amount)
                    {
                        $tax = format_number($ledger_tx->tax_amount_1 + $ledger_tx->tax_amount_2 + $ledger_tx->tax_amount_3);
                        $rate = $ledger_tx->tax_rate_1;
                        if ($ledger_tx->tax_rate_2 > 0 || $ledger_tx->tax_rate_3 > 0)
                        {
                            //If more than one rate, mark it as a guess so the user can confirm
                            $guess = true;
                        }
                    }
                    else
                    {
                        //See if there is a related document item for exactly this amount
                        $sql = "SELECT #__nbill_document_items.nominal_ledger_code, #__nbill_document_items.gross_price_for_item,
                                #__nbill_document_items.tax_for_item, #__nbill_document_items.tax_for_shipping, #__nbill_document_items.tax_rate_for_item
                                FROM `#__nbill_document_items`
                                INNER JOIN #__nbill_document ON #__nbill_document.id = #__nbill_document_items.document_id
                                INNER JOIN #__nbill_document_transaction ON #__nbill_document_transaction.document_id = #__nbill_document_items.document_id
                                INNER JOIN #__nbill_transaction_ledger ON #__nbill_document_transaction.transaction_id = #__nbill_transaction_ledger.transaction_id
                                WHERE #__nbill_document.currency = '" . $ledger_tx->currency . "' AND #__nbill_transaction_ledger.id = " . $ledger_tx->id;
                        $ledger_pointer = $nb_database->_pointer; //Remember where we are
                        $nb_database->setQuery($sql);
                        $document_items = $nb_database->loadObjectList();
                        $nb_database->_pointer = $ledger_pointer; //Back to where we were
                        $nb_database->_sql = $query;
                        if ($document_items && count($document_items))
                        {
                            foreach ($document_items as $document_item)
                            {
                                if ($document_item->gross_price_for_item == $ledger_tx->gross_amount)
                                {
                                    $tax = format_number($document_item->tax_for_item + $document_item->tax_for_shipping);
                                    $rate = $document_item->tax_rate_for_item;
                                    break;
                                }
                            }
                        }
                        //If not, try to work it out...
                        if ($tax == null)
                        {
                            //If there is only one tax rate, we can work out the net and tax ok
                            if ($ledger_tx->tax_rate_1 > 0 && $ledger_tx->tax_rate_2 == 0 && $ledger_tx->tax_rate_3 == 0)
                            {
                                $tax = format_number(($ledger_tx->gross_amount / (100 + $ledger_tx->tax_rate_1)) * $ledger_tx->tax_rate_1);
                                $rate = $ledger_tx->tax_rate_1;
                            }
                            else
                            {
                                //Otherwise, guess (by using a percentage of the total) - we will ask the user to verify
                                $percentage = ($ledger_tx->gross_amount / $ledger_tx->amount) * 100;
                                $total_net = float_subtract($ledger_tx->amount, float_add($ledger_tx->tax_amount_1, float_add($ledger_tx->tax_amount_2, $ledger_tx->tax_amount_3)));
                                $item_net = format_number(($total_net / 100) * $percentage);
                                $tax = format_number(float_subtract($ledger_tx->gross_amount, $item_net));
                                if ($tax > 0)
                                {
                                    $rate = $ledger_tx->tax_rate_1;
                                    $guess = true;
                                }
                                else
                                {
                                    $rate = 0;
                                }
                            }
                        }
                    }
                    $net = format_number($ledger_tx->gross_amount - $tax);
                    $sql = "UPDATE #__nbill_transaction_ledger SET net_amount = '$net', tax_rate = '$rate', tax_amount = '$tax' WHERE id = " . $ledger_tx->id;
                    $ledger_pointer = $nb_database->_pointer; //Remember where we are
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                    if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                    {
                        nbf_globals::$db_errors[] = $nb_database->_errorMsg . " (SQL=" . $nb_database->_sql . ")";
                    }
                    if ($guess)
                    {
                        //Store guesses so that user can confirm the correct values later
                        $sql = "REPLACE INTO #__nbill_ledger_breakdown_guesses (transaction_id, transaction_type) VALUES (" . $ledger_tx->transaction_id . ", '" . $ledger_tx->transaction_type . "')";
                        $nb_database->setQuery($sql);
                        $nb_database->query();
                        if (nbf_common::nb_strlen($nb_database->_errorMsg) > 0)
                        {
                            nbf_globals::$db_errors[] = $nb_database->_errorMsg . " (SQL=" . $nb_database->_sql . ")";
                        }
                    }
                }
            }
            $percentage = $count > 0 ? format_number(($pointer / $count) * 100, 1) : 0;

            if (!$more)
            {
                $pointer = 0;
                $task_name = 'finish';
                $percentage = 0;
            }
            break;

        case "finish":
            if ($pointer == 0)
            {
                //Check whether any transactions have a tax amount mis-match between ledger breakdown and tax on the record - if so, mark as guessed
                $sql = "SELECT #__nbill_transaction.id, #__nbill_transaction.tax_amount_1, #__nbill_transaction.tax_amount_2,
                            #__nbill_transaction.tax_amount_3, #__nbill_transaction.transaction_type,
                            SUM(#__nbill_transaction_ledger.tax_amount) AS ledger_tax
                            FROM `#__nbill_transaction`
                            INNER JOIN #__nbill_transaction_ledger ON #__nbill_transaction.id = #__nbill_transaction_ledger.transaction_id
                            GROUP BY #__nbill_transaction.id, #__nbill_transaction.tax_amount_1, #__nbill_transaction.tax_amount_2,
                            #__nbill_transaction.tax_amount_3, #__nbill_transaction.transaction_type
                            HAVING #__nbill_transaction.tax_amount_1 + #__nbill_transaction.tax_amount_2 + #__nbill_transaction.tax_amount_3 != ledger_tax";
                $nb_database->setQuery($sql);
                $guesses = $nb_database->loadObjectList();
                if ($guesses && count($guesses))
                {
                    $sql = "REPLACE INTO #__nbill_ledger_breakdown_guesses (transaction_id, transaction_type) VALUES ";
                    $guesses_sql = array();
                    foreach ($guesses as $guess)
                    {
                        $guesses_sql[] = "(" . $guess->id . ", '" . $guess->transaction_type . "')";
                    }
                    $sql .= implode(",", $guesses_sql);
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                }
                $pointer = 1;
                $percentage = 34;
            }
            else if ($pointer == 1)
            {
                //If only 1 vendor, mark it as default. If more than one, count clients - whichever has most = default.
                $sql = "SELECT count(*) FROM #__nbill_vendor";
                $nb_database->setQuery($sql);
                $vendor_count = $nb_database->loadResult();
                if ($vendor_count == 1)
                {
                    $sql = "UPDATE #__nbill_vendor SET default_vendor = 1";
                    $nb_database->setQuery($sql);
                    $nb_database->query();
                }
                else
                {
                    $default_vendor = null;
                    $sql = "SELECT vendor_id, COUNT(*) AS client_count FROM #__inv_client GROUP BY vendor_id ORDER BY client_count DESC LIMIT 1";
                    $nb_database->setQuery($sql);
                    $nb_database->loadObject($default_vendor);
                    if ($default_vendor)
                    {
                        $sql = "UPDATE #__nbill_vendor SET default_vendor = 1 WHERE id = " . $default_vendor->vendor_id;
                        $nb_database->setQuery($sql);
                        $nb_database->query();
                        //Ensure only 1 is set to default
                        $sql = "UPDATE #__nbill_vendor SET default_vendor = 0 WHERE id != " . $default_vendor->vendor_id;
                        $nb_database->setQuery($sql);
                        $nb_database->query();
                        //Also set the cookie to pre-select the default vendor
                        setcookie("nbill_vendor_" . md5(nbf_cms::$interop->live_site), $default_vendor->vendor_id, nbf_common::nb_mktime(23,59,59,12,31,2037));
                    }
                }
                //Set email invoice option according to value held on default vendor
                $default_vendor_sql = "SELECT id FROM #__nbill_vendor WHERE default_vendor = 1";
                $nb_database->setQuery($default_vendor_sql);
                $eio_sql = "SELECT email_invoice_option FROM #__inv_vendor WHERE id = " . intval($nb_database->loadResult());
                $nb_database->setQuery($eio_sql);
                $eio = $nb_database->loadResult();
                if ($eio)
                {
                    $eio_sql = "UPDATE #__nbill_configuration SET email_invoice_option = '$eio' WHERE id = 1";
                    $nb_database->setQuery($eio_sql);
                    $nb_database->query();
                }
                $percentage = 67;
                $pointer = 2;
            }
            else
            {
                //Copy vendor logo(s)
                $logo_files = @array_diff(@scandir(@str_replace("com_nbill", "com_netinvoice", nbf_cms::$interop->nbill_fe_base_path) . "/images/vendors/"), array(".", ".."));
                if ($logo_files && is_array($logo_files))
                {
                    foreach ($logo_files as $logo_file)
                    {
                        @copy(str_replace("com_nbill", "com_netinvoice", nbf_cms::$interop->nbill_fe_base_path) . "/images/vendors/" . $logo_file, nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/" . $logo_file);
                    }
                }

                //Copy gateway files (except Paypal)
                $admin_gateway_folders = @array_diff(@scandir(@str_replace("com_nbill", "com_netinvoice", nbf_cms::$interop->nbill_admin_base_path) . "/admin.gateway/"), array(".", ".."));
                if ($admin_gateway_folders && is_array($admin_gateway_folders))
                {
                    foreach ($admin_gateway_folders as $admin_gateway_folder)
                    {
                        if (file_exists(str_replace("com_nbill", "com_netinvoice", nbf_cms::$interop->nbill_admin_base_path) . "/admin.gateway/" . $admin_gateway_folder) && is_dir(str_replace("com_nbill", "com_netinvoice", nbf_cms::$interop->nbill_admin_base_path) . "/admin.gateway/" . $admin_gateway_folder) && $admin_gateway_folder != 'admin.paypal')
                        {
                            @nbill_migrate_recurse_copy(str_replace("com_nbill", "com_netinvoice", nbf_cms::$interop->nbill_admin_base_path) . "/admin.gateway/" . $admin_gateway_folder, nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/" . $admin_gateway_folder);
                        }
                        else if (file_exists(str_replace("com_nbill", "com_netinvoice", nbf_cms::$interop->nbill_admin_base_path) . "/admin.gateway/" . $admin_gateway_folder) && $admin_gateway_folder != 'admin.paypal')
                        {
                            @copy(str_replace("com_nbill", "com_netinvoice", nbf_cms::$interop->nbill_admin_base_path) . "/admin.gateway/" . $admin_gateway_folder, nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/" . $admin_gateway_folder);
                        }
                    }
                }
                $gateway_folders = @array_diff(@scandir(@str_replace("com_nbill", "com_netinvoice", nbf_cms::$interop->nbill_fe_base_path) . "/gateway/"), array(".", ".."));
                if ($gateway_folders && is_array($gateway_folders))
                {
                    foreach ($gateway_folders as $gateway_folder)
                    {
                        if (file_exists(str_replace("com_nbill", "com_netinvoice", nbf_cms::$interop->nbill_fe_base_path) . "/gateway/" . $gateway_folder) && is_dir(str_replace("com_nbill", "com_netinvoice", nbf_cms::$interop->nbill_fe_base_path) . "/gateway/" . $gateway_folder) && $gateway_folder != 'paypal')
                        {
                            @nbill_migrate_recurse_copy(str_replace("com_nbill", "com_netinvoice", nbf_cms::$interop->nbill_fe_base_path) . "/gateway/" . $gateway_folder, nbf_cms::$interop->nbill_fe_base_path . "/gateway/" . $gateway_folder);
                        }
                        else if (file_exists(str_replace("com_nbill", "com_netinvoice", nbf_cms::$interop->nbill_fe_base_path) . "/gateway/" . $gateway_folder) && $gateway_folder != 'paypal')
                        {
                            @copy(str_replace("com_nbill", "com_netinvoice", nbf_cms::$interop->nbill_fe_base_path) . "/gateway/" . $gateway_folder, nbf_cms::$interop->nbill_fe_base_path . "/gateway/" . $gateway_folder);
                        }
                    }
                }

                //Make a note that we have upgraded from 1.2.x
                $sql = "UPDATE #__nbill_version SET upgraded_from = '1.2.99999' WHERE id = 1";
                $nb_database->setQuery($sql);
                $nb_database->query();

                //Check for any ledger breakdown guesses that need confirming
                $sql = "SELECT transaction_id FROM #__nbill_ledger_breakdown_guesses ORDER BY transaction_id";
                $nb_database->setQuery($sql);
                $guesses = $nb_database->loadObjectList();
                if ($guesses && count($guesses) > 0)
                {
                    $message = sprintf(NBILL_MIGRATE_SUCCESS_WITH_GUESSES, '<a href="' . nbf_cms::$interop->admin_page_prefix . '&action=anomaly">' . NBILL_MNU_ANOMALY . '</a>');
                }
                else
                {
                    $message = sprintf(NBILL_MIGRATE_SUCCESS, '<a href="' . nbf_cms::$interop->admin_page_prefix . '&action=anomaly">' . NBILL_MNU_ANOMALY . '</a>');
                }
                $percentage = 100;
                $complete = true;
            }
            break;
    }

    if (count(nbf_globals::$db_errors) > 0)
    {
        $message = NBILL_MIGRATE_DB_ERRORS;
        if ($message == "NBILL_MIGRATE_DB_ERRORS")
        {
            $message = "The migration was attempted, but one or more database errors occurred. The errors are displayed below (error message may be truncated if it is very long).";
        }
        $message .= '\n\n';
        $message .= substr(implode('\n', nbf_globals::$db_errors), 0, 2000);
    }

    //Return array...
    $ret_val = array();
    $ret_val[0] = $pointer;
    $ret_val[1] = $task_name;
    $ret_val[2] = $complete ? 1 : 0;
    $ret_val[3] = $percentage;
    switch ($task_name)
    {
        case "start":
            $ret_val[4] = NBILL_CFG_MIGRATE_DATA;
            break;
        case "address":
            $ret_val[4] = NBILL_CFG_MIGRATE_ADDRESS;
            break;
        case "transactions":
            $ret_val[4] = NBILL_CFG_MIGRATE_TXS;
            break;
        case "ledger":
            $ret_val[4] = NBILL_CFG_MIGRATE_LEDGER;
            break;
        case "finish":
            $ret_val[4] = NBILL_CFG_MIGRATE_FINISH;
            break;
    }
    $ret_val[5] = $message;
    return $ret_val;
}

/**
* The following recurse_copy function ONLY - based on unlicensed code from PHP user comments
*/
function nbill_migrate_recurse_copy($src,$dst)
{
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                nbill_migrate_recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                @copy($src . '/' . $file,$dst . '/' . $file);
                if (file_exists($dst . '/' . $file))
                {
                    $file_contents = @file_get_contents($dst . '/' . $file);                    $file_amended = false;
                    if (nbf_common::nb_strpos($file_contents, "com_netinvoice") !== false)
                    {
                        $file_contents = str_replace("com_netinvoice", "com_nbill", $file_contents);
                        $file_amended = true;
                    }
                    if (nbf_common::nb_strpos($file_contents, "#__inv_") !== false)
                    {
                        $file_contents = str_replace("#__inv_", "#__nbill_", $file_contents);
                        $file_amended = true;
                    }
                    if ($file_amended)
                    {
                        @file_put_contents($dst . '/' . $file, $file_contents);
                    }
                    unset($file_contents);
                }
            }
        }
    }
    closedir($dir);
}

/**
* Convert old-style forms to new absolutely positioned, multi-page forms
*/
function convert_forms()
{
    $nb_database = nbf_cms::$interop->database;
    $field_count = 0;
    $convert_sql = array();

    $sql = "SELECT id, capture_client_data, single_page, client_data_intro, intro, footer, security_image, use_email_address,
                show_login_box, auto_create_user, logged_in_users_only, show_summary, table_border FROM #__inv_order_form ORDER BY ordering";
    $nb_database->setQuery($sql);
    $forms = $nb_database->loadObjectList();

    //Find highest ID number of user-defined fields, and ensure core field IDs are all higher
    $sql = "SELECT id FROM #__inv_order_form_fields ORDER BY id DESC LIMIT 1";
    $nb_database->setQuery($sql);
    $next_id = intval($nb_database->loadResult());

    foreach ($forms as $form)
    {
        //Create 1st page
        $next_id++;
        $ordering = 0;
        $nav_y_pos = 5;
        if ($form->capture_client_data)
        {
            $nav_y_pos = add_core_profile_fields($convert_sql, $next_id, $form->id, 1, $ordering, !$form->logged_in_users_only && $form->show_login_box,
                    !$form->logged_in_users_only && $form->auto_create_user && !$form->use_email_address,
                    !$form->logged_in_users_only && $form->auto_create_user);
        }
        if ($form->single_page || !$form->capture_client_data)
        {
            //Add custom fields
            $nav_y_pos = add_custom_fields($convert_sql, $form->id, 1, $nav_y_pos, $ordering);
        }
        $field_count = $ordering;

        //Finish page and add next button
        $intro = (($form->single_page || !$form->capture_client_data) ? str_replace("'", "\\'", $form->intro) : str_replace("'", "\\'", $form->client_data_intro));
        $convert_sql[] = "INSERT INTO #__nbill_order_form_pages (form_id, page_no, label_width, published, close_gaps, min_width,
                            intro, footer, next_default_value, next_x_pos, next_y_pos, next_z_pos, renderer) VALUES
                            (" . intval($form->id) . ", 1, 200, 1, 1, 750,
                            '" . (nbf_common::nb_strlen($intro) > 0 ? $intro . "<br />" : "") . "',
                            '" . str_replace("'", "\\'", $form->footer) . "', 'NBILL_FORM_NEXT', 400, $nav_y_pos, 0, 1)";

        if (!$form->single_page && $form->capture_client_data)
        {
            //Add custom fields
            $nav_y_pos = 5;
            $ordering = 0;
            $nav_y_pos = add_custom_fields($convert_sql, $form->id, 2, $nav_y_pos, $ordering);
            $field_count += $ordering;

            //Finish 2nd page and add previous/next buttons
            $convert_sql[] = "INSERT INTO #__nbill_order_form_pages (form_id, page_no, label_width, published, close_gaps, min_width,
                                intro, footer, prev_default_value, prev_x_pos, prev_y_pos, prev_z_pos,
                                next_default_value, next_x_pos, next_y_pos, next_z_pos, renderer) VALUES
                                (" . intval($form->id) . ", 2, 200, 1, 1, 750, '" . str_replace("'", "\\'", (nbf_common::nb_strlen($form->intro) > 0 ? $form->intro . "<br />" : "")) . "',
                                '" . str_replace("'", "\\'", $form->footer) . "', 'NBILL_FORM_PREVIOUS', 5, $nav_y_pos, 0, 'NBILL_FORM_NEXT', 400, $nav_y_pos, 0, 1)";
        }

        //Add separate page for summary
        if ($form->show_summary)
        {
            $next_id++;
            $page_no = ($form->single_page || !$form->capture_client_data) ? 2 : 3;
            $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, name, x_pos, y_pos, z_pos, merge_columns, published)
                VALUES ($next_id, " . intval($form->id) . ", $page_no, 1, 'PP', 'NBILL_CORE_summary', 5, 5, 0, 1, 1)";

            $nav_y_pos = 25 + ($field_count * 25);

            //Finish page and add next button
            $convert_sql[] = "INSERT INTO #__nbill_order_form_pages (form_id, page_no, label_width, published, close_gaps, min_width,
                            intro, footer, prev_default_value, prev_x_pos, prev_y_pos, next_default_value, next_x_pos, next_y_pos, renderer) VALUES
                            (" . intval($form->id) . ", $page_no, 200, 1, 1, 750,
                            '" . NBILL_FORM_SUMMARY_INTRO . "<br />', '', 'NBILL_FORM_PREVIOUS', 5, $nav_y_pos, 'NBILL_FORM_NEXT', 400, $nav_y_pos, 1)";
        }
    }

    return $convert_sql;
}

/**
* Add the core profile fields to the given form and page, and return the y_pos of the last item added
* @param mixed $form_id
* @param mixed $page_no
* @param mixed $login_box
*/
function add_core_profile_fields(&$convert_sql, &$next_id, $form_id, $page_no, &$ordering, $login_box, $show_user_name, $show_password)
{
    $nb_database = nbf_cms::$interop->database;
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.frontend.class.php");
    nbf_common::load_language("form.editor");

    $y = 5;

    if ($login_box)
    {
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, name, x_pos, y_pos, z_pos,
                merge_columns, attributes, post_field, default_value, show_on_summary, published) VALUES
                ($next_id, $form_id, $page_no, $ordering, 'OO', 'NBILL_CORE_login', 5, $y, 0, 1, 'style=\"border: solid 1px #cccccc;margin-left:auto;margin-right:auto;margin-bottom:10px;\"',
                '<div class=\"nbill-login-box-outer\"><div class=\"nbill-login-box-inner\">\$\$return defined(\"NBILL_NOT_YET_REGISTERED\") ? NBILL_NOT_YET_REGISTERED : \"" . (defined("NBILL_NOT_YET_REGISTERED") ? NBILL_NOT_YET_REGISTERED : "New Client? Please fill in your details below.") . "\";$$</div></div>',
                '', 0, 3)";
        $y += get_field_height("OO", false, 0);
        $next_id++;
    }
    if (nbf_frontend::get_display_option("contact_name"))
    {
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, required, name, label, x_pos, y_pos, z_pos, contact_mapping, published)
                VALUES ($next_id, $form_id, $page_no, $ordering, 'AA', 1, 'NBILL_CORE_first_name', '* NBILL_FIRST_NAME', 5, $y, 0, 'first_name', 3)";
        $y += get_field_height("AA", false, 0);
        $next_id++;
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, required, name, label, x_pos, y_pos, z_pos, contact_mapping, published)
                VALUES ($next_id, $form_id, $page_no, $ordering, 'AA', 1, 'NBILL_CORE_last_name', '* NBILL_LAST_NAME', 5, $y, 0, 'last_name', 3)";
        $y += get_field_height("AA", false, 0);
        $next_id++;
    }
    if (nbf_frontend::get_display_option("company_name"))
    {
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, name, label, x_pos, y_pos, z_pos, entity_mapping, published)
                VALUES ($next_id, $form_id, $page_no, $ordering, 'AA', 'NBILL_CORE_company_name', 'NBILL_COMPANY_NAME', 5, $y, 0, 'company_name', 3)";
        $y += get_field_height("AA", false, 0);
        $next_id++;
    }
    if (nbf_frontend::get_display_option("address"))
    {
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, name, label,
                    x_pos, y_pos, z_pos, required, entity_mapping, contact_mapping, published)
                    VALUES ($next_id, $form_id, $page_no, $ordering, 'AA', 'NBILL_CORE_address_1', '* NBILL_ADDRESS_1', 5, $y, 0, 1, 'address_1', 'address_1', 3)";
        $y += get_field_height("AA", false, 0);
        $next_id++;
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, name, label,
                    x_pos, y_pos, z_pos, entity_mapping, contact_mapping, published)
                    VALUES ($next_id, $form_id, $page_no, $ordering, 'AA', 'NBILL_CORE_address_2', 'NBILL_ADDRESS_2', 5, $y, 0, 'address_2', 'address_2', 3)";
        $y += get_field_height("AA", false, 0);
        $next_id++;
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, name, label, x_pos, y_pos, z_pos, entity_mapping, contact_mapping, published)
                VALUES ($next_id, $form_id, $page_no, $ordering, 'AA', 'NBILL_CORE_address_3', 'NBILL_ADDRESS_3', 5, $y, 0, 'address_3', 'address_3', 3)";
        $y += get_field_height("AA", false, 0);
        $next_id++;
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, name, label,
                    x_pos, y_pos, z_pos, required, entity_mapping, contact_mapping, published)
                    VALUES ($next_id, $form_id, $page_no, $ordering, 'AA', 'NBILL_CORE_town', '* NBILL_TOWN', 5, $y, 0, 1, 'town', 'town', 3)";
        $y += get_field_height("AA", false, 0);
        $next_id++;
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, name, label, x_pos, y_pos, z_pos, entity_mapping, contact_mapping, published)
                VALUES ($next_id, $form_id, $page_no, $ordering, 'AA', 'NBILL_CORE_state', 'NBILL_STATE', 5, $y, 0, 'state', 'state', 3)";
        $y += get_field_height("AA", false, 0);
        $next_id++;
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, name, label,
                    x_pos, y_pos, z_pos, required, entity_mapping, contact_mapping, published)
                    VALUES ($next_id, $form_id, $page_no, $ordering, 'AA', 'NBILL_CORE_postcode', '* NBILL_POSTCODE', 5, $y, 0, 1, 'postcode', 'postcode', 3)";
        $y += get_field_height("AA", false, 0);
        $next_id++;
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, name, label,
                    x_pos, y_pos, z_pos, required, default_value, xref, entity_mapping, contact_mapping, published)
                    VALUES ($next_id, $form_id, $page_no, $ordering, 'BB', 'NBILL_CORE_country', '* NBILL_COUNTRY', 5, $y, 0, 1, CONCAT('" .
                    '$$$sql="SELECT vendor_country FROM #' . "', '" . '__nbill_vendor WHERE default_vendor = 1";nbf_cms::$interop->database->setQuery($sql);return nbf_cms::$interop->database->loadResult();$$'
                    . "'), 'country_codes', 'country', 'country', 3)";
        $y += get_field_height("BB", false, 0);
        $next_id++;
    }
    if (nbf_frontend::get_display_option("email"))
    {
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, name, label,
                    x_pos, y_pos, z_pos, required, confirmation, contact_mapping, published)
                    VALUES ($next_id, $form_id, $page_no, $ordering, 'CC', 'NBILL_CORE_email_address', '* NBILL_EMAIL_ADDRESS', 5, $y, 0, 1, 1, 'email_address', 3)";
        $y += get_field_height("CC", true, 0);
        $next_id++;
    }
    if (nbf_frontend::get_display_option("telephone"))
    {
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, name, label, x_pos, y_pos, z_pos, contact_mapping, published)
                VALUES ($next_id, $form_id, $page_no, $ordering, 'AA', 'NBILL_CORE_telephone', 'NBILL_TELEPHONE', 5, $y, 0, 'telephone', 3)";
        $y += get_field_height("AA", false, 0);
        $next_id++;
    }
    if ($show_user_name)
    {
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, name, label,
                    x_pos, y_pos, z_pos, required, help_text, contact_mapping, published)
                    VALUES ($next_id, $form_id, $page_no, $ordering, 'AA', 'NBILL_CORE_username', '* NBILL_USERNAME', 5, $y, 0, 1, 'NBILL_USERNAME_HELP', 'username', 3)";
        $y += get_field_height("AA", false, 0);
        $next_id++;
    }
    if ($show_password)
    {
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, field_type, name, label,
                    x_pos, y_pos, z_pos, required, confirmation, help_text, contact_mapping, published)
                    VALUES ($next_id, $form_id, $page_no, $ordering, 'CP', 'NBILL_CORE_password', '* NBILL_PASSWORD', 5, $y, 0, 1, 1, '" . ($show_user_name ? "" : "NBILL_PASSWORD_HELP") . "', 'password', 3)";
        $y += get_field_height("CP", true, 0);
        $next_id++;
    }
    return $y;
}

function add_custom_fields(&$convert_sql, $form_id, $page_no, $y, $ordering)
{
    $nb_database = nbf_cms::$interop->database;
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/classes/nbill.frontend.class.php");

    $sql = "SELECT * FROM #__inv_order_form_fields WHERE form_id = " . intval($form_id) . " ORDER BY ordering";
    $nb_database->setQuery($sql);
    $old_fields = $nb_database->loadObjectList();

    foreach ($old_fields as $old_field)
    {
        //Find out if this field is used in an order value
        $order_value = "";
        $form_data = null;
        $sql = "SELECT discount_voucher_code, payment_gateway, relating_to, carriage_id, tax_exemption_code, payment_frequency,
                currency, unique_invoice, auto_renew, expire_after, expiry_date FROM #__inv_order_form WHERE id = " . intval($form_id);
        $nb_database->setQuery($sql);
        $nb_database->loadObject($form_data);
        if ($form_data)
        {
            $form_properties = get_object_vars($form_data);
            foreach ($form_properties as $form_property)
            {
                if (nbf_common::nb_strpos($form_property, "##" . $old_field->id . "##") !== false)
                {
                    $order_value = $form_property;
                    break;
                }
            }
        }

        //Add the field
        $ordering++;
        $convert_sql[] = "INSERT INTO #__nbill_order_form_fields (id, form_id, page_no, ordering, published, field_type, name,
                label, x_pos, y_pos, z_pos, merge_columns, attributes, checkbox_text, pre_field, post_field,
                default_value, required, help_text, confirmation, related_product_cat, related_product,
                related_product_quantity, value_required_for_order, order_value, show_on_summary) VALUES
                (" . intval($old_field->id) . ", $form_id, $page_no, $ordering, " . $old_field->published . ", '" . $old_field->field_type . "',
                '" . $old_field->name . "', '" . str_replace("'", "\\'", (nbf_common::nb_strlen($old_field->label) && $old_field->required && nbf_common::nb_strpos($old_field->label, "*") === false ? "* " : "") . $old_field->label) . "',
                5, $y, 0, " . $old_field->merge_columns . ", '" . str_replace("'", "\\'", $old_field->attributes) . "',
                '" . str_replace("'", "\\'", ($old_field->required && nbf_common::nb_strlen($old_field->checkbox_text) && nbf_common::nb_strlen($old_field->label == 0) && nbf_common::nb_strpos($old_field->checkbox_text, "*") === false ? "* " : "") . str_replace("'", "\\'", $old_field->checkbox_text)) . "',
                '" . str_replace("'", "\\'", $old_field->pre_field) . "', '" . str_replace("'", "\\'", $old_field->post_field) . "',
                '" . str_replace("'", "\\'", $old_field->default_value) . "', " . $old_field->required . ",
                '" . str_replace("'", "\\'", $old_field->help_text) . "',
                " . ($old_field->field_type == 'CC' || $old_field->field_type == 'CP' ? "1" : "0") . ",
                " . ($old_field->related_product_cat ? $old_field->related_product_cat : "0") . ",
                " . ($old_field->related_product ? $old_field->related_product : "0") . ",
                '" . ($old_field->related_product_quantity ? $old_field->related_product_quantity : "1") . "',
                '" . str_replace("'", "\\'", $old_field->value_required_for_order) . "', '$order_value', " . ($old_field->field_type == 'HH' ? '0' : '2') . ")";

        //Count any options
        $old_option_count = 0;
        if ($old_field->field_type == 'BB' || $old_field->field_type == 'DD')
        {
            $sql = "SELECT COUNT(*) FROM #__inv_order_form_fields_options WHERE form_id = " . intval($form_id) . " AND field_id = " . intval($old_field->id);
            $nb_database->setQuery($sql);
            $old_option_count = intval($nb_database->loadResult());
        }

        $y += get_field_height($old_field->field_type, ($old_field->field_type == 'CC' || $old_field->field_type == 'CP' ? "1" : "0"), $old_option_count);
    }
    return $y;
}

function get_field_height($field_type, $confirmation, $option_count)
{
    $control_class = "nbf_field_control";
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control.base.php");
    if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control." . nbf_common::nb_strtolower($field_type) . ".php"))
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/form_editor/field_controls/custom/nbill.field.control." . nbf_common::nb_strtolower($field_type) . ".php");
        $control_class .= "_" . nbf_common::nb_strtolower($field_type);
    }

    $control = new $control_class(0, 0);
    $support_options = $control->support_options;
    $height_allowance = $control->height_allowance;
    $option_height_allowance = $control->option_height_allowance;

    //Try to guess height
    $field_height = 25; //Default assumption
    if ($support_options)
    {
        $field_height = $height_allowance + (($option_count - 1) * $option_height_allowance);
    }
    else
    {
        $field_height = $height_allowance;
    }
    if ($confirmation)
    {
        $field_height = $field_height * 2;
    }

    return $field_height;
}

function add_client_credit()
{
    $nb_database = nbf_cms::$interop->database;
    $convert_sql = array();

    $sql = "SELECT #__inv_client.vendor_id, #__inv_client.id, #__inv_client.credit_amount, #__inv_client.credit_tax_amount,
            #__inv_client.credit_currency, #__inv_client.credit_ledger_code, #__inv_client.credit_description, #__inv_client.tax_zone,
            #__inv_client.auto_deduct, #__inv_client.country, #__inv_xref_eu_country_codes.code AS in_eu
            FROM #__inv_client
            LEFT JOIN #__inv_xref_eu_country_codes ON #__inv_client.country = #__inv_xref_eu_country_codes.code
            WHERE #__inv_client.credit_amout > 0";
    $nb_database->setQuery($sql);
    $client_credits = $nb_database->loadObjectList();

    if ($client_credits && count($client_credits) > 0)
    {
        foreach ($client_credits as $client_credit)
        {
            //Work out the likely tax rate
            $credit_tax_rate = 0;
            $sql = "SELECT tax_rate, country_code, tax_zone FROM #__inv_tax WHERE vendor_id = " . intval($client_credit->vendor_id);
            $nb_database->setQuery($sql);
            $tax_rates = $nb_database->loadObjectList();
            $standard_rate = null;
            //First try to find a matching tax zone
            foreach ($tax_rates as $tax_rate)
            {
                if ($tax_rate->tax_zone == $client_credit->tax_zone)
                {
                    $standard_rate = $tax_rate->tax_rate;
                    break;
                }
            }
            //Next try to find a matching country (or in EU)
            if ($standard_rate === null)
            {
                foreach ($tax_rates as $tax_rate)
                {
                    if (($tax_rate->country_code == $client_credit->country) || ($tax_rate->country_code == 'EU' && $client_credit->in_eu))
                    {
                        $standard_rate = $tax_rate->tax_rate;
                        break;
                    }
                }
            }
            //Finally default to worldwide
            if ($standard_rate === null)
            {
                foreach ($tax_rates as $tax_rate)
                {
                    if ($tax_rate->country_code == 'WW')
                    {
                        $standard_rate = $tax_rate->tax_rate;
                        break;
                    }
                }
            }
            if ($standard_rate === null)
            {
                $standard_rate = 0;
            }
            if (!float_gtr(abs(float_subtract(($client_credit->credit_amount / 100) * $standard_rate, $client_credit->credit_tax_amount)), 0.05))
            {
                $credit_tax_rate = $standard_rate;
            }
            else
            {
                $credit_tax_rate = format_number(($client_credit->credit_tax_amount / $client_credit->credit_amount) * 100);
                //See if rounding to the nearest 0.5% will yield a valid result
                $test_percentage = format_number(nbf_tax::round_to_nearest(format_number($credit_tax_rate * 10, 0), 5) / 10);
                if (float_cmp(format_number((($client_credit->credit_amount / 100) * $test_percentage) * 100, 0) / 100, $client_credit->credit_tax_amount)
                        || float_cmp(format_number(($invoice_item->gross_price_for_item / ($test_percentage + 100)) * 1002), format_number($client_credit->credit_amount)))
                {
                    $credit_tax_rate = $test_percentage;
                }
                else
                {
                    //See if rounding to the nearest 0.1% will yield a valid result
                    $test_percentage = format_number(format_number($credit_tax_rate * 10, 0) / 10);
                    if (float_cmp(format_number((($client_credit->credit_amount / 100) * $test_percentage) * 100, 0) / 100, $client_credit->credit_tax_amount)
                        || float_cmp(format_number(($invoice_item->gross_price_for_item / ($test_percentage + 100)) * 100), format_number($client_credit->credit_amount)))
                    {
                        $credit_tax_rate = $test_percentage;
                    }
                }
                if (!$credit_tax_rate)
                {
                    //Otherwise, take what we're given
                    $credit_tax_rate = format_number(($client_credit->credit_tax_amount / $client_credit->credit_amount) * 100);
                }
            }

            $convert_sql[] = "INSERT INTO #__nbill_client_credit (vendor_id, entity_id, net_amount, tax_rate, tax_amount, currency,
                                ledger_code, description, auto_deduct)
                                VALUES (" . intval($client_credit->vendor_id) . ", " . intval($client_credit->id) . ",
                                '" . $client_credit->credit_amount . "', '" . $credit_tax_rate . "', '" . $client_credit->credit_tax_amount . "',
                                '" . $client_credit->credit_currency . "', '" . $client_credit->credit_ledger_code . "',
                                '" . $client_credit->credit_description . "', " . intval($client_credit->auto_deduct) . ")";
        }
    }

    return $convert_sql;
}