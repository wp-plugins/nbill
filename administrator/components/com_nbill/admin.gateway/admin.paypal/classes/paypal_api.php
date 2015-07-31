<?php
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\FundingConstraint;
use PayPal\Types\AP\FundingTypeInfo;
use PayPal\Types\AP\FundingTypeList;
use PayPal\Types\AP\PayRequest;
use PayPal\Types\AP\Receiver;
use PayPal\Types\AP\ReceiverList;
use PayPal\Types\AP\SenderIdentifier;
use PayPal\Types\Common\PhoneNumberType;
use PayPal\Types\Common\RequestEnvelope;

/**
* Receives IPN callbacks from Paypal classic API and processes them.
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillPaypalApi
{
    protected $gateway_settings = array();
    /** @var array **/
    protected $request_values = array();
    /** @var nBillPaypalMapper **/
    protected $mapper = null;

    public function __construct($request_values = array(), nBillPaypalMapper $mapper)
    {
        $this->request_values = $request_values;
        $this->mapper = $mapper;
        $this->gateway_settings = $this->mapper->loadGatewaySettings();
    }

    public function getConfig()
    {
        $config = array(
            // Signature Credentials
            "mode" => $this->gateway_settings['api_sandbox']['g_value'] ? "sandbox" : "live",
            "acct1.UserName" => $this->gateway_settings['api_sandbox']['g_value'] ? $this->gateway_settings['api_sandbox_user']['g_value'] : $this->gateway_settings['api_user']['g_value'],
            "acct1.Password" => $this->gateway_settings['api_sandbox']['g_value'] ? $this->gateway_settings['api_sandbox_password']['g_value'] : $this->gateway_settings['api_password']['g_value'],
            "acct1.Signature" => $this->gateway_settings['api_sandbox']['g_value'] ? $this->gateway_settings['api_sandbox_signature']['g_value'] : $this->gateway_settings['api_signature']['g_value'],
            "acct1.AppId" => $this->gateway_settings['api_sandbox']['g_value'] ? $this->gateway_settings['api_sandbox_appid']['g_value'] : $this->gateway_settings['api_appid']['g_value']
        );
        return $config;
    }

    public function ipn()
    {
        switch ($this->getRequestValue('transaction_type')) {
            case 'Adaptive Payment PREAPPROVAL':
                if ($this->getRequestValue('status') == 'CANCELED') {
                    $this->mapper->deletePreApproval($this->getRequestValue('preapproval_key'));
                } else {
                    $invitation_id = intval($this->getRequestValue('invitation_id'));
                    $invitation = $this->mapper->loadInvitation($invitation_id);
                    if ($invitation && $this->getRequestValue('approved') == 'true') {
                        $this->confirmApproval($invitation);
                    } else {
                        $this->preappFailure(sprintf($invitation ? NBILL_PAYPAL_PREAPP_NOT_APPROVED : NBILL_PAYPAL_PREAPP_INVITATION_NOT_FOUND, $invitation->id . ', ' . trim($invitation->first_name . ' ' . $invitation->last_name)), true);
                    }
                }
                break;
        }
    }

    protected function confirmApproval(nBillPaypalInvitation $invitation)
    {
        $resource = new nBillPaypalResource();
        $resource->created_date = new \DateTime();
        $resource->currency = $this->getRequestValue('currency_code', FILTER_SANITIZE_STRING, $invitation->currency);
        $resource->amount = $invitation->max_amount;
        $resource->entity_id = $invitation->client_id;
        $resource->payer_email = $this->getRequestValue('sender_email');
        $resource->resource_id = $this->getRequestValue('preapproval_key');
        $resource->status = $this->getRequestValue('status');
        $resource->invitation_id = $invitation->id;
        $contact_name = trim($invitation->first_name . ' ' . $invitation->last_name);
        $resource->name = $contact_name;
        $result = $this->mapper->saveResource($resource);
        if (!$result) {
            $this->preappFailure(sprintf(NBILL_PAYPAL_PREAPP_NOT_SAVED, $result), true);
        }
        $this->preappThanks(true, $contact_name, $invitation->email_address);
    }

    public function preappThanks($send_email = false, $contact_name = '', $contact_email = '')
    {
        if ($send_email) {
            $admin_email = $this->mapper->getAdminEmail();
            nbf_cms::$interop->send_email($admin_email, $admin_email, $admin_email, sprintf(NBILL_PAYPAL_NEW_PREAPP_NOTIFICATION_SUBJECT, $contact_name), sprintf(NBILL_PAYPAL_NEW_PREAPP_NOTIFICATION_MESSAGE, nbf_cms::$interop->live_site, $contact_name), 0, null, null, '', $contact_email);
        }

        if ($this->gateway_settings['preapp_success_url']['g_value']) {
            nbf_common::redirect($this->gateway_settings['preapp_success_url']['g_value']);
        } else {
            $thanks = $this->gateway_settings['preapp_thankyou']['g_value'];
            if (!$thanks) {
                $thanks = NBILL_PAYPAL_DEFAULT_PREAPP_THANKS_VALUE;
            }
            echo $thanks;
        }
    }

    protected function preappFailure($failure_message, $send_email = false)
    {
        if ($send_email) {
            $admin_email = $this->mapper->getAdminEmail();
            nbf_cms::$interop->send_email($admin_email, $admin_email, $admin_email, sprintf(NBILL_PAYPAL_NEW_PREAPP_NOTIFICATION_SUBJECT, $contact_name), $failure_message, 0);
        }
        if ($this->gateway_settings['preapp_failure_url']['g_value']) {
            nbf_common::redirect($this->gateway_settings['preapp_failure_url']['g_value']);
        } else {
            echo $failure_message;
        }
        return;
    }

    public function executePayment($payment_details)
    {
        require_once(nbf_cms::$interop->nbill_admin_base_path . '/admin.gateway/admin.paypal/sdk/vendor/autoload.php');
        $requestEnvelope = new RequestEnvelope("en_US");

        $receiver = new Receiver();
        $receiver->email = $this->gateway_settings['business']['g_value'];
        $receiver->amount = $payment_details['amount'];
        $receiver->invoiceId = $payment_details['invoice_no'];
        $receiverList = new ReceiverList(array($receiver));

        $failure_url = $this->gateway_settings['preapp_failure_url']['g_value'] ? $this->gateway_settings['preapp_failure_url']['g_value'] : nbf_cms::$interop->live_site . '/' . nbf_cms::$interop->site_page_prefix . '&action=gateway&gateway=paypal&task=cancel' . nbf_cms::$interop->site_page_suffix;
        $success_url = $this->gateway_settings['preapp_success_url']['g_value'] ? $this->gateway_settings['preapp_success_url']['g_value'] : nbf_cms::$interop->live_site . '/' . nbf_cms::$interop->site_page_prefix . '&action=gateway&gateway=paypal&task=success&pre_approval_setup=1' . nbf_cms::$interop->site_page_suffix;
        $payRequest = new PayRequest($requestEnvelope, 'PAY', $failure_url, $payment_details['currency'], $receiverList, $success_url);
        $payRequest->preapprovalKey = $payment_details['pre_approval_resource_id'];
        $payRequest->reverseAllParallelPaymentsOnError = true; //Not applicable?

        if (substr($this->gateway_settings['notify_url']['g_value'], 0, 7) == "http://" || substr($this->gateway_settings['notify_url']['g_value'], 0, 8) == "https://") {
            $payRequest->ipnNotificationUrl = str_replace("[NBILL_FE_PAGE_PREFIX]", nbf_cms::$interop->site_page_prefix, $this->gateway_settings['notify_url']['g_value']) . '&nbill_paypal_api=1&g_tx_id=' . intval($payment_details['g_tx_id']) . nbf_cms::$interop->public_site_page_suffix();
        } else {
            $payRequest->ipnNotificationUrl = str_replace("[NBILL_FE_PAGE_PREFIX]", nbf_cms::$interop->site_page_prefix, nbf_cms::$interop->live_site . "/" . $this->gateway_settings['notify_url']['g_value']) . '&nbill_paypal_api=1&g_tx_id=' . intval($payment_details['g_tx_id']) . nbf_cms::$interop->public_site_page_suffix();
        }

        $payRequest->senderEmail = $payment_details['payer_email'];
        $payRequest->trackingId = $payment_details['transaction_id']; //prefix + g_tx_id

        $service = new AdaptivePaymentsService($this->getConfig());
        try {
            $response = $service->Pay($payRequest);
        } catch(Exception $ex) {
            echo sprintf(NBILL_GATEWAY_ERR, str_replace(nbf_cms::$interop->site_base_path, "", $ex->getFile()) . ":line " . $ex->getLine() . ": " . $ex->getMessage());
            return;
        }

        $ack = strtoupper(@$response->responseEnvelope->ack);
        if($ack == "SUCCESS"){
            $warning = '';
            $error = '';
            nbf_payment::gateway_processing($payment_details['g_tx_id'], $payment_details['amount'], $payment_details['currency'], $warning, $error, '', $payment_details['pre_approval_resource_id'], '', 'paypal');
            ob_start();
            nbf_payment::finish_gateway_processing($warning, $error, $this->gateway_settings['add_debug_info']['g_value'], "", $payment_details['g_tx_id']);
            ob_end_clean();
            echo $payment_details['document_id'] . '#!#success#!#';
            return;
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
    }

    protected function getRequestValue($key, $filter = FILTER_SANITIZE_STRING, $default_value = null)
    {
        if (array_key_exists($key, $this->request_values)) {
            return filter_var($this->request_values[$key], $filter);
        }
        return $default_value;
    }
}