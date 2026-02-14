<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

return [
    'brand' => 'Marchio',
    'brands' => 'Marchi',
    'create' => 'Crea Marchio',
    'update' => 'Aggiorna Marchio',
    'at_least_one_required' => 'Deve esserci almeno un marchio.',
    'form' => [
        'sections' => [
            'general' => 'Generale',
            'navigation' => 'Navigazione',
            'email' => 'E-mail',
            'thank_you' => 'Grazie',
            'signature' => 'Firma',
            'pdf' => 'PDF',
        ],
        'is_default' => 'Questo è il marchio predefinito dell\'azienda?',
        'name' => 'Come si fa riferimento a questo marchio internamente?',
        'display_name' => 'Come vuoi che venga visualizzato ai tuoi clienti?',
        'primary_color' => 'Scegli il colore primario del marchio',
        'upload_logo' => 'Carica il logo della tua azienda',
        'navigation' => [
            'background_color' => 'Colore di sfondo della navigazione',
            'upload_logo_info' => 'Se hai uno sfondo scuro, usa un logo chiaro. Se stai usando un colore di sfondo chiaro, usa un logo con testo scuro.',
        ],
        'pdf' => [
            'default_font' => 'Famiglia di font predefinita',
            'default_font_info' => 'Il font :fontName offre la copertura Unicode più adeguata per caratteri standard. Assicurati di selezionare un font appropriato se caratteri speciali o Unicode non vengono visualizzati correttamente nel documento PDF.',
            'size' => [
                'size' => 'Dimensione',
                'a4' => 'A4',
                'letter' => 'Letter',
            ],
            'orientation' => [
                'orientation' => 'Orientamento',
                'portrait' => 'Verticale',
                'landscape' => 'Orizzontale',
            ],
        ],
        'email' => [
            'upload_logo_info' => 'Assicurati che il logo sia adatto a uno sfondo bianco. Se non viene caricato un logo, verrà utilizzato il logo scuro caricato nelle impostazioni Generali.',
        ],
        'document' => [
            'send' => [
                'info' => 'Quando invii un documento',
                'subject' => 'Oggetto predefinito',
                'message' => 'Messaggio e-mail predefinito quando invii un documento',
                'button_text' => 'Testo del pulsante e-mail',
            ],
            'sign' => [
                'info' => 'Quando qualcuno firma il tuo documento',
                'subject' => 'Oggetto predefinito per e-mail di ringraziamento',
                'message' => 'Messaggio e-mail da inviare quando qualcuno firma il tuo documento',
                'after_sign_message' => 'Dopo la firma, cosa dovrebbe dire il messaggio?',
            ],
            'accept' => [
                'after_accept_message' => 'Dopo l\'accettazione (senza firma digitale), cosa dovrebbe dire il messaggio?',
            ],
        ],
        'signature' => [
            'bound_text' => 'Testo Vincolante Legale',
        ],
    ],
    'delete_documents_usage_warning' => 'Il marchio è già associato a documenti, quindi non può essere eliminato.',
    'created' => 'Marchio creato con successo.',
    'updated' => 'Marchio aggiornato con successo.',
    'deleted' => 'Marchio eliminato con successo',
];
