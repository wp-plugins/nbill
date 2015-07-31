<?php
//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

if (!$ajax_refresh) {
    ob_start();
    ?>
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
    function fetch_date_range_<?php echo $this->widget->id; ?>(range_option) {
        var graph_type_elem = document.getElementById('graph_type_<?php echo $this->widget->id; ?>');
        var graph_type = graph_type_elem.options[graph_type_elem.selectedIndex].value;
        <?php if ($this->widget->refresh_on_change) { ?>
            var current_page = window.location.href;
            var param_start = current_page.indexOf('&date_range=');
            if (param_start > 0) {
                current_page=current_page.substring(0, param_start);
            }
            current_page += '&date_range=' + range_option;
            window.location=current_page;
        <?php } else { ?>
            submit_ajax_request('', 'action=widgets&widget_type=sales_graph&task=new_date_range&widget_id=<?php echo $this->widget->id; ?>&graph_type=' + graph_type + '&new_date_range=' + range_option, function(content){document.getElementById('nbill_widget_<?php echo $this->widget->id; ?>').innerHTML=content;extract_and_execute_js(content);});
        <?php } ?>
    }

    //googleGraph object for graph rendering
    google.load("visualization", "1", {packages:["corechart"]});
    function googleGraph_<?php echo $this->widget->id; ?>(graphData, graphType, options)
    {
        //Properties
        this.graphData = graphData;
        this.graphType = graphType;
        this.options = options;
        this.graphResizeTimer = null;
        this.chart = null;

        //Methods
        this.drawChart = function() {
            this.chart = new google.visualization[this.graphType + 'Chart'](document.getElementById('chart_div_<?php echo $this->widget->id; ?>'));
            this.chart.draw(google.visualization.arrayToDataTable(this.graphData), this.options);
        }

        //Constructor
        if (!this.graphType) {
            this.graphType = 'Line';
        }
        if (!this.options) {
            this.options = {
                    title:'',
                    chartArea:{'width':'90%','height':'80%'},
                    legend:'top'
                };
        }
    }

    </script>
    <?php
    $header = ob_get_clean();
    nbf_cms::$interop->add_html_header($header);
}
?>

