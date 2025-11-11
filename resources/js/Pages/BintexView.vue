<template>
    <div>
        <h2>{{ bintex.name }}</h2>
        <p>{{ bintex.description }}</p>

        <div class="docs">
            <PixelFrame v-for="doc in bintex.documents" :key="doc.id">
                <router-link :to="'/viewer/' + doc.title">{{
                    doc.title
                }}</router-link>
            </PixelFrame>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import api from "../api/api";
import PixelFrame from "../components/PixelFrame.vue";

const route = useRoute();
const bintex = ref({});

onMounted(async () => {
    const res = await api.get(`/admin/bintexes/${route.params.slug}`);
    bintex.value = res.data;
});
</script>
