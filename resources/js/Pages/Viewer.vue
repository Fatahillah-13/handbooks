<template>
    <div class="viewer">
        <div class="viewer-header">
            <div>
                <h1>📖 {{ title || route.params.slug }}</h1>
                <p class="subtitle">Document preview (read-only)</p>
            </div>

            <button class="logout-btn" @click="logout">Logout</button>
        </div>

        <p v-if="loading">Loading pages...</p>
        <p v-if="error" class="error">{{ error }}</p>

        <Flipbook v-if="!loading && !error && pages.length" :pages="pages" />

        <p v-if="!loading && !error && !pages.length">
            This document has no pages to display.
        </p>

        <p class="back-link">
            <router-link
                :to="{
                    name: 'bintex',
                    params: {
                        slug: route.query.bintex || 'peraturan-karyawan',
                    },
                }"
            >
                ← Back to Bintex
            </router-link>
        </p>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import api from "../api/api";
import Flipbook from "../components/Flipbook.vue";

const route = useRoute();
const pages = ref([]);
const title = ref("");
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    try {
        const res = await api.get(`/viewer/${route.params.id}`);
        pages.value = res.data.pages;
        title.value = res.data.title;
    } catch (e) {
        console.error(e);
        error.value = "Gagal memuat dokumen.";
    } finally {
        loading.value = false;
    }
});
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

.back-link a {
    text-decoration: none;
}
</style>
