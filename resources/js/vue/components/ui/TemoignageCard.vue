<template>
    <div class="temoignage-card" ref="cardRef" @mousemove="handleFlashMove" @mouseleave="handleFlashLeave"
        :style="{ '--mouse-x': flash.x, '--mouse-y': flash.y, '--flash-opacity': flash.opacity }">
        <div class="card-content">
            <div class="author-info">
                <div class="avatar-wrapper">
                    <img v-if="avatar" :src="avatar" :alt="name" class="avatar" />
                    <div v-else class="avatar placeholder">
                        {{ (name?.[0] || 'U') }}
                    </div>
                    <div class="avatar-border-glow"></div>
                </div>
                <div class="author-details">
                    <h4 class="author-name">{{ name }}</h4>
                    <span class="author-role" v-if="role">{{ role }}</span>
                </div>
            </div>

            <p :class="['testimonial-text', variant === 'media' ? 'media-mode' : 'text-mode']" v-if="text">
                "{{ text }}"
            </p>

            <slot name="media"></slot>
        </div>

        <div class="liquidGlass-effect"></div>
        <div class="flashlight-overlay"></div>
        <div class="border-glow"></div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';

defineProps({
    name: String,
    role: String,
    avatar: String,
    text: String,
    variant: {
        type: String,
        default: 'text'
    }
});

const flash = reactive({ x: '50%', y: '50%', opacity: 0 });
const cardRef = ref(null);

function handleFlashMove(event) {
    const rect = event.currentTarget.getBoundingClientRect();
    flash.x = `${event.clientX - rect.left}px`;
    flash.y = `${event.clientY - rect.top}px`;
    flash.opacity = 1;
}

function handleFlashLeave() {
    flash.opacity = 0;
}
</script>

<style scoped>
.temoignage-card {
    position: relative;
    background-color: #111;
    width: 100%;
    border-radius: 30px;
    overflow: hidden;
    padding: 40px;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease;
}

.temoignage-card:hover {
    transform: translateY(-5px);
}

.card-content {
    position: relative;
    z-index: 5;
    color: #fff;
}

.author-info {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.avatar-wrapper {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.11);
    position: relative;
    flex-shrink: 0;
}

.avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar.placeholder {
    background: linear-gradient(135deg, #333, #555);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: white;
    font-size: 1.2rem;
    height: 100%;
}

.author-name {
    font-size: 1.3rem;
    font-weight: 600;
    margin: 0;
    color: #fff;
}

.author-role {
    font-size: 1.1rem;
    color: #888;
    display: block;
}

.testimonial-text {
    line-height: 1.4;
    color: #9c9c9c;
    margin: 0;
}

.testimonial-text.text-mode {
    font-size: 1.3rem;
}

.testimonial-text.media-mode {
    font-size: 1rem;
    margin-bottom: 15px;
    color: #b0b0b0;
    width: 80%;
    margin-left: 10%;
}

.flashlight-overlay {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 2;
    background: radial-gradient(600px circle at var(--mouse-x) var(--mouse-y),
            rgba(255, 255, 255, 0.178),
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
    background: radial-gradient(400px circle at var(--mouse-x) var(--mouse-y),
            rgba(255, 255, 255, 0.425),
            transparent 60%);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: var(--flash-opacity, 0);
    transition: opacity 0.3s ease;
}

.liquidGlass-effect {
    position: absolute;
    inset: 0;
    backdrop-filter: blur(2px);
    background: rgba(255, 255, 255, 0.02);
    z-index: 1;
}
</style>
