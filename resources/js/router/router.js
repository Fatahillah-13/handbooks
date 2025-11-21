import { createRouter, createWebHistory } from "vue-router";
import Home from "../Pages/Home.vue";
import StorageView from "../Pages/StorageView.vue";
import BintexView from "../Pages/BintexView.vue";
import Viewer from "../Pages/Viewer.vue";
import Login from "../Pages/Login.vue";
import StorageManage from "../Pages/StorageManage.vue";
import BintexManage from "../Pages/BintexManage.vue";
import UsersManage from "../Pages/UsersManage.vue";
import AuditLogs from "../Pages/AuditLogs.vue";

const routes = [
    { path: "/login", name: "login", component: Login },
    { path: "/", name: "home", component: Home },
    { path: "/storage/:slug", name: "storage", component: StorageView },
    { path: "/bintex/:slug", name: "bintex", component: BintexView },
    { path: "/viewer/:id", name: "viewer", component: Viewer },
    {
        path: "/admin/storages",
        name: "storage-manage",
        component: StorageManage,
    },
    {
        path: "/admin/bintexes",
        name: "bintex-manage",
        component: BintexManage,
    },
    { path: "/admin/users", name: "users-manage", component: UsersManage },
    { path: "/admin/audit-logs", name: "audit-logs", component: AuditLogs },
];

const router = createRouter({ history: createWebHistory(), routes });

router.beforeEach((to, from, next) => {
    if (to.name === "login") {
        return next();
    }

    const userStr = localStorage.getItem("user");
    if (!userStr) {
        return next({ name: "login" });
    }
    next();
});

export default router;
