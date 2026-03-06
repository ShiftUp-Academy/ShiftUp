<template>
    <PremiumModal :isOpen="isOpen" :header="'Validation : ' + submission?.utilisateur?.profil?.Prenom" width="50rem"
        @close="$emit('close')">
        <div v-if="submission" class="validation-details">
            <div class="submission-header">
                <div class="user-info">
                    <img :src="submission.utilisateur?.profil?.Avatar || '/images/default-avatar.png'"
                        class="user-avatar" />
                    <div>
                        <h3>{{ submission.utilisateur?.profil?.Prenom }} {{ submission.utilisateur?.profil?.Nom }}</h3>
                        <p class="text-gray-500 text-sm">{{ submission.utilisateur?.Email }}</p>
                    </div>
                </div>
                <div class="context-info">
                    <span class="badge-program">{{ submission.etape?.lecon?.theme?.programme?.Titre }}</span>
                    <p class="text-xs mt-1">Leçon : {{ submission.etape?.lecon?.Titre }}</p>
                    <p class="text-xs">Étape : {{ submission.etape?.Titre }}</p>
                </div>
            </div>

            <div class="questions-answers">
                <div v-for="detail in submission.details" :key="detail.IdDetail" class="qa-item">
                    <div class="question-text">
                        <i class="fas fa-question-circle mr-2"></i>
                        {{ detail.question?.Intitule }}
                    </div>
                    <div class="answer-text">
                        {{ detail.TexteReponse }}
                    </div>
                </div>
            </div>

            <div class="admin-response-section">
                <label class="block font-bold mb-2">Votre réponse / feedback</label>
                <Textarea v-model="form.ReponseAdmin" rows="4" class="w-full premium-textarea"
                    placeholder="Écrivez votre réponse ici. Elle sera envoyée par email à l'utilisateur." />
            </div>

            <div class="modal-footer">
                <button class="reject-btn" @click="submitSub('Rejeté')" :disabled="form.processing">
                    <i class="fas fa-times mr-2"></i> Rejeter
                </button>
                <PremiumButton @click="submitSub('Validé')" :loading="form.processing">
                    <i class="fas fa-check mr-2"></i> Valider & Envoyer
                </PremiumButton>
            </div>
        </div>
    </PremiumModal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import PremiumModal from '../../ui/PremiumModal.vue';
import PremiumButton from '../../ui/PremiumButton.vue';
import Textarea from 'primevue/textarea';
import { watch } from 'vue';

const props = defineProps({
    isOpen: Boolean,
    submission: Object
});

const emit = defineEmits(['close', 'success']);

const form = useForm({
    StatutValidation: '',
    ReponseAdmin: ''
});

watch(() => props.submission, (newVal) => {
    if (newVal) {
        form.ReponseAdmin = newVal.ReponseAdmin || '';
    }
});

const submitSub = (status) => {
    form.StatutValidation = status;
    form.post(`/admin/programmes/submissions/${props.submission.IdReponse}/validate`, {
        onSuccess: () => {
            emit('success');
            emit('close');
        }
    });
};
</script>

<style scoped>
.validation-details {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.submission-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.user-info h3 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
}

.badge-program {
    background: #8A38F5;
    color: white;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 700;
}

.questions-answers {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.qa-item {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 12px;
    border: 1px solid #eee;
}

.question-text {
    font-weight: 700;
    color: #555;
    margin-bottom: 8px;
}

.answer-text {
    color: #111;
    line-height: 1.5;
    white-space: pre-wrap;
    font-size: 1.05rem;
}

.premium-textarea {
    border-radius: 12px;
    border: 1px solid #ddd;
    padding: 15px;
    font-size: 1rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    margin-top: 10px;
}

.reject-btn {
    background: #fff;
    border: 1px solid #ef4444;
    color: #ef4444;
    padding: 10px 25px;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
}

.reject-btn:hover {
    background: #fee2e2;
}
</style>
