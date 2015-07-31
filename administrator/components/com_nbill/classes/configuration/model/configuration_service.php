<?php
class nBillConfigurationService
{
    /** @var nBillConfigurationService singleton **/
    protected static $instance;

    /** @var nBillConfigurationMapper **/
    protected $mapper;
    /** @var nBillConfiguration **/
    protected $config;

    protected function __construct(nBillConfigurationMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
    * @param nBillConfigurationMapper $mapper
    * @return nBillConfigurationService
    */
    public static function getInstance(nBillConfigurationMapper $mapper = null)
    {
        if (!isset(self::$instance)) {
            if ($mapper === null) {
                $mapper = new nBillConfigurationMapper(nbf_cms::$interop->database);
            }
            self::$instance = new nBillConfigurationService($mapper);
        }
        return self::$instance;
    }

    /**
    * @return nBillConfiguration;
    */
    public function getConfig()
    {
        if (!isset($this->config) || !$this->config) {
            $this->config = new nBillConfiguration();
            $this->mapper->loadObject($this->config);
        }
        $this->validateTemplateCss();

        return $this->config;
    }

    protected function validateTemplateCss()
    {
        //If template CSS file missing, set it to blank
        if (!file_exists($this->config->getColourSchemePath() . DIRECTORY_SEPARATOR . $this->config->admin_custom_stylesheet)) {
            $this->config->admin_custom_stylesheet = '';
        }
    }

    public function saveConfig(nBillConfiguration &$config)
    {
        $this->config = $config;
        $this->validateTemplateCss();
        $this->mapper->saveObject($this->config);
    }

    public function findAllColourTemplates()
    {
        $path = realpath($this->getConfig()->getColourSchemePath());
        $css_files = array();
        foreach (glob($path . DIRECTORY_SEPARATOR . "*.css") as $css_file) {
            $css_files[] = basename($css_file);
        }
        return $css_files;
    }
}