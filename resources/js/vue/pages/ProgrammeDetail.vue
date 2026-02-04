<template>
    <div class="program-detail-page">
        <div class="background-noise"></div>

        <div class="hero-section" @mousemove="handleMouseMove" @mouseleave="handleMouseLeave" ref="heroRef">
            <div class="hero-content">
                <div class="back-link" @click="goBack">
                    <i class="fas fa-arrow-left"></i> Retour aux programmes
                </div>

                <h1 class="hover-title">
                    <span v-for="(char, index) in titleChars" :key="index" class="char"
                        :ref="el => charRefs[index] = el" :style="{ fontWeight: 300 }">
                        {{ char === ' ' ? '&nbsp;' : char }}
                    </span>
                </h1>

                <div class="meta-tags ">
                    <span class="tag premium" v-if="program.Prix > 0">{{ formatPrice(program.Prix) }}</span>
                    <span class="tag free" v-else>GRATUIT</span>
                </div>

                <div class="action-buttons-container">
                    <button class="cart-btn">
                        <i class="fas fa-shopping-cart"></i> Ajouter au panier
                    </button>

                    <PremiumButton class="buy-btn-premium">
                        <span>Acheter maintenant</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </PremiumButton>
                </div>
            </div>
        </div>

        <div class="content-container">

            <div class="video-section" ref="videoSectionRef">
                <div class="video-wrapper">
                    <div class="video-overlay" v-if="!isPlaying" @click="playVideo">
                        <LiquidGlass class="play-btn-glass" border-radius="50%">
                            <i class="fas fa-play"></i>
                        </LiquidGlass>
                        <img :src="program.LienPhoto || '/images/placeholder.jpg'" alt="Cover" class="video-cover" />
                    </div>

                    <div v-else class="video-player-container">
                        <iframe v-if="videoUrl" :src="videoUrl" title="YouTube video player" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen class="video-iframe"></iframe>
                        <div v-else class="no-video">
                            <p>Aucune vidéo d'aperçu disponible.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="details-grid">

                <!-- Left Column: Description (Now Full Width and First) -->
                <div class="details-description">
                    <div class="info-block">
                        <h2 class="section-title">À propos de ce programme</h2>
                        <p class="description-text">
                            {{ program.Descriptions || "Aucune description fournie pour ce programme." }}
                        </p>
                    </div>
                </div>

                <!-- Right Column: Curriculum (Now Full Width and Below) -->
                <div class="details-curriculum">
                    <div class="section-header-flex">
                        <h2 class="section-title">Au programme</h2>
                        <div class="progression-pill">
                            <span class="prog-label">Votre progression</span>
                            <span class="prog-value">{{ overallProgress }}%</span>
                        </div>
                    </div>

                    <div class="overall-progress-wrapper">
                        <div class="overall-progress-bar">
                            <div class="overall-progress-fill" :style="{ width: overallProgress + '%' }"></div>
                        </div>
                    </div>

                    <div class="curriculum-container">
                        <template v-if="visibleThemes && visibleThemes.length > 0">
                            <div v-for="(theme, tIndex) in visibleThemes" :key="theme.IdTheme" class="theme-item">
                                <div class="theme-header" @click="toggleTheme(tIndex)">
                                    <span class="theme-number">0{{ tIndex + 1 }}</span>
                                    <div class="theme-title-wrapper">
                                        <h3 class="theme-title">{{ theme.Titre }}</h3>
                                        <div class="theme-progress-mini-container">
                                            <div class="theme-progress-bar">
                                                <div class="theme-progress-fill"
                                                    :style="{ width: getThemeProgress(theme) + '%' }"></div>
                                            </div>
                                            <span class="theme-progress-percent">{{ getThemeProgress(theme) }}%</span>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-down theme-icon"
                                        :class="{ 'rotated': expandedThemes.includes(tIndex) }"></i>
                                </div>

                                <div class="theme-lessons" ref="themeContentRefs"
                                    :style="{ maxHeight: expandedThemes.includes(tIndex) ? '500px' : '0px' }">

                                    <!-- Theme Description -->
                                    <div v-if="theme.Descriptions" class="theme-description">
                                        <p>{{ theme.Descriptions }}</p>
                                    </div>

                                    <div v-for="(lesson, lIndex) in theme.lecons" :key="lesson.IdLecon"
                                        class="lesson-item"
                                        :class="{ 'locked': isLessonLocked(tIndex, lIndex), 'completed': isCompleted(lesson.IdLecon) }"
                                        @click="openLessonModal(tIndex, lIndex, lesson)">
                                        <div class="lesson-progress-circle">
                                            <svg viewBox="0 0 36 36" class="circular-chart-purple">
                                                <path class="circle-bg"
                                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                                <path class="circle-fill"
                                                    :stroke-dasharray="isCompleted(lesson.IdLecon) ? '100, 100' : '0, 100'"
                                                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                            </svg>
                                        </div>
                                        <div class="lesson-info">
                                            <h4>
                                                {{ lesson.Titre }}
                                                <i v-if="isLessonLocked(tIndex, lIndex)"
                                                    class="fas fa-lock ml-2 text-xs opacity-50"></i>
                                            </h4>
                                            <div class="lesson-meta-info">
                                                <span><i class="fas fa-layer-group"></i> {{ (lesson.etapes?.length || 0)
                                                    }} étape(s)</span>
                                                <span><i class="fas fa-history"></i> {{ lesson.DelaiDrop ?
                                                    lesson.DelaiDrop + 'h' : '0h'
                                                    }}</span>
                                            </div>
                                        </div>
                                        <div class="lesson-action-icon">
                                            <i class="fas"
                                                :class="isLessonLocked(tIndex, lIndex) ? 'fa-lock' : (isCompleted(lesson.IdLecon) ? 'fa-check' : 'fa-play')"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div v-else class="empty-curriculum">
                            <i class="fas fa-book-open"></i>
                            <p>Le contenu du programme est en cours de création.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Full Screen Lesson Overlay -->
        <transition name="fade">
            <div v-if="showLessonModal" class="fullscreen-lesson-overlay no-global-reveal">

                <!-- Hover Header / Back Button -->
                <div class="fs-hover-nav">
                    <button class="fs-back-btn-floating" @click="closeLessonModal">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div class="fs-lesson-title-floating">{{ currentLesson?.Titre }}</div>
                </div>

                <!-- Main Content -->
                <div class="fs-content-container" @scroll="handleScroll">

                    <!-- Lesson Content (Video/Text) -->
                    <div v-if="!showSteps" class="fs-lesson-view fade-in">
                        <div class="fs-lesson-wrapper relative-wrapper">

                            <!-- Actions Overlay (Top Left) -->
                            <div class="fs-actions-overlay">
                                <a v-if="isPdfLesson" :href="pdfDownloadUrl" class="premium-btn-link fade-in" download
                                    target="_blank">
                                    <i class="fas fa-download mr-2"></i> Télécharger le PDF
                                </a>

                                <PremiumButton v-if="canProceedToSteps" class="fade-in" @click="proceedToSteps"
                                    :style="{ '--btn-bg': 'rgba(0,0,0,0.6)', '--btn-gradient': 'linear-gradient(90deg, #8A38F5, #5e22ab)' }">
                                    {{ (!currentLesson?.etapes?.length) ? 'Poser une question' : 'Passer aux étapes' }}
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </PremiumButton>
                            </div>

                            <LessonContentPlayer v-if="currentLesson" :lesson="currentLesson" :fullScreenMode="true"
                                @content-finished="onContentFinished" class="fullscreen-player-override" />
                        </div>
                    </div>


                    <!-- Steps / Quiz Content -->
                    <div v-else class="fs-steps-view fade-in">
                        <div v-if="currentStep" class="fs-step-wrapper">
                            <div class="step-header">
                                <h3 class="step-main-title">{{ currentStep.Titre || 'Étape ' + (currentStepIndex + 1) }}
                                </h3>
                                <p class="step-desc">{{ currentStep.Descriptions || currentStep.Description }}</p>
                            </div>

                            <!-- Questions Section -->
                            <div v-if="currentStep.questions && currentStep.questions.length > 0"
                                class="fs-questions-section">
                                <div v-for="(question, qIndex) in currentStep.questions" :key="question.IdQuestion"
                                    class="question-block"
                                    :class="[`question-${question.IdQuestion}`, { 'multiple-choice': question.options.filter(o => o.EstCorrecte).length > 1 }]">

                                    <h5 class="question-title">
                                        <span class="q-number">{{ qIndex + 1 }}.</span> {{ question.Intitule }}
                                        <i v-if="questionStatus[question.IdQuestion] === 'correct'"
                                            class="fas fa-check-circle success-icon"></i>
                                    </h5>

                                    <div class="options-list">
                                        <div v-for="option in question.options" :key="option.IdOption"
                                            class="option-item" :class="{
                                                'selected': selectedOptions[question.IdQuestion]?.includes(option.IdOption),
                                                'correct': questionStatus[question.IdQuestion] === 'correct' && option.EstCorrecte,
                                                'wrong': questionStatus[question.IdQuestion] === 'wrong' && selectedOptions[question.IdQuestion]?.includes(option.IdOption),
                                                'disabled': (retryTimers[question.IdQuestion] || 0) > 0 || questionStatus[question.IdQuestion] === 'correct'
                                            }" @click="selectOption(question, option.IdOption)">
                                            <div class="option-marker"></div>
                                            <span class="option-text">{{ option.TexteOption }}</span>
                                        </div>
                                    </div>

                                    <div class="question-footer">
                                        <div v-if="(retryTimers[question.IdQuestion] || 0) > 0" class="retry-timer">
                                            <i class="fas fa-clock"></i> Réflechissez bien puis on recommence dans {{
                                                retryTimers[question.IdQuestion] }}s 😉
                                        </div>

                                        <button
                                            v-if="questionStatus[question.IdQuestion] !== 'correct' && (retryTimers[question.IdQuestion] || 0) <= 0"
                                            class="validate-btn"
                                            :disabled="!selectedOptions[question.IdQuestion] || selectedOptions[question.IdQuestion].length === 0"
                                            @click="validateQuestion(question)">
                                            Valider
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="currentLesson?.etapes?.length === 0" class="empty-steps">
                            <p>Cette leçon ne contient pas d'étapes supplémentaires.</p>
                        </div>

                        <div class="fs-action-footer">
                            <button v-if="hasNextStep" class="fs-next-btn" @click="nextStep">
                                Passer à l'étape suivante <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>


        <!-- Ask Question Modal -->
        <PremiumModal :isOpen="showAskQuestionModal" :valid="questionForm.processing" :dark="true"
            header="Avez-vous tout compris ?" @close="handleAskModalClose" width="500px">
            <div class="ask-modal-body">
                <p class="ask-instruction">
                    Est-ce que vous avez tout bien suivi ? Posez une question par rapport à cette leçon et le coach vous
                    répondra par le biais de consultations.
                </p>

                <form @submit.prevent="submitQuestion" class="ask-form">
                    <div class="form-group">
                        <label>Titre de votre question</label>
                        <input type="text" v-model="questionForm.titre" class="dark-input"
                            placeholder="Sujet de votre question..." required />
                    </div>

                    <div class="form-group">
                        <label>Votre Question</label>
                        <textarea v-model="questionForm.question" class="dark-input textarea-input" rows="4"
                            placeholder="Écrivez votre question ici..." required></textarea>
                    </div>

                    <div class="modal-actions">
                        <PremiumButton type="submit" :loading="questionForm.processing"
                            :disabled="!questionForm.question" class="submit-btn">
                            Envoyer la question
                        </PremiumButton>
                    </div>
                </form>
            </div>
        </PremiumModal>

        <Toast />

    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { gsap } from 'gsap';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import LiquidGlass from '../components/ui/LiquidGlass.vue';
