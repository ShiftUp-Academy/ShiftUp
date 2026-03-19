<template>
    <div class="hero-page-container no-global-reveal" @mousemove="handleMouseMove"
        @mouseover="showCursor = true; animateCursorIn()" @mouseleave="showCursor = false; animateCursorOut()"
        @click="navigateToProgrammes">

        <video class="hero-video" ref="videoRef" muted loop playsinline autoplay preload="metadata"
            :src="heroVideoSrc"></video>

        <div class="hero-overlay">
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
                        <defs>
                            <linearGradient id="textGradient" x1="0%" y1="0%" x2="200%" y2="0%">
                                <stop offset="0%" class="grad-stop stop-1" style="stop-color:#0E7EC3;stop-opacity:1" />
                                <stop offset="50%" class="grad-stop stop-2" style="stop-color:#8A38F5;stop-opacity:1" />
                                <stop offset="100%" class="grad-stop stop-3"
                                    style="stop-color:#0E7EC3;stop-opacity:1" />
                            </linearGradient>
                        </defs>

                        <g clip-path="url(#text-reveal-clip)">
                            <text x="50%" y="70%" dominant-baseline="middle" text-anchor="middle" class="svg-text"
                                fill="url(#textGradient)">
                                {{ $t('Heropage.libre') }}
                            </text>
                        </g>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="custom-cursor" :style="{ left: cursorPosition.x + 'px', top: cursorPosition.y + 'px' }">
        {{ $t('Heropage.voir_nos_programmes') }}
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, reactive } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const $t = (key) => page.props.translations[key] ?? key;

function navigateToProgrammes() {
    router.visit('/programmes');
}

gsap.registerPlugin(ScrollTrigger);

const videoRef = ref(null);

const heroVideoSrc = "https://res.cloudinary.com/dzgdjei0h/video/upload/v1766751142/Shift_Up_1_Workflow_1_lzre8i.mp4";

