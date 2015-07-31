<?php
/**
* Data Mapper for Administrator home page widgets
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillWidgetMapper
{
    public $widgets = array();
    protected $db;

    public function __construct(nbf_database $db)
    {
        $this->db = $db;
    }

    public function loadWidgetTypes()
    {
        //We used to store these in the database, but it is easier to add new widgets if we just look at the file system instead
        //We are still kind of mapping, so arguably this doesn't break the single responsibiltiy principle!
        $widget_types = array();
        try {
            $dir = new DirectoryIterator(realpath(dirname(__FILE__) . "/..") . "/types/");
            foreach ($dir as $item) {
                $name = $item->getFilename();
                if ($name != '.' & $name != '..' && is_dir($item->getPathname())) {
                    $description = ucwords(str_replace('_', ' ', $name));
                    $widget_types[$name] = $description;
                }
            }
        }
        catch (Exception $e) {
            //Unlikely to be able to recover from this, just return an empty array
            $widget_types = array();
        }
        return $widget_types;
    }

    public function loadAllWidgets($include_parameters = false)
    {
        return $this->loadWidgets(true, $include_parameters);
    }

    public function loadPublishedWidgets()
    {
        return $this->loadWidgets();
    }

    protected function loadWidgets($include_unpublished = false, $include_parameters = true)
    {
        $sql = "SELECT #__nbill_widgets."
                . ($include_parameters ? "*" : "id, title, show_title, published, configurable, widget_type, ordering, width ")
                . " FROM #__nbill_widgets ";
        if (!$include_unpublished) {
            $sql .= "WHERE published = 1 ";
        }
        $sql .= "ORDER BY ordering";
        $this->db->setQuery($sql);
        $db_widgets = $this->db->loadObjectList();
        $this->widgets = array();
        foreach ($db_widgets as $db_widget)
        {
            $this->widgets[] = $this->mapWidget($db_widget);
        }
        return $this->widgets;
    }

    public function loadWidget($widget_id, $populate_from_request = false)
    {
        $db_widget = null;
        $sql = "SELECT #__nbill_widgets.*
                FROM #__nbill_widgets
                WHERE id = " . intval($widget_id);
        $this->db->setQuery($sql);
        $this->db->loadObject($db_widget);
        return $this->mapWidget($db_widget, $populate_from_request);
    }

    protected function mapWidget($db_widget, $populate_from_request = false)
    {
        $type = $populate_from_request && isset($_REQUEST['widget_type']) && $_REQUEST['widget_type'] ? $_REQUEST['widget_type'] : $db_widget->widget_type;
        $published = $populate_from_request && isset($_REQUEST['published']) ? $_REQUEST['published'] : $db_widget->published;
        $ordering = $populate_from_request && isset($_REQUEST['ordering']) ? $_REQUEST['ordering'] : $db_widget->ordering;
        $widget = nBillWidgetFactory::makeWidget($type, $published, $ordering);
        $widget->id = $db_widget->id;
        $widget->width = $db_widget->width;
        if ($populate_from_request) {
            $request_width = $widget->width;
            if (isset($_REQUEST['widget_width'])) {
                $request_width = $_REQUEST['widget_width'];
                switch ($request_width)
                {
                    case 'px':
                    case '%':
                        $request_width = @$_REQUEST['width_fixed_amount'] . $request_width;
                        break;
                }
            }
            if ($request_width != $widget->width) {
                $widget->layout_dirty = true;
                $widget->width = $request_width;
            }
        }
        $widget->show_title = $populate_from_request && isset($_REQUEST['show_title']) ? $_REQUEST['show_title'] : $db_widget->show_title;
        $title = $populate_from_request && isset($_REQUEST['title']) ? $_REQUEST['title'] : $db_widget->title;
        if (strlen($title) > 0) {
            $widget->title = $title;
        }
        $widget->configurable = $db_widget->configurable;
        $widget = $this->loadParams($widget, $db_widget, $populate_from_request);
        return $widget;
    }

    protected function loadParams(nBillWidget $widget, $db_widget, $populate_from_request = false)
    {
        if (property_exists($db_widget, 'params')) {
            $params = json_decode($db_widget->params, true);
            $persistent_properties = $widget->getParams();
            foreach ($persistent_properties as $property)
            {
                if ($populate_from_request) {
                    if (isset($_REQUEST[$property])) {
                        $widget->$property = is_string($_REQUEST[$property]) ? html_entity_decode($_REQUEST[$property]) : $_REQUEST[$property];
                    }
                } else if (isset($params[$property])) {
                    $widget->$property = $params[$property];
                }
            }
        }
        return $widget;
    }

    public function saveWidget(nBillWidget &$widget, $save_params = true)
    {
        if ($widget->id) {
            $sql = "UPDATE #__nbill_widgets SET
                    title = '" . $this->db->getEscaped($widget->title) . "',
                    show_title = " . intval($widget->show_title) . ",
                    width = '" . $this->db->getEscaped($widget->width) . "',
                    published = " . intval($widget->published) . ",
                    configurable = " . intval($widget->configurable) . ",
                    widget_type = '" . $this->db->getEscaped($widget->type) . "',
                    ordering = " . intval($widget->ordering) . "
                    WHERE id = " . intval($widget->id);
        } else {
            if (!$widget->ordering) {
                //If no ordering specified, get the next available slot
                $widget->ordering = $this->getNextOrdering();
            }
            $sql = "INSERT INTO #__nbill_widgets
                    (title, show_title, width, published, configurable, widget_type, ordering)
                    VALUES
                    ('" . $this->db->getEscaped($widget->title) . "',
                    " . intval($widget->show_title) . ",
                    '" . $this->db->getEscaped($widget->width) . "',
                    " . intval($widget->published) . ",
                    " . intval($widget->configurable) . ",
                    '" . $this->db->getEscaped($widget->type) . "',
                    " . intval($widget->ordering) . ")";
        }
        $this->db->setQuery($sql);
        $this->db->query();
        if (!$widget->id) {
            $widget->id = $this->db->insertid();
        }

        if ($save_params) {
            $this->saveParams($widget);
        }
    }

    protected function saveParams(nBillWidget $widget)
    {
        if ($widget->id) {
            $params = $widget->getParams();
            $json_params = new stdClass();
            foreach ($params as $param)
            {
                $json_params->$param = $widget->$param;
            }
            $json = json_encode($json_params);
            if ($json !== false) {
                $sql = "UPDATE #__nbill_widgets SET params = '" . $this->db->getEscaped($json) . "' WHERE id = " . $widget->id;
                $this->db->setQuery($sql);
                $this->db->query();
            } else {
                throw new RuntimeException('Cannot encode configuration parameters as JSON for ' . $widget->type . ' widget ' . $widget->id);
            }
        } else {
            throw new BadMethodCallException('Cannot save parameters for ' . $widget->type . ' widget - no ID number supplied');
        }
    }

    protected function getNextOrdering()
    {
        $sql = "SELECT MAX(ordering) FROM #__nbill_widgets";
        $this->db->setQuery($sql);
        return intval($this->db->loadResult()) + 1;
    }

    public function deleteWidget($widget_id)
    {
        if (intval($widget_id)) {
            $sql = "DELETE FROM #__nbill_widgets WHERE id = " . intval($widget_id);
            $this->db->setQuery($sql);
            $this->db->query();
        }
    }

    public function resetAllWidgets()
    {
        //Reset to factory settings
        $sql = "DELETE FROM #__nbill_widgets WHERE 1";
        $this->db->setQuery($sql);
        $this->db->query();

        $sql = "INSERT INTO `#__nbill_widgets` (`id`, `title`, `show_title`, `published`, `configurable`, `params`, `width`, `widget_type`, `ordering`) VALUES
                (1, '', 1, 1, 1, '', 'auto', 'html', 0),
                (2, '', 1, 1, 1, '', '49%', 'sales_graph', 1),
                (3, '', 1, 1, 1, '', '49%', 'orders_due', 2),
                (4, '', 1, 1, 1, '', 'auto', 'links', 3);";
        $this->db->setQuery($sql);
        $this->db->query();
    }
}