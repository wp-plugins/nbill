<?php
/**
* Main error handler for front end features
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

	set_error_handler("nbill_error_handler");
	global $fatal_error_handled;
	$fatal_error_handled = false;

	function fatal_error_handler($buffer)
	{
		global $fatal_error_handled;
		if(error_reporting() != 0)
		{
			restore_error_handler();
			$error_pos = nbf_common::nb_strpos($buffer, "<b>Fatal error</b>:");
			if ($error_pos !== false && !$fatal_error_handled)
			{
				//Parse output for error details ... eg.
				//<b>Fatal error</b>:  Call to undefined function:  madeupfunction() in <b>W:\www\letting\administrator\components\com_nbill\admin.nbill.php</b> on line <b>111</b><br />
				$loc_pos = nbf_common::nb_strpos($buffer, " in <b>", $error_pos + 19);
				$error_str = substr($buffer, $error_pos + 19, $loc_pos - ($error_pos + 19));
				$line_pos = nbf_common::nb_strpos($buffer, "</b> on line <b>", $loc_pos + 7);
				$error_loc = substr($buffer, $loc_pos + 7, $line_pos - ($loc_pos + 7));
				$end_pos = nbf_common::nb_strpos($buffer, "</b><br />", $line_pos + 16);
				$error_line = substr($buffer, $line_pos + 16, $end_pos - ($line_pos + 16));
				$fatal_error_handled = true; //Don't try to handle it again when the buffer gets cleared
				$result = nbill_error_handler(E_ERROR, $error_str, $error_loc, $error_line);
				return $result;
			}
			else
			{
                return $buffer;
			}
		}
		else
		{
            return $buffer;
        }
	}

	function nbill_error_handler($errno, $errstr, $errfile="", $errline=-1, $errcontext = array())
	{
		global $NBILL_SOFTWARE_VERSION;
		global $NBILL_SERVICE_PACK;

        //Ignore strict (PHP often logs them even if you ask it not to)
        if (defined("E_STRICT") && $errno == E_STRICT)
        {
            return;
        }

		if(error_reporting() != 0)
		{
			restore_error_handler();
            nbf_common::debug_trace("Error caught: $errno - $errstr in $errfile at line $errline");
			$mailsent = false;

			if ((nbf_common::nb_strlen($errfile) > 0 && (nbf_common::nb_strpos($errfile, "com_nbill") !== false || @nbf_common::nb_strpos($errfile, NBILL_BRANDING_COMPONENT_NAME) !== false)) &&
                    $errfile != nbf_cms::$interop->nbill_fe_base_path . "/error.handler.php" &&
					$errfile != nbf_cms::$interop->nbill_fe_base_path . "\\error.handler.php")
			{
				error_log($errstr . "; file: " . $errfile . "; line: " . $errline);

				switch($errno)
                {
                    case E_USER_NOTICE:
                    case E_NOTICE:
	                    break;
                    case E_USER_WARNING:
                    case E_WARNING:
                    case E_USER_ERROR:
                    case E_ERROR:
                        $nb_database = nbf_cms::$interop->database;
                        if (nbf_common::nb_strpos($errstr, "fatal protocol error") !== false) //This is not really an error - IIS fails to handle fgets correctly
                        {
	                        break;
                        }
                        if (nbf_common::nb_strpos($errstr, "FONT_EMBEDDING_MODE") !== false) //Intentional override of PDF generator constant
                        {
                            break;
                        }

                        $timestamp = nbf_common::nb_time();
                        $old_timestamp = $timestamp - 20160; //Keep for 2 weeks
                        $insert_id = 0;
                        $error_email = "";

                        if (method_exists($nb_database, "setQuery"))
                        {
	                        //Delete any old records (in case this keeps happening!)
	                        $sql = "DELETE FROM #__nbill_error_log WHERE time < " . $old_timestamp;
	                        @$nb_database->setQuery($sql);
	                        @$nb_database->query();

                            //If the same error has occurred within the last minute, don't bother logging it or emailing again
                            $sql = "SELECT id FROM #__nbill_error_log WHERE `message` = '" . addslashes($errstr) . "'
                                        AND filename = '" . addslashes($errfile) . "'
                                        AND linenum = " . intval($errline) . "
                                        AND `time` > " . time() - 60;
                            @$nb_database->setQuery($sql);
                            if (!@$nb_database->loadResult())
                            {
	                            //Insert new error log record
	                            $sql = "INSERT INTO #__nbill_error_log (severity, message, filename, linenum, time)
		                                 VALUES('$errno', '" . addslashes($errstr) . "', '" . addslashes($errfile) .
				                             "', $errline, " . $timestamp . ")";
	                            @$nb_database->setQuery($sql);
	                            @$nb_database->query();

                                $insert_id = $nb_database->insertid();

                                $sql = "SELECT error_email FROM #__nbill_configuration WHERE id = 1";
                                @$nb_database->setQuery($sql);
                                $error_email = @$nb_database->loadResult();
                            }
                        }

                        nbf_globals::$message = @NBILL_ERR_REPORT_INTRO;
                        if (nbf_common::nb_strlen(nbf_globals::$message) == 0 || nbf_globals::$message == "NBILL_ERR_REPORT_INTRO")
                        {
	                        nbf_globals::$message = "An error has occurred in " . NBILL_BRANDING_NAME . " (front end)! Details of the error are given below:\n\n";
                        }

                        if (@nbf_cms::$interop->user->id)
                        {
	                        nbf_globals::$message .= "User ID: " . nbf_cms::$interop->user->id . "\n";
                        }
                        if (file_exists(realpath(dirname(__FILE__)) . '/administrator/components/com_nbill/framework/classes/nbill.version.class.php')) {
                            include_once(realpath(dirname(__FILE__)) . '/administrator/components/com_nbill/framework/classes/nbill.version.class.php');
                            nbf_globals::$message .= "Software Version: " . nbf_version::$nbill_version_no . "\n";
                        }
                        nbf_globals::$message .= "Domain: " . @nbf_cms::$interop->live_site . "\n";
                        nbf_globals::$message .= "Date/Time: " . @nbf_common::nb_date("d-M-Y h:m:s a", $timestamp) . "\n";
                        nbf_globals::$message .= "IP Address: " . @$_SERVER['REMOTE_ADDR'] . "\n";
                        nbf_globals::$message .= "Page: " . @nbf_common::get_requested_page(true) . "\n";
                        nbf_globals::$message .= "Referrer: " . @$_SERVER['HTTP_REFERER'] . "\n";
                        nbf_globals::$message .= "Error Log ID: " . $insert_id . "\n";
                        nbf_globals::$message .= "Error Message: $errstr\n";
                        nbf_globals::$message .= "File Name: $errfile\n";
                        nbf_globals::$message .= "Line Number: $errline\n";
                        nbf_globals::$message .= "PHP Version (and OS): " . PHP_VERSION . " (" . PHP_OS . ")";
                        if (@nbf_cms::$interop->cms_version)
                        {
                            nbf_globals::$message .= "\nCMS: " . @nbf_cms::$interop->cms_name;
                            nbf_globals::$message .= "\nCMS Version: " . @nbf_cms::$interop->cms_version;
                        }
                        if (!function_exists("obsafe_print_r"))
                        {
                            //This function based on code in public domain
                            /**
                            * An alternative to print_r that unlike the original does not use output buffering with
                            * the return parameter set to true. Thus, Fatal errors that would be the result of print_r
                            * in return-mode within ob handlers can be avoided.
                            *
                            * Comes with an extra parameter to be able to generate html code. If you need a
                            * human readable DHTML-based print_r alternative, see http://krumo.sourceforge.net/
                            *
                            * Support for printing of objects as well as the $return parameter functionality
                            * added by Fredrik Wolls?n (fredrik dot motin at gmail), to make it work as a drop-in
                            * replacement for print_r (Except for that this function does not output
                            * paranthesises around element groups... ;) )
                            *
                            * Based on return_array() By Matthew Ruivo (mruivo at gmail)
                            * (http://se2.php.net/manual/en/function.print-r.php#73436)
                            */
                            function obsafe_print_r($var, $return = false, $html = false, $level = 0) {
                                $spaces = "";
                                $space = $html ? "&nbsp;" : " ";
                                $newline = $html ? "<br />" : "\n";
                                for ($i = 1; $i <= 6; $i++) {
                                    $spaces .= $space;
                                }
                                $tabs = $spaces;
                                for ($i = 1; $i <= $level; $i++) {
                                    $tabs .= $spaces;
                                }
                                if (is_array($var)) {
                                    $title = "Array";
                                } elseif (is_object($var)) {
                                    $title = get_class($var)." Object";
                                }
                                $output = $title . $newline . $newline;
                                foreach($var as $key => $value) {
                                    if (is_array($value) || is_object($value)) {
                                        $level++;
                                        $value = obsafe_print_r($value, true, $html, $level);
                                        $level--;
                                    }
                                    $output .= $tabs . "[" . $key . "] => " . $value . $newline;
                                }
                                if ($return) return $output;
                                  else echo $output;
                            }
                        }
                        //nbf_globals::$message .= "\nCall Stack: " . obsafe_print_r(debug_backtrace(), true);

                        if (nbf_common::nb_strlen($error_email) > 0 && nbf_common::nb_strlen(nbf_globals::$message) > 0)
                        {
	                        $mailsent = @nbf_cms::$interop->send_email($error_email, nbf_cms::$interop->live_site, $error_email, NBILL_BRANDING_NAME . " Runtime Error", nbf_globals::$message);
                        }
                        else
                        {
	                        $mailsent = false;
                        }
                        break;
                    default:
		                break;
                }

                switch ($errno)
                {
                    case E_USER_ERROR:
                    case E_ERROR:
                        if ($mailsent)
                        {
	                        $errmsg = @NBILL_ERR_SERIOUS_ERROR;
	                        if (nbf_common::nb_strlen($errmsg) == 0 || $errmsg == "NBILL_ERR_SERIOUS_ERROR")
	                        {
		                        $errmsg = "Sorry, an error has occurred. An e-mail containing details of the error has been sent to the appropriate person. Apologies for the inconvenience.";
	                        }
                        }
                        else
                        {
                            error_log(nbf_globals::$message . "; file: " . $errfile . "; line: " . $errline);
                            $errmsg = @sprintf(NBILL_ERR_SERIOUS_ERROR_NOMAIL, nbf_cms::$interop->live_site);
	                        if (nbf_common::nb_strlen($errmsg) == 0 || $errmsg == "NBILL_ERR_SERIOUS_ERROR_NOMAIL")
	                        {
		                        $errmsg = "Sorry, an error has occurred. " . NBILL_BRANDING_NAME . " was unable to send an e-mail containing details of the error to the appropriate person. Please contact the owner of this website (nbf_cms::$interop->live_site) to inform them of this error. Apologies for the inconvenience.";
	                        }
                        }

                        return "<div class=\"nbill-message\">$errmsg</div>";
                }
			}
		}
	}