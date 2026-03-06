<template>
  <div class="admin-content">
    <div class="page-header">
      <h1 class="page-title">Programmes</h1>

      <div class="header-actions">
        <AdminFilters v-model="filters" @refresh="onRefresh" />
        <PremiumButton @click="openCreateModal" class="new-btn-premium">
          <i class="fas fa-plus-circle"></i> Nouveau
        </PremiumButton>
      </div>
    </div>

    <div class="main-toggles">
      <StatusToggle leftLabel="Publiés" rightLabel="Dépublié" v-model="activeTab" />

      <div class="sub-toggle-wrapper">
        <div class="toggle-container">
          <button class="toggle-btn" :class="{ active: subTab === 'categories' }" @click="setSubTab('categories')">
            Programme de formation
          </button>
          <button class="toggle-btn" :class="{ active: subTab === 'lessons' }" @click="setSubTab('lessons')">
            Programme de séminaire
          </button>
          <button class="toggle-btn" :class="{ active: subTab === 'submissions' }" @click="setSubTab('submissions')">
            Validations
            <span v-if="pendingValidationsCount > 0" class="tab-badge">{{ pendingValidationsCount }}</span>
          </button>
          <div class="toggle-indicator" ref="indicator" :style="indicatorStyle"></div>
        </div>
      </div>
    </div>

    <div class="contentArea">
      <transition mode="out-in" @before-enter="beforeContentEnter" @enter="enterContent">
        <div :key="subTab === 'submissions' ? 'submissions' : activeTab" class="tab-main-view">
          <div v-if="subTab !== 'submissions'" class="tab-content">
            <template v-for="tab in ['left', 'right']" :key="tab">
              <div v-if="activeTab === tab" class="tab-pane">
                <div v-if="(tab === 'left' ? publishedProgrammes : unpublishedProgrammes).length > 0"
                  class="programmes-grid">
                  <ProgrammeCard v-for="prog in (tab === 'left' ? publishedProgrammes : unpublishedProgrammes)"
                    :key="prog.IdProgrammeFormation" :programme="prog" @click="openProgramDetails"
                    @duplicate="duplicateProgram" @edit="editProgram" @delete="deleteProgram" />
                </div>
                <div v-else class="empty-state">
                  <p>Aucun programme {{ tab === 'left' ? 'publié' : 'dépublié' }} pour le moment.</p>
                </div>
              </div>
            </template>
          </div>

          <div v-else class="submissions-container">
            <div v-if="submissions.length > 0" class="submissions-list">
              <div v-for="sub in submissions" :key="sub.IdReponse" class="submission-card" @click="openSubmission(sub)">
                <div class="sub-user-info">
                  <img :src="sub.utilisateur?.profil?.Avatar || '/images/default-avatar.png'" class="sub-avatar" />
                  <div>
                    <div class="sub-user-name">{{ sub.utilisateur?.profil?.Prenom }} {{ sub.utilisateur?.profil?.Nom }}
                    </div>
                    <div class="sub-date">{{ formatDate(sub.created_at) }}</div>
                  </div>
                </div>
                <div class="sub-context">
                  <div class="sub-program">{{ sub.etape?.lecon?.theme?.programme?.Titre }}</div>
                  <div class="sub-step">{{ sub.etape?.Titre }}</div>
                </div>
                <div class="sub-action">
                  <button class="view-sub-btn">Répondre</button>
                </div>
              </div>
            </div>
            <div v-else class="empty-state">
              <i class="fas fa-check-double mb-4 text-4xl opacity-20"></i>
              <p>Toutes les réponses ont été validées ! 🎉</p>
            </div>
          </div>
        </div>
      </transition>
    </div>


    <ModalProgramDetails :isOpen="!!selectedProgram" :program="selectedProgram" @close="selectedProgramId = null"
      @edit-program="editProgram(selectedProgram)" @create-lesson="openCreateLessonModal"
      @edit-lesson="openEditLessonModal" @create-theme="openCreateThemeModal" @edit-theme="openEditThemeModal" />

    <ModalTheme :isOpen="showThemeModal" :programId="selectedProgram?.IdProgrammeFormation" :themeToEdit="editingTheme"
      @close="showThemeModal = false" />

    <ModalLesson :isOpen="showLessonModal" :programId="selectedProgram?.IdProgrammeFormation"
      :defaultThemeId="targetThemeId || editingLesson?.IdTheme || selectedProgram?.themes?.[0]?.IdTheme"
      :lessonToEdit="editingLesson" :defaultOrder="nextLessonOrder" @close="showLessonModal = false"
      @create-step="openCreateStepModal" @edit-step="openEditStepModal" />

    <ModalStep :isOpen="showStepModal" :lessonId="currentLessonIdForStep" :stepToEdit="editingStep"
      :defaultOrder="nextStepOrder" @close="showStepModal = false" />

    <ModalValidationStep :isOpen="showValidationModal" :submission="selectedSubmission"
      @close="showValidationModal = false" @success="fetchSubmissions" />

    <ModalCreateProgramme v-if="subTab === 'categories'" :isOpen="showCreateModal" :categories="categories"
      :programToEdit="editingProgram" @close="showCreateModal = false" />

    <ModalSeminar v-else :isOpen="showCreateModal" :categories="categories" :programToEdit="editingProgram"
      @close="showCreateModal = false" />

    <ConfirmModal :isOpen="confirmData.isOpen" :title="confirmData.title" :message="confirmData.message"
      :type="confirmData.type" @confirm="onModalConfirm" @cancel="confirmData.isOpen = false" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import axios from 'axios';

