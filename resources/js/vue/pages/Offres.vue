<template>
    <div class="program-detail-page">
        <div class="background-noise"></div>

        <div class="hero-section" @mousemove="handleMouseMove" @mouseleave="handleMouseLeave" ref="heroRef">
            <video autoplay muted loop playsinline class="hero-video-bg">
                <source :src="'/videos/offre.webm'" type="video/webm">
            </video>
            <div class="hero-content">
                <h1 class="hover-title">
                    <span v-for="(char, index) in ($t('Offres.exclusives') || 'OFFRES EXCLUSIVES').split('')"
                        :key="index" class="char" :class="{ 'break-word': char === ' ' }"
                        :ref="el => charRefs[index] = el" :style="{ fontWeight: 300 }">
                        {{ char === ' ' ? '&nbsp;' : char }}
                    </span>
                </h1>
                <p class="page-subtitle">{{ $t('Offres.profitez_de_nos') }}</p>
            </div>
        </div>

        <div class="offers-list">
            <div v-if="offres.length === 0" class="empty-offers">
                <div class="empty-content">
                    <i class="fas fa-gift"></i>
                    <p>{{ $t('Offres.aucune_offre') || "Pas d'offre pour le moment" }}</p>
                </div>
            </div>

            <div v-for="offre in offres" :key="offre.IdOffre" class="offer-item"
                :class="{ 'is-open': isOpen(offre.IdOffre) }">

                <div class="offer-summary" @click="toggleOffer(offre.IdOffre)">
                    <div class="offer-bg"
                        :style="{ backgroundImage: 'url(' + (offre.LienPhoto || '/images/placeholder.jpg') + ')' }">
                    </div>
                    <div class="offer-overlay"></div>

                    <div class="offer-info">
                        <div class="offer-badges">
                            <span v-if="offre.ReductionGlobal > 0" class="badge reduction">{{ $t('Offres.prix_a') }} -{{
                                Number(offre.ReductionGlobal) }}%</span>
                            <span class="badge duration" v-if="offre.DureeJours">{{ $t('Offres.profitez_pendant') }} {{
                                offre.DureeJours }} {{ $t('Offres.jours') }}</span>
                        </div>

                        <h2 class="offer-title">{{ offre.Titre }}</h2>
                        <p class="offer-desc">{{ offre.Descriptions }}</p>

                        <div class="offer-footer">
                            <div class="offer-meta-footer">
                                <span class="offer-countdown">
                                    <i class="fas fa-clock"></i> {{ countdowns[offre.IdOffre] }}
                                </span>
                                <span class="offer-total-price">
                                    <span class="price-label">{{ $t('Offres.pack__seulement') }} </span>
                                    {{ formatPrice(calcPackPrice(offre)) }}
                                </span>
                            </div>

                            <span class="view-details-btn">
                                {{ isOpen(offre.IdOffre) ? $t('Offres.masquer_details') : $t('Offres.voir_contenu') }}
                                <i class="fas" :class="isOpen(offre.IdOffre) ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                            </span>

                            <div class="offer-actions">
                                <button class="cart-btn" @click.stop="addToCart(offre)">
                                    <i class="fas fa-shopping-cart"></i> {{ $t('Offres.ajouter_au_panier') }}
                                </button>
                                <PremiumButton class="buy-btn-premium" @click.stop="">
                                    <span>{{ $t('Offres.acheter_maintenant') }}</span>
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </PremiumButton>
                            </div>
                        </div>
                    </div>
                </div>

                <transition name="expand">
                    <div v-if="isOpen(offre.IdOffre)" class="offer-details">
                        <div class="details-inner">
                            <div class="content-grid-container">

                                <div v-if="getRegularPrograms(offre).length > 0" class="content-column">
                                    <div class="column-header">
                                        <h3 class="column-title">{{ $t('Programmes') }}</h3>
                                        <span class="item-count">{{ getRegularPrograms(offre).length }}</span>
                                    </div>
                                    <div class="cards-column">
                                        <ProgramCard v-for="op in getRegularPrograms(offre)" :key="'prog-' + op.id"
                                            :id="op.programme.IdProgrammeFormation"
                                            :image="op.programme.LienPhoto || '/images/placeholder.jpg'"
                                            :type="op.programme.Type || 'Programme'" :showAddToCart="false"
                                            :reduction="op.ReductionSpecifique" />
                                    </div>
                                </div>

                                <div v-if="getSeminarPrograms(offre).length > 0" class="content-column">
                                    <div class="column-header">
                                        <h3 class="column-title">{{ $t('Offres.programmes_sminaire') }}</h3>
                                        <span class="item-count">{{ getSeminarPrograms(offre).length }}</span>
                                    </div>
                                    <div class="cards-column">
                                        <ProgramCard v-for="op in getSeminarPrograms(offre)" :key="'sem-' + op.id"
                                            :id="op.programme.IdProgrammeFormation"
                                            :image="op.programme.LienPhoto || '/images/placeholder.jpg'"
                                            :type="op.programme.Type || 'Séminaire'" :showAddToCart="false"
                                            :reduction="op.ReductionSpecifique" />
                                    </div>
                                </div>

                                <div v-if="offre.coachings && offre.coachings.length > 0" class="content-column">
                                    <div class="column-header">
                                        <h3 class="column-title">{{ $t('Coachings.coaching') }}</h3>
                                        <span class="item-count">{{ offre.coachings.length }}</span>
                                    </div>
                                    <div class="cards-column">
                                        <CoachingCard v-for="oc in offre.coachings" :key="'coach-' + oc.IdOffreCoaching"
                                            :title="oc.coaching.NomDeType" :description="oc.coaching.Descriptions"
                                            :price="Number(oc.coaching.Prix)" :reduction="oc.ReductionSpecifique"
                                            @reserve="openBookingModal(oc.coaching)" />
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </transition>

            </div>
        </div>

        <!-- Booking Modal -->
        <ModalReservationCoaching v-if="showReservationModal" :isOpen="showReservationModal"
            :coaching="selectedCoaching" :availabilities="availabilities" @close="closeReservationModal" />

        <Toast />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

