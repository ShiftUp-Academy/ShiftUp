<template>
  <div class="program-card" :style="{ '--mouse-x': flash.x, '--mouse-y': flash.y, '--flash-opacity': flash.opacity }"
    @mousemove="handleFlashMove" @mouseleave="handleFlashLeave" @click="goToDetail">
    <div class="image-container">
      <div class="parallax-target">
        <img :src="image" class="program-image" />
      </div>
      <div class="flashlight-overlay"></div>
      <div class="image-overlay"></div>
      <div class="image-border-glow"></div>
    </div>

    <div class="program-content">
      <div class="card-footer">
        <div class="price-tag">{{ displayPrice }}</div>
        <div v-if="progression > 0" class="mini-progression">
          <div class="prog-bar">
            <div class="prog-fill" :style="{ width: progression + '%' }"></div>
          </div>
          <span class="prog-text">{{ progression }}%</span>
        </div>
        <button v-if="showAddToCart" class="add-to-cart">
          <span class="btn-text">{{ $t('ProgramCard.ajouter_au_panier') }}</span>
          <svg viewBox="0 0 24 24" fill="none" class="arrow-icon">
            <path d="M7 17L17 7M17 7H7M17 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </svg>
        </button>
        <div v-else-if="reduction" class="reduction-badge">
          -{{ Number(reduction) }}%
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  id: {
    type: [Number, String],
    required: true
  },
  image: {
    type: String,
    required: true
  },
  price: {
    type: String,
    default: '190k Ar'
  },
  progression: {
    type: Number,
    default: 0
  },
  type: {
    type: String,
    default: 'Programme'
  },
  showAddToCart: {
    type: Boolean,
    default: true
  },
  reduction: {
    type: [Number, String],
    default: null
  }
});

const displayPrice = computed(() => {
  if (!props.price || props.price === 'Gratuit') return props.price;
  const numericValue = parseInt(props.price.replace(/[^0-9]/g, ''));
  if (!isNaN(numericValue) && numericValue >= 1000) {
    return Math.round(numericValue / 1000) + ' k';
  }
  return props.price.replace(/\s*Ar$/i, '');
});

const flash = reactive({ x: '50%', y: '50%', opacity: 0 });

function handleFlashMove(event) {
  const rect = event.currentTarget.getBoundingClientRect();
  flash.x = `${(event.clientX - rect.left)}px`;
  flash.y = `${(event.clientY - rect.top)}px`;
  flash.opacity = 1;
}

function handleFlashLeave() {
  flash.opacity = 0;
}

function goToDetail() {
  if (props.id !== undefined && props.id !== null) {
    // Normaliser le type pour gérer les variantes de casse et d'accentuation
    const normalizedType = (props.type || '').toLowerCase().trim();
    if (normalizedType === 'séminaire' || normalizedType === 'seminaire') {
      router.visit(`/seminaires/${props.id}`);
    } else {
      router.visit(`/programmes/${props.id}`);
    }
  }
}
</script>

<style scoped>
.program-card {
  background: transparent;
  border-radius: 20px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  position: relative;
  cursor: pointer;
  border: none;
  width: 100%;
  transition: filter 0.8s ease;
  will-change: transform, opacity, filter;
}

.image-container {
  position: relative;
  width: 100%;
  aspect-ratio: 18 / 9;
  overflow: hidden;
  border-radius: 20px;
  transform: translateZ(0);
}

.program-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.8s cubic-bezier(0.2, 0, 0.2, 1);
}

.flashlight-overlay {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 5;
  background: radial-gradient(400px circle at var(--mouse-x) var(--mouse-y),
      rgba(255, 255, 255, 0.23),
      transparent 80%);
  opacity: var(--flash-opacity, 0);
  transition: opacity 0.3s ease;
}

.image-border-glow {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 6;
  border-radius: inherit;
  padding: 1.5px;
  background: radial-gradient(300px circle at var(--mouse-x) var(--mouse-y),
      rgba(255, 255, 255, 0.5),
      transparent 60%);
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  opacity: var(--flash-opacity, 0);
  transition: opacity 0.3s ease;
}

.parallax-target {
  width: 100%;
  height: 100%;
  will-change: transform;
}

.image-overlay {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 2;
}

.program-content {
  padding: 18px 2px;
  display: flex;
  flex-direction: column;
  position: relative;
  z-index: 6;
  flex-grow: 1;
}

.program-title {
  font-size: 1.2rem;
  font-weight: 500;
  color: #fff;
  margin: 0 0 12px 0;
  line-height: 1.2;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.price-tag {
  font-size: 1.5rem;
  font-weight: 600;
  color: #fff;
  letter-spacing: -0.02em;
}

.mini-progression {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
  margin: 0 15px;
}

.prog-bar {
  flex: 1;
  height: 4px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 2px;
  overflow: hidden;
}

.prog-fill {
  height: 100%;
  background: #8A38F5;
  transition: width 0.6s ease;
}

.prog-text {
  font-size: 0.75rem;
  font-weight: 700;
  color: #8A38F5;
}

.add-to-cart {
  background: none;
  border: none;
  display: flex;
  align-items: center;
  font-size: 1rem;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
  padding: 0;
  letter-spacing: 0.05em;
  transition: all 0.4s cubic-bezier(0.2, 0, 0.2, 1);
}

.btn-text {
  transition: transform 0.4s cubic-bezier(0.17, 0.67, 0.83, 0.67);
}

.arrow-icon {
  width: 25px;
  height: 25px;
  transition: transform 0.4s cubic-bezier(0.17, 0.67, 0.83, 0.67);
}

.add-to-cart:hover {
  transform: translateY(-5px);
}

.add-to-cart:hover .btn-text {
  transform: translateX(-3px);
}

.add-to-cart:hover .arrow-icon {
  transform: translateX(3px);
}

@media (max-width: 768px) {
  .price-tag {
    font-size: 1.3rem;
  }
}

.reduction-badge {
  position: relative;
  background: linear-gradient(90deg, #dc2626, #f7b455, #dc2626);
  background-size: 200% 100%;
  color: white;
  padding: 6px 14px;
  border-radius: 12px;
  font-weight: 800;
  font-size: 0.9rem;
  animation: gradient-move 3s linear infinite, pulse 2s infinite;
  overflow: hidden;
}

.reduction-badge::after {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 50%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transform: skewX(-20deg);
  animation: shine-move 2.5s infinite;
}

@keyframes gradient-move {
  0% {
    background-position: 0% 50%;
  }

  100% {
    background-position: 200% 50%;
  }
}

@keyframes shine-move {
  0% {
    left: -100%;
  }

  100% {
    left: 150%;
  }
}

@keyframes pulse {
  0% {
    transform: scale(1);
  }

  50% {
    transform: scale(1.03);
  }

  100% {
    transform: scale(1);
  }
}
</style>
