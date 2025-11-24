<!-- resources/js/Pages/Viewer.vue -->
<template>
    <div class="viewer">
        <Breadcrumb :items="breadcrumbItems" />
        <div class="viewer-header">
            <div>
                <h1>📖 {{ title || "Document" }}</h1>
                <p class="subtitle">Document preview (read-only)</p>
            </div>

            <button class="logout-btn" @click="logout">Logout</button>
        </div>

        <p v-if="loading">Loading pages...</p>
        <p v-if="error" class="error">{{ error }}</p>

        <FlipbookRealistic v-if="pages.length" :pages="pages" />

        <p v-if="!loading && !error && !pages.length">
            This document has no pages to display.
        </p>

        <p class="back-link">
            <router-link :to="{ name: 'home' }"> ← Back to Home </router-link>
        </p>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../api/api";
import Flipbook from "../components/Flipbook.vue";
import Breadcrumb from "../components/Breadcrumb.vue";
import axios from "axios";
// import Flipbook from "../components/Flipbook.vue";
import FlipbookRealistic from "../components/FlipbookRealistic.vue";

const route = useRoute();
const router = useRouter();

const pages = ref([]);
const title = ref("");
const loading = ref(true);
const error = ref(null);

// Breadcrumb data
const bintex = ref(null);
const storage = ref(null);

onMounted(async () => {
    const userStr = localStorage.getItem("user");
    if (!userStr) {
        router.push({ name: "login" });
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        const res = await api.get(`/viewer/${route.params.id}`);
        pages.value = res.data.pages || [];
        title.value = res.data.title || "";
        bintex.value = res.data.bintex || null;
        storage.value = res.data.storage || null;
    } catch (e) {
        console.error(e);
        error.value = "Gagal memuat dokumen.";
    } finally {
        loading.value = false;
    }
});

const breadcrumbItems = computed(() => {
    const items = [{ label: "Home", to: { name: "home" } }];

    if (storage.value) {
        items.push({
            label: storage.value.name,
            to: { name: "storage", params: { slug: storage.value.slug } },
        });
    }

    if (bintex.value) {
        items.push({
            label: bintex.value.name,
            to: { name: "bintex", params: { slug: bintex.value.slug } },
        });
    }

    items.push({
        label: title.value || "Document",
    });

    return items;
});

const logout = async () => {
    try {
        await axios.post("/logout");
    } catch (e) {
        console.error("Logout error (ignored)", e);
    }
    localStorage.removeItem("user");
    router.push({ name: "login" });
};
</script>

<style scoped>
.viewer {
    text-align: center;
    padding: 20px;
}

.viewer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.subtitle {
    margin-top: 4px;
    font-size: 14px;
    color: #4b5563;
}

.logout-btn {
    padding: 6px 12px;
    font-size: 14px;
    border-radius: 4px;
    border: none;
    background: #ef4444;
    color: white;
    cursor: pointer;
}

.logout-btn:hover {
    opacity: 0.9;
}

.error {
    margin-top: 10px;
    color: #b91c1c;
}

.back-link {
    margin-top: 16px;
}
</style>