import PremiumButton from '../components/ui/PremiumButton.vue';
import PremiumModal from '../components/ui/PremiumModal.vue';
import ShaderBackground from '../components/ui/ShaderBackground.vue';
import LessonContentPlayer from '../components/sections/LessonContentPlayer.vue';

// Props expected from the backend
const props = defineProps({
    program: {
        type: Object,
        default: () => ({
            Titre: "Titre du Programme par Défaut",
            Descriptions: "Description du programme...",
            Prix: 0,
            PointsOfferts: 100,
            LienPhoto: null,
            ApercuVideo: "",
            StatutVerrouillageProgression: "Déverrouillé",
            themes: []
        })
    },
    lessonProgress: {
        type: Object,
        default: () => ({})
    },
    categories: {
        type: Array,
        default: () => []
    }
});

// State
const titleChars = computed(() => props.program.Titre ? props.program.Titre.split('') : []);
const charRefs = ref([]);
const heroRef = ref(null);
const videoSectionRef = ref(null);
const isPlaying = ref(false);
const expandedThemes = ref([0]);

// Modal & Progress State
const showLessonModal = ref(false);
const currentLesson = ref(null);
const currentThemeIndex = ref(0);
const currentLessonIndex = ref(0);
const currentStepIndex = ref(0);
// User progress state
const lessonProgress = ref(props.lessonProgress);

