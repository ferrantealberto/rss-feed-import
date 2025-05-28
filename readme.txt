# RSS Feed Importer Pro - Plugin WordPress

Un plugin completo per importare automaticamente contenuti da feed RSS esterni, con generazione intelligente di categorie e tag, programmazione avanzata e gestione completa dei post importati.

## 🚀 Caratteristiche Principali

### ✨ Importazione Avanzata
- **Importazione automatica programmata** (ogni ora, giornaliera, settimanale)
- **Validazione feed RSS** in tempo reale
- **Controllo duplicati** intelligente (titolo, URL, o entrambi)
- **Gestione errori** completa con log dettagliati

### 🏷️ Gestione Contenuti
- **Generazione automatica categorie** basata sul contenuto
- **Creazione tag intelligente** tramite analisi delle parole chiave
- **Pulizia contenuto HTML** con rimozione elementi pericolosi
- **Supporto immagini** con importazione locale opzionale

### ⚡ Interfaccia Amministrazione
- **Dashboard intuitiva** per gestione feed
- **Elenco completo post importati** con filtri avanzati
- **Statistiche dettagliate** su importazioni e performance
- **Azioni in blocco** per gestire più post contemporaneamente

### 🔧 Configurazione Avanzata
- **Impostazioni granulari** per ogni feed
- **Personalizzazione metodi** di creazione categorie/tag
- **Controlli sicurezza** integrati
- **Backup e esportazione** dati

## 📁 Struttura File Plugin

```
rss-feed-importer/
├── rss-feed-importer.php          # File principale plugin
├── uninstall.php                  # Script disinstallazione
├── readme.txt                     # Documentazione WordPress
├── includes/
│   ├── ajax-handlers.php          # Gestori AJAX
│   └── helpers.php                # Funzioni utilità
├── templates/
│   ├── admin-page.php             # Pagina gestione feed
│   ├── imported-posts.php         # Pagina post importati
│   └── settings-page.php          # Pagina impostazioni
├── assets/
│   ├── admin.css                  # Stili amministrazione
│   └── admin.js                   # JavaScript amministrazione
└── languages/
    └── rss-feed-importer.pot      # File traduzioni
```

## 🛠️ Installazione

### Metodo 1: Upload Manuale
1. Scarica tutti i file del plugin
2. Crea la cartella `rss-feed-importer` in `/wp-content/plugins/`
3. Carica tutti i file mantenendo la struttura delle cartelle
4. Attiva il plugin dal pannello WordPress

### Metodo 2: Upload ZIP
1. Comprimi tutti i file in un file `rss-feed-importer.zip`
2. Va in WordPress Admin → Plugin → Aggiungi nuovo → Carica plugin
3. Seleziona il file ZIP e installa
4. Attiva il plugin

## ⚙️ Configurazione Iniziale

### 1. Impostazioni Base
1. Vai in **RSS Importer → Impostazioni**
2. Configura:
   - **Numero massimo post per importazione**: 10 (raccomandato)
   - **Metodo controllo duplicati**: Titolo + URL
   - **Stato post predefinito**: Bozza (per revisione)

### 2. Aggiunta Primo Feed
1. Vai in **RSS Importer → Gestione Feed**
2. Compila il modulo:
   - **Nome Feed**: Nome identificativo
   - **URL Feed RSS**: URL completo del feed
   - **Clicca "Valida Feed"** per verificare la correttezza
3. Configura:
   - **Frequenza importazione**: Ogni ora/Giornaliera/Settimanale
   - **Stato post**: Bozza/Pubblicato/In attesa
   - **Opzioni automatiche**: Categorie e tag automatici
4. Salva il feed

### 3. Test Importazione
1. Clicca **"Importa Ora"** sul feed appena creato
2. Verifica i risultati in **RSS Importer → Post Importati**
3. Controlla che categorie e tag siano stati creati correttamente

## 📖 Guida all'Uso

### Gestione Feed RSS

