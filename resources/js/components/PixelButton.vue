<template>
    <button class="pixel-btn" @click="$emit('click')">
        <slot />
    </button>
</template>

<style scoped>
.pixel-btn {
    border: 3px solid #e43f5a;
    background: #162447;
    color: #ffd460;
    font-family: "Press Start 2P", monospace;
    padding: 8px 16px;
    cursor: pointer;
    box-shadow: 3px 3px 0 #1b1b2f;
    transition: all 0.1s ease;
}

.pixel-btn:hover {
    background: #e43f5a;
    color: #fff;
    box-shadow: 2px 2px 0 #1b1b2f;
}
</style>
