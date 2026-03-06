<template>
    <div class="coaching-page">
        <ShaderBackground :colors="themeColors" class="intro-section">
            <div class="intro-content">
                <h1 class="impact-title">{{ $t('Coachings.coaching') }}</h1>
                <p class="subtitle">
                    {{ $t('Coachings.vous_cherchez_un') }} <br>
                    {{ $t('Coachings.choisissez_entre_nos') }}
                </p>
            </div>
        </ShaderBackground>

        <section class="coaching-grid-section">
            <div class="grid-container">
                <div v-for="type in coachingTypes" :key="type.IdTypeCoaching" class="card-wrapper">
                    <CoachingCard :title="type.NomDeType" :description="type.Descriptions" :price="type.Prix"
                        @reserve="handleReserve(type)" />
                </div>
            </div>

            <ModalReservationCoaching :isOpen="showModal" :coaching="selectedCoaching" :availabilities="availabilities"
                @close="showModal = false" @success="onReservationSuccess" />

            <Toast />
        </section>

        <section class="consultations-extra-section">
            <div class="sections-container">

                <div v-if="auth.user && userCoachings.length > 0" class="extra-block">
                    <div class="upcoming-gradient-list">
                        <div v-for="item in userCoachings" :key="item.IdReservation" class="gradient-card-wrapper"
                            @click="openCoachingDetail(item)">
                            <div class="gradient-card">
                                <div class="gc-main">
                                    <div class="gc-info">
                                        <span class="gc-label">{{ $t('Coachings.votre_session_a') }}</span>
                                        <h3 class="gc-title">{{ item.type?.NomDeType }}</h3>
                                    </div>
                                    <div class="gc-date-box">
                                        <div class="gc-date">{{ formatDateUppercase(item.disponibilite?.DateDisponible)
                                            }}</div>
                                        <div class="gc-time">{{ item.HeureDebutReservation?.substring(0, 5) }}</div>
                                    </div>
                                </div>
                                <div class="gc-footer">
                                    <span class="gc-status">{{ $t('Coachings.StatusLabel') }}: {{ item.StatutReservation
                                    }}</span>
                                    <span v-if="item.LienVideoReplay" class="gc-action">{{
                                        $t('Coachings.rplay_disponible') }} <i class="fas fa-play ml-2"></i></span>
                                    <span v-else class="gc-action">{{ $t('Coachings.votre_coach_vous') }} <i
                                            class="fas fa-clock ml-2"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="coachingReplays.length > 0" class="extra-block">
                    <h2 class="section-title">{{ $t('Coachings.replays_de_coaching') }}</h2>
                    <div class="horizontal-cards-list">
                        <div v-for="replay in coachingReplays" :key="replay.IdReservation"
                            class="h-card-wrapper clickable" @click="openCoachingDetail(replay)"
                            @mousemove="handleReplayFlashMove($event, replay.IdReservation)"
                            @mouseleave="handleReplayFlashLeave(replay.IdReservation)"
                            :ref="el => replayCardRefs[replay.IdReservation] = el"
                            :style="getReplayFlashStyle(replay.IdReservation)">
                            <div class="h-card archive-card-new">
                                <div class="h-card-content">
                                    <div class="h-card-left">
                                        <div class="archive-icon"><i class="fas fa-video"></i></div>
                                        <h3 class="h-title">{{ replay.TitreReplay || replay.type?.NomDeType }}</h3>
                                        <p class="h-subtitle">{{ $t('Coachings.SessionOn') }} {{
                                            formatDate(replay.disponibilite?.DateDisponible) }}</p>
                                    </div>
                                    <div class="h-card-right">
                                        <div class="h-category">{{ $t('Coachings.public_replay') }}</div>
                                        <div class="h-action">{{ $t('Coachings.voir_la_session') }} <i
                                                class="fas fa-arrow-right ml-2"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flashlight-overlay"></div>
                                <div class="border-glow"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <PremiumModal :isOpen="showDetailModal" :header="$t('Coachings.session_de_coaching')"
            @close="showDetailModal = false" width="800px" :dark="true">
            <div v-if="selectedCoachingSession" class="consultation-detail-body">
                <div v-if="selectedCoachingSession.LienVideoReplay" class="video-wrapper">
                    <iframe :src="getEmbedUrl(selectedCoachingSession.LienVideoReplay)" frameborder="0" allowfullscreen
                        class="video-iframe"></iframe>
                </div>
                <div v-else class="no-video-info">
                    <i class="fas fa-clock fa-3x mb-4"></i>
                    <p>{{ $t('Coachings.votre_session_est') }}</p>
                </div>
                <div class="detail-text">
                    <h2 class="modal-res-title">{{ selectedCoachingSession.TitreReplay ||
                        selectedCoachingSession.type?.NomDeType }}</h2>
                    <p v-if="selectedCoachingSession.DescriptionReplay" class="modal-res-desc replay-desc">
                        {{ selectedCoachingSession.DescriptionReplay }}
                    </p>
                    <p class="modal-res-desc session-details">
                        {{ $t('Coachings.DateLabel') }} : {{
                            formatDate(selectedCoachingSession.disponibilite?.DateDisponible) }} à {{
                            selectedCoachingSession.HeureDebutReservation?.substring(0, 5) }}<br>
                        {{ $t('Coachings.StatusLabel') }} : {{ selectedCoachingSession.StatutReservation }}
                    </p>
                </div>
            </div>
        </PremiumModal>

    </div>
