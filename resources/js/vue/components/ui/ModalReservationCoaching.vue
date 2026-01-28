<template>
    <PremiumModal :isOpen="isOpen" :header="'Réserver votre coaching'" width="50rem" :dark="true"
        @close="$emit('close')">
        <div class="reservation-modal-body" v-if="coaching">
            <div class="coaching-summary">
                <div class="summary-content">
                    <h3 class="type-name">{{ coaching.NomDeType }}</h3>
                    <p class="description">{{ coaching.Descriptions }}</p>
                </div>
                <div class="price-badge">
                    <span class="label">Prix</span>
                    <span class="amount">{{ formatPrice(coaching.Prix) }}</span>
                </div>
            </div>

            <form @submit.prevent="submitReservation" class="reservation-form">
                <div class="selection-section">
                    <h4 class="section-title">1. Choisir une date</h4>
                    <div class="date-picker-wrapper">
                        <DatePicker v-model="selectedDate" inline :minDate="new Date()" class="custom-datepicker"
                            @date-select="onDateSelect">
                            <template #date="slotProps">
                                <div class="datepicker-day-cell"
                                    :class="{ 'has-availability': isDateAvailable(slotProps.date) }">
                                    {{ slotProps.date.day }}
                                    <div v-if="isDateAvailable(slotProps.date)" class="availability-dot"></div>
                                </div>
                            </template>
                        </DatePicker>
                    </div>

                    <div v-if="selectedDate" class="time-selection fade-in">
                        <h4 class="section-title mt-4">2. Choisir l'heure</h4>
                        <div v-if="selectableIntervals.length > 0" class="time-slots-grid">
                            <div v-for="slot in selectableIntervals" :key="slot.time + slot.id" class="time-card"
                                :class="{ active: form.HeureChoisie === slot.time && form.IdDisponibilite === slot.id }"
                                @click="selectTime(slot)">
                                <i class="far fa-clock"></i>
                                <span class="slot-time">{{ slot.time }}</span>
                            </div>
                        </div>
                        <div v-else class="no-slots-small">
                            Complet ce jour-là.
                        </div>
                    </div>
                </div>

                <div class="note-section">
                    <h4 class="section-title">Note ou message (Optionnel)</h4>
                    <textarea v-model="form.NoteUtilisateur"
                        placeholder="Décrivez brièvement vos attentes pour cette séance..." rows="3"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="cancel-btn" @click="$emit('close')">Annuler</button>
                    <PremiumButton type="submit" :loading="form.processing"
                        :disabled="!form.IdDisponibilite || !form.HeureChoisie" class="confirm-btn">
                        Confirmer la réservation
                    </PremiumButton>
                </div>
            </form>
        </div>
    </PremiumModal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import PremiumModal from '../ui/PremiumModal.vue';
import PremiumButton from '../ui/PremiumButton.vue';
import DatePicker from 'primevue/datepicker';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    isOpen: Boolean,
    coaching: Object,
    availabilities: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'success']);

const form = useForm({
    IdTypeCoaching: null,
    IdDisponibilite: null,
    HeureChoisie: null,
    NoteUtilisateur: ''
});

// Watch for coaching change to update form
watch(() => props.coaching, (newVal) => {
    if (newVal) {
        form.IdTypeCoaching = newVal.IdTypeCoaching;
        form.IdDisponibilite = null;
        form.HeureChoisie = null;
        selectedDate.value = null; // Reset selection
    }
});

const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleDateString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long'
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MGA', minimumFractionDigits: 0 }).format(price);
};

const selectedDate = ref(null);

const availableDates = computed(() => {
    const dates = new Set(props.availabilities.map(a => a.DateDisponible));
    return Array.from(dates);
});