const page = usePage();
const $t = (key) => page.props.translations?.[key] || key;
import ProgramCard from '../components/ui/ProgramCard.vue';
import CoachingCard from '../components/ui/CoachingCard.vue';
import ModalReservationCoaching from '../components/ui/ModalReservationCoaching.vue';
import PremiumButton from '../components/ui/PremiumButton.vue';

import { useToast } from "primevue/usetoast";
import Toast from 'primevue/toast';

const toast = useToast();

const props = defineProps({
    offres: Array,
    availabilities: Array,
    categories: Array,
    typesCoachings: Array
});

const openOffers = ref(new Set());
const showReservationModal = ref(false);
const selectedCoaching = ref(null);

const charRefs = ref([]);
const heroRef = ref(null);

const handleMouseMove = (e) => {
    if (!heroRef.value) return;

    const mouseX = e.clientX;
    const mouseY = e.clientY;

    charRefs.value.forEach((charEl) => {
        if (!charEl) return;

        const charRect = charEl.getBoundingClientRect();
        const charCenterX = charRect.left + charRect.width / 2;
        const charCenterY = charRect.top + charRect.height / 2;

        const dist = Math.sqrt(Math.pow(mouseX - charCenterX, 2) + Math.pow(mouseY - charCenterY, 2));

        const maxDist = 200;
        let weight = 300;

        if (dist < maxDist) {
            weight = 800 - ((dist / maxDist) * 500);
        }

        gsap.to(charEl, {
            fontWeight: weight,
            color: '#ffffff',
            duration: 0.15,
            ease: "power3.out"
        });
    });
};

