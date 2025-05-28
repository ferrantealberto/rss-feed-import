<?php
/**
 * Script di disinstallazione del plugin RSS Feed Importer
 * Questo file viene eseguito quando il plugin viene disinstallato
 * Path: uninstall.php
 */

// Se la disinstallazione non è chiamata da WordPress, esci
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Costanti del plugin
define('RSS_IMPORTER_TABLE_FEEDS', 'rss_importer_feeds');
define('RSS_IMPORTER_TABLE_IMPORTS', 'rss_importer_imports');

/**
 * Funzione principale di disinstallazione
 */
function rss_importer_uninstall() {
    global $wpdb;
    
    // Conferma che l'utente ha i permessi necessari
    if (!current_user_can('activate_plugins')) {
        return;
    }
    
    // Verifica che il plugin sia quello giusto
    check_admin_referer('bulk-plugins');
    
    // Rimuovi le tabelle del database
    rss_importer_drop_tables();
    
    // Rimuovi le opzioni dal database
    rss_importer_delete_options();
    
    // Rimuovi i cron jobs programmati
    rss_importer_clear_cron_jobs();
    
    // Rimuovi i metadati dei post
    rss_importer_clean_post_meta();
    
    // Rimuovi i file di cache se esistono
    rss_importer_clean_cache();
    
    // Log della disinstallazione (opzionale)
    error_log('RSS Feed Importer: Plugin disinstallato completamente');
}

/**
 * Rimuove le tabelle del database
 */
function rss_importer_drop_tables() {
    global $wpdb;
    
    $table_feeds = $wpdb->prefix . RSS_IMPORTER_TABLE_FEEDS;
    $table_imports = $wpdb->prefix . RSS_IMPORTER_TABLE_IMPORTS;
    
    // Elimina le tabelle
    $wpdb->query("DROP TABLE IF EXISTS $table_imports");
    $wpdb->query("DROP TABLE IF EXISTS $table_feeds");
    
    // Verifica che le tabelle siano state eliminate
    $tables_remaining = $wpdb->get_results(
        $wpdb->prepare(
            "SHOW TABLES LIKE %s OR SHOW TABLES LIKE %s",
            $table_feeds,
            $table_imports
        )
    );
    
    if (empty($tables_remaining)) {
        error_log('RSS Feed Importer: Tabelle del database rimosse con successo');
    } else {
        error_log('RSS Feed Importer: ATTENZIONE - Alcune tabelle potrebbero non essere state rimosse');
    }
}

/**
 * Rimuove tutte le opzioni del plugin
 */
function rss_importer_delete_options() {
    // Lista delle opzioni da rimuovere
    $options_to_delete = array(
        'rss_importer_settings',
        'rss_importer_version',
        'rss_importer_strip_shortcodes',
        'rss_importer_sanitize_html',
        'rss_importer_limit_external_links',
        'rss_importer_db_version',
        'rss_importer_activation_date',
        'rss_importer_last_cron_run'
    );
    
    $deleted_count = 0;
    foreach ($options_to_delete as $option) {
        if (delete_option($option)) {
            $deleted_count++;
        }
    }
    
    // Rimuovi anche le opzioni con prefisso
    global $wpdb;
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE 'rss_importer_%' 
         OR option_name LIKE '_transient_rss_importer_%'
         OR option_name LIKE '_transient_timeout_rss_importer_%'"
    );
    
    error_log("RSS Feed Importer: Rimosse $deleted_count opzioni del plugin");
}

/**
 * Rimuove i cron jobs programmati
 */
function rss_importer_clear_cron_jobs() {
    // Rimuovi tutti i cron jobs del plugin
    $cron_hooks = array(
        'rss_importer_scheduled_import',
        'rss_importer_cleanup_logs',
        'rss_importer_backup_settings'
    );
    
    $cleared_count = 0;
    foreach ($cron_hooks as $hook) {
        $timestamp = wp_next_scheduled($hook);
        if ($timestamp) {
            wp_unschedule_event($timestamp, $hook);
            $cleared_count++;
        }
        
        // Rimuovi tutti gli eventi di questo hook
        wp_clear_scheduled_hook($hook);
    }
    
    error_log("RSS Feed Importer: Rimossi $cleared_count cron jobs");
}

/**
 * Rimuove i metadati dei post creati dal plugin
 */
function rss_importer_clean_post_meta() {
    global $wpdb;
    
    // Meta keys utilizzati dal plugin
    $meta_keys = array(
        'rss_importer_source_url',
        'rss_importer_feed_id',
        'rss_importer_import_date',
        'rss_importer_original_title',
        'rss_importer_categories_created',
        'rss_importer_tags_created'
    );
    
    $deleted_count = 0;
    foreach ($meta_keys as $meta_key) {
        $deleted = $wpdb->delete(
            $wpdb->postmeta,
            array('meta_key' => $meta_key),
            array('%s')
        );
        
        if ($deleted !== false) {
            $deleted_count += $deleted;
        }
    }
    
    error_log("RSS Feed Importer: Rimossi $deleted_count meta dati dei post");
}

