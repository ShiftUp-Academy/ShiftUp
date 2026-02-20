<template>
    <section class="bento-section">
        <div class="custom-cursor" ref="cursorRef" :style="{ backgroundColor: cursorBgColor }">
            <span :style="{ color: cursorTextColor }">Explorer</span>
        </div>

        <div class="bento-grid" ref="bentoGrid">

            <!-- 1. Catégories de programme -->
            <div class="card card-categories">
                <div class="card-bg-container">
                    <img :src="imgCategoriesBg" class="card-img blur-effect" />
                    <div class="overlay-base"></div>
                    <div class="overlay-hover"></div>
                </div>
                <div class="card-body">
                    <div class="cat-header">
                        <span class="sub-label">CATÉGORIES DE PROGRAMME</span>
                        <p class="cat-desc">Naviguez pour voir tous les programmes que ShiftUp propose</p>
                    </div>
                    <div class="cat-list">
                        <Link v-for="link in categoryLinks" :key="link.href" :href="link.href"
                            class="cat-link item-cat">
                            <span>{{ link.label }}</span>
                            <img :src="ArrowIcon" class="btn-icon">
                        </Link>
                    </div>
                </div>
            </div>

            <!-- 2. Coaching -->
            <Link href="/coaching" class="card card-coaching" @mousemove="moveCursor"
                @mouseenter="showCursor('#a71543')" @mouseleave="hideCursor">
                <div class="card-bg-container">
                    <img :src="imgCoaching" class="card-img" />
                    <div class="overlay-base"></div>
                    <div class="overlay-hover"></div>
                </div>
                <div class="card-body center-all">
                    <h2 class="card-title">Coaching</h2>
                </div>
            </Link>

            <!-- 3. Live -->
            <Link href="/live" class="card card-live" @mousemove="moveCursor" @mouseenter="onLiveHoverCustom"
                @mouseleave="onLiveLeaveCustom">
                <div class="card-bg-container">
                    <img :src="imgLive" class="card-img" />
                    <div class="overlay-base"></div>
                    <div class="overlay-hover"></div>
                </div>
                <div class="card-body center-all row">
                    <div class="live-indicator">
                        <span class="dot" ref="liveDot"></span>
                    </div>
                    <h2 class="card-title">Live</h2>
                </div>
            </Link>

            <!-- 4. Articles et conseils -->
            <Link href="/articles-conseils" class="card card-articles" @mousemove="moveCursor"
                @mouseenter="showCursor('#f7b455')" @mouseleave="hideCursor">
                <div class="card-bg-container">
                    <img :src="imgArticles" class="card-img" />
                    <div class="overlay-base"></div>
                    <div class="overlay-hover"></div>
                </div>
                <div class="card-body center-all">
                    <h2 class="card-title">Articles et conseils</h2>
                </div>
            </Link>

            <!-- 5. Témoignages -->
            <div class="card card-testimonials">
                <div class="card-bg-container">
                    <img :src="imgTestimonials" class="card-img" />
                    <div class="overlay-base"></div>
                    <div class="overlay-hover"></div>
                </div>
                <div class="card-body body-testimonials">
                    <h2 class="card-title-temoignage">Témoignages</h2>
                    <p class="card-desc-temoignage">Êtes-vous satisfait de notre accompagnement ? Témoignez votre
                        avancée.
                    </p>
                    <PremiumButton style="width: 10vw; margin-top: -2vh;" class="mobile-temoignage-btn"
                        href="/temoignages" text="Témoigner" />
                </div>
            </div>

            <!-- 6. L'organisme -->
            <Link href="/organisme" class="card card-organisme" @mousemove="moveCursor"
                @mouseenter="showCursor('#1A888D')" @mouseleave="hideCursor">
                <div class="card-bg-container">
                    <img :src="imgOrganisme" class="card-img" />
                    <div class="overlay-base"></div>
                    <div class="overlay-hover"></div>
                </div>
                <div class="card-body body-organisme">
                    <h2 class="card-title-organisme">L'organisme</h2>
                    <p class="card-desc-organisme">L'organisme ShiftUp aide les cadres en reconversion.</p>
                </div>
            </Link>

        </div>
    </section>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import ArrowIcon from '../../assets/images/fleche-lien.svg';
import PremiumButton from '../components/ui/PremiumButton.vue';


