<?php
/*
Plugin Name: Theme Wizard
Plugin URI: http://yourwebsite.com/theme-wizard
Description: A theme setup wizard for easy theme installation and setup.
Version: 1.0
Author: CareSort
*/

// Make sure we don't expose any info if called directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function install_custom_plugin_on_activation() {
    $plugin_dir = plugin_dir_path(__FILE__);
    $import_plugin_dir = $plugin_dir . 'import_plugin';
    $target_plugin_dir = WP_PLUGIN_DIR;

    if (is_dir($import_plugin_dir)) {
        $plugin_zips = glob($import_plugin_dir . '/*.zip');
        
        foreach ($plugin_zips as $zip_file) {
            $zip = new ZipArchive();
            if ($zip->open($zip_file) === TRUE) {
                // Extract the zip file
                $zip->extractTo($target_plugin_dir);
                $zip->close();

                // Get the plugin folder name (assuming it's the root folder in the zip)
                $plugin_folder = basename($zip_file, '.zip');

                // Construct the plugin path relative to the plugins directory
                $plugin_path = trailingslashit($plugin_folder) . $plugin_folder . '.php';

                // Check if the plugin is already active
                if (!is_plugin_active($plugin_path)) {
                    // Activate the plugin
                    activate_plugin($plugin_path);
                } else {
                    // Log that the plugin is already activated
                    error_log('Plugin already activated: ' . $plugin_path);
                }
            } else {
                // Handle zip extraction failure
                error_log('Failed to extract zip file: ' . $zip_file);
            }
        }
    }
}

register_activation_hook(__FILE__, 'install_custom_plugin_on_activation');



// Include the WordPress Importer
require_once ABSPATH . 'wp-admin/includes/class-wp-importer.php';
    
    $plugin_dir = plugin_dir_path(__FILE__);

      //  $class_wp_importer = $plugin_dir . '/includes/class-wp-importer.php';

//require_once($plugin_dir . 'includes/wordpress-importer/class-wp-import.php');
require_once($plugin_dir . '../../../wp-load.php');
 require_once($plugin_dir . '../../../wp-admin/includes/file.php');
    require_once($plugin_dir . '../../../wp-admin/includes/plugin-install.php');
        require_once $plugin_dir . '../../../wp-admin/includes/import.php';





