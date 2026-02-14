<template>
  <Panel :panel="panel">
    <ITextDisplay>
      {{ $t('core::app.attachments') }}

      <IText
        v-if="resource.media.length > 0"
        class="ml-0.5 inline"
        :text="'(' + resource.media.length + ')'"
      />
    </ITextDisplay>

    <ResourceRecordMediaList
      :resource-name="resourceName"
      :resource-id="resourceId"
      :media="resource.media"
      :authorize-delete="resource.authorizations.update"
      @deleted="
        synchronizeResource({ media: { id: $event.id, _delete: true } })
      "
      @uploaded="synchronizeResource({ media: [$event] })"
    />
  </Panel>
</template>

<script setup>
import { inject } from 'vue'

import ResourceRecordMediaList from '@/Core/components/Media/ResourceRecordMediaList.vue'

defineProps({
  resourceName: { required: true, type: String },
  resourceId: { required: true, type: [String, Number] },
  resource: { required: true, type: Object },
  panel: { required: true, type: Object },
})

const synchronizeResource = inject('synchronizeResource')
</script>
