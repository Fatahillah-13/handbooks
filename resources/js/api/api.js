// resources/js/api/api.js
import axios from "axios";

const api = axios.create({
    baseURL: "/api",
});

// Interceptor: sebelum setiap request dikirim
api.interceptors.request.use((config) => {
    const token = localStorage.getItem("token");
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    } else {
        delete config.headers.Authorization;
    }

    // Biar sama seperti axios bawaan Laravel (optional)
    config.headers["X-Requested-With"] = "XMLHttpRequest";

    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            localStorage.removeItem("token");
            localStorage.removeItem("user");

            // Jangan bikin infinite loop kalau sudah di /login
            if (window.location.pathname !== "/login") {
                window.location.href = "/login";
            }
        }

        return Promise.reject(error);
    }
);

export default api;
