<template>
  <div class="relative h-dvh w-full shrink-0 overflow-hidden">
    <picture class="absolute inset-0 z-0 block h-full w-full">
      <source :srcset="backgroundImage" media="(min-width: 768px)" />
      <img :src="backgroundImageMobile" class="h-full w-full object-cover" alt="hero" />
    </picture>
    <div
      class="absolute inset-0 z-10"
      :class="variant === 'dark' ? 'bg-neutral-950/35' : 'bg-white/5'"
    />
    <div
      class="relative z-20 mx-auto flex h-full w-full max-w-384 flex-col md:flex-row-reverse md:justify-center"
      :class="variant === 'dark' ? 'text-white' : 'text-neutral-900'"
    >
      <div class="flex flex-col md:basis-2/4 md:items-stretch">
        <img
          :src="image"
          :alt="imageAlt"
          class="h-full overflow-visible object-cover object-left"
        />
      </div>
      <div
        class="relative flex basis-2/4 flex-col justify-center p-4 md:items-start md:p-10"
        :class="variant === 'dark' ? 'bg-transparent' : 'bg-transparent'"
      >
        <div class="relative z-10">
          <p
            class="text-xs font-bold uppercase tracking-widest md:text-sm"
            :class="variant === 'dark' ? 'text-white/80' : 'text-neutral-700'"
          >
            {{ headline }}
          </p>
          <h1 class="mb-4 mt-2 text-5xl font-bold leading-none md:text-7xl md:leading-[67.5px]">
            {{ title }}
          </h1>
          <p class="text-base md:text-lg" :class="variant === 'dark' ? 'text-gray-200' : 'text-neutral-700'">
            {{ description }}
          </p>
          <div class="mt-6 flex flex-col gap-4 md:flex-row">
            <button
              type="button"
              class="inline-flex min-h-12 items-center justify-center rounded-md px-6 py-3 text-sm font-medium transition-colors focus:outline-none focus:ring-2"
              :class="variant === 'dark'
                ? 'bg-white text-neutral-950 hover:bg-gray-200 focus:ring-white/50'
                : 'bg-neutral-900 text-white hover:bg-neutral-800 focus:ring-neutral-400'"
            >
              {{ callToAction }}
            </button>
            <button
              type="button"
              class="inline-flex min-h-12 items-center justify-center rounded-md border px-6 py-3 text-sm font-medium transition-colors focus:outline-none focus:ring-2"
              :class="variant === 'dark'
                ? 'border-white/20 bg-white/10 text-white hover:bg-white/15 focus:ring-white/30'
                : 'border-neutral-300 bg-white/85 text-neutral-900 hover:bg-white focus:ring-neutral-300'"
            >
              {{ callToActionSecondary }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
const {
  headline,
  title,
  description,
  callToAction,
  callToActionSecondary,
  backgroundImage,
  backgroundImageMobile,
  image,
  imageAlt,
  variant,
} = {
  headline: 'Feel the music',
  title: 'New Wireless Pro',
  description: 'Spatial audio. Adjustable ear cups. On-device controls. All-day battery.',
  callToAction: 'Order now',
  callToActionSecondary: 'Show more',
  backgroundImage: 'https://storage.googleapis.com/sfui_docs_artifacts_bucket_public/production/hero-bg.png',
  backgroundImageMobile: 'https://storage.googleapis.com/sfui_docs_artifacts_bucket_public/production/hero-bg-mobile.png',
  image: 'https://storage.googleapis.com/sfui_docs_artifacts_bucket_public/production/hero-headphones.png',
  imageAlt: 'Headphones',
  variant: 'light',
};
</script>

