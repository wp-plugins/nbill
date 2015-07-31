<?php
/**
* nBill File upload Control Class file - for handling output and processing of file uploads on forms.
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

include_once(realpath(dirname(__FILE__)) . "/../custom/nbill.field.control.base.php");

/**
* File Upload
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_kk_default extends nbf_field_control
{
	/**
	* Renders the control
	*/
	protected function _render_control($admin = false)
	{
        $existing_filename = nbf_common::get_param($_REQUEST, 'ctl_' . $this->name . $this->suffix);
		if (!$admin && nbf_common::nb_strlen($existing_filename) > 0 && file_exists(nbf_cms::$interop->site_temp_path . "/" . $existing_filename) && is_file(nbf_cms::$interop->site_temp_path . "/" . $existing_filename))
        {
            //File already uploaded - show the name and offer to delete
            ?><span class="nbill_uploaded_file_name"><?php echo nbf_common::get_param($_REQUEST, 'ctl_' . $this->name . $this->suffix); ?></span>
            <input type="submit" name="ctl_<?php echo $this->name . $this->suffix; ?>_delete" id="ctl_<?php echo $this->id . $this->suffix; ?>" value="<?php echo NBILL_FILE_DELETE; ?>" />
            <input type="hidden" name="ctl_<?php echo $this->name . $this->suffix; ?>" id="ctl_<?php echo $this->id . $this->suffix; ?>" value="<?php echo nbf_common::get_param($_REQUEST, 'ctl_' . $this->name . $this->suffix); ?>" />
            <?php
        }
        else
        {
            ?><input type="file" name="ctl_<?php echo $this->name . $this->suffix; ?>" id="ctl_<?php echo $this->id . $this->suffix; ?>" value="<?php echo defined($this->value) ? str_replace("\"", "&quot;", constant($this->value)) : str_replace("\"", "&quot;", $this->value); ?>" class="nbill_upload_control" size="15" <?php if ($admin) { ?>onclick="<?php echo $this->onclick_admin; ?>" onchange="<?php echo $this->onchange_admin; ?>" <?php } echo $this->attributes; ?> /><?php
        }
	}

    /**
    * Make sure the value held is valid for this type of field
    * @param string $error_message If the value is not valid, this output parameter should be populated with an appropriate message
    * @return boolean Whether or not validation passed successfully
    */
    function validate(&$error_message)
    {
        if (nbf_common::nb_strlen(@$_FILES['ctl_' . $this->name . $this->suffix]['name']) > 0)
        {
            $nb_database = nbf_cms::$interop->database;
            $sql = "SELECT max_upload_size, allowed_types FROM #__nbill_order_form WHERE id = " . intval($this->form_id);
            $nb_database->setQuery($sql);
            $form_upload_data = null;
            $nb_database->loadObject($form_upload_data);
            if ($form_upload_data)
            {
                $max_upload_size = $form_upload_data->max_upload_size;
                $allowed_types = $form_upload_data->allowed_types;
            }
            else
            {
                $max_upload_size = 2000;
                $allowed_types = '.jpg|.gif|.png|.bmp|.txt|.pdf|.odt|.doc|.csv|.xls';
            }

            if (count($_FILES) > 0)
            {
                $file_info = $_FILES['ctl_' . $this->name];
                if ($file_info['size'] > intval($max_upload_size) * 1000)
                {
                    $error_message = sprintf(NBILL_FILE_TOO_BIG, $max_upload_size);
                    return false;
                }
                else if ($file_info['size'] > 0)
                {
                    //Check file extension (no point checking mime type, as many browsers don't bother checking)
                    $type_allowed = true;
                    $allowed_types = explode("|", $allowed_types);
                    if (count($allowed_types) > 0)
                    {
                        $type_allowed = false;
                        foreach ($allowed_types as $allowed_type)
                        {
                            $allowed_type = str_replace(".", "", $allowed_type);
                            if (nbf_common::nb_strlen($file_info['name']) > nbf_common::nb_strlen($allowed_type) &&
                                    nbf_common::nb_substr($file_info['name'], nbf_common::nb_strlen($file_info['name']) - (nbf_common::nb_strlen($allowed_type) + 1)) == ".$allowed_type")
                            {
                                $type_allowed = true;
                                break;
                            }
                        }
                    }
                    if (!$type_allowed)
                    {
                        $error_message = sprintf(NBILL_FILE_TYPE_NOT_ALLOWED, implode("&nbsp; " ,$allowed_types));
                        return false;
                    }
                }
                else if ($file_info['error'])
                {
                    $error_reason = "";
                    switch ($file_info['error'])
                    {
                        case UPLOAD_ERR_INI_SIZE:
                            $error_reason = NBILL_ERR_UPLOAD_ERR_INI_SIZE;
                            break;
                        case UPLOAD_ERR_FORM_SIZE: //Should never happen unless someone has hacked the order.html.php file to add the MAX_FILE_SIZE directive back in (which doesn't work!)
                            $error_reason = NBILL_UPLOAD_ERR_FORM_SIZE;
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $error_reason = NBILL_UPLOAD_ERR_PARTIAL;
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $error_reason = NBILL_UPLOAD_ERR_NO_FILE;
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $error_reason = NBILL_UPLOAD_ERR_NO_TMP_DIR;
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $error_reason = NBILL_UPLOAD_ERR_CANT_WRITE;
                            break;
                    }
                    if (nbf_common::nb_strlen($error_reason) > 0)
                    {
                        $error_message = NBILL_FILE_UPLOAD_FAILED_REASON . " " . $error_reason;
                    }
                    else
                    {
                        $error_message = NBILL_FILE_UPLOAD_FAILED;
                    }
                    return false;
                }
            }
        }
        return true;
    }

    public function process(&$message)
    {
        if (nbf_common::get_param($_REQUEST, 'ctl_' . $this->name . $this->suffix . '_delete'))
        {
            //Attempt to delete the file from the temp directory
            if (file_exists(nbf_cms::$interop->site_temp_path . "/" . nbf_common::get_param($_REQUEST, 'ctl_' . $this->name . $this->suffix)))
            {
                @unlink(nbf_cms::$interop->site_temp_path . "/" . nbf_common::get_param($_REQUEST, 'ctl_' . $this->name . $this->suffix));
            }
            $_REQUEST['ctl_' . $this->name . $this->suffix] = null;
            $_POST['ctl_' . $this->name . $this->suffix] = null;
        }
    }

    /**
    * Copy the uploaded file to the temp directory and make a note of the file name (also perform some housekeeping on old temp files)
    */
    public function page_submit(&$message)
    {
        //Copy any uploaded files to the temp directory (add suffix if already exists in EITHER temp OR final destination folder)
        $abort = false;
        clearstatcache();
        $nb_database = nbf_cms::$interop->database;

        //Get the final destination folder
        $sql = "SELECT upload_path FROM #__nbill_order_form WHERE id = " . intval($this->form_id);
        $nb_database->setQuery($sql);
        $upload_path = $nb_database->loadResult();
        if (nbf_common::nb_substr($upload_path, nbf_common::nb_strlen($upload_path) - 1) == "/" || nbf_common::nb_substr($upload_path, nbf_common::nb_strlen($upload_path) - 1) == "\\")
        {
            $upload_path = nbf_common::nb_substr($upload_path, 0, nbf_common::nb_strlen($upload_path) - 1); //Remove trailing slash
        }

        if (count($_FILES) > 0)
        {
            foreach ($_FILES as $key=>$file_info)
            {
                if ($key == 'ctl_' . $this->name . $this->suffix && $file_info['size'] > 0)
                {
                    $new_file_name = nbf_cms::$interop->site_temp_path;
                    if (nbf_common::nb_substr($new_file_name, nbf_common::nb_strlen($new_file_name) - 1, 1) != "/" &&
                            nbf_common::nb_substr($new_file_name, nbf_common::nb_strlen($new_file_name) - 1, 1) != "\\")
                    {
                        $new_file_name .= "/";
                    }
                    if (!nbf_file::is_folder_writable($new_file_name))
                    {
                        $message = NBILL_FILE_UPLOAD_FAILED_REASON . NBILL_UPLOAD_PATH_NOT_WRITABLE;
                        $abort = true;
                        continue;
                    }
                    $new_file_name .= nbf_common::nb_filename_safe($file_info['name']);
                    $loop_counter = 1;
                    $path_parts = pathinfo($new_file_name);
                    $extension = "." . $path_parts['extension'];
                    $root_file_name = $path_parts['dirname'] . "/" . $path_parts['filename'];
                    if (file_exists($new_file_name) && is_file($new_file_name))
                    {
                        //If it is older than 48h, delete it
                        if (filemtime($new_file_name) < nbf_common::nb_strtotime("-48 hours"))
                        {
                            @unlink($new_file_name);
                        }
                    }
                    $final_file_name = $upload_path . "/" . $path_parts['basename'];
                    $root_final_file_name = $upload_path . "/" . $path_parts['filename'];
                    while (file_exists($new_file_name) || file_exists($final_file_name))
                    {
                        $loop_counter++;
                        if ($loop_counter > 1000)
                        {
                            $message = NBILL_FILE_UPLOAD_FAILED_REASON . NBILL_UPLOAD_ERR_FILE_EXISTS;
                            $new_file_name = "";
                            break;
                        }
                        $new_file_name = $root_file_name . "_$loop_counter" . "$extension";
                        $final_file_name = $root_final_file_name . "_$loop_counter" . "$extension";
                    }
                    if (nbf_common::nb_strlen($new_file_name) == 0)
                    {
                        $abort = true;
                        continue;
                    }
                    if (!move_uploaded_file($file_info['tmp_name'], $new_file_name))
                    {
                        $message = NBILL_FILE_UPLOAD_FAILED_REASON . NBILL_UPLOAD_PATH_NOT_WRITABLE;
                        $abort = true;
                        continue;
                    }
                    else
                    {
                        //Make a note of the file name
                        $_POST['ctl_' . $this->name . $this->suffix] = basename($new_file_name);
                        $_REQUEST['ctl_' . $this->name . $this->suffix] = basename($new_file_name);
                    }
                }
            }
        }
        return !$abort;
    }

    /**
    * Copy uploaded files from temp directory to their final destination
    * @param boolean $abort Whether or not to abort the form submission and stay on the last page
    */
    public function form_submit()
    {
        $nb_database = nbf_cms::$interop->database;

        $new_file_name = nbf_cms::$interop->site_temp_path . "/" . nbf_common::nb_filename_safe(nbf_common::get_param($_REQUEST, 'ctl_' . $this->name . $this->suffix));
        if (nbf_common::nb_strlen($new_file_name) > 0 && file_exists($new_file_name) && is_file($new_file_name))
        {
            //Get the final destination folder
            $sql = "SELECT upload_path FROM #__nbill_order_form WHERE id = " . intval($this->form_id);
            $nb_database->setQuery($sql);
            $upload_path = $nb_database->loadResult();
            if (nbf_common::nb_substr($upload_path, nbf_common::nb_strlen($upload_path) - 1) == "/" || nbf_common::nb_substr($upload_path, nbf_common::nb_strlen($upload_path) - 1) == "\\")
            {
                $upload_path = nbf_common::nb_substr($upload_path, 0, nbf_common::nb_strlen($upload_path) - 1); //Remove trailing slash
            }
            $path_parts = pathinfo($new_file_name);
            $extension = "." . $path_parts['extension'];
            $final_file_name = $upload_path . "/" . $path_parts['basename'];
            $root_final_file_name = $upload_path . "/" . $path_parts['filename'];
            $loop_counter = 1;
            while (file_exists($final_file_name))
            {
                $loop_counter++;
                if ($loop_counter > 1000)
                {
                    $message = NBILL_FILE_UPLOAD_FAILED_REASON . NBILL_UPLOAD_ERR_FILE_EXISTS;
                    $final_file_name = "";
                    break;
                }
                $final_file_name = $root_final_file_name . "_$loop_counter" . "$extension";
            }
            if (nbf_common::nb_strlen($final_file_name) > 0)
            {
                if (!rename($new_file_name, $final_file_name))
                {
                    $message = NBILL_FILE_UPLOAD_FAILED_REASON . NBILL_UPLOAD_COPY_FAILED;
                    return false;
                }
                $_POST['ctl_' . $this->name . $this->suffix] = basename($final_file_name);
                $_REQUEST['ctl_' . $this->name . $this->suffix] = basename($final_file_name);
                return true;
            }
            else
            {
                $message = NBILL_FILE_UPLOAD_FAILED_REASON . NBILL_UPLOAD_ERR_FILE_EXISTS;
                return false;
            }
        }
        return true;
    }
}