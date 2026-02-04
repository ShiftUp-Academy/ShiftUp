<template>
  <div class="admin-content">
    <div class="page-header">
      <h1 class="page-title">Lives</h1>
      <PremiumButton @click="openCreateModal">
        <i class="fas fa-plus-circle"></i> Programmer un live
      </PremiumButton>
    </div>

    <div class="main-toggles">
      <StatusToggle leftLabel="À venir" rightLabel="Archive et replays" v-model="activeTab" />
    </div>

    <div class="contentArea">
      <transition mode="out-in" @before-enter="beforeEnter" @enter="enter">
        <div :key="activeTab" class="tab-content">
          <div class="categories-list-container">
            <div class="categories-table">
              <div class="table-header-row">
                <div class="col col-title">Titre</div>
                <div class="col col-date">Date</div>
                <div class="col col-time">Heure (Début - Fin)</div>
                <div class="col col-cat">Catégorie</div>
                <div class="col col-status">Statut</div>
                <div class="col col-actions"></div>
              </div>

              <div v-for="live in filteredLives" :key="live.IdLive" class="table-row">
                <div class="col col-title font-bold text-black">{{ live.Titre }}</div>
                <div class="col col-date text-gray-600">{{ formatLocalDate(live.DateDebut) }}</div>
                <div class="col col-time font-bold">
                  {{ formatLocalTime(live.DateDebut) }} - {{ formatLocalTime(live.DateFin) }}
                </div>
                <div class="col col-cat font-bold">
                  {{ live.categorie?.Nom || 'Non catégorisé' }}
                </div>
                <div class="col col-status">
                  <PremiumSlideToggle v-model="live.Statut" checkedValue="Publié" uncheckedValue="Dépublié"
                    activeColor="#22c55e" @update:modelValue="toggleStatus(live)" />
                </div>
                <div class="col col-actions">
                  <button class="edit-btn" @click="openEditModal(live)">
                    <i class="fas fa-pen"></i> Modifier
                  </button>
                </div>
              </div>

              <div v-if="filteredLives.length === 0" class="empty-state">
                <i class="fas fa-video-slash"></i>
                <p>Aucun live trouvé pour cette section.</p>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>

    <!-- CREATE/EDIT MODAL -->
    <PremiumModal :isOpen="showModal" :header="editingLive ? 'Modifier le live' : 'Programmer un nouveau live'"
      width="45rem" @close="closeModal">
      <form @submit.prevent="submitForm" class="admin-form">
        <div class="form-grid">
          <div class="field-group full-width">
            <label>Titre du live</label>
            <InputText v-model="form.Titre" :class="{ 'p-invalid': form.errors.Titre }" placeholder="Entrez le titre..."
              required />
            <small class="p-error" v-if="form.errors.Titre">{{ form.errors.Titre }}</small>
          </div>

          <div class="field-group">
            <label>Catégorie</label>
            <Dropdown v-model="form.IdCategorie" :options="categories" optionLabel="Nom" optionValue="IdCategorie"
              placeholder="Sélectionner une catégorie" class="w-full"
              :class="{ 'p-invalid': form.errors.IdCategorie }" />
            <small class="p-error" v-if="form.errors.IdCategorie">{{ form.errors.IdCategorie }}</small>
          </div>

          <div class="field-group">
            <label>Lien Google Meet</label>
            <InputText v-model="form.LienGoogleMeet" :class="{ 'p-invalid': form.errors.LienGoogleMeet }"
              placeholder="https://meet.google.com/..." />
            <small class="p-error" v-if="form.errors.LienGoogleMeet">{{ form.errors.LienGoogleMeet }}</small>
          </div>

          <div class="field-group">
            <label>Date & Heure de début</label>
            <div class="date-picker-custom">
              <Calendar v-model="form.DateDebut" showTime hourFormat="24" class="w-full"
                :class="{ 'p-invalid': form.errors.DateDebut }" />
            </div>
            <small class="p-error" v-if="form.errors.DateDebut">{{ form.errors.DateDebut }}</small>
          </div>

          <div class="field-group">
            <label>Date & Heure de fin</label>
            <div class="date-picker-custom">
              <Calendar v-model="form.DateFin" showTime hourFormat="24" class="w-full"
                :class="{ 'p-invalid': form.errors.DateFin }" />
            </div>
            <small class="p-error" v-if="form.errors.DateFin">{{ form.errors.DateFin }}</small>
          </div>

          <div class="field-group full-width">
            <label>Description</label>
            <Textarea v-model="form.Descriptions" rows="4" placeholder="Description du live..."
              :class="{ 'p-invalid': form.errors.Descriptions }" />
            <small class="p-error" v-if="form.errors.Descriptions">{{ form.errors.Descriptions }}</small>
          </div>

          <div class="field-group full-width">
            <label>Photo de couverture</label>
            <InputText v-model="form.LienPhoto" :class="{ 'p-invalid': form.errors.LienPhoto }"
              placeholder="mettez ici le lien de photo format 2854x1324" />
            <small class="p-error" v-if="form.errors.LienPhoto">{{ form.errors.LienPhoto }}</small>
          </div>

          <div class="field-group full-width" v-if="activeTab === 'right'">
            <label>Lien du Replay</label>
            <InputText v-model="form.LienReplay" :class="{ 'p-invalid': form.errors.LienReplay }"
              placeholder="Lien vers la vidéo replay..." />
            <small class="p-error" v-if="form.errors.LienReplay">{{ form.errors.LienReplay }}</small>
          </div>

          <div class="field-group" v-if="editingLive">
            <label>Statut de publication</label>
            <PremiumSlideToggle v-model="form.Statut" checkedValue="Publié" uncheckedValue="Dépublié"
              activeColor="#22c55e" />
          </div>
        </div>

        <div class="modal-footer">
          <PremiumButton type="submit" :loading="form.processing">
            {{ editingLive ? 'Mettre à jour' : 'Programmer le live' }}
          </PremiumButton>
        </div>
      </form>
    </PremiumModal>

    <Toast />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import StatusToggle from '../../components/ui/StatusToggle.vue';
