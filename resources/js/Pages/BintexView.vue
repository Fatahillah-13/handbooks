<template>
    <div class="bintex">
        <div class="bintex-header">
            <div>
                <h1>📚 {{ bintex.name || route.params.slug }}</h1>
                <p>{{ bintex.description }}</p>
                <p v-if="user" class="user-info">
                    Logged in as <strong>{{ user.name }}</strong> ({{
                        user.username
                    }})
                </p>
            </div>

            <button class="logout-btn" @click="logout">Logout</button>
        </div>

        <p>
            <router-link
                :to="{
                    name: 'storage',
                    params: {
                        slug: bintex.storage?.slug || route.query.storage,
                    },
                }"
            >
                ← Back to storage
            </router-link>
        </p>

        <p v-if="loading">Loading documents...</p>
        <p v-if="error" class="error">{{ error }}</p>

        <div v-if="!loading && !error" class="docs">
            <PixelFrame v-for="doc in bintex.documents" :key="doc.id">
                <!-- pakai slug doc untuk viewer -->
                <router-link
                    :to="{ name: 'viewer', params: { id: doc.id } }"
                >
                    {{ doc.title }}
                </router-link>
            </PixelFrame>

            <p v-if="!bintex.documents || bintex.documents.length === 0">
                This bintex has no documents yet.
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

const bintex = ref({ documents: [], storage: null });
const loading = ref(true);
const error = ref(null);
const user = ref(null);

onMounted(async () => {
    // auth guard sederhana
    const token = localStorage.getItem("token");
    if (!token) {
        router.push({ name: "login" });
        return;
    }

    const savedUser = localStorage.getItem("user");
    if (savedUser) {
        user.value = JSON.parse(savedUser);
    }

    loading.value = true;
    error.value = null;

    try {
        // ambil bintex berdasarkan slug di URL
        const res = await api.get(`/admin/bintexes/${route.params.slug}`);
        bintex.value = res.data; // harus sudah memuat documents di backend
    } catch (e) {
        console.error("Failed to load bintex", e);
        if (e.response?.status === 404) {
            error.value = "Bintex tidak ditemukan.";
        } else if (e.response?.status === 401) {
            error.value = "Tidak bisa memuat bintex (401). Coba login lagi.";
        } else {
            error.value = "Terjadi kesalahan saat memuat bintex.";
        }
    } finally {
        loading.value = false;
    }
});

const logout = async () => {
    try {
        await api.post("/logout");
    } catch (e) {
        console.error("Logout API error (ignored)", e);
    }

    localStorage.removeItem("token");
    localStorage.removeItem("user");
    router.push({ name: "login" });
};
</script>

<style scoped>
.bintex {
    text-align: center;
    padding: 20px;
}

.bintex-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
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

.docs {
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
