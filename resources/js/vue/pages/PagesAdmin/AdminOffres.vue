<template>
  <div class="admin-content">
    <div class="page-header">
      <h1 class="page-title">Offres</h1>

      <div class="header-actions">
        <AdminFilters v-model="filters" @refresh="onRefresh" />
        <PremiumButton @click="openCreateModal" class="new-btn-premium">
          <i class="fas fa-plus-circle"></i> Nouveau
        </PremiumButton>
      </div>
    </div>

    <div class="main-toggles">
      <StatusToggle leftLabel="Publiés" rightLabel="Dépubliés" v-model="activeTab" />
    </div>

    <div class="contentArea">
      <transition mode="out-in" @before-enter="beforeEnter" @enter="enter">
        <div :key="activeTab" class="tab-content">
          <div v-if="filteredOffres.length > 0" class="offres-grid">
            <OffreCard v-for="offre in filteredOffres" :key="offre.IdOffre" :offre="offre" @edit="openEditModal"
              @delete="deleteOffer" @toggle-status="toggleOfferStatus($event)" />
          </div>
          <div v-else class="empty-state">
            <p>Aucune offre {{ activeTab === 'left' ? 'publiée' : 'dépubliée' }} trouvée.</p>
          </div>
        </div>
      </transition>
    </div>

    <!-- Modal Form -->
    <PremiumModal :isOpen="showModal" :header="editingOffre ? 'Modifier l\'offre' : 'Nouvelle Offre'" width="40rem"
      @close="closeModal">
      <form v-if="showModal" @submit.prevent="submitForm" class="offre-form">
        <div class="form-grid">
          <div class="field-row">
            <div class="field-group">
              <label>Statut</label>
              <PremiumSlideToggle v-model="form.Statut" checkedValue="Publié" uncheckedValue="Dépublié"
                activeColor="#22c55e" />
            </div>
            <div class="field-group">
              <label>Durée de l'offre (jours)</label>
              <InputNumber v-model="form.DureeJours" placeholder="Ex: 365" suffix=" jours" :min="0" class="w-full" />
            </div>
          </div>

          <div class="field-group full-width">
            <label>Titre de l'offre</label>
            <InputText v-model="form.Titre" placeholder="Ex: Pack Succès Illimité" required class="w-full" />
            <small class="p-error" v-if="form.errors.Titre">{{ form.errors.Titre }}</small>
          </div>

          <div class="field-group full-width">
            <label>Description</label>
            <Textarea v-model="form.Descriptions" rows="3" placeholder="Présentez les avantages de ce bundle..."
              class="w-full" />
          </div>

          <div class="field-group full-width">
            <label>Photo de couverture</label>
            <InputText v-model="form.LienPhoto" placeholder="Lien vers l'image..." class="w-full" />
          </div>

          <!-- Total & Réduction Row -->
          <div class="calc-box-row">
            <div class="total-value-banner-black">
              <span class="total-label">Valeur cumulée</span>
              <span class="total-amount">{{ formatPrice(totalDiscountedPrice) }} Ar</span>
            </div>
            <div class="glob-red-input-group">
              <label>Réduction Globale (%)</label>
              <InputNumber v-model="form.ReductionGlobal" placeholder="0" suffix=" %" :min="0" :max="100" />
            </div>
          </div>

          <!-- Selection Programmes -->
          <div class="field-group full-width selection-section">
            <label class="section-label">Programmes de formation inclus ({{ programmes.length }} disponibles)</label>

            <div class="custom-search-selector">
              <span class="search-icon-inside"><i class="fas fa-search"></i></span>
              <InputText v-model="progSearch" placeholder="Rechercher un programme..." class="w-full search-input"
                @focus="showProgResults = true" />

              <div v-if="showProgResults && filteredProgs.length > 0"
                class="search-results-box dark-dropdown scroll-premium">
                <div v-for="p in filteredProgs" :key="p.IdProgrammeFormation" class="search-result-item"
                  @mousedown.prevent="addItem('prog', p.IdProgrammeFormation)">
                  <img :src="p.LienPhoto || '/images/placeholder.jpg'" class="result-img-xl" />
                  <div class="result-content">
                    <span class="result-title">{{ p.Titre }}</span>
                    <span class="result-type-hint">{{ p.Type || 'Formation' }}</span>
                    <span class="result-price">{{ formatPrice(p.Prix) }} Ar</span>
                  </div>
                  <div class="add-indicator"><i class="fas fa-plus"></i></div>
                </div>
              </div>
              <div v-if="showProgResults" class="dropdown-overlay-closer" @click="showProgResults = false"></div>
            </div>

            <div class="selected-details-list">
              <div v-if="selectedProgsIds.length === 0" class="empty-selection-placeholder">
                <i class="fas fa-plus-circle"></i> Recherchez et sélectionnez des programmes ci-dessus
              </div>
              <div v-for="id in selectedProgsIds" :key="'det-prog-' + id" class="detail-item-xl-card-dark">
                <div class="img-container">
                  <img :src="getItemPhoto(programmes, 'IdProgrammeFormation', id)" class="selected-item-img-xl" />
                </div>
                <div class="item-main-content">
                  <div class="item-info">
                    <div class="item-type-badge">{{ getItemType(programmes, 'IdProgrammeFormation', id) }}</div>
                    <span class="item-name">{{ getItemTitle(programmes, 'IdProgrammeFormation', id) }}</span>
                    <span class="item-price">{{ formatPrice(getItemFinalPrice(programmes, 'IdProgrammeFormation', id,
                      progReductions)) }} Ar</span>
                  </div>
                  <div class="item-actions-row">
                    <div class="reduction-input-box">
                      <label>Réduction spécifique %</label>
                      <InputNumber v-model="progReductions[id]" placeholder="0" suffix=" %" :min="0" :max="100" />
                    </div>
                    <button type="button" class="remove-btn-premium-dark" @click="removeItem('prog', id)">
                      <i class="fas fa-trash-alt"></i> Retirer
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Selection Coachings -->
          <div class="field-group full-width selection-section">
            <label class="section-label">Coachings inclus ({{ coachings.length }} disponibles)</label>

            <div class="custom-search-selector">
              <span class="search-icon-inside"><i class="fas fa-search"></i></span>
              <InputText v-model="coachSearch" placeholder="Rechercher un coaching..." class="w-full search-input"
                @focus="showCoachResults = true" />

              <div v-if="showCoachResults && filteredCoaches.length > 0"
                class="search-results-box dark-dropdown scroll-premium">
                <div v-for="c in filteredCoaches" :key="c.IdTypeCoaching" class="search-result-item"
                  @mousedown.prevent="addItem('coach', c.IdTypeCoaching)">
                  <div class="result-icon-box-xl"><i class="fas fa-video"></i></div>
                  <div class="result-content">
                    <span class="result-title">{{ c.NomDeType }}</span>
                    <span class="result-price">{{ formatPrice(c.Prix) }} Ar</span>
                  </div>
                  <div class="add-indicator"><i class="fas fa-plus"></i></div>
                </div>
              </div>
              <div v-if="showCoachResults" class="dropdown-overlay-closer" @click="showCoachResults = false"></div>
            </div>

            <div class="selected-details-list">
              <div v-if="selectedCoachIds.length === 0" class="empty-selection-placeholder">
                <i class="fas fa-video"></i> Aucun coaching sélectionné
              </div>
              <div v-for="id in selectedCoachIds" :key="'det-coach-' + id" class="detail-item-xl-card-dark">
                <div class="img-container">
                  <div class="selected-item-icon-xl"><i class="fas fa-video"></i></div>
                </div>
                <div class="item-main-content">
                  <div class="item-info">
                    <div class="item-type-badge coaching">Coaching</div>
                    <span class="item-name">{{ getItemTitle(coachings, 'IdTypeCoaching', id) }}</span>
                    <span class="item-price">{{ formatPrice(getItemFinalPrice(coachings, 'IdTypeCoaching', id,
                      coachReductions)) }} Ar</span>
                  </div>
                  <div class="item-actions-row">
                    <div class="reduction-input-box">
                      <label>Réduction spécifique %</label>
                      <InputNumber v-model="coachReductions[id]" placeholder="0" suffix=" %" :min="0" :max="100" />
                    </div>
                    <button type="button" class="remove-btn-premium-dark" @click="removeItem('coach', id)">
                      <i class="fas fa-trash-alt"></i> Retirer
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <Button label="Annuler" text @click="closeModal" />
          <PremiumButton type="submit" :loading="form.processing" width="12vw">
            Enregistrer
          </PremiumButton>
        </div>
      </form>
    </PremiumModal>

    <ConfirmModal :isOpen="confirmData.isOpen" :title="confirmData.title" :message="confirmData.message"
      :type="confirmData.type" @confirm="onModalConfirm" @cancel="confirmData.isOpen = false" />

    <Toast />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import gsap from 'gsap';
