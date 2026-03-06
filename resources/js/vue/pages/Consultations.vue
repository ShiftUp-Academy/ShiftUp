<template>
  <ChildHeropage :title="$t('Consultations.title_html')" :description="$t('Consultations.explorez_toutes_vos')"
    :cursorText="$t('Consultations.dcouvrir')" :colors="{
      primary: '#1A888D',
      secondary: '#0E7EC3',
      accent: '#F7B455',
      dark: '#000000'
    }">
    <template #action>
      <a href="#" class="hero-subtitle" @click.prevent="showQuestionModal = true">{{
        $t('Consultations.poser_une_question') }}</a>
    </template>
  </ChildHeropage>

  <Toast />
  <QuestionSection :categories="categories" :questions="publishedConsultations" @view-detail="openDetail" />

  <div class="consultation-section">
    <div class="container">
      <div class="consultation-lists">
        <ConsultationSessionsList :sessions="myConsultations" @view-detail="openDetail" />
        <FreeConsultationsList :consultations="publishedConsultations" @view-detail="openDetail" />
      </div>
    </div>
  </div>

  <PremiumModal :isOpen="showDetailModal" :valid="true" :dark="true" :header="$t('Consultations.detail_modal_header')"
    @close="showDetailModal = false" width="800px">
    <div v-if="selectedConsultation" class="detail-modal-body">
      <div class="video-container" v-if="selectedConsultation.LienVideo">
        <iframe :src="getEmbedUrl(selectedConsultation.LienVideo)" frameborder="0" allowfullscreen
          class="responsive-iframe"></iframe>
      </div>

      <div class="detail-info">
        <h3 class="detail-title">{{ selectedConsultation.Titre }}</h3>
        <p class="detail-description">{{ selectedConsultation.Descriptions }}</p>

        <div class="archived-questions">
          <h4>{{ $t('Consultations.addressed_questions') }}</h4>
          <ul class="questions-list">
            <li v-for="q in selectedConsultation.questions" :key="q.IdConsultation" class="question-pill">
              <span class="q-author">{{ q.utilisateur?.profil?.Prenom || $t('Consultations.Utilisateur') }}</span>
              <span class="q-text">{{ q.Question }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </PremiumModal>

  <PremiumModal :isOpen="showQuestionModal" :valid="questionForm.processing" :dark="true"
    :header="$t('Consultations.ask_modal_header')" @close="showQuestionModal = false" width="500px">
    <div class="ask-modal-body">
      <p class="ask-instruction">
        {{ $t('Consultations.ask_modal_instruction') }}
      </p>

      <form @submit.prevent="submitQuestion" class="ask-form">
        <div class="form-group">
          <label>{{ $t('Consultations.ask_modal_title_label') }}</label>
          <input type="text" v-model="questionForm.titre" class="dark-input"
            :placeholder="$t('Consultations.ask_modal_title_placeholder')" required />
        </div>

        <div class="form-group">
          <label>{{ $t('Consultations.ask_modal_category_label') }}</label>
          <Select v-model="questionForm.category_obj" :options="categories" optionLabel="Nom"
            :placeholder="$t('Consultations.ask_modal_category_placeholder')" class="premium-select"
            @change="onCategoryChange" />
        </div>

        <div class="form-group">
          <label>{{ $t('Consultations.ask_modal_question_label') }}</label>
          <textarea v-model="questionForm.question" class="dark-input textarea-input" rows="5"
            :placeholder="$t('Consultations.ask_modal_question_placeholder')" required></textarea>
        </div>

        <div class="modal-actions">
          <PremiumButton type="submit" :loading="questionForm.processing"
            :disabled="!questionForm.question || !questionForm.category_id" class="submit-btn" width="100%">
            {{ $t('Consultations.ask_modal_submit') }}
          </PremiumButton>
        </div>
      </form>
    </div>
  </PremiumModal>

</template>
<script setup>
import { ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import ChildHeropage from '../components/ui/ChildHeropage.vue';
import ConsultationSessionsList from '../components/ui/ConsultationSessionsList.vue';
import FreeConsultationsList from '../components/ui/FreeConsultationsList.vue';
import QuestionSection from '../components/sections/QuestionSection.vue';
import PremiumModal from '../components/ui/PremiumModal.vue';
import PremiumButton from '../components/ui/PremiumButton.vue';
import Select from 'primevue/select';

const page = usePage();
const $t = (key) => page.props.translations?.[key] || key;

import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";

const props = defineProps({
  categories: {
    type: Array,
    default: () => []
  },
  myConsultations: {
    type: Array,
    default: () => []
  },
  publishedConsultations: {
    type: Array,
    default: () => []
  }
});

const toast = useToast();
const showQuestionModal = ref(false);
const showDetailModal = ref(false);
const selectedConsultation = ref(null);

const questionForm = useForm({
  titre: '',
  question: '',
  category_id: null,
  category_obj: null,
  source_type: 'Formulaire'
});

const onCategoryChange = (e) => {
  questionForm.category_id = e.value ? e.value.IdCategorie : null;
};

const openDetail = (consultation) => {
  selectedConsultation.value = consultation;
  showDetailModal.value = true;
};

const getEmbedUrl = (url) => {
  if (!url) return '';
  if (url.includes('youtube.com/watch?v=')) {
    return url.replace('watch?v=', 'embed/');
  }
  if (url.includes('youtu.be/')) {
    return url.replace('youtu.be/', 'youtube.com/embed/');
  }
  return url;
};

const submitQuestion = () => {
  questionForm.post('/consultations', {
    onSuccess: () => {
      showQuestionModal.value = false;
      questionForm.reset();
      toast.add({ severity: 'success', summary: $t('Success'), detail: $t('Consultations.SuccessDetail'), life: 3000 });
    },
    onError: () => {
      toast.add({ severity: 'error', summary: $t('Error'), detail: $t('Consultations.ErrorDetail'), life: 3000 });
    }
  });
};
</script>


<style scoped>
.consultation-section {
  background-color: #050505;
  padding-bottom: 40px;
  color: white;
}

.container {
  max-width: 90%;
  margin: 0 auto;
}

.consultation-lists {
  display: flex;
  flex-direction: column;
  gap: 40px;
}

.ask-modal-body {
  padding: 10px 0;
  padding-bottom: 3vh;
  color: #e0e0e0;
}

.ask-instruction {
  font-size: 1rem;
  margin-bottom: 25px;
  line-height: 1.5;
  color: #ccc;
  text-align: left;
}

.ask-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  text-align: left;
}

.form-group label {
  font-weight: 600;
  font-size: 0.9rem;
  color: #fff;
}

.dark-input {
  width: 100%;
  background: #151515;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 14px 18px;
  color: white;
  font-family: inherit;
  font-size: 1rem;
  outline: none;
  transition: all 0.3s;
}

.dark-input:focus {
  border-color: #8A38F5;
  background: #1a1a1a;
  box-shadow: 0 0 15px rgba(138, 56, 245, 0.2);
}

:deep(.premium-select) {
  width: 100% !important;
  background: #151515 !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  border-radius: 12px !important;
  padding: 2px 8px !important;
  transition: all 0.3s !important;
}

:deep(.premium-select.p-focus) {
  border-color: #8A38F5 !important;
  box-shadow: 0 0 15px rgba(138, 56, 245, 0.2) !important;
}

:deep(.premium-select .p-select-label) {
  color: white !important;
  padding: 12px 10px !important;
}

:deep(.premium-select .p-select-dropdown) {
  color: #8A38F5 !important;
}

.select-input {
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 1em;
  cursor: pointer;
}

.modal-actions {
  margin-top: 10px;
  display: flex;
  justify-content: flex-end;
}

/* Detail Modal Styles */
.detail-modal-body {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding: 10px 0;
}

.video-container {
  position: relative;
  width: 100%;
  padding-bottom: 56.25%;
  /* 16:9 Aspect Ratio */
  height: 0;
  border-radius: 12px;
  overflow: hidden;
  background: #000;
}

.responsive-iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.detail-info {
  display: flex;
  flex-direction: column;
  gap: 16px;
  text-align: left;
}

.detail-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #fff;
}

.detail-description {
  font-size: 1rem;
  line-height: 1.6;
  color: #ccc;
  white-space: pre-wrap;
}

.archived-questions h4 {
  font-size: 1.1rem;
  margin-bottom: 12px;
  color: #8A38F5;
}

.questions-list {
  list-style: none;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.question-pill {
  background: rgba(255, 255, 255, 0.05);
  padding: 12px 16px;
  border-left: 3px solid #1A888D;
}

.q-author {
  font-weight: 700;
  color: #1A888D;
  margin-right: 8px;
}

.q-text {
  color: #e0e0e0;
}

@media (max-width: 768px) {
  .consultation-lists {
    gap: 30px;
  }

  .hero-subtitle {
    margin-left: 6vw;
  }
}
</style>
