<template>
    <div class="home">
        <!-- Breadcrumb opsional -->
        <Breadcrumb :items="breadcrumbItems" />

        <div class="header-row">
            <div>
                <h1>🏠 Storage Library</h1>
                <p>Select a storage to view its contents:</p>

                <p v-if="user" class="user-info">
                    Logged in as <strong>{{ user.name }}</strong> ({{
                        user.username
                    }})
                </p>

                <!-- 🌟 MENU ADMIN/EDITOR -->
                <p v-if="canEditContent" class="manage-link">
                    <router-link :to="{ name: 'storage-manage' }">
                        ⚙️ Go to Storage Management
                    </router-link>
                </p>
            </div>

            <!-- tombol logout -->
            <button class="logout-btn" @click="logout">Logout</button>
        </div>

        <!-- error / loading -->
        <p v-if="loading">Loading storages...</p>
        <p v-if="error" class="error">{{ error }}</p>

        <!-- LIST STORAGE -->
        <div v-if="!loading && storages.length" class="storage-grid">
            <PixelFrame v-for="s in storages" :key="s.id">
                <router-link :to="'/storage/' + s.slug">
                    {{ s.name }}
                </router-link>
            </PixelFrame>
        </div>

        <p v-if="!loading && !storages.length">No storages available.</p>
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
</script>

<style scoped>
.home {
    padding: 20px;
}

.header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.user-info {
    font-size: 14px;
    color: #4b5563;
}

.manage-link {
    margin-top: 10px;
    font-size: 15px;
}

.logout-btn {
    padding: 6px 12px;
    border: none;
    background: #ef4444;
    color: white;
    border-radius: 5px;
    cursor: pointer;
}
.logout-btn:hover {
    opacity: 0.8;
}

.storage-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

a {
    text-decoration: none;
    color: #ffffff;
}

.error {
    color: #b91c1c;
    margin-top: 10px;
}
</style>