/**
 * Rimuove i file di cache e temporary
 */
function rss_importer_clean_cache() {
    // Directory di cache del plugin
    $cache_dirs = array(
        WP_CONTENT_DIR . '/cache/rss-importer/',
        WP_CONTENT_DIR . '/uploads/rss-importer-temp/',
        WP_CONTENT_DIR . '/rss-importer-logs/'
    );
    
    $deleted_files = 0;
    foreach ($cache_dirs as $dir) {
        if (is_dir($dir)) {
            $deleted_files += rss_importer_delete_directory($dir);
        }
    }
    
    // Rimuovi i transient del plugin
    global $wpdb;
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_rss_feed_cache_%'
         OR option_name LIKE '_transient_timeout_rss_feed_cache_%'"
    );
    
    if ($deleted_files > 0) {
        error_log("RSS Feed Importer: Rimossi $deleted_files file di cache");
    }
}

/**
 * Funzione helper per eliminare ricorsivamente una directory
 */
function rss_importer_delete_directory($dir) {
    if (!is_dir($dir)) {
        return 0;
    }
    
    $files_deleted = 0;
    $files = array_diff(scandir($dir), array('.', '..'));
    
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        
        if (is_dir($path)) {
            $files_deleted += rss_importer_delete_directory($path);
        } else {
            if (unlink($path)) {
                $files_deleted++;
            }
        }
    }
    
    rmdir($dir);
    return $files_deleted;
}

/**
 * Rimuove i post importati (opzionale - commentato per sicurezza)
 * Decommentare solo se si desidera eliminare TUTTI i post importati
 */
function rss_importer_delete_imported_posts() {
    global $wpdb;
    
    // ATTENZIONE: Questa funzione eliminerà TUTTI i post importati dal plugin!
    // Usare con estrema cautela!
    
    /*
    $imported_posts = $wpdb->get_col(
        "SELECT DISTINCT post_id FROM {$wpdb->prefix}" . RSS_IMPORTER_TABLE_IMPORTS . " WHERE post_id IS NOT NULL"
    );
    
    $deleted_count = 0;
    foreach ($imported_posts as $post_id) {
        if (wp_delete_post($post_id, true)) { // true = forza eliminazione permanente
            $deleted_count++;
        }
    }
    
    error_log("RSS Feed Importer: Eliminati $deleted_count post importati");
    */
}

/**
 * Funzione per backup dei dati prima della disinstallazione (opzionale)
 */
function rss_importer_backup_before_uninstall() {
    // Crea un backup delle impostazioni e dei dati prima della rimozione
    $backup_data = array(
        'settings' => get_option('rss_importer_settings'),
        'feeds' => rss_importer_export_feeds_data(),
        'uninstall_date' => current_time('mysql'),
        'wordpress_version' => get_bloginfo('version'),
        'plugin_version' => get_option('rss_importer_version', 'unknown')
    );
    
    // Salva il backup in un file (opzionale)
    $backup_file = WP_CONTENT_DIR . '/rss-importer-backup-' . date('Y-m-d-H-i-s') . '.json';
    file_put_contents($backup_file, json_encode($backup_data, JSON_PRETTY_PRINT));
    
    error_log("RSS Feed Importer: Backup creato in $backup_file");
}

/**
 * Esporta i dati dei feed per il backup
 */
function rss_importer_export_feeds_data() {
    global $wpdb;
    
    $table_feeds = $wpdb->prefix . RSS_IMPORTER_TABLE_FEEDS;
    $table_imports = $wpdb->prefix . RSS_IMPORTER_TABLE_IMPORTS;
    
    $feeds = $wpdb->get_results("SELECT * FROM $table_feeds", ARRAY_A);
    $imports_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_imports");
    
    return array(
        'feeds' => $feeds,
        'total_imports' => $imports_count
    );
}

/**
 * Mostra un messaggio di conferma della disinstallazione
 */
function rss_importer_show_uninstall_message() {
    // Questo messaggio viene mostrato solo nei log, non all'utente
    $message = "
RSS Feed Importer è stato disinstallato completamente.

Dati rimossi:
- Tabelle del database (feeds e imports)
- Opzioni e impostazioni
- Cron jobs programmati
- Metadati dei post
- File di cache

Se hai bisogno di reinstallare il plugin in futuro, 
tutti i dati dovranno essere riconfigurati da zero.
    ";
    
    error_log($message);
}

// === ESECUZIONE DELLO SCRIPT DI DISINSTALLAZIONE ===

try {
    // Esegui il backup prima della disinstallazione (opzionale)
    // rss_importer_backup_before_uninstall();
    
    // Esegui la disinstallazione completa
    rss_importer_uninstall();
    
    // Mostra messaggio di conferma
    rss_importer_show_uninstall_message();
    
} catch (Exception $e) {
    error_log('RSS Feed Importer: Errore durante la disinstallazione - ' . $e->getMessage());
}