=== Ikreate Demo Importer ===
Contributors: ikreatethemes
Description: Easily imports your content, customizer, widgets and theme settings with one click.
Tags: one click import, demo importer, ikreatethemes, widgets, content, import, demo data, data import, sample data import
Requires at least: 5.5
Tested up to: 6.4
Stable tag: 1.0.2
Requires PHP: 5.6
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl.html

Import your demo content, widgets and theme settings with one click.

== Description ==
Ikreate Demo Import is a free demo importer WordPress plugin allowing users to import the desired demo with just a single click. Ikreate Demo Importer plugin is specially designed and developed to work with themes developed by ikreatethemes. It is especially for demo import purposes. The plugin works out of the box; all you have to do is install and activate the plugin and all the demos available on your currently used theme will be on your fingertips (visit Appearance=> Ikreate Themes Demo Importer). If the plugin doesn’t have any predefine demo import files, then you have to install ikreatethemes [Free Themes](https://ikreatethemes.com/wordpress-themes/) or if you wishing to use this plugin other themes then you need to implement predefine plugin filter for compatibility.


If the theme you are using does not have any predefined import files, then you will be presented some file, First one is required and you will have to export content XML file, for the actual demo import. The second one is a WIE or JSON file for widgets import. You create that file using the [Widget Importer & Exporter](https://wordpress.org/plugins/widget-importer-exporter/) plugin. The third one is the customizer settings, select the DAT file which you can generate from [Customizer Export/Import](https://wordpress.org/plugins/customizer-export-import/) plugin (the customizer settings will be imported only if the export file was created from the same theme), and don't missing to file name, content.xml, widget.wie, customizer.dat then you can zip all above three file with the name of theme name then you can use below predefine plugin filter for example.

You just need to define the array that includes the location of the demo zip files and other related info.

    function ikreate_themes_import_files_array(){
        return array(
            'demo-slug1' => array( // demo-slug should match the 'external_url' zip file name
                'name' => 'Demo Import',
                'type' => 'pro', // the value should be either 'free' or 'pro' - default is 'free'
                'buy_url' => 'https//www.your_domain.com/theme-name/', // optional - only if the 'type' is set to 'pro'
                'external_url' => 'https://www.your_domain.com/import/demo-slug1.zip', // zip file should contain content.xml, customizer.dat, widget.wie, option_name1.json, option_name2.json, revslider.zip(exported slider content from revolution slider) - you can skip any of the files if your demo does not need it
                'image' => 'https://www.your_domain.com/import/screenshot.png',
                'preview_url' => 'https://www.your_domain.com/demo-slug',
                'menu_array' => array( // list of menus
                    'primary' => 'Primary Menu',
                    'secondary' => 'Secondary Menu'
                ),
                'plugins' => array( // these plugins will be installed automatically before demo import
                    'contact-form-7' => array(
                        'name' => 'Contact Form 7',
                        'source' => 'wordpress',
                        'file_path' => 'contact-form-7/wp-contact-form-7.php'
                    )
                ),
                'home_slug' => 'home',
                'blog_slug' => 'blog',
                'tags' => array( // Optional - add filter tab on the header to sort the demo by their type
                        'insurance' => 'Insurance',
                        'roofing' => 'Roofing'
                    )
                )
        );
    }
    add_filter( 'ikdi_demo_data_config', 'ikreate_themes_import_files_array' );

Please refer to [Terms & Conditions](https://ikreatethemes.com/terms-conditions/) and [Privacy Policy](https://ikreatethemes.com/privacy-policy/) for details.

<h4>Features</h4>
<ul>
<li>Reset website (Optional)</li>
<li>Install recommended and required plugins automatically</li>
<li>Imports fully functional demo</li>
</ul>

== Notes ==

* The plugin makes a call to our CloudFront server remotely to import static demo content.

## Get the outstanding themes from Ikreate Themes
__ Check all of our [Free & Premium](https://ikreatethemes.com/wordpress-themes/) themes __


== Installation ==
The easy way to install the plugin is via WordPress.org plugin directory.

<ol>
    <li>Go to WordPress Dashboard > Plugins > Add New</li>
    <li>Search for "Ikreate Demo Importer" and install the plugin.</li>
    <li>Activate Plugin from "Plugins" menu in WordPress.</li>
</ol>

== Frequently Asked Questions ==

= Where is the “Import Demo Data” page?  =

You can find the import page in wp-admin -> Appearance -> Ikreate Demo Importer.

= What is the plugin license?  =

This plugin is released under a GPL license.

= What themes this plugin supports?  =

This plugin support all the themes developed by Ikreate Themes.

= How to predefine demo imports?  =
<code>
<?php
function ikreate_themes_import_files_array(){
    return array(
        'demo-slug1' => array( // demo-slug should match the 'external_url' zip file name
            'name' => 'Demo Import One',
            'type' => 'pro', // the value should be either 'free' or 'pro' - default is 'free'
            'buy_url' => 'https//www.your_domain.com/theme-name/', // optional - only if the 'type' is set to 'pro'
            'external_url' => 'https://www.your_domain.com/import/demo-slug1.zip', // zip file should contain content.xml, customizer.dat, widget.wie, option_name1.json, option_name2.json, revslider.zip(exported slider content from revolution slider) - you can skip any of the files if your demo does not need it
            'image' => 'https://www.your_domain.com/import/screenshot.png',
            'preview_url' => 'https://www.your_domain.com/demo-slug',
            'options_array' => array('option_name1','option_name2'), // option_name1.json, option_name2.json file should be included in the zip file
            'menu_array' => array( // list of menus
                'primary' => 'Primary Menu',
                'secondary' => 'Secondary Menu'
            ),
            'plugins' => array( // these plugins will be installed automatically before demo import
                'contact-form-7' => array(
                    'name' => 'Contact Form 7',
                    'source' => 'wordpress',
                    'file_path' => 'contact-form-7/wp-contact-form-7.php'
                )
            ),
            'home_slug' => 'home',
            'blog_slug' => 'blog',
            'tags' => array( // Optional - add filter tab on the header to sort the demo by their type
                'insurance' => 'Insurance',
                'roofing' => 'Roofing'
            )
        ),
        'demo-slug2' => array(
            'name' => 'Demo Import Two',
            'external_url' => 'http://www.your_domain.com/import/demo-slug2.zip',
            'image' => 'http://www.your_domain.com/import/screenshot.png',
            'preview_url' => 'http://www.your_domain.com/demo-slug2',
            'menu_array' => array(
                'primary' => 'Primary Menu'
            ),
            'home_slug' => 'home',
            'blog_slug' => 'blog'
        )
    );
}

add_filter( 'ikdi_demo_data_config', 'ikreate_themes_import_files_array' );
?>
</code>

== Credits == 
    * Forked from Sparkle Demo Importer Plugin
    
== Changelog ==

= 1.0.2 26nd February 2024 =
* Fixed translation and escaping issue.

= 1.0.1 25nd January 2024 =
* Fixed translation and escaping issue.
* Remove WordPress pre define prefix and add common plugin prefix in function, class and option.
* add translation file.

= 1.0.0 22nd January 2024 =
* Released 1.0.0
