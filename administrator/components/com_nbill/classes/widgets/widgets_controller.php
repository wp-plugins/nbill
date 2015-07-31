<?php
/**
* Main processing file for nBill administrator home page widgets
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillWidgetsController
{
    /** @var nbf_database **/
    protected $db;
    /** @var nBillWidgetMapper **/
    protected $mapper;
    /** @var nBillConfigurationService **/
    protected $config_service;

    /** @var array List of widget types **/
    protected $widget_types = array();
    /** @var array List of ALL widget objects (published or not) for configuration **/
    protected $widgets = array();
    /** @var array List of the available colour scheme css files **/
    protected $templates = array();

    public function __construct(nbf_database $db, nBillConfigurationService $config_service)
    {
        nbf_common::load_language("widgets");
        $this->db = $db;
        $this->mapper = new nBillWidgetMapper($db);
        $this->config_service = $config_service;
    }

    public function route()
    {
        $exit_after_show = false;
        switch (@$_REQUEST['task'])
        {
            case 'main_config':
                $this->widget_types = $this->mapper->loadWidgetTypes();
                $this->widgets = $this->mapper->loadAllWidgets();
                $this->templates = $this->config_service->findAllColourTemplates();
                $this->showDashboardConfig();
                break;
            case 'reset_all':
                $this->mapper->resetAllWidgets();
                break;
            case 'save_main_config':
                if (@$_REQUEST['task'] == 'save_main_config') {
                    $this->clearAllBuffers();
                    $this->saveMainConfig();
                    if (nbf_cms::$interop->cms_name == 'joomla' && substr(nbf_cms::$interop->cms_version, 0, 2) == '1.') {
                        //For some reason, the widgets.css stylesheet disappears from Joomla 1.0 if we do an ajax refresh!
                        ?>
                        <script type="text/javascript">window.location('<?php echo nbf_cms::$interop->admin_page_prefix; ?>');</script>
                        <?php
                        exit;
                    }
                    $exit_after_show = true;
                }
                //fall through
            case null:
            case '':
            case 'show_all':
                //Show all widgets
                $this->showAllWidgets();
                if ($exit_after_show) {
                    exit;
                }
                break;
            default:
                //Hand over to the appropriate controller for the given widget ID
                if (intval(@$_REQUEST['widget_id']) == 0) {
                    if (strlen(@$_REQUEST['widget_type']) > 0) {
                        $widget = nBillWidgetFactory::makeWidget(nbf_common::get_param($_REQUEST, 'widget_type'));
                        $widget->id = 'x';
                        $widget->show_title = @$_REQUEST['widget_show_title'] ? true : false;
                        $widget->width = intval(@$_REQUEST['widget_width']) ? intval(@$_REQUEST['widget_width']) : $widget->width;
                    } else {
                        exit;
                    }
                } else {
                    $widget = $this->mapper->loadWidget(intval($_REQUEST['widget_id']));
                }
                $controller = $this->getWidgetController($widget);
                $controller->route();
        }
    }

    protected function showAllWidgets()
    {
        $widgets = $this->mapper->loadPublishedWidgets();
        foreach ($widgets as $widget)
        {
            $controller = $this->getWidgetController($widget);
            $controller->showWidget();
        }
    }

    public function showDashboardConfig($then_exit = true)
    {
        $this->clearAllBuffers();
        $view = new nBillWidgetsView($this->widget_types, $this->widgets);
        $view->templates = $this->templates;
        $view->selected_template = $this->config_service->getConfig()->admin_custom_stylesheet;
        $view->template_path = $this->config_service->getConfig()->getColourSchemePath();
        $view->renderConfig();
        if ($then_exit) {
            exit;
        }
    }

    protected function getWidgetController($widget)
    {
        $mapper = nBillWidgetFactory::makeWidgetMapper($widget, $this->db);
        if (get_class($mapper) != 'nBillWidgetMapper' && intval($widget->id)) {
            //Re-load using the child mapper so we get any additional functionality the widget may rely on
            $widget = $mapper->loadWidget($widget->id);
        }
        $controller = nBillWidgetFactory::makeWidgetController($widget, $mapper);
        return $controller;
    }

    protected function saveMainConfig()
    {
        $widgets = $this->mapper->loadAllWidgets(false);
        foreach ($widgets as $widget)
        {
            if (isset($_POST['deleted_' . $widget->id]) && $_POST['deleted_' . $widget->id]) {
                $this->mapper->deleteWidget($widget->id);
            } else {
                $widget->published = (isset($_POST['published_' . $widget->id]) && $_POST['published_' . $widget->id]);
                if (isset($_POST['ordering_' . $widget->id])) {
                    $widget->ordering = $_POST['ordering_' . $widget->id];
                }
                $this->mapper->saveWidget($widget, false);
            }
        }
        foreach ($_POST as $key=>$value)
        {
            if (substr($key, 0, 10) == 'added_row_' && strlen($value) > 0) {
                $json_widget = json_decode($value);
                $new_widget = nBillWidgetFactory::makeWidget($json_widget->type, true, 0);
                $new_widget->title = $json_widget->title;
                $this->mapper->saveWidget($new_widget);
            }
        }

        $config = $this->config_service->getConfig();
        $config->admin_custom_stylesheet = @$_POST['colour_scheme_css'];
        $this->config_service->saveConfig($config);
    }

    protected function clearAllBuffers()
    {
        $level = ob_get_level();
        for ($i=0;$i<=$level;$i++)
        {
            @ob_end_clean();
        }
        if (!@headers_sent()) {
          foreach (@headers_list() as $header)
            @header_remove($header);
        }
    }
}