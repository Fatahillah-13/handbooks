import { createRouter, createWebHistory } from "vue-router";
import Home from "../Pages/Home.vue";
import StorageView from "../Pages/StorageView.vue";
import BintexView from "../Pages/BintexView.vue";
import Viewer from "../Pages/Viewer.vue";
import Login from "../Pages/Login.vue";

const routes = [
    { path: "/login", name: "login", component: Login },
    { path: "/", name: "home", component: Home },
    { path: "/storage/:slug", name: "storage", component: StorageView },
    { path: "/bintex/:slug", name: "bintex", component: BintexView },
    { path: "/viewer/:id", name: "viewer", component: Viewer },
];

// export default createRouter({
//     history: createWebHistory(),
//     routes,
// });


const router = createRouter({history:createWebHistory(),routes,})

export default router;

