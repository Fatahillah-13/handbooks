<template>
    <div>
        <h2>{{ title }}</h2>
        <Flipbook v-if="pages.length" :pages="pages" />
        <p v-else>Loading pages...</p>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import api from "../api/api";
import Flipbook from "../components/Flipbook.vue";

const route = useRoute();
const pages = ref([]);
const title = ref("");

onMounted(async () => {
    const res = await api.get(`/viewer/${route.params.slug}`);
    pages.value = res.data.pages;
    title.value = res.data.title;
});
</script>
