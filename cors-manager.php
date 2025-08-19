<?php
/**
 * Plugin Name: CORS Manager
 * Plugin URI: https://wordpress.org/plugins/cors-manager
 * Description: A WordPress plugin to manage CORS (Cross-Origin Resource Sharing) settings and allowed origins.
 * Version: 1.0.0
 * Author: Navid Mirzaaghazadeh
 * License: GPL v2 or later
 * Text Domain: cors-manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CORS_MANAGER_VERSION', '1.0.0');
define('CORS_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CORS_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));

class CORSManager {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('wp_loaded', array($this, 'handle_cors'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    public function init() {
        // Initialize plugin
        // Note: load_plugin_textdomain() is no longer needed for WordPress.org hosted plugins
        // WordPress automatically loads translations for plugins hosted on WordPress.org
    }
    
    public function add_admin_menu() {
        add_management_page(
            __('CORS Manager', 'cors-manager'),
            __('CORS Manager', 'cors-manager'),
            'manage_options',
            'cors-manager',
            array($this, 'admin_page')
        );
    }
    
    public function admin_init() {
        register_setting('cors_manager_settings', 'cors_manager_options', array(
            'sanitize_callback' => array($this, 'sanitize_options')
        ));
        
        add_settings_section(
            'cors_manager_main_section',
            __('CORS Settings', 'cors-manager'),
            array($this, 'settings_section_callback'),
            'cors-manager'
        );
        
        add_settings_field(
            'cors_enabled',
            __('Enable CORS', 'cors-manager'),
            array($this, 'cors_enabled_callback'),
            'cors-manager',
            'cors_manager_main_section'
        );
        
        add_settings_field(
            'allowed_origins',
            __('Allowed Origins', 'cors-manager'),
            array($this, 'allowed_origins_callback'),
            'cors-manager',
            'cors_manager_main_section'
        );
        
        add_settings_field(
            'allowed_methods',
            __('Allowed Methods', 'cors-manager'),
            array($this, 'allowed_methods_callback'),
            'cors-manager',
            'cors_manager_main_section'
        );
        
        add_settings_field(
            'allowed_headers',
            __('Allowed Headers', 'cors-manager'),
            array($this, 'allowed_headers_callback'),
            'cors-manager',
            'cors_manager_main_section'
        );
        
        add_settings_field(
            'allow_credentials',
            __('Allow Credentials', 'cors-manager'),
            array($this, 'allow_credentials_callback'),
            'cors-manager',
            'cors_manager_main_section'
        );
    }
    
    public function settings_section_callback() {
        echo '<p>' . esc_html__('Configure CORS settings for your WordPress site.', 'cors-manager') . '</p>';
    }
    
    public function cors_enabled_callback() {
        $options = get_option('cors_manager_options');
        $enabled = isset($options['cors_enabled']) ? $options['cors_enabled'] : 0;
        echo '<input type="checkbox" name="cors_manager_options[cors_enabled]" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="cors_manager_options[cors_enabled]">' . esc_html__('Enable CORS headers', 'cors-manager') . '</label>';
    }
    
    public function allowed_origins_callback() {
        $options = get_option('cors_manager_options');
        $origins = isset($options['allowed_origins']) ? $options['allowed_origins'] : '';
        echo '<textarea name="cors_manager_options[allowed_origins]" rows="5" cols="50" class="large-text">' . esc_textarea($origins) . '</textarea>';
        echo '<p class="description">' . esc_html__('Enter allowed origins, one per line. Use * for all origins (not recommended for production).', 'cors-manager') . '</p>';
        echo '<p class="description">' . esc_html__('Example: https://example.com', 'cors-manager') . '</p>';
    }
    
    public function allowed_methods_callback() {
        $options = get_option('cors_manager_options');
        $methods = isset($options['allowed_methods']) ? $options['allowed_methods'] : array('GET', 'POST');
        $all_methods = array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH', 'HEAD');
        
        foreach ($all_methods as $method) {
            $checked = in_array($method, $methods) ? 'checked' : '';
            echo '<label><input type="checkbox" name="cors_manager_options[allowed_methods][]" value="' . esc_attr($method) . '" ' . esc_attr($checked) . ' /> ' . esc_html($method) . '</label><br>';
        }
    }
    
    public function allowed_headers_callback() {
        $options = get_option('cors_manager_options');
        $headers = isset($options['allowed_headers']) ? $options['allowed_headers'] : 'Content-Type, Authorization, X-Requested-With';
        echo '<input type="text" name="cors_manager_options[allowed_headers]" value="' . esc_attr($headers) . '" class="large-text" />';
        echo '<p class="description">' . esc_html__('Comma-separated list of allowed headers.', 'cors-manager') . '</p>';
    }
    
    public function allow_credentials_callback() {
        $options = get_option('cors_manager_options');
        $credentials = isset($options['allow_credentials']) ? $options['allow_credentials'] : 0;
        echo '<input type="checkbox" name="cors_manager_options[allow_credentials]" value="1" ' . checked(1, $credentials, false) . ' />';
        echo '<label for="cors_manager_options[allow_credentials]">' . esc_html__('Allow credentials (cookies, authorization headers)', 'cors-manager') . '</label>';
    }
    
    public function sanitize_options($input) {
        $sanitized = array();
        
        // Sanitize CORS enabled checkbox
        $sanitized['cors_enabled'] = isset($input['cors_enabled']) ? 1 : 0;
        
        // Sanitize allowed origins
        if (isset($input['allowed_origins'])) {
            $origins = sanitize_textarea_field($input['allowed_origins']);
            // Additional validation for origins
            $origins_array = array_map('trim', explode("\n", $origins));
            $valid_origins = array();
            foreach ($origins_array as $origin) {
                if (!empty($origin)) {
                    // Allow wildcard or validate URL format
                    if ($origin === '*' || filter_var($origin, FILTER_VALIDATE_URL) || preg_match('/^https?:\/\/[a-zA-Z0-9.-]+/', $origin)) {
                        $valid_origins[] = $origin;
                    }
                }
            }
            $sanitized['allowed_origins'] = implode("\n", $valid_origins);
        } else {
            $sanitized['allowed_origins'] = '';
        }
        
        // Sanitize allowed methods
        if (isset($input['allowed_methods']) && is_array($input['allowed_methods'])) {
            $valid_methods = array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH', 'HEAD');
            $sanitized['allowed_methods'] = array_intersect($input['allowed_methods'], $valid_methods);
        } else {
            $sanitized['allowed_methods'] = array('GET', 'POST');
        }
        
        // Sanitize allowed headers
        if (isset($input['allowed_headers'])) {
            $sanitized['allowed_headers'] = sanitize_text_field($input['allowed_headers']);
        } else {
            $sanitized['allowed_headers'] = 'Content-Type, Authorization, X-Requested-With';
        }
        
        // Sanitize allow credentials checkbox
        $sanitized['allow_credentials'] = isset($input['allow_credentials']) ? 1 : 0;
        
        return $sanitized;
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="cors-manager-header">
                <h2><?php esc_html_e('Cross-Origin Resource Sharing (CORS) Configuration', 'cors-manager'); ?></h2>
                <p><?php esc_html_e('CORS allows web applications running at one domain to access resources from another domain. Configure these settings carefully to maintain security while enabling necessary cross-origin requests.', 'cors-manager'); ?></p>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields('cors_manager_settings');
                do_settings_sections('cors-manager');
                submit_button(__('Save Settings', 'cors-manager'));
                ?>
            </form>
            
            <div class="cors-manager-info">
                <h3><?php esc_html_e('Current CORS Status', 'cors-manager'); ?></h3>
                <?php $this->display_current_status(); ?>
            </div>
            
            <div class="cors-manager-help">
                <h3><?php esc_html_e('Help & Documentation', 'cors-manager'); ?></h3>
                <div class="help-section">
                    <h4><?php esc_html_e('What is CORS?', 'cors-manager'); ?></h4>
                    <p><?php esc_html_e('CORS (Cross-Origin Resource Sharing) is a security feature implemented by web browsers that blocks web pages from making requests to a different domain than the one serving the web page, unless the target domain explicitly allows it.', 'cors-manager'); ?></p>
                    
                    <h4><?php esc_html_e('Security Considerations', 'cors-manager'); ?></h4>
                    <ul>
                        <li><?php esc_html_e('Never use "*" for allowed origins in production environments', 'cors-manager'); ?></li>
                        <li><?php esc_html_e('Only allow origins that you trust and need access to your API', 'cors-manager'); ?></li>
                        <li><?php esc_html_e('Be cautious when enabling credentials if using wildcard origins', 'cors-manager'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function display_current_status() {
        $options = get_option('cors_manager_options');
        $enabled = isset($options['cors_enabled']) ? $options['cors_enabled'] : 0;
        
        if ($enabled) {
            echo '<div class="notice notice-success inline"><p>' . esc_html__('CORS is currently enabled', 'cors-manager') . '</p></div>';
            
            $origins = isset($options['allowed_origins']) ? $options['allowed_origins'] : '';
            $methods = isset($options['allowed_methods']) ? $options['allowed_methods'] : array();
            $headers = isset($options['allowed_headers']) ? $options['allowed_headers'] : '';
            
            echo '<table class="widefat">';
            echo '<tr><td><strong>' . esc_html__('Allowed Origins:', 'cors-manager') . '</strong></td><td>' . esc_html($origins ? $origins : esc_html__('None specified', 'cors-manager')) . '</td></tr>';
            echo '<tr><td><strong>' . esc_html__('Allowed Methods:', 'cors-manager') . '</strong></td><td>' . esc_html(is_array($methods) ? implode(', ', $methods) : esc_html__('None specified', 'cors-manager')) . '</td></tr>';
            echo '<tr><td><strong>' . esc_html__('Allowed Headers:', 'cors-manager') . '</strong></td><td>' . esc_html($headers ? $headers : esc_html__('None specified', 'cors-manager')) . '</td></tr>';
            echo '</table>';
        } else {
            echo '<div class="notice notice-warning inline"><p>' . esc_html__('CORS is currently disabled', 'cors-manager') . '</p></div>';
        }
    }
    
    public function handle_cors() {
        $options = get_option('cors_manager_options');
        $enabled = isset($options['cors_enabled']) ? $options['cors_enabled'] : 0;
        
        if (!$enabled) {
            return;
        }
        
        // Handle preflight requests
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $this->send_cors_headers();
            exit;
        }
        
        // Add CORS headers to all responses
        add_action('wp_headers', array($this, 'add_cors_headers'));
    }
    
    public function add_cors_headers($headers) {
        $cors_headers = $this->get_cors_headers();
        return array_merge($headers, $cors_headers);
    }
    
    public function send_cors_headers() {
        $cors_headers = $this->get_cors_headers();
        foreach ($cors_headers as $header => $value) {
            header($header . ': ' . $value);
        }
    }
    
    private function get_cors_headers() {
        $options = get_option('cors_manager_options');
        $headers = array();
        
        // Handle allowed origins
        $allowed_origins = isset($options['allowed_origins']) ? $options['allowed_origins'] : '';
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_ORIGIN'])) : '';
        
        if ($allowed_origins) {
            $origins_array = array_map('trim', explode("\n", $allowed_origins));
            
            if (in_array('*', $origins_array)) {
                $headers['Access-Control-Allow-Origin'] = '*';
            } elseif (in_array($origin, $origins_array)) {
                $headers['Access-Control-Allow-Origin'] = $origin;
            }
        }
        
        // Handle allowed methods
        $allowed_methods = isset($options['allowed_methods']) ? $options['allowed_methods'] : array('GET', 'POST');
        if (is_array($allowed_methods) && !empty($allowed_methods)) {
            $headers['Access-Control-Allow-Methods'] = implode(', ', $allowed_methods);
        }
        
        // Handle allowed headers
        $allowed_headers = isset($options['allowed_headers']) ? $options['allowed_headers'] : 'Content-Type, Authorization, X-Requested-With';
        if ($allowed_headers) {
            $headers['Access-Control-Allow-Headers'] = $allowed_headers;
        }
        
        // Handle credentials
        $allow_credentials = isset($options['allow_credentials']) ? $options['allow_credentials'] : 0;
        if ($allow_credentials) {
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }
        
        // Add max age for preflight requests
        $headers['Access-Control-Max-Age'] = '86400'; // 24 hours
        
        return $headers;
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'tools_page_cors-manager') {
            return;
        }
        
        wp_enqueue_style('cors-manager-admin', CORS_MANAGER_PLUGIN_URL . 'assets/admin.css', array(), CORS_MANAGER_VERSION);
        wp_enqueue_script('cors-manager-admin', CORS_MANAGER_PLUGIN_URL . 'assets/admin.js', array('jquery'), CORS_MANAGER_VERSION, true);
    }
}

// Initialize the plugin
new CORSManager();

// Activation hook
register_activation_hook(__FILE__, 'cors_manager_activate');
function cors_manager_activate() {
    // Set default options
    $default_options = array(
        'cors_enabled' => 0,
        'allowed_origins' => '',
        'allowed_methods' => array('GET', 'POST'),
        'allowed_headers' => 'Content-Type, Authorization, X-Requested-With',
        'allow_credentials' => 0
    );
    
    add_option('cors_manager_options', $default_options);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'cors_manager_deactivate');
function cors_manager_deactivate() {
    // Clean up if needed
}

// Uninstall hook
register_uninstall_hook(__FILE__, 'cors_manager_uninstall');
function cors_manager_uninstall() {
    delete_option('cors_manager_options');
}
?>