</template>

<script setup>
import CoachingCard from '../components/ui/CoachingCard.vue';
import ModalReservationCoaching from '../components/ui/ModalReservationCoaching.vue';
import ShaderBackground from '../components/ui/ShaderBackground.vue';
import LiquidGlass from '../components/ui/LiquidGlass.vue';
import PremiumModal from '../components/ui/PremiumModal.vue';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'fr');

const props = defineProps({
    auth: Object,
    coachingTypes: {
        type: Array,
        default: () => []
    },
    availabilities: {
        type: Array,
        default: () => []
    },
    userCoachings: {
        type: Array,
        default: () => []
    },
    coachingReplays: {
        type: Array,
        default: () => []
    }
});

const toast = useToast();
const selectedCoaching = ref(null);
const showModal = ref(false);

const themeColors = {
    primary: '#1A888D',
    secondary: '#202020',
    accent: '#F7B455',
    dark: '#010101'
};

const handleReserve = (type) => {
    if (!props.auth.user) {
        toast.add({
            severity: 'info',
            summary: $t('Coachings.IdentificationRequiredSummary'),
            detail: $t('Coachings.IdentificationRequiredDetail'),
            life: 3000
        });
        setTimeout(() => {
            router.visit('/login');
        }, 1000);
        return;
    }
    selectedCoaching.value = type;
    showModal.value = true;
};

const onReservationSuccess = () => {
    toast.add({
        severity: 'success',
        summary: $t('Coachings.ReservationSentSummary'),
        detail: $t('Coachings.ReservationSentDetail'),
        life: 5000
    });
};

const showDetailModal = ref(false);
const selectedCoachingSession = ref(null);

const openCoachingDetail = (session) => {
    selectedCoachingSession.value = session;
    if (session.LienVideoReplay) {
        showDetailModal.value = true;
    } else {
        toast.add({
            severity: 'info',
            summary: $t('Coachings.ComingSoonSummary'),
            detail: $t('Coachings.ComingSoonDetail'),
            life: 3000
        });
    }
};

const getEmbedUrl = (url) => {
    if (!url) return '';
    if (url.includes('youtube.com/watch?v=')) return url.replace('watch?v=', 'embed/');
    if (url.includes('youtu.be/')) return url.replace('youtu.be/', 'youtube.com/embed/');
    return url;
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString(currentLocale.value, { day: '2-digit', month: 'long', year: 'numeric' });
};

const formatDateUppercase = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString(currentLocale.value, { day: '2-digit', month: 'long', year: 'numeric' }).toUpperCase();
};

