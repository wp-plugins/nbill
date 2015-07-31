<?php
class nBillString
{
    protected $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function convertEncoding($default_value) {
        if (function_exists('iconv')) {
            $this->value = @iconv("UTF-8", "ISO-8859-1//TRANSLIT", $this->value);
        } else {
            $this->value = @utf8_decode($this->value);
        }
        if (strlen(trim(str_replace('?', '', $this->value))) == 0) {
            $this->value = $default_value;
        }
        return $this->value;
    }

    public function __toString()
    {
        return $this->value;
    }
}