<template>
  <section class="question-section">
    <div class="container">
      <div class="filters-panel-wrapper">
        <div class="filters-grid">
          <div class="filter-group">
            <label>{{ $t('QuestionSection.contenu_de_la') }}</label>
            <input type="text" v-model="filters.search" :placeholder="$t('QuestionSection.recherchez_par_mot')"
              class="filter-input-simple" />
          </div>

          <div class="filter-group">
            <label>{{ $t('QuestionSection.catgories') }}</label>
            <Select v-model="filters.category" :options="categories" optionLabel="Nom"
              :placeholder="$t('QuestionSection.catgories_2')" class="filter-prime-simple" />
          </div>

          <div class="filter-actions-inline">
            <button class="minimal-reset-btn" @click="resetFilters">
              <i class="fas fa-redo-alt"></i> {{ $t('QuestionSection.rinitialiser') }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="isSearching" class="questions-list">
        <div v-for="(item, index) in filteredQuestions" :key="index" class="session-item full-page-item">
          <div class="item-header">{{ $t('QuestionSection.les_questions') }}</div>
          <div class="author-row">
            <img :src="getAuthorAvatar(item)" :alt="getAuthorName(item)" class="avatar" />
            <div class="author-info">
              <span class="author-name">{{ getAuthorName(item) }}</span>
              <span v-if="item.questionsLibres && item.questionsLibres[0]?.utilisateur?.Role === 'Admin'"
                class="admin-badge">{{
                  $t('QuestionSection.coach') }}</span>
              <span v-if="getStatusLabel(item)" class="status-badge">{{ $t('QuestionSection.' + getStatusLabel(item))
              }}</span>
            </div>
          </div>
          <p class="question-text">{{ getQuestionShort(item) }}</p>
          <div class="item-footer">
            <div class="meta-info">
              <span class="category-tag">{{ $t('NosEvenements.catgorie') }} <span class="category-name">{{
                item.categorie?.Nom || 'Général'
                  }}</span></span>
            </div>
            <a href="#" class="consultation-link" @click.prevent="$emit('view-detail', item)">{{
              $t('QuestionSection.voir_la_consultation') }} <span class="arrow">{{ $t('QuestionSection.arrow')
              }}</span></a>
          </div>
        </div>

        <div v-if="filteredQuestions.length === 0" class="no-results">
          <p>{{ $t('QuestionSection.aucune_question_ne') }}</p>
        </div>
      </div>

      <div v-else class="initial-state">
        <p class="initial-text">{{ $t('QuestionSection.entrez_un_motcl') }}
        </p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AutoComplete from 'primevue/autocomplete';
import Select from 'primevue/select';

const page = usePage();
const $t = (key) => page.props.translations?.[key] || key;

const props = defineProps({
  categories: {
    type: Array,
    default: () => []
  },
  questions: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['view-detail']);

const filters = reactive({
  search: '',
  category: null
});

const isSearching = computed(() => {
  return filters.search.trim() !== '' || filters.category !== null;
});

const filteredQuestions = computed(() => {
  if (!isSearching.value) return [];

  const searchLower = filters.search.toLowerCase();

  return props.questions.filter(item => {
    // Consultation fields are Titre and Question
    const title = item.Titre || '';
    const description = item.Question || '';

    const matchesMain = !filters.search ||
      title.toLowerCase().includes(searchLower) ||
      description.toLowerCase().includes(searchLower);

    const matchesQuestions = !filters.search || (item.questionsLibres && item.questionsLibres.some(q => {
      const qTitle = q.Titre || '';
      const qContent = q.ContenuQuestion || '';
      return qTitle.toLowerCase().includes(searchLower) || qContent.toLowerCase().includes(searchLower);
    }));

    const matchesCategory = !filters.category || item.IdCategorie === filters.category.IdCategorie;

    return (matchesMain || matchesQuestions) && matchesCategory;
  });
});

const resetFilters = () => {
  filters.search = '';
  filters.category = null;
};

const getAuthorName = (item) => {
  if (!item.questionsLibres || item.questionsLibres.length === 0) return 'Archivé';
  const u = item.questionsLibres[0].utilisateur;
  if (!u || !u.profil) return 'Utilisateur';
  return `${u.profil.Prenom} ${u.profil.Nom}`;
};

const getAuthorAvatar = (item) => {
  if (!item.questionsLibres || item.questionsLibres.length === 0) return '/images/Bibliothèque/Nantenaina.jpg';
  const u = item.questionsLibres[0].utilisateur;
  return u?.profil?.PhotoProfil || '/images/Bibliothèque/Nantenaina.jpg';
};

const getQuestionShort = (item) => {
  if (!item.questionsLibres || item.questionsLibres.length === 0) return item.Titre;
  // If searching, try to return the specific question that matched
  if (filters.search) {
    const searchLower = filters.search.toLowerCase();
    const match = item.questionsLibres.find(q => {
      const qTitle = q.Titre || '';
      const qContent = q.ContenuQuestion || '';
      return qTitle.toLowerCase().includes(searchLower) || qContent.toLowerCase().includes(searchLower);
    });
    if (match) return match.ContenuQuestion || match.Titre;
  }
  return item.questionsLibres[0].ContenuQuestion || item.questionsLibres[0].Titre;
};

const getStatusLabel = (item) => {
  return item.Statut === 'Publié' ? null : 'non_publie';
};
</script>

<style scoped>
.question-section {
  background-color: #050505;
  padding: 40px 0;
  color: white;
}

.container {
  max-width: 90%;
  margin: 0 auto;
}

.filters-panel-wrapper {
  background: #ffffff;
  border-radius: 25px;
  padding: 0 5vw;
  margin-bottom: 30px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.panel-header {
  padding-top: 1.5rem;
}

.filter-group label {
  font-size: 1.5rem !important;
  font-weight: 500 !important;
}

.panel-title {
  font-size: 1.4rem;
  font-weight: 700;
  color: #111;
  margin: 0;
}

.filters-grid {
  padding: 20px 0;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr auto;
  gap: 20px;
  align-items: flex-end;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.filter-group label {
  font-size: 0.9rem;
  font-weight: 700;
  color: #111;
}

.filter-input-simple,
:deep(.filter-input-simple-inner) {
  width: 100%;
  background: transparent !important;
  border: none !important;
  border-bottom: 2px solid #afafaf !important;
  padding: 8px 0 !important;
  font-size: 1.1rem !important;
  color: #111 !important;
  outline: none !important;
  box-shadow: none !important;
  transition: all 0.3s;
  border-radius: 0 !important;
}

.filter-input-simple:focus,
:deep(.filter-input-simple-inner:focus) {
  border-bottom-color: #111 !important;
}

:deep(.filter-prime-simple) {
  width: 100% !important;
}

:deep(.filter-prime-simple.p-select) {
  background: transparent !important;
  border-radius: 0 !important;
  border: none !important;
  border-bottom: 2px solid #afafaf !important;
}

:deep(.filter-prime-simple.p-select .p-select-label) {
  padding: 8px 0 !important;
  font-size: 1.1rem !important;
  color: #111 !important;
}

:deep(.filter-prime-simple:not(.p-disabled).p-focus),
:deep(.filter-prime-simple.p-select:not(.p-disabled).p-focus) {
  border-bottom-color: #111 !important;
  box-shadow: none !important;
}

.filter-actions-inline {
  display: flex;
  align-items: center;
  padding-bottom: 8px;
}

.minimal-reset-btn {
  background: transparent;
  border: none;
  color: #202020;
  font-size: 1rem;
  font-weight: 400;
  cursor: pointer;
  display: flex;
  font-style: uppercase;
  align-items: center;
  gap: 8px;
  transition: color 0.3s;
}

.minimal-reset-btn:hover {
  color: #111;
}

/* List Style */
.questions-list {
  display: flex;
  flex-direction: column;
}

.session-item.full-page-item {
  padding: 30px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.session-item:last-child {
  border-bottom: none;
}

.item-header {
  font-size: 0.7rem;
  font-weight: 800;
  color: rgba(255, 255, 255, 0.4);
  letter-spacing: 0.1em;
  text-transform: uppercase;
  margin-bottom: 15px;
}

.author-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 15px;
}

.avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.author-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.author-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #fff;
}

.admin-badge {
  font-size: 0.9rem;
  color: #A16EFF;
  font-weight: 500;
}

.status-badge {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.4);
  background: rgba(255, 255, 255, 0.1);
  padding: 2px 8px;
  border-radius: 4px;
  width: fit-content;
}

.question-text {
  font-size: 1.5rem;
  line-height: 1.2;
  color: #fff;
  font-weight: 600;
  margin-bottom: 20px;
  letter-spacing: -0.01em;
}

.item-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.meta-info {
  display: flex;
  align-items: center;
  color: rgba(255, 255, 255, 0.5);
  font-size: 0.9rem;
}

.category-name {
  color: #fff;
  font-weight: 700;
  margin-left: 5px;
}

.tags-list {
  color: #F7B455;
  font-weight: 700;
  margin-left: 5px;
}

.consultation-link {
  font-size: 0.8rem;
  font-weight: 800;
  color: #fff;
  text-decoration: none;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s;
}

.consultation-link:hover {
  color: #8A38F5;
}

.consultation-link:hover .arrow {
  transform: translate(3px, -3px);
}

.initial-state,
.no-results {
  text-align: center;
  padding: 60px 20px;
  border: 1px dashed rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  color: rgba(255, 255, 255, 0.4);
}

.initial-text {
  font-size: 1rem;
  font-weight: 500;
  max-width: 400px;
  margin: 0 auto;
  line-height: 1.5;
}

@media (max-width: 1100px) {
  .filters-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 768px) {
  .filters-grid {
    grid-template-columns: 1fr;
    padding: 20px 10px;
  }

  .question-text {
    font-size: 1.2rem;
  }

  .item-footer {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
}
</style>