const isDateAvailable = (date) => {
    if (!date) return false;

    let y, m, d;
    if (date.year !== undefined && date.month !== undefined && date.day !== undefined) {
        y = date.year;
        m = date.month;
        d = date.day;
    } else {
        const dateObj = new Date(date);
        y = dateObj.getFullYear();
        m = dateObj.getMonth();
        d = dateObj.getDate();
    }

    const dateStr = `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    return availableDates.value.includes(dateStr);
};

const selectableIntervals = computed(() => {
    if (!selectedDate.value) return [];
    const selDate = new Date(selectedDate.value);

    const relevantSlots = props.availabilities.filter(slot => {
        const slotDate = new Date(slot.DateDisponible);
        return slotDate.getDate() === selDate.getDate() &&
            slotDate.getMonth() === selDate.getMonth() &&
            slotDate.getFullYear() === selDate.getFullYear();
    });

    const intervals = [];
    relevantSlots.forEach(slot => {
        const [startH, startM] = slot.HeureDebut.split(':').map(Number);
        const [endH, endM] = slot.HeureFin.split(':').map(Number);

        let currentH = startH;
        let currentM = startM;

        while (currentH < endH || (currentH === endH && currentM < endM)) {
            const timeStr = `${String(currentH).padStart(2, '0')}:${String(currentM).padStart(2, '0')}`;
            intervals.push({
                time: timeStr,
                id: slot.IdDisponibilite
            });

            currentM += 30;
            if (currentM >= 60) {
                currentM = 0;
                currentH += 1;
            }
        }
    });

    return intervals;
});

const selectTime = (slot) => {
    form.IdDisponibilite = slot.id;
    form.HeureChoisie = slot.time;
};

const onDateSelect = (date) => {
    if (!isDateAvailable(date)) {
        selectedDate.value = null;
        form.IdDisponibilite = null;
        form.HeureChoisie = null;
    } else {
        form.IdDisponibilite = null;
        form.HeureChoisie = null;
    }
};

const submitReservation = () => {
    form.post('/coaching/reserve', {
        onSuccess: () => {
            emit('success');
            emit('close');
            form.reset();
            selectedDate.value = null;
        }
    });
};
</script>

<style scoped>
.reservation-modal-body {
    padding: 10px 0;
}

.coaching-summary {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: 30px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 30px;
    gap: 30px;
}

.type-name {
    font-size: 1.8rem;
    font-weight: 800;
    margin: 0 0 10px 0;
    color: #fff;
}

.description {
    color: #aaa;
    line-height: 1.4;
    font-size: 1rem;
}

.price-badge {
    background: rgb(238 238 238 / 16%);
    padding: 15px 25px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    min-width: 150px;
}

.price-badge .label {
    font-size: 0.6rem;
    color: #ffffff;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.price-badge .amount {
    font-size: 1.4rem;
    font-weight: 500;
    color: #fff;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 15px;
    color: #fff;
}

.slots-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 30px;
    max-height: 250px;
    overflow-y: auto;
    padding-right: 5px;
}

.slot-card {
    background: #1a1a1a;
    border: 1px solid #333;
    padding: 15px;
    border-radius: 12px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}

.slot-card:hover {
    border-color: #8A38F5;
    background: rgba(138, 56, 245, 0.05);
}

.slot-card.active {
    border-color: #8A38F5;
    background: rgba(138, 56, 245, 0.15);
    box-shadow: 0 0 15px rgba(138, 56, 245, 0.2);
}

.slot-card i {
    font-size: 1.2rem;
    margin-bottom: 5px;
    color: #8A38F5;
}

.slot-date {
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: capitalize;
}

.slot-time {
    font-size: 1rem;
    font-weight: 600;
    color: #ffffff;
}

.no-slots {
    padding: 40px;
    background: #1a1a1a;
    border-radius: 12px;
    text-align: center;
    color: #666;
}

.note-section textarea {
    width: 100%;
    background: #1a1a1a;
    border: 1px solid #333;
    border-radius: 12px;
    padding: 15px;
    color: #fff;
    font-family: inherit;
    resize: none;
    transition: border-color 0.2s;
}

.note-section textarea:focus {
    outline: none;
    border-color: #8A38F5;
}

.modal-footer {
    margin-top: 40px;
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 20px;
}

.cancel-btn {
    background: none;
    border: none;
    color: #888;
    cursor: pointer;
    font-weight: 600;
}

.cancel-btn:hover {
    color: #fff;
}

.confirm-btn {
    padding: 12px 30px !important;
}

@media (max-width: 768px) {
    .slots-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .coaching-summary {
        flex-direction: column;
    }
}

.date-picker-wrapper {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
    width: 100%;
}

.custom-datepicker {
    width: 100% !important;
}

:deep(.p-datepicker) {
    background: #000;
    border: 1px solid #333;
    color: white;
    width: 100% !important;
}

:deep(.p-datepicker-header) {
    background: #000;
    color: white;
    font-size: 1.2rem !important;
    font-weight: 600 !important;
    border-bottom: 1px solid #333;
}

:deep(.p-datepicker-title),
:deep(.p-datepicker-calendar th),
:deep(.p-datepicker-calendar td) {
    color: white;
    font-size: 1.2rem !important;
    font-weight: 600 !important;
}

:deep(.p-datepicker-calendar td.p-datepicker-today > span) {
    background: rgba(255, 255, 255, 0.1);
    font-size: 1.2rem !important;
    font-weight: 600 !important;
}

:deep(.p-datepicker-calendar td > span:hover) {
    background: rgba(255, 255, 255, 0.3) !important;
    font-size: 1.2rem !important;
    font-weight: 600 !important;
}

:deep(.p-datepicker-calendar td.p-datepicker-selected > span) {
    background: #8A38F5 !important;
    color: white !important;
}

.time-slots-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}

.time-card {
    background: #1a1a1a;
    border: 1px solid #333;
    padding: 10px;
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.time-card:hover {
    border-color: #8A38F5;
}

.time-card.active {
    background: #8A38F5;
    border-color: #8A38F5;
    color: white;
}

.fade-in {
    animation: fadeIn 0.4s ease;
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

.mt-4 {
    margin-top: 20px;
}

.datepicker-day-cell {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.availability-dot {
    position: absolute;
    bottom: 2px;
    width: 4px;
    height: 4px;
    background: #8A38F5;
    border-radius: 50%;
}

.has-availability {
    color: #8A38F5 !important;
    font-weight: 700;
}
</style>
