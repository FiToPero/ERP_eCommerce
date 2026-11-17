<script setup>
defineProps({
  category: {
    type: Object,
    required: true
  },
  depth: {
    type: Number,
    default: 0
  }
});
</script>
<template>
  <li v-if="depth === 0" class="group">
    <a
      :href="`/categoria/${category.slug}`"
      class="mr-2 inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium text-neutral-900 transition-colors hover:bg-neutral-200 hover:text-neutral-700 active:bg-neutral-300 active:text-neutral-900 dark:text-neutral-100 dark:hover:bg-blue-800 dark:hover:text-white dark:active:bg-blue-700"
    >
      <span>{{ category.name }}</span>
      <svg
        v-if="category.children?.length"
        xmlns="http://www.w3.org/2000/svg"
        class="h-5 w-5 rotate-90 fill-current text-neutral-500 group-hover:text-neutral-700 group-active:text-neutral-900 dark:text-neutral-400 dark:group-hover:text-neutral-200"
        viewBox="0 0 24 24"
        aria-hidden="true"
      >
        <path d="M9.003 17a1 1 0 0 1 0-1.41l3.876-3.885-3.876-3.884a.998.998 0 0 1 1.411-1.41l4.588 4.588a1 1 0 0 1 0 1.414l-4.588 4.588A.997.997 0 0 1 9.003 17" />
      </svg>
    </a>
    <div
      v-if="category.children?.length"
      class="absolute left-0 right-0 top-full z-30 hidden grid-cols-4 items-start gap-x-6 bg-white p-6 shadow-lg outline-hidden before:absolute before:inset-x-0 before:-top-3 before:h-3 before:content-[''] group-hover:grid group-focus-within:grid dark:bg-blue-950 dark:shadow-black/40"
    >
      <CategoryMenuItem
        v-for="child in category.children"
        :key="child.id"
        :category="child"
        :depth="1"
      />
    </div>
  </li>
  <div v-else-if="depth === 1">
    <a
      :href="`/categoria/${category.slug}`"
      class="block whitespace-nowrap border-b border-b-neutral-200 px-4 py-1.5 text-base font-medium text-neutral-900 dark:border-b-blue-700 dark:text-white"
    >
      {{ category.name }}
    </a>
    <ul v-if="category.children?.length" class="mt-2">
      <CategoryMenuItem
        v-for="child in category.children"
        :key="child.id"
        :category="child"
        :depth="2"
      />
    </ul>
  </div>
  <li v-else>
    <a
      :href="`/categoria/${category.slug}`"
      class="flex w-auto items-center justify-between gap-3 rounded-md px-4 py-1.5 text-sm text-neutral-700 transition-colors hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-200 dark:hover:bg-blue-800 dark:hover:text-white"
    >
      <span>{{ category.name }}</span>
    </a>
    <ul v-if="category.children?.length" class="ml-4">
      <CategoryMenuItem
        v-for="child in category.children"
        :key="child.id"
        :category="child"
        :depth="depth + 1"
      />
    </ul>
  </li>
</template>