const isCompleted = (lessonId) => {
    return lessonProgress.value[lessonId]?.EstTermine || false;
};

const getOpeningDate = (lessonId) => {
    return lessonProgress.value[lessonId]?.DateOuverture ? new Date(lessonProgress.value[lessonId].DateOuverture) : null;
};
// Lesson Flow State
const showSteps = ref(false);
const canProceedToSteps = ref(false);

// Ask Question Modal Logic
const showAskQuestionModal = ref(false);
const questionForm = useForm({
    titre: '',
    question: '',
    category_id: null,
    lesson_id: null,
    source_type: 'Lecon'
});

const openAskQuestionModal = () => {
    questionForm.lesson_id = currentLesson.value?.IdLecon;
    questionForm.category_id = props.program.IdCategorie || null;
    showAskQuestionModal.value = true;
};

const handleAskModalClose = () => {
    showAskQuestionModal.value = false;
    markLessonComplete();
};



// PDF Logic
const isPdfLesson = computed(() => {
    return currentLesson.value &&
        (currentLesson.value.TypeContenu === 'PDF' ||
            (currentLesson.value.Contenu && currentLesson.value.Contenu.toLowerCase().endsWith('.pdf')));
});

const pdfDownloadUrl = computed(() => {
    if (!currentLesson.value) return '#';
    if (currentLesson.value.Contenu.startsWith('/storage/')) {
        return `/lecons/${currentLesson.value.IdLecon}/content?download=1`;
    }
    return currentLesson.value.Contenu;
});

// Quiz State
const selectedOptions = ref({});
const questionStatus = ref({});
const retryTimers = ref({});
const intervals = {}; // Non-reactive for storing interval IDs

const resetQuizState = () => {
    selectedOptions.value = {};
    questionStatus.value = {};
    retryTimers.value = {};
    Object.values(intervals).forEach(clearInterval);
};

const selectOption = (question, optionId) => {
    const qId = question.IdQuestion;
    if (questionStatus.value[qId] === 'correct' || (retryTimers.value[qId] || 0) > 0) return;

    const correctOptionsCount = question.options ? question.options.filter(o => o.EstCorrecte).length : 1;
    let currentSelection = selectedOptions.value[qId] || [];

    // Ensure it's an array (migration from old single-value if needed, though we reset on load)
    if (!Array.isArray(currentSelection)) {
        currentSelection = [currentSelection];
    }

    if (correctOptionsCount > 1) {
        // Multi-select behavior
        if (currentSelection.includes(optionId)) {
            // Deselect
            currentSelection = currentSelection.filter(id => id !== optionId);
        } else {
            // Select only if limit not reached
            if (currentSelection.length < correctOptionsCount) {
                currentSelection = [...currentSelection, optionId];
            } else {
                // Optional: visual feedback that limit is reached?
                // For now, just don't add.
                // Or maybe replace the first one?
                // "on à le droit de séléctioner plusieur réponse" usually means up to N.
            }
        }
    } else {
        // Single-select behavior
        currentSelection = [optionId];
    }

    selectedOptions.value = { ...selectedOptions.value, [qId]: currentSelection };

    if (questionStatus.value[qId] === 'wrong') {
        questionStatus.value[qId] = 'pending';
    }
};