import PremiumButton from '../../components/ui/PremiumButton.vue';
import PremiumModal from '../../components/ui/PremiumModal.vue';
import PremiumSlideToggle from '../../components/ui/PremiumSlideToggle.vue';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
  lives: Array,
  categories: Array
});

const toast = useToast();
const activeTab = ref('left'); // left = À venir, right = Archive
const showModal = ref(false);
const editingLive = ref(null);

const form = useForm({
  Titre: '',
  IdCategorie: null,
  Descriptions: '',
  DateDebut: null,
  DateFin: null,
  LienGoogleMeet: '',
  LienPhoto: '',
  LienReplay: '',
  Statut: 'Publié'
});

const filteredLives = computed(() => {
  const now = new Date();
  return props.lives.filter(live => {
    const dateFin = new Date(live.DateFin);
    if (activeTab.value === 'left') {
      return dateFin >= now;
    } else {
      return dateFin < now;
    }
  });
});

const openCreateModal = () => {
  editingLive.value = null;
  form.reset();
  showModal.value = true;
};

const openEditModal = (live) => {
  editingLive.value = live;
  form.Titre = live.Titre;
  form.IdCategorie = live.IdCategorie;
  form.Descriptions = live.Descriptions || '';
  form.DateDebut = new Date(live.DateDebut);
  form.DateFin = new Date(live.DateFin);
  form.LienGoogleMeet = live.LienGoogleMeet || '';
  form.LienPhoto = live.LienPhoto || '';
  form.LienReplay = live.LienReplay || '';
  form.Statut = live.Statut;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  form.reset();
  editingLive.value = null;
};

const submitForm = () => {
  if (editingLive.value) {
    form.post('/admin/lives/' + editingLive.value.IdLive, {
      onSuccess: () => {
        closeModal();
        toast.add({ severity: 'success', summary: 'Mis à jour', detail: 'Le live a été mis à jour', life: 3000 });
      }
    });
  } else {
    form.post('/admin/lives', {
      onSuccess: () => {
        closeModal();
        toast.add({ severity: 'success', summary: 'Programmé', detail: 'Le live a été ajouté avec succès', life: 3000 });
      }
    });
  }
};

