<template>
    <div class="intro-loader" ref="container">
        <ShaderBackground class="shader-bg" :colors="colors" />

        <div class="loader-content">
            <div class="logo-wrapper" ref="logoRef">
                <img :src="logoUrl" alt="ShiftUp Logo" class="loader-logo" />
            </div>

            <div class="text-content" ref="textRef">
                <p class="loader-subtitle" ref="subTitleRef">
                    CHARGEMENT DE VOTRE EXPÉRIENCE
                </p>
            </div>

            <div class="progress-wrapper" ref="progressWrapperRef">
                <svg viewBox="0 0 400 20" preserveAspectRatio="none" class="wave-svg">
                    <defs>
                        <path id="wavePath"
                            d="M 0 10 Q 25 0 50 10 T 100 10 T 150 10 T 200 10 T 250 10 T 300 10 T 350 10 T 400 10" />
                        <mask id="waveMask">
                            <rect x="0" y="0" width="0" height="20" fill="white" ref="progressBarRef" />
                        </mask>
                    </defs>

                    <use xlink:href="#wavePath" class="wave-bg" />

                    <g mask="url(#waveMask)">
                        <use xlink:href="#wavePath" class="wave-progress" />
                    </g>
                </svg>
            </div>

            <div class="once-notice" ref="onceNoticeRef">
                Ne vous inquiétez pas , ceci est un chargement unique
            </div>
        </div>

        <div class="footer-left" ref="footerLeftRef">
            <div class="footer-line"></div>
            <span class="footer-text">2026</span>
        </div>

        <div class="footer-right" ref="footerRightRef">
            <span class="footer-text">© EPIK BRAND</span>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { gsap } from 'gsap';
import ShaderBackground from './ShaderBackground.vue';

const emit = defineEmits(['complete']);

const container = ref(null);
const logoRef = ref(null);
const textRef = ref(null);
const subTitleRef = ref(null);
const progressWrapperRef = ref(null);
const progressBarRef = ref(null);
const onceNoticeRef = ref(null);
const footerLeftRef = ref(null);
const footerRightRef = ref(null);

const logoUrl = '/images/logo-site-blanc.png';

const colors = {
    primary: '#202020',
    secondary: '#1A888D',
    accent: '#202020',
    dark: '#000000'
};

onMounted(() => {
    // Prevent scrolling immediately
    document.body.style.overflow = 'hidden';

    // Initial states for elements that will be animated
    gsap.set([logoRef.value, subTitleRef.value, progressWrapperRef.value, onceNoticeRef.value, footerLeftRef.value, footerRightRef.value], {
        opacity: 0,
        y: 20
    });
    gsap.set(logoRef.value, { scale: 0.95, y: 0 });

    const tl = gsap.timeline({
        onComplete: () => {
            const exitTl = gsap.timeline({
                onComplete: () => {
                    document.body.style.overflow = '';
                    emit('complete');
                }
            });

            exitTl.to([logoRef.value, textRef.value, progressWrapperRef.value, onceNoticeRef.value, footerLeftRef.value, footerRightRef.value], {
                y: -20,
                opacity: 0,
                duration: 0.8,
                stagger: 0.05,
                ease: 'power3.in'
            })
                .to(container.value, {
                    yPercent: -100,
                    duration: 1.2,
                    ease: 'power4.inOut'
                }, '-=0.4');
        }
    });

    // In Animation
    tl.to(logoRef.value, {
        opacity: 1,
        scale: 1,
        duration: 1.5,
        ease: 'power4.out'
    })
        .to(subTitleRef.value, {
            opacity: 1,
            y: 0,
            duration: 1,
            ease: 'power3.out'
        }, '-=1.2')
        .to(progressWrapperRef.value, {
            opacity: 1,
            y: 0,
            duration: 1
        }, '-=0.8')
        .to(progressBarRef.value, {
            attr: { width: 400 },
            duration: 15,
            ease: 'power1.inOut'
        }, '-=0.4')
        .to([onceNoticeRef.value, footerLeftRef.value, footerRightRef.value], {
            opacity: 1,
            y: 0,
            duration: 0.8,
            stagger: 0.1
        }, '-=2.8');

    // Continuous wave motion
    gsap.to('.wave-svg', {
        x: -20,
        duration: 2,
        repeat: -1,
        yoyo: true,
        ease: 'sine.inOut'
    });
});
</script>

<style scoped>
.intro-loader {
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #000000;
    overflow: hidden;
    margin: 0 !important;
    top: 0 !important;
    left: 0 !important;
}

.shader-bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

.loader-content {
    position: relative;
    z-index: 20;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    max-width: 40rem;
    padding: 0 2rem;
    text-align: center;
}

.logo-wrapper {
    margin-bottom: 3rem;
    filter: drop-shadow(0 0 30px rgba(255, 255, 255, 0.1));
}

.loader-logo {
    width: 8rem;
    height: auto;
}

@media (min-width: 768px) {
    .loader-logo {
        width: 16rem;
    }
}

.text-content {
    margin-bottom: 4rem;
}

.loader-title {
    font-size: 1.5rem;
    font-weight: 300;
    color: #ffffff;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin-bottom: 1rem;
}

@media (min-width: 768px) {
    .loader-title {
        font-size: 1.875rem;
    }
}

.loader-subtitle {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.938);
    letter-spacing: 0.2em;
    font-weight: 300;
}

@media (min-width: 768px) {
    .loader-subtitle {
        font-size: 1rem;
    }
}

.progress-wrapper {
    width: 20rem;
    height: 30px;
    position: relative;
    overflow: visible;
    display: flex;
    align-items: center;
}

@media (min-width: 768px) {
    .progress-wrapper {
        width: 16rem;
    }
}

.wave-svg {
    width: 100%;
    height: 100%;
    overflow: visible;
}

.wave-bg {
    fill: none;
    stroke: rgba(255, 255, 255, 0.1);
    stroke-width: 2;
}

.wave-progress {
    fill: none;
    stroke: #ffffff;
    stroke-width: 2;
    stroke-linecap: round;
    filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.6));
}

.once-notice {
    margin-top: 1rem;
    font-size: 10px;
    color: rgba(255, 255, 255, 0.919);
    text-transform: uppercase;
    letter-spacing: 0.3em;
}

.footer-left {
    position: absolute;
    bottom: 3rem;
    left: 3rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.footer-line {
    width: 2rem;
    height: 1px;
    background-color: rgba(255, 255, 255, 0.604);
}

.footer-right {
    position: absolute;
    bottom: 3rem;
    right: 3rem;
}

.footer-text {
    font-size: 10px;
    color: rgba(255, 255, 255, 0.4);
    letter-spacing: 0.2em;
}
</style>