import PremiumModal from '../../components/ui/PremiumModal.vue';
import PremiumButton from '../../components/ui/PremiumButton.vue';
import PremiumSlideToggle from '../../components/ui/PremiumSlideToggle.vue';
import StatusToggle from '../../components/ui/StatusToggle.vue';
import AdminFilters from '../../components/admin/AdminFilters.vue';
import OffreCard from '../../components/admin/OffreCard.vue';
import ConfirmModal from '../../components/ui/ConfirmModal.vue';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
  offres: { type: Array, default: () => [] },
  programmes: { type: Array, default: () => [] },
  coachings: { type: Array, default: () => [] }
});

const toast = useToast();
const showModal = ref(false);
const editingOffre = ref(null);
const activeTab = ref('left');
const filters = ref({ search: '', dateStart: null, dateEnd: null });

// State pour la sélection
const selectedProgsIds = ref([]);
const progReductions = ref({});
const selectedCoachIds = ref([]);
const coachReductions = ref({});

// State pour la recherche custom
const progSearch = ref('');
const coachSearch = ref('');
const showProgResults = ref(false);
const showCoachResults = ref(false);

const filteredProgs = computed(() => {
  const q = progSearch.value.toLowerCase();
  return props.programmes.filter(p =>
    (!q || p.Titre.toLowerCase().includes(q)) &&
    !selectedProgsIds.value.includes(p.IdProgrammeFormation)
  );
});

