<template>
    <div class="bintex-manage">
        <Breadcrumb :items="breadcrumbItems" />

        <div class="header-row">
            <div>
                <h1>📚 Bintex Management</h1>
                <p>Kelola kategori (bintex) yang berisi kumpulan dokumen.</p>
                <p v-if="user" class="user-info">
                    Logged in as
                    <strong>{{ user.name }}</strong>
                    ({{ user.username }}) – role_id: {{ user.role_id }}
                </p>
            </div>

            <button class="logout-btn" @click="logout">Logout</button>
        </div>

        <p v-if="!canEditContent" class="error">
            You do not have permission to manage bintexes. (Admin/Editor only)
        </p>

        <div v-if="canEditContent">
            <!-- FORM CREATE BINTEK -->
            <section class="card">
                <h2>➕ Create New Bintex</h2>

                <form @submit.prevent="submitCreate" class="form">
                    <div class="form-row">
                        <label for="storage_id">Storage</label>
                        <select id="storage_id" v-model="createForm.storage_id">
                            <option value="">-- Select storage --</option>
                            <option
                                v-for="s in storages"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.name }}
                            </option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="name">Name</label>
                        <input
                            id="name"
                            v-model="createForm.name"
                            type="text"
                            placeholder="e.g. Peraturan Karyawan"
                        />
                    </div>

                    <div class="form-row">
                        <label for="description">Description</label>
                        <textarea
                            id="description"
                            v-model="createForm.description"
                            rows="2"
                            placeholder="Short description about this bintex"
                        ></textarea>
                    </div>

                    <button type="submit" :disabled="createLoading">
                        <span v-if="createLoading">Saving...</span>
                        <span v-else>Create</span>
                    </button>

                    <p v-if="createError" class="error">{{ createError }}</p>
                    <p v-if="createSuccess" class="success">
                        {{ createSuccess }}
                    </p>
                </form>
            </section>

            <!-- LIST BINTEKS -->
            <section class="card list-card">
                <div class="list-header">
                    <h2>📋 All Bintexes</h2>
                    <span v-if="loading">Loading...</span>
                </div>

                <p v-if="error" class="error">{{ error }}</p>

                <table v-if="!loading && bintexes.length" class="table">
                    <thead>
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 20%">Name</th>
                            <th style="width: 25%">Description</th>
                            <th style="width: 15%">Storage</th>
                            <th style="width: 10%">Slug</th>
                            <th style="width: 10%">Documents</th>
                            <th style="width: 15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(b, index) in bintexes" :key="b.id">
                            <td>{{ index + 1 }}</td>

                            <!-- Name -->
                            <td>
                                <div v-if="editId === b.id">
                                    <input
                                        v-model="editForm.name"
                                        type="text"
                                    />
                                </div>
                                <div v-else>
                                    {{ b.name }}
                                </div>
                            </td>

                            <!-- Description -->
                            <td>
                                <div v-if="editId === b.id">
                                    <textarea
                                        v-model="editForm.description"
                                        rows="2"
                                    ></textarea>
                                </div>
                                <div v-else>
                                    {{ b.description }}
                                </div>
                            </td>

                            <!-- Storage -->
                            <td>
                                <div v-if="editId === b.id">
                                    <select v-model="editForm.storage_id">
                                        <option
                                            v-for="s in storages"
                                            :key="s.id"
                                            :value="s.id"
                                        >
                                            {{ s.name }}
                                        </option>
                                    </select>
                                </div>
                                <div v-else>
                                    {{ b.storage?.name || "-" }}
                                </div>
                            </td>

                            <!-- Slug -->
                            <td>{{ b.slug }}</td>

                            <!-- Documents count -->
                            <td>
                                {{
                                    b.documents_count ??
                                    b.documents?.length ??
                                    0
                                }}
                            </td>

                            <!-- Actions -->
                            <td>
                                <div
                                    v-if="editId === b.id"
                                    class="action-buttons"
                                >
                                    <button
                                        @click="submitUpdate(b.id)"
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
                                    <button @click="startEdit(b)">Edit</button>
                                    <button @click="confirmDelete(b)">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-if="!loading && !bintexes.length">
                    No bintexes yet. Create one above.
                </p>
            </section>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import api from "../api/api";
