<template>
    <div class="heropage2-root no-global-reveal" ref="rootRef">
        <div class="hero-section-bg">
            <ShaderBackground :colors="themeColors" class="shader-inner-bg">
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
                                {{ $t('Heropage.libre') }}
                            </text>
                        </svg>
                    </div>
                </div>
            </ShaderBackground>
        </div>

        <div class="video-transition-container" ref="videoContainerRef">
            <div class="black-curve-bg" ref="curveBgRef"></div>

            <div class="video-wrapper" ref="videoWrapperRef">
                <!-- Admin Edit Button -->
                <div v-if="isAdmin" class="admin-edit-video" @click.stop="openEditModal">
                    <LiquidGlass borderRadius="50%" center noBorder class="edit-btn-glass">
                        <i class="fas fa-edit"></i>
                    </LiquidGlass>
                </div>
                <div class="video-overlay" ref="videoOverlayRef">
                    <div class="scroll-indicator">
                        <div class="scroll-pill">
                            <span class="scroll-text">{{ $t('Heropage2.faites_dfiler') }}</span>
                            <div class="mouse-icon">
                                <span class="wheel"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <video class="transition-video" ref="videoRef" :muted="isMuted" loop playsinline autoplay preload="metadata"
                    :src="currentVideoSrc">
                </video>

                <div class="mute-control-container" @click="isMuted = !isMuted">
                    <LiquidGlass borderRadius="50px" center noBorder class="mute-glass-btn">
                        <div class="mute-btn-content">
                            <i v-if="isMuted" class="fas fa-volume-mute"></i>
                            <i v-else class="fas fa-volume-up"></i>
                        </div>
                    </LiquidGlass>
                </div>
            </div>
        </div>

        <div class="custom-cursor" ref="cursorRef" @click="handleCursorClick">
            <span ref="cursorTextRef">{{ $t('Heropage2.voir_programme') }}</span>
        </div>

        <!-- Premium Modal for Video Update -->
        <PremiumModal :isOpen="isModalOpen" @close="isModalOpen = false" header="Modifier la Vidéo Hero" dark width="600px">
            <form @submit.prevent="submitVideoUpdate" class="video-update-form">
                <div class="form-group">
                    <label>Lien Cloudinary de la vidéo</label>
                    <input type="url" v-model="form.video_url" placeholder="https://res.cloudinary.com/..." required class="premium-input" />
                </div>
                <div class="form-actions">
                    <PremiumButton type="submit" text="Mettre à jour" :disabled="form.processing" />
                </div>
            </form>
        </PremiumModal>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { router, usePage, useForm } from '@inertiajs/vue3';
import ShaderBackground from '../ui/ShaderBackground.vue';
import LiquidGlass from '../ui/LiquidGlass.vue';
import PremiumModal from '../ui/PremiumModal.vue';
import PremiumButton from '../ui/PremiumButton.vue';

const props = defineProps({
    heroVideo: {
        type: String,
        default: null
    }
});

gsap.registerPlugin(ScrollTrigger);

const page = usePage();
const $t = (key) => page.props.translations?.[key] ?? key;

const themeColors = {
    primary: '#202020',
    secondary: '#1A888D',
    accent: '#1A888D',
    dark: '#000000'
};
const defaultVideoSrc = "https://res.cloudinary.com/dzgdjei0h/video/upload/v1766751142/Shift_Up_1_Workflow_1_lzre8i.mp4";
const currentVideoSrc = computed(() => props.heroVideo || defaultVideoSrc);
const isMuted = ref(true);

const isAdmin = computed(() => usePage().props.auth.user?.Role === 'admin');
const isModalOpen = ref(false);

const form = useForm({
    video_url: props.heroVideo || defaultVideoSrc
});

const openEditModal = () => {
    isModalOpen.value = true;
};

const submitVideoUpdate = () => {
    form.post('/admin/heropage-video', {
        onSuccess: () => {
            isModalOpen.value = false;
        }
    });
};

