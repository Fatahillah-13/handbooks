<template>
    <div class="login-page">
        <!-- background image layer -->
        <div class="login-bg"></div>

        <!-- overlay gelap tipis biar teks lebih kebaca -->
        <div class="login-overlay"></div>

        <!-- card login -->
        <div class="login-card">
            <div class="login-header">
                <h1>Handbooks</h1>
                <p>Silakan login untuk mengakses dokumen internal.</p>
            </div>

            <form @submit.prevent="submit" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        id="username"
                        v-model="form.username"
                        type="text"
                        autocomplete="username"
                        placeholder="Masukkan username"
                    />
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                    />
                </div>

                <button class="btn-login" type="submit" :disabled="loading">
                    <span v-if="loading">Memproses...</span>
                    <span v-else>Masuk</span>
                </button>

                <p v-if="error" class="error-message">
                    {{ error }}
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
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

    if (!form.value.username || !form.value.password) {
        error.value = "Username dan password wajib diisi.";
        return;
    }

    loading.value = true;

    try {
        await axios.get("/sanctum/csrf-cookie");
        const res = await axios.post("/login", form.value);

        setUser(res.data.user);

        // setelah login sukses arahkan ke home
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
    position: fixed;
    inset: 0;
    height: 100vh;
    width: 100%;
    overflow: hidden;
    display: flex;

    /* ⬇️ awalnya center-center, sekarang kanan-atas */
    justify-content: flex-end; /* horizontal: ke kanan */
    align-items: flex-start; /* vertikal: ke atas */

    padding: 32px 64px; /* jarak dari pinggir layar */
    box-sizing: border-box;
}

/* layer background image */
.login-bg {
    position: absolute;
    inset: 0;
    background-image: url("/assets/login-bg-4.jpg");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    z-index: 0;
}

.login-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.1); /* atau gradient */
    z-index: 1;
}

/* card */
.login-card {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 340px;
    padding: 16px 18px;
    box-sizing: border-box;

    background: rgba(248, 250, 252, 0.96);
    border: 4px solid #111827; /* garis hitam tebal */
    border-radius: 0; /* sudut kotak */
    margin-top: 6px;

    /* shadow kotak offset → kesan pixel */
    box-shadow: 6px 6px 0 #111827;

    image-rendering: pixelated;
}

.login-header {
    text-align: center;
    margin-bottom: 16px;
}

.login-header h1 {
    margin: 0;
    font-size: 20px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-family: "Press Start 2P", system-ui, sans-serif;
}

.login-header p {
    margin: 6px 0 0;
    font-size: 12px;
    color: #4b5563;
}

.login-form {
    margin-top: 10px;
}

.form-group {
    margin-bottom: 12px;
    text-align: left;
}

label {
    display: block;
    margin-bottom: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: #111827;
}

input {
    width: 100%;
    padding: 6px 7px;
    box-sizing: border-box;
    font-size: 14px;

    background: #ffffff;
    border: 3px solid #111827;
    border-radius: 0;
    box-shadow: 3px 3px 0 #9ca3af;
}

input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 3px 3px 0 #1d4ed8;
}

.btn-login {
    margin-top: 4px;
    width: 100%;
    padding: 7px 8px;
    border-radius: 0;
    border: 3px solid #111827;
    box-sizing: border-box;

    background: linear-gradient(90deg, #2563eb, #4f46e5);
    color: #f9fafb;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-family: "Press Start 2P", system-ui, sans-serif;

    cursor: pointer;
    box-shadow: 4px 4px 0 #111827;
    transition: transform 0.05s ease, box-shadow 0.05s ease, filter 0.1s;
}

.btn-login:hover:enabled {
    transform: translate(-1px, -1px);
    box-shadow: 5px 5px 0 #111827;
    filter: brightness(1.05);
}

.btn-login:active:enabled {
    transform: translate(1px, 1px);
    box-shadow: 3px 3px 0 #111827;
}

.btn-login:disabled {
    opacity: 0.7;
    cursor: default;
    box-shadow: 0 0 0 #111827;
}

.error-message {
    margin-top: 10px;
    color: #b91c1c;
    font-size: 12px;
    text-align: center;
}

@media (max-width: 768px) {
    .login-page {
        justify-content: center;
        align-items: center;
        padding: 16px;
    }

    .login-card {
        margin-top: 0;
    }
}
</style>
