<template>
  <ITextDisplay v-if="group.quick" class="mb-2 mt-8 font-medium">
    {{ $t('core::filters.quick_filters') }}
  </ITextDisplay>

  <ITextDisplay v-else class="mb-2 mt-4 font-medium">
    {{ $t('core::filters.advanced_filters') }}
  </ITextDisplay>

  <div class="group/main mb-2 flex items-center">
    <QueryBuilderCondition
      v-model="condition"
      :keypath="
        isFirstGroup
          ? 'core::filters.show_matching_records_conditions'
          : 'core::filters.or_match_any_conditions'
      "
      :labels="labels"
      :disabled="readonly || group.quick || false"
    />

    <div
      v-if="!isFirstGroup"
      class="ml-auto sm:opacity-0 sm:group-hover/main:opacity-100"
    >
      <IButton
        icon="Trash"
        basic
        small
        @click.prevent.stop="removeGroup(index)"
      />
    </div>
  </div>

  <QueryBuilderChildGroup
    :query="group"
    :depth="1"
    :index="index"
    :labels="labels"
    :readonly="readonly || group.quick"
    v-bind="$attrs"
  />
</template>

<script setup>
import { computed, watch } from 'vue'
import { useStore } from 'vuex'

import { useQueryBuilder } from '../../composables/useQueryBuilder'

import QueryBuilderChildGroup from './QueryBuilderChildGroup.vue'
import QueryBuilderCondition from './QueryBuilderCondition.vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  group: { type: Object, required: true },
  identifier: { type: String, required: true },
  index: { type: Number, required: true },
  labels: { type: Object, required: true },
  readonly: { type: Boolean, required: true },
})

const store = useStore()

const { removeGroup } = useQueryBuilder(props.identifier)

/**
 * Indicates whether this is the first group.
 */
const isFirstGroup = computed(() => props.index === 0)

/**
 * The selected group condition.
 */
const condition = computed({
  get() {
    return props.group.condition
  },
  set(value) {
    store.commit('queryBuilder/UPDATE_QUERY_CONDITION', {
      query: props.group,
      value: value,
    })
  },
})

/**
 * Count of total group children.
 */
const totalChildren = computed(() => props.group.children.length)

/**
 * When the user removed the last child from the group, remove the group.
 */
watch(totalChildren, newVal => {
  if (newVal === 0 && !isFirstGroup.value) {
    removeGroup(props.index)
  }
})
</script>
