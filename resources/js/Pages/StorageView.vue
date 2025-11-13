<template>
    <div class="storage">
        <div class="storage-header">
            <div class="title-section">
                <h1>📁 Storage: {{ storageName || route.params.slug }}</h1>
                <p>List of bintexes inside this storage:</p>

                <p v-if="user" class="user-info">
                    Logged in as <strong>{{ user.name }}</strong> ({{
                        user.username
                    }})
                </p>
            </div>

            <button class="logout-btn" @click="logout">Logout</button>
        </div>

        <p>
            <router-link to="/">← Back to all storages</router-link>
        </p>

        <p v-if="loading">Loading bintexes...</p>
        <p v-if="error" class="error">{{ error }}</p>

        <div v-if="!loading && !error" class="bintex-list">
            <PixelFrame v-for="b in bintexes" :key="b.id">
                <router-link :to="{ name: 'bintex', params: { slug: b.slug } }">
                    {{ b.name }}
                </router-link>
            </PixelFrame>

            <p v-if="bintexes.length === 0">
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

const route = useRoute();
const router = useRouter();

const bintexes = ref([]);
const storageName = ref("");
const loading = ref(true);
const error = ref(null);
const user = ref(null);

onMounted(async () => {
    // auth guard sederhana
    const token = localStorage.getItem("token");
    if (!token) {
        router.visit("/login");
        return;
    }

    const savedUser = localStorage.getItem("user");
    if (savedUser) {
        user.value = JSON.parse(savedUser);
    }
    const slug = route.params.slug;

    try {
        // 1) Ambil semua bintex
        const res = await api.get("/admin/bintexes");

        // 2) Filter hanya yang storage.slug-nya cocok
        const allBintexes = res.data;
        const filtered = allBintexes.filter(
            (b) => b.storage && b.storage.slug === slug
        );

        bintexes.value = filtered;

        // 3) Ambil nama storage dari bintex pertama (kalau ada)
        if (filtered.length > 0 && filtered[0].storage) {
            storageName.value = filtered[0].storage.name;
        } else {
            storageName.value = slug;
        }
    } catch (e) {
        console.error("Failed to load bintexes", e);
        if (e.response?.status === 401) {
            error.value = "Tidak bisa memuat bintexes (401). Coba login lagi.";
        } else {
            error.value = "Terjadi kesalahan saat memuat bintexes.";
        }
    } finally {
        loading.value = false;
    }
});

// logout sama seperti di Home
const logout = async () => {
    try {
        await api.post("/logout");
    } catch (e) {
        console.error("Logout API error (ignored)", e);
    }

    localStorage.removeItem("token");
    localStorage.removeItem("user");
    router.visit("/login");
};
</script>

<style scoped>
.storage {
    text-align: center;
    padding: 20px;
}

.storage-header {
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
