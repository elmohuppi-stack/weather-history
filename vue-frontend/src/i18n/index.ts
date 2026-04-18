import { createI18n } from 'vue-i18n'
import de from './locales/de.json'

// Type-define locale messages as schema to provide type-checking and intellisense
type MessageSchema = typeof de

const i18n = createI18n<[MessageSchema], 'de'>({
  legacy: false, // Use Composition API
  locale: 'de', // Default language
  fallbackLocale: 'de', // Fallback language
  messages: {
    de
  }
})

export default i18n
