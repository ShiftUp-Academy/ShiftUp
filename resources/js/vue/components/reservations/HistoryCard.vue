<template>
    <div class="history-card-wrapper" @mousemove="handleFlashMove" @mouseleave="handleFlashLeave" :style="flashStyle">
        <div class="archive-card-new">
            <div class="h-card-content">
                <div class="h-card-left">
                    <div class="archive-icon"><i :class="iconClass"></i></div>
                    <div>
                        <h3 class="h-title">{{ title }}</h3>
                        <p class="h-subtitle">{{ subtitle }}</p>
                    </div>
                </div>
                <div class="h-card-right">
                    <div class="h-category">{{ category }}</div>
                    <div class="h-action">
                        {{ actionText }}
                        <i :class="actionIconClass" class="ml-2"></i>
                    </div>
                </div>
            </div>
            <div class="flashlight-overlay"></div>
            <div class="border-glow"></div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    title: String,
    subtitle: String,
    category: String,
    actionText: String,
    iconClass: {
        type: String,
        default: 'fas fa-archive'
    },
    actionIconClass: {
        type: String,
        default: 'fas fa-check-circle'
    }
});

const flashX = ref('50%');
const flashY = ref('50%');
const flashOpacity = ref(0);

const handleFlashMove = (event) => {
    const rect = event.currentTarget.getBoundingClientRect();
    flashX.value = `${event.clientX - rect.left}px`;
    flashY.value = `${event.clientY - rect.top}px`;
    flashOpacity.value = 1;
};

const handleFlashLeave = () => {
    flashOpacity.value = 0;
};

const flashStyle = computed(() => ({
    '--mouse-x': flashX.value,
    '--mouse-y': flashY.value,
    '--flash-opacity': flashOpacity.value
}));
</script>

<style scoped>
.history-card-wrapper {
    position: relative;
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    cursor: pointer;
    width: 100%;
}

.history-card-wrapper:hover {
    transform: scale(1.005) translateY(-2px);
    z-index: 10;
}

.archive-card-new {
    background: linear-gradient(135deg, #202020 0%, #1A888D 50%, #8a38f5b7 100%);
    padding: 25px 35px;
    position: relative;
    overflow: hidden;
    transition: border-color 0.3s ease;
}

.archive-card-new:hover {
    border-color: rgba(255, 255, 255, 0.15);
}

.h-card-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 5;
}

.h-card-left {
    display: flex;
    align-items: center;
    gap: 25px;
}

.archive-icon {
    font-size: 1.2rem;
    color: rgba(255, 255, 255, 0.858);
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 10px;
}

.h-title {
    font-size: 1.4rem;
    font-weight: 500;
    color: #fff;
    margin: 0;
    letter-spacing: -0.01em;
}

.h-subtitle {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.91);
    margin: 4px 0 0 0;
}

.h-card-right {
    text-align: right;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 5px;
}

.h-category {
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.879);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.h-action {
    font-size: 0.9rem;
    font-weight: 500;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 8px;
}

.flashlight-overlay {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 2;
    background: radial-gradient(400px circle at var(--mouse-x, 50%) var(--mouse-y, 50%),
            rgba(255, 255, 255, 0.08),
            transparent 80%);
    opacity: var(--flash-opacity, 0);
    transition: opacity 0.3s ease;
}

.border-glow {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 3;
    border-radius: inherit;
    padding: 1px;
    background: radial-gradient(300px circle at var(--mouse-x, 50%) var(--mouse-y, 50%),
            rgba(255, 255, 255, 0.2),
            transparent 60%);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: var(--flash-opacity, 0);
    transition: opacity 0.3s ease;
}

.ml-2 {
    margin-left: 0.5rem;
}

@media (max-width: 768px) {
    .h-card-left {
        gap: 15px;
    }

    .h-title {
        font-size: 1.1rem;
    }

    .h-card-right {
        display: none;
    }
}
</style>
