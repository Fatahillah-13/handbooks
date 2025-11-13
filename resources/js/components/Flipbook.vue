<!-- resources/js/components/Flipbook.vue -->
<template>
    <div class="flipbook" v-if="pages.length">
        <img :src="pages[current]" class="page" alt="page" />

        <div class="controls">
            <PixelButton @click="prevPage" :disabled="current === 0">
                ⬅ Prev
            </PixelButton>

            <span>{{ current + 1 }} / {{ pages.length }}</span>

            <PixelButton
                @click="nextPage"
                :disabled="current === pages.length - 1"
            >
                Next ➡
            </PixelButton>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from "vue";
import PixelButton from "./PixelButton.vue";

const props = defineProps({
    pages: { type: Array, required: true },
});

const current = ref(0);

// reset ke halaman pertama jika dokumen berganti
watch(
    () => props.pages,
    () => {
        current.value = 0;
    }
);

function nextPage() {
    if (current.value < props.pages.length - 1) current.value++;
}

function prevPage() {
    if (current.value > 0) current.value--;
}
</script>

<style scoped>
.flipbook {
    text-align: center;
    margin-top: 20px;
}

.page {
    width: 80%;
    max-width: 900px;
    border: 4px solid #e43f5a;
    box-shadow: 4px 4px 0 #162447;
    image-rendering: pixelated;
}

.controls {
    margin-top: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
}
</style>
