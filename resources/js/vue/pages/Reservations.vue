<template>
    <div class="reservations-page">
        <AppHeader :auth="auth" />

        <ShaderBackground :colors="themeColors" class="hero-section">
            <div class="hero-content" ref="heroContent">
                <h1 class="page-title">Mes Réservations</h1>
                <p class="page-subtitle">Retrouvez ici tous vos rendez-vous, séminaires et lives à venir.</p>
            </div>
        </ShaderBackground>

        <main class="content-section">
            <div class="reservations-container">

                <!-- Section Coachings -->
                <div class="category-block" v-if="coachings.length > 0">
                    <div class="section-header">
                        <i class="fas fa-headset section-icon"></i>
                        <h2 class="section-title">Mes Coachings</h2>
                    </div>
                    <div class="cards-wrapper row-flex mobile-horizontal scroll-premium">
                        <div v-for="c in coachings" :key="c.IdReservation"
                            class="res-card coaching-card shadow-premium">
                            <div class="card-status" :class="getStatusClass(c.StatutReservation)">
                                {{ c.StatutReservation }}
                            </div>
                            <div class="card-header">
                                <span class="card-type">{{ c.type?.NomDeType || 'Coaching Personnel' }}</span>
                                <h3 class="card-title">{{ formatDate(c.HeureDebutReservation, true) }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="info-item">
                                    <i class="far fa-calendar-alt"></i>
                                    <span>{{ formatFullDate(c.HeureDebutReservation) }}</span>
                                </div>
                                <div class="info-item" v-if="c.NoteUtilisateur">
                                    <i class="far fa-sticky-note"></i>
                                    <p class="note-text">{{ c.NoteUtilisateur }}</p>
                                </div>
                            </div>
                            <div class="card-footer">
                                <a v-if="c.StatutReservation === 'Confirmé'" href="#" class="btn-join disabled"
                                    title="Le lien sera disponible 5 min avant">
                                    <i class="fas fa-video"></i> Rejoindre la session
                                </a>
                                <span v-else class="status-msg">En attente de confirmation</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Séminaires -->
                <div class="category-block" v-if="seminaires.length > 0">
                    <div class="section-header">
                        <i class="fas fa-users-rectangle section-icon"></i>
                        <h2 class="section-title">Mes Séminaires</h2>
                    </div>
                    <div class="cards-wrapper row-flex mobile-horizontal scroll-premium">
                        <div v-for="s in seminaires" :key="s.IdProgrammeFormation"
                            class="res-card seminar-card shadow-premium">
                            <div class="card-visual">
                                <img v-if="s.LienPhoto" :src="s.LienPhoto" :alt="s.Titre" />
                                <div v-else class="placeholder-visual"><i class="fas fa-map-marker-alt"></i></div>
                            </div>
                            <div class="card-content">
                                <span class="card-type">Séminaire {{ s.ModaliteSeminaire }}</span>
                                <h3 class="card-title">{{ s.Titre }}</h3>
                                <div class="info-item">
                                    <i class="far fa-calendar-check"></i>
                                    <span>{{ formatFullDate(s.DateSeminaire) }} à {{ s.HeureSeminaire }}</span>
                                </div>
                                <div class="info-item" v-if="s.LieuSeminaire">
                                    <i class="fas fa-location-dot"></i>
                                    <span>{{ s.LieuSeminaire }}</span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <Link :href="`/seminaires/${s.IdProgrammeFormation}`" class="btn-view">
                                    Détails <i class="fas fa-arrow-right"></i>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Lives -->
                <div class="category-block" v-if="lives.length > 0">
                    <div class="section-header">
                        <i class="fas fa-video section-icon"></i>
                        <h2 class="section-title">Lives à Venir</h2>
                    </div>
                    <div class="cards-wrapper row-flex mobile-horizontal scroll-premium">
                        <div v-for="l in lives" :key="l.IdLive" class="res-card live-card shadow-premium">
                            <div class="live-indicator">LIVE EN ATTENTE</div>
                            <div class="card-header">
                                <div class="cat-pill" v-if="l.categorie">{{ l.categorie.Nom }}</div>
                                <h3 class="card-title">{{ l.Titre }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="info-item">
                                    <i class="far fa-clock"></i>
                                    <span>{{ formatFullDate(l.DateDebut) }} - {{ formatTime(l.DateDebut) }}</span>
                                </div>
                                <p class="excerpt" v-if="l.Descriptions">{{ truncate(l.Descriptions, 80) }}</p>
                            </div>
                            <div class="card-footer">
                                <Link :href="`/live`" class="btn-reserve">
                                    S'informer <i class="fas fa-circle-info"></i>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State Global -->
                <div v-if="coachings.length === 0 && seminaires.length === 0 && lives.length === 0"
                    class="empty-state-global">
                    <div class="empty-box shadow-sm">
                        <i class="fas fa-calendar-xmark"></i>
                        <h2>Aucune réservation trouvée</h2>
                        <p>Vous n'avez pas encore de coaching programmé ou de séminaire réservé.</p>
                        <div class="empty-actions">
                            <Link href="/coaching" class="action-btn">Réserver un coaching</Link>
                            <Link href="/toutcategorie" class="action-btn secondary">Voir les programmes</Link>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <AppFooter />
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppHeader from '../components/headerfooter/AppHeader.vue';
import AppFooter from '../components/headerfooter/AppFooter.vue';
import ShaderBackground from '../components/ui/ShaderBackground.vue';
import gsap from 'gsap';

const props = defineProps({
    auth: Object,
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

onMounted(() => {
    gsap.from(heroContent.value, {
        y: 40,
        opacity: 0,
        duration: 1.2,
        ease: 'power4.out'
    });

    gsap.from('.category-block', {
        x: -30,
        opacity: 0,
        duration: 0.8,
        stagger: 0.2,
        ease: 'power3.out',
        delay: 0.5
    });

    gsap.from('.res-card', {
        scale: 0.95,
        opacity: 0,
        duration: 0.6,
        stagger: 0.1,
        ease: 'back.out(1.7)',
        delay: 0.8
    });
});

const formatDate = (dateString, withTime = false) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const opt = { day: '2-digit', month: 'short' };
    if (withTime) { opt.hour = '2-digit'; opt.minute = '2-digit'; }
    return date.toLocaleDateString('fr-FR', opt);
};

const formatFullDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
};

const formatTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
};

const getStatusClass = (status) => {
    if (status === 'Confirmé') return 'status-confirmed';
    if (status === 'En attente') return 'status-pending';
    if (status === 'Terminé') return 'status-done';
    return 'status-cancelled';
};

const truncate = (text, length) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
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
    font-weight: 800;
    text-transform: uppercase;
    margin: 0;
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
    margin-top: -80px;
    position: relative;
    z-index: 5;
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
    font-size: 1.8rem;
    font-weight: 600;
    margin: 0;
}

