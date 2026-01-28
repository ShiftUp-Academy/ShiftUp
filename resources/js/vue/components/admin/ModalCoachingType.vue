<template>
    <PremiumModal :isOpen="isOpen" :header="typeToEdit ? 'Modifier le Type de Coaching' : 'Nouveau Type de Coaching'"
        width="45rem" @close="$emit('close')">
        <form @submit.prevent="submitForm" class="create-form">
            <div class="form-grid">
                <div class="field-group">
                    <PremiumSlideToggle v-model="form.Statut" checkedValue="Publié" uncheckedValue="Dépublié"
                        activeColor="#22c55e" />
                </div>

                <div class="field-group full-width">
                    <label>Nom</label>
                    <InputText v-model="form.NomDeType" placeholder="Ex: Coaching Business Stratégique" required />
                </div>

                <div class="field-group full-width">
                    <label>Description (Optionnel)</label>
                    <Textarea v-model="form.Descriptions" rows="4"
                        placeholder="Détails sur ce que contient ce coaching..." />
                </div>

                <div class="field-group">
                    <label>Prix par session</label>
                    <InputNumber v-model="form.Prix" mode="decimal" suffix=" Ar" locale="fr-FR" :minFractionDigits="0"
                        :useGrouping="true" />
                </div>
            </div>

            <div class="modal-footer">
                <Button label="Annuler" text @click="$emit('close')" />
                <PremiumButton type="submit" :loading="form.processing">
                    Enregistrer
                </PremiumButton>
            </div>
        </form>
    </PremiumModal>
</template>

<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PremiumModal from '../ui/PremiumModal.vue';
import PremiumButton from '../ui/PremiumButton.vue';
import PremiumSlideToggle from '../ui/PremiumSlideToggle.vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';

const props = defineProps({
    isOpen: Boolean,
    typeToEdit: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close']);

const form = useForm({
    NomDeType: '',
    Descriptions: '',
    Prix: 0,
    Statut: 'Dépublié'
});

watch(() => props.typeToEdit, (newVal) => {
    if (newVal) {
        form.NomDeType = newVal.NomDeType;
        form.Descriptions = newVal.Descriptions;
        form.Prix = Number(newVal.Prix);
        form.Statut = newVal.Statut;
    } else {
        form.reset();
        form.Statut = 'Dépublié';
    }
}, { immediate: true });

const submitForm = () => {
    const url = props.typeToEdit
        ? `/admin/coachings/types/${props.typeToEdit.IdTypeCoaching}`
        : '/admin/coachings/types';

    form.post(url, {
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
