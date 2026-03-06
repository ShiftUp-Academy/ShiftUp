<template>
    <div class="reservations-page">
        <ShaderBackground :colors="themeColors" class="hero-section">
            <div class="hero-content" ref="heroContent">
                <h1 class="page-title">{{ $t('Reservations.mes_rservations') }}</h1>
                <p class="page-subtitle">{{ $t('Reservations.retrouvez_ici_tous') }}</p>
            </div>
        </ShaderBackground>

        <main class="content-section">
            <div class="reservations-container">

                <div v-if="hasUpcoming" class="upcoming-section">
                    <div class="main-section-header">
                        <div class="section-label-line"></div>
                        <h2 class="main-section-title">{{ $t('Reservations.a_venir') }}</h2>
                        <div class="section-label-line"></div>
                    </div>

                    <div class="category-block" v-if="upcomingCoachings.length > 0">
                        <div class="section-header">
                            <h2 class="section-title">{{ $t('Reservations.mes_coachings') }}</h2>
                        </div>
                        <div class="upcoming-gradient-list">
                            <UpcomingCard v-for="c in upcomingCoachings" :key="c.IdReservation" type="coaching"
                                :item="c" :dateTime="getCoachingDateTime(c)" />
                        </div>
                    </div>

                    <div class="category-block" v-if="upcomingSeminaires.length > 0">
                        <div class="section-header">
                            <i class="fas fa-users-rectangle section-icon"></i>
                            <h2 class="section-title">{{ $t('Reservations.mes_sminaires') }}</h2>
                        </div>
                        <div class="upcoming-gradient-list">
                            <UpcomingCard v-for="s in upcomingSeminaires" :key="s.IdProgrammeFormation" type="seminar"
                                :item="s" />
                        </div>
                    </div>

                    <!-- Lives à venir -->
                    <div class="category-block" v-if="upcomingLives.length > 0">
                        <div class="section-header">
                            <i class="fas fa-video section-icon"></i>
                            <h2 class="section-title">{{ $t('Reservations.lives__venir') }}</h2>
                        </div>
                        <div class="upcoming-gradient-list">
                            <UpcomingCard v-for="l in upcomingLives" :key="l.IdLive" type="live" :item="l" />
                        </div>
                    </div>
                </div>

                <!-- Message si aucune réservation à venir mais histoire existante -->
                <div v-if="!hasUpcoming && hasHistory" class="no-upcoming-state">
                    <div class="no-upcoming-box">
                        <i class="fas fa-calendar-check"></i>
                        <p>{{ $t('Reservations.aucune_reservation_a_venir') }}</p>
                    </div>
                </div>

                <!-- SECTION: HISTORIQUE -->
                <div v-if="hasHistory" class="history-section">
                    <div class="main-section-header history-header" @click="historyOpen = !historyOpen">
                        <div class="section-label-line history-line"></div>
                        <h2 class="main-section-title history-title">
                            <i class="fas fa-clock-rotate-left"></i>
                            {{ $t('Reservations.historique') }}
                            <span class="history-count">{{ totalHistoryCount }}</span>
                        </h2>
                        <div class="section-label-line history-line"></div>
                        <i class="fas fa-chevron-down history-chevron" :class="{ 'is-open': historyOpen }"></i>
                    </div>

                    <transition name="slide-history">
                        <div v-show="historyOpen" class="history-content">
                            <!-- Coachings passés -->
                            <div class="category-block" v-if="pastCoachings.length > 0">
                                <div class="section-header-history">
                                    <h2 class="history-category-title">{{ $t('Reservations.mes_coachings') }}</h2>
                                </div>
                                <div class="history-cards-list">
                                    <HistoryCard v-for="c in pastCoachings" :key="c.IdReservation"
                                        :title="c.type?.NomDeType || $t('Reservations.CoachingPersonnel')"
                                        :subtitle="formatDate(getCoachingDateTime(c), true)"
                                        :category="translateStatus(c.StatutReservation)"
                                        :actionText="$t('Reservations.termine')" iconClass="fas fa-headset" />
                                </div>
                            </div>

                            <!-- Séminaires passés -->
                            <div class="category-block" v-if="pastSeminaires.length > 0">
                                <div class="section-header-history">
                                    <h2 class="history-category-title">{{ $t('Reservations.mes_sminaires') }}</h2>
                                </div>
                                <div class="history-cards-list">
                                    <HistoryCard v-for="s in pastSeminaires" :key="s.IdProgrammeFormation"
                                        :title="s.Titre" :subtitle="formatFullDate(s.DateSeminaire)"
                                        :category="s.ModaliteSeminaire" :actionText="$t('Reservations.dtails')"
                                        iconClass="fas fa-users-rectangle" actionIconClass="fas fa-arrow-right" />
                                </div>
                            </div>

                            <!-- Lives passés -->
                            <div class="category-block" v-if="pastLives.length > 0">
                                <div class="section-header-history">
                                    <h2 class="history-category-title">{{ $t('Reservations.lives_passes') }}</h2>
                                </div>
                                <div class="history-cards-list">
                                    <HistoryCard v-for="l in pastLives" :key="l.IdLive" :title="l.Titre"
                                        :subtitle="formatFullDate(l.DateDebut)" :category="l.categorie?.Nom"
                                        :actionText="$t('Reservations.revoir')" iconClass="fas fa-video"
                                        actionIconClass="fas fa-play-circle" />
                                </div>
                            </div>
                        </div>
                    </transition>
                </div>

                <ReservationEmptyState v-if="!hasUpcoming && !hasHistory"
                    :title="$t('Reservations.aucune_rservation_trouve')"
                    :description="$t('Reservations.vous_navez_pas')">
                    <template #actions>
                        <Link href="/coaching" class="action-btn">{{ $t('Reservations.rserver_un_coaching') }}</Link>
                        <Link href="/toutcategorie" class="action-btn secondary">{{
                            $t('Reservations.voir_les_programmes') }}</Link>
                    </template>
                </ReservationEmptyState>

            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ShaderBackground from '../components/ui/ShaderBackground.vue';
