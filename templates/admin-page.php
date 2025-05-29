<?php
/**
 * Template per la pagina di amministrazione dei feed RSS
 * Path: templates/admin-page.php
 */

// Impedire l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('RSS Feed Importer - Gestione Feed', 'rss-feed-importer'); ?></h1>
    
    <div class="rss-importer-container">
        <!-- Statistiche rapide -->
        <?php
        global $wpdb;
        $table_feeds = $wpdb->prefix . RSS_IMPORTER_TABLE_FEEDS;
        $table_imports = $wpdb->prefix . RSS_IMPORTER_TABLE_IMPORTS;
        
        $total_feeds = $wpdb->get_var("SELECT COUNT(*) FROM $table_feeds");
        $active_feeds = $wpdb->get_var("SELECT COUNT(*) FROM $table_feeds WHERE status = 'active'");
        $total_imports = $wpdb->get_var("SELECT COUNT(*) FROM $table_imports");
        $images_imported = $wpdb->get_var("SELECT COUNT(*) FROM $table_imports WHERE featured_image_imported = 1");
        ?>
        
        <?php if ($total_feeds > 0): ?>
        <div class="rss-importer-stats">
            <div class="stats-grid">
                <div class="stat-box total-stat">
                    <h3><?php echo number_format_i18n($total_feeds); ?></h3>
                    <p><?php _e('Feed Configurati', 'rss-feed-importer'); ?></p>
                </div>
                <div class="stat-box success-stat">
                    <h3><?php echo number_format_i18n($active_feeds); ?></h3>
                    <p><?php _e('Feed Attivi', 'rss-feed-importer'); ?></p>
                </div>
                <div class="stat-box today-stat">
                    <h3><?php echo number_format_i18n($total_imports); ?></h3>
                    <p><?php _e('Post Importati', 'rss-feed-importer'); ?></p>
                </div>
                <div class="stat-box images-stat">
                    <h3><?php echo number_format_i18n($images_imported); ?></h3>
                    <p><?php _e('Immagini Importate', 'rss-feed-importer'); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Sezione aggiunta/modifica feed -->
        <div class="rss-importer-form-section">
            <h2><?php _e('Aggiungi/Modifica Feed RSS', 'rss-feed-importer'); ?></h2>
            
            <form method="post" action="" id="rss-feed-form">
                <?php wp_nonce_field('save_feed', 'rss_importer_nonce'); ?>
                <input type="hidden" name="save_feed" value="1">
                <input type="hidden" name="feed_id" id="feed_id" value="">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="feed_name"><?php _e('Nome Feed', 'rss-feed-importer'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="feed_name" id="feed_name" class="regular-text" required>
                            <p class="description"><?php _e('Nome identificativo per questo feed RSS', 'rss-feed-importer'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="feed_url"><?php _e('URL Feed RSS', 'rss-feed-importer'); ?></label>
                        </th>
                        <td>
                            <input type="url" name="feed_url" id="feed_url" class="regular-text" required>
                            <button type="button" id="validate_feed" class="button"><?php _e('Valida Feed', 'rss-feed-importer'); ?></button>
                            <p class="description"><?php _e('URL completo del feed RSS da importare', 'rss-feed-importer'); ?></p>
                            <div id="validation_result" class="validation-result"></div>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="feed_status"><?php _e('Stato', 'rss-feed-importer'); ?></label>
                        </th>
                        <td>
                            <select name="feed_status" id="feed_status">
                                <option value="active"><?php _e('Attivo', 'rss-feed-importer'); ?></option>
                                <option value="inactive"><?php _e('Inattivo', 'rss-feed-importer'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="import_frequency"><?php _e('Frequenza Importazione', 'rss-feed-importer'); ?></label>
                        </th>
                        <td>
                            <select name="import_frequency" id="import_frequency">
                                <option value="hourly"><?php _e('Ogni ora', 'rss-feed-importer'); ?></option>
                                <option value="daily"><?php _e('Giornaliera', 'rss-feed-importer'); ?></option>
                                <option value="weekly"><?php _e('Settimanale', 'rss-feed-importer'); ?></option>
                            </select>
                            <p class="description"><?php _e('Frequenza con cui importare automaticamente nuovi post da questo feed', 'rss-feed-importer'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="post_status"><?php _e('Stato Post', 'rss-feed-importer'); ?></label>
                        </th>
                        <td>
                            <select name="post_status" id="post_status">
                                <option value="draft"><?php _e('Bozza', 'rss-feed-importer'); ?></option>
                                <option value="publish"><?php _e('Pubblicato', 'rss-feed-importer'); ?></option>
                                <option value="pending"><?php _e('In attesa di revisione', 'rss-feed-importer'); ?></option>
                            </select>
                            <p class="description"><?php _e('Stato dei post importati da questo feed', 'rss-feed-importer'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="author_id"><?php _e('Autore', 'rss-feed-importer'); ?></label>
                        </th>
                        <td>
                            <?php
                            wp_dropdown_users(array(
                                'name' => 'author_id',
                                'id' => 'author_id',
                                'selected' => 1,
                                'include_selected' => true
                            ));
                            ?>
                            <p class="description"><?php _e('Autore assegnato ai post importati', 'rss-feed-importer'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Opzioni Automatiche', 'rss-feed-importer'); ?></th>
                        <td>
                            <fieldset>
                                <label for="auto_categorize">
                                    <input type="checkbox" name="auto_categorize" id="auto_categorize" value="1" checked>
                                    <?php _e('Genera categorie automaticamente', 'rss-feed-importer'); ?>
                                </label>
                                <br>
                                <label for="auto_tags">
                                    <input type="checkbox" name="auto_tags" id="auto_tags" value="1" checked>
                                    <?php _e('Genera tag automaticamente', 'rss-feed-importer'); ?>
                                </label>
                            </fieldset>
                            <p class="description">
                                <?php _e('Genera automaticamente categorie e tag basati sul contenuto del post', 'rss-feed-importer'); ?>
                                <br>
                                <small style="color: #2271b1;">
                                    <span class="dashicons dashicons-info" style="font-size: 14px; vertical-align: middle;"></span>
                                    <?php _e('Le immagini in evidenza vengono importate automaticamente se abilitato nelle impostazioni generali.', 'rss-feed-importer'); ?>
                                </small>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Salva Feed', 'rss-feed-importer'); ?>">
                    <button type="button" id="reset_form" class="button"><?php _e('Nuovo Feed', 'rss-feed-importer'); ?></button>
                    
                    <?php $settings = get_option('rss_importer_settings', array()); ?>
                    <?php if (empty($settings['image_import'])): ?>
                        <span style="margin-left: 20px; color: #dba617;">
                            <span class="dashicons dashicons-warning" style="font-size: 16px; vertical-align: middle;"></span>
                            <?php _e('Importazione immagini disabilitata.', 'rss-feed-importer'); ?>
                            <a href="<?php echo admin_url('admin.php?page=rss-importer-settings'); ?>"><?php _e('Abilita nelle impostazioni', 'rss-feed-importer'); ?></a>
                        </span>
                    <?php endif; ?>
                </p>
            </form>
        </div>
        
        <!-- Sezione elenco feed esistenti -->
        <div class="rss-importer-feeds-section">
            <h2><?php _e('Feed RSS Configurati', 'rss-feed-importer'); ?></h2>
            
            <?php if (empty($feeds)): ?>
                <div class="notice notice-info">
                    <p><?php _e('Nessun feed RSS configurato. Aggiungi il tuo primo feed utilizzando il modulo sopra.', 'rss-feed-importer'); ?></p>
                </div>
            <?php else: ?>
                <div class="tablenav top">
                    <div class="alignleft actions">
                        <button type="button" id="import_all_feeds" class="button action"><?php _e('Importa Tutti i Feed', 'rss-feed-importer'); ?></button>
                    </div>
                    <br class="clear">
                </div>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column column-name column-primary"><?php _e('Nome', 'rss-feed-importer'); ?></th>
                            <th scope="col" class="manage-column column-url"><?php _e('URL', 'rss-feed-importer'); ?></th>
                            <th scope="col" class="manage-column column-status"><?php _e('Stato', 'rss-feed-importer'); ?></th>
                            <th scope="col" class="manage-column column-frequency"><?php _e('Frequenza', 'rss-feed-importer'); ?></th>
                            <th scope="col" class="manage-column column-last-import"><?php _e('Ultima Importazione', 'rss-feed-importer'); ?></th>
                            <th scope="col" class="manage-column column-total"><?php _e('Post Importati', 'rss-feed-importer'); ?></th>
                            <th scope="col" class="manage-column column-images"><?php _e('Immagini', 'rss-feed-importer'); ?></th>
                            <th scope="col" class="manage-column column-actions"><?php _e('Azioni', 'rss-feed-importer'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feeds as $feed): ?>
                            <?php
                            // Calcola statistiche immagini per questo feed
                            $feed_images = $wpdb->get_var($wpdb->prepare(
                                "SELECT COUNT(*) FROM $table_imports WHERE feed_id = %d AND featured_image_imported = 1",
                                $feed->id
                            ));
                            $feed_total_posts = $wpdb->get_var($wpdb->prepare(
                                "SELECT COUNT(*) FROM $table_imports WHERE feed_id = %d",
                                $feed->id
                            ));
                            ?>
                            <tr data-feed-id="<?php echo esc_attr($feed->id); ?>">
                                <td class="column-name column-primary">
                                    <strong><?php echo esc_html($feed->name); ?></strong>
                                    <div class="row-actions">
                                        <span class="edit">
                                            <a href="#" class="edit-feed" data-feed-id="<?php echo esc_attr($feed->id); ?>"><?php _e('Modifica', 'rss-feed-importer'); ?></a> |
                                        </span>
                                        <span class="import">
                                            <a href="#" class="import-feed" data-feed-id="<?php echo esc_attr($feed->id); ?>"><?php _e('Importa Ora', 'rss-feed-importer'); ?></a> |
                                        </span>
                                        <span class="delete">
                                            <a href="#" class="delete-feed" data-feed-id="<?php echo esc_attr($feed->id); ?>"><?php _e('Elimina', 'rss-feed-importer'); ?></a>
                                        </span>
                                    </div>
                                </td>
                                <td class="column-url">
                                    <a href="<?php echo esc_url($feed->url); ?>" target="_blank" title="<?php _e('Apri feed in nuova finestra', 'rss-feed-importer'); ?>">
                                        <?php echo esc_html(wp_trim_words($feed->url, 8, '...')); ?>
                                    </a>
                                </td>
                                <td class="column-status">
                                    <span class="status-badge status-<?php echo esc_attr($feed->status); ?>">
                                        <?php echo $feed->status === 'active' ? __('Attivo', 'rss-feed-importer') : __('Inattivo', 'rss-feed-importer'); ?>
                                    </span>
                                </td>
                                <td class="column-frequency">
                                    <?php
                                    $frequencies = array(
                                        'hourly' => __('Ogni ora', 'rss-feed-importer'),
                                        'daily' => __('Giornaliera', 'rss-feed-importer'),
                                        'weekly' => __('Settimanale', 'rss-feed-importer')
                                    );
                                    echo esc_html($frequencies[$feed->import_frequency] ?? $feed->import_frequency);
                                    ?>
                                </td>
                                <td class="column-last-import">
                                    <?php
                                    if ($feed->last_import && $feed->last_import !== '0000-00-00 00:00:00') {
                                        echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $feed->last_import));
                                    } else {
                                        echo '<em>' . __('Mai', 'rss-feed-importer') . '</em>';
                                    }
                                    ?>
                                </td>
                                <td class="column-total">
                                    <strong><?php echo intval($feed->total_imported); ?></strong>
                                </td>
                                <td class="column-images">
                                    <?php if ($feed_total_posts > 0): ?>
                                        <div style="display: flex; align-items: center; gap: 5px;">
                                            <span class="dashicons dashicons-format-image" style="color: #2271b1; font-size: 16px;"></span>
                                            <span style="font-weight: 600;"><?php echo intval($feed_images); ?></span>
                                            <small style="color: #666;">/ <?php echo intval($feed_total_posts); ?></small>
                                        </div>
                                        <?php
                                        $success_rate = $feed_total_posts > 0 ? round(($feed_images / $feed_total_posts) * 100) : 0;
                                        $color = $success_rate >= 70 ? '#46b450' : ($success_rate >= 40 ? '#dba617' : '#dc3232');
                                        ?>
                                        <small style="color: <?php echo $color; ?>; font-weight: 500;">
                                            <?php echo $success_rate; ?>% importate
                                        </small>
                                    <?php else: ?>
                                        <em style="color: #666;"><?php _e('Nessun dato', 'rss-feed-importer'); ?></em>
                                    <?php endif; ?>
                                </td>
                                <td class="column-actions">
                                    <div class="button-group">
                                        <button type="button" class="button button-small import-feed" data-feed-id="<?php echo esc_attr($feed->id); ?>">
                                            <?php _e('Importa', 'rss-feed-importer'); ?>
                                        </button>
                                        <button type="button" class="button button-small edit-feed" data-feed-id="<?php echo esc_attr($feed->id); ?>">
                                            <?php _e('Modifica', 'rss-feed-importer'); ?>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Informazioni aggiuntive -->
                <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 6px;">
                    <h4 style="margin-top: 0; color: #495057;"><?php _e('Suggerimenti per l\'importazione immagini:', 'rss-feed-importer'); ?></h4>
                    <ul style="margin-bottom: 0; color: #6c757d;">
                        <li><?php _e('Le immagini vengono cercate automaticamente nei feed RSS (enclosure, media:content, contenuto HTML)', 'rss-feed-importer'); ?></li>
                        <li><?php _e('Se l\'importazione di un\'immagine fallisce, verrà utilizzata l\'immagine predefinita configurata nelle impostazioni', 'rss-feed-importer'); ?></li>
                        <li><?php _e('Le immagini importate vengono ottimizzate automaticamente per le performance del sito', 'rss-feed-importer'); ?></li>
                        <li><?php _e('Puoi gestire l\'immagine predefinita e altre impostazioni nella pagina', 'rss-feed-importer'); ?> 
                            <a href="<?php echo admin_url('admin.php?page=rss-importer-settings'); ?>"><?php _e('Impostazioni', 'rss-feed-importer'); ?></a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Loading overlay -->
    <div id="loading_overlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="spinner is-active"></div>
            <p id="loading_message"><?php _e('Elaborazione in corso...', 'rss-feed-importer'); ?></p>
        </div>
    </div>
</div>

<!-- Script inline per i dati dei feed (per la modifica) -->
<script type="text/javascript">
var rssImporterFeeds = <?php echo json_encode($feeds); ?>;
</script>