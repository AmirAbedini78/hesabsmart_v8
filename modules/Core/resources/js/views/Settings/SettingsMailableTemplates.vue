<template>
  <IAlert
    v-if="!isMailerConfiguredToSendEmails()"
    variant="warning"
    class="mb-6"
  >
    <IAlertHeading>{{ $t('core::app.action_required') }}</IAlertHeading>

    <IAlertBody>
      {{ $t('core::mail_template.email_account_not_configured') }}
    </IAlertBody>

    <IAlertActions>
      <IButton
        variant="warning"
        :to="{ name: 'settings-general' }"
        :text="$t('core::settings.go_to_settings')"
        soft
      />
    </IAlertActions>
  </IAlert>

  <ICardHeader>
    <div class="flex items-center space-x-4">
      <ICardHeading :text="$t('core::mail_template.mail_templates')" />

      <div class="flex items-center space-x-2">
        <ITextDark class="font-medium" :text="$t('core::app.locale')" />

        <IDropdown>
          <IDropdownButton :text="selectedLocale" basic />

          <IDropdownMenu>
            <IDropdownItem
              v-for="locale in locales"
              :key="locale.value"
              :text="locale.label"
              :active="selectedLocale === locale.value"
              condensed
              @click="setActiveLocale(locale)"
            />
          </IDropdownMenu>
        </IDropdown>
      </div>
    </div>

    <ICardActions class="-mt-4 sm:-mt-0">
      <IDropdown placement="bottom-end">
        <IDropdownButton class="-ml-3.5 sm:ml-0" basic>
          <span class="max-w-[13rem] truncate" v-text="selectedTemplate.name" />
        </IDropdownButton>

        <IDropdownMenu>
          <div class="px-3 py-2 font-medium">
            <ITextDark :text="$t('core::mail_template.choose_to_edit')" />
          </div>

          <IDropdownSeparator />

          <IDropdownItem
            v-for="template in templates"
            :key="template.id"
            :text="template.name"
            :active="selectedTemplate.id === template.id"
            condensed
            @click="setActiveTemplate(template, true)"
          />
        </IDropdownMenu>
      </IDropdown>
    </ICardActions>
  </ICardHeader>

  <ICard as="form" :overlay="isLoading" @submit.prevent="submit">
    <ICardBody>
      <IFormGroup
        label-for="subject"
        :label="$t('core::mail_template.subject')"
        required
      >
        <IFormInput id="subject" v-model="form.subject" name="subject" />

        <IFormError :error="form.getError('subject')" />
      </IFormGroup>

      <IFormGroup>
        <div class="mb-2 flex items-center">
          <!--
                <IDropdownSelect :items="['HTML', 'Text']"
                v-model="templateType" />
              -->
          <IFormLabel :label="$t('core::mail_template.message')" required />
        </div>

        <div v-show="isHtmlTemplateType">
          <Editor
            v-if="componentReady"
            v-model="form.html_template"
            :config="{
              urlconverter_callback: fixUrlPlaceholderInterpolation,
              content_style: styleForPlaceholders,
              toolbar: editorToolbar,
              format_noneditable_selector: `span.${templatePlaceholdersClass}-simple`,
            }"
            :auto-completer="editorAutoCompleter"
            absolute-urls
            minimal
            @setup="configureEditor"
          />
        </div>

        <div v-show="!isHtmlTemplateType">
          <IFormTextarea
            v-model="form.text_template"
            name="text_template"
            :rows="8"
          />
        </div>

        <IFormError :error="form.getError('html_template')" />

        <IFormError :error="form.getError('text_template')" />
      </IFormGroup>

      <IFormGroup
        v-if="
          selectedTemplate.placeholders &&
          selectedTemplate.placeholders.length > 0
        "
      >
        <ITextDark
          class="mb-1 font-medium"
          :text="$t('core::mail_template.placeholders.placeholders')"
        />

        <TextPlaceholders :placeholders="selectedTemplate.placeholders" />
      </IFormGroup>
    </ICardBody>

    <ICardFooter class="text-right">
      <IButton
        type="submit"
        variant="primary"
        :disabled="form.busy"
        :text="$t('core::app.save')"
      />
    </ICardFooter>
  </ICard>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import find from 'lodash/find'
