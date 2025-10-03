=== Custom Product Reviews ===
Contributors: gerri-lebowski
Tags: reviews, woocommerce, stars, accordion, consent, gdpr
Requires at least: 6.0
Tested up to: 6.6
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sistema di recensioni personalizzato per WooCommerce, con selezione a stelline interattiva, form in accordion e raccolta consensi privacy/marketing.

== Descrizione ==
Questo plugin aggiunge un sistema di recensioni esterno a quello nativo WooCommerce, con:
- Stelline interattive (hover + click) per la valutazione (1–5).
- Form "Lascia una recensione" dentro un accordion espandibile.
- Campi per raccogliere consensi: privacy (obbligatorio) e marketing (facoltativo).
- Archiviazione su tabella dedicata nel database (`wp_cpr_reviews`).

== Installazione ==
1. Copia la cartella `custom-product-reviews` in `wp-content/plugins/`.
2. Attiva il plugin da Plugin > Plugin installati.
3. Assicurati di avere WooCommerce attivo e utilizza lo shortcode sulla pagina prodotto.

== Shortcode ==
Usa lo shortcode seguente sulle pagine prodotto:

[custom_reviews]

Note:
- Lo shortcode mostra il form di invio e l'elenco delle recensioni approvate per il prodotto corrente.
- Su pagine non-prodotto, viene mostrato un messaggio informativo.

== Consensi e GDPR ==
- Privacy: obbligatorio per inviare la recensione. Viene salvato nel database come `privacy_consent` (0/1).
- Marketing: opzionale; salvato come `marketing_consent` (0/1). Puoi usarlo per integrazioni con CRM/newsletter.

== Stili e Script ==
Il plugin carica automaticamente:
- `assets/style.css` per lo stile del form, recensioni, stelline, accordion.
- `assets/frontend.js` per il comportamento interattivo delle stelline e del toggle accordion.

== Note per sviluppatori ==
- Tabella DB: `{$wpdb->prefix}cpr_reviews` con colonne: id, product_id, email, name, rating, review_text, privacy_consent, marketing_consent, status, created_at.
- È presente una routine di upgrade schema che aggiunge automaticamente le colonne mancanti per i consensi.
- Imposta `define('CPR_AUTO_APPROVE', true);` per approvare automaticamente in ambienti di sviluppo.

== Changelog ==
= 1.1.0 =
- Aggiunte checkbox consensi privacy/marketing e persistenza in database.
- Accordion per il form con CTA.
- Stelline interattive per la valutazione.

= 1.0.0 =
- Versione iniziale con shortcode e salvataggio recensioni.
