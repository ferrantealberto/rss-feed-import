/**
 * JavaScript per l'amministrazione del plugin RSS Feed Importer
 * Path: assets/admin.js
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Variabili globali
    var loadingOverlay = $('#loading_overlay');
    var feedForm = $('#rss-feed-form');
    var currentEditingFeed = null;
    
    // Inizializzazione
    init();
    
    function init() {
        bindEvents();
        setupFormValidation();
        initializeDataTables();
    }
    
    function bindEvents() {
        // Validazione feed RSS
        $('#validate_feed').on('click', handleValidateFeed);
        
        // Importazione feed
        $('.import-feed').on('click', handleImportFeed);
        $('#import_all_feeds').on('click', handleImportAllFeeds);
        
        // Gestione feed
        $('.edit-feed').on('click', handleEditFeed);
        $('.delete-feed').on('click', handleDeleteFeed);
        $('#reset_form').on('click', handleResetForm);
        
        // Azioni impostazioni
        $('#test_connection').on('click', handleTestConnection);
        $('#clear_import_logs').on('click', handleClearLogs);
        $('#reset_feed_stats').on('click', handleResetStats);
        $('#force_cron_run').on('click', handleForceCronRun);
        $('#reset_to_defaults').on('click', handleResetToDefaults);
        
        // Validazione in tempo reale URL
        $('#feed_url').on('input', debounce(validateUrlFormat, 500));
        
        // Azioni in blocco
        $('#bulk-action-selector-top').on('change', handleBulkActionChange);
        $('#doaction').on('click', handleBulkAction);
        
        // Checkbox "seleziona tutto"
        $('#cb-select-all-1').on('change', handleSelectAll);
    }
    
    function setupFormValidation() {
        // Validazione del form prima dell'invio
        feedForm.on('submit', function(e) {
            var feedUrl = $('#feed_url').val();
            var feedName = $('#feed_name').val();
            
            if (!feedUrl || !feedName) {
                e.preventDefault();
                showNotice('Tutti i campi obbligatori devono essere compilati.', 'error');
                return false;
            }
            
            if (!isValidUrl(feedUrl)) {
                e.preventDefault();
                showNotice('Inserisci un URL valido per il feed RSS.', 'error');
                $('#feed_url').focus();
                return false;
            }
        });
    }
    
    function initializeDataTables() {
        // Inizializza le tabelle con funzionalità avanzate se necessario
        if ($('.wp-list-table').length > 0) {
            // Aggiunge funzionalità di ordinamento se necessario
            $('.wp-list-table th').on('click', function() {
                // Implementa ordinamento personalizzato se necessario
            });
        }
    }
    
    // === GESTIONE VALIDAZIONE FEED ===
    function handleValidateFeed(e) {
        e.preventDefault();
        
        var url = $('#feed_url').val().trim();
        if (!url) {
            showValidationResult('Inserisci un URL prima di validare.', 'error');
            return;
        }
        
        if (!isValidUrl(url)) {
            showValidationResult('L\'URL inserito non è valido.', 'error');
            return;
        }
        
        var button = $(this);
        button.prop('disabled', true).addClass('loading');
        showValidationResult(rssImporter.strings.validating, 'loading');
        
        $.ajax({
            url: rssImporter.ajaxurl,
            type: 'POST',
            data: {
                action: 'validate_rss_feed',
                url: url,
                nonce: rssImporter.nonce
            },
            success: function(response) {
                if (response.success) {
                    var message = response.data.message;
                    if (response.data.title) {
                        message += '<br><strong>Titolo:</strong> ' + escapeHtml(response.data.title);
                    }
                    if (response.data.items_count) {
                        message += '<br><strong>Articoli trovati:</strong> ' + response.data.items_count;
                    }
                    showValidationResult(message, 'success');
                    
                    // Popola automaticamente il nome del feed se vuoto
                    if (!$('#feed_name').val() && response.data.title) {
                        $('#feed_name').val(response.data.title);
                    }
                } else {
                    showValidationResult(response.data.message || 'Errore durante la validazione.', 'error');
                }
            },
            error: function(xhr, status, error) {
                showValidationResult('Errore di connessione: ' + error, 'error');
            },
            complete: function() {
                button.prop('disabled', false).removeClass('loading');
            }
        });
    }
    
    function showValidationResult(message, type) {
        var resultDiv = $('#validation_result');
        resultDiv.removeClass('success error loading')
                 .addClass(type)
                 .html(message)
                 .slideDown();
        
        if (type === 'success' || type === 'error') {
            setTimeout(function() {
                resultDiv.slideUp();
            }, 5000);
        }
    }
    
    // === GESTIONE IMPORTAZIONE ===
    function handleImportFeed(e) {
        e.preventDefault();
        
        var feedId = $(this).data('feed-id');
        if (!feedId) {
            showNotice('ID feed non valido.', 'error');
            return;
        }
        
        importSingleFeed(feedId, $(this));
    }
    
    function handleImportAllFeeds(e) {
        e.preventDefault();
        
        if (!confirm('Vuoi importare tutti i feed attivi? Questa operazione potrebbe richiedere del tempo.')) {
            return;
        }
        
        var button = $(this);
        button.prop('disabled', true).addClass('loading');
        showLoading('Importazione di tutti i feed in corso...');
        
        var activeFeeds = $('.import-feed').map(function() {
            return $(this).data('feed-id');
        }).get();
        
        importMultipleFeeds(activeFeeds, button);
    }
    
    function importSingleFeed(feedId, button) {
        button.prop('disabled', true).addClass('loading');
        showLoading('Importazione feed in corso...');
        
        $.ajax({
            url: rssImporter.ajaxurl,
            type: 'POST',
            data: {
                action: 'import_rss_feed',
                feed_id: feedId,
                nonce: rssImporter.nonce
            },
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    
                    // Aggiorna le statistiche nella riga
                    updateFeedRow(feedId, response.data);
                    
                    // Ricarica la pagina dopo 2 secondi per aggiornare i dati
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showNotice(response.data.message || 'Errore durante l\'importazione.', 'error');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                showNotice('Errore di connessione: ' + error, 'error');
            },
            complete: function() {
                button.prop('disabled', false).removeClass('loading');
            }
        });
    }
    
    function importMultipleFeeds(feedIds, button) {
        var totalFeeds = feedIds.length;
        var completedFeeds = 0;
        var results = [];
        
        function importNext(index) {
            if (index >= totalFeeds) {
                // Tutti i feed sono stati processati
                hideLoading();
                button.prop('disabled', false).removeClass('loading');
                
                showImportSummary(results);
                
                setTimeout(function() {
                    location.reload();
                }, 3000);
                return;
            }
            
            var feedId = feedIds[index];
            updateLoadingMessage('Importazione feed ' + (index + 1) + ' di ' + totalFeeds + '...');
            
            $.ajax({
                url: rssImporter.ajaxurl,
                type: 'POST',
                data: {
                    action: 'import_rss_feed',
                    feed_id: feedId,
                    nonce: rssImporter.nonce
                },
                success: function(response) {
                    completedFeeds++;
                    results.push({
                        feedId: feedId,
                        success: response.success,
                        message: response.data ? response.data.message : 'Errore sconosciuto'
                    });
                    
                    // Importa il prossimo feed
                    setTimeout(function() {
                        importNext(index + 1);
                    }, 1000); // Pausa di 1 secondo tra le importazioni
                },
                error: function(xhr, status, error) {
                    completedFeeds++;
                    results.push({
                        feedId: feedId,
                        success: false,
                        message: 'Errore di connessione: ' + error
                    });
                    
                    // Continua con il prossimo feed anche in caso di errore
                    setTimeout(function() {
                        importNext(index + 1);
                    }, 1000);
                }
            });
        }
        
        importNext(0);
    }
    
    function showImportSummary(results) {
        var successCount = results.filter(r => r.success).length;
        var errorCount = results.length - successCount;
        
        var message = 'Importazione completata!<br>';
        message += 'Successi: ' + successCount + '<br>';
        message += 'Errori: ' + errorCount;
        
        if (errorCount > 0) {
            message += '<br><br>Dettagli errori:';
            results.filter(r => !r.success).forEach(function(result) {
                message += '<br>Feed ' + result.feedId + ': ' + result.message;
            });
        }
        
        showNotice(message, successCount > errorCount ? 'success' : 'warning');
    }
    
    // === GESTIONE FEED ===
    function handleEditFeed(e) {
        e.preventDefault();
        
        var feedId = $(this).data('feed-id');
        if (!feedId || !window.rssImporterFeeds) {
            showNotice('Impossibile caricare i dati del feed.', 'error');
            return;
        }
        
        var feed = window.rssImporterFeeds.find(f => f.id == feedId);
        if (!feed) {
            showNotice('Feed non trovato.', 'error');
            return;
        }
        
        populateFormWithFeed(feed);
        scrollToForm();
    }
    
    function populateFormWithFeed(feed) {
        $('#feed_id').val(feed.id);
        $('#feed_name').val(feed.name);
        $('#feed_url').val(feed.url);
        $('#feed_status').val(feed.status);
        $('#import_frequency').val(feed.import_frequency);
        $('#post_status').val(feed.post_status);
        $('#author_id').val(feed.author_id);
        $('#auto_categorize').prop('checked', feed.auto_categorize == '1');
        $('#auto_tags').prop('checked', feed.auto_tags == '1');
        
        currentEditingFeed = feed.id;
        
        // Cambia il testo del pulsante
        $('#submit').val('Aggiorna Feed');
        
        // Nasconde il risultato di validazione precedente
        $('#validation_result').hide();
    }
    
    function handleResetForm(e) {
        e.preventDefault();
        
        feedForm[0].reset();
        $('#feed_id').val('');
        currentEditingFeed = null;
        $('#submit').val('Salva Feed');
        $('#validation_result').hide();
        
        showNotice('Form reimpostato per nuovo feed.', 'info');
    }
    
    function handleDeleteFeed(e) {
        e.preventDefault();
        
        var feedId = $(this).data('feed-id');
        var feedName = $(this).closest('tr').find('.column-name strong').text();
        
        if (!confirm(rssImporter.strings.confirm_delete + '\n\nFeed: ' + feedName)) {
            return;
        }
        
        var button = $(this);
        var row = button.closest('tr');
        
        button.prop('disabled', true);
        row.addClass('deleting');
        
        $.ajax({
            url: rssImporter.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_feed',
                feed_id: feedId,
                nonce: rssImporter.nonce
            },
            success: function(response) {
                if (response.success) {
                    row.fadeOut(function() {
                        $(this).remove();
                        
                        // Controlla se ci sono ancora feed
                        if ($('.wp-list-table tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                    showNotice(response.data || 'Feed eliminato con successo.', 'success');
                } else {
                    showNotice(response.data || 'Errore durante l\'eliminazione.', 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotice('Errore di connessione: ' + error, 'error');
            },
            complete: function() {
                button.prop('disabled', false);
                row.removeClass('deleting');
            }
        });
    }
    
    // === GESTIONE IMPOSTAZIONI ===
    function handleTestConnection(e) {
        e.preventDefault();
        
        var url = $('#test_feed_url').val().trim();
        if (!url) {
            showTestResult('Inserisci un URL per testare la connessione.', 'error');
            return;
        }
        
        if (!isValidUrl(url)) {
            showTestResult('L\'URL inserito non è valido.', 'error');
            return;
        }
        
        var button = $(this);
        button.prop('disabled', true).addClass('loading');
        showTestResult('Test connessione in corso...', 'loading');
        
        $.ajax({
            url: rssImporter.ajaxurl,
            type: 'POST',
            data: {
                action: 'validate_rss_feed',
                url: url,
                nonce: rssImporter.nonce
            },
            success: function(response) {
                if (response.success) {
                    showTestResult('Connessione riuscita! ' + response.data.message, 'success');
                } else {
                    showTestResult('Connessione fallita: ' + response.data.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                showTestResult('Errore di connessione: ' + error, 'error');
            },
            complete: function() {
                button.prop('disabled', false).removeClass('loading');
            }
        });
    }
    
    function showTestResult(message, type) {
        var resultDiv = $('#test_result');
        resultDiv.removeClass('success error loading')
                 .addClass(type)
                 .html(message)
                 .slideDown();
        
        if (type !== 'loading') {
            setTimeout(function() {
                resultDiv.slideUp();
            }, 5000);
        }
    }
    
    function handleClearLogs(e) {
        e.preventDefault();
        
        if (!confirm('Vuoi eliminare tutti i log di importazione più vecchi di 30 giorni?')) {
            return;
        }
        
        performMaintenanceAction('clear_logs', $(this), 'Log puliti con successo.');
    }
    
    function handleResetStats(e) {
        e.preventDefault();
        
        if (!confirm('Vuoi reimpostare tutte le statistiche dei feed? Questa azione non può essere annullata.')) {
            return;
        }
        
        performMaintenanceAction('reset_stats', $(this), 'Statistiche reimpostate con successo.');
    }
    
    function handleForceCronRun(e) {
        e.preventDefault();
        
        performMaintenanceAction('force_cron', $(this), 'Importazione programmata eseguita.');
    }
    
    function handleResetToDefaults(e) {
        e.preventDefault();
        
        if (!confirm('Vuoi ripristinare tutte le impostazioni ai valori predefiniti?')) {
            return;
        }
        
        // Resetta il form alle impostazioni predefinite
        resetFormToDefaults();
        showNotice('Impostazioni ripristinate ai valori predefiniti. Ricorda di salvare.', 'info');
    }
    
    function performMaintenanceAction(action, button, successMessage) {
        button.prop('disabled', true).addClass('loading');
        
        $.ajax({
            url: rssImporter.ajaxurl,
            type: 'POST',
            data: {
                action: 'rss_importer_maintenance',
                maintenance_action: action,
                nonce: rssImporter.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice(successMessage, 'success');
                } else {
                    showNotice(response.data || 'Errore durante l\'operazione.', 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotice('Errore di connessione: ' + error, 'error');
            },
            complete: function() {
                button.prop('disabled', false).removeClass('loading');
            }
        });
    }
    
    // === AZIONI IN BLOCCO ===
    function handleBulkActionChange() {
        var action = $(this).val();
        var selectedCount = $('input[name="post[]"]:checked').length;
        
        if (action !== '-1' && selectedCount === 0) {
            showNotice('Seleziona almeno un post per eseguire l\'azione.', 'warning');
            $(this).val('-1');
        }
    }
    
    function handleBulkAction(e) {
        e.preventDefault();
        
        var action = $('#bulk-action-selector-top').val();
        var selectedPosts = $('input[name="post[]"]:checked').map(function() {
            return this.value;
        }).get();
        
        if (action === '-1') {
            showNotice('Seleziona un\'azione da eseguire.', 'warning');
            return;
        }
        
        if (selectedPosts.length === 0) {
            showNotice('Seleziona almeno un post.', 'warning');
            return;
        }
        
        var actionText = $('#bulk-action-selector-top option:selected').text();
        if (!confirm('Vuoi eseguire "' + actionText + '" su ' + selectedPosts.length + ' post selezionati?')) {
            return;
        }
        
        performBulkAction(action, selectedPosts);
    }
    
    function performBulkAction(action, postIds) {
        showLoading('Esecuzione azione in blocco...');
        
        $.ajax({
            url: rssImporter.ajaxurl,
            type: 'POST',
            data: {
                action: 'rss_importer_bulk_action',
                bulk_action: action,
                post_ids: postIds,
                nonce: rssImporter.nonce
            },
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    
                    // Ricarica la pagina per mostrare i cambiamenti
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showNotice(response.data.message || 'Errore durante l\'azione in blocco.', 'error');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                showNotice('Errore di connessione: ' + error, 'error');
            }
        });
    }
    
    function handleSelectAll() {
        var checked = this.checked;
        $('input[name="post[]"]').prop('checked', checked);
    }
    
    // === UTILITY FUNCTIONS ===
    function showLoading(message) {
        if (message) {
            updateLoadingMessage(message);
        }
        loadingOverlay.show();
    }
    
    function hideLoading() {
        loadingOverlay.hide();
    }
    
    function updateLoadingMessage(message) {
        $('#loading_message').text(message);
    }
    
    function showNotice(message, type) {
        type = type || 'info';
        
        var noticeClass = 'notice-' + type;
        var notice = $('<div class="notice ' + noticeClass + ' is-dismissible rss-importer-notice"><p>' + message + '</p></div>');
        
        // Rimuovi notice precedenti
        $('.rss-importer-notice').remove();
        
        // Aggiungi la nuova notice
        $('.wrap h1').after(notice);
        
        // Auto-rimuovi dopo 5 secondi
        setTimeout(function() {
            notice.slideUp(function() {
                $(this).remove();
            });
        }, 5000);
        
        // Scroll verso l'alto per mostrare la notice
        $('html, body').animate({
            scrollTop: $('.wrap').offset().top - 50
        }, 300);
    }
    
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    function validateUrlFormat() {
        var url = $('#feed_url').val().trim();
        var urlField = $('#feed_url');
        
        if (url && !isValidUrl(url)) {
            urlField.addClass('error');
            showValidationResult('Formato URL non valido.', 'error');
        } else {
            urlField.removeClass('error');
            $('#validation_result').slideUp();
        }
    }
    
    function updateFeedRow(feedId, data) {
        var row = $('tr[data-feed-id="' + feedId + '"]');
        if (row.length > 0 && data.imported_count) {
            // Aggiorna il contatore dei post importati
            var totalCell = row.find('.column-total strong');
            var currentTotal = parseInt(totalCell.text()) || 0;
            totalCell.text(currentTotal + data.imported_count);
            
            // Aggiorna la data dell'ultima importazione
            var dateCell = row.find('.column-last-import');
            dateCell.html('<strong>Adesso</strong>');
        }
    }
    
    function scrollToForm() {
        $('html, body').animate({
            scrollTop: feedForm.offset().top - 50
        }, 500);
    }
    
    function resetFormToDefaults() {
        $('#max_posts_per_import').val('10');
        $('#duplicate_check_method').val('title_url');
        $('#default_post_status').val('draft');
        $('#category_creation_method').val('auto');
        $('#tag_creation_method').val('auto');
        $('#image_import').prop('checked', true);
        $('#excerpt_length').val('150');
    }
    
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        
        return text.replace(/[&<>"']/g, function(m) {
            return map[m];
        });
    }
    
    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
    
    // === INIZIALIZZAZIONE FINALE ===
    
    // Mostra un messaggio se non ci sono feed configurati
    if ($('.wp-list-table tbody tr').length === 0 && $('.rss-importer-feeds-section').length > 0) {
        $('.rss-importer-feeds-section').append(
            '<div class="no-feeds-message" style="text-align: center; padding: 20px; color: #666;">' +
            '<p style="font-size: 16px;">🚀 Inizia aggiungendo il tuo primo feed RSS!</p>' +
            '<p>Compila il modulo sopra per iniziare a importare contenuti automaticamente.</p>' +
            '</div>'
        );
    }
    
    // Evidenzia il feed in modifica
    if (currentEditingFeed) {
        $('tr[data-feed-id="' + currentEditingFeed + '"]').addClass('editing-highlight');
    }
    
    // Tooltip informativi
    $('[data-tooltip]').each(function() {
        $(this).attr('title', $(this).data('tooltip'));
    });
    
    console.log('RSS Feed Importer Admin JS initialized successfully');
});