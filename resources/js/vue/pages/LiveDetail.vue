<template>
    <div class="live-detail-page">
        <div class="background-full no-global-reveal">
            <img :src="live?.LienPhoto" alt="Background" class="bg-image" v-if="live?.LienPhoto" />
            <div class="gradient-overlay-full"></div>
        </div>

        <div class="main-content container" v-if="live">
            <div class="content-left">
                <div class="event-meta">
                    <div class="edition-label-container">
                        <span class="recording-dot" v-if="isUpcoming || isActive"></span>
                        <span class="edition-label" v-if="isUpcoming">LIVE À VENIR</span>
                        <span class="edition-label active-live" v-else-if="isActive">EN DIRECT</span>
                        <span class="edition-label replay-label" v-else>REPLAY DISPONIBLE</span>
                    </div>
                </div>

                <h1 class="main-title">
                    {{ live.Titre }}
                </h1>

                <div class="date-time-block">
                    <p class="date-text">{{ formattedDate }}</p>
                    <p class="time-text">
                        à {{ formatTime(live.DateDebut) }}
                    </p>
                </div>

                <div class="countdown-section" v-if="isUpcoming">
                    <p class="starts-in-label">Commence dans</p>
                    <div class="countdown-timer">
                        <div class="time-unit">
                            <span class="number">{{ days }}</span>
                            <span class="label">Jours</span>
                        </div>
                        <div class="time-unit">
                            <span class="number">{{ hours }}</span>
                            <span class="label">Heures</span>
                        </div>
                        <div class="time-unit">
                            <span class="number">{{ minutes }}</span>
                            <span class="label">Minutes</span>
                        </div>
                        <div class="time-unit">
                            <span class="number">{{ seconds }}</span>
                            <span class="label">Secondes</span>
                        </div>
                    </div>
                </div>
                <div class="actions-section">
                    <template v-if="isActive">
                        <PremiumButton v-if="live.LienGoogleMeet" class="action-btn meet-btn" @click="joinLive"
                            :style="{ '--btn-gradient': 'linear-gradient(90deg, #EA4335, #FBBC05, #EA4335)' }">
                            OUVRIR SUR GOOGLE MEET
                        </PremiumButton>

                        <PremiumButton class="action-btn copy-btn" @click="copyLink"
                            :style="{ '--btn-bg': '#000', '--btn-gradient': 'none' }">
                            <i class="fas fa-copy" style="margin-right: 8px;"></i> {{ linkCopied ? 'LIEN COPIÉ !' :
                            'COPIER LE LIEN' }}
                        </PremiumButton>
                    </template>

                    <template v-else-if="!isUpcoming && live.LienReplay">
                        <PremiumButton class="action-btn replay-btn" @click="watchReplay">
                            VOIR LE REPLAY
                        </PremiumButton>
                    </template>

                    <template v-else-if="isUpcoming">
                        <PremiumButton class="action-btn notify-btn" disabled>
                            TENEZ-VOUS PRÊT
                        </PremiumButton>
                        <PremiumButton class="action-btn copy-btn" @click="copyLink"
                            :style="{ '--btn-bg': '#000', '--btn-gradient': 'none' }">
                            <i class="fas fa-copy" style="margin-right: 8px;"></i> {{ linkCopied ? 'LIEN COPIÉ !' :
                            'COPIER LE LIEN' }}
                        </PremiumButton>
                    </template>
                </div>
            </div>
        </div>

        <div class="main-content container empty-state" v-else>
            <div class="content-left">
                <h1 class="main-title">AUCUN LIVE PROGRAMMÉ</h1>
                <PremiumButton class="action-btn" @click="goHome">RETOUR À L'ACCUEIL</PremiumButton>
            </div>
        </div>
        <Toast />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import PremiumButton from '../components/ui/PremiumButton.vue';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    live: Object
});

const toast = useToast();
const days = ref(0);
const hours = ref(0);
const minutes = ref(0);
const seconds = ref(0);
const linkCopied = ref(false);
let timerInterval = null;

const isUpcoming = computed(() => {
    if (!props.live) return false;
    return new Date(props.live.DateDebut) > new Date();
});

const isActive = computed(() => {
    if (!props.live) return false;
    const now = new Date();
    return now >= new Date(props.live.DateDebut) && now <= new Date(props.live.DateFin);
});

const formattedDate = computed(() => {
    if (!props.live) return '';
    const date = new Date(props.live.DateDebut);
    return date.toLocaleDateString('fr-FR', { month: 'long', day: 'numeric', year: 'numeric' }).toUpperCase();
});

const formatTime = (dateStr) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }).replace(':', 'h');
};

const calculateTimeLeft = () => {
    if (!props.live || !isUpcoming.value) return;

    const targetDate = new Date(props.live.DateDebut).getTime();
    const now = new Date().getTime();
    const difference = targetDate - now;

    if (difference > 0) {
        days.value = Math.floor(difference / (1000 * 60 * 60 * 24));
        hours.value = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        minutes.value = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
        seconds.value = Math.floor((difference % (1000 * 60)) / 1000);
    } else {
        days.value = 0;
        hours.value = 0;
        minutes.value = 0;
        seconds.value = 0;
        if (timerInterval) clearInterval(timerInterval);
    }
};

