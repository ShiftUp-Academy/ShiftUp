<template>
  <div 
    class="program-card"
    :style="{ '--mouse-x': flash.x, '--mouse-y': flash.y, '--flash-opacity': flash.opacity }"
    @mousemove="handleFlashMove"
    @mouseleave="handleFlashLeave"
  >
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
        <div class="price-tag">{{ price }}</div>
        <button class="add-to-cart">
          <span class="btn-text">AJOUTER AU PANIER</span>
          <svg viewBox="0 0 24 24" fill="none" class="arrow-icon">
            <path d="M7 17L17 7M17 7H7M17 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue';

defineProps({
  image: {
    type: String,
    required: true
  },
  price: {
    type: String,
    default: '190k Ar'
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

.program-card:hover .program-image {
  transform: scale(1.1); 
}


.flashlight-overlay {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 5;
  background: radial-gradient(
    400px circle at var(--mouse-x) var(--mouse-y), 
    rgba(255, 255, 255, 0.23), 
    transparent 80%
  );
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
  background: radial-gradient(
    300px circle at var(--mouse-x) var(--mouse-y), 
    rgba(255, 255, 255, 0.5), 
    transparent 60%
  );
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
</style>