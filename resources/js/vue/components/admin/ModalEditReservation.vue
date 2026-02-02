<template>
    <PremiumModal :isOpen="isOpen" header="Gestion de la réservation" width="32rem" @close="$emit('close')">
        <div v-if="reservation" class="modal-body p-6">
            <div class="customer-card mb-8">
                <div class="user-avatar">
                    {{ getUserInitials(reservation) }}
                </div>
                <div class="user-info">
                    <h3>{{ getUserName(reservation) }}</h3>
                    <p class="coaching-type">{{ reservation.type?.NomDeType }}</p>
                </div>
            </div>

            <div class="details-grid mb-8">
                <div class="detail-item">
                    <i class="far fa-calendar-alt"></i>
                    <div class="detail-content">
                        <span class="label">Date</span>
                        <span class="value">{{ formatDate(reservation.disponibilite?.DateDisponible) }}</span>
                    </div>
                </div>
                <div class="detail-item">
                    <i class="far fa-clock"></i>
                    <div class="detail-content">
                        <span class="label">Heure</span>
                        <span class="value">
                            {{ reservation.HeureDebutReservation ? reservation.HeureDebutReservation.substring(0, 5) :
                                (reservation.disponibilite?.HeureDebut?.substring(0, 5) || 'N/A') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- REPLAY SECTION -->
            <div v-if="reservation.StatutReservation === 'Terminé'" class="replay-section mb-8">
                <label class="section-label mb-4 block">Lien vidéo du Replay</label>
                <div class="input-wrapper">
                    <i class="fas fa-play-circle input-icon"></i>
                    <input type="text" v-model="form.LienVideoReplay" placeholder="https://..." class="premium-input">
                </div>
                <p class="helper-text mt-2">Ce lien sera visible pour l'utilisateur dans son espace Replays.</p>
            </div>

            <div class="actions-section">
                <label class="section-label mb-4 block">Changer le statut {{ reservation.StatutReservation === 'Terminé'
                    ? '& Sauvegarder' : '' }}</label>
                <div class="status-buttons-grid">
                    <button type="button" class="action-status-btn confirm"
                        :class="{ active: reservation.StatutReservation === 'Confirmé' }"
                        @click="updateStatus('Confirmé')" :disabled="form.processing">
                        <i class="fas fa-check-circle"></i>
                        Confirmer
                    </button>

                    <button type="button" class="action-status-btn complete"
                        :class="{ active: reservation.StatutReservation === 'Terminé' }"
                        @click="updateStatus('Terminé')" :disabled="form.processing">
                        <i class="fas fa-save"></i>
                        {{ reservation.StatutReservation === 'Terminé' ? 'Enregistrer' : 'Terminer' }}
                    </button>

                    <button type="button" class="action-status-btn pending"
                        :class="{ active: reservation.StatutReservation === 'En attente' }"
                        @click="updateStatus('En attente')" :disabled="form.processing">
                        <i class="fas fa-hourglass-half"></i>
                        En attente
                    </button>

                    <button type="button" class="action-status-btn refund"
                        :class="{ active: reservation.StatutReservation === 'Remboursé' }"
                        @click="updateStatus('Remboursé')" :disabled="form.processing">
                        <i class="fas fa-undo"></i>
                        Remboursé
                    </button>
                </div>
            </div>
        </div>
    </PremiumModal>
</template>

<script setup>
import { watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import PremiumModal from "../ui/PremiumModal.vue";

const props = defineProps({
    isOpen: Boolean,
    reservation: Object,
});

const emit = defineEmits(["close", "success"]);

const form = useForm({
    StatutReservation: "",
    LienVideoReplay: "",
    StatutReplay: "Dépublié"
});

watch(() => props.reservation, (newRes) => {
    if (newRes) {
        form.StatutReservation = newRes.StatutReservation;
        form.LienVideoReplay = newRes.LienVideoReplay || "";
        form.StatutReplay = newRes.StatutReplay || "Dépublié";
    }
}, { immediate: true });

const getUserName = (res) => {
    if (!res?.utilisateur?.profil) return "Utilisateur Inconnu";
    return `${res.utilisateur.profil.Nom} ${res.utilisateur.profil.Prenom}`;
};

const getUserInitials = (res) => {
    if (!res?.utilisateur?.profil) return "?";
    const n = res.utilisateur.profil.Nom?.charAt(0) || "";
    const p = res.utilisateur.profil.Prenom?.charAt(0) || "";
    return (n + p).toUpperCase();
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
};

const updateStatus = (newStatus) => {
    if (!props.reservation) return;

    form.StatutReservation = newStatus;
    form.post(`/admin/coachings/reservations/${props.reservation.IdReservation}/update`, {
        onSuccess: () => {
            emit("success");
            emit("close");
        },
        preserveScroll: true
    });
};
</script>

<style scoped>
.customer-card {
    display: flex;
    align-items: center;
    gap: 15px;
    background: #f8f9fa;
    margin-bottom: 2vh;
    padding: 20px;
    border-radius: 16px;
    position: relative;
    border: 1px solid #eee;
}

.user-avatar {
    width: 50px;
    height: 50px;
    background: #111;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
}

.user-info h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #111;
}

.coaching-type {
    margin: 0;
    font-size: 0.9rem;
    color: #666;
}

.status-pill {
    position: absolute;
    top: 20px;
    right: 20px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.status-pill.confirmé {
    background: #dcfce7;
    color: #166534;
}

.status-pill.enattente {
    background: #fef9c3;
    color: #854d0e;
}

.status-pill.terminé {
    background: #e0f2fe;
    color: #075985;
}

.status-pill.remboursé {
    background: #fee2e2;
    color: #991b1b;
}

.status-pill.annulé {
    background: #f3f4f6;
    color: #374151;
}

.details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    margin-bottom: 2vh;
    gap: 15px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px;
    background: white;
    border: 1px solid #eee;
    border-radius: 12px;
}

.detail-item i {
    color: #8A38F5;
    font-size: 1.2rem;
}

.detail-content {
    display: flex;
    flex-direction: column;
}

.label {
    font-size: 0.75rem;
    color: #888;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.value {
    font-size: 1rem;
    font-weight: 700;
    color: #111;
}

.section-label {
    font-size: 0.9rem;
    font-weight: 700;
    color: #444;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-buttons-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.action-status-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px;
    border-radius: 12px;
    border: 2px solid #eee;
    background: white;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s;
    color: #555;
}

.action-status-btn i {
    font-size: 1.1rem;
}

.action-status-btn:hover:not(:disabled) {
    border-color: #8A38F5;
    color: #8A38F5;
    background: #fbf8ff;
}

.action-status-btn.active {
    border-color: #8A38F5;
    background: #8A38F5;
    color: white;
    box-shadow: 0 4px 12px rgba(138, 56, 245, 0.2);
}

.action-status-btn.refund:hover:not(:disabled) {
    border-color: #ef4444;
    color: #ef4444;
    background: #fff5f5;
}

.action-status-btn.refund.active {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
}

.close-text-btn {
    background: none;
    border: none;
    color: #999;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.9rem;
    transition: color 0.2s;
}

.close-text-btn:hover {
    color: #666;
    text-decoration: underline;
}

/* PREMIUM INPUT STYLES */
.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 15px;
    color: #8A38F5;
    font-size: 1.1rem;
}

.premium-input {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border-radius: 12px;
    border: 2px solid #eee;
    font-size: 1rem;
    font-weight: 500;
    color: #111;
    transition: all 0.3s;
    outline: none;
}

.premium-input:focus {
    border-color: #8A38F5;
    box-shadow: 0 0 0 4px rgba(138, 56, 245, 0.1);
}

.helper-text {
    font-size: 0.8rem;
    color: #888;
    font-weight: 500;
}

:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
