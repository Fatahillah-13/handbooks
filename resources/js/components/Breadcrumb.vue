<template>
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol>
            <li
                v-for="(item, index) in items"
                :key="index"
                class="breadcrumb-item"
            >
                <span
                    v-if="!item.to || index === items.length - 1"
                    class="crumb active"
                >
                    {{ item.label }}
                </span>
                <router-link v-else :to="item.to" class="crumb">
                    {{ item.label }}
                </router-link>

                <span v-if="index !== items.length - 1" class="separator"
                    >/</span
                >
            </li>
        </ol>
    </nav>
</template>

<script setup>
const props = defineProps({
    items: {
        type: Array,
        required: true,
        // [{ label: 'Home', to: { name: 'home' } }, { label: 'Storage A' }]
    },
});
</script>

<style scoped>
.breadcrumb {
    font-size: 14px;
    margin-bottom: 8px;
}

ol {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
}

.crumb {
    text-decoration: none;
    color: #374151;
}

.crumb:hover {
    text-decoration: underline;
}

.crumb.active {
    font-weight: 600;
    color: #111827;
    text-decoration: none;
}

.separator {
    margin: 0 6px;
    color: #9ca3af;
}
</style>
