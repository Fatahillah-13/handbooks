<template>
    <div class="home-page">
        <!-- background -->
        <div class="home-bg"></div>

        <div class="home-content">
            <!-- HEADER -->
            <header class="home-header">
                <div class="brand">
                    <span class="home-icon">🏠</span>
                    <div>
                        <h1 class="home-title">Storage Library</h1>
                        <p class="home-subtitle">
                            Pilih lemari untuk melihat isi dokumen.
                        </p>
                    </div>
                </div>

                <div class="header-right">
                    <div class="top-row">
                        <router-link
                            v-if="canEditContent"
                            class="manage-link"
                            :to="{ name: 'storage-manage' }"
                        >
                            ⚙️ Kelola Storage
                        </router-link>
                    </div>
                    <div class="user-info" v-if="user">
                        <span class="user-name">
                            Logged in as
                            <strong>{{ user.name }}</strong>
                        </span>
                    </div>
                    <button class="logout-btn" @click="handleLogout">
                        Logout
                    </button>
                </div>
            </header>

            <!-- MAIN AREA: teks kecil + grid lemari -->
            <main class="storage-area">
                <div v-if="error" class="error-box">
                    {{ error }}
                </div>

                <div v-if="loading" class="loading">
                    Memuat daftar storage...
                </div>

                <div v-else class="storage-grid">
                    <div
                        v-for="s in storages"
                        :key="s.id"
                        class="storage-card"
                        @click="goToStorage(s)"
                    >
                        <div class="storage-name">
                            {{ s.name }}
                        </div>
                        <div class="cabinet-image"></div>
                    </div>

                    <p
                        v-if="!storages.length && !loading && !error"
                        class="empty-text"
                    >
                        Belum ada storage yang dibuat.
                    </p>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import api from "../api/api";
import PixelFrame from "../components/PixelFrame.vue";
import Breadcrumb from "../components/Breadcrumb.vue";
import useAuth from "../composables/useAuth";

// auth
const { user, canEditContent, logout, requireAuth } = useAuth();

// routing
const router = useRouter();

// state
const storages = ref([]);
const loading = ref(true);
const error = ref(null);

// breadcrumb
const breadcrumbItems = computed(() => [{ label: "Home" }]);

onMounted(async () => {
    if (!requireAuth()) return;

    loading.value = true;
    error.value = null;

    try {
        const res = await api.get("/admin/storages");
        storages.value = res.data;
    } catch (e) {
        console.error(e);
        error.value = "Failed to load storages.";
    } finally {
        loading.value = false;
    }
});

const goToStorage = (storage) => {
    router.push({
        name: "storage",
        params: { slug: storage.slug },
    });
};

const handleLogout = async () => {
    try {
        await logout();
        router.push({ name: "login" });
    } catch (e) {
        console.error(e);
    }
};
</script>

<style scoped>
.home-page {
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100vh;
    overflow: auto;
}

/* BACKGROUND */
.home-bg {
    position: fixed;
    inset: 0;
    background-image: url("/assets/home-bg.jpg"); /* 🔁 GANTI sesuai gambarmu */
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    z-index: 0;
}

/* CONTENT WRAPPER */
.home-content {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    padding: 24px 32px;
    box-sizing: border-box;
}

/* HEADER */
.home-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.brand {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    background: rgba(248, 250, 252, 0.9);
    border: 3px solid #111827;
    box-shadow: 4px 4px 0 #111827;
}

.home-icon {
    font-size: 26px;
}

.home-title {
    margin: 0;
    font-size: 22px;
}

.home-subtitle {
    margin: 2px 0 0;
    font-size: 13px;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 8px;
}

.user-info {
    padding: 4px 8px;
    background: rgba(248, 250, 252, 0.9);
    border: 2px solid #111827;
    box-shadow: 3px 3px 0 #111827;
    font-size: 13px;
}

.user-name strong {
    font-weight: 600;
}

.role {
    color: #4b5563;
    font-size: 12px;
}

/* LOGOUT BUTTON */
.logout-btn {
    padding: 6px 12px;
    border-radius: 0;
    border: 3px solid #111827;
    background: #ef4444;
    color: #f9fafb;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 4px 4px 0 #111827;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    transition: transform 0.05s ease, box-shadow 0.05s ease, filter 0.1s;
}

.logout-btn:hover {
    transform: translate(-1px, -1px);
    box-shadow: 5px 5px 0 #111827;
    filter: brightness(1.05);
}

.logout-btn:active {
    transform: translate(1px, 1px);
    box-shadow: 3px 3px 0 #111827;
}

/* MAIN */
/* area utama lemari, tidak pakai panel */
.storage-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center; /* lemari dekat bagian bawah layar */
    /* padding-top: 4px; */
}

.top-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.breadcrumb {
    font-size: 13px;
    font-weight: 600;
}

.manage-link {
    font-size: 13px;
    text-decoration: none;
    border: 2px solid #111827;
    padding: 4px 8px;
    background: #e5e7eb;
    color: #111827;
    box-shadow: 3px 3px 0 #111827;
}

.manage-link:hover {
    background: #d1d5db;
}

.hint-text {
    margin: 10px 0 16px;
    font-size: 13px;
}

/* GRID STORAGE */
.storage-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 8px;
    /* posisikan di tengah horizontal */
    justify-content: center;
}

.storage-card {
    width: 180px;
    cursor: pointer;
    text-align: center;
    padding: 8px 4px 2px;
    transition: transform 0.06s ease, box-shadow 0.06s ease;
}

.storage-card:hover {
    transform: translate(-2px, -2px);
}

/* gambar lemari */
.cabinet-image {
    width: 140px;
    height: 150px;
    background-image: url("/assets/storage-cabinet-4.png"); /* 🔁 GANTI ke lemari pixelmu */
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center bottom;
    margin-bottom: 6px;
}

.storage-name {
    font-size: 13px;
    font-weight: 600;
    color: whitesmoke;
}

/* STATES */
.loading,
.empty-text {
    font-size: 13px;
}

.error-box {
    font-size: 13px;
    padding: 8px 10px;
    border: 2px solid #b91c1c;
    background: #fee2e2;
    color: #7f1d1d;
    margin-bottom: 10px;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .home-content {
        padding: 16px;
    }

    .home-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .home-main {
        margin-top: 16px;
        padding: 10px 12px;
    }

    .storage-grid {
        justify-content: center;
    }
}
</style>
