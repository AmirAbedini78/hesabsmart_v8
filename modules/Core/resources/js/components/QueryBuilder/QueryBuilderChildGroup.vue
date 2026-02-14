<template>
  <div :class="['group/child flex items-center', depth > 1 ? 'ml-1' : '']">
    <QueryBuilderCondition
      v-if="isDeeplyNestedGroup"
      v-model="condition"
      keypath="core::filters.or_match_any_conditions"
      :displayable-condition="previousMatchType"
      :disabled="readonly"
      :labels="labels"
    />

    <div
      v-if="isDeeplyNestedGroup"
      class="ml-auto sm:opacity-0 sm:group-hover/child:opacity-100"
    >
      <IButton
        icon="Trash"
        basic
        small
        @click.prevent.stop="requestChildGroupDeletion"
      />
    </div>
  </div>

  <div
    :class="[
      'space-y-3.5 rounded border border-neutral-200 p-3.5 dark:border-neutral-500/30',
      depth > 0 && 'mb-4',
      bgClasses,
    ]"
  >
    <component
      :is="components[child.type]"
      v-for="(child, childIndex) in children"
      :key="child.query.rule + '-' + childIndex"
      :query="child.query"
      :index="childIndex"
      :previous-match-type="query.condition"
      :readonly="readonly"
      :max-depth="maxDepth"
      :depth="nextDepth"
      :available-rules="availableRules"
      :labels="labels"
      @child-deletion-requested="removeChild"
    />

    <div :class="readonly ? 'hidden' : ''">
      <ILink
        v-if="totalChildren === 0 || !(depth < maxDepth)"
        basic
        @click="addQueryRule"
      >
        &plus;
        {{
          totalChildren === 0 ? labels.addCondition : labels.addAnotherCondition
        }}
      </ILink>

      <IDropdown v-else>
        <IDropdownButton link basic no-caret>
          &plus;
          {{
            totalChildren === 0
              ? labels.addCondition
              : labels.addAnotherCondition
          }}
        </IDropdownButton>

        <IDropdownMenu>
          <IDropdownItem @click="addQueryRule">
            &plus; {{ labels.addCondition }}
          </IDropdownItem>

          <IDropdownItem
            v-if="depth < maxDepth && totalChildren > 0"
            @click="addQueryGroup"
          >
            <IDropdownItemLabel>
              &plus; {{ labels.addGroup }}
            </IDropdownItemLabel>

            <IDropdownItemDescription
              :text="$t('core::filters.add_group_info')"
            />
          </IDropdownItem>
        </IDropdownMenu>
      </IDropdown>
    </div>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useStore } from 'vuex'

import { useGroupBackgroundClasses } from '../../composables/useQueryBuilder'

import QueryBuilderCondition from './QueryBuilderCondition.vue'
import QueryBuilderRule from './QueryBuilderRule.vue'

defineOptions({
  name: 'QueryBuilderChildGroup',
})

const props = defineProps([
  'index',
  'query',
  'availableRules',
  'maxDepth',
  'depth',
  'labels',
  'readonly',
  'previousMatchType',
])

const emit = defineEmits(['childDeletionRequested'])

const components = { rule: QueryBuilderRule, group: 'QueryBuilderChildGroup' }

const store = useStore()

const bgClasses = useGroupBackgroundClasses(props.depth)

/**
 * The selected group query condition.
 */
const condition = computed({
  get() {
    return props.query.condition
  },
  set(value) {
    store.commit('queryBuilder/UPDATE_QUERY_CONDITION', {
      query: props.query,
      value: value,
    })
  },
})

/**
 * Indicates if the children is deeply nested group.
 */
const isDeeplyNestedGroup = computed(() => props.depth > 1)

/**
 * Get/set the child rules in the group.
 */
const children = computed({
  get() {
    return props.query.children
  },
  set(value) {
    store.commit('queryBuilder/SET_QUERY_CHILDREN', {
      query: props.query,
      children: value,
    })
  },
})

/**
 * The number of total child rules in the group.
 */
const totalChildren = computed(() => children.value.length)

/**
 * When the user removed the last child from a nested group, remove the group.
 */
watch(totalChildren, newVal => {
  if (newVal === 0 && props.depth > 1) {
    requestChildGroupDeletion()
  }
})

/**
 * Get the next depth.
 */
const nextDepth = computed(() => props.depth + 1)

/**
 * Add new empty rule condition to the group.
 */
function addQueryRule() {
  store.commit('queryBuilder/ADD_QUERY_RULE', props.query)
}

/**
 * Add new query group.
 */
function addQueryGroup() {
  if (props.depth < props.maxDepth) {
    store.commit('queryBuilder/ADD_QUERY_GROUP', props.query)
  }
}

/**
 * Request remove query child group.
 */
function requestChildGroupDeletion() {
  emit('childDeletionRequested', props.index)
}

/**
 * Remove child of the group by given index.
 */
function removeChild(index) {
  store.commit('queryBuilder/REMOVE_QUERY_CHILD', {
    query: props.query,
    index: index,
  })
}
</script>
