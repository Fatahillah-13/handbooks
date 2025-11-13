import "./bootstrap";
import { createApp, h } from "vue";
import router from "./router/router.js";
import App from "./App.vue";

// createInertiaApp({
//     resolve: (name) => {
//         const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
//         return pages[`./Pages/${name}.vue`];
//     },
//     setup({ el, App, props, plugin }) {
//         createApp({ render: () => h(App, props) })
//             .use(plugin)
//             .use(router)
//             .mount(el);
//     },
// });

createApp(App).use(router).mount("#app");
