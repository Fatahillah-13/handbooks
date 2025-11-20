<template>
    <div class="users-manage">
        <Breadcrumb :items="breadcrumbItems" />

        <div class="header-row">
            <div>
                <h1>👑 User Management</h1>
                <p>Kelola akun pengguna dan perannya.</p>
                <p v-if="user" class="user-info">
                    Logged in as
                    <strong>{{ user.name }}</strong>
                    ({{ user.username }}) – role_id: {{ user.role_id }}
                </p>
            </div>

            <button class="logout-btn" @click="logout">Logout</button>
        </div>

        <!-- kalau bukan admin, tolak -->
        <p v-if="!isAdmin" class="error">
            You do not have permission to manage users. (Admin only)
        </p>

        <div v-if="isAdmin">
            <!-- FORM CREATE USER BARU -->
            <section class="card">
                <h2>➕ Create New User</h2>

                <form @submit.prevent="submitCreate" class="form">
                    <div class="form-row">
                        <label for="name">Name</label>
                        <input
                            id="name"
                            v-model="createForm.name"
                            type="text"
                            placeholder="e.g. John Doe"
                        />
                    </div>

                    <div class="form-row">
                        <label for="username">Username</label>
                        <input
                            id="username"
                            v-model="createForm.username"
                            type="text"
                            placeholder="e.g. johndoe"
                        />
                    </div>

                    <div class="form-row">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            v-model="createForm.email"
                            type="email"
                            placeholder="e.g. john@example.com"
                        />
                    </div>

                    <div class="form-row">
                        <label for="role_id">Role</label>
                        <select id="role_id" v-model="createForm.role_id">
                            <option value="">-- Select role --</option>
                            <option :value="1">Admin</option>
                            <option :value="2">Editor</option>
                            <option :value="3">Viewer</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="password">Password</label>
                        <input
                            id="password"
                            v-model="createForm.password"
                            type="password"
                            placeholder="Min. 8 characters"
                        />
                    </div>

                    <div class="form-row">
                        <label for="password_confirmation"
                            >Confirm Password</label
                        >
                        <input
                            id="password_confirmation"
                            v-model="createForm.password_confirmation"
                            type="password"
                        />
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

            <!-- LIST USERS -->
            <section class="card list-card">
                <div class="list-header">
                    <h2>📋 All Users</h2>
                    <span v-if="loading">Loading...</span>
                </div>

                <p v-if="error" class="error">{{ error }}</p>

                <table v-if="!loading && users.length" class="table">
                    <thead>
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 20%">Name</th>
                            <th style="width: 15%">Username</th>
                            <th style="width: 20%">Email</th>
                            <th style="width: 10%">Role</th>
                            <th style="width: 15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(u, index) in users" :key="u.id">
                            <td>{{ index + 1 }}</td>

                            <!-- Name -->
                            <td>
                                <div v-if="editId === u.id">
                                    <input
                                        v-model="editForm.name"
                                        type="text"
                                    />
                                </div>
                                <div v-else>
                                    {{ u.name }}
                                </div>
                            </td>

                            <!-- Username -->
                            <td>
                                <div v-if="editId === u.id">
                                    <input
                                        v-model="editForm.username"
                                        type="text"
                                    />
                                </div>
                                <div v-else>
                                    {{ u.username }}
                                </div>
                            </td>

                            <!-- Email -->
                            <td>
                                <div v-if="editId === u.id">
                                    <input
                                        v-model="editForm.email"
                                        type="email"
                                    />
                                </div>
                                <div v-else>
                                    {{ u.email }}
                                </div>
                            </td>

                            <!-- Role -->
                            <td>
                                <div v-if="editId === u.id">
                                    <select v-model="editForm.role_id">
                                        <option :value="1">Admin</option>
                                        <option :value="2">Editor</option>
                                        <option :value="3">Viewer</option>
                                    </select>
                                </div>
                                <div v-else>
                                    <span v-if="u.role_id === 1">Admin</span>
                                    <span v-else-if="u.role_id === 2"
                                        >Editor</span
                                    >
                                    <span v-else-if="u.role_id === 3"
                                        >Viewer</span
                                    >
                                    <span v-else>-</span>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td>
                                <div
                                    v-if="editId === u.id"
                                    class="action-buttons"
                                >
                                    <button
                                        @click="submitUpdate(u.id)"
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
                                    <button @click="startEdit(u)">Edit</button>
                                    <button
                                        @click="confirmResetPassword(u)"
                                        class="secondary"
                                    >
                                        Reset Password
                                    </button>
                                    <button
                                        @click="confirmDelete(u)"
                                        class="danger"
                                        :disabled="u.id === user.id"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p v-if="!loading && !users.length">No users yet.</p>
            </section>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import api from "../api/api";
import Breadcrumb from "../components/Breadcrumb.vue";
import useAuth from "../composables/useAuth";

