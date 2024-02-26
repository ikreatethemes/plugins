<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/*
 * Config file with each demo data
 */

$business_roy = array(
    'businessroy' => array(
        'name' => 'Business Roy',
        'external_url' => 'https://free.ikreatethemes.com/demo-data/businessroy/businessroy/businessroy.zip',
        'image' => IKDI_DEMODATA_URL . 'businessroy/businessroy.png',
        'preview_url' => 'https://free.ikreatethemes.com/business-roy/',
        'menuArray' => array(
            'menu-1' => 'primary',
        ),
        'home_slug' => '',
        'tags' => array(
            'free' => 'Free',
        ),
        'plugins' => array(
            'contact-form-7' => array(
                'name' => 'Contact Form 7',
                'source' => 'wordpress',
                'file_path' => 'contact-form-7/wp-contact-form-7.php'
            ),
            'elementor' => array(
                'name' => 'Elementor',
                'source' => 'wordpress',
                'file_path' => 'elementor/elementor.php',
            ),
        ),
    )
);

$active_theme = str_replace('-', '_', get_option('stylesheet'));

if (isset($$active_theme)) {
    $demo_array = $$active_theme;
} else {
    $demo_array = array();
}

return apply_filters('ikdi_demo_data_config', $demo_array);