import AdminFilters from '../../components/admin/AdminFilters.vue';
import StatusToggle from '../../components/ui/StatusToggle.vue';
import PremiumButton from '../../components/ui/PremiumButton.vue';
import ConfirmModal from '../../components/ui/ConfirmModal.vue';

import ModalCreateProgramme from '../../components/admin/ComposantModalProgramme/ModalCreateProgramme.vue';
import ModalSeminar from '../../components/admin/ComposantModalProgramme/ModalSeminar.vue';
import ModalProgramDetails from '../../components/admin/ComposantModalProgramme/ModalProgramDetails.vue';
import ModalTheme from '../../components/admin/ComposantModalProgramme/ModalTheme.vue';
import ModalLesson from '../../components/admin/ComposantModalProgramme/ModalLesson.vue';
import ModalStep from '../../components/admin/ComposantModalProgramme/ModalStep.vue';
import ModalValidationStep from '../../components/admin/ComposantModalProgramme/ModalValidationStep.vue';
import ProgrammeCard from '../../components/admin/ProgrammeCard.vue';

const props = defineProps({
  programmes: Array,
  categories: Array,
  pendingValidationsCount: Number
});

const filters = ref({ search: '', dateStart: null, dateEnd: null });
const activeTab = ref('left');
const subTab = ref('categories');
const indicator = ref(null);
const showCreateModal = ref(false);
const editingProgram = ref(null);

const publishedProgrammes = computed(() => {
  return props.programmes.filter(p => {
    const isTypeMatch = subTab.value === 'categories' ? (p.Type === 'Formation' || !p.Type) : p.Type === 'Seminaire';
    return p.Statut === 'Publié' && isTypeMatch;
  });
});

const unpublishedProgrammes = computed(() => {
  return props.programmes.filter(p => {
    const isTypeMatch = subTab.value === 'categories' ? (p.Type === 'Formation' || !p.Type) : p.Type === 'Seminaire';
    return p.Statut === 'Dépublié' && isTypeMatch;
  });
});

// SELECTED PROGRAM & SYNC
const selectedProgramId = ref(null);
const selectedProgram = computed(() => {
  if (!selectedProgramId.value) return null;
  return props.programmes.find(p => p.IdProgrammeFormation === selectedProgramId.value);
});

const openProgramDetails = (prog) => { selectedProgramId.value = prog.IdProgrammeFormation; };

// THEME STATE
const showThemeModal = ref(false);
const editingThemeId = ref(null);
const editingTheme = computed(() => {
  if (!editingThemeId.value || !selectedProgram.value) return null;
  return selectedProgram.value.themes?.find(t => t.IdTheme === editingThemeId.value);
});

const openCreateThemeModal = () => {
  editingThemeId.value = null;
  showThemeModal.value = true;
};

const openEditThemeModal = (theme) => {
  editingThemeId.value = theme.IdTheme;
  showThemeModal.value = true;
};

// LESSON STATE
const showLessonModal = ref(false);
const editingLessonId = ref(null);
const targetThemeId = ref(null);
const nextLessonOrder = ref(1);
const editingLesson = computed(() => {
  if (!editingLessonId.value || !selectedProgram.value) return null;
  for (const theme of selectedProgram.value.themes || []) {
    const lesson = theme.lecons?.find(l => l.IdLecon === editingLessonId.value);
    if (lesson) return lesson;
  }
  return null;
});

