<?php
/**
 * Plugin Name: RSS Feed Importer Pro
 * Plugin URI: https://your-website.com/rss-feed-importer
 * Description: Plugin completo per importare post da feed RSS con programmazione automatica, generazione categorie/tag e gestione completa.
 * Version: 1.0.0
 * Author: Il Tuo Nome
 * Author URI: https://your-website.com
 * License: GPL v2 or later
 * Text Domain: rss-feed-importer
 * Domain Path: /languages
 */

// Impedire l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Definire costanti del plugin
define('RSS_IMPORTER_VERSION', '1.0.0');
define('RSS_IMPORTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RSS_IMPORTER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RSS_IMPORTER_TABLE_FEEDS', 'rss_importer_feeds');
define('RSS_IMPORTER_TABLE_IMPORTS', 'rss_importer_imports');

class RSSFeedImporter {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('admin_notices', array($this, 'show_bulk_action_messages'));
        add_action('wp_ajax_validate_rss_feed', array($this, 'ajax_validate_rss_feed'));
        add_action('wp_ajax_import_rss_feed', array($this, 'ajax_import_rss_feed'));
        add_action('wp_ajax_delete_feed', array($this, 'ajax_delete_feed'));
        
        // Hook per le azioni in blocco
        add_action('admin_init', array($this, 'handle_bulk_posts_action'));
        
        // Hook per la programmazione automatica
        add_action('rss_importer_scheduled_import', array($this, 'run_scheduled_import'));
        
