<?php
class nBillProductService
{
    /** @var nBillProductMapper **/
    protected $mapper;

    public function __construct(nBillProductMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function getCategoryTree($vendor_id)
    {
        return $this->mapper->getCategoryTree($vendor_id);
    }

    public function populateCategoryTreeWithProducts($category_tree, $vendor_id = null, $currency_code = '')
    {
        return $this->mapper->populateCategoryTreeWithProducts($category_tree, $vendor_id, $currency_code);
    }

    public function loadProduct($product_id, $currency_code)
    {
        return $this->mapper->loadProduct($product_id, $currency_code);
    }

    public function productCodeExists($sku)
    {
        if (strpos($sku, '[') !== false && strpos($sku, '=') !== false) {
            return true;
        } else {
            return $this->mapper->productCodeExists($sku);
        }
    }

    public function wasProductUpdated($vendor_id, $sku, $nominal_ledger_code, $product_name, $product_description, $currency_code, $net_price, $pay_freq = 'AA')
    {
        if (strpos($sku, '[') === false || strpos($sku, '=') === false) {
            $product = $this->loadProduct($this->productCodeExists($sku), $currency_code);
            if ($product) {
                //Check if anything has been updated
                $supplied_name = $product_name ? html_entity_decode($product_name, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) : $product_name;
                $supplied_desc = str_replace("\n", " ", str_replace("\r", "", $product_description));
                $supplied_desc = $supplied_desc ? html_entity_decode($supplied_desc, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) : $supplied_desc;
                $supplied_ledger = $nominal_ledger_code ? html_entity_decode($nominal_ledger_code, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) : $nominal_ledger_code;

                $saved_name = $product->name ? html_entity_decode($product->name, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) : $product->name;
                $saved_desc = $product->description ? html_entity_decode(str_replace("\n", " ", str_replace("\r", "", $product->description)), ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) : $product->description;
                $saved_ledger = $product->nominal_ledger->code ? html_entity_decode($product->nominal_ledger->code, ENT_COMPAT | 0, nbf_cms::$interop->char_encoding) : $product->nominal_ledger->code;

                //Clean up HTML descriptions so we don't get false mismatches
                $tinymce_fluff = html_entity_decode(' <p>&nbsp;</p>', ENT_COMPAT | 0, nbf_cms::$interop->char_encoding);
                if (substr($supplied_desc, strlen($supplied_desc) - strlen($tinymce_fluff)) == $tinymce_fluff)
                {
                    //Strip off the extra paragraph added by TinyMCE
                    $supplied_desc = rtrim(substr($supplied_desc, 0, strlen($supplied_desc) - strlen($tinymce_fluff)));
                }
                if (nbf_config::$editor == 'nicEdit' && strpos($supplied_desc, '<br>') !== false && strpos($saved_desc, '<br />') !== false)
                {
                    //nicEdit converts self closing line breaks to HTML 4.0 ones, so we'll convert them back for comparison purposes
                    $supplied_desc = str_replace('<br>', '<br />', $supplied_desc);
                }

                if (($saved_name != $supplied_name && (@strpos($supplied_name, '(', strlen($saved_name))===false || substr($supplied_name, 0, strlen($saved_name)) != $saved_name)) ||
                    ($saved_desc != $supplied_desc && @utf8_decode($saved_desc) != $supplied_desc && trim(str_replace(chr(160), chr(32), utf8_decode($saved_desc))) != trim($supplied_desc)) || //CodeMirror editor returns decoded html
                    $saved_ledger != $supplied_ledger)
                {
                    return true;
                }
                else
                {
                    //Check whether price has been updated
                    $current_price = $this->mapper->getCurrentPrice($vendor_id, $sku, $currency_code, $pay_freq);
                    if ($current_price !== null && $current_price->value != null && $current_price->value != $net_price->value)
                    {
                        return true;
                    }
                }

            }
        }
        return false;
    }
}
