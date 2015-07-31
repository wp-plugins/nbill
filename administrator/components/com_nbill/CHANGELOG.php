<?php
die();
/*
(c) Copyright 2015, Netshine Software Limited. All Rights Reserved.
This log indicates the changes that have been made to nBill in each successive version.
There may be other minor changes that are not listed here (eg. removal of unnecessary
white space, correction of mis-typed variable names, etc.) - if you have customised your
copy of nBill, please keep a record of what you have changed so that you can re-instate
your customisation after upgrading.

3.1.1 (31/07/2015)
Updated invoice template customisation instructions in comments at top of default template's index.php file (to match website help page)
Fixed error when editing a document whose associated client has no contacts
Fixed a problem connecting to the database during installation for systems that still use the older MySQL PHP extension
Fixed an issue where a field on a form is mapped to a non-existent custom field, and the value was being lost
Fixed problem with HTML product descriptions not being pulled through to ad-hoc invoices when a product was selected from the list
Tax Summary Report now shows tax label in local language instead of picking up the last tax name used when including unpaid invoices
Prerequisite check now occurs in an unencoded file so that wrong PHP version can be detected without bombing out
Fixed a problem saving intro and footer text on order forms
EU intra-community supplies now show a notice to that effect on the invoice
New facility to add custom columns to admin lists for clients, products, orders, invoices, and income
Fixed a problem with UTF-8 encoding being lost when using the vendor synchronisation feature (previous data will be corrected on next sync)
Order confirmation e-mail now shows order number in subject line for auto-created orders
Fixed an issue with changes to invoice email message being lost when emailing an invoice from the invoice list
Tax exemption code now taken into account on line item editor's default tax rate value when editing an existing invoice for a client with an exemption code
Duplicate payment notification from Paypal will now result in an error, rather than adding a new income record with the same reference number
Orders that are not associated with a product but have a tax amount now pick up default VAT rate
Phoning home for version checking or VAT rate updates is disabled by default on new installs of nBill Lite on Wordpress (for compliance with WP Plugin Directory terms)
When phoning home is disabled, a warning is shown on the dashboard with a link to re-enable (and the option to suppress the warning)
Reinstated display option to show tax on invoice even if no tax charged
Fixed problem with tooltips not showing inside lightbox

3.1.0 (11/05/2015)
Fixed a problem that occurs when trying to restore a backup taken with nBill v2
Fixed saving of detailed product descriptions for invoice line items in Safari
Calculation of outstanding invoice amounts now consistent in front-end and back-end for cases with multiple partial payments spanning multiple invoices
Allow for quote characters to appear in a database password
Convert document_ids column in transaction table to latin1 encoding to ensure the index is not too long for MySQL
Fixed an issue with currency (and other vendor related information) reverting to vendor default when editing an existing quote or invoice
E-mail address fields and search filters now trim values in case of extra white space being pasted in by mistake
Where an order involves a custom tax rate, electronic delivery flag on invoice generation is now set according to the global config default
Id attribute removed from invoice template (replaced with class attribute) to prevent problems in DomPDF when producing multiple invoices as PDFs
New shipping address feature - including new core profile fields, ability to specify separate shipping address on client and contact records, and to choose a shipping address on order and invoice records
New delivery note feature - from the invoice list, you can now produce a delivery note (similar to an invoice, but without amounts or tax summary, and showing the shipping address instead of the billing address)
Paypal gateway updated to support pre-approved payments (API credentials from Paypal required)
With this release, nBill is now compatible with Wordpress (not all nBill extensions are compatible yet though, and nBill should be considered a BETA release when used on Wordpress)
Fixed software version reporting on error report emails
New filter added to search by order number on the order list
New global configuration option to show negative numbers in brackets or with a minus symbol
Tax summary report updated to group transactions by country code, show net amounts, and sub-totals for each country - to better facilitate EU MOSS tax returns and EC Sales Lists
User import now includes fields from Joomla user profile plugin

3.0.6
If a page on an order form has no published fields, it is no longer displayed, even if the page itself is published
Fixed an error message that was being generated when a client accepts and pays for a quote
Added an option to use the default Itemid specified in global config in preference to the one passed in (currently only used by latest nCart checkout module)
Database connection is now explicitly closed on destruction
Fixed an issue with small print being output twice when using the CodeMirror HTML editor
Nominal ledger dropdown in line item editor now prefixes the ledger description with the code, so you can use the keyboard to quickly select a ledger by its code
User-controlled payment plan now uses editable decimal rather than formatted currency output to allow for partial payment amounts entered by client
Quantity precision now defaults to 2 instead of 0 (people were getting confused and not realising that non-integer values are possible)
Fixed a problem where the tax rate was omitted from invoices and income for orders that are renewed manually (correct tax amount was applied, but without a tax rate - affected records are corrected during upgrade from 3.0.5 to 3.0.6)
Where no matching tax rate is found during invoice generation, it now reverts the rate to zero instead of using the rate from the vendor's country (does not affect tax amount calculation, just the rate specified on the invoice) - affected records are corrected during upgrade from 3.0.5 to 3.0.6
When an invoice is overpaid, a warning is now shown on the invoice
Support for custom fields on all admin editors (new, edit, save, and delete hooks)
Fixed PHP warning message when creating a new vendor
Fixed an issue with marking invoices as paid when multiple partial payments span multiple invoices with multiple nominal ledger codes
Fixed an issue with calculating the ledger breakdown on income records when multiple partial payments span multiple invoices with multiple nominal ledger codes
Inline calculated percentage discounts can now be added to ad-hoc invoice line items
No longer attempts to use mysql PHP extension on installation
 
3.0.5 15/01/2015
Added ability to control auto create invoice, auto create income, and transaction ID values on order record advanced tab
Fixed an issue where the order of line items on an invoice generated from a quote did not always match the order of line items on the quote
Tax summary report now allows items to be expanded and collapsed on currency tabs other than the first one
Fixed an issue with tax rates on the tax summary report being treated as different solely due to using a different decimal precision rather than having a different value
Fixed browser hang issue when checking a box on a list with pagination set to show all records
Fixed an issue with non-default quantity precision on inline editing of line items
New configuration option to allow you to specify that quantity is always shown on invoices, even if quantity is exactly 1
Even when quantity is not always shown, any value other than exactly 1 will now cause quantity to be shown
Vendor sync feature now uploads directly to top-most master database instead of updating each child database in the links between
Fixed an issue with character encoding for currency symbols on Joomla 1.0
Where there is no previous quote correspondence, a message to that effect is now shown instead of an empty box in the front-end
Custom small print is now preserved on documents, whilst still allowing for vendor defaults and tax record overrides to change the value when the client, country, or tax exemption code is changed
Quote marks can now appear in line item descriptions and quote 'relating to' settings
Fixed a problem where some installations were failing to send invoice e-mails using the embed option

3.0.4 02/01/2015
Fixed an issue with the pre-defined 'Previous Month' setting on reports when running a report on 31st of the month
Fixed some error messages (that appeared in nBill Lite only) when an invoice was paid
Fixed issue with page published value showing 'visible to everyone' even when set to 'visible to new clients only' or 'visible to existing clients only'
When a page other than the first one is selected on the form editor, with the page properties loaded, and the apply toolbar button is clicked, it now restores the correct page properties on reload (instead of going back to the properties of page 1 even though another page is selected)

3.0.3 31/12/2014
Fixed problem with insufficient amount charged where a partially paid invoice is presented for payment but a previous invoice was paid using the same income record as the partial payment for the current invoice and both invoices share the same ledger code
Invoice preview on front-end payment page now shows PDF instead of HTML if PDF is enabled but HTML preview is not
Can now preview invoice on front-end payment page if not logged in, when 'login required for payment' is set to 'no'
ID attributes added to list items on main My Account page, and new CSS styling added
Fixed a problem which prevented the product list on the line item editor from auto-collapsing when using Google Chrome
Fixed some layout issues with printer-friendly version of nominal ledger report
Page breaks now appear between invoices when multiple invoices are printed or PDF generated in one go
Fixed a problem with payment instructions and small print not being pulled through from the tax record
Fixed problem with order form editor not showing up in Google Chrome
Where a client has a tax exemption code and the tax rate allows exemption, tax is now omitted when looking up a product from the line item editor
Fixed an issue with saving potential client records manually
Fixed a display issue when changing products to or from electronic delivery and EU orders are affected for those using Joomla 1.0 or who used the manual upgrade patch

3.0.2 11/12/2014
Negative amounts are now properly HTML formatted when shown on the invoice list
Fixed a problem where electronic delivery tax rate was being pulled through to the invoice editor when no normal tax rate record was present
Improved error reporting for Paypal callback verification problems
Use of SSL Cipher for Paypal is now optional (new gateway setting)
Print icon on top of invoices no longer shows when printed (same for quotes and credit notes)
Fixed a problem with tax rate not being pulled through to the line item editor when a product is selected if the client in question belongs to a tax zone
Fixed an issue with tax reference number caption being shown on the invoice even if there is no tax reference number to show
Fixed a problem with quantities greater than 1000 being reverted to 1 due to unwanted thousands separator
PHP temp directory is now preferred over local media directory for temporary files (eg. during extension installation)
Adjusted responsive sizing of HTML editor in iframe when sending invoices/quotes by email
Added indexes to database tables to improve performance and fix problem with too many join records on some server configurations
Improved error reporting on database upgrades between versions
Fixed use of 'none' as HTML editor in config file
Fixed a problem with escape characters in JSON encoded data preventing some line items from being edited (if they had certain characters in their HTML description)
Fixed a problem with unwanted space sometimes appearing after a currency symbol
Lack of Joomla toolbar no longer causes the Joomla logo to be obscured in the back end
Fixed problem with accessing invoice for payment when not logged in, even if display options allow it (only affected nBill Lite)
First item on invoice shows section name in front-end if applicable (consistent with back end)
Fixed problem with invoice line items being duplicated under certain circumstances
Fixed problem with user-supplied quantity always reverting to 1 on order forms
Fixed problem where invoices were being marked as partially paid instead of unpaid or fully paid where multiple invoices (including some previously partially paid) are partially covered by a single receipt
Added overrides to stop Joomla admin template CSS rules from mis-rendering nBill pages during printing

3.0.1 02/12/2014
Removed reference to an unecessary legacy file which caused a PHP warning when running scheduled reminders by cron
Added extra security restrictions when running in demo mode
index.html files are now excluded from email template options on vendor record
Hide toolbar when editing contact via client record as front-end administrator

3.0.0 01/12/2014
Negative amounts on sales graph no longer show html code in hover caption
HTTP connections using CURL now use TLS rather than SSLv3
HTTP connections using CURL now support custom headers
Re-added support for using TinyMCE instead of nicEdit (except in popup/lighboxes where Tiny won't play nicely)
You can now set the editor to 'none' in the nBill configuration file (/administrator/components/com_nbill/framework/nbill.config.php) to prevent it loading any html editor
Fixed problem with phpMailer class name clash which would prevent multiple attachments being sent in older versions of Joomla
Custom separators now work on servers that use a locale not recognised by PHP's loose comparison for floating point numbers
Quote acceptance confirmation email now includes a note of which payment gateway was selected, if applicable
Adjustments to facilitate automatic deployment of Lite edition
Fixed an issue with product lookup not working when there are no categories
Embedded invoice now fills width of page
You can now preview an HTML or PDF invoice/quote/credit note from within the document editor
Manual upgrade of database now shows any database errors if there are any
Fixed problem loading appropriate language pack if administrator also has a client record

2.9.3
Added more CSS classes to front end order form field elements
EU VAT rates can now be forcefully refreshed
EU VAT rate update now marks vendor's own country tax rate as not exempt with a reference number
Fixed problem with lightbox not loading fully when a product description contains line breaks
Fixed problem with amounts displaying incorrectly when custom thousands and decimal separators are directly swapped compared with the locale values
Editable amounts in text boxes no longer use custom separators (so they can be saved correctly as decimals)
Toggle accepted status of a quote line item now updates the tooltip text correctly
When a quote with a status of 'new' has an item marked as accepted, then unmarked again, it now reverts back to 'new' instead of staying on 'part accepted' or 'accepted'
When item marked as accepted (or not) in the lightbox, quote status is now updated when the line items are refreshed
Custom small print and payment instructions on a quote or invoice are now preserved between edits
Section discount gross amount is now shown in front-end quote acceptance screen
Fixed problem with unit prices not showing up on quote acceptance screen
Net price column on quote acceptance now only shows if it is different from the gross amount

2.9.2
Changed default geo-ip lookup service for better reliability
IP addresses that relate to localhost use no longer do a geo-ip lookup
Fixed a problem with extra white space appearing on invoice list when there is a mixture of paid and unpaid invoices and the payment link is hidden by responsiveness rules
Extra id and class attributes added to rows in the back end to allow for more control over what is displayed using CSS
Fixed problem with linking from contact list to clients (was previously only finding a link for primary contacts)
Added workaround for LastPass extension autofill creating blank contact records
Configuration page now detects dompdf, not just html2ps/pdf
Fixed problem with duplicate items being added to quotes when sections are used
Fixed quote line item display to allow inline editing of amounts
When section editor is opened, it now focuses on the section name field by default
On mobile devices, tapping on a line item now opens the line item editor
Example files added to support overriding document line item rendering in custom templates
Fixed problem with page break delete button not showing up in Google Chrome

2.9.1
Fixed problem with electronic delivery VAT rate being applied to invoices automatically where no other tax rates are defined
Fixed problem with very long invoices causing extra blank pages to appear on PDFs
Added support for UTF-8 characters on PDF invoices
Where a page break is used on the last item in a section, the section sub-total now shows up before the page break
Fixed a problem with product lookups not working on quotes
Fixed an issue with horizontal scrollbars showing up on webkit browsers
Increased size of lightbox for line item editing
Increased size of HTML editor for line item detailed description setting
Added new configuration setting to allow you to override the timezone specified in php.ini
Fixed issue with restoring a backup taken in earlier versions, while upgrading to v3
Home menu item renamed to 'Dashboard'
phpMailer upgraded to latest version (5.2.8)
Product list on line item editor sku lookup now displays already collapsed instead of starting expanded then collapsing after a delay
Number formatting from global configuration is now used for the Google charts on the sales graph widget
When multiple invoices are being marked as paid in one go, they are now marked off in date order, so if there is insufficient income to cover all invoices, the later invoice(s) will be marked as partially paid in preference to older ones
Line items can now be edited inline on the invoice editor without using the lightbox if the browser window is wide enough (mobile devices must still use lightbox) 
Fixed problem where order form mapping warning was being shown unnecessarily (it was treating quote request forms as though they were order forms)
Fixed an issue where a field was sometimes repeated at the end of a list of fields on an order form
Summary table width is no longer restricted to 300px
On Joomla 1.0, changing dashboard configuration now reloads the whole dashboard rather than doing a partial refresh, so as to keep the styling intact
Fixed a problem where when emails are turned off, extension installation was failing silently
Links widget now shows the names of menu items for installed extensions
You can now open an order form or quote request form in the website front end directly from a link on the form list in the back end
Fixed positioning of next button on order form table renderer in front end
Fixed sending PDF invoices generated by dompdf as email attachments
Where there is no contact e-mail address on an order form, an e-mail can now still be sent to admin when the form is submitted
Fixed problem with default currency on expenditure being set to empty when a supplier is selected if they don't have a default currency and the browser is webkit
New global config setting to specify whether or not new products, line items, income, and expenditure records should default to electronic delivery (for those who predominantly sell digital goods in the EU)
When a product is updated from electronic delivery to non-electronic deliver or vice-versa, you are now prompted to update the amounts on any orders for that product for customers within the EU
Fixed problem with auto-suggest not loading all the relevant suggestions on ad-hoc invoice creation
QR code generation on invoices now works without browser warnings over SSL
Orders due widget now includes orders that are due the same day
Added ability to show or hide HTML preview and PDF preview of quotes (in addition to invoices)
Fixed issue with anomaly report showing date errors when an invoice is marked as paid manually
Fixed some formatting issues which were making text hard to read on printer-friendly tax summary report and invoice details section of order editor
Fixed save copy feature on invoices/quotes/credit notes
New combined bootstrap file for loading essential framework elements (used by back end, front end, and potentially extensions such as nCart in future)
Contact e-mail address can have several addresses separated by semi-colon if invoices need to be sent to several addresses (and you don't want to add another contact record)
Version check now uses a separate script to look for new versions with a warning if their custom branding file is still looking in the old location
Where a mix of invoices with and without due dates exist for a client, the table columns now line up correctly
Additional 2 levels of granularity added to responsive options for mobile devices
Paypal gateway now defaults to CURL - no longer optional

2.9.0 (nBill 3 BETA)
HTML attributes settings on next and previous buttons in order forms now no longer encode quote characters
Fixed non-critical error message when creating a new supplier record
Amended stylesheet to use same margins for selects as for other inputs (allows vat checker fields to line up better on some templates)
Added base64 encoding to embedded HTML invoice by email submission to get past Cloudflare content filtering
Ampersands are now allowed in text fields (such as vendor name)
Fixed potential bug with invoice items being repeated on invoice editor when SKU is specified
Form editor now supports extended properties for custom field controls
Allow 15 minute override of selected language using nbill_lang parameter in URL
Fixed CSS class name for table heading on front-end order list (jlist-table, not j-list-table)
Master database connection details now support format host:port
Fixed a problem where not all invoices were being uploaded to master database on synchronisation if there were also credit notes present on the child
Line breaks are now stripped from help text on order forms as they mess up the javascript
Fixed missing space before payment link on PDF invoices
Fixed an issue with SKU lookups for products with an HTML description
Fixed an issue with cloning an array of objects when calculating section discounts on quotes
Vendor agnostic forms now show up even if a non-default vendor filter is in force
Vendor agnostic forms can show products from all vendors for prerequisite and disqualifying product lists
Corrected column name when unlocking receipt number on vendor record
Added support for hooking extensions into invoice editor
New configuration option to specify whether or not to prompt for product updates from the invoice/quote editor
Added facility to apply client credit to an ad-hoc invoice at the time it is first e-mailed to the client
Made front-end and most back-end screens responsive for mobile use (including tap-friendly overlib tooltips, help text hidden until icon clicked, and dynamic adaptive tables)
New display options to allow specification of which columns to prioritise for display on narrow screens
New display option to allow choice of whether or not to allow an HTML preview of invoices in the front-end (so you can suppress HTML and just use PDF if you prefer)
Fixed problem saving dropdown options for client profile fields
Products assigned via a dropdown list option or radio list option can now have fractional quantities
Order form editor now allows 3 different renderers: absolute (previous default), responsive (new default), and table (previously a separate yes/no option)
New field property to allow responsive rendering to be overridden for individual fields and absolute positioning used instead
Overpaid invoices are no longer removed from the front-end invoice list
Discounts that appear on generated invoices now show the correct tax rate where a tax exemption code is used (thus preventing false positives on the anomaly report)
Where a PSP processes setup fee and regular payment as 2 separate transactions, nBill will no longer complain about an amount mismatch
Fixed timestamp on email log detail view (was previously showing current time instead of the time the message was sent)
Default quote intro and payment instructions are now populated when quotes are created via a quote request form
Rounded edges added to tabs, tables, and toolbar buttons
New home page dashboard with configurable widgets (existing welcome message and favourite links converted to widgets)
Addition of widgets for sales graph and orders due
Where an order is not renewed and has a download associated with it, a more relevant error message is now shown, telling the client why they can no longer access the download
Income records now record the time of receipt, not just the date
It is now possible to show a calculated due date on invoices (a defined number of days, weeks, or months after the invoice date)
Invoices can now be generated in advance (in conjunction with the due date feature)
Fixed problem with passing tracking variables to thank you URL when payment process is complete for an order form submission
All CSS and javascript files moved to the front-end folders so that admin via front end feature works even if the administrator folder is password protected
When a new default language is selected in My Profile, it now redirects to that language in Joomla
New options for formatting numeric output (precision, currency formatting and separators for different types of number)
New line item editor for invoices, quotes, and credit notes (mobile friendly, and ajaxified)
New option to make order forms available to guests only (useful for user registration forms)
Fixed an issue with forms being shown in list even if they are unavailable to the user and the 'always show' flag is not set
Fixed a bug calculating the total shipping tax for an invoice
Transaction report, ledger report, tax summary, and e-mail log now all have quick-select date range options (current month, previous month, etc.)
Front end uses new numeric formatting features
Added support for dompdf as a replacement for html2ps/pdf which is now deprecated
Payment instructions and small print can now be left blank on VAT records and the value from the vendor record will be used by default
Tax reference number moved from VAT record to vendor record, to avoid repetition for each tax record
Products and VAT rates (as well as document items and the tax breakdown on income records) now have a flag for 'electronic delivery' - so that tax can be charged at the client's tax rate rather than the vendor's (in line with new EU VAT legislation which comes into effect in January 2015)
New option to allow EU VAT rates for electronic delivery items to be downloaded automatically from the nBill server
When a VAT rate changes, and it is being used by one or more orders, the option to amend either the net or gross amount of existing orders is offered after automatic download of VAT rates (as well as when manually changing a rate, as before)
New configuration option to allow you to completely disable e-mailing by nBill (for doing test runs)
Fixed erroneous discrepancy report on tax summary when a section discount is used
Tax summary report now shows breakdown of items that are flagged as for electronic delivery
New options on global configuration page to specify a Geo-IP lookup (to verify that a client's country matches the country of their IP address, and to allow the country to be auto-populated on order forms and quote request forms for guests)
Collected IP address information is visible from the client record (if applicable)
Invoices now include a QR code, enabling payment to be made by scanning the code on a mobile device. This can be turned off if required via the Display Options page (My Invoices tab).
You can now see more details about an order in the front-end, including all the data submitted on the order form (if applicable)
Order summary table now picks up tax abbreviation from tax record instead of defaulting to 'tax'
Extra CSS classes and IDs added to front-end HTML elements to allow for greater control over display and styling
Fixed bug with apostrophes being escaped unnecesarrily on filters in the back end
You can now reset a user's password from the contact editor
Added a note to the backup/restore page to warn that it can only be used for small databases
*/