<template>
    <section class="seminaire-section no-global-reveal" ref="sectionRef">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">{{ $t('SeminaireSection.nos_vnements') }} <br />{{
                    $t('SeminaireSection.imminents') }}</h2>
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
                                <span class="en-label">{{ $t('In') }}</span>
                                <span class="modality-text">{{ sem.ModaliteSeminaire ?
                                    sem.ModaliteSeminaire.toUpperCase() : $t('Presentiel') }}</span>
                            </div>
                        </div>

                        <div class="bottom-row">
                            <div class="location-container">
                                <span class="lieu-label">{{ $t('SeminaireSection.lieu') }}</span>
                                <span class="lieu-text">{{ sem.LieuSeminaire || $t('Location') }}</span>
                            </div>
                            <div class="v-divider small"></div>
                            <Link :href="`/seminaires/${sem.IdProgrammeFormation}`" class="savoir-plus"
                                @click="$trackEvent('clic_seminaire', { id: sem.IdProgrammeFormation, titre: sem.Titre })">
                                {{ $t('SeminaireSection.en_savoir_plus') }}
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
                <p>{{ $t('SeminaireSection.aucun_sminaire_prvu') }}</p>
            </div>
        </div>
    </section>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const props = defineProps({
    seminaires: {
        type: Array,
        default: () => []
    }
});

const page = usePage();
const currentLocale = computed(() => page.props.locale || 'fr');

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
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat(currentLocale.value, { day: 'numeric', month: 'long', year: 'numeric' }).format(date).toUpperCase();
}
</script>

<style scoped>
.seminaire-section {
    color: #fff;
    padding-bottom: 3rem;
    position: relative;
    background-color: #010101;
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
    font-size: 2.5vw;
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
    font-size: 1.5vw;
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
    font-size: clamp(2.8rem, 4.5vw, 3.8rem);
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
    font-size: 0.8vw;
    color: #f9f9f9;
    font-weight: 500;
}

.modality-text {
    font-size: 2vw;
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
    font-size: 1.2vw;
}

.lieu-label {
    color: #fdfdfd;
}

.lieu-text {
    color: #fff;
    font-weight: 500;
    font-size: 1.2vw;
}

.savoir-plus {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    font-size: 1vw;
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
    background-color: #010101;
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
    background: linear-gradient(to right, #010101 0%, rgba(1, 1, 1, 3.8) 0%, transparent 60%);
    pointer-events: none;
}

.no-seminaires {
    text-align: center;
    padding: 60px;
    color: #444;
}

@media (max-width: 768px) {
    .seminaire-section {
        margin-top: -2px;
    }

    .section-title {
        font-size: 1.8rem;
        margin-bottom: 2vh;
    }

    .seminaire-card {
        height: auto;
        border: none;
        min-height: 320px;
    }

    .card-content {
        flex-direction: column-reverse;
    }

    .info-side {
        padding: 20px;
    }

    .title-label {
        font-size: 1.1rem;
        line-height: 1;
    }

    .huge-date {
        font-size: 2rem;
        margin-bottom: 5px;
    }

    .main-row {
        flex-direction: row !important;
        align-items: center;
        gap: 10px;
        margin: 5px 0;
    }

    .v-divider {
        display: block !important;
        height: 15px;
        width: 1px;
        background-color: rgba(255, 255, 255, 0.5);
    }

    .modality-text {
        font-size: 0.9rem;
    }

    .en-label {
        font-size: 0.6rem;
    }

    .lieu-text,
    .lieu-label {
        font-size: 1.1rem;
    }

    .location-container {
        font-size: 0.8rem;
        gap: 5px;
    }

    .savoir-plus {
        font-size: 0.9rem;
        margin-top: 3px;
    }

    .savoir-plus svg {
        width: 10px;
        height: 10px;
    }

    .image-side {
        width: 100%;
        height: 250px;
    }

    .image-overlay {
        display: none;
    }

    .v-divider {
        display: none;
    }
}
</style>
