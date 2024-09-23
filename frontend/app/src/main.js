import { createApp } from 'vue';
import App from './App.vue';
import router from './router'; // Only if using Vue Router
import './assets/tailwind.css';

const app = createApp(App);

// Use Vue Router if installed
if (router) {
  app.use(router);
}

app.mount('#app');
