<?php
if(!class_exists('Simple_Page_Builder')){
    class Simple_Page_Builder{
        private $api_keys_table;
        private $logs_table;
        private $pages_table;

        public function __construct() {
            global $wpdb;
            $this->api_keys_table = $wpdb->prefix . 'page_builder_api_keys';
            $this->logs_table = $wpdb->prefix . 'page_builder_api_logs';
            $this->pages_table = $wpdb->prefix . 'page_builder_created_pages';

            add_action('rest_api_init', array($this, 'create_rest_api'));
            add_action('admin_menu', array($this, 'create_page_builder_page_settings'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        }

        public function create_rest_api(){
            register_rest_route(
                'pagebuilder/v1',
                '/create-pages',
                [
                    'methods' => 'POST',
                    'permission_callback' => '__return_true',
                    'callback' => array($this, 'page_builder_create_pages'),
                    'args' => [
                        'pages' => [
                            'required' => true,
                            'type' => 'array',
                            'description' => 'Array of pages with title and content',
                        ],
                    ],
                ],
            );
        }

        public function page_builder_create_pages($request){
            $page_builder_enabled = get_option('pagebuilder_api_enabled', 1);
            if (!$page_builder_enabled) {
                return new WP_Error('api_disabled', 'API access is currently disabled', ['status' => 403]);
            }
            global $wpdb;
            $start_time = microtime(true);
            //$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            //$endpoint = '/pagebuilder/v1/create-pages';
            //$status = 'failed';

            /* $headers = $request->get_headers();
            $provided_key = '';

            if (!empty($headers['api_key'][0])) {
                $provided_key = trim($headers['api_key'][0]);
            } elseif ($request->get_param('api_key')) {
                $provided_key = trim($request->get_param('api_key'));
            } */

            $provided_key = $this->get_api_key_from_request($request);
            //echo $provided_key;

            $key_data = $this->page_builder_validate_api_key($provided_key);

            if (!$key_data) {
                /* $wpdb->insert(
                    $this->logs_table, 
                    [
                        'api_key_preview' => substr($key_data->api_key, 0, 8) . '...',
                        'endpoint' => $endpoint,
                        'status' => 'failed',
                        'ip_address' => $ip,
                        'response_time' => microtime(true) - $start_time,
                    ]
                ); */
                $response_time = microtime(true) - $start_time;
                $this->log_request_activities(null, null, 'failed', $response_time);
                
                return new WP_Error(
                    'unauthorized',
                    'Invalid or missing API key',
                    ['status' => 401]
                );
            }

            $pages = $request->get_param('pages');

            if (empty($pages) || !is_array($pages)) {
                 /* $wpdb->insert(
                    $this->logs_table, 
                    [
                        'api_key_id' => $key_data->id,
                        'api_key_preview' => substr($api_key, 0, 8) . '...',
                        'endpoint' => $endpoint,
                        'status' => 'failed',
                        'ip_address' => $ip,
                        'response_time' => microtime(true) - $start_time,
                    ]
                ); */
                $response_time = microtime(true) - $start_time;
                $this->log_request_activities($key_data->id,$key_data->api_key, 'failed',  $response_time);
                return new WP_Error('invalid_input', 'Expected an array of pages', ['status' => 400]);
            }

            $created = [];
            $failed = [];

            foreach ($pages as $index => $page) {
                $title = isset($page['title']) ? sanitize_text_field($page['title']) : '';
                $content = isset($page['content']) ? wp_kses_post($page['content']) : '';

                if (empty($title)) {
                    $failed[] = [
                        'index' => $index,
                        'error' => 'Missing title'
                    ];
                    continue;
                }

                $page_id = wp_insert_post([
                    'post_title'   => $title,
                    'post_content' => $content,
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                ]);

                if (is_wp_error($page_id)) {
                    $failed[] = [
                        'index' => $index,
                        'title' => $title,
                        'error' => $page_id->get_error_message()
                    ];
                } else {
                    $page_url = get_permalink($page_id);
                    $api_key_record = $wpdb->get_row(
                        $wpdb->prepare("SELECT key_name FROM {$this->api_keys_table} WHERE id = %d",  $key_data->id)
                    );

                    $api_key_name = isset($api_key_record->key_name) ? $api_key_record->key_name : 'Unknown';
                    $api_key_preview = substr($provided_key, 0, 8) . '...';

                    $wpdb->insert(
                        $this->pages_table, 
                        [
                            'page_id'        => $page_id,
                            'page_title'     => get_the_title($page_id),
                            'page_url'       => $page_url,
                            'api_key_name'   => $api_key_name,
                            'api_key_preview'=> $api_key_preview,
                        ]
                    );
                    $created[] = [
                        'title' => $title,
                        'page_id' => $page_id,
                        'link' => get_permalink($page_id),
                    ];


                }
            }
            /* $wpdb->insert(
                $this->logs_table, 
                [
                    'api_key_id' => $key_data->id,
                    'api_key_preview' => substr($key_data->api_key, 0, 8) . '...',
                    'endpoint' => $endpoint,
                    'status' => 'success',
                    'pages_created' => count($created),
                    'response_time' => microtime(true) - $start_time,
                    'ip_address' => $ip,
                ]
            ); */
            $response_time = microtime(true) - $start_time;
            $this->log_request_activities($key_data->id, $key_data->api_key, 'success', $response_time, count($created));

            $payload = [
                'event'        => 'pages_created',
                'timestamp'    => gmdate('c'),
                'request_id'   => 'req_' . wp_generate_uuid4(),
                'api_key_name' => 'Production Server',
                'total_pages'  => count($created),
                'pages'        => $created,
            ];

            $this->page_builder_send_webhook($payload);

            return [
                'success' => true,
                'created_count' => count($created),
                'failed_count' => count($failed),
                'created_pages' => $created,
                'failed_pages' => $failed,
            ];
        }

        public function create_page_builder_page_settings(){
            add_submenu_page(
                'tools.php',
                'Page Builder',
                'Page Builder',
                'manage_options',
                'page_builder',
                array($this, 'page_builder_setting_content'),
            );
        }

        public function page_builder_setting_content(){
            if(!current_user_can('manage_options')){
                return;
            }
            require_once(SIMPLE_PAGE_BUILDER_PATH. 'templates/page-builder-setting-page.php');
        }

        public function enqueue_scripts(){
            wp_enqueue_script(
                'page-builder-js',
                SIMPLE_PAGE_BUILDER_URI. 'assets/js/main.js',
                ['jquery']
            );
        }

        private function  page_builder_send_webhook($payload) {
            $webhook_url = get_option('pagebuilder_webhook_url');
            $webhook_secret = 'mySuperSecretWebhookKey123!@#';

            if (empty($webhook_url)) {
                return; 
            }

            $body = wp_json_encode($payload);
            $signature = hash_hmac('sha256', $body, $webhook_secret);

            $args = [
                'method'      => 'POST',
                'timeout'     => 10,
                'headers'     => [
                    'Content-Type'          => 'application/json',
                    'X-Webhook-Signature'   => $signature,
                ],
                'body'        => $body,
            ];

            $max_retries = 2;
            $delay = 2; 

            for ($i = 0; $i <= $max_retries; $i++) {
                $response = wp_remote_post($webhook_url, $args);
                $code = wp_remote_retrieve_response_code($response);


                if (!is_wp_error($response) && $code >= 200 && $code < 300) {
                    pagebuilder_log_webhook_delivery('success', $payload, $response);
                    return true;
                }

        
                if ($i < $max_retries) {
                    sleep($delay);
                    $delay *= 2; 
                }
            }
            $this->pagebuilder_log_webhook_delivery('failed', $payload, $response);
            return false;
        }

        private function  page_builder_log_webhook_delivery($status, $payload, $response) {
            $log_entry = [
                'timestamp' => current_time('mysql'),
                'status'    => $status,
                'event'     => isset($payload['event']) ? $payload['event'] : 'unknown',
                'response'  => is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_body($response),
            ];

            $upload_dir = wp_upload_dir();
            $log_file = trailingslashit($upload_dir['basedir']) . 'pagebuilder-webhook-log.txt';

            $log_text = sprintf("[%s] %s | Event: %s | %s\n",
                $log_entry['timestamp'],
                strtoupper($log_entry['status']),
                $log_entry['event'],
                $log_entry['response']
            );

            file_put_contents($log_file, $log_text, FILE_APPEND);
        }

        private function get_api_key_from_request($request){
            $headers = $request->get_headers();
            $provided_key = '';

            if (!empty($headers['api_key'][0])) {
                $provided_key = trim($headers['api_key'][0]);
            } elseif ($request->get_param('api_key')) {
                $provided_key = trim($request->get_param('api_key'));
            }

            return $provided_key;
        }

        private function log_request_activities($api_key_id, $api_key, $status, $response_time, $pages_created = 0){
            global $wpdb;
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $endpoint = '/pagebuilder/v1/create-pages';

            $wpdb->insert(
                $this->logs_table, 
                [
                    'api_key_id' => $api_key_id,
                    'api_key_preview' => substr($api_key, 0, 8) . '...',
                    'endpoint' => $endpoint,
                    'status' => $status,
                    'ip_address' => $ip,
                    'response_time' => $response_time,
                    'pages_created' => $pages_created,
                ]
            );
        }


        private function page_builder_validate_api_key($provided_key) {
            if (empty($provided_key)) return false;

            global $wpdb;
            $keys = $wpdb->get_results("SELECT * FROM {$this->api_keys_table} WHERE status = 'active'");

            foreach ($keys as $key) {
                if (wp_check_password($provided_key, $key->api_key)) {
                    if ($key->expires_at && strtotime($key->expires_at) < time()) {
                        return false;
                    }

                    $wpdb->update(
                        $this->api_keys_table, 
                        [
                            'last_used' => current_time('mysql'),
                            'request_count' => $key->request_count + 1,
                        ], 
                        ['id' => $key->id]
                );

                    return $key; 
                }
            } 

            return false;
        }

        static public function activate(){
            global $wpdb;
            $api_keys_table = $wpdb->prefix . 'page_builder_api_keys';
            $logs_table = $wpdb->prefix . 'page_builder_api_logs';
            $pages_table = $wpdb->prefix . 'page_builder_created_pages';

            $charset_collate = $wpdb->get_charset_collate();

            //create API keys table
            $sql1 = "CREATE TABLE IF NOT EXISTS $api_keys_table (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                api_key varchar(128) NOT NULL UNIQUE,
                key_name varchar(255) NOT NULL,
                status varchar(20) DEFAULT 'active',
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                expires_at datetime DEFAULT NULL,
                last_used datetime DEFAULT NULL,
                request_count bigint(20) DEFAULT 0,
                permissions varchar(255) DEFAULT 'create_pages',
                PRIMARY KEY (id)
            ) $charset_collate;";

            // Create logs table
            $sql2 = "CREATE TABLE IF NOT EXISTS $logs_table (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                timestamp datetime DEFAULT CURRENT_TIMESTAMP,
                api_key_id bigint(20) unsigned DEFAULT NULL,
                api_key_preview varchar(16) DEFAULT NULL,
                endpoint varchar(200) DEFAULT NULL,
                status varchar(20) DEFAULT NULL,
                pages_created int(11) DEFAULT 0,
                response_time float DEFAULT NULL,
                ip_address varchar(45) DEFAULT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

             // Create pages table
            $sql3 = "CREATE TABLE $pages_table (
                id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                page_id BIGINT(20) UNSIGNED NOT NULL,
                page_title VARCHAR(255),
                page_url VARCHAR(255),
                created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                api_key_name VARCHAR(100),
                api_key_preview VARCHAR(64),
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql1);
            dbDelta($sql2);
            dbDelta($sql3);
        }

        static public function deactivate(){}

        static public function uninstall(){}
    }
}