function theme_wizard_scripts() {
    wp_enqueue_script('theme-wizard-script', plugin_dir_url(__FILE__) . 'js/theme-wizard.js', array('jquery'), '1.0', true);
    wp_enqueue_style('theme-wizard-style', plugin_dir_url(__FILE__) . 'css/theme-wizard.css', array(), '1.0');
    
    wp_localize_script('theme-wizard-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    
    wp_localize_script('theme-wizard-script', 'theme_wizard_data', array(
        'selected_theme' => esc_html(get_option('selected_theme')),
        'title' => esc_html(get_option('custom_title')),
        'logo' => esc_html(get_option('custom_logo')),
        'header_menu' => esc_html(get_option('custom_header_menu')),
        'footer_menu' => esc_html(get_option('custom_footer_menu')),
        'selected_demo_content' => esc_html(get_option('selected_demo_content')),
        'selected_plugins' => json_encode(get_option('selected_plugins'))
    ));

  
}
add_action('admin_enqueue_scripts', 'theme_wizard_scripts',20);

add_action('init', 'plugin_theme_setup');
function plugin_theme_setup() {
    add_theme_support('menus');
}



function add_theme_wizard_menu() {
    add_menu_page('Theme Wizard', 'Theme Wizard', 'manage_options', 'theme-wizard', 'theme_wizard_step_select_theme', 'dashicons-admin-generic', 30);
}
add_action('admin_menu', 'add_theme_wizard_menu');



function find_theme_thumbnail_url($theme_folder_path) {
    $screenshot_path = $theme_folder_path . '/screenshot.png';
    if (file_exists($screenshot_path)) {
        return plugins_url('listed_themes/' . basename($theme_folder_path) . '/screenshot.png', __FILE__);
    }
    return ''; 
}


// Step 1: Select Theme
function theme_wizard_step_select_theme() {
   
     echo '<div class="wrap">';
    

    $themes_directory = plugin_dir_path(__FILE__) . 'listed_themes';

    if (is_dir($themes_directory)) {
        $folders = scandir($themes_directory);

        echo '<div class="bg__">';
        echo '<h2 class="head__">Theme Wizard</h2>';
        echo '<form id="theme-selection-form" method="post" action="">';
        foreach ($folders as $folder) {
            if ($folder != '.' && $folder != '..' && is_dir($themes_directory . '/' . $folder)) {
                $thumbnail_url = find_theme_thumbnail_url($themes_directory . '/' . $folder);
                echo '<label class="new_lbl"><div class="radio__"><input type="radio" name="selected_theme" value="' . $folder . '"></div> ';
                if (!empty($thumbnail_url)) {
                    echo '<img src="' . $thumbnail_url . '" alt="Theme Thumbnail">';
                } else {
                    echo 'No Thumbnail';
                }
                echo '<h3 class="theme_title__">'.$folder.'</h3>' . '</label><br>';
            }
        }
    
       
        
        echo '</form>';
         echo '<input type="button" id="next-step-btn" value="Next" disabled>';
        echo '</div>';
        echo '    <div id="message-container"></div>
';
    } else {
        echo '<p>Themes directory not found.</p>';
    }

    echo '</div>';
}




add_action('wp_ajax_theme_selection', 'theme_selection_callback');
function theme_selection_callback() {
    if (isset($_POST['selected_theme'])) {
        $selected_theme = sanitize_text_field($_POST['selected_theme']);

        update_option('selected_theme', $selected_theme); 

        theme_wizard_step_customize_theme();
    } else {
        echo 'Error: No theme data provided.';
    }
    wp_die(); 
}


// Step 2: Customize Theme
function theme_wizard_step_customize_theme() {
    echo '<div class="wrap">';
   
    
  
    echo '<div class="bg__2">';
    echo '<h2>Customize Theme</h2>';
    echo '<div class="bg__center">';
    echo '<div class="ajaxer">';
    echo '<form method="post" action="" enctype="multipart/form-data>'; // Opening form tag

    echo '<div class="title__">';
    echo '<label for="title">Title:</label>';
    echo '<input type="text" id="title" name="title"><br>';
    echo '</div>';
    
     echo '<div class="logochange__">';
     echo '<label for="logo">Logo:</label>';
     echo '<input type="file" id="logo" name="logo"><br>';
     echo '</div>';
    
    echo '<div class="create-menu">';
    echo '<input type="checkbox" id="create-menu" name="create_menu" value="1">';
    echo '<label for="create-menu" style="color:#fff; vertical-align: bottom;">Create Menu</label><br>';
    echo '</div>';

    echo '<div class="header-menu" style="display: none;">';
    echo '<input type="checkbox" id="header-menu" name="header_menu" value="1" >';
    echo '<label for="header-menu" style="color:#fff; vertical-align: bottom; margin-top:5px; display: inline-block;">Include Header Menu</label><br>';
    echo '</div>';

    echo '<div class="footer-menu" style="display: none;">';
    echo '<input type="checkbox" id="footer-menu" name="footer_menu" value="1" >';
    echo '<label for="footer-menu" style="color:#fff; vertical-align: bottom; margin-top:5px; display: inline-block;">Include Footer Menu</label><br>';
    echo '</div>';
    
      echo '<div class="btn_bn">';
    echo '<input type="button" id="next-step-btn-2" value="Next">';
    echo '</div>';
    
    echo '</form>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}


add_action('wp_ajax_customize_theme', 'customize_theme_callback');
function customize_theme_callback() {
    $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $header_menu = $_POST['header_menu'];
    $footer_menu = $_POST['footer_menu'];
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $logo = $_FILES['logo'];
        $uploaded_logo_id = media_handle_upload('logo', 0); 
       
        if (is_wp_error($uploaded_logo_id)) {
            echo 'Error: Failed to upload logo.';
        } else {
            update_option('custom_title', $title);
            update_option('custom_header_menu', $header_menu);
            update_option('custom_footer_menu', $footer_menu);
            update_option('uploaded_logo_id', $uploaded_logo_id);
            theme_wizard_step_choose_demo_content();
        }
    } else {
        // If no logo file is uploaded, set the uploaded_logo_id option to empty
        update_option('uploaded_logo_id', '');

        update_option('custom_title', $title);
        update_option('custom_header_menu', $header_menu);
        update_option('custom_footer_menu', $footer_menu);
        
        theme_wizard_step_choose_demo_content();
    }

    wp_die();
}





// Step 3: Choose Demo Content
function theme_wizard_step_choose_demo_content() {
    
    // Get the site URL dynamically
    $site_url = get_site_url();

    // Generate the WordPress Importer URL
    $importer_url = $site_url . '/wp-admin/admin.php?import=wordpress';

 

    // Get the path to the demo content folder
    $demo_content_folder = plugin_dir_path(__FILE__) . 'demo_content';
    
    // Check if the demo content folder exists
    if (is_dir($demo_content_folder)) {
        // Get the list of files in the demo content folder
        $files = scandir($demo_content_folder);
        
         echo '<div class="bg__2">';
            echo '<div class="wrap"><h2>Choose Demo Content</h2>';
         echo '<form id="choose-demo-content-form">';
        // echo '<label>Select Demo Content:</label><br>';
        foreach ($files as $file) {
            // Exclude current directory (.) and parent directory (..)
            if ($file != '.' && $file != '..') {
                echo '<label>';
                echo '<input type="radio" name="demo_content" value="' . $file . '"> ' . $file;
                // $themes_directory = plugin_dir_path(__FILE__) . 'listed_themes';
                // $thumbnail_url = 
                
                    echo '<img src="https://picsum.photos/seed/picsum/200/300" alt="Theme Thumbnail">';
                echo '</label><br>';
            }
        }
       
        echo '</form>';
        echo'<div class="btn_bn">';
         //echo '<input type="button" id="back-step-btn-3" value="Back">';
        echo '<input type="button" id="next-step-btn-3" value="Next">';
        //echo '<a href="' . esc_url($importer_url) . '" class="button">Go to WordPress Importer</a>';

        echo'</div>';
        echo'</div>';
    } else {
        echo '<p>Demo content folder not found.</p>';
    }
    
    echo '</div>';
}



add_action('wp_ajax_choose_demo_content', 'choose_demo_content_callback');
function choose_demo_content_callback() {
    
    if (isset($_POST['selected_demo_content'])) {
        $selected_demo_content = sanitize_text_field($_POST['selected_demo_content']);
        
        // Store selected demo content data in the database
        update_option('selected_demo_content', $selected_demo_content);

        // Return a success message (or any other data) back to the JavaScript
       // echo 'Demo content selected: ' . $selected_demo_content;
        
        theme_wizard_step_select_plugins();
    } else {
       theme_wizard_step_select_plugins();
    }
    wp_die(); // Always use wp_die() to end AJAX requests
}

// Step 4: Select Required Plugins
function theme_wizard_step_select_plugins() {
    echo '<div class="wrap">';
   
    // Path to the plugins directory
    $plugins_directory = plugin_dir_path(__FILE__) . 'required_plugins/';

    if (is_dir($plugins_directory)) {
        // Get list of plugin files in the directory
        $plugin_files = scandir($plugins_directory);

        // Display each plugin file
          echo '<div class="bg__2">';
           echo'<h2>Select Required Plugins</h2>';
            echo '<div class="bg__center_rp">';
            echo'<h3>Required Plugins</h3>';
        echo '<form id="select-plugins-form">';
        foreach ($plugin_files as $file) {
            // Exclude current directory (.) and parent directory (..)
            if ($file != '.' && $file != '..') {
                echo '<label>';
                echo '<input type="checkbox" name="selected_plugin[]" value="' . $file . '" checked> ' . $file;
                echo '</label><br>';

            }
        }
         echo '<div class="btn_bn_rp">';
        //echo '<input type="button" id="back-step-btn-4" value="Back">';
        echo '<input type="button" id="next-step-btn-4" value="Next">';
         echo '</div>';
        echo '</div>';
         
        echo '</form>';
         echo '</div>';
    } else {
        echo '<p>Required plugins directory not found.</p>';
    }

    echo '</div>';
}


add_action('wp_ajax_choose_required_plugin', 'choose_required_plugin_callback');
function choose_required_plugin_callback() {
    if (isset($_POST['selected_plugin']) && is_array($_POST['selected_plugin'])) {
        $selected_plugins = array_map('sanitize_text_field', $_POST['selected_plugin']);
        
        update_option('selected_plugins', $selected_plugins);

        theme_wizard_step_review_finish();
    } else {
        echo 'Error: No required plugins selected.';
    }
    wp_die(); 
}




// Step 5: Review and Finish
function theme_wizard_step_review_finish() {
    echo '<div class="wrap_5">';
    
    echo "<div id='popup-overlay' style='display: none;'>
        <div id='popup-modal'>
        <div class='btn_bn_pp_2'>
        <button id='close-modal'>X</button>
        </div>
            <div id='popup-content'></div>
            <div class='btn_bn_pp'>
             </div>
           
        </div>
    </div>
";
    echo '<div class="bg__2">
    <h2>Review and Finish</h2>';
    echo '<div id="loading-spinner" style="display: none;">
    <div class="spinner2">
         
    </div>
</div>';
    
   
    // Retrieve selected data from the WordPress options table
    $selected_theme = get_option('selected_theme');
    $custom_title = get_option('custom_title');
    $custom_logo = get_option('uploaded_logo_id');
    $custom_header_menu = get_option('custom_header_menu');
    $custom_footer_menu = get_option('custom_footer_menu');
    $selected_demo_content = get_option('selected_demo_content');
    $selected_plugins = get_option('selected_plugins'); // Retrieve array of selected plugins
    

    // Display summary of selections
    echo '<div class="sr_mian_review">';
    echo '<div class="mian_review">';
    
    echo '<div class="sel_theme">';
    echo '<h3>Selected Theme:</h3>';
    echo '<p>' . $selected_theme . '</p>';
    echo '</div>';
  
    echo '<div class="cus_theme">';
    echo '<h3>Customized Theme:</h3>';
    echo '<p>Title: ' . $custom_title . '</p>';
    echo '<p>Logo: ';
        if ($custom_logo) {
            $custom_logo_url = wp_get_attachment_url($custom_logo);
           echo '<img src="' . $custom_logo_url . '" alt="Custom Logo" width="50" height="50">';
    
        } else {
            echo 'No logo uploaded';
        }
    echo '</p>';    echo '<p>Header Menu: ' . ($custom_header_menu ? 'Included' : 'Not Included') . '</p>';
    echo '<p>Footer Menu: ' . ($custom_footer_menu ? 'Included' : 'Not Included') . '</p>';
     echo '</div>';

    echo '<div class="demo__">';
    echo '<h3>Selected Demo Content:</h3>';
    echo '<p>' . $selected_demo_content . '</p>';
    echo '</div>';

   echo '<div class="req_plugin__">';
    echo '<h3>Selected Required Plugins:</h3>';
    if (!empty($selected_plugins)) {
        foreach ($selected_plugins as $plugin) {
            echo '<p>' . $plugin . '</p>'; // Display each selected plugin
        }
    } else {
        echo '<p>No plugins selected</p>';
    }
      echo '</div>';
      echo '</div>';
echo '<form method="post" action="">';
    echo '<input type="hidden" name="selected_theme" value="' . $selected_theme . '">';
    echo '<input type="hidden" id="title" name="custom_title" value="' . $custom_title . '">';
    echo '<input type="hidden" id="logo" name="custom_logo" value="' . $custom_logo . '">';
    echo '<input type="hidden" id="custom_header_menu" name="custom_header_menu" value="' . $custom_header_menu . '">'; 
    echo '<input type="hidden" id="custom_footer_menu" name="custom_footer_menu" value="' . $custom_footer_menu . '">';
    echo '<input type="hidden" id="selected_demo_content" name="selected_demo_content" value="' . $selected_demo_content . '">';
    
    $selected_plugins_json = json_encode($selected_plugins);
    echo '<input type="hidden" id="selected_plugins" name="selected_plugins" data-plugins="' . htmlspecialchars($selected_plugins_json) . '">';

    echo '<input type="submit" name="finish_btn" id="finish-btn" class="button button-primary" value="Finish">';
    echo '</form>';
    
    
    
    echo '</div>';

    if (isset($_POST['finish_btn'])) {
        switch_theme($selected_theme);

       
    }        echo '</div>';
    // Finish button
   
     
    echo '</div>';
     echo '</div>';
}

add_action('wp_ajax_install_selected_theme', 'install_selected_theme_callback');

function install_selected_theme_callback() {
    //print_r($_POST);
    $response = array(); // Initialize an empty array to store messages
    
    if (isset($_POST['selected_theme'], $_POST['site_title'], $_POST['site_logo'], $_POST['header_menu'], $_POST['footer_menu'], $_POST['demo_content'])) {
        $selectedTheme = sanitize_text_field($_POST['selected_theme']);
        
        // Call each function and store its message in the response array
        $response[] = import_demo_content($_POST['demo_content']);
        $response[] = install_and_activate_theme($selectedTheme);
        $response[] = update_site_title($_POST['site_title']);
        $response[] = update_site_logo($_POST['site_logo']);
        // $response[] = create_or_update_header_menu($_POST['header_menu']);
        // $response[] = create_or_update_footer_menu($_POST['footer_menu']);

        $plugin_dir = ABSPATH . 'wp-content/plugins/';
        $selectedPlugins = $_POST['selected_plugins'];
        $installed_plugins = array();

        if (!empty($selectedPlugins) && is_array($selectedPlugins)) {
            foreach ($selectedPlugins as $plugin_file) {
                $installation_result = install_plugin_from_zip($plugin_file);

                if (is_wp_error($installation_result)) {
                    if ($installation_result->get_error_code() === 'plugin_already_installed') {
                        $response[] = 'Plugin already installed: ' . $plugin_file;
                    } else {
                        $response[] = 'Error installing ' . $plugin_file . ': ' . $installation_result->get_error_message();
                    }
                } else {
                    $installed_plugins[] = $plugin_file;
                }
            }
        }

        foreach ($installed_plugins as $plugin_file) {
            $activation_result = activate_installed_plugin($plugin_file);

            if (is_wp_error($activation_result)) {
                $response[] = 'Error activating ' . $plugin_file . ': ' . $activation_result->get_error_message();
            } else {
                $response[] = 'Plugin activated successfully: ' . $plugin_file;
            }
        }
    } else {
        $response[] = 'Error: Insufficient data provided.';
    }
    
    // Send the response array as JSON
    echo json_encode($response);
   // print_r($response);
    wp_die();
}


function install_and_activate_theme($selectedTheme) {
 
    $themeDirectory = WP_CONTENT_DIR . '/themes/' . $selectedTheme;
    if (!is_dir($themeDirectory)) {
        $pluginThemeDirectory = plugin_dir_path(__FILE__) . 'listed_themes/' . $selectedTheme;
        if (is_dir($pluginThemeDirectory)) {
            if (!copy_directory($pluginThemeDirectory, $themeDirectory)) {
                return 'Error: Failed to copy theme files.';
            }
        } else {
            return 'Error: Theme folder not found in the plugin directory.';
        }
    }
    switch_theme($selectedTheme);
    return 'Theme changed successfully';
}

function update_site_title($siteTitle) {
    if (empty($siteTitle)) {
        $currentSiteTitle = get_option('blogname');
        
        return 'Site title remains unchanged: ' . $currentSiteTitle;
    } else {
        update_option('blogname', sanitize_text_field($siteTitle));
        return 'Site title updated';
    }
}




function update_site_logo($siteLogoId) {
    if (isset($siteLogoId)) {
        $uploadedLogoId = intval($siteLogoId);

        $file_path = get_attached_file($uploadedLogoId);

        if ($file_path) {
            // Check the current image format
            $image_info = getimagesize($file_path);
            $image_mime_type = $image_info['mime'];
            $is_jpeg = $image_mime_type === 'image/jpeg';

            // Convert JPEG to PNG if necessary
            if ($is_jpeg) {
                $png_file_path = wp_tempnam($file_path, '.png');
                $image = imagecreatefromjpeg($file_path);
                imagepng($image, $png_file_path);
                imagedestroy($image);
                $file_path = $png_file_path;
            }

            // Set the desired image sizes
            $image_sizes = array(
                'width' => 512,
                'height' => 512,
                'crop' => true,
            );

            // Get the image editor
            $resized_image = wp_get_image_editor($file_path);

            if (!is_wp_error($resized_image)) {
                // Resize the image
                $resized_image->resize($image_sizes['width'], $image_sizes['height'], $image_sizes['crop']);

                // Save the resized image
                $saved = $resized_image->save($file_path);

                if (is_wp_error($saved)) {
                    echo 'Error: Failed to save the resized logo image.';
                } else {
                    // Update the site icon option with the uploaded photo ID
                    update_option('site_icon', $uploadedLogoId);
                     return 'Logo updated successfully';
                }
            } else {
                return 'Error: Failed to resize the logo image.';
            }

            // Delete the temporary PNG file if created
            if ($is_jpeg && isset($png_file_path) && file_exists($png_file_path)) {
                unlink($png_file_path);
            }
        } else {
            return ' Logo file not found.';
        }
    } else {
        return ' No logo ID provided.';
    }
}




function create_or_update_header_menu($headerMenu) {
    // Check if $headerMenu is '0', meaning no menu is needed
    if ($headerMenu === '0') {
        // Define the filter callback function to remove menu items
        function remove_header_menu_items($items, $args) {
            // Check if the current theme's header navigation menu is being outputted
            if (isset($args->theme_location) && $args->theme_location === 'header-menu') {
                // Empty the menu items array to remove all items
                $items = array();
            }
            return $items;
        }

        // Hook the filter callback function to the wp_nav_menu_objects filter
        add_filter('wp_nav_menu_objects', 'remove_header_menu_items', 10, 2);
    }
}




function remove_theme_nav_menu_items($items, $args) {
    // Check if the current theme's navigation menu is being outputted
    if (isset($args->theme_location) && $args->theme_location === 'primary') {
        // Empty the menu items array to remove all items
        $items = array();
    }
    return $items;
}

add_filter('wp_nav_menu_objects', 'remove_theme_nav_menu_items', 10, 2);





function create_or_update_footer_menu($footerMenu) {
    if ($footerMenu === '0') {
        unregister_nav_menu('footer'); // Replace 'footer' with the menu location you want to deregister
    }
}




function copy_directory($source, $destination) {
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    if ($dir = opendir($source)) {
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                if (is_dir($source . '/' . $file)) {
                    copy_directory($source . '/' . $file, $destination . '/' . $file);
                } else {
                    copy($source . '/' . $file, $destination . '/' . $file);
                }
            }
        }
        closedir($dir);
        return true;
    } else {
        return false;
    }
}