<div id="chart_controls_<?php echo $this->widget->id; ?>" class="nbill-sales-graph-controls">
    <label for="graph_type_<?php echo $this->widget->id; ?>"><?php echo NBILL_WIDGETS_SALES_GRAPH_GRAPH_TYPE; ?></label>
    <select name="graph_type" class="graph-selector" id="graph_type_<?php echo $this->widget->id; ?>" onchange="graph_<?php echo $this->widget->id; ?>.graphType=this.options[this.selectedIndex].value;graph_<?php echo $this->widget->id; ?>.drawChart();this.blur();">
        <option value="Line"<?php if ($this->widget->graph_type == SalesGraphWidget::GRAPH_TYPE_LINE) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_LINE; ?></option>
        <option value="Column"<?php if ($this->widget->graph_type == SalesGraphWidget::GRAPH_TYPE_COLUMN) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_COLUMN; ?></option>
        <option value="Bar"<?php if ($this->widget->graph_type == SalesGraphWidget::GRAPH_TYPE_BAR) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_BAR; ?></option>
        <option value="Pie"<?php if ($this->widget->graph_type == SalesGraphWidget::GRAPH_TYPE_PIE) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_PIE; ?></option>
    </select>
    <label for="graph_date_range_<?php echo $this->widget->id; ?>"><?php echo NBILL_WIDGETS_SALES_GRAPH_DATE_RANGE; ?></label>
    <select name="graph_date_range" class="graph-selector" id="graph_date_range_<?php echo $this->widget->id; ?>" onchange="fetch_date_range_<?php echo $this->widget->id; ?>(this.options[this.selectedIndex].value);">
        <option value="0"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_CURRENT_MONTH) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_CURRENT_MONTH; ?></option>
        <option value="10"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_PREV_MONTH) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_MONTH; ?></option>
        <option value="20"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_PREV_AND_CURRENT_MONTH) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_AND_CURRENT_MONTH; ?></option>
        <option value="30"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_PREV_VS_CURRENT_MONTH) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_VS_CURRENT_MONTH; ?></option>
        <option value="40"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_24_HOURS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_24_HOURS; ?></option>
        <option value="50"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_48_HOURS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_48_HOURS; ?></option>
        <option value="60"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_7_DAYS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_7_DAYS; ?></option>
        <option value="70"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_14_DAYS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_14_DAYS; ?></option>
        <option value="80"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_28_DAYS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_28_DAYS; ?></option>
        <option value="90"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_30_DAYS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_30_DAYS; ?></option>
        <option value="100"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_60_DAYS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_60_DAYS; ?></option>
        <option value="110"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_90_DAYS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_90_DAYS; ?></option>
        <option value="120"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_3_MONTHS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_3_MONTHS; ?></option>
        <option value="130"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_6_MONTHS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_6_MONTHS; ?></option>
        <option value="140"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_12_MONTHS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_12_MONTHS; ?></option>
        <option value="150"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_CURRENT_QUARTER) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_CURRENT_QUARTER; ?></option>
        <option value="160"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_PREV_QUARTER) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_QUARTER; ?></option>
        <option value="170"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_PREV_VS_CURRENT_QUARTER) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_VS_CURRENT_QUARTER; ?></option>
        <option value="180"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_CURRENT_YEAR) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_CURRENT_YEAR; ?></option>
        <option value="190"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_PREV_YEAR) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_YEAR; ?></option>
        <option value="200"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_PREV_VS_CURRENT_YEAR) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_PREV_VS_CURRENT_YEAR; ?></option>
        <option value="210"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_LAST_5_YEARS) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_LAST_5_YEARS; ?></option>
        <option value="220"<?php if ($this->widget->date_range == SalesGraphWidget::RANGE_ALL_TIME) {echo ' selected="selected"';} ?>><?php echo NBILL_WIDGETS_SALES_GRAPH_RANGE_ALL_TIME; ?></option>
    </select>
    <span class="sales-graph-printable" id="graph_png_<?php echo $this->widget->id; ?>"><a href="javascript:void(0);" onclick="var image_uri=graph_<?php echo $this->widget->id; ?>.chart.getImageURI();prt=window.open(image_uri);prt.focus();prt.print();" title="<?php echo NBILL_WIDGETS_SALES_GRAPH_PRINTABLE; ?>"><img src="<?php echo nbf_cms::$interop->nbill_site_url_path ?>/images/widget_print.png" border="0" alt="<?php echo NBILL_WIDGETS_SALES_GRAPH_PRINTABLE; ?>" /></a></span>
</div>

<div id="chart_div_<?php echo $this->widget->id; ?>" class="nbill-sales-graph"<?php echo $this->widget->graph_height ? ' style="height:' . rtrim($this->widget->graph_height, '%pxemt') . 'px"' : ''; ?>>

</div>

<script type="text/javascript">
var graphData = <?php echo $this->graph_data; ?>;
var graph_<?php echo $this->widget->id; ?> = new googleGraph_<?php echo $this->widget->id; ?>(graphData, '<?php echo $this->widget->graph_type; ?>', <?php echo json_encode($this->chart_options); ?>);
graph_<?php echo $this->widget->id; ?>.drawChart();
function nbill_graph_window_resize()
{
    clearTimeout(graph_<?php echo $this->widget->id; ?>.graphResizeTimer);graph_<?php echo $this->widget->id; ?>.graphResizeTimer = setTimeout(function(){graph_<?php echo $this->widget->id; ?>.drawChart();}, 250);
}
window.addEventListener('resize', nbill_graph_window_resize);

var nbill_container = document.getElementById('nbill_main_container')
addResizeListener(nbill_container, nbill_graph_window_resize);
</script>