const handleMouseLeave = () => {
    charRefs.value.forEach(charEl => {
        gsap.to(charEl, {
            fontWeight: 300,
            color: '#ffffff',
            duration: 0.4,
            ease: "power3.out"
        });
    });
};

const toggleOffer = (id) => {
    if (openOffers.value.has(id)) {
        openOffers.value.delete(id);
    } else {
        openOffers.value.add(id);
    }
};

const countdowns = ref({});
let countdownInterval = null;

const calculateTimeRemaining = (offre) => {
    const startDate = new Date(offre.DateCreation);
    const endDate = new Date(startDate.getTime() + offre.DureeJours * 24 * 60 * 60 * 1000);
    const now = new Date();
    const diff = endDate - now;

    if (diff <= 0) return $t('Offres.termine');

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    return `${days}j ${hours.toString().padStart(2, '0')}h ${minutes.toString().padStart(2, '0')}m ${seconds.toString().padStart(2, '0')}s`;
};

const updateCountdowns = () => {
    props.offres.forEach(offre => {
        countdowns.value[offre.IdOffre] = calculateTimeRemaining(offre);
    });
};

const calcPackPrice = (offre) => {
    let total = 0;

    // Programmes
    if (offre.programmes) {
        offre.programmes.forEach(op => {
            const base = Number(op.programme.Prix || 0);
            const red = Number(op.ReductionSpecifique || 0);
            total += base * (1 - red / 100);
        });
    }

    // Coachings
    if (offre.coachings) {
        offre.coachings.forEach(oc => {
            const base = Number(oc.coaching.Prix || 0);
            const red = Number(oc.ReductionSpecifique || 0);
            total += base * (1 - red / 100);
        });
    }

    const globalRed = Number(offre.ReductionGlobal || 0);
    return Math.round(total * (1 - globalRed / 100));
};

const formatPrice = (price) => {
    return Math.round(price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + ' Ar';
};

onMounted(() => {
    updateCountdowns();
    countdownInterval = setInterval(updateCountdowns, 1000);
});

onUnmounted(() => {
    if (countdownInterval) clearInterval(countdownInterval);
});

const isOpen = (id) => openOffers.value.has(id);

const addToCart = (offre) => {
    router.post('/panier/ajouter', {
        id: offre.IdOffre,
        type: 'offre',
        prix: calcPackPrice(offre)
    }, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: $t('Panier'), detail: $t('Offres.pack_ajoute'), life: 3000 });
        }
    });
};

const getRegularPrograms = (offre) => {
    if (!offre.programmes || offre.programmes.length === 0) return [];
    return offre.programmes.filter(op => {
        const type = (op.programme.Type || '').toLowerCase().trim();
        return type !== 'séminaire' && type !== 'seminaire';
    });
};

const getSeminarPrograms = (offre) => {
    if (!offre.programmes || offre.programmes.length === 0) return [];
    return offre.programmes.filter(op => {
        const type = (op.programme.Type || '').toLowerCase().trim();
        return type === 'séminaire' || type === 'seminaire';
    });
};

const openBookingModal = (coaching) => {
    selectedCoaching.value = coaching;
    showReservationModal.value = true;
};

