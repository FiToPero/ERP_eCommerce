<template>
  <section class="fixed inset-0 z-50 flex items-center justify-center bg-neutral-950/60 p-4 sm:p-6 dark:bg-gray-950/80">
    <div
      class="group relative z-0 max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-4xl bg-white p-6 shadow-2xl ring-1 ring-black/5 transition-all duration-300 ease-out hover:z-30 hover:-translate-y-2 hover:scale-[1.01] hover:shadow-[0_30px_80px_rgba(15,23,42,0.28)] dark:bg-gray-800 dark:ring-white/5 dark:shadow-black/40 dark:hover:shadow-black/60 sm:p-8"
    >
      <div
        class="mb-4 inline-flex items-center justify-center rounded-full bg-rose-600 px-3 py-1.5 text-sm font-semibold text-white"
      >
      <svg
        class="mr-1.5 h-4 w-4"
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
      <p class="text-xs font-medium uppercase tracking-[0.2em] text-neutral-500 dark:text-gray-400">Aerial tech</p>
      <h1 class="mb-2 mt-2 text-3xl font-bold leading-tight text-neutral-900 dark:text-gray-100 sm:text-4xl">
        Mini Foldable Drone with HD Camera FPV Wifi RC Quadcopter
      </h1>
      <div class="flex items-end gap-3">
        <strong class="block text-3xl font-bold text-neutral-900 dark:text-gray-100 sm:text-4xl">$2,345.99</strong>
        <span class="pb-1 text-sm text-neutral-500 line-through dark:text-gray-500">$2,599.99</span>
      </div>
      <div class="mb-3 mt-4 inline-flex flex-wrap items-center gap-2 text-sm text-neutral-600 dark:text-gray-400">
        <div class="flex items-center gap-0.5 text-amber-400" aria-label="3 out of 5 stars">
          <svg
            v-for="star in 5"
            :key="star"
            class="h-4 w-4"
            viewBox="0 0 20 20"
            :class="star <= rating ? 'fill-current' : 'fill-neutral-200 text-neutral-200 dark:fill-gray-700 dark:text-gray-700'"
            aria-hidden="true"
          >
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.539 1.118l-2.8-2.034a1 1 0 0 0-1.176 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81H7.03a1 1 0 0 0 .95-.69l1.07-3.292Z" />
          </svg>
        </div>
        <span class="rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-semibold text-neutral-700 dark:bg-gray-700 dark:text-gray-200">123 reviews</span>
        <a href="#" class="text-xs text-neutral-500 transition-colors hover:text-neutral-800 dark:text-gray-400 dark:hover:text-gray-100">Read all reviews</a>
      </div>
      <div class="mb-6">
        <div class="mx-auto overflow-hidden rounded-3xl border border-neutral-200 bg-neutral-100 shadow-sm dark:border-gray-700 dark:bg-gray-900">
          <img
            :src="selectedImage.src"
            :alt="selectedImage.alt"
            class="h-72 w-full object-cover transition-transform duration-300 hover:scale-[1.02] sm:h-96"
          />
        </div>
        <div class="mt-4 flex flex-wrap justify-center gap-3">
          <button
            v-for="image in galleryImages"
            :key="image.src"
            type="button"
            class="overflow-hidden rounded-2xl border-2 transition-all duration-200"
            :class="image.src === selectedImage.src
              ? 'border-neutral-900 shadow-lg dark:border-gray-100'
              : 'border-transparent opacity-80 hover:border-neutral-300 hover:opacity-100 dark:hover:border-gray-600'"
            @click="selectedImage = image"
          >
            <img
              :src="image.src"
              :alt="image.alt"
              class="h-16 w-16 object-cover sm:h-20 sm:w-20"
            />
          </button>
        </div>
      </div>
      <ul class="mb-5 space-y-1 text-sm font-normal text-neutral-700 dark:text-gray-300">
        <li>HD Pictures & Videos and FPV Function</li>
        <li>Intelligent Voice Control</li>
        <li>Multiple Fun Flights</li>
        <li>Easy to Use</li>
        <li>Foldable Design & Double Flight Time</li>
      </ul>
      <div class="mb-5 rounded-2xl border border-neutral-200 bg-neutral-50 p-4 dark:border-gray-700 dark:bg-gray-900/70">
        <div
          class="mb-4 flex items-center justify-center gap-1.5 rounded-xl bg-emerald-100 py-2 text-sm font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300"
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
            <path d="M3 4h2.2a1 1 0 0 1 .98.8l.54 2.7a1 1 0 0 0 .98.8h10.86a1 1 0 0 1 .98 1.2l-1.07 5.1a1 1 0 0 1-.98.8H8.1a1 1 0 0 1-.98-.8L5.1 5.3" />
            <circle cx="9" cy="20" r="1.25" />
            <circle cx="18" cy="20" r="1.25" />
            <path d="M12 9v6" />
            <path d="M9 12h6" />
          </svg>
          1 in cart
        </div>
        <div class="flex flex-wrap items-start gap-4">
          <div class="flex grow flex-col items-stretch xs:inline-flex xs:items-center sm:grow-0">
            <div class="flex w-full items-center rounded-full border border-neutral-300 bg-white dark:border-gray-600 dark:bg-gray-800">
              <button
                type="button"
                :disabled="count <= min"
                class="inline-flex h-12 w-12 items-center justify-center rounded-l-full text-neutral-700 transition-colors hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-40 dark:text-gray-300 dark:hover:bg-gray-700"
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
                class="grow appearance-none bg-transparent px-2 text-center text-base font-semibold text-neutral-900 [-moz-appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none focus:outline-none dark:text-gray-100"
                :min="min"
                :max="max"
                @input="handleOnChange"
                v-model="count"
              />
              <button
                type="button"
                :disabled="count >= max"
                class="inline-flex h-12 w-12 items-center justify-center rounded-r-full text-neutral-700 transition-colors hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-40 dark:text-gray-300 dark:hover:bg-gray-700"
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
            <p class="mt-2 self-center text-xs text-neutral-500 dark:text-gray-400">
              <strong class="text-neutral-900 dark:text-gray-100">{{ max }}</strong> in stock
            </p>
          </div>
          <button
            type="button"
            class="grow rounded-xl bg-neutral-900 px-5 py-3.5 text-sm font-semibold text-white transition-colors hover:bg-neutral-800 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white"
          >
            <span class="inline-flex items-center gap-2">
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
                <circle cx="9" cy="20" r="1.25" />
                <circle cx="18" cy="20" r="1.25" />
                <path d="M3 4h2.2a1 1 0 0 1 .98.8l.54 2.7a1 1 0 0 0 .98.8h10.86a1 1 0 0 1 .98 1.2l-1.07 5.1a1 1 0 0 1-.98.8H8.1a1 1 0 0 1-.98-.8L5.1 5.3" />
              </svg>
              Add to cart
            </span>
          </button>
        </div>
        <div class="mt-4 flex flex-wrap justify-center gap-3">
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-xl border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 transition-colors hover:border-neutral-400 hover:bg-neutral-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-gray-500 dark:hover:bg-gray-700"
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
              <path d="M7 7h10" />
              <path d="m13 3 4 4-4 4" />
              <path d="M17 17H7" />
              <path d="m11 21-4-4 4-4" />
            </svg>
            Compare
          </button>
          <button
            type="button"
            class="inline-flex items-center gap-2 rounded-xl border border-neutral-300 bg-white px-4 py-2 text-sm font-medium text-neutral-700 transition-colors hover:border-neutral-400 hover:bg-neutral-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-gray-500 dark:hover:bg-gray-700"
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
              <path d="M12.62 20.55a1 1 0 0 1-1.24 0C6.2 16.54 3 13.63 3 9.5 3 6.46 5.42 4 8.4 4c1.55 0 3.04.72 4 1.89A5.28 5.28 0 0 1 16.4 4C19.58 4 22 6.46 22 9.5c0 4.13-3.2 7.04-8.38 11.05Z" />
            </svg>
            Add to list
          </button>
        </div>
      </div>
      <div class="flex first:mt-4">
          <svg
          class="mr-1 mt-0.5 h-4 w-4 shrink-0 text-neutral-500 dark:text-gray-400"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.8"
          stroke-linecap="round"
          stroke-linejoin="round"
          aria-hidden="true"
        >
          <path d="m12 3 8 4.5-8 4.5-8-4.5L12 3Z" />
          <path d="M4 7.5V16.5L12 21l8-4.5V7.5" />
          <path d="M12 12v9" />
        </svg>
        <p class="text-sm text-neutral-700 dark:text-gray-300">
          Free shipping, arrives by Thu, Apr 7. Want it faster?
          <a href="#" class="mx-1 text-neutral-900 underline decoration-neutral-300 underline-offset-4 transition-colors hover:text-neutral-700 dark:text-gray-100 dark:decoration-gray-600 dark:hover:text-white">
            Add an address
          </a>
          to see options
        </p>
      </div>
      <div class="mt-4 flex">
        <svg
          class="mr-1 mt-0.5 h-4 w-4 shrink-0 text-neutral-500 dark:text-gray-400"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.8"
          stroke-linecap="round"
          stroke-linejoin="round"
          aria-hidden="true"
        >
          <path d="M3 10.5 12 4l9 6.5" />
          <path d="M5 9.5V20h14V9.5" />
          <path d="M9 20v-6h6v6" />
        </svg>
        <p class="text-sm text-neutral-700 dark:text-gray-300">
          Pickup not available at your shop.
          <a href="#" class="ml-1 text-neutral-900 underline decoration-neutral-300 underline-offset-4 transition-colors hover:text-neutral-700 dark:text-gray-100 dark:decoration-gray-600 dark:hover:text-white">
            Check availability nearby
          </a>
        </p>
      </div>
      <div class="mt-4 flex">
        <svg
          class="mr-1 mt-0.5 h-4 w-4 shrink-0 text-neutral-500 dark:text-gray-400"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="1.8"
          stroke-linecap="round"
          stroke-linejoin="round"
          aria-hidden="true"
        >
          <path d="M12 3 5 6v6c0 4.97 3.06 8.94 7 10 3.94-1.06 7-5.03 7-10V6l-7-3Z" />
          <path d="m9.5 12 1.75 1.75L14.75 10" />
        </svg>
        <p class="text-sm text-neutral-700 dark:text-gray-300">
          Free 30-days returns.
          <a href="#" class="ml-1 text-neutral-900 underline decoration-neutral-300 underline-offset-4 transition-colors hover:text-neutral-700 dark:text-gray-100 dark:decoration-gray-600 dark:hover:text-white">
            Details
          </a>
        </p>
      </div>
    </div>
  </section>
</template>

<script lang="ts" setup>
import { ref, useId } from 'vue';

const inputId = useId();
const min = ref(1);
const max = ref(999);
const count = ref(1);
const rating = 3;
const galleryImages = [
  {
    src: 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80',
    alt: 'Abstract technology image 1',
  },
  {
    src: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
    alt: 'Abstract technology image 2',
  },
  {
    src: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1200&q=80',
    alt: 'Abstract technology image 3',
  },
  {
    src: 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80',
    alt: 'Abstract technology image 4',
  },
];
const selectedImage = ref(galleryImages[0]);

function clamp(value: number, lower: number, upper: number) {
  return Math.min(Math.max(value, lower), upper);
}

function set(nextValue: number) {
  count.value = clamp(nextValue, min.value, max.value);
}

function inc() {
  set(count.value + 1);
}

function dec() {
  set(count.value - 1);
}

function handleOnChange(event: Event) {
  const currentValue = (event.target as HTMLInputElement)?.value;
  const nextValue = parseFloat(currentValue);
  set(Number.isNaN(nextValue) ? min.value : nextValue);
}
</script>

