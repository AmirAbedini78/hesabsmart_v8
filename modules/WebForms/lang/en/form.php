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
    'forms' => 'Web Forms',
    'form' => 'Web Form',
    'created' => 'Web form successfully added.',
    'updated' => 'Web form successfully updated.',
    'deleted' => 'Web form successfully deleted',
    'submission' => 'Web Form Submission',
    'total_submissions' => 'Submissions: :total',
    'editor' => 'Editor',
    'submit_options' => 'Submit Options',
    'info' => 'Create customizeable web forms that can be embedded into your existing web site or share the forms as link to automatically create, deals, contacts and companies.',
    'inactive_info' => 'This form is inactive, you are able to preview the form because you are logged in, if you want the form to be publicly available, make sure to set the form as active.',
    'create' => 'Create Web Form',
    'active' => 'Active',
    'title' => 'Title',
    'title_visibility_info' => 'The title is not visible to the visitors that will be filling the form.',
    'fields_action_required' => 'Additional action required',
    'required_fields_needed' => 'To save new deals you must add at least contact email or phone field.',
    'must_requires_fields' => 'To save new deals, the web form must requires at least contact email or phone field.',
    'non_optional_fields_required' => 'Non optional fields required',
    'notifications' => 'Notifications',
    'notification_email_placeholder' => 'Enter email address',
    'new_notification' => '+ Add Email',
    'no_sections' => 'This web form has no sections defined.',
    'style' => [
        'style' => 'Style',
        'primary_color' => 'Primary Color',
        'background_color' => 'Background Color',
        'logo' => 'Display a logo on top of the form',
    ],

    'success_page' => [
        'success_page' => 'Success Page',
        'success_page_info' => 'What should happen after a visitor submits this form?',
        'thank_you_message' => 'Display thank you message',
        'redirect' => 'Redirect to another website',
        'title' => 'Title',
        'title_placeholder' => 'Enter text for the success message.',
        'message' => 'Message',
        'redirect_url' => 'Webiste URL',
        'redirect_url_placeholder' => 'Enter URL to redirect after the form is submitted.',
    ],

    'saving_preferences' => [
        'saving_preferences' => 'Saving preferences',
        'deal_title_prefix' => 'Deal title prefix',
        'deal_title_prefix_info' => 'For each newly created deal via the form, the deal name will be prefixed with the text added in the field for easier recognition.',
    ],

    'sections' => [
        'new' => 'Add new section',
        'type' => 'Section type',

        'types' => [
            'input_field' => 'Input Field',
            'message' => 'Message',
            'file' => 'File',
        ],

        'field' => [
            'resourceName' => 'Field for',
        ],

        'introduction' => [
            'introduction' => 'Introduction',
            'title' => 'Title',
            'message' => 'Message',
        ],

        'message' => [
            'message' => 'Message',
        ],

        'file' => [
            'file' => 'File',
            'files' => 'Files',
            'multiple' => 'Allow multiple files upload?',
        ],

        'submit' => [
            'button' => 'Submit Button',
            'default_text' => 'Submit',
            'button_text' => 'Button text',
            'spam_protected' => 'Spam protected?',
            'require_privacy_policy' => 'Require privacy policy consent',
            'privacy_policy_url' => 'Privacy policy URL',
        ],

        'embed' => [
            'embed' => 'Embed',
            'share_via_link' => 'Share via link',
            'embed_form_Website' => 'Embed the form on your Website',
            'copy_code_snippet' => 'Copy the code snippet below',
            'paste_code_form_location' => 'Paste the code right where you want the form to appear in your template or CMS editor',
            'cms_snippet_editing_mode' => 'When entering the snippet to your CMS, make sure you are in :editing_mode.',
            'editing_mode' => 'editing mode',
            'iframe_protocol_requirement' => 'You must place the iframe snippet on a website that uses the same protocol like your installation, for example, if the current installation uses :uri_protocol URL protocol, you will need to add the iframe in a website that uses :uri_protocol URL protocol, adding https URL iframe on a non-https url, will prevent the form from loading.',
            'snippet_code' => 'Snippet code',
        ],
    ],
];
