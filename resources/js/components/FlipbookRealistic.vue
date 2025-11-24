<template>
    <div class="flipbook-wrapper">
        <!-- Kontrol sederhana -->
        <div class="toolbar">
            <PixelButton @click="prevPage">⬅ Prev</PixelButton>

            <span class="page-info" v-if="pageCount > 0">
                Page {{ currentPage + 1 }} / {{ pageCount }}
            </span>

            <PixelButton @click="nextPage">Next ➡</PixelButton>
        </div>

        <!-- Container untuk StPageFlip -->
        <div ref="bookRef" class="book-container"></div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import { PageFlip } from "page-flip"; // 👈 dari npm
import PixelButton from "./PixelButton.vue";

const props = defineProps({
    pages: { type: Array, required: true }, // array URL gambar halaman
});

const bookRef = ref(null);
const flip = ref(null);
const currentPage = ref(0);
const pageCount = ref(0);

const initFlip = () => {
    if (!bookRef.value || !props.pages.length) return;

    // Kalau sebelumnya sudah ada instance, destroy dulu
    if (flip.value) {
        flip.value.destroy();
        flip.value = null;
    }

    // Buat instance baru
    flip.value = new PageFlip(bookRef.value, {
        width: 800, // base width halaman (px) → sesuaikan
        height: 600, // base height halaman (px) → sesuaikan
        size: "stretch", // "fixed" atau "stretch"
        minWidth: 300,
        maxWidth: 1200,
        minHeight: 400,
        maxHeight: 1000,
        maxShadowOpacity: 0.5,
        showCover: true, // cover terlihat sendiri di depan
        mobileScrollSupport: true, // tetap bisa scroll di HP
        flippingTime: 800, // durasi animasi (ms)
    });

    // Muat halaman dari array URL gambar
    flip.value.loadFromImages(props.pages);

    // Set info page count
    pageCount.value = flip.value.getPageCount();
    currentPage.value = flip.value.getCurrentPageIndex();

    // Event saat flip halaman
    flip.value.on("flip", (e) => {
        currentPage.value = e.data; // e.data = index halaman (0-based)
    });
};

const nextPage = () => {
    if (!flip.value) return;
    // animasi next halaman
    flip.value.flipNext("bottom");
};

const prevPage = () => {
    if (!flip.value) return;
    flip.value.flipPrev("bottom");
};

onMounted(() => {
    initFlip();
});

// Jika dokumen berganti (pages berubah), re-init
watch(
    () => props.pages,
    () => {
        initFlip();
    }
);

onBeforeUnmount(() => {
    if (flip.value) {
        flip.value.destroy();
        flip.value = null;
    }
});
</script>

<style scoped>
.flipbook-wrapper {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: center;
}

.toolbar {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: center;
}

.page-info {
    font-size: 14px;
}

/* container untuk StPageFlip */
.book-container {
    width: 100%;
    max-width: 900px;
    height: 600px;
    margin: 0 auto;
    /* background optional biar kelihatan area bukunya */
    background: #f3f4f6;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}
</style>