const filteredCoaches = computed(() => {
  const q = coachSearch.value.toLowerCase();
  return props.coachings.filter(c =>
    (!q || c.NomDeType.toLowerCase().includes(q)) &&
    !selectedCoachIds.value.includes(c.IdTypeCoaching)
  );
});

const addItem = (type, id) => {
  if (type === 'prog') {
    selectedProgsIds.value.push(id);
    progSearch.value = '';
    showProgResults.value = false;
  } else {
    selectedCoachIds.value.push(id);
    coachSearch.value = '';
    showCoachResults.value = false;
  }
};

const removeItem = (type, id) => {
  if (type === 'prog') {
    selectedProgsIds.value = selectedProgsIds.value.filter(i => i !== id);
    delete progReductions.value[id];
  } else {
    selectedCoachIds.value = selectedCoachIds.value.filter(i => i !== id);
    delete coachReductions.value[id];
  }
};

const totalBasePrice = computed(() => {
  let total = 0;
  selectedProgsIds.value.forEach(id => {
    const p = props.programmes.find(item => item.IdProgrammeFormation === id);
    if (p) total += Number(p.Prix || 0);
  });
  selectedCoachIds.value.forEach(id => {
    const c = props.coachings.find(item => item.IdTypeCoaching === id);
    if (c) total += Number(c.Prix || 0);
  });
  return total;
});

const totalDiscountedPrice = computed(() => {
  let total = 0;

  selectedProgsIds.value.forEach(id => {
    const p = props.programmes.find(item => item.IdProgrammeFormation === id);
    if (p) {
      const base = Number(p.Prix || 0);
      const red = Number(progReductions.value[id] || 0);
      total += base * (1 - red / 100);
    }
  });

  selectedCoachIds.value.forEach(id => {
    const c = props.coachings.find(item => item.IdTypeCoaching === id);
    if (c) {
      const base = Number(c.Prix || 0);
      const red = Number(coachReductions.value[id] || 0);
      total += base * (1 - red / 100);
    }
  });

  const globalRed = Number(form.ReductionGlobal || 0);
  return total * (1 - globalRed / 100);
});

