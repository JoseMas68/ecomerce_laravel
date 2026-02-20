import translations from '../lang/es.json';

/**
 * Composable para manejar traducciones en español
 * @returns {Object} - Objeto con función t() para traducir claves
 */
export function useTranslations() {
    /**
     * Traduce una clave usando notación de puntos
     * @param {string} key - Clave de traducción (ej: 'nav.shop', 'cart.title')
     * @param {Object} params - Parámetros para reemplazar en la traducción
     * @returns {string} - Texto traducido
     *
     * @example
     * t('nav.shop') // 'Tienda'
     * t('validation.min_length', { min: 5 }) // 'Debe tener al menos 5 caracteres'
     */
    const t = (key, params = {}) => {
        // Dividir la clave por puntos para navegar el objeto
        const keys = key.split('.');

        // Buscar la traducción en el objeto JSON
        let result = keys.reduce((obj, k) => obj?.[k], translations);

        // Si no se encuentra la traducción, devolver la clave original
        if (result === undefined) {
            console.warn(`Translation key not found: ${key}`);
            return key;
        }

        // Si hay parámetros, reemplazarlos en el texto
        if (Object.keys(params).length > 0) {
            Object.keys(params).forEach(param => {
                const placeholder = `{${param}}`;
                result = result.replace(new RegExp(placeholder, 'g'), params[param]);
            });
        }

        return result;
    };

    /**
     * Verifica si existe una clave de traducción
     * @param {string} key - Clave a verificar
     * @returns {boolean}
     */
    const has = (key) => {
        const keys = key.split('.');
        const result = keys.reduce((obj, k) => obj?.[k], translations);
        return result !== undefined;
    };

    return {
        t,
        has,
        translations
    };
}
