<template>
  <div class="relative h-dvh w-full shrink-0 overflow-hidden">
    <svg
      class="absolute inset-0 z-0 block h-full w-full dark:hidden"
      viewBox="0 0 1920 1080"
      xmlns="http://www.w3.org/2000/svg"
      preserveAspectRatio="xMidYMid slice"
      aria-hidden="true"
    >
      <defs>
        <linearGradient id="light-bg" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0%" stop-color="#ffffff"/>
          <stop offset="50%" stop-color="#f8f9ff"/>
          <stop offset="100%" stop-color="#f2fbff"/>
        </linearGradient>

        <linearGradient id="light-purple" x1="0" y1="0" x2="1" y2="0">
          <stop offset="0%" stop-color="#8b5cf6" stop-opacity="0.24"/>
          <stop offset="50%" stop-color="#a78bfa" stop-opacity="0.13"/>
          <stop offset="100%" stop-color="#6366f1" stop-opacity="0.03"/>
        </linearGradient>

        <linearGradient id="light-cyan" x1="0" y1="0" x2="1" y2="0">
          <stop offset="0%" stop-color="#60a5fa" stop-opacity="0.04"/>
          <stop offset="50%" stop-color="#38bdf8" stop-opacity="0.16"/>
          <stop offset="100%" stop-color="#22d3ee" stop-opacity="0.28"/>
        </linearGradient>

        <filter id="light-glow" x="-30%" y="-30%" width="160%" height="160%">
          <feGaussianBlur stdDeviation="35"/>
        </filter>

        <filter id="light-soft" x="-20%" y="-20%" width="140%" height="140%">
          <feGaussianBlur stdDeviation="7"/>
        </filter>
      </defs>

      <rect width="1920" height="1080" fill="url(#light-bg)"/>

      <ellipse cx="300" cy="260" rx="450" ry="270" fill="#a78bfa" opacity="0.12" filter="url(#light-glow)"/>
      <ellipse cx="1620" cy="380" rx="480" ry="290" fill="#67e8f9" opacity="0.12" filter="url(#light-glow)"/>

      <path
        d="M-100 370 C180 120 390 150 650 300 C920 455 1090 430 1320 250 C1530 85 1730 55 2020 170 L2020 430 C1730 310 1530 335 1320 470 C1080 625 850 650 610 500 C350 335 170 330 -100 570 Z"
        fill="url(#light-purple)"
      />
      <path
        d="M-100 700 C170 570 380 610 610 750 C870 905 1060 850 1260 650 C1480 430 1690 360 2020 500 L2020 740 C1700 600 1510 610 1300 810 C1080 1020 850 1010 590 850 C360 710 170 690 -100 850 Z"
        fill="url(#light-cyan)"
      />
      <path
        d="M-100 560 C180 700 420 700 680 545 C940 390 1110 385 1320 510 C1550 645 1760 690 2020 555"
        fill="none"
        stroke="#6366f1"
        stroke-width="3"
        opacity="0.35"
      />
      <path
        d="M-100 465 C180 250 390 265 650 410 C900 550 1090 560 1320 405 C1540 255 1750 215 2020 330"
        fill="none"
        stroke="#8b5cf6"
        stroke-width="2"
        opacity="0.45"
      />
      <path
        d="M-100 760 C190 620 400 650 620 790 C870 950 1070 920 1280 720 C1500 510 1740 470 2020 590"
        fill="none"
        stroke="#06b6d4"
        stroke-width="2"
        opacity="0.45"
      />
      <path
        d="M-100 465 C180 250 390 265 650 410 C900 550 1090 560 1320 405 C1540 255 1750 215 2020 330"
        fill="none"
        stroke="#8b5cf6"
        stroke-width="14"
        opacity="0.07"
        filter="url(#light-soft)"
      />
      <path
        d="M-100 760 C190 620 400 650 620 790 C870 950 1070 920 1280 720 C1500 510 1740 470 2020 590"
        fill="none"
        stroke="#06b6d4"
        stroke-width="14"
        opacity="0.07"
        filter="url(#light-soft)"
      />
    </svg>
    <svg
      class="absolute inset-0 z-0 hidden h-full w-full dark:block"
      viewBox="0 0 1920 1080"
      xmlns="http://www.w3.org/2000/svg"
      preserveAspectRatio="xMidYMid slice"
      aria-hidden="true"
    >
      <defs>
        <linearGradient id="dark-bg" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0%" stop-color="#050816"/>
          <stop offset="50%" stop-color="#0b1024"/>
          <stop offset="100%" stop-color="#07131c"/>
        </linearGradient>

        <linearGradient id="dark-purple" x1="0" y1="0" x2="1" y2="0">
          <stop offset="0%" stop-color="#8b5cf6" stop-opacity="0.45"/>
          <stop offset="50%" stop-color="#6366f1" stop-opacity="0.18"/>
          <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.05"/>
        </linearGradient>

        <linearGradient id="dark-cyan" x1="0" y1="0" x2="1" y2="0">
          <stop offset="0%" stop-color="#2563eb" stop-opacity="0.08"/>
          <stop offset="50%" stop-color="#06b6d4" stop-opacity="0.20"/>
          <stop offset="100%" stop-color="#22d3ee" stop-opacity="0.40"/>
        </linearGradient>

        <filter id="dark-glow" x="-30%" y="-30%" width="160%" height="160%">
          <feGaussianBlur stdDeviation="28"/>
        </filter>

        <filter id="dark-soft" x="-20%" y="-20%" width="140%" height="140%">
          <feGaussianBlur stdDeviation="8"/>
        </filter>
      </defs>

      <rect width="1920" height="1080" fill="url(#dark-bg)"/>

      <ellipse cx="320" cy="280" rx="430" ry="250" fill="#7c3aed" opacity="0.10" filter="url(#dark-glow)"/>
      <ellipse cx="1600" cy="380" rx="450" ry="280" fill="#06b6d4" opacity="0.10" filter="url(#dark-glow)"/>

      <path
        d="M-100 370 C180 120 390 150 650 300 C920 455 1090 430 1320 250 C1530 85 1730 55 2020 170 L2020 430 C1730 310 1530 335 1320 470 C1080 625 850 650 610 500 C350 335 170 330 -100 570 Z"
        fill="url(#dark-purple)"
      />
      <path
        d="M-100 700 C170 570 380 610 610 750 C870 905 1060 850 1260 650 C1480 430 1690 360 2020 500 L2020 740 C1700 600 1510 610 1300 810 C1080 1020 850 1010 590 850 C360 710 170 690 -100 850 Z"
        fill="url(#dark-cyan)"
      />
      <path
        d="M-100 560 C180 700 420 700 680 545 C940 390 1110 385 1320 510 C1550 645 1760 690 2020 555"
        fill="none"
        stroke="#3b82f6"
        stroke-width="3"
        opacity="0.55"
      />
      <path
        d="M-100 465 C180 250 390 265 650 410 C900 550 1090 560 1320 405 C1540 255 1750 215 2020 330"
        fill="none"
        stroke="#8b5cf6"
        stroke-width="2"
        opacity="0.65"
      />
      <path
        d="M-100 760 C190 620 400 650 620 790 C870 950 1070 920 1280 720 C1500 510 1740 470 2020 590"
        fill="none"
        stroke="#22d3ee"
        stroke-width="2"
        opacity="0.65"
      />
      <path
        d="M-100 465 C180 250 390 265 650 410 C900 550 1090 560 1320 405 C1540 255 1750 215 2020 330"
        fill="none"
        stroke="#8b5cf6"
        stroke-width="14"
        opacity="0.12"
        filter="url(#dark-soft)"
      />
      <path
        d="M-100 760 C190 620 400 650 620 790 C870 950 1070 920 1280 720 C1500 510 1740 470 2020 590"
        fill="none"
        stroke="#22d3ee"
        stroke-width="14"
        opacity="0.10"
        filter="url(#dark-soft)"
      />
    </svg>
    <div class="absolute inset-0 z-10 bg-white/5 dark:bg-neutral-950/35" />
    <div
      class="relative z-20 mx-auto flex h-full w-full max-w-384 flex-col text-neutral-900 dark:text-white md:flex-row-reverse md:justify-center"
    >
      <div class="flex flex-col md:basis-2/4 md:items-stretch">
        <img
          :src="image"
          :alt="imageAlt"
          class="h-full overflow-visible object-cover object-left"
        />
      </div>
        <div class="relative flex basis-2/4 flex-col justify-center p-4 md:items-start md:p-10">
        <div class="relative z-10">
          <p class="text-xs font-bold uppercase tracking-widest text-neutral-700 dark:text-white/80 md:text-sm">
            {{ headline }}
          </p>
          <h1 class="mb-4 mt-2 text-5xl font-bold leading-none md:text-7xl md:leading-[67.5px]">
            {{ title }}
          </h1>
          <p class="text-base text-neutral-700 dark:text-gray-200 md:text-lg">
            {{ description }}
          </p>
          <div class="mt-6 flex flex-col gap-4 md:flex-row">
            <button
              type="button"
            class="inline-flex min-h-12 items-center justify-center rounded-md bg-neutral-900 px-6 py-3 text-sm font-medium text-white transition-colors hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-neutral-400 dark:bg-white dark:text-neutral-950 dark:hover:bg-gray-200 dark:focus:ring-white/50"
            >
              {{ callToAction }}
            </button>
            <button
              type="button"
            class="inline-flex min-h-12 items-center justify-center rounded-md border border-neutral-300 bg-white/85 px-6 py-3 text-sm font-medium text-neutral-900 transition-colors hover:bg-white focus:outline-none focus:ring-2 focus:ring-neutral-300 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/15 dark:focus:ring-white/30"
            >
              {{ callToActionSecondary }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const {
  headline,
  title,
  description,
  callToAction,
  callToActionSecondary,
  image,
  imageAlt,
} = {
  headline: 'Feel the music',
  title: 'New Wireless Pro',
  description: 'Spatial audio. Adjustable ear cups. On-device controls. All-day battery.',
  callToAction: 'Order now',
  callToActionSecondary: 'Show more',
  image: 'https://storage.googleapis.com/sfui_docs_artifacts_bucket_public/production/hero-headphones.png',
  imageAlt: 'Headphones',
};
</script>

