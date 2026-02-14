<template>
  <QueryBuilderGroup
    v-for="(group, index) in queryBuilderRules"
    :key="index"
    :index="index"
    :identifier="identifier"
    :group="group"
    :available-rules="availableRules"
    :max-depth="maxDepth"
    :readonly="readonly"
    :labels="labels"
  />
</template>

<script setup>
/**
 * INFO: Query builder consist of groups (main groups)
 * These groups contains deeply nested child groups with another child groups and rules.
 */
import {
  useQueryBuilder,
  useQueryBuilderLabels,
} from '../../composables/useQueryBuilder'

import QueryBuilderGroup from './QueryBuilderGroup.vue'

const props = defineProps({
  identifier: { type: String, required: true },
  readonly: Boolean,
  maxDepth: {
    type: Number,
    default: 2,
    validator: value => value >= 1 && value <= 2,
  },
})

const { queryBuilderRules, availableRules } = useQueryBuilder(props.identifier)

const { labels } = useQueryBuilderLabels()
</script>
