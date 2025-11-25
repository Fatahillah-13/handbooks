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
import { PageFlip } from "page-flip";
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

    // destroy instance lama jika ada
    if (flip.value) {
        flip.value.destroy();
        flip.value = null;
    }

    // 🚩 ukuran & layout di sini kita kecilkan + paksa 2 halaman
    flip.value = new PageFlip(bookRef.value, {
        width: 600, // lebar buku (2 halaman gabungan)
        height: 600, // tinggi buku
        size: "fixed", // stretch sesuai container
        minWidth: 300,
        maxWidth: 800,
        minHeight: 300,
        maxHeight: 500,

        maxShadowOpacity: 0.5,
        showCover: true, // ⬅️ langsung 2 halaman, tanpa cover tunggal
        usePortrait: false, // ⬅️ jangan auto switch ke 1 halaman
        mobileScrollSupport: true,
        flippingTime: 800, // durasi animasi (ms)
    });

    // Muat halaman dari array URL gambar
    flip.value.loadFromImages(props.pages);

    pageCount.value = flip.value.getPageCount();
    currentPage.value = flip.value.getCurrentPageIndex();

    flip.value.on("flip", (e) => {
        currentPage.value = e.data; // index 0-based
    });
};

const nextPage = () => {
    if (!flip.value) return;
    flip.value.flipNext("bottom");
};

const prevPage = () => {
    if (!flip.value) return;
    flip.value.flipPrev("bottom");
};

onMounted(() => {
    initFlip();
});

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
    max-width: 700px; /* ⬅️ buku lebih kecil */
    height: 450px; /* ⬅️ tinggi juga diperkecil */
    margin: 0 auto;
    background: transparent;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}
</style>
