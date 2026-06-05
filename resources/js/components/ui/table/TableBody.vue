<script setup lang="ts">
import { cn } from '@/lib/utils'

const props = defineProps<{ class?: import('vue').HTMLAttributes['class'] }>()
</script>

<template>
  <tbody :class="cn('[&_tr:last-child]:border-0', props.class)">
    <slot />
  </tbody>
</template>
