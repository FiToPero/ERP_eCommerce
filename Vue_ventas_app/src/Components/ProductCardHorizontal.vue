<template>
  <article
    class="group relative z-0 flex min-w-80 max-w-160 gap-4 rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm transition-all duration-300 ease-out hover:z-30 hover:-translate-y-2 hover:scale-[1.02] hover:shadow-2xl dark:border-gray-700 dark:bg-gray-800 dark:shadow-black/30 dark:hover:shadow-black/50"
  >
    <div class="relative w-25 overflow-hidden rounded-xl bg-neutral-100 dark:bg-gray-900 sm:w-44">
      <a href="#" class="block">
        <img
          class="h-auto w-full rounded-xl border border-neutral-200 object-cover transition-transform duration-300 group-hover:scale-[1.08] dark:border-gray-700"
          src="https://storage.googleapis.com/sfui_docs_artifacts_bucket_public/production/smartwatch.png"
          alt="Smartwatch Fitness Tracker"
          width="176"
          height="176"
        />
      </a>
      <div
        class="absolute left-2 top-2 inline-flex items-center rounded-full bg-rose-600 px-2.5 py-1 text-xs font-semibold text-white shadow-sm"
      >
        <svg
          class="mr-1 h-3.5 w-3.5"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.8"
          stroke-linecap="round"
          stroke-linejoin="round"
          aria-hidden="true"
        >
          <path d="m12 3 1.9 3.86L18 8.1l-3 2.93.71 4.14L12 13.2l-3.71 1.96.71-4.14-3-2.93 4.1-.24L12 3Z" />
        </svg>
        Sale
      </div>
    </div>
    <div class="flex min-w-45 flex-1 flex-col">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="text-xs font-medium uppercase tracking-[0.18em] text-neutral-500 dark:text-gray-400">Wearables</p>
          <a
            href="#"
            class="mt-1 block text-sm font-semibold text-neutral-900 transition-colors hover:text-neutral-700 dark:text-gray-100 dark:hover:text-white sm:text-lg"
          >
            Smartwatch Fitness Tracker
          </a>
        </div>
        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Ready to ship</span>
      </div>
      <div class="my-3 sm:mb-0">
        <ul class="text-xs font-normal leading-5 text-neutral-700 dark:text-gray-300 sm:text-sm">
          <li>
            <span class="mr-1">Size:</span>
            <span class="font-medium">6.5</span>
          </li>
          <li>
            <span class="mr-1">Color:</span>
            <span class="font-medium">Red</span>
          </li>
        </ul>
      </div>
      <div class="mt-4 flex flex-col gap-4 sm:mt-auto sm:flex-row sm:items-end">
        <div class="sm:order-1 sm:ml-auto sm:text-right">
          <p class="text-xs text-neutral-500 line-through dark:text-gray-500">$2,599.99</p>
          <span class="text-lg font-bold text-neutral-900 dark:text-gray-100 sm:text-2xl">$2,345.99</span>
        </div>
        <div class="flex items-center justify-between gap-3 sm:flex-1">
          <div class="flex items-center rounded-full border border-neutral-300 bg-neutral-50 dark:border-gray-600 dark:bg-gray-900">
            <button
              type="button"
              :disabled="count <= min"
              class="inline-flex h-10 w-10 items-center justify-center rounded-l-full text-neutral-700 transition-colors hover:bg-white disabled:cursor-not-allowed disabled:opacity-40 dark:text-gray-300 dark:hover:bg-gray-800"
              :aria-controls="inputId"
              aria-label="Decrease value"
              @click="dec()"
            >
              <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                aria-hidden="true"
              >
                <path d="M5 12h14" />
              </svg>
            </button>
            <input
              :id="inputId"
              type="number"
              class="w-10 appearance-none bg-transparent text-center text-sm font-semibold text-neutral-900 [-moz-appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none focus:outline-none dark:text-gray-100"
              :min="min"
              :max="max"
              @input="handleOnChange"
              v-model="count"
            />
            <button
              type="button"
              :disabled="count >= max"
              class="inline-flex h-10 w-10 items-center justify-center rounded-r-full text-neutral-700 transition-colors hover:bg-white disabled:cursor-not-allowed disabled:opacity-40 dark:text-gray-300 dark:hover:bg-gray-800"
              :aria-controls="inputId"
              aria-label="Increase value"
              @click="inc()"
            >
              <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                aria-hidden="true"
              >
                <path d="M12 5v14M5 12h14" />
              </svg>
            </button>
          </div>
          <button
            aria-label="Remove"
            type="button"
            class="ml-auto inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-neutral-500 transition-colors hover:text-neutral-800 dark:text-gray-400 dark:hover:text-gray-100"
          >
            <svg
              class="h-4 w-4"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              stroke-linecap="round"
              stroke-linejoin="round"
              aria-hidden="true"
            >
              <path d="M3 6h18" />
              <path d="M8 6V4.75A1.75 1.75 0 0 1 9.75 3h4.5A1.75 1.75 0 0 1 16 4.75V6" />
              <path d="m19 6-.84 12.59A2 2 0 0 1 16.16 20H7.84a2 2 0 0 1-1.99-1.41L5 6" />
              <path d="M10 10.5v5" />
              <path d="M14 10.5v5" />
            </svg>
            <span class="hidden sm:block">Remove</span>
          </button>
        </div>
      </div>
    </div>
  </article>
</template>

<script setup>
import { ref, useId } from 'vue';

const min = ref(1);
const max = ref(10);
const inputId = useId();
const count = ref(1);

function clamp(value, lower, upper) {
  return Math.min(Math.max(value, lower), upper);
}

function set(nextValue) {
  count.value = clamp(nextValue, min.value, max.value);
}

function inc() {
  set(count.value + 1);
}

function dec() {
  set(count.value - 1);
}

function handleOnChange(event) {
  const currentValue = event.target instanceof HTMLInputElement ? event.target.value : undefined;
  const nextValue = parseFloat(currentValue);
  set(Number.isNaN(nextValue) ? min.value : nextValue);
}
</script>

