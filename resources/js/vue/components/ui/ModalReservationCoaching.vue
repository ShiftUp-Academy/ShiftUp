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

                    <div class="custom-calendar">
                        <!-- Calendar Header -->
                        <div class="calendar-header">
                            <button type="button" class="nav-btn" @click="changeMonth(-1)">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <h3 class="current-month">{{ currentMonthName }}</h3>
                            <button type="button" class="nav-btn" @click="changeMonth(1)">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>

                        <div class="calendar-grid">
                            <div class="weekday" v-for="day in ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']"
                                :key="day">
                                {{ day }}
                            </div>

                            <div v-for="empty in emptyCells" :key="'empty-' + empty" class="day-cell empty"></div>
                            <div v-for="day in daysInMonth" :key="day.dateStr" class="day-cell" :class="{
                                'available': isDateAvailable(day.dateStr),
                                'selected': selectedDateStr === day.dateStr,
                                'today': day.isToday,
                                'disabled': day.isPast
                            }" @click="handleDateSelect(day)">
                                <span class="day-number">{{ day.dayNumber }}</span>
                                <div v-if="isDateAvailable(day.dateStr)" class="availability-indicator"></div>
                            </div>
                        </div>
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
                    <h4 class="section-title mt-4">Note ou message (Optionnel)</h4>
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
import { ref, computed, watch, onMounted } from 'vue';

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

const selectedDate = ref(null);
const selectedDateStr = ref(null);
const currentViewDate = ref(new Date());

// Calendar Logic
const currentMonthName = computed(() => {
    return currentViewDate.value.toLocaleString('fr-FR', { month: 'long', year: 'numeric' });
});

const daysInMonth = computed(() => {
    const year = currentViewDate.value.getFullYear();
    const month = currentViewDate.value.getMonth();
    const lastDay = new Date(year, month + 1, 0).getDate();

    const days = [];
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const _ = availableDates.value;

    for (let i = 1; i <= lastDay; i++) {
        const date = new Date(year, month, i);
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
        days.push({
            dayNumber: i,
            dateStr: dateStr,
            dateObj: date,
            isToday: date.getTime() === today.getTime(),
            isPast: date < today
        });
    }

    return days;
});

const emptyCells = computed(() => {
    const year = currentViewDate.value.getFullYear();
    const month = currentViewDate.value.getMonth();
    const firstDay = new Date(year, month, 1).getDay();
    return firstDay === 0 ? 6 : firstDay - 1;
});

function changeMonth(delta) {
    const newDate = new Date(currentViewDate.value);
    newDate.setMonth(newDate.getMonth() + delta);
    currentViewDate.value = newDate;
}

function handleDateSelect(day) {
    if (day.isPast || !isDateAvailable(day.dateStr)) return;

    selectedDate.value = day.dateObj;
    selectedDateStr.value = day.dateStr;
    form.IdDisponibilite = null;
    form.HeureChoisie = null;
}

watch(() => props.coaching, (newVal) => {
    if (newVal) {
        form.IdTypeCoaching = newVal.IdTypeCoaching;
        form.IdDisponibilite = null;
        form.HeureChoisie = null;
        selectedDate.value = null;
        selectedDateStr.value = null;
    }
});

const availableDates = computed(() => {
    if (!props.availabilities || props.availabilities.length === 0) {
        console.log('⚠️ Aucune disponibilité trouvée');
        return new Set();
    }

    console.log('📅 Disponibilités reçues:', props.availabilities);
    const dates = new Set(props.availabilities.map(a => a.DateDisponible));
    console.log('📅 Dates extraites:', Array.from(dates));
    return dates;
});

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'MGA', minimumFractionDigits: 0 }).format(price);
};

const isDateAvailable = (dateStr) => {
    const available = availableDates.value.has(dateStr);
    if (available) {
        console.log(`✅ Date ${dateStr} disponible`);
    }
    return available;
};

onMounted(() => {
    console.log('🔍 Modal montée - Props coaching:', props.coaching);
    console.log('🔍 Nombre de disponibilités:', props.availabilities?.length || 0);
});

const selectableIntervals = computed(() => {
    if (!selectedDate.value) return [];

    const relevantSlots = props.availabilities
        .filter(slot => slot.DateDisponible === selectedDateStr.value)
        .sort((a, b) => a.HeureDebut.localeCompare(b.HeureDebut));

    const intervals = [];
    const now = new Date();
    const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    const isToday = selectedDateStr.value === todayStr;

    relevantSlots.forEach(slot => {
        const [startH, startM] = slot.HeureDebut.split(':').map(Number);
        const [endH, endM] = slot.HeureFin.split(':').map(Number);

        let currentH = startH;
        let currentM = startM;

        while (currentH < endH || (currentH === endH && currentM < endM)) {
            const isPast = isToday && (currentH < now.getHours() || (currentH === now.getHours() && currentM <= now.getMinutes() + 15));

            if (!isPast) {
                const timeStr = `${String(currentH).padStart(2, '0')}:${String(currentM).padStart(2, '0')}`;

                if (!intervals.some(i => i.time === timeStr)) {
                    intervals.push({
                        time: timeStr,
                        id: slot.IdDisponibilite
                    });
                }
            }

            currentM += 30;
            if (currentM >= 60) {
                currentM = 0;
                currentH += 1;
            }
        }
    });

    return intervals.sort((a, b) => a.time.localeCompare(b.time));
});

