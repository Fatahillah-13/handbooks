// resources/js/composables/useAuth.js
import { ref, computed } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const storedUser = localStorage.getItem("user");

// state global (dipakai bersama semua komponen)
const user = ref(storedUser ? JSON.parse(storedUser) : null);

export default function useAuth() {
    const router = useRouter();

    const isLoggedIn = computed(() => !!user.value);

    // mapping role_id:
    // 1 = admin, 2 = editor, 3 = viewer
    const isAdmin = computed(() => user.value?.role_id === 1);
    const isEditor = computed(() => user.value?.role_id === 2);
    const isViewer = computed(() => user.value?.role_id === 3);

    const canEditContent = computed(() => [1, 2].includes(user.value?.role_id));

    const setUser = (data) => {
        user.value = data;
        if (data) {
            localStorage.setItem("user", JSON.stringify(data));
        } else {
            localStorage.removeItem("user");
        }
    };

    const clearUser = () => {
        user.value = null;
        localStorage.removeItem("user");
    };

    const logout = async () => {
        try {
            await axios.post("/logout");
        } catch (e) {
            console.error("Logout error (ignored)", e);
        }
        clearUser();
        router.push({ name: "login" });
    };

    const requireAuth = () => {
        if (!isLoggedIn.value) {
            router.push({ name: "login" });
            return false;
        }
        return true;
    };

    return {
        user,
        isLoggedIn,
        isAdmin,
        isEditor,
        isViewer,
        canEditContent,
        setUser,
        clearUser,
        logout,
        requireAuth,
    };
}