const titleWords = computed(() => {
    const fullTitle = $t('Heropage.title') !== 'Heropage.title'
        ? $t('Heropage.title')
        : 'Propulsez votre entreprise au niveau supérieur et devenez un entrepreneur';

    const bogartString = $t('Heropage.bogart_words') !== 'Heropage.bogart_words'
        ? $t('Heropage.bogart_words')
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

// --- CURSEUR LOGIC (Non Modifié) ---
const cursorPosition = reactive({ x: 0, y: 0 });
const showCursor = ref(false);
let stopTimeout = null;

function handleMouseMove(event) {
    // 1. Mise à jour de la position (effet de traînée fluide)
    gsap.to(cursorPosition, {
        x: event.clientX,
        y: event.clientY,
        duration: 0.25,
        ease: "power3.out"
    });

    // 2. EFFET DE GROSSISSEMENT AU MOUVEMENT
    gsap.to(".custom-cursor", {
        scale: 1.1,
        opacity: 1,
        duration: 0.2,
        ease: "power2.out",
        overwrite: "auto"
    });
    clearTimeout(stopTimeout);

    stopTimeout = setTimeout(() => {
        if (showCursor.value) {
            gsap.to(".custom-cursor", {
                scale: 1,
                opacity: 0,
                duration: 0.8,
                ease: "power2.inOut"
            });
        }
    }, 100); // 100ms d'arrêt avant de rétrécir
}

function animateCursorIn() {
    if (showCursor.value) {
        gsap.to(".custom-cursor", {
            opacity: 1,
            scale: 1,
            duration: 0.4,
            ease: "power3.out"
        });
    }
}

function animateCursorOut() {
    clearTimeout(stopTimeout);
    gsap.to(".custom-cursor", {
        scale: 0.1,
        opacity: 0,
        duration: 0.4,
        ease: "power3.in"
    });
}

function animateGradientScroll() {
    const gradientTl = gsap.timeline({ repeat: -1, defaults: { ease: "none", duration: 8 } });

    // Animer la position X du dégradé de 100% (Décalage du dégradé)
    gradientTl.fromTo("#textGradient",
        { attr: { x1: '0%', x2: '200%' } },
        { attr: { x1: '-100%', x2: '100%' } }
    );
}


function initAnimations() {
    gsap.fromTo(".word", { y: '110%' }, { y: '0%', duration: 1.2, ease: "power4.out", stagger: 0.05, delay: 0.2 });

    const svgTextTl = gsap.timeline({ delay: 1.2 });
    svgTextTl.fromTo(".highlight-text-wrapper", { opacity: 0, scale: 0.9 }, { opacity: 1, scale: 1, duration: 1, ease: "power3.out", transformOrigin: "center center" })
        .to("#clip-rect", { width: 1000, duration: 2, ease: "power2.inOut", }, "<0.1")
        .to(".svg-text", { duration: 0.8, ease: "power2.out", }, "-=0.5");

    const triggerSettings = {
        trigger: ".hero-page-container",
        start: "top top",
        end: "bottom top",
        scrub: true,
    };

    gsap.to(".hero-video", {
        y: 500,
        ease: "none",
        force3D: true,
        scrollTrigger: triggerSettings
    });

    gsap.to(".hero-title", {
        y: 200,
        ease: "power3.out",
        opacity: 0,
        force3D: true,
        scrollTrigger: triggerSettings
    });

    // Déplacement du Texte "Libre" (s'éloigne plus rapidement)
    gsap.to(".highlight-text-wrapper", {
        y: 200,
        ease: "power3.out",
        force3D: true,
        scrollTrigger: triggerSettings
    });
    // --- 3. Playback Optimization ---
    const vST = ScrollTrigger.create({
        trigger: ".hero-page-container",
        start: "top top",
        end: "bottom top",
        onEnter: () => { if (videoRef.value) videoRef.value.play().catch(() => { }); },
        // On attend que la section soit bien sortie (50vh plus loin)
        onLeave: () => {
            const isFarEnough = window.scrollY > (document.querySelector('.hero-page-container').offsetHeight + window.innerHeight * 0.5);
            if (isFarEnough && videoRef.value) videoRef.value.pause();
        },
        onEnterBack: () => { if (videoRef.value) videoRef.value.play().catch(() => { }); },
    });
}

onMounted(() => {
    initAnimations();
    animateGradientScroll();

    // Initialisation du curseur en petite taille et invisible
    gsap.set(".custom-cursor", { scale: 0.1, opacity: 0 });
    window.addEventListener('resize', onWindowResize);
});

onBeforeUnmount(() => {
    // Kill only ScrollTriggers related to hero-page-container
    ScrollTrigger.getAll().forEach(trigger => {
        if (trigger.vars && trigger.vars.trigger === ".hero-page-container") {
            trigger.kill();
        }
    });
    clearTimeout(stopTimeout);
    window.removeEventListener('resize', onWindowResize);
});

function onWindowResize() { /* Laisser vide ou gérer la responsivité si nécessaire */ }
</script>

<style scoped>
/* Conteneur Principal */
.hero-page-container {
    width: 100%;
    height: 100vh;
    position: relative;
    overflow: hidden;
    cursor: none;
    z-index: 1;
    /* Cache le curseur par défaut */
}

/* Vidéo de Fond */
.hero-video {
    position: absolute;
    top: 50%;
    left: 50%;
    min-width: 100%;
    min-height: 100%;
    width: auto;
    height: auto;
    z-index: 1;
    transform: translate(-50%, -50%);
    object-fit: cover;
    filter: brightness(0.6);
}

/* --- HERO CONTENT & TITRES --- */
.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10;
    pointer-events: auto;
    /* Permet la détection du mousemove */
}

.hero-content {
    max-width: 1400px;
    width: 100%;
    height: 100%;
    position: relative;
    pointer-events: none;
    /* Pour que les clics passent à travers le contenu */
}

.hero-title {
    position: absolute;
    width: 60vw;
    left: 50%;
    top: 20%;
    /* La transformation initiale sert de base au mouvement de parallaxe */
    transform: translateX(-50%);
    font-weight: 400;
    font-size: 4em;
    line-height: 85%;
    color: #FFFFFF;
    text-shadow: 0 0 5px rgba(0, 0, 0, 0.099);
    text-align: center;
    z-index: 12;
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


/* --- HIGHLIGHT SVG & CLIP PATH "LIBRE" --- */
.highlight-text-wrapper {
    position: absolute;
    width: 419px;
    height: 240px;
    left: 50%;
    top: 50%;
    transform: translateX(-50%) rotate(-6.55deg);
    z-index: 15;
    opacity: 0;
    transform-origin: center center;
    pointer-events: none;
}

.svg-text {
    font-family: 'Xtradex', sans-serif;
    /* Police d'accentuation */
    font-weight: 400;
    font-size: 19em;
    line-height: 100%;
    stroke: none;
}

/* --- CURSEUR PERSONNALISÉ --- */
.custom-cursor {
    position: fixed;
    pointer-events: none;
    z-index: 100;
    width: 9vw;
    height: 20vh;
    padding: 1.5vw !important;
    border-radius: 50%;
    background-color: #8A38F5;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #fcfcfc;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    text-align: center;
    letter-spacing: 1px;
    line-height: 1.1;

    /* Le transform gère le centrage du curseur sous la souris */
    transform: translate(-50%, -50%) scale(0.1);
    opacity: 0;
}
</style>
