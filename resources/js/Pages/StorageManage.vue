<template>
    <div class="storage-manage">
        <Breadcrumb :items="breadcrumbItems" />

        <div class="header-row">
            <div>
                <h1>📁 Storage Management</h1>
                <p>Kelola daftar storage yang berisi bintex & dokumen.</p>
                <p v-if="user" class="user-info">
                    Logged in as
                    <strong>{{ user.name }}</strong>
                    ({{ user.username }}) – role_id: {{ user.role_id }}
                </p>
            </div>

            <button class="logout-btn" @click="logout">Logout</button>
        </div>

        <p v-if="!canEditContent" class="error">
            You do not have permission to manage storages. (Admin/Editor only)
        </p>

        <div v-if="canEditContent">
            <!-- FORM BUAT STORAGE BARU -->
            <section class="card">
                <h2>➕ Create New Storage</h2>
                <form @submit.prevent="submitCreate" class="form">
                    <div class="form-row">
                        <label for="name">Name</label>
                        <input
                            id="name"
                            v-model="createForm.name"
                            type="text"
                            placeholder="e.g. HR Documents"
                        />
                    </div>

                    <div class="form-row">
                        <label for="description">Description</label>
                        <textarea
                            id="description"
                            v-model="createForm.description"
                            rows="2"
                            placeholder="Short description about this storage"
                        ></textarea>
                    </div>

                    <button type="submit" :disabled="createLoading">
                        <span v-if="createLoading">Saving...</span>
                        <span v-else>Create</span>
                    </button>

                    <p v-if="createError" class="error">
                        {{ createError }}
                    </p>
                    <p v-if="createSuccess" class="success">
                        {{ createSuccess }}
                    </p>
                </form>
            </section>

            <!-- LIST STORAGE -->
            <section class="card list-card">
                <div class="list-header">
                    <h2>📋 All Storages</h2>
                    <span v-if="loading">Loading...</span>
                </div>

                <p v-if="error" class="error">{{ error }}</p>

                <table v-if="!loading && storages.length" class="table">
                    <thead>
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 25%">Name</th>
                            <th style="width: 30%">Description</th>
                            <th style="width: 15%">Slug</th>
                            <th style="width: 10%">Bintexes</th>
                            <th style="width: 15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(s, index) in storages" :key="s.id">
                            <td>{{ index + 1 }}</td>
                            <td>
                                <div v-if="editId === s.id">
                                    <input
                                        v-model="editForm.name"
                                        type="text"
                                    />
                                </div>
                                <div v-else>
                                    {{ s.name }}
                                </div>
                            </td>
                            <td>
                                <div v-if="editId === s.id">
                                    <textarea
                                        v-model="editForm.description"
                                        rows="2"
                                    ></textarea>
                                </div>
                                <div v-else>
                                    {{ s.description }}
                                </div>
                            </td>
                            <td>{{ s.slug }}</td>
                            <td>
                                {{
                                    s.bintexes_count ?? s.bintexes?.length ?? 0
                                }}
                            </td>
                            <td>
                                <div
                                    v-if="editId === s.id"
                                    class="action-buttons"
                                >
                                    <button
                                        @click="submitUpdate(s.id)"
                                        :disabled="editLoading"
                                    >
                                        <span v-if="editLoading"
                                            >Saving...</span
                                        >
                                        <span v-else>Save</span>
                                    </button>
                                    <button @click="cancelEdit">Cancel</button>
                                </div>
                                <div v-else class="action-buttons">
                                    <button @click="startEdit(s)">Edit</button>
                                    <button @click="confirmDelete(s)">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-if="!loading && !storages.length">
                    No storages yet. Create one above.
                </p>
            </section>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import api from "../api/api";
import Breadcrumb from "../components/Breadcrumb.vue";
import useAuth from "../composables/useAuth";

const router = useRouter();
const { user, canEditContent, logout, requireAuth } = useAuth();

const storages = ref([]);
const loading = ref(true);
const error = ref(null);