function import_demo_content($file_name) {
    // Define the log file path
    $log_file_path = plugin_dir_path(__FILE__) . 'import_log.txt';

    // Helper function to log messages
    function log_message($message, $log_file_path) {
        // Open the log file in append mode
        $log_file = fopen($log_file_path, 'a');
        // Write the message to the log file with a timestamp
        fwrite($log_file, date('Y-m-d H:i:s') . " - " . $message . "\n");
        // Close the log file
        fclose($log_file);
    }

    // Check if the WordPress Importer plugin is installed and activated
    if (!class_exists('WP_Importer') || !class_exists('WP_Import')) {
        // Attempt to activate the WordPress Importer plugin
        $activation_result = activate_installed_plugin('wordpress-importer');
        
        // Check if activation was successful
        if (is_wp_error($activation_result)) {
            log_message('Error: Failed to activate WordPress Importer plugin.', $log_file_path);
            return 'Error: Failed to activate WordPress Importer plugin.';
        }
    }

    // Check if the XML file exists
    $file_path = plugin_dir_path(__FILE__) . 'demo_content/' . $file_name;
    if (!file_exists($file_path)) {
        log_message('Error: Demo content XML file not found.', $log_file_path);
        return 'Error: Demo content XML file not found.';
    }

    // Load WordPress Importer
    require_once ABSPATH . 'wp-admin/includes/import.php';

    // Check if the WP_Import class exists, if not, include it from the WordPress Importer plugin directory
    if (!class_exists('WP_Import')) {
        require_once ABSPATH . 'wp-content/plugins/wordpress-importer/class-wp-import.php';
        require_once ABSPATH . 'wp-content/plugins/wordpress-importer/parsers/class-wxr-parser.php';
        require_once ABSPATH . 'wp-content/plugins/wordpress-importer/parsers/class-wxr-parser-xml.php';
        require_once ABSPATH . 'wp-content/plugins/wordpress-importer/parsers/class-wxr-parser-regex.php';
        require_once ABSPATH . 'wp-content/plugins/wordpress-importer/parsers/class-wxr-parser-simplexml.php';
    }

    // Create new instance of WP_Import class
    $wp_import = new WP_Import();

    // Set up options for importing, including file attachments
    $options = array(
        'fetch_attachments' => true
    );

    // Increase PHP execution time and memory limit
    @ini_set('max_execution_time', '300');
    @ini_set('memory_limit', '256M');

    // Perform the import with the options
    ob_start(); // Start output buffering to capture import messages
    $wp_import->fetch_attachments = true; // Ensure fetching attachments is enabled
    $import_result = $wp_import->import($file_path, $options);
    $import_output = ob_get_clean(); // Get the import messages

    // Log import output for debugging
    log_message('Import Output: ' . $import_output, $log_file_path);

    // Check if the import result is a WP_Error object
    if (is_wp_error($import_result)) {
        $error_message = $import_result->get_error_message();
        log_message('Error: ' . $error_message, $log_file_path);
        return 'Error: ' . $error_message;
    } else {
        log_message('Demo content imported successfully.', $log_file_path);
        return 'Demo content imported successfully.';
    }
}