const openCreateLessonModal = (theme) => {
  editingLessonId.value = null;
  targetThemeId.value = theme?.IdTheme || null;

  // Calculer l'ordre suivant
  const lessons = theme?.lecons || [];
  const maxOrder = lessons.reduce((max, l) => Math.max(max, l.Ordre || 0), 0);
  nextLessonOrder.value = maxOrder + 1;

  showLessonModal.value = true;
};

const openEditLessonModal = (lecon) => {
  editingLessonId.value = lecon.IdLecon;
  showLessonModal.value = true;
};

// STEP STATE
const showStepModal = ref(false);
const editingStepId = ref(null);
const currentLessonIdForStep = ref(null);
const nextStepOrder = ref(1);

const editingStep = computed(() => {
  if (!editingStepId.value || !editingLesson.value) return null;
  return editingLesson.value.etapes?.find(s => s.IdEtape === editingStepId.value);
});

const openCreateStepModal = (leconId) => {
  editingStepId.value = null;
  currentLessonIdForStep.value = leconId;

  // Trouver la leçon pour calculer l'ordre
  let lesson = null;
  if (selectedProgram.value) {
    for (const theme of selectedProgram.value.themes || []) {
      const found = theme.lecons?.find(l => l.IdLecon === leconId);
      if (found) {
        lesson = found;
        break;
      }
    }
  }

  const steps = lesson?.etapes || [];
  const maxOrder = steps.reduce((max, s) => Math.max(max, s.Ordre || 0), 0);
  nextStepOrder.value = maxOrder + 1;

  showStepModal.value = true;
};

const openEditStepModal = (step) => {
  editingStepId.value = step.IdEtape;
  currentLessonIdForStep.value = step.IdLecon;
  showStepModal.value = true;
};

// SUBMISSIONS logic
const submissions = ref([]);
const showValidationModal = ref(false);
const selectedSubmission = ref(null);

const fetchSubmissions = async () => {
  try {
    const response = await axios.get('/admin/programmes/submissions');
    submissions.value = response.data;
  } catch (err) {
    console.error(err);
  }
};