import findIndex from 'lodash/findIndex'

import TextPlaceholders from '@/Core/components/TextPlaceholders.vue'
import { useApp } from '@/Core/composables/useApp'
import { useForm } from '@/Core/composables/useForm'
import { useLoader } from '@/Core/composables/useLoader'

const { t } = useI18n()
const { setLoading, isLoading } = useLoader()
const { currentUser, locales, isMailerConfiguredToSendEmails } = useApp()

const componentReady = ref(false)
const { form } = useForm()
const templateType = ref('HTML')
const templates = ref([]) // in locale templates
const selectedTemplate = ref({})
const selectedLocale = ref(currentUser.value.locale)

const templatePlaceholdersClass = 'template-placeholders'

// We will add the "bold" command to the toolbar so at least user can make placeholders bold.
const editorToolbar = 'bold | emoticons removeformat | undo redo'

const styleForPlaceholders = `
.${templatePlaceholdersClass} {
    background-color: #f1f5f9;
    padding: 2px 4px;
    border-radius: 3px;
}
.dark .${templatePlaceholdersClass} {
    background-color: #1e293b;
    color: #f1f5f9;
}
`

watch(selectedLocale, fetch)

const isHtmlTemplateType = computed(() => templateType.value === 'HTML')

const editorAutoCompleter = computed(() => ({
  id: 'placeholders',
  trigger: '{',
  list: selectedTemplate.value.placeholders
    .filter(p => p.tag !== 'action_button')
    .map(p => ({
      value: `${p.interpolation_start} ${p.tag} ${p.interpolation_end}`,
      text: `${p.interpolation_start} ${p.tag} ${p.interpolation_end} - ${p.description}`,
    })),
}))

/**
 * Save a template.
 */
function submit() {
  form.html_template = unwrapTemplatePlaceholdersElements(form.html_template)

  form.put(`/mailable-templates/${selectedTemplate.value.id}`).then(data => {
    let index = findIndex(templates.value, ['id', parseInt(data.id)])
    templates.value[index] = data

    // Re-set the data so the isDirty() method returns false
    setActiveTemplate(data)

    Innoclapps.success(t('core::mail_template.updated'))
  })
}

/**
 * Fetch the templates for the current locale.
 */
function fetch() {
  setLoading(true)

  Innoclapps.request(`/mailable-templates/${selectedLocale.value}/locale`)
    .then(({ data }) => {
      templates.value = data

      // If previous template selected, keep it selected
      // Otherwise find the template with the same name
      // We find by name because the template may have different id
      setActiveTemplate(
        Object.keys(selectedTemplate.value).length === 0
          ? data[0]
          : find(templates.value, ['name', selectedTemplate.value.name])
      )

      componentReady.value = true
    })
    .finally(() => setLoading(false))
}

/**
 * Set active locale.
 */
async function setActiveLocale(newLocale) {
  if (newLocale.value !== selectedLocale.value && form.isDirty()) {
    await Innoclapps.confirm({
      message: t('core::mail_template.changes_not_saved_warning'),
      confirmText: t('core::app.discard_changes'),
    })
  }
  selectedLocale.value = newLocale.value
}

/**
 * Set active template for editing.
 */
async function setActiveTemplate(mailableTemplate, dirtyCheck = false) {
  if (
    dirtyCheck &&
    mailableTemplate.id !== selectedTemplate.value.id &&
    form.isDirty()
  ) {
    await Innoclapps.confirm({
      message: t('core::mail_template.changes_not_saved_warning'),
      confirmText: t('core::app.discard_changes'),
    })
  }

  selectedTemplate.value = mailableTemplate

  form.set({
    subject: mailableTemplate.subject,
    html_template: fixHtmlPurifierEncodedUrls(mailableTemplate.html_template),
    text_template: fixHtmlPurifierEncodedUrls(mailableTemplate.text_template),
  })
}

