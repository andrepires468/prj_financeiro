const STORAGE_KEY = 'financeiro.theme'
const THEME_LIGHT = 'light'
const THEME_DARK = 'dark'

/**
 * Retorna o tema salvo no localStorage. Padrão: 'light'.
 * @returns {'light'|'dark'}
 */
export function getTheme() {
  const saved = localStorage.getItem(STORAGE_KEY)
  if (saved === THEME_DARK || saved === THEME_LIGHT) return saved
  return THEME_LIGHT
}

/**
 * Salva o tema no localStorage e aplica no documento (data-bs-theme).
 * Dispara o evento 'financeiro-theme-changed' para componentes reagirem.
 * @param {'light'|'dark'} theme
 */
export function setTheme(theme) {
  const value = theme === THEME_DARK ? THEME_DARK : THEME_LIGHT
  localStorage.setItem(STORAGE_KEY, value)
  document.documentElement.setAttribute('data-bs-theme', value)
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent('financeiro-theme-changed', { detail: value }))
  }
}

/**
 * Aplica o tema salvo no documento. Chamar no carregamento da aplicação.
 */
export function initTheme() {
  setTheme(getTheme())
}

export const THEME = { LIGHT: THEME_LIGHT, DARK: THEME_DARK }
