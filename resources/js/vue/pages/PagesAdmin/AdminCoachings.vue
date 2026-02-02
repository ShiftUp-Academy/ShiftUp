<template>
  <div class="admin-content">
    <div class="page-header">
      <h1 class="page-title">Coachings</h1>
      <div class="header-actions">
        <PremiumButton @click="openAvailabilityModal" class="availability-btn">
          <i class="fas fa-calendar-alt"></i> Programmer les disponibilités
        </PremiumButton>
        <PremiumButton @click="showMeetModal = true" class="new-btn-premium">
          <i class="fas fa-video"></i> Lien Google Meet
        </PremiumButton>
        <PremiumButton @click="openCreateTypeModal" class="new-btn-premium">
          <i class="fas fa-plus-circle"></i> Nouveau type de coaching
        </PremiumButton>
      </div>
    </div>

    <TripleStatusToggle v-model="mainTab" :options="[
      { label: 'Les réservations', value: 'reservations' },
      { label: 'Type de coaching', value: 'types' },
      { label: 'Les replays', value: 'replays' }
    ]" />

    <div class="filters-container" v-if="mainTab === 'reservations' || mainTab === 'replays'">
      <AdminFilters v-model="filters" @refresh="onRefresh" />
    </div>

    <div class="tab-content">
      <transition mode="out-in">
        <div v-if="mainTab === 'reservations'" class="reservations-list-container">
          <div class="admin-table">
            <div class="table-header-row">
              <div class="col-user">Nom de l'utilisateur</div>
              <div class="col-type">Coaching</div>
              <div class="col-date">Date & Heure</div>
              <div class="col-status-res">Statut</div>
              <div class="col-actions"></div>
            </div>

            <div v-for="res in filteredReservations" :key="res.IdReservation" class="table-row">
              <div class="col-user">
                <span class="user-name font-bold">
                  {{ res.utilisateur?.profil ? (res.utilisateur.profil.Nom + ' ' + res.utilisateur.profil.Prenom) :
                    'Utilisateur Inconnu' }}
                </span>
              </div>
              <div class="col-type">{{ res.type?.NomDeType }}</div>
              <div class="col-date">
                {{ formatDate(res.disponibilite?.DateDisponible) }} à {{ res.HeureDebutReservation ?
                  res.HeureDebutReservation.substring(0, 5) : (res.disponibilite?.HeureDebut ?
                    res.disponibilite.HeureDebut.substring(0, 5) : 'N/A') }}
              </div>
              <div class="col-status-res">
                <span class="status-badge" :class="res.StatutReservation.toLowerCase()">
                  {{ res.StatutReservation }}
                </span>
              </div>
              <div class="col-actions">
                <button class="edit-btn small" @click="editReservation(res)">
                  <i class="fas fa-pen"></i>
                </button>
              </div>
            </div>

            <div v-if="filteredReservations.length === 0" class="empty-state">
              <p>Aucune réservation trouvée.</p>
            </div>
          </div>
        </div>

        <div v-else-if="mainTab === 'types'" class="coaching-list-container">
          <div class="admin-table">
            <div class="table-header-row">
              <div class="col-name">Nom</div>
              <div class="col-count">Nombre de réservation</div>
              <div class="col-status">Statut</div>
              <div class="col-actions"></div>
            </div>

            <div v-for="type in filteredCoachingTypes" :key="type.IdTypeCoaching" class="table-row">
              <div class="col-name font-bold">{{ type.NomDeType }}</div>
              <div class="col-count">
                <span class="reservation-pill">{{ type.reservations_count || 0 }}</span>
              </div>
              <div class="col-status">
                <PremiumSlideToggle v-model="type.Statut" checkedValue="Publié" uncheckedValue="Dépublié"
                  activeColor="#22c55e" @update:modelValue="updateStatus(type)" />
              </div>
              <div class="col-actions">
                <button class="edit-btn" @click="editCoachingType(type)">
                  <i class="fas fa-pen"></i> Modifier
                </button>
              </div>
            </div>

            <div v-if="filteredCoachingTypes.length === 0" class="empty-state">
              Aucun type de coaching trouvé.
            </div>
          </div>
        </div>

        <div v-else-if="mainTab === 'replays'" class="replays-list-container">
          <div class="admin-table">
            <div class="table-header-row">
              <div class="col-user">Utilisateur</div>
              <div class="col-type">Coaching</div>
              <div class="col-date">Date</div>
              <div class="col-status">Statut</div>
              <div class="col-actions"></div>
            </div>

            <div v-for="res in filteredReplays" :key="res.IdReservation" class="table-row">
              <div class="col-user">
                <span class="user-name font-bold">
                  {{ res.utilisateur?.profil ? (res.utilisateur.profil.Nom + ' ' + res.utilisateur.profil.Prenom) :
                    'Utilisateur Inconnu' }}
                </span>
              </div>
              <div class="col-type">{{ res.type?.NomDeType }}</div>
              <div class="col-date">{{ formatDate(res.disponibilite?.DateDisponible) }}</div>
              <div class="col-status">
                <PremiumSlideToggle v-model="res.StatutReplay" checkedValue="Publié" uncheckedValue="Dépublié"
                  activeColor="#22c55e" @update:modelValue="updateReplayStatus(res)" />
              </div>
              <div class="col-actions">
                <button class="edit-btn" @click="editReservation(res)">
                  <i class="fas fa-video"></i> Replay
                </button>
              </div>
            </div>

            <div v-if="filteredReplays.length === 0" class="empty-state">
              <p>Aucun coaching terminé trouvé pour le moment.</p>
            </div>
          </div>
        </div>
      </transition>
    </div>

    <!-- MODALS -->
    <ModalEditReservation :isOpen="showEditReservationModal" :reservation="editingReservation"
      @close="closeEditReservationModal" @success="onRefresh" />
    <ModalCoachingType :isOpen="showTypeModal" :typeToEdit="editingType" @close="closeTypeModal" />

    <ModalCoachingAvailability :isOpen="showAvailModal" :existingAvailabilities="availabilities"
      @close="showAvailModal = false" />

    <PremiumModal :isOpen="showMeetModal" header="Lien Google Meet Global" width="35rem" @close="showMeetModal = false">
      <form @submit.prevent="submitMeetLink" class="p-4">
        <div class="flex flex-col gap-4">
          <div class="field-group">
            <label class="font-bold mb-2 block">Lien Meet</label>
            <InputText v-model="meetForm.LienGoogleMeet" placeholder="https://meet.google.com/..." class="w-full"
              required />
          </div>
          <div class="flex justify-end gap-2 mt-4">
            <PremiumButton type="submit" :loading="meetForm.processing">Enregistrer</PremiumButton>
          </div>
        </div>
      </form>
    </PremiumModal>

    <Toast />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import PremiumButton from '../../components/ui/PremiumButton.vue';