/**
 * The content is cleaned from HTML purifier before returning to the front-end.
 * In this case the HTML purifier is url encoding the placeholders interpolation's when used as "href".
 * For example the href of a link, <a href="{{ key }}">Text</a> becomes href="%7B%7B%20key%20%7D%7D".
 * We need to decode to the placeholders before adding it to the editor so they became "{{ key }}".
 */
function fixHtmlPurifierEncodedUrls(text) {
  const encodedPlaceholdersRegex = /((%7B){2,3}(%20)?)(.*?)((%20)?(%7D){2,3})/gm

  return text.replace(
    encodedPlaceholdersRegex,
    function (match, p1, p2, p3, p4, p5) {
      return (
        fixUrlPlaceholderInterpolation(p1) +
        p4 +
        fixUrlPlaceholderInterpolation(p5)
      )
    }
  )
}

/**
 * Decode encoded placeholder interpolation's.
 */
function fixUrlPlaceholderInterpolation(text) {
  if (
    text.includes('%7B%7B') ||
    text.includes('%7D%7D') ||
    text.includes('{{') ||
    text.includes('}}')
  ) {
    // Only replace %20 with spaces if the URL contains encoded placeholders
    text = text.replaceAll('%20', ' ')
  }

  text = text.replaceAll('%7B', '{')
  text = text.replaceAll('%7D', '}')

  return text
}

function prepareComponent() {
  fetch()
}

function configureEditor(editor) {
  editor.on('init', function () {
    editor.formatter.register('placeholders', {
      inline: 'span',
      classes: templatePlaceholdersClass,
    })
  })

  let nonEditableClass = editor.getParam(
    'noneditable_noneditable_class',
    'mceNonEditable'
  )

  editor.on('input', function () {
    wrapTemplatePlaceholders(editor, nonEditableClass)
  })

  editor.on('BeforeSetContent', function (e) {
    e.content = wrapTemplatePlaceholdersInContent(e.content, nonEditableClass)
  })

  editor.on('PastePostProcess', function (e) {
    e.node.innerHTML = wrapTemplatePlaceholdersInContent(
      e.node.innerHTML,
      nonEditableClass
    )
  })
}

function wrapTemplatePlaceholders(editor, nonEditableClass) {
  const content = editor.getContent({ format: 'raw' })

  const wrappedContent = wrapTemplatePlaceholdersInContent(
    content,
    nonEditableClass
  )

  if (content !== wrappedContent) {
    editor.setContent(wrappedContent, { format: 'raw' })
  }
}

function wrapTemplatePlaceholdersInContent(content, nonEditableClass) {
  const regex = new RegExp(
    `(<span class="${templatePlaceholdersClass}[^>]*>.*?</span>)|(\\{\\{\\{?[\\s\\S]*?\\}\\}\\}?)`,
    'g'
  )

  return content.replace(regex, function (match, existingSpan, placeholder) {
    if (existingSpan) {
      return existingSpan // If it's an existing span, return it as is
    } else {
      const isSimple = !(placeholder.includes('/') || placeholder.includes('#'))
      const wrapperClasses = [templatePlaceholdersClass, nonEditableClass]

      if (isSimple) {
        wrapperClasses.push(templatePlaceholdersClass + '-simple')
      }

      return `<span class="${wrapperClasses.join(' ')}">${placeholder}</span>`
    }
  })
}

function unwrapTemplatePlaceholdersElements(content) {
  const regex = new RegExp(
    `<span\\b[^>]*\\bclass=["'][^"']*\\b${templatePlaceholdersClass}\\b[^"']*["'][^>]*>(.*?)</span>`,
    'gi'
  )

  return content.replace(regex, '$1')
}

prepareComponent()
</script>