import UpcomingCard from '../components/reservations/UpcomingCard.vue';
import HistoryCard from '../components/reservations/HistoryCard.vue';
import ReservationEmptyState from '../components/reservations/ReservationEmptyState.vue';

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'fr');
const $t = (key) => page.props.translations[key] || key;

const props = defineProps({
    coachings: Array,
    seminaires: Array,
    lives: Array
});

const themeColors = {
    primary: '#1A888D',
    secondary: '#0a0a0a',
    accent: '#8A38F5',
    dark: '#000000'
};

const heroContent = ref(null);
const historyOpen = ref(false);

const getCoachingDateTime = (coaching) => {
    const datePart = coaching.disponibilite?.DateDisponible;
    const timePart = coaching.HeureDebutReservation;
    if (datePart && timePart) return new Date(`${datePart}T${timePart}`);
    if (datePart) return new Date(datePart);
    return new Date(coaching.DateCreation || 0);
};

const upcomingCoachings = computed(() => {
    const now = new Date();
    return (props.coachings || []).filter(c => getCoachingDateTime(c) >= now);
});
const pastCoachings = computed(() => {
    const now = new Date();
    return (props.coachings || []).filter(c => getCoachingDateTime(c) < now);
});

const getSeminaireDateTime = (s) => {
    return new Date(`${s.DateSeminaire}T${s.HeureSeminaire || '00:00:00'}`);
};

const upcomingSeminaires = computed(() => {
    const now = new Date();
    return (props.seminaires || []).filter(s => getSeminaireDateTime(s) >= now);
});
const pastSeminaires = computed(() => {
    const now = new Date();
    return (props.seminaires || []).filter(s => getSeminaireDateTime(s) < now);
});

const upcomingLives = computed(() => {
    const now = new Date();
    return (props.lives || []).filter(l => new Date(l.DateDebut) >= now);
});
const pastLives = computed(() => {
    const now = new Date();
    return (props.lives || []).filter(l => new Date(l.DateDebut) < now);
});