        // Hook di attivazione/disattivazione
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Includi file aggiuntivi
        $this->include_files();
    }
    
    /**
     * Includi file necessari del plugin
     */
    private function include_files() {
        // Includi costanti
        if (file_exists(RSS_IMPORTER_PLUGIN_DIR . 'includes/constants.php')) {
            require_once RSS_IMPORTER_PLUGIN_DIR . 'includes/constants.php';
        }
        
        // Includi gestori AJAX aggiuntivi
        if (file_exists(RSS_IMPORTER_PLUGIN_DIR . 'includes/ajax-handlers.php')) {
            require_once RSS_IMPORTER_PLUGIN_DIR . 'includes/ajax-handlers.php';
        }
        
        // Includi funzioni helper
        if (file_exists(RSS_IMPORTER_PLUGIN_DIR . 'includes/helpers.php')) {
            require_once RSS_IMPORTER_PLUGIN_DIR . 'includes/helpers.php';
        }
    }
    
    /**
     * Gestisce le azioni in blocco sui post
     */
    public function handle_bulk_posts_action() {
        if (!isset($_POST['action']) || $_POST['action'] === '-1') {
            return;
        }
        
        if (!isset($_POST['post']) || !is_array($_POST['post'])) {
            return;
        }
        
        $action = sanitize_text_field($_POST['action']);
        $post_ids = array_map('intval', $_POST['post']);
        
        if (!current_user_can('edit_posts')) {
            wp_die(__('Permessi insufficienti', 'rss-feed-importer'));
        }
        
        $updated = 0;
        $errors = 0;
        
        foreach ($post_ids as $post_id) {
            $result = false;
            
            switch ($action) {
                case 'publish':
                    $result = wp_update_post(array(
                        'ID' => $post_id,
                        'post_status' => 'publish'
                    ));
                    break;
                    
                case 'draft':
                    $result = wp_update_post(array(
                        'ID' => $post_id,
                        'post_status' => 'draft'
                    ));
                    break;
                    
                case 'trash':
                    $result = wp_trash_post($post_id);
                    break;
            }
            
            if ($result && !is_wp_error($result)) {
                $updated++;
            } else {
                $errors++;
            }
        }
        
        // Redirect con messaggio
        $redirect_url = add_query_arg(array(
            'page' => 'rss-importer-posts',
            'bulk_updated' => $updated,
            'bulk_errors' => $errors,
            'bulk_action' => $action
        ), admin_url('admin.php'));
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Mostra messaggi per le azioni in blocco
     */
    public function show_bulk_action_messages() {
        if (!isset($_GET['bulk_updated'])) {
            return;
        }
        
        $updated = intval($_GET['bulk_updated']);
        $errors = intval($_GET['bulk_errors']) ?: 0;
        $action = sanitize_text_field($_GET['bulk_action']) ?: '';
        
        $action_labels = array(
            'publish' => __('pubblicati', 'rss-feed-importer'),
            'draft' => __('portati in bozza', 'rss-feed-importer'),
            'trash' => __('spostati nel cestino', 'rss-feed-importer')
        );
        
        $action_label = isset($action_labels[$action]) ? $action_labels[$action] : $action;
        
        if ($updated > 0) {
            $message = sprintf(
                _n('%d post %s.', '%d post %s.', $updated, 'rss-feed-importer'),
                $updated,
                $action_label
            );
            
            if ($errors > 0) {
                $message .= ' ' . sprintf(
                    _n('%d errore.', '%d errori.', $errors, 'rss-feed-importer'),
                    $errors
                );
            }
            
            echo '<div class="notice notice-success is-dismissible"><p>' . $message . '</p></div>';
        } elseif ($errors > 0) {
            $message = sprintf(
                _n('Errore su %d post.', 'Errori su %d post.', $errors, 'rss-feed-importer'),
                $errors
            );
            
            echo '<div class="notice notice-error is-dismissible"><p>' . $message . '</p></div>';
        }
    }
    
    public function init() {
        load_plugin_textdomain('rss-feed-importer', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function activate() {
        $this->create_tables();
        $this->schedule_cron_jobs();
    }
    
    public function deactivate() {
        $this->clear_cron_jobs();
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Tabella per i feed RSS
        $table_feeds = $wpdb->prefix . RSS_IMPORTER_TABLE_FEEDS;
        $sql_feeds = "CREATE TABLE $table_feeds (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            url text NOT NULL,
            status enum('active', 'inactive') DEFAULT 'active',
            import_frequency varchar(50) DEFAULT 'hourly',
            last_import datetime NULL,
            total_imported int(11) DEFAULT 0,
            auto_categorize tinyint(1) DEFAULT 1,
            auto_tags tinyint(1) DEFAULT 1,
            post_status varchar(20) DEFAULT 'draft',
            author_id int(11) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Tabella per i log delle importazioni
        $table_imports = $wpdb->prefix . RSS_IMPORTER_TABLE_IMPORTS;
        $sql_imports = "CREATE TABLE $table_imports (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            feed_id mediumint(9) NOT NULL,
            post_id bigint(20) NOT NULL,
            original_url text,
            original_title text,
            import_date datetime DEFAULT CURRENT_TIMESTAMP,
            categories_created text,
            tags_created text,
            status enum('success', 'error', 'duplicate') DEFAULT 'success',
            error_message text,
            PRIMARY KEY (id),
            KEY feed_id (feed_id),
            KEY post_id (post_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_feeds);
        dbDelta($sql_imports);
    }
    
    private function schedule_cron_jobs() {
        if (!wp_next_scheduled('rss_importer_scheduled_import')) {
            wp_schedule_event(time(), 'hourly', 'rss_importer_scheduled_import');
        }
    }
    
    private function clear_cron_jobs() {
        wp_clear_scheduled_hook('rss_importer_scheduled_import');
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('RSS Feed Importer', 'rss-feed-importer'),
            __('RSS Importer', 'rss-feed-importer'),
            'manage_options',
            'rss-feed-importer',
            array($this, 'admin_page'),
            'dashicons-rss',
            30
        );
        
        add_submenu_page(
            'rss-feed-importer',
            __('Gestione Feed', 'rss-feed-importer'),
            __('Gestione Feed', 'rss-feed-importer'),
            'manage_options',
            'rss-feed-importer',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'rss-feed-importer',
            __('Post Importati', 'rss-feed-importer'),
            __('Post Importati', 'rss-feed-importer'),
            'manage_options',
            'rss-importer-posts',
            array($this, 'imported_posts_page')
        );
        
        add_submenu_page(
            'rss-feed-importer',
            __('Impostazioni', 'rss-feed-importer'),
            __('Impostazioni', 'rss-feed-importer'),
            'manage_options',
            'rss-importer-settings',
            array($this, 'settings_page')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'rss-feed-importer') !== false || strpos($hook, 'rss-importer') !== false) {
            wp_enqueue_script('rss-importer-admin', RSS_IMPORTER_PLUGIN_URL . 'assets/admin.js', array('jquery'), RSS_IMPORTER_VERSION, true);
            wp_enqueue_style('rss-importer-admin', RSS_IMPORTER_PLUGIN_URL . 'assets/admin.css', array(), RSS_IMPORTER_VERSION);
            
            wp_localize_script('rss-importer-admin', 'rssImporter', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('rss_importer_nonce'),
                'strings' => array(
                    'validating' => __('Validazione in corso...', 'rss-feed-importer'),
                    'importing' => __('Importazione in corso...', 'rss-feed-importer'),
                    'success' => __('Operazione completata con successo!', 'rss-feed-importer'),
                    'error' => __('Errore durante l\'operazione', 'rss-feed-importer'),
                    'confirm_delete' => __('Sei sicuro di voler eliminare questo feed?', 'rss-feed-importer')
                )
            ));
        }
    }
    
    public function admin_page() {
        global $wpdb;
        
        // Gestione salvataggio feed
        if (isset($_POST['save_feed']) && wp_verify_nonce($_POST['rss_importer_nonce'], 'save_feed')) {
            $this->save_feed($_POST);
        }
        
        // Recupera tutti i feed
        $table_feeds = $wpdb->prefix . RSS_IMPORTER_TABLE_FEEDS;
        $feeds = $wpdb->get_results("SELECT * FROM $table_feeds ORDER BY created_at DESC");
        
        include RSS_IMPORTER_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    public function imported_posts_page() {
        global $wpdb;
        
        $table_imports = $wpdb->prefix . RSS_IMPORTER_TABLE_IMPORTS;
        $table_feeds = $wpdb->prefix . RSS_IMPORTER_TABLE_FEEDS;
        
        // Gestione esportazione
        if (isset($_POST['export_imports']) && wp_verify_nonce($_POST['rss_importer_nonce'], 'export_imports')) {
            $this->handle_export_request($_POST);
            return;
        }
        
        // Paginazione
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        // Filtri
        $where_conditions = array('1=1');
        $where_values = array();
        
        if (!empty($_GET['filter_feed'])) {
            $where_conditions[] = 'i.feed_id = %d';
            $where_values[] = intval($_GET['filter_feed']);
        }
        
        if (!empty($_GET['filter_status'])) {
            $where_conditions[] = 'i.status = %s';
            $where_values[] = sanitize_text_field($_GET['filter_status']);
        }
        
        if (!empty($_GET['filter_date_from'])) {
            $where_conditions[] = 'DATE(i.import_date) >= %s';
            $where_values[] = sanitize_text_field($_GET['filter_date_from']);
        }
        
        if (!empty($_GET['filter_date_to'])) {
            $where_conditions[] = 'DATE(i.import_date) <= %s';
            $where_values[] = sanitize_text_field($_GET['filter_date_to']);
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Query per i post importati
        $query = "
            SELECT i.*, f.name as feed_name, p.post_title, p.post_date, p.post_status
            FROM $table_imports i
            LEFT JOIN $table_feeds f ON i.feed_id = f.id
            LEFT JOIN {$wpdb->posts} p ON i.post_id = p.ID
            WHERE $where_clause
            ORDER BY i.import_date DESC
            LIMIT %d OFFSET %d
        ";
        
        if (!empty($where_values)) {
            $imports = $wpdb->get_results($wpdb->prepare($query, array_merge($where_values, array($per_page, $offset))));
            $total_imports = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_imports i WHERE " . implode(' AND ', array_slice($where_conditions, 1)), $where_values));
        } else {
            $imports = $wpdb->get_results($wpdb->prepare($query, $per_page, $offset));
            $total_imports = $wpdb->get_var("SELECT COUNT(*) FROM $table_imports");
        }
        
        $total_pages = ceil($total_imports / $per_page);
        
        include RSS_IMPORTER_PLUGIN_DIR . 'templates/imported-posts.php';
    }
    
    /**
     * Gestisce le richieste di esportazione
     */
    private function handle_export_request($data) {
        $format = sanitize_text_field($data['export_format']);
        $date_range = sanitize_text_field($data['export_date_range']);
        
        $export_data = $this->get_export_data($date_range);
        
        if (empty($export_data)) {
            wp_die(__('Nessun dato da esportare', 'rss-feed-importer'));
        }
        
        switch ($format) {
            case 'csv':
                $this->export_csv($export_data);
                break;
            case 'json':
                $this->export_json($export_data);
                break;
            default:
                wp_die(__('Formato non supportato', 'rss-feed-importer'));
        }
    }
    
    /**
     * Esporta i dati in formato CSV
     */
    private function export_csv($data) {
        $filename = 'rss-import-data-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        // Header CSV
        fputcsv($output, array(
            'ID',
            'Feed',
            'Titolo Originale',
            'URL Originale',
            'Data Importazione',
            'Stato Importazione',
            'Titolo Post',
            'Stato Post',
            'Data Pubblicazione'
        ));
        
        // Dati
        foreach ($data as $row) {
            fputcsv($output, array(
                $row['id'],
                $row['feed_name'],
                $row['original_title'],
                $row['original_url'],
                $row['import_date'],
                $row['status'],
                $row['post_title'],
                $row['post_status'],
                $row['post_date']
            ));
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Esporta i dati in formato JSON
     */
    private function export_json($data) {
        $filename = 'rss-import-data-' . date('Y-m-d') . '.json';
        
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Recupera i dati per l'esportazione
     */
    private function get_export_data($date_range) {
        global $wpdb;
        
        $table_imports = $wpdb->prefix . RSS_IMPORTER_TABLE_IMPORTS;
        $table_feeds = $wpdb->prefix . RSS_IMPORTER_TABLE_FEEDS;
        
        $where_clause = '';
        switch ($date_range) {
            case 'today':
                $where_clause = "AND DATE(i.import_date) = CURDATE()";
                break;
            case 'last_week':
                $where_clause = "AND i.import_date >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'last_month':
                $where_clause = "AND i.import_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
        }
        
        $query = "
            SELECT 
                i.id,
                f.name as feed_name,
                i.original_title,
                i.original_url,
                i.import_date,
                i.status,
                p.post_title,
                p.post_status,
                p.post_date
            FROM $table_imports i
            LEFT JOIN $table_feeds f ON i.feed_id = f.id
            LEFT JOIN {$wpdb->posts} p ON i.post_id = p.ID
            WHERE 1=1 $where_clause
            ORDER BY i.import_date DESC
        ";
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    public function settings_page() {
        if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['rss_importer_nonce'], 'save_settings')) {
            $this->save_settings($_POST);
        }
        
        $settings = get_option('rss_importer_settings', $this->get_default_settings());
        
        include RSS_IMPORTER_PLUGIN_DIR . 'templates/settings-page.php';
    }
    
    private function save_feed($data) {
        global $wpdb;
        
        $feed_data = array(
            'name' => sanitize_text_field($data['feed_name']),
            'url' => esc_url_raw($data['feed_url']),
            'status' => sanitize_text_field($data['feed_status']),
            'import_frequency' => sanitize_text_field($data['import_frequency']),
            'auto_categorize' => isset($data['auto_categorize']) ? 1 : 0,
            'auto_tags' => isset($data['auto_tags']) ? 1 : 0,
            'post_status' => sanitize_text_field($data['post_status']),
            'author_id' => intval($data['author_id'])
        );
        
        $table_feeds = $wpdb->prefix . RSS_IMPORTER_TABLE_FEEDS;
        
        if (isset($data['feed_id']) && !empty($data['feed_id'])) {
            // Aggiorna feed esistente
            $wpdb->update($table_feeds, $feed_data, array('id' => intval($data['feed_id'])));
            $message = __('Feed aggiornato con successo!', 'rss-feed-importer');
        } else {
            // Crea nuovo feed
            $wpdb->insert($table_feeds, $feed_data);
            $message = __('Feed creato con successo!', 'rss-feed-importer');
        }
        
        echo '<div class="notice notice-success"><p>' . $message . '</p></div>';
    }
    
    private function save_settings($data) {
        $settings = array(
            'max_posts_per_import' => intval($data['max_posts_per_import']),
            'duplicate_check_method' => sanitize_text_field($data['duplicate_check_method']),
            'default_post_status' => sanitize_text_field($data['default_post_status']),
            'category_creation_method' => sanitize_text_field($data['category_creation_method']),
            'tag_creation_method' => sanitize_text_field($data['tag_creation_method']),
            'image_import' => isset($data['image_import']) ? 1 : 0,
            'excerpt_length' => intval($data['excerpt_length'])
        );
        
        update_option('rss_importer_settings', $settings);
        echo '<div class="notice notice-success"><p>' . __('Impostazioni salvate con successo!', 'rss-feed-importer') . '</p></div>';
    }
    
    private function get_default_settings() {
        return array(
            'max_posts_per_import' => 10,
            'duplicate_check_method' => 'title_url',
            'default_post_status' => 'draft',
            'category_creation_method' => 'auto',
            'tag_creation_method' => 'auto',
            'image_import' => 1,
            'excerpt_length' => 150
        );
    }
    
    public function ajax_validate_rss_feed() {
        check_ajax_referer('rss_importer_nonce', 'nonce');
        
        $url = esc_url_raw($_POST['url']);
        $validation_result = $this->validate_rss_feed($url);
        
        wp_send_json($validation_result);
    }
    
    public function ajax_import_rss_feed() {
        check_ajax_referer('rss_importer_nonce', 'nonce');
        
        $feed_id = intval($_POST['feed_id']);
        $result = $this->import_feed($feed_id);
        
        wp_send_json($result);
    }
    
    public function ajax_delete_feed() {
        check_ajax_referer('rss_importer_nonce', 'nonce');
        
        global $wpdb;
        $feed_id = intval($_POST['feed_id']);
        $table_feeds = $wpdb->prefix . RSS_IMPORTER_TABLE_FEEDS;
        
        $deleted = $wpdb->delete($table_feeds, array('id' => $feed_id));
        
        if ($deleted) {
            wp_send_json_success(__('Feed eliminato con successo!', 'rss-feed-importer'));
        } else {
            wp_send_json_error(__('Errore durante l\'eliminazione del feed', 'rss-feed-importer'));
        }
    }
    
    private function validate_rss_feed($url) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return array(
                'success' => false,
                'message' => __('URL non valido', 'rss-feed-importer')
            );
        }
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => __('Impossibile raggiungere l\'URL: ', 'rss-feed-importer') . $response->get_error_message()
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $rss = simplexml_load_string($body);
        
        if ($rss === false) {
            return array(
                'success' => false,
                'message' => __('Il contenuto non è un feed RSS valido', 'rss-feed-importer')
            );
        }
        
        // Controlla se ha elementi
        $items_count = 0;
        if (isset($rss->channel->item)) {
            $items_count = count($rss->channel->item);
        } elseif (isset($rss->item)) {
            $items_count = count($rss->item);
        }
        
        return array(
            'success' => true,
            'message' => sprintf(__('Feed RSS valido con %d elementi', 'rss-feed-importer'), $items_count),
            'title' => isset($rss->channel->title) ? (string)$rss->channel->title : __('Titolo non disponibile', 'rss-feed-importer'),
            'description' => isset($rss->channel->description) ? (string)$rss->channel->description : '',
            'items_count' => $items_count
        );
    }
    
    public function import_feed($feed_id) {
        global $wpdb;
        
        $table_feeds = $wpdb->prefix . RSS_IMPORTER_TABLE_FEEDS;
        $feed = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_feeds WHERE id = %d", $feed_id));
        
        if (!$feed) {
            return array('success' => false, 'message' => __('Feed non trovato', 'rss-feed-importer'));
        }
        
        $validation = $this->validate_rss_feed($feed->url);
        if (!$validation['success']) {
            return $validation;
        }
        
        $response = wp_remote_get($feed->url, array('timeout' => 30));
        $body = wp_remote_retrieve_body($response);
        $rss = simplexml_load_string($body);
        
        $settings = get_option('rss_importer_settings', $this->get_default_settings());
        $imported_count = 0;
        $errors = array();
        
        $items = isset($rss->channel->item) ? $rss->channel->item : $rss->item;
        $max_items = min(count($items), $settings['max_posts_per_import']);
        
        for ($i = 0; $i < $max_items; $i++) {
            $item = $items[$i];
            $import_result = $this->import_single_item($item, $feed, $settings);
            
            if ($import_result['success']) {
                $imported_count++;
            } else {
                $errors[] = $import_result['message'];
            }
        }
        
        // Aggiorna statistiche del feed
        $wpdb->update(
            $table_feeds,
            array(
                'last_import' => current_time('mysql'),
                'total_imported' => $feed->total_imported + $imported_count
            ),
            array('id' => $feed_id)
        );
        
        $message = sprintf(__('Importati %d post su %d elaborati', 'rss-feed-importer'), $imported_count, $max_items);
        if (!empty($errors)) {
            $message .= '. ' . __('Errori: ', 'rss-feed-importer') . implode(', ', array_slice($errors, 0, 3));
        }
        
        return array(
            'success' => true,
            'message' => $message,
            'imported_count' => $imported_count,
            'total_processed' => $max_items,
            'errors' => $errors
        );
    }
    
    private function import_single_item($item, $feed, $settings) {
        global $wpdb;
        
        $title = sanitize_text_field((string)$item->title);
        $link = esc_url_raw((string)$item->link);
        $description = wp_kses_post((string)$item->description);
        $pub_date = strtotime((string)$item->pubDate);
        
        // Controllo duplicati
        if ($this->is_duplicate_post($title, $link, $settings['duplicate_check_method'])) {
            return array(
                'success' => false,
                'message' => sprintf(__('Post duplicato: %s', 'rss-feed-importer'), $title)
            );
        }
        
        // Crea il post
        $post_data = array(
            'post_title' => $title,
            'post_content' => $description,
            'post_excerpt' => wp_trim_words($description, $settings['excerpt_length']),
            'post_status' => $feed->post_status,
            'post_author' => $feed->author_id,
            'post_date' => $pub_date ? date('Y-m-d H:i:s', $pub_date) : current_time('mysql'),
            'meta_input' => array(
                'rss_importer_source_url' => $link,
                'rss_importer_feed_id' => $feed->id
            )
        );
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return array(
                'success' => false,
                'message' => sprintf(__('Errore creazione post: %s', 'rss-feed-importer'), $post_id->get_error_message())
            );
        }
        
        // Genera categorie e tag se abilitato
        $categories_created = array();
        $tags_created = array();
        
        if ($feed->auto_categorize) {
            $categories_created = $this->auto_generate_categories($item, $post_id, $settings);
        }
        
        if ($feed->auto_tags) {
            $tags_created = $this->auto_generate_tags($item, $post_id, $settings);
        }
        
        // Salva log dell'importazione
        $table_imports = $wpdb->prefix . RSS_IMPORTER_TABLE_IMPORTS;
        $wpdb->insert($table_imports, array(
            'feed_id' => $feed->id,
            'post_id' => $post_id,
            'original_url' => $link,
            'original_title' => $title,
            'categories_created' => json_encode($categories_created),
            'tags_created' => json_encode($tags_created),
            'status' => 'success'
        ));
        
        return array(
            'success' => true,
            'message' => sprintf(__('Post importato: %s', 'rss-feed-importer'), $title),
            'post_id' => $post_id
        );
    }
    
    private function is_duplicate_post($title, $url, $method) {
        global $wpdb;
        
        switch ($method) {
            case 'title':
                $existing = get_page_by_title($title, OBJECT, 'post');
                return !empty($existing);
                
            case 'url':
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'rss_importer_source_url' AND meta_value = %s",
                    $url
                ));
                return !empty($existing);
                
            case 'title_url':
            default:
                $existing_title = get_page_by_title($title, OBJECT, 'post');
                $existing_url = $wpdb->get_var($wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'rss_importer_source_url' AND meta_value = %s",
                    $url
                ));
                return !empty($existing_title) || !empty($existing_url);
        }
    }
    
    private function auto_generate_categories($item, $post_id, $settings) {
        $categories = array();
        
        // Estrai categorie dal feed RSS se presenti
        if (isset($item->category)) {
            foreach ($item->category as $category) {
                $cat_name = sanitize_text_field((string)$category);
                if (!empty($cat_name)) {
                    $cat_obj = get_category_by_slug(sanitize_title($cat_name));
                    if (!$cat_obj) {
                        $cat_id = wp_insert_category(array(
                            'cat_name' => $cat_name,
                            'category_nicename' => sanitize_title($cat_name)
                        ));
                        if (!is_wp_error($cat_id)) {
                            $categories[] = $cat_id;
                        }
                    } else {
                        $categories[] = $cat_obj->term_id;
                    }
                }
            }
        }
        
        // Se non ci sono categorie, crea una basata sul titolo
        if (empty($categories) && $settings['category_creation_method'] === 'auto') {
            $title = (string)$item->title;
            $keywords = $this->extract_keywords($title);
            
            if (!empty($keywords)) {
                $cat_name = ucfirst($keywords[0]);
                $cat_obj = get_category_by_slug(sanitize_title($cat_name));
                if (!$cat_obj) {
                    $cat_id = wp_insert_category(array(
                        'cat_name' => $cat_name,
                        'category_nicename' => sanitize_title($cat_name)
                    ));
                    if (!is_wp_error($cat_id)) {
                        $categories[] = $cat_id;
                    }
                } else {
                    $categories[] = $cat_obj->term_id;
                }
            }
        }
        
        if (!empty($categories)) {
            wp_set_post_categories($post_id, $categories);
        }
        
        return $categories;
    }
    
    private function auto_generate_tags($item, $post_id, $settings) {
        $tags = array();
        
        // Estrai parole chiave dal titolo e descrizione
        $title = (string)$item->title;
        $description = (string)$item->description;
        $text = $title . ' ' . strip_tags($description);
        
        $keywords = $this->extract_keywords($text);
        
        // Limita a 5-8 tag per evitare spam
        $keywords = array_slice($keywords, 0, 8);
        
        foreach ($keywords as $keyword) {
            if (strlen($keyword) > 2) { // Solo parole di almeno 3 caratteri
                $tags[] = $keyword;
            }
        }
        
        if (!empty($tags)) {
            wp_set_post_tags($post_id, $tags);
        }
        
        return $tags;
    }
    
    private function extract_keywords($text) {
        // Parole comuni da ignorare
        $stop_words = array(
            'il', 'lo', 'la', 'le', 'gli', 'i', 'un', 'una', 'uno', 'di', 'da', 'del', 'dello', 'della',
            'dei', 'degli', 'delle', 'in', 'su', 'per', 'con', 'tra', 'fra', 'a', 'ad', 'al', 'allo',
            'alla', 'ai', 'agli', 'alle', 'e', 'ed', 'o', 'od', 'ma', 'però', 'quindi', 'inoltre',
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'
        );
        
        // Pulisci il testo
        $text = strtolower($text);
        $text = preg_replace('/[^\w\s]/', ' ', $text);
        $words = explode(' ', $text);
        
        // Filtra parole
        $keywords = array();
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) > 2 && !in_array($word, $stop_words) && !is_numeric($word)) {
                $keywords[] = $word;
            }
        }
        
        // Rimuovi duplicati e restituisci le parole più frequenti
        $word_count = array_count_values($keywords);
        arsort($word_count);
        
        return array_keys($word_count);
    }
    
    public function run_scheduled_import() {
        global $wpdb;
        
        $table_feeds = $wpdb->prefix . RSS_IMPORTER_TABLE_FEEDS;
        $feeds = $wpdb->get_results("SELECT * FROM $table_feeds WHERE status = 'active'");
        
        foreach ($feeds as $feed) {
            $should_import = false;
            $last_import = strtotime($feed->last_import);
            $current_time = current_time('timestamp');
            
            switch ($feed->import_frequency) {
                case 'hourly':
                    $should_import = ($current_time - $last_import) >= 3600;
                    break;
                case 'daily':
                    $should_import = ($current_time - $last_import) >= 86400;
                    break;
                case 'weekly':
                    $should_import = ($current_time - $last_import) >= 604800;
                    break;
            }
            
            if ($should_import || empty($feed->last_import)) {
                $this->import_feed($feed->id);
            }
        }
    }
}

// Inizializza il plugin
new RSSFeedImporter();