<template>
  <div class="admin-content">
    <div class="page-header">
      <h1 class="page-title">Réussites</h1>
      <PremiumButton @click="openCreateModal" width="220">
        <i class="fas fa-plus-circle ml-2"></i> Nouvelle réussite
      </PremiumButton>
    </div>

    <div class="cards-container">
      <div class="reussite-card-admin" v-for="reussite in reussites" :key="reussite.id">
        <div class="admin-card-content">
          <div class="video-circle-wrapper">
            <video v-if="reussite.video_link" :src="reussite.video_link" autoplay loop muted playsinline
              class="badge-video-circle"></video>
            <div v-else class="video-placeholder-mini"><i class="fas fa-trophy"></i></div>
            <div class="glow-ring" :class="{ 'inactive': !reussite.est_actif }"></div>
            <div class="status-indicator" :class="{ 'active': reussite.est_actif }"></div>
          </div>

          <div class="admin-card-details">
            <div class="admin-title-row">
              <div class="admin-title-column">
                <h3 class="admin-card-title">{{ reussite.nom }}</h3>
                <div v-if="reussite.context_title" class="context-subtitle">{{ reussite.context_title }}</div>
              </div>
              <div class="admin-actions">
                <button class="icon-btn edit" @click="openEditModal(reussite)" title="Modifier"><i
                    class="fas fa-edit"></i></button>
                <button class="icon-btn delete" @click="confirmDelete(reussite)" title="Supprimer"><i
                    class="fas fa-trash"></i></button>
              </div>
            </div>

            <p class="admin-card-desc">{{ reussite.description || 'Aucune description' }}</p>

            <div class="admin-card-meta">
              <span class="meta-badge type">{{ typeLabels[reussite.type_action] || reussite.type_action }}</span>
              <span class="meta-badge points" v-if="reussite.points_recompense">+{{ reussite.points_recompense }}
                pts</span>
              <span class="meta-badge seuil" v-if="reussite.seuil_points">Seuil: {{ reussite.seuil_points }}</span>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!reussites.length" class="empty-state">
        <i class="fas fa-medal empty-icon"></i>
        <p>Aucune réussite créée. Commencez par en ajouter une !</p>
      </div>
    </div>

    <PremiumModal :isOpen="isModalOpen" :header="editingId ? 'Modifier la réussite' : 'Créer une réussite'"
      width="45rem" @close="closeModal">
      <form @submit.prevent="submitForm" class="reussite-form">
        <div class="form-row">
          <div class="form-group">
            <label>Nom du badge</label>
            <InputText v-model="form.nom" placeholder="Ex: Grand Maître" required />
          </div>
          <div class="form-group">
            <label>Type d'action</label>
            <Dropdown v-model="form.type_action" :options="typeOptions" optionLabel="label" optionValue="value"
              placeholder="Choisir un type" />
          </div>
        </div>

        <div class="form-group">
          <label>Description (optionnel)</label>
          <Textarea v-model="form.description" rows="2" placeholder="Explication pour l'utilisateur..." />
        </div>

        <div class="form-group">
          <label>URL de la mini-vidéo ImageKit ou Cloudinary</label>
          <InputText v-model="form.video_link" placeholder="https://ik.imagekit.io/.../badge.mp4" required />
          <div class="video-preview" v-if="form.video_link">
            <video :src="form.video_link" autoplay loop muted playsinline class="preview-video"></video>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group" v-if="form.type_action === 'seuil_points'">
            <label>Points requis (seuil)</label>
            <InputNumber v-model="form.seuil_points" />
          </div>
          <div class="form-group">
            <label>Points bonus offerts</label>
            <InputNumber v-model="form.points_recompense" placeholder="0" />
          </div>
          <div class="form-group premium-toggle-group">
            <label>Badge actif ?</label>
            <PremiumSlideToggle v-model="form.est_actif" :checked-value="true" :unchecked-value="false"
              checked-label="Badge Actif" unchecked-label="Badge Inactif" />
          </div>
        </div>

        <div class="modal-footer">
          <Button label="Annuler" text severity="secondary" @click="closeModal" />
          <PremiumButton type="submit" width="160">
            Enregistrer <i class="fas fa-save ml-2"></i>
          </PremiumButton>
        </div>
      </form>
    </PremiumModal>

    <PremiumModal :isOpen="isDeleteModalOpen" header="Confirmer la suppression" width="30rem"
      @close="isDeleteModalOpen = false">
      <p style="color:#555;line-height:1.6;">Voulez-vous vraiment supprimer la réussite <strong>{{
        reussiteToDelete?.nom
      }}</strong> ?</p>
      <div class="modal-footer">
        <Button label="Annuler" text severity="secondary" @click="isDeleteModalOpen = false" />
        <button class="delete-btn" @click="executeDelete">Supprimer</button>
      </div>
    </PremiumModal>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import PremiumModal from '../../components/ui/PremiumModal.vue';
import PremiumButton from '../../components/ui/PremiumButton.vue';
import PremiumSlideToggle from '../../components/ui/PremiumSlideToggle.vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import InputSwitch from 'primevue/inputswitch';
import Button from 'primevue/button';

const props = defineProps({
  reussites: { type: Array, default: () => [] }
});

const isModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const editingId = ref(null);
const reussiteToDelete = ref(null);

