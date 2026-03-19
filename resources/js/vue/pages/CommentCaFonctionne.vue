<template>
    <div class="page-container no-global-reveal how-it-works-page" ref="pageRef">
        <div class="back-link" @click="goBack">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M5 12L12 19M5 12L12 5" />
            </svg>
            <span>{{ $t('CommentCaFonctionne.retour') }}</span>
        </div>

        <div class="intro-section">
            <div class="intro-content">
                <h1 class="impact-title">{{ $t('CommentCaFonctionne.principes_oprationnels') }}</h1>
                <p class="subtitle">
                    {{ $t('CommentCaFonctionne.voici_les_principes') }}
                </p>
                <div class="scroll-indicator">
                    <span>{{ $t('CommentCaFonctionne.scroller_pour_dcouvrir') }}</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M7 13L12 18L17 13M7 6L12 11L17 6" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="horizontal-scroll-container" ref="scrollContainer">
            <div class="cards-track" ref="trackRef">
                <div v-for="(principleId, index) in principles" :key="index" class="principle-card"
                    :class="{ 'has-long-text': isLongText($t('CommentCaFonctionne.principle_' + principleId + '_body')), 'is-expanded': hoveredIndex === index }"
                    :style="{ backgroundColor: getCardColor(index) }" @mouseenter="handleMouseEnter(index)"
                    @mouseleave="handleMouseLeave">
                    <div class="card-content">
                        <div class="card-number">{{ String(index + 1).padStart(2, '0') }}</div>
                        <h3 class="card-title">{{ $t('CommentCaFonctionne.principle_' + principleId + '_title') }}</h3>
                        <div class="card-body"
                            v-html="formatBody($t('CommentCaFonctionne.principle_' + principleId + '_body'))"
                            data-lenis-prevent @wheel.stop></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const pageRef = ref(null);
const scrollContainer = ref(null);
const trackRef = ref(null);
const hoveredIndex = ref(null);

const handleMouseEnter = (index) => {
    hoveredIndex.value = index;
    document.body.classList.add('is-hovering-expandable');
};

const handleMouseLeave = () => {
    hoveredIndex.value = null;
    document.body.classList.remove('is-hovering-expandable');
};

const goBack = () => {
    window.history.back();
};

const principles = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16];

const colors = [
    '#F7B455', '#E67E22', '#D35400', '#C0392B',
    '#8E44AD', '#2980B9', '#27AE60', '#16A085',
    '#F39C12', '#D2527F', '#19B5FE', '#2ECC71',
    '#9B59B6', '#34495E', '#E74C3C', '#1A888D'
];

const getCardColor = (index) => colors[index % colors.length];

const formatBody = (text) => {
    return text.replace(/\n/g, '<br>');
};

const isLongText = (text) => {
    return text.length > 250 || text.includes('<br><br>');
};

let ctx;

onMounted(async () => {
    await nextTick();

    ctx = gsap.context(() => {
        const scrollSection = scrollContainer.value;
        const track = trackRef.value;
        const isMobile = window.innerWidth <= 768;

        let cardWidth;
        let expansionDelta = 0;

        if (isMobile) {
            // Mobile: 85vw, min-width 320px
            const vw85 = window.innerWidth * 0.85;
            cardWidth = Math.max(vw85, 320);
        } else {
            // Desktop: 28vw, min-width 400px
            const vw28 = window.innerWidth * 0.27;
            cardWidth = Math.max(vw28, 300);
            const vw45 = window.innerWidth * 0.45;
            expansionDelta = (window.innerWidth * 0.17);
        }

        // Total width
        const stableTrackWidth = (principles.length * cardWidth) + expansionDelta;

        track.style.width = `${stableTrackWidth}px`;
        const scrollDistance = stableTrackWidth - window.innerWidth;

        gsap.to(track, {
            x: -scrollDistance,
            ease: "none",
            scrollTrigger: {
                trigger: scrollSection,
                start: "top top",
                end: () => `+=${scrollDistance}`,
                pin: true,
                scrub: 1,
                anticipatePin: 1,
                invalidateOnRefresh: true
            }
        });

        gsap.from('.impact-title', { y: 50, opacity: 0, duration: 1, ease: 'power3.out', delay: 0.2 });
        gsap.from('.subtitle', { y: 30, opacity: 0, duration: 1, ease: 'power3.out', delay: 0.4 });
    }, pageRef.value);
});

