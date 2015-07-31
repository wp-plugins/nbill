<?php
class nBillWpController
{
    protected $plugin_file;
    public $component_name = 'nbill';

    public function __construct($plugin_file, $component_name)
    {
        $this->plugin_file = $plugin_file;
        $this->component_name = $component_name;
        ob_start();
    }

    public function __destruct()
    {
        @ob_flush(); //Default WP templates seem to get the ob nesting wrong. Gantry (and possibly others) get it right. In any case, this is supposed to match up with the constructor.
    }

    public function bootstrap()
    {
        register_activation_hook($this->plugin_file, array($this, 'activate'));
        register_deactivation_hook($this->plugin_file, array($this, 'deactivate'));
        add_action('admin_menu', array($this, 'nbill_admin_menu'));
        add_action('init', array($this, 'create_post_type'));
        add_action('init', array($this, 'start_session'), 1);
        add_action('admin_enqueue_scripts', array($this, 'disable_autosave'));
        add_filter('single_template', array($this, 'nbill_fe_template'));
        add_filter('get_user_option_metaboxhidden_nav-menus', array($this, 'nbill_always_visible'), 10, 3);
    }

    public function nbill_always_visible( $result, $option, $user )
    {
        if( in_array( 'add-nbill', $result ) ) {
            $result = array_diff( $result, array( 'add-nbill' ) );
        }
        return $result;
    }

    public function start_session()
    {
        session_start();
    }

    public function activate()
    {
        //Create default post to allow access to front-end features
        $nbill_fe_post = array(
            'post_title' => 'My Account',
            'post_status' => 'publish',
            'post_type' => $this->component_name,
        );
        $post_id = wp_insert_post($nbill_fe_post);
        update_option('nbill-options', array('default_post_id'=>$post_id));

        ob_start();
        include(plugin_dir_path(__FILE__) . '/administrator/components/com_nbill/install.nbill.php');
        echo com_install(true);
        $result = ob_end_clean();
        if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'warning') !== false) {
            trigger_error($result, E_USER_ERROR);
        }
    }

    public function deactivate()
    {
        //Delete default post
        $options = get_option('nbill-options');
        $post_id = isset($options['default_post_id']) ? intval($options['default_post_id']) : 0;
        if ($post_id > 0) {
            wp_delete_post($post_id, true);
        }
    }

    public function nbill_admin_menu() {
        if (!defined('NBILL_BRANDING_NAME')) {
            if (!defined('NBILL_VALID_NBF')) {
                define('NBILL_VALID_NBF', '1');
            }
            if (file_exists(plugin_dir_path(__FILE__) . '/administrator/components/com_nbill/branding_custom.php')) {
                include_once(plugin_dir_path(__FILE__) . '/administrator/components/com_nbill/branding_custom.php');
            }
            if (class_exists('nbill_custom_branding') && strlen(nbill_custom_branding::$product_name) > 0) {
                define('NBILL_BRANDING_NAME', nbill_custom_branding::$product_name);
            } else {
                define('NBILL_BRANDING_NAME', 'nBill');
            }
        }
        add_menu_page(NBILL_BRANDING_NAME, NBILL_BRANDING_NAME, 'manage_options', $this->plugin_file, array($this, 'nbill_plugin_admin'), plugins_url() . '/nbill/components/com_nbill/images/logo-icon-16.png');
    }

    public function nbill_plugin_admin()
    {
        if (!current_user_can('manage_options'))  {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        if (class_exists('nbf_cms')) {
            nbf_cms::set_interop(true); //Undo the damage done by wordpress magic quotes
        }
        include(plugin_dir_path(__FILE__) . '/administrator/components/com_nbill/nbill.php');
    }

    public function create_post_type() {
        register_post_type($this->component_name, array(
                'labels' => array(
                    'name' => __('nBill Front-end'),
                    'singular_name' => __('Front-end Page')
                ),
                'can_export'          => false,
                'exclude_from_search' => true,
                'hierarchical'        => false,
                'public'              => false,
                'publicly_queryable'  => true,
                'query_var'           => $this->component_name,
                'show_in_menu'        => false,
                'supports' => false,
                'has_archive' => false,
                'rewrite' => array('slug' => $this->component_name),
                'capability_type' => 'post',
                'show_ui' => false,
                'show_in_nav_menus' => true
            )
        );
        flush_rewrite_rules();
    }

    public function disable_autosave() {
        if (get_post_type() == $this->component_name) {
            wp_dequeue_script('autosave');
        }
    }

    function nbill_fe_template($single) {
        if (get_post_type() == $this->component_name) {
            if (class_exists('nbf_cms')) {
                nbf_cms::set_interop(true); //Undo the damage done by wordpress magic quotes and re-load the user now we have initialised
            }
            if ($theme_file = locate_template(array('single-nbill.php'))) {
                return $theme_file;
            } else {
                if(file_exists(plugin_dir_path(__FILE__) . '/single-nbill.php')) {
                    return plugin_dir_path(__FILE__) . '/single-nbill.php';
                }
            }
        }
        return $single;
    }
}