#### Aggiungere un Feed
```
1. RSS Importer → Gestione Feed
2. Inserisci URL del feed (es: https://example.com/feed.rss)
3. Clicca "Valida Feed" per verificare
4. Configura le opzioni:
   - Nome identificativo
   - Frequenza importazione
   - Stato dei post importati
   - Autore assegnato
   - Generazione automatica categorie/tag
5. Salva il feed
```

#### Modificare un Feed
```
1. Nella lista feed, clicca "Modifica" sulla riga del feed
2. I dati verranno caricati nel modulo sopra
3. Modifica i campi necessari
4. Salva le modifiche
```

#### Importazione Manuale
```
1. Clicca "Importa Ora" su un feed specifico
2. Oppure "Importa Tutti i Feed" per tutti i feed attivi
3. Monitora il progresso nella finestra di caricamento
4. Verifica i risultati nel messaggio di conferma
```

### Gestione Post Importati

#### Visualizzare Post Importati
```
1. RSS Importer → Post Importati
2. Usa i filtri per:
   - Feed specifico
   - Stato importazione (successo/errore/duplicato)
   - Periodo (dal/al)
3. Visualizza dettagli come:
   - Titolo originale e post WordPress
   - Categorie/tag creati
   - Link al post originale
```

#### Azioni in Blocco
```
1. Seleziona i post dalla checkbox
2. Scegli azione dal menu dropdown:
   - Pubblica
   - Porta in bozza
   - Sposta nel cestino
3. Clicca "Applica"
```

#### Esportazione Dati
```
1. Nella pagina Post Importati, scorri in basso
2. Sezione "Esporta Dati":
   - Scegli formato (CSV/JSON)
   - Seleziona periodo
   - Clicca "Esporta"
3. Il file verrà scaricato automaticamente
```

### Configurazione Avanzata

#### Impostazioni Importazione
- **Max post per importazione**: Limita per evitare timeout
- **Controllo duplicati**: Come identificare post già importati
- **Stato post predefinito**: Bozza per revisione, Pubblicato per automatico

#### Categorie e Tag
- **Creazione categorie**:
  - Automatico: Estrae parole chiave dal titolo
  - Da feed RSS: Usa categorie presenti nel feed
  - Categoria fissa: Assegna sempre la stessa categoria
- **Creazione tag**: Analisi automatica del contenuto per parole chiave

#### Sicurezza
- **Rimozione shortcode**: Pulisce shortcode potenzialmente dannosi
- **Sanitizzazione HTML**: Rimuove elementi pericolosi
- **Link esterni**: Aggiunge rel="nofollow" automaticamente

## 🔧 Risoluzione Problemi

### Feed Non Valido
**Problema**: Errore "Feed RSS non valido"
**Soluzioni**:
1. Verifica che l'URL sia corretto e raggiungibile
2. Controlla che il feed sia in formato RSS valido
3. Testa l'URL in un lettore RSS esterno
4. Verifica che il server non richieda autenticazione

### Importazione Fallisce
**Problema**: "Errore durante l'importazione"
**Soluzioni**:
1. Controlla i log in Impostazioni → Debug
2. Verifica connessione internet del server
3. Aumenta memory_limit PHP se necessario
4. Riduci "Max post per importazione" nelle impostazioni

### Post Duplicati
**Problema**: Post importati più volte
**Soluzioni**:
1. Impostazioni → Controllo duplicati → "Titolo + URL"
2. Verifica che l'URL del feed sia sempre lo stesso
3. Controlla che titoli dei post siano univoci

### Cron Non Funziona
**Problema**: Importazione automatica non avviene
**Soluzioni**:
1. Verifica in Impostazioni se WP Cron è attivo
2. Se disabilitato, configura cron del server:
   ```bash
   */15 * * * * wget -q -O - http://tuosito.com/wp-cron.php
   ```
3. Oppure usa "Esegui Importazione Ora" manualmente

### Errori di Memoria
**Problema**: "Fatal error: Allowed memory size exhausted"
**Soluzioni**:
1. Aumenta memory_limit in wp-config.php:
   ```php
   ini_set('memory_limit', '256M');
   ```