const selectTime = (slot) => {
    form.IdDisponibilite = slot.id;
    form.HeureChoisie = slot.time;
};

const submitReservation = () => {
    form.post('/coaching/reserve', {
        onSuccess: () => {
            emit('success');
            emit('close');
            form.reset();
            selectedDate.value = null;
            selectedDateStr.value = null;
        }
    });
};
</script>

<style scoped>
.reservation-modal-body {
    padding: 10px 0;
    background-color: #0b0b0b;
    color: #ffffff;
}

.coaching-summary {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 25px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.05);
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
    color: rgba(255, 255, 255, 0.6);
    line-height: 1.4;
    font-size: 1rem;
}

.price-badge {
    background: rgba(138, 56, 245, 0.15);
    padding: 15px 25px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    min-width: 150px;
    border-radius: 12px;
    border: 1px solid rgba(138, 56, 245, 0.2);
}

.price-badge .label {
    font-size: 0.65rem;
    color: rgba(255, 255, 255, 0.5);
    text-transform: uppercase;
    letter-spacing: 1.5px;
}

.price-badge .amount {
    font-size: 1.4rem;
    font-weight: 600;
    color: #fff;
}

.section-title {
    font-size: 1rem;
    font-weight: 500;
    margin-bottom: 20px;
    color: rgba(255, 255, 255, 0.9);
    text-transform: uppercase;
    letter-spacing: 2px;
}

/* --- Global Custom Calendar Design --- */
.custom-calendar {
    background: #080808;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 24px;
    padding: 25px;
    width: 100%;
    user-select: none;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.current-month {
    font-size: 1.1rem;
    font-weight: 600;
    text-transform: capitalize;
    letter-spacing: 1px;
}

.nav-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.nav-btn:hover {
    background: #8A38F5;
    border-color: #8A38F5;
    transform: scale(1.1);
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
}

.weekday {
    text-align: center;
    font-size: 0.75rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.3);
    text-transform: uppercase;
    letter-spacing: 1px;
    padding-bottom: 10px;
}

.day-cell {
    aspect-ratio: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 400;
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    color: rgba(255, 255, 255, 0.8);
    background: transparent;
}

.day-cell:hover:not(.disabled):not(.empty) {
    background: rgba(138, 56, 245, 0.1);
    color: #8A38F5;
    transform: scale(1.05);
}

.day-cell.available {
    color: #8A38F5;
    font-weight: 700;
    background: rgba(138, 56, 245, 0.05);
}

.day-cell.selected {
    background: #8A38F5 !important;
    color: white !important;
    box-shadow: 0 10px 20px rgba(138, 56, 245, 0.3);
}

.day-cell.today {
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.2);
}

.day-cell.disabled {
    opacity: 0.1;
    cursor: not-allowed;
}

.day-cell.empty {
    cursor: default;
}

.availability-indicator {
    position: absolute;
    bottom: 6px;
    width: 4px;
    height: 4px;
    background: currentColor;
    border-radius: 50%;
}

/* --- Time Selection Styles --- */
.time-slots-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.time-card {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.08);
    padding: 16px;
    border-radius: 16px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.time-card:hover {
    background: rgba(138, 56, 245, 0.08);
    border-color: #8A38F5;
    transform: translateY(-3px);
}

.time-card.active {
    background: #8A38F5;
    border-color: #8A38F5;
    color: white;
    box-shadow: 0 10px 20px rgba(138, 56, 245, 0.2);
}

.time-card i {
    font-size: 0.8rem;
    opacity: 0.6;
}

.time-card.active i {
    opacity: 1;
}

.slot-time {
    font-size: 1rem;
    font-weight: 600;
}

/* --- Note Section --- */
.note-section textarea {
    width: 100%;
    background: #0f0f0f;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    padding: 18px;
    color: #ffffff;
    font-family: inherit;
    resize: none;
    transition: all 0.3s ease;
}

.note-section textarea:focus {
    outline: none;
    border-color: #8A38F5;
    background: #151515;
}

.modal-footer {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 25px;
}

.cancel-btn {
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    font-weight: 600;
    transition: color 0.3s;
}

.cancel-btn:hover {
    color: #fff;
}

.confirm-btn {
    padding: 14px 40px !important;
}

.fade-in {
    animation: fadeIn 0.5s ease;
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
    margin-top: 35px;
}

.no-slots-small {
    padding: 40px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px dashed rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    text-align: center;
    color: #444;
}

@media (max-width: 768px) {
    .time-slots-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .description {
        color: rgba(255, 255, 255, 0.914);
        line-height: 1.1;
        font-size: 1.2rem;
    }

    .type-name {
        line-height: 1 !important;
    }

    .section-title {
        margin-left: 10vw;
    }

    .price-badge {
        min-width: 100%;
    }

    .coaching-summary {
        flex-direction: column;
    }

    .cancel-btn {
        display: none;
    }
}
</style>
