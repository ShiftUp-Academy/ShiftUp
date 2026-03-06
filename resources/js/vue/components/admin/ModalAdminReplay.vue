<template>
    <PremiumModal :isOpen="isOpen" header="Programmer le Replay" width="35rem" @close="$emit('close')">
        <div v-if="reservation" class="modal-body p-6">
            <div class="customer-card mb-6">
                <div class="user-info">
                    <h3>Session de {{ getUserName(reservation) }}</h3>
                    <p class="coaching-type">{{ reservation.type?.NomDeType }} - {{
                        formatDate(reservation.disponibilite?.DateDisponible) }}</p>
                </div>
            </div>

            <form @submit.prevent="submitReplay" class="replay-form">
                <div class="field-item mb-4">
                    <label class="section-label mb-2 block">Titre du Replay</label>
                    <div class="input-wrapper">
                        <i class="fas fa-heading input-icon"></i>
                        <input type="text" v-model="form.TitreReplay"
                            placeholder="Ex: Coaching Stratégie Digitale - Nantenaina" class="premium-input" required>
                    </div>
                </div>

                <div class="field-item mb-4">
                    <label class="section-label mb-2 block">Description / Notes</label>
                    <div class="input-wrapper">
                        <textarea v-model="form.DescriptionReplay" placeholder="Détails abordés durant la séance..."
                            class="premium-textarea" rows="4"></textarea>
                    </div>
                </div>

                <div class="field-item mb-6">
                    <label class="section-label mb-2 block">Lien Vidéo (Cloudinary, Youtube ou autre)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-play-circle input-icon"></i>
                        <input type="text" v-model="form.LienVideoReplay" placeholder="https://youtube.com/..."
                            class="premium-input" required>
                    </div>
                </div>

                <div class="field-item mb-6">
                    <label class="section-label mb-2 block">Visibilité</label>
                    <div class="toggle-container">
                        <span class="toggle-label">{{ form.StatutReplay === 'Publié' ? 'Public' : 'Privé' }}</span>
                        <PremiumSlideToggle v-model="form.StatutReplay" checkedValue="Publié" uncheckedValue="Dépublié"
                            activeColor="#22c55e" />
                    </div>
                </div>

                <div class="modal-footer flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" class="close-text-btn" @click="$emit('close')">Annuler</button>
                    <PremiumButton type="submit" :loading="form.processing">
                        {{ reservation.LienVideoReplay ? 'Mettre à jour' : 'Enregistrer' }} le Replay
                    </PremiumButton>
                </div>
            </form>
        </div>
    </PremiumModal>
</template>

<script setup>
import { watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import PremiumModal from "../ui/PremiumModal.vue";
import PremiumButton from "../ui/PremiumButton.vue";
import PremiumSlideToggle from "../ui/PremiumSlideToggle.vue";

const props = defineProps({
    isOpen: Boolean,
    reservation: Object,
});

const emit = defineEmits(["close", "success"]);

const form = useForm({
    StatutReservation: "Terminé", // On force Terminé quand on crée un replay
    LienVideoReplay: "",
    StatutReplay: "Dépublié",
    TitreReplay: "",
    DescriptionReplay: ""
});

watch(() => props.reservation, (newRes) => {
    if (newRes) {
        form.StatutReservation = "Terminé";
        form.LienVideoReplay = newRes.LienVideoReplay || "";
        form.StatutReplay = newRes.StatutReplay || "Dépublié";
        form.TitreReplay = newRes.TitreReplay || newRes.type?.NomDeType || "";
        form.DescriptionReplay = newRes.DescriptionReplay || "";
    }
}, { immediate: true });

const getUserName = (res) => {
    if (!res?.utilisateur?.profil) return "Utilisateur Inconnu";
    return `${res.utilisateur.profil.Nom} ${res.utilisateur.profil.Prenom}`;
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
};

const submitReplay = () => {
    if (!props.reservation) return;

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
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.user-info h3 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
    color: #111;
}

.coaching-type {
    margin: 5px 0 0;
    font-size: 0.95rem;
    color: #666;
}

.section-label {
    font-size: 0.85rem;
    font-weight: 700;
    color: #444;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 15px;
    color: #8A38F5;
}

.premium-input {
    width: 100%;
    padding: 12px 15px 12px 45px;
    border-radius: 12px;
    border: 2px solid #eee;
    font-size: 1rem;
    outline: none;
    transition: all 0.3s;
}

.premium-input:focus {
    border-color: #8A38F5;
}

.premium-textarea {
    width: 100%;
    padding: 12px 15px;
    border-radius: 12px;
    border: 2px solid #eee;
    font-size: 1rem;
    outline: none;
    resize: none;
    font-family: inherit;
    transition: all 0.3s;
}

.premium-textarea:focus {
    border-color: #8A38F5;
}

.toggle-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 12px;
}

.toggle-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #555;
}

.close-text-btn {
    background: none;
    border: none;
    color: #999;
    font-weight: 600;
    cursor: pointer;
}

.flex {
    display: flex;
}

.justify-end {
    justify-content: flex-end;
}

.gap-3 {
    gap: 0.75rem;
}

.pt-4 {
    padding-top: 1rem;
}

.border-t {
    border-top-width: 1px;
}

.border-gray-100 {
    border-color: #f3f4f6;
}

.mb-2 {
    margin-bottom: 0.5rem;
}

.mb-4 {
    margin-bottom: 1rem;
}

.mb-6 {
    margin-bottom: 1.5rem;
}

.block {
    display: block;
}
</style>
