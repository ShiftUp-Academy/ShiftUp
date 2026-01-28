<template>
  <ChildHeropage title="Toutes les <br/>Categories<br/> de programmes "
    description="Explorez toutes les offres, programmes et consultations proposés par ShiftUp regroupés en une seule page."
    cursorText="Découvrir" :colors="{
      primary: '#0A659D',
      secondary: '#8A38F5',
      accent: '#A71543',
      dark: '#000000'
    }" />
  <CategorySection :programs="availablePrograms" />
  <TrainingProgramsSection :programs="availablePrograms" :categories="categories" />

  <div class="consultation-section">
    <div class="container">
      <div class="consultation-lists">
        <ConsultationSessionsList :sessions="myConsultations" @view-detail="openDetail" />
        <FreeConsultationsList :consultations="publishedConsultations" @view-detail="openDetail" />
      </div>
    </div>
  </div>

  <PremiumModal :isOpen="showDetailModal" :valid="true" :dark="true" header="Détails de la consultation"
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
          <h4>Questions abordées :</h4>
          <ul class="questions-list">
            <li v-for="q in selectedConsultation.questions" :key="q.IdConsultation" class="question-pill">
              <span class="q-author">{{ q.utilisateur?.profil?.Prenom || 'Utilisateur' }}</span>
              <span class="q-text">{{ q.Question }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </PremiumModal>
</template>



<script setup>
import { ref, computed } from 'vue';
import ChildHeropage from '../components/ui/ChildHeropage.vue';
import CategorySection from '../components/sections/CategorySection.vue';
import TrainingProgramsSection from '../components/sections/TrainingProgramsSection.vue';
import ConsultationSessionsList from '../components/ui/ConsultationSessionsList.vue';
import FreeConsultationsList from '../components/ui/FreeConsultationsList.vue';
import PremiumModal from '../components/ui/PremiumModal.vue';

const props = defineProps({
  programmes: {
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
  },
  categories: {
    type: Array,
    default: () => []
  }
});

const showDetailModal = ref(false);
const selectedConsultation = ref(null);

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

const availablePrograms = computed(() => {
  return props.programmes
    .map(p => ({
      id: p.IdProgrammeFormation,
      title: p.Titre,
      image: p.LienPhoto || '/images/Programmes/Plan de travail1.png',
      price: Number(p.Prix) > 0 ? Number(p.Prix).toLocaleString() + ' Ar' : 'Gratuit',
      progression: p.progression,
      categoryId: p.IdCategorie
    }));
});
</script>


<style scoped>
.consultation-section {
  background-color: #050505;
  padding-bottom: 80px;
  color: white;
}

.container {
  max-width: 90%;
  margin: 0 auto;
}

.consultation-lists {
  display: flex;
  flex-direction: column;
  gap: 60px;
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
</style>
