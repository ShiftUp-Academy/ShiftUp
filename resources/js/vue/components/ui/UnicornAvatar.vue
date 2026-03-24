<template>
    <div ref="avatarRef" class="unicorn-avatar-wrapper" :style="{ width: size + 'px', height: size + 'px' }">
        <div v-if="isVisible" class="unicorn-studio-item" :class="{ 'fade-in': !isReady }" :data-us-project="projectId"></div>
    </div>
</template>

<script setup>
import { onMounted, ref, onBeforeUnmount } from 'vue';

const props = defineProps({
    size: {
        type: [Number, String],
        default: 32
    },
    projectId: {
        type: String,
        default: 'LTJllho0oFn5UfG5n1uo'
    }
});

const isVisible = ref(false);
const isReady = ref(window._UnicornManager && window._UnicornManager.initialized);
const avatarRef = ref(null);
let observer = null;

// Global singleton to track initialization
if (typeof window !== 'undefined' && !window._UnicornManager) {
    window._UnicornManager = {
        initialized: false,
        initTimeout: null,
        triggerGlobalInit() {
            if (this.initTimeout) clearTimeout(this.initTimeout);
            this.initTimeout = setTimeout(() => {
                if (window.UnicornStudio && window.UnicornStudio.init) {
                    window.UnicornStudio.init();
                    this.initialized = true;
                }
            }, 50);
        }
    };
}

const initUnicorn = () => {
    if (window.UnicornStudio) {
        window._UnicornManager.triggerGlobalInit();
        isReady.value = true;
        
        // Watermark cleanup (shared singleton logic could also work but keeping it simple)
        if (!window._UnicornWatermarkCleaner) {
            window._UnicornWatermarkCleaner = setInterval(() => {
                const watermarks = [
                    ...document.querySelectorAll('a[href*="unicornstudio.io"]'),
                    ...document.querySelectorAll('div[id*="unicorn-studio-watermark"]'),
                    ...document.querySelectorAll('div[style*="z-index: 999999"]'),
                    ...document.querySelectorAll('div[style*="pointer-events: none"] > a')
                ];
                watermarks.forEach(el => {
                    if (el && el.style.display !== 'none') {
                        el.style.display = 'none';
                        el.style.visibility = 'hidden';
                        el.style.opacity = '0';
                        el.style.pointerEvents = 'none';
                    }
                });
            }, 600);
            
            setTimeout(() => {
                if (window._UnicornWatermarkCleaner) clearInterval(window._UnicornWatermarkCleaner);
                window._UnicornWatermarkCleaner = null;
            }, 20000);
        }
    }
};

onMounted(() => {
    // Add Caching & Performance Hints
    if (!document.getElementById('unicorn-caching-hints')) {
        const preconnect = document.createElement('link');
        preconnect.id = 'unicorn-caching-hints';
        preconnect.rel = 'preconnect';
        preconnect.href = 'https://cdn.jsdelivr.net';
        document.head.appendChild(preconnect);
    }

    // Faster Observer
    observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                isVisible.value = true;
                initLoading();
                observer.disconnect(); 
            }
        });
    }, { rootMargin: '100px' });

    if (avatarRef.value) observer.observe(avatarRef.value);
});

const initLoading = () => {
    if (window.UnicornStudio && window.UnicornStudio.init) {
        initUnicorn();
    } else if (!document.getElementById('unicorn-studio-script')) {
        const script = document.createElement('script');
        script.id = 'unicorn-studio-script';
        script.src = 'https://cdn.jsdelivr.net/gh/hiunicornstudio/unicornstudio.js@v2.1.5/dist/unicornStudio.umd.js';
        script.async = true;
        script.onload = initUnicorn;
        document.head.appendChild(script);
    } else {
        const checkExist = setInterval(() => {
            if (window.UnicornStudio && window.UnicornStudio.init) {
                initUnicorn();
                clearInterval(checkExist);
            }
        }, 50);
        setTimeout(() => clearInterval(checkExist), 5000);
    }
};

onBeforeUnmount(() => {
    if (observer) observer.disconnect();
});
</script>

<style scoped>
.unicorn-avatar-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    flex-shrink: 0;
    transform: translateZ(0);
}

.unicorn-studio-item {
    width: 155%;
    height: 155%;
    position: absolute;
    top: 45% !important; 
    left: 50% !important;
    transform: translate(-50%, -50%);
    clip-path: inset(0 0 12% 0);
    pointer-events: none;
    will-change: transform, opacity;
}

.fade-in {
    opacity: 0;
    animation: fadeInAvatar 0.5s forwards;
}

@keyframes fadeInAvatar {
    to { opacity: 1; }
}

:deep(canvas) {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover;
    image-rendering: optimizeSpeed; /* Performance boost */
}
</style>
