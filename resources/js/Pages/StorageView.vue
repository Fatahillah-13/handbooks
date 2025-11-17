<template>
    <div class="storage">
        <Breadcrumb :items="breadcrumbItems" />

        <div class="storage-header">
            <div>
                <h1>📁 Storage: {{ storage.name || route.params.slug }}</h1>
                <p>{{ storage.description }}</p>

                <p v-if="user" class="user-info">
                    Logged in as
                    <strong>{{ user.name }}</strong>
                    ({{ user.username }})
                </p>

                <!-- 🌟 Hanya admin/editor -->
                <p v-if="canEditContent" class="manage-link">
                    <router-link :to="{ name: 'bintex-manage' }">
                        📚 Go to Bintex Management
                    </router-link>
                </p>
            </div>

            <button class="logout-btn" @click="logout">Logout</button>
        </div>

        <p>
            <router-link :to="{ name: 'home' }">
                ← Back to all storages
            </router-link>
        </p>

        <p v-if="loading">Loading bintexes...</p>
        <p v-if="error" class="error">{{ error }}</p>

        <div v-if="!loading && !error" class="bintex-list">
            <PixelFrame v-for="b in storage.bintexes" :key="b.id">
                <router-link :to="{ name: 'bintex', params: { slug: b.slug } }">
                    {{ b.name }}
                </router-link>
            </PixelFrame>

            <p v-if="!storage.bintexes || !storage.bintexes.length">
                This storage has no bintexes yet.
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute } from "vue-router";
import api from "../api/api";
import PixelFrame from "../components/PixelFrame.vue";
import Breadcrumb from "../components/Breadcrumb.vue";
import useAuth from "../composables/useAuth";

const route = useRoute();
const { user, canEditContent, logout, requireAuth } = useAuth();

const storage = ref({ bintexes: [] });
const loading = ref(true);
const error = ref(null);

const breadcrumbItems = computed(() => [
    { label: "Home", to: { name: "home" } },
    { label: storage.value.name || route.params.slug },
]);

onMounted(async () => {
    if (!requireAuth()) return;

    loading.value = true;
    error.value = null;

    try {
        // backend endpoint: GET /api/admin/storages/{slug}
        const res = await api.get(`/admin/storages/${route.params.slug}`);
        storage.value = res.data;

        if (!storage.value.bintexes) {
            storage.value.bintexes = [];
        }
    } catch (e) {
        console.error(e);
        error.value = "Gagal memuat storage.";
    } finally {
        loading.value = false;
    }
});
</script>

<style scoped>
.storage {
    text-align: left;
    padding: 20px;
}

.storage-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.user-info {
    margin-top: 4px;
    font-size: 14px;
    color: #4b5563;
}

.manage-link {
    margin-top: 8px;
    font-size: 14px;
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

.bintex-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.error {
    color: #b91c1c;
    margin-top: 8px;
}

a {
    text-decoration: none;
    color: #ffffff;
}
</style>