// Flashlight logic for Replay Cards
const replayFlashStates = ref({});
const replayCardRefs = ref({});

const handleReplayFlashMove = (event, id) => {
    const rect = event.currentTarget.getBoundingClientRect();
    if (!replayFlashStates.value[id]) {
        replayFlashStates.value[id] = { x: '50%', y: '50%', opacity: 0 };
    }
    replayFlashStates.value[id].x = `${event.clientX - rect.left}px`;
    replayFlashStates.value[id].y = `${event.clientY - rect.top}px`;
    replayFlashStates.value[id].opacity = 1;
};

const handleReplayFlashLeave = (id) => {
    if (replayFlashStates.value[id]) {
        replayFlashStates.value[id].opacity = 0;
    }
};

const getReplayFlashStyle = (id) => {
    const state = replayFlashStates.value[id];
    if (!state) return {};
    return {
        '--mouse-x': state.x,
        '--mouse-y': state.y,
        '--flash-opacity': state.opacity
    };
};
</script>

<style scoped>
.coaching-page {
    background-color: #090909;
    min-height: 100vh;
    color: white;
}

.intro-section {
    height: 70vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 0 5vw;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.intro-content {
    position: relative;
    z-index: 2;
}

.impact-title {
    font-size: clamp(3rem, 8vw, 5rem);
    margin-bottom: 2rem;
    font-weight: 400;
    line-height: 1;
    text-transform: uppercase;
    letter-spacing: -0.03em;
}

.subtitle {
    font-size: clamp(1.1rem, 3vw, 1.8rem);
    line-height: 1.3;
    opacity: 0.85;
    max-width: 850px;
    margin: 0 auto;
    font-weight: 300;
}

.coaching-grid-section {
    padding: 5vh 5vw;
    background: #f8f8f8;
}

.grid-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    max-width: 1400px;
    margin: 0 auto;
}

.card-wrapper {
    height: 100%;
}

.empty-state {
    text-align: center;
    padding: 100px 0;
    color: #444;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 20px;
}

.empty-state p {
    font-size: 1.2rem;
}

@media (max-width: 1024px) {
    .grid-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .intro-section {
        height: auto;
        min-height: 50vh;
        padding: 120px 6vw 60px;
    }

    .impact-title {
        font-size: 3.5rem;
    }

    .subtitle {
        font-size: 1.2rem;
        width: 90%;
        font-weight: 400;
        color: #ffffff;
    }

    .grid-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .coaching-grid-section {
        padding: 40px 6vw;
    }
}

.consultations-extra-section {
    padding: 8vh 5vw;
    background: #090909;
}

.sections-container {
    max-width: 1400px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 8vh;
}

.section-title {
    font-size: clamp(1.5rem, 3vw, 2.5rem);
    width: 30vw;
    line-height: 1.1;
    font-weight: 600;
    color: #ffffff;
    margin: 0;
}

.section-title::after {
    content: '';
    flex-grow: 1;
    height: 1px;
    background: linear-gradient(90deg, rgba(26, 136, 141, 0.3), transparent);
}

.horizontal-cards-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 5vh;
}

