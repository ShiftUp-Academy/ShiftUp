<template>
    <div class="coaching-card" ref="cardRef" @mousemove="handleFlashMove" @mouseleave="handleFlashLeave"
        :style="{ '--mouse-x': flash.x, '--mouse-y': flash.y, '--flash-opacity': flash.opacity }">
        <div class="card-content">
            <div class="card-header">
                <h3 class="coaching-type">{{ title }}</h3>
            </div>

            <div class="card-body">
                <p class="description">{{ description }}</p>
            </div>

            <div class="card-footer">
                <div class="formateur-info">
                    <div class="avatar-wrapper">
                        <img :src="trainerAvatar" :alt="trainerName" class="avatar" />
                        <div class="avatar-border-glow"></div>
                    </div>
                    <div class="details">
                        <span class="trainer-name">{{ trainerName }}</span>
                        <span class="label">Coach Formateur</span>
                    </div>
                </div>

                <PremiumButton class="reserve-btn" @click="$emit('reserve')">
                    Réserver
                </PremiumButton>
            </div>
        </div>

        <div class="flashlight-overlay"></div>
        <div class="border-glow"></div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import PremiumButton from './PremiumButton.vue';

defineProps({
    title: String,
    description: String,
    price: [Number, String],
    trainerName: {
        type: String,
        default: 'Nantenaina Randria'
    },
    trainerAvatar: {
        type: String,
        default: '/images/Bibliothèque/Nantenaina.jpg'
    },
});

const emit = defineEmits(['reserve']);

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MGA', minimumFractionDigits: 0 }).format(price);
};

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
.coaching-card {
    position: relative;
    background-color: #111;
    overflow: hidden;
    padding: 35px;
    height: 100%;
    display: flex;
    flex-direction: column;
    border: 0.5px solid rgba(17, 17, 17, 0);
    transition: transform 0.3s ease;
}

.coaching-card:hover {
    transform: translateY(-5px);
}

.card-content {
    position: relative;
    z-index: 5;
    color: #fff;
    display: flex;
    flex-direction: column;
    height: 100%;
    justify-content: space-between;
}

.coaching-type {
    font-size: 1.8rem;
    font-weight: 800;
    margin: 0;
    background: linear-gradient(90deg, #fff, #888);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1.2;
}

.price-info {
    margin-bottom: 20px;
}

.price-amount {
    font-size: 0.9rem;
    color: #8A38F5;
    font-weight: 600;
    letter-spacing: 0.05em;
    background: rgba(138, 56, 245, 0.1);
    padding: 6px 12px;
    border-radius: 6px;
}

.description {
    font-size: 1.1rem;
    line-height: 1.2;
    color: #9c9c9c;
    margin: 10px 0 30px 0 !important;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.card-footer {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.formateur-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar-wrapper {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.details {
    display: flex;
    flex-direction: column;
}

.trainer-name {
    font-size: 1rem;
    font-weight: 600;
    color: #fff;
}

.label {
    font-size: 0.8rem;
    color: #666;
}

.reserve-btn {
    width: 100% !important;
    margin-top: 10px;
}

.flashlight-overlay {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 2;
    background: radial-gradient(600px circle at var(--mouse-x) var(--mouse-y),
            rgba(214, 214, 214, 0.15),
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
            rgba(224, 224, 224, 0.5),
            transparent 60%);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: var(--flash-opacity, 0);
    transition: opacity 0.3s ease;
}
</style>