.cards-wrapper {
    display: flex;
    gap: 25px;
}

.row-flex {
    flex-wrap: wrap;
}

.res-card {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 25px;
    padding: 30px;
    width: calc(33.333% - 17px);
    min-width: 320px;
    display: flex;
    flex-direction: column;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
}

.res-card:hover {
    transform: translateY(-10px);
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(26, 136, 141, 0.3);
}

.card-status {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 50px;
    letter-spacing: 1px;
}

.status-confirmed {
    background: #dcfce7;
    color: #166534;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-done {
    background: #f1f5f9;
    color: #475569;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

.card-type {
    font-size: 0.8rem;
    color: #1A888D;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.card-title {
    font-size: 1.4rem;
    margin: 10px 0 20px;
    line-height: 1.2;
    font-weight: 600;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    font-size: 0.95rem;
    color: #ccc;
}

.info-item i {
    width: 20px;
    color: #1A888D;
}

.excerpt {
    font-size: 0.9rem;
    color: #888;
    line-height: 1.5;
    margin-top: 15px;
}

.card-footer {
    margin-top: auto;
    padding-top: 25px;
}

.btn-join,
.btn-view,
.btn-reserve {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 25px;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
    font-size: 0.9rem;
}

.btn-join {
    background: #1A888D;
    color: white;
}

.btn-join.disabled {
    opacity: 0.5;
    cursor: help;
}

.btn-view {
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
}

.btn-view:hover {
    background: white;
    color: black;
}

.btn-reserve {
    background: rgba(138, 56, 245, 0.1);
    color: #8A38F5;
    border: 1px solid rgba(138, 56, 245, 0.2);
}

.btn-reserve:hover {
    background: #8A38F5;
    color: white;
}

.seminar-card {
    padding: 0;
}

.card-visual {
    height: 180px;
    overflow: hidden;
    position: relative;
}

.card-visual img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.placeholder-visual {
    width: 100%;
    height: 100%;
    background: #111;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: #333;
}

.seminar-card .card-content {
    padding: 25px;
}

.seminar-card .card-footer {
    padding: 0 25px 25px;
}

.live-indicator {
    background: #ff0055;
    padding: 4px 15px;
    font-size: 0.65rem;
    font-weight: 800;
    border-radius: 5px;
    width: fit-content;
    margin-bottom: 15px;
}

.cat-pill {
    background: rgba(255, 255, 255, 0.05);
    padding: 3px 10px;
    border-radius: 50px;
    font-size: 0.7rem;
    width: fit-content;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.empty-state-global {
    display: flex;
    justify-content: center;
    padding: 100px 0;
}

.empty-box {
    text-align: center;
    max-width: 500px;
    background: rgba(255, 255, 255, 0.02);
    padding: 60px;
    border-radius: 40px;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.empty-box i {
    font-size: 4rem;
    color: #333;
    margin-bottom: 30px;
}

.empty-box h2 {
    font-size: 2rem;
    margin-bottom: 15px;
}

.empty-box p {
    color: #888;
    line-height: 1.6;
}

.empty-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 40px;
}

.action-btn {
    padding: 15px 30px;
    border-radius: 15px;
    text-decoration: none;
    font-weight: 700;
    transition: 0.3s;
}

.action-btn:not(.secondary) {
    background: #1A888D;
    color: white;
}

.action-btn.secondary {
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
}

/* Modal mobile horizontal scroll */
@media (max-width: 768px) {
    .content-section {
        margin-top: -40px;
        padding: 40px 0;
    }

    .section-header {
        padding: 0 6vw;
    }

    .mobile-horizontal {
        flex-wrap: nowrap !important;
        overflow-x: auto;
        padding: 0 6vw 40px;
        scroll-snap-type: x mandatory;
        gap: 20px;
    }

    .res-card {
        scroll-snap-align: center;
        width: 85vw;
        min-width: 85vw;
    }

    .hero-section {
        height: 40vh;
    }

    .page-title {
        font-size: 2.8rem;
    }
}

.scroll-premium::-webkit-scrollbar {
    height: 6px;
}

.scroll-premium::-webkit-scrollbar-thumb {
    background: rgba(26, 136, 141, 0.2);
    border-radius: 10px;
}

.scroll-premium::-webkit-scrollbar-track {
    background: transparent;
}
</style>