const openSubmission = (sub) => {
  selectedSubmission.value = sub;
  showValidationModal.value = true;
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
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

// MAIN LIST ACTIONS
const onRefresh = () => { };
const duplicateProgram = (prog) => {
  triggerConfirm(
    "Dupliquer le programme",
    `Voulez-vous vraiment dupliquer le programme "${prog.Titre}" ?`,
    'info',
    () => router.post('/admin/programmes/' + prog.IdProgrammeFormation + '/duplicate')
  );
};
const deleteProgram = (prog) => {
  triggerConfirm(
    "Supprimer le programme",
    `Voulez-vous vraiment déplacer le programme "${prog.Titre}" vers la corbeille ?`,
    'danger',
    () => router.delete('/admin/programmes/' + prog.IdProgrammeFormation)
  );
};
const editProgram = (prog) => {
  editingProgram.value = prog;
  showCreateModal.value = true;
};

const openCreateModal = () => {
  editingProgram.value = null;
  showCreateModal.value = true;
};

const setSubTab = (newTab) => {
  subTab.value = newTab;
  if (newTab === 'submissions') fetchSubmissions();
  animateIndicator(newTab);
};

const indicatorStyle = computed(() => {
  if (subTab.value === 'categories') return { left: '4px', width: 'calc(33.33% - 4px)' };
  if (subTab.value === 'lessons') return { left: '33.33%', width: 'calc(33.33% - 4px)' };
  return { left: '66.66%', width: 'calc(33.33% - 4px)' };
});

const animateIndicator = (tab) => {
  if (!indicator.value) return;
  let targetLeft = '4px';
  if (tab === 'lessons') targetLeft = '33.33%';
  if (tab === 'submissions') targetLeft = '66.66%';

  gsap.to(indicator.value, {
    left: targetLeft,
    duration: 0.4,
    ease: "power3.out"
  });
};

const beforeContentEnter = (el) => { gsap.set(el.querySelectorAll('.programme-card'), { opacity: 0, y: 30 }); };
const enterContent = (el, done) => { gsap.to(el.querySelectorAll('.programme-card'), { opacity: 1, y: 0, duration: 0.6, stagger: 0.1, onComplete: done }); };

onMounted(() => {
  setTimeout(() => animateIndicator(subTab.value), 100);
});
</script>

<style scoped>
.admin-content {
  padding: 40px;
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

.main-toggles {
  display: flex;
  flex-direction: column;
  margin-bottom: 40px;
}

.sub-toggle-wrapper {
  display: flex;
  justify-content: center;
}

.toggle-container {
  display: flex;
  background: #f0f0f0;
  border-radius: 15px;
  position: relative;
  padding: 4px;
  width: 700px;
}

.toggle-btn {
  flex: 1;
  background: none;
  border: none;
  padding: 10px 0;
  font-size: 0.9rem;
  font-weight: 600;
  z-index: 2;
  cursor: pointer;
  color: #888;
  transition: color 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toggle-btn.active {
  color: #000;
}

.toggle-indicator {
  position: absolute;
  top: 4px;
  width: calc(33.33% - 4px);
  bottom: 4px;
  background: #fff;
  border-radius: 11px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  z-index: 1;
}

.tab-badge {
  background: #ef4444;
  color: white;
  font-size: 0.7rem;
  padding: 2px 6px;
  border-radius: 10px;
  margin-left: 8px;
  font-weight: 800;
}

.contentArea {
  min-height: 400px;
}

.tab-pane {
  width: 100%;
}

.programmes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 30px;
}

.program-details-container {
  margin-bottom: 4vh;
}


.card-bottom-info {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  padding: 20px;
  z-index: 5;
}

.card-title-overlay {
  font-size: 1.3rem;
  font-weight: 700;
  color: white;
  margin: 0 0 10px 0;
  line-height: 1.3;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-metrics-row {
  display: flex;
  align-items: center;
  gap: 15px;
}

.metric-price {
  font-size: 1.2rem;
  font-weight: 800;
  color: #fff;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

.metric-points {
  font-size: 0.9rem;
  color: #ffffff;
  font-weight: 600;
  background: #1a878d;
  padding: 5px 10px;
  border-radius: 8px;
  backdrop-filter: blur(4px);
}


.empty-state {
  padding: 80px;
  text-align: center;
  font-size: 1.2rem;
  color: #d0d0d0;
  background: #111;
  border-radius: 20px;
}

.program-title-modal {
  font-size: 1.8rem;
  font-weight: 700;
  color: #111;
  margin: 0;
}

.title-actions {
  display: flex;
  gap: 8px;
}

.header-action-btn {
  height: 40px;
  padding: 0 15px;
  border-radius: 10px;
  border: 1px solid #ddd;
  background: white;
  color: #444;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  transition: all 0.2s;
  font-size: 0.9rem;
}

.header-action-btn.small {
  width: 36px;
  height: 36px;
  padding: 0;
  justify-content: center;
  border-radius: 8px;
}

.header-action-btn:hover {
  background: #f9f9f9;
  border-color: #8A38F5;
  color: #8A38F5;
}

.header-action-btn.edit-full {
  background: #1c1c1c;
  color: white;
  border: none;
  padding: 0 20px;
}

.header-action-btn.edit-full:hover {
  background: #7626d8;
}

.header-action-btn.close-btn {
  width: 40px;
  padding: 0;
  justify-content: center;
  color: #999;
  border: none;
  background: #f5f5f5;
}

.header-action-btn.close-btn:hover {
  background: #eee;
  color: #333;
}

.program-header-info {
  margin-bottom: 30px;
}

.program-description {
  font-size: 1rem;
  color: #666;
  line-height: 1.6;
  margin-bottom: 20px;
}

.program-stats-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.status-indicator {
  font-size: 0.9rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
}

.status-indicator.publié {
  color: #22c55e;
}

.status-indicator.dépublié {
  color: #ef4444;
}

.progression-text {
  font-weight: 700;
  color: #111;
  font-size: 0.95rem;
}

.progress-bar-container {
  width: 100%;
  height: 8px;
  background: #eee;
  border-radius: 4px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: #8A38F5;
  transition: width 0.5s ease;
}

/* Form Styles */
.create-form {
  padding: 20px 0;
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

.field-group label {
  font-weight: 600;
  font-size: 1rem;
  color: #111;
}

.image-upload-wrapper {
  background: #f9f9f9;
  padding: 15px;
  border-radius: 12px;
  border: 1px solid #eee;
}

.divider-container {
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  margin: 15px 0;
}

.divider-container::before {
  content: "";
  position: absolute;
  width: 100%;
  height: 1px;
  background: #eee;
}

.divider {
  background: #f9f9f9;
  padding: 0 10px;
  position: relative;
  font-size: 0.7rem;
  color: #999;
}

.modal-footer {
  margin-top: 30px;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.steps-preview-section {
  padding: 20px;
  background: #f8f9fa;
  border-radius: 12px;
  border: 1px solid #eee;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
}

.add-step-btn-large {
  width: 100%;
  padding: 15px;
  background: white;
  border: 2px dashed #ddd;
  border-radius: 12px;
  color: #8A38F5;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
}

.add-step-btn-large:hover {
  background: rgba(138, 56, 245, 0.05);
  border-color: #8A38F5;
}

.add-chapter-container {
  margin-top: 20px;
  display: flex;
  justify-content: center;
}

.add-chapter-btn {
  background: transparent;
  border: 2px dashed #2f2f2f;
  color: #2f2f2f;
  padding: 12px 24px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.add-chapter-btn:hover {
  border-color: #8A38F5;
  color: #8A38F5;
  background: rgba(138, 56, 245, 0.05);
}

/* TYPE SELECTOR CUSTOM */
.type-selector {
  display: flex;
  gap: 15px;
  margin-top: 10px;
}

.type-pill {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 15px;
  background: #f8f9fa;
  border: 2px solid transparent;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
  color: #666;
}

.type-pill i {
  font-size: 1.5rem;
  opacity: 0.7;
}

.type-pill span {
  font-weight: 600;
  font-size: 0.9rem;
}

.type-pill:hover {
  background: white;
  border-color: #ddd;
  transform: translateY(-2px);
}

.type-pill.active {
  background: white;
  border-color: #8A38F5;
  color: #8A38F5;
  box-shadow: 0 10px 20px rgba(138, 56, 245, 0.1);
}

.type-pill.active i {
  opacity: 1;
}

.lesson-type-section {
  animation: fadeInModal 0.4s ease;
  padding: 20px;
  background: rgba(138, 56, 245, 0.02);
  border-radius: 15px;
  margin-top: 20px;
}

@keyframes fadeInModal {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.submissions-container {
  padding: 20px 0;
}

.submissions-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.submission-card {
  background: white;
  border-radius: 16px;
  padding: 20px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border: 1px solid #eee;
  cursor: pointer;
  transition: all 0.3s ease;
}

.submission-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
  border-color: #8A38F5;
}

.sub-user-info {
  display: flex;
  align-items: center;
  gap: 15px;
  flex: 1;
}

.sub-avatar {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  object-fit: cover;
}

.sub-user-name {
  font-weight: 700;
  font-size: 1.1rem;
}

.sub-date {
  font-size: 0.8rem;
  color: #999;
}

.sub-context {
  flex: 2;
}

.sub-program {
  font-weight: 600;
  color: #8A38F5;
}

.sub-step {
  font-size: 0.9rem;
  color: #555;
}

.view-sub-btn {
  background: #1c1c1c;
  color: white;
  padding: 8px 20px;
  border-radius: 12px;
  font-weight: 700;
  border: none;
  cursor: pointer;
}

.existing-steps-list {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 15px;
}

.step-preview-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 15px;
  background: linear-gradient(95deg, #0E7EC3, #8A38F5);
  background-size: 200% 100%;
  border-radius: 12px;
  border: none;
  transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
  color: white;
  box-shadow: 0 4px 15px rgba(138, 56, 245, 0.2);
}

.step-preview-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(138, 56, 245, 0.3);
}

.step-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.step-type-badge {
  font-size: 0.65rem;
  font-weight: 800;
  text-transform: uppercase;
  padding: 3px 8px;
  background: rgb(255 255 255);
  border-radius: 6px;
  color: #1977c7;
  backdrop-filter: blur(4px);
}

.step-details-summary {
  display: flex;
  flex-direction: column;
}

.step-title-small {
  font-size: 0.95rem;
  font-weight: 700;
  color: white;
  margin: 0;
  line-height: 1.2;
}

.step-question-preview {
  margin: 0;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.8);
  font-style: italic;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 350px;
}

.step-actions {
  display: flex;
  gap: 8px;
}

.btn-icon {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.9);
  cursor: pointer;
  padding: 5px;
  transition: all 0.2s;
}

.btn-icon:hover {
  color: white;
  transform: scale(1.1);
}

.btn-icon.delete:hover {
  color: #ff4d4d;
}

.option-row-item {
  background: white;
  padding: 10px;
  border-radius: 10px;
  border: 1px solid #efefef;
  transition: all 0.2s;
}

.option-row-item:hover {
  border-color: #8A38F5;
  box-shadow: 0 4px 12px rgba(138, 56, 245, 0.05);
}

.custom-radio-step,
.custom-checkbox-step {
  width: 20px;
  height: 20px;
  cursor: pointer;
  accent-color: #8A38F5;
}
</style>