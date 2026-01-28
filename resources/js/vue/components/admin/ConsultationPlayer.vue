<template>
    <div>
        <PremiumModal v-if="step === 1 || step === 2" :isOpen="isOpen" :header="selectionHeader" width="800px"
            :showClose="step === 1" :noPadding="step === 1" @close="close">

            <div class="wizard-container" v-if="step === 1">
                <div class="wizard-body">
                    <p class="wizard-instruction">
                        Sélectionnez une question puis cliquez sur le bouton "Démarrer la Consultation" et votre
                        consultation démarrera.
                        Le timestamp sera appliqué à chaque question.
                    </p>
                    <h4 class="section-label">Bannière de la section</h4>

                    <div class="selection-list">
                        <div v-for="q in questions" :key="q.IdConsultation" class="selection-item"
                            :class="{ 'selected': isSelected(q.IdConsultation) }"
                            @click="toggleSelection(q.IdConsultation)">

                            <div class="check-circle">
                                <i v-if="isSelected(q.IdConsultation)" class="fas fa-check"></i>
                            </div>

                            <span class="item-title">{{ q.Titre }}</span>

                            <div class="item-actions">
                                <button class="icon-btn edit" @click.stop><i class="fas fa-pen"></i></button>
                                <button class="icon-btn trash" @click.stop><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="selection-footer">
                        <button class="deselect-all-btn" @click="toggleAll">
                            {{ allSelected ? 'DÉSÉLECTIONNER TOUTES LES QUESTIONS' : 'SÉLECTIONNER TOUTES LES QUESTIONS'
                            }}
                            <i class="fas fa-arrow-right arrow-icon"></i>
                        </button>

                        <PremiumButton type="button" text="Démarrer la consultation" @click="goToStep2"
                            :disabled="selectedIds.length === 0" class="start-btn-premium" />
                    </div>
                </div>
            </div>

            <!-- STEP 2: Intro -->
            <div class="wizard-container centered" v-else-if="step === 2">
                <div class="intro-content">
                    <h2 class="intro-title">Vous êtes sur le point de<br>commencer une consultation</h2>
                    <p class="intro-text">
                        ShiftUp va vous aider à afficher les questions une par une en pleine écran et vous pouvez
                        enregistrer en vidéo votre écran,
                        assurez-vous que votre micro fonctionne à merveille.
                    </p>
                    <p class="intro-text">
                        Si vous êtes prêt, cliquez sur le bouton ci-dessous pour afficher la première question
                    </p>
                    <div class="intro-actions">
                        <PremiumButton type="button" text="Je suis prêt" @click="startLiveSession"
                            class="ready-btn-premium" />
                    </div>
                </div>
            </div>
        </PremiumModal>

        <!-- STEP 4: Save Modal -->
        <PremiumModal v-if="step === 4" :isOpen="isOpen" header="" width="700px" :showClose="false" @close="close">
            <div class="save-step-container">
                <div class="save-header">
                    <button class="modal-back-btn" @click="step = 3">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <h2 class="save-title">Enregistrer les informations de votre consultation</h2>
                </div>

                <div class="save-form-content">
                    <div class="form-group status-row">
                        <PremiumSlideToggle v-model="saveForm.Statut" checkedValue="Publié" uncheckedValue="Dépublié"
                            activeColor="#22c55e" />
                    </div>

                    <div class="form-group">
                        <label class="input-label">Nom de la consultation</label>
                        <InputText v-model="saveForm.nom" placeholder="Modifier le nom de votre consultation ici"
                            class="w-full prime-input-custom" />
                    </div>

                    <div class="form-group">
                        <label class="input-label">Description (facultatif)</label>
                        <Textarea v-model="saveForm.description" placeholder="la déscritpion ici"
                            class="w-full prime-textarea-custom" rows="3" autoResize />
                    </div>

                    <div class="form-group">
                        <label class="input-label">Lien de la vidéo</label>
                        <InputText v-model="saveForm.videoUrl"
                            placeholder="Coller un lien Cloudinary, Imagekit ou Youtube ici"
                            class="w-full prime-input-custom" />
                    </div>
                </div>

                <div class="save-actions">
                    <button class="cancel-save-btn" @click="step = 3">Annuler</button>
                    <PremiumButton type="button" text="Enregistrer" @click="handleFinalSave" :loading="isSaving"
                        class="final-save-btn" />
                </div>
            </div>
        </PremiumModal>

        <transition name="fade">
            <div v-if="step === 3" class="live-session-overlay" ref="liveOverlay">
                <div class="live-header">
                    <img :src="logoSrc" alt="ShiftUp Logo" class="live-logo" />
                    <div class="live-question-title">{{ currentQuestion?.Titre }}</div>
                </div>

                <div class="live-card-container">
                    <div class="card-transition-wrapper">
                        <transition :css="false" @before-enter="beforeCardEnter" @enter="enterCard" @leave="leaveCard"
                            mode="out-in">
                            <div :key="currentQuestion?.IdConsultation" class="question-display-card">
                                <div class="q-meta">
                                    <span class="meta-label">Questions posé par</span>
                                    <div class="q-user">
                                        <img v-if="currentQuestion?.utilisateur?.profil?.PhotoProfil"
                                            :src="currentQuestion.utilisateur.profil.PhotoProfil"
                                            class="q-avatar-img" />
                                        <div v-else class="q-avatar">{{
                                            currentQuestion?.utilisateur?.profil?.Prenom?.[0] || '?' }}</div>

                                        <div class="q-username-block">
                                            <span class="q-username">
                                                {{ currentQuestion?.utilisateur?.profil?.Prenom }} {{
                                                    currentQuestion?.utilisateur?.profil?.Nom }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="q-content-large">
                                    {{ currentQuestion?.Question }}
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>

                <div class="live-footer">
                    <div class="nav-buttons">
                        <button class="nav-btn prev" @click="prevQuestion" :disabled="currentIndex === 0">
                            Précédant
                        </button>
                        <button class="nav-btn next" @click="nextQuestion"
                            :disabled="currentIndex === selectedQuestions.length - 1">
                            Suivant
                        </button>
                    </div>

                    <button class="terminate-btn" @click="terminateSession">
                        <span class="red-dot"></span> Terminer la consultation
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import PremiumModal from '../ui/PremiumModal.vue';
import PremiumButton from '../ui/PremiumButton.vue';
import PremiumSlideToggle from '../ui/PremiumSlideToggle.vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';

const props = defineProps({
    isOpen: Boolean,
    category: Object,
    questions: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close']);

const step = ref(1);
const selectedIds = ref([]);
const currentIndex = ref(0);
const direction = ref(1);
const liveOverlay = ref(null);
const logoSrc = ref('/images/logo-site-blanc.png');

const saveForm = ref({
    nom: '',
    description: '',
    videoUrl: '',
    Statut: 'Publié'
});

watch(() => props.questions, (newVal) => {
    if (newVal && newVal.length > 0) {
        selectedIds.value = newVal.map(q => q.IdConsultation);
    }
}, { immediate: true });

watch(() => props.isOpen, (val) => {
    if (val) {
        step.value = 1;
        currentIndex.value = 0;
    }
});



const selectionHeader = computed(() => {
    if (step.value === 1) return props.category ? props.category.Nom : 'Nouvelle Consultation';
    return '';
});

const isSelected = (id) => selectedIds.value.includes(id);

const toggleSelection = (id) => {
    if (isSelected(id)) {
        selectedIds.value = selectedIds.value.filter(i => i !== id);
    } else {
        selectedIds.value.push(id);
    }
};

const allSelected = computed(() => {
    return props.questions.length > 0 && selectedIds.value.length === props.questions.length;
});

const toggleAll = () => {
    if (allSelected.value) {
        selectedIds.value = [];
    } else {
        selectedIds.value = props.questions.map(q => q.IdConsultation);
    }
};

const selectedQuestions = computed(() => {
    return props.questions.filter(q => selectedIds.value.includes(q.IdConsultation));
});

const currentQuestion = computed(() => {
    if (selectedQuestions.value.length === 0) return null;
    return selectedQuestions.value[currentIndex.value];
});

// Navigation
const goToStep2 = () => {
    step.value = 2;
};

const startLiveSession = () => {
    step.value = 3;
    // Request fullscreen
    const elem = document.documentElement;
    if (elem.requestFullscreen) {
        elem.requestFullscreen().catch(err => {
            console.log(`Error attempting to enable fullscreen: ${err.message}`);
        });
    }
};

const prevQuestion = () => {
    if (currentIndex.value > 0) {
        direction.value = -1;
        currentIndex.value--;
    }
};

const nextQuestion = () => {
    if (currentIndex.value < selectedQuestions.value.length - 1) {
        direction.value = 1;
        currentIndex.value++;
    }
};

const terminateSession = () => {
    if (document.fullscreenElement) {
        document.exitFullscreen().catch(err => {
            console.log(`Error attempting to exit fullscreen: ${err.message}`);
        });
    }

    setTimeout(() => {
        saveForm.value.nom = props.category?.Nom || '';
        step.value = 4;
    }, 100);
};

const isSaving = ref(false);
const handleFinalSave = () => {
    isSaving.value = true;
    router.post('/admin/consultations/store-response', {
        ...saveForm.value,
        category_id: props.category?.IdCategorie,
        question_ids: selectedIds.value
    }, {
        onSuccess: () => {
            isSaving.value = false;
            close();
        },
        onError: (errors) => {
            isSaving.value = false;
            console.error('Erreur lors de la sauvegarde de la consultation :', errors);
        }
    });
};

const close = () => {
    emit('close');
};

const beforeCardEnter = (el) => {
    const startY = direction.value === 1 ? 80 : -80;
    gsap.set(el, {
        y: startY,
        opacity: 0,
        scale: 0.95
    });
};

const enterCard = (el, done) => {
    gsap.to(el, {
        y: 0,
        opacity: 1,
        scale: 1,
        duration: 0.8,
        ease: "expo.out",
        onComplete: done
    });
};

const leaveCard = (el, done) => {
    const endY = direction.value === 1 ? -80 : 80;
    gsap.to(el, {
        y: endY,
        opacity: 0,
        scale: 0.95,
        duration: 0.6,
        ease: "expo.in",
        onComplete: done
    });
};
</script>

<style scoped>
.wizard-container {
    padding: 30px 40px;
    color: #111;
}

.wizard-container.centered {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.wizard-instruction {
    color: #666;
    margin-top: 0;
    font-size: 1.1rem;
    margin-bottom: 25px;
    line-height: 1.3;
}

.section-label {
    font-weight: 700;
    margin-bottom: 15px;
    font-size: 1rem;
}

.selection-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 30px;
    max-height: 400px;
    overflow-y: auto;
}

.selection-item {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    border-radius: 12px;
    background: #f1f1f1;
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid transparent;
}

.selection-item:hover {
    background: #e9e9e9;
}

.selection-item.selected {
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    color: white;
}

.check-circle {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #999;
    margin-right: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    color: transparent;
    transition: all 0.2s;
}

.selection-item.selected .check-circle {
    background: #00C853;
    color: white;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.item-title {
    flex: 1;
    font-weight: 600;
    font-size: 1rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.item-actions {
    display: flex;
    gap: 10px;
}

.icon-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.1);
    color: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.selection-item.selected .icon-btn {
    border-color: rgba(255, 255, 255, 0.3);
    color: white;
}

.selection-item:not(.selected) .icon-btn {
    border-color: #ddd;
    color: #666;
    background: white;
}

.icon-btn.trash:hover {
    background: #ef4444;
    border-color: #ef4444;
    color: white !important;
}

.selection-footer {
    border-top: 1px solid #eee;
    padding-top: 20px;
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    gap: 8vw;
}

.deselect-all-btn {
    background: none;
    border: none;
    padding: 0;
    font-weight: 550;
    font-size: 1rem;
    color: #111;
    text-transform: uppercase;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 0;
}

.arrow-icon {
    font-size: 0.8rem;
    transform: rotate(-45deg);
}

.start-form {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    width: 100%;
}

.start-btn-premium {
    min-width: 250px;
}

.intro-content {
    max-width: 600px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.intro-actions {
    display: flex;
    justify-content: center;
    width: 100%;
    margin-top: 20px;
}

.intro-title {
    font-size: 2.8rem;
    font-weight: 500;
    margin-bottom: 30px;
    line-height: 1;
}

.intro-text {
    font-size: 1.2rem;
    color: #444;
    margin-bottom: 20px;
    line-height: 1.3;
}

.ready-btn-premium {
    margin-top: 20px;
    min-width: 200px;
}

.live-session-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: #111111;
    z-index: 30000;
    display: flex;
    flex-direction: column;
    color: white;
    padding: 40px;
    overflow: hidden;
}

.live-header {
    display: flex;
    align-items: center;
    gap: 30px;
    height: 50px;
}

.live-logo {
    height: 40px;
    object-fit: contain;
}

.live-question-title {
    font-size: 1.6rem;
    font-weight: 550;
    padding-left: 30px;
    border-left: 2px solid rgba(255, 255, 255)
}

.live-card-container {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    perspective: 1200px;
}

.card-transition-wrapper {
    width: 90%;
    position: relative;
    display: flex;
    justify-content: center;
}

.question-display-card {
    width: 100%;
    min-height: 400px;
    background: linear-gradient(135deg, #1A888D 0%, #0E7EC3 100%);
    border-radius: 24px;
    padding: 60px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.q-meta {
    position: absolute;
    top: 40px;
    left: 40px;
}

.meta-label {
    font-size: 1rem;
    font-weight: 300;
    margin-bottom: 8px;
    display: block;
}

.q-user {
    display: flex;
    align-items: center;
    gap: 12px;
}

.q-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #F59E0B;
    color: black;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
}

.q-avatar-img {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid white;
}

.q-username {
    font-weight: 400;
    font-size: 1.4rem;
}

.q-content-large {
    font-size: 2.7rem;
    font-weight: 500;
    text-align: center;
    line-height: 1.2;
    margin-left: 5vw;
    margin-right: 5vw;
    margin-top: 5vh;
}

.live-footer {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    margin-top: 40px;
    height: 60px;
}

.nav-buttons {
    display: flex;
    gap: 15px;
}

.nav-btn {
    background: white;
    color: #111;
    border: none;
    padding: 12px 30px;
    border-radius: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 1rem;
}

.nav-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 255, 255, 0.2);
}

.nav-btn.prev {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
}

.nav-btn.prev:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.1);
}

.nav-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
    transform: none;
}

