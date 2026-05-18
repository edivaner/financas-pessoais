<template>
  <div class="app-layout">
    <BottomNav @add="showAddModal = true" />

    <main class="main-content">
      <slot />
    </main>

    <TransactionModal
      v-if="showAddModal"
      @close="showAddModal = false"
      @created="showAddModal = false"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import BottomNav from '@/components/ui/BottomNav.vue'
import TransactionModal from '@/components/modals/TransactionModal.vue'

const showAddModal = ref(false)
</script>

<style scoped lang="scss">
.app-layout { min-height: 100vh; }
</style>
