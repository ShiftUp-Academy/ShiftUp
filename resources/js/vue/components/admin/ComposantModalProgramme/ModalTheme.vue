<template>
    <PremiumModal :isOpen="isOpen" :header="themeToEdit ? 'Modifier le Chapitre' : 'Nouveau Chapitre'" width="40rem"
        @close="$emit('close')">
        <Toast />
        <form @submit.prevent="submitTheme" class="create-form">
            <div class="form-grid">
                <div class="field-group full-width">
                    <label>Titre du Chapitre</label>
                    <InputText v-model="themeForm.Titre" required />
                </div>
                <div class="field-group full-width">
                    <label>Description</label>
                    <Textarea v-model="themeForm.Descriptions" rows="3" />
                </div>
                <div class="field-group">
                    <div class="flex align-items-center gap-3">
                        <PremiumSlideToggle v-model="themeForm.Statut" checkedValue="Publié" uncheckedValue="Dépublié"
                            activeColor="#22c55e" />
                        <label>Statut du Chapitre</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <Button label="Annuler" text @click="$emit('close')" />
                    <PremiumButton type="submit" :loading="themeForm.processing">Enregistrer</PremiumButton>
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
import Button from 'primevue/button';
import Toast from 'primevue/toast';

const props = defineProps({
    isOpen: Boolean,
    programId: [String, Number],
    themeToEdit: Object
});

const emit = defineEmits(['close']);
const toast = useToast();

const themeForm = useForm({
    IdProgramme: null,
    Titre: '',
    Descriptions: '',
    Statut: 'Dépublié',
    Ordre: 0
});

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        themeForm.clearErrors();
        if (props.themeToEdit) {
            themeForm.IdProgramme = props.themeToEdit.IdProgramme;
            themeForm.Titre = props.themeToEdit.Titre;
            themeForm.Descriptions = props.themeToEdit.Descriptions;
            themeForm.Statut = props.themeToEdit.Statut || 'Dépublié';
            themeForm.Ordre = props.themeToEdit.Ordre;
        } else {
            themeForm.reset();
            themeForm.IdProgramme = props.programId;
        }
    }
});

const submitTheme = () => {
    const url = props.themeToEdit
        ? '/admin/themes/' + props.themeToEdit.IdTheme + '/maj'
        : '/admin/themes/insertion';

    themeForm.post(url, {
        onSuccess: () => {
            emit('close');
            toast.add({ severity: 'success', summary: props.themeToEdit ? 'Chapitre Modifié' : 'Chapitre Ajouté', life: 3000 });
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
</style>
