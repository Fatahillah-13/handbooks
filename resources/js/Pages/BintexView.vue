<!-- resources/js/Pages/BintexView.vue -->
<template>
    <div class="bintex">
        <Breadcrumb :items="breadcrumbItems" />
        <div class="bintex-header">
            <div>
                <h1>📚 {{ bintex.name || route.params.slug }}</h1>
                <p>{{ bintex.description }}</p>
                <p v-if="user" class="user-info">
                    Logged in as
                    <strong>{{ user.name }}</strong>
                    ({{ user.username }})
                </p>
            </div>

            <button class="logout-btn" @click="logout">Logout</button>
        </div>

        <p>
            <router-link
                v-if="bintex.storage"
                :to="{ name: 'storage', params: { slug: bintex.storage.slug } }"
            >
                ← Back to Storage
            </router-link>
        </p>

        <p v-if="loading">Loading documents...</p>
        <p v-if="error" class="error">{{ error }}</p>

        <div v-if="!loading && !error" class="docs">
            <PixelFrame v-for="doc in bintex.documents" :key="doc.id">
                <!-- pakai id dokumen untuk viewer -->
                <router-link :to="{ name: 'viewer', params: { id: doc.id } }">
                    {{ doc.title }}
                </router-link>
            </PixelFrame>

            <p v-if="!bintex.documents || !bintex.documents.length">
                This bintex has no documents yet.
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../api/api";
import PixelFrame from "../components/PixelFrame.vue";
import Breadcrumb from "../components/Breadcrumb.vue";
import axios from "axios";

const route = useRoute();
const router = useRouter();

const bintex = ref({ documents: [], storage: null });
const loading = ref(true);
const error = ref(null);
const user = ref(null);

onMounted(async () => {
    const userStr = localStorage.getItem("user");
    if (!userStr) {
        router.push({ name: "login" });
        return;
    }
    user.value = JSON.parse(userStr);

    loading.value = true;
    try {
        const res = await api.get(`/admin/bintexes/${route.params.slug}`);
        bintex.value = res.data;
    } catch (e) {
        console.error(e);
        error.value = "Gagal memuat bintex.";
    } finally {
        loading.value = false;
    }
});

const breadcrumbItems = computed(() => {
    const items = [{ label: "Home", to: { name: "home" } }];

    if (bintex.value.storage) {
        items.push({
            label: bintex.value.storage.name,
            to: {
                name: "storage",
                params: { slug: bintex.value.storage.slug },
            },
        });
    }

    items.push({
        label: bintex.value.name || route.params.slug,
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
.bintex {
    text-align: left;
    padding: 20px;
}

.bintex-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.user-info {
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

.docs {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.error {
    color: #b91c1c;
}

a {
    text-decoration: none;
    color: #ffffff;
}
</style>