const typeOptions = [
  { label: 'Leçon terminée', value: 'lecon_terminee' },
  { label: 'Chapitre terminé', value: 'chapitre_fini' },
  { label: 'Étape passée', value: 'etape_passee' },
  { label: 'Seuil de points', value: 'seuil_points' },
  { label: 'Réservation événement', value: 'reservation_evenement' },
  { label: 'Témoignage laissé', value: 'temoignage_laisse' },
  { label: 'Offre achetée', value: 'offre_achetee' },
  { label: 'Autre', value: 'autre' },
];

const typeLabels = typeOptions.reduce((acc, curr) => { acc[curr.value] = curr.label; return acc; }, {});

const emptyForm = () => ({
  nom: '', description: '', video_link: '', type_action: 'seuil_points',
  seuil_points: null, points_recompense: 0, est_actif: true, valeur_requise: ''
});

const form = ref(emptyForm());

const openCreateModal = () => {
  editingId.value = null;
  form.value = emptyForm();
  isModalOpen.value = true;
};

const openEditModal = (r) => {
  editingId.value = r.id;
  form.value = { ...r, valeur_requise: r.valeur_requise ? JSON.stringify(r.valeur_requise) : '' };
  isModalOpen.value = true;
};

const closeModal = () => { isModalOpen.value = false; };

const submitForm = () => {
  let valReq = null;
  if (form.value.valeur_requise) {
    try { valReq = JSON.parse(form.value.valeur_requise); } catch (e) { }
  }
  const payload = { ...form.value, valeur_requise: valReq };
  const url = editingId.value ? `/admin/reussites/${editingId.value}/update` : '/admin/reussites';
  router.post(url, payload, { onSuccess: () => closeModal() });
};

const confirmDelete = (r) => { reussiteToDelete.value = r; isDeleteModalOpen.value = true; };

const executeDelete = () => {
  if (reussiteToDelete.value) {
    router.delete(`/admin/reussites/${reussiteToDelete.value.id}`, {
      onSuccess: () => { isDeleteModalOpen.value = false; }
    });
  }
};
</script>

<style scoped>
.admin-content {
  padding: 40px;
  min-height: 100vh;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 40px;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0;
  color: #1a1a1a;
}

.cards-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(480px, 1fr));
  gap: 24px;
}

.reussite-card-admin {
  background: #0a0a0a;
  border-radius: 30px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 24px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

.admin-card-content {
  display: flex;
  align-items: center;
  gap: 24px;
}

.video-circle-wrapper {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  position: relative;
  flex-shrink: 0;
  background: #000;
}

.badge-video-circle {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  z-index: 2;
  position: relative;
  border: 2px solid rgba(255, 255, 255, 0.1);
}

.glow-ring {
  position: absolute;
  inset: -2px;
  border-radius: 50%;
  background: linear-gradient(135deg, #F7B455, #00D2FF);
  z-index: 1;
  opacity: 0.5;
  filter: blur(8px);
}

.glow-ring.inactive {
  background: #444;
  opacity: 0.2;
}

.status-indicator {
  position: absolute;
  bottom: 5px;
  right: 5px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: #ef4444;
  border: 2px solid #000;
  z-index: 3;
}

.status-indicator.active {
  background: #10b981;
}

.admin-card-details {
  flex: 1;
}

.admin-title-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 6px;
}

.admin-title-column {
  display: flex;
  flex-direction: column;
}

.admin-card-title {
  margin: 0;
  font-size: 1.4rem;
  font-weight: 800;
  color: #fff;
  line-height: 1.1;
  letter-spacing: -0.5px;
}

.context-subtitle {
  font-size: 1.05rem;
  color: #8A38F5;
  font-weight: 600;
  margin-top: 4px;
}

.admin-actions {
  display: flex;
  gap: 8px;
}

.icon-btn {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: none;
  background: rgba(255, 255, 255, 0.1);
  cursor: pointer;
  color: #fff;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-btn:hover {
  background: #fff;
  color: #000;
}

.icon-btn.delete:hover {
  background: #ef4444;
  color: #fff;
}

.admin-card-desc {
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.5);
  margin: 0 0 12px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.admin-card-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.meta-badge {
  font-size: 0.75rem;
  padding: 4px 12px;
  border-radius: 20px;
  font-weight: 600;
}

.meta-badge.type {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}

.meta-badge.points {
  background: rgba(247, 180, 85, 0.2);
  color: #F7B455;
}

.meta-badge.seuil {
  background: rgba(0, 210, 255, 0.2);
  color: #00D2FF;
}

.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 100px;
  color: #444;
}

.empty-icon {
  margin-bottom: 20px;
  font-size: 4rem;
}

/* Form styles */
.reussite-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
  color: #333;
}

.form-row {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.form-group label {
  font-weight: 600;
  font-size: 0.9rem;
}

.checkbox-group {
  flex-direction: row;
  align-items: center;
}

.video-preview {
  margin-top: 10px;
  border-radius: 12px;
  overflow: hidden;
  height: 140px;
  background: #000;
}

.preview-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.save-btn {
  background: #111;
  color: white;
  border: none;
  padding: 12px 30px;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
}

.save-btn:hover {
  background: #F7B455;
}

@media (max-width: 600px) {
  .cards-container {
    grid-template-columns: 1fr;
  }

  .admin-card-content {
    flex-direction: column;
    text-align: center;
  }
}

:deep(.p-inputtext),
:deep(.p-dropdown),
:deep(.p-inputnumber-input) {
  width: 100%;
  border-radius: 8px !important;
}
</style>
