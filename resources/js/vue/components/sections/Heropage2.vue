<template>
    <div class="heropage2-root no-global-reveal" ref="rootRef">
        <ShaderBackground :colors="themeColors" class="hero-section">
            <div class="hero-content">
                <h1 class="hero-title" id="animated-text">
                    <span v-for="(wordData, index) in titleWords" :key="index" class="word-wrapper">
                        <span class="word" :class="{ 'gt-alpina-font': wordData.isBogart }">
                            {{ wordData.text }}
                        </span>&nbsp;
                    </span>
                </h1>

                <div class="highlight-text-wrapper">
                    <svg class="highlight-svg" viewBox="0 0 1000 200">
                        <text x="50%" y="70%" dominant-baseline="middle" text-anchor="middle" class="svg-text"
                            fill="#FFFFFF">
                            Libre
                        </text>
                    </svg>
                </div>
            </div>
        </ShaderBackground>

        <div class="video-transition-container" ref="videoContainerRef">
            <div class="black-curve-bg" ref="curveBgRef"></div>

            <div class="video-wrapper" ref="videoWrapperRef">
                <div class="video-overlay" ref="videoOverlayRef">
                    <div class="scroll-indicator">
                        <div class="scroll-pill">
                            <span class="scroll-text">FAITES DÉFILER</span>
                            <div class="mouse-icon">
                                <span class="wheel"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <video class="transition-video" ref="videoRef" muted loop playsinline autoplay preload="metadata"
                    :src="heroVideoSrc">
                </video>
            </div>
        </div>

        <div class="custom-cursor" ref="cursorRef" @click="handleCursorClick">
            <span ref="cursorTextRef">VOIR PROGRAMME</span>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { router } from '@inertiajs/vue3';
import ShaderBackground from '../ui/ShaderBackground.vue';

gsap.registerPlugin(ScrollTrigger);

const themeColors = { 
    primary: '#F7B455', 
    secondary: '#0E7EC3', 
    accent: '#981e12', 
    dark: '#000000' 
};
const heroVideoSrc = "https://res.cloudinary.com/dzgdjei0h/video/upload/v1766751142/Shift_Up_1_Workflow_1_lzre8i.mp4";
const fullTitle = 'Propulsez votre entreprise au niveau supérieur et devenez un entrepreneur';
const bogartWords = ['votre', 'entreprise', 'devenez', 'un', 'entrepreneur'];
const titleWords = fullTitle.trim().split(/\s+/).map(word => ({
    text: word,
    isBogart: bogartWords.includes(word.toLowerCase())
}));

const rootRef = ref(null);
const cursorRef = ref(null);
const cursorTextRef = ref(null);
const videoContainerRef = ref(null);
const curveBgRef = ref(null);
const videoWrapperRef = ref(null);
const videoOverlayRef = ref(null);

let currentMode = 'programmes';
let xSetter, ySetter;

function initAnimations() {
    gsap.fromTo(".word", { y: '110%' }, { y: '0%', duration: 1.2, ease: "power4.out", stagger: 0.05, delay: 0.2 });
    gsap.fromTo(".highlight-text-wrapper", { opacity: 0, scale: 0.9 }, { opacity: 1, scale: 1, duration: 1, ease: "power3.out", transformOrigin: "center center", delay: 1.2 });

    const videoTl = gsap.timeline({
        scrollTrigger: {
            trigger: videoContainerRef.value,
            start: "top 95%",
            end: "bottom center",
            scrub: true,
        }
    });

    videoTl.fromTo(curveBgRef.value,
        { clipPath: 'ellipse(15% 0% at 50% 100%)' },
        { clipPath: 'ellipse(150% 120% at 50% 100%)', ease: "power1.inOut" }
    ).fromTo(videoWrapperRef.value,
        { width: "5%", borderRadius: "0px", y: 400 },
        { width: "85%", borderRadius: "30px", y: -5, ease: "power2.out" },
        0
    );

    gsap.fromTo(videoOverlayRef.value,
        { opacity: 1 },
        {
            opacity: 0,
            ease: "power1.in",
            scrollTrigger: {
                trigger: videoContainerRef.value,
                start: "top 55%",
                end: "top 40%",
                scrub: true,
            }
        }
    );
}

const handleGlobalMouseMove = (e) => {
    if (!cursorRef.value || !rootRef.value) return;

    xSetter(e.clientX);
    ySetter(e.clientY);

    const rect = rootRef.value.getBoundingClientRect();
    const isInside = e.clientX >= rect.left && e.clientX <= rect.right &&
        e.clientY >= rect.top && e.clientY <= rect.bottom;

    cursorRef.value.style.pointerEvents = 'none';
    const elementUnderMouse = document.elementFromPoint(e.clientX, e.clientY);
    cursorRef.value.style.pointerEvents = 'auto';

    const isOverHeader = elementUnderMouse?.closest('header') ||
        elementUnderMouse?.closest('.menu') ||
        elementUnderMouse?.closest('.footer-menu') ||
        elementUnderMouse?.closest('.robot-container');

    if (!isInside || isOverHeader) {
        gsap.set(cursorRef.value, { opacity: 0, scale: 0 });
        return;
    } else {
        gsap.to(cursorRef.value, { opacity: 1, scale: 1, duration: 0.2 });
    }

    const isOverVideo = videoWrapperRef.value?.contains(elementUnderMouse) ||
        curveBgRef.value?.contains(elementUnderMouse);

    const newMode = isOverVideo ? 'evenements' : 'programmes';

    if (newMode !== currentMode) {
        currentMode = newMode;
        if (currentMode === 'evenements') {
            cursorTextRef.value.textContent = 'VOIR LES ÉVÈNEMENTS';
            gsap.to(cursorRef.value, { backgroundColor: '#000', duration: 0.2 });
        } else {
            cursorTextRef.value.textContent = 'VOIR NOS PROGRAMMES';
            gsap.to(cursorRef.value, { backgroundColor: '#8A38F5', duration: 0.2 });
        }
    }
};

