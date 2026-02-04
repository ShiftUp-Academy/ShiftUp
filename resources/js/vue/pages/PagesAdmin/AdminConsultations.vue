<template>
  <div class="admin-content">
    <div class="page-header">
      <h1 class="page-title">Consultations</h1>
    </div>

    <div class="main-toggles">
      <StatusToggle leftLabel="Question en attente" rightLabel="Archive" v-model="mainTab" />

      <div class="sub-toggle-wrapper">
        <div class="toggle-container">
          <button class="toggle-btn" :class="{ active: subTab === 'categories' }" @click="setSubTab('categories')">
            Questions par catégories
          </button>
          <button class="toggle-btn" :class="{ active: subTab === 'lessons' }" @click="setSubTab('lessons')">
            Questions par leçon
          </button>
          <div class="toggle-indicator" ref="indicator" :style="indicatorStyle"></div>
        </div>
      </div>
    </div>

    <div class="contentArea">
      <transition mode="out-in" @before-enter="beforeEnter" @enter="enter">
        <div :key="mainTab + subTab + selectedCategoryId" class="tab-content">

          <template v-if="mainTab === 'right'">
            <div class="archive-header-controls" v-if="subTab === 'categories'">
              <span class="filter-label">Trier par catégorie de question</span>
              <Dropdown v-model="archiveCategoryFilter" :options="categories" optionLabel="Nom"
                optionValue="IdCategorie" placeholder="Toutes les catégories" class="archive-cat-dropdown" showClear />
            </div>

            <div class="categories-list-container">
              <div class="categories-table">
                <div class="table-header-row">
                  <div class="col-name">Nom de la consultation</div>
                  <div class="col-date">Date</div>
                  <div v-if="subTab === 'categories'" class="col-category">Catégorie</div>
                  <div v-else class="col-lesson">Leçon</div>
                  <div class="col-count">Nombre de question</div>
                  <div class="col-status">Statut</div>
                  <div class="col-actions"></div>
                </div>

                <div v-for="rep in filteredArchives" :key="rep.IdReponseConsultation" class="table-row">
                  <div class="col-name font-bold">{{ rep.Titre }}</div>
                  <div class="col-date text-gray-600">
                    {{ formatDate(rep.DateCreation) }}
                  </div>
                  <div v-if="subTab === 'categories'" class="col-category font-bold">
                    {{ rep.categorie?.Nom || 'Non catégorisé' }}
                  </div>
                  <div v-else class="col-lesson font-bold">
                    {{ getLessonContext(rep) }}
                  </div>
                  <div class="col-count font-bold">{{ rep.questions_count }}</div>
                  <div class="col-status">
                    <PremiumSlideToggle v-model="rep.Statut" checkedValue="Publié" uncheckedValue="Dépublié"
                      activeColor="#22c55e" @update:modelValue="updateArchiveStatus(rep)" />
                  </div>
                  <div class="col-actions">
                    <button class="edit-btn" @click="openEditResponseModal(rep)">
                      <i class="fas fa-pen"></i> Modifier
                    </button>
                  </div>
                </div>

                <div v-if="filteredArchives.length === 0" class="empty-state">
                  <i class="fas fa-box-open"></i>
                  <p>Aucune réponse archivée trouvée.</p>
                </div>
              </div>
            </div>
          </template>

          <template v-else>
            <template v-if="subTab === 'categories'">
              <div v-if="!selectedCategoryId" class="category-grid">
                <div v-for="cat in categoryStats" :key="cat.IdCategorie" class="category-stat-card"
                  @click="openPlayer(cat)">
                  <h3 class="cat-card-title">{{ cat.Nom }}</h3>
                  <p class="cat-card-count">{{ cat.pendingCount }} question{{ cat.pendingCount > 1 ? 's' : '' }} en
                    attente</p>
                </div>
                <div v-if="categoryStats.length === 0" class="empty-state">
                  <i class="fas fa-folder-open"></i>
                  <p>Aucune catégorie avec des questions en attente.</p>
                </div>
              </div>

              <div v-else class="questions-view">
                <div class="view-header">
                  <button class="back-link-btn" @click="selectedCategoryId = null">
                    <i class="fas fa-arrow-left"></i> Retour aux catégories
                  </button>
                  <h2 class="view-title">{{ selectedCategoryName }}</h2>
                </div>

                <div v-if="displayConsultations.length > 0" class="questions-list">
                  <AdminQuestionCard v-for="item in displayConsultations" :key="item.IdConsultation" :item="item"
                    @reply="openReply" @view-lesson="openLessonModal" />
                </div>
                <div v-else class="empty-state">
                  <p>Aucune question trouvée dans cette catégorie.</p>
                </div>
              </div>
            </template>

            <!-- LESSONS VIEW -->
            <template v-else>
              <div v-if="displayConsultations.length > 0" class="questions-list">
                <AdminQuestionCard v-for="item in displayConsultations" :key="item.IdConsultation" :item="item"
                  @reply="openReply" @view-lesson="openLessonModal" />
              </div>
              <div v-else class="empty-state">
                <i class="fas fa-book-reader"></i>
                <p>Aucune question liée à une leçon pour le moment.</p>
              </div>
            </template>
          </template>

        </div>
      </transition>
    </div>

    <!-- PLAYER -->
    <ConsultationPlayer :isOpen="showPlayer" :category="playerCategory" :questions="playerQuestions"
      @close="showPlayer = false" />

    <!-- EDIT ARCHIVE MODAL -->
    <PremiumModal :isOpen="showEditModal" header="Modifier la réponse consultation" width="40rem"
      @close="closeEditModal">
      <form @submit.prevent="submitEditResponse" class="create-form">
        <div class="field-group">
          <label>Nom de la consultation</label>
          <InputText v-model="editForm.Titre" placeholder="Titre..." required />
        </div>

        <div class="field-group mt-4">
          <label>Catégorie</label>
          <Dropdown v-model="editForm.IdCategorie" :options="categories" optionLabel="Nom" optionValue="IdCategorie"
            placeholder="Sélectionner une catégorie" class="w-full" />
        </div>

        <div class="field-group mt-4">
          <label>Lien de la vidéo</label>
          <InputText v-model="editForm.LienVideo" placeholder="Lien..." required />
        </div>

        <div class="field-group mt-4">
          <label>Description (facultatif)</label>
          <Textarea v-model="editForm.Descriptions" rows="3" placeholder="Description..." />
        </div>

        <div class="field-group mt-4">
          <PremiumSlideToggle v-model="editForm.Statut" checkedValue="Publié" uncheckedValue="Dépublié"
            activeColor="#22c55e" />
        </div>

        <div class="modal-footer">
          <PremiumButton type="submit" :loading="editForm.processing">Enregistrer les modifications</PremiumButton>
        </div>
      </form>
    </PremiumModal>

    <!-- LESSON CONTEXT MODAL -->
    <PremiumModal :isOpen="showLessonModal" :header="selectedLesson?.Titre" width="85vw"
      @close="showLessonModal = false">
      <div v-if="selectedLesson" class="lesson-view-container">
        <LessonContentPlayer :lesson="selectedLesson" />
      </div>
    </PremiumModal>

    <Toast />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { gsap } from 'gsap';
