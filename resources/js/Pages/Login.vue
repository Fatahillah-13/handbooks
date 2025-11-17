<template>
    <div class="login-page">
        <div class="login-card">
            <h1>Login</h1>

            <form @submit.prevent="submit">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        id="username"
                        v-model="form.username"
                        type="text"
                        autocomplete="username"
                    />
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                    />
                </div>

                <button type="submit" :disabled="loading">
                    <span v-if="loading">Masuk...</span>
                    <span v-else>Masuk</span>
                </button>

                <p v-if="error" class="error">
                    {{ error }}
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios"; // pakai axios langsung untuk /login & /sanctum
import { useRouter } from "vue-router";
import useAuth from "../composables/useAuth";

const router = useRouter();
const { setUser } = useAuth();

const form = ref({
    username: "",
    password: "",
});
const loading = ref(false);
const error = ref(null);

const submit = async () => {
    error.value = null;
    loading.value = true;

    try {
        // 1) ambil CSRF cookie untuk Sanctum
        await axios.get("/sanctum/csrf-cookie");

        // 2) kirim login (web route, bukan /api)
        const res = await axios.post("/login", form.value);

        // pakai helper global
        setUser(res.data.user);

        router.push({ name: "home" });
    } catch (e) {
        console.error(e);
        const msg =
            e.response?.data?.message ||
            e.response?.data?.errors?.username?.[0] ||
            "Login gagal. Periksa username / password.";
        error.value = msg;
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.login-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    padding: 16px;
}

.login-card {
    background: white;
    padding: 24px 28px;
    border-radius: 8px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    width: 100%;
    max-width: 360px;
}

h1 {
    margin-bottom: 16px;
    text-align: center;
}

.form-group {
    margin-bottom: 12px;
    text-align: left;
}

label {
    display: block;
    margin-bottom: 4px;
    font-size: 14px;
}

input {
    width: 100%;
    padding: 8px 10px;
    border-radius: 4px;
    border: 1px solid #d1d5db;
    font-size: 14px;
}

button {
    margin-top: 8px;
    width: 100%;
    padding: 8px 10px;
    border-radius: 4px;
    border: none;
    background: #2563eb;
    color: white;
    font-size: 14px;
    cursor: pointer;
}

button:disabled {
    opacity: 0.7;
    cursor: default;
}

.error {
    margin-top: 10px;
    color: #b91c1c;
    font-size: 13px;
}
</style>
