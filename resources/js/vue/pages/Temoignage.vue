<template>
    <div class="temoignage-page">
        <ShaderBackground :colors="{ primary: '#4A1E82', secondary: '#3B1545', accent: '#250040', dark: '#050505' }">

            <!-- VUE MOBILE : Liste unique mélangée -->
            <div v-if="isMobile" class="mobile-container" @scroll="handleMobileScroll">
                <div class="mobile-list">
                    <TemoignageCard v-for="t in mobileTestimonials" :key="t.IdTemoignage" class="mobile-card"
                        :name="t.utilisateur?.profil ? `${t.utilisateur.profil.Prenom} ${t.utilisateur.profil.Nom}` : (t.Auteur || $t('Temoignage.Membre'))"
                        :avatar="t.utilisateur?.profil?.PhotoProfil" :role="t.utilisateur?.profil?.Metier"
                        :userId="t.IdUtilisateur" @open-profile="openPublicProfile" :text="t.ContenuTexte"
                        :variant="t.Type !== 'Texte' ? 'media' : 'default'">

                        <template #media v-if="t.Type !== 'Texte'">
                            <div class="media-container mobile-media" v-if="t.Type === 'Photo'">
                                <img :src="t.CheminFichier" :alt="'Témoignage'" loading="lazy" />
                            </div>
                            <div class="media-container video-container mobile-media" v-if="t.Type === 'Video'">
                                <iframe v-if="isYoutube(t.CheminFichier)" :src="getYoutubeEmbed(t.CheminFichier)"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen>
                                </iframe>
                                <video v-else controls class="video-player">
                                    <source :src="t.CheminFichier" type="video/mp4">
                                    {{ $t('Temoignage.votre_navigateur_ne') }}
                                </video>
                            </div>
                        </template>
                    </TemoignageCard>

                    <div v-if="mobileTestimonials.length === 0" class="empty-state">
                        <p>{{ $t('Temoignage.AucunTemoignage') }}</p>
                    </div>
                </div>
            </div>

            <!-- VUE DESKTOP : 3 Colonnes -->
            <div v-else class="split-container">
                <div class="column text-column" @scroll="handleTextScroll" ref="textColumn" data-lenis-prevent>
                    <div class="testimonials-wrapper" ref="textSet1">
                        <TemoignageCard v-for="t in textTestimonials" :key="t.IdTemoignage"
                            :name="t.utilisateur?.profil ? `${t.utilisateur.profil.Prenom} ${t.utilisateur.profil.Nom}` : (t.Auteur || $t('Temoignage.Membre'))"
                            :avatar="t.utilisateur?.profil?.PhotoProfil" :role="t.utilisateur?.profil?.Metier"
                            :userId="t.IdUtilisateur" @open-profile="openPublicProfile" :text="t.ContenuTexte" />
                    </div>

                    <div class="testimonials-wrapper" ref="textSet2" v-if="textTestimonials.length > 0">
                        <TemoignageCard v-for="t in textTestimonials" :key="'clone-' + t.IdTemoignage"
                            :name="t.utilisateur?.profil ? `${t.utilisateur.profil.Prenom} ${t.utilisateur.profil.Nom}` : (t.Auteur || $t('Temoignage.Membre'))"
                            :avatar="t.utilisateur?.profil?.PhotoProfil" :role="t.utilisateur?.profil?.Metier"
                            :userId="t.IdUtilisateur" @open-profile="openPublicProfile" :text="t.ContenuTexte" />
                    </div>
                    <div v-if="textTestimonials.length === 0" class="empty-state">
                        <p>{{ $t('Temoignage.AucunEcrit') }}</p>
                    </div>
                </div>

                <div class="column photo-column" @scroll="handlePhotoScroll" ref="photoColumn" data-lenis-prevent>

                    <div class="testimonials-wrapper" ref="photoSet1">
                        <TemoignageCard v-for="t in photoTestimonials" :key="t.IdTemoignage"
                            :name="t.utilisateur?.profil ? `${t.utilisateur.profil.Prenom} ${t.utilisateur.profil.Nom}` : (t.Auteur || $t('Temoignage.Membre'))"
                            :avatar="t.utilisateur?.profil?.PhotoProfil" :role="t.utilisateur?.profil?.Metier"
                            :userId="t.IdUtilisateur" @open-profile="openPublicProfile" :text="t.ContenuTexte"
                            variant="media">
                            <template #media>
                                <div class="media-container" v-if="t.Type === 'Photo'">
                                    <img :src="t.CheminFichier" :alt="'Témoignage'" loading="lazy" />
                                </div>
                            </template>
                        </TemoignageCard>
                    </div>
                    <div class="testimonials-wrapper" ref="photoSet2" v-if="photoTestimonials.length > 0">
                        <TemoignageCard v-for="t in photoTestimonials" :key="'clone-' + t.IdTemoignage"
                            :name="t.utilisateur?.profil ? `${t.utilisateur.profil.Prenom} ${t.utilisateur.profil.Nom}` : (t.Auteur || $t('Temoignage.Membre'))"
                            :avatar="t.utilisateur?.profil?.PhotoProfil" :role="t.utilisateur?.profil?.Metier"
                            :userId="t.IdUtilisateur" @open-profile="openPublicProfile" :text="t.ContenuTexte"
                            variant="media">
                            <template #media>
                                <div class="media-container" v-if="t.Type === 'Photo'">
                                    <img :src="t.CheminFichier" :alt="'Témoignage'" loading="lazy" />
                                </div>
                            </template>
                        </TemoignageCard>
                    </div>
                    <div v-if="photoTestimonials.length === 0" class="empty-state">
                        <p>{{ $t('Temoignage.AucunPhoto') }}</p>
                    </div>
                </div>

                <div class="column video-column" @scroll="handleVideoScroll" ref="videoColumn" data-lenis-prevent>
                    <div class="testimonials-wrapper" ref="videoSet1">
                        <TemoignageCard v-for="t in videoTestimonials" :key="t.IdTemoignage"
                            :name="t.utilisateur?.profil ? `${t.utilisateur.profil.Prenom} ${t.utilisateur.profil.Nom}` : (t.Auteur || $t('Temoignage.Membre'))"
                            :avatar="t.utilisateur?.profil?.PhotoProfil" :role="t.utilisateur?.profil?.Metier"
                            :userId="t.IdUtilisateur" @open-profile="openPublicProfile" :text="t.ContenuTexte"
                            variant="media">
                            <template #media>
                                <div class="media-container video-container" v-if="t.Type === 'Video'">
                                    <iframe v-if="isYoutube(t.CheminFichier)" :src="getYoutubeEmbed(t.CheminFichier)"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen>
                                    </iframe>
                                    <video v-else controls class="video-player">
                                        <source :src="t.CheminFichier" type="video/mp4">
                                        Votre navigateur ne supporte pas la vidéo.
                                    </video>
                                </div>
                            </template>
                        </TemoignageCard>
                    </div>
                    <div class="testimonials-wrapper" ref="videoSet2" v-if="videoTestimonials.length > 0">
                        <TemoignageCard v-for="t in videoTestimonials" :key="'clone-' + t.IdTemoignage"
                            :name="t.utilisateur?.profil ? `${t.utilisateur.profil.Prenom} ${t.utilisateur.profil.Nom}` : (t.Auteur || $t('Temoignage.Membre'))"
                            :avatar="t.utilisateur?.profil?.PhotoProfil" :role="t.utilisateur?.profil?.Metier"
                            :userId="t.IdUtilisateur" @open-profile="openPublicProfile" :text="t.ContenuTexte"
                            variant="media">
                            <template #media>
                                <div class="media-container video-container" v-if="t.Type === 'Video'">
                                    <iframe v-if="isYoutube(t.CheminFichier)" :src="getYoutubeEmbed(t.CheminFichier)"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen>
                                    </iframe>
                                    <video v-else controls class="video-player">
                                        <source :src="t.CheminFichier" type="video/mp4">
                                        Votre navigateur ne supporte pas la vidéo.
                                    </video>
                                </div>
                            </template>
                        </TemoignageCard>
                    </div>
                    <div v-if="videoTestimonials.length === 0" class="empty-state">
                        <p>{{ $t('Temoignage.AucunVideo') }}</p>
                    </div>
                </div>
            </div>

            <div class="submission-container" @click="!user ? (showLoginModal = true) : openSubmitModal()">
                <template v-if="!user">
                    <div class="submit-trigger">
                        <LiquidGlass borderRadius="50px" class="submission-glass" center>
                            <span class="prompt-text">{{ $t('Temoignage.LoginToSubmit') }}</span>
                        </LiquidGlass>
                    </div>
                </template>
                <template v-else>
                    <div class="submit-trigger">
                        <LiquidGlass borderRadius="50px" class="submission-glass2" center>
                            <div class="trigger-content">
                                <div class="user-avatar" v-if="user.profil?.PhotoProfil">
                                    <img :src="user.profil.PhotoProfil" alt="User">
                                </div>
                                <div class="user-avatar placeholder" v-else>
                                    {{ user.profil?.Prenom?.[0] || 'U' }}
                                </div>
                                <span class="trigger-text">{{ $t('Temoignage.ShareExperience') }}</span>
                                <div class="trigger-icons">
                                    <i class="fas fa-image"></i>
                                    <i class="fas fa-video"></i>
                                </div>
                            </div>
                        </LiquidGlass>
                    </div>
                </template>
            </div>

            <PremiumModal :isOpen="showSubmitModal" :header="$t('Temoignage.ModalHeader')" @close="closeSubmitModal"
                dark width="600px">
                <form @submit.prevent="submitTestimonial" class="testimonial-form">
                    <div class="form-group">
                        <textarea v-model="form.ContenuTexte" :placeholder="$t('Temoignage.Placeholder')" rows="5"
                            class="dark-input"></textarea>
                    </div>

                    <div class="form-group row-group">
                        <label class="type-label">{{ $t('Temoignage.TypeLabel') }}</label>
                        <div class="type-selectors">
                            <button type="button" :class="{ active: form.Type === 'Texte' }"
                                @click="form.Type = 'Texte'">
                                <i class="fas fa-align-left"></i> {{ $t('Temoignage.TypeTexte') }}
                            </button>
                            <button type="button" :class="{ active: form.Type === 'Photo' }"
                                @click="form.Type = 'Photo'">
                                <i class="fas fa-image"></i> {{ $t('Temoignage.TypePhoto') }}
                            </button>
                            <button type="button" :class="{ active: form.Type === 'Video' }"
                                @click="form.Type = 'Video'">
                                <i class="fas fa-video"></i> {{ $t('Temoignage.TypeVideo') }}
                            </button>
                        </div>
                    </div>

                    <div class="form-group" v-if="form.Type !== 'Texte'">
                        <div class="upload-options">
                            <div class="upload-box" @click="$refs.fileInput.click()">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>{{ form.Fichier ? form.Fichier.name : $t('Temoignage.ImportFile') }}</span>
                                <input type="file" ref="fileInput" hidden @change="handleFileUpload"
                                    :accept="form.Type === 'Photo' ? 'image/*' : 'video/*'">
                            </div>
                            <div class="url-input-container">
                                <span class="or-separator">{{ $t('Temoignage.Or') }}</span>
                                <input type="text" v-model="form.CheminFichier"
                                    :placeholder="$t('Temoignage.PasteLink')" class="dark-input">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <PremiumButton type="submit" class="submit-btn" :disabled="form.processing"
                            :text="form.processing ? $t('Temoignage.Publishing') : $t('Temoignage.SubmitBtn')"
                            @click="submitTestimonial" />
                    </div>
                </form>
            </PremiumModal>

            <LoginModal :isOpen="showLoginModal" @close="showLoginModal = false" />
            <PublicProfileModal :isOpen="showPublicProfile" :userId="selectedUserId"
                @close="showPublicProfile = false" />
        </ShaderBackground>
    </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage, useForm } from '@inertiajs/vue3';