const form = useForm({
  Titre: '',
  Descriptions: '',
  LienPhoto: '',
  ReductionGlobal: 0,
  DureeJours: 0,
  Statut: 'Dépublié',
  SelectedProgrammes: [],
  SelectedCoachings: []
});

const filteredOffres = computed(() => {
  let list = activeTab.value === 'left' ?
    props.offres.filter(o => o.Statut === 'Publié') :
    props.offres.filter(o => o.Statut === 'Dépublié');

  if (filters.value.search) {
    list = list.filter(o =>
      o.Titre.toLowerCase().includes(filters.value.search.toLowerCase())
    );
  }
  return list;
});

const formatPrice = (price) => {
  return Number(price || 0).toLocaleString('fr-FR');
};

const getItemTitle = (list, key, id) => {
  const item = list.find(it => it[key] === id);
  return item ? (item.Titre || item.NomDeType) : '';
};

const getItemFinalPrice = (list, key, id, redMap) => {
  const item = list.find(it => it[key] === id);
  if (!item) return 0;
  const base = Number(item.Prix || 0);
  const redValue = (redMap && typeof redMap === 'object') ? redMap[id] : 0;
  const red = Number(redValue || 0);
  return base * (1 - red / 100);
};

const getItemType = (list, key, id) => {
  const item = list.find(it => it[key] === id);
  if (!item) return '';
  return item.Type || 'Formation';
};

const getItemPhoto = (list, key, id) => {
  const item = list.find(it => it[key] === id);
  return item ? (item.LienPhoto || '/images/placeholder.jpg') : '/images/placeholder.jpg';
};

const openCreateModal = () => {
  editingOffre.value = null;
  form.reset();
  selectedProgsIds.value = [];
  progReductions.value = {};
  selectedCoachIds.value = [];
  coachReductions.value = {};
  progSearch.value = '';
  coachSearch.value = '';
  showModal.value = true;
};

const openEditModal = (offre) => {
  editingOffre.value = offre;
  form.Titre = offre.Titre;
  form.Descriptions = offre.Descriptions || '';
  form.LienPhoto = offre.LienPhoto || '';
  form.ReductionGlobal = offre.ReductionGlobal || 0;
  form.DureeJours = offre.DureeJours || 0;
  form.Statut = offre.Statut;

  selectedProgsIds.value = (offre.programmes || []).map(p => p.IdProgrammeFormation);
  progReductions.value = {};
  (offre.programmes || []).forEach(p => {
    if (p.ReductionSpecifique !== null) progReductions.value[p.IdProgrammeFormation] = p.ReductionSpecifique;
  });

  selectedCoachIds.value = (offre.coachings || []).map(c => c.IdTypeCoaching);
  coachReductions.value = {};
  (offre.coachings || []).forEach(c => {
    if (c.ReductionSpecifique !== null) coachReductions.value[c.IdTypeCoaching] = c.ReductionSpecifique;
  });

  progSearch.value = '';
  coachSearch.value = '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  form.reset();
};

const submitForm = () => {
  form.SelectedProgrammes = selectedProgsIds.value.map(id => ({
    id: id,
    reduction: progReductions.value[id] || null
  }));

  form.SelectedCoachings = selectedCoachIds.value.map(id => ({
    id: id,
    reduction: coachReductions.value[id] || null
  }));

  if (editingOffre.value) {
    form.post('/admin/offres/' + editingOffre.value.IdOffre + '/update', {
      onSuccess: () => {
        closeModal();
        toast.add({ severity: 'success', summary: 'Succès', detail: 'Offre mise à jour', life: 3000 });
      }
    });
  } else {
    form.post('/admin/offres', {
      onSuccess: () => {
        closeModal();
        toast.add({ severity: 'success', summary: 'Succès', detail: 'Offre créée avec succès', life: 3000 });
      }
    });
  }
};

const onRefresh = () => {
  router.reload();
};

const beforeEnter = (el) => {
  el.style.opacity = '0';
  el.style.transform = 'translateY(20px)';
};

