<template>
    <PremiumModal :isOpen="isOpen" :header="stepToEdit ? 'Modifier l\'Étape' : 'Nouvelle Étape'" width="55rem"
        @close="$emit('close')">
        <Toast />
        <form @submit.prevent="submitStep" class="create-form">
            <div class="form-grid">
                <div class="flex justify-content-center mt-2">
                    <div class="flex align-items-center gap-3">
                        <PremiumSlideToggle v-model="stepForm.Statut" checkedValue="Publié" uncheckedValue="Dépublié"
                            activeColor="#22c55e" />
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-group full-width">
                        <label>Titre de l'étape</label>
                        <InputText v-model="stepForm.Titre" required placeholder="Ex: Quiz de révision" />
                    </div>
                    <div class="field-group">
                        <label>Points Offerts</label>
                        <InputNumber v-model="stepForm.PointsOfferts" :min="0" />
                    </div>
                </div>

                <div class="field-group full-width">
                    <label>Description (Optionnel)</label>
                    <Textarea v-model="stepForm.Descriptions" rows="2" placeholder="Instructions pour l'élève..." />
                </div>

                <div class="field-group full-width">
                    <label class="text-center w-full block mb-2">Type d'étape</label>
                    <div class="type-selector-container">
                        <div class="type-selector">
                            <div v-for="type in stepTypes" :key="type.value" class="type-pill"
                                :class="{ active: stepForm.TypeEtape === type.value }"
                                @click="stepForm.TypeEtape = type.value">
                                <i :class="type.icon"></i><span>{{ type.label }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lesson-type-section">
                    <div class="section-header">
                        <div class="header-info">
                            <span class="step-badge">{{ stepForm.TypeEtape }}</span>
                            <h3 class="section-title">Configuration du contenu</h3>
                        </div>
                    </div>

                    <div class="question-card">
                        <div class="field-group">
                            <div class="label-with-icon">
                                <label>{{ stepForm.TypeEtape === 'QuestionReponse' ? 'Votre question' : "Intitulé de l'exercice" }}</label>
                            </div>
                            <InputText v-model="stepForm.Question" required class="premium-input-large"
                                placeholder="Entrez votre texte ici..." />
                        </div>
                    </div>

                    <div v-if="stepForm.TypeEtape !== 'QuestionReponse'" class="options-wrapper">
                        <div class="options-header">
                            <div class="options-title-block">
                                <label>Options de réponses</label>
                                <small v-if="stepForm.TypeEtape === 'Quiz'">Cochez l'unique bonne réponse</small>
                                <small v-else>Cochez les bonnes réponses</small>
                            </div>
                            <Button label="Ajouter" class="btn-black-action" @click="addStepOption" />
                        </div>

                        <TransitionGroup name="option-list" tag="div" class="options-grid">
                            <div v-for="(opt, idx) in stepForm.Options" :key="idx" class="option-row"
                                :class="{ 'is-correct-row': opt.est_correcte }">

                                <div class="option-check-wrapper" :class="{ 'is-radio': stepForm.TypeEtape === 'Quiz' }"
                                    @click="stepForm.TypeEtape === 'Quiz' ? setCorrectOption(idx) : toggleCorrectOption(opt)">
                                    <div class="custom-check-indicator" :class="{ 'checked': opt.est_correcte }">
                                        <i v-if="opt.est_correcte" class="fas fa-check"></i>
                                    </div>
                                </div>

                                <div class="option-content-side">
                                    <InputText v-model="opt.texte" placeholder="Écrire une option..."
                                        class="option-input-clean" />
                                </div>

                                <div class="option-actions-side">
                                    <Button icon="pi pi-times" severity="danger" text rounded
                                        @click="removeStepOption(idx)" />
                                </div>
                            </div>
                        </TransitionGroup>

                        <div v-if="stepForm.Options.length === 0" class="empty-options">
                            <p>Cliquez sur "Ajouter" pour créer des choix.</p>
                        </div>
                    </div>

                    <div v-if="stepForm.TypeEtape === 'QuestionReponse'" class="modern-admin-alert">
                        <div class="alert-body">
                            <h4>Validation manuelle requise</h4>
                            <p>L'élève repondra à la question mais c'est vous qui aller devoir validé la réponse si
                                c'est correct ou pas</p>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <Button label="Annuler" text @click="$emit('close')" />
                    <PremiumButton type="submit" :loading="stepForm.processing">Sauvegarder l'étape</PremiumButton>
                </div>
            </div>
        </form>
    </PremiumModal>
</template>

<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import PremiumModal from '../../../components/ui/PremiumModal.vue';
import PremiumButton from '../../../components/ui/PremiumButton.vue';
import PremiumSlideToggle from '../../../components/ui/PremiumSlideToggle.vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import Toast from 'primevue/toast';

const props = defineProps({
    isOpen: Boolean,
    lessonId: [String, Number],
    stepToEdit: Object,
    defaultOrder: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['close']);
const toast = useToast();

const stepTypes = [
    { label: 'Quiz', value: 'Quiz', icon: 'fas fa-list-ul' },
    { label: 'Question / Réponse', value: 'QuestionReponse', icon: 'fas fa-comment-dots' },
    { label: 'À Cocher', value: 'Cocher', icon: 'fas fa-check-square' }
];

const stepForm = useForm({
    IdLecon: null,
    Titre: '',
    Descriptions: '',
    Statut: 'Dépublié',
    TypeEtape: 'Quiz',
    Ordre: 0,
    PointsOfferts: 0,
    NecessiteValidationAdmin: false,
    Question: '',
    Options: []
});

watch(() => stepForm.TypeEtape, (newType) => {
    stepForm.NecessiteValidationAdmin = (newType === 'QuestionReponse');
    if (newType === 'Quiz') {
        let foundFirst = false;
        stepForm.Options.forEach(opt => {
            if (opt.est_correcte && !foundFirst) foundFirst = true;
            else opt.est_correcte = false;
        });
    }
});

watch(() => props.stepToEdit, (step) => {
    if (step) {
        stepForm.IdLecon = step.IdLecon || step.id_lecon;
        stepForm.Titre = step.Titre || step.titre;
        stepForm.Descriptions = step.Descriptions || step.descriptions;
        stepForm.Statut = step.Statut || step.statut || 'Dépublié';
        stepForm.TypeEtape = step.TypeEtape || step.type_etape || 'Quiz';
        stepForm.Ordre = step.Ordre || step.ordre || 0;
        stepForm.PointsOfferts = step.PointsOfferts || step.points_offerts || 0;
        stepForm.NecessiteValidationAdmin = !!(step.NecessiteValidationAdmin || step.necessite_validation_admin);

        const question = step.questions?.[0];
        stepForm.Question = question?.Intitule || question?.intitule || '';
        const opts = question?.options || question?.Options || [];
        stepForm.Options = opts.map(o => ({
            texte: o.TexteOption || o.texte_option || o.texte || '',
            est_correcte: !!(o.EstCorrecte || o.est_correcte)
        }));
    } else {
        stepForm.reset();
        stepForm.IdLecon = props.lessonId;
        stepForm.Statut = 'Dépublié';
        stepForm.TypeEtape = 'Quiz';
        stepForm.Ordre = props.defaultOrder;
        stepForm.Options = [
            { texte: '', est_correcte: true },
            { texte: '', est_correcte: false }
        ];
    }
}, { immediate: true });

watch(() => props.isOpen, (newVal) => {
    if (newVal && !props.stepToEdit) {
        stepForm.reset();
        stepForm.IdLecon = props.lessonId;
        stepForm.Ordre = props.defaultOrder;
        stepForm.Options = [
            { texte: '', est_correcte: true },
            { texte: '', est_correcte: false }
        ];
    }
});

const addStepOption = () => stepForm.Options.push({ texte: '', est_correcte: false });
const removeStepOption = (idx) => stepForm.Options.splice(idx, 1);
const setCorrectOption = (idx) => stepForm.Options.forEach((opt, i) => opt.est_correcte = (i === idx));
const toggleCorrectOption = (opt) => { if (stepForm.TypeEtape !== 'Quiz') opt.est_correcte = !opt.est_correcte; };

const submitStep = () => {
    const url = props.stepToEdit ? '/admin/etapes/' + props.stepToEdit.IdEtape + '/maj' : '/admin/etapes/insertion';
    stepForm.post(url, {
        onSuccess: () => {
            emit('close');
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Étape enregistrée', life: 3000 });
        },
        onError: (errs) => {
            Object.values(errs).forEach(msg => {
                toast.add({ severity: 'error', summary: 'Erreur', detail: msg, life: 5000 });
            });
        }
    });
};
</script>

<style scoped>
.create-form {
    padding: 10px 0;
}

.form-grid {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.field-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.field-group label {
    font-weight: 600;
    font-size: 0.9rem;
    color: #374151;
}

/* SÉLECTEUR CENTRÉ */
.type-selector-container {
    display: flex;
    justify-content: center;
}

.type-selector {
    display: flex;
    gap: 10px;
    background: #f3f4f6;
    padding: 6px;
    border-radius: 14px;
}

.type-pill {
    padding: 10px 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 600;
    font-size: 0.85rem;
    color: #6b7280;
}

.type-pill.active {
    background: white;
    color: #8A38F5;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

/* SECTION CONTENU */
.lesson-type-section {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    padding: 24px;
    margin-top: 10px;
}

.section-header {
    margin-bottom: 18px;
    border-bottom: 1px solid #f3f4f6;
    padding-bottom: 15px;
}

.step-badge {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    color: #8A38F5;
    letter-spacing: 0.5px;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: #111827;
    margin: 4px 0 0 0;
}

/* QUESTION */
.question-card {
    margin-bottom: 24px;
}

.label-with-icon {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #6b7280;
    margin-bottom: 8px;
}

.premium-input-large {
    width: 100%;
    border: 1.5px solid #e5e7eb !important;
    border-radius: 12px !important;
    padding: 14px !important;
    font-size: 1rem;
    transition: border-color 0.2s;
}

.premium-input-large:focus {
    border-color: #8A38F5 !important;
}

/* OPTIONS */
.options-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.options-title-block label {
    display: block;
    font-weight: 700;
    color: #111827;
}

.options-title-block small {
    color: #6b7280;
}

.btn-black-action {
    background: #111827 !important;
    border: none !important;
    border-radius: 15px !important;
    padding: 1.5vh 1.5vw !important;
    font-size: 1.1rem !important;
}

.options-grid {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.option-row {
    display: flex;
    align-items: center;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 6px 10px;
    transition: 0.2s;
}

.option-row:hover {
    border-color: #d1d5db;
    background: #f9fafb;
}

.option-row.is-correct-row {
    background: #1a878d97;
}

.option-check-wrapper {
    padding: 0 10px;
    cursor: pointer;
}

.custom-check-indicator {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    display: flex;
    border: 1px solid #1a1a1a;
    align-items: center;
    justify-content: center;
    color: white;
    transition: 0.2s;
}

.option-check-wrapper.is-radio .custom-check-indicator {
    border-radius: 50%;
}

.custom-check-indicator.checked {
    color: #ffffff;
    border: none;
    background: #1A888D;
}

.option-content-side {
    flex: 1;
}

.option-input-clean {
    width: 100%;
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    font-weight: 500;
    padding: 8px !important;
}

/* MODERNISED ADMIN ALERT */
.modern-admin-alert {
    position: relative;
    display: flex;
    align-items: center;
    gap: 16px;
    background: #1a1a1a;
    border-radius: 14px;
    padding: 16px;
    margin-top: 20px;
    overflow: hidden;
}

.alert-body h4 {
    margin: 0;
    font-size: 1.1rem;
    padding: 0 2vw;
    font-weight: 500;
    color: #ffffff;
}

.alert-body p {
    margin: 4px 0 0 0;
    font-size: 1rem;
    padding: 0 2vw;
    color: #ababab;
    line-height: 1.4;
}

.modal-footer {
    margin-top: 25px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.option-list-enter-active,
.option-list-leave-active {
    transition: all 0.3s ease;
}

.option-list-enter-from,
.option-list-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>