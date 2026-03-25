<template>
  <Link :href="`/programmes/${props.id}`" class="program-card" :style="{ '--mouse-x': flash.x, '--mouse-y': flash.y, '--flash-opacity': flash.opacity }"
    @mousemove="handleFlashMove" @mouseleave="handleFlashLeave"
    @click="$trackEvent('clic_ressource_gratuite', { id: props.id })">
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
        <div v-if="progression > 0" class="mini-progression">
          <div class="prog-bar">
            <div class="prog-fill" :style="{ width: progression + '%' }"></div>
          </div>
          <span class="prog-text">{{ progression }}%</span>
        </div>
        <div class="view-more">
          <span class="btn-text">{{ $t('voir_plus') }}</span>
          <svg viewBox="0 0 24 24" fill="none" class="arrow-icon">
            <path d="M7 17L17 7M17 7H7M17 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </svg>
        </div>
      </div>
    </div>
  </Link>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const $t = (key) => {
    return page.props.translations?.[key] || key;
};

const props = defineProps({
  id: {
    type: [Number, String],
    required: true
  },
  image: {
    type: String,
    required: true
  },
  progression: {
    type: Number,
    default: 0
  }
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

// Removed goToDetail to use Link prefetch
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
  transition: opacity 0.4s ease, filter 0.4s ease;
  will-change: transform, opacity, filter;
  transform: translateZ(0);
  text-decoration: none !important;
}

.image-container {
  position: relative;
  width: 100%;
  aspect-ratio: 18 / 9;
  overflow: hidden;
  border-radius: 20px;
  transform: translateZ(0);
  will-change: transform;
}

.program-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.2, 0, 0.2, 1);
  will-change: transform;
}

.program-card:hover .program-image {
  transform: scale(1.05);
  /* Restore subtle scale if it was supposed to be there */
}

.flashlight-overlay {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 5;
  background: radial-gradient(400px circle at var(--mouse-x) var(--mouse-y),
      rgba(255, 255, 255, 0.15),
      transparent 80%);
  opacity: var(--flash-opacity, 0);
  transition: opacity 0.3s ease;
  transform: translateZ(0);
  will-change: transform, opacity;
}

.image-border-glow {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 6;
  border-radius: inherit;
  padding: 1.5px;
  background: radial-gradient(300px circle at var(--mouse-x) var(--mouse-y),
      rgba(255, 255, 255, 0.4),
      transparent 60%);
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  opacity: var(--flash-opacity, 0);
  transition: opacity 0.3s ease;
  transform: translateZ(0);
}

.parallax-target {
  width: 100%;
  height: 100%;
  will-change: transform;
  transform: translateZ(0);
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
  gap: 12px;
}

.mini-progression {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
  margin-right: 15px;
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
  font-size: 0.7vw;
  font-weight: 700;
  color: #8A38F5;
}

.view-more {
  background: none;
  border: none;
  display: flex;
  align-items: center;
  font-size: 0.9vw;
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

.view-more:hover {
  transform: translateY(-5px);
}

.view-more:hover .btn-text {
  transform: translateX(-3px);
}

.view-more:hover .arrow-icon {
  transform: translateX(3px);
}

.card-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  width: 100%;
}

@media (max-width: 768px) {
  .image-container {
    aspect-ratio: 16 / 10;
    /* Image plus haute sur mobile */
  }

  .view-more {
    font-size: 0.8rem;
    /* Bouton plus petit */
  }

  .arrow-icon {
    width: 18px;
    /* Icône plus petite */
    height: 18px;
  }

  .program-content {
    padding: 10px 2px;
    /* Réduire le padding au profit de l'image */
  }
}
</style>
