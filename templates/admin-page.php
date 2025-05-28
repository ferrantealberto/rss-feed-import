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
                            <p class="description"><?php _e('Genera automaticamente categorie e tag basati sul contenuto del post', 'rss-feed-importer'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Salva Feed', 'rss-feed-importer'); ?>">
                    <button type="button" id="reset_form" class="button"><?php _e('Nuovo Feed', 'rss-feed-importer'); ?></button>
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
                            <th scope="col" class="manage-column column-actions"><?php _e('Azioni', 'rss-feed-importer'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feeds as $feed): ?>
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