import Breadcrumb from "../components/Breadcrumb.vue";
import useAuth from "../composables/useAuth";

const { user, canEditContent, logout, requireAuth } = useAuth();

const bintexes = ref([]);
const storages = ref([]);

const loading = ref(true);
const error = ref(null);

// create form
const createForm = ref({
    storage_id: "",
    name: "",
    description: "",
});
const createLoading = ref(false);
const createError = ref(null);
const createSuccess = ref(null);

// edit state
const editId = ref(null);
const editForm = ref({
    storage_id: "",
    name: "",
    description: "",
});
const editLoading = ref(false);

// breadcrumb
const breadcrumbItems = computed(() => [
    { label: "Home", to: { name: "home" } },
    { label: "Bintex Management" },
]);

onMounted(async () => {
    if (!requireAuth()) return;

    await Promise.all([fetchStorages(), fetchBintexes()]);
});

const fetchStorages = async () => {
    try {
        const res = await api.get("/admin/storages");
        storages.value = res.data;
    } catch (e) {
        console.error("Failed to load storages", e);
    }
};

const fetchBintexes = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.get("/admin/bintexes");
        bintexes.value = res.data;
    } catch (e) {
        console.error(e);
        error.value = "Failed to load bintexes.";
    } finally {
        loading.value = false;
    }
};

const submitCreate = async () => {
    createError.value = null;
    createSuccess.value = null;

    if (!createForm.value.storage_id) {
        createError.value = "Storage is required.";
        return;
    }
    if (!createForm.value.name.trim()) {
        createError.value = "Name is required.";
        return;
    }

    createLoading.value = true;
    try {
        const res = await api.post("/admin/bintexes", {
            storage_id: createForm.value.storage_id,
            name: createForm.value.name,
            description: createForm.value.description,
        });

        // Asumsi backend return satu object bintex
        bintexes.value.push(res.data);

        createSuccess.value = "Bintex created.";
        createForm.value = {
            storage_id: "",
            name: "",
            description: "",
        };
    } catch (e) {
        console.error(e);
        if (e.response?.status === 422) {
            const errors = e.response.data.errors || {};
            createError.value =
                errors.storage_id?.[0] ||
                errors.name?.[0] ||
                errors.description?.[0] ||
                "Validation error.";
        } else {
            createError.value = "Failed to create bintex.";
        }
    } finally {
        createLoading.value = false;
    }
};

const startEdit = (b) => {
    editId.value = b.id;
    editForm.value = {
        storage_id: b.storage_id || b.storage?.id || "",
        name: b.name,
        description: b.description,
    };
};

const cancelEdit = () => {
    editId.value = null;
    editForm.value = {
        storage_id: "",
        name: "",
        description: "",
    };
};

const submitUpdate = async (id) => {
    if (!editForm.value.storage_id) {
        alert("Storage is required.");
        return;
    }
    if (!editForm.value.name.trim()) {
        alert("Name is required.");
        return;
    }

    editLoading.value = true;
    try {
        const res = await api.put(`/admin/bintexes/${id}`, {
            storage_id: editForm.value.storage_id,
            name: editForm.value.name,
            description: editForm.value.description,
        });

        const index = bintexes.value.findIndex((b) => b.id === id);
        if (index !== -1) {
            bintexes.value[index] = res.data;
        }

        cancelEdit();
    } catch (e) {
        console.error(e);
        alert("Failed to update bintex.");
    } finally {
        editLoading.value = false;
    }
};

const confirmDelete = async (b) => {
    const yes = confirm(`Are you sure you want to delete bintex "${b.name}"?`);
    if (!yes) return;

    try {
        await api.delete(`/admin/bintexes/${b.id}`);
        bintexes.value = bintexes.value.filter((x) => x.id !== b.id);
    } catch (e) {
        console.error(e);
        alert("Failed to delete bintex.");
    }
};
</script>

<style scoped>
.bintex-manage {
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
textarea,
select {
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
