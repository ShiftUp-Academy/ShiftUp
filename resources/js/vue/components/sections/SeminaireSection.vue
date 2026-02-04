<template>
    <section class="seminaire-section no-global-reveal" ref="sectionRef">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Nos évènements <br />imminents</h2>
            </div>
            <div v-for="(sem, index) in seminaires" :key="sem.IdProgrammeFormation || index" class="seminaire-card">
                <div class="card-content">
                    <div class="info-side">
                        <div class="top-row">
                            <span class="title-label">{{ sem.Titre }}</span>
                        </div>

                        <div class="main-row">
                            <span class="huge-date">{{ formatDate(sem.DateSeminaire) }}</span>
                            <div class="v-divider"></div>
                            <div class="modality-container">
                                <span class="en-label">En</span>
                                <span class="modality-text">{{ sem.ModaliteSeminaire ?
                                    sem.ModaliteSeminaire.toUpperCase() : 'PRESENTIEL' }}</span>
                            </div>
                        </div>

                        <div class="bottom-row">
                            <div class="location-container">
                                <span class="lieu-label">Lieu</span>
                                <span class="lieu-text">{{ sem.LieuSeminaire || 'LIEU' }}</span>
                            </div>
                            <div class="v-divider small"></div>
                            <Link :href="`/seminaires/${sem.IdProgrammeFormation}`" class="savoir-plus">
                                EN SAVOIR PLUS
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none"
                                    xmlns="http://www.w3.org/2000/svg" class="arrow-icon">
                                    <path d="M1 11L11 1M11 1H3M11 1V9" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <div class="image-side">
                        <img :src="sem.LienPhoto" :alt="sem.Titre" class="sem-image">
                        <div class="image-overlay"></div>
                    </div>
                </div>
            </div>

            <div v-if="seminaires.length === 0" class="no-seminaires">
                <p>Aucun séminaire prévu pour le moment.</p>
            </div>
        </div>
    </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    seminaires: {
        type: Array,
        default: () => []
    }
});

const sectionRef = ref(null);

onMounted(() => {
    if (!sectionRef.value) return;

    gsap.from(sectionRef.value.querySelector('.section-title'), {
        y: 50,
        opacity: 0,
        duration: 1,
        ease: "power3.out",
        scrollTrigger: {
            trigger: sectionRef.value.querySelector('.section-title'),
            start: "top 90%",
        }
    });

    gsap.from(sectionRef.value.querySelectorAll('.seminaire-card'), {
        y: 100,
        opacity: 0,
        duration: 1.2,
        stagger: 0.2,
        ease: "power4.out",
        scrollTrigger: {
            trigger: sectionRef.value,
            start: "top 80%",
        }
    });
});

function formatDate(dateString) {
    if (!dateString) return '30 MARS 2026';
    const date = new Date(dateString);
    const day = date.getDate();
    const months = ['JANVIER', 'FEVRIER', 'MARS', 'AVRIL', 'MAI', 'JUIN', 'JUILLET', 'AOUT', 'SEPTEMBRE', 'OCTOBRE', 'NOVEMBRE', 'DECEMBRE'];
    const month = months[date.getMonth()];
    const year = date.getFullYear();
    return `${day} ${month} ${year}`;
}
</script>

<style scoped>
.seminaire-section {
    color: #fff;
    padding-bottom: 3rem;
    position: relative;
    background-color: #111;
    z-index: 20;
}

.container {
    padding: 0 5.2vw;
    margin: 0 auto;
}

.section-header {
    margin-bottom: 5vh;

}

.section-title {
    font-size: 2.5rem;
    font-weight: 600;
    color: #fefefe;
    line-height: 1;
    margin: 0;
}

.seminaire-card {
    background: linear-gradient(135deg, #8A38F5, #A71543);
    width: 100%;
    border-radius: 2px;
    overflow: hidden;
}

.card-content {
    display: flex;
    height: 100%;
}

.info-side {
    flex: 1;
    padding: 1rem 4vw;
    padding-right: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.title-label {
    font-size: 1.7rem;
    text-transform: uppercase;
    font-weight: 550;
    letter-spacing: 1px;
    color: #fff;
    margin-bottom: 0.5vh;
}

.main-row {
    display: flex;
    align-items: center;
    gap: 30px;
}

.huge-date {
    font-size: clamp(2.8rem, 5vw, 3.8rem);
    font-weight: 300;
    color: #f5f5f5;
    letter-spacing: -1px;
}

.v-divider {
    width: 1px;
    height: 3rem;
    background-color: #e6e6e6;
}

.v-divider.small {
    width: 2px;
    height: 1.2rem;
    background-color: #ebebeb;
    align-self: center;
}

.modality-container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.1;
}

.en-label {
    font-size: 1rem;
    color: #f9f9f9;
    font-weight: 500;
}

.modality-text {
    font-size: 2.2rem;
    color: #fff;
    letter-spacing: 0.5px;
}

.bottom-row {
    display: flex;
    align-items: center;
    gap: 15px;
}

.location-container {
    display: flex;
    gap: 10px;
    font-size: 1.4rem;
}

.lieu-label {
    color: #fdfdfd;
}

.lieu-text {
    color: #fff;
    font-weight: 500;
    font-size: 1.4rem;
}

.savoir-plus {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 10px;
    letter-spacing: 0.5px;
    opacity: 0.9;
    transition: opacity 0.2s;
}

.savoir-plus:hover {
    opacity: 1;
}

.image-side {
    width: 40%;
    position: relative;
    background-color: #111;
}

.sem-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to right, #111 0%, rgba(17, 17, 17, 3.8) 0%, transparent 60%);
    pointer-events: none;
}

.no-seminaires {
    text-align: center;
    padding: 60px;
    color: #444;
}

@media (max-width: 768px) {
    .seminaire-card {
        height: auto;
        min-height: 350px;
    }

    .card-content {
        flex-direction: column-reverse;
    }

    .image-side {
        width: 100%;
        height: 180px;
    }

    .main-row {
        flex-wrap: wrap;
        height: auto;
        gap: 15px;
        margin: 15px 0;
    }

    .v-divider {
        display: none;
    }
}
</style>
