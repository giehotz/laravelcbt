<script setup lang="ts">
import { cn } from '@/lib/utils'

const props = defineProps<{ class?: import('vue').HTMLAttributes['class'] }>()
</script>

<template>
  <thead :class="cn('[&_tr]:border-b', props.class)">
    <slot />
  </thead>
</template>
