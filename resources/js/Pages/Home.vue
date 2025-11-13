<!-- resources/js/Pages/Home.vue -->
<template>
    <div class="home">
        <div class="home-header">
            <div>
                <h1>🏠 Storage Library</h1>
                <p>Select a storage to view its contents:</p>
                <p v-if="user" class="user-info">
                    Logged in as
                    <strong>{{ user.name }}</strong>
                    ({{ user.username }})
                </p>
            </div>

            <button class="logout-btn" @click="logout">Logout</button>
        </div>

        <p v-if="loading">Loading storages...</p>
        <p v-if="error" class="error">{{ error }}</p>

        <div v-if="!loading && !error" class="storage-grid">
            <PixelFrame v-for="s in storages" :key="s.id">
                <router-link
                    :to="{ name: 'storage', params: { slug: s.slug } }"
                >
                    {{ s.name }}
                </router-link>
            </PixelFrame>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../api/api";
import PixelFrame from "../components/PixelFrame.vue";
import axios from "axios";

const router = useRouter();

const storages = ref([]);
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
        const res = await api.get("/admin/storages");
        storages.value = res.data;
    } catch (e) {
        console.error(e);
        error.value = "Gagal memuat storages.";
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
.home {
    text-align: left;
    padding: 20px;
}

.home-header {
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

.storage-grid {
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
