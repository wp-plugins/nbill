<?php
/**
* Represents a set of data to be plotted on a sales graph
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class SalesGraphDataset
{
    const LINE_GRAPH = 0;
    const BAR_CHART = 1;

    public $x_axis_title;
    public $y_axis_title;
    public $y_axis_min_value;
    public $y_axis_max_value;
    public $plot_point_sets = array();
    public $plot_point_legends = array();

    public $style = self::LINE_GRAPH;

    public function __construct($plot_point_sets, $y_axis_min_value = null, $y_axis_max_value = null)
    {
        $this->plot_point_sets = $plot_point_sets;
        $this->y_axis_min_value = $y_axis_min_value === null ? $this->calculate_y_min() : $y_axis_min_value;
        $this->y_axis_max_value = $y_axis_max_value === null ? $this->calculate_y_max() : $y_axis_max_value;
    }

    public function setLabelsNumeric()
    {
        foreach ($this->plot_point_sets as &$plot_point_set)
        {
            for ($i=0; $i<count($plot_point_set); $i++)
            {
                if (isset($plot_point_set[$i])) {
                    $plot_point_set[$i]->x_label = $i + 1;
                }
            }
        }
    }

    protected function calculate_y_min()
    {
        if ($this->plot_point_sets && count($this->plot_point_sets) > 0 && $this->plot_point_sets[0] && count($this->plot_point_sets[0]) > 0) {
            $min = $this->plot_point_sets[0][0]->y_value;
            for ($i = 0; $i < count($this->plot_point_sets); $i++)
            {
                foreach ($this->plot_point_sets[$i] as $plot_point)
                {
                    $min = $plot_point->y_value < $min ? $plot_point->y_value : $min;
                }
            }
            return $min;
        }
        else {
            return 0;
        }
    }

    protected function calculate_y_max()
    {
        if ($this->plot_point_sets && count($this->plot_point_sets) > 0 && $this->plot_point_sets[0] && count($this->plot_point_sets[0]) > 0) {
            $max = $this->plot_point_sets[0][0]->y_value;
            for ($i = 0; $i < count($this->plot_point_sets); $i++)
            {
                foreach ($this->plot_point_sets[$i] as $plot_point)
                {
                    $max = $plot_point->y_value > $max ? $plot_point->y_value : $max;
                }
            }
            return $max;
        }
        else {
            return 100;
        }
    }
}