import PremiumSlideToggle from '../../components/ui/PremiumSlideToggle.vue';
import TripleStatusToggle from '../../components/ui/TripleStatusToggle.vue';
import ModalCoachingType from '../../components/admin/ModalCoachingType.vue';
import ModalCoachingAvailability from '../../components/admin/ModalCoachingAvailability.vue';
import PremiumModal from '../../components/ui/PremiumModal.vue';
import InputText from 'primevue/inputtext';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import AdminFilters from '../../components/admin/AdminFilters.vue';
import ModalEditReservation from '../../components/admin/ModalEditReservation.vue';

const props = defineProps({
  coachingTypes: { type: Array, default: () => [] },
  reservations: { type: Array, default: () => [] },
  availabilities: { type: Array, default: () => [] },
  googleMeetLink: { type: String, default: '' },
  replays: { type: Array, default: () => [] }
});

const mainTab = ref('reservations');
const filters = ref({ search: '', dateStart: null, dateEnd: null });
const toast = useToast();

const filteredReservations = computed(() => {
  let list = props.reservations;
  if (filters.value.search) {
    const query = filters.value.search.toLowerCase();
    list = list.filter(res => {
      const userName = res.utilisateur?.profil ? `${res.utilisateur.profil.Nom || ''} ${res.utilisateur.profil.Prenom || ''}`.toLowerCase() : '';
      const typeName = res.type ? res.type.NomDeType.toLowerCase() : '';
      const status = res.StatutReservation.toLowerCase();
      return userName.includes(query) || typeName.includes(query) || status.includes(query);
    });
  }
  if (filters.value.dateStart) {
    const start = new Date(filters.value.dateStart);
    start.setHours(0, 0, 0, 0);
    list = list.filter(res => res.disponibilite && new Date(res.disponibilite.DateDisponible) >= start);
  }
  if (filters.value.dateEnd) {
    const end = new Date(filters.value.dateEnd);
    end.setHours(23, 59, 59, 999);
    list = list.filter(res => res.disponibilite && new Date(res.disponibilite.DateDisponible) <= end);
  }
  return list;
});

const filteredReplays = computed(() => {
  let list = props.reservations.filter(res => res.StatutReservation === 'Terminé');
  if (filters.value.search) {
    const query = filters.value.search.toLowerCase();
    list = list.filter(res => {
      const userName = res.utilisateur?.profil ? `${res.utilisateur.profil.Nom || ''} ${res.utilisateur.profil.Prenom || ''}`.toLowerCase() : '';
      const typeName = res.type ? res.type.NomDeType.toLowerCase() : '';
      return userName.includes(query) || typeName.includes(query);
    });
  }
  return list;
});

