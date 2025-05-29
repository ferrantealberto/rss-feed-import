# RSS Feed Importer Pro - Plugin WordPress

Un plugin completo per importare automaticamente contenuti da feed RSS esterni, con generazione intelligente di categorie e tag, importazione immagini in evidenza, programmazione avanzata e gestione completa dei post importati.

## 🚀 Caratteristiche Principali

### ✨ Importazione Avanzata
- **Importazione automatica programmata** (ogni ora, giornaliera, settimanale)
- **Validazione feed RSS** in tempo reale
- **Controllo duplicati** intelligente (titolo, URL, o entrambi)
- **Gestione errori** completa con log dettagliati
- **Importazione immagini in evidenza** dal feed RSS originale

### 🏷️ Gestione Contenuti
- **Generazione automatica categorie** basata sul contenuto
- **Creazione tag intelligente** tramite analisi delle parole chiave
- **Pulizia contenuto HTML** con rimozione elementi pericolosi
- **Supporto immagini** con importazione locale e fallback su immagine predefinita

### 🖼️ Gestione Immagini in Evidenza
- **Importazione automatica** dell'immagine in evidenza dal feed RSS
- **Rilevamento intelligente** dell'immagine da varie fonti (enclosure, media:content, media:thumbnail, contenuto HTML)
- **Immagine predefinita** configurabile come fallback
- **Ottimizzazione immagini** con ridimensionamento automatico
- **Libreria media integrata** per gestione completa delle immagini

### ⚡ Interfaccia Amministrazione
- **Dashboard intuitiva** per gestione feed
- **Elenco completo post importati** con filtri avanzati e stato importazione immagini
- **Statistiche dettagliate** su importazioni e performance
- **Azioni in blocco** per gestire più post contemporaneamente

### 🔧 Configurazione Avanzata
- **Impostazioni granulari** per ogni feed
- **Personalizzazione metodi** di creazione categorie/tag
- **Controlli sicurezza** integrati
- **Backup e esportazione** dati con informazioni sulle immagini

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
   - **Importazione immagini**: Abilitata (per immagini in evidenza)
   - **Immagine predefinita**: Seleziona un'immagine di fallback

### 2. Configurazione Immagini
1. In **RSS Importer → Impostazioni → Gestione Media**
2. Abilita **"Importa e salva immagini in evidenza localmente"**
3. Seleziona un'**immagine predefinita** usando il media uploader
4. L'immagine predefinita verrà utilizzata quando:
   - Il feed RSS non contiene immagini
   - L'immagine originale non è scaricabile
   - Si verificano errori durante l'importazione

### 3. Aggiunta Primo Feed
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

### 4. Test Importazione
1. Clicca **"Importa Ora"** sul feed appena creato
2. Verifica i risultati in **RSS Importer → Post Importati**
3. Controlla che categorie, tag e immagini in evidenza siano stati creati correttamente

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

#### Importazione Automatica Immagini
Il plugin cerca automaticamente immagini in evidenza da:
- **Enclosure RSS**: Tag enclosure con type="image/*"
- **Media RSS**: Tag media:content e media:thumbnail
- **Contenuto HTML**: Prima immagine trovata nel contenuto del post
- **Fallback**: Immagine predefinita configurata nelle impostazioni

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
   - Stato importazione immagine in evidenza
   - Link al post originale
