<script setup lang="ts">
import { cn } from '@/lib/utils'

const props = defineProps<{ class?: import('vue').HTMLAttributes['class'] }>()
</script>

<template>
  <caption :class="cn('mt-4 text-sm text-muted-foreground', props.class)">
    <slot />
  </caption>
</template>
