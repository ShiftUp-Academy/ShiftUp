<template>
  <div class="loader-container">
    <div class="loader-circles">
      <div class="loader-circle circle-1"></div>
      <div class="loader-circle circle-2"></div>
      <div class="loader-circle circle-3"></div>
    </div>
    <div class="loader-text">Kely sisa...</div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { gsap } from 'gsap';

onMounted(() => {
  gsap.fromTo('.loader-container', 
    { opacity: 0 }, 
    { opacity: 1, duration: 0.3, ease: "power2.out" }
  );

  const tl = gsap.timeline({ repeat: -1 });

  tl.to('.loader-circle', {
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
</script>

<style scoped>
.loader-container {
  /* Positioning */
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  
  /* Flexbox Centering */
  display: flex ;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  
  /* Styling */
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  z-index: 9999;
  border-radius: inherit;
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
