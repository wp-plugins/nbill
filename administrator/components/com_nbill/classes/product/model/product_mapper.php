<?php
class nBillProductMapper
{
    /** @var nbf_database **/
    protected $db;
    /** @var nBillProductFactory **/
    protected $factory;
    /** @var nBillNumberFactory **/
    protected $number_factory;
    /** @var nBillNominalLedgerFactory **/
    protected $ledger_factory;
    /** @var nBillCurrencyService **/
    protected $currency_service;

    public function __construct(nbf_database $db, nBillProductFactory $factory, nBillNumberFactory $number_factory, nBillNominalLedgerFactory $ledger_factory, nBillCurrencyService $currency_service)
    {
        $this->db = $db;
        $this->factory = $factory;
        $this->number_factory = $number_factory;
        $this->ledger_factory = $ledger_factory;
        $this->currency_service = $currency_service;
    }

    public function getCategoryTree($vendor_id)
    {
        $category = null;
        $sql = "SELECT * FROM #__nbill_product_category WHERE vendor_id = " . intval($vendor_id) . " ORDER BY parent_id, name";
        $this->db->setQuery($sql);
        $db_categories = $this->db->loadObjectList();

        if ($db_categories && count($db_categories) > 0) {
            $first_cat = reset($db_categories);
            if ($first_cat->parent_id > 0) {
                //There is no root category - I don't think this should ever happen, but just in case, we'll add our own root
                $category = $this->factory->createCategory();
                $category->id = 0;
                $category->name = NBILL_ROOT;
                //Make sure any kids know who their mama is
                foreach ($db_categories as &$db_category)
                {
                    if ($db_category->parent_id < 1) {
                        $db_category->parent_id = 0;
                    }
                }
            } else {
                $category = $this->factory->createCategory();
                $category->id = $first_cat->id;
                $category->name = $first_cat->name;
                $category->description = $first_cat->description;
            }
            $this->addChildCategories($category, $db_categories);
        } else {
            //Lite
            $category = $this->factory->createCategory();
            $category->id = 0;
            $category->name = NBILL_ROOT;
        }
        return $category;
    }

    protected function addChildCategories(&$parent, $db_categories)
    {
        foreach ($db_categories as $db_category)
        {
            if ($db_category->parent_id == $parent->id) {
                $category = $this->factory->createCategory();
                $category->id = $db_category->id;
                $category->name = $db_category->name;
                $category->description = $db_category->description;
                $this->addChildCategories($category, $db_categories);
                $parent->categories[] = $category;
            }
        }
    }

    public function populateCategoryTreeWithProducts(&$category_tree, $vendor_id = null, $currency_code = '')
    {
        $sql = "SELECT #__nbill_product.*, #__nbill_product.id AS product_id,
                        #__nbill_nominal_ledger.description AS nominal_ledger_description
                        FROM #__nbill_product
                        LEFT JOIN #__nbill_nominal_ledger ON #__nbill_product.nominal_ledger_code = #__nbill_nominal_ledger.code";
        if (strlen($currency_code) > 0) {
            $sql .= " LEFT JOIN #__nbill_product_price ON #__nbill_product_price.product_id = #__nbill_product.id";
        }
        $sql .= " WHERE 1";
        if (strlen($currency_code) > 0) {
            $sql .= " AND #__nbill_product_price.currency_code = '" . $this->db->getEscaped($currency_code) . "'";
        }
        if (intval($vendor_id) > 0) {
            $sql .= " AND #__nbill_product.vendor_id = " . intval($vendor_id);
        }
        $sql .= " GROUP BY #__nbill_product.id ORDER BY #__nbill_product.product_code";

        $this->db->setQuery($sql);
        $db_products = $this->db->loadObjectList();

        foreach ($db_products as $db_product)
        {
            $product = $this->factory->createProduct();
            $product->vendor_id = $db_product->vendor_id;
            $product->id = $db_product->id;
            $product->product_code = $db_product->product_code;
            $product->name = $db_product->name;
            $product->description = $db_product->description;
            $product->custom_tax_rate = $this->number_factory->createNumber($db_product->custom_tax_rate, 'tax_rate');
            $product->nominal_ledger = $this->ledger_factory->createLedgerCode($db_product->nominal_ledger_code, $db_product->nominal_ledger_description);
            //$this->addPrices($product, $db_product);
            if ($db_product->category == $category_tree->id || $db_product->category < 0) {
                $category_tree->products[] = $product;
            } else {
                $this->addProductToCategory($category_tree->categories, $db_product->category, $product);
            }
        }

        return $category_tree;
    }

    protected function addProductToCategory(&$categories, $category_id, nBillProduct $product)
    {
        foreach ($categories as &$category)
        {
            if ($category->id == $category_id) {
                $category->products[] = $product;
                return true;
            }
            if (count($category->categories) > 0) {
                if ($this->addProductToCategory($category->categories, $category_id, $product)) {
                    return true;
                }
            }
        }
        return false;
    }

    /*protected function findCategory(&$categories, $category_id)
    {
        foreach ($categories as &$category)
        {
            if ($category->id == $category_id) {
                return $category;
            }
            if (count($category->categories) > 0) {
                $category = $this->findCategory($category->categories, $category_id);
                if ($category) {
                    return $category;
                }
            }
        }
        return null;
    }*/

