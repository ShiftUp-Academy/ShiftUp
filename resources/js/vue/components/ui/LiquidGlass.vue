<template>
  <div 
    class="liquid-glass-wrapper"
    :style="{ 
      '--mouse-x': flash.x, 
      '--mouse-y': flash.y, 
      '--flash-opacity': flash.opacity,
       borderRadius: borderRadius 
    }"
    @mousemove="handleFlashMove" 
    @mouseleave="handleFlashLeave"
  >
    <!-- Use unique ID for filter to avoid conflicts if multiple instances -->
    <div class="liquidGlass-effect" :style="{ filter: `url(#${filterId})` }"></div>
    <div class="liquidGlass-tint"></div>
    <div class="liquidGlass-shine"></div>
    
    <div class="content-slot">
      <slot></slot>
    </div>

    <!-- SVG Filter matching AppHeader exactly -->
    <svg style="position: absolute; width: 0; height: 0; overflow: hidden;" aria-hidden="true">
      <defs>
        <filter :id="filterId" x="0%" y="0%" width="100%" height="100%" filterUnits="objectBoundingBox">
            <feTurbulence type="fractalNoise" baseFrequency="0.01 0.01" numOctaves="1" seed="5" result="turbulence" />
            <feComponentTransfer in="turbulence" result="mapped">
                <feFuncR type="gamma" amplitude="1" exponent="10" offset="0.5" />
                <feFuncG type="gamma" amplitude="0" exponent="1" offset="0" />
                <feFuncB type="gamma" amplitude="0" exponent="1" offset="0.5" />
            </feComponentTransfer>
            <feGaussianBlur in="turbulence" stdDeviation="3" result="softMap" />
            <feSpecularLighting in="softMap" surfaceScale="5" specularConstant="1" specularExponent="100" lighting-color="white" result="specLight">
                <fePointLight x="-200" y="-200" z="300" />
            </feSpecularLighting>
            <feComposite in="specLight" operator="arithmetic" k1="0" k2="1" k3="1" k4="0" result="litImage" />
            <feDisplacementMap in="SourceGraphic" in2="softMap" scale="200" xChannelSelector="R" yChannelSelector="G" />
        </filter>
      </defs>
    </svg>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';

const props = defineProps({
  borderRadius: {
    type: String,
    default: '0px'
  }
});

const filterId = `glass-distortion-${Math.random().toString(36).substr(2, 9)}`;

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
.liquid-glass-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  overflow: hidden; /* Ensures radius clipping work */
  cursor: pointer; /* Pointer as requested */
  /* Replicate AppHeader Shadow */
  box-shadow: 
    0.5px 0.5px 1px rgba(255, 255, 255, 1) inset, 
    -0.5px -0.5px 0.5px rgba(255, 255, 255, 0.112) inset, 
    1px 1px 5px rgba(0, 0, 0, 0.018) inset;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 2.2);
  /* Ensure clipping works for all children including absolute ones */
  isolation: isolate;
  transform: translateZ(0); 
}

.liquidGlass-effect {
  position: absolute;
  z-index: 0;
  inset: 0;
  backdrop-filter: blur(4px);
  overflow: hidden;
  pointer-events: none;
  border-radius: inherit; /* Inherit border radius from wrapper */
}

.liquidGlass-tint {
  z-index: 1;
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(0, 0, 0, 0.3) 100%, rgba(0, 0, 0, 0.08) 100%);
  pointer-events: none;
  border-radius: inherit;
}

.liquidGlass-shine {
  position: absolute;
  inset: 0;
  z-index: 2;
  overflow: hidden;
  box-shadow: 
    inset 1.5px 1.5px 3px rgba(255, 255, 255, 0.221),
    inset -1.5px -1.5px 3px rgba(0, 0, 0, 0.15),
    0 0 0 1px rgba(255, 255, 255, 0.05);
  pointer-events: none;
  border-radius: inherit;
}

.liquid-glass-wrapper::after {
  content: "";
  position: absolute;
  inset: -1px;
  border-radius: inherit; 
  padding: 1.2px; 
  background: radial-gradient(
    100px circle at var(--mouse-x) var(--mouse-y), 
    rgba(255, 255, 255, 0.9), 
    transparent 100%
  );
  -webkit-mask: 
    linear-gradient(#fff 0 0) content-box, 
    linear-gradient(#fff 0 0);
  mask: 
    linear-gradient(#fff 0 0) content-box, 
    linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
  z-index: 10;
  opacity: 1;
}

.content-slot {
  position: relative;
  z-index: 5;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}
</style>