import LiquidGlass from '../components/ui/LiquidGlass.vue';
import PremiumModal from '../components/ui/PremiumModal.vue';
import LoginModal from '../components/auth/LoginModal.vue';
import PremiumButton from '../components/ui/PremiumButton.vue';
import TemoignageCard from '../components/ui/TemoignageCard.vue';
import PublicProfileModal from '../components/ui/PublicProfileModal.vue';
import ShaderBackground from '../components/ui/ShaderBackground.vue';
// Lenis removed to prioritize working auto-scroll
import { gsap } from 'gsap';

const page = usePage();
const $t = (key) => page.props.translations[key] ?? key;
const currentLocale = computed(() => page.props.locale || 'fr');

const { auth } = usePage().props;
const user = computed(() => auth.user);

const props = defineProps({
    temoignages: Array
});

const showSubmitModal = ref(false);
const showLoginModal = ref(false);
const showPublicProfile = ref(false);
const selectedUserId = ref(null);

const openPublicProfile = (userId) => {
    selectedUserId.value = userId;
    showPublicProfile.value = true;
};
const form = useForm({
    Type: 'Texte',
    ContenuTexte: '',
    CheminFichier: '',
    Fichier: null
});

const openSubmitModal = () => {
    showSubmitModal.value = true;
};