2. Riduci numero massimo post per importazione
3. Contatta il provider hosting per aumentare i limiti

## 📊 Monitoraggio e Statistiche

### Dashboard Statistiche
- **Post totali importati**: Conteggio complessivo
- **Importazioni oggi**: Attività giornaliera
- **Successi vs Errori**: Ratio affidabilità
- **Feed più attivi**: Performance per feed

### Log e Debug
1. **Impostazioni → Debug** per informazioni sistema
2. **Test connessione** per verificare raggiungibilità feed
3. **Log dettagliati** in `wp-content/rss-importer.log` (se WP_DEBUG attivo)

## 🔒 Sicurezza e Privacy

### Misure di Sicurezza
- Validazione e sanitizzazione di tutti gli input
- Controllo permessi utente per ogni azione
- Pulizia automatica contenuto HTML
- Prevenzione XSS e injection attacks

### Privacy
- Nessun dato inviato a server esterni
- Log locali opzionali
- Possibilità di eliminare completamente tutti i dati

## 🛡️ Backup e Ripristino

### Backup Automatico
Il plugin non sovrascrive dati esistenti e mantiene:
- Log delle importazioni
- Collegamento post originali
- Metadati per tracciabilità

### Backup Manuale
1. **Esporta feed**: Salva configurazioni feed
2. **Esporta post**: CSV/JSON di tutti i post importati
3. **Database**: Backup delle tabelle plugin:
   - `wp_rss_importer_feeds`
   - `wp_rss_importer_imports`

### Disinstallazione Pulita
Il file `uninstall.php` rimuove automaticamente:
- Tabelle database
- Opzioni WordPress
- Cron job
- Metadati post
- File cache

## 📚 Estensioni e Personalizzazioni

### Hook Disponibili
```php
// Prima dell'importazione
do_action('rss_importer_before_import', $feed_id, $items);

// Dopo l'importazione
do_action('rss_importer_after_import', $feed_id, $results);

// Filtro contenuto
$content = apply_filters('rss_importer_filter_content', $content, $item);

// Filtro categorie
$categories = apply_filters('rss_importer_categories', $categories, $item);
```

### Personalizzazioni Comuni
```php
// Modifica stato post automaticamente
add_filter('rss_importer_post_status', function($status, $feed_id) {
    if ($feed_id == 1) {
        return 'publish'; // Pubblica automaticamente feed ID 1
    }
    return $status;
}, 10, 2);

// Aggiungi prefisso ai titoli
add_filter('rss_importer_post_title', function($title, $feed_id) {
    return '[NEWS] ' . $title;
}, 10, 2);
```

## 📞 Supporto

### Documentazione
- README completo (questo file)
- Commenti dettagliati nel codice
- Log di debug integrati

### Risoluzione Problemi
1. Controlla requisiti sistema
2. Verifica configurazione server
3. Consulta log degli errori
4. Testa con feed RSS pubblici noti

### Segnalazione Bug
Quando segnali un problema, includi:
- Versione WordPress
- Versione PHP
- URL feed che causa problemi
- Messaggi di errore completi
- Log del plugin (se disponibile)

## 📝 Licenza

GPL v2 or later - Uso libero per progetti personali e commerciali.

## 🔄 Aggiornamenti

Il plugin controlla automaticamente:
- Compatibilità versioni WordPress
- Aggiornamenti struttura database
- Migrazioni impostazioni

Per aggiornamenti manuali:
1. Backup completo del sito
2. Disattiva plugin
3. Sostituisci file plugin
4. Riattiva plugin

---

## ✅ Checklist Post-Installazione

- [ ] Plugin attivato correttamente
- [ ] Tabelle database create
- [ ] Primo feed RSS aggiunto e testato
- [ ] Importazione manuale funzionante
- [ ] Cron automatico configurato (se desiderato)
- [ ] Impostazioni sicurezza verificate
- [ ] Backup procedure configurate

**Plugin sviluppato per WordPress 5.0+ con PHP 7.4+**

*Ultimo aggiornamento: 2025*