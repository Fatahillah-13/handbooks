<template>
    <div class="flipbook" ref="container">
        <!-- MAIN PAGE VIEWER -->
        <div class="page-wrapper">
            <!-- TWO-PAGE MODE -->
            <template v-if="twoPageMode && pages.length > 1">
                <img
                    v-if="leftPage !== null"
                    :src="pages[leftPage]"
                    class="page"
                    alt="left page"
                    :style="imageStyle"
                />
                <img
                    v-if="rightPage !== null"
                    :src="pages[rightPage]"
                    class="page"
                    alt="right page"
                    :style="imageStyle"
                />
            </template>

            <!-- SINGLE-PAGE MODE -->
            <template v-else>
                <img
                    :src="pages[current]"
                    class="page"
                    alt="page"
                    :style="imageStyle"
                />
            </template>
        </div>

        <!-- TOOLBAR -->
        <div class="toolbar">
            <div class="toolbar-left">
                <PixelButton @click="prevPage">⬅ Prev</PixelButton>
                <span class="page-info">
                    Page {{ pageLabel }} / {{ pages.length }}
                </span>
                <PixelButton @click="nextPage">Next ➡</PixelButton>
            </div>

            <div class="toolbar-center">
                <PixelButton @click="zoomOut">➖</PixelButton>
                <span class="zoom-info">{{ Math.round(zoom * 100) }}%</span>
                <PixelButton @click="zoomIn">➕</PixelButton>
                <PixelButton @click="resetZoom">Reset</PixelButton>
            </div>

            <div class="toolbar-right">
                <PixelButton @click="toggleLayout">
                    <span v-if="twoPageMode">📄 Single Page</span>
                    <span v-else>📖 Two-page</span>
                </PixelButton>

                <PixelButton @click="toggleFullscreen">
                    <span v-if="isFullscreen">🡼 Exit Fullscreen</span>
                    <span v-else>🡾 Fullscreen</span>
                </PixelButton>
            </div>
        </div>

        <!-- THUMBNAILS -->
        <div class="thumbnails" v-if="pages.length > 1">
            <div
                v-for="(p, index) in pages"
                :key="index"
                class="thumb-wrapper"
                :class="{ active: index === activeThumbIndex }"
                @click="goToPage(index)"
            >
                <img :src="p" class="thumb" :alt="'Thumbnail ' + (index + 1)" />
                <span class="thumb-label">{{ index + 1 }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import PixelButton from "./PixelButton.vue";

const props = defineProps({
    pages: { type: Array, required: true },
});

// current index selalu mengacu ke "halaman fokus"
// di twoPageMode, dia akan otomatis disnap ke halaman kiri (even)
const current = ref(0);

// zoom
const zoom = ref(1);
const minZoom = 0.5;
const maxZoom = 3;

// layout mode
const twoPageMode = ref(true);

// fullscreen
const isFullscreen = ref(false);
const container = ref(null);

const imageStyle = computed(() => ({
    transform: `scale(${zoom.value})`,
}));

// Hitung halaman kiri & kanan di mode dua halaman
const leftPage = computed(() => {
    if (!twoPageMode.value || !props.pages.length) return null;

    // pastikan halaman kiri selalu even index (0,2,4,...)
    const base = current.value % 2 === 0 ? current.value : current.value - 1;
    return base >= 0 && base < props.pages.length ? base : null;
});

const rightPage = computed(() => {
    if (!twoPageMode.value || leftPage.value === null) return null;
    const candidate = leftPage.value + 1;
    return candidate < props.pages.length ? candidate : null;
});

// label di toolbar
const pageLabel = computed(() => {
    if (!props.pages.length) return "-";

    if (twoPageMode.value && leftPage.value !== null) {
        if (rightPage.value !== null) {
            return `${leftPage.value + 1}–${rightPage.value + 1}`;
        }
        return `${leftPage.value + 1}`;
    }

    return `${current.value + 1}`;
});

// index aktif untuk highlight thumbnail
const activeThumbIndex = computed(() => {
    if (twoPageMode.value && leftPage.value !== null) {
        return leftPage.value;
    }
    return current.value;
});

function normalizeCurrent() {
    if (!props.pages.length) {
        current.value = 0;
        return;
    }
    if (current.value >= props.pages.length) {
        current.value = props.pages.length - 1;
    }
    if (current.value < 0) {
        current.value = 0;
    }
}

function nextPage() {
    if (!props.pages.length) return;

    if (twoPageMode.value) {
        const base = leftPage.value ?? current.value;
        const nextIndex = base + 2;
        if (nextIndex < props.pages.length) {
            current.value = nextIndex;
        } else {
            // kalau ganjil terakhir, boleh tetap ke halaman tunggal terakhir
            current.value = props.pages.length - 1;
        }
    } else {
        if (current.value < props.pages.length - 1) {
            current.value++;
        }
    }
}

function prevPage() {
    if (!props.pages.length) return;

    if (twoPageMode.value) {
        const base = leftPage.value ?? current.value;
        const prevIndex = base - 2;
        if (prevIndex >= 0) {
            current.value = prevIndex;
        } else {
            current.value = 0;
        }
    } else {
        if (current.value > 0) {
            current.value--;
        }
    }
}

function goToPage(index) {
    if (index >= 0 && index < props.pages.length) {
        current.value = index;
    }
}

function zoomIn() {
    zoom.value = Math.min(maxZoom, zoom.value + 0.25);
}

function zoomOut() {
    zoom.value = Math.max(minZoom, zoom.value - 0.25);
}

function resetZoom() {
    zoom.value = 1;
}

function toggleLayout() {
    twoPageMode.value = !twoPageMode.value;
    normalizeCurrent();
    resetZoom();
}

async function toggleFullscreen() {
    try {
        if (!document.fullscreenElement && container.value) {
            await container.value.requestFullscreen();
            isFullscreen.value = true;
        } else if (document.fullscreenElement) {
            await document.exitFullscreen();
            isFullscreen.value = false;
        }
    } catch (e) {
        console.error("Fullscreen error:", e);
    }
}

function handleFullscreenChange() {
    isFullscreen.value = !!document.fullscreenElement;
}

// Keyboard navigation
function handleKeydown(e) {
    if (!props.pages.length) return;

    switch (e.key) {
        case "ArrowRight":
        case "PageDown":
            nextPage();
            break;
        case "ArrowLeft":
        case "PageUp":
            prevPage();
            break;
        case "+":
        case "=":
            zoomIn();
            break;
        case "-":
        case "_":
            zoomOut();
            break;
        case "0":
            resetZoom();
            break;
    }
}

onMounted(() => {
    document.addEventListener("fullscreenchange", handleFullscreenChange);
    window.addEventListener("keydown", handleKeydown);
    normalizeCurrent();
});

onBeforeUnmount(() => {
    document.removeEventListener("fullscreenchange", handleFullscreenChange);
    window.removeEventListener("keydown", handleKeydown);
});

// kalau jumlah pages berubah (dokumen lain), reset current & zoom
watch(
    () => props.pages,
    () => {
        current.value = 0;
        zoom.value = 1;
    }
);
</script>

<style scoped>
.flipbook {
    text-align: center;
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* MAIN IMAGE AREA */
.page-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    max-height: 80vh;
    gap: 8px;
}

.page {
    max-width: 48%;
    max-height: 80vh;
    border: 4px solid #e43f5a;
    box-shadow: 4px 4px 0 #162447;
    image-rendering: pixelated;
    transition: transform 0.15s ease-out;
    transform-origin: center center;
}

/* Jika single-page mode, biar gambar agak lebih besar */
.flipbook :deep(.page-wrapper) img:only-child {
    max-width: 80%;
}

/* TOOLBAR */
.toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.toolbar-left,
.toolbar-center,
.toolbar-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.page-info,
.zoom-info {
    font-size: 14px;
}

/* THUMBNAILS */
.thumbnails {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding: 8px 2px;
    margin-top: 8px;
    border-top: 1px solid #e5e7eb;
}

.thumb-wrapper {
    position: relative;
    border: 2px solid transparent;
    border-radius: 4px;
    padding: 2px;
    cursor: pointer;
    flex: 0 0 auto;
}

.thumb-wrapper.active {
    border-color: #e43f5a;
    box-shadow: 0 0 0 2px rgba(228, 63, 90, 0.4);
}

.thumb {
    height: 70px;
    display: block;
    object-fit: cover;
}

.thumb-label {
    position: absolute;
    bottom: 2px;
    right: 4px;
    font-size: 11px;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    padding: 1px 4px;
    border-radius: 3px;
}
</style>
