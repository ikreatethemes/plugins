<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
	return;
}

/** WordPress Import Administration API */
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( ! class_exists( 'WP_Importer' ) ) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) ) {
		require $class_wp_importer;
	}
}

/** Functions missing in older WordPress versions. */
require_once dirname( __FILE__ ) . '/compat.php';

/** IKDI_Parser class */
require_once dirname( __FILE__ ) . '/parsers/class-wxr-parser.php';

/** IKDI_Parser_SimpleXML class */
require_once dirname( __FILE__ ) . '/parsers/class-wxr-parser-simplexml.php';

/** IKDI_Parser_XML class */
require_once dirname( __FILE__ ) . '/parsers/class-wxr-parser-xml.php';

/** IKDI_Parser_Regex class */
require_once dirname( __FILE__ ) . '/parsers/class-wxr-parser-regex.php';

/** IKDI_Import class */
require_once dirname( __FILE__ ) . '/class-wp-import.php';