const closeReservationModal = () => {
    showReservationModal.value = false;
    selectedCoaching.value = null;
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

.program-detail-page {
    min-height: 100vh;
    background-color: #050505;
    color: #e0e0e0;
    font-family: 'Plus Jakarta Sans', sans-serif;
    overflow-x: hidden;
    padding-bottom: 50px;
}

.background-noise {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.05'/%3E%3C/svg%3E");
    pointer-events: none;
    z-index: 0;
}

.hero-section {
    position: relative;
    height: 40vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 5vw;
    z-index: 1;
    margin-top: 10vh;
    overflow: hidden;
}

.hero-video-bg {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 20%;
    min-width: 200px;
    z-index: 0;
    pointer-events: none;
}

.hero-content {
    text-align: center;
    position: relative;
    z-index: 2;
}

.hover-title {
    font-size: 5rem;
    line-height: 1.1;
    margin: 0 0 20px 0;
    cursor: default;
    user-select: none;
    display: flex;
    flex-wrap: nowrap;
    justify-content: center;
    color: #ffffff;
}

.char {
    display: inline-block;
    color: #ffffff;
    transition: color 0.15s;
}

.page-subtitle {
    color: #ffffff;
    font-size: 1.2rem;
    max-width: 600px;
    margin: 0 auto;
}

.empty-offers {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 40vh;
    width: 100%;
    z-index: 2;
}

.empty-content {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.05);
    padding: 60px 100px;
    border-radius: 40px;
    text-align: center;
    animation: fadeIn 1s ease;
}

.empty-content i {
    font-size: 3rem;
    color: #8A38F5;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-content p {
    font-size: 1.5rem;
    font-weight: 500;
    color: #888;
    margin: 0;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.offers-list {
    max-width: 90%;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    flex-direction: column;
    gap: 40px;
    position: relative;
    z-index: 2;
}

.offer-item {
    border-radius: 25px;
    overflow: hidden;
    background: #111;
    transition: all 0.3s ease;
}

.offer-item.is-open {
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.offer-summary {
    position: relative;
    height: 40vh;
    cursor: pointer;
    display: flex;
    align-items: center;
    padding: 40px;
    overflow: hidden;
}

.offer-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-size: cover;
    background-position: center;
    filter: brightness(0.6);
    transition: transform 0.6s ease;
}

.offer-summary:hover .offer-bg {
    transform: scale(1.05);
}

.offer-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to right, rgba(0, 0, 0, 0.489), rgba(0, 0, 0, 0.147));
}

.offer-info {
    position: relative;
    z-index: 2;
    width: 100%;
}

.offer-badges {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge.reduction {
    position: relative;
    background: linear-gradient(90deg, #dc2626, #f7b455, #fc5656);
    background-size: 200% 100%;
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-weight: 700;
    animation: gradient-move 3s linear infinite;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
}

.badge.reduction::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transform: skewX(-20deg);
    animation: shine-move 2.5s infinite;
}

.badge.duration {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.offer-title {
    font-size: 2.2rem;
    margin: 0 0 10px;
    font-weight: 700;
}

.offer-desc {
    color: #ccc;
    max-width: 600px;
    margin-bottom: 0;
    line-height: 1.5;
}

.offer-footer {
    position: absolute;
    right: 40px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.offer-meta-footer {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
    margin-bottom: 12px;
}

.offer-countdown {
    font-size: 1.1rem;
    font-weight: 700;
    color: #ff4b4b;
    display: flex;
    align-items: center;
    gap: 8px;
    text-shadow: 0 0 10px rgba(247, 180, 85, 0.3);
}

.offer-total-price {
    font-size: 3.5rem;
    font-weight: 400;
    color: #fff;
    letter-spacing: -0.02em;
    margin-top: 0;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.price-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #fafafa;
    text-align: end;
    width: 12vw;
    letter-spacing: 1px;
    margin-bottom: 0;
    margin-top: 1vh;
}

.view-details-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #ffffff;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 1px;
    cursor: pointer;
    transition: color 0.3s;
}

.view-details-btn:hover {
    color: #fff;
}

.offer-actions {
    display: flex;
    gap: 15px;
    margin-top: 10px;
}

.cart-btn {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 15px 20px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    backdrop-filter: blur(5px);
}

.cart-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-2px);
}

.buy-btn-premium {
    --btn-bg: linear-gradient(130deg, #0d7bc0, #8A38F5);
    padding: 10px 20px !important;
    font-size: 0.9rem !important;
    border-radius: 20px !important;
    min-height: auto !important;
}

.buy-btn-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(138, 56, 245, 0.4);
}

