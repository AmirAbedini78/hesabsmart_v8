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
    'forms' => 'Formularios web',
    'form' => 'Formulario web',
    'created' => 'Formulario web añadido correctamente.',
    'updated' => 'Formulario web actualizado con éxito.',
    'deleted' => 'Formulario web eliminado con éxito.',
    'submission' => 'Envío de formularios web',
    'total_submissions' => 'Envíos: :total',
    'editor' => 'Editor',
    'submit_options' => 'Opciones de envío',
    'info' => 'Crear formularios web personalizables que se pueden incrustar en su sitio web existente o compartir los formularios como enlace para crear automáticamente, ofertas, contactos y empresas.',
    'inactive_info' => 'Este formulario está inactivo, usted puede previsualizar el formulario porque ha iniciado sesión, si desea que esté disponible públicamente, asegúrese de establecer el formulario como activo.',
    'create' => 'Crear formulario web',
    'active' => 'Activo',
    'title' => 'Título',
    'title_visibility_info' => 'El título no es visible para los visitantes que rellenen el formulario.',
    'fields_action_required' => 'Acción adicional requerida',
    'required_fields_needed' => 'Para guardar nuevas ofertas, debe agregar al menos el campo de correo electrónico o teléfono de contacto.',
    'must_requires_fields' => 'Para guardar nuevas ofertas, su formulario web debe requerir al menos un campo de correo electrónico o teléfono de contacto.',
    'non_optional_fields_required' => 'Campos no opcionales obligatorios',
    'notifications' => 'Notificaciones',
    'notification_email_placeholder' => 'Ingresar correo electrónico',
    'new_notification' => '+ Agregar Correo Electrónico',
    'no_sections' => 'Este formulario web no tiene secciones definidas.',
    'style' => [
        'style' => 'Estilo',
        'primary_color' => 'Color principal',
        'background_color' => 'Color de fondo',
    ],
    'success_page' => [
        'success_page' => 'Página de éxito',
        'success_page_info' => '¿Qué debe suceder después de que un visitante envíe este formulario?',
        'thank_you_message' => 'Mostrar mensaje de agradecimiento',
        'redirect' => 'Redirigir a otro sitio web',
        'title' => 'Título',
        'title_placeholder' => 'Escriba el texto del mensaje de éxito.',
        'message' => 'Mensaje',
        'redirect_url' => 'URL del sitio web',
        'redirect_url_placeholder' => 'Ingrese la URL para redirigir después de enviar el formulario.',
    ],
    'saving_preferences' => [
        'saving_preferences' => 'Guardar preferencias',
        'deal_title_prefix' => 'Prefijo del título',
        'deal_title_prefix_info' => 'Para cada nueva operación creada a través del formulario, el nombre de la operación irá precedido del texto añadido en el campo para facilitar su reconocimiento.',
    ],
    'sections' => [
        'new' => 'Agregar nueva sección',
        'type' => 'Tipo de sección',
        'types' => [
            'input_field' => 'Campo de entrada',
            'message' => 'Mensaje',
            'file' => 'Archivo',
        ],
        'field' => [
            'resourceName' => 'Campo para',
        ],
        'introduction' => [
            'introduction' => 'Introducción',
            'title' => 'Título',
            'message' => 'Mensaje',
        ],
        'message' => [
            'message' => 'Mensaje',
        ],
        'file' => [
            'file' => 'Archivo',
            'files' => 'Archivos',
            'multiple' => '¿Permitir la carga de varios archivos?',
        ],
        'submit' => [
            'button' => 'Botó Enviar',
            'default_text' => 'Enviar',
            'button_text' => 'Texto del botón',
            'spam_protected' => '¿Protegido contra el spam?',
            'require_privacy_policy' => 'Requerir consentimiento de política de privacidad',
            'privacy_policy_url' => 'URL de la política de privacidad',
        ],
        'embed' => [
            'embed' => 'Insertar',
            'share_via_link' => 'Compartir mediante enlace',
            'embed_form_Website' => 'Insertar el formulario en tu sitio web',
            'copy_code_snippet' => 'Copia el fragmento de código a continuación',
            'paste_code_form_location' => 'Pega el código exactamente donde quieres que aparezca el formulario en tu plantilla o editor CMS',
            'cms_snippet_editing_mode' => 'Al ingresar el fragmento en tu CMS, asegúrate de estar en el modo :editing_mode.',
            'editing_mode' => 'edición',
            'iframe_protocol_requirement' => 'Debes colocar el fragmento iframe en un sitio web que use el mismo protocolo que tu instalación. Por ejemplo, si la instalación actual usa el protocolo URL :uri_protocol, necesitas añadir el iframe en un sitio web que use el protocolo :uri_protocol. Añadir un iframe con URL https en una URL no https evitará que el formulario se cargue.',
            'snippet_code' => 'Código del fragmento',
        ],
    ],
];