const enter = (el, done) => {
  gsap.to(el, {
    opacity: 1,
    y: 0,
    duration: 0.4,
    ease: 'power2.out',
    onComplete: done
  });
};

const toggleOfferStatus = ({ offre, statut }) => {
  router.post('/admin/offres/' + offre.IdOffre + '/status', {
    Statut: statut
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({ severity: 'success', summary: 'Statut mis à jour', life: 2000 });
    }
  });
};

// CONFIRM MODAL STATE
const confirmData = ref({
  isOpen: false,
  title: '',
  message: '',
  type: 'danger',
  action: null
});

const triggerConfirm = (title, message, type, action) => {
  confirmData.value = { isOpen: true, title, message, type, action };
};

const onModalConfirm = () => {
  if (confirmData.value.action) confirmData.value.action();
  confirmData.value.isOpen = false;
};

const deleteOffer = (offre) => {
  triggerConfirm(
    "Supprimer l'offre",
    `Voulez-vous vraiment supprimer l'offre "${offre.Titre}" ?`,
    'danger',
    () => {
      router.delete('/admin/offres/' + offre.IdOffre, {
        onSuccess: () => {
          toast.add({ severity: 'info', summary: 'Supprimé', detail: "L'offre a été supprimée", life: 3000 });
        }
      });
    }
  );
};
</script>

<style scoped>
.admin-content {
  padding: 40px;
  background: #fdfdfd;
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
  gap: 20px;
  align-items: center;
}

.main-toggles {
  margin-bottom: 40px;
}

.contentArea {
  min-height: 400px;
}

.offres-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 30px;
}

.empty-state {
  padding: 80px;
  text-align: center;
  font-size: 1.2rem;
  color: #666;
  background: #f9f9f9;
  border-radius: 20px;
  border: 2px dashed #eee;
}

.offre-form {
  padding: 10px 0;
}