const toggleStatus = (live) => {
  router.post('/admin/lives/' + live.IdLive, {
    Statut: live.Statut,
    Titre: live.Titre,
    DateDebut: live.DateDebut,
    DateFin: live.DateFin,
    IdCategorie: live.IdCategorie,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({ severity: 'success', summary: 'Statut mis à jour', life: 2000 });
    }
  });
};

const formatLocalDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  });
};

const formatLocalTime = (dateStr) => {
  return new Date(dateStr).toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Animations
const beforeEnter = (el) => {
  gsap.set(el.querySelectorAll('.table-row'), { opacity: 0, y: 30 });
};

const enter = (el, done) => {
  gsap.to(el.querySelectorAll('.table-row'), {
    opacity: 1,
    y: 0,
    duration: 0.5,
    stagger: 0.1,
    ease: 'power2.out',
    onComplete: done
  });
};
</script>

<style scoped>
.admin-content {
  padding: 40px;
  background-color: #fcfcfc;
  min-height: 100vh;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.page-title {
  font-size: 3rem;
  font-weight: 600;
  margin: 0;
}

.main-toggles {
  display: flex;
  justify-content: center;
}

.categories-list-container {
  background: white;
  border-radius: 16px;
  border: 1px solid #e0e0e0;
  overflow: hidden;
  margin-top: 10px;
}

.table-header-row {
  display: flex;
  padding: 15px 25px;
  background: white;
  border-bottom: 1px solid #eee;
  font-weight: 600;
  color: #111;
  font-size: 1rem;
}

.table-row {
  display: flex;
  padding: 20px 25px;
  align-items: center;
  border-bottom: 1px solid #f0f0f0;
  transition: background 0.2s;
}

.table-row:last-child {
  border-bottom: none;
}

.table-row:hover {
  background: #f9f9f9;
}

.col {
  flex: 1;
}

.col-title {
  flex: 1.5;
  font-size: 1.1rem;
}

.col-date {
  flex: 1;
  font-size: 1rem;
}

.col-time {
  flex: 1.2;
  font-size: 1rem;
}

.col-cat {
  flex: 1.2;
  font-size: 1rem;
}

.col-status {
  flex: 0.8;
  display: flex;
  justify-content: center;
}

.col-actions {
  flex: 0.8;
  display: flex;
  justify-content: flex-end;
}

.edit-btn {
  background: #1c1c1c;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.95rem;
  font-weight: 700;
  transition: all 0.2s;
}

.edit-btn:hover {
  background: #333;
}

.empty-state {
  padding: 100px;
  text-align: center;
  color: #aaa;
}

.empty-state i {
  font-size: 4rem;
  margin-bottom: 20px;
  opacity: 0.2;
}

/* FORM STYLES */
.admin-form {
  padding: 10px 0;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 25px;
}

.full-width {
  grid-column: span 2;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.field-group label {
  font-weight: 700;
  font-size: 1rem;
  color: #111;
}

.modal-footer {
  margin-top: 30px;
  display: flex;
  justify-content: flex-end;
}

.font-bold {
  font-weight: 700;
}

.text-black {
  color: #111;
}

.text-gray-600 {
  color: #666;
}

/* Custom styles for PrimeVue Calendar/Dropdown */
:deep(.p-calendar .p-inputtext),
:deep(.p-dropdown),
:deep(.p-inputtext),
:deep(.p-textarea) {
  border-radius: 12px;
  padding: 12px;
  border: 1px solid #ddd;
}

:deep(.p-inputtext.p-invalid),
:deep(.p-dropdown.p-invalid),
:deep(.p-calendar.p-invalid .p-inputtext) {
  border-color: #ef4444;
}

.p-error {
  color: #ef4444;
  font-size: 0.85rem;
  margin-top: 4px;
  font-weight: 500;
}
</style>
