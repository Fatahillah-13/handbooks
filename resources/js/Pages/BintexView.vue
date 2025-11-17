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

        <!-- LIST DOKUMEN -->
        <div v-if="!loading && !error" class="docs">
            <PixelFrame v-for="doc in bintex.documents" :key="doc.id">
                <router-link :to="{ name: 'viewer', params: { id: doc.id } }">
                    {{ doc.title }}
                </router-link>
            </PixelFrame>

            <p v-if="!bintex.documents || !bintex.documents.length">
                This bintex has no documents yet.
            </p>
        </div>

        <!-- FORM UPLOAD DOKUMEN -->
        <div v-if="!loading && !error && canEditContent" class="upload-section">
            <h2>➕ Upload New Document</h2>

            <form @submit.prevent="submitUpload" class="upload-form">
                <div class="form-row">
                    <label for="title">Title</label>
                    <input
                        id="title"
                        v-model="uploadForm.title"
                        type="text"
                        placeholder="e.g. Peraturan Umum"
                    />
                </div>

                <div class="form-row">
                    <label for="file">PDF File</label>
                    <input
                        id="file"
                        type="file"
                        accept="application/pdf"
                        @change="handleFileChange"
                    />
                </div>

                <button type="submit" :disabled="uploadLoading">
                    <span v-if="uploadLoading">Uploading...</span>
                    <span v-else>Upload</span>
                </button>

                <p v-if="uploadError" class="error">
                    {{ uploadError }}
                </p>
                <p v-if="uploadSuccess" class="success">
                    {{ uploadSuccess }}
                </p>
            </form>
        </div>

        <p v-if="!loading && !error && !canEditContent" class="readonly-info">
            You are in read-only mode. Only editors and admins can upload
            documents.
        </p>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from "../api/api";
import PixelFrame from "../components/PixelFrame.vue";
import Breadcrumb from "../components/Breadcrumb.vue";
import axios from "axios";
import useAuth from "../composables/useAuth";

const route = useRoute();
const router = useRouter();

const {
    user,
    isAdmin,
    isEditor,
    canEditContent,
    isLoggedIn,
    logout,
    requireAuth,
} = useAuth();

const bintex = ref({ documents: [], storage: null });
const loading = ref(true);
const error = ref(null);

// ====== UPLOAD STATE ======
const uploadForm = ref({
    title: "",
    file: null,
});
const uploadLoading = ref(false);
const uploadError = ref(null);
const uploadSuccess = ref(null);

// breadcrumb
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

onMounted(async () => {
    if (!requireAuth()) return;

    loading.value = true;
    try {
        const res = await api.get(`/admin/bintexes/${route.params.slug}`);
        bintex.value = res.data;
        if (!bintex.value.documents) {
            bintex.value.documents = [];
        }
    } catch (e) {
        console.error(e);
        error.value = "Gagal memuat bintex.";
    } finally {
        loading.value = false;
    }
});

const handleFileChange = (event) => {
    const file = event.target.files[0];
    uploadForm.value.file = file || null;
};

const submitUpload = async () => {
    uploadError.value = null;
    uploadSuccess.value = null;

    if (!uploadForm.value.title.trim()) {
        uploadError.value = "Title is required.";
        return;
    }

    if (!uploadForm.value.file) {
        uploadError.value = "PDF file is required.";
        return;
    }

    if (!bintex.value.id) {
        uploadError.value = "Bintex data not loaded.";
        return;
    }

    uploadLoading.value = true;

    try {
        const formData = new FormData();
        formData.append("bintex_id", bintex.value.id);
        formData.append("title", uploadForm.value.title);
        formData.append("file", uploadForm.value.file);

        const res = await api.post("/admin/documents", formData);

        // asumsikan respon: { message: "...", document: { ... } }
        const newDoc = res.data.document;

        if (!bintex.value.documents) {
            bintex.value.documents = [];
        }
        bintex.value.documents.push(newDoc);

        uploadSuccess.value = "Document uploaded. Conversion has been queued.";
        uploadForm.value.title = "";
        uploadForm.value.file = null;
        // reset input file (opsional)
        const fileInput = document.getElementById("file");
        if (fileInput) fileInput.value = "";
    } catch (e) {
        console.error(e);
        if (e.response?.status === 422) {
            // error validasi backend
            const errors = e.response.data.errors || {};
            uploadError.value =
                errors.file?.[0] ||
                errors.title?.[0] ||
                errors.bintex_id?.[0] ||
                "Validation error.";
        } else {
            uploadError.value = "Failed to upload document.";
        }
    } finally {
        uploadLoading.value = false;
    }
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

.upload-section {
    margin-top: 32px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
}

.upload-form {
    margin-top: 12px;
    max-width: 400px;
}

.form-row {
    margin-bottom: 10px;
    display: flex;
    flex-direction: column;
}

label {
    font-size: 14px;
    margin-bottom: 4px;
}

input[type="text"],
input[type="file"] {
    padding: 6px 8px;
    border-radius: 4px;
    border: 1px solid #d1d5db;
    font-size: 14px;
}

button[type="submit"] {
    margin-top: 4px;
    padding: 8px 12px;
    border-radius: 4px;
    border: none;
    background: #2563eb;
    color: white;
    font-size: 14px;
    cursor: pointer;
}

button[disabled] {
    opacity: 0.7;
    cursor: default;
}

.error {
    color: #b91c1c;
    margin-top: 8px;
}

.success {
    color: #15803d;
    margin-top: 8px;
}

a {
    text-decoration: none;
    color: #ffffff;
}

.readonly-info {
    margin-top: 24px;
    font-size: 14px;
    color: #6b7280;
    font-style: italic;
}
</style>
