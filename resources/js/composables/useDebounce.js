import { ref, watch } from 'vue';

/**
 * Composable para debounce de valores reactivos
 * @param {Ref} value - Valor reactivo a debounce
 * @param {number} delay - Tiempo de espera en ms (default: 300)
 * @returns {Ref} - Valor con debounce aplicado
 */
export function useDebounce(value, delay = 300) {
    const debouncedValue = ref(value.value);
    let timeout = null;

    watch(value, (newValue) => {
        if (timeout) {
            clearTimeout(timeout);
        }

        timeout = setTimeout(() => {
            debouncedValue.value = newValue;
        }, delay);
    });

    return debouncedValue;
}

/**
 * Funcion debounce simple (no reactiva)
 * @param {Function} fn - Funcion a ejecutar con debounce
 * @param {number} delay - Tiempo de espera en ms
 * @returns {Function} - Funcion con debounce
 */
export function debounce(fn, delay = 300) {
    let timeout = null;

    return function (...args) {
        if (timeout) {
            clearTimeout(timeout);
        }

        timeout = setTimeout(() => {
            fn.apply(this, args);
        }, delay);
    };
}
