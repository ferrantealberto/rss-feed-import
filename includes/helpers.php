<?php
/**
 * Funzioni helper e utilità per il plugin RSS Feed Importer
 * Path: includes/helpers.php
 */

// Impedire l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

class RSSImporterHelpers {
    
    /**
     * Controlla se l'URL è raggiungibile
     */
    public static function is_url_reachable($url, $timeout = 10) {
        $response = wp_remote_head($url, array(
            'timeout' => $timeout,
            'redirection' => 5,
            'user-agent' => 'RSS Feed Importer/' . RSS_IMPORTER_VERSION
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        return $status_code >= 200 && $status_code < 400;
    }
    
    /**
     * Sanitizza e valida un URL RSS
     */
    public static function sanitize_rss_url($url) {
        $url = trim($url);
        
        // Aggiungi http:// se manca il protocollo
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'http://' . $url;
        }
        
        // Sanitizza l'URL
        $url = esc_url_raw($url);
        
        // Verifica che sia un URL valido
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        return $url;
    }
    
    /**
     * Estrae e pulisce il contenuto HTML
     */
    public static function clean_html_content($content) {
        // Rimuovi shortcode WordPress
        $content = strip_shortcodes($content);
        
        // Rimuovi tag script e style
        $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content);
        $content = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $content);
        
        // Rimuovi commenti HTML
        $content = preg_replace('/<!--.*?-->/s', '', $content);
        
