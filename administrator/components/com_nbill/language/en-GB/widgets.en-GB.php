<?php
/**
* Language file for Widgets
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
define("NBILL_WIDGETS_NOT_FOUND", "Cannot load widget! File not found: %s");
define("NBILL_WIDGET_CONFIGURATION", "Configuration");
define("NBILL_WIDGET_CLOSE", "Close Temporarily (to permanently remove, unpublish the widget in the dashboard configuration settings, above).");
define("NBILL_WIDGETS_CONFIG_NOT_FOUND", "Cannot load configuration! File not found: %s");

//Dashboard Config
define("NBILL_WIDGETS_DASHBOARD_CONFIG", "Dashboard Configuration");
define("NBILL_WIDGETS_DASHBOARD_CONFIG_INTRO", "Here you can control which widgets are shown on the main dashboard when you open nBill administrator. You can have more than one copy of a widget if you wish (eg. you could have 2 or 3 different sales graphs showing different date ranges).");
define("NBILL_WIDGETS_DASHBOARD_CONFIG_TITLE", "Title");
define("NBILL_WIDGETS_DASHBOARD_CONFIG_TYPE", "Type");
define("NBILL_WIDGETS_DASHBOARD_CONFIG_PUBLISHED", "Published");
define("NBILL_WIDGETS_DASHBOARD_PUBLISH", "Click to publish");
define("NBILL_WIDGETS_DASHBOARD_UNPUBLISH", "Click to unpublish");
define("NBILL_WIDGETS_DASHBOARD_CONFIG_ORDERING", "Ordering");
define("NBILL_WIDGETS_DASHBOARD_CONFIG_ACTION", "Action");
define("NBILL_WIDGETS_DASHBOARD_ADD_NEW", "Add New Widget");
define("NBILL_WIDGETS_DASHBOARD_ADD_ENTER_TITLE", "Enter Title");
define("NBILL_WIDGETS_DASHBOARD_TITLE_MANDATORY", "Please enter a title");
define("NBILL_WIDGETS_DASHBOARD_RESET_ALL", "Reset All");
define("NBILL_WIDGETS_DASHBOARD_RESET_ALL_HELP", "This will reset all dashboard widgets to their default state and you will lose any changes you have made to their configuration. Are you sure?");
define("NBILL_WIDGETS_DASHBOARD_COLOUR_SCHEME_CSS", "CSS File to use for administrator colour scheme:");
define("NBILL_WIDGETS_DASHBOARD_COLOUR_SCHEME_INFO", "You can add your own CSS files to the %s folder to add more colour and styling options if you wish");

//Config
define("NBILL_WIDGETS_CONFIG_TITLE", "'%s' Widget Configuration");
define("NBILL_WIDGETS_CONFIG_TITLE_PROMPT", "Title");
define("NBILL_WIDGETS_CONFIG_SHOW_TITLE_PROMPT", "Show Title?");
define("NBILL_WIDGETS_CONFIG_WIDTH", "Width");
define("NBILL_WIDGETS_CONFIG_WIDTH_AUTO", "Auto");
define("NBILL_WIDGETS_CONFIG_WIDTH_FULL", "Full");
define("NBILL_WIDGETS_CONFIG_WIDTH_HALF", "Half");
define("NBILL_WIDGETS_CONFIG_WIDTH_FIXED_PX", "Fixed (specify px)");
define("NBILL_WIDGETS_CONFIG_WIDTH_FIXED_PC", "Percentage (specify %)");
define("NBILL_WIDGETS_CONFIG_WIDTH_HELP", "Using the default stylesheet, there is a 0.5% margin between widgets, so you should subtract 1% width per widget. So for a widget to take up one third of the screen, and still allow 2 more one-third-width widgets next to it, you would have to set the width of each to 32.33% (instead of 33.33%).");

//HTML
define("NBILL_WIDGETS_HTML_MESSAGE", "HTML Message");
define("NBILL_WIDGETS_HTML_DEFAULT_WELCOME", "Click the dashboard gear icon above to control what you see on the main dashboard. Please refer to %s for documentation and support.");

//Links
define("NBILL_WIDGETS_LINKS_DEFAULT_TITLE", "Links");
define("NBILL_WIDGETS_LINKS_ICON_TYPE", "Icon Type");
define("NBILL_WIDGETS_LINKS_ICONS_LARGE", "Large Icons");
define("NBILL_WIDGETS_LINKS_ICONS_SMALL", "Small Icons");
define("NBILL_WIDGETS_LINKS_ICONS_NONE", "None (Text Links)");

//Sales Graph
define("NBILL_WIDGETS_SALES_GRAPH_DEFAULT_TITLE", "Sales Graph");
define("NBILL_WIDGETS_SALES_GRAPH_GRAPH_TYPE", "Graph Type");
define("NBILL_WIDGETS_SALES_GRAPH_LINE", "Line");
define("NBILL_WIDGETS_SALES_GRAPH_COLUMN", "Column");
define("NBILL_WIDGETS_SALES_GRAPH_BAR", "Bar");
define("NBILL_WIDGETS_SALES_GRAPH_PIE", "Pie");
define("NBILL_WIDGETS_SALES_GRAPH_CURRENCY", "Currency");
define("NBILL_WIDGETS_SALES_GRAPH_HEIGHT", "Height");
define("NBILL_WIDGETS_SALES_GRAPH_HEIGHT_HELP", "Number of pixels of height to allow for the graph container. Leave blank to auto-size.");
define("NBILL_WIDGETS_SALES_GRAPH_GROSS_OR_NET", "Gross or Net?");
define("NBILL_WIDGETS_SALES_GRAPH_GROSS", "Show Gross Sales (income only)");
define("NBILL_WIDGETS_SALES_GRAPH_NET", "Show Net Sales (deduct expenditure)");
define("NBILL_WIDGETS_SALES_GRAPH_INCLUDE_UNPAID_INVOICES", "Include Unpaid Invoices?");
define("NBILL_WIDGETS_SALES_GRAPH_INCLUDE_UNPAID_INVOICES_HELP", "Whether or not to regard unpaid invoices as income (and unpaid credit notes as expenditure).");
define("NBILL_WIDGETS_SALES_GRAPH_DATE_RANGE", "Date Range");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_CURRENT_MONTH", "Current Month");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_MONTH", "Previous Month");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_AND_CURRENT_MONTH", "Previous Month and Current Month");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_VS_CURRENT_MONTH", "Current Month Vs Previous Month");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_24_HOURS", "Last 24 Hours");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_48_HOURS", "Last 48 Hours");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_7_DAYS", "Last 7 Days");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_14_DAYS", "Last 14 Days");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_28_DAYS", "Last 28 Days");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_30_DAYS", "Last 30 Days");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_60_DAYS", "Last 60 Days");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_90_DAYS", "Last 90 Days");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_3_MONTHS", "Last 3 Months");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_6_MONTHS", "Last 6 Months");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_12_MONTHS", "Last 12 Months");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_CURRENT_QUARTER", "Current Quarter");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_QUARTER", "Previous Quarter");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_VS_CURRENT_QUARTER", "Current Quarter Vs Previous Quarter");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_CURRENT_YEAR", "Current Year");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_YEAR", "Previous Year");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_VS_CURRENT_YEAR", "Current Year Vs Previous Year");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_5_YEARS", "5 Years");
define("NBILL_WIDGETS_SALES_GRAPH_RANGE_ALL_TIME", "All Time");
define("NBILL_WIDGETS_SALES_GRAPH_LEDGERS", "Ledger Codes");
define("NBILL_WIDGETS_SALES_GRAPH_PRINTABLE", "Printable");

//Orders Due
define("NBILL_WIDGETS_ORDERS_DUE_DEFAULT_TITLE", "Orders Due");
define("NBILL_WIDGETS_ORDERS_DUE_DEFAULT_DESC", "Invoices are due to be generated soon for the following orders.");
define("NBILL_WIDGETS_ORDERS_DUE_RANGE", "Date Range");
define("NBILL_WIDGETS_ORDERS_DUE_RANGE_DAYS", "Days");
define("NBILL_WIDGETS_ORDERS_DUE_RANGE_WEEKS", "Weeks");
define("NBILL_WIDGETS_ORDERS_DUE_RANGE_MONTHS", "Months");
define("NBILL_WIDGETS_ORDERS_DUE_RANGE_HELP", "Specify how many days, weeks, or months in advance to check for orders that are due for renewal.");
define("NBILL_WIDGETS_ORDERS_DUE_MAX", "Maximum No. of Records");
define("NBILL_WIDGETS_ORDERS_DUE_NONE", "No records found.");
define("NBILL_WIDGETS_ORDERS_DUE_HEIGHT", "Height");
define("NBILL_WIDGETS_ORDERS_DUE_HEIGHT_HELP", "Number of pixels of height to allow for the list of orders. Leave blank to auto-stretch to show the whole list.");