// Function to install the plugin from the ZIP file
function install_plugin_from_zip($plugin_zip_file) {
    global $plugin_dir;

    // Get the plugin directory name
    $plugin_dir_name = pathinfo($plugin_zip_file, PATHINFO_FILENAME);

    // Check if the plugin is already installed
if (is_plugin_active($plugin_dir_name . '/' . $plugin_dir_name . '.php')) {
        return new WP_Error('plugin_already_installed', 'Plugin already installed: ' . $plugin_dir_name);
    }

    // Define the path to the plugin ZIP file
    $plugin_path = $plugin_dir . 'required_plugins/' . $plugin_zip_file;

    // Check if the plugin ZIP file exists
    if (!file_exists($plugin_path)) {
        return new WP_Error('plugin_not_found', 'Plugin file not found: ' . $plugin_zip_file);
    }

    // Unzip the plugin file
    $unzip_result = unzip_plugin($plugin_path, WP_PLUGIN_DIR);

    if (is_wp_error($unzip_result)) {
        return $unzip_result;
    }

    return true; // Plugin installed successfully
}

// Function to activate the installed plugin
function activate_installed_plugin($plugin_dir_name) {
    // Remove the .zip extension from the plugin directory name
    $plugin_dir_name = basename($plugin_dir_name, '.zip');

    // Define the correct plugin directory name if there's a mapping
    $plugin_dir_mapping = array(
        'wp-contact-form-7' => 'contact-form-7'
        // Add more mappings if needed
    );

    // Check if there's a mapping for the plugin directory name
    if (isset($plugin_dir_mapping[$plugin_dir_name])) {
        $plugin_dir_name = $plugin_dir_mapping[$plugin_dir_name];
    }

    // Get the path to the plugin directory
    $plugin_directory = WP_PLUGIN_DIR . '/' . $plugin_dir_name;

    // Check if the plugin directory exists
    if (!is_dir($plugin_directory)) {
        return new WP_Error('plugin_directory_not_found', 'Plugin directory not found: ' . $plugin_directory);
    }

    // Get all PHP files in the plugin directory
    $php_files = glob($plugin_directory . '/*.php');

    // Check if any PHP files are found
    if (empty($php_files)) {
        return new WP_Error('plugin_main_file_not_found', 'No PHP files found in the plugin directory: ' . $plugin_directory);
    }

    // Search for the PHP file containing the specified header comments
    $plugin_file = '';
    foreach ($php_files as $php_file) {
        $file_contents = file_get_contents($php_file);
        if (strpos($file_contents, 'Plugin Name:') !== false && strpos($file_contents, 'Version:') !== false) {
            $plugin_file = $php_file;
            break;
        }
    }

    // If no PHP file with the specified header comments is found, use the first PHP file
    if (empty($plugin_file)) {
        $plugin_file = $php_files[0];
    }

    // Activate the plugin
    $activation_result = activate_plugin(plugin_basename($plugin_file));

    if (is_wp_error($activation_result)) {
        return $activation_result;
    }

    return true; 
}







// Function to unzip the plugin
function unzip_plugin($plugin_zip_path, $destination) {
    $zip = new ZipArchive;

    if ($zip->open($plugin_zip_path) === true) {
        $zip->extractTo($destination);
        $zip->close();
        return true; // Plugin unzipped successfully
    } else {
        return new WP_Error('unzip_error', 'Failed to unzip the plugin zip file.');
    }
}





add_action('theme_wizard_step_select_theme', 'theme_wizard_step_select_theme');
add_action('theme_wizard_step_customize_theme', 'theme_wizard_step_customize_theme');
add_action('theme_wizard_step_choose_demo_content', 'theme_wizard_step_choose_demo_content');
add_action('theme_wizard_step_select_plugins', 'theme_wizard_step_select_plugins');
add_action('theme_wizard_step_review_finish', 'theme_wizard_step_review_finish');
