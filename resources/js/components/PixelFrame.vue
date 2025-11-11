<template>
    <div class="pixel-frame">
        <slot />
    </div>
</template>

<style scoped>
.pixel-frame {
    border: 4px solid #e43f5a;
    background: #1f4068;
    padding: 8px;
    box-shadow: 4px 4px 0 #162447;
    color: #f5f5f5;
    font-family: "Press Start 2P", monospace;
    text-align: center;
    image-rendering: pixelated;
}
</style>