    protected function addPrices(&$product, $db_product)
    {
        $currency = $this->currency_service->findCurrency($db_product->currency_code);
        $product->prices[] = $this->addPrice($db_product->net_price_setup_fee, 'AA', $db_product->net_price_one_off, $currency);
        $product->prices[] = $this->addPrice($db_product->net_price_setup_fee, 'BB', $db_product->net_price_weekly, $currency);
        $product->prices[] = $this->addPrice($db_product->net_price_setup_fee, 'BX', $db_product->net_price_four_weekly, $currency);
        $product->prices[] = $this->addPrice($db_product->net_price_setup_fee, 'CC', $db_product->net_price_monthly, $currency);
        $product->prices[] = $this->addPrice($db_product->net_price_setup_fee, 'DD', $db_product->net_price_quarterly, $currency);
        $product->prices[] = $this->addPrice($db_product->net_price_setup_fee, 'DX', $db_product->net_price_semi_annually, $currency);
        $product->prices[] = $this->addPrice($db_product->net_price_setup_fee, 'EE', $db_product->net_price_annually, $currency);
        $product->prices[] = $this->addPrice($db_product->net_price_setup_fee, 'FF', $db_product->net_price_biannually, $currency);
        $product->prices[] = $this->addPrice($db_product->net_price_setup_fee, 'GG', $db_product->net_price_five_years, $currency);
        $product->prices[] = $this->addPrice($db_product->net_price_setup_fee, 'HH', $db_product->net_price_ten_years, $currency);
        $product->prices = array_values(array_filter($product->prices)); //Remove any null entries (no price defined)
    }

    protected function addPrice($setup_fee_amount, $pay_freq_code, $amount, nBillCurrency $currency)
    {
        if ($amount > 0) {
            $price = $this->factory->createPrice($currency);
            $price->setup_fee = $this->number_factory->createNumberCurrency($setup_fee_amount, $currency);
            $price->payment_frequency->code = $pay_freq_code;
            $price->amount = $this->number_factory->createNumberCurrency($amount, $currency);
            return $price;
        }
    }

    public function loadProduct($product_id, $currency_code)
    {
        $sql = "SELECT #__nbill_product.*, #__nbill_product.id AS product_id,
                        #__nbill_nominal_ledger.description AS nominal_ledger_description,
                        #__nbill_product_price.*
                        FROM #__nbill_product
                        LEFT JOIN #__nbill_nominal_ledger ON #__nbill_product.nominal_ledger_code = #__nbill_nominal_ledger.code
                        LEFT JOIN #__nbill_product_price ON #__nbill_product_price.product_id = #__nbill_product.id
                        WHERE #__nbill_product_price.currency_code = '" . $this->db->getEscaped($currency_code) . "'
                        AND #__nbill_product.id = " . intval($product_id);
        $this->db->setQuery($sql);
        $db_product = null;
        $this->db->loadObject($db_product);

        $product = $this->factory->createProduct();
        $product->id = $product_id;
        if ($db_product) {
            $product->vendor_id = $db_product->vendor_id;
            $product->product_code = $db_product->product_code;
            $product->name = $db_product->name;
            $product->description = $db_product->description;
            $product->nominal_ledger = $this->ledger_factory->createLedgerCode($db_product->nominal_ledger_code, $db_product->nominal_ledger_description);
            $product->requires_shipping = $db_product->requires_shipping;
            $product->shipping_units = $db_product->shipping_units;
            $product->is_freebie = $db_product->is_freebie;
            $product->is_taxable = $db_product->is_taxable;
            $product->custom_tax_rate = $db_product->custom_tax_rate;
            $product->electronic_delivery = $db_product->electronic_delivery;
            $this->addPrices($product, $db_product);
        }
        return $product;
    }

    public function productCodeExists($sku)
    {
        $sql = "SELECT id FROM #__nbill_product WHERE product_code = '" . $this->db->getEscaped($sku) . "'";
        $this->db->setQuery($sql);
        return $this->db->loadResult();
    }

    /**
    * @param int $vendor_id
    * @param string $sku
    * @param string $currency_code
    * @param string $pay_freq
    * @return nBillINumberCurrency
    */
    public function getCurrentPrice($vendor_id, $sku, $currency_code, $pay_freq)
    {
        $pay_freq_col = $pay_freq ? nbf_common::convert_pay_freq($pay_freq) : 'net_price_one_off';
        $prices = null;
        $sql = "SELECT net_price_one_off, net_price_weekly, net_price_four_weekly, net_price_monthly,
                        net_price_quarterly, net_price_semi_annually, net_price_annually, net_price_biannually,
                        net_price_five_years, net_price_ten_years
                FROM #__nbill_product_price
                INNER JOIN #__nbill_product ON #__nbill_product_price.product_id = #__nbill_product.id
                WHERE #__nbill_product.vendor_id = " . intval($vendor_id) . "
                AND #__nbill_product.product_code = '" . $sku . "'
                AND #__nbill_product_price.currency_code = '" . $currency_code ."'";
        $this->db->setQuery($sql);
        $this->db->loadObject($prices);
        $current_price = null;
        if ($prices) {
            $current_price = $prices->$pay_freq_col;
            if (!$current_price || $current_price == 0) {
                foreach (get_object_vars($prices) as $key=>$value)
                {
                    if ($value && $value != 0) {
                        $current_price = $value;
                        break;
                    }
                }
            }
        }
        return $this->number_factory->createNumberCurrency($current_price, $this->currency_service->findCurrency($currency_code));
    }
}
