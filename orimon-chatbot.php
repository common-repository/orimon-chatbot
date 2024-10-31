<?php
/*
Plugin Name: Orimon Chatbot
Plugin URI: https://orimon.ai
Description: This plugin allows you to easily add the Orimon chatbot to your WordPress site. This will enable a ChatGPT like chatbot for your own business. 
Version: 1.0
Author: Orilabs
License: GPL2
*/

function orimon_chatbot_menu() {
    add_options_page(
        'Orimon Chatbot',
        'Orimon Chatbot',
        'manage_options',
        'orimon-chatbot',
        'orimon_chatbot_options_page'
    );
}
add_action('admin_menu', 'orimon_chatbot_menu');

function orimon_chatbot_options_page() {
?>
    <div class="wrap">
        <h1>Orimon Chatbot</h1>
        <img src="<?php echo plugins_url('assets/orimon-logo.png', __FILE__); ?>" alt="Orimon Logo" style="width: 200px;">
        <p>To get your Orimon <b>Chabot ID</b>, please visit <a href="https://orimon.ai/" target="_blank">orimon.ai</a> and follow the instructions provided on the platform.</p>
        <form method="post" action="options.php">
            <?php
            settings_fields('orimon_chatbot_options');
            do_settings_sections('orimon-chatbot');
            submit_button('Save Changes');
            ?>
        </form>
    </div>
    <?php
}


function orimon_chatbot_settings_init() {
    register_setting('orimon_chatbot_options', 'orimon_chatbot_options', array(
        'sanitize_callback' => 'orimon_chatbot_sanitize_options',
        'default' => array(
            'orimon_chatbot_js' => '',
            'orimon_chatbot_enabled' => 0,
            'orimon_chatbot_excluded_pages' => ''
        )
    ));

    add_settings_section(
        'orimon_chatbot_section_main',
        'Main Settings',
        'orimon_chatbot_section_main_callback',
        'orimon-chatbot'
    );

    add_settings_field(
        'orimon_chatbot_js',
        'Orimon Chatbot ID',
        'orimon_chatbot_js_callback',
        'orimon-chatbot',
        'orimon_chatbot_section_main'
    );

     add_settings_field(
        'orimon_chatbot_enabled',
        'Enable chatbot',
        'orimon_chatbot_enabled_callback',
        'orimon-chatbot',
        'orimon_chatbot_section_main'
    );

    // Add the "Excluded Pages" textarea field
    // add_settings_field(
    //     'orimon_chatbot_excluded_pages',
    //     'Excluded Pages',
    //     'orimon_chatbot_excluded_pages_callback',
    //     'orimon-chatbot',
    //     'orimon_chatbot_section_main'
    // );
}
add_action('admin_init', 'orimon_chatbot_settings_init');

function orimon_chatbot_section_main_callback() {
    echo '<p>Enter your Orimon Chabot ID below:</p>';
}

function orimon_chatbot_js_callback() {
    $options = wp_parse_args(get_option('orimon_chatbot_options'), array(
        'orimon_chatbot_js' => '',
        'orimon_chatbot_enabled' => 0,
        'orimon_chatbot_excluded_pages' => ''
    ));

    echo '<input type="text" name="orimon_chatbot_options[orimon_chatbot_js]" value="' . esc_attr($options['orimon_chatbot_js']) . '" size="40">';
    echo '<p class="description">Enter your Orimon chatbot Chatbot ID.</p>';
}



function orimon_chatbot_enqueue_scripts() {
    $options = wp_parse_args(get_option('orimon_chatbot_options'), array(
        'orimon_chatbot_js' => '',
        'orimon_chatbot_enabled' => 0,
        'orimon_chatbot_excluded_pages' => ''
    ));

    if (!empty($options['orimon_chatbot_js']) && !empty($options['orimon_chatbot_enabled'])) {

    $excluded_pages = explode(",", $options['orimon_chatbot_excluded_pages']);
    $current_url = add_query_arg(null, null);

    error_log('Excluded pages: ' . print_r($excluded_pages, true));
    error_log('Current URL: ' . $current_url);

    $exclude = false;
    foreach ($excluded_pages as $page_url) {
        $page_url = trim($page_url);
        if (strpos($page_url, 'http') !== 0) {
            $page_url = home_url($page_url);
        }
        if ($page_url == $current_url || strpos($current_url, $page_url) === 0) {
            $exclude = true;
            break;
        }
        if (strpos($current_url, $page_url) !== false) {
            $exclude = true;
            break;
        }
    }

        if (!$exclude) {
            add_action('wp_footer', function() use ($options) {
                echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    var script = document.createElement("script");
                    script.src = "https://bot.orimon.ai/deploy/index.js";
                    script.setAttribute("tenantId", "' . esc_attr($options['orimon_chatbot_js']) . '");
                    document.body.appendChild(script);
                });
                </script>';
            }, 100);
        } 
    }
}

add_action('wp_enqueue_scripts', 'orimon_chatbot_enqueue_scripts');

function orimon_chatbot_enabled_callback() {
    $options = wp_parse_args(get_option('orimon_chatbot_options'), array(
        'orimon_chatbot_js' => '',
        'orimon_chatbot_enabled' => 0,
        'orimon_chatbot_excluded_pages' => ''
    ));
    
    echo '<input type="checkbox" name="orimon_chatbot_options[orimon_chatbot_enabled]" value="1" ' . checked(1, $options['orimon_chatbot_enabled'], false) . '>';
}

function orimon_chatbot_excluded_pages_callback() {
    $options = wp_parse_args(get_option('orimon_chatbot_options'), array(
        'orimon_chatbot_js' => '',
        'orimon_chatbot_enabled' => 0,
        'orimon_chatbot_excluded_pages' => ''
    ));
    
    echo '<textarea name="orimon_chatbot_options[orimon_chatbot_excluded_pages]" rows="10" cols="50">' . esc_textarea($options['orimon_chatbot_excluded_pages']) . '</textarea>';
    echo '<p class="description">Enter one URL per line to exclude the chatbot from specific pages.</p>';
}

function orimon_chatbot_sanitize_options($input) {
    $output = array();

    if (isset($input['orimon_chatbot_js'])) {
        $output['orimon_chatbot_js'] = sanitize_textarea_field($input['orimon_chatbot_js']);
    }

    if (isset($input['orimon_chatbot_enabled'])) {
        $output['orimon_chatbot_enabled'] = $input['orimon_chatbot_enabled'] ? 1 : 0;
    }

    if (isset($input['orimon_chatbot_excluded_pages'])) {
        $output['orimon_chatbot_excluded_pages'] = sanitize_textarea_field($input['orimon_chatbot_excluded_pages']);
    }

    return $output;
}


?>
