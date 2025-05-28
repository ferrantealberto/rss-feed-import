=== RSS Feed Importer Pro ===
Contributors: yourusername
Donate link: https://your-website.com/donate
Tags: rss, feed, import, automation, content, posts, categories, tags, scheduling
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin completo per importare automaticamente contenuti da feed RSS con generazione intelligente di categorie, tag e programmazione avanzata.

== Description ==

RSS Feed Importer Pro è un plugin WordPress avanzato che permette di importare automaticamente contenuti da feed RSS esterni, creando post WordPress completi con categorie e tag generati intelligentemente.

= Caratteristiche Principali =

**🚀 Importazione Automatica**
* Programmazione flessibile (oraria, giornaliera, settimanale)
* Validazione feed RSS in tempo reale
* Controllo duplicati intelligente
* Gestione errori completa con log dettagliati

**🏷️ Gestione Contenuti Intelligente**
* Generazione automatica categorie basata sul contenuto
* Creazione tag tramite analisi delle parole chiave
* Pulizia automatica HTML e rimozione contenuti pericolosi
* Supporto importazione immagini (opzionale)

**⚡ Dashboard Amministrazione**
* Interfaccia intuitiva per gestione feed
* Elenco completo post importati con filtri avanzati
* Statistiche dettagliate su importazioni e performance
* Azioni in blocco per gestione multipla post

**🔧 Configurazione Avanzata**
* Impostazioni granulari per ogni singolo feed
* Personalizzazione metodi creazione categorie/tag
* Controlli sicurezza integrati
* Esportazione dati e backup

= Caso d'Uso Ideale =

Perfetto per:
* Siti di notizie che aggregano contenuti
* Blog che curano contenuti da più fonti
* Portali che necessitano aggiornamenti automatici
* Aziende che vogliono sincronizzare contenuti

= Funzionalità Avanzate =

**Controllo Duplicati**
* Verifica per titolo, URL o entrambi
* Prevenzione automatica post duplicati
* Log delle importazioni respinte

**Sicurezza Integrata**
* Sanitizzazione automatica contenuti HTML
* Rimozione shortcode potenzialmente pericolosi
* Controllo permessi utente granulari

**Automazione Completa**
* Cron job WordPress integrato
* Importazione programmata senza intervento manuale
* Notifiche email opzionali per amministratori

== Installation ==

= Installazione Automatica =

1. Vai in WordPress Admin → Plugin → Aggiungi nuovo
2. Cerca "RSS Feed Importer Pro"
3. Clicca "Installa" e poi "Attiva"
4. Vai in RSS Importer nel menu admin per configurare

= Installazione Manuale =

1. Scarica il file ZIP del plugin
2. Vai in WordPress Admin → Plugin → Aggiungi nuovo → Carica plugin
3. Seleziona il file ZIP e clicca "Installa ora"
4. Attiva il plugin
5. Vai in RSS Importer → Gestione Feed per iniziare

= Configurazione Post-Installazione =

1. **RSS Importer → Impostazioni**: Configura le opzioni base
2. **RSS Importer → Gestione Feed**: Aggiungi il tuo primo feed RSS
3. **Testa l'importazione**: Usa "Importa Ora" per verificare il funzionamento
4. **Configura automazione**: Imposta la frequenza di importazione desiderata

== Frequently Asked Questions ==

= Il plugin funziona con tutti i feed RSS? =

Sì, il plugin supporta tutti i feed RSS/Atom standard. Include una funzione di validazione che verifica la compatibilità prima dell'importazione.

= Posso importare da più feed contemporaneamente? =

Assolutamente! Puoi aggiungere un numero illimitato di feed RSS, ognuno con le proprie impostazioni di importazione e programmazione.

= Come viene gestito il controllo dei duplicati? =

Il plugin offre tre metodi di controllo duplicati:
- Solo titolo del post
- Solo URL sorgente
- Titolo + URL (raccomandato)

= Le categorie e i tag vengono creati automaticamente? =

Sì, il plugin può:
- Creare categorie basate sul contenuto del post
- Utilizzare categorie presenti nel feed RSS
- Assegnare una categoria fissa per feed
- Generare tag tramite analisi delle parole chiave

= Cosa succede se un feed RSS non è raggiungibile? =

Il plugin gestisce automaticamente gli errori:
- Log dettagliato dell'errore
- Ritenta l'importazione al prossimo ciclo programmato
- Notifica opzionale all'amministratore

= Posso modificare i post dopo l'importazione? =

Certamente! I post importati sono normali post WordPress che puoi modificare, pubblicare o eliminare come qualsiasi altro contenuto.

= Il plugin supporta le immagini? =

Sì, con l'opzione "Importazione immagini" abilitata, il plugin può scaricare e salvare le immagini nella libreria media di WordPress.

= Come funziona la programmazione automatica? =

Il plugin utilizza il sistema cron di WordPress. Puoi impostare:
- Importazione ogni ora
- Importazione giornaliera
- Importazione settimanale

= Posso esportare i dati importati? =

Sì, dalla pagina "Post Importati" puoi esportare tutti i dati in formato CSV o JSON per backup o analisi.

= Il plugin è sicuro? =

Il plugin include multiple misure di sicurezza:
- Sanitizzazione automatica di tutto il contenuto HTML
- Rimozione di shortcode e script potenzialmente pericolosi
- Controllo permessi utente per ogni azione
- Validazione rigorosa di tutti gli input

== Screenshots ==

1. **Dashboard principale** - Interfaccia per gestione feed RSS
2. **Aggiunta feed** - Modulo per configurare nuovo feed con validazione
3. **Post importati** - Elenco completo con filtri e statistiche
4. **Impostazioni** - Configurazione avanzata del plugin
5. **Statistiche** - Dashboard con metriche di importazione
6. **Azioni in blocco** - Gestione multipla post importati

== Changelog ==

= 1.0.0 =
* Rilascio iniziale
* Importazione automatica feed RSS
* Generazione automatica categorie e tag
* Dashboard amministrazione completa
* Sistema programmazione avanzato
* Controllo duplicati intelligente
* Esportazione dati CSV/JSON
* Gestione errori e log dettagliati
* Interfaccia responsive e accessibile

== Upgrade Notice ==

= 1.0.0 =
Prima versione stabile del plugin. Installazione pulita senza necessità di migrazione dati.

== Support ==

Per supporto tecnico e documentazione completa:

* **Documentazione**: Consulta il file README.md incluso nel plugin
* **FAQ**: Sezione domande frequenti sopra
* **Test sistema**: Usa la funzione "Test connessione" nelle impostazioni
* **Log debug**: Abilita WP_DEBUG per log dettagliati

= Requisiti Sistema =

* WordPress 5.0 o superiore
* PHP 7.4 o superiore
* Estensioni PHP: simplexml, curl, json
* Memory limit: 128MB raccomandato
* Connessione internet attiva per importazione

= Compatibilità =

* ✅ WordPress Multisite
* ✅ Temi personalizzati
* ✅ Plugin caching (W3 Total Cache, WP Rocket)
* ✅ Plugin SEO (Yoast, RankMath)
* ✅ Plugin di sicurezza
* ✅ Gutenberg e Classic Editor

= Performance =

* Importazione ottimizzata per evitare timeout
* Limitazione automatica post per esecuzione
* Cache intelligente per feed frequenti
* Pulizia automatica log vecchi

== Privacy ==

Questo plugin:
* Non invia dati a server esterni
* Non traccia gli utenti
* Salva solo log locali opzionali
* Permette eliminazione completa dati

I dati importati rimangono sul tuo server WordPress.