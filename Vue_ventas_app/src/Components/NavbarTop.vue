<template>
  <header
    class="flex w-full justify-center border-b border-green-800 bg-green-700 px-4 py-2 dark:border-green-700 dark:bg-green-900 lg:px-6 lg:py-5"
  >
    <div class="flex h-full w-full max-w-384 flex-row flex-wrap items-center justify-start lg:flex-nowrap">
      <a
        href="#"
        aria-label="SF Homepage"
        class="mr-4 inline-block shrink-0 focus-visible:rounded-xs focus-visible:outline focus-visible:outline-offset focus-visible:outline-white"
      >
        <picture>
          <source srcset="https://storage.googleapis.com/sfui_docs_artifacts_bucket_public/production/alokai-logo-white.svg" media="(min-width: 768px)" />
          <img
            src="https://storage.googleapis.com/sfui_docs_artifacts_bucket_public/production/alokai-sign-white.svg"
            alt="Sf Logo"
            class="h-8 w-8 md:h-6 md:w-27.5 lg:h-7 lg:w-42"
          />
        </picture>
      </a>

      <button
        type="button"
        aria-label="Open categories"
        class="order-first mr-4 inline-flex items-center justify-center gap-2 rounded-full p-2 text-base font-medium text-white hover:bg-green-800 hover:text-white focus-visible:outline focus-visible:outline-offset active:bg-green-900 active:text-white disabled:cursor-not-allowed disabled:bg-transparent disabled:text-disabled-500 disabled:ring-0 disabled:shadow-none dark:hover:bg-green-800 lg:order-1 lg:hidden"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-6 w-6 fill-current" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M4 18a1 1 0 1 1 0-2h16a1 1 0 1 1 0 2zm0-5a1 1 0 1 1 0-2h16a1 1 0 1 1 0 2zM3 7a1 1 0 0 1 1-1h16a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1" />
        </svg>
      </button>

      <button
        type="button"
        class="hidden items-center justify-center gap-2 rounded-full px-4 py-2 text-base font-medium text-white hover:bg-green-800 hover:text-white focus-visible:outline focus-visible:outline-offset active:bg-green-900 active:text-white disabled:cursor-not-allowed disabled:bg-transparent disabled:text-disabled-500 disabled:ring-0 disabled:shadow-none dark:hover:bg-green-800 lg:mr-4 lg:flex"
      >
        <span class="hidden whitespace-nowrap lg:flex">Browse products</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="hidden h-6 w-6 fill-current lg:block" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M17 9.003a1 1 0 0 0-1.41 0l-3.885 3.876L7.82 9.003a.998.998 0 0 0-1.41 1.411l4.588 4.588a1 1 0 0 0 1.414 0L17 10.414a.997.997 0 0 0 0-1.41" />
        </svg>
      </button>

      <form role="search" class="order-last mt-2 flex flex-[100%] pb-2 lg:order-3 lg:mt-0 lg:pb-0" @submit.prevent="search">
        <label
          class="flex h-10 flex-1 items-center gap-2 rounded-full bg-white px-4 pr-0 text-neutral-500 ring-1 ring-inset ring-neutral-300 hover:ring-green-700 focus-within:outline focus-within:outline-offset focus-within:ring-2 focus-within:ring-green-700 focus-within:caret-green-700 active:ring-2 active:ring-green-700 active:caret-green-700 dark:bg-green-700 dark:text-green-300 dark:ring-green-600 dark:hover:ring-green-500 dark:focus-within:ring-green-500 dark:focus-within:caret-green-400 dark:active:ring-green-500 dark:active:caret-green-400"
        >
          <span class="sr-only">Search</span>
          <input
            v-model="inputValue"
            type="search"
            placeholder="Search"
            class="min-w-20 w-full appearance-none text-base text-neutral-900 outline-hidden placeholder:text-neutral-500 disabled:cursor-not-allowed disabled:bg-transparent read-only:bg-transparent [&::-webkit-search-cancel-button]:appearance-none dark:text-white dark:placeholder:text-neutral-400"
          />
          <span class="flex items-center">
            <button
              type="submit"
              aria-label="search"
              class="inline-flex items-center justify-center gap-2 rounded-full rounded-l-none p-2 text-base font-medium text-green-700 hover:bg-transparent hover:text-green-800 focus-visible:outline focus-visible:outline-offset active:bg-transparent active:text-green-900 disabled:cursor-not-allowed disabled:bg-transparent disabled:text-disabled-500 disabled:ring-0 disabled:shadow-none dark:text-green-300 dark:hover:text-green-200 dark:active:text-green-100"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-6 w-6 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                <path d="m18.9 20.3-5.6-5.6q-.75.6-1.725.95T9.5 16q-2.725 0-4.612-1.887T3 9.5t1.888-4.613Q6.775 3 9.5 3t4.613 1.887T16 9.5a6.1 6.1 0 0 1-1.3 3.8l5.625 5.625a.92.92 0 0 1 .275.675q0 .4-.3.7a.95.95 0 0 1-.7.275.95.95 0 0 1-.7-.275M9.5 14q1.875 0 3.188-1.312Q14 11.375 14 9.5t-1.312-3.188Q11.375 5 9.5 5T6.312 6.312 5 9.5t1.312 3.188Q7.625 14 9.5 14" />
              </svg>
            </button>
          </span>
        </label>
      </form>

      <nav class="flex flex-1 justify-end lg:order-last lg:ml-4">
        <div class="flex flex-row flex-nowrap">
          <button
            v-for="actionItem in actionItems"
            :key="actionItem.ariaLabel"
            type="button"
            :aria-label="actionItem.ariaLabel"
            class="-ml-0.5 mr-2 inline-flex items-center justify-center gap-2 rounded-md p-2 text-base font-medium text-white hover:bg-green-800 hover:text-white focus-visible:outline focus-visible:outline-offset active:bg-green-900 active:text-white disabled:cursor-not-allowed disabled:bg-transparent disabled:text-disabled-500 disabled:ring-0 disabled:shadow-none dark:hover:bg-green-800"
          >
            <svg
              v-if="actionItem.icon === 'cart'"
              xmlns="http://www.w3.org/2000/svg"
              class="inline-block h-6 w-6 fill-current"
              viewBox="0 0 24 24"
              aria-hidden="true"
            >
              <path d="M7 22q-.824 0-1.412-.587A1.93 1.93 0 0 1 5 20q0-.824.588-1.413A1.93 1.93 0 0 1 7 18q.824 0 1.412.587Q9 19.176 9 20t-.588 1.413A1.93 1.93 0 0 1 7 22m10 0q-.825 0-1.412-.587A1.93 1.93 0 0 1 15 20q0-.824.588-1.413A1.93 1.93 0 0 1 17 18q.824 0 1.413.587Q19 19.176 19 20t-.587 1.413A1.93 1.93 0 0 1 17 22M6.15 6l2.4 5h7l2.75-5zM7 17q-1.125 0-1.7-.988-.575-.987-.05-1.962L6.6 11.6 3 4H1.975a.93.93 0 0 1-.7-.288A1 1 0 0 1 1 3q0-.424.288-.712A.97.97 0 0 1 2 2h1.625q.274 0 .525.15.25.15.375.425L5.2 4h14.75q.675 0 .925.5t-.025 1.05l-3.55 6.4a2.03 2.03 0 0 1-.725.775q-.45.275-1.025.275H8.1L7 15h11.025q.425 0 .7.287.275.288.275.713 0 .424-.288.712A.97.97 0 0 1 18 17z" />
            </svg>
            <svg
              v-else-if="actionItem.icon === 'wishlist'"
              xmlns="http://www.w3.org/2000/svg"
              class="inline-block h-6 w-6 fill-current"
              viewBox="0 0 24 24"
              aria-hidden="true"
            >
              <path
                fill-rule="evenodd"
                d="M19.664 4.99c-2.64-1.8-5.9-.96-7.66 1.1-1.76-2.06-5.02-2.91-7.66-1.1-1.4.96-2.28 2.58-2.34 4.29-.14 3.88 3.3 6.99 8.55 11.76l.1.09c.76.69 1.93.69 2.69-.01l.11-.1c5.25-4.76 8.68-7.87 8.55-11.75-.06-1.7-.94-3.32-2.34-4.28m-7.56 14.56-.1.1-.1-.1c-4.76-4.31-7.9-7.16-7.9-10.05 0-2 1.5-3.5 3.5-3.5 1.54 0 3.04.99 3.57 2.36h1.87c.52-1.37 2.02-2.36 3.56-2.36 2 0 3.5 1.5 3.5 3.5 0 2.89-3.14 5.74-7.9 10.05"
                clip-rule="evenodd"
              />
            </svg>
            <svg
              v-else
              xmlns="http://www.w3.org/2000/svg"
              class="inline-block h-6 w-6 fill-current"
              viewBox="0 0 24 24"
              aria-hidden="true"
            >
              <path d="M12 12q-1.65 0-2.825-1.175T8 8t1.175-2.825T12 4t2.825 1.175T16 8t-1.175 2.825T12 12m6 8H6q-.824 0-1.412-.587A1.93 1.93 0 0 1 4 18v-.8q0-.85.438-1.563A2.9 2.9 0 0 1 5.6 14.55a15 15 0 0 1 3.15-1.163A13.8 13.8 0 0 1 12 13q1.65 0 3.25.387 1.6.388 3.15 1.163.724.375 1.162 1.087T20 17.2v.8q0 .825-.587 1.413A1.93 1.93 0 0 1 18 20M6 18h12v-.8a.94.94 0 0 0-.137-.5 1 1 0 0 0-.363-.35q-1.35-.675-2.725-1.013a11.6 11.6 0 0 0-5.55 0Q7.85 15.675 6.5 16.35a.97.97 0 0 0-.5.85zm6-8q.825 0 1.413-.588Q14 8.825 14 8q0-.824-.587-1.412A1.93 1.93 0 0 0 12 6q-.825 0-1.412.588A1.92 1.92 0 0 0 10 8q0 .825.588 1.412Q11.175 10 12 10" />
            </svg>
            <span v-if="actionItem.role === 'login'" class="hidden whitespace-nowrap xl:inline-flex">{{ actionItem.label }}</span>
          </button>
        </div>
      </nav>
    </div>
  </header>
</template>
<script setup>
import { ref } from 'vue';

const actionItems = [
  {
    icon: 'cart',
    ariaLabel: 'Cart',
    role: 'button',
    label: '',
  },
  {
    icon: 'wishlist',
    ariaLabel: 'Wishlist',
    role: 'button',
    label: '',
  },
  {
    label: 'Log in',
    icon: 'login',
    ariaLabel: 'Log in',
    role: 'login',
  },
];

const inputValue = ref('');

const search = () => {
  alert(`Successfully found 10 results for ${inputValue.value}`);
};
</script>