onBeforeUnmount(() => {
    if (ctx) ctx.revert();
    document.body.classList.remove('is-hovering-expandable');
});
</script>

<style scoped>
.page-container {
    min-height: 100vh;
    background-color: #0d0616;
    color: white;
    width: 100%;
}

.back-link {
    position: fixed;
    top: 2rem;
    left: 2rem;
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 0.8rem 1.2rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50px;
    backdrop-filter: blur(15px);
    transition: all 0.3s cubic-bezier(0.19, 1, 0.22, 1);
    font-weight: 600;
    border: 1px solid rgba(255, 255, 255, 0.15);
}

@media (max-width: 768px) {
    .back-link {
        display: none !important;
    }
}

.intro-section {
    height: 87vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 140px 5vw 5vw;
    text-align: center;
}

.impact-title {
    font-size: clamp(3rem, 5vw, 5rem);
    margin-bottom: 2rem;
    font-weight: 400;
    line-height: 1;
    text-transform: uppercase;
    letter-spacing: -0.03em;
}

.subtitle {
    font-size: clamp(1.1rem, 1.5vw, 1.8rem);
    line-height: 1.5;
    opacity: 0.85;
    max-width: 850px;
    margin: 0 auto 5rem;
    font-weight: 300;
}

.scroll-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    opacity: 0.5;
    animation: bounce 2.5s infinite ease-in-out;
}

.horizontal-scroll-container {
    height: 100vh;
    width: 100vw;
    display: flex;
    overflow: hidden;
    position: relative;
    background: #0d0616;
}

.cards-track {
    display: flex;
    height: 100%;
}

.principle-card {
    flex: 0 0 28vw;
    min-width: 400px;
    height: 100vh;
    padding: 100px 50px 150px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    color: white;
    border-right: 1px solid rgba(255, 255, 255, 0.08);
    transition: flex-basis 0.6s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.4s ease;
    overflow: hidden;
}

.principle-card.is-expanded.has-long-text {
    flex-basis: 45vw;
}

.card-number {
    font-size: clamp(4rem, 6vw, 7rem);
    font-weight: 900;
    opacity: 0.12;
    margin-bottom: -1rem;
    line-height: 1;
}

.card-title {
    font-size: clamp(1.5rem, 2.5vw, 2.2rem);
    font-weight: 700;
    margin-bottom: 2rem;
    line-height: 1.1;
    letter-spacing: -0.02em;
}

.card-body {
    font-size: clamp(1rem, 1.1vw, 1.2rem);
    line-height: 1.5;
    opacity: 0.92;
    max-height: 52vh;
    overflow-y: auto;
    padding-right: 20px;
    padding-bottom: 120px;
    /* Increased security space at the bottom */
}

.card-body::-webkit-scrollbar {
    width: 4px;
}

.card-body::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
}

@keyframes bounce {

    0%,
    100% {
        transform: translateY(0);
        opacity: 0.4;
    }

    50% {
        transform: translateY(15px);
        opacity: 0.8;
    }
}

@media (max-width: 1024px) {
    .principle-card {
        flex-basis: 45vw;
    }
}

@media (max-width: 768px) {
    .principle-card {
        flex-basis: 85vw;
        min-width: 320px;
        padding: 100px 30px;
    }

    .principle-card.is-expanded.has-long-text {
        flex-basis: 85vw;
    }
}
</style>
