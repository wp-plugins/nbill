<?php
class nBillProductListView
{
    /** @var nBillCategory **/
    public $category_tree = array();
    /** @var boolean **/
    protected $tree_script_loaded = false;

    public function __construct(nBillCategory $category_tree)
    {
        $this->category_tree = $category_tree;
    }

    protected function loadTreeScript()
    {
        if (!$this->tree_script_loaded) {
            $js_file = nbf_cms::$interop->nbill_fe_base_path . '/js/tree/CollapsibleLists.compressed.js';
            if (file_exists($js_file)) {
                $js = file_get_contents($js_file);
                if (strlen($js) > 0) {
                    $this->tree_script_loaded = true;
                    ?>
                    <script type="text/javascript">
                    <?php echo $js; ?>
                    </script>
                    <?php
                }
            }
        }
        $css = '';
        if ($this->tree_script_loaded) {
            $css_file = nbf_cms::$interop->nbill_fe_base_path . '/js/tree/tree.css';
            if (file_exists($css_file)) {
                $css = file_get_contents($css_file);
                if (strlen($css) > 0) {
                    $image_pos = strpos($css, 'image://');
                    $loop_breaker = 0;
                    while($image_pos !== false) {
                        $loop_breaker++;
                        if ($loop_breaker > 200) {
                            break;
                        }
                        $image_end = strpos($css, ')', $image_pos);
                        $file_name = trim(substr($css, $image_pos + 8, $image_end - ($image_pos + 8)));
                        $full_file_name = nbf_cms::$interop->nbill_fe_base_path . '/js/tree/' . $file_name;
                        $image_data = '';
                        if (file_exists($full_file_name)) {
                            $image_data = base64_encode(file_get_contents($full_file_name));
                        }
                        $css = substr($css, 0, $image_pos) . 'data:image/png;base64,' . $image_data . substr($css, $image_end);
                        $image_pos = strpos($css, 'image://');
                    }
                }
            }
        }
        if (strlen($css) > 0) {
            $this->tree_script_loaded = true;
            ?>
            <style type="text/css">
            <?php echo $css; ?>
            </style>
            <?php
        }
    }

    public function showProductList()
    {
        $this->loadTreeScript();
        ?>
        <ul id="category_list" style="display:none;">
            <?php
            $this->showCategoryContents($this->category_tree);
            ?>
        </ul>
        <?php
        if ($this->tree_script_loaded) {
            ?>
            <script type="text/javascript">
            CollapsibleLists.apply();
            document.getElementById('category_list').style.display = '';
            </script>
            <?php
        }
    }

    protected function showCategoryContents(nBillCategory $category)
    {
        ?>
        <li><?php echo $category->name; ?>
            <ul class="collapsibleList">
                <?php
                if ($category->categories) {
                    foreach ($category->categories as $child_category)
                    {
                        $this->showCategoryContents($child_category);
                    }
                }
                if ($category->products) {
                    foreach ($category->products as $product)
                    {
                        ?>
                        <li><a href="javascript:void(0);" onclick="loadProduct(this,<?php echo intval($product->id); ?>);"><?php echo (strlen($product->product_code) > 0 ? $product->product_code . ' - ' : '') . $product->name; ?></a></li>
                        <?php
                    }
                } ?>
            </ul>
        </li>
        <?php
    }
}