import { useForm, router } from '@inertiajs/vue3';
import StatusToggle from '../../components/ui/StatusToggle.vue';
import PremiumButton from '../../components/ui/PremiumButton.vue';
import ConsultationPlayer from '../../components/admin/ConsultationPlayer.vue';
import PremiumSlideToggle from '../../components/ui/PremiumSlideToggle.vue';
import PremiumModal from '../../components/ui/PremiumModal.vue';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import AdminQuestionCard from '../../components/admin/UiAdmin/AdminQuestionCard.vue';
import LessonContentPlayer from '../../components/sections/LessonContentPlayer.vue';
import { useToast } from "primevue/usetoast";

const props = defineProps({
  consultations: Array,
  reponseConsultations: {
    type: Array,
    default: () => []
  },
  categories: Array
});

const filters = ref({ search: '', dateStart: null, dateEnd: null });
const mainTab = ref('left');
const subTab = ref('categories');
const selectedCategoryId = ref(null);
const indicator = ref(null);
const archiveCategoryFilter = ref(null);
const toast = useToast();

const showEditModal = ref(false);
const editingResponse = ref(null);

const editForm = useForm({
  Titre: '',
  Descriptions: '',
  LienVideo: '',
  Statut: 'Publié',
  IdCategorie: null
});

// Player State
const showPlayer = ref(false);
const playerCategory = ref(null);
const playerQuestions = ref([]);

