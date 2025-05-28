<?php
/**
 * Costanti e configurazioni del plugin RSS Feed Importer
 * Path: includes/constants.php
 */

// Impedire l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Versione database plugin
define('RSS_IMPORTER_DB_VERSION', '1.0');

// Limiti di sistema
define('RSS_IMPORTER_MAX_FEEDS_PER_USER', 50);
define('RSS_IMPORTER_MAX_POSTS_PER_IMPORT', 100);
define('RSS_IMPORTER_DEFAULT_TIMEOUT', 30);
define('RSS_IMPORTER_MIN_IMPORT_INTERVAL', 300); // 5 minuti

// Configurazioni cache
define('RSS_IMPORTER_CACHE_DURATION', 3600); // 1 ora
define('RSS_IMPORTER_VALIDATION_CACHE_DURATION', 1800); // 30 minuti

// Configurazioni sicurezza
define('RSS_IMPORTER_MAX_TITLE_LENGTH', 250);
define('RSS_IMPORTER_MAX_CONTENT_LENGTH', 50000);
define('RSS_IMPORTER_ALLOWED_HTML_TAGS', array(
    'p', 'br', 'strong', 'em', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
    'ul', 'ol', 'li', 'blockquote', 'a', 'img'
));

// Configurazioni email
define('RSS_IMPORTER_EMAIL_NOTIFICATIONS', false);
define('RSS_IMPORTER_EMAIL_FREQUENCY', 'daily');

// Log levels
define('RSS_IMPORTER_LOG_LEVEL_ERROR', 'error');
define('RSS_IMPORTER_LOG_LEVEL_WARNING', 'warning');
define('RSS_IMPORTER_LOG_LEVEL_INFO', 'info');
define('RSS_IMPORTER_LOG_LEVEL_DEBUG', 'debug');

// User agents per le richieste HTTP
define('RSS_IMPORTER_USER_AGENTS', array(
    'Mozilla/5.0 (compatible; RSS Feed Importer/' . RSS_IMPORTER_VERSION . '; WordPress)',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    'Mozilla/5.0 (compatible; Feedbot/1.0; +http://example.com/feedbot)'
));

// Formati feed supportati
define('RSS_IMPORTER_SUPPORTED_FORMATS', array(
    'application/rss+xml',
    'application/xml',
    'text/xml',
    'application/atom+xml',
    'application/rdf+xml'
));

// Pattern per l'estrazione di parole chiave
define('RSS_IMPORTER_KEYWORD_PATTERNS', array(
    'common_words' => '/\b(il|lo|la|le|gli|i|un|una|uno|di|da|del|dello|della|dei|degli|delle|in|su|per|con|tra|fra|a|ad|al|allo|alla|ai|agli|alle|e|ed|o|od|ma|però|quindi|inoltre|the|a|an|and|or|but|in|on|at|to|for|of|with|by)\b/i',
    'numbers' => '/\b\d+\b/',
    'short_words' => '/\b\w{1,2}\b/',
    'special_chars' => '/[^\w\s]/u'
));

// Configurazioni automatiche categorie/tag
define('RSS_IMPORTER_MAX_AUTO_CATEGORIES', 3);
define('RSS_IMPORTER_MAX_AUTO_TAGS', 8);
define('RSS_IMPORTER_MIN_KEYWORD_LENGTH', 3);
define('RSS_IMPORTER_MIN_KEYWORD_FREQUENCY', 2);

// Configurazioni backup e manutenzione
define('RSS_IMPORTER_CLEANUP_LOGS_AFTER_DAYS', 30);
define('RSS_IMPORTER_BACKUP_RETENTION_DAYS', 7);
define('RSS_IMPORTER_AUTO_CLEANUP_ENABLED', true);

// Hook actions personalizzate
define('RSS_IMPORTER_HOOK_BEFORE_IMPORT', 'rss_importer_before_import');
define('RSS_IMPORTER_HOOK_AFTER_IMPORT', 'rss_importer_after_import');
define('RSS_IMPORTER_HOOK_POST_CREATED', 'rss_importer_post_created');
define('RSS_IMPORTER_HOOK_IMPORT_ERROR', 'rss_importer_import_error');

// Filtri personalizzati
define('RSS_IMPORTER_FILTER_POST_TITLE', 'rss_importer_post_title');
define('RSS_IMPORTER_FILTER_POST_CONTENT', 'rss_importer_post_content');
define('RSS_IMPORTER_FILTER_POST_STATUS', 'rss_importer_post_status');
define('RSS_IMPORTER_FILTER_CATEGORIES', 'rss_importer_categories');
define('RSS_IMPORTER_FILTER_TAGS', 'rss_importer_tags');

