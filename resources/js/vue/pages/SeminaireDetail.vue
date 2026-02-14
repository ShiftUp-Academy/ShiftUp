<template>
    <div class="seminaire-detail-page">
        <div class="background-full no-global-reveal">
            <img :src="seminaire.LienPhoto" alt="Background" class="bg-image" />
            <div class="gradient-overlay-full"></div>
        </div>

        <div class="main-content container">
            <div class="content-left">
                <div class="event-meta">
                    <span class="edition-label">SÉMINAIRE EXCLUSIF</span>
                </div>

                <h1 class="main-title">
                    {{ seminaire.Titre }}
                </h1>

                <p class="seminaire-description" v-if="seminaire.Descriptions">
                    {{ seminaire.Descriptions }}
                </p>

                <div class="date-time-block">
                    <p class="date-text">{{ formattedDate }}</p>
                    <p class="time-text" v-if="seminaire.HeureSeminaire">
                        à {{ seminaire.HeureSeminaire.replace(':', 'h') }}
                    </p>
                </div>

                <div class="countdown-section">
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

                <PremiumButton class="reserve-btn" @click="reserveSeat">
                    RÉSERVER MA PLACE
                </PremiumButton>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import PremiumButton from '../components/ui/PremiumButton.vue';

const props = defineProps({
    seminaire: Object
});

const timeLeft = ref(0);
const days = ref(0);
const hours = ref(0);
const minutes = ref(0);
const seconds = ref(0);
let timerInterval = null;

const formattedDate = computed(() => {
    if (!props.seminaire.DateSeminaire) return 'DATE À ANNONCER';

    const startDate = new Date(props.seminaire.DateSeminaire);

    if (props.seminaire.NombreDeJours && props.seminaire.NombreDeJours > 1) {
        const endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + (props.seminaire.NombreDeJours - 1));

        if (startDate.getMonth() === endDate.getMonth() && startDate.getFullYear() === endDate.getFullYear()) {
            const month = startDate.toLocaleDateString('fr-FR', { month: 'long' });
            const year = startDate.getFullYear();
            return `${startDate.getDate()}-${endDate.getDate()} ${month} ${year}`.toUpperCase();
        }
        else if (startDate.getFullYear() === endDate.getFullYear()) {
            const startMonth = startDate.toLocaleDateString('fr-FR', { month: 'long' });
            const endMonth = endDate.toLocaleDateString('fr-FR', { month: 'long' });
            return `${startDate.getDate()} ${startMonth} - ${endDate.getDate()} ${endMonth} ${startDate.getFullYear()}`.toUpperCase();
        }
    }

    return startDate.toLocaleDateString('fr-FR', { month: 'long', day: 'numeric', year: 'numeric' }).toUpperCase();
});

const calculateTimeLeft = () => {
    if (!props.seminaire.DateSeminaire) return;

    // Combine Date and Time
    let targetDateStr = props.seminaire.DateSeminaire;
    if (props.seminaire.HeureSeminaire) {
        targetDateStr += 'T' + props.seminaire.HeureSeminaire;
    } else {
        targetDateStr += 'T00:00:00';
    }

    const targetDate = new Date(targetDateStr).getTime();
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

onMounted(() => {
    calculateTimeLeft();
    timerInterval = setInterval(calculateTimeLeft, 1000);

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

const reserveSeat = () => {
    console.log('Reserve seat clicked');
    alert("Fonctionnalité de réservation à venir");
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap');

.seminaire-detail-page {
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

.edition-label {
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-top: 5vh;
    font-size: 0.9rem;
    font-weight: 400;
    display: block;
    opacity: 0.9;
}

.main-title {
    font-size: clamp(2rem, 5vw, 2.5rem);
    font-weight: 500;
    margin-top: 0;
    line-height: 1.1;
    text-transform: uppercase;
    margin-bottom: 1rem;
    color: #fff;
}

.seminaire-description {
    font-size: 1.2rem;
    line-height: 1.1;
    color: rgb(255, 255, 255);
    margin-bottom: 2rem;
    max-width: 45vw;
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

.reserve-btn {
    opacity: 1 !important;
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

    .edition-label {
        display: block;
        margin-left: auto;
        margin-right: auto;
    }

    .seminaire-description {
        max-width: 100%;
        margin: 1rem auto 2rem;
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
        line-height: 1.1;
    }

    .starts-in-label {
        justify-content: center;
        font-size: 0.9rem;
        margin-bottom: 0.5vh;
    }

    .countdown-timer {
        justify-content: center;
        gap: 1.8rem;
        margin-bottom: 0;
    }

    .time-unit .number {
        font-size: 3rem;
        margin-bottom: 0 !important;
    }

    .reserve-btn {
        margin: 0 auto;
        width: 100%;
        margin-bottom: 5vh;
        max-width: 300px;
    }

    .gradient-overlay-full {
        width: 100%;
        background: linear-gradient(180deg, rgba(24, 24, 24, 0.4) 0%, rgba(24, 24, 24, 0.9) 100%);
    }
}
</style>
