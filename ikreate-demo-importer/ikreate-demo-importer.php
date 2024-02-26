<?php
/*
Plugin Name: Ikreate Demo Importer
Plugin URI: https://github.com/ikreatethemes
Description: Easily imports your content, customizer, widgets and theme settings with one click.
Version: 1.0.2
Requires at least: 5.5
Requires PHP: 5.6
Author: ikreatethemes
Author URI:  https://ikreatethemes.com/
Text Domain: ikreate-demo-importer
License: GPL3
License URI: http://www.gnu.org/licenses/gpl.html
Domain Path: /languages
*/
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly  

define('IKDI_VERSION', '1.0.2');

define('IKDI_FILE', __FILE__);
define('IKDI_PLUGIN_BASENAME', plugin_basename(IKDI_FILE));
define('IKDI_PATH', plugin_dir_path(IKDI_FILE));
define('IKDI_URL', plugins_url('/', IKDI_FILE));
define('IKDI_ASSETS_URL', IKDI_URL . 'assets/');
define('IKDI_DEMODATA_URL', IKDI_URL . 'demodata/');

if (!class_exists('IKDI')) {

    class IKDI {

        public $configFile;
        public $uploads_dir;
        public $plugin_install_count;
        public $plugin_active_count;
        public $theme_name;
        public $ajax_response = array();
        
        /*
         * Constructor
        */
        public function __construct() {

            $this->uploads_dir = wp_get_upload_dir();

            $this->plugin_install_count = 0;
            $this->plugin_active_count = 0;

            $theme = wp_get_theme();
            $this->theme_name = $theme->Name;

            // Include necesarry files
            $this->configFile = include IKDI_PATH . 'import_config.php';

            require_once IKDI_PATH . 'classes/class-demo-importer.php';
            require_once IKDI_PATH . 'classes/class-customizer-importer.php';
            require_once IKDI_PATH . 'classes/class-widget-importer.php';

            // Load translation files
            add_action('init', array($this, 'ikreate_themes_load_plugin_textdomain'));
            
            // Amin Menu
            add_action('admin_menu', array($this, 'ikreate_themes_menu'));
            
            // Add necesary backend JS
            add_action('admin_enqueue_scripts', array($this, 'ikreate_themes_load_backends'));

            // Uploads SVG 
            add_filter('upload_mimes', array($this, 'ikreate_themes_file_types_to_uploads'));
           
            // Actions for the ajax call
            add_action('wp_ajax_ikreate_themes_install_demo', array($this, 'ikreate_themes_install_demo'));
            add_action('wp_ajax_ikreate_themes_install_plugin', array($this, 'ikreate_themes_install_plugin'));
            add_action('wp_ajax_ikreate_themes_ajax_download_files', array($this, 'ikreate_themes_ajax_download_files'));
            add_action('wp_ajax_ikreate_themes_import_xml', array($this, 'ikreate_themes_import_xml'));
            add_action('wp_ajax_ikreate_themes_customizer_import', array($this, 'ikreate_themes_customizer_import'));
            add_action('wp_ajax_ikreate_themes_menu_import', array($this, 'ikreate_themes_menu_import'));
            add_action('wp_ajax_ikreate_themes_importing_widget', array($this, 'ikreate_themes_importing_widget'));
        }


        /*
         * Loads the translation files
         */
         public function ikreate_themes_load_plugin_textdomain() {
            load_plugin_textdomain('ikreate-demo-importer', false, IKDI_PATH . '/languages');
        }

        /*
         * ADMIN Menu for importer
         */
        function ikreate_themes_menu() {
            add_submenu_page('themes.php', esc_html__('Ikreate One Click Demo Install', 'ikreate-demo-importer'), esc_html__('IkreateThemes Demo Importer', 'ikreate-demo-importer'), 'manage_options', 'ikreatethemes-demo-importer', array($this, 'ikreate_themes_display_demos'));
        }

        /*
         *  Ploads SVG
         */
        function ikreate_themes_file_types_to_uploads($file_types) {
            $new_filetypes = array();
            $new_filetypes['svg'] = 'image/svg+xml';
            $file_types = array_merge($file_types, $new_filetypes);
            return $file_types;
        }

        /**
         * @package Ikreate Demo Importer
         * @since 1.0.2
        */
        function ikreate_themes_demo_import_welcome(){
            ?>
                <div class="updated1 notice1 welcome-notice">
                    <h2><?php echo sprintf(esc_html__('Welcome to the Ikreate Demo Importer for %s', 'ikreate-demo-importer'), esc_attr( $this->theme_name ) ); ?></h2>
                </div>
            <?php

        }

        /**
         * @package Ikreate Demo Importer
         * @since 1.0.2
         * ikreate_themes_demo_import_tag_search_filter
         * Display the available demos
         * */
        function ikreate_themes_demo_import_tag_search_filter(){

            if (is_array($this->configFile) && !is_null($this->configFile)) {  
                $tags = $pagebuilders = array();
                foreach ($this->configFile as $demo_slug => $demo_pack) {
                    if (isset($demo_pack['tags']) && is_array($demo_pack['tags'])) {
                        foreach ($demo_pack['tags'] as $key => $tag) {
                            $tags[$key] = $tag;
                        }
                    }
                }
                foreach ($this->configFile as $demo_slug => $demo_pack) {
                    if (isset($demo_pack['pagebuilder']) && is_array($demo_pack['pagebuilder'])) {
                        foreach ($demo_pack['pagebuilder'] as $key => $pagebuilder) {
                            $pagebuilders[$key] = $pagebuilder;
                        }
                    }
                }
                asort($tags);
                asort($pagebuilders);
                
                if (!empty($tags) || !empty($pagebuilders)) {
                    ?>
                    <div class="ikreate-theme-tab-filter">
                        <?php if (!empty($tags)) { ?>
                            <div class="ikreate-theme-tab-group ikreate-theme-tag-group" data-filter-group="tag">
                                <div class="ikreate-theme-tab" data-filter="*">
                                    <?php esc_html_e('All', 'ikreate-demo-importer'); ?>
                                </div>
                                <?php foreach ($tags as $key => $value) { ?>
                                    <div class="ikreate-theme-tab" data-filter=".<?php echo esc_attr($key); ?>">
                                        <?php echo esc_html($value); ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <?php
                        }
                        if (!empty($pagebuilders)) {
                            ?>
                            <div class="ikreate-theme-tab-group ikreate-theme-pagebuilder-group" data-filter-group="pagebuilder">
                                <div class="ikreate-theme-tab" data-filter="*">
                                    <?php esc_html_e('All', 'ikreate-demo-importer'); ?>
                                </div>
                                <?php
                                foreach ($pagebuilders as $key => $value) {
                                    ?>
                                    <div class="ikreate-theme-tab" data-filter=".<?php echo esc_attr($key); ?>">
                                        <?php echo esc_html($value); ?>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        <?php } ?>
                    </div>
                    <?php
                }
            }
        }

        /*
         *  Display the available demos
         */
        function ikreate_themes_display_demos() {
            ?>
            <div class="wrap ikreate-theme-demo-importer-wrap">
                <?php $this->ikreate_themes_demo_import_welcome(); ?>
                <div class="main-wrapper">
                    <?php $this->ikreate_themes_demo_import_tag_search_filter(); ?>
                    
                    <?php if (is_array($this->configFile) && !is_null($this->configFile)) { ?>
                        <div class="ikreate-theme-demo-box-wrap wp-clearfix">
                            <?php
                                // Loop through Demos
                                foreach ($this->configFile as $demo_slug => $demo_pack) {

                                    $tags = $pagebuilder  = $categories = $class = "";

                                    if (isset($demo_pack['tags'])) {
                                        $tags = implode(' ', array_keys($demo_pack['tags']));
                                    }

                                    if (isset($demo_pack['pagebuilder'])) {
                                        $pagebuilder = implode(' ', array_keys($demo_pack['pagebuilder']));
                                    }

                                    $classes = $tags . ' '. $pagebuilder. ' '. $categories;

                                    $type = isset($demo_pack['type']) ? $demo_pack['type'] : 'free';

                                    ?>
                                    <div id="<?php echo esc_attr($demo_slug); ?>" class="ikreate-theme-demo-box <?php echo esc_attr($classes); ?>">
                                        <div class="ikreate-theme-demo-elements">
                                            <?php if ($type == 'pro') { ?>
                                                <div class="ikreate-demo-ribbon"><span><?php echo esc_html__('Premium', 'ikreate-demo-importer') ?></span></div>
                                            <?php } ?>
                                            
                                            <img src="<?php echo esc_url($demo_pack['image']); ?> ">
                                            
                                            <div class="ikreate-theme-demo-actions">
                                                <h4><?php echo esc_html($demo_pack['name']); ?></h4>
                                                <div class="ikreate-theme-demo-buttons">
                                                    <a href="<?php echo esc_url($demo_pack['preview_url']); ?>" target="_blank" class="button preview">
                                                        <?php echo esc_html__('Preview Demo', 'ikreate-demo-importer'); ?>
                                                    </a> 
                                                    
                                                    <?php
                                                        if ($type == 'pro') {
                                                        $buy_url = isset($demo_pack['buy_url']) ? $demo_pack['buy_url'] : '#';
                                                    ?>
                                                        <a href="<?php echo esc_url($buy_url) ?>" target="_blank" class="button button-primary">
                                                            <?php echo esc_html__('Buy Now', 'ikreate-demo-importer') ?>
                                                        </a>
                                                    <?php } else { ?>
                                                        <a href="#ikreate-theme-modal-<?php echo esc_attr($demo_slug) ?>" class="ikreate-theme-modal-button button button-primary">
                                                            <?php echo esc_html__('Install Demo', 'ikreate-demo-importer') ?>
                                                        </a>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="ikreate-theme-demo-wrap">
                            <?php esc_html_e("It looks like the config file for the demos is missing or conatins errors!. Demo install can\'t go futher!", 'ikreate-demo-importer'); ?>  
                        </div>
                    <?php } 

                    /***
                     * Demo Modals 
                     */
                    if (is_array($this->configFile) && !is_null($this->configFile)) {

                        foreach ($this->configFile as $demo_slug => $demo_pack) {
                            ?>
                            <div id="ikreate-theme-modal-<?php echo esc_attr($demo_slug) ?>" class="ikreate-theme-modal" style="display: none;">
                                <div class="ikreate-theme-modal-header">
                                    <h2><?php printf(esc_html('Import %s Demo', 'ikreate-demo-importer'), esc_html($demo_pack['name'])); ?></h2>
                                    <div class="ikreate-theme-modal-back"><span class="dashicons dashicons-no-alt"></span></div>
                                </div>

                                <div class="ikreate-theme-modal-wrap">
                                    <p><?php echo sprintf(esc_html__('We recommend you backup your website content before attempting to import the demo so that you can recover your website if something goes wrong. You can use %s plugin for it.', 'ikreate-demo-importer'), '<a href="https://wordpress.org/plugins/all-in-one-wp-migration/" target="_blank">' . esc_html__('All in one migration', 'ikreate-demo-importer') . '</a>'); ?></p>
                                    <p><?php echo esc_html__('This process will install all the required plugins, import contents and setup customizer and theme options.', 'ikreate-demo-importer'); ?></p>
                                    
                                    <div class="ikreate-theme-modal-recommended-plugins">
                                        <h4><?php esc_html_e('Required Plugins', 'ikreate-demo-importer') ?></h4>
                                        <p><?php esc_html_e('For your website to look exactly like the demo,the import process will install and activate the following plugin if they are not installed or activated.', 'ikreate-demo-importer') ?></p>
                                        <?php
                                            $plugins = isset($demo_pack['plugins']) ? $demo_pack['plugins'] : '';
                                            if (is_array($plugins)) {
                                        ?>
                                            <ul>
                                                <?php
                                                    foreach ($plugins as $plugin) {
                                                    $name = isset($plugin['name']) ? $plugin['name'] : '';
                                                    $status = IKDI_Demo_Importer::ikreate_themes_plugin_active_status($plugin['file_path']);
                                                    if ($status == 'active') {
                                                        $plugin_class = '<span class="dashicons dashicons-yes-alt"></span>';
                                                    } else if ($status == 'inactive') {
                                                        $plugin_class = '<span class="dashicons dashicons-warning"></span>';
                                                    } else {
                                                        $plugin_class = '<span class="dashicons dashicons-dismiss"></span>';
                                                    }
                                                ?>
                                                    <li class="ikreate-theme-<?php echo esc_attr($status); ?>">
                                                        <?php echo wp_kses_post($plugin_class) . ' ' . esc_html($name) . ' - <i>' . esc_html($this->ikreate_themes_get_plugin_status($status)) . '</i>'; ?>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        <?php } ?>
                                    </div>

                                    <div class="ikreate-theme-exclude-image-checkbox">
                                        <h4><?php esc_html_e('Exclude Images', 'ikreate-demo-importer') ?></h4>
                                        <p><?php esc_html_e('Check this option if importing demo fails multiple times. Excluding image will make the demo import process super quick.', 'ikreate-demo-importer') ?></p>
                                        <label>
                                            <input id="checkbox-exclude-image-<?php echo esc_attr($demo_slug); ?>" type="checkbox" value='1'/>
                                            <?php echo esc_html('Yes, Exclude Images', 'ikreate-demo-importer'); ?>
                                        </label>
                                    </div>

                                    <div class="ikreate-theme-reset-checkbox">
                                        <h4><?php esc_html_e('Reset Website', 'ikreate-demo-importer') ?></h4>
                                        <p><?php esc_html_e('Reseting the website will delete all your post, pages, custom post types, categories, taxonomies, images and all other customizer and theme option settings.', 'ikreate-demo-importer') ?></p>
                                        <p><?php esc_html_e('It is always recommended to reset the database for a complete demo import.', 'ikreate-demo-importer') ?></p>
                                        <label>
                                            <input id="checkbox-reset-<?php echo esc_attr($demo_slug); ?>" type="checkbox" value='1'/>
                                            <?php echo esc_html('Reset Website - Check this box only if you are sure to reset the website.', 'ikreate-demo-importer'); ?>
                                        </label>
                                    </div>

                                    <p><strong><?php echo sprintf(esc_html__('IMPORTANT!! Please make sure that there is not any red indication in the %s page for the demo import to work properly.', 'ikreate-demo-importer'), '<a href="' . admin_url('/admin.php?page=systemstatus') . '" target="_blank">' . esc_html__('System Status', 'ikreate-demo-importer') . '</a>'); ?></strong></p>
                                    
                                    <a href="javascript:void(0)" data-demo-slug="<?php echo esc_attr($demo_slug) ?>" class="button button-primary ikreate-theme-import-demo"><?php esc_html_e('Import Demo', 'ikreate-demo-importer'); ?></a>
                                    <a href="javascript:void(0)" class="button ikreate-theme-modal-cancel"><?php esc_html_e('Cancel', 'ikreate-demo-importer'); ?></a>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                    <div id="ikreate-theme-import-progress" style="display: none">
                        <h2 class="ikreate-theme-import-progress-header"><?php echo esc_html__('Demo Import Progress', 'ikreate-demo-importer'); ?></h2>
                        <div class="ikreate-theme-import-progress-wrap">
                            <div class="ikreate-theme-import-loader">
                                <div class="ikreate-theme-loader-content">
                                    <div class="ikreate-theme-loader-content-inside">
                                        <div class="ikreate-theme-loader-rotater"></div>
                                        <div class="ikreate-theme-loader-line-point"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="ikreate-theme-import-progress-message"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        /*
         *  Do the install on ajax call
         */
        function ikreate_themes_install_demo() {
            check_ajax_referer('demo-importer-ajax', 'security');

            // Get the demo content from the right file
            $demo_slug = isset($_POST['demo']) ? sanitize_text_field(wp_unslash($_POST['demo']) ) : '';
            $excludeImages = isset($_POST['excludeImages']) ? sanitize_text_field(wp_unslash($_POST['excludeImages'])) : '';

            if (isset($_POST['reset']) && $_POST['reset'] == 'true') {
                $this->ikreate_themes_database_reset();
                $this->ajax_response['complete_message'] = esc_html__('Database reset complete', 'ikreate-demo-importer');
            }

            $this->ajax_response['demo'] = $demo_slug;
            $this->ajax_response['excludeImages'] = $excludeImages;
            $this->ajax_response['next_step'] = 'ikreate_themes_install_plugin';
            $this->ajax_response['next_step_message'] = esc_html__('Installing required plugins', 'ikreate-demo-importer');
            $this->ikreate_themes_send_ajax_response();
        }

        function ikreate_themes_install_plugin() {
            if (!current_user_can('manage_options')) {
                return;
            }
            check_ajax_referer('demo-importer-ajax', 'security');
            
            $demo_slug = isset($_POST['demo']) ? sanitize_text_field(wp_unslash($_POST['demo']) ) : '';
            $excludeImages = isset($_POST['excludeImages']) ? sanitize_text_field(wp_unslash($_POST['excludeImages'])) : '';

            // Install Required Plugins
            $this->ikreate_themes_install_plugins($demo_slug);
            $plugin_install_count = $this->plugin_install_count;
            
            if ($plugin_install_count > 0) {
                $this->ajax_response['complete_message'] = esc_html__('All the required plugins installed and activated successfully', 'ikreate-demo-importer');
            } else {
                $this->ajax_response['complete_message'] = esc_html__('No plugin required to install', 'ikreate-demo-importer');
            }

            $this->ajax_response['demo'] = $demo_slug;
            $this->ajax_response['next_step'] = 'ikreate_themes_ajax_download_files';
            $this->ajax_response['excludeImages'] = $excludeImages;
            $this->ajax_response['next_step_message'] = esc_html__('Downloading demo files', 'ikreate-demo-importer');
            $this->ikreate_themes_send_ajax_response();
        }

        function ikreate_themes_ajax_download_files() {
            if (!current_user_can('manage_options')) {
                return;
            }
            check_ajax_referer('demo-importer-ajax', 'security');

            $demo_slug = isset($_POST['demo']) ? sanitize_text_field(wp_unslash($_POST['demo']) ) : '';
            $excludeImages = isset($_POST['excludeImages']) ? sanitize_text_field(wp_unslash($_POST['excludeImages'])) : '';

            $downloads = $this->ikreate_themes_download_files($this->configFile[$demo_slug]['external_url']);
            if ($downloads) {
                $this->ajax_response['complete_message'] = esc_html__('All demo files downloaded', 'ikreate-demo-importer');
                $this->ajax_response['next_step'] = 'ikreate_themes_import_xml';
                $this->ajax_response['next_step_message'] = esc_html__('Importing posts, pages and medias. It may take a bit longer time', 'ikreate-demo-importer');
            } else {
                $this->ajax_response['error'] = true;
                $this->ajax_response['error_message'] = esc_html__('Demo import process failed. Demo files can not be downloaded', 'ikreate-demo-importer');
            }

            $this->ajax_response['demo'] = $demo_slug;
            $this->ajax_response['excludeImages'] = $excludeImages;
            $this->ikreate_themes_send_ajax_response();
        }
        
        function ikreate_themes_import_xml() {
            if (!current_user_can('manage_options')) {
                return;
            }

            check_ajax_referer('demo-importer-ajax', 'security');

            $demo_slug = isset($_POST['demo']) ? sanitize_text_field(wp_unslash($_POST['demo']) ) : '';
            $excludeImages = isset($_POST['excludeImages']) ? sanitize_text_field(wp_unslash($_POST['excludeImages'])) : '';

            // Import XML content
            $xml_filepath = $this->ikreate_themes_demo_upload_dir($demo_slug) . '/content.xml';

            if (file_exists($xml_filepath)) {
                $this->ikreate_themes_import_demo_content($xml_filepath, $excludeImages);
                $this->ajax_response['complete_message'] = esc_html__('All content imported', 'ikreate-demo-importer');
                $this->ajax_response['next_step'] = 'ikreate_themes_customizer_import';
                $this->ajax_response['next_step_message'] = esc_html__('Importing customizer settings', 'ikreate-demo-importer');
            } else {
                $this->ajax_response['error'] = true;
                $this->ajax_response['error_message'] = esc_html__('Demo import process failed. No content file found', 'ikreate-demo-importer');
            }

            $this->ajax_response['demo'] = $demo_slug;
            $this->ajax_response['excludeImages'] = $excludeImages;
            $this->ikreate_themes_send_ajax_response();
        }

        function ikreate_themes_customizer_import() {
            if (!current_user_can('manage_options')) {
                return;
            }
            check_ajax_referer('demo-importer-ajax', 'security');

            $demo_slug = isset($_POST['demo']) ? sanitize_text_field(wp_unslash($_POST['demo']) ) : '';
            $excludeImages = isset($_POST['excludeImages']) ? sanitize_text_field(wp_unslash($_POST['excludeImages'])) : '';

            $customizer_filepath = $this->ikreate_themes_demo_upload_dir($demo_slug) . '/customizer.dat';

            if (file_exists($customizer_filepath)) {
                ob_start();
                IKDI_Customizer_Importer::import($customizer_filepath, $excludeImages);
                ob_end_clean();
                $this->ajax_response['complete_message'] = esc_html__('Customizer settings imported', 'ikreate-demo-importer');
            } else {
                $this->ajax_response['complete_message'] = esc_html__('No Customizer settings found', 'ikreate-demo-importer');
            }
            $this->ajax_response['demo'] = $demo_slug;
            $this->ajax_response['next_step'] = 'ikreate_themes_menu_import';
            $this->ajax_response['next_step_message'] = esc_html__('Setting primary menu', 'ikreate-demo-importer');
            $this->ikreate_themes_send_ajax_response();
        }

        function ikreate_themes_menu_import() {
            if (!current_user_can('manage_options')) {
                return;
            }
            check_ajax_referer('demo-importer-ajax', 'security');

            $demo_slug = isset($_POST['demo']) ? sanitize_text_field(wp_unslash($_POST['demo']) ) : '';

            $menu_array = isset($this->configFile[$demo_slug]['menuArray']) ? $this->configFile[$demo_slug]['menuArray'] : '';
            
            // Set menu
            if ($menu_array) {
                $this->setMenu($menu_array);
            }
            // Set menu
            if ($menu_array) {
                $this->setMenu($menu_array);
                $this->ajax_response['complete_message'] = esc_html__('Menus saved', 'ikreate-demo-importer');
            } else {
                $this->ajax_response['complete_message'] = esc_html__('No menus saved', 'ikreate-demo-importer');
            }

            $this->ajax_response['demo'] = $demo_slug;
            $this->ajax_response['next_step'] = 'ikreate_themes_importing_widget';
            $this->ajax_response['next_step_message'] = esc_html__('Importing Widgets', 'ikreate-demo-importer');
            $this->ikreate_themes_send_ajax_response();
        }

        function ikreate_themes_importing_widget() {
            if (!current_user_can('manage_options')) {
                return;
            }
            check_ajax_referer('demo-importer-ajax', 'security');

            $demo_slug = isset($_POST['demo']) ? sanitize_text_field(wp_unslash($_POST['demo']) ) : '';

            $widget_filepath = $this->ikreate_themes_demo_upload_dir($demo_slug) . '/widget.wie';

            if (file_exists($widget_filepath)) {
                ob_start();
                IKDI_Widget_Importer::import($widget_filepath);
                ob_end_clean();
                $this->ajax_response['complete_message'] = esc_html__('Widgets Imported', 'ikreate-demo-importer');
            } else {
                $this->ajax_response['complete_message'] = esc_html__('No Widgets found', 'ikreate-demo-importer');
            }

            $this->ajax_response['demo'] = $demo_slug;
            $this->ajax_response['next_step'] = '';
            $this->ajax_response['next_step_message'] = '';
            $this->ikreate_themes_send_ajax_response();
        }

        public function ikreate_themes_download_files($external_url) {

            // Make sure we have the dependency.
            if (!function_exists('WP_Filesystem')) {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
            }

            /**
             * Initialize WordPress' file system handler.
             *
             * @var WP_Filesystem_Base $wp_filesystem
             */
            WP_Filesystem();
            global $wp_filesystem;

            $result = true;
            if (!($wp_filesystem->exists($this->ikreate_themes_demo_upload_dir()))) {
                $result = $wp_filesystem->mkdir($this->ikreate_themes_demo_upload_dir());
            }

            // Abort the request if the local uploads directory couldn't be created.
            // if (!$result) {
            //     $this->add_ajax_message['message'] = esc_html__('The directory for the demo packs couldn\'t be created.', 'ikreate-demo-importer');
            //     $this->ajax_response['error'] = true;
            //     $this->ikreate_themes_send_ajax_response();
            // }
            // Abort the request if the local uploads directory couldn't be created.
            if (!$result) {
                return false;
            } else {
                $demo_pack = $this->ikreate_themes_demo_upload_dir() . 'demo-pack.zip';
                $file = wp_remote_retrieve_body(wp_remote_get($external_url, array(
                    'timeout' => 60,
                )));
                
                $wp_filesystem->put_contents($demo_pack, $file);
                unzip_file($demo_pack, $this->ikreate_themes_demo_upload_dir());
                $wp_filesystem->delete($demo_pack);
                return true;
            }
        }

        /*
         * Reset the database, if the case
         */
        function ikreate_themes_database_reset() {
            global $wpdb;
            $options = array(
                'offset' => 0,
                'orderby' => 'post_date',
                'order' => 'DESC',
                'post_type' => 'post',
                'post_status' => 'publish'
            );
            $statuses = array('publish', 'future', 'draft', 'pending', 'private', 'trash', 'inherit', 'auto-draft', 'scheduled');
            $types = array(
                'post',
                'page',
                'attachment',
                'nav_menu_item',
                'wpcf7_contact_form',
                'product',
                'portfolio',
                'custom_css'
            );

            // delete posts
            foreach ($types as $type) {
                foreach ($statuses as $status) {
                    $options['post_type'] = $type;
                    $options['post_status'] = $status;
                    $posts = get_posts($options);
                    $offset = 0;
                    while (count($posts) > 0) {
                        if ($offset == 10) {
                            break;
                        }
                        $offset++;
                        foreach ($posts as $post) {
                            wp_delete_post($post->ID, true);
                        }
                        $posts = get_posts($options);
                    }
                }
            }

            // Delete categories, tags, etc
            $taxonomies_array = array('category', 'post_tag', 'portfolio_type', 'nav_menu', 'product_cat');
            foreach ($taxonomies_array as $tax) {
                $cats = get_terms($tax, array('hide_empty' => false ));
                foreach ($cats as $cat) {
                    wp_delete_term($cat, $tax);
                }
            }

            // Delete Widgets
            global $wp_registered_widget_controls;
            $widget_controls = $wp_registered_widget_controls;
            $available_widgets = array();
            foreach ($widget_controls as $widget) {
                if (!empty($widget['id_base']) && !isset($available_widgets[$widget['id_base']])) {
                    $available_widgets[] = $widget['id_base'];
                }
            }
            update_option('sidebars_widgets', array('wp_inactive_widgets' => array()));
            foreach ($available_widgets as $widget_data) {
                update_option('widget_' . $widget_data, array());
            }

            // Delete Thememods
            $theme_slug = get_option('stylesheet');
            $mods = get_option("theme_mods_$theme_slug");
            if (false !== $mods) {
                delete_option("theme_mods_$theme_slug");
            }

            //Clear "uploads" folder
            $this->ikreate_themes_clear_uploads($this->uploads_dir['basedir']);
        }

        /**
         * Clear "uploads" folder
         * @param string $dir
         * @return bool
         */
        private function ikreate_themes_clear_uploads($dir) {
            if( scandir($dir) == false) return true;
            
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                ( is_dir("$dir/$file") ) ? $this->ikreate_themes_clear_uploads("$dir/$file") : unlink("$dir/$file");
            }
            return ( $dir != $this->uploads_dir['basedir'] ) ? rmdir($dir) : true;
        }

        /*
         * Import demo XML content
         */
        function ikreate_themes_import_demo_content($xml_filepath, $excludeImages) {

            if (!defined('WP_LOAD_IMPORTERS'))
                define('WP_LOAD_IMPORTERS', true);

            if (!class_exists('IKDI_Import')) {
                $class_wp_importer = IKDI_PATH . "wordpress-importer/wordpress-importer.php";
                if (file_exists($class_wp_importer)) {
                    require_once $class_wp_importer;
                }
            }

            // Import demo content from XML
            if (class_exists('IKDI_Import')) {

                //$import_filepath = $this->ikreate_themes_demo_upload_dir($slug) . '/content.xml'; // Get the xml file from directory 
                
                $demo_slug = isset($_POST['demo']) ? sanitize_text_field( wp_unslash( $_POST['demo'] ) ) : '';
                $excludeImages = $excludeImages == 'true' ? false : true;
                $home_slug = isset($this->configFile[$demo_slug]['home_slug']) ? $this->configFile[$demo_slug]['home_slug'] : '';
                $blog_slug = isset($this->configFile[$demo_slug]['blog_slug']) ? $this->configFile[$demo_slug]['blog_slug'] : '';

                if (file_exists($xml_filepath)) {
                    $IKDI_Import = new IKDI_Import();
                    $IKDI_Import->fetch_attachments = $excludeImages;

                    // Capture the output.
                    ob_start();
                    $IKDI_Import->import($xml_filepath);
                    // Clean the output.
                    ob_end_clean();

                    if($home_slug){
                        $page = get_page_by_path($home_slug);
                        if ($page) {
                            update_option('show_on_front', 'page');
                            update_option('page_on_front', $page->ID);
                        } else {
                            $page = get_page_by_title('Home');
                            if ($page) {
                                update_option('show_on_front', 'page');
                                update_option('page_on_front', $page->ID);
                            }
                        }
                    }

                    if($blog_slug){
                        $page = get_page_by_path($blog_slug);
                        if ($blog) {
                            update_option('show_on_front', 'page');
                            update_option('page_for_posts', $blog->ID);
                        }
                    }

                    if (!$home_slug && !$blog_slug) {
                        update_option('show_on_front', 'posts');
                    }
                }
            }
        }

        function ikreate_themes_demo_upload_dir($path = '') {
            $upload_dir = $this->uploads_dir['basedir'] . '/demo-pack/' . $path;
            return $upload_dir;
        }

        function ikreate_themes_install_plugins($slug) {
            $demo = $this->configFile[$slug];

            $plugins = $demo['plugins'];

            foreach ($plugins as $plugin_slug => $plugin) {
                $name = isset($plugin['name']) ? $plugin['name'] : '';
                $source = isset($plugin['source']) ? $plugin['source'] : '';
                $file_path = isset($plugin['file_path']) ? $plugin['file_path'] : '';
                $location = isset($plugin['location']) ? $plugin['location'] : '';
                
                if ($source == 'wordpress') {
                    $this->ikreate_themes_plugin_installer_callback($file_path, $plugin_slug);
                } else {
                    $this->ikreate_themes_plugin_offline_installer_callback($file_path, $location);
                }
            }
        }

        public function ikreate_themes_plugin_installer_callback($path, $slug) {

            $plugin_status = $this->ikreate_themes_plugin_status($path);

            if ($plugin_status == 'install') {

                // Include required libs for installation
                require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
                require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

                // Get Plugin Info
                $api = $this->ikreate_themes_call_plugin_api($slug);
                $skin = new WP_Ajax_Upgrader_Skin();
                $upgrader = new Plugin_Upgrader($skin);
                $upgrader->install($api->download_link);

                $this->ikreate_themes_activate_plugin($path);

                $this->plugin_install_count++;

            } else if ($plugin_status == 'inactive') {;

                $this->ikreate_themes_activate_plugin($path);
                $this->plugin_install_count++;
            }
        }

        public function ikreate_themes_plugin_offline_installer_callback($path, $external_url) {
            
            $plugin_status = $this->ikreate_themes_plugin_status($path);
            
            if ($plugin_status == 'install') {

                // Make sure we have the dependency.
                if (!function_exists('WP_Filesystem')) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                }

                /**
                 * Initialize WordPress' file system handler.
                 *
                 * @var WP_Filesystem_Base $wp_filesystem
                 */
                WP_Filesystem();
                global $wp_filesystem;
                $plugin = $this->ikreate_themes_demo_upload_dir() . 'plugin.zip';

                $file = wp_remote_retrieve_body(wp_remote_get($external_url, array(
                    'timeout' => 60,
                )));
                $wp_filesystem->mkdir($this->ikreate_themes_demo_upload_dir());
                $wp_filesystem->put_contents($plugin, $file);
                
                unzip_file($plugin, WP_PLUGIN_DIR);

                $plugin_file = WP_PLUGIN_DIR . '/' . esc_html($path);
                if (file_exists($plugin_file)) {
                    $this->ikreate_themes_activate_plugin($path);
                    $this->plugin_install_count++;
                }
                $wp_filesystem->delete($plugin);

            } else if ($plugin_status == 'inactive') {
                $this->ikreate_themes_activate_plugin($path);
                $this->plugin_install_count++;
            }
        }

        /* Plugin API */
        public function ikreate_themes_call_plugin_api($slug) {

            include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

            $call_api = plugins_api('plugin_information', array(
                'slug' => $slug,
                'fields' => array(
                    'downloaded' => false,
                    'rating' => false,
                    'description' => false,
                    'short_description' => false,
                    'donate_link' => false,
                    'tags' => false,
                    'sections' => false,
                    'homepage' => false,
                    'added' => false,
                    'last_updated' => false,
                    'compatibility' => false,
                    'tested' => false,
                    'requires' => false,
                    'downloadlink' => true,
                    'icons' => false
            )));
            return $call_api;
        }

        public function ikreate_themes_activate_plugin($file_path) {
            if ($file_path) {
                $activate = activate_plugin($file_path, '', false, true);
            }
        }

        /* Check if plugin is active or not */
        public function ikreate_themes_plugin_status($file_path) {
            $status = 'install';
            $plugin_path = WP_PLUGIN_DIR . '/' . $file_path;
            if (file_exists($plugin_path)) {
                $status = is_plugin_active($file_path) ? 'active' : 'inactive';
            }
            return $status;
        }

        public function ikreate_themes_get_plugin_status($status) {
            switch ($status) {
                case 'install':
                    $plugin_status = esc_html__('Not Installed', 'ikreate-demo-importer');
                    break;
                case 'active':
                    $plugin_status = esc_html__('Installed and Active', 'ikreate-demo-importer');
                    break;
                case 'inactive':
                    $plugin_status = esc_html__('Installed but Not Active', 'ikreate-demo-importer');
                    break;
            }
            return $plugin_status;
        }
        
        public function ikreate_themes_send_ajax_response() {
            $json = wp_json_encode($this->ajax_response);
            echo $json;
            die();
        }

        /*
         * Set the menu on theme location
         */
        function setMenu($menuArray) {
            if (!$menuArray) {
                return;
            }
            $locations = get_theme_mod('nav_menu_locations');
            foreach ($menuArray as $menuId => $menuname) {
                $menu_exists = wp_get_nav_menu_object($menuname);
                if (!$menu_exists) {
                    $term_id_of_menu = wp_create_nav_menu($menuname);
                } else {
                    $term_id_of_menu = $menu_exists->term_id;
                }
                $locations[$menuId] = $term_id_of_menu;
            }
            set_theme_mod('nav_menu_locations', $locations);
        }
        
        /**
         * Register necessary backend js
         */
        function ikreate_themes_load_backends() {
            $data = array(
                'nonce' => wp_create_nonce('demo-importer-ajax'),
                'prepare_importing' => esc_html__('Preparing to import demo', 'ikreate-demo-importer'),
                'reset_database' => esc_html__('Reseting database', 'ikreate-demo-importer'),
                'no_reset_database' => esc_html__('Database was not reset', 'ikreate-demo-importer'),
                'import_error' => '<p>'. esc_html__('There was an error in importing demo. Please reload the page and try again', 'ikreate-demo-importer').'</p> <a class="button" href="">Refresh</a>',
                'import_success' => '<h2>' . esc_html__('All done. Have fun!', 'ikreate-demo-importer') . '</h2><p>' . esc_html__('Your website has been successfully setup.', 'ikreate-demo-importer') . '</p><a class="button" target="_blank" href="' . esc_url(home_url('/')) . '">' . esc_html__('View your Website', 'ikreate-demo-importer') . '</a><a class="button" href="">' . esc_html__('Go Back', 'ikreate-demo-importer') . '</a>'
            );
            wp_enqueue_script('isotope-pkgd', IKDI_ASSETS_URL . 'isotope.pkgd.js', array('jquery'), IKDI_VERSION, true);
            wp_enqueue_script('ikreate-theme-demo-ajax', IKDI_ASSETS_URL . 'demo-importer-ajax.js', array('jquery', 'imagesloaded'), IKDI_VERSION, true);
            wp_localize_script('ikreate-theme-demo-ajax', 'ikreate_ajax_data', $data);
            wp_enqueue_style('ikreate-theme-demo-style', IKDI_ASSETS_URL . 'demo-importer-style.css', array(), IKDI_VERSION);
        }
    }
}

function ikdi_Importer() {
    new IKDI;
}
add_action('after_setup_theme', 'ikdi_Importer');