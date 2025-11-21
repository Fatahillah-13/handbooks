<template>
    <div class="audit-logs">
        <Breadcrumb :items="breadcrumbItems" />

        <div class="header-row">
            <div>
                <h1>📝 Audit Logs</h1>
                <p>Riwayat aktivitas penting di sistem ini.</p>
                <p v-if="user" class="user-info">
                    Logged in as
                    <strong>{{ user.name }}</strong>
                    ({{ user.username }})
                </p>
            </div>

            <button class="logout-btn" @click="logout">Logout</button>
        </div>

        <p v-if="loading">Loading logs...</p>
        <p v-if="error" class="error">{{ error }}</p>

        <table v-if="!loading && logs.length" class="table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>Meta</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="log in logs" :key="log.id">
                    <td>{{ formatDate(log.created_at) }}</td>
                    <td>
                        <span v-if="log.user">
                            {{ log.user.username }} (id: {{ log.user.id }})
                        </span>
                        <span v-else> - (guest / unknown) </span>
                    </td>
                    <td>{{ log.action }}</td>
                    <td>
                        <span v-if="log.auditable_type">
                            {{ shortType(log.auditable_type) }}
                            #{{ log.auditable_id }}
                        </span>
                        <span v-else>-</span>
                    </td>
                    <td>
                        <pre v-if="log.meta" class="meta"
                            >{{ JSON.stringify(log.meta, null, 2) }}
                        </pre>
                        <span v-else>-</span>
                    </td>
                    <td>{{ log.ip_address }}</td>
                </tr>
            </tbody>
        </table>

        <p v-if="!loading && !logs.length">No audit logs yet.</p>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import api from "../api/api";
import Breadcrumb from "../components/Breadcrumb.vue";
import useAuth from "../composables/useAuth";

const { user, isAdmin, logout, requireAuth } = useAuth();

const logs = ref([]);
const loading = ref(true);
const error = ref(null);

const breadcrumbItems = computed(() => [
    { label: "Home", to: { name: "home" } },
    { label: "Audit Logs" },
]);

onMounted(async () => {
    if (!requireAuth()) return;
    if (!isAdmin.value) {
        error.value = "You do not have permission to view audit logs.";
        loading.value = false;
        return;
    }

    try {
        const res = await api.get("/admin/audit-logs");
        logs.value = Array.isArray(res.data) ? res.data : res.data.data ?? [];
    } catch (e) {
        console.error(e);
        error.value = "Failed to load audit logs.";
    } finally {
        loading.value = false;
    }
});

const formatDate = (iso) => {
    if (!iso) return "-";
    const d = new Date(iso);
    return d.toLocaleString();
};

const shortType = (full) => {
    if (!full) return "-";
    return full.split("\\").pop();
};
</script>

<style scoped>
.audit-logs {
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

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
    font-size: 13px;
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

.error {
    color: #b91c1c;
    margin-top: 6px;
}

.meta {
    font-size: 11px;
    background: #f3f4f6;
    padding: 4px;
    border-radius: 4px;
    max-width: 260px;
    white-space: pre-wrap;
}
</style>
