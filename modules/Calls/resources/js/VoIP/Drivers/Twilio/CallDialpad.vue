<template>
  <div
    class="grid grid-cols-3 rounded-xl border border-neutral-300 bg-neutral-50 p-1 dark:border-neutral-700 dark:bg-neutral-800"
  >
    <button
      type="button"
      class="h-12 w-full rounded-xl text-xl font-bold text-neutral-500 hover:bg-neutral-900/5 dark:text-white dark:hover:bg-white/5"
      @click="$emit('pressed', '1')"
    >
      1
    </button>

    <button
      type="button"
      class="h-12 w-full rounded-xl text-xl font-bold text-neutral-500 hover:bg-neutral-900/5 dark:text-white dark:hover:bg-white/5"
      @click="$emit('pressed', '2')"
    >
      2
    </button>

    <button
      type="button"
      class="h-12 w-full rounded-xl text-xl font-bold text-neutral-500 hover:bg-neutral-900/5 dark:text-white dark:hover:bg-white/5"
      @click="$emit('pressed', '3')"
    >
      3
    </button>

    <button
      type="button"
      class="h-12 w-full rounded-xl text-xl font-bold text-neutral-500 hover:bg-neutral-900/5 dark:text-white dark:hover:bg-white/5"
      @click="$emit('pressed', '4')"
    >
      4
    </button>

    <button
      type="button"
      class="h-12 w-full rounded-xl text-xl font-bold text-neutral-500 hover:bg-neutral-900/5 dark:text-white dark:hover:bg-white/5"
      @click="$emit('pressed', '5')"
    >
      5
    </button>

    <button
      type="button"
      class="h-12 w-full rounded-xl text-xl font-bold text-neutral-500 hover:bg-neutral-900/5 dark:text-white dark:hover:bg-white/5"
      @click="$emit('pressed', '6')"
    >
      6
    </button>

    <button
      type="button"
      class="h-12 w-full rounded-xl text-xl font-bold text-neutral-500 hover:bg-neutral-900/5 dark:text-white dark:hover:bg-white/5"
      @click="$emit('pressed', '7')"
    >
      7
    </button>

    <button
      type="button"
      class="h-12 w-full rounded-xl text-xl font-bold text-neutral-500 hover:bg-neutral-900/5 dark:text-white dark:hover:bg-white/5"
      @click="$emit('pressed', '8')"
    >
      8
    </button>

    <button
      type="button"
      class="h-12 w-full rounded-xl text-xl font-bold text-neutral-500 hover:bg-neutral-900/5 dark:text-white dark:hover:bg-white/5"
      @click="$emit('pressed', '9')"
    >
      9
    </button>

    <button
      type="button"
      class="h-12 w-full rounded-xl text-xl font-bold text-neutral-500 hover:bg-neutral-900/5 dark:text-white dark:hover:bg-white/5"
    >
      *
    </button>

    <button
      type="button"
      class="h-12 w-full rounded-xl text-xl font-bold text-neutral-500 hover:bg-neutral-900/5 dark:text-white dark:hover:bg-white/5"
      @click="$emit('pressed', '#')"
    >
      0
    </button>

    <button
      type="button"
      class="h-12 w-full rounded-xl text-xl font-bold text-neutral-500 hover:bg-neutral-900/5 dark:text-white dark:hover:bg-white/5"
      @click="$emit('pressed', '#')"
    >
      #
    </button>
  </div>
</template>

<script setup>
defineEmits(['pressed'])
</script>
