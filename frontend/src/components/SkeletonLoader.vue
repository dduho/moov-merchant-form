<template>
  <div class="animate-pulse">
    <!-- Skeleton pour texte -->
    <div v-if="variant === 'text'" class="space-y-3">
      <div 
        v-for="i in lines" 
        :key="i"
        class="h-4 bg-gray-200 dark:bg-gray-700 rounded"
        :style="{ width: i === lines ? '80%' : '100%' }"
      ></div>
    </div>

    <!-- Skeleton pour card -->
    <div v-else-if="variant === 'card'" class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 space-y-4">
      <div class="flex items-center space-x-4">
        <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-full"></div>
        <div class="flex-1 space-y-2">
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
          <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
        </div>
      </div>
      <div class="space-y-2">
        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded"></div>
        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-5/6"></div>
      </div>
    </div>

    <!-- Skeleton pour image -->
    <div v-else-if="variant === 'image'" class="bg-gray-200 dark:bg-gray-700 rounded-lg" :style="{ width, height }"></div>

    <!-- Skeleton pour avatar -->
    <div v-else-if="variant === 'avatar'" class="bg-gray-200 dark:bg-gray-700 rounded-full" :style="{ width: size, height: size }"></div>

    <!-- Skeleton pour bouton -->
    <div v-else-if="variant === 'button'" class="h-10 bg-gray-200 dark:bg-gray-700 rounded-lg" :style="{ width }"></div>

    <!-- Skeleton pour tableau -->
    <div v-else-if="variant === 'table'" class="space-y-3">
      <div class="h-8 bg-gray-200 dark:bg-gray-700 rounded"></div>
      <div v-for="i in rows" :key="i" class="h-12 bg-gray-100 dark:bg-gray-800 rounded"></div>
    </div>

    <!-- Skeleton pour formulaire -->
    <div v-else-if="variant === 'form'" class="space-y-4">
      <div v-for="i in lines" :key="i" class="space-y-2">
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
        <div class="h-12 bg-gray-200 dark:bg-gray-700 rounded"></div>
      </div>
    </div>

    <!-- Skeleton personnalisé -->
    <div v-else class="h-4 bg-gray-200 dark:bg-gray-700 rounded" :style="{ width, height }"></div>
  </div>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'text',
    validator: (value) => ['text', 'card', 'image', 'avatar', 'button', 'table', 'form', 'custom'].includes(value)
  },
  lines: {
    type: Number,
    default: 3
  },
  rows: {
    type: Number,
    default: 5
  },
  width: {
    type: String,
    default: '100%'
  },
  height: {
    type: String,
    default: '200px'
  },
  size: {
    type: String,
    default: '40px'
  }
})
</script>

<style scoped>
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
