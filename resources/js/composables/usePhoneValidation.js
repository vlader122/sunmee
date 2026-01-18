export function usePhoneValidation() {
    /**
     * Valida un numero de telefono
     * @param {string} phone - Numero de telefono a validar
     * @returns {'valid' | 'observed' | 'empty'} - Estado de validacion
     */
    function validate(phone) {
        if (!phone || !phone.trim()) {
            return 'empty';
        }

        const tel = phone.trim();

        // Debe contener solo digitos
        if (!/^\d+$/.test(tel)) {
            return 'observed';
        }

        // Debe tener al menos 8 digitos
        if (tel.length < 8) {
            return 'observed';
        }

        // Debe empezar con 6 o 7
        if (!['6', '7'].includes(tel[0])) {
            return 'observed';
        }

        return 'valid';
    }

    /**
     * Obtiene la clase CSS segun el estado de validacion
     * @param {string} status - Estado de validacion
     * @returns {string} - Clase CSS
     */
    function getStatusClass(status) {
        switch (status) {
            case 'valid':
                return 'bg-green-50 border-green-300';
            case 'observed':
                return 'bg-amber-50 border-amber-300';
            case 'empty':
                return 'bg-red-50 border-red-300';
            default:
                return '';
        }
    }

    /**
     * Obtiene el color del badge segun el estado
     * @param {string} status - Estado de validacion
     * @returns {string} - Severidad para PrimeVue Tag
     */
    function getStatusSeverity(status) {
        switch (status) {
            case 'valid':
                return 'success';
            case 'observed':
                return 'warn';
            case 'empty':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    return {
        validate,
        getStatusClass,
        getStatusSeverity
    };
}
