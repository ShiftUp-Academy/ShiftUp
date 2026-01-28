<template>
    <PremiumModal :isOpen="isOpen" :header="programToEdit ? 'Modifier le Séminaire' : 'Nouveau Programme de Séminaire'"
        width="55rem" @close="$emit('close')">
        <form @submit.prevent="submitCreate" class="create-form">
            <div class="form-grid">
                <div class="field-row">
                    <div class="field-group">
                        <PremiumSlideToggle v-model="form.Statut" checkedValue="Publié" uncheckedValue="Dépublié"
                            activeColor="#22c55e" />
                    </div>
                </div>

                <div class="field-group full-width">
                    <label>Catégorie ({{ categories?.length || 0 }} disponibles)</label>
                    <Select v-model="form.IdCategorie" :options="categories" optionLabel="Nom" optionValue="IdCategorie"
                        placeholder="Sélectionnez une catégorie" class="w-full" filter appendTo="body" />
                </div>

                <div class="field-group full-width section-image">
                    <label class="section-label">Image du Séminaire</label>
                    <div class="image-upload-wrapper">
                        <Toast />
                        <FileUpload name="PhotoFile" @select="onFileSelect" :multiple="false" :fileLimit="1"
                            accept="image/*" :maxFileSize="13000000" customUpload @uploader="() => { }">
                            <template #empty>
                                <span>Ou faites glisser ici (4252x2481 ou format similaire)</span>
                            </template>
                        </FileUpload>

                        <div class="divider-container my-4">
                            <span class="divider">OU UTILISER UN LIEN</span>
                        </div>

                        <InputText v-model="form.LienPhoto" placeholder="Coller un lien Cloudinary ou imagekit.io"
                            class="w-full" style="width: 100%" />
                    </div>
                </div>

                <div class="field-group full-width">
                    <label>Titre du Séminaire</label>
                    <InputText v-model="form.Titre" placeholder="Ex: Masterclass Leadership" required />
                </div>

                <div class="field-group full-width">
                    <label>Description</label>
                    <Textarea v-model="form.Descriptions" rows="4" placeholder="Détails du séminaire..." />
                </div>

                <div class="field-row">
                    <div class="field-group">
                        <label>Date du Séminaire</label>
                        <DatePicker v-model="form.DateSeminaire" dateFormat="dd/mm/yy"
                            placeholder="Sélectionner une date" required appendTo="body" />
                    </div>
                    <div class="field-group">
                        <label>Heure de Début</label>
                        <DatePicker v-model="form.HeureSeminaire" timeOnly placeholder="HH:mm" required
                            appendTo="body" />
                    </div>
                </div>

                <div class="field-group full-width modality-section">
                    <label>Modalité du Séminaire</label>
                    <div class="modality-toggle-wrapper">
                        <div class="modality-options">
                            <div class="modality-option" :class="{ active: form.ModaliteSeminaire === 'Présentiel' }"
                                @click="form.ModaliteSeminaire = 'Présentiel'">
                                <i class="fas fa-users"></i>
                                <span>Présentiel</span>
                            </div>
                            <div class="modality-option" :class="{ active: form.ModaliteSeminaire === 'En ligne' }"
                                @click="form.ModaliteSeminaire = 'En ligne'">
                                <i class="fas fa-video"></i>
                                <span>En ligne</span>
                            </div>
                        </div>
                    </div>
                </div>

                <transition name="fade-slide">
                    <div v-if="form.ModaliteSeminaire === 'Présentiel'" class="field-group full-width">
                        <label>Lieu</label>
                        <InputText v-model="form.LieuSeminaire" placeholder="Ex: Antananarivo, Hôtel Carlton..."
                            required />
                    </div>
                    <div v-else class="field-group full-width">
                        <label>Lien Google Meet</label>
                        <InputText v-model="form.LienGoogleMeet" placeholder="https://meet.google.com/..." required />
                    </div>
                </transition>

                <div class="field-row">
                    <div class="field-group">
                        <label>Prix en ariary</label>
                        <InputNumber v-model="form.Prix" mode="decimal" suffix=" Ar" locale="fr-FR"
                            :minFractionDigits="0" :useGrouping="true" />
                    </div>
                    <div class="field-group">
                        <label>Durée (en jours)</label>
                        <InputNumber v-model="form.NombreDeJours" placeholder="Ex: 3" :min="1" />
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <Button label="Annuler" text @click="$emit('close')" />
                <PremiumButton type="submit" :loading="form.processing" width="12vw">
                    Enregistrer
                </PremiumButton>
            </div>
        </form>
    </PremiumModal>
