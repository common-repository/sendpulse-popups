<?php
/**
 * Plugin Name: SendPulse Popups
 * Description: Add SendPulse Pop-ups integration for WordPress as easy as can be.
 * Version: 1.1
 * Author: SendPulse
 * Author URI: https://sendpulse.com
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sendpulse-popups
 * Domain Path: languages
 */

defined('ABSPATH') || exit;
define('SENDPULSE_POPUPS_PLUGIN_VERSION', '1.1');
define('SENDPULSE_POPUPS_PLUGIN_TEXTDOMAIN', 'sendpulse-popups');
define('SENDPULSE_POPUPS_PLUGIN_BASE_NAME', plugin_basename(__FILE__));
define('SENDPULSE_POPUPS_PLUGIN_BASE_DIR', plugin_dir_path(__FILE__));

function sendpulse_popups_load_textdomain() {
    load_plugin_textdomain(SENDPULSE_POPUPS_PLUGIN_TEXTDOMAIN, false, dirname(SENDPULSE_POPUPS_PLUGIN_BASE_NAME) . '/languages/');
}

add_action('plugins_loaded', 'sendpulse_popups_load_textdomain');

/**
 * Add a menu item in the Settings menu
 */
function sendpulse_popups_menu() {
    add_options_page(
        'SendPulse Pop-ups Settings',
        __('SendPulse Pop-ups', 'sendpulse-popups'),
        'manage_options',
        'sendpulse-popups-settings',
        'sendpulse_popups_settings_page'
    );
}

add_action('admin_menu', 'sendpulse_popups_menu');

/**
 * Create the settings page
 */
function sendpulse_popups_settings_page() {
    ?>
    <div class="wrap">
        <h2><?php esc_html_e('SendPulse Pop-ups Settings', 'sendpulse-popups');?></h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('sendpulse_popups_options');
            do_settings_sections('sendpulse-popups-settings');
            submit_button();
            ?>
        </form>

        <?php
        // Check if SendPulse Pop-ups inline JavaScript code is blank
        $inline_js = get_option('sendpulse_popups_inline_js');
        if (empty($inline_js)) {
            ?>
            <div class="notice notice-info" style="display: flex; align-items: center; justify-content: space-between;">
                <div style="flex: 1;">
                    <h3><?php esc_html_e('Connect your Pop-ups created with SendPulse', 'sendpulse-popups');?></h3>
                    <p>
                        <?php
                        printf(
                        /* translators: %s: URL */
                            wp_kses(
                                sprintf(
                                    'Add a new Pop-ups created with SendPulse to your site or <a href="%1$s" target="_blank">connect the existing one</a>. If you don\'t have a SendPulse account, <a href="%2$s" target="_blank">sign up for free</a> to start using the plugin.',
                                    esc_url('https://login.sendpulse.com/pop-ups/main/'),
                                    esc_url('https://sendpulse.com/register')
                                ),
                                array(
                                    'a' => array(
                                        'href'  => array(),
                                        'target' => array(),
                                    ),
                                )
                            ),
                            'sendpulse-popups'
                        );
                        ?>
                    </p>
                </div>

                <?php
                printf(
                /* translators: %s: URL */
                    wp_kses(
                        sprintf(
                            '<a class="button button-primary" href="%1$s" target="_blank">%2$s</a>',
                            esc_url('https://login.sendpulse.com/pop-ups/main/'),
                            esc_html('Connect Pop-ups','sendpulse-popups')
                        ),
                        array(
                            'a' => array(
                                'class' => array(),
                                'href'  => array(),
                                'target' => array(),
                            ),
                        )
                    ),
                    'sendpulse-popups'
                );
                ?>

            </div>
            <?php
        }
        ?>
    </div>
    <?php
}

/**
 * Register settings and fields
 */
function sendpulse_popups_register_settings() {
    register_setting('sendpulse_popups_options', 'sendpulse_popups_inline_js', 'sendpulse_popups_sanitize_textarea_field');

    add_settings_section(
        'sendpulse_popups_js_section',
        __('SendPulse Pop-ups', 'sendpulse-popups'),
        'sendpulse_popups_js_section_callback',
        'sendpulse-popups-settings',
        array(
            'after_section' => sprintf(__('For more information about Pop-ups, check the <a href="%s" target="_blank">SendPulse Knowledge Base</a>.', 'sendpulse-popups'),
                'https://sendpulse.com/knowledge-base/pop-ups'
            ),
        )
    );

    add_settings_field(
        'sendpulse_popups_inline_js',
        __('Paste your Pop-ups code here', 'sendpulse-popups'),
        'sendpulse_popups_inline_js_callback',
        'sendpulse-popups-settings',
        'sendpulse_popups_js_section'
    );
}

add_action('admin_init', 'sendpulse_popups_register_settings');

function sendpulse_popups_js_section_callback() {
    echo '<div>' . esc_html__('To connect your Pop-ups, copy its installation code from your SendPulse account. Once pasted and saved, it will be added before the closing </body> tag.', 'sendpulse-popups') . '</div>';
    echo '<div>' . esc_html__('After successful installation, your Pop-up will appear on your website. To customize your widget appearance, go to your SendPulse account.', 'sendpulse-popups') . '</div>';
}

/**
 * Callback for inline JavaScript code field
 */
function sendpulse_popups_inline_js_callback() {
    $inline_js = get_option('sendpulse_popups_inline_js');
    if(!empty($inline_js)) {
        $decoded_js = html_entity_decode($inline_js);
        echo '<textarea name="sendpulse_popups_inline_js" rows="5" cols="50">' . esc_textarea($decoded_js) . '</textarea>';
    } else {
        echo '<textarea name="sendpulse_popups_inline_js" rows="5" cols="50">' . esc_textarea($inline_js) . '</textarea>';
    }

}

/**
 * Sanitization callback for inline JavaScript code
 */
function sendpulse_popups_sanitize_textarea_field($input) {
    return esc_js($input);
}

function sendpulse_popups_inline_script() {
    $inline_js = get_option('sendpulse_popups_inline_js');
    if (!empty($inline_js)) {
        $decoded_js = html_entity_decode($inline_js);

        // Define allowed HTML tags and attributes for the script content
        $allowed_tags = array(
            'script' => array(
                'src' => array(),
                'data-chats-widget-id' => array(),
                'async' => array(),
            ),
        );

        // Sanitize the script content
        $sanitized_script = wp_kses($decoded_js, $allowed_tags);

        // Return the sanitized script tag
        return $sanitized_script;
    }
    return ''; // Return an empty string if no script is available
}

function sendpulse_popups_output_inline_script() {
    add_action('wp_footer', 'sendpulse_popups_output_inline_script_callback');
}

function sendpulse_popups_output_inline_script_callback() {
    echo sendpulse_popups_inline_script();
}

add_action('init', 'sendpulse_popups_output_inline_script');