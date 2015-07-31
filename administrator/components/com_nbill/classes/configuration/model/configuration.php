<?php
class nBillConfiguration
{
    public $id = 1;
    protected $colour_scheme_path;

    public $error_email = '';
    public $date_format = 'd/m/Y';
    public $locale = '';
    public $precision_decimal = 2;
    public $precision_currency = 2;
    public $precision_currency_line_total = 2;
    public $precision_currency_grand_total = 2;
    public $precision_quantity = 0;
    public $precision_tax_rate = 2;
    public $thousands_separator = 'default';
    public $decimal_separator = 'default';
    public $currency_format = '';
    public $negative_in_brackets = true;
    public $select_users_from_list = true;
    public $default_user_groups = '';
    public $cron_auth_token = '';
    public $version_auto_check = true;
    public $auto_update = false;
    public $default_start_date = 'BB';
    public $switch_to_ssl = false;
    public $all_pages_ssl = false;
    public $email_invoice_option = 'AA';
    public $title_colour = '366999';
    public $heading_bg_colour = '366999';
    public $heading_fg_colour = 'fff';
    public $supporting_docs_path = '';
    public $default_itemid = 0;
    public $redirect_to_itemid = 0;
    public $admin_custom_stylesheet = 'template_green.css';
    public $use_legacy_document_editor = false;
    public $edit_products_in_documents = false;
    public $auto_check_eu_vat_rates = true;
    public $api_url_eu_vat_rates = '';
    public $geo_ip_lookup = true;
    public $api_url_geo_ip = '';
    public $geo_ip_fail_on_mismatch = false;
    public $eu_tax_rate_refresh_timestamp = 0;
    public $disable_email = false;
    public $timezone = '';
    public $default_electronic = false;
    public $never_hide_quantity = false;
    public $never_hide_tax = false;

    public function __construct()
    {
        $this->colour_scheme_path = nbf_cms::$interop->nbill_fe_base_path . "/style/admin/colours/";
    }

    public function getColourSchemePath()
    {
        return $this->colour_scheme_path;
    }

    public function applyTimezone()
    {
        if (function_exists("date_default_timezone_get") && function_exists("date_default_timezone_set"))
        {
            $tz = $this->getCurrentTimezone();
            if ($tz && strlen($tz) > 0 && $tz != 'Unknown') {
                @date_default_timezone_set($tz);
            }
        }
    }

    public function getCurrentTimezone()
    {
        if (function_exists("date_default_timezone_get")) {
            $tz = trim($this->timezone);
            if (!$tz || strlen($tz) == 0) {
                $tz = trim(ini_get('date.timezone'));
                if (!$tz || strlen($tz) == 0) {
                    $tz = trim(@date_default_timezone_get());
                }
            }
            return $tz;
        } else {
            return 'Unknown';
        }
    }
}