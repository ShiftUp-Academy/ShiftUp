<template>
    <PremiumModal :isOpen="isOpen" :header="lessonToEdit ? 'Modifier la Leçon' : 'Nouvelle Leçon'" width="55rem"
        @close="$emit('close')">
        <Toast />
        <form @submit.prevent="submitLesson" class="create-form">
            <div class="form-grid">
                <div class="field-row">
                    <div class="field-group full-width"><label>Titre de la leçon</label>
                        <InputText v-model="lessonForm.Titre" required />
                    </div>
                    <div class="field-group"><label>Points Offerts</label>
                        <InputNumber v-model="lessonForm.PointsOfferts" :min="0" />
                    </div>
                </div>
                <div class="field-group full-width"><label>Description</label><Textarea
                        v-model="lessonForm.Descriptions" rows="3" /></div>
                <div class="field-row">
                    <div class="field-group full-width">
                        <label>Type de leçon</label>
                        <div class="type-selector">
                            <div v-for="type in lessonTypes" :key="type.value" class="type-pill"
                                :class="{ active: lessonForm.TypeLecon === type.value }"
                                @click="lessonForm.TypeLecon = type.value">
                                <i :class="type.icon"></i><span>{{ type.label }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="field-group">
                        <div class="flex align-items-center justify-content-center" style="margin-top: 6vh;">
                            <PremiumSlideToggle v-model="lessonForm.Statut" checkedValue="Publié"
                                uncheckedValue="Dépublié" activeColor="#22c55e" />
                        </div>
                    </div>
                </div>

                <div v-if="lessonForm.TypeLecon === 'Vidéo'" class="lesson-type-section">
                    <div class="field-row">
                        <div class="field-group" style="flex: 2"><label>Lien Vidéo</label>
                            <InputText v-model="lessonForm.Contenu" required />
                        </div>
                        <div class="field-group"><label>Délais de drip (en heures)</label>
                            <InputNumber v-model="lessonForm.DelaiDrop" :min="0" />
                        </div>
                    </div>
                </div>

                <div v-if="lessonForm.TypeLecon === 'PDF'" class="lesson-type-section">
                    <div class="field-row">
                        <div class="field-group"><label>Fichier PDF (Max 50Mo)</label>
                            <FileUpload mode="basic" :maxFileSize="52428800"
                                @select="(e) => lessonForm.ContenuFile = e.files[0]" />
                        </div>
                        <div class="field-group"><label>Pages</label>
                            <InputNumber v-model="lessonForm.NombreDePages" />
                        </div>
                    </div>
                </div>

                <div v-if="lessonForm.TypeLecon === 'Texte'" class="lesson-type-section">
                    <div class="field-group full-width"><label>Contenu</label>
                        <Editor v-model="lessonForm.Contenu" editorStyle="height: 320px" />
                    </div>
                </div>

                <div class="divider-container"><span class="divider">ÉTAPES INTERACTIVES</span></div>
                <div class="steps-preview-section">
                    <div v-if="lessonToEdit?.etapes?.length > 0" class="existing-steps-list">
                        <div v-for="step in lessonToEdit.etapes" :key="step.IdEtape" class="step-preview-item">
                            <div class="step-info">
                                <span class="step-type-badge">{{ step.TypeEtape }}</span>
                                <div class="step-details-summary">
                                    <p class="step-title-small">{{ step.Titre }}</p>
                                    <p v-if="step.questions?.[0]" class="step-question-preview">{{
                                        step.questions[0].Intitule }}</p>
                                </div>
                            </div>
                            <div class="step-actions">
                                <button type="button" class="btn-icon" @click="$emit('edit-step', step)"><i
                                        class="fas fa-pen"></i></button>
                                <button type="button" class="btn-icon delete" @click="deleteEtape(step.IdEtape)"><i
                                        class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="add-step-btn-large"
                        @click="$emit('create-step', lessonToEdit?.IdLecon)" :disabled="!lessonToEdit">
                        <i class="fas fa-plus"></i> Ajouter une étape interactive
                        <span v-if="!lessonToEdit"
                            style="font-size: 0.8rem; display: block; font-weight: 400">(Enregistrez la leçon
                            d'abord)</span>
                    </button>
                </div>

                <div class="modal-footer">
                    <Button label="Annuler" text @click="$emit('close')" />
                    <PremiumButton type="submit" :loading="lessonForm.processing">Enregistrer la leçon</PremiumButton>
                </div>
            </div>
        </form>

        <ConfirmModal :isOpen="confirmData.isOpen" :title="confirmData.title" :message="confirmData.message"
            :type="confirmData.type" @confirm="onModalConfirm" @cancel="confirmData.isOpen = false" />
    </PremiumModal>
</template>

<script setup>
import { watch, ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import PremiumModal from '../../../components/ui/PremiumModal.vue';
import PremiumButton from '../../../components/ui/PremiumButton.vue';
import ConfirmModal from '../../../components/ui/ConfirmModal.vue';
import PremiumSlideToggle from '../../../components/ui/PremiumSlideToggle.vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Editor from 'primevue/editor';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import Toast from 'primevue/toast';

const props = defineProps({
    isOpen: Boolean,
    programId: [String, Number],
    defaultThemeId: [String, Number],
    lessonToEdit: Object,
    defaultOrder: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['close', 'edit-step', 'create-step']);
const toast = useToast();

const lessonTypes = [
    { label: 'Vidéo', value: 'Vidéo', icon: 'fas fa-video' },
    { label: 'PDF', value: 'PDF', icon: 'fas fa-file-pdf' },
    { label: 'Texte', value: 'Texte', icon: 'fas fa-align-left' }
];

const lessonForm = useForm({
    IdProgramme: null,
    IdTheme: null,
    Titre: '',
    Descriptions: '',
    Statut: 'Dépublié',
    TypeLecon: 'Vidéo',
    SourceType: 'Youtube',
    Contenu: '',
    ContenuFile: null,
    DelaiDrop: 0,
    NombreDePages: 0,
    PointsOfferts: 0,
    Ordre: 0
});

const confirmData = ref({
    isOpen: false,
    title: '',
    message: '',
    type: 'danger',
    action: null
});

const onModalConfirm = () => {
    if (confirmData.value.action) confirmData.value.action();
    confirmData.value.isOpen = false;
};

const triggerConfirm = (title, message, type, action) => {
    confirmData.value = { isOpen: true, title, message, type, action };
};

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        if (props.lessonToEdit) {
            const lecon = props.lessonToEdit;
            lessonForm.IdProgramme = lecon.IdProgramme;
            lessonForm.IdTheme = lecon.IdTheme;
            lessonForm.Titre = lecon.Titre;
            lessonForm.Descriptions = lecon.Descriptions;
            lessonForm.Statut = lecon.Statut;
            lessonForm.TypeLecon = lecon.TypeLecon;
            lessonForm.SourceType = lecon.SourceType;
            lessonForm.Contenu = lecon.Contenu;
            lessonForm.DelaiDrop = lecon.DelaiDrop;
            lessonForm.NombreDePages = lecon.NombreDePages;
            lessonForm.PointsOfferts = lecon.PointsOfferts;
            lessonForm.Ordre = lecon.Ordre;
        } else {
            lessonForm.reset();
            lessonForm.IdProgramme = props.programId;
            lessonForm.IdTheme = props.defaultThemeId;
            lessonForm.Ordre = props.defaultOrder;
        }
    }
});

const submitLesson = () => {
    // Validation client
    if (lessonForm.TypeLecon === 'Vidéo' && !lessonForm.Contenu) {
        toast.add({ severity: 'error', summary: 'Contenu manquant', detail: 'Veuillez saisir le lien de la vidéo.', life: 3000 });
        return;
    }
    if (lessonForm.TypeLecon === 'PDF' && !lessonForm.Contenu && !lessonForm.ContenuFile) {
        toast.add({ severity: 'error', summary: 'Contenu manquant', detail: 'Veuillez sélectionner un fichier PDF.', life: 3000 });
        return;
    }
    if (lessonForm.TypeLecon === 'Texte' && (!lessonForm.Contenu || lessonForm.Contenu.trim() === '')) {
        toast.add({ severity: 'error', summary: 'Contenu manquant', detail: 'Le contenu du texte ne peut pas être vide.', life: 3000 });
        return;
    }

    const url = props.lessonToEdit
        ? '/admin/lecons/' + props.lessonToEdit.IdLecon + '/maj'
        : '/admin/lecons/insertion';

    lessonForm.post(url, {
        onSuccess: () => {
            emit('close');
            toast.add({
                severity: 'success',
                summary: props.lessonToEdit ? 'Mis à jour' : 'Ajouté',
                life: 3000
            });
        },
        onError: (errs) => {
            Object.values(errs).forEach(msg => {
                toast.add({ severity: 'error', summary: 'Erreur', detail: msg, life: 5000 });
            });
        }
    });
};

const deleteEtape = (id) => {
    triggerConfirm(
        "Supprimer l'étape",
        "Voulez-vous vraiment supprimer cette étape interactive ?",
        "danger",
        () => {
            router.delete('/admin/etapes/' + id, {
                onSuccess: () => toast.add({ severity: 'success', summary: 'Supprimée', life: 3000 })
            });
        }
    );
};
</script>

<style scoped>
.create-form {
    padding: 20px 0;
    margin-bottom: 4vh;
}

.form-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.field-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.field-group label {
    font-weight: 600;
    font-size: 1rem;
    color: #111;
}

.modal-footer {
    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.divider-container {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    margin: 15px 0;
}

.divider-container::before {
    content: "";
    position: absolute;
    width: 100%;
    height: 1px;
    background: #eee;
}

.divider {
    background: #f9f9f9;
    padding: 0 10px;
    position: relative;
    font-size: 0.7rem;
    color: #999;
}

/* TYPE SELECTOR CUSTOM */
.type-selector {
    display: flex;
    gap: 15px;
    margin-top: 10px;
}

.type-pill {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 15px;
    background: #f8f9fa;
    border: 2px solid transparent;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    color: #666;
}

.type-pill i {
    font-size: 1.5rem;
    opacity: 0.7;
}

.type-pill span {
    font-weight: 600;
    font-size: 0.9rem;
}

.type-pill:hover {
    background: white;
    border-color: #ddd;
    transform: translateY(-2px);
}

.type-pill.active {
    background: white;
    border-color: #8A38F5;
    color: #8A38F5;
    box-shadow: 0 10px 20px rgba(138, 56, 245, 0.1);
}

.type-pill.active i {
    opacity: 1;
}

.lesson-type-section {
    animation: fadeInModal 0.4s ease;
    padding: 20px;
    background: rgba(138, 56, 245, 0.02);
    border-radius: 15px;
    margin-top: 20px;
}

@keyframes fadeInModal {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.steps-preview-section {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #eee;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

.add-step-btn-large {
    width: 100%;
    padding: 15px;
    background: white;
    border: 2px dashed #ddd;
    border-radius: 12px;
    color: #8A38F5;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
}

.add-step-btn-large:hover:not(:disabled) {
    background: rgba(138, 56, 245, 0.05);
    border-color: #8A38F5;
}

.add-step-btn-large:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.existing-steps-list {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 15px;
}

.step-preview-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    background: linear-gradient(95deg, #0E7EC3, #8A38F5);
    background-size: 200% 100%;
    border-radius: 12px;
    border: none;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    color: white;
    box-shadow: 0 4px 15px rgba(138, 56, 245, 0.2);
}

.step-preview-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(138, 56, 245, 0.3);
}

.step-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.step-type-badge {
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    padding: 3px 8px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(4px);
}

.step-details-summary {
    display: flex;
    flex-direction: column;
}

.step-title-small {
    font-size: 0.95rem;
    font-weight: 700;
    color: white;
    margin: 0;
    line-height: 1.2;
}

.step-question-preview {
    margin: 0;
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.8);
    font-style: italic;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 350px;
}

.step-actions {
    display: flex;
    gap: 8px;
}

.btn-icon {
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.9);
    cursor: pointer;
    padding: 5px;
    transition: all 0.2s;
}

.btn-icon:hover {
    color: white;
    transform: scale(1.1);
}

.btn-icon.delete:hover {
    color: #ff4d4d;
}
</style>
