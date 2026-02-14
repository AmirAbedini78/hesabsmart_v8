<template>
  <div
    class="flex snap-x space-x-1 overflow-x-auto text-[0.96rem] scrollbar-thin scrollbar-track-neutral-200 scrollbar-thumb-neutral-300 dark:scrollbar-thumb-neutral-600"
  >
    <template v-for="(group, groupIndex) in groups" :key="groupIndex">
      <RuleDisplay
        v-for="(child, childIndex) in group.children"
        :key="String(child.query.value) + String(childIndex)"
        :depth="1"
        :condition="group.condition"
        :identifier="identifier"
        :child="child"
      />
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import cloneDeep from 'lodash/cloneDeep'

import { useQueryBuilder } from '../../composables/useQueryBuilder'

import RuleDisplay from './RuleDisplay.vue'

const props = defineProps({
  identifier: { required: true, type: String },
})

const { queryBuilderRules, availableRules } = useQueryBuilder(props.identifier)

// We will filter any rules empty rules
const groups = computed(() => {
  return cloneDeep(queryBuilderRules.value || []).map(group => {
    group.children = (group.children || [])
      .map(child => {
        if (child.type === 'group') {
          return child
        }

        let rule = availableRules.value.find(r => r.id === child.query.rule)

        if (rule && rule.isStatic) {
          child.static = true
        }

        return child
      })
      .filter(child => {
        return child.type === 'group' || child.static
          ? true
          : Boolean(child.query.type)
      })

    return group
  })
})
</script>