        // Pulisci spazi extra
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        return $content;
    }
    
    /**
     * Rielabora il contenuto usando OpenRouter
     */
    public static function rewrite_content($content, $settings) {
        if (empty($settings['openrouter_api_key']) || empty($settings['ai_model'])) {
            return false;
        }
        
        $tone = $settings['rewrite_tone'] ?? 'professional';
        
        $prompt = "Rielabora il seguente testo in stile $tone, mantenendo le informazioni chiave ma rendendolo unico:\n\n$content";
        
        $response = wp_remote_post('https://openrouter.ai/api/v1/chat/completions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $settings['openrouter_api_key'],
                'Content-Type' => 'application/json',
                'HTTP-Referer' => home_url(),
                'X-Title' => 'RSS Feed Importer'
            ),
            'body' => json_encode(array(
                'model' => $settings['ai_model'],
                'messages' => array(
                    array(
                        'role' => 'user',
                        'content' => $prompt
                    )
                ),
                'temperature' => 0.7,
                'max_tokens' => 2000
            ))
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (empty($body['choices'][0]['message']['content'])) {
            return false;
        }
        
        return $body['choices'][0]['message']['content'];
    }
    
    /**
     * Genera uno slug unico per una categoria
     */
    public static function generate_unique_category_slug($name, $parent = 0) {
        $slug = sanitize_title($name);
        $original_slug = $slug;
        $counter = 1;
        
        while (term_exists($slug, 'category', $parent)) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Estrae le parole chiave dal testo
     */
    public static function extract_keywords($text, $max_keywords = 10) {
        // Pulisci il testo
        $text = self::clean_html_content($text);
        $text = strtolower($text);
        $text = preg_replace('/[^\w\s]/', ' ', $text);
        
        // Parole da ignorare (stop words)
        $stop_words = self::get_stop_words();
        
        // Dividi in parole
        $words = explode(' ', $text);
        $words = array_filter($words, function($word) use ($stop_words) {
            return strlen($word) > 2 && !in_array($word, $stop_words) && !is_numeric($word);
        });
        
        // Conta le occorrenze
        $word_count = array_count_values($words);
        arsort($word_count);
        
        // Restituisci le parole più frequenti
        return array_slice(array_keys($word_count), 0, $max_keywords);
    }
    
    /**
     * Restituisce un array di stop words
     */
    public static function get_stop_words() {
        return array(
            // Italiano
            'il', 'lo', 'la', 'le', 'gli', 'i', 'un', 'una', 'uno', 'di', 'da', 'del', 'dello', 'della',
            'dei', 'degli', 'delle', 'in', 'su', 'per', 'con', 'tra', 'fra', 'a', 'ad', 'al', 'allo',
            'alla', 'ai', 'agli', 'alle', 'e', 'ed', 'o', 'od', 'ma', 'però', 'quindi', 'inoltre',
            'che', 'chi', 'cui', 'dove', 'quando', 'come', 'perché', 'se', 'non', 'più', 'molto',
            'tutto', 'tutti', 'questa', 'questo', 'questi', 'queste', 'quella', 'quello', 'quelli',
            'quelle', 'essere', 'avere', 'fare', 'dire', 'andare', 'vedere', 'sapere', 'dare',
            'stare', 'volere', 'dovere', 'potere', 'sono', 'è', 'ha', 'hanno', 'sia', 'sia',
            
            // Inglese
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by',
            'from', 'up', 'about', 'into', 'through', 'during', 'before', 'after', 'above', 'below',
            'between', 'among', 'this', 'that', 'these', 'those', 'is', 'are', 'was', 'were', 'be',
            'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could',
            'should', 'may', 'might', 'must', 'can', 'i', 'you', 'he', 'she', 'it', 'we', 'they'
        );
    }
    
    /**
     * Formatta la data per la visualizzazione
     */
    public static function format_date($date, $format = null) {
        if (!$format) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }
        
        if (empty($date) || $date === '0000-00-00 00:00:00') {
            return __('Mai', 'rss-feed-importer');
        }
        
        return mysql2date($format, $date);
    }
    
    /**
     * Calcola il tempo relativo (es. "2 ore fa")
     */
    public static function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
        
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;
        
        $string = array(
            'y' => 'anno',
            'm' => 'mese',
            'w' => 'settimana',
            'd' => 'giorno',
            'h' => 'ora',
            'i' => 'minuto',
            's' => 'secondo',
        );
        
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? ($k == 'i' ? '' : 'i') : '');
            } else {
                unset($string[$k]);
            }
        }
        
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' fa' : 'adesso';
    }
    
    /**
     * Controlla se il contenuto contiene spam
     */
    public static function is_spam_content($title, $content) {
        $spam_indicators = array(
            // Pattern comuni di spam
            '/\b(viagra|cialis|casino|poker|lottery|winner|congratulations)\b/i',
            '/\b(click here|free money|guaranteed|urgent|act now)\b/i',
            '/\$\d+\s*(million|billion|thousand)/i',
            '/(!!!|!!!)/', // Troppi punti esclamativi
            '/[A-Z]{10,}/', // Troppe maiuscole consecutive
        );
        
        $text = $title . ' ' . $content;
        
        foreach ($spam_indicators as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Valida e scarica un'immagine da un URL
     */
    public static function download_image($image_url, $timeout = 30) {
        // Valida URL
        if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
            return new WP_Error('invalid_url', 'URL immagine non valido');
        }
        
        // Applica filtro all'URL
        $image_url = apply_filters(RSS_IMPORTER_FILTER_IMAGE_URL, $image_url);
        
        // Download dell'immagine
        $response = wp_remote_get($image_url, array(
            'timeout' => $timeout,
            'user-agent' => rss_importer_get_random_user_agent(),
            'headers' => array(
                'Accept' => 'image/*'
            )
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            return new WP_Error('download_failed', sprintf('Download fallito con codice %d', $status_code));
        }
        
        $image_data = wp_remote_retrieve_body($response);
        $content_type = wp_remote_retrieve_header($response, 'content-type');
        
        // Verifica tipo di contenuto
        if (!rss_importer_is_supported_image_type($content_type)) {
            return new WP_Error('unsupported_type', 'Tipo di immagine non supportato: ' . $content_type);
        }
        
        // Verifica dimensione
        $image_size = strlen($image_data);
        if ($image_size > RSS_IMPORTER_IMAGE_MAX_SIZE) {
            return new WP_Error('file_too_large', 'Immagine troppo grande: ' . size_format($image_size));
        }
        
        // Applica filtro alla dimensione
        $image_size = apply_filters(RSS_IMPORTER_FILTER_IMAGE_SIZE, $image_size, $image_url);
        
        return array(
            'data' => $image_data,
            'mime_type' => $content_type,
            'size' => $image_size
        );
    }
    
    /**
     * Salva un'immagine nella libreria media di WordPress
     */
    public static function save_image_to_media_library($image_data, $filename, $post_id = 0) {
        if (!function_exists('wp_upload_bits')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        // Upload del file
        $upload = wp_upload_bits($filename, null, $image_data['data']);
        
        if ($upload['error']) {
            return new WP_Error('upload_failed', $upload['error']);
        }
        
        // Prepara i dati per l'attachment
        $attachment = array(
            'post_mime_type' => $image_data['mime_type'],
            'post_title' => sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME)),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        // Inserisci l'attachment nel database
        $attachment_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
        
        if (is_wp_error($attachment_id)) {
            // Rimuovi il file se l'inserimento fallisce
            @unlink($upload['file']);
            return $attachment_id;
        }
        
        // Genera i metadati dell'immagine
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        
        return $attachment_id;
    }
    
    /**
     * Ottimizza un'immagine dopo l'upload
     */
    public static function optimize_image($attachment_id) {
        $image_path = get_attached_file($attachment_id);
        
        if (!$image_path || !file_exists($image_path)) {
            return false;
        }
        
        $image_editor = wp_get_image_editor($image_path);
        
        if (is_wp_error($image_editor)) {
            return false;
        }
        
        $image_size = $image_editor->get_size();
        $max_width = RSS_IMPORTER_IMAGE_MAX_WIDTH;
        $max_height = RSS_IMPORTER_IMAGE_MAX_HEIGHT;
        
        // Ridimensiona se necessario
        if ($image_size['width'] > $max_width || $image_size['height'] > $max_height) {
            $image_editor->resize($max_width, $max_height, false);
        }
        
        // Imposta qualità
        $image_editor->set_quality(RSS_IMPORTER_IMAGE_QUALITY);
        
        // Salva l'immagine ottimizzata
        $result = $image_editor->save();
        
        if (is_wp_error($result)) {
            return false;
        }
        
        // Rigenera i metadati
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $result['path']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        
        return true;
    }
    
    /**
     * Estrae l'URL dell'immagine da un elemento RSS
     */
    public static function extract_image_from_rss_item($item) {
        $image_urls = array();
        
        // 1. Controlla enclosure (RSS standard)
        if (isset($item->enclosure)) {
            $enclosure = $item->enclosure;
            $type = (string)$enclosure['type'];
            if (strpos($type, 'image/') === 0) {
                $image_urls[] = array(
                    'url' => (string)$enclosure['url'],
                    'type' => $type,
                    'source' => 'enclosure',
                    'priority' => 1
                );
            }
        }
        
        // 2. Controlla media:content (Media RSS)
        if (isset($item->children('media', true)->content)) {
            foreach ($item->children('media', true)->content as $media) {
                $type = (string)$media['type'];
                if (strpos($type, 'image/') === 0) {
                    $image_urls[] = array(
                        'url' => (string)$media['url'],
                        'type' => $type,
                        'source' => 'media:content',
                        'priority' => 2
                    );
                }
            }
        }
        
        // 3. Controlla media:thumbnail
        if (isset($item->children('media', true)->thumbnail)) {
            $thumbnail = $item->children('media', true)->thumbnail;
            $image_urls[] = array(
                'url' => (string)$thumbnail['url'],
                'type' => 'image/jpeg', // Assunto
                'source' => 'media:thumbnail',
                'priority' => 3
            );
        }
        
        // 4. Cerca immagini nel contenuto HTML
        $content_fields = array('description', 'content:encoded');
        foreach ($content_fields as $field) {
            $content = '';
            if ($field === 'content:encoded') {
                if (isset($item->children('content', true)->encoded)) {
                    $content = (string)$item->children('content', true)->encoded;
                }
            } else {
                if (isset($item->$field)) {
                    $content = (string)$item->$field;
                }
            }
            
            if ($content) {
                preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $img_url) {
                        $image_urls[] = array(
                            'url' => $img_url,
                            'type' => 'image/jpeg', // Assunto
                            'source' => 'content_html',
                            'priority' => 4
                        );
                    }
                }
            }
        }
        
        // Ordina per priorità e restituisci la migliore
        if (!empty($image_urls)) {
            usort($image_urls, function($a, $b) {
                return $a['priority'] - $b['priority'];
            });
            
            return $image_urls[0];
        }
        
        return null;
    }
    
    /**
     * Genera un nome file unico per l'immagine
     */
    public static function generate_image_filename($post_id, $original_url, $mime_type) {
        $extension = rss_importer_get_image_extension($mime_type);
        return rss_importer_generate_image_filename($post_id, $original_url, $extension);
    }
    
    /**
     * Imposta l'immagine in evidenza per un post
     */
    public static function set_featured_image_from_url($post_id, $image_url) {
        // Scarica l'immagine
        $image_data = self::download_image($image_url);
        
        if (is_wp_error($image_data)) {
            return $image_data;
        }
        
        // Genera nome file
        $filename = self::generate_image_filename($post_id, $image_url, $image_data['mime_type']);
        
        // Salva nella libreria media
        $attachment_id = self::save_image_to_media_library($image_data, $filename, $post_id);
        
        if (is_wp_error($attachment_id)) {
            return $attachment_id;
        }
        
        // Ottimizza l'immagine
        self::optimize_image($attachment_id);
        
        // Imposta come immagine in evidenza
        $result = set_post_thumbnail($post_id, $attachment_id);
        
        if (!$result) {
            return new WP_Error('thumbnail_failed', 'Impossibile impostare l\'immagine in evidenza');
        }
        
        // Trigger hook per immagine importata
        do_action(RSS_IMPORTER_HOOK_IMAGE_IMPORTED, $post_id, $attachment_id, $image_url);
        
        return $attachment_id;
    }
    
    /**
     * Imposta un'immagine predefinita come immagine in evidenza
     */
    public static function set_default_featured_image($post_id, $default_image_id) {
        if (!$default_image_id || !get_post($default_image_id)) {
            return false;
        }
        
        $result = set_post_thumbnail($post_id, $default_image_id);
        
        if ($result) {
            // Trigger hook per immagine predefinita
            do_action(RSS_IMPORTER_HOOK_IMAGE_IMPORTED, $post_id, $default_image_id, 'default');
        }
        
        return $result;
    }
    
    /**
     * Ridimensiona un'immagine (mantenuto per compatibilità)
     */
    public static function resize_image($image_url, $width = 300, $height = 200) {
        $upload_dir = wp_upload_dir();
        $image_data = wp_remote_get($image_url);
        
        if (is_wp_error($image_data)) {
            return false;
        }
        
        $image_content = wp_remote_retrieve_body($image_data);
        $image_name = basename($image_url);
        $image_path = $upload_dir['path'] . '/' . $image_name;
        
        // Salva l'immagine
        file_put_contents($image_path, $image_content);
        
        // Ridimensiona
        $image_editor = wp_get_image_editor($image_path);
        if (!is_wp_error($image_editor)) {
            $image_editor->resize($width, $height, true);
            $resized = $image_editor->save();
            
            if (!is_wp_error($resized)) {
                return $upload_dir['url'] . '/' . basename($resized['path']);
            }
        }
        
        return $upload_dir['url'] . '/' . $image_name;
    }
    
    /**
     * Registra un log del plugin
     */
    public static function log($message, $level = 'info') {
        if (!WP_DEBUG) {
            return;
        }
        
        $log_entry = sprintf(
            '[%s] RSS Importer [%s]: %s',
            current_time('Y-m-d H:i:s'),
            strtoupper($level),
            $message
        );
        
        error_log($log_entry);
        
        // Opzionalmente, salva in un file di log personalizzato
        $log_file = WP_CONTENT_DIR . '/rss-importer.log';
        file_put_contents($log_file, $log_entry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Invia una notifica email agli amministratori
     */
    public static function send_admin_notification($subject, $message, $level = 'info') {
        $settings = get_option('rss_importer_settings', array());
        
        if (!isset($settings['email_notifications']) || !$settings['email_notifications']) {
            return;
        }
        
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $full_subject = sprintf('[%s] RSS Importer: %s', $site_name, $subject);
        
        $full_message = sprintf(
            "Ciao,\n\nIl plugin RSS Feed Importer ha un aggiornamento:\n\n%s\n\nLivello: %s\nData: %s\n\nSito: %s\nURL: %s",
            $message,
            strtoupper($level),
            current_time('Y-m-d H:i:s'),
            $site_name,
            home_url()
        );
        
        wp_mail($admin_email, $full_subject, $full_message);
    }
    
    /**
     * Controlla gli aggiornamenti del plugin
     */
    public static function check_plugin_updates() {
        $current_version = RSS_IMPORTER_VERSION;
        $stored_version = get_option('rss_importer_version', '0.0.0');
        
        if (version_compare($current_version, $stored_version, '>')) {
            // Esegui aggiornamenti se necessario
            self::perform_plugin_updates($stored_version, $current_version);
            update_option('rss_importer_version', $current_version);
        }
    }
    
    /**
     * Esegue gli aggiornamenti del plugin
     */
    private static function perform_plugin_updates($from_version, $to_version) {
        self::log("Aggiornamento plugin da $from_version a $to_version", 'info');
        
        // Aggiornamenti specifici per versione
        if (version_compare($from_version, '1.1.0', '<')) {
            // Aggiornamenti per la versione 1.1.0
            self::update_to_1_1_0();
        }
        
        // Pulisci la cache
        wp_cache_flush();
        
        self::log("Aggiornamento completato", 'info');
    }
    
    /**
     * Aggiornamenti specifici per la versione 1.1.0
     */
    private static function update_to_1_1_0() {
        global $wpdb;
        
        // Aggiungi colonne per supporto immagini se non esistono
        $table_imports = $wpdb->prefix . RSS_IMPORTER_TABLE_IMPORTS;
        
        $columns_to_add = array(
            'featured_image_imported' => 'tinyint(1) DEFAULT 0',
            'featured_image_url' => 'text'
        );
        
        foreach ($columns_to_add as $column_name => $column_definition) {
            $column_exists = $wpdb->get_results(
                $wpdb->prepare("SHOW COLUMNS FROM $table_imports LIKE %s", $column_name)
            );
            
            if (empty($column_exists)) {
                $wpdb->query("ALTER TABLE $table_imports ADD COLUMN $column_name $column_definition");
                self::log("Aggiunta colonna $column_name alla tabella imports", 'info');
            }
        }
    }
    
    /**
     * Genera un hash unico per il contenuto
     */
    public static function generate_content_hash($title, $content, $url) {
        return md5($title . $content . $url);
    }
    
    /**
     * Verifica se il sistema supporta tutte le funzionalità
     */
    public static function check_system_requirements() {
        $requirements = array();
        
        // Verifica PHP
        $requirements['php_version'] = array(
            'required' => '7.4',
            'current' => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '7.4', '>=')
        );
        
        // Verifica estensioni PHP
        $required_extensions = array('simplexml', 'curl', 'json', 'gd');
        foreach ($required_extensions as $ext) {
            $requirements['extension_' . $ext] = array(
                'required' => true,
                'current' => extension_loaded($ext),
                'status' => extension_loaded($ext)
            );
        }
        
        // Verifica WordPress
        $requirements['wp_version'] = array(
            'required' => '5.0',
            'current' => get_bloginfo('version'),
            'status' => version_compare(get_bloginfo('version'), '5.0', '>=')
        );
        
        // Verifica memory limit
        $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        $requirements['memory_limit'] = array(
            'required' => '128M',
            'current' => size_format($memory_limit),
            'status' => $memory_limit >= wp_convert_hr_to_bytes('128M')
        );
        
        // Verifica supporto immagini
        $image_requirements = rss_importer_check_image_support();
        $requirements = array_merge($requirements, $image_requirements);
        
        return $requirements;
    }
    
    /**
     * Ottiene le statistiche del plugin
     */
    public static function get_plugin_stats() {
        global $wpdb;
        
        $table_feeds = $wpdb->prefix . RSS_IMPORTER_TABLE_FEEDS;
        $table_imports = $wpdb->prefix . RSS_IMPORTER_TABLE_IMPORTS;
        
        $stats = array();
        
        // Numero di feed
        $stats['total_feeds'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_feeds");
        $stats['active_feeds'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_feeds WHERE status = 'active'");
        
        // Numero di importazioni
        $stats['total_imports'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_imports");
        $stats['successful_imports'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_imports WHERE status = 'success'");
        $stats['failed_imports'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_imports WHERE status = 'error'");
        
        // Statistiche immagini
        $image_stats = rss_importer_get_image_stats();
        $stats = array_merge($stats, $image_stats);
        
        // Importazioni per periodo
        $stats['imports_today'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_imports WHERE DATE(import_date) = CURDATE()");
        $stats['imports_this_week'] = $wpdb->get_var("SELECT COUNT