<template>
    <Transition name="none">
        <div v-if="currentAch" class="reward-notif-container" ref="notifRef">
            <div class="reward-card-glass">
                <div class="reward-content">
                    <div class="video-circle-wrapper">
                        <video :src="currentAch.video_link" autoplay loop muted playsinline
                            class="badge-video-circle"></video>
                        <div class="glow-ring"></div>
                    </div>

                    <div class="reward-details">
                        <div class="reward-header">
                            <span class="reward-label">Réussite débloquée !</span>
                            <h3 class="reward-title">{{ currentAch.nom }}</h3>
                            <div v-if="currentAch.context_title" class="context-subtitle">{{ currentAch.context_title }}
                            </div>
                        </div>
                        <p class="reward-desc">{{ currentAch.description }}</p>
                    </div>
                </div>
                <div class="reward-progress-bar" ref="progressRef"></div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { gsap } from 'gsap';

const page = usePage();
const notifRef = ref(null);
const progressRef = ref(null);
const currentAch = ref(null);
const queue = ref([]);
const isShowing = ref(false);

const showNext = () => {
    if (queue.value.length === 0) {
        isShowing.value = false;
        currentAch.value = null;
        return;
    }

    isShowing.value = true;
    currentAch.value = queue.value.shift();

    nextTick(() => {
        const el = notifRef.value;
        const bar = progressRef.value;
        if (!el) return;
        gsap.set(el, {
            y: -100,
            opacity: 0,
            scale: 0.8,
            filter: 'blur(20px)',
            borderRadius: '100px'
        });

        const tl = gsap.timeline({
            onComplete: () => {
                setTimeout(() => {
                    gsap.to(el, {
                        y: -50,
                        opacity: 0,
                        scale: 0.9,
                        filter: 'blur(15px)',
                        borderRadius: '100px',
                        duration: 0.8,
                        ease: "power3.inOut",
                        onComplete: () => {
                            currentAch.value = null;
                            showNext();
                        }
                    });
                }, 5000);
            }
        });

        tl.to(el, {
            y: 20,
            opacity: 1,
            scale: 1,
            filter: 'blur(0px)',
            borderRadius: '30px',
            duration: 1.2,
            ease: "elastic.out(1, 0.75)"
        });

        // Progress bar animation
        gsap.fromTo(bar, { scaleX: 1 }, {
            scaleX: 0,
            duration: 5,
            ease: "none",
            delay: 1.2
        });
    });
};

watch(() => page.props.flash?.new_achievements, (newVal) => {
    if (newVal && Array.isArray(newVal)) {
        newVal.forEach(ach => {
            // Check if this specific instance (id + context) is already in queue
            const isDuplicate = queue.value.some(q => q.id === ach.id && q.context_title === ach.context_title);
            if (!isDuplicate) {
                queue.value.push(ach);
            }
        });
        if (!isShowing.value) {
            showNext();
        }
    }
}, { deep: true, immediate: true });
</script>

<style scoped>
.reward-notif-container {
    position: fixed;
    top: 2vh;
    left: 50%;
    transform: translateX(-50%);
    z-index: 999999;
    width: 500px;
    pointer-events: auto;
    will-change: transform, opacity, filter;
}

.reward-card-glass {
    background: rgba(10, 10, 10, 0.85);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: inherit;
    padding: 24px;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
    position: relative;
    overflow: hidden;
}

.reward-content {
    display: flex;
    align-items: center;
    gap: 24px;
}

.video-circle-wrapper {
    width: 85px;
    height: 85px;
    border-radius: 50%;
    position: relative;
    flex-shrink: 0;
    background: #000;
}

.badge-video-circle {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    z-index: 2;
    position: relative;
    border: 2px solid rgba(255, 255, 255, 0.1);
}

.glow-ring {
    position: absolute;
    inset: -3px;
    border-radius: 50%;
    background: linear-gradient(135deg, #F7B455, #00D2FF);
    z-index: 1;
    opacity: 0.6;
    filter: blur(8px);
    animation: rotateGlow 4s linear infinite;
}

@keyframes rotateGlow {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.reward-details {
    flex: 1;
}

.reward-header {
    margin-bottom: 6px;
}

.reward-label {
    display: block;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #F7B455;
    letter-spacing: 1.5px;
    margin-bottom: 2px;
}

.reward-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 800;
    color: #fff;
    line-height: 1.1;
    letter-spacing: -0.5px;
}

.context-subtitle {
    font-size: 1.05rem;
    color: #8A38F5;
    font-weight: 600;
    margin-top: 4px;
}

.reward-desc {
    margin: 4px 0 0;
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.6);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.reward-progress-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 2px;
    background: linear-gradient(to right, #F7B455, #00D2FF);
    transform-origin: left;
}

@media (max-width: 520px) {
    .reward-notif-container {
        width: 95%;
    }

    .reward-content {
        gap: 16px;
    }

    .video-circle-wrapper {
        width: 70px;
        height: 70px;
    }

    .reward-title {
        font-size: 1.1rem;
    }
}
</style>
