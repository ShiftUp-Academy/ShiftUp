<template>
    <PremiumModal :isOpen="isOpen" :dark="true" :showClose="false" width="450px" @close="onCancel">
        <div class="confirm-modal-body">
            <h2 class="confirm-title">{{ title }}</h2>

            <div class="confirm-icon" :class="type">
                <i v-if="type === 'danger'" class="fas fa-exclamation-triangle"></i>
                <i v-else-if="type === 'warning'" class="fas fa-exclamation-circle"></i>
                <i v-else class="fas fa-question-circle"></i>
            </div>

            <p class="confirm-message">{{ message }}</p>

            <div class="confirm-actions">
                <PremiumButton class="action-btn confirm-btn" :class="type" width="100%" @click="onConfirm">
                    {{ confirmLabel }}
                </PremiumButton>
            </div>
        </div>
    </PremiumModal>
</template>

<script setup>
import PremiumModal from './PremiumModal.vue';
import PremiumButton from './PremiumButton.vue';

const props = defineProps({
    isOpen: Boolean,
    title: {
        type: String,
        default: 'Confirmation'
    },
    message: {
        type: String,
        default: 'Êtes-vous sûr de vouloir continuer ?'
    },
    confirmLabel: {
        type: String,
        default: 'Confirmer'
    },
    cancelLabel: {
        type: String,
        default: 'Annuler'
    },
    type: {
        type: String,
        default: 'danger'
    }
});

const emit = defineEmits(['confirm', 'cancel']);

const onConfirm = () => emit('confirm');
const onCancel = () => emit('cancel');
</script>

<style scoped>
.confirm-modal-body {
    padding: 30px 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.confirm-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 25px;
    color: #fff;
}

.confirm-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 7rem;
}

.confirm-icon.danger {
    color: #ef4444;
    box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
}

.confirm-icon.warning {
    color: #f59e0b;
    box-shadow: 0 0 20px rgba(245, 158, 11, 0.2);
}

.confirm-icon.info {
    color: #3b82f6;
    box-shadow: 0 0 20px rgba(59, 130, 246, 0.2);
}

.confirm-message {
    font-size: 1.2rem;
    color: #aaa;
    margin-bottom: 30px;
    line-height: 1.3;
    max-width: 90%;
}

.confirm-actions {
    display: flex;
    gap: 15px;
    width: 100%;
}

.action-btn {
    --btn-bg: #3d3d3d;
}

.confirm-btn.danger {
    --btn-gradient: linear-gradient(90deg, #dc2626, #ae5d17, #dc2626);
}

.confirm-btn.warning {
    --btn-gradient: linear-gradient(90deg, #d97706, #F7B455, #d97706);
}

.confirm-btn.info {
    --btn-gradient: linear-gradient(90deg, #2563eb, #1A888D, #2563eb);
}
</style>
