<?php
/*
Plugin Name: nBill Lite
Plugin URI: http://nbill.co.uk/lite
Description: Mobile friendly online invoicing. nBill Lite is a free version of nBill (the online billing system), which includes features for creating ad-hoc invoices that can be paid for online. It allows you to create client records for the people you want to bill, or import them from a CSV file or from your Wordpress user records. You don't have to create a client record though, nBill Lite includes support for billing someone on a one-off basis even if they are not a registered client. Note for compliance with Wordpress Plugin Directory rules: The Sales Graph widget on the main nBill administration dashboard uses Google Charts. The script to generate graphs is downloaded directly from Google's server, but your data is NOT sent to Google - the graph is rendered locally on your own browser only.
Version: 3.1.1
Author: Russell Walker
Author URI: http://netshinesoftware.com/
License: GPL v2
Text Domain: nbill
*/
defined('ABSPATH') or die ('Access Denied');

include_once(plugin_dir_path(__FILE__) . '/controller.php');
if (!isset($nbill_plugin_file)) {
    $nbill_plugin_file = __FILE__;
}
if (!isset($nbill_component_name)) {
    $nbill_component_name = 'nbill';
}
$nbill_controller = new nBillWpController($nbill_plugin_file, $nbill_component_name);
$nbill_controller->bootstrap();