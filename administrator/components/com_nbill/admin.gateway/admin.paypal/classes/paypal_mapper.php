<?php
/**
* Maps paypal objects to and from database
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillPaypalMapper
{
    /** @var nbf_database **/
    protected $db = null;
    /** @var array **/
    protected $gateway_settings;

    public function __construct(nbf_database $db)
    {
        $this->db = $db;
    }

    public function loadGatewaySettings()
    {
        if (!$this->gateway_settings) {
            $sql = "SELECT * FROM #__nbill_payment_gateway WHERE gateway_id = 'paypal'";
            $this->db->setQuery($sql);
            $this->gateway_settings = $this->db->loadAssocList('g_key');
            if (!array_key_exists('business', $this->gateway_settings)) {
                //loadAssocList has not worked
                $this->gateway_settings = array();
                $alt_paypal_fields = $this->db->loadObjectList();
                if (!$alt_paypal_fields) {
                    $alt_paypal_fields = array();
                }
                foreach ($alt_paypal_fields as $alt_paypal_field) {
                    $this->gateway_settings[$alt_paypal_field->g_key] = array();
                    $this->gateway_settings[$alt_paypal_field->g_key]['g_key'] = $alt_paypal_field->g_key;
                    $this->gateway_settings[$alt_paypal_field->g_key]['g_value'] = $alt_paypal_field->g_value;
                }
            }
        }
        return $this->gateway_settings;
    }

    public function loadInvitation($invitation_id)
    {
        $invitation = new nBillPaypalInvitation();
        $sql = "SELECT * FROM #__nbill_paypal_preapp_invitations WHERE id = " . intval($invitation_id);
        $this->db->setQuery($sql);
        $this->db->loadObject($invitation);
        return $invitation;
    }

    public function saveResource(nBillPaypalResource $resource)
    {
        $properties = get_object_vars($resource);
        $sql = (array_key_exists('id', $properties) ? 'REPLACE' : 'INSERT') . " INTO #__nbill_paypal_preapp_resources (";
        foreach ($properties as $key=>$value) {
            if ($key != 'id' || $value) {
                $sql .= $key . ",";
            }
        }
        $sql = rtrim($sql, ",") . ") VALUES (";
        foreach ($properties as $key=>$value) {
            if ($key != 'id' || $value) {
                if ($value instanceof \DateTime) {
                    $sql .= "'" . intval($value->format('U')) . "',";
                } else {
                    $sql .= "'" . $this->db->getEscaped($value) . "',";
                }
            }
        }
        $sql = rtrim($sql, ",") . ")";

        $this->db->setQuery($sql);
        $this->db->query();
        if (strlen($this->db->_errorMsg) > 0) {
            return $this->db->_errorMsg;
        }
        return $resource->id ? $resource->id : $this->db->insertid();
    }

    public function getAdminEmail()
    {
        $sql = "SELECT admin_email FROM #__nbill_vendor WHERE default_vendor = 1";
        $this->db->setQuery($sql);
        return $this->db->loadResult();
    }

    public function deletePreApproval($resource_id)
    {
        $sql = "DELETE FROM #__nbill_paypal_preapp_resources WHERE resource_id LIKE '" . $this->db->getEscaped($resource_id) . "'";
        $this->db->setQuery($sql);
        $this->db->query();
    }
}