const hasUpcoming = computed(() => upcomingCoachings.value.length > 0 || upcomingSeminaires.value.length > 0 || upcomingLives.value.length > 0);
const hasHistory = computed(() => pastCoachings.value.length > 0 || pastSeminaires.value.length > 0 || pastLives.value.length > 0);
const totalHistoryCount = computed(() => pastCoachings.value.length + pastSeminaires.value.length + pastLives.value.length);

const formatDate = (dateString, withTime = false) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const opt = { day: '2-digit', month: 'short' };
    if (withTime) { opt.hour = '2-digit'; opt.minute = '2-digit'; }
    return date.toLocaleDateString(currentLocale.value, opt);
};

const formatFullDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString(currentLocale.value, { day: 'numeric', month: 'long', year: 'numeric' });
};

const translateStatus = (status) => {
    if (status === 'Confirmé') return $t('Reservations.StatusConfirmed');
    if (status === 'En attente') return $t('Reservations.StatusPending');
    if (status === 'Terminé') return $t('Reservations.StatusDone');
    return $t('Reservations.StatusCancelled');
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap');

.reservations-page {
    background-color: #050505;
    min-height: 100vh;
    font-family: 'Outfit', sans-serif;
    color: white;
}

.hero-section {
    height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 0 5vw;
}

.page-title {
    font-size: clamp(2.5rem, 6vw, 4.5rem);
    font-weight: 400;
    margin-top: 5vh !important;
    text-transform: uppercase;
    letter-spacing: -2px;
}

.page-subtitle {
    font-size: 1.2rem;
    opacity: 0.7;
    margin-top: 10px;
    font-weight: 300;
}

.content-section {
    padding: 60px 5vw;
    margin-top: 3vh;
    z-index: 5;
    position: relative;
}

.reservations-container {
    max-width: 1400px;
    margin: 0 auto;
}

.category-block {
    margin-bottom: 80px;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 30px;
}

.section-icon {
    font-size: 1.5rem;
    color: #1A888D;
    background: rgba(26, 136, 141, 0.1);
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
}

.section-title {
    font-size: 2rem;
    font-weight: 600;
}

.upcoming-gradient-list {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
}

.main-section-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 50px;
}

.section-label-line {
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
}

.main-section-title {
    font-size: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 3px;
    color: rgba(255, 255, 255, 0.997);
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 10px;
}

.history-section {
    margin-top: 60px;
    padding-top: 40px;
}

.history-header {
    cursor: pointer;
    user-select: none;
}

.history-line {
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.08), transparent);
}

.history-title {
    color: rgb(255, 255, 255);
}

.history-count {
    font-size: 1rem;
    font-weight: 700;
    color: rgb(255, 255, 255);
}

.history-chevron {
    color: rgb(255, 255, 255);
    font-size: 0.8rem;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.history-chevron.is-open {
    transform: rotate(180deg);
}

.history-content {
    overflow: hidden;
}

.section-header-history {
    margin: 40px 0 20px;
    padding-left: 10px;
    border-left: 2px solid rgba(255, 255, 255, 0.1);
}

.history-category-title {
    font-size: 1.2rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: rgb(255, 255, 255);
    font-weight: 500;
}

.history-cards-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 40px;
}

.no-upcoming-state {
    text-align: center;
    padding: 60px 20px;
}

.no-upcoming-box {
    display: inline-flex;
    align-items: center;
    gap: 15px;
    padding: 20px 40px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.06);
}

.no-upcoming-box i {
    font-size: 1.3rem;
    color: rgba(255, 255, 255, 0.25);
}

.no-upcoming-box p {
    margin: 0;
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.4);
}

.slide-history-enter-active,
.slide-history-leave-active {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    max-height: 3000px;
}

.slide-history-enter-from,
.slide-history-leave-to {
    max-height: 0;
    opacity: 0;
}

@media (max-width: 768px) {
    .cards-wrapper.mobile-horizontal {
        flex-wrap: nowrap !important;
        overflow-x: auto;
        padding: 0 0 40px;
        scroll-snap-type: x mandatory;
        gap: 20px;
    }

    .hero-section {
        height: 40vh;
    }

    .page-title {
        font-size: 2.8rem;
    }
}
</style>
