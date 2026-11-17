<script setup>
import { ref } from 'vue';

defineProps({
  category: {
    type: Object,
    required: true
  }
});
const emit = defineEmits(['close']);
const isExpanded = ref(false);
</script>
<template>
  <li>
    <div class="flex items-center justify-between">
      <a
        :href="`/categoria/${category.slug}`"
        class="flex-1 px-4 py-4 text-left text-neutral-900 dark:text-white"
        @click="emit('close')"
      >
        {{ category.name }}
      </a>
      <button
        v-if="category.children?.length"
        type="button"
        :aria-expanded="isExpanded"
        :aria-label="`${isExpanded ? 'Collapse' : 'Expand'} ${category.name}`"
        class="px-4 py-4"
        @click="isExpanded = !isExpanded"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-6 w-6 fill-current text-neutral-500 transition-transform dark:text-neutral-400"
          :class="{ 'rotate-90': isExpanded }"
          viewBox="0 0 24 24"
          aria-hidden="true"
        >
          <path d="M9.003 17a1 1 0 0 1 0-1.41l3.876-3.885-3.876-3.884a.998.998 0 0 1 1.411-1.41l4.588 4.588a1 1 0 0 1 0 1.414l-4.588 4.588A.997.997 0 0 1 9.003 17" />
        </svg>
      </button>
    </div>
    <ul
      v-if="isExpanded && category.children?.length"
      class="ml-4 border-l border-l-neutral-200 dark:border-l-blue-700"
    >
      <CategoryMenuItemMobile
        v-for="child in category.children"
        :key="child.id"
        :category="child"
        @close="emit('close')"
      />
    </ul>
  </li>
</template>
