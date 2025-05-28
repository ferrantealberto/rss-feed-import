<?php
/**
 * Template per la pagina dei post importati
 * Path: templates/imported-posts.php
 */

// Impedire l'accesso diretto
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('RSS Feed Importer - Post Importati', 'rss-feed-importer'); ?></h1>
    
    <div class="rss-importer-container">
        <!-- Statistiche rapide -->
        <div class="rss-importer-stats">
            <div class="stats-grid">
                <div class="stat-box">
                    <h3><?php echo number_format_i18n($total_imports); ?></h3>
                    <p><?php _e('Post Totali Importati', 'rss-feed-importer'); ?></p>
                </div>
                <div class="stat-box">
                    <h3><?php 
                        global $wpdb;
                        $table_imports = $wpdb->prefix . RSS_IMPORTER_TABLE_IMPORTS;
                        $today_imports = $wpdb->get_var("SELECT COUNT(*) FROM $table_imports WHERE DATE(import_date) = CURDATE()");
                        echo number_format_i18n($today_imports);
                    ?></h3>
                    <p><?php _e('Importati Oggi', 'rss-feed-importer'); ?></p>
                </div>
                <div class="stat-box">
                    <h3><?php 
                        $success_imports = $wpdb->get_var("SELECT COUNT(*) FROM $table_imports WHERE status = 'success'");
                        echo number_format_i18n($success_imports);
                    ?></h3>
                    <p><?php _e('Importazioni Riuscite', 'rss-feed-importer'); ?></p>
                </div>
                <div class="stat-box">
                    <h3><?php 
                        $error_imports = $wpdb->get_var("SELECT COUNT(*) FROM $table_imports WHERE status = 'error'");
                        echo number_format_i18n($error_imports);
                    ?></h3>
                    <p><?php _e('Errori', 'rss-feed-importer'); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Filtri di ricerca -->
        <div class="rss-importer-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="rss-importer-posts">
                
                <div class="filter-row">
                    <label for="filter_feed"><?php _e('Feed:', 'rss-feed-importer'); ?></label>
                    <select name="filter_feed" id="filter_feed">
                        <option value=""><?php _e('Tutti i feed', 'rss-feed-importer'); ?></option>
                        <?php
                        $table_feeds = $wpdb->prefix . RSS_IMPORTER_TABLE_FEEDS;
                        $feeds = $wpdb->get_results("SELECT id, name FROM $table_feeds ORDER BY name");
                        foreach ($feeds as $feed) {
                            $selected = isset($_GET['filter_feed']) && $_GET['filter_feed'] == $feed->id ? 'selected' : '';
                            echo '<option value="' . esc_attr($feed->id) . '" ' . $selected . '>' . esc_html($feed->name) . '</option>';
                        }
                        ?>
                    </select>
                    
                    <label for="filter_status"><?php _e('Stato:', 'rss-feed-importer'); ?></label>
                    <select name="filter_status" id="filter_status">
                        <option value=""><?php _e('Tutti gli stati', 'rss-feed-importer'); ?></option>
                        <option value="success" <?php selected(isset($_GET['filter_status']) ? $_GET['filter_status'] : '', 'success'); ?>><?php _e('Successo', 'rss-feed-importer'); ?></option>
                        <option value="error" <?php selected(isset($_GET['filter_status']) ? $_GET['filter_status'] : '', 'error'); ?>><?php _e('Errore', 'rss-feed-importer'); ?></option>
                        <option value="duplicate" <?php selected(isset($_GET['filter_status']) ? $_GET['filter_status'] : '', 'duplicate'); ?>><?php _e('Duplicato', 'rss-feed-importer'); ?></option>
                    </select>
                    
                    <label for="filter_date_from"><?php _e('Dal:', 'rss-feed-importer'); ?></label>
                    <input type="date" name="filter_date_from" id="filter_date_from" value="<?php echo esc_attr(isset($_GET['filter_date_from']) ? $_GET['filter_date_from'] : ''); ?>">
                    
                    <label for="filter_date_to"><?php _e('Al:', 'rss-feed-importer'); ?></label>
                    <input type="date" name="filter_date_to" id="filter_date_to" value="<?php echo esc_attr(isset($_GET['filter_date_to']) ? $_GET['filter_date_to'] : ''); ?>">
                    
                    <input type="submit" class="button" value="<?php _e('Filtra', 'rss-feed-importer'); ?>">
                    <a href="<?php echo admin_url('admin.php?page=rss-importer-posts'); ?>" class="button"><?php _e('Reset', 'rss-feed-importer'); ?></a>
                </div>
            </form>
        </div>
        
        <!-- Tabella post importati -->
        <div class="rss-importer-posts-section">
            <?php if (empty($imports)): ?>
                <div class="notice notice-info">
                    <p><?php _e('Nessun post importato trovato con i filtri selezionati.', 'rss-feed-importer'); ?></p>
                </div>
            <?php else: ?>
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <label for="bulk-action-selector-top" class="screen-reader-text"><?php _e('Seleziona azione da eseguire', 'rss-feed-importer'); ?></label>
                        <select name="action" id="bulk-action-selector-top">
                            <option value="-1"><?php _e('Azioni in blocco', 'rss-feed-importer'); ?></option>
                            <option value="publish"><?php _e('Pubblica', 'rss-feed-importer'); ?></option>
                            <option value="draft"><?php _e('Porta in bozza', 'rss-feed-importer'); ?></option>
                            <option value="trash"><?php _e('Sposta nel cestino', 'rss-feed-importer'); ?></option>
                        </select>
                        <input type="submit" id="doaction" class="button action" value="<?php _e('Applica', 'rss-feed-importer'); ?>">
                    </div>
                    
                    <div class="alignright">
                        <span class="displaying-num"><?php printf(__('%s elementi', 'rss-feed-importer'), number_format_i18n($total_imports)); ?></span>
                    </div>
                    <br class="clear">
                </div>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column">
                                <label class="screen-reader-text" for="cb-select-all-1"><?php _e('Seleziona tutto', 'rss-feed-importer'); ?></label>
                                <input id="cb-select-all-1" type="checkbox">
                            </td>
                            <th scope="col" class="manage-column column-title column-primary"><?php _e('Titolo', 'rss-feed-importer'); ?></th>
                            <th scope="col" class="manage-column column-feed"><?php _e('Feed', 'rss-feed-importer'); ?></th>
                            <th scope="col" class="manage-column column-post-status"><?php _e('Stato Post', 'rss-feed-importer'); ?></th>
                            <th scope="col" class="manage-column column-import-status"><?php _e('Stato Importazione', 'rss-feed-importer'); ?></th>
                            <th scope="col" class="manage-column column-date"><?php _e('Data Importazione', 'rss-feed-importer'); ?></th>
                            <th scope="col" class="manage-column column-categories"><?php _e('Categorie/Tag', 'rss-feed-importer'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($imports as $import): ?>
                            <tr>
                                <th scope="row" class="check-column">
                                    <label class="screen-reader-text" for="cb-select-<?php echo esc_attr($import->id); ?>">
                                        <?php printf(__('Seleziona %s', 'rss-feed-importer'), esc_html($import->original_title)); ?>
                                    </label>
                                    <input id="cb-select-<?php echo esc_attr($import->id); ?>" type="checkbox" name="post[]" value="<?php echo esc_attr($import->post_id); ?>">
                                </th>
                                <td class="column-title column-primary">
                                    <?php if ($import->post_id && get_post($import->post_id)): ?>
                                        <strong>
                                            <a href="<?php echo get_edit_post_link($import->post_id); ?>" target="_blank">
                                                <?php echo esc_html($import->post_title ?: $import->original_title); ?>
                                            </a>
                                        </strong>
                                        <div class="row-actions">
                                            <span class="edit">
                                                <a href="<?php echo get_edit_post_link($import->post_id); ?>" target="_blank"><?php _e('Modifica', 'rss-feed-importer'); ?></a> |
                                            </span>
                                            <span class="view">
                                                <a href="<?php echo get_permalink($import->post_id); ?>" target="_blank"><?php _e('Visualizza', 'rss-feed-importer'); ?></a> |
                                            </span>
                                            <span class="source">
                                                <a href="<?php echo esc_url($import->original_url); ?>" target="_blank"><?php _e('Sorgente', 'rss-feed-importer'); ?></a>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <strong><?php echo esc_html($import->original_title); ?></strong>
                                        <div class="row-actions">
                                            <span class="source">
                                                <a href="<?php echo esc_url($import->original_url); ?>" target="_blank"><?php _e('Sorgente', 'rss-feed-importer'); ?></a>
                                            </span>
                                        </div>
                                        <small class="error"><?php _e('Post non più disponibile', 'rss-feed-importer'); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="column-feed">
                                    <strong><?php echo esc_html($import->feed_name); ?></strong>
                                </td>
                                <td class="column-post-status">
                                    <?php if ($import->post_id && get_post($import->post_id)): ?>
                                        <span class="post-status-badge post-status-<?php echo esc_attr($import->post_status); ?>">
                                            <?php
                                            $status_labels = array(
                                                'publish' => __('Pubblicato', 'rss-feed-importer'),
                                                'draft' => __('Bozza', 'rss-feed-importer'),
                                                'pending' => __('In attesa', 'rss-feed-importer'),
                                                'private' => __('Privato', 'rss-feed-importer'),
                                                'trash' => __('Cestino', 'rss-feed-importer')
                                            );
                                            echo esc_html($status_labels[$import->post_status] ?? $import->post_status);
                                            ?>
                                        </span>
                                    <?php else: ?>
                                        <em><?php _e('N/A', 'rss-feed-importer'); ?></em>
                                    <?php endif; ?>
                                </td>
                                <td class="column-import-status">
                                    <span class="import-status-badge import-status-<?php echo esc_attr($import->status); ?>">
                                        <?php
                                        $import_status_labels = array(
                                            'success' => __('Successo', 'rss-feed-importer'),
                                            'error' => __('Errore', 'rss-feed-importer'),
                                            'duplicate' => __('Duplicato', 'rss-feed-importer')
                                        );
                                        echo esc_html($import_status_labels[$import->status] ?? $import->status);
                                        ?>
                                    </span>
                                    <?php if ($import->status === 'error' && $import->error_message): ?>
                                        <br><small class="error-message" title="<?php echo esc_attr($import->error_message); ?>">
                                            <?php echo esc_html(wp_trim_words($import->error_message, 8)); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="column-date">
                                    <?php echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $import->import_date)); ?>
                                </td>
                                <td class="column-categories">
                                    <?php
                                    $categories = json_decode($import->categories_created, true);
                                    $tags = json_decode($import->tags_created, true);
                                    
                                    if (!empty($categories)) {
                                        echo '<div class="categories-list">';
                                        echo '<strong>' . __('Cat:', 'rss-feed-importer') . '</strong> ';
                                        $cat_names = array();
                                        foreach ($categories as $cat_id) {
                                            $cat = get_category($cat_id);
                                            if ($cat) {
                                                $cat_names[] = $cat->name;
                                            }
                                        }
                                        echo esc_html(implode(', ', $cat_names));
                                        echo '</div>';
                                    }
                                    
                                    if (!empty($tags)) {
                                        echo '<div class="tags-list">';
                                        echo '<strong>' . __('Tag:', 'rss-feed-importer') . '</strong> ';
                                        echo esc_html(implode(', ', array_slice($tags, 0, 3)));
                                        if (count($tags) > 3) {
                                            echo ' <small>(+' . (count($tags) - 3) . ')</small>';
                                        }
                                        echo '</div>';
                                    }
                                    
                                    if (empty($categories) && empty($tags)) {
                                        echo '<em>' . __('Nessuna', 'rss-feed-importer') . '</em>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Paginazione -->
                <?php if ($total_pages > 1): ?>
                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <span class="displaying-num"><?php printf(__('%s elementi', 'rss-feed-importer'), number_format_i18n($total_imports)); ?></span>
                            <span class="pagination-links">
                                <?php
                                $page_links = paginate_links(array(
                                    'base' => add_query_arg('paged', '%#%'),
                                    'format' => '',
                                    'prev_text' => __('&laquo;', 'rss-feed-importer'),
                                    'next_text' => __('&raquo;', 'rss-feed-importer'),
                                    'total' => $total_pages,
                                    'current' => $current_page,
                                    'type' => 'plain'
                                ));
                                echo $page_links;
                                ?>
                            </span>
                        </div>
                        <br class="clear">
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <!-- Sezione esportazione dati -->
        <div class="rss-importer-export-section">
            <h3><?php _e('Esporta Dati', 'rss-feed-importer'); ?></h3>
            <p><?php _e('Esporta l\'elenco completo dei post importati in formato CSV per analisi o backup.', 'rss-feed-importer'); ?></p>
            <form method="post" action="">
                <?php wp_nonce_field('export_imports', 'rss_importer_nonce'); ?>
                <input type="hidden" name="export_imports" value="1">
                
                <label for="export_format"><?php _e('Formato:', 'rss-feed-importer'); ?></label>
                <select name="export_format" id="export_format">
                    <option value="csv">CSV</option>
                    <option value="json">JSON</option>
                </select>
                
                <label for="export_date_range"><?php _e('Periodo:', 'rss-feed-importer'); ?></label>
                <select name="export_date_range" id="export_date_range">
                    <option value="all"><?php _e('Tutti', 'rss-feed-importer'); ?></option>
                    <option value="last_month"><?php _e('Ultimo mese', 'rss-feed-importer'); ?></option>
                    <option value="last_week"><?php _e('Ultima settimana', 'rss-feed-importer'); ?></option>
                    <option value="today"><?php _e('Oggi', 'rss-feed-importer'); ?></option>
                </select>
                
                <input type="submit" class="button" value="<?php _e('Esporta', 'rss-feed-importer'); ?>">
            </form>
        </div>
    </div>
</div>