</template>

<script setup>
import { watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import PremiumModal from '../../../components/ui/PremiumModal.vue';
import PremiumButton from '../../../components/ui/PremiumButton.vue';
import PremiumSlideToggle from '../../../components/ui/PremiumSlideToggle.vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import Toast from 'primevue/toast';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';

const props = defineProps({
    isOpen: Boolean,
    categories: {
        type: Array,
        default: () => []
    },
    programToEdit: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close']);

const form = useForm({
    Type: 'Seminaire',
    Titre: '',
    Descriptions: '',
    Prix: 0,
    LienPhoto: '',
    photoFile: null,
    Statut: 'Dépublié',
    StatutVerrouillageProgression: 'Libre',
    DateSeminaire: null,
    HeureSeminaire: null,
    ModaliteSeminaire: 'Présentiel',
    LieuSeminaire: '',
    LienGoogleMeet: '',
    NombreDeJours: null,
    IdCategorie: null
});

watch(() => props.programToEdit, (newVal) => {
    if (newVal) {
        form.Titre = newVal.Titre;
        form.Descriptions = newVal.Descriptions;
        form.Prix = newVal.Prix;
        form.LienPhoto = newVal.LienPhoto;
        form.Statut = newVal.Statut;
        form.DateSeminaire = newVal.DateSeminaire ? new Date(newVal.DateSeminaire) : null;
        if (newVal.HeureSeminaire) {
            const [hours, minutes] = newVal.HeureSeminaire.split(':');
            const time = new Date();
            time.setHours(parseInt(hours), parseInt(minutes), 0);
            form.HeureSeminaire = time;
        } else {
            form.HeureSeminaire = null;
        }
        form.ModaliteSeminaire = newVal.ModaliteSeminaire || 'Présentiel';
        form.LieuSeminaire = newVal.LieuSeminaire || '';
        form.LienGoogleMeet = newVal.LienGoogleMeet || '';
        form.NombreDeJours = newVal.NombreDeJours || null;
        form.IdCategorie = newVal.IdCategorie;
    } else {
        form.reset();
        form.Type = 'Seminaire';
        form.Statut = 'Dépublié';
        form.ModaliteSeminaire = 'Présentiel';
    }
}, { immediate: true });

const onFileSelect = (event) => {
    form.photoFile = event.files[0];
};

const submitCreate = () => {
    // Format dates before sending
    const dataToSend = { ...form.data() };
    if (dataToSend.DateSeminaire instanceof Date) {
        dataToSend.DateSeminaire = dataToSend.DateSeminaire.toISOString().split('T')[0];
    }
    if (dataToSend.HeureSeminaire instanceof Date) {
        dataToSend.HeureSeminaire = dataToSend.HeureSeminaire.toLocaleTimeString('fr-FR', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
    }

    const url = props.programToEdit
        ? `/admin/programmes/${props.programToEdit.IdProgrammeFormation}/update`
        : '/admin/programmes/insertion';

    router.post(url, dataToSend, {
        forceFormData: true,
        onSuccess: () => {
            emit('close');
            form.reset();
        }
    });
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

.image-upload-wrapper {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 12px;
    border: 1px solid #eee;
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

.modal-footer {
    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* Modality options */
.modality-options {
    display: flex;
    gap: 15px;
    margin-top: 5px;
}

.modality-option {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 12px;
    background: #f4f4f4;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    color: #666;
    font-weight: 600;
}

.modality-option i {
    font-size: 1.1rem;
}

.modality-option.active {
    background: white;
    border-color: #8A38F5;
    color: #8A38F5;
    box-shadow: 0 4px 12px rgba(138, 56, 245, 0.1);
}

.modality-option:hover:not(.active) {
    background: #eee;
}

/* Transitions */
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
