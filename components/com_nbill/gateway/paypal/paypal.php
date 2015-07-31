<?php
/**
* Receives IPN callbacks from Paypal and processes them.
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal." . nbf_cms::$interop->language . ".php")) {
	include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal." . nbf_cms::$interop->language . ".php");
} else {
	include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal.en-GB.php");
}
$error_message = '';

    $pp_mapper = new nBillPaypalMapper(nbf_cms::$interop->database);
    $paypal_fields = $pp_mapper->loadGatewaySettings();

	switch (nbf_common::get_param($_REQUEST, 'task')) {
		case "ipn":
            $preapp = false;

			$warning_message = "";
			$order_no = "";

            $preapp = nbf_common::get_param($_REQUEST, 'nbill_paypal_api');// strpos(nbf_common::get_param($_REQUEST, 'transaction_type'), 'Adaptive') !== false;
            if (!$preapp) {
			    //Assign posted variables to local variables
			    $item_name = nbf_common::get_param($_POST,'item_name');
			    $item_number = nbf_common::get_param($_POST,'item_number');
			    $payment_status = nbf_common::get_param($_POST,'payment_status');
			    $payment_amount = nbf_common::get_param($_POST,'mc_gross');
			    $payment_currency = nbf_common::get_param($_POST,'mc_currency');
			    $txn_id = nbf_common::get_param($_POST,'txn_id');
			    $receiver_email = nbf_common::get_param($_POST,'receiver_email');
			    $payer_email = nbf_common::get_param($_POST,'payer_email');
			    $g_tx_id = intval(nbf_common::get_param($_POST,'custom'));
			    $subscription_id = nbf_common::get_param($_POST,'subscr_id');
            }

            if ((!$preapp && $paypal_fields['sandbox']['g_value'] == 1) || ($preapp && $paypal_fields['api_sandbox']['g_value'] == 1)) {
                $paypal_url = "www.sandbox.paypal.com";
            } else {
                $paypal_url = "www.paypal.com";
            }

			$callback_verified = false;

			if ($paypal_fields['verify_callback']['g_value'] == 1) {
				// read the post from PayPal system and add 'cmd'
				$req = 'cmd=_notify-validate';

				foreach ($_POST as $key => $value)
				{
                    if (is_string($value)) {
					    $value = urlencode(stripslashes($value));
					    $req .= "&$key=$value";
                    }
				}

                //Verify using CURL
                $chandle = curl_init();
                curl_setopt($chandle, CURLOPT_URL, "https://$paypal_url/cgi-bin/webscr");
                curl_setopt($chandle, CURLOPT_SSL_VERIFYHOST,  2); //Make sure certificate matches domain
                curl_setopt($chandle, CURLOPT_SSL_VERIFYPEER, 1); //Make sure we are connecting to the correct server, not a MITM
                $ssl_cipher = $paypal_fields['ssl_cipher']['g_value'];
                if (strlen(trim($ssl_cipher)) > 0) {
                    curl_setopt($chandle, CURLOPT_SSL_CIPHER_LIST, $ssl_cipher);
                }
                curl_setopt($chandle, CURLOPT_POSTFIELDS, $req);
                curl_setopt($chandle, CURLOPT_POST, 1); //POST, not GET
                curl_setopt($chandle, CURLOPT_RETURNTRANSFER,1); //Don't echo anything
                curl_setopt($chandle, CURLOPT_TIMEOUT, 30);
                curl_setopt($chandle, CURLOPT_FAILONERROR, 1);
                $res = curl_exec($chandle);
                if (strpos($res, "VERIFIED") !== false) {
                    $callback_verified = true;
                } else if (strpos($res, "INVALID") !== false) {
                    $error_message = NBILL_PAYPAL_ERR_INVALID_TX;
                } else {
                    $error_message = curl_error($chandle);
                }
                curl_close($chandle);
			}
			else
			{
				//Assume it is ok
				$callback_verified = true;
			}

			if ($callback_verified)
			{
                if ($preapp) {
                    $pp_api = new nBillPaypalApi($_REQUEST, $pp_mapper);
                    $pp_api->ipn();
                    return;
                } else {
				    if ($payment_status == "Completed")
				    {
					    $error_message = "";
                        //Do the nBill processing
					    nbf_payment::gateway_processing($g_tx_id, $payment_amount, $payment_currency, $warning_message, $error_message, $payer_email, $txn_id, null, null, false);
				    }
				    else
				    {
					    //This is probably just notification of a new subscription or something, not a receipt of money - don't need to do anything
				    }
                }
			}
			nbf_payment::finish_gateway_processing($warning_message, $error_message, @$paypal_fields['add_debug_info']['g_value'], "", $g_tx_id);
			break;
        case "success":
            if (nbf_common::get_param($_REQUEST, 'pre_approval_setup')) {
                $pp_api = new nBillPaypalApi($_REQUEST, $pp_mapper);
                $pp_api->preappThanks();
                return;
            } else {
                //Check whether a successful payment has been confirmed - if so, load the transaction details and redirect
                $g_tx_id = intval(nbf_common::get_param($_REQUEST, 'g_tx_id'));
                $amount = 0;
                $currency = '';
                $order_id = '';
                $order_no = '';
                if ($g_tx_id)
                {
                    $loop_start_time = time();
                    $success_confirmed = false;
                    while (!$success_confirmed)
                    {
                        if ($loop_start_time + 30 < time()) {
                            //Go ahead with redirect, but don't load transaction data
                            break;
                        }
                        $sql = "SELECT success_confirmed FROM #__nbill_gateway_tx WHERE id = " . intval($g_tx_id);
                        $nb_database->setQuery($sql);
                        $success_confirmed = $nb_database->loadResult();
                        if (!$success_confirmed) {
                            sleep(2);
                        }
                    }
                    if ($success_confirmed)
                    {
                        nbf_payment::load_order_conf_tx_data($g_tx_id, $amount, $currency, $order_id, $order_no);
                    }
                }

                $redirect_url = @base64_decode(nbf_common::get_param($_REQUEST, 'return', null, true, false, true));
                if ($redirect_url)
                {
                    if ($success_confirmed && $g_tx_id)
                    {
                        //Replace placeholders with transaction data
                        $redirect_url = str_replace("##TX_ID##", $g_tx_id, $redirect_url);
                        $redirect_url = str_replace("##AMOUNT##", $amount, $redirect_url);
                        $redirect_url = str_replace("##CURRENCY##", $currency, $redirect_url);
                        $redirect_url = str_replace("##ORDER_ID##", $order_id, $redirect_url);
                        $redirect_url = str_replace("##ORDER_NO##", $order_no, $redirect_url);
                        //Execute any PHP code in the URL
                        $redirect_url = nbf_common::parse_and_execute_code($redirect_url);
                    }
                    nbf_common::redirect($redirect_url);
                    exit;
                }
                else
                {
                    if ($g_tx_id)
                    {
                        $sql = "SELECT #__nbill_order_form.id FROM #__nbill_order_form INNER JOIN #__nbill_gateway_tx ON #__nbill_order_form.id = #__nbill_gateway_tx.form_id WHERE #__nbill_gateway_tx.id = " . intval($g_tx_id);
                        $nb_database->setQuery($sql);
                        $thanks_id = $nb_database->loadResult();
                        if (intval($thanks_id))
                        {
                            nbf_common::redirect(nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_page_prefix . "&action=orders&task=complete&id=" . $thanks_id . "&g_tx_id=" . $g_tx_id . "&amount=" . $amount . "&currency=" . $currency . "&order_id=" . $order_id . "&order_no=" . $order_no . nbf_cms::$interop->site_page_suffix);
                            exit;
                        }
                    }

                    //Check for a redirect in the gateway settings
                    $sql = "SELECT g_value FROM #__nbill_payment_gateway WHERE gateway_id = 'paypal' AND g_key = 'success_url'";
                    $nb_database->setQuery($sql);
                    $success_url = $nb_database->loadResult();
                    if (strlen($success_url) > 0)
                    {
                        nbf_common::redirect($success_url);
                        exit;
                    }

                    //Show generic success message
                    $thanks = NBILL_GATEWAY_SUCCESS;
                    if ($g_tx_id)
                    {
                        //Replace placeholders with transaction data
                        $thanks = str_replace("##TX_ID##", $g_tx_id, $thanks);
                        $thanks = str_replace("##AMOUNT##", $amount, $thanks);
                        $thanks = str_replace("##CURRENCY##", $currency, $thanks);
                        $thanks = str_replace("##ORDER_ID##", $order_id, $thanks);
                        $thanks = str_replace("##ORDER_NO##", $order_no, $thanks);
                    }
                    echo $thanks;
                }
            }
            break;
	}