const imgTestimonials = '/images/Role Models - Kinfolk.jpg';
const imgCoaching = '/images/téléchargement.jpg';
const imgLive = '/images/Zoom созво.jpg';
const imgArticles = '/images/téléchargement (5).jpg';
const imgOrganisme = '/images/Organisme.jpg';
const imgCategoriesBg = '/images/catégorie.jpg';

const categoryLinks = [
    { label: 'Toutes les catégories', href: '/toutcategorie' },
    { label: 'Formations', href: '/programmes' },
    { label: 'Offres', href: '/offres' },
    { label: 'Consultations', href: '/consultations' },
];

const liveDot = ref(null);
const cursorRef = ref(null);
const bentoGrid = ref(null);
const cursorBgColor = ref('white');

const cursorTextColor = computed(() => {
    return cursorBgColor.value === 'white' ? 'black' : 'white';
});

onMounted(() => {
    gsap.set(liveDot.value, { scale: 1.3, boxShadow: "0 0 0 0px rgba(255,0,0,0)" });

    const cards = bentoGrid.value.querySelectorAll('.card');
    gsap.from(cards, {
        y: 30,
        opacity: 0,
        duration: 0.8,
        stagger: 0.1,
        ease: "power2.out",
        delay: 0.1
    });
});

const moveCursor = (e) => {
    gsap.to(cursorRef.value, {
        x: e.clientX,
        y: e.clientY,
        duration: 0.1,
        ease: "power2.out"
    });
};

const showCursor = (color = 'white') => {
    cursorBgColor.value = color;
    gsap.to(cursorRef.value, { scale: 1, opacity: 1, duration: 0.3 });
};

const hideCursor = () => {
    gsap.to(cursorRef.value, { scale: 0, opacity: 0, duration: 0.3 });
};

const onLiveHoverCustom = (e) => {
    showCursor('#ff0000');
    gsap.to(liveDot.value, {
        scale: 2,
        boxShadow: "0 0 20px 5px rgba(255, 0, 0, 0.8)",
        duration: 0.4,
        ease: "sine.out"
    });
};

const onLiveLeaveCustom = () => {
    hideCursor();
    gsap.to(liveDot.value, {
        scale: 1.3,
        boxShadow: "0 0 0 0px rgba(255,0,0,0)",
        duration: 0.4,
        ease: "power1.inOut"
    });
};
</script>

<style scoped>
/* REPRISE EXACTE DE TES STYLES ORIGINAUX */
.bento-section {
    background-color: #1a1a1a;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.bento-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    grid-template-rows: repeat(3, 1fr);
    gap: 20px;
    width: 100%;
    max-width: 96vw;
    height: 92vh;
}

.custom-cursor {
    position: fixed;
    top: -40px;
    left: -40px;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    z-index: 9999;
    transform: scale(0);
    opacity: 0;
    transition: background-color 0.3s ease;
}

