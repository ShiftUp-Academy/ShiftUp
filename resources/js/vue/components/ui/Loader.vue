<template>
  <div class="loader-container" v-show="isVisible" ref="loaderRef">
    <div class="loader-circles">
      <div class="loader-circle circle-1"></div>
      <div class="loader-circle circle-2"></div>
      <div class="loader-circle circle-3"></div>
    </div>
    <div class="loader-text">Patientez...</div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { gsap } from 'gsap';

const isVisible = ref(false);
const loaderRef = ref(null);
let animationTimeline = null;

onMounted(() => {
  // Setup continuous animation
  animationTimeline = gsap.timeline({ repeat: -1, paused: true });

  animationTimeline.to('.loader-circle', {
    y: -12,
    scale: 1.2,
    duration: 0.6,
    stagger: {
      each: 0.15,
      repeat: -1,
      yoyo: true
    },
    ease: "power2.inOut"
  });
  
  gsap.fromTo('.loader-text', 
    { opacity: 0.4 }, 
    { opacity: 1, duration: 1, repeat: -1, yoyo: true, ease: "sine.inOut" }
  );
});

const startTransition = () => {
  return new Promise(resolve => {
    isVisible.value = true;
    if (animationTimeline) animationTimeline.play();
    
    // Add a slight delay before showing overlay immediately to avoid flashes on fast networks
    gsap.fromTo(loaderRef.value, 
      { opacity: 0 }, 
      { opacity: 1, duration: 0.3, ease: "power2.out", delay: 0.1, onComplete: resolve }
    );
  });
};

const endTransition = () => {
  return new Promise(resolve => {
    if (!loaderRef.value) { resolve(); return; }
    
    gsap.to(loaderRef.value, {
      opacity: 0, 
      duration: 0.3, 
      ease: "power2.in", 
      onComplete: () => {
        isVisible.value = false;
        if (animationTimeline) animationTimeline.pause();
        resolve();
      }
    });
  });
};

defineExpose({
    startTransition,
    endTransition
});
</script>

<style scoped>
.loader-container {
  /* Positioning */
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  
  /* Flexbox Centering */
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  
  /* Styling */
  background: rgba(0, 0, 0, 0.85);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  z-index: 999999;
  margin: 0;
  padding: 0;
  gap: 24px;
  overflow: hidden;
  pointer-events: all;
}

.loader-circles {
  display: flex !important;
  align-items: center;
  justify-content: center;
  gap: 12px;
}

.loader-circle {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  box-shadow: 0 0 15px rgba(138, 56, 245, 0.4);
}

.circle-1 { background-color: #8A38F5; }
.circle-2 { background-color: #A855F7; }
.circle-3 { background-color: #ffffff; }

.loader-text {
  font-family: 'Jost', sans-serif;
  font-size: 0.9rem;
  font-weight: 500;
  color: #ffffff;
  letter-spacing: 3px;
  text-transform: uppercase;
  margin-top: 10px;
}
</style>
