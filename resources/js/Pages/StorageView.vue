<!-- resources/js/Pages/StorageView.vue -->
<template>
    <div class="storage">
        <div class="storage-header">
            <div>
                <h1>📁 Storage: {{ storage.name || route.params.slug }}</h1>
                <p>{{ storage.description }}</p>
                <p v-if="user" class="user-info">
                    Logged in as
                    <strong>{{ user.name }}</strong>
                    ({{ user.username }})
                </p>
            </div>

            <button class="logout-btn" @click="logout">Logout</button>
        </div>

        <p>
            <router-link :to="{ name: 'home' }"
                >← Back to all storages</router-link
            >
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
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../api/api";
import PixelFrame from "../components/PixelFrame.vue";
import axios from "axios";

const route = useRoute();
const router = useRouter();

const storage = ref({ bintexes: [] });
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
        // asumsinya route api storages sudah pakai slug
        const res = await api.get(`/admin/storages/${route.params.slug}`);
        storage.value = res.data;
    } catch (e) {
        console.error(e);
        error.value = "Gagal memuat storage.";
    } finally {
        loading.value = false;
    }
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
.storage {
    text-align: left;
    padding: 20px;
}

.storage-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
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

.user-info {
    margin-top: 4px;
    font-size: 14px;
    color: #4b5563;
}

.bintex-list {
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
}
</style>
