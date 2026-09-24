<!-- eslint-disable vuejs-accessibility/no-static-element-interactions -->
<template>
  <div class="w-full h-full">
    <header class="relative">
      <div
        class="flex h-full w-full flex-wrap items-center justify-between border-0 border-green-800 bg-green-700 px-4 py-2 text-white md:z-10 md:h-20 md:flex-nowrap md:px-10 md:py-5 dark:bg-green-900"
      >
        <div class="flex items-center shrink-0">
          <button
            aria-label="Close menu"
            type="button"
            class="mr-5 inline-flex items-center justify-center rounded-full bg-transparent p-2 text-white transition-colors hover:bg-green-800 active:bg-green-900 dark:hover:bg-green-800 md:hidden"
            @click="openMenu([])"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 fill-current" viewBox="0 0 24 24" aria-hidden="true">
              <path d="M4 18a1 1 0 1 1 0-2h16a1 1 0 1 1 0 2zm0-5a1 1 0 1 1 0-2h16a1 1 0 1 1 0 2zM3 7a1 1 0 0 1 1-1h16a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1" />
            </svg>
          </button>
          <a
            href="#"
            aria-label="SF Homepage"
            class="flex shrink-0 items-center mr-auto text-white focus-visible:outline focus-visible:outline-offset focus-visible:rounded-xs"
          >
            <picture>
              <source srcset="https://storage.googleapis.com/sfui_docs_artifacts_bucket_public/production/alokai-logo-white.svg" media="(min-width: 1024px)" />
              <img
                src="https://storage.googleapis.com/sfui_docs_artifacts_bucket_public/production/alokai-sign-white.svg"
                alt="Sf Logo"
                class="h-8 w-8 lg:h-7 lg:w-42"
              />
            </picture>
          </a>
        </div>
        <form role="search" class="hidden md:flex flex-[100%] ml-10" @submit.prevent="search">
          <label
            class="flex h-10 flex-1 items-center gap-2 rounded-full bg-white px-4 pr-0 text-neutral-500 ring-1 ring-inset ring-neutral-300 hover:ring-green-700 focus-within:outline focus-within:outline-offset focus-within:ring-2 focus-within:ring-green-700 focus-within:caret-green-700 active:ring-2 active:ring-green-700 active:caret-green-700 dark:bg-green-700 dark:text-neutral-300 dark:ring-green-600 dark:hover:ring-green-500 dark:focus-within:ring-green-500 dark:focus-within:caret-green-400"
          >
            <input
              v-model="inputValue"
              type="search"
              placeholder="Search"
              class="min-w-20 w-full appearance-none bg-transparent text-base text-neutral-900 outline-hidden placeholder:text-neutral-500 disabled:cursor-not-allowed disabled:bg-transparent read-only:bg-transparent [&::-webkit-search-cancel-button]:appearance-none dark:text-white dark:placeholder:text-neutral-400"
            />
            <span class="flex items-center">
              <button
                aria-label="search"
                type="submit"
                class="inline-flex items-center justify-center rounded-full rounded-l-none p-2 text-green-700 transition-colors hover:bg-transparent hover:text-green-800 active:bg-transparent active:text-green-900 dark:text-green-300 dark:hover:text-green-200"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="m18.9 20.3-5.6-5.6q-.75.6-1.725.95T9.5 16q-2.725 0-4.612-1.887T3 9.5t1.888-4.613Q6.775 3 9.5 3t4.613 1.887T16 9.5a6.1 6.1 0 0 1-1.3 3.8l5.625 5.625a.92.92 0 0 1 .275.675q0 .4-.3.7a.95.95 0 0 1-.7.275.95.95 0 0 1-.7-.275M9.5 14q1.875 0 3.188-1.312Q14 11.375 14 9.5t-1.312-3.188Q11.375 5 9.5 5T6.312 6.312 5 9.5t1.312 3.188Q7.625 14 9.5 14" />
                </svg>
              </button>
            </span>
          </label>
        </form>
        <nav class="flex flex-nowrap justify-end items-center md:ml-10 gap-x-1">
          <!-- Recorre actionItems y crea un boton por cada accion disponible en la barra. -->
          <button
            v-for="actionItem in actionItems"
            :key="actionItem.ariaLabel"
            :aria-label="actionItem.ariaLabel"
            type="button"
            class="relative inline-flex items-center justify-center gap-2 rounded-md bg-transparent p-2 text-white transition-colors hover:bg-green-800 hover:text-white active:bg-green-900 active:text-white dark:hover:bg-green-700"
            @click="actionItem.onClick()"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6 fill-current"
              viewBox="0 0 24 24"
              aria-hidden="true"
            >
              <path
                :d="actionItem.icon.path"
                :fill-rule="actionItem.icon.fillRule"
                :clip-rule="actionItem.icon.clipRule"
              />
            </svg>
            <!-- Solo algunos roles muestran texto junto al icono: autenticacion y tema. -->
            <p v-if="actionItem.role === 'auth' || actionItem.role === 'auth-text' || actionItem.role === 'theme'" class="hidden lg:inline-flex whitespace-nowrap mr-2">
              {{ actionItem.label }}
            </p>
            <span
              v-if="actionItem.badge"
              class="absolute -right-1 top-0 inline-flex min-w-5 items-center justify-center rounded-full border border-green-700 bg-white px-1.5 text-xs font-semibold leading-5 text-neutral-900 dark:border-green-900 dark:bg-green-200"
            >
              <!-- Si el item trae badge, muestra el contador formateado en la esquina del boton. -->
              {{ formatBadge(actionItem.badge.content, actionItem.badge.max) }}
            </span>
          </button>
        </nav>
        <form role="search" class="flex md:hidden flex-[100%] my-2" @submit.prevent="search">
          <label
            class="flex h-10 flex-1 items-center gap-2 rounded-full bg-white px-4 pr-0 text-neutral-500 ring-1 ring-inset ring-neutral-300 hover:ring-green-700 focus-within:outline focus-within:outline-offset focus-within:ring-2 focus-within:ring-green-700 focus-within:caret-green-700 active:ring-2 active:ring-green-700 active:caret-green-700 dark:bg-green-700 dark:text-neutral-300 dark:ring-green-600 dark:hover:ring-green-500 dark:focus-within:ring-green-500 dark:focus-within:caret-green-400"
          >
            <input
              v-model="inputValue"
              type="search"
              placeholder="Search"
              class="min-w-20 w-full appearance-none bg-transparent text-base text-neutral-900 outline-hidden placeholder:text-neutral-500 disabled:cursor-not-allowed disabled:bg-transparent read-only:bg-transparent [&::-webkit-search-cancel-button]:appearance-none dark:text-white dark:placeholder:text-neutral-400"
            />
            <span class="flex items-center">
              <button
                aria-label="search"
                type="submit"
                class="inline-flex items-center justify-center rounded-full rounded-l-none p-2 text-green-700 transition-colors hover:bg-transparent hover:text-green-800 active:bg-transparent active:text-green-900 dark:text-green-300 dark:hover:text-green-200"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="m18.9 20.3-5.6-5.6q-.75.6-1.725.95T9.5 16q-2.725 0-4.612-1.887T3 9.5t1.888-4.613Q6.775 3 9.5 3t4.613 1.887T16 9.5a6.1 6.1 0 0 1-1.3 3.8l5.625 5.625a.92.92 0 0 1 .275.675q0 .4-.3.7a.95.95 0 0 1-.7.275.95.95 0 0 1-.7-.275M9.5 14q1.875 0 3.188-1.312Q14 11.375 14 9.5t-1.312-3.188Q11.375 5 9.5 5T6.312 6.312 5 9.5t1.312 3.188Q7.625 14 9.5 14" />
                </svg>
              </button>
            </span>
          </label>
        </form>
      </div>
      <!-- Desktop dropdown -->
      <nav>
        <ul
          class="hidden border-b border-b-neutral-200 bg-white px-6 py-2 dark:border-b-green-700 dark:bg-green-950 md:flex"
          @blur="handleDesktopBlur"
        >
          <li v-for="(menuNode, index) in content.children" :key="menuNode.key">
            <button
              :ref="(element) => setTriggerRef(element, index)"
              type="button"
              class="group mr-2 inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium text-neutral-900 transition-colors hover:bg-neutral-200 hover:text-neutral-700 active:bg-neutral-300 active:text-neutral-900 dark:text-neutral-100 dark:hover:bg-green-800 dark:hover:text-white dark:active:bg-green-700"
              @mouseenter="openMenu([menuNode.key])"
              @click="openMenu([menuNode.key])"
            >
              <span>{{ menuNode.value.label }}</span>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 rotate-90 fill-current text-neutral-500 group-hover:text-neutral-700 group-active:text-neutral-900 dark:text-neutral-400 dark:group-hover:text-neutral-200"
                viewBox="0 0 24 24"
                aria-hidden="true"
              >
                <path d="M9.003 17a1 1 0 0 1 0-1.41l3.876-3.885-3.876-3.884a.998.998 0 0 1 1.411-1.41l4.588 4.588a1 1 0 0 1 0 1.414l-4.588 4.588A.997.997 0 0 1 9.003 17" />
              </svg>
            </button>

            <div
              v-if="isOpen && activeNode.length === 1 && activeNode[0] === menuNode.key"
              :key="activeMenu.key"
              ref="megaMenuRef"
              class="absolute left-0 right-0 top-full z-30 hidden grid-cols-4 gap-x-6 bg-white p-6 shadow-lg outline-hidden dark:bg-green-950 dark:shadow-black/40 md:grid"
              tabindex="0"
              @mouseleave="close()"
              @keydown.esc="focusTrigger(index)"
            >
              <template v-for="node in activeMenu.children" :key="node.key">
                <template v-if="node.isLeaf">
                  <a
                    :href="node.value.link"
                    class="mb-2 inline-flex w-fit rounded-md px-4 py-1.5 text-sm text-neutral-700 transition-colors hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-200 dark:hover:bg-green-800 dark:hover:text-white"
                  >
                    {{ node.value.label }}
                  </a>
                  <div class="col-start-2 col-end-5" />
                </template>
                <div v-else>
                  <p
                    class="whitespace-nowrap border-b border-b-neutral-200 px-4 py-1.5 text-base font-medium text-neutral-900 dark:border-b-green-700 dark:text-white"
                  >
                    {{ node.value.label }}
                  </p>
                  <ul class="mt-2">
                    <li v-for="child in node.children" :key="child.key">
                      <a
                        :href="child.value.link"
                        class="flex w-auto items-center justify-between gap-3 rounded-md px-4 py-1.5 text-sm text-neutral-700 transition-colors hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-200 dark:hover:bg-green-800 dark:hover:text-white"
                      >
                        <span>{{ child.value.label }}</span>
                        <span class="rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600 dark:bg-green-800 dark:text-neutral-300">
                          {{ child.value.counter }}
                        </span>
                      </a>
                    </li>
                  </ul>
                </div>
              </template>
              <div
                class="flex grow flex-col items-center justify-center overflow-hidden rounded-md bg-neutral-100 dark:bg-green-800"
              >
                <img :src="bannerNode.value.banner" :alt="bannerNode.value.bannerTitle" class="object-contain" />
                <p class="mb-4 mt-4 px-4 text-center text-base font-medium text-neutral-900 dark:text-white">
                  {{ bannerNode.value.bannerTitle }}
                </p>
              </div>
            </div>
          </li>
        </ul>
      </nav>

      <!-- Mobile drawer -->
      <div v-if="isOpen" class="fixed inset-0 z-30 bg-neutral-500/50 md:hidden" @click="close()" />
      <aside
        v-if="isOpen"
        ref="drawerRef"
        class="fixed inset-y-0 left-0 z-40 w-[calc(100%-50px)] max-w-94 overflow-y-auto bg-white shadow-xl dark:bg-green-950 md:hidden"
      >
        <nav>
          <div class="flex items-center justify-between border-b border-b-neutral-200 p-4 dark:border-b-green-700">
            <p class="text-base font-medium text-neutral-900 dark:text-white">Browse products</p>
            <button
              type="button"
              aria-label="Close menu"
              class="ml-2 inline-flex items-center justify-center rounded-full p-2 text-neutral-500 transition-colors hover:bg-neutral-100 hover:text-neutral-700 dark:text-neutral-400 dark:hover:bg-green-800 dark:hover:text-white"
              @click="close()"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                <path d="m12 13.4-4.9 4.9q-.275.275-.7.275t-.7-.275a.96.96 0 0 1-.287-.7q0-.425.287-.7l4.9-4.9-4.9-4.9a.96.96 0 0 1-.287-.7q0-.425.287-.7t.7-.275q.425 0 .7.275l4.9 4.9 4.9-4.9q.275-.275.7-.275t.7.275.275.7q0 .425-.275.7l-4.9 4.9 4.9 4.9q.275.275.275.7t-.275.7-.7.275q-.425 0-.7-.275z" />
              </svg>
            </button>
            </div>
          <ul class="mb-6 mt-2">
            <li v-if="activeMenu.key !== 'root'">
              <button
                type="button"
                class="flex w-full items-center border-b border-b-neutral-200 px-4 py-4 text-left dark:border-b-green-700"
                @click="goBack()"
              >
                <div class="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 fill-current text-neutral-500 dark:text-neutral-400" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M14.997 17a.997.997 0 0 1-.707-.293l-4.587-4.588a1 1 0 0 1 0-1.414l4.588-4.588a.999.999 0 1 1 1.413 1.411l-3.877 3.884 3.877 3.885A.999.999 0 0 1 14.997 17" />
                  </svg>
                  <p class="ml-5 font-medium text-neutral-900 dark:text-white">{{ activeMenu.value.label }}</p>
                </div>
              </button>
            </li>
            <template v-for="node in activeMenu.children" :key="node.value.label">
              <li v-if="node.isLeaf">
                <a
                  :href="node.value.link"
                  class="flex items-center justify-between px-4 py-4 text-neutral-900 first-of-type:mt-2 dark:text-white"
                  @click="close()"
                >
                  <div class="flex items-center">
                    <p class="text-left">{{ node.value.label }}</p>
                    <span class="ml-2 rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600 dark:bg-green-800 dark:text-neutral-300">
                      {{ node.value.counter }}
                    </span>
                  </div>
                </a>
              </li>
              <li v-else>
                <button type="button" class="flex w-full items-center justify-between px-4 py-4 text-left" @click="goNext(node.key)">
                  <div class="flex items-center">
                    <p class="text-left text-neutral-900 dark:text-white">{{ node.value.label }}</p>
                    <span class="ml-2 rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600 dark:bg-green-800 dark:text-neutral-300">
                      {{ node.value.counter }}
                    </span>
                  </div>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 fill-current text-neutral-500 dark:text-neutral-400" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M9.003 17a1 1 0 0 1 0-1.41l3.876-3.885-3.876-3.884a.998.998 0 0 1 1.411-1.41l4.588 4.588a1 1 0 0 1 0 1.414l-4.588 4.588A.997.997 0 0 1 9.003 17" />
                  </svg>
                </button>
              </li>
            </template>
          </ul>
          <div
            v-if="bannerNode.value.banner"
            class="flex grow items-center overflow-hidden bg-neutral-100 dark:bg-green-800"
          >
            <img
              :src="bannerNode.value.banner"
              :alt="bannerNode.value.bannerTitle"
              class="object-contain w-[50%] basis-6/12"
            />
            <p class="basis-6/12 p-6 text-base font-medium text-neutral-900 dark:text-white">{{ bannerNode.value.bannerTitle }}</p>
          </div>
        </nav>
      </aside>
    </header>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useAuthStore } from '../Stores/useAuthStore';

