<?php
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\PreapprovalRequest;
use PayPal\Types\Common\RequestEnvelope;

/**
* This gateway was developed by and is copyright of Netshine Software Limited.
* Sections of code may be copyrighted to other parties (eg. where sample code was used
* from the Paypal documentation). All parts (of this gateway only) written by
* Netshine Software Limited are licensed for use in any way you wish, as long
* as this copyright message remains intact, and without any guarantee of any sort -
* use at your own risk.
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal." . nbf_cms::$interop->language . ".php")) {
    include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal." . nbf_cms::$interop->language . ".php");
} else {
    if (file_exists(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal.en-GB.php")) {
        include_once(nbf_cms::$interop->nbill_admin_base_path . "/admin.gateway/admin.paypal/paypal.en-GB.php");
    }
}

$nb_database = nbf_cms::$interop->database;

switch (nbf_common::get_param($_REQUEST, 'process')) {
    case 'preauth':
        $invitation_id = intval(nbf_common::get_param($_REQUEST, 'id'));
        $hash = nbf_common::get_param($_REQUEST, 'token');
        if (!$invitation_id) {
            echo sprintf(NBILL_PAYPAL_FE_INVITATION_NOT_FOUND, $invitation_id);
            break;
        }
        $invitation = null;
        $sql = "SELECT * FROM #__nbill_paypal_preapp_invitations WHERE id = $invitation_id";
        $nb_database->setQuery($sql);
        $nb_database->loadObject($invitation);
        if (!$invitation || !$invitation->id) {
            echo sprintf(NBILL_PAYPAL_FE_INVITATION_NOT_FOUND, $invitation_id);
            break;
        }
        $saved_hash = md5($invitation_id . $invitation->token);
        if ($saved_hash != $hash) {
            echo NBILL_PAYPAL_FE_INVITATION_HASH_MISMATCH;
            break;
        }

        //All ok, let's build the URL and redirect to Paypal:

        //Load gateway parameters
        $sql = "SELECT * FROM #__nbill_payment_gateway WHERE gateway_id = 'paypal'";
        $nb_database->setQuery($sql);
        $paypal_fields = $nb_database->loadAssocList('g_key');
        if (!array_key_exists('business', $paypal_fields))
        {
            //loadAssocList has not worked
            $paypal_fields = array();
            $alt_paypal_fields = $nb_database->loadObjectList();
            if (!$alt_paypal_fields)
            {
                $alt_paypal_fields = array();
            }
            foreach ($alt_paypal_fields as $alt_paypal_field)
            {
                $paypal_fields[$alt_paypal_field->g_key] = array();
                $paypal_fields[$alt_paypal_field->g_key]['g_key'] = $alt_paypal_field->g_key;
                $paypal_fields[$alt_paypal_field->g_key]['g_value'] = $alt_paypal_field->g_value;
            }
        }

        //Construct the URL
        $pp_url = '';
        $now = new \DateTime();
        $one_year_hence = clone($now);
        $one_year_hence->add(new DateInterval('P1Y'));
        require_once(nbf_cms::$interop->nbill_admin_base_path . '/admin.gateway/admin.paypal/sdk/vendor/autoload.php');
        $requestEnvelope = new RequestEnvelope("en_US");
        $preapprovalRequest = new PreapprovalRequest($requestEnvelope, $paypal_fields['failure_url']['g_value'],
                        $invitation->currency, $paypal_fields['success_url']['g_value'], $now->format('Y-m-d'));
        $preapprovalRequest->maxAmountPerPayment = $invitation->max_amount;
        if (intval($invitation->payment_count) != 0) {
            $preapprovalRequest->endingDate = $one_year_hence->format('Y-m-d');
            $preapprovalRequest->maxNumberOfPayments = intval($invitation->payment_count);
            $preapprovalRequest->maxNumberOfPaymentsPerPeriod = intval($invitation->payment_count);
            //Estimate maximum
            $max = $preapprovalRequest->maxAmountPerPayment * $preapprovalRequest->maxNumberOfPayments;
            switch ($invitation->currency) {
                case 'USD':
                    $max = 2000;
                case 'GBP':
                    $max = 1500;
                    break;
                case 'EUR':
                    $max = 1800;
                    break;
                case 'CAD':
                case 'AUD':
                    $max = 2500;
            }
            $preapprovalRequest->maxTotalAmountOfAllPayments = $preapprovalRequest->maxAmountPerPayment * $preapprovalRequest->maxNumberOfPayments > $max ? $max : $preapprovalRequest->maxAmountPerPayment * $preapprovalRequest->maxNumberOfPayments;
        }

        $preapprovalRequest->memo = $invitation->description;

        if (substr($paypal_fields['notify_url']['g_value'], 0, 7) == "http://" || substr($paypal_fields['notify_url']['g_value'], 0, 8) == "https://") {
            $preapprovalRequest->ipnNotificationUrl = str_replace("[NBILL_FE_PAGE_PREFIX]", nbf_cms::$interop->site_page_prefix, $paypal_fields['notify_url']['g_value']) . '&nbill_paypal_api=1&invitation_id=' . intval($invitation_id) . nbf_cms::$interop->public_site_page_suffix();
        } else {
            $preapprovalRequest->ipnNotificationUrl = str_replace("[NBILL_FE_PAGE_PREFIX]", nbf_cms::$interop->site_page_prefix, nbf_cms::$interop->live_site . "/" . $paypal_fields['notify_url']['g_value']) . '&nbill_paypal_api=1&invitation_id=' . intval($invitation_id) . nbf_cms::$interop->public_site_page_suffix();
        }
        $preapprovalRequest->cancelUrl = $paypal_fields['preapp_failure_url']['g_value'] ? $paypal_fields['preapp_failure_url']['g_value'] : nbf_cms::$interop->live_site . '/' . nbf_cms::$interop->site_page_prefix . '&action=gateway&gateway=paypal&task=cancel' . nbf_cms::$interop->site_page_suffix;
        $preapprovalRequest->returnUrl = $paypal_fields['preapp_success_url']['g_value'] ? $paypal_fields['preapp_success_url']['g_value'] : nbf_cms::$interop->live_site . '/' . nbf_cms::$interop->site_page_prefix . '&action=gateway&gateway=paypal&task=success&pre_approval_setup=1' . nbf_cms::$interop->site_page_suffix;
        $preapprovalRequest->displayMaxTotalAmount = false;

        $config = array(
            // Signature Credential
            "mode" => $paypal_fields['api_sandbox']['g_value'] ? "sandbox" : "live",
            "acct1.UserName" => $paypal_fields['api_sandbox']['g_value'] ? $paypal_fields['api_sandbox_user']['g_value'] : $paypal_fields['api_user']['g_value'],
            "acct1.Password" => $paypal_fields['api_sandbox']['g_value'] ? $paypal_fields['api_sandbox_password']['g_value'] : $paypal_fields['api_password']['g_value'],
            "acct1.Signature" => $paypal_fields['api_sandbox']['g_value'] ? $paypal_fields['api_sandbox_signature']['g_value'] : $paypal_fields['api_signature']['g_value'],
            "acct1.AppId" => $paypal_fields['api_sandbox']['g_value'] ? $paypal_fields['api_sandbox_appid']['g_value'] : $paypal_fields['api_appid']['g_value']
        );

        $service = new AdaptivePaymentsService($config);
        try {
            $response = $service->Preapproval($preapprovalRequest);
        } catch(Exception $ex) {
            echo sprintf(NBILL_GATEWAY_ERR, str_replace(nbf_cms::$interop->site_base_path, "", $ex->getFile()) . ":line " . $ex->getLine() . ": " . $ex->getMessage());
            return;
        }

        $ack = strtoupper(@$response->responseEnvelope->ack);
        if($ack == "SUCCESS"){
            // Redirect to paypal.com here
            $token = $response->preapprovalKey;
            $pp_url = 'https://www.' . ($paypal_fields['api_sandbox']['g_value'] ? 'sandbox.' : '') . 'paypal.com/webscr&cmd=_ap-preapproval&preapprovalkey='.$token;
        } else {
            if (isset($response->error)) {
                if (isset($response->error[0]->message)) {
                    echo sprintf(NBILL_GATEWAY_ERR, $response->error[0]->message);
                } else {
                    echo sprintf(NBILL_GATEWAY_ERR, print_r($response->error, true));
                }
            } else {
                echo sprintf(NBILL_GATEWAY_ERR, print_r($response, true));
            }
            return;
        }
        nbf_common::redirect($pp_url);
        break;
    default:
        echo NBILL_PAYPAL_FE_UNKNOWN_FUNCTION;
        break;
}