```

#### Monitoraggio Immagini
Nella pagina **Post Importati** puoi vedere:
- **Icona immagine** per post con immagine in evidenza importata
- **Stato importazione** per ogni immagine
- **Statistiche** su successi e fallimenti nell'importazione immagini

### Configurazione Avanzata

#### Impostazioni Importazione
- **Max post per importazione**: Limita per evitare timeout
- **Controllo duplicati**: Come identificare post già importati
- **Stato post predefinito**: Bozza per revisione, Pubblicato per automatico

#### Gestione Immagini
- **Importazione automatica**: Scarica e salva immagini nella libreria media
- **Immagine predefinita**: Fallback quando l'importazione fallisce
- **Ottimizzazione**: Ridimensionamento automatico per performance

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

### Immagini Non Importate
**Problema**: Le immagini in evidenza non vengono importate
**Soluzioni**:
1. Verifica che l'opzione "Importa immagini" sia abilitata nelle impostazioni
2. Controlla che il feed RSS contenga immagini nei formati supportati
3. Verifica che l'immagine predefinita sia configurata come fallback
4. Controlla i permessi di scrittura nella cartella uploads di WordPress
5. Aumenta il memory_limit PHP se necessario

### Immagini di Bassa Qualità
**Problema**: Le immagini importate hanno qualità scadente
**Soluzioni**:
1. Il plugin cerca automaticamente la migliore qualità disponibile
2. Controlla che il feed RSS fornisca immagini ad alta risoluzione
3. Considera l'utilizzo di un'immagine predefinita di alta qualità

### Errori di Memoria con Immagini
**Problema**: "Fatal error: Allowed memory size exhausted" con importazione immagini
**Soluzioni**:
1. Aumenta memory_limit in wp-config.php:
   ```php
   ini_set('memory_limit', '512M');
   ```
2. Riduci numero massimo post per importazione
3. Considera disabilitare temporaneamente l'importazione immagini per feed con molte immagini grandi

### Feed Senza Immagini
**Problema**: Alcuni feed RSS non hanno immagini in evidenza
**Soluzioni**:
1. Configura un'immagine predefinita nelle impostazioni
2. L'immagine predefinita verrà assegnata automaticamente
3. Puoi cambiare l'immagine predefinita in qualsiasi momento

## 📊 Formati Immagine Supportati

### Tipi di Immagine
- **JPEG/JPG**: Formato più comune, buona compressione
- **PNG**: Supporto trasparenza, qualità elevata
- **GIF**: Supporto animazioni (mantenute)
- **WebP**: Formato moderno, ottima compressione

### Fonti di Rilevamento
1. **RSS Enclosure**: `<enclosure url="image.jpg" type="image/jpeg" />`
2. **Media RSS**: `<media:content url="image.jpg" type="image/jpeg" />`
3. **Media Thumbnail**: `<media:thumbnail url="thumb.jpg" />`
4. **Contenuto HTML**: Prima immagine nel contenuto del post

### Ottimizzazioni Automatiche
- **Ridimensionamento**: Immagini ottimizzate per web
- **Compressione**: Bilanciamento qualità/dimensione
- **Metadati**: Generazione automatica thumbnails WordPress
- **Sicurezza**: Validazione tipo MIME per sicurezza

## 🛡️ Backup e Ripristino

### Backup Automatico
Il plugin mantiene traccia di:
- Log delle importazioni con stato immagini
- Collegamento alle immagini originali
- Metadati per tracciabilità completa

### Gestione Immagini
- **Libreria Media**: Tutte le immagini importate finiscono nella libreria media
- **Identificazione**: Immagini importate hanno prefisso "rss-import-"
- **Pulizia**: Script di disinstallazione può rimuovere immagini importate (opzionale)

### Disinstallazione Sicura
Il file `uninstall.php` può rimuovere:
- Tabelle database (incluse info immagini)
- Opzioni WordPress
- Metadati post
- **NOTA**: Le immagini nella libreria media sono preservate per sicurezza

## 📚 Esempi Pratici

### Caso d'Uso: Blog di Notizie
```
Configurazione ottimale:
- Importazione immagini: ON
- Immagine predefinita: Logo del sito
- Frequenza: Ogni ora
- Stato post: Bozza (per revisione)
- Categorie: Automatiche da feed
```

### Caso d'Uso: Aggregatore Contenuti
```
Configurazione ottimale:
- Importazione immagini: ON
- Immagine predefinita: Immagine generica del topic
- Frequenza: Giornaliera
- Stato post: Pubblicato
- Controllo duplicati: Titolo + URL
```

### Caso d'Uso: Portfolio/Gallery
```
Configurazione ottimale:
- Importazione immagini: ON (essenziale)
- Immagine predefinita: Placeholder elegante
- Qualità: Massima
- Revisione manuale: Consigliata
```

## 🔄 Aggiornamenti

### Novità v1.0.0
- ✅ Importazione automatica immagini in evidenza
- ✅ Rilevamento intelligente da multiple fonti
- ✅ Immagine predefinita configurabile
- ✅ Ottimizzazione automatica immagini
- ✅ Integrazione libreria media WordPress
- ✅ Statistiche dettagliate importazione immagini
- ✅ Gestione errori avanzata per immagini
- ✅ Supporto formati immagine moderni (WebP)

### Compatibilità
- ✅ WordPress 5.0+
- ✅ PHP 7.4+
- ✅ Estensioni: GD/ImageMagick per elaborazione immagini
- ✅ Libreria media WordPress
- ✅ Multisite
- ✅ Temi personalizzati

## 📞 Supporto Tecnico

### Segnalazione Problemi Immagini
Quando segnali problemi con le immagini, includi:
- URL feed RSS
- Esempi di post con immagini problematiche
- Messaggio di errore (se presente)
- Screenshot dell'immagine predefinita configurata
- Impostazioni di importazione attuali

### Debug Immagini
1. Verifica che il feed contenga immagini: controlla il codice sorgente RSS
2. Testa il download manuale dell'immagine dal browser
3. Controlla i log di WordPress per errori di importazione
4. Verifica permessi cartella uploads

---

## ✅ Checklist Post-Installazione (Aggiornata)

- [ ] Plugin attivato correttamente
- [ ] Tabelle database create (inclusa colonna immagini)
- [ ] Primo feed RSS aggiunto e testato
- [ ] Importazione manuale funzionante
- [ ] **Immagini in evidenza configurate**:
  - [ ] Importazione immagini abilitata
  - [ ] Immagine predefinita selezionata
  - [ ] Test importazione con immagine riuscito
- [ ] Cron automatico configurato (se desiderato)
- [ ] Impostazioni sicurezza verificate
- [ ] Backup procedure configurate

**Plugin sviluppato per WordPress 5.0+ con PHP 7.4+ e supporto completo per immagini in evidenza**

*Ultimo aggiornamento: 2025 - Versione 1.0.0 con supporto immagini*