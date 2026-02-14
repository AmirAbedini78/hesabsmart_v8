<template>
  <div>
    <ITextDisplay class="mb-3 inline-flex items-center">
      {{ $t('webforms::form.sections.embed.share_via_link') }}
      <IButtonCopy
        v-i-tooltip="$t('core::app.copy')"
        class="ml-3"
        :text="form.public_url"
        :success-message="$t('core::app.copied')"
      />
    </ITextDisplay>

    <div
      class="ltr select-all rounded-lg border border-neutral-200 bg-neutral-100 px-3 py-1.5 text-neutral-900 dark:border-neutral-500/30 dark:bg-neutral-800 dark:text-neutral-200"
    >
      {{ form.public_url }}
    </div>

    <ITextDisplay class="mb-3 mt-8">
      {{ $t('webforms::form.sections.embed.embed_form_Website') }}
    </ITextDisplay>

    <ITextBlock>
      <ul class="ml-4 list-disc leading-7">
        <li>
          {{ $t('webforms::form.sections.embed.copy_code_snippet') }}
        </li>

        <li>
          {{ $t('webforms::form.sections.embed.paste_code_form_location') }}
        </li>
      </ul>
    </ITextBlock>

    <IText class="mb-2 mt-4">
      <em class="font-italic">
        * <I18nT
          scope="global"
          keypath="webforms::form.sections.embed.cms_snippet_editing_mode">
          <template #editing_mode>
            <span class="font-medium">
              "{{ $t("webforms::form.sections.embed.editing_mode") }}"
            </span>
          </template>
        </I18nT>
      </em>
    </IText>

    <IText class="mb-2 mt-4">
      <em class="font-italic">
        * <I18nT
          scope="global"
          keypath="webforms::form.sections.embed.iframe_protocol_requirement">
          <template #uri_protocol>
            <span class="font-medium">
              https://
            </span>
          </template>
        </I18nT>
      </em>
    </IText>

    <ITextDisplay class="mb-3 mt-8">
      {{ $t('webforms::form.sections.embed.snippet_code') }}
    </ITextDisplay>

    <div
      class="ltr mt-3 select-all rounded-lg border border-neutral-200 bg-neutral-100 px-2 py-1.5 text-neutral-900 dark:border-neutral-500/30 dark:bg-neutral-800 dark:text-neutral-200"
    >
      {{ embedText }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps(['form'])

// the "allow-popups allow-popups-to-escape-sandbox" sandbox values are
// to allow links to open in new tab, for example links added in message or introduction sections.

const embedText = computed(() => {
  return `<iframe src="${props.form.public_url}?e=true" frameborder="0" width="700" height="500" sandbox="allow-top-navigation allow-scripts allow-forms allow-same-origin allow-popups allow-popups-to-escape-sandbox"></iframe>`
})
</script>
