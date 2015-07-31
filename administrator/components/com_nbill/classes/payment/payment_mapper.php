<?php
class nBillPaymentMapper
{
    /** @var nbf_database **/
    protected $db;
    /** @var nBillPaymentFactory **/
    protected $factory;

    public function __construct(nbf_database $db, nBillPaymentFactory $factory)
    {
        $this->db = $db;
        $this->factory = $factory;
    }

    public function getPaymentFrequencies()
    {
        nbf_common::load_language('xref');
        $sql = "SELECT code, description FROM #__nbill_xref_pay_frequency ORDER BY code";
        $this->db->setQuery($sql);
        $db_payment_frequencies = $this->db->loadObjectList();

        $payment_frequencies = array();
        foreach ($db_payment_frequencies as $db_payment_frequency)
        {
            $payment_frequency = $this->factory->createPaymentFrequency();
            $payment_frequency->code = $db_payment_frequency->code;
            $payment_frequency->description = defined($db_payment_frequency->description) ? constant($db_payment_frequency->description) : $db_payment_frequency->description;
            $payment_frequencies[] = $payment_frequency;
        }
        return $payment_frequencies;
    }
}
