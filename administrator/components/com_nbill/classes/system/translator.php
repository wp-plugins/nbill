<?php
class nBillTranslator
{
    /** @var string **/
    protected $admin_base_path;
    /** @var string **/
    protected $system_language;

    public function __construct($admin_base_path, $system_language)
    {
        $this->admin_base_path = $admin_base_path;
        $this->system_language = $system_language;
    }

    /**
    * Returns the value of the given constant in the given language, if available (even though that language is not in use, so the constant actually holds a different value). If no translation is found, the constant's value is returned.
    * @param string $language The language to translate into
    * @param string $feature The feature in whose language file to lookup the value (ie. first part of file name)
    * @param string $constant The constant name
    */
    public function parseTranslation($language, $constant, $feature = "template.common")
    {
        $translation = "";
        $constant = trim($constant);
        if ($language && $language != $this->system_language)
        {
            $file_name = $this->admin_base_path . "/language/" . $language . "/" . $feature . "." . $language . ".php";
            if (file_exists($file_name))
            {
                $handle = @fopen($file_name, "r");
                if ($handle)
                {
                    while (!@feof($handle))
                    {
                        $line = @fgets($handle, 4096);
                        $start = nbf_common::nb_strpos($line, 'define("' . $constant . '",');
                        if ($start !== false)
                        {
                            $start += nbf_common::nb_strlen('define("' . $constant . '",');
                            $start = nbf_common::nb_strpos($line, '"', $start) + 1;
                            $end = nbf_common::nb_strpos($line, '");', $start);
                            $translation = nbf_common::nb_substr($line, $start, $end - $start);
                            break;
                        }
                    }
                    @fclose($handle);
                }
            }
        }
        if (!$translation && defined($constant)) {
            $translation = constant($constant);
        } else if (!$translation) {
            $translation = $constant;
        }
        return $translation;
    }
}
