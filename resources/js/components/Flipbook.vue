<template>
    <div class="flipbook">
        <img :src="pages[current]" class="page" alt="page" />

        <div class="controls">
            <PixelButton @click="prevPage">⬅ Prev</PixelButton>
            <span>{{ current + 1 }} / {{ pages.length }}</span>
            <PixelButton @click="nextPage">Next ➡</PixelButton>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import PixelButton from "./PixelButton.vue";

const props = defineProps({
    pages: { type: Array, required: true },
});

const current = ref(0);

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
    border: 4px solid #e43f5a;
    box-shadow: 4px 4px 0 #162447;
    image-rendering: pixelated;
}
.controls {
    margin-top: 10px;
}
</style>
