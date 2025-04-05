import './bootstrap'

import {createApp} from 'vue'
import routes from './routes/index.js'
import {createPinia} from 'pinia'

import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { createVuetify } from "vuetify";
import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'

const vuetify = createVuetify({
    components,
    directives,
    icons: {
        defaultSet: 'mdi',
    },
})

import toast, {POSITION} from 'vue-toastification'
import 'vue-toastification/dist/index.css'

const pinia = createPinia()

const toastOptions = {
    position: POSITION.TOP_RIGHT,
    timeout: 3000,
    hideProgressBar: true,
};

import App from './App.vue';

const app = createApp(App);

app.use(vuetify);
app.use(toast, toastOptions);
app.use(routes);
app.use(pinia);
app.mount('#app');