.custom-cursor span {
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.card-testimonials {
    grid-area: 1 / 1 / 3 / 2;
}

.card-coaching {
    grid-area: 3 / 1 / 4 / 2;
    cursor: none;
}

.card-categories {
    grid-area: 1 / 2 / 3 / 3;
    height: 50.5vh;
    margin-top: 11vh;
    z-index: 10;
}

.card-live {
    grid-area: 3 / 2 / 4 / 3;
    cursor: none;
}

.card-articles {
    grid-area: 1 / 3 / 2 / 4;
    cursor: none;
}

.card-organisme {
    grid-area: 2 / 3 / 4 / 4;
    cursor: none;
}

.card {
    position: relative;
    border-radius: 30px;
    overflow: hidden;
    text-decoration: none;
    color: white;
    display: flex;
    flex-direction: column;
}

.card-bg-container {
    position: absolute;
    inset: 0;
    z-index: 0;
    transition: transform 1s cubic-bezier(0.25, 1, 0.5, 1);
}

.card:hover .card-bg-container {
    transform: scale(1.1);
}

.card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.blur-effect {
    filter: blur(1px) brightness(0.6);
    transform: scale(1.15);
}

.overlay-base,
.overlay-hover {
    position: absolute;
    inset: 0;
    transition: opacity 0.8s ease;
}

.overlay-base {
    z-index: 1;
    opacity: 1;
}

.overlay-hover {
    z-index: 2;
    opacity: 0;
}

.card:hover .overlay-hover {
    opacity: 1;
}

.card-testimonials .overlay-base {
    background: linear-gradient(to top, rgba(58, 58, 58, 0.689) 0%, transparent 70%);
}

.card-testimonials .overlay-hover {
    background: linear-gradient(to top, rgba(70, 70, 70, 0.141) 0%, rgba(79, 79, 79, 0.092) 100%);
}

.card-categories .overlay-base {
    background: rgba(0, 0, 0, 0.096);
}

.card-categories .overlay-hover {
    background: rgba(0, 0, 0, 0.264);
}

.card-articles .overlay-base {
    background: linear-gradient(to top, #f7b4555f 0%, transparent 60%);
}

.card-articles .overlay-hover {
    background: linear-gradient(to top, #f7b45500 0%, #f7b45532 100%);
}

.card-coaching .overlay-base {
    background: linear-gradient(to top, #a7154390 0%, transparent 60%);
}

.card-coaching .overlay-hover {
    background: linear-gradient(to bottom, #a7154343 0%, #a715434a 100%);
}

.card-live .overlay-base {
    background: rgba(0, 0, 0, 0.142);
}

.card-live .overlay-hover {
    background: rgba(15, 15, 15, 0.2);
}

.card-organisme .overlay-base {
    background: linear-gradient(to top, #1A888D 0%, transparent 50%);
}

.card-organisme .overlay-hover {
    background: linear-gradient(to top, #1a878d00 0%, #1a878d56 100%);
}

.card-body {
    position: relative;
    z-index: 5;
    padding: 2rem;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.body-testimonials {
    justify-content: flex-start;
}

.card-title-temoignage {
    font-size: 2.2rem;
    font-weight: 800;
    margin-top: 11vh;
    margin-bottom: 0;
    line-height: 1.1;
}

.card-desc-temoignage {
    font-size: 1.2em;
    margin-top: 10px;
    margin-bottom: 2rem;
}



.body-organisme {
    justify-content: flex-end;
}

.card-title-organisme {
    font-size: 2.2rem;
    margin-top: 11vh;
    margin-bottom: 0;
    font-weight: 800;
    line-height: 1.1;
}

.card-desc-organisme {
    font-size: 1.2em;
    color: rgb(255, 255, 255);
    margin-top: 10px;
    margin-bottom: 0;
}

.center-all {
    justify-content: center;
    align-items: center;
    text-align: center;
}

.card-title {
    font-size: 2.2rem;
    font-weight: 800;
}

.sub-label {
    margin-bottom: 1.5vh;
    font-weight: 600;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    display: block;
}

.cat-desc {
    font-size: 1.2em;
    margin-bottom: 1.5vh;
    line-height: 1.4;
}

.cat-list {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    margin-top: 1rem;
}

.cat-link {
    color: #f3f3f3;
    font-size: 1.8rem;
    line-height: 1.3;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.4s;
}


.cat-link:hover {
    color: #ffffff;
    transform: translateX(10px) !important;
    transform: translateY(-5px) !important;
}

.btn-icon {
    width: 20px;
    opacity: 0 !important;
    transition: all 0.4s ease;
    filter: invert(1);
}

.cat-link:hover .btn-icon {
    opacity: 1 !important;
    transform: translateX(-15px) !important;
    transform: translateY(0) !important;
}

.row {
    flex-direction: row !important;
    gap: 15px;
}

.dot {
    width: 12px;
    height: 12px;
    background-color: #ff0000;
    border-radius: 50%;
    display: block;
}

@media (max-width: 1024px) {
    .bento-section {
        height: auto;
        padding: 120px 20px 40px 20px;
        align-items: flex-start;
    }

    .bento-grid {
        display: flex;
        flex-direction: column;
        flex-direction: column;
        grid-template-columns: 1fr;
        height: auto;
        gap: 20px;
        max-width: 100%;
    }

    .card {
        grid-area: auto !important;
        width: 100%;
        transform: none !important;
        cursor: pointer !important;
        min-height: 250px;
    }

    .card-testimonials {
        height: 400px;
        line-height: 1.1;
        margin-top: 0;
    }

    .card-categories {
        height: auto;
        min-height: 320px;
        margin-top: 0;
    }

    .card-articles {
        height: 250px;
        line-height: 1.1;
    }

    .card-coaching {
        height: 250px;
    }

    .card-live {
        height: 200px;
    }

    .card-organisme {
        height: 350px;
    }

    .custom-cursor {
        display: none;
    }

    .mobile-temoignage-btn {
        width: 140px !important;
        margin-top: 20px !important;
        font-size: 1rem !important;
    }
}
</style>