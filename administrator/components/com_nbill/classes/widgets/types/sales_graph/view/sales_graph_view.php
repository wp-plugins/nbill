<?php
/**
* View class for sales graph rendering
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class SalesGraphView extends nBillWidgetView
{
    /** @var string JSON encoded string representing the graph data in a format that can be used by the Google Chart API **/
    protected $graph_data = '';
    /** @var SalesGraphChartOptions **/
    protected $chart_options;

    public function __construct(SalesGraphWidget $widget)
    {
        parent::__construct($widget);
        $this->chart_options = new SalesGraphChartOptions();
    }

    public function renderContent($ajax_refresh = false)
    {
        //Work out which set of plot points has most entries and how many that is
        $biggest_set_index = 0;
        $max_plot_points = count($this->widget->dataset->plot_point_sets[0]);
        for ($i = 1; $i < count($this->widget->dataset->plot_point_sets); $i++) {
            $max_plot_points = count($this->widget->dataset->plot_point_sets[$i]) > $max_plot_points ? count($this->widget->dataset->plot_point_sets[$i]) : $max_plot_points;
            $biggest_set_index = $i;
        }

        //Convert our data into a format that can be used by the google chart API
        $this->graph_data = "[";
        $this->graph_data .= "['Date'";
        for ($i = 1; $i <= count($this->widget->dataset->plot_point_sets); $i++) {
            $this->graph_data .= ", '" . (defined($this->widget->dataset->plot_point_legends[$i - 1]) ? constant($this->widget->dataset->plot_point_legends[$i - 1]) : $this->widget->dataset->plot_point_legends[$i - 1]) . "'";
        }
        $this->graph_data .= "]";

        for ($main_index = 0; $main_index < $max_plot_points; $main_index++)
        {
            $plot_point = $this->widget->dataset->plot_point_sets[$biggest_set_index][$main_index];
            $this->graph_data .= ", ['" . $plot_point->x_label . "'";
            for ($i = 0; $i < count($this->widget->dataset->plot_point_sets); $i++)
            {
                $this->graph_data .= ", ";
                if (count($this->widget->dataset->plot_point_sets[$i]) > $main_index) {
                    $currency_object = nbf_common::convertValueToCurrencyObject($this->widget->dataset->plot_point_sets[$i][$main_index]->y_value, $this->widget->currency, true);
                    $currency_object->html_format_negative = false;
                    $display_value = $currency_object->format();
                    if (strpos($display_value, '&') !== false) {
                        $display_value = html_entity_decode($display_value, ENT_COMPAT | 0, 'utf-8');
                        if (nbf_cms::$interop->char_encoding == 'iso-8859-1' && strpos(strtolower(@$_SERVER['CONTENT_TYPE']), 'utf') === false) {
                            $display_value = utf8_decode($display_value);
                        }
                    }
                    $this->graph_data .= "{v: " . $this->widget->dataset->plot_point_sets[$i][$main_index]->y_value . ", f: '" . $display_value . "'}";
                }
            }
            $this->graph_data .= "]";
        }
        $this->graph_data .= "]";

        /*if (count($this->widget->dataset->plot_point_sets) < 2) {
            //No point in showing a legend as there is only one set of points
            $this->chart_options->legend = 'none';
        }*/

        parent::renderContent($ajax_refresh);
    }
}