function handleCursorClick() {
    if (currentMode === 'evenements') {
        const seminaireSection = document.querySelector('.seminaire-section');
        if (seminaireSection) {
            const y = seminaireSection.getBoundingClientRect().top + window.pageYOffset;
            window.scrollTo({ top: y, behavior: 'smooth' });
        }
    } else {
        router.visit('/programmes');
    }
}

onMounted(() => {
    xSetter = gsap.quickSetter(cursorRef.value, "x", "px");
    ySetter = gsap.quickSetter(cursorRef.value, "y", "px");

    initAnimations();

    window.addEventListener('mousemove', handleGlobalMouseMove);

    setTimeout(() => {
        ScrollTrigger.refresh();
    }, 300);
});

onBeforeUnmount(() => {
    window.removeEventListener('mousemove', handleGlobalMouseMove);
    ScrollTrigger.getAll().forEach(t => t.kill());
    if (cursorRef.value) gsap.set(cursorRef.value, { display: 'none' });
});
</script>

<style scoped>
.heropage2-root {
    width: 100%;
    overflow-x: hidden;
    position: relative;
    scrollbar-width: none;
    background-color: #010101;
    cursor: none !important;
}

.hero-section,
.video-wrapper,
.black-curve-bg {
    cursor: none !important;
}

.hero-section {
    height: 100vh;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 1;
}

.hero-content {
    position: relative;
    z-index: 2;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.hero-title {
    position: absolute;
    width: 60vw;
    left: 50%;
    top: 12%;
    transform: translateX(-50%);
    font-weight: 400;
    font-size: 3.8em;
    line-height: 80%;
    color: #FFFFFF;
    text-align: center;
    z-index: 5;
}

.word-wrapper {
    display: inline-block;
    overflow: hidden;
    vertical-align: bottom;
}

.word {
    display: inline-block;
}

.gt-alpina-font {
    font-family: 'GT Alpina Fine Trial', serif;
    font-weight: 300;
    font-style: italic;
}

.highlight-text-wrapper {
    position: absolute;
    width: 380px;
    height: 210px;
    left: 50%;
    top: 39%;
    transform: translateX(-50%) rotate(-6.55deg);
    z-index: 10;
    opacity: 0;
    pointer-events: none;
}

.svg-text {
    font-family: 'Xtradex', sans-serif;
    font-size: 18em;
    fill: #20a1a7;
}

.video-transition-container {
    width: 100%;
    height: auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 50;
    pointer-events: none;
    margin-bottom: 15vh;
    margin-top: -53vh;
}

.black-curve-bg {
    position: absolute;
    bottom: -15vh;
    left: 0;
    width: 100%;
    height: 180vh;
    background-color: #000;
    z-index: 1;
    clip-path: ellipse(20% 0% at 50% 100%);
    pointer-events: auto;
}

.video-wrapper {
    position: relative;
    z-index: 5;
    width: 45%;
    height: 500px;
    overflow: hidden;
    border-radius: 25px;
    pointer-events: auto;
}

.transition-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-overlay {
    position: absolute;
    inset: 0;
    z-index: 10;
    opacity: 1;
    background: linear-gradient(to bottom, rgb(0, 0, 0) 0%, rgba(0, 0, 0, 0) 60%);
    display: flex;
    justify-content: center;
    padding-top: 5vh;
    pointer-events: none;
}

.scroll-indicator {
    height: fit-content;
}

.scroll-pill {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.mouse-icon {
    width: 20px;
    height: 32px;
    border: 1.5px solid rgba(255, 255, 255, 0.4);
    border-radius: 10px;
    position: relative;
    display: flex;
    justify-content: center;
}

.mouse-icon .wheel {
    width: 3px;
    height: 6px;
    background: white;
    border-radius: 2px;
    position: absolute;
    top: 6px;
    animation: mouse-wheel-anim 2s infinite cubic-bezier(0.65, 0, 0.35, 1);
}

.scroll-text {
    font-size: 0.7rem;
    font-weight: 500;
    letter-spacing: 0.35em;
    color: rgba(255, 255, 255, 0.9);
    text-transform: uppercase;
    white-space: nowrap;
}

@keyframes mouse-wheel-anim {
    0% {
        transform: translateY(0);
        opacity: 0;
    }

    20% {
        opacity: 1;
    }

    50% {
        transform: translateY(12px);
        opacity: 0.5;
    }

    100% {
        transform: translateY(15px);
        opacity: 0;
    }
}

.custom-cursor {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 100;
    cursor: none !important;
    width: 9vw;
    height: 20vh;
    padding: 1.5vw !important;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #fcfcfc;
    font-size: 0.9rem;
    font-weight: 500;
    text-transform: uppercase;
    text-align: center;
    letter-spacing: 1px;
    line-height: 1.1;
    transform: translate(-50%, -50%) scale(0);
    opacity: 0;
    cursor: pointer;
    background-color: #8A38F5;
    transition: background-color 0.3s;
    will-change: transform;
}
</style>