const copyLink = () => {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        linkCopied.value = true;
        toast.add({ severity: 'success', summary: 'Copié', detail: 'Lien copié dans le presse-papier', life: 2000 });
        setTimeout(() => {
            linkCopied.value = false;
        }, 2000);
    });
};

onMounted(() => {
    if (props.live && isUpcoming.value) {
        calculateTimeLeft();
        timerInterval = setInterval(calculateTimeLeft, 1000);
    }

    const tl = gsap.timeline();
    tl.from('.content-left > *', {
        duration: 1,
        y: 30,
        opacity: 0,
        stagger: 0.1,
        ease: 'power3.out'
    });

    gsap.from('.bg-image', {
        scale: 1.1,
        duration: 2,
        ease: 'power2.out'
    });
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

const joinLive = () => {
    if (props.live?.LienGoogleMeet) {
        window.open(props.live.LienGoogleMeet, '_blank');
    }
};

const watchReplay = () => {
    if (props.live?.LienReplay) {
        window.open(props.live.LienReplay, '_blank');
    }
};

const goHome = () => {
    router.visit('/');
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap');

.live-detail-page {
    position: relative;
    min-height: 100vh;
    color: #fff;
    overflow: hidden;
    display: flex;
    align-items: center;
}

.background-full {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
}

.bg-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    top: 0;
    left: 0;
}

.gradient-overlay-full {
    position: absolute;
    top: 0;
    left: 0;
    width: 50%;
    height: 100%;
    background: linear-gradient(90deg, #181818e4 10%, #1818189d 70%, #24242400 100%);
    z-index: 1;
}

.main-content {
    position: relative;
    z-index: 10;
    display: flex;
    align-items: center;
    width: 100%;
    padding: 0 5vw;
    margin: 0 auto;
}

.content-left {
    flex: 1;
    max-width: 48vw;
}

.edition-label-container {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 2rem;
    margin-top: 5vh;
}

.recording-dot {
    width: 12px;
    height: 12px;
    background-color: #ff0000;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 10px #ff0000;
    animation: pulse-recording 1.5s infinite ease-in-out;
}

@keyframes pulse-recording {
    0% {
        transform: scale(1);
        opacity: 1;
    }

    50% {
        transform: scale(1.4);
        opacity: 0.5;
    }

    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.edition-label {
    text-transform: uppercase;
    letter-spacing: 2px;
    font-size: 0.9rem;
    font-weight: 400;
    display: block;
    opacity: 0.9;
}

.active-live {
    color: #ef4444;
    font-weight: 700;
}

.replay-label {
    color: #1A888D;
    font-weight: 700;
}

.main-title {
    font-size: clamp(2rem, 5vw, 2.5rem);
    font-weight: 500;
    margin-top: 0;
    line-height: 1.1;
    text-transform: uppercase;
    margin-bottom: 2rem;
    color: #fff;
}

.date-time-block {
    margin-bottom: 1rem;
    display: flex;
    gap: 1rem;
    flex-direction: row;
}

.date-text {
    font-size: 3.5rem;
    margin: 0;
    font-weight: 400;
}

.time-text {
    font-size: 3.5rem;
    margin: 0;
    padding-left: 1vw;
    border-left: 2px solid white;
    opacity: 0.9;
}

.starts-in-label {
    font-size: 1.3rem;
    font-weight: 400;
    letter-spacing: 2px;
    opacity: 0.8;
    display: flex;
    align-items: center;
    gap: 10px;
}

.starts-in-label::before {
    content: '';
    display: inline-block;
    width: 15px;
    height: 8px;
    background-color: #0E7EC3;
    border-radius: 50%;
}

.countdown-timer {
    display: flex;
    gap: 4rem;
    margin-bottom: 3.5rem;
}

.time-unit {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.time-unit .number {
    font-size: 4rem;
    font-weight: 500;
    line-height: 1;
}

.time-unit .label {
    font-size: 0.8rem;
    text-transform: uppercase;
    margin-top: 5px;
}

.actions-section {
    display: flex;
    gap: 1.5rem;
}

.action-btn {
    min-width: 220px;
}

@media (max-width: 900px) {
    .main-content {
        padding-top: 0;
        justify-content: center;
    }

    .content-left {
        text-align: center;
        margin: 0 auto;
        max-width: 90vw;
    }

    .edition-label-container {
        justify-content: center;
    }

    .date-time-block {
        flex-direction: column;
        align-items: center;
        gap: 0;
    }

    .time-text {
        border-left: none;
        padding-left: 0;
    }

    .date-text,
    .time-text {
        font-size: 2.5rem;
    }

    .starts-in-label {
        justify-content: center;
    }

    .countdown-timer {
        justify-content: center;
        gap: 1.5rem;
    }

    .time-unit .number {
        font-size: 2rem;
    }

    .actions-section {
        flex-direction: column;
        align-items: center;
    }

    .gradient-overlay-full {
        width: 100%;
        background: linear-gradient(180deg, rgba(24, 24, 24, 0.4) 0%, rgba(24, 24, 24, 0.9) 100%);
    }
}
</style>
