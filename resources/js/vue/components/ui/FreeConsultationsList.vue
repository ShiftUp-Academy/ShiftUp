<template>
  <div class="free-consultations-list">
    <div class="list-container">
      <div class="sidebar">
        <h2 class="sidebar-title">LISTES DES REDIFFUSIONS DE CONSULTATIONS GRATUITES</h2>
      </div>

      <div class="main-content scrollable" data-lenis-prevent>
        <div v-for="(item, index) in consultations" :key="index" class="replay-item">
          <div class="item-header">LES QUESTIONS</div>
          <div class="author-row">
            <img :src="getAuthorAvatar(item.questions)" :alt="getAuthorName(item.questions)" class="avatar" />
            <span class="author-name">{{ getAuthorName(item.questions) }}</span>
          </div>
          <p class="question-text">{{ getQuestionText(item.questions) }}</p>
          <div class="item-footer">
            <span class="category-tag">Catégorie <br /> <span class="category-name">{{ item.categorie?.Nom || 'Général'
            }}</span></span>
            <a href="#" class="consultation-link" @click.prevent="$emit('view-detail', item)">VOIR LA REPONSE <span
                class="arrow">↗</span></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  consultations: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['view-detail']);

const getAuthorName = (questions) => {
  if (!questions || questions.length === 0) return 'Anonyme';
  const user = questions[0].utilisateur;
  if (!user || !user.profil) return 'Utilisateur';
  return `${user.profil.Prenom} ${user.profil.Nom}`;
};

const getAuthorAvatar = (questions) => {
  if (!questions || questions.length === 0) return '/images/Bibliothèque/Nantenaina.jpg';
  const user = questions[0].utilisateur;
  return user?.profil?.PhotoProfil || '/images/Bibliothèque/Nantenaina.jpg';
};

const getQuestionText = (questions) => {
  if (!questions || questions.length === 0) return 'Question non disponible';
  return questions[0].Question;
};
</script>

<style scoped>
.free-consultations-list {
  width: 100%;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.list-container {
  display: flex;
  background: transparent;
  overflow: hidden;
  height: 500px;
}

.sidebar {
  flex: 0 0 35%;
  padding: 60px 40px;
  display: flex;
  align-items: flex-start;
}

.sidebar-title {
  font-size: 3.5rem;
  margin-left: 2vw;
  margin-top: 0;
  font-weight: 300;
  line-height: 1.1;
  color: #fff;
  text-transform: uppercase;
}

.main-content {
  flex: 1;
  padding: 40px 60px 100px 60px;
  /* Added bottom padding */
  overflow-y: auto;
  overscroll-behavior: auto;
}

/* Custom Scrollbar */
.main-content::-webkit-scrollbar {
  width: 6px;
}

.main-content::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
}

.main-content::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 10px;
}

.replay-item {
  padding: 30px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.replay-item:first-child {
  padding-top: 0;
}

.replay-item:last-child {
  border-bottom: none;
}

.item-header {
  font-size: 0.8rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.5);
  text-transform: uppercase;
  margin-bottom: 15px;
}

.author-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
}

.author-name {
  font-size: 1rem;
  font-weight: 400;
  color: #ffffff;
}

.question-text {
  font-size: 1.5rem;
  line-height: 1.3;
  color: #ffffff;
  font-weight: 500;
  margin-bottom: 25px;
}

.item-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.category-tag {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.5);
  line-height: 1.2;
}

.category-name {
  color: #ffffff;
  font-weight: 500;
}

.consultation-link {
  font-size: 0.8rem;
  font-weight: 700;
  color: #ffffff;
  text-decoration: none;
  text-transform: uppercase;
}

.arrow {
  display: inline-block;
  margin-left: 5px;
}

@media (max-width: 1024px) {
  .list-container {
    flex-direction: column;
    height: auto;
  }

  .sidebar {
    padding: 40px;
  }

  .sidebar-title {
    font-size: 2.5rem;
  }

  .main-content {
    padding: 40px;
    height: 500px;
  }
}

@media (max-width: 768px) {
  .sidebar {
    padding: 30px 20px;
  }

  .sidebar-title {
    font-size: 2.6rem !important;
    width: 80% !important;
    margin-top: 2vh;
    margin-bottom: 0;
    margin-left: 2vw;
  }

  .category-tag {
    width: 3vw;
  }

  .list-container {
    height: 70vh;
  }

  .main-content {
    padding: 20px;
    height: 300px;
  }

  .question-text {
    font-size: 1.2rem;
    margin-bottom: 15px;
  }

  .replay-item {
    padding: 20px 0;
  }
}
</style>