const { user, isAdmin, logout, requireAuth } = useAuth();

const users = ref([]);
const loading = ref(true);
const error = ref(null);

// create form
const createForm = ref({
    name: "",
    username: "",
    email: "",
    role_id: "",
    password: "",
    password_confirmation: "",
});
const createLoading = ref(false);
const createError = ref(null);
const createSuccess = ref(null);

// edit state
const editId = ref(null);
const editForm = ref({
    name: "",
    username: "",
    email: "",
    role_id: "",
});
const editLoading = ref(false);

// breadcrumb
const breadcrumbItems = computed(() => [
    { label: "Home", to: { name: "home" } },
    { label: "User Management" },
]);

onMounted(async () => {
    if (!requireAuth()) return;
    await fetchUsers();
});

const fetchUsers = async () => {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.get("/admin/users");
        if (Array.isArray(res.data)) {
            users.value = res.data;
        } else if (Array.isArray(res.data.data)) {
            users.value = res.data.data;
        } else {
            users.value = [];
        }
    } catch (e) {
        console.error(e);
        error.value = "Failed to load users.";
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
    if (!createForm.value.username.trim()) {
        createError.value = "Username is required.";
        return;
    }
    if (!createForm.value.email.trim()) {
        createError.value = "Email is required.";
        return;
    }
    if (!createForm.value.role_id) {
        createError.value = "Role is required.";
        return;
    }
    if (!createForm.value.password) {
        createError.value = "Password is required.";
        return;
    }

    createLoading.value = true;
    try {
        const res = await api.post("/admin/users", createForm.value);

        // kalau backend pakai resource, kadang di res.data.data
        const newUser = res.data && res.data.data ? res.data.data : res.data;

        if (!newUser || !newUser.id) {
            createError.value = "Server did not return created user.";
            return;
        }

        users.value.push(newUser);

        createSuccess.value = "User created.";
        createForm.value = {
            name: "",
            username: "",
            email: "",
            role_id: "",
            password: "",
            password_confirmation: "",
        };
    } catch (e) {
        console.error(e);
        if (e.response?.status === 422) {
            const errors = e.response.data.errors || {};
            createError.value =
                errors.name?.[0] ||
                errors.username?.[0] ||
                errors.email?.[0] ||
                errors.password?.[0] ||
                errors.role_id?.[0] ||
                "Validation error.";
        } else {
            createError.value = "Failed to create user.";
        }
    } finally {
        createLoading.value = false;
    }
};

const startEdit = (u) => {
    editId.value = u.id;
    editForm.value = {
        name: u.name,
        username: u.username,
        email: u.email,
        role_id: u.role_id,
    };
};

const cancelEdit = () => {
    editId.value = null;
    editForm.value = {
        name: "",
        username: "",
        email: "",
        role_id: "",
    };
};

const submitUpdate = async (id) => {
    if (!editForm.value.name.trim() || !editForm.value.username.trim()) {
        alert("Name and username are required.");
        return;
    }

    editLoading.value = true;
    try {
        const res = await api.put(`/admin/users/${id}`, editForm.value);

        const index = users.value.findIndex((u) => u.id === id);
        if (index !== -1) {
            users.value[index] = res.data;
        }

        cancelEdit();
    } catch (e) {
        console.error(e);
        alert("Failed to update user.");
    } finally {
        editLoading.value = false;
    }
};

const confirmDelete = async (u) => {
    if (u.id === user.value.id) {
        alert("You cannot delete your own account.");
        return;
    }

    const yes = confirm(
        `Are you sure you want to delete user "${u.username}"?`
    );
    if (!yes) return;

    try {
        await api.delete(`/admin/users/${u.id}`);
        users.value = users.value.filter((x) => x.id !== u.id);
    } catch (e) {
        console.error(e);
        alert("Failed to delete user.");
    }
};

// Reset password simpel: kirim password default (misal "password123")
const confirmResetPassword = async (u) => {
    const yes = confirm(
        `Reset password for "${u.username}" to a default value?`
    );
    if (!yes) return;

    try {
        // Asumsi kamu nanti bikin endpoint khusus:
        // POST /api/admin/users/{id}/reset-password
        // atau pakai PUT /admin/users/{id} dengan field khusus.
        // Untuk sekarang, aku tulis contoh generic:
        await api.put(`/admin/users/${u.id}`, {
            // misal: hanya password (backend perlu cek & hash)
            password: "password123",
        });
        alert('Password has been reset to "password123".');
    } catch (e) {
        console.error(e);
        alert("Failed to reset password.");
    }
};
</script>

<style scoped>
.users-manage {
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
input[type="email"],
input[type="password"],
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

.action-buttons .secondary {
    background: #6b7280;
}

.action-buttons .danger {
    background: #ef4444;
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