const validateQuestion = (question) => {
    const selectedIds = selectedOptions.value[question.IdQuestion] || [];
    if (selectedIds.length === 0) return;

    const correctOptions = question.options?.filter(o => o.EstCorrecte) || [];
    const correctIds = correctOptions.map(o => o.IdOption);

    // Loose comparison in case of string/int mismatch
    const isCorrect = selectedIds.length === correctIds.length &&
        selectedIds.every(id => correctIds.some(cId => cId == id));

    if (isCorrect) {
        questionStatus.value[question.IdQuestion] = 'correct';
        // Play success animation
        nextTick(() => {
            gsap.fromTo(`.question-${question.IdQuestion} .success-icon`,
                { scale: 0, opacity: 0 },
                { scale: 1, opacity: 1, duration: 0.5, ease: 'back.out(1.7)' }
            );
        });
    } else {
        questionStatus.value[question.IdQuestion] = 'wrong';
        // Play shake animation
        gsap.to(`.question-${question.IdQuestion}`, { x: [-10, 10, -10, 10, 0], duration: 0.4 });

        // Start Timer (10s)
        retryTimers.value[question.IdQuestion] = 10;
        if (intervals[question.IdQuestion]) clearInterval(intervals[question.IdQuestion]);

        intervals[question.IdQuestion] = setInterval(() => {
            if (retryTimers.value[question.IdQuestion] > 0) {
                retryTimers.value[question.IdQuestion]--;
            } else {
                clearInterval(intervals[question.IdQuestion]);
                retryTimers.value[question.IdQuestion] = 0;
                questionStatus.value[question.IdQuestion] = 'pending';
            }
        }, 1000);
    }
};

// Computed Properties
const visibleThemes = computed(() => {
    return props.program.themes?.filter(t => t.Statut === 'Publié') || [];
});

const allLessons = computed(() => {
    return visibleThemes.value.flatMap(t => t.lecons || []);
});

const overallProgress = computed(() => {
    if (allLessons.value.length === 0) return 0;
    const completedCount = allLessons.value.filter(l => isCompleted(l.IdLecon)).length;
    return Math.round((completedCount / allLessons.value.length) * 100);
});

const getThemeProgress = (theme) => {
    if (!theme.lecons || theme.lecons.length === 0) return 0;
    const completedCount = theme.lecons.filter(l => isCompleted(l.IdLecon)).length;
    return Math.round((completedCount / theme.lecons.length) * 100);
};

const currentStep = computed(() => {
    if (!currentLesson.value || !currentLesson.value.etapes) return null;
    return currentLesson.value.etapes[currentStepIndex.value];
});

const isStepValidated = computed(() => {
    if (!currentStep.value || !currentStep.value.questions || currentStep.value.questions.length === 0) return true;
    return currentStep.value.questions.every(q => questionStatus.value[q.IdQuestion] === 'correct');
});

// Auto-complete lesson when last step is validated
// Toast Logic
import { useToast } from "primevue/usetoast";
import Toast from 'primevue/toast';

const toast = useToast();

const submitQuestion = () => {
    questionForm.post('/consultations', {
        preserveScroll: true,
        onSuccess: () => {
            showAskQuestionModal.value = false;
            questionForm.reset();
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Votre question a été envoyée.', life: 3000 });
            markLessonComplete();
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Erreur', detail: 'Une erreur est survenue.', life: 3000 });
        }
    });
};

/* ... existing logic ... */

// Auto-complete lesson when last step is validated
watch(isStepValidated, (isValid) => {
    if (isValid && !hasNextStep.value && showSteps.value) {
        setTimeout(() => {
            // Instead of completing immediately, offer the user to ask a question
            openAskQuestionModal();
        }, 1500);
    }
});

const hasNextStep = computed(() => {
    if (!currentLesson.value || !currentLesson.value.etapes) return false;
    return currentStepIndex.value < currentLesson.value.etapes.length - 1;
});

// Computed Lock Logic
const isProgressionLocked = computed(() => props.program.StatutVerrouillageProgression === 'Verrouillé');

const isLessonLocked = (themeIdx, lessonIdx) => {
    if (!isProgressionLocked.value) return false;

    const currentTheme = visibleThemes.value[themeIdx];
    const currentLessonObj = currentTheme.lecons[lessonIdx];
    const flatIndex = allLessons.value.findIndex(l => l.IdLecon === currentLessonObj.IdLecon);

    // First lesson is always unlocked
    if (flatIndex <= 0) return false;

    // Check all previous lessons in sequence
    for (let i = 0; i < flatIndex; i++) {
        const prevLesson = allLessons.value[i];

        // 1. Check if previous lesson is completed
        if (!isCompleted(prevLesson.IdLecon)) return true;

        // 2. Check for Drop Delay (DelaiDrop) on previous lesson
        if (prevLesson.DelaiDrop && prevLesson.DelaiDrop > 0) {
            const openedAt = getOpeningDate(prevLesson.IdLecon);
            if (openedAt) {
                const now = new Date();
                const diffInHours = (now - openedAt) / (1000 * 60 * 60);
                if (diffInHours < prevLesson.DelaiDrop) {
                    return true; // Still locked by a previous delay
                }
            }
        }
    }

    return false;
};

const openLessonModal = async (themeIdx, lessonIdx, lesson) => {
    if (isLessonLocked(themeIdx, lessonIdx)) return;

    currentThemeIndex.value = themeIdx;
    currentLessonIndex.value = lessonIdx;
    currentLesson.value = lesson;
    currentStepIndex.value = 0;
    showSteps.value = false;
    resetQuizState();
    canProceedToSteps.value = false;
    showLessonModal.value = true;
    enterFullScreen();

    // Record opening in backend if not already done
    if (!lessonProgress.value[lesson.IdLecon]?.DateOuverture) {
        try {
            const response = await axios.post('/lecons/record-opening', { lecon_id: lesson.IdLecon });
            if (response.data.success) {
                lessonProgress.value[lesson.IdLecon] = {
                    ...lessonProgress.value[lesson.IdLecon],
                    DateOuverture: response.data.data.DateOuverture
                };
            }
        } catch (error) {
            console.error('Error recording lesson opening:', error);
        }
    }
};

