import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import Money from './components/ui/Money.vue';
import LoadingSpinner from './components/ui/LoadingSpinner.vue';

createApp(App)
    .use(createPinia())
    .use(router)
    .component('Money', Money)
    .component('LoadingSpinner', LoadingSpinner)
    .mount('#dashboard-app');
