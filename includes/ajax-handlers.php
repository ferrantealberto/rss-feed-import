<?php
/**
 * Gestori AJAX aggiuntivi per il plugin RSS Feed Importer
 * Path: includes/ajax-handlers.php
 */

// Impedire l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

class RSSImporterAjaxHandlers {
    
    public function __construct() {
        add_action('wp_ajax_rss_importer_maintenance', array($this, 'handle_maintenance_action'));
        add_action('wp_ajax_rss_importer_bulk_action', array($this, 'handle_bulk_action'));
        add_action('wp_ajax_rss_importer_export_data', array($this, 'handle_export_data'));
        add_action('wp_ajax_rss_importer_get_feed_preview', array($this, 'handle_feed_preview'));
    }
    
    /**
     * Gestisce le azioni di manutenzione
     */
    public function handle_maintenance_action() {
        check_ajax_referer('rss_importer_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permessi insufficienti', 'rss-feed-importer'));
        }
        
        $action = sanitize_text_field($_POST['maintenance_action']);
        
        switch ($action) {
            case 'clear_logs':
                $result = $this->clear_old_logs();
                break;
                
            case 'reset_stats':
                $result = $this->reset_feed_stats();
                break;
                
            case 'force_cron':
                $result = $this->force_cron_execution();
                break;
                
            default:
                wp_send_json_error(__('Azione non riconosciuta', 'rss-feed-importer'));
        }
        
        if ($result['success']) {
            wp_send_json_success($result['message']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * Pulisce i log di importazione più vecchi di 30 giorni
     */
    private function clear_old_logs() {
        global $wpdb;
        
        $table_imports = $wpdb->prefix . RSS_IMPORTER_TABLE_IMPORTS;
        $cutoff_date = date('Y-m-d H:i:s', strtotime('-30 days'));
        
        $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_imports WHERE import_date < %s",
            $cutoff_date
        ));
        
        if ($deleted === false) {
            return array(
                'success' => false,
                'message' => __('Errore durante la pulizia dei log', 'rss-feed-importer')
            );
        }
        
        return array(
            'success' => true,
            'message' => sprintf(__('Eliminati %d log di importazione', 'rss-feed-importer'), $deleted)
        );
    }
    
    /**
     * Reimposta le statistiche dei feed
     */
    private function reset_feed_stats() {
        global $wpdb;
        
        $table_feeds = $wpdb->prefix . RSS_IMPORTER_TABLE_FEEDS;
        
        $updated = $wpdb->query(
            "UPDATE $table_feeds SET total_imported = 0, last_import = NULL"
        );
        
        if ($updated === false) {
            return array(
                'success' => false,
                'message' => __('Errore durante il reset delle statistiche', 'rss-feed-importer')
            );
        }
        
        return array(
            'success' => true,
            'message' => __('Statistiche reimpostate con successo', 'rss-feed-importer')
        );
    }
    
    /**
     * Forza l'esecuzione del cron di importazione
     */
    private function force_cron_execution() {
        try {
            // Esegue l'azione del cron manualmente
            do_action('rss_importer_scheduled_import');
            
            return array(
                'success' => true,
                'message' => __('Importazione programmata eseguita con successo', 'rss-feed-importer')
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => sprintf(__('Errore durante l\'esecuzione: %s', 'rss-feed-importer'), $e->getMessage())
            );
        }
    }
    
    /**
     * Gestisce le azioni in blocco sui post
     */
    public function handle_bulk_action() {
        check_ajax_referer('rss_importer_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Permessi insufficienti', 'rss-feed-importer'));
        }
        
        $action = sanitize_text_field($_POST['bulk_action']);
        $post_ids = array_map('intval', $_POST['post_ids']);
        
        if (empty($post_ids)) {
            wp_send_json_error(__('Nessun post selezionato', 'rss-feed-importer'));
        }
        
        $results = array(
            'success' => 0,
            'errors' => 0,
            'messages' => array()
        );
        
        foreach ($post_ids as $post_id) {
            $result = $this->execute_bulk_action($action, $post_id);
            if ($result) {
                $results['success']++;
            } else {
                $results['errors']++;
                $results['messages'][] = sprintf(__('Errore sul post ID %d', 'rss-feed-importer'), $post_id);
            }
        }
        
        $message = sprintf(
            __('%d post aggiornati con successo, %d errori', 'rss-feed-importer'),
            $results['success'],
            $results['errors']
        );
        
        if (!empty($results['messages'])) {
            $message .= '. ' . implode(', ', array_slice($results['messages'], 0, 3));
        }
        
        wp_send_json_success(array(
            'message' => $message,
            'details' => $results
        ));
    }
    
    /**
     * Esegue un'azione singola su un post
     */
    private function execute_bulk_action($action, $post_id) {
        switch ($action) {
            case 'publish':
                return wp_update_post(array(
                    'ID' => $post_id,
                    'post_status' => 'publish'
                )) !== 0;
                
            case 'draft':
                return wp_update_post(array(
                    'ID' => $post_id,
                    'post_status' => 'draft'
                )) !== 0;
                
            case 'trash':
                return wp_trash_post($post_id) !== false;
                
            default:
                return false;
        }
    }
    
    /**
     * Gestisce l'esportazione dei dati
     */
    public function handle_export_data() {
        check_ajax_referer('rss_importer_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permessi insufficienti', 'rss-feed-importer'));
        }
        
        $format = sanitize_text_field($_POST['export_format']);
        $date_range = sanitize_text_field($_POST['export_date_range']);
        
        $data = $this->get_export_data($date_range);
        
        if (empty($data)) {
            wp_send_json_error(__('Nessun dato da esportare', 'rss-feed-importer'));
        }
        
        switch ($format) {
            case 'csv':
                $file_data = $this->generate_csv($data);
                $filename = 'rss-import-data-' . date('Y-m-d') . '.csv';
                $content_type = 'text/csv';
                break;
                
            case 'json':
                $file_data = json_encode($data, JSON_PRETTY_PRINT);
                $filename = 'rss-import-data-' . date('Y-m-d') . '.json';
                $content_type = 'application/json';
                break;
                
            default:
                wp_send_json_error(__('Formato non supportato', 'rss-feed-importer'));
        }
        
        // Invia il file per il download
        header('Content-Type: ' . $content_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($file_data));
        
        echo $file_data;
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
                i.categories_created,
                i.tags_created,
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
    
    /**
     * Genera file CSV dai dati
     */
    private function generate_csv($data) {
        if (empty($data)) {
            return '';
        }
        
        $output = fopen('php://temp', 'r+');
        
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
            'Data Pubblicazione',
            'Categorie Create',
            'Tag Creati'
        ));
        
        // Dati
        foreach ($data as $row) {
            $categories = json_decode($row['categories_created'], true);
            $tags = json_decode($row['tags_created'], true);
            
            fputcsv($output, array(
                $row['id'],
                $row['feed_name'],
                $row['original_title'],
                $row['original_url'],
                $row['import_date'],
                $row['status'],
                $row['post_title'],
                $row['post_status'],
                $row['post_date'],
                is_array($categories) ? implode(', ', $categories) : '',
                is_array($tags) ? implode(', ', $tags) : ''
            ));
        }
        
        rewind($output);
        $csv_data = stream_get_contents($output);
        fclose($output);
        
        return $csv_data;
    }
    
    /**
     * Gestisce l'anteprima del feed
     */
    public function handle_feed_preview() {
        check_ajax_referer('rss_importer_nonce', 'nonce');
        
        $url = esc_url_raw($_POST['url']);
        $limit = intval($_POST['limit']) ?: 5;
        
        if (!$url) {
            wp_send_json_error(__('URL non valido', 'rss-feed-importer'));
        }
        
        $response = wp_remote_get($url, array('timeout' => 30));
        
        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $rss = simplexml_load_string($body);
        
        if ($rss === false) {
            wp_send_json_error(__('Feed RSS non valido', 'rss-feed-importer'));
        }
        
        $items = isset($rss->channel->item) ? $rss->channel->item : $rss->item;
        $preview_items = array();
        
        $count = 0;
        foreach ($items as $item) {
            if ($count >= $limit) break;
            
            $preview_items[] = array(
                'title' => (string)$item->title,
                'link' => (string)$item->link,
                'description' => wp_trim_words(strip_tags((string)$item->description), 20),
                'pub_date' => (string)$item->pubDate,
                'categories' => $this->extract_item_categories($item)
            );
            
            $count++;
        }
        
        wp_send_json_success(array(
            'feed_title' => isset($rss->channel->title) ? (string)$rss->channel->title : '',
            'feed_description' => isset($rss->channel->description) ? (string)$rss->channel->description : '',
            'total_items' => count($items),
            'preview_items' => $preview_items
        ));
    }
    
    /**
     * Estrae le categorie da un elemento del feed
     */
    private function extract_item_categories($item) {
        $categories = array();
        
        if (isset($item->category)) {
            foreach ($item->category as $category) {
                $categories[] = (string)$category;
            }
        }
        
        return $categories;
    }
}

// Inizializza i gestori AJAX
new RSSImporterAjaxHandlers();