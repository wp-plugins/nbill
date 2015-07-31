<?php
class nBillAddress
{
    public $id;
    public $line_1;
    public $line_2;
    public $line_3;
    public $town;
    public $state;
    public $postcode;
    public $country;
    public $country_desc;
    public $use_eu_format = false;
    protected $formatted_address = '';

    public function __construct($line_1 = '', $line_2 = '', $line_3 = '', $town = '', $state = '', $postcode = '', $country = '')
    {
        $this->formatted_address = '';
        $this->line_1 = $line_1;
        $this->line_2 = $line_2;
        $this->line_3 = $line_3;
        $this->town = $town;
        $this->state = $state;
        $this->postcode = $postcode;
        $this->country = $country;
    }

    public function __toString()
    {
        return $this->format(true);
    }

    public function format($single_line = false)
    {
        $this->formatted_address = "";
        if ($this->country == "JP") {
            $this->getAddressJapan();
        } else {
            $this->getAddressLines();

            //Format address depending on country
            switch ($this->country) {
                case "GB":  //UK
                    $this->townNLStateNLPostcode();
                    break;
                case "AR":  //Argentina
                case "MY":  //Malaysia
                case "RO":  //Romania
                    $this->postcodeTownNLState();
                    break;
                case "AU":  //Australia
                case "CA":  //Canada
                case "ID":  //Indonesia
                case "LV":  //Latvia
                case "US":  //USA
                    $this->townStatePostcode();
                    break;
                case "AT":  //Austria
                case "BE":  //Belgium
                case "CH":  //Switzerland
                case "CN":  //China
                case "CZ":  //Czech Republic
                case "CR":  //Costa Rica
                case "DE":  //Germany
                case "DK":  //Denmark
                case "EE":  //Estonia
                case "ES":  //Spain
                case "FI":  //Finland
                case "FR":  //France
                case "GL":  //Greenland
                case "IL":  //Israel
                case "IS":  //Iceland
                case "IT":  //Italy
                case "LT":  //Lithuania
                case "LU":  //Luxembourg
                case "MX":  //Mexico
                case "NL":  //Netherlands
                case "NO":  //Norway
                case "PL":  //Poland
                case "PT":  //Portugal
                case "RU":  //Russia
                case "SE":  //Sweden
                    $this->postcodeTownState();
                    break;
                case "BR":  //Brazil
                    $this->townStateNLPostcode();
                    break;
                case "HK":  //Hong Kong
                    $this->townStateNLPostcodeUpper();
                    break;
                case "IE":  //Ireland
                    $this->townPostcodeNLState();
                    break;
                case "IN":  //India
                case "NZ":  //New Zealand
                case "SG":  //Singapore
                case "KR":  //South Korea
                case "TW":  //Taiwan
                    $this->townPostcodeState();
                    break;
                case "ZA":  //South Africa
                    $this->townStateNLPostcodeCountry();
                    break;
                default:
                    //If in EU, use postcode/town/state, otherwise default to town \n state \n postcode
                    if ($this->use_eu_format) {
                        $this->postcodeTownState();
                    } else {
                        $this->townNLStateNLPostcode();
                    }
                    break;
            }
        }

        $this->appendCountry();

        return $single_line ? str_replace("\n", ', ', $this->formatted_address) : $this->formatted_address;
    }

    protected function getAddressJapan()
    {
        //Japan does it a bit back-to-front
        //postcode \n state/town (no space) \n address_1 \n address_2 \n address_3 \n country
        if (strlen($this->postcode) > 0) {
            $this->formatted_address = $this->postcode;
        }
        if (strlen($this->state) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->state;
        }
        if (strlen($this->town) > 0) {
            $this->formatted_address .= $this->town;
        }
        if (strlen($this->line_1) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->line_1;
        }
        if (strlen($this->line_2) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->line_2;
        }
        if (strlen($this->line_3) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->line_3;
        }
        return $this->formatted_address;
    }

    protected function getAddressLines()
    {
        if (strlen($this->line_1) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->line_1;
        }
        if (strlen($this->line_2) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->line_2;
        }
        if (strlen($this->line_3) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->line_3;
        }
    }

    protected function townNLStateNLPostcode()
    {
        //town \n state \n postcode
        if (strlen($this->town) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->town;
        }
        if (strlen($this->state) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->state;
        }
        if (strlen($this->postcode) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->postcode;
        }
    }

    protected function postcodeTownNLState()
    {
        //postcode/town \n state
        if (strlen($this->postcode) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->postcode;
        }
        if (strlen($this->town) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= " ";
            }
            $this->formatted_address .= $this->town;
        }
        if (strlen($this->state) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->state;
        }
    }

    protected function townStatePostcode()
    {
        //town/state/postcode
        if (strlen($this->town) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->town;
        }
        if (strlen($this->state) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= " ";
            }
            $this->formatted_address .= $this->state;
        }
        if (strlen($this->postcode) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "  ";
            }
            $this->formatted_address .= $this->postcode;
        }
    }

    protected function postcodeTownState()
    {
        //postcode/town/state
        if (strlen($this->postcode) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->postcode;
        }
        if (strlen($this->town) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= " ";
            }
            $this->formatted_address .= $this->town;
        }
        if (strlen($this->state) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= ", ";
            }
            $this->formatted_address .= $this->state;
        }
    }

    protected function townStateNLPostcode()
    {
        //town/state \n postcode
        if (strlen($this->town) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->town;
        }
        if (strlen($this->state) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= " ";
            }
            $this->formatted_address .= $this->state;
        }
        if (strlen($this->postcode) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->postcode;
        }
    }

    protected function townStateNLPostcodeUpper()
    {
        //TOWN/STATE \n POSTCODE
        if (strlen($this->town) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= nbf_common::nb_strtoupper($this->town);
        }
        if (strlen($this->state) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= " ";
            }
            $this->formatted_address .= nbf_common::nb_strtoupper($this->state);
        }
        if (strlen($this->postcode) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= nbf_common::nb_strtoupper($this->postcode);
        }
    }

    protected function townPostcodeNLState()
    {
        //town/postcode \n state
        if (strlen($this->town) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->town;
        }
        if (strlen($this->postcode) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= " ";
            }
            $this->formatted_address .= $this->postcode;
        }
        if (strlen($this->state) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->state;
        }
    }

    protected function townPostcodeState()
    {
        //town/postcode/state
        if (strlen($this->town) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->town;
        }
        if (strlen($this->postcode) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= " ";
            }
            $this->formatted_address .= $this->postcode;
        }
        if (strlen($this->state) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= ", ";
            }
            $this->formatted_address .= $this->state;
        }
    }

    protected function townStateNLPostcodeCountry()
    {
        //town/state \n postcode/country
        if (strlen($this->town) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->town;
        }
        if (strlen($this->state) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= " ";
            }
            $this->formatted_address .= $this->state;
        }
        if (strlen($this->postcode) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            $this->formatted_address .= $this->postcode;
        }
        if (strlen($this->country_desc) > 0) {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= " ";
            }
            $this->formatted_address .= $this->country_desc;
        }
    }

    protected function appendCountry()
    {
        //Country goes at end regardless (except South Africa where it is already there - prefixed by postcode)
        if (strlen($this->country_desc) > 0 && $this->country != "ZA") {
            if (strlen($this->formatted_address) > 0) {
                $this->formatted_address .= "\n";
            }
            if ($this->country == "HK") { //Hong Kong must be in capitals
                $this->formatted_address .= nbf_common::nb_strtoupper($this->country_desc);
            } else {
                $this->formatted_address .= $this->country_desc;
            }
        }
    }
}