// create form state
const createForm = ref({
    name: "",
    description: "",
});
const createLoading = ref(false);
const createError = ref(null);
const createSuccess = ref(null);

// edit state
const editId = ref(null);
const editForm = ref({
    name: "",
    description: "",
});
const editLoading = ref(false);

// breadcrumb
const breadcrumbItems = computed(() => [
    { label: "Home", to: { name: "home" } },
    { label: "Storage Management" },
]);

onMounted(async () => {
    if (!requireAuth()) return;

    // kalau user viewer (tidak boleh edit), kita tetap load list, tapi read-only
    await fetchStorages();
});

const fetchStorages = async () => {
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
};

const submitCreate = async () => {
    createError.value = null;
    createSuccess.value = null;

    if (!createForm.value.name.trim()) {
        createError.value = "Name is required.";
        return;
    }

    createLoading.value = true;
    try {
        const res = await api.post("/admin/storages", {
            name: createForm.value.name,
            description: createForm.value.description,
        });

        // asumsinya backend mengembalikan storage yang baru
        storages.value.push(res.data);

        createSuccess.value = "Storage created.";
        createForm.value.name = "";
        createForm.value.description = "";
    } catch (e) {
        console.error(e);
        if (e.response?.status === 422) {
            const errors = e.response.data.errors || {};
            createError.value =
                errors.name?.[0] ||
                errors.description?.[0] ||
                "Validation error.";
        } else {
            createError.value = "Failed to create storage.";
        }
    } finally {
        createLoading.value = false;
    }
};

const startEdit = (storage) => {
    editId.value = storage.id;
    editForm.value = {
        name: storage.name,
        description: storage.description,
    };
};

const cancelEdit = () => {
    editId.value = null;
    editForm.value = {
        name: "",
        description: "",
    };
};

const submitUpdate = async (id) => {
    if (!editForm.value.name.trim()) {
        alert("Name is required.");
        return;
    }

    editLoading.value = true;
    try {
        const res = await api.put(`/admin/storages/${id}`, {
            name: editForm.value.name,
            description: editForm.value.description,
        });

        const index = storages.value.findIndex((s) => s.id === id);
        if (index !== -1) {
            storages.value[index] = res.data;
        }

        cancelEdit();
    } catch (e) {
        console.error(e);
        alert("Failed to update storage.");
    } finally {
        editLoading.value = false;
    }
};

const confirmDelete = async (storage) => {
    const yes = confirm(
        `Are you sure you want to delete storage "${storage.name}"?`
    );
    if (!yes) return;

    try {
        await api.delete(`/admin/storages/${storage.id}`);
        storages.value = storages.value.filter((s) => s.id !== storage.id);
    } catch (e) {
        console.error(e);
        alert("Failed to delete storage.");
    }
};
</script>

<style scoped>
.storage-manage {
    padding: 20px;
}

.header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
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
}

.logout-btn:hover {
    opacity: 0.9;
}

.card {
    background: #ffffff;
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.list-card {
    margin-top: 12px;
}

.form {
    max-width: 500px;
    margin-top: 8px;
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
textarea {
    padding: 6px 8px;
    border-radius: 4px;
    border: 1px solid #d1d5db;
    font-size: 14px;
}

button[type="submit"],
.action-buttons button {
    padding: 6px 10px;
    border-radius: 4px;
    border: none;
    background: #2563eb;
    color: white;
    font-size: 13px;
    cursor: pointer;
    margin-right: 6px;
}

button[disabled] {
    opacity: 0.7;
    cursor: default;
}

.error {
    color: #b91c1c;
    margin-top: 6px;
}

.success {
    color: #15803d;
    margin-top: 6px;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
    font-size: 14px;
}

.table th,
.table td {
    border: 1px solid #e5e7eb;
    padding: 6px 8px;
    vertical-align: top;
}

.table th {
    background: #f9fafb;
    text-align: left;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
}
</style>