.terminate-btn {
    position: absolute;
    right: 0;
    background: transparent;
    color: white;
    /* Make text white per image */
    border: none;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1rem;
    transition: opacity 0.2s;
}

.terminate-btn:hover {
    opacity: 0.8;
}

.red-dot {
    width: 12px;
    height: 12px;
    background: #dc2626;
    border-radius: 50%;
    box-shadow: 0 0 10px #dc2626;
    animation: pulse-dot 2s infinite;
}

@keyframes pulse-dot {
    0% {
        transform: scale(1);
        opacity: 1;
    }

    50% {
        transform: scale(1.2);
        opacity: 0.8;
    }

    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* --- SAVE STEP STYLES --- */
.save-step-container {
    padding: 30px;
    background: #fff;
    color: #111;
}

.save-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 40px;
}

.modal-back-btn {
    width: 36px;
    height: 36px;
    background: #1c1c1c;
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s;
}

.modal-back-btn:hover {
    transform: scale(1.05);
    background: #000;
}

.save-title {
    font-size: 1.5rem;
    font-weight: 500;
    line-height: 1.1;
    margin: 0;
}

.save-form-content {
    display: flex;
    flex-direction: column;
    gap: 25px;
    margin-bottom: 35px;
}

.status-row {
    margin-bottom: 10px;
}

.input-label {
    display: block;
    font-size: 0.95rem;
    font-weight: 600;
    margin-bottom: 8px;
    color: #444;
}

.prime-input-custom,
.prime-textarea-custom {
    font-size: 1rem;
}

:deep(.p-inputtext),
:deep(.p-textarea) {
    width: 100% !important;
    display: block !important;
    padding: 14px 18px !important;
    border-radius: 12px !important;
    border: 1px solid #e0e0e0 !important;
    background: #fcfcfc !important;
    transition: all 0.2s !important;
}

:deep(.p-inputtext:focus),
:deep(.p-textarea:focus) {
    border-color: #8b5cf6 !important;
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1) !important;
    background: #fff !important;
}

.desc-textarea {
    height: 100px;
}

.save-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    border-top: 1px solid #f0f0f0;
    padding-top: 25px;
}

.cancel-save-btn {
    background: transparent;
    border: 1px solid #e0e0e0;
    color: #666;
    padding: 10px 25px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.cancel-save-btn:hover {
    background: #f5f5f5;
    color: #111;
    border-color: #d0d0d0;
}

.final-save-btn {
    min-width: 160px;
}

/* --- PREVIOUS ANIMATIONS --- */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
