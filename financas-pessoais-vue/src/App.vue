<template>
  <router-view />
</template>

<script setup lang="ts">
import { watch, onMounted } from 'vue'
import { useUiStore } from '@/stores/ui'

const uiStore = useUiStore()

const applyTheme = (dark: boolean) => {
  document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light')
}

onMounted(() => applyTheme(uiStore.darkMode))
watch(() => uiStore.darkMode, applyTheme)
</script>
