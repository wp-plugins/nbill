<?php
/**
* Language file for the Form Editor
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

define("NBILL_FORM_EDITOR_PREVIEW", "Canvas Properties:");
define("NBILL_FORM_EDITOR_PROPERTIES", "Page/Field Properties");
define("NBILL_FORM_EDITOR_SNAP", "Snap to Grid");
define("NBILL_FORM_EDITOR_GRID_SIZE", "Grid Size");
define("NBILL_FORM_EDITOR_CANVAS_WIDTH", "Canvas Width");
define("NBILL_FORM_EDITOR_CANVAS_HEIGHT", "Canvas Height");
define("NBILL_FORM_EDITOR_MIN_PAGE_WIDTH", "Minimum Page Width");
define("NBILL_FORM_EDITOR_MIN_PAGE_WIDTH_HELP", "This specifies the amount of horizontal space available for your fields before they start wrapping. As wrapping can look ugly, make sure you allow enough space for your form content, or insert line breaks in the form content if necessary. If the browser window is resized smaller than the minimum page width, the overflowing form content will be obscured. If the width is too large and there is additional content to the right of the main body area of the page (eg. in a module), the form may overlap the additional content, which might make it impossible to click hyperlinks that appear in it. In that case, make the minimum page width smaller. If you are using Internet Explorer, you will need to save the form before changes to this property are reflected in the form editor.");
define("NBILL_FORM_EDITOR_LABEL_WIDTH", "Label Column Width");
define("NBILL_FORM_EDITOR_LABEL_WIDTH_HELP", "By default, fields are shown with a label to the left and control to the right, with the controls lined up according to the label column width you specify here. If you want to position labels differently, simply set `merge columns` to `yes` on the field, and add a separate field of type `label` to act as the label (also with `merge columns` set to `yes`). You can then position the label and the control indpendently of each other.");
define("NBILL_FORM_EDITOR_NO_PROPERTIES", "Click on a field in the left hand pane to see its properties here.");
define("NBILL_FORM_EDITOR_PROPERTIES_NOT_FOUND", "No properties could be found for the selected field.");
define("NBILL_FORM_EDITOR_LOADING", "Loading Properties...");
define("NBILL_FORM_EDITOR_TAB_GENERAL", "General");
define("NBILL_FORM_EDITOR_TAB_ADVANCED", "Advanced");
define("NBILL_FORM_EDITOR_TAB_PROCESS", "Processing");
define("NBILL_FORM_EDITOR_FIELD", "Field");
define("NBILL_FORM_EDITOR_FIELDS", "Fields");
define("NBILL_FORM_PAGE", "Page %s");
define("NBILL_FORM_NEW_PAGE", "New Page");
define("NBILL_FORM_FIELD_DEFAULT_LABEL", "Field %s");
define("NBILL_FORM_EDITOR_PAGE_ACTIONS", "Page Actions:");
define("NBILL_FORM_EDITOR_ADD_FIELD", "Add New Field");
define("NBILL_FORM_EDITOR_MERGE_BY_DEFAULT", "Merge Columns");
define("NBILL_FORM_EDITOR_BTN_ADD_FIELD", "Add");
define("NBILL_FORM_EDITOR_DELETE_FIELDS", "Delete");
define("NBILL_FORM_EDITOR_DELETE_SURE", "Are you sure you want to delete the selected field(s)?");
define("NBILL_FORM_EDITOR_DELETE_SELECT", "Please select one or more fields to delete!");
define("NBILL_FORM_EDITOR_DELETE_SYSTEM", "You cannot delete system fields (previous/next/submit buttons)");
define("NBILL_FORM_EDITOR_CLOSE_GAPS", "Close Gaps?");
define("NBILL_FORM_EDITOR_CLOSE_GAPS_HELP", "In the event that one or more fields are not published, having this option checked will move any subsequent fields to close the gap(s) by placing them in the position that would have been occupied by the previous field (using the tab order to determine which field(s) to move where)");
define("NBILL_FORM_EDITOR_ONLOAD", "Onload Javascript");
define("NBILL_FORM_EDITOR_ONLOAD_HELP", "If you want to execute some javascript when this page loads, enter the script here.");
define("NBILL_FORM_EDITOR_EXTERNAL_JS_FILES", "External JS Files");
define("NBILL_FORM_EDITOR_EXTERNAL_JS_FILES_HELP", "If you want to load any external javascript files when this page is rendered, enter the full URL to each file here (separated by a line break if there is more than one).");
define("NBILL_FORM_EDITOR_PAGE_SUBMIT_CODE", "Page Submit Code");
define("NBILL_FORM_EDITOR_PAGE_SUBMIT_CODE_HELP", "Enter any PHP code that you want to execute when this page is submitted (ie. when the user moves from this page to the next page). Set the \$abort flag to true if you want to abandon the page submission and keep the user on this page.");
define("NBILL_FORM_DELETE_PAGE", "Delete this page");
define("NBILL_FORM_DELETE_PAGE_SURE", "Are you sure you want to delete page %s INCLUDING all the fields it contains?");
define("NBILL_FORM_SELECT_ALL", "Select All Fields");
define("NBILL_EDIT_PAGE_INTRO", "+ Click to Edit Page Intro");
define("NBILL_HIDE_PAGE_INTRO", "- Click to Hide Page Intro");
define("NBILL_EDIT_PAGE_FOOTER", "+ Click to Edit Page Footer");
define("NBILL_HIDE_PAGE_FOOTER", "- Click to Hide Page Footer");

//Validation
define("NBILL_ADVANCED_PROPERTY_WARNING", "WARNING! Properties on this tab are for advanced users only. Do not use these properties unless you are familiar with HTML and/or SQL, otherwise you could seriously mess up your form!");
define("NBILL_DATE_NOT_VALID", "The field `%s` requires a date value. Please only enter a date here in the format %s.");
define("NBILL_FIELD_NAME_MANDATORY", "Field name is mandatory");
define("NBILL_FIELD_NAME_ALPHA_FIRST", "The first character in a field name must be a letter, not a number");
define("NBILL_FIELD_NAME_NO_SPACES", "No spaces are allowed in field names");
define("NBILL_FIELD_NAME_ALPHANUM", "Only letters and numbers are allowed in field names");
define("NBILL_FIELD_NAME_PREV_NEXT", "Field names cannot start with `prev_` or `next_` as these names are used for the navigation buttons");
define("NBILL_FIELD_NAME_IN_USE_WARNING", "WARNING! The field name `%s` is already being used by another field. Although this is allowed, it might cause undesirable side effects! Are you sure you want to use this name?");

//Field Properties
define("NBILL_FORM_FIELD_ID", "ID");
define("NBILL_FORM_FIELD_TYPE", "Type");
define("NBILL_FORM_FIELD_NAME", "Name");
define("NBILL_FORM_FIELD_PAGE_NO", "Page Number");
define("NBILL_FORM_FIELD_COORDS", "Coordinates");
define("NBILL_FORM_FIELD_X_POS", "X");
define("NBILL_FORM_FIELD_Y_POS", "Y");
define("NBILL_FORM_FIELD_Z_POS", "Z");
define("NBILL_FORM_FIELD_TAB_ORDER", "Tab Order");
define("NBILL_FORM_EDITOR_AUTO_TAB", "Auto-tab on Save?");
define("NBILL_FORM_FIELD_LABEL", "Label");
define("NBILL_FORM_FIELD_SUFFIX", "Checkbox Label");
define("NBILL_FORM_FIELD_HORIZONTAL", "Horizontal Options");
define("NBILL_FORM_FIELD_REQUIRED", "Required");
define("NBILL_FORM_FIELD_NOT_REQUIRED", "Not Required");
define("NBILL_FORM_FIELD_ORDERING", "Order");
define("NBILL_FORM_PUBLISHED", "Published");
define("NBILL_FORM_PUBLISHED_ALL", "Visible to everyone");
define("NBILL_FORM_UNPUBLISHED", "Not visible to anyone");
define("NBILL_FORM_CLIENTS_ONLY", "Visible to existing clients only");
define("NBILL_FORM_NEW_ONLY", "Visible to new clients only");
define("NBILL_FORM_FIELD_PUBLISHED_HELP", "Whether or not to display this field on the form in the website front-end.");
define("NBILL_FORM_FIELD_ATTRIBUTES", "HTML Attributes");
define("NBILL_FORM_FIELD_DEFAULT_VALUE", "Default Value");
define("NBILL_FORM_FIELD_PRE_FIELD", "Pre-Field");
define("NBILL_FORM_FIELD_POST_FIELD", "Post-Field");
define("NBILL_FORM_FIELD_HELP_TEXT", "Help Text");
define("NBILL_FORM_FIELD_RELATED_PRODUCT_CAT", "Related Product Category");
define("NBILL_FORM_FIELD_RELATED_PRODUCT", "Related Product");
define("NBILL_FORM_FIELD_VALUE_REQUIRED_FOR_ORDER", "Value Required for Order");
define("NBILL_FORM_FIELD_ATTRIBUTES_HELP", "Enter any extra HTML Attributes you want to add to the HTML control - eg. style=&quot;width:200px;&quot;. You can also add Javascript events here (if you don`t know what an HTML attribute or Javascript event is, just leave this blank).");
define("NBILL_FORM_FIELD_DEFAULT_VALUE_HELP", "If you want to pre-populate a value for this field, enter the value here (to select a checkbox, enter the word `On`). Label field types support HTML for this setting. You can also execute PHP code by surrounding it in double dollar signs, for example:<br />\$\$return nbf_common::nb_date(&quot;d/m/Y&quot;);\$\$ (WARNING! Fatal errors in any PHP code you enter will break " . NBILL_BRANDING_NAME . ", and you may need to modify the database directly to remove the offending code! Make sure your code is error-free before saving!)");
define("NBILL_FORM_FIELD_DEFAULT_VALUE_OPTIONS_HELP", "There are specific options defined this field (to change these, click on the `options` button for the field on the main field list). The only values that you will be able to select here are those defined option values (because they will be the only values available to the end user when they are using this form).");
define("NBILL_FORM_FIELD_HELP_TEXT_HELP", "You can enter some text here that will help the end user understand how to fill in this field. Where help text has been defined, an info icon will appear on the form, and the text will be displayed when the user hovers their mouse over it. NOTE: This can be a constant name (typically represented in upper case) which will allow the value to be picked up from the language file (after changing the value, apply or save changes to see the value from the language file instead of the constant name in the form designer). Alternatively you could just type the exact text you want to see.");
define("NBILL_FORM_FIELD_PRE_FIELD_HELP", "Anything you enter here will be output on the form immediately before the field itself. You can use HTML code if you wish.");
define("NBILL_FORM_FIELD_POST_FIELD_HELP", "Anything you enter here will be output on the form immediately after the field itself. You can use HTML code if you wish.");
define("NBILL_FORM_FIELD_RELATED_PRODUCT_CAT_HELP", "If you want the selection of a certain value for this field to result in a product being ordered, specify the category of the product here. NOTE: You can also specify different products for different option values using the `Options` button for the field on the main field list.");
define("NBILL_FORM_FIELD_RELATED_PRODUCT_HELP", "If you want the selection of a certain value for this field to result in a product being ordered, specify the product here. NOTE: You can also specify different products for different option values using the `Options` button for the field on the main field list.");
define("NBILL_FORM_FIELD_VALUE_REQUIRED_FOR_ORDER_HELP", "If you only want the above product to be ordered in the event that the this field matches a certain value, enter the value here. If you leave this blank AND select a product above, the product will be ordered if ANY value is entered for the field.");
define("NBILL_FORM_FIELD_VALUE_REQUIRED_FOR_ORDER_OPTIONS_HELP", "If you only want the above product to be ordered in the event that the this field matches a certain value, enter the value here. If you leave this set to `Not Applicable` AND select a product above, the product will be ordered if ANY value is entered for the field.");
define("NBILL_FORM_FIELD_ORDER_VALUE", "Order Value");
define("NBILL_FORM_FIELD_ORDER_VALUE_HELP", "If you want to use the value from this field as one of the order values, select the order value here (see also the `Order` tab on the main form editing screen).");
define("NBILL_FORM_FIELD_ID_HELP", "The unique identifier for the field (used with the settings on the `Order` tab for specifying what to do with the field values).");
define("NBILL_FORM_FIELD_TYPE_HELP", "The type of HTML control to display for the field. Option lists and dropdown lists allow you to specify a number of options to choose from. The `E-Mail Address` field type is just a textbox, but when the form is submitted the value entered is validated to make sure it is a valid e-mail address.");
define("NBILL_FORM_FIELD_NAME_HELP", "The unique name for the control on the form. This cannot have any spaces or special characters like punctuation (except underscore characters). It is not displayed to the user.");
define("NBILL_FORM_FIELD_PAGE_NO_HELP", "The page number this field appears on (if no fields appear on a page, that page will not be shown in the front end).");
define("NBILL_FORM_FIELD_COORDS_HELP", "Controls the positioning of the field on the form. X defines how many pixels from the left to start the field label, Y defines how many pixels from the top, and Z defines the layer on which the field will appear (ie. in case it overlaps another element, you can control which item appears on top).");
define("NBILL_FORM_FIELD_TAB_ORDER_HELP", "Controls the order in which fields are rendered on the page, and therefore the order in which most browsers will move the caret when the user hits the tab key. It is also used to determine how to close gaps in case a field is not published (if applicable).");
define("NBILL_FORM_PAGE_TAB_ORDER_HELP", "Automatically generate the tab order values for all fields on this page (assigns a numerical tab order according to the position of each field on the page - from top to bottom and left to right). If auto-tab is switched on (below), you don`t need to bother clicking this button!");
define("NBILL_FORM_EDITOR_AUTO_TAB_HELP", "Whether or not to automatically re-generate the tab order for this page whenever the form is saved (recommended in most cases).");
define("NBILL_AUTO_TAB", "Auto Generate...");
define("NBILL_AUTO_TAB_SURE", "This will automatically set the tab order of EVERY field on THIS PAGE according to its position on the page.");
define("NBILL_FORM_FIELD_LABEL_HELP", "The label which is displayed to the user for this control. Appears to the left of the control. NOTE: This can be a constant name (typically represented in upper case) which will allow the value to be picked up from the language file (after changing the value, apply or save changes to see the value from the language file instead of the constant name in the form designer). Alternatively you could just type the exact text you want to see.");
define("NBILL_FORM_FIELD_SUFFIX_HELP", "If the field type is a checkbox, enter the text for the checkbox here (this text appears to the right of the actual tick box)");
define("NBILL_FORM_FIELD_HORIZONTAL_HELP", "If the field type supports it (typically only for option lists), this setting controls whether the options are output horizontally (all on the same line), or vertically (where a line break is inserted between each option).");
define("NBILL_FORM_FIELD_MERGE_COLS", "Merge Columns?");
define("NBILL_FORM_FIELD_MERGE_COLS_HELP", "Whether or not to merge the label column with the value column when displaying the form");
define("NBILL_FORM_FIELD_XREF", "Cross Reference");
define("NBILL_FORM_FIELD_XREF_HELP", "If you want to populate the options of a dropdown list or option list based on the values held in a database table (rather than defining each option manually), you can specify the table here. To add your own cross reference table, just create a table in the database with a prefix of `%snbill_xref_`");
define("NBILL_FORM_FIELD_XREF_SQL", "XRef SQL");
define("NBILL_FORM_FIELD_XREF_SQL_HELP", "If you select to populate the options using the special [SQL List] cross reference table, you can enter the SQL here to return a list of name/value pairs (use `code` and `description` as the column aliases)");
define("NBILL_FORM_FIELD_CONFIRMATION", "Show Confirmation?");
define("NBILL_FORM_FIELD_CONFIRMATION_HELP", "Select whether or not to prompt the user to enter the information a 2nd time so as to avoid typing mistakes (typically for email address or password field types).");
define("NBILL_FORM_FIELD_INCLUDE_ON_FORMS", "Include on Forms?");
define("NBILL_FORM_FIELD_INCLUDE_ON_FORMS_HELP", "Whether or not to include this field on new order forms and quote request forms.");
define("NBILL_FORM_FIELD_SUMMARY", "Show on Summary?");
define("NBILL_FORM_FIELD_SUMMARY_HELP", "Whether or not to show the value of this field on the order summary page");
define("NBILL_FORM_FIELD_SUMMARY_SHOW", "Always show on summary page");
define("NBILL_FORM_FIELD_SUMMARY_NO_SHOW", "Never show on summary page");
define("NBILL_FORM_FIELD_SUMMARY_IF_VALUE", "Only show if field holds a value");
define("NBILL_FORM_FIELD_CATS", "Related Product Category");
define("NBILL_FORM_FIELD_CATS_HELP", "To associate this field with a product, first select the product category here.");
define("NBILL_FORM_FIELD_PRODUCTS", "Related Product");
define("NBILL_FORM_FIELD_PRODUCTS_HELP", "To associate this field with a product, select the product here.");
define("NBILL_FORM_FIELD_QTY", "Related Product Quantity");
define("NBILL_FORM_FIELD_QTY_HELP", "Enter the quantity of the related product to order, or if you want to pick up the quantity from the value that is typed in to a particular field by the end user, enter the field ID surrounded by double-hashes - eg. ##21##");
define("NBILL_FORM_FIELD_OVERRIDE_FREQ", "Override Frequency?");
define("NBILL_FORM_FIELD_OVERRIDE_FREQ_HELP", "If you set this to yes, the payment frequency for any product assigned to this field will be set to one-off, regardless of the payment frequency for the rest of the form (this allows you to have both one-off and recurring orders on the same form).");
define("NBILL_FORM_FIELD_VALUE_FOR_ORDER", "Value Required for Order");
define("NBILL_FORM_FIELD_VALUE_FOR_ORDER_HELP", "If you only want the associated product to be ordered if the value entered or selected for this field matches a certain value, enter the value to match against here.");
define("NBILL_FORM_FIELD_ENTITY_MAP", "Client Field Mapping");
define("NBILL_FORM_FIELD_ENTITY_MAP_HELP", "If you want the value of this field to be used to populate an equivalent field on the Client record, select the client record field here. Where more than one field maps to the same client field, the values will be concatenated (joined together) and separated by a space. Where the user is not the only contact associated with the client record, or belongs to more than one client record, this field will appear separately from the contact data (once for each client record).");
define("NBILL_FORM_FIELD_CONTACT_MAP", "Contact Field Mapping");
define("NBILL_FORM_FIELD_CONTACT_MAP_HELP", "If you want the value of this field to be used to populate an equivalent field on the Contact record, select the contact record field here. Where more than one field maps to the same contact field, the values will be concatenated and separated by a space.");
define("NBILL_CUSTOM_FIELD", "Custom Field");
define("NBILL_FORM_FIELD_OPTIONS", "Options...");
define("NBILL_FORM_FIELD_OPTIONS_HELP", "For dropdown lists and option lists only, click the button to open a popup dialog which will allow you to define the options. Alternatively, you could use a cross reference table to load the options from the database (advanced users only - see Advanced property tab).");
define("NBILL_FORM_VOUCHER_CODE", "Discount Voucher Code");
define("NBILL_FORM_ORDER_GATEWAY", "Payment Gateway");
define("NBILL_FORM_RELATING_TO", "Relating to");
define("NBILL_FORM_SHIPPING_ID", "Shipping ID");
define("NBILL_FORM_TAX_EXEMPTION_CODE", "Tax Exemption Code");
define("NBILL_FORM_PAYMENT_FREQUENCY", "Payment Frequency Code");
define("NBILL_FORM_CURRENCY", "Currency");
define("NBILL_FORM_UNIQUE_INVOICE", "Unique Invoice?");
define("NBILL_FORM_AUTO_RENEW", "Auto Renew?");
define("NBILL_ORDER_FORM_EXPIRE_AFTER", "Number of Billing Cycles");
define("NBILL_FORM_EXPIRY_DATE", "Expiry Date (%s)");
define("NBILL_ERR_FLD_NAME_IS_RESERVED_WORD", "Sorry, `%s` is a reserved word that is used by " . NBILL_BRANDING_NAME . " - you cannot use it as a field name.");

//Options
define("NBILL_OPTIONS_LOADING", "Loading Options, Please wait...");
define("NBILL_FORM_FIELD_EDIT_OPTIONS", "Edit Field Options");
define("NBILL_FORM_FIELD_OPTIONS_INTRO", "Specify the options from which the end-user can select for this field (note: this is only applicable to field types that allow multiple choice - ie. dropdown lists and option lists)");
define("NBILL_OPTION", "Option");
define("NBILL_NEW_OPTION", "New");
define("NBILL_OPTION_VALUE", "Value");
define("NBILL_OPTION_DESCRIPTION", "Description");
define("NBILL_OPTION_ORDERING", "Ordering");
define("NBILL_OPTION_PRODUCT", "Related Product");
define("NBILL_OPTION_VALUE_HELP", "This is the actual value that will be stored for the field if the user selects this option. This can be different to the text or value that is actually displayed to the user.");
define("NBILL_OPTION_DESCRIPTION_HELP", "This is the text that is displayed to the user for this option.");
define("NBILL_OPTION_PRODUCT_HELP", "If selecting this option should cause a particular product to be ordered, you can specify which product here. Select a category from the first list, and the actual product from the second.");
define("NBILL_OPTION_DELETE", "Delete");
define("NBILL_OPTION_ADD_NEW", "Add");
define("NBILL_FIELD_OPTION_ENTER_VALUE", "Please enter the value for the new option.");
define("NBILL_FIELD_OPTION_ENTER_DESCRIPTION", "Please enter the description for the new option (this can be the same as the value, but doesn`t have to be).");
define("NBILL_SAVE_ORDERING", "Save Ordering");
define("NBILL_OPTION_QUANTITY", "Qty");
define("NBILL_OPTION_QUANTITY_HELP", "Either a static value for the quantity of items to order (eg. `1`), or a token representing a field value (eg. ##23## - where 23 is the internal field id). You can use the field id for this field if you wish. NOTE: Only applicable if you have specified a related product. If left blank, the value will default to 1.");

//Navigation
define("NBILL_FORM_PREV_BUTTON", "Page %s Previous Button");
define("NBILL_FORM_NEXT_BUTTON", "Page %s Next/Submit Button");

//Save
define("NBILL_FORM_CANNOT_UNSERIALIZE", "There was a problem transfering the form data to the server. The form cannot be saved. Please contact support.");

//Version 2.1.0
define("NBILL_FORM_PRODUCT_ID", "Product ID");
define("NBILL_FORM_PRODUCT_SKU", "Product SKU");
define("NBILL_FORM_FIELD_PRODUCT_LIST", "Product List");
define("NBILL_FORM_FIELD_GATEWAY_LIST", "Gateway List");
define("NBILL_FORM_FILED_PRODUCT_LIST_ALL", "All");
define("NBILL_AUTO_SET_PRODUCT_ORDER_VALUE", "If a product is selected from this list, do you want it to be ordered when the form is submitted?");
define("NBILL_FORM_WARNING_DELETE_OPTIONS", "WARNING! Changing this field type will cause your manually defined list options to be deleted. Are you sure you want to do this?");

//Version 2.1.1
define("NBILL_OPTIONS_WARN_DUPLICATE_VALUES", "WARNING! You have more than one option defined with the same value. This might be OK if the duplicated values are just placeholders, but otherwise it should be avoided! Click OK to save anyway or Cancel to amend.");
define("NBILL_FORM_PAGE_LEGACY_RENDERER", "Render in a Table?");
define("NBILL_INSTR_FORM_PAGE_LEGACY_RENDERER", "If you set this to `yes`, the fields will be rendered in a table instead of being absolutely positioned. This means the fields might not be positioned exactly where you put them in the editor, but it can help resolve some layout problems (especially with the order summary control, which can vary in size depending on the values entered on the form by the end user).");
define("NBILL_FORM_PAGE_LEGACY_TABLE_BORDER", "Legacy Table Border?<br />(Deprecated)");
define("NBILL_INSTR_FORM_PAGE_LEGACY_TABLE_BORDER", "This setting is deprecated and should be set to `no`. In previous versions of " . NBILL_BRANDING_NAME . ", you could specify that the table containing your fields should have a border. This generally looks rubbish, is not semantic, and was a bad idea, but the option is included here for the benefit of those who are migrating from a previous version and wish to keep it. This option will only take effect if `Render in a Table` is set to `yes`.");

//Version 2.2.0
define("NBILL_FORM_FIELD_ORDER_LIST", "Existing Order List");
define("NBILL_FORM_FIELD_PREREQ_ORDER_LIST", "Prerequisite Order List");

//Version 2.6.2
define("NBILL_FORM_EDITOR_TAB_EXTENDED", "Extended");
define("NBILL_FORM_EDITOR_EXTENDED_UPDATING", "Updating...");
define("NBILL_FORM_EDITOR_EXTENDED_NONE", "There are no extended properties for this field type");

//Version 2.7.0
define("NBILL_FORM_PAGE_RENDERER", "Renderer");
define("NBILL_FORM_PAGE_RENDERER_ABSOLUTE", "Absolute");
define("NBILL_FORM_PAGE_RENDERER_RESPONSIVE", "Responsive");
define("NBILL_FORM_PAGE_RENDERER_TABLE", "Table");
define("NBILL_INSTR_FORM_PAGE_RENDERER", "Form pages can be rendered in 3 different ways. Absolute rendering positions fields exactly where you put them in the editor (your template styling rules might cause them to take up more or less space than in the editor, so some adjustment may be required) - this is NOT responsive for mobile devices, but allows for multi-column layouts and free-format positioning of fields. Responsive rendering tries to adapt to the screen width for optimal display on both desktop and mobile devices, but may not be positioned exactly where you place them in the editor - although you can override it to use absolute positioning for certain fields (this is the recommended renderer for most pages). Table rendering outputs the fields in a table which can fix some rendering problems and is usually also responsive for mobile devices, but the fields are simply output sequentially.");
define("NBILL_FORM_FIELD_OVERRIDE_ABSOLUTE", "Absolute Position<br />Override");
define("NBILL_FORM_FIELD_OVERRIDE_ABSOLUTE_HELP", "When using the responsive renderer (on the page properties), you can set this property to `yes` to force this field to be positioned absolutely - ie. at the exact co-ordinates you specify (instead of rendering it sequentially). This setting has no effect unless the page uses responsive rendering (see page properties).");