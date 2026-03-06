<template>
    <div class="gradient-card-wrapper" :class="customClass">
        <div class="gradient-card" :class="type + '-card'">
            <div class="gc-main">
                <div class="gc-info">
                    <span class="gc-label">{{ getLabel }}</span>
                    <h3 class="gc-title">{{ getTitle }}</h3>
                </div>
                <div class="gc-date-box">
                    <div class="gc-date">{{ getDate }}</div>
                    <div class="gc-time">{{ getTime }}</div>
                </div>
            </div>
            <div class="gc-footer">
                <div class="gc-status-container">
                    <span class="gc-status">{{ getStatusLabel }}</span>
                </div>
                <div class="gc-action-container">
                    <span v-if="type === 'coaching'" class="gc-action">
                        <template v-if="item.StatutReservation === 'Confirmé'">
                            {{ $t('Reservations.rejoindre_la_session') }} <i class="fas fa-video ml-2"></i>
                        </template>
                        <template v-else>
                            {{ $t('Reservations.en_attente_de') }} <i class="fas fa-clock ml-2"></i>
                        </template>
                    </span>
                    <Link v-else-if="type === 'seminar'" :href="`/seminaires/${item.IdProgrammeFormation}`"
                        class="gc-action link-action">
                        {{ $t('Reservations.dtails') }} <i class="fas fa-arrow-right ml-2"></i>
                    </Link>
                    <Link v-else-if="type === 'live'" :href="`/live`" class="gc-action link-action">
                        {{ $t('Reservations.sinformer') }} <i class="fas fa-circle-info ml-2"></i>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        required: true, // 'coaching', 'seminar', 'live'
    },
    item: {
        type: Object,
        required: true
    },
    dateTime: [Date, String, Object], // Prepared Date object for coaching
    customClass: String
});

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'fr');
const $t = (key) => page.props.translations[key] || key;

const getLabel = computed(() => {
    if (props.type === 'coaching') return $t('Coachings.votre_session_a');
    if (props.type === 'seminar') return $t('Reservations.SeminarLabel');
    if (props.type === 'live') return props.item.categorie?.Nom || 'LIVE';
    return '';
});

const getTitle = computed(() => {
    if (props.type === 'coaching') return props.item.type?.NomDeType || $t('Reservations.CoachingPersonnel');
    return props.item.Titre || '';
});

const getDate = computed(() => {
    let dateVal = '';
    if (props.type === 'coaching') dateVal = props.dateTime;
    else if (props.type === 'seminar') dateVal = props.item.DateSeminaire;
    else if (props.type === 'live') dateVal = props.item.DateDebut;

    if (!dateVal) return '';
    const date = new Date(dateVal);
    return date.toLocaleDateString(currentLocale.value, { day: '2-digit', month: 'long', year: 'numeric' }).toUpperCase();
});

const getTime = computed(() => {
    if (props.type === 'coaching') return props.item.HeureDebutReservation?.substring(0, 5);
    if (props.type === 'seminar') return props.item.HeureSeminaire?.substring(0, 5);
    if (props.type === 'live') {
        const date = new Date(props.item.DateDebut);
        return date.toLocaleTimeString(currentLocale.value, { hour: '2-digit', minute: '2-digit' });
    }
    return '';
});

const getStatusLabel = computed(() => {
    if (props.type === 'coaching') return $t('Coachings.StatusLabel') + ': ' + translateStatus(props.item.StatutReservation);
    if (props.type === 'seminar') return props.item.ModaliteSeminaire;
    if (props.type === 'live') return $t('Reservations.live_en_attente');
    return '';
});

const translateStatus = (status) => {
    if (status === 'Confirmé') return $t('Reservations.StatusConfirmed');
    if (status === 'En attente') return $t('Reservations.StatusPending');
    if (status === 'Terminé') return $t('Reservations.StatusDone');
    return $t('Reservations.StatusCancelled');
};
</script>

<style scoped>
.gradient-card-wrapper {
    cursor: pointer;
    width: 95%;
    transition: width 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.gradient-card-wrapper:hover {
    width: 100%;
}

.gradient-card {
    background: linear-gradient(135deg, #202020 0%, #1A888D 50%, #8a38f5b7 100%);
    padding: 40px;
    border-radius: 0;
    position: relative;
    overflow: hidden;
    min-height: 250px;
}

.seminar-card.gradient-card {
    background: linear-gradient(135deg, #1A1A1A 0%, #1A888D 50%, #8a38f5b7 100%);
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
    margin-bottom: 15px;
    text-transform: uppercase;
}

.gc-title {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 400;
    line-height: 1.1;
    text-transform: uppercase;
    margin: 0;
    color: rgba(255, 255, 255, 0.95);
    max-width: 70%;
}

.gc-date-box {
    text-align: right;
}

.gc-date {
    font-size: 1.5rem;
    font-weight: 400;
    color: #fff;
    margin-bottom: 5px;
}

.gc-time {
    font-size: clamp(3rem, 6vw, 4.5rem);
    font-weight: 500;
    color: #f5f5f5;
    line-height: 1;
}

.gc-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 25px;
}

.gc-status {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    background: rgba(0, 0, 0, 0.3);
    padding: 8px 18px;
    border-radius: 0;
    color: #fff;
}

.gc-action {
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 1px;
    color: rgba(255, 255, 255, 0.9);
    text-transform: uppercase;
    display: flex;
    align-items: center;
}

.link-action {
    text-decoration: none;
    transition: opacity 0.3s ease;
}

.link-action:hover {
    opacity: 0.7;
}

.ml-2 {
    margin-left: 0.5rem;
}

@media (max-width: 768px) {
    .gradient-card {
        padding: 30px 20px;
    }

    .gc-main {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }

    .gc-date-box {
        text-align: left;
    }

    .gc-time {
        font-size: 2.5rem;
    }

    .gc-title {
        max-width: 100%;
        font-size: 1.8rem;
    }

    .gc-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }
}
</style>
