<template>
    <div class="temoignage-page">
        <ShaderBackground :colors="{ primary: '#4A1E82', secondary: '#3B1545', accent: '#250040', dark: '#050505' }">
            <div class="split-container">
                <div class="column text-column" @scroll="handleTextScroll" ref="textColumn" data-lenis-prevent>
                    <div class="testimonials-wrapper" ref="textSet1">
                        <TemoignageCard v-for="t in textTestimonials" :key="t.IdTemoignage"
                            :name="t.utilisateur?.profil ? `${t.utilisateur.profil.Prenom} ${t.utilisateur.profil.Nom}` : (t.Auteur || 'Membre')"
                            :avatar="t.utilisateur?.profil?.PhotoProfil" :role="t.utilisateur?.profil?.Metier"
                            :text="t.ContenuTexte" />
                    </div>

                    <div class="testimonials-wrapper" ref="textSet2" v-if="textTestimonials.length > 0">
                        <TemoignageCard v-for="t in textTestimonials" :key="'clone-' + t.IdTemoignage"
                            :name="t.utilisateur?.profil ? `${t.utilisateur.profil.Prenom} ${t.utilisateur.profil.Nom}` : (t.Auteur || 'Membre')"
                            :avatar="t.utilisateur?.profil?.PhotoProfil" :role="t.utilisateur?.profil?.Metier"
                            :text="t.ContenuTexte" />
                    </div>
                    <div v-if="textTestimonials.length === 0" class="empty-state">
                        <p>Aucun témoignage écrit pour le moment.</p>
                    </div>
                </div>

                <div class="column media-column" @scroll="handleMediaScroll" ref="mediaColumn" data-lenis-prevent>
                    <div class="testimonials-wrapper" ref="mediaSet1">
                        <TemoignageCard v-for="t in mediaTestimonials" :key="t.IdTemoignage"
                            :name="t.utilisateur?.profil ? `${t.utilisateur.profil.Prenom} ${t.utilisateur.profil.Nom}` : (t.Auteur || 'Membre')"
                            :avatar="t.utilisateur?.profil?.PhotoProfil" :role="t.utilisateur?.profil?.Metier"
                            :text="t.ContenuTexte" variant="media">
                            <template #media>
                                <div class="media-container" v-if="t.Type === 'Photo'">
                                    <img :src="t.CheminFichier" :alt="'Témoignage'" loading="lazy" />
                                </div>
                                <div class="media-container video-container" v-else-if="t.Type === 'Video'">
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
                    <div class="testimonials-wrapper" ref="mediaSet2" v-if="mediaTestimonials.length > 0">
                        <TemoignageCard v-for="t in mediaTestimonials" :key="'clone-' + t.IdTemoignage"
                            :name="t.utilisateur?.profil ? `${t.utilisateur.profil.Prenom} ${t.utilisateur.profil.Nom}` : (t.Auteur || 'Membre')"
                            :avatar="t.utilisateur?.profil?.PhotoProfil" :role="t.utilisateur?.profil?.Metier"
                            :text="t.ContenuTexte" variant="media">
                            <template #media>
                                <div class="media-container" v-if="t.Type === 'Photo'">
                                    <img :src="t.CheminFichier" :alt="'Témoignage'" loading="lazy" />
                                </div>
                                <div class="media-container video-container" v-else-if="t.Type === 'Video'">
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
                    <div v-if="mediaTestimonials.length === 0" class="empty-state">
                        <p>Aucun témoignage photo/vidéo pour le moment.</p>
                    </div>
                </div>
            </div>

            <div class="submission-container">
                <template v-if="!user">
                    <div @click="showLoginModal = true" class="submit-trigger">
                        <LiquidGlass borderRadius="50px" class="submission-glass">
                            <span class="prompt-text">Connectez-vous pour témoigner</span>
                        </LiquidGlass>
                    </div>
                </template>
                <template v-else>
                    <div @click="openSubmitModal" class="submit-trigger">
                        <LiquidGlass borderRadius="50px" class="submission-glass">
                            <div class="trigger-content">
                                <div class="user-avatar" v-if="user.profil?.PhotoProfil">
                                    <img :src="user.profil.PhotoProfil" alt="User">
                                </div>
                                <div class="user-avatar placeholder" v-else>
                                    {{ user.profil?.Prenom?.[0] || 'U' }}
                                </div>
                                <span class="trigger-text">Partagez votre expérience</span>
                                <div class="trigger-icons">
                                    <i class="fas fa-image"></i>
                                    <i class="fas fa-video"></i>
                                </div>
                            </div>
                        </LiquidGlass>
                    </div>
                </template>
            </div>

            <PremiumModal :isOpen="showSubmitModal" header="Publier un témoignage" @close="closeSubmitModal" dark
                width="600px">
                <form @submit.prevent="submitTestimonial" class="testimonial-form">
                    <div class="form-group">
                        <textarea v-model="form.ContenuTexte" placeholder="Racontez-nous votre parcours..." rows="5"
                            class="dark-input"></textarea>
                    </div>

                    <div class="form-group row-group">
                        <label class="type-label">Type de témoignage :</label>
                        <div class="type-selectors">
                            <button type="button" :class="{ active: form.Type === 'Texte' }"
                                @click="form.Type = 'Texte'">
                                <i class="fas fa-align-left"></i> Texte
                            </button>
                            <button type="button" :class="{ active: form.Type === 'Photo' }"
                                @click="form.Type = 'Photo'">
                                <i class="fas fa-image"></i> Photo
                            </button>
                            <button type="button" :class="{ active: form.Type === 'Video' }"
                                @click="form.Type = 'Video'">
                                <i class="fas fa-video"></i> Vidéo
                            </button>
                        </div>
                    </div>

                    <div class="form-group" v-if="form.Type !== 'Texte'">
                        <div class="upload-options">
                            <div class="upload-box" @click="$refs.fileInput.click()">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>{{ form.Fichier ? form.Fichier.name : 'Importer un fichier' }}</span>
                                <input type="file" ref="fileInput" hidden @change="handleFileUpload"
                                    :accept="form.Type === 'Photo' ? 'image/*' : 'video/*'">
                            </div>
                            <div class="url-input-container">
                                <span class="or-separator">OU</span>
                                <input type="text" v-model="form.CheminFichier"
                                    placeholder="Coller un lien (YouTube, Cloudinary, etc.)" class="dark-input">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <PremiumButton type="submit" class="submit-btn" :disabled="form.processing"
                            :text="form.processing ? 'Publication' : 'Publier votre témoignage'"
                            @click="submitTestimonial" />
                    </div>
                </form>
            </PremiumModal>

            <LoginModal :isOpen="showLoginModal" @close="showLoginModal = false" />
        </ShaderBackground>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, usePage, useForm } from '@inertiajs/vue3';
