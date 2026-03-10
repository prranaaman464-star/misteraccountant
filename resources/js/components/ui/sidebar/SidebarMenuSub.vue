<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <ul
    data-slot="sidebar-menu-sub"
    data-sidebar="menu-badge"
    :class="cn(
      'border-sidebar-border/60 flex w-full min-w-0 flex-col gap-0.5 border-l-2 pl-2 pr-1 py-1.5',
      'group-data-[collapsible=icon]:hidden',
      props.class,
    )"
  >
    <slot />
  </ul>
</template>
