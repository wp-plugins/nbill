<?php
/**
* nBill Domain lookup Control Class file - for handling output and processing of domain lookups on forms.
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
* Domain Lookup
*
* @package nBill Framework
* @author Russell Walker
* @version 1.0
* @copyright (C) 2015 Netshine Software Limited
*/
class nbf_field_control_jj_default extends nbf_field_control
{
	/**
	* Renders the control
	*/
	protected function _render_control($admin = false)
	{
		?>http://www.<input type="text" name="ctl_<?php echo $this->name . $this->suffix; ?>" id="ctl_<?php echo $this->id . $this->suffix; ?>" value="<?php echo defined($this->value) ? str_replace("\"", "&quot;", constant($this->value)) : str_replace("\"", "&quot;", $this->value); ?>" class="<?php echo $this->css_class; ?>" <?php if ($admin) { ?>onclick="<?php echo $this->onclick_admin; ?>" onchange="<?php echo $this->onchange_admin; ?>" <?php } echo $this->attributes; if (nbf_common::nb_strpos($this->attributes, "style") == 0) {echo ' style="width:130px;"';} ?> /><input type="submit" class="button btn" name="ctl_<?php echo $this->name . $this->suffix; ?>_whois" id="ctl_<?php echo $this->id . $this->suffix; ?>_whois" value="<?php echo NBILL_DOMAIN_CHECK; ?>" /><?php
	}
    
    /**
    * Make sure the value held is valid for this type of field
    * @param string $error_message If the value is not valid, this output parameter should be populated with an appropriate message
    * @return boolean Whether or not validation passed successfully
    */
    function validate(&$error_message)
    {
        if ($this->required || nbf_common::nb_strlen(nbf_common::get_param($_REQUEST, 'ctl_' . $this->name . $this->suffix . '_whois')) > 0)
        {
            if ($this->_do_domain_lookup(nbf_common::get_param($_REQUEST, 'ctl_' . $this->name . $this->suffix), $error_message))
            {
                $error_message = "";
                return true;
            }
        }
        else
        {
            return true;
        }
    }
    
    /**
    * Perform any custom processing that should occur if the form is posted without moving to a new page (eg. when a process button is clicked)
    * If the processing should only occur if a particular button is clicked, make sure to check that the required button was clicked, as this
    * method will be called for ALL fields whenever ANY ONE of them triggers a postback.
    * @param string $message This should be populated with any feedback to the user
    */
    public function process(&$message)
    {
        if (nbf_common::get_param($_REQUEST, 'ctl_' . $this->name . $this->suffix . '_whois'))
        {
            $this->_do_domain_lookup(nbf_common::get_param($_REQUEST, 'ctl_' . $this->name . $this->suffix), $message);
        }
    }
    
    /** 
    * Look up the domain using J!Whois or tp_whois, if available
    * Return true if available, false if unavailable, zero if unknown 
    */
    private function _do_domain_lookup($domain, &$message)
    {
        $com_name = "jfwhois";
        if (file_exists(nbf_cms::$interop->site_base_path . "/components/com_tpwhois/classes/global.php"))
        {
            $com_name = "tpwhois";
        }
        if (file_exists(nbf_cms::$interop->site_base_path . "/components/com_$com_name/classes/global.php"))
        {
            $dotpos = nbf_common::nb_strpos($domain, ".");
            $extensions = array();
            $extensions[] = substr($domain, $dotpos + 1);
            $domain = substr($domain, 0, $dotpos);

            global $mosConfig_absolute_path; //J!Whois does not declare this itself. TPWhois does not need it.
            $mosConfig_absolute_path = nbf_cms::$interop->site_base_path;
            include_once(nbf_cms::$interop->site_base_path . "/components/com_$com_name/classes/global.php");
            include_once(nbf_cms::$interop->site_base_path . "/components/com_$com_name/includes/$com_name.config.php");
            
            // Languages
            $languages = array_diff(@scandir(JF_LANGUAGE), array(".", ".."));
            foreach ($languages as $language)
            {
                if (file_exists(JF_LANGUAGE . $language.'.php'))
                {
                    include_once(JF_LANGUAGE . $language.'.php');
                }
                else
                {
                    include_once(JF_LANGUAGE . 'english.php');
                }
            }
            $WhoisChecker = new EP_Dev_Whois_Global(nbf_cms::$interop->site_base_path);// . "/components/com_$com_name/");
            $WhoisChecker->USER->setValue("domain", $domain);
            $WhoisChecker->USER->setValue("extension", $extensions[0]);
            $WhoisChecker->USER->setValue("extensions", $extensions);
            if (!in_array($extensions[0], $WhoisChecker->DOMAINS->getAllExtensions()))
            {
                $results = false;
            }
            else
            {
                $results = $WhoisChecker->whoisRequest($domain, $extensions[0]);
                $results->lookup();
                if (!$results->server)
                {
                    //Try again
                    $results->lookup();
                    if (!$results->server)
                    {
                        $results = null;
                    }
                }
            }

            if ($results)
            {
                if ($results->available)
                {
                    $message = sprintf(NBILL_DOMAIN_AVAILABLE, $domain . "." . $extensions[0]);
                    return true;
                }
                else
                {
                    $message = sprintf(NBILL_DOMAIN_UNAVAILABLE, $domain . "." . $extensions[0]);
                    return false;
                }
            }
            else
            {
                if ($results === false)
                {
                    $message = sprintf(NBILL_DOMAIN_TLD_NOT_SUPPORTED, $extensions[0]);
                }
                else
                {
                    $message = NBILL_DOMAIN_RETURNED_NOTHING;
                }
            }
        }
        else
        {
            $message = NBILL_DOMAIN_NOT_FOUND;
        }
        return 0; //Unknown
    }
}