import LiquidGlass from '../components/ui/LiquidGlass.vue';
import PremiumModal from '../components/ui/PremiumModal.vue';
import LoginModal from '../components/auth/LoginModal.vue';
import PremiumButton from '../components/ui/PremiumButton.vue';
import TemoignageCard from '../components/ui/TemoignageCard.vue';
import ShaderBackground from '../components/ui/ShaderBackground.vue';

const { auth } = usePage().props;
const user = computed(() => auth.user);

const props = defineProps({
    temoignages: Array
});

const showSubmitModal = ref(false);
const showLoginModal = ref(false);
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

const textTestimonials = computed(() => {
    if (!props.temoignages) return [];
    return props.temoignages.filter(t => t.Type === 'Texte');
});

const mediaTestimonials = computed(() => {
    if (!props.temoignages) return [];
    return props.temoignages.filter(t => ['Photo', 'Video'].includes(t.Type));
});

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('fr-FR', {
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

// Infinite Scroll Logic
const textColumn = ref(null);
const textSet1 = ref(null);
const mediaColumn = ref(null);
const mediaSet1 = ref(null);

const handleTextScroll = (e) => {
    if (!textSet1.value) return;
    const scrollTop = e.target.scrollTop;
    const set1Height = textSet1.value.clientHeight;

    // Using a threshold slightly less than height to avoid glitches
    if (scrollTop >= set1Height) {
        e.target.scrollTop = scrollTop - set1Height;
    }
};

const handleMediaScroll = (e) => {
    if (!mediaSet1.value) return;
    const scrollTop = e.target.scrollTop;
    const set1Height = mediaSet1.value.clientHeight;

    if (scrollTop >= set1Height) {
        e.target.scrollTop = scrollTop - set1Height;
    }
};
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
    background-color: #050505;
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

.text-column {
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
    font-size: 2.5rem;
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
    .split-container {
        flex-direction: column;
    }

    .column {
        height: auto;
        min-height: 50vh;
        padding: 20px;
    }

    .back-button-container {
        position: static;
        padding: 20px;
    }

    .split-container {
        padding-top: 0;
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
    color: #888;
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
</style>