const closeSubmitModal = () => {
    showSubmitModal.value = false;
    form.reset();
};

const handleFileUpload = (e) => {
    form.Fichier = e.target.files[0];
};

const submitTestimonial = () => {
    form.post('/temoignages', {
        onSuccess: () => {
            closeSubmitModal();
        },
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString(currentLocale.value, {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const isYoutube = (url) => {
    if (!url) return false;
    return url.includes('youtube.com') || url.includes('youtu.be');
};

const getYoutubeEmbed = (url) => {
    if (!url) return '';
    let videoId = '';
    if (url.includes('youtu.be')) {
        videoId = url.split('/').pop();
    } else if (url.includes('v=')) {
        videoId = url.split('v=')[1].split('&')[0];
    }
    return `https://www.youtube.com/embed/${videoId}`;
};

const textColumn = ref(null);
const textSet1 = ref(null);
const photoColumn = ref(null);
const photoSet1 = ref(null);
const videoColumn = ref(null);
const videoSet1 = ref(null);

const handleTextScroll = (e) => {
    if (!textSet1.value) return;
    const scrollTop = e.target.scrollTop;
    const set1Height = textSet1.value.clientHeight;

    // Pause auto-scroll on manual interaction
    activeColumns.value.text = false;
    clearTimeout(scrollTimers.text);
    scrollTimers.text = setTimeout(() => { activeColumns.value.text = true; }, 3000);

    if (scrollTop >= set1Height) {
        e.target.scrollTop = scrollTop - set1Height;
    }
};

const handleMobileScroll = (e) => {
    // Logique scroll mobile si besoin (infinite loading par ex)
};

const handlePhotoScroll = (e) => {
    if (!photoSet1.value) return;
    const scrollTop = e.target.scrollTop;
    const set1Height = photoSet1.value.clientHeight;

    activeColumns.value.photo = false;
    clearTimeout(scrollTimers.photo);
    scrollTimers.photo = setTimeout(() => { activeColumns.value.photo = true; }, 3000);

    if (scrollTop >= set1Height) {
        e.target.scrollTop = scrollTop - set1Height;
    }
};

const handleVideoScroll = (e) => {
    if (!videoSet1.value) return;
    const scrollTop = e.target.scrollTop;
    const set1Height = videoSet1.value.clientHeight;

    activeColumns.value.video = false;
    clearTimeout(scrollTimers.video);
    scrollTimers.video = setTimeout(() => { activeColumns.value.video = true; }, 3000);

    if (scrollTop >= set1Height) {
        e.target.scrollTop = scrollTop - set1Height;
    }
};

// Auto-scroll Animation Logic
const activeColumns = ref({
    text: true,
    photo: true,
    video: true
});

const scrollTimers = {
    text: null,
    photo: null,
    video: null
};

let animationFrame;
const animate = () => {
    // Auto-scroll logic via direct scrollTop
    if (activeColumns.value.text && textColumn.value && textTestimonials.value.length > 0) {
        textColumn.value.scrollTop += 0.6;
    }
    if (activeColumns.value.photo && photoColumn.value && photoTestimonials.value.length > 0) {
        photoColumn.value.scrollTop += 0.6;
    }
    if (activeColumns.value.video && videoColumn.value && videoTestimonials.value.length > 0) {
        videoColumn.value.scrollTop += 0.6;
    }

    animationFrame = requestAnimationFrame(animate);
};

const isMobile = ref(false);

const checkMobile = () => {
    isMobile.value = window.innerWidth <= 768;
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);

    animationFrame = requestAnimationFrame(animate);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
    if (animationFrame) cancelAnimationFrame(animationFrame);
});

const mobileTestimonials = computed(() => {
    if (!props.temoignages) return [];
    const shuffled = [...props.temoignages].sort(() => 0.5 - Math.random());
    return shuffled;
});

const textTestimonials = computed(() => {
    if (!props.temoignages) return [];
    return props.temoignages.filter(t => t.Type === 'Texte');
});

const photoTestimonials = computed(() => {
    if (!props.temoignages) return [];
    return props.temoignages.filter(t => t.Type === 'Photo');
});

const videoTestimonials = computed(() => {
    if (!props.temoignages) return [];
    return props.temoignages.filter(t => t.Type === 'Video');
});

</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

.temoignage-page {
    height: 100vh;
    color: #e0e0e0;
    font-family: 'Plus Jakarta Sans', sans-serif;
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
    overscroll-behavior: none;
    background-color: transparent;
}

/* Hide footer on this page */
:global(body:has(.temoignage-page) footer:not(.footer-menu)),
:global(body:has(.temoignage-page) .app-footer) {
    visibility: hidden !important;
    height: 0 !important;
    overflow: hidden !important;
    margin: 0 !important;
    padding: 0 !important;
}

.split-container {
    display: flex;
    height: 100vh;
    width: 100%;
    z-index: 1;
}

.column {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 10px 20px;
    padding-top: 15vh;
    overflow-y: auto;
    scrollbar-width: none;
    position: relative;
    overscroll-behavior: none;
}

.testimonials-wrapper {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding-bottom: 20px;
}

.text-column,
.photo-column {
    border-right: 1px solid rgba(255, 255, 255, 0.2);
}

.column::-webkit-scrollbar {
    width: 6px;
}

.column::-webkit-scrollbar-track {
    background: transparent;
}

.column::-webkit-scrollbar-thumb {
    background-color: #333;
    border-radius: 6px;
}

.column-header {
    margin-bottom: 40px;
    text-align: center;
    position: sticky;
    top: 0;
    background: linear-gradient(180deg, #050505 0%, #050505 80%, transparent 100%);
    padding-bottom: 20px;
    padding-top: 10px;
    z-index: 10;
}

.column-header h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
    background: linear-gradient(90deg, #fff, #aaa);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.column-header p {
    color: #888;
    margin-top: 10px;
}

.testimonials-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding-bottom: 60px;
    max-width: 100%;
    margin: 0 auto;
    width: 100%;
}

.testimonial-card {
    background: #111;
    border: 1px solid rgba(255, 255, 255, 0.05);
    padding: 30px;
    transition: transform 0.3s, box-shadow 0.3s;
}

.testimonial-card:hover {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    border-color: rgba(255, 255, 255, 0.1);
}

.quote-icon {
    font-size: 3rem;
    color: #333;
    line-height: 1;
    margin-bottom: 10px;
}

.text-content {
    font-size: 0.9rem;
    line-height: 1.2;
    color: #e0dfdf;
    margin-bottom: 20px;
}

.media-text-content {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.text-content.small {
    font-size: 1rem;
    margin-bottom: 10px;
}

.author-info {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.author-details {
    display: flex;
    gap: 12px;
    align-items: center;
}

.author-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.author-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.author-avatar.placeholder {
    background: linear-gradient(135deg, #333, #555);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: white;
    font-size: 1.2rem;
}

.author-meta {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.author-name {
    font-weight: 700;
    color: white;
    font-size: 0.95rem;
    line-height: 1.2;
}

.author-job {
    font-size: 0.8rem;
    color: #888;
    margin-top: 2px;
}

.video-container {
    padding-bottom: 56.25%;
    height: 0;
    position: relative;
    background: black;
}

.video-container iframe,
.video-container video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.media-container {
    border-radius: 12px;
    overflow: hidden;
    position: relative;
    background: #000;
    width: 90%;
    margin-left: auto;
    display: block;
    max-height: 45vh;
}

.media-container img,
.media-container video {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.empty-state {
    text-align: center;
    color: #666;
    margin-top: 50px;
}

@media (max-width: 768px) {
    .temoignage-page {
        overflow: hidden;
        height: 100vh;
    }

    .split-container {
        flex-direction: row;
        height: 100vh;
        width: 100vw;
        overflow-x: auto;
        padding-top: 80px;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
    }

    .column {
        height: auto;
        min-width: 85vw;
        max-width: 85vw;
        padding: 20px 15px 120px 15px;
        border-right: none;
        border-bottom: none;
        overflow-y: auto;
        scroll-snap-align: center;
        margin-right: 15px;
    }

    .column:first-child {
        margin-left: 15px;
    }

    .submission-container {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 90%;
        max-width: 400px;
        padding: 0;
        z-index: 100;
    }

    .prompt-text,
    .trigger-text {
        font-size: 0.9rem;
    }

    .author-name {
        font-size: 0.85rem;
    }
}

.submission-container {
    position: absolute;
    bottom: 13vh;
    left: 50%;
    display: flex;
    justify-content: center;
    transform: translateX(-50%);
    width: 100%;
    max-width: 600px;
    z-index: 110;
    padding: 0 20px;
    pointer-events: none;
}

.submission-glass {
    height: 60px;
    width: 100%;
    transition: transform 0.3s, box-shadow 0.3s;
    pointer-events: auto;
}

.submission-glass2 {
    height: 60px;
    width: 100%;
    transition: transform 0.3s, box-shadow 0.3s;
    pointer-events: auto;
    display: flex;
    align-items: center;
    justify-content: center;
}

.submission-glass:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
}

.login-prompt-link {
    text-decoration: none;
    display: block;
}

.prompt-text {
    color: #c9c9c9;
    width: 90%;
    margin-top: 1vh;
    text-align: center;
    font-weight: 500;
    font-size: 1.1rem;
}

.trigger-content {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 0 20px;
    gap: 15px;
    position: relative;
    z-index: 20;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    position: relative;
    z-index: 30;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-avatar.placeholder {
    background: #444;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
}

.trigger-text {
    flex: 1;
    color: #c2c2c2;
    text-align: left;
    font-size: 0.95rem;
}

.trigger-icons {
    display: flex;
    gap: 15px;
    font-size: 1.3rem;
    margin-right: 0.5vw;
    color: #ffffff;
}

.testimonial-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding-top: 20px;
}

.dark-input {
    width: 100%;
    background: #111;
    border: 1px solid #333;
    border-radius: 12px;
    padding: 15px;
    color: white;
    font-family: inherit;
    font-size: 1rem;
    outline: none;
    transition: border-color 0.3s;
}

.dark-input:focus {
    border-color: #8A38F5;
}

.row-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.type-label {
    font-size: 0.9rem;
    color: #a2a2a2;
}

.type-selectors {
    display: flex;
    gap: 10px;
}

.type-selectors button {
    flex: 1;
    background: #111;
    border: 1px solid #333;
    padding: 10px;
    border-radius: 10px;
    color: #888;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.type-selectors button.active {
    background: #8A38F5;
    color: white;
    border-color: #8A38F5;
}

.upload-options {
    display: flex;
    flex-direction: column;
    gap: 15px;
    background: #090909;
    padding: 15px;
    border-radius: 12px;
    border: 1px dashed #333;
}

.upload-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    cursor: pointer;
    color: #888;
}

.upload-box i {
    font-size: 2rem;
    margin-bottom: 5px;
}

.or-separator {
    display: block;
    text-align: center;
    font-size: 0.8rem;
    color: #444;
    font-weight: 700;
    margin-bottom: 10px;
}

.submit-btn {
    background: linear-gradient(130deg, #0d7bc0, #8A38F5);
    border: none;
    color: white;
    padding: 15px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.1rem;
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(138, 56, 245, 0.4);
}

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

@media (max-width: 768px) {
    .temoignage-page {
        height: 100vh;
        overflow: hidden;
    }

    .mobile-container {
        height: 100%;
        width: 100%;
        overflow-y: auto;
        padding-top: 100px;
        padding-bottom: 120px;
        -webkit-overflow-scrolling: touch;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .mobile-list {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        width: 100%;
        padding: 0 2px;
    }

    .mobile-card {
        width: 100%;
        max-width: 95%;
        margin: 0 auto;
    }

    .mobile-media {
        max-height: 300px !important;
        margin-top: 15px;
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
    }

    .split-container {
        display: none !important;
    }

    .submission-container {
        position: fixed;
        left: 50%;
        transform: translateX(-50%);
        width: 85%;
        max-width: 350px;
        padding-top: 3px;
        padding: 0;
        font-size: 1.2em;
        z-index: 1000;
    }

    /* --- Personnalisation Mobile --- */

    .mobile-card :deep(.temoignage-card) {
        padding: 20px !important;
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    /* Remove hover effects on mobile */
    .mobile-card :deep(.temoignage-card:hover),
    .mobile-card :deep(.temoignage-card:focus),
    .mobile-card :deep(.temoignage-card:active),
    .mobile-card:hover,
    .mobile-card:focus,
    .mobile-card:active {
        transform: none !important;
        box-shadow: none !important;
    }

    /* Nom de l'auteur */
    .mobile-card :deep(.author-name) {
        font-size: 0.9rem !important;
        /* Ajuster la taille du nom */
        line-height: 1.2;
    }

    /* Rôle de l'auteur (ex: CEO) */
    .mobile-card :deep(.author-role) {
        font-size: 0.7rem !important;
    }

    /* Texte principal (Type: Texte) */
    .mobile-card :deep(.testimonial-text.text-mode) {
        font-size: 0.95rem !important;
        /* Ajuster la taille du texte témoignage */
        line-height: 1.2;
    }

    /* Texte légende (Type: Média) */
    .mobile-card :deep(.testimonial-text.media-mode) {
        font-size: 0.85rem !important;
        line-height: 1.1;
        width: 100% !important;
        margin-left: 0 !important;
        margin-bottom: 10px !important;
    }

    .mobile-card :deep(.author-avatar) {
        width: 35px;
        height: 35px;
    }

    .mobile-card :deep(.avatar-wrapper) {
        width: 35px;
        height: 35px;
    }

    .prompt-text,
    .trigger-text {
        font-size: 0.95rem;
        width: 95%;
        margin-top: 0;
        padding-top: 0;
        line-height: 1.2;
    }

    .author-name {
        font-size: 0.95rem !important;
    }
}
</style>
