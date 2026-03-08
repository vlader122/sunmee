import './bootstrap';
import { createApp } from 'vue';
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';

// Componentes PrimeVue
import AutoComplete from 'primevue/autocomplete';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import Fieldset from 'primevue/fieldset';
import Divider from 'primevue/divider';
import Message from 'primevue/message';
import ProgressSpinner from 'primevue/progressspinner';
import ConfirmDialog from 'primevue/confirmdialog';
import Paginator from 'primevue/paginator';
import SelectButton from 'primevue/selectbutton';
import Chip from 'primevue/chip';
import Badge from 'primevue/badge';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import Checkbox from 'primevue/checkbox';

// Componentes personalizados
import MemberAutocomplete from './components/MemberAutocomplete.vue';
import MemberList from './components/MemberList.vue';
import AgendaForm from './components/AgendaForm.vue';
import SuggestionsPanel from './components/SuggestionsPanel.vue';
import Dashboard from './components/Dashboard.vue';
import HistorialAgendas from './components/HistorialAgendas.vue';
import GenerarCartas from './components/GenerarCartas.vue';

// Exportar componentes para uso desde Blade
window.VueComponents = {
    MemberAutocomplete,
    MemberList,
    AgendaForm,
    SuggestionsPanel,
    Dashboard,
    HistorialAgendas,
    GenerarCartas
};

// Funcion global para montar aplicaciones Vue
window.createVueApp = function(rootComponent, props = {}) {
    const app = createApp(rootComponent, props);

    // Configurar PrimeVue con tema Aura
    app.use(PrimeVue, {
        theme: {
            preset: Aura,
            options: {
                darkModeSelector: '.dark-mode',
                cssLayer: false
            }
        }
    });
    app.use(ToastService);
    app.use(ConfirmationService);

    // Registrar componentes PrimeVue globalmente
    app.component('AutoComplete', AutoComplete);
    app.component('DataTable', DataTable);
    app.component('Column', Column);
    app.component('InputText', InputText);
    app.component('InputNumber', InputNumber);
    app.component('Textarea', Textarea);
    app.component('Button', Button);
    app.component('Dialog', Dialog);
    app.component('Toast', Toast);
    app.component('Tag', Tag);
    app.component('Card', Card);
    app.component('Select', Select);
    app.component('DatePicker', DatePicker);
    app.component('Fieldset', Fieldset);
    app.component('Divider', Divider);
    app.component('Message', Message);
    app.component('ProgressSpinner', ProgressSpinner);
    app.component('ConfirmDialog', ConfirmDialog);
    app.component('Paginator', Paginator);
    app.component('SelectButton', SelectButton);
    app.component('Chip', Chip);
    app.component('Badge', Badge);
    app.component('InputGroup', InputGroup);
    app.component('InputGroupAddon', InputGroupAddon);
    app.component('Checkbox', Checkbox);

    return app;
};

// Funcion helper para montar directamente
window.mountVueApp = function(selector, rootComponent, props = {}) {
    const app = window.createVueApp(rootComponent, props);
    app.mount(selector);
    return app;
};
