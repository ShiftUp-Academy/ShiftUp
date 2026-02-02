<template>
    <div class="coaching-page">
        <ShaderBackground :colors="themeColors" class="intro-section">
            <div class="intro-content">
                <h1 class="impact-title">Coaching</h1>
                <p class="subtitle">
                    Vous cherchez un coaching particulier ? <br>
                    choisissez entre nos programmes ci-dessous et reservez votre apprentissages
                </p>
            </div>
        </ShaderBackground>

        <section class="coaching-grid-section">
            <div class="grid-container">
                <div v-for="type in coachingTypes" :key="type.IdTypeCoaching" class="card-wrapper">
                    <CoachingCard :title="type.NomDeType" :description="type.Descriptions"
                        @reserve="handleReserve(type)" />
                </div>
            </div>

            <ModalReservationCoaching :isOpen="showModal" :coaching="selectedCoaching" :availabilities="availabilities"
                @close="showModal = false" @success="onReservationSuccess" />

            <Toast />
        </section>
    </div>
</template>

<script setup>
import CoachingCard from '../components/ui/CoachingCard.vue';
import ModalReservationCoaching from '../components/ui/ModalReservationCoaching.vue';
import ShaderBackground from '../components/ui/ShaderBackground.vue';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    auth: Object,
    coachingTypes: {
        type: Array,
        default: () => []
    },
    availabilities: {
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
            summary: 'Identification requise',
            detail: 'Veuillez vous connecter pour réserver un coaching.',
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
        summary: 'Réservation envoyée',
        detail: 'Votre demande a été prise en compte.',
        life: 5000
    });
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

@media (max-width: 1100px) {
    .grid-container {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .grid-container {
        grid-template-columns: 1fr;
    }

    .intro-section {
        height: auto;
        padding-bottom: 50px;
    }
}
</style>