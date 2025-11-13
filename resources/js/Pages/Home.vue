<template>
    <div class="home">
        <div class="home-header">
            <div class="title-section">
                <h1>🏠 Storage Library</h1>
                <p>Select a storage to view its contents:</p>
                <p v-if="user" class="user-info">
                    Logged in as <strong>{{ user.name }}</strong> ({{
                        user.username
                    }})
                </p>
            </div>

            <button class="logout-btn" @click="logout">Logout</button>
        </div>

        <p v-if="loading">Loading storages...</p>
        <p v-if="error" class="error">{{ error }}</p>

        <div v-if="!loading && !error" class="storage-grid">
            <PixelFrame v-for="s in storages" :key="s.id">
                <router-link :to="`/storage/${s.slug}`">
                    {{ s.name }}
                </router-link>
            </PixelFrame>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../api/api";
import PixelFrame from "../components/PixelFrame.vue";
import { useRouter } from "vue-router";

const router = useRouter();

const storages = ref([]);
const loading = ref(true);
const error = ref(null);
const user = ref(null);

// Simple auth guard + load data
onMounted(async () => {
    // cek token dulu
    const token = localStorage.getItem("token");
    if (!token) {
        // kalau belum login, lempar ke /login
        router.push({ name: "login" });
        return;
    }

    const savedUser = localStorage.getItem("user");
    if (savedUser) {
        user.value = JSON.parse(savedUser);
    }

    try {
        const res = await api.get("/admin/storages");
        storages.value = res.data;
    } catch (e) {
        console.error("Failed to load storages", e);
        if (e.response?.status === 401) {
            error.value =
                "Tidak bisa memuat data storage (401). Coba login lagi.";
        } else {
            error.value = "Terjadi kesalahan saat memuat data storage.";
        }
    } finally {
        loading.value = false;
    }
});

// fungsi logout
const logout = async () => {
    try {
        await api.post("/logout"); // kalau gagal juga tidak masalah
    } catch (e) {
        console.error("Logout API error (ignored)", e);
    }

    localStorage.removeItem("token");
    localStorage.removeItem("user");

    router.visit("/login"); // atau window.location.href = "/login";
};
</script>

<style scoped>
a {
    text-decoration: none;
    color: #fafafa;
}

.home {
    text-align: center;
    padding: 20px;
}

.home-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
}

.title-section {
    text-align: left;
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
    white-space: nowrap;
}

.logout-btn:hover {
    opacity: 0.9;
}

.storage-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.error {
    color: #b91c1c;
    margin-top: 8px;
}
</style>
