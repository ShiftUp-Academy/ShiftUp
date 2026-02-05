<template>
    <div class="liquidGlass-wrapper" :style="wrapperStyle" @mousemove="handleFlashMove" @mouseleave="handleFlashLeave">

        <!-- The Effect Layer -->
        <div class="liquidGlass-effect" :style="{ filter: `url(#${filterId})` }"></div>
        <div class="liquidGlass-tint"></div>
        <div class="liquidGlass-shine"></div>

        <div :class="['liquidGlass-content', { 'is-centered': center }]">
            <slot></slot>
        </div>

        <!-- SVG Filter Definition: Absolute positioning instead of display:none to keep it active -->
        <svg style="position: absolute; width: 0; height: 0; pointer-events: none; opacity: 0; visibility: hidden;">
            <defs>
                <filter :id="filterId" x="-20%" y="-20%" width="140%" height="140%" filterUnits="objectBoundingBox">
                    <feTurbulence type="fractalNoise" baseFrequency="0.012 0.012" numOctaves="1" seed="5"
                        result="turbulence" />
                    <feComponentTransfer in="turbulence" result="mapped">
                        <feFuncR type="gamma" amplitude="1" exponent="10" offset="0.5" />
                        <feFuncG type="gamma" amplitude="0" exponent="1" offset="0" />
                        <feFuncB type="gamma" amplitude="0" exponent="1" offset="0.5" />
                    </feComponentTransfer>
                    <feGaussianBlur in="turbulence" stdDeviation="3" result="softMap" />
                    <feSpecularLighting in="softMap" surfaceScale="5" specularConstant="1" specularExponent="100"
                        lighting-color="white" result="specLight">
                        <fePointLight x="-200" y="-200" z="300" />
                    </feSpecularLighting>
                    <feComposite in="specLight" operator="arithmetic" k1="0" k2="1" k3="1" k4="0" result="litImage" />
                    <feDisplacementMap in="SourceGraphic" in2="softMap" scale="200" xChannelSelector="R"
                        yChannelSelector="G" />
                </filter>
            </defs>
        </svg>
    </div>
</template>

<script setup>
import { reactive, computed } from 'vue';

const props = defineProps({
    borderRadius: {
        type: String,
        default: '0.3rem'
    },
    center: {
        type: Boolean,
        default: false
    }
});

const filterId = `glass-distort-${Math.random().toString(36).substring(2, 7)}`;
const flash = reactive({ x: '50%', y: '50%', opacity: 0 });

const wrapperStyle = computed(() => ({
    '--mouse-x': flash.x,
    '--mouse-y': flash.y,
    '--flash-opacity': flash.opacity,
    '--border-radius-val': props.borderRadius.replace(' !important', '')
}));

function handleFlashMove(event) {
    const rect = event.currentTarget.getBoundingClientRect();
    flash.x = `${(event.clientX - rect.left) / rect.width * 100}%`;
    flash.y = `${(event.clientY - rect.top) / rect.height * 100}%`;
    flash.opacity = 0.8;
}

function handleFlashLeave() {
    flash.opacity = 0;
}
</script>

<style scoped>
.liquidGlass-wrapper {
    position: relative;
    display: flex !important;
    background: transparent !important;
    overflow: hidden !important;
    border-radius: var(--border-radius-val) !important;
    box-shadow:
        0.5px 0.5px 1px rgba(255, 255, 255, 0.8) inset,
        -0.5px -0.5px 0.5px rgba(255, 255, 255, 0.1) inset,
        1px 1px 5px rgba(0, 0, 0, 0.1) inset !important;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 2.2);
    transform: translateZ(0);
    isolation: isolate;
}

.liquidGlass-effect {
    position: absolute;
    z-index: 0;
    inset: 0 !important;
    backdrop-filter: blur(10px) !important;
    /* Increased for visibility */
    background: rgba(255, 255, 255, 0.05) !important;
    /* Base for SourceGraphic */
    border-radius: var(--border-radius-val) !important;
    pointer-events: none;
    overflow: hidden;
}

.liquidGlass-tint {
    z-index: 1;
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.45) 0%, rgba(0, 0, 0, 0.15) 100%) !important;
    border-radius: var(--border-radius-val) !important;
    pointer-events: none;
}

.liquidGlass-shine {
    position: absolute;
    inset: 0;
    z-index: 2;
    overflow: hidden;
    box-shadow:
        inset 1.5px 1.5px 4px rgba(255, 255, 255, 0.25),
        inset -1.5px -1.5px 4px rgba(0, 0, 0, 0.2),
        0 0 0 1px rgba(255, 255, 255, 0.1) !important;
    border-radius: var(--border-radius-val) !important;
    pointer-events: none;
}

.liquidGlass-wrapper::after {
    content: "";
    position: absolute;
    inset: -1px;
    border-radius: var(--border-radius-val) !important;
    padding: 1.5px;
    background: radial-gradient(120px circle at var(--mouse-x) var(--mouse-y),
            rgba(255, 255, 255, 0.9),
            transparent 100%) !important;
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
    opacity: var(--flash-opacity, 0);
    transition: opacity 0.3s ease;
}

.liquidGlass-content {
    position: relative;
    z-index: 3;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.liquidGlass-content.is-centered {
    justify-content: center !important;
    align-items: center !important;
}
</style>
