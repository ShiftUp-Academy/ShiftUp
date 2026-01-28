<template>
    <PremiumModal :isOpen="isOpen" header="Programmer les disponibilités" width="50rem" @close="$emit('close')">
        <form @submit.prevent="submitForm" class="create-form">
            <p class="section-hint">Précisez les créneaux où vous êtes disponible pour les séances de coaching ce
                mois-ci.</p>

            <div class="slots-container">
                <div v-for="(slot, index) in form.slots" :key="index" class="slot-row">
                    <div class="field-group date-col">
                        <label v-if="index === 0">Date</label>
                        <DatePicker v-model="slot.DateDisponible" dateFormat="dd/mm/yy"
                            placeholder="Sélectionner une date" required appendTo="body" />
                    </div>
                    <div class="field-group time-col">
                        <label v-if="index === 0">Heure Début</label>
                        <DatePicker v-model="slot.HeureDebut" timeOnly placeholder="HH:mm" required appendTo="body"
                            :stepMinute="15" />
                    </div>
                    <div class="field-group time-col">
                        <label v-if="index === 0">Heure Fin</label>
                        <DatePicker v-model="slot.HeureFin" timeOnly placeholder="HH:mm" required appendTo="body"
                            :stepMinute="15" />
                    </div>
                    <div class="action-col" :class="{ 'with-label': index === 0 }">
                        <button type="button" class="remove-btn" @click="removeSlot(index)"
                            :disabled="form.slots.length === 1">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" class="add-slot-btn" @click="addSlot">
                <i class="fas fa-plus"></i> Ajouter un autre créneau
            </button>

            <div class="modal-footer">
                <Button label="Annuler" text @click="$emit('close')" />
                <PremiumButton type="submit" :loading="form.processing">
                    Enregistrer les disponibilités
                </PremiumButton>
            </div>
        </form>
    </PremiumModal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import PremiumModal from '../ui/PremiumModal.vue';
import PremiumButton from '../ui/PremiumButton.vue';
import DatePicker from 'primevue/datepicker';
import Button from 'primevue/button';

const props = defineProps({
    isOpen: Boolean,
    existingAvailabilities: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close']);

const form = useForm({
    slots: []
});

import { watch } from 'vue';

watch(() => props.isOpen, (isNowOpen) => {
    if (isNowOpen) {
        if (props.existingAvailabilities && props.existingAvailabilities.length > 0) {
            form.slots = props.existingAvailabilities.map(a => {
                // Parse date string (YYYY-MM-DD)
                const date = new Date(a.DateDisponible);

                // Parse time strings (HH:mm:ss)
                const [startH, startM] = a.HeureDebut.split(':').map(Number);
                const [endH, endM] = a.HeureFin.split(':').map(Number);

                const start = new Date();
                start.setHours(startH, startM, 0);

                const end = new Date();
                end.setHours(endH, endM, 0);

                return {
                    DateDisponible: date,
                    HeureDebut: start,
                    HeureFin: end
                };
            });
        } else {
            form.slots = [{ DateDisponible: null, HeureDebut: null, HeureFin: null }];
        }
    }
}, { immediate: true });

const addSlot = () => {
    form.slots.push({ DateDisponible: null, HeureDebut: null, HeureFin: null });
};

const removeSlot = (index) => {
    if (form.slots.length > 1) {
        form.slots.splice(index, 1);
    } else {
        form.slots[0] = { DateDisponible: null, HeureDebut: null, HeureFin: null };
    }
};

const submitForm = () => {
    // Format dates and times before sending
    const formattedSlots = form.slots.map(slot => {
        const s = { ...slot };
        if (s.DateDisponible instanceof Date) {
            s.DateDisponible = s.DateDisponible.toISOString().split('T')[0];
        }
        if (s.HeureDebut instanceof Date) {
            s.HeureDebut = s.HeureDebut.toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        }
        if (s.HeureFin instanceof Date) {
            s.HeureFin = s.HeureFin.toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        }
        return s;
    });

    form.transform((data) => ({
        slots: formattedSlots
    })).post('/admin/coachings/availabilities', {
        onSuccess: () => {
            emit('close');
            form.reset();
        }
    });
};
</script>

<style scoped>
.create-form {
    padding: 10px 0;
}

.section-hint {
    color: #666;
    margin-bottom: 25px;
    font-size: 0.95rem;
}

.slots-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
    max-height: 400px;
    overflow-y: auto;
    padding-right: 10px;
    margin-bottom: 20px;
}

.slot-row {
    display: flex;
    gap: 15px;
    align-items: flex-end;
}

.field-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.date-col {
    flex: 2;
}

.time-col {
    flex: 1;
}

.action-col {
    padding-bottom: 4px;
}

.action-col.with-label {
    padding-bottom: 4px;
}

.field-group label {
    font-weight: 600;
    font-size: 0.9rem;
    color: #111;
}

.remove-btn {
    background: #fff5f5;
    color: #e53e3e;
    border: 1px solid #fed7d7;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.remove-btn:hover:not(:disabled) {
    background: #feb2b2;
    color: #fff;
}

.remove-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.add-slot-btn {
    background: #f8f9fa;
    border: 2px dashed #ddd;
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    color: #666;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.add-slot-btn:hover {
    border-color: #8A38F5;
    color: #8A38F5;
    background: #f5f0ff;
}

.modal-footer {
    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
</style>