const closeLessonModal = () => {
    showLessonModal.value = false;
    currentLesson.value = null;
    currentStepIndex.value = 0;
    showSteps.value = false;
    exitFullScreen();
};

const enterFullScreen = () => {
    if (window.lenis) {
        window.lenis.stop();
    }

    const elem = document.documentElement;
    if (elem.requestFullscreen) {
        elem.requestFullscreen().catch(err => {
            console.log(`Error attempting to enable full-screen mode: ${err.message} (${err.name})`);
        });
    } else if (elem.webkitRequestFullscreen) { /* Safari */
        elem.webkitRequestFullscreen();
    } else if (elem.msRequestFullscreen) { /* IE11 */
        elem.msRequestFullscreen();
    }
};

const exitFullScreen = () => {
    // Re-enable Lenis smooth scroll
    if (window.lenis) {
        window.lenis.start();
    }

    if (document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) { /* Safari */
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) { /* IE11 */
            document.msExitFullscreen();
        }
    }
};

const onContentFinished = () => {
    canProceedToSteps.value = true;
};

const handleScroll = (event) => {
    const { scrollTop, scrollHeight, clientHeight } = event.target;
    if (scrollTop + clientHeight >= scrollHeight - 50) {
        onContentFinished();
    }
};

const proceedToSteps = () => {
    // If no steps, mark complete immediately or handle logic
    if (!currentLesson.value.etapes || currentLesson.value.etapes.length === 0) {
        openAskQuestionModal();
    } else {
        showSteps.value = true;
    }
};

const nextStep = () => {
    if (!isStepValidated.value) {
        gsap.to('.fs-step-wrapper', { x: [-10, 10, -10, 10, 0], duration: 0.4 });
        return;
    }
    if (hasNextStep.value) {
        currentStepIndex.value++;
        resetQuizState();
    }
};

const markLessonComplete = () => {
    if (!isStepValidated.value) {
        gsap.to('.fs-step-wrapper', { x: [-10, 10, -10, 10, 0], duration: 0.4 });
        return;
    }
    if (currentLesson.value) {
        const lessonId = currentLesson.value.IdLecon;

        // Optimistic update
        if (!lessonProgress.value[lessonId]) {
            lessonProgress.value[lessonId] = { DateOuverture: new Date().toISOString() };
        }
        lessonProgress.value[lessonId].EstTermine = true;

        // Persist to backend
        axios.post('/progress/mark', {
            entite_id: lessonId,
            entite_type: 'Lecon',
            est_termine: true
        }).catch(err => {
            console.error('Failed to save progress', err);
        });
    }
    closeLessonModal();
};

// Hover Effect Logic
const handleMouseMove = (e) => {
    if (!heroRef.value) return;

    const mouseX = e.clientX;
    const mouseY = e.clientY;

    charRefs.value.forEach((charEl) => {
        if (!charEl) return;

        const charRect = charEl.getBoundingClientRect();
        const charCenterX = charRect.left + charRect.width / 2;
        const charCenterY = charRect.top + charRect.height / 2;

        const dist = Math.sqrt(Math.pow(mouseX - charCenterX, 2) + Math.pow(mouseY - charCenterY, 2));

        const maxDist = 200;
        let weight = 300;

        if (dist < maxDist) {
            weight = 800 - ((dist / maxDist) * 500);
        }

        gsap.to(charEl, {
            fontWeight: weight,
            color: '#ffffff', // Always white
            background: 'none', // No gradient
            duration: 0.15,
            ease: "power3.out"
        });
    });
};

const handleMouseLeave = () => {
    charRefs.value.forEach(charEl => {
        gsap.to(charEl, {
            fontWeight: 300,
            color: '#ffffff',
            background: 'none',
            duration: 0.4,
            ease: "power3.out"
        });
    });
};

// Video Logic
const videoUrl = computed(() => {
    if (!props.program.ApercuVideo) return null;
    let url = props.program.ApercuVideo;
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
        const videoId = url.split('v=')[1] || url.split('/').pop();
        const cleanId = videoId.split('&')[0];
        return `https://www.youtube.com/embed/${cleanId}?autoplay=1`;
    }
    return url;
});

const playVideo = () => {
    if (videoUrl.value) {
        isPlaying.value = true;
    }
};

// Curriculum Logic
const toggleTheme = (index) => {
    if (expandedThemes.value.includes(index)) {
        expandedThemes.value = expandedThemes.value.filter(i => i !== index);
    } else {
        expandedThemes.value.push(index);
    }
};