// Lesson Modal State
const showLessonModal = ref(false);
const selectedLesson = ref(null);

const openPlayer = (cat) => {
  const catQuestions = props.consultations.filter(c =>
    c.IdCategorie === cat.IdCategorie &&
    c.SourceType?.toLowerCase() === 'formulaire' &&
    c.Statut !== 'Fermée'
  );
  playerCategory.value = cat;
  playerQuestions.value = catQuestions;
  showPlayer.value = true;
};

const openReply = (item) => {
  playerCategory.value = item.categorie || { Nom: item.lecon?.Titre || 'Question unique', IdCategorie: item.IdCategorie };
  playerQuestions.value = [item];
  showPlayer.value = true;
};

const openLessonModal = (lesson) => {
  selectedLesson.value = lesson;
  showLessonModal.value = true;
};

const setSubTab = (newTab) => {
  subTab.value = newTab;
  selectedCategoryId.value = null;
  animateIndicator(newTab);
};

const indicatorStyle = computed(() => ({
  left: subTab.value === 'categories' ? '4px' : '50%'
}));

const animateIndicator = (tab) => {
  if (!indicator.value) return;
  gsap.to(indicator.value, {
    left: tab === 'categories' ? '4px' : '50%',
    duration: 0.4,
    ease: "power3.out"
  });
};

// --- COMPUTED DATA ---

const categoryStats = computed(() => {
  return props.categories.map(cat => {
    const catQuestions = props.consultations.filter(c =>
      c.IdCategorie === cat.IdCategorie &&
      c.SourceType?.toLowerCase() === 'formulaire' &&
      (mainTab.value === 'left' ? c.Statut !== 'Fermée' : c.Statut === 'Fermée')
    );
    return {
      ...cat,
      pendingCount: catQuestions.length
    };
  }).filter(cat => cat.pendingCount > 0);
});

const selectedCategoryName = computed(() => {
  const cat = props.categories.find(c => c.IdCategorie === selectedCategoryId.value);
  return cat ? cat.Nom : '';
});

const displayConsultations = computed(() => {
  return props.consultations.filter(c => {
    const isArchive = c.Statut === 'Fermée';
    if (mainTab.value === 'left' && isArchive) return false;
    if (mainTab.value === 'right' && !isArchive) return false;

    const normalizedSource = c.SourceType ? c.SourceType.toLowerCase() : '';
    if (subTab.value === 'categories') {
      if (normalizedSource !== 'formulaire') return false;
      if (selectedCategoryId.value && c.IdCategorie != selectedCategoryId.value) return false;
    } else {
      if (normalizedSource !== 'lecon' && normalizedSource !== 'leçon') return false;
    }

    if (filters.value.search) {
      const search = filters.value.search.toLowerCase();
      return (
        c.Titre?.toLowerCase().includes(search) ||
        c.Question?.toLowerCase().includes(search) ||
        c.utilisateur?.Nom?.toLowerCase().includes(search) ||
        c.utilisateur?.Prenom?.toLowerCase().includes(search)
      );
    }

    return true;
  });
});

const filteredArchives = computed(() => {
  if (mainTab.value !== 'right') return [];

  let archives = props.reponseConsultations;

  if (subTab.value === 'categories') {
    // Archives for formulaire (Category questions)
    archives = archives.filter(r =>
      // Check if IT IS NOT a lesson response
      !r.questions || !r.questions.some(q => q.SourceType?.toLowerCase() === 'lecon' || q.SourceType?.toLowerCase() === 'leçon')
    );
  } else {
    // Archives for lesson questions (Lesson column)
    archives = archives.filter(r =>
      r.questions && r.questions.some(q => q.SourceType?.toLowerCase() === 'lecon' || q.SourceType?.toLowerCase() === 'leçon')
    );
  }

  if (archiveCategoryFilter.value && subTab.value === 'categories') {
    archives = archives.filter(r => r.IdCategorie === archiveCategoryFilter.value);
  }

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase();
    archives = archives.filter(r => r.Titre?.toLowerCase().includes(search));
  }

  return archives;
});

