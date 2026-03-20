<template>
    <div class="page-transition-overlay" ref="container">
        <div class="transition-bg" ref="bg">
            <div class="content-wrapper" ref="contentWrapper">
                <div class="logo-mask-wrapper" ref="logoWrapper">
                    <div class="gradient-bg"></div>
                </div>

                <div class="discovery-card" v-if="currentItem" ref="cardRef">
                    <div class="card-image" :style="{ backgroundImage: `url(${formatImgUrl(currentItem.image)})` }"></div>
                    <div class="card-overlay">
                        <span class="category">{{ currentItem.category }}</span>
                        <h4 class="title">{{ currentItem.title }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { gsap } from 'gsap';
import { usePage } from '@inertiajs/vue3';

const container = ref(null);
const bg = ref(null);
const logoWrapper = ref(null);
const cardRef = ref(null);
const contentWrapper = ref(null);

let isTransitioning = false;
let startAnimationPromise = null;

const page = usePage();

const defaultItems = [
    { category: 'Programme', title: 'Le Parcours Excellence', image: '/images/Programmes/Plan de travail1.png' },
    { category: 'Live', title: 'Sessions Interactives', image: '/images/Bibliothèque/Zoom.jpg' },
    { category: 'Article', title: 'Le Futur de l\'Expertise', image: '/images/Bibliothèque/aesthetics graphic designer.jpg' },
    { category: 'Offre', title: 'Accès Masterclass', image: '/images/Programmes/Plan de travail3.png' },
    { category: 'Conseil', title: 'Vision ShiftUp', image: '/images/Bibliothèque/image-coaching.webp' }
];

const currentItem = ref(defaultItems[0]);

const formatImgUrl = (url) => {
    if (!url) return '/images/catégorie.jpg';
    if (url.startsWith('http') || url.startsWith('data:')) return url;
    return `/${url.replace(/^\/+/, '')}`;
};

onMounted(() => {
    const dbItems = page.props.discoveryItems || [];
    const allItems = [...dbItems, ...defaultItems];
    allItems.forEach(item => {
        const img = new Image();
        img.src = formatImgUrl(item.image);
    });
});

const startTransition = () => {
    if (isTransitioning) return startAnimationPromise;
    isTransitioning = true;

    const dbItems = page.props.discoveryItems || [];
    const source = (dbItems.length > 0) ? dbItems : defaultItems;
    currentItem.value = source[Math.floor(Math.random() * source.length)];

    startAnimationPromise = new Promise((resolve) => {
        if (!container.value) { resolve(); return; }

        gsap.set(container.value, { display: 'flex', autoAlpha: 1, pointerEvents: 'all' });
        
        gsap.set(bg.value, { 
            yPercent: 120,
            opacity: 1,
            boxShadow: '0 0 250px 150px #141414' 
        });
        
        gsap.set(logoWrapper.value, { opacity: 0, y: 10, scale: 0.95 });
        gsap.set(cardRef.value, { opacity: 0, y: 40, scale: 0.95 });

        const tl = gsap.timeline({ onComplete: resolve });

        // 1. Slide In Panel
        tl.to(bg.value, {
            yPercent: 0,
            duration: 0.85,
            ease: "expo.out"
        })
        // 2. Clear Shadow
        .to(bg.value, {
            boxShadow: '0 0 0px 0px #141414',
            duration: 0.4,
            ease: "power2.inOut"
        }, "-=0.3")
        // 3. Logo Reveal (Tight move)
        .to(logoWrapper.value, {
            opacity: 1,
            y: -30, 
            scale: 1,
            duration: 0.7,
            ease: "power3.out"
        }, "-=0.2")
        // 4. Discovery Card Reveal
        .to(cardRef.value, {
            opacity: 1,
            y: 0,
            scale: 1,
            duration: 0.8,
            ease: "back.out(1.1)"
        }, "-=0.5");
    });

    return startAnimationPromise;
};

const endTransition = async () => {
    if (!isTransitioning) return;

    await new Promise(r => setTimeout(r, 450));
    if (startAnimationPromise) await startAnimationPromise;

    const tl = gsap.timeline({
        onComplete: () => {
            gsap.set(container.value, { display: 'none', autoAlpha: 0, pointerEvents: 'none' });
            isTransitioning = false;
            startAnimationPromise = null;
        }
    });

    // 1. Elements fade and shrink upward (Compact)
    tl.to([logoWrapper.value, cardRef.value], {
        opacity: 0,
        y: (i) => i === 0 ? -60 : -15, // Compact exit
        scale: 0.95,
        duration: 0.8,
        ease: "power2.in"
    })
    .to(bg.value, {
        boxShadow: '0 0 250px 150px #141414',
        duration: 0.6,
        ease: "power2.out"
    }, "-=0.2")
    .to(bg.value, {
        yPercent: -120,
        duration: 1.0,
        ease: "expo.inOut"
    }, "-=0.3");
};

defineExpose({
    startTransition,
    endTransition
});
</script>

<style scoped>
.page-transition-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 2147483647;
    pointer-events: none;
    display: none;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.transition-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: #141414;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    outline: none;
}

.content-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
}

.logo-mask-wrapper {
    width: min(250px, 45vw);
    height: 120px;
    -webkit-mask-image: url('/images/logo-site-blanc.png');
    -webkit-mask-size: contain;
    -webkit-mask-position: center;
    -webkit-mask-repeat: no-repeat;
    mask-image: url('/images/logo-site-blanc.png');
    mask-size: contain;
    mask-position: center;
    mask-repeat: no-repeat;
    overflow: hidden;
}

.discovery-card {
    width: min(750px, 92vw);
    height: min(480px, 55vh);
    background: #202020;
    border-radius: 35px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.card-image {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    transition: transform 0.6s ease;
}

.discovery-card:hover .card-image {
    transform: scale(1.05);
}

.card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0) 60%);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 15px;
    padding-bottom: 0;
}

.category {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #F7B455;
    font-weight: 500;
    margin-bottom: 4px;
}

.discovery-card .title {
    color: white;
    font-size: 1.2rem;
    font-weight: 400;
    margin: 0;
    opacity: 0.9;
    line-height: 1.3;
}

@media (max-width: 768px) {
    .logo-mask-wrapper {
        width: 180px;
        height: 80px;
    }

    .discovery-card {
        width: 92vw;
        height: 45vh; 
        border-radius: 25px;
    }

    .card-overlay {
        padding: 18px;
    }

    .discovery-card .title {
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .logo-mask-wrapper {
        width: 140px;
        height: 60px;
    }

    .discovery-card {
        height: 35vh;
    }

    .category {
        font-size: 0.65rem;
        letter-spacing: 1px;
    }
}

.gradient-bg {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #202020, #1A888D, #F7B455);
    background-size: 300% 300%;
    animation: gradientShift 4s ease infinite;
}

@keyframes gradientShift {
    0% {
        background-position: 0% 50%;
    }

    50% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0% 50%;
    }
}
</style>