const formatPrice = (price) => {
    return Math.round(price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") + 'Ar';
};

const goBack = () => {
    window.history.length > 1 ? window.history.back() : router.visit('/');
};

// Animations on Mount
onMounted(() => {
    const tl = gsap.timeline();

    tl.from(videoSectionRef.value, {
        y: 50,
        opacity: 0,
        duration: 0.8,
        ease: "power2.out"
    })
        .from(".details-grid", {
            y: 30,
            opacity: 0,
            duration: 0.6
        }, "-=0.4");
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

.program-detail-page {
    min-height: 100vh;
    background-color: #050505;
    color: #e0e0e0;
    font-family: 'Plus Jakarta Sans', sans-serif;
    overflow-x: hidden;
}

.background-noise {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)' opacity='0.05'/%3E%3C/svg%3E");
    pointer-events: none;
    z-index: 0;
}

/* HERO SECTION */
.hero-section {
    position: relative;
    height: 70vh;
    display: flex;
    align-items: center;
    margin-top: 8vh;
    margin-bottom: 5vh;
    justify-content: center;
    padding: 0 5vw;
    z-index: 1;
}

.hero-content {
    text-align: center;
    position: relative;
}

.back-link {
    position: fixed;
    top: 5vh;
    left: 3vw;
    z-index: 1000;
    font-weight: 700;
    font-size: 0.9rem;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: 2px;
    padding: 10px 14px;
    border-radius: 12px;
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(6px);
    transition: transform 0.2s ease, box-shadow 0.3s ease, background 0.3s ease;
}

.back-link:hover {
    transform: translateY(-2px);
    background: rgba(255, 255, 255, 0.1);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.hover-title {
    font-size: 5rem;
    line-height: 1.1;
    margin: 0 0 30px 0;
    cursor: default;
    user-select: none;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    color: #ffffff;
}

.char {
    display: inline-block;
    color: #ffffff;
    transition: color 0.15s;
}

.meta-tags {
    display: flex;
    justify-content: center;
    gap: 15px;
}

.tag {
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.tag.premium {
    color: #878787;
    font-size: 2rem;
    font-weight: 500;
}

.tag.free {
    border: 1px solid white;
    color: white;
}

.tag.points {
    background: #8A38F5;
    color: white;
}

/* Action Buttons */
.action-buttons-container {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    gap: 20px;
    margin-top: 30px;
    width: 100%;
}

.cart-btn {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    padding: 14px 28px;
    border-radius: 15px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    backdrop-filter: blur(5px);
    height: 52px;
}

.cart-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

:deep(.buy-btn-premium) {
    --btn-bg: transparent;
    box-shadow: 0 10px 30px rgba(138, 56, 245, 0.3);
}

:deep(.buy-btn-premium)::before {
    opacity: 1 !important;
    animation: gradient-move 2s linear infinite !important;
}

:deep(.buy-btn-premium):hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 15px 40px rgba(138, 56, 245, 0.5);
}

@keyframes gradient-move {
    0% {
        background-position: 0% 50%;
    }

    100% {
        background-position: 200% 50%;
    }
}

/* CONTENT CONTAINER */
.content-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 5vw 100px;
    position: relative;
    z-index: 2;
}

/* VIDEO SECTION */
.video-section {
    margin-top: -100px;
    margin-bottom: 60px;
}

.video-wrapper {
    width: 100%;
    aspect-ratio: 16/9;
    background: #111;
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    border: 1px solid #222;
}

.video-overlay {
    width: 100%;
    height: 100%;
    position: relative;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.video-cover {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.7;
    transition: opacity 0.3s, transform 0.5s;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 1;
}

.video-overlay:hover .video-cover {
    opacity: 0.5;
    transform: scale(1.02);
}

.play-btn-glass {
    width: 150px;
    height: 150px;
    color: white;
    font-size: 1.5rem;
    z-index: 2;
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.video-overlay:hover .play-btn-glass {
    transform: scale(1.15);
}

.video-player-container,
.video-iframe {
    width: 100%;
    height: 100%;
}

.no-video {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
}

.details-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 60px;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 500;
    margin-bottom: 25px;
    width: 30vw;
    color: white;
    display: inline-block;
    padding-bottom: 10px;
}

.description-text {
    line-height: 1.6;
    color: #e6e6e6;
    font-size: 1.25rem;
    opacity: 0.85;
    white-space: pre-wrap;
}

/* OVERALL PROGRESSION */
.section-header-flex {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 20px;
}

.section-header-flex .section-title {
    margin-bottom: 0;
}

.progression-pill {
    background: rgba(138, 56, 245, 0.1);
    padding: 8px 16px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
}

.prog-label {
    font-size: 0.85rem;
    color: #ccc;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.prog-value {
    font-size: 1.1rem;
    font-weight: 800;
    color: #8A38F5;
}

.overall-progress-wrapper {
    margin-bottom: 40px;
}

.overall-progress-bar {
    width: 100%;
    height: 12px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.overall-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #0E7EC3, #8A38F5);
    border-radius: 10px;
    transition: width 1s cubic-bezier(0.23, 1, 0.32, 1);
    box-shadow: 0 0 15px rgba(138, 56, 245, 0.4);
}

/* CURRICULUM */
.curriculum-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.theme-item {
    background: #0f0f0f;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #222;
}

.theme-header {
    padding: 20px;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: background 0.3s;
}

.theme-header:hover {
    background: #1a1a1a;
}

.theme-number {
    font-size: 1.7rem;
    font-weight: 700;
    color: #ffffff;
    margin-right: 20px;
    min-width: 30px;
}

.theme-title {
    flex: 1;
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: white;
}

/* THEME PROGRESS BAR */
.theme-title-wrapper {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.theme-progress-mini-container {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 60%;
}

.theme-progress-bar {
    flex: 1;
    height: 4px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    overflow: hidden;
}

.theme-progress-fill {
    height: 100%;
    background: #8A38F5;
    border-radius: 4px;
    transition: width 0.6s cubic-bezier(0.23, 1, 0.32, 1);
}

.theme-progress-percent {
    font-size: 0.75rem;
    font-weight: 700;
    color: #8A38F5;
    min-width: 30px;
}

.theme-icon {
    transition: transform 0.3s;
    margin-left: 1vw;
    color: #ffffff;
}

.theme-icon.rotated {
    transform: rotate(180deg);
}

.theme-lessons {
    background: #0a0a0a;
    transition: max-height 0.5s ease-in-out;
    overflow: hidden;
    padding: 12px 16px;
}

.lesson-item {
    display: flex;
    align-items: center;
    background: linear-gradient(90deg, #0E7EC3, #8A38F5);
    padding: 15px 20px;
    border-radius: 15px;
    color: white;
    box-shadow: 0 4px 15px rgba(138, 56, 245, 0.2);
    transition: transform 0.2s, background 0.2s;
    margin-bottom: 10px;
    cursor: pointer;
}

.lesson-item:hover {
    transform: translateX(5px);
}

.lesson-item.locked {
    background: #444444;
    opacity: 0.6;
    cursor: not-allowed;
    box-shadow: none;
}

.lesson-item.locked:hover {
    transform: none;
}

.lesson-item.completed {
    background: #10565a;
}

.lesson-progress-circle {
    width: 44px;
    height: 44px;
    margin-right: 18px;
    position: relative;
}

.circular-chart-purple {
    width: 100%;
    height: 100%;
}

.circle-bg {
    fill: none;
    stroke: rgba(255, 255, 255, 0.2);
    stroke-width: 3;
}

.circle-fill {
    fill: none;
    stroke: white;
    stroke-width: 3;
    stroke-linecap: round;
    transition: stroke-dasharray 0.3s ease;
}

.lesson-info {
    flex: 1;
}

.lesson-info h4 {
    margin: 0 0 5px 0;
    font-size: 1.05rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.lesson-meta-info {
    font-size: 0.85rem;
    opacity: 0.9;
    display: flex;
    gap: 15px;
}

.lesson-meta-info i {
    margin-right: 4px;
}

.lesson-action-icon {
    margin-left: 0;
    margin-right: 1vw;
    font-size: 1.2rem;
    opacity: 0.8;
}

.empty-curriculum {
    padding: 30px;
    text-align: center;
    background: #0f0f0f;
    border-radius: 12px;
    color: #666;
}

.modal-body {
    padding: 20px;
    overflow-y: auto;
}

.theme-description {
    padding-bottom: 15px;
    margin-bottom: 15px;
    border-bottom: 1px solid #222;
    color: #ccc;
    font-size: 0.95rem;
    line-height: 1.5;
    white-space: pre-wrap;
}

/* Modal Styles */
.modal-lesson-container {
    color: #e0e0e0;
}

.modal-steps-section {
    padding: 20px 0 0 0;
}

.step-view {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.step-indicator {
    font-size: 0.85rem;
    text-transform: uppercase;
    color: #8A38F5;
    font-weight: 700;
    margin-bottom: 10px;
    letter-spacing: 1px;
}

.step-title {
    font-size: 1.5rem;
    margin: 0 0 15px 0;
    color: white;
}

.step-description {
    color: #d0d0d0;
    line-height: 1.6;
    font-size: 1rem;
    margin-bottom: 30px;
    white-space: pre-wrap;
}

.modal-footer-actions {
    display: flex;
    justify-content: flex-end;
    border-top: 1px solid #333;
    padding-top: 20px;
    margin-top: 20px;
}

.content-footer {
    display: flex;
    justify-content: center;
    padding: 20px 0;
}

.fade-in {
    animation: fadeIn 0.5s ease;
}

.next-btn {
    background: transparent;
    border: 1px solid #8A38F5;
    color: #8A38F5;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.next-btn:hover {
    background: rgba(138, 56, 245, 0.1);
}

.complete-btn {
    background: #8A38F5;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.complete-btn:hover {
    background: #7a2be0;
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .details-grid {
        grid-template-columns: 1fr;
    }

    .hover-title {
        font-size: 3rem;
    }

    .hero-section {
        height: auto;
        padding-top: 100px;
        padding-bottom: 120px;
    }

    .back-link {
        top: -40px;
        left: 50%;
        transform: translateX(-50%);
    }
}

/* QUIZ STYLES */
.questions-section {
    margin-top: 30px;
}

.question-block {
    background: #151515;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    border: 1px solid #222;
}

.question-title {
    font-size: 1.2rem;
    margin-bottom: 20px;
    color: white;
    display: flex;
    align-items: center;
    gap: 10px;
}

.q-number {
    color: #8A38F5;
    font-weight: bold;
}

.success-icon {
    color: #00C853;
    font-size: 1.2rem;
}

.options-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.option-item {
    background: #1a1a1a;
    border: 1px solid #333;
    padding: 15px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: all 0.2s;
}

.option-item:hover:not(.disabled) {
    background: #252525;
    border-color: #8A38F5;
}

.option-item.selected {
    background: #2a1a3a;
    border-color: #8A38F5;
}

.option-item.correct {
    background: rgba(0, 200, 83, 0.1);
    border-color: #00C853;
    color: #00C853;
}

.option-item.wrong {
    background: rgba(255, 61, 0, 0.1);
    border-color: #FF3D00;
    color: #FF3D00;
}

.option-item.disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.option-marker {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #555;
    margin-right: 15px;
    flex-shrink: 0;
}

.option-item.selected .option-marker {
    border-color: #8A38F5;
    background: #8A38F5;
}

.option-item.correct .option-marker {
    border-color: #00C853;
    background: #00C853;
}

.option-item.wrong .option-marker {
    border-color: #FF3D00;
    background: #FF3D00;
}

.question-footer {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
    align-items: center;
}

.validate-btn {
    background: #8A38F5;
    color: white;
    border: none;
    padding: 10px 25px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.validate-btn:disabled {
    background: #444;
    cursor: not-allowed;
}

.validate-btn:hover:not(:disabled) {
    background: #7a2be0;
}

.retry-timer {
    color: #F7B455;
    font-weight: 400;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Full Screen Overlay Styles */
.fullscreen-lesson-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: #050505;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.premium-btn-link {
    position: relative;
    padding: 14px 28px;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    border: none;
    border-radius: 15px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    margin-right: 15px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.premium-btn-link:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 10px 20px rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.1);
}

/* Hover Nav (Back Button) */
.fs-hover-nav {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100px;
    /* Larger trigger zone */
    z-index: 10001;
    display: flex;
    align-items: center;
    padding: 0 40px;
    background: linear-gradient(to bottom, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0) 100%);
    opacity: 0;
    transition: opacity 0.4s ease;
}

.fs-hover-nav:hover {
    opacity: 1;
}

.fs-back-btn-floating {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    font-size: 1.1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
    transition: all 0.2s;
}

.fs-back-btn-floating:hover {
    background: #8A38F5;
    transform: scale(1.1);
}

.fs-lesson-title-floating {
    margin-left: 20px;
    font-size: 1.1rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.9);
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

.fs-content-container {
    flex: 1;
    overflow-y: auto;
    padding: 0;
    /* Hide scrollbar */
    scrollbar-width: none;
    -ms-overflow-style: none;
    /* Smooth touch */
    -webkit-overflow-scrolling: touch;
    touch-action: pan-y;
}

.fs-content-container::-webkit-scrollbar {
    display: none;
}

.fs-lesson-view,
.fs-steps-view {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
    padding: 100px 20px 150px 20px;
    /* Top padding for nav, large bottom padding to prevent clipping */
}

/* Override LessonContentPlayer styles for Full Screen */
:deep(.fullscreen-player-override) {
    background: transparent !important;
    border-radius: 0;
    position: relative;
    overflow: visible !important;
    /* Ensure the wrapper doesn't clip */
    margin-bottom: 100px;
}

.relative-wrapper {
    position: relative;
}

.fs-actions-overlay {
    position: fixed;
    bottom: 40px;
    right: 40px;
    z-index: 10002;
}



:deep(.fullscreen-player-override .text-container::-webkit-scrollbar) {
    display: none;
}

:deep(.fullscreen-player-override .text-content) {
    font-size: 1.2rem;
    /* Larger text for reading */
    line-height: 1.9;
    max-width: 800px;
    margin: 0 auto;
}

:deep(.fullscreen-player-override .video-container) {
    width: 100vw;
    height: 80vh;
    /* Taller for full screen feel */
    margin-left: calc(50% - 50vw);
    margin-right: calc(50% - 50vw);
    border-radius: 0;
    margin-bottom: 40px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8), 0 0 40px rgba(138, 56, 245, 0.15);
    /* Enhanced Glow */
    background: #000;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

:deep(.fullscreen-player-override .video-container iframe) {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.step-header {
    margin-bottom: 60px;
    text-align: center;
    margin-top: 40px;
}

.step-main-title {
    font-size: 2.5rem;
    margin-bottom: 20px;
    color: white;
    font-weight: 700;
}

.step-desc {
    color: #ccc;
    font-size: 1.2rem;
    line-height: 1.7;
    max-width: 800px;
    margin: 0 auto;
}

.fs-footer,
.fs-action-footer {
    margin-top: 60px;
    padding-top: 40px;
    border-top: 1px solid #222;
    display: flex;
    justify-content: center;
}

.fs-next-btn {
    background: transparent;
    border: 1px solid #8A38F5;
    color: #8A38F5;
    padding: 15px 30px;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.1rem;
}

.fs-next-btn:hover {
    background: #8A38F5;
    color: white;
}

.fs-complete-btn {
    background: #8A38F5;
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.1rem;
}

.fs-complete-btn:hover {
    background: #7a2be0;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(138, 56, 245, 0.3);
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.4s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .fs-hover-nav {
        height: 80px;
        padding: 0 20px;
        background: rgba(0, 0, 0, 0.8);
        /* Always visible bg on mobile? Or keep hover logic? */
        opacity: 1;
        /* On mobile, hover is hard, so maybe always show or show on tap? Let's keep it visible for now or 1 for usability */
    }

    .fs-lesson-title-floating {
        display: none;
    }

    .fs-lesson-view,
    .fs-steps-view {
        padding: 80px 20px 40px 20px;
    }

    .step-main-title {
        font-size: 1.8rem;
    }

    :deep(.fullscreen-player-override .text-content) {
        font-size: 1rem;
    }
}

/* Ask Question Modal Styles */
.ask-modal-body {
    padding: 10px 0;
    color: #e0e0e0;
    padding-bottom: 3vh;
}

.ask-instruction {
    font-size: 1rem;
    margin-bottom: 25px;
    line-height: 1.5;
    color: #ccc;
    text-align: left;
}

.ask-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
    text-align: left;
}

.form-group label {
    font-weight: 600;
    font-size: 0.9rem;
    color: #fff;
}

.dark-input {
    width: 100%;
    background: #2a2a2a;
    border: 1px solid #3d3d3d;
    border-radius: 8px;
    padding: 12px;
    color: white;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 1rem;
    outline: none;
    transition: all 0.3s;
}

.dark-input:focus {
    border-color: #8A38F5;
    background: #333;
}

.select-input {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1em;
    cursor: pointer;
}

.modal-actions {
    margin-top: 10px;
    display: flex;
    justify-content: flex-end;
}
</style>