import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import Money from './components/ui/Money.vue';

createApp(App)
    .use(createPinia())
    .use(router)
    .component('Money', Money)
    .mount('#dashboard-app');