const getLessonContext = (rep) => {
  if (!rep.questions || rep.questions.length === 0) return 'N/A';
  const q = rep.questions.find(q => q.lecon);
  return q?.lecon?.Titre || 'N/A';
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const datePart = date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
  const timePart = date.getHours().toString().padStart(2, '0') + 'h' + date.getMinutes().toString().padStart(2, '0');
  return `${datePart} ${timePart}`;
};

const onRefresh = () => { };

const updateArchiveStatus = (rep) => {
  router.post('/admin/consultations/update-response/' + rep.IdReponseConsultation, {
    Statut: rep.Statut
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({ severity: 'success', summary: 'Mis à jour', detail: 'Le statut a été mis à jour immédiatement', life: 2000 });
    }
  });
};

const openEditResponseModal = (rep) => {
  editingResponse.value = rep;
  editForm.Titre = rep.Titre;
  editForm.Descriptions = rep.Descriptions || '';
  editForm.LienVideo = rep.LienVideo;
  editForm.Statut = rep.Statut;
  editForm.IdCategorie = rep.IdCategorie;
  showEditModal.value = true;
};

const closeEditModal = () => {
  showEditModal.value = false;
  editForm.reset();
  editingResponse.value = null;
};

const submitEditResponse = () => {
  editForm.post('/admin/consultations/update-response/' + editingResponse.value.IdReponseConsultation, {
    onSuccess: () => {
      closeEditModal();
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Consultation archivée mise à jour', life: 3000 });
    }
  });
};

const beforeEnter = (el) => {
  gsap.set(el.querySelectorAll('.category-stat-card, .question-admin-card, .table-row'), { opacity: 0, y: 30 });
};
const enter = (el, done) => {
  gsap.to(el.querySelectorAll('.category-stat-card, .question-admin-card, .table-row'), {
    opacity: 1,
    y: 0,
    duration: 0.5,
    stagger: 0.1,
    ease: "power2.out",
    onComplete: done
  });
};

onMounted(() => {
  setTimeout(() => animateIndicator(subTab.value), 100);
});
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
  width: 450px;
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
  width: calc(50% - 4px);
  bottom: 4px;
  background: #fff;
  border-radius: 11px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  z-index: 1;
}

/* CATEGORY GRID STYLES */
.category-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.category-stat-card {
  background: #1c1c1c;
  color: white;
  padding: 15px 30px;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.category-stat-card:hover {
  transform: translateY(-5px);
  background: #252525;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.cat-card-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.cat-card-count {
  font-size: 0.9rem;
  opacity: 0.7;
  margin: 0;
}

/* QUESTIONS VIEW HEADER */
.view-header {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-bottom: 30px;
}

.back-link-btn {
  background: none;
  border: none;
  color: #8A38F5;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  width: fit-content;
  padding: 0;
}

.view-title {
  font-size: 2rem;
  margin: 0;
  font-weight: 700;
  color: #111;
}

/* QUESTION CARD STYLES */
.questions-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 25px;
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

/* ARCHIVE TABLE STYLES */
.archive-header-controls {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 25px;
}

.filter-label {
  font-weight: 600;
  font-size: 1rem;
}

.archive-cat-dropdown {
  min-width: 300px;
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

.col-name {
  flex: 1.5;
  font-size: 1.1rem;
  color: #111;
}

.col-date {
  flex: 1;
  font-size: 1rem;
}

.col-category,
.col-lesson {
  flex: 1.2;
  font-size: 1rem;
}

.col-count {
  flex: 0.8;
  text-align: center;
  font-size: 1.1rem;
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

.create-form {
  margin-bottom: 2vh;
}

.field-group {
  display: flex;
  flex-direction: column;
  margin-top: 2vh;
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

.lesson-view-container {
  background: #1a1a1a;
  border-radius: 12px;
  overflow: hidden;
  padding: 20px;
}
</style>
