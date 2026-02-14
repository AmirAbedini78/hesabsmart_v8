<template>
  <div class="px-1">
    <div class="mb-4 px-3">
      <InputSearch
        v-model="search"
        :placeholder="$t('core::data_views.search')"
      />
    </div>

    <IText v-show="!filteredViews.length" class="text-center">
      {{ $t('core::data_views.no_views_found') }}
    </IText>

    <ul class="max-h-80 overflow-y-auto">
      <li
        v-for="view in filteredViews"
        :key="view.id"
        class="mx-2 rounded-lg py-0.5 text-base/6 text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 dark:text-white dark:hover:bg-neutral-700 sm:text-sm/6"
      >
        <ILink
          class="flex px-2 py-0.5"
          basic
          plain
          @click="$emit('selected', view)"
        >
          <span class="block truncate">
            {{ view.name }}
          </span>
        </ILink>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({ views: Array })

defineEmits(['selected'])

const search = ref(null)

const filteredViews = computed(() =>
  !search.value
    ? props.views
    : props.views.filter(view =>
        view.name.toLowerCase().includes(search.value.toLowerCase())
      )
)
</script>
