<?php
/**
* Language file for the Profile Fields feature
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

define("NBILL_PROFILE_FIELDS_TITLE", "Client Profile Fields");
define("NBILL_PROFILE_FIELDS_INTRO", "You can define here which fields will be displayed on the My Profile page, and map them to the values held against contact or client records. You can even create additional fields that will be held on the client and/or contact records. Also, in most cases, order forms and quote forms need to prompt for personal information that can be used to create a client profile and/or contact record automatically. Rather than creating the fields yourself for each form, you can indicate which of the default profile fields defined here should appear on new forms by default (they can still be edited or removed from the form afterwards).");
define("NBILL_PROFILE_FIELD_REQUIRED", "Required?");
define("NBILL_PROFILE_FIELD_PUBLISHED", "Published?");
define("NBILL_PROFILE_FIELD_ORDERING", "Ordering");
define("NBILL_PROFILE_FIELD_NOT_REQUIRED", "Non-mandatory");
define("NBILL_EDIT_PROFILE_FIELD", "Edit Field");
define("NBILL_PROFILE_FIELD_NAME_REQUIRED", "Please specify a name for the field");
define("NBILL_PROFILE_FIELD_RESERVED_WORD", "Sorry, `%s` is a reserved word with a special meaning. Please select a different name for this field.");
define("NBILL_PROFILE_FIELD_NAME_NUMERIC", "Sorry, you cannot use a plain number as the field name - please add at least one letter");
define("NBILL_NEW_PROFILE_FIELD", "New Field");
define("NBILL_PROFILE_FIELD_DETAILS", "Field Details");
define("NBILL_INSTR_PROFILE_FIELD_PUBLISHED", "Whether or not to use this field on the My Profile page.");
define("NBILL_PROFILE_FIELD_UPDATE_FORMS", "WARNING! A field with this name and client/contact mapping is already being used on one or more forms. If you would like to apply your changes to the corresponding field on all of your existing forms, click OK.");
define("NBILL_PROFILE_FIELD_IN_USE", "In Use?");
define("NBILL_PROFILE_FIELD_IN_USE_YES", "In Use");
define("NBILL_PROFILE_FIELD_IN_USE_NO", "Not In Use");
define("NBILL_PROFILE_FIELD_IN_USE_HELP", "Whether or not any fields on any existing forms have the same name, client mapping, and contact mapping as this field (and therefore may be updated if this field is updated).");
define("NBILL_PROFILE_FIELD_SAVE_FAILED", "Sorry, a problem occurred whilst saving the field - it is not known whether the field was saved successfully or not. This could be due to a transient communication problem between your browser and the web server. Please try again.");
define("NBILL_OPTIONS_CANNOT_UNSERIALIZE", "There was a problem transfering the data to the server. The field cannot be saved. Please contact support.");

//Version 2.0.9
define("NBILL_PROFILE_FIELD_PUBLISHED_YES", "Published");
define("NBILL_PROFILE_FIELD_PUBLISHED_NO", "Not Published");