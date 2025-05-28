<?php
/**
 * Template per la pagina delle impostazioni
 * Path: templates/settings-page.php
 */

// Impedire l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('RSS Feed Importer - Impostazioni', 'rss-feed-importer'); ?></h1>
    
    <div class="rss-importer-container">
        <form method="post" action="">
            <?php wp_nonce_field('save_settings', 'rss_importer_nonce'); ?>
            <input type="hidden" name="save_settings" value="1">
            
            <!-- Sezione Importazione -->
            <div class="settings-section">
                <h2><?php _e('Impostazioni Importazione', 'rss-feed-importer'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="max_posts_per_import"><?php _e('Numero massimo post per importazione', 'rss-feed-importer'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="max_posts_per_import" id="max_posts_per_import" 
                                   value="<?php echo esc_attr($settings['max_posts_per_import']); ?>" 
                                   min="1" max="100" class="small-text">
                            <p class="description">
                                <?php _e('Limita il numero di post importati per ogni esecuzione per evitare timeout del server.', 'rss-feed-importer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="duplicate_check_method"><?php _e('Metodo controllo duplicati', 'rss-feed-importer'); ?></label>
                        </th>
                        <td>
                            <select name="duplicate_check_method" id="duplicate_check_method">
                                <option value="title_url" <?php selected($settings['duplicate_check_method'], 'title_url'); ?>>
                                    <?php _e('Titolo + URL', 'rss-feed-importer'); ?>
                                </option>
                                <option value="title" <?php selected($settings['duplicate_check_method'], 'title'); ?>>
                                    <?php _e('Solo Titolo', 'rss-feed-importer'); ?>
                                </option>
                                <option value="url" <?php selected($settings['duplicate_check_method'], 'url'); ?>>
                                    <?php _e('Solo URL', 'rss-feed-importer'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php _e('Scegli come identificare i post duplicati durante l\'importazione.', 'rss-feed-importer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="default_post_status"><?php _e('Stato post predefinito', 'rss-feed-importer'); ?></label>
                        </th>
                        <td>
                            <select name="default_post_status" id="default_post_status">
                                <option value="draft" <?php selected($settings['default_post_status'], 'draft'); ?>>
                                    <?php _e('Bozza', 'rss-feed-importer'); ?>
                                </option>
                                <option value="publish" <?php selected($settings['default_post_status'], 'publish'); ?>>
                                    <?php _e('Pubblicato', 'rss-feed-importer'); ?>
                                </option>
                                <option value="pending" <?php selected($settings['default_post_status'], 'pending'); ?>>
                                    <?php _e('In attesa di revisione', 'rss-feed-importer'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php _e('Stato predefinito per i nuovi post importati (può essere sovrascritto per singolo feed).', 'rss-feed-importer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="excerpt_length"><?php _e('Lunghezza riassunto', 'rss-feed-importer'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="excerpt_length" id="excerpt_length" 
                                   value="<?php echo esc_attr($settings['excerpt_length']); ?>" 
                                   min="50" max="500" class="small-text">
                            <span><?php _e('parole', 'rss-feed-importer'); ?></span>
                            <p class="description">
                                <?php _e('Numero di parole per il riassunto automatico dei post importati.', 'rss-feed-importer'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Sezione Categorie e Tag -->
            <div class="settings-section">
                <h2><?php _e('Gestione Categorie e Tag', 'rss-feed-importer'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="category_creation_method"><?php _e('Metodo creazione categorie', 'rss-feed-importer'); ?></label>
                        </th>
                        <td>
                            <select name="category_creation_method" id="category_creation_method">
                                <option value="auto" <?php selected($settings['category_creation_method'], 'auto'); ?>>
                                    <?php _e('Automatico da contenuto', 'rss-feed-importer'); ?>
                                </option>
                                <option value="feed_categories" <?php selected($settings['category_creation_method'], 'feed_categories'); ?>>
                                    <?php _e('Da categorie del feed RSS', 'rss-feed-importer'); ?>
                                </option>
                                <option value="fixed" <?php selected($settings['category_creation_method'], 'fixed'); ?>>
                                    <?php _e('Categoria fissa per feed', 'rss-feed-importer'); ?>
                                </option>
                                <option value="none" <?php selected($settings['category_creation_method'], 'none'); ?>>
                                    <?php _e('Non creare categorie', 'rss-feed-importer'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php _e('Scegli come creare le categorie per i post importati.', 'rss-feed-importer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="tag_creation_method"><?php _e('Metodo creazione tag', 'rss-feed-importer'); ?></label>
                        </th>
                        <td>
                            <select name="tag_creation_method" id="tag_creation_method">
                                <option value="auto" <?php selected($settings['tag_creation_method'], 'auto'); ?>>
                                    <?php _e('Automatico da parole chiave', 'rss-feed-importer'); ?>
                                </option>
                                <option value="feed_keywords" <?php selected($settings['tag_creation_method'], 'feed_keywords'); ?>>
                                    <?php _e('Da parole chiave del feed', 'rss-feed-importer'); ?>
                                </option>
                                <option value="none" <?php selected($settings['tag_creation_method'], 'none'); ?>>
                                    <?php _e('Non creare tag', 'rss-feed-importer'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php _e('Scegli come creare i tag per i post importati.', 'rss-feed-importer'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Sezione Media e Contenuto -->
            <div class="settings-section">
                <h2><?php _e('Gestione Media e Contenuto', 'rss-feed-importer'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Importazione immagini', 'rss-feed-importer'); ?></th>
                        <td>
                            <fieldset>
                                <label for="image_import">
                                    <input type="checkbox" name="image_import" id="image_import" value="1" 
                                           <?php checked($settings['image_import'], 1); ?>>
                                    <?php _e('Importa e salva immagini localmente', 'rss-feed-importer'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('Se abilitato, le immagini dei post verranno scaricate e salvate nella libreria media di WordPress.', 'rss-feed-importer'); ?>
                                    <br>
                                    <strong><?php _e('Attenzione:', 'rss-feed-importer'); ?></strong>
                                    <?php _e('Questa opzione può rallentare significativamente l\'importazione e occupare spazio sul server.', 'rss-feed-importer'); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Sezione Programmazione -->
            <div class="settings-section">
                <h2><?php _e('Impostazioni Programmazione', 'rss-feed-importer'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Stato Cron WordPress', 'rss-feed-importer'); ?></th>
                        <td>
                            <?php
                            $cron_disabled = defined('DISABLE_WP_CRON') && DISABLE_WP_CRON;
                            if ($cron_disabled): ?>
                                <span class="status-badge status-error"><?php _e('Disabilitato', 'rss-feed-importer'); ?></span>
                                <p class="description">
                                    <?php _e('Il cron di WordPress è disabilitato. Le importazioni programmate non funzioneranno automaticamente.', 'rss-feed-importer'); ?>
                                    <br>
                                    <?php _e('Configura un cron job del server per eseguire: ', 'rss-feed-importer'); ?>
                                    <code>wp-cron.php</code>
                                </p>
                            <?php else: ?>
                                <span class="status-badge status-success"><?php _e('Attivo', 'rss-feed-importer'); ?></span>
                                <p class="description">
                                    <?php _e('Il cron di WordPress è attivo. Le importazioni programmate verranno eseguite automaticamente.', 'rss-feed-importer'); ?>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Prossime esecuzioni programmate', 'rss-feed-importer'); ?></th>
                        <td>
                            <?php
                            $next_cron = wp_next_scheduled('rss_importer_scheduled_import');
                            if ($next_cron): ?>
                                <strong><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_cron); ?></strong>
                                <p class="description">
                                    <?php printf(__('Prossima esecuzione automatica prevista per %s', 'rss-feed-importer'), 
                                                human_time_diff($next_cron, current_time('timestamp'))); ?>
                                </p>
                            <?php else: ?>
                                <em><?php _e('Nessuna esecuzione programmata', 'rss-feed-importer'); ?></em>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Sezione Debug e Log -->
            <div class="settings-section">
                <h2><?php _e('Debug e Diagnostica', 'rss-feed-importer'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Informazioni Sistema', 'rss-feed-importer'); ?></th>
                        <td>
                            <div class="system-info">
                                <p><strong><?php _e('Versione PHP:', 'rss-feed-importer'); ?></strong> <?php echo PHP_VERSION; ?></p>
                                <p><strong><?php _e('Versione WordPress:', 'rss-feed-importer'); ?></strong> <?php echo get_bloginfo('version'); ?></p>
                                <p><strong><?php _e('Memory Limit:', 'rss-feed-importer'); ?></strong> <?php echo ini_get('memory_limit'); ?></p>
                                <p><strong><?php _e('Max Execution Time:', 'rss-feed-importer'); ?></strong> <?php echo ini_get('max_execution_time'); ?>s</p>
                                <p><strong><?php _e('Funzioni necessarie:', 'rss-feed-importer'); ?></strong>
                                    <?php
                                    $required_functions = array('simplexml_load_string', 'wp_remote_get', 'curl_init');
                                    $missing_functions = array();
                                    foreach ($required_functions as $func) {
                                        if (!function_exists($func)) {
                                            $missing_functions[] = $func;
                                        }
                                    }
                                    if (empty($missing_functions)): ?>
                                        <span class="status-badge status-success"><?php _e('Tutte disponibili', 'rss-feed-importer'); ?></span>
                                    <?php else: ?>
                                        <span class="status-badge status-error"><?php _e('Mancanti:', 'rss-feed-importer'); ?> <?php echo implode(', ', $missing_functions); ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Test Connessione Feed', 'rss-feed-importer'); ?></th>
                        <td>
                            <div class="test-connection">
                                <input type="url" id="test_feed_url" placeholder="<?php _e('Inserisci URL feed di test', 'rss-feed-importer'); ?>" class="regular-text">
                                <button type="button" id="test_connection" class="button"><?php _e('Testa Connessione', 'rss-feed-importer'); ?></button>
                                <div id="test_result" class="test-result"></div>
                            </div>
                            <p class="description">
                                <?php _e('Testa la connessione a un feed RSS per verificare che il server possa raggiungere URLs esterni.', 'rss-feed-importer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Azioni Manutenzione', 'rss-feed-importer'); ?></th>
                        <td>
                            <div class="maintenance-actions">
                                <button type="button" id="clear_import_logs" class="button"><?php _e('Pulisci Log Importazioni Vecchie', 'rss-feed-importer'); ?></button>
                                <button type="button" id="reset_feed_stats" class="button"><?php _e('Reset Statistiche Feed', 'rss-feed-importer'); ?></button>
                                <button type="button" id="force_cron_run" class="button button-primary"><?php _e('Esegui Importazione Ora', 'rss-feed-importer'); ?></button>
                            </div>
                            <p class="description">
                                <?php _e('Azioni per la manutenzione e la risoluzione di problemi del plugin.', 'rss-feed-importer'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Sezione Sicurezza -->
            <div class="settings-section">
                <h2><?php _e('Impostazioni Sicurezza', 'rss-feed-importer'); ?></h2>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Filtri Contenuto', 'rss-feed-importer'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="strip_shortcodes" value="1" 
                                           <?php checked(get_option('rss_importer_strip_shortcodes', 1), 1); ?>>
                                    <?php _e('Rimuovi shortcode dal contenuto importato', 'rss-feed-importer'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" name="sanitize_html" value="1" 
                                           <?php checked(get_option('rss_importer_sanitize_html', 1), 1); ?>>
                                    <?php _e('Sanitizza HTML potenzialmente pericoloso', 'rss-feed-importer'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" name="limit_external_links" value="1" 
                                           <?php checked(get_option('rss_importer_limit_external_links', 0), 1); ?>>
                                    <?php _e('Aggiungi rel="nofollow" ai link esterni', 'rss-feed-importer'); ?>
                                </label>
                            </fieldset>
                            <p class="description">
                                <?php _e('Filtri di sicurezza applicati al contenuto importato per proteggere il sito.', 'rss-feed-importer'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Salva Impostazioni', 'rss-feed-importer'); ?>">
                <button type="button" id="reset_to_defaults" class="button"><?php _e('Ripristina Predefinite', 'rss-feed-importer'); ?></button>
            </p>
        </form>
        
        <!-- Sezione di supporto -->
        <div class="settings-section support-section">
            <h2><?php _e('Supporto e Documentazione', 'rss-feed-importer'); ?></h2>
            <div class="support-content">
                <div class="support-box">
                    <h3><?php _e('Documentazione', 'rss-feed-importer'); ?></h3>
                    <p><?php _e('Consulta la documentazione completa per guide dettagliate e risoluzione problemi.', 'rss-feed-importer'); ?></p>
                    <a href="#" class="button" target="_blank"><?php _e('Vai alla Documentazione', 'rss-feed-importer'); ?></a>
                </div>
                
                <div class="support-box">
                    <h3><?php _e('Supporto Tecnico', 'rss-feed-importer'); ?></h3>
                    <p><?php _e('Hai problemi? Contatta il supporto tecnico per assistenza personalizzata.', 'rss-feed-importer'); ?></p>
                    <a href="#" class="button" target="_blank"><?php _e('Richiedi Supporto', 'rss-feed-importer'); ?></a>
                </div>
                
                <div class="support-box">
                    <h3><?php _e('Valutazione Plugin', 'rss-feed-importer'); ?></h3>
                    <p><?php _e('Se il plugin ti è utile, lascia una recensione positiva!', 'rss-feed-importer'); ?></p>
                    <a href="#" class="button button-primary" target="_blank"><?php _e('Lascia Recensione', 'rss-feed-importer'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>