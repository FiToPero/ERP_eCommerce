<template>
  <article
    class="group relative z-0 max-w-75 overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm transition-all duration-300 ease-out hover:z-30 hover:-translate-y-2 hover:scale-[1.03] hover:shadow-2xl dark:border-gray-700 dark:bg-gray-800 dark:shadow-black/30 dark:hover:shadow-black/50"
  >
    <div class="relative bg-neutral-100 dark:bg-gray-900">
      <a href="#" class="block">
        <img
          src="https://storage.googleapis.com/sfui_docs_artifacts_bucket_public/production/sneakers.png"
          alt="Great product"
          class="block aspect-square h-auto w-full object-cover transition-transform duration-300 group-hover:scale-[1.09]"
          width="300"
          height="300"
        />
      </a>
      <button
        type="button"
        class="absolute bottom-3 right-3 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-neutral-700 shadow-sm ring-1 ring-inset ring-neutral-200 transition-colors hover:bg-neutral-50 hover:text-rose-500 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-700 dark:hover:text-rose-400"
        aria-label="Add to wishlist"
      >
        <svg
          class="h-5 w-5"
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
      </button>
    </div>
    <div class="border-t border-neutral-200 p-4 dark:border-gray-700">
      <div class="mb-2 flex items-start justify-between gap-3">
        <div>
          <p class="text-xs font-medium uppercase tracking-[0.18em] text-neutral-500 dark:text-gray-400">Performance</p>
          <a
            href="#"
            class="mt-1 line-clamp-2 text-base font-semibold leading-6 text-neutral-900 transition-colors hover:text-neutral-700 dark:text-gray-100 dark:hover:text-white"
          >
            Athletic mens walking sneakers
          </a>
        </div>
        <span class="rounded-full bg-lime-100 px-2.5 py-1 text-xs font-semibold text-lime-700 dark:bg-lime-950 dark:text-lime-300">In stock</span>
      </div>

      <div class="flex items-center gap-2 pt-1 text-sm text-neutral-600 dark:text-gray-400">
        <div class="flex items-center gap-0.5 text-amber-400" aria-label="5 out of 5 stars">
          <svg
            v-for="star in 5"
            :key="star"
            class="h-4 w-4 fill-current"
            viewBox="0 0 20 20"
            aria-hidden="true"
          >
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.539 1.118l-2.8-2.034a1 1 0 0 0-1.176 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81H7.03a1 1 0 0 0 .95-.69l1.07-3.292Z" />
          </svg>
        </div>
        <a href="#" class="transition-colors hover:text-neutral-900 dark:hover:text-gray-100">123 reviews</a>
      </div>

      <p class="py-3 text-sm font-normal leading-5 text-neutral-700 dark:text-gray-300">
        Lightweight • Non slip • Flexible outsole • Easy to wear on and off
      </p>

      <div class="flex items-end justify-between gap-3 pb-1">
        <div>
          <p class="text-xs text-neutral-500 line-through dark:text-gray-500">$2599,99</p>
          <span class="block text-2xl font-bold leading-none text-neutral-900 dark:text-gray-100">$2345,99</span>
        </div>
        <span class="rounded-full bg-neutral-900 px-2.5 py-1 text-xs font-semibold text-white dark:bg-gray-100 dark:text-gray-900">-10%</span>
      </div>

      <button
        type="button"
        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-neutral-900 px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-neutral-800 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white"
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
          <circle cx="9" cy="20" r="1.25" />
          <circle cx="18" cy="20" r="1.25" />
          <path d="M3 4h2.2a1 1 0 0 1 .98.8l.54 2.7a1 1 0 0 0 .98.8h10.86a1 1 0 0 1 .98 1.2l-1.07 5.1a1 1 0 0 1-.98.8H8.1a1 1 0 0 1-.98-.8L5.1 5.3" />
        </svg>
        Add to cart
      </button>
    </div>
  </article>
</template>

