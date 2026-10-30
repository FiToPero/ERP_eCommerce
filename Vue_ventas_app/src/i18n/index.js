import { createI18n } from 'vue-i18n'

import en from '@/Locales/en.json'
import es from '@/Locales/es.json'
import it from '@/Locales/it.json'

const i18n = createI18n({
    legacy: false,
    locale: 'es',
    fallbackLocale: 'en',
    messages: {
        en,
        es,
        it,
    },
})

export default i18n