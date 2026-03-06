<template>
    <Teleport to="body">
        <div ref="profileOverlay" class="public-profile-overlay" :class="{ 'is-open': isOpen }">
            <div class="global-close-btn" @click="emit('close')">
                <i class="fa-solid fa-times"></i>
            </div>

            <div v-if="loading" class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Chargement du profil...</p>
            </div>

            <div v-else-if="userData" class="menu-columns">
                <div class="menu-column user-profile-column animation-slide-up">
                    <div class="profile-header-section">
                        <div class="avatar-container">
                            <img v-if="userData.profil?.PhotoProfil" :src="userData.profil.PhotoProfil" alt="Avatar"
                                class="large-avatar">
                            <div v-else class="large-avatar placeholder">
                                {{ userData.profil?.Prenom?.[0] || 'U' }}
                            </div>
                            <div class="avatar-glow"></div>
                        </div>
                        <div class="profile-title-group">
                            <h2 class="user-fullname">{{ userData.profil?.Prenom }}</h2>
                            <h2 class="user-fullname bold-text">{{ userData.profil?.Nom }}</h2>
                            <span class="user-metier" :class="{ 'is-missing': !userData.profil?.Metier }">
                                {{ userData.profil?.Metier || 'Métier non renseigné' }}
                            </span>
                        </div>
                    </div>

                    <div class="profile-info-content">
                        <div class="profile-section bio-section"
                            :class="{ 'is-missing': !userData.profil?.Biographie }">
                            <div class="separator-line"></div>
                            <p class="bio-text">{{ userData.profil?.Biographie || 'Aucune biographie disponible pour le moment.' }}</p>
                        </div>

                        <div class="separator-line"></div>

                        <div class="contact-info">
                            <div class="contact-item"
                                :class="{ 'is-missing': !(userData.profil?.EmailContact || userData.email) }">
                                <span class="text-bold">{{ userData.profil?.EmailContact || userData.email || 'Email de contact non renseigné' }}</span>
                            </div>
                            <div class="contact-item" :class="{ 'is-missing': !userData.profil?.NumeroTelephone }">
                                <span class="field-value">{{ userData.profil?.NumeroTelephone || 'Téléphone non renseigné' }}</span>
                            </div>
                        </div>

                        <div class="social-icons" :class="{ 'is-missing': !userData.profil?.reseaux_sociaux?.length }">
                            <template v-if="userData.profil?.reseaux_sociaux?.length > 0">
                                <a v-for="social in userData.profil.reseaux_sociaux" :key="social.Nom"
                                    :href="social.Lien" target="_blank" class="social-circle" :title="social.Nom">
                                    <i :class="getSocialIcon(social.Nom)"></i>
                                </a>
                            </template>
                            <template v-else>
                                <div class="social-circle">
                                    <i class="fas fa-share-nodes"></i>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Colonne 2 : Témoignages (Style Profil.vue Columns 1-4) -->
                <div class="menu-column border-left col-testimonials animation-slide-up" style="animation-delay: 0.1s;">
                    <div class="vertical-title full-center cube-scene">
                        <div class="cube-container">
                            <div class="cube-face face-front">
                                <span class="rotate-text">{{ $t('PublicProfile.Testimonials') }}</span>
                            </div>
                            <div class="cube-face face-right">
                                <div class="scrollable-content">
                                    <div class="testimonials-list" v-if="userData.temoignages?.length > 0">
                                        <div v-for="t in userData.temoignages" :key="t.IdTemoignage"
                                            class="mini-testimonial-card">
                                            <div class="testimonial-header">
                                                <span class="testimonial-date">{{ formatDate(t.DateCreation) }}</span>
                                            </div>
                                            <p class="testimonial-text">{{ t.ContenuTexte }}</p>
                                        </div>
                                    </div>
                                    <div v-else class="empty-column-state">
                                        <p>{{ $t('PublicProfile.NoTestimonials') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colonne 3 : Badge Réussites (Style Profil.vue Columns 1-4) -->
                <div class="menu-column border-left col-badges animation-slide-up" style="animation-delay: 0.2s;">
                    <div class="vertical-title full-center cube-scene">
                        <div class="cube-container">
                            <div class="cube-face face-front">
                                <span class="rotate-text">{{ $t('PublicProfile.Badges') }}</span>
                            </div>
                            <div class="cube-face face-right">
                                <div class="scrollable-content">
                                    <div class="badges-grid" v-if="userData.reussites?.length > 0">
                                        <div v-for="r in userData.reussites" :key="r.id" class="mini-badge-card">
                                            <div class="badge-visual">
                                                <video v-if="r.video_link" :src="r.video_link" autoplay loop muted
                                                    playsinline></video>
                                                <i v-else class="fas fa-medal"></i>
                                            </div>
                                            <div class="badge-info">
                                                <span class="badge-name">{{ r.nom }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="empty-column-state">
                                        <p>{{ $t('PublicProfile.NoBadges') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- État vide si ni loading ni userData -->
            <div v-else class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Initialisation...</p>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { gsap } from 'gsap';

const props = defineProps({
    isOpen: Boolean,
    userId: [Number, String]
});

const emit = defineEmits(['close']);

const page = usePage();
const $t = (key) => {
    if (!page.props.translations) return key;
    return page.props.translations[key] || key;
};
const currentLocale = computed(() => page.props.locale || 'fr');

const loading = ref(false);
const userData = ref(null);
const profileOverlay = ref(null);

const fetchProfile = async (id) => {
    if (!id) return;
    loading.value = true;
    try {
        const response = await axios.get(`/api/public-profile/${id}`);
        userData.value = response.data;
    } catch (error) {
        console.error("Failed to fetch public profile", error);
    } finally {
        loading.value = false;
    }
};

watch(() => props.isOpen, async (newVal) => {
    if (newVal) {
        if (props.userId) {
            fetchProfile(props.userId);
        }

        await nextTick();
        if (profileOverlay.value) {
            gsap.to(profileOverlay.value, {
                height: '85vh',
                duration: 0.8,
                ease: 'expo.out'
            });

            gsap.fromTo('.menu-column',
                { y: -50, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.8, stagger: 0.1, ease: 'power3.out', delay: 0.2, overwrite: true }
            );
        }
    } else {
        if (profileOverlay.value) {
            gsap.to(profileOverlay.value, {
                height: 0,
                duration: 0.6,
                ease: 'expo.inOut'
            });
        }
    }
});

const getSocialIcon = (name) => {
    const icons = {
        Facebook: 'fab fa-facebook-f',
        WhatsApp: 'fab fa-whatsapp',
        LinkedIn: 'fab fa-linkedin-in',
        YouTube: 'fab fa-youtube',
        Instagram: 'fab fa-instagram',
        GitHub: 'fab fa-github',
        Twitter: 'fab fa-twitter',
        Discord: 'fab fa-discord'
    };
    return icons[name] || 'fas fa-link';
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    try {
        return new Date(dateString).toLocaleDateString(currentLocale.value, {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
    } catch (e) {
        return dateString;
    }
};

const handleOutsideClick = (event) => {
    if (!props.isOpen) return;
    // Si on clique sur le fond de l'overlay (qui est à height: 85vh)
    if (profileOverlay.value && event.target === profileOverlay.value) {
        emit('close');
    }
};

onMounted(() => {
    window.addEventListener('mousedown', handleOutsideClick);
});

onUnmounted(() => {
    window.removeEventListener('mousedown', handleOutsideClick);
});
</script>

<style scoped>
.public-profile-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 0;
    background-color: #F4F4F4;
    z-index: 10000;
    /* Très haut pour passer devant tout */
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
    pointer-events: none;
    border-bottom: 2px solid #000;
}

.public-profile-overlay.is-open {
    pointer-events: auto;
}

.global-close-btn {
    position: absolute;
    top: 30px;
    right: 40px;
    z-index: 10100;
    cursor: pointer;
    font-size: 1.8rem;
    color: #000;
    padding: 10px;
    transition: transform 0.3s ease, opacity 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.global-close-btn:hover {
    transform: scale(1.1) rotate(90deg);
    opacity: 0.7;
}

.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #000;
    gap: 15px;
    padding-top: 100px;
}

.loading-state i {
    font-size: 2.5rem;
    color: #8A38F5;
}

.menu-columns {
    display: flex;
    height: 100%;
    width: 100%;
}

.menu-column {
    flex: 1;
    position: relative;
    display: flex;
    flex-direction: column;
    background-color: #F4F4F4;
    transition: background-color 0.5s ease;
}

.border-left {
    border-left: 1px solid #000;
}

.cube-scene {
    perspective: 1000px;
    height: 100%;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cube-container {
    position: relative;
    width: 100%;
    height: 100%;
    scrollbar-width: none;
    transform-style: preserve-3d;
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.cube-face {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.face-front {
    transform: rotateY(0deg) translateZ(60px);
}

.face-right {
    transform: rotateY(90deg) translateZ(60px);
    padding: 40px 30px;
    background-color: transparent;
    justify-content: flex-start;
    align-items: stretch;
}

.menu-column:hover .cube-container {
    transform: rotateY(-90deg);
}

.rotate-text {
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    font-size: 3.5rem;
    letter-spacing: 2px;
    font-weight: 400;
    text-transform: uppercase;
    white-space: nowrap;
    color: #000;
}

/* Testimonials Hover Colors */
.col-testimonials:hover {
    background-color: #1A237E !important;
}

.col-testimonials:hover .rotate-text {
    color: #00E5FF;
}

.col-testimonials .face-right .testimonial-text {
    color: #FFF;
    font-weight: 500;
}

.col-testimonials .face-right .testimonial-date {
    color: rgba(255, 255, 255, 0.7);
}

.col-testimonials .mini-testimonial-card {
    border-bottom: 1px solid #ffffffcd;
    /* White horizontal line */
    padding-bottom: 20px;
    margin-bottom: 10px;
}

/* Badges Hover Colors */
.col-badges:hover {
    background-color: #8A38F5 !important;
}

.col-badges:hover .rotate-text {
    color: #FFF;
}

.mini-badge-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #000;
    /* Black cards */
    border-radius: 35px;
    /* 35px radius */
    width: 100%;
    margin-bottom: 15px;
    transition: transform 0.3s;
}

.mini-badge-card:hover {
    transform: translateY(-5px);
}

.badge-visual {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    background: #111;
    flex-shrink: 0;
    border: 1px solid #333;
}

.badge-visual video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.badge-name {
    font-size: 1rem;
    font-weight: 700;
    color: #FFF;
    text-transform: uppercase;
    text-align: center;
}

.menu-column.user-profile-column {
    background-color: #F4F4F4;
    padding: 60px 40px;
    overflow-y: auto;
    scroll-behavior: smooth;
    scrollbar-width: thin;
    scrollbar-color: #8A38F5 transparent;
}

.menu-column.user-profile-column::-webkit-scrollbar {
    width: 6px;
}

.menu-column.user-profile-column::-webkit-scrollbar-thumb {
    background: #8A38F5;
    border-radius: 10px;
}

.profile-header-section {
    display: flex;
    align-items: flex-start;
    gap: 30px;
    margin-bottom: 30px;
}

.profile-title-group {
    display: flex;
    flex-direction: column;
}

.user-fullname {
    font-size: 1.5rem;
    font-weight: 400;
    text-transform: uppercase;
    margin: 0;
    line-height: 1.1;
    color: #000;
}

.bold-text {
    font-weight: 600;
}

.user-metier {
    font-size: 1.1rem;
    color: #8A38F5;
    font-weight: 500;
    margin-top: 10px;
}

.large-avatar {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    object-fit: cover;
    background-color: #D9D9D9;
}

.large-avatar.placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #fff;
    background: #ccc;
}

.separator-line {
    height: 1px;
    background-color: #212121;
    margin: 1.5rem 0;
    width: 100%;
}

.bio-text {
    color: #040404;
    font-size: 1.3rem;
    line-height: 1.4;
    margin: 0;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 30px;
}

.text-bold {
    font-weight: 600;
    color: #000;
    font-size: 1.1rem;
}

.field-value {
    color: #333;
    font-size: 1.1rem;
}

.social-icons {
    display: flex;
    gap: 15px;
}

.social-circle {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background-color: #D9D9D9;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1a1a1a;
    transition: 0.3s;
}

.social-circle:hover {
    background-color: #8A38F5;
    color: white;
    transform: translateY(-3px);
}

.scrollable-content {
    width: 100%;
    height: 100%;
    overflow-y: auto;
    scroll-behavior: smooth;
    padding-right: 5px;
    scrollbar-width: thin;
    scrollbar-color: #8A38F5 transparent;
}

.scrollable-content::-webkit-scrollbar {
    display: block;
    width: 4px;
}

.scrollable-content::-webkit-scrollbar-track {
    background: transparent;
}

.scrollable-content::-webkit-scrollbar-thumb {
    background: #8A38F5;
    border-radius: 10px;
}

.is-missing {
    opacity: 0.35;
    filter: grayscale(0.5);
}

.testimonials-list,
.badges-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.mini-testimonial-card {
    border-bottom: 2px solid #000;
    padding-bottom: 15px;
}

.testimonial-date {
    font-size: 0.9rem;
    color: #666;
}

.testimonial-text {
    font-size: 1.1rem;
    color: inherit;
    /* Fallback to parent color (white in hover) */
    margin-top: 10px;
    line-height: 1.4;
}

.mini-badge-card {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 10px;
    border: 1px solid transparent;
    transition: 0.3s;
}

.mini-badge-card:hover {
    background: rgba(0, 0, 0, 0.05);
}

.badge-visual {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    overflow: hidden;
    background: #000;
    flex-shrink: 0;
    border: 2px solid #000;
}

.badge-visual video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.badge-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: #000;
    text-transform: uppercase;
}

.empty-column-state {
    text-align: center;
    color: #999;
    font-size: 1rem;
    padding-top: 40px;
    font-style: italic;
}

@media (max-width: 992px) {
    .menu-columns {
        flex-direction: column;
        overflow-y: auto;
    }

    .menu-column {
        flex: none;
        width: 100%;
        min-height: 300px;
        border-left: none;
        border-bottom: 1px solid #000;
        padding: 40px 20px;
    }

    .rotate-text {
        writing-mode: horizontal-tb;
        transform: none;
        font-size: 2rem;
    }

    .cube-container {
        height: 100px;
        width: 100%;
    }

    .face-right {
        transform: rotateX(-90deg) translateZ(50px);
    }

    .face-front {
        transform: rotateX(0deg) translateZ(50px);
    }

    .menu-column:hover .cube-container {
        transform: rotateX(90deg);
    }

    .public-profile-overlay {
        height: auto !important;
        max-height: 0;
        transition: max-height 0.8s ease;
    }

    .public-profile-overlay.is-open {
        max-height: 100vh;
        overflow-y: auto;
    }
}
</style>
