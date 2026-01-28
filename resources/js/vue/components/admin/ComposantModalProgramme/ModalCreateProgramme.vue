<template>
    <PremiumModal :isOpen="isOpen" :header="programToEdit ? 'Modifier le Programme' : 'Nouveau Programme de Formation'"
        width="55rem" @close="$emit('close')">
        <form @submit.prevent="submitCreate" class="create-form">
            <div class="form-grid">
                <div class="field-row">
                    <div class="field-group">
                        <PremiumSlideToggle v-model="form.Statut" checkedValue="Publié" uncheckedValue="Dépublié"
                            activeColor="#22c55e" />
                    </div>
                    <div class="field-group">
                        <PremiumSlideToggle v-model="form.StatutVerrouillageProgression" checkedValue="Verrouillé"
                            uncheckedValue="Libre" checkedLabel="Progression Verrouillée"
                            uncheckedLabel="Progression Libre" activeColor="#3b82f6" />
                    </div>
                </div>

                <div class="field-group full-width">
                    <label>Catégorie ({{ categories?.length || 0 }} disponibles)</label>
                    <Select v-model="form.IdCategorie" :options="categories" optionLabel="Nom" optionValue="IdCategorie"
                        placeholder="Sélectionnez une catégorie" class="w-full" filter appendTo="body" />
                </div>

                <div class="field-group full-width section-image">
                    <label class="section-label">Image du Programme</label>
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
                    <label>Titre du Programme</label>
                    <InputText v-model="form.Titre" placeholder="Ex: Masterclass de Coaching" required />
                </div>

                <div class="field-group full-width">
                    <label>Lien Aperçu Vidéo</label>
                    <InputText v-model="form.ApercuVideo" placeholder="https://www.youtube.com/..." />
                </div>

                <div class="field-group full-width">
                    <label>Description</label>
                    <Textarea v-model="form.Descriptions" rows="4" placeholder="Détails du programme..." />
                </div>

                <div class="field-row">
                    <div class="field-group">
                        <label>Prix en ariary</label>
                        <InputNumber v-model="form.Prix" mode="decimal" suffix=" Ar" locale="fr-FR"
                            :minFractionDigits="0" :useGrouping="true" />
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
import { useForm } from '@inertiajs/vue3';
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
    Titre: '',
    Descriptions: '',
    Prix: 0,
    LienPhoto: '',
    photoFile: null,
    Statut: 'Dépublié',
    StatutVerrouillageProgression: 'Libre',
    ApercuVideo: '',
    IdCategorie: null
});




watch(() => props.programToEdit, (newVal) => {
    if (newVal) {
        form.Titre = newVal.Titre;
        form.Descriptions = newVal.Descriptions;
        form.Prix = newVal.Prix;
        form.LienPhoto = newVal.LienPhoto;
        form.Statut = newVal.Statut;
        form.StatutVerrouillageProgression = newVal.StatutVerrouillageProgression;
        form.ApercuVideo = newVal.ApercuVideo;
        form.IdCategorie = newVal.IdCategorie;
    } else {
        form.reset();
        form.Statut = 'Dépublié'; // Ensure default
    }
}, { immediate: true });

const onFileSelect = (event) => {
    form.photoFile = event.files[0];
};

const submitCreate = () => {
    if (props.programToEdit) {
        // Handle Update
        // router.post does not support multipart/form-data for PUT/PATCH easily with Inertia sometimes, 
        // but Laravel allows POST method spoofing if we were using it. 
        // Here we use a dedicated POST route for update to simplify file upload handling.
        form.post(`/admin/programmes/${props.programToEdit.IdProgrammeFormation}/update`, {
            onSuccess: () => {
                emit('close');
                form.reset();
            }
        });
    } else {
        // Handle Create
        form.post('/admin/programmes/insertion', {
            onSuccess: () => {
                emit('close');
                form.reset();
            }
        });
    }
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
</style>