.form-grid {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.field-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.full-width {
  grid-column: span 2;
}

.field-group label {
  font-weight: 600;
  font-size: 1rem;
  color: #111;
}

.section-label {
  border-left: 4px solid #8A38F5;
  padding-left: 10px;
  margin-bottom: 5px;
  font-weight: 700;
}

.selection-section {
  background: #fafafa;
  padding: 15px;
  border-radius: 15px;
  border: 1px solid #f0f0f0;
}

.calc-box-row {
  display: flex;
  gap: 30px;
  align-items: center;
}

.total-value-banner-black {
  flex: 1;
  background: #111;
  color: white;
  padding: 15px 30px;
  padding-bottom: 10px;
  border-radius: 18px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: flex-start;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.total-value-banner-black .total-label {
  font-weight: 500;
  font-size: 0.7rem;
  opacity: 0.7;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.total-value-banner-black .total-amount {
  font-size: 1.9rem;
  font-weight: 400;
  margin-right: 1vw;
  color: #c9c9c9;
}

.glob-red-input-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 200px;
}

.glob-red-input-group label {
  font-weight: 700;
  color: #111;
  font-size: 0.9rem;
}

.selected-details-list {
  margin-top: 25px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  min-height: 15vh;
}

.empty-selection-placeholder {
  padding: 40px;
  border-radius: 20px;
  background: #f9f9f9;
  border: 2px dashed #eee;
  text-align: center;
  color: #aaa;
  font-weight: 500;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.empty-selection-placeholder i {
  font-size: 2rem;
  color: #eee;
}

.detail-item-xl-card-dark {
  display: flex;
  background: #111;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s;
}

.detail-item-xl-card-dark:hover {
  transform: scale(1.01);
}

.img-container {
  width: 220px;
  flex-shrink: 0;
}

.selected-item-img-xl {
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.9;
}

.selected-item-icon-xl {
  width: 100%;
  height: 100%;
  background: #1a1a1a;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #8A38F5;
  font-size: 3rem;
}

.item-main-content {
  flex: 1;
  padding: 25px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.item-info {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.item-type-badge {
  display: inline-block;
  align-self: flex-start;
  color: #e2e2e2;
  border-radius: 6px;
  margin-top: 2vh;
  font-size: 0.7rem;
  font-weight: 400;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.item-type-badge.coaching {
  color: #f6f6f6;
}

.item-name {
  font-weight: 500;
  line-height: 1.1;
  color: #fff;
  font-size: 1.5rem;
}

.item-price {
  font-weight: 500;
  color: #d2d2d2;
  font-size: 1.8rem;
}

.item-actions-row {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-top: 15px;
}

.reduction-input-box {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.reduction-input-box label {
  font-size: 0.7rem !important;
  color: #f2f2f2;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  font-weight: 700;
}

.reduction-input-box :deep(.p-inputnumber-input) {
  width: 110px;
  padding: 12px;
  border-radius: 12px;
  background: #1a1a1a !important;
  border: 2px solid #333 !important;
  text-align: center;
  font-weight: 800;
  color: #fff !important;
  font-size: 1.2rem;
  transition: border-color 0.3s;
}

.reduction-input-box :deep(.p-inputnumber-input:focus) {
  border-color: #8A38F5 !important;
  outline: none;
}

.remove-btn-premium-dark {
  background: rgba(255, 77, 77, 0.1);
  border: 1px solid rgba(255, 77, 77, 0.2);
  color: #ff4d4d;
  padding: 12px 24px;
  border-radius: 14px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: all 0.3s;
}

.remove-btn-premium-dark:hover {
  background: #ff4d4d;
  color: white;
  box-shadow: 0 4px 20px rgba(255, 77, 77, 0.4);
}

/* Custom Search UI - DARK DROPDOWN */
.custom-search-selector {
  position: relative;
  width: 100%;
}

.search-icon-inside {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #8A38F5;
  opacity: 0.5;
  z-index: 2;
}

.search-input {
  width: 100% !important;
  padding-left: 45px !important;
  border-radius: 15px !important;
  height: 50px;
  font-size: 1rem;
  border: 2px solid #eee !important;
}

.search-results-box.dark-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  scrollbar-width: none;
  right: 0;
  background: #111;
  border: 1px solid #333;
  border-radius: 25px;
  max-height: 28vh;
  overflow-y: auto;
  z-index: 1001;
  margin-top: 10px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.search-result-item {
  display: flex;
  align-items: center;
  padding: 10px;
  gap: 20px;
  cursor: pointer;
  transition: all 0.2s;
  border-bottom: 1px solid #222;
  position: relative;
  z-index: 1002;
  min-height: 120px;
  /* Assure que le dropdown a toujours la même allure */
}

.search-result-item:hover {
  background: #1a1a1a;
}

.result-img-xl {
  width: 160px;
  height: 100%;
  border-radius: 15px;
  object-fit: cover;
}

.result-icon-box-xl {
  width: 160px;
  height: 100%;
  min-height: 100px;
  border-radius: 10px;
  background: #222;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #8A38F5;
  font-size: 1.5rem;
}

.result-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.result-title {
  font-weight: 500;
  color: #fff;
  font-size: 1.4rem;
}

.result-type-hint {
  font-size: 0.85rem;
  color: #ffffff;
  text-transform: uppercase;
  font-weight: 400;
  letter-spacing: 0.5px;
}

.result-price {
  font-weight: 500;
  color: #b9b9b9;
  font-size: 1.3rem;
  margin-top: 3px;
}

.add-indicator {
  color: #8A38F5;
  font-size: 1.4rem;
  opacity: 0.4;
}

.search-result-item:hover .add-indicator {
  opacity: 1;
  transform: scale(1.2);
}

.dropdown-overlay-closer {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 1000;
  background: transparent;
}

/* Scroll */
.scroll-premium::-webkit-scrollbar {
  width: 8px;
}

.scroll-premium::-webkit-scrollbar-track {
  background: #111;
}

.scroll-premium::-webkit-scrollbar-thumb {
  background: #333;
  border-radius: 10px;
}

.scroll-premium::-webkit-scrollbar-thumb:hover {
  background: #8A38F5;
}

.modal-footer {
  margin-top: 30px;
  display: flex;
  justify-content: flex-end;
  gap: 15px;
}
</style>

<style>
/* Global */
.p-datepicker-panel,
.p-tooltip {
  z-index: 40000 !important;
}
</style>