const findNode = (keys, node) => {
  if (keys.length > 1) {
    const [currentKey, ...restKeys] = keys;
    return findNode(restKeys, node.children?.find((child) => child.key === currentKey) || node);
  } else {
    return node.children?.find((child) => child.key === keys[0]) || node;
  }
};

const isOpen = ref(false);
const isDarkMode = ref(false);
const drawerRef = ref(null);
const megaMenuRef = ref(null);
const triggerRefs = ref([]);
const activeNode = ref([]);
const router = useRouter();
const authStore = useAuthStore();
const { isLoggedIn } = storeToRefs(authStore);
const API_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8090';

const activeMenu = computed(() => findNode(activeNode.value, content));
const bannerNode = computed(() => findNode(activeNode.value.slice(0, 1), content));

const setTriggerRef = (element, index) => {
  const resolvedElement = element && '$el' in element ? element.$el : element;
  triggerRefs.value[index] = resolvedElement ?? null;
};

const handleDesktopBlur = (event) => {
  const currentTarget = event.currentTarget;

  if (currentTarget instanceof Element && !currentTarget.contains(event.relatedTarget)) {
    close();
  }
};

const close = () => {
  isOpen.value = false;
  activeNode.value = [];
};

const openMenu = (menuType) => {
  activeNode.value = menuType;
  isOpen.value = true;
};