const titleWords = computed(() => {
    const fullTitle = $t('Heropage2.title') !== 'Heropage2.title'
        ? $t('Heropage2.title')
        : 'Propulsez votre entreprise au niveau supérieur et devenez un entrepreneur';

    const bogartString = $t('Heropage2.bogart_words') !== 'Heropage2.bogart_words'
        ? $t('Heropage2.bogart_words')
        : 'votre, entreprise, devenez, un, entrepreneur';

    const bogartWords = bogartString.split(',').map(w => w.trim().toLowerCase());

    return fullTitle.trim().split(/\s+/).map(word => {
        const cleanWord = word.replace(/[.,!?;:]/g, "").toLowerCase();
        return {
            text: word,
            isBogart: bogartWords.includes(cleanWord)
        };
    });
});

const splitText = (text) => text.split('');

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
    const isMobile = window.innerWidth <= 768;

    gsap.fromTo(".word", { y: '110%' }, { y: '0%', duration: 1.2, ease: "power4.out", stagger: 0.05, delay: 0.2 });
    gsap.fromTo(".highlight-text-wrapper", { opacity: 0, scale: 0.9 }, { opacity: 1, scale: 1, duration: 1, ease: "power3.out", transformOrigin: "center center", delay: 1.2 });

    if (isMobile) {
        const videoTl = gsap.timeline({
            scrollTrigger: {
                trigger: videoContainerRef.value,
                start: "top 85%",
                end: "top -10%",
                scrub: 1,
            }
        });

        videoTl.fromTo(curveBgRef.value,
            { clipPath: 'ellipse(100% 20% at 50% 100%)', y: 0 },
            { clipPath: 'ellipse(150% 120% at 50% 100%)', y: -40, ease: "power1.inOut" }
        ).fromTo(videoWrapperRef.value,
            { borderRadius: "0px", y: 150 },
            { borderRadius: "30px", y: -10, ease: "power2.out" },
            0
        );

        videoTl.fromTo(videoOverlayRef.value,
            { opacity: 1 },
            { opacity: 0, duration: 0.3, ease: "power2.in" },
            0
        );
    } else {
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
        elementUnderMouse?.closest('.robot-container') ||
        elementUnderMouse?.closest('.admin-edit-video');

    let isTouchingMute = false;
    const muteBtn = document.querySelector('.mute-control-container');
    if (muteBtn && isInside) {
        const muteRect = muteBtn.getBoundingClientRect();
        const radius = (window.innerWidth * 0.045);
        
        const closestX = Math.max(muteRect.left, Math.min(e.clientX, muteRect.right));
        const closestY = Math.max(muteRect.top, Math.min(e.clientY, muteRect.bottom));
        
        const distanceX = e.clientX - closestX;
        const distanceY = e.clientY - closestY;
        const distanceSquared = (distanceX * distanceX) + (distanceY * distanceY);
        
        if (distanceSquared < (radius * radius)) {
            isTouchingMute = true;
        }
    }

    const isOverInteractive = isOverHeader || isTouchingMute;

    if (!isInside || isOverInteractive) {
        gsap.to(cursorRef.value, { opacity: 0, scale: 0, duration: 0.2 });
        if (rootRef.value) rootRef.value.style.cursor = isOverInteractive ? 'pointer' : 'auto';
        return;
    } else {
        gsap.to(cursorRef.value, { opacity: 1, scale: 1, duration: 0.2 });
        if (rootRef.value) rootRef.value.style.cursor = 'none';
    }

    const isOverVideo = videoWrapperRef.value?.contains(elementUnderMouse) ||
        curveBgRef.value?.contains(elementUnderMouse);

    const newMode = isOverVideo ? 'evenements' : 'programmes';

    if (newMode !== currentMode) {
        currentMode = newMode;
        if (currentMode === 'evenements') {
            cursorTextRef.value.textContent = $t('Heropage2.voir_les_evenements');
            gsap.to(cursorRef.value, { backgroundColor: '#000', duration: 0.2 });
        } else {
            cursorTextRef.value.textContent = $t('Heropage2.voir_nos_programmes');
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
    const isMobile = window.innerWidth <= 768;

    if (!isMobile) {
        xSetter = gsap.quickSetter(cursorRef.value, "x", "px");
        ySetter = gsap.quickSetter(cursorRef.value, "y", "px");
        window.addEventListener('mousemove', handleGlobalMouseMove);
    }

    initAnimations();

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
    cursor: none;
}

.hero-section-bg,
.video-wrapper,
.black-curve-bg {
    cursor: inherit;
}

.hero-section-bg {
    height: 100vh;
    width: 100%;
    position: relative;
    z-index: 10;
}

.shader-inner-bg {
    height: 100%;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
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
    font-size: 4vw;
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
    font-family: 'GT Alpina', serif;
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
    z-index: 20;
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

.mute-control-container {
    position: absolute;
    bottom: 2.5vh;
    left: 2vw;
    z-index: 30;
    cursor: pointer;
    pointer-events: auto;
}

.mute-glass-btn {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.mute-glass-btn:hover {
    transform: scale(1.1);
}

.mute-btn-content {
    color: white;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
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
    font-size: 0.6vw;
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
    height: 19vh;
    padding: 1.5vw !important;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #fcfcfc;
    font-size: 0.8vw;
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

@media (max-width: 1200px) {
    .hero-title {
        width: 80vw;
        font-size: 3.2em;
    }
}

@media (max-width: 768px) {
    .heropage2-root {
        cursor: auto !important;
        overflow: visible !important;
        padding-bottom: 30vh;
    }

    .hero-section-bg {
        height: 100vh !important;
        min-height: 100vh !important;
        z-index: 1 !important;
        visibility: visible !important;
    }

    .shader-inner-bg {
        height: 100%;
        width: 100%;
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    .hero-title {
        width: 90vw;
        font-size: 2.2em;
        top: 20%;
        line-height: 1;
        z-index: 10;
    }

    .highlight-text-wrapper {
        width: 250px;
        height: 150px;
        top: 35%;
        z-index: 11;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .svg-text {
        font-size: 14em;
    }

    .video-transition-container {
        margin-top: -45vh;
        margin-bottom: 5vh;
        z-index: 20;
        position: relative;
        pointer-events: auto !important;
    }

    .black-curve-bg {
        height: 150vh;
        top: -95vh;
        clip-path: ellipse(15% 0% at 50% 100%);
        visibility: visible !important;
        display: block !important;
        pointer-events: none;
    }

    .video-wrapper {
        width: 95% !important;
        height: 300px;
        margin-top: 0vh;
        /* On remet votre valeur exacte */
        z-index: 25;
        pointer-events: auto !important;
    }

    .custom-cursor {
        display: none !important;
    }

    .scroll-indicator {
        display: flex !important;
        z-index: 30;
        transform: scale(0.8);
    }

    .video-overlay {
        padding-top: 2vh !important;
    }
}

@media (max-width: 480px) {
    .hero-title {
        font-size: 1.8rem !important;
        top: 15%;
    }

    .highlight-text-wrapper {
        width: 180px;
        height: 100px;
        top: 35%;
    }

    .svg-text {
        font-size: 10em;
    }

    .video-wrapper {
        height: 250px;
        border-radius: 15px !important;
    }

    .video-transition-container {
        margin-top: -50vh;
    }
}

/* Admin Edit Video Button */
.admin-edit-video {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 100;
    cursor: pointer;
    pointer-events: auto;
}

.edit-btn-glass {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #fff;
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.edit-btn-glass:hover {
    transform: scale(1.15);
}

/* Video Update Form */
.video-update-form {
    padding-top: 1.5rem;
}

.video-update-form .form-group {
    margin-bottom: 1.5rem;
}

.video-update-form label {
    display: block;
    font-size: 0.9rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: rgba(255, 255, 255, 0.7);
}

.video-update-form .premium-input {
    width: 100%;
    padding: 14px 18px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.05);
    color: #fff;
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    box-sizing: border-box;
}

.video-update-form .premium-input:focus {
    border-color: #1A888D;
    box-shadow: 0 0 0 3px rgba(26, 136, 141, 0.2);
}

.video-update-form .premium-input::placeholder {
    color: rgba(255, 255, 255, 0.3);
}

.video-update-form .form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .admin-edit-video {
        top: 1vh;
        right: 3vw;
    }

    .edit-btn-glass {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}
</style>