.details-inner {
    padding: 40px;
    background: #0a0a0a;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.content-grid-container {
    display: flex;
    gap: 30px;
    width: 100%;
}

.content-column {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.column-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px;
    position: relative;
    overflow: hidden;
}

.column-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 2px;
    height: 90%;
    background: linear-gradient(180deg, #c9c9c95b, #b4b4b4);
}

.column-title {
    flex: 1;
    font-size: 1rem;
    font-weight: 700;
    margin: 0;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.item-count {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 10px;
    background: linear-gradient(130deg, #0d7bc0, #8A38F5);
    border-radius: 10px;
    color: #ffffff;
    font-size: 0.9rem;
    font-weight: 700;
}

.cards-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.content-section {
    margin-bottom: 40px;
}

.content-section:last-child {
    margin-bottom: 0;
}

.section-label {
    font-size: 1.4rem;
    margin-bottom: 25px;
    color: #fff;
    border-left: 4px solid #8A38F5;
    padding-left: 15px;
}

.items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

/* Transition styles */
.expand-enter-active,
.expand-leave-active {
    transition: all 0.4s ease;
    max-height: 2000px;
    opacity: 1;
}

.expand-enter-from,
.expand-leave-to {
    max-height: 0;
    opacity: 0;
    padding-top: 0;
    padding-bottom: 0;
    overflow: hidden;
}

@media (max-width: 768px) {
    .hero-section {
        height: auto;
        min-height: 40vh;
        width: 90%;
        margin: 0 auto;
        padding-top: 5vh;
        margin-top: 10vh;
        margin-bottom: 5vh;
    }

    .hero-video-bg {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 30%;
        min-width: 290px;
        z-index: 0;
        pointer-events: none;
    }

    .hover-title {
        font-size: 3rem;
        width: 100% !important;
        margin: 0 auto 20px auto;
        font-weight: 600 !important;
        flex-wrap: wrap !important;
        padding: 0 20px;
        line-height: 1.2;
    }

    .break-word {
        flex-basis: 100%;
        height: 0;
    }

    .page-subtitle {
        font-size: 1rem;
        padding: 0 10vw;
    }

    .offers-list {
        max-width: 97%;
    }

    .offer-item {
        border-radius: 30px;
    }

    .offer-summary {
        flex-direction: column;
        height: auto;
        text-align: center;
        padding: 30px 30px;
        align-items: stretch;
    }

    .offer-title {
        font-size: 1.6rem;
        text-align: center;
    }

    .offer-desc {
        font-size: 0.95rem;
        text-align: center;
        margin-bottom: 20px;
    }

    .offer-badges {
        justify-content: center;
        flex-wrap: wrap;
    }

    .offer-footer {
        position: static;
        margin-top: 20px;
        align-items: center;
        transform: none;
        top: auto;
    }

    .offer-meta-footer {
        align-items: center;
        width: 100%;
    }

    .offer-total-price {
        font-size: 2.5rem;
        align-items: center;
    }

    .price-label {
        width: auto;
        text-align: center;
    }

    .offer-actions {
        flex-direction: column;
        width: 100%;
        gap: 10px;
    }

    .cart-btn,
    .buy-btn-premium {
        width: 100% !important;
        justify-content: center;
    }

    .items-grid {
        grid-template-columns: 1fr;
    }

    .content-grid-container {
        flex-direction: column;
        gap: 20px;
    }

    .details-inner {
        padding: 20px;
    }

    .column-header {
        padding: 15px;
    }

    .column-title {
        font-size: 1.1rem;
    }

    .header-icon {
        width: 38px;
        height: 38px;
        font-size: 1rem;
    }
}

@keyframes gradient-move {
    0% {
        background-position: 0% 50%;
    }

    100% {
        background-position: 200% 50%;
    }
}

@keyframes shine-move {
    0% {
        left: -100%;
    }

    100% {
        left: 150%;
    }
}
</style>