const applyTheme = (nextIsDark) => {
  isDarkMode.value = nextIsDark;
  document.documentElement.classList.toggle('dark', nextIsDark);
  localStorage.setItem('theme', nextIsDark ? 'dark' : 'light');
};

const toggleDarkMode = () => {
  applyTheme(!isDarkMode.value);
};

const goBack = () => {
  activeNode.value = activeNode.value.slice(0, activeNode.value.length - 1);
};

const goNext = (key) => {
  activeNode.value = [...activeNode.value, key];
};

const focusTrigger = (index) => {
  close();
  triggerRefs.value[index]?.focus();
};

const inputValue = ref('');

const search = () => {
  alert(`Successfully found 10 results for ${inputValue.value}`);
};

const logout = async () => {
  try {
    await fetch(`${API_URL}/auth/logout`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
        Accept: 'application/json',
      },
    });
  } finally {
    authStore.clearToken();
    router.push('/login');
  }
};

onMounted(() => {
  const savedTheme = localStorage.getItem('theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  applyTheme(savedTheme ? savedTheme === 'dark' : prefersDark);
});

const formatBadge = (content, max) => {
  return content > max ? `${max}+` : `${content}`;
};

const getActionIcon = (iconName) => {
  if (iconName === 'theme' && isDarkMode.value) {
    return {
      path: 'M12 17.5q-2.3 0-3.9-1.6Q6.5 14.3 6.5 12t1.6-3.9Q9.7 6.5 12 6.5t3.9 1.6q1.6 1.6 1.6 3.9t-1.6 3.9q-1.6 1.6-3.9 1.6M12 22q-.425 0-.712-.288A.97.97 0 0 1 11 21v-1.025q0-.425.288-.712A.97.97 0 0 1 12 18.975q.424 0 .712.288.288.287.288.712V21q0 .425-.288.712A.97.97 0 0 1 12 22m0-16.975q-.425 0-.712-.288A.97.97 0 0 1 11 4.025V3q0-.425.288-.712A.97.97 0 0 1 12 2q.424 0 .712.288Q13 2.575 13 3v1.025q0 .424-.288.712a.97.97 0 0 1-.712.288M4.925 6.35l-.725-.7q-.3-.3-.3-.713 0-.411.3-.712.275-.3.7-.3.425 0 .725.3l.7.725q.3.3.287.7-.012.4-.287.675-.275.3-.687.313-.413.012-.713-.288m13.7 13.725-.725-.725q-.3-.3-.3-.712 0-.413.3-.688.275-.3.688-.312.412-.013.712.287l.725.7q.3.3.3.725t-.3.7q-.3.3-.712.3-.413 0-.688-.275M19.975 13q-.425 0-.712-.288a.97.97 0 0 1-.288-.712q0-.425.288-.712a.97.97 0 0 1 .712-.288H21q.425 0 .712.288.288.287.288.712 0 .424-.288.712A.97.97 0 0 1 21 13zm-16.975 0q-.425 0-.712-.288A.97.97 0 0 1 2 12q0-.425.288-.712A.97.97 0 0 1 3 11h1.025q.424 0 .712.288Q5.025 11.575 5.025 12q0 .424-.288.712a.97.97 0 0 1-.712.288zm2.625 7.075q-.3.275-.712.275-.413 0-.713-.3-.3-.275-.3-.688 0-.412.3-.712l.725-.725q.3-.3.712-.288.413.013.688.313.275.275.288.688.012.412-.288.712zm13.7-13.7q-.3.3-.7.288-.4-.013-.675-.313-.3-.275-.312-.687-.013-.413.287-.713l.7-.725q.3-.3.725-.3t.7.3q.3.3.313.712.012.413-.288.713z',
    };
  }

  if (iconName === 'theme') {
    return {
      path: 'M12.05 21q-3.775 0-6.413-2.625Q3 15.75 3 12.025q0-3.05 1.8-5.537 1.8-2.488 4.675-3.338.35-.1.638.013.287.112.437.387t.138.587a.94.94 0 0 1-.263.588q-.725.85-1.075 1.85t-.35 2.05q0 2.075 1.463 3.538Q11.925 13.625 14 13.625q1.05 0 2.05-.35t1.85-1.075q.25-.225.575-.25.325-.025.6.125t.4.425q.15.25.05.625-.85 2.875-3.325 4.675Q15.725 21 12.675 21z',
    };
  }

  if (iconName === 'cart') {
    return {
      path: 'M7 22q-.824 0-1.412-.587A1.93 1.93 0 0 1 5 20q0-.824.588-1.413A1.93 1.93 0 0 1 7 18q.824 0 1.412.587Q9 19.176 9 20t-.588 1.413A1.93 1.93 0 0 1 7 22m10 0q-.825 0-1.412-.587A1.93 1.93 0 0 1 15 20q0-.824.588-1.413A1.93 1.93 0 0 1 17 18q.824 0 1.413.587Q19 19.176 19 20t-.587 1.413A1.93 1.93 0 0 1 17 22M6.15 6l2.4 5h7l2.75-5zM7 17q-1.125 0-1.7-.988-.575-.987-.05-1.962L6.6 11.6 3 4H1.975a.93.93 0 0 1-.7-.288A1 1 0 0 1 1 3q0-.424.288-.712A.97.97 0 0 1 2 2h1.625q.274 0 .525.15.25.15.375.425L5.2 4h14.75q.675 0 .925.5t-.025 1.05l-3.55 6.4a2.03 2.03 0 0 1-.725.775q-.45.275-1.025.275H8.1L7 15h11.025q.425 0 .7.287.275.288.275.713 0 .424-.288.712A.97.97 0 0 1 18 17z',
    };
  }

  if (iconName === 'wishlist') {
    return {
      path: 'M19.664 4.99c-2.64-1.8-5.9-.96-7.66 1.1-1.76-2.06-5.02-2.91-7.66-1.1-1.4.96-2.28 2.58-2.34 4.29-.14 3.88 3.3 6.99 8.55 11.76l.1.09c.76.69 1.93.69 2.69-.01l.11-.1c5.25-4.76 8.68-7.87 8.55-11.75-.06-1.7-.94-3.32-2.34-4.28m-7.56 14.56-.1.1-.1-.1c-4.76-4.31-7.9-7.16-7.9-10.05 0-2 1.5-3.5 3.5-3.5 1.54 0 3.04.99 3.57 2.36h1.87c.52-1.37 2.02-2.36 3.56-2.36 2 0 3.5 1.5 3.5 3.5 0 2.89-3.14 5.74-7.9 10.05',
      fillRule: 'evenodd',
      clipRule: 'evenodd',
    };
  }

  return {
    path: 'M12 12q-1.65 0-2.825-1.175T8 8t1.175-2.825T12 4t2.825 1.175T16 8t-1.175 2.825T12 12m6 8H6q-.824 0-1.412-.587A1.93 1.93 0 0 1 4 18v-.8q0-.85.438-1.563A2.9 2.9 0 0 1 5.6 14.55a15 15 0 0 1 3.15-1.163A13.8 13.8 0 0 1 12 13q1.65 0 3.25.387 1.6.388 3.15 1.163.724.375 1.162 1.087T20 17.2v.8q0 .825-.587 1.413A1.93 1.93 0 0 1 18 20M6 18h12v-.8a.94.94 0 0 0-.137-.5 1 1 0 0 0-.363-.35q-1.35-.675-2.725-1.013a11.6 11.6 0 0 0-5.55 0Q7.85 15.675 6.5 16.35a.97.97 0 0 0-.5.85zm6-8q.825 0 1.413-.588Q14 8.825 14 8q0-.824-.587-1.412A1.93 1.93 0 0 0 12 6q-.825 0-1.412.588A1.92 1.92 0 0 0 10 8q0 .825.588 1.412Q11.175 10 12 10',
  };
};

const actionItems = computed(() => {
  const commonItems = [
    {
      icon: getActionIcon('theme'),
      label: isDarkMode.value ? 'Light mode' : 'Dark mode',
      ariaLabel: isDarkMode.value ? 'Activate light mode' : 'Activate dark mode',
      role: 'theme',
      badge: undefined,
      onClick: toggleDarkMode,
    },
    {
      icon: getActionIcon('cart'),
      label: '',
      ariaLabel: 'Cart',
      role: 'button',
      badge: {
        content: 2,
        max: 99,
      },
      onClick: () => {},
    },
    {
      icon: getActionIcon('wishlist'),
      label: '',
      ariaLabel: 'Wishlist',
      role: 'button',
      badge: undefined,
      onClick: () => {},
    },
  ];

  if (isLoggedIn.value) {
    return [
      ...commonItems,
      {
        icon: getActionIcon('login'),
        label: 'Log out',
        ariaLabel: 'Log out',
        role: 'auth',
        badge: undefined,
        onClick: logout,
      },
    ];
  }

  return [
    ...commonItems,
    {
      icon: getActionIcon('login'),
      label: 'Log in',
      ariaLabel: 'Log in',
      role: 'auth',
      badge: undefined,
      onClick: () => router.push('/login'),
    },
    {
      icon: getActionIcon(),
      label: 'Register',
      ariaLabel: 'Register',
      role: 'auth-text',
      badge: undefined,
      onClick: () => router.push('/register'),
    },
  ];
});

const content = {
  key: 'root',
  value: { label: '', counter: 0 },
  isLeaf: false,
  children: [
    {
      key: 'WOMEN',
      value: {
        label: 'Women',
        counter: 515,
        banner: 'https://storage.googleapis.com/sfui_docs_artifacts_bucket_public/production/glasses.png',
        bannerTitle: 'The world in a new light',
      },
      isLeaf: false,
      children: [
        {
          key: 'ALL_WOMEN',
          value: { label: "All Women's", counter: 515, link: '#' },
          isLeaf: true,
        },
        {
          key: 'CATEGORIES',
          value: { label: 'Categories', counter: 178 },
          isLeaf: false,
          children: [
            {
              key: 'ALL_CATEGORIES',
              value: { label: 'All Categories', counter: 178, link: '#' },
              isLeaf: true,
            },
            {
              key: 'CLOTHING',
              value: { label: 'Clothing', counter: 30, link: '#' },
              isLeaf: true,
            },
            {
              key: 'SHOES',
              value: { label: 'Shoes', counter: 28, link: '#' },
              isLeaf: true,
            },
            {
              key: 'ACCESSORIES',
              value: { label: 'Accessories', counter: 56, link: '#' },
              isLeaf: true,
            },
            {
              key: 'WEARABLES',
              value: { label: 'Wearables', counter: 12, link: '#' },
              isLeaf: true,
            },
            {
              key: 'FOOD_DRINKS',
              value: { label: 'Food & Drinks', counter: 52, link: '#' },
              isLeaf: true,
            },
          ],
        },
        {
          key: 'ACTIVITIES',
          value: { label: 'Activities', counter: 239 },
          isLeaf: false,
          children: [
            {
              key: 'ALL_ACTIVITIES',
              value: { label: 'All Activities', counter: 239, link: '#' },
              isLeaf: true,
            },
            {
              key: 'FITNESS',
              value: { label: 'Fitness', counter: 83, link: '#' },
              isLeaf: true,
            },
            {
              key: 'PILATES',
              value: { label: 'Pilates', counter: 65, link: '#' },
              isLeaf: true,
            },
            {
              key: 'TRAINING',
              value: { label: 'Training', counter: 21, link: '#' },
              isLeaf: true,
            },
            {
              key: 'CARDIO_WORKOUT',
              value: { label: 'Cardio Workout', counter: 50, link: '#' },
              isLeaf: true,
            },
            {
              key: 'YOGA',
              value: { label: 'Yoga', counter: 20, link: '#' },
              isLeaf: true,
            },
          ],
        },
        {
          key: 'DEALS',
          value: { label: 'Deals', counter: 98 },
          isLeaf: false,
          children: [
            {
              key: 'ALL_DEALS',
              value: { label: 'All Deals', counter: 98, link: '#' },
              isLeaf: true,
            },
            {
              key: 'OUTLET',
              value: { label: 'Outlet', counter: 98, link: '#' },
              isLeaf: true,
            },
          ],
        },
      ],
    },
    {
      key: 'MEN',
      value: {
        label: 'Men',
        counter: 364,
        banner: 'https://storage.googleapis.com/sfui_docs_artifacts_bucket_public/production/watch.png',
        bannerTitle: 'New in designer watches',
      },
      isLeaf: false,
      children: [
        {
          key: 'ALL_MEN',
          value: { label: "All Men's", counter: 364, link: '#' },
          isLeaf: true,
        },
        {
          key: 'CATEGORIES',
          value: { label: 'Categories', counter: 164 },
          isLeaf: false,
          children: [
            {
              key: 'ALL_CATEGORIES',
              value: { label: 'All Categories', counter: 164, link: '#' },
              isLeaf: true,
            },
            {
              key: 'CLOTHING',
              value: { label: 'Clothing', counter: 41, link: '#' },
              isLeaf: true,
            },
            {
              key: 'SHOES',
              value: { label: 'Shoes', counter: 20, link: '#' },
              isLeaf: true,
            },
            {
              key: 'ACCESSORIES',
              value: { label: 'Accessories', counter: 56, link: '#' },
              isLeaf: true,
            },
            {
              key: 'WEARABLES',
              value: { label: 'Wearables', counter: 32, link: '#' },
              isLeaf: true,
            },
            {
              key: 'FOOD_DRINKS',
              value: { label: 'Food & Drinks', counter: 15, link: '#' },
              isLeaf: true,
            },
          ],
        },
        {
          key: 'ACTIVITIES',
          value: { label: 'Activities', counter: 132 },
          isLeaf: false,
          children: [
            {
              key: 'ALL_ACTIVITIES',
              value: { label: 'All Activities', counter: 132, link: '#' },
              isLeaf: true,
            },
            {
              key: 'TRAINING',
              value: { label: 'Training', counter: 21, link: '#' },
              isLeaf: true,
            },
            {
              key: 'WORKOUT',
              value: { label: 'Workout', counter: 43, link: '#' },
              isLeaf: true,
            },
            {
              key: 'FOOTBALL',
              value: { label: 'Football', counter: 30, link: '#' },
              isLeaf: true,
            },
            {
              key: 'FITNESS',
              value: { label: 'Fitness', counter: 38, link: '#' },
              isLeaf: true,
            },
          ],
        },
        {
          key: 'DEALS',
          value: { label: 'Deals', counter: 68 },
          isLeaf: false,
          children: [
            {
              key: 'ALL_DEALS',
              value: { label: 'All Deals', counter: 68, link: '#' },
              isLeaf: true,
            },
            {
              key: 'OUTLET',
              value: { label: 'Outlet', counter: 68, link: '#' },
              isLeaf: true,
            },
          ],
        },
      ],
    },
    {
      key: 'KIDS',
      value: {
        label: 'Kids',
        counter: 263,
        banner: 'https://storage.googleapis.com/sfui_docs_artifacts_bucket_public/production/toy.png',
        bannerTitle: 'Unleash your imagination',
      },
      isLeaf: false,
      children: [
        {
          key: 'ALL_KIDS',
          value: { label: 'All Kids', counter: 263, link: '#' },
          isLeaf: true,
        },
        {
          key: 'CATEGORIES',
          value: { label: 'Categories', counter: 192 },
          isLeaf: false,
          children: [
            {
              key: 'ALL_CATEGORIES',
              value: { label: 'All Categories', counter: 192, link: '#' },
              isLeaf: true,
            },
            {
              key: 'CLOTHING',
              value: { label: 'Clothing', counter: 29, link: '#' },
              isLeaf: true,
            },
            {
              key: 'SHOES',
              value: { label: 'Shoes', counter: 60, link: '#' },
              isLeaf: true,
            },
            {
              key: 'ACCESSORIES',
              value: { label: 'Accessories', counter: 48, link: '#' },
              isLeaf: true,
            },
            {
              key: 'WEARABLES',
              value: { label: 'Wearables', counter: 22, link: '#' },
              isLeaf: true,
            },
            {
              key: 'FOOD_DRINKS',
              value: { label: 'Food & Drinks', counter: 33, link: '#' },
              isLeaf: true,
            },
          ],
        },
        {
          key: 'ACTIVITIES',
          value: { label: 'Activities', counter: 40 },
          isLeaf: false,
          children: [
            {
              key: 'ALL_ACTIVITIES',
              value: { label: 'All Activities', counter: 40, link: '#' },
              isLeaf: true,
            },
            {
              key: 'FOOTBALL',
              value: { label: 'Football', counter: 21, link: '#' },
              isLeaf: true,
            },
            {
              key: 'BASKETBALL',
              value: { label: 'Basketball', counter: 19, link: '#' },
              isLeaf: true,
            },
          ],
        },
        {
          key: 'DEALS',
          value: { label: 'Deals', counter: 31 },
          isLeaf: false,
          children: [
            {
              key: 'ALL_DEALS',
              value: { label: 'All Deals', counter: 31, link: '#' },
              isLeaf: true,
            },
            {
              key: 'OUTLET',
              value: { label: 'Outlet', counter: 31, link: '#' },
              isLeaf: true,
            },
          ],
        },
      ],
    },
  ],
};
</script>

