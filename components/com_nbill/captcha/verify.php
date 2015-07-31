<?php
// check captcha post form field, return 1 if OK
// it is case-insensitive.
function check_captcha($captcha_input = "")
{
    if (nbf_common::nb_strlen($captcha_input) == 0)
    {
        $captcha_input = @$_POST['captcha_input'];
    }
    
    //Load the cron token and use that as a hash key (to prevent tampering)
    $nb_database = nbf_cms::$interop->database;
    $sql = "SELECT cron_auth_token FROM #__nbill_configuration WHERE id = 1";
    $nb_database->setQuery($sql);
    $token = $nb_database->loadResult();

    @session_start();
    
    $captcha_string = @$_SESSION['captcha_string'];
    
    $userletters = nbf_common::nb_strtoupper($captcha_input);
    
    if (!isset($captcha_string))
    {
        return 0;
    }
    if (md5($token . $userletters) === $captcha_string) 
    {
        return 1;
    }
    return 0;  
}