const filteredCoachingTypes = computed(() => {
  if (!filters.value.search) return props.coachingTypes;
  const query = filters.value.search.toLowerCase();
  return props.coachingTypes.filter(type => type.NomDeType.toLowerCase().includes(query));
});

const showTypeModal = ref(false);
const showAvailModal = ref(false);
const showMeetModal = ref(false);
const showEditReservationModal = ref(false);
const editingType = ref(null);
const editingReservation = ref(null);

const meetForm = useForm({ LienGoogleMeet: props.googleMeetLink });

const openCreateTypeModal = () => { editingType.value = null; showTypeModal.value = true; };
const openAvailabilityModal = () => { showAvailModal.value = true; };
const editCoachingType = (type) => { editingType.value = type; showTypeModal.value = true; };
const closeTypeModal = () => { showTypeModal.value = false; editingType.value = null; };
const editReservation = (res) => { editingReservation.value = res; showEditReservationModal.value = true; };
const closeEditReservationModal = () => { showEditReservationModal.value = false; editingReservation.value = null; };
const onRefresh = () => { router.reload(); };

const submitMeetLink = () => {
  meetForm.post('/admin/coachings/google-meet', {
    onSuccess: () => {
      showMeetModal.value = false;
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Lien Meet mis à jour', life: 3000 });
    }
  });
};

const updateStatus = (type) => {
  router.post(`/admin/coachings/types/${type.IdTypeCoaching}/status`, { Statut: type.Statut }, {
    preserveScroll: true,
    onSuccess: () => { toast.add({ severity: 'success', summary: 'Succès', detail: 'Le statut a été mis à jour', life: 3000 }); }
  });
};

const updateReplayStatus = (res) => {
  router.post(`/admin/coachings/reservations/${res.IdReservation}/update`, {
    StatutReservation: res.StatutReservation,
    StatutReplay: res.StatutReplay,
    LienVideoReplay: res.LienVideoReplay
  }, {
    preserveScroll: true,
    onSuccess: () => { toast.add({ severity: 'success', summary: 'Succès', detail: 'Le statut du replay a été mis à jour', life: 3000 }); }
  });
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
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
  margin-bottom: 20px;
}

.page-title {
  font-size: 3rem;
  font-weight: 600;
  margin: 0;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 20px;
}

.new-btn-premium {
  padding: 10px 25px !important;
  border-radius: 15px !important;
  font-size: 0.95rem !important;
  height: 48px;
}

.availability-btn {
  background: linear-gradient(90deg, #0E7EC3, #8A38F5);
  padding: 10px 25px;
  border-radius: 15px;
  font-size: 0.95rem;
  height: 48px;
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
}

.reservations-list-container,
.coaching-list-container,
.replays-list-container {
  background: white;
  border-radius: 16px;
  border: 1px solid #e0e0e0;
  overflow: hidden;
  margin-top: 20px;
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
  background: #fff;
}

.table-row:hover {
  background: #f9f9f9;
}

.table-row:last-child {
  border-bottom: none;
}

.col-name {
  flex: 1.5;
  font-size: 1.1rem;
  color: #111;
}

.col-user {
  flex: 2;
  color: #111;
}

.col-type {
  flex: 1.5;
}

.col-date {
  flex: 1.5;
}

.col-status-res {
  flex: 1;
  text-align: center;
}

.status-badge {
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
}

.status-badge.confirmé {
  background: #dcfce7;
  color: #166534;
}

.status-badge.en\ attente {
  background: #fef9c3;
  color: #854d0e;
}

.status-badge.annulé {
  background: #fee2e2;
  color: #991b1b;
}

.status-badge.remboursé {
  background: #fee2e2;
  color: #b91c1c;
}

.edit-btn.small {
  width: 35px;
  height: 35px;
  padding: 0;
  justify-content: center;
  border-radius: 10px;
}

.col-count {
  flex: 1;
  text-align: center;
}

.col-status {
  flex: 1;
  display: flex;
  justify-content: center;
}

.col-actions {
  flex: 1;
  display: flex;
  justify-content: center;
}

.reservation-pill {
  background: #f0f0f0;
  padding: 4px 12px;
  border-radius: 20px;
  font-weight: 700;
  color: #111;
}

.edit-btn {
  background: #1c1c1c;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  transition: all 0.2s;
}

.edit-btn:hover {
  background: #333;
}

.tab-content {
  margin-top: 40px;
}

.empty-state {
  padding: 80px;
  text-align: center;
  font-size: 1.2rem;
  color: #d0d0d0;
  background: #111;
  border-radius: 20px;
}

.filters-container {
  margin-bottom: 30px;
  display: flex;
  justify-content: center;
}
</style>
