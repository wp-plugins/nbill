<?php
/**
* Class file just containing static methods relating to the file system.
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

/**
* Static functions relating to the file system
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_file
{
    /**
    * As the PHP is_writable function does not always work on Windows, the best way to be sure
    * is to actually try writing a file. Pass in the folder name (with or without trailing slash)
    * to write a temporary file there, then delete it, and return whether or not it was successful.
    * @param string $path The full path to the folder you want to check (with or without trailing slash).
    * @return boolean Whether or not the folder is writable.
    */
    public static function is_folder_writable($path)
    {
        //Add trailing slash if not present
        if (nbf_common::nb_substr($path, nbf_common::nb_strlen($path) - 1, 1) != "/" && substr($path, nbf_common::nb_strlen($path) - 1, 1) != "\\")
        {
            $path .= "/";
        }
        //Generate a temporary file name
        $tmp_file_name = md5(uniqid()) . ".tmp";
        //Open the file for writing
        $handle = @fopen($path . $tmp_file_name, "w");
        if (!$handle)
        {
            //Could not open for writing
            return false;
        }
        //Try writing some text
        if (!@fwrite($handle, "test"))
        {
            //Opened file ok, but could not write the text - attempt to clean up and return false
            @fclose($handle);
            @unlink($path . $tmp_file_name);
            return false;
        }
        //Wrote ok - clean up and return true
        @fclose($handle);
        @unlink($path . $tmp_file_name);
        return true;
    }

    /** Test whether file is writable (by writing to it! so ONLY use on a test file) using PHP file functions and/or FTP
    * @param string $file_name Test file name to write to (full path and file name)
    * @param boolean $use_ftp If false, it will try to use PHP file functions first, and FTP if that fails
    * @param string $error_message If an error occurs, this will be populated and the function will return false
    * @return boolean Whether or not the file is writable
    */
    public static function is_test_file_writable(&$use_ftp, &$error_message)
    {
        $file_name = nbf_cms::$interop->nbill_admin_base_path . "/framework/test_write_file.txt";
        nbf_common::load_language("configuration");
        $success = false;
        $error_message = "";
        $test_string = mt_rand();

        if (!$use_ftp)
        {
            //Try editing the file using PHP file functions first
            $file_handle = @fopen($file_name, "w");
            @fwrite($file_handle, $test_string);
            @fclose($file_handle);
            $file_handle = @fopen($file_name, "r");
            $read_value = trim(@fread($file_handle, 8192));
            if ($read_value == $test_string)
            {
                $success = true;
            }
            @fclose($file_handle);
        }

        //If that fails (or is skipped), try FTP
        if (!$success)
        {
            $use_ftp = true;
            include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.config.php");
            $handle = false;
            if (nbf_common::nb_strlen(nbf_config::$ftp_address) > 0 && function_exists("ftp_connect"))
            {
                $handle = @ftp_connect(nbf_config::$ftp_address, nbf_config::$ftp_port, 1);
            }
            if ($handle !== false)
            {
                if (@ftp_login($handle, nbf_config::$ftp_username, nbf_config::$ftp_password))
                {
                    $ch_dir = @ftp_chdir($handle, str_replace(nbf_config::$ftp_root, "", dirname($file_name) . "/"));
                    if ($ch_dir)
                    {
                        //Test writableness
                        $memory_handle = @fopen("php://memory", "w");
                        @fwrite($memory_handle, $test_string);
                        @rewind($memory_handle);
                        if (@ftp_fput($handle, basename($file_name), $memory_handle, FTP_ASCII))
                        {
                            //Try reading the file to make sure the value was uploaded correctly
                            $read_memory_handle = @fopen("php://memory", "r+");
                            @ftp_fget($handle, $read_memory_handle, basename($file_name), FTP_ASCII);
                            @rewind($read_memory_handle);
                            $read_value = trim(@fread($read_memory_handle, 8192));
                            if (trim(str_replace("\r\n", "\n", $read_value)) == trim(str_replace("\r\n", "\n", $test_string)))
                            {
                                $success = true;
                            }
                            else
                            {
                                //Wrote to file but could not verify that it was saved correctly
                                $error_message = NBILL_CFG_FTP_WRITE_OK_NO_READ;
                            }
                        }
                        else
                        {
                            //Could not write to file
                            $error_message = NBILL_CFG_FTP_OK_BUT_FILE_NOT_WRITTEN;
                        }
                    }
                    else
                    {
                        //Could not navigate to nBill folders
                        $error_message = NBILL_CFG_FTP_OK_BUT_NBILL_NOT_FOUND;
                    }
                }
                else
                {
                    //Could not login
                    $error_message = NBILL_CFG_FTP_LOGIN_FAILED;
                }
                @ftp_close($handle);
            }
            else
            {
                //Could not connect
                $error_message = NBILL_CFG_FTP_CONNECT_FAILED;
            }
        }
        return $success;
    }

    /**
    * Replace (or create) the given file with the given contents (during upgrade).
    * Tries to use PHP file functions, but if that does not work, uses FTP. If the directory does not exist, it will be created.
    * @param string $file_name Name of file
    * @param string $file_contents File contents as a string
    * @return boolean Whether or not the file was successfully replaced
    */
    public static function replace_file($file_name, $file_contents)
    {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/framework/nbill.config.php");

        //Make sure directory exists
        $path_parts = explode("\\", dirname($file_name)); //Windows uses backslashes
        $creation_path = "";
        foreach ($path_parts as $path_part)
        {
            if (nbf_common::nb_strlen($path_part) > 0)
            {
                $parts2 = explode("/", $path_part); //Other OS's use forward slashes
                foreach ($parts2 as $part)
                {
                    if (!@file_exists($creation_path . $part))
                    {
                        if (!@mkdir($creation_path . $part, 0755))
                        {
                            //Try using FTP to create the folder
                            $handle = false;
                            $success = false;
                            if (nbf_common::nb_strlen(nbf_config::$ftp_address) > 0)
                            {
                                $handle = @ftp_connect(nbf_config::$ftp_address, nbf_config::$ftp_port, 5);
                            }
                            if ($handle !== false)
                            {
                                if (@ftp_login($handle, nbf_config::$ftp_username, nbf_config::$ftp_password))
                                {
                                    @ftp_chdir($handle, str_replace(nbf_config::$ftp_root, "", $creation_path));
                                    @ftp_mkdir($handle, $part);
                                }
                                @ftp_close($handle);
                            }
                        }
                    }
                    $creation_path .= $part . "/"; //Even if using Windows, forward slash is understood
                }
            }
        }

        //Try creating or replacing the file using PHP
        $byte_count = file_put_contents($file_name, $file_contents);
        if ($byte_count >= strlen($file_contents))
        {
            return true;
        }

        //Failed to copy using file functions, so try FTP
        $handle = false;
        $success = false;
        if (nbf_common::nb_strlen(nbf_config::$ftp_address) > 0)
        {
            $handle = @ftp_connect(nbf_config::$ftp_address, nbf_config::$ftp_port, 5);
        }
        if ($handle !== false)
        {
            if (@ftp_login($handle, nbf_config::$ftp_username, nbf_config::$ftp_password))
            {
                $ch_dir = @ftp_chdir($handle, str_replace(nbf_config::$ftp_root, "", dirname($file_name) . "/"));
                if ($ch_dir)
                {
                    //Try writing
                    $memory_handle = @fopen("php://memory", "w");
                    @fwrite($memory_handle, $file_contents);
                    @rewind($memory_handle);
                    if (@ftp_fput($handle, basename($file_name), $memory_handle, FTP_ASCII))
                    {
                        //Try reading the file to make sure the value was uploaded correctly
                        $read_memory_handle = @fopen("php://memory", "r+");
                        @ftp_fget($handle, $read_memory_handle, basename($file_name), FTP_ASCII);
                        @rewind($read_memory_handle);
                        $read_value = trim(@fread($read_memory_handle, 8192));
                        if (trim(str_replace("\r\n", "\n", $read_value)) == trim(str_replace("\r\n", "\n", $file_contents)))
                        {
                            $success = true;
                        }
                    }
                }
            }
            @ftp_close($handle);
        }
        return $success;
    }

    /**
    * Recursively remove a directory and all of its contents
    */
    public static function remove_directory($dir, $delete_attachments = false)
    {
        $nb_database = nbf_cms::$interop->database;

        if(substr($dir, -1, 1) == "/")
        {
          $dir = nbf_common::nb_substr($dir, 0, nbf_common::nb_strlen($dir) - 1);
        }
        if ($handle = @opendir("$dir"))
        {
            while (false !== ($item = @readdir($handle)))
            {
                if ($item != "." && $item != "..")
                {
                    if (is_dir("$dir/$item"))
                    {
                        self::remove_directory("$dir/$item", $delete_attachments);
                    }
                    else
                    {
                        @unlink("$dir/$item");
                        if ($delete_attachments)
                        {
                            $sql = "DELETE FROM #__nbill_supporting_docs WHERE file_path = '" . $nb_database->getEscaped($dir) . "' AND file_name = '$item'";
                            $nb_database->setQuery($sql);
                            $nb_database->query();
                        }
                    }
                }
            }
            closedir($handle);
            return @rmdir($dir);
        }
    }

    public static function do_file_download($download_location)
    {
        //Download the file...
        $loopbreaker = 0;
        while (ob_get_length() !== false)
        {
            $loopbreaker++;
            @ob_end_clean();
            if ($loopbreaker > 15)
            {
                break;
            }
        }
        $file_extension = nbf_common::nb_strtolower(substr(strrchr($download_location,"."),1));
        switch( $file_extension )
        {
          case "pdf":
              $ctype="application/pdf";
              break;
          /*case "exe":
              $ctype="application/octet-stream";
              break;
          case "zip":
              $ctype="application/zip";
              break;*/
          case "doc":
              $ctype="application/msword";
              break;
          case "xls":
              $ctype="application/vnd.ms-excel";
              break;
          case "ppt":
              $ctype="application/vnd.ms-powerpoint";
              break;
          case "gif":
              $ctype="image/gif";
              break;
          case "png":
              $ctype="image/png";
              break;
          case "jpeg":
          case "jpg":
              $ctype="image/jpg";
              break;
          case "txt":
            $ctype="text/txt";
            break;
          case "wmv":
            $ctype="video/x-ms-wmv";
            break;
          default:
              $ctype="application/octet-stream";
              break;
        }

        //Required for IE, otherwise Content-disposition is ignored
        if(ini_get('zlib.output_compression')) {@ini_set('zlib.output_compression', 'Off');};

        header("Pragma: public"); // required
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false); // required for certain browsers
        header("Content-Type: $ctype");
        header("Content-Disposition: attachment; filename=\"" . basename($download_location) . "\";" );
        header("Content-Transfer-Encoding: binary");
        clearstatcache();
        header("Content-Length: ". @filesize($download_location));
        self::readfile_chunked($download_location);
        exit;
    }

    public static function readfile_chunked($filename, $retbytes = true)
    {
        //Based on unlicensed code in public domain from php.net user comments
        $chunksize = 1*(1024*1024); // how many bytes per chunk
        $buffer = '';
        $cnt =0;

        $handle = fopen($filename, 'rb');
        if ($handle === false)
        {
           return false;
        }
        while (!feof($handle))
        {
            set_time_limit(0);
            $buffer = fread($handle, $chunksize);
            echo $buffer;
            @ob_flush();
            @flush();
            if ($retbytes)
            {
                $cnt += strlen($buffer);
            }
        }
        $status = fclose($handle);
        if ($retbytes && $status)
        {
            return $cnt; // return num. bytes delivered like readfile() does.
        }
        return $status;
    }

    public static function nb_mkdir_recursive($directory)
    {
        //Create the specified directory (and all parent directories as required)
        $directory = str_replace("\\", "/", $directory);
        $cur_path = "";
        $folders = explode("/", $directory);
        foreach ($folders as $folder)
        {
            if (substr($folder, strlen($folder) - 1) == ":")
            {
                $cur_path .= $folder; //Windows drive letter
            }
            else
            {
                $cur_path .= "/" . $folder;
            }
            clearstatcache();
            if (!@file_exists($cur_path))
            {
                @mkdir($cur_path);
                clearstatcache();
            }
        }
        return @file_exists($directory);
    }
}