// Configurazioni performance
define('RSS_IMPORTER_MEMORY_LIMIT', '256M');
define('RSS_IMPORTER_MAX_EXECUTION_TIME', 300); // 5 minuti
define('RSS_IMPORTER_BATCH_SIZE', 10);

// Configurazioni API (per future estensioni)
define('RSS_IMPORTER_API_VERSION', 'v1');
define('RSS_IMPORTER_API_NAMESPACE', 'rss-importer/v1');

/**
 * Funzione per ottenere le impostazioni predefinite
 */
function rss_importer_get_default_settings() {
    return array(
        'max_posts_per_import' => 10,
        'duplicate_check_method' => 'title_url',
        'default_post_status' => 'draft',
        'category_creation_method' => 'auto',
        'tag_creation_method' => 'auto',
        'image_import' => 1,
        'excerpt_length' => 150,
        'timeout' => RSS_IMPORTER_DEFAULT_TIMEOUT,
        'user_agent_rotation' => true,
        'cache_feed_validation' => true,
        'auto_cleanup_logs' => RSS_IMPORTER_AUTO_CLEANUP_ENABLED,
        'email_notifications' => RSS_IMPORTER_EMAIL_NOTIFICATIONS,
        'log_level' => RSS_IMPORTER_LOG_LEVEL_INFO,
        'strip_shortcodes' => true,
        'sanitize_html' => true,
        'limit_external_links' => false,
        'max_auto_categories' => RSS_IMPORTER_MAX_AUTO_CATEGORIES,
        'max_auto_tags' => RSS_IMPORTER_MAX_AUTO_TAGS,
        'min_keyword_length' => RSS_IMPORTER_MIN_KEYWORD_LENGTH
    );
}

/**
 * Funzione per ottenere un User-Agent casuale
 */
function rss_importer_get_random_user_agent() {
    $user_agents = RSS_IMPORTER_USER_AGENTS;
    return $user_agents[array_rand($user_agents)];
}

/**
 * Funzione per verificare se un formato feed è supportato
 */
function rss_importer_is_supported_format($content_type) {
    $supported_formats = RSS_IMPORTER_SUPPORTED_FORMATS;
    
    foreach ($supported_formats as $format) {
        if (strpos($content_type, $format) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Funzione per ottenere i limiti correnti del sistema
 */
function rss_importer_get_system_limits() {
    return array(
        'max_feeds' => RSS_IMPORTER_MAX_FEEDS_PER_USER,
        'max_posts_per_import' => RSS_IMPORTER_MAX_POSTS_PER_IMPORT,
        'timeout' => RSS_IMPORTER_DEFAULT_TIMEOUT,
        'memory_limit' => wp_convert_hr_to_bytes(RSS_IMPORTER_MEMORY_LIMIT),
        'max_execution_time' => RSS_IMPORTER_MAX_EXECUTION_TIME,
        'min_import_interval' => RSS_IMPORTER_MIN_IMPORT_INTERVAL
    );
}

/**
 * Funzione per validare le impostazioni del plugin
 */
function rss_importer_validate_settings($settings) {
    $defaults = rss_importer_get_default_settings();
    $limits = rss_importer_get_system_limits();
    
    // Valida max_posts_per_import
    if (isset($settings['max_posts_per_import'])) {
        $settings['max_posts_per_import'] = min(
            max(1, intval($settings['max_posts_per_import'])), 
            $limits['max_posts_per_import']
        );
    }
    
    // Valida timeout
    if (isset($settings['timeout'])) {
        $settings['timeout'] = min(
            max(5, intval($settings['timeout'])), 
            $limits['timeout']
        );
    }
    
    // Valida excerpt_length
    if (isset($settings['excerpt_length'])) {
        $settings['excerpt_length'] = min(
            max(50, intval($settings['excerpt_length'])), 
            500
        );
    }
    
    // Merge con i defaults per i valori mancanti
    return array_merge($defaults, array_intersect_key($settings, $defaults));
}

/**
 * Funzione per ottenere le informazioni di debug del sistema
 */
function rss_importer_get_debug_info() {
    return array(
        'plugin_version' => RSS_IMPORTER_VERSION,
        'db_version' => RSS_IMPORTER_DB_VERSION,
        'wp_version' => get_bloginfo('version'),
        'php_version' => PHP_VERSION,
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'curl_version' => curl_version()['version'] ?? 'N/A',
        'simplexml_loaded' => extension_loaded('simplexml'),
        'json_loaded' => extension_loaded('json'),
        'wp_cron_disabled' => defined('DISABLE_WP_CRON') && DISABLE_WP_CRON,
        'wp_debug' => defined('WP_DEBUG') && WP_DEBUG,
        'wp_cache' => defined('WP_CACHE') && WP_CACHE
    );
}