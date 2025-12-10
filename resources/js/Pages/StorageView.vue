<template>
    <div class="storage-page">
        <!-- background sama seperti Home -->
        <div class="storage-bg"></div>

        <div class="storage-content">
            <!-- HEADER -->
            <header class="home-header">
                <div class="brand">
                    <span class="home-icon">📁</span>
                    <div>
                        <h1 class="home-title">
                            Storage: {{ storage.name || route.params.slug }}
                        </h1>
                        <p class="home-subtitle">
                            Pilih bintex untuk melihat isi dokumen di lemari
                            ini.
                        </p>
                        <p v-if="storage.description" class="storage-desc">
                            {{ storage.description }}
                        </p>
                    </div>
                </div>

                <div class="header-right">
                    <div class="top-row">
                        <router-link
                            v-if="canEditContent"
                            class="manage-link"
                            :to="{ name: 'bintex-manage' }"
                        >
                            ⚙️ Kelola Bintex
                        </router-link>
                    </div>

                    <div class="user-info" v-if="user">
                        <span class="user-name">
                            Logged in as
                            <strong>{{ user.name }}</strong>
                            <span class="user-username"
                                >({{ user.username }})</span
                            >
                        </span>
                    </div>

                    <button class="logout-btn" @click="logout">Logout</button>
                </div>
            </header>

            <!-- BREADCRUMB -->
            <div class="breadcrumb-wrapper">
                <Breadcrumb :items="breadcrumbItems" />
            </div>

            <!-- MAIN AREA -->
            <main class="storage-main">
                <div v-if="error" class="error-box">
                    {{ error }}
                </div>

                <div v-if="loading" class="loading">Memuat bintex...</div>

                <section v-else class="bintex-section">
                    <h2 class="section-title">
                        Bintex di lemari ini
                        <span class="count-badge">
                            {{ storage.bintexes.length }}
                        </span>
                    </h2>

                    <!-- ada bintex -->
                    <div
                        v-if="storage.bintexes && storage.bintexes.length"
                        class="bintex-grid"
                    >
                        <PixelFrame
                            v-for="b in storage.bintexes"
                            :key="b.id"
                            class="bintex-card"
                        >
                            <router-link
                                :to="{
                                    name: 'bintex',
                                    params: { slug: b.slug },
                                }"
                                class="bintex-link"
                            >
                                <div class="bintex-inner">
                                    <div class="bintex-image"></div>

                                    <!-- label di bawah gambar -->
                                    <div class="bintex-label">
                                        <div class="bintex-name">
                                            {{ b.name }}
                                        </div>
                                        <div class="bintex-slug">
                                            /{{ b.slug }}
                                        </div>
                                    </div>
                                </div>
                            </router-link>
                        </PixelFrame>
                    </div>

                    <!-- kosong -->
                    <p v-else class="empty-text">
                        Lemari ini belum punya bintex.
                    </p>
                </section>

                <!-- back -->
                <div class="back-wrapper">
                    <router-link :to="{ name: 'home' }" class="back-link">
                        ⬅ Kembali ke semua storage
                    </router-link>
                </div>
            </main>
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
.storage-page {
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100vh;
    overflow: auto;
}

/* BACKGROUND – samakan dengan Home.vue */
.storage-bg {
    position: fixed;
    inset: 0;
    background-image: url("/assets/home-bg.jpg"); /* sesuaikan dgn path di Home.vue */
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    z-index: 0;
}

/* CONTENT WRAPPER */
.storage-content {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    padding: 24px 32px;
    box-sizing: border-box;
}

/* HEADER – copy gaya Home */
.home-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
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

.storage-desc {
    margin: 4px 0 0;
    font-size: 12px;
    color: #4b5563;
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

.user-username {
    margin-left: 4px;
    color: #4b5563;
    font-size: 12px;
}

/* LOGOUT – sama dengan Home */
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

/* BREADCRUMB */
.breadcrumb-wrapper {
    margin: 4px 0 10px;
    max-width: 900px;
}

.breadcrumb-wrapper :deep(a) {
    text-decoration: none;
}

/* MAIN AREA */
.storage-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* PANEL BIN-TEX (panel gelap transparan di tengah) */
.bintex-section {
    margin-top: 8px;
    padding: 12px 16px 16px;
    background: rgba(15, 23, 42, 0.75);
    border-radius: 10px;
    border: 2px solid rgba(15, 23, 42, 0.9);
    box-shadow: 0 14px 30px rgba(0, 0, 0, 0.6);
    color: #f9fafb;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
    margin: 0 0 10px;
}

.count-badge {
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 11px;
    background: rgba(248, 250, 252, 0.1);
}

/* GRID BIN-TEX – kartu pixel */
.bintex-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
}

.bintex-card {
    padding: 0;
}

.bintex-link {
    display: block;
    text-decoration: none;
    color: inherit;
}

.bintex-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 4px 0 2px;
    cursor: pointer;
}

.bintex-image {
    width: 140px;
    height: 90px;
    background-image: url("/assets/bintex-scroll.png");
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    image-rendering: pixelated;
    transition: transform 0.06s ease, filter 0.06s ease;
}

.bintex-label {
    text-align: center;
    padding: 4px 6px;
    background: rgba(15, 23, 42, 0.9);
    border: 2px solid rgba(15, 23, 42, 1);
    box-shadow: 0 2px 0 rgba(0, 0, 0, 0.8);
    min-width: 150px;
}

.bintex-name {
    font-size: 14px;
    font-weight: 700;
    color: #fff7ed;
}

.bintex-slug {
    font-size: 11px;
    opacity: 0.9;
}

.bintex-inner:hover .bintex-image {
    transform: translateY(-3px);
    filter: brightness(1.1);
}

.bintex-inner:hover .bintex-label {
    box-shadow: 0 4px 0 rgba(0, 0, 0, 0.9);
}

/* tombol kelola */
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

/* BACK LINK BAWAH */
.back-wrapper {
    margin-top: 12px;
    text-align: center;
}

.back-link {
    font-size: 13px;
    text-decoration: none;
    padding: 4px 10px;
    border-radius: 0;
    border: 2px solid #111827;
    background: rgba(248, 250, 252, 0.9);
    color: #111827;
    box-shadow: 3px 3px 0 #111827;
}

.back-link:hover {
    background: #e5e7eb;
}

/* STATES */
.loading,
.empty-text {
    font-size: 13px;
    text-align: center;
    color: #f9fafb;
    text-shadow: 2px 2px 0 #111827;
    margin-top: 10px;
}

.error-box {
    font-size: 13px;
    padding: 8px 10px;
    border: 2px solid #b91c1c;
    background: #fee2e2;
    color: #7f1d1d;
    margin-bottom: 10px;
    max-width: 480px;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .storage-content {
        padding: 16px;
    }

    .home-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .bintex-section {
        padding: 10px 12px 14px;
    }
}
</style>