.h-card-wrapper {
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.h-card-wrapper.clickable {
    cursor: pointer;
}

.h-card-wrapper.clickable:hover {
    transform: scale(1.01) translateY(-2px);
}

.h-card {
    padding: 20px 30px;
}

.h-card-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.h-card-left {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.h-status {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 4px 10px;
    border-radius: 4px;
    width: fit-content;
}

.status-closed {
    background: rgba(0, 166, 80, 0.1);
    color: #00A650;
}

.status-pending {
    background: rgba(247, 180, 85, 0.1);
    color: #F7B455;
}

.h-title {
    font-size: 2rem;
    margin-top: 0;
    font-weight: 600;
    color: #fff;
    margin: 0;
}

.h-subtitle {
    font-size: 1.2rem;
    color: rgba(255, 255, 255, 0.737);
    margin: 0;
}

.h-card-right {
    text-align: right;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
}

.h-category {
    font-size: 1rem;
    width: 10vw;
    color: rgb(255, 255, 255);
    font-weight: 300;
}

.h-action {
    font-size: 1.2rem;
    font-weight: 400;
    color: #1A888D;
    letter-spacing: 1px;
}

.archive-card-new {
    background: linear-gradient(135deg, #202020 0%, #1A888D 50%, #F7B455 100%);
    padding: 30px 40px;
    border-radius: 0 !important;
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.archive-card-new:hover {
    border-color: rgba(255, 255, 255, 0.74);
}

.archive-card-new .archive-icon {
    font-size: 1rem;
    color: #fff;
    margin-right: 1vw;
    opacity: 0.8;
}

.archive-card-new .h-title {
    font-weight: 500;
    letter-spacing: -0.02em;
}

.archive-card-new .h-action {
    color: #000000;
    font-weight: 500;
}

.flashlight-overlay {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 2;
    background: radial-gradient(600px circle at var(--mouse-x, 50%) var(--mouse-y, 50%),
            rgba(255, 255, 255, 0.211),
            transparent 80%);
    opacity: var(--flash-opacity, 0);
    transition: opacity 0.3s ease;
}

.border-glow {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 3;
    border-radius: inherit;
    padding: 1px;
    background: radial-gradient(400px circle at var(--mouse-x, 50%) var(--mouse-y, 50%),
            rgba(255, 255, 255, 0.404),
            transparent 60%);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: var(--flash-opacity, 0);
    transition: opacity 0.3s ease;
}

.upcoming-gradient-list {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
}

.gradient-card-wrapper {
    cursor: pointer;
    width: 95%;
    transition: width 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.gradient-card-wrapper:hover {
    width: 100%;
}

.gradient-card {
    background: linear-gradient(135deg, #202020 0%, #1A888D 50%, #F7B455 100%);
    padding: 40px;
    border-radius: 0;
    position: relative;
    overflow: hidden;
}

.gc-main {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 30px;
}

.gc-label {
    font-size: 0.8rem;
    font-weight: 400;
    letter-spacing: 4px;
    color: rgba(255, 255, 255, 0.7);
    display: block;
    margin-bottom: 10px;
}

.gc-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 400;
    line-height: 1;
    text-transform: uppercase;
    margin: 0;
    color: rgba(255, 255, 255, 0.875);
}

.gc-date-box {
    text-align: right;
}

.gc-date {
    font-size: 1.5rem;
    font-weight: 400;
    color: #fff;
}

.gc-time {
    font-size: 4rem;
    font-weight: 500;
    color: #1f1f1fcc;
    line-height: 1;
}

.gc-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.gc-status {
    font-size: 0.9rem;
    font-weight: 700;
    text-transform: uppercase;
    background: rgba(0, 0, 0, 0.3);
    padding: 6px 15px;
    border-radius: 0;
}

.gc-action {
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 1px;
    color: #181818d5;
    text-transform: uppercase;
}

.consultation-detail-body {
    padding: 20px 0;
}

.video-wrapper {
    position: relative;
    width: 100%;
    padding-bottom: 56.25%;
    height: 0;
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 30px;
    background: #000;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.video-iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.detail-text {
    text-align: left;
}

.modal-res-title {
    font-size: 1.8rem;
    margin-bottom: 15px;
    color: #fff;
    font-weight: 700;
}

.modal-res-desc {
    font-size: 1.1rem;
    line-height: 1.6;
    color: #ccc;
    margin-bottom: 30px;
}

.no-video-info {
    text-align: center;
    padding: 40px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 15px;
    color: #F7B455;
    margin-bottom: 20px;
}

.related-questions h4 {
    font-size: 1rem;
    color: #1A888D;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.q-bubble {
    background: rgba(255, 255, 255, 0.03);
    padding: 15px 20px;
    border-radius: 12px;
    border-left: 3px solid #1A888D;
    margin-bottom: 10px;
    font-size: 0.95rem;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .h-card-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }

    .h-card-right {
        text-align: left;
        align-items: flex-start;
        width: 100%;
        padding-top: 15px;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }
}
</style>