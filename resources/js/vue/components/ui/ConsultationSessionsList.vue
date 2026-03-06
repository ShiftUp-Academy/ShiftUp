<template>
  <div v-if="user" class="consultation-sessions-list">
    <div class="list-container">
      <div class="sidebar">
        <h2 class="sidebar-title">{{ $t('Consultations.my_sessions_title') }}</h2>
      </div>

      <div class="main-content scrollable" data-lenis-prevent>
        <div v-for="(item, index) in sessions" :key="index" class="session-item">
          <div class="item-header">{{ $t('Consultations.the_questions') }}</div>
          <div class="author-row" @click="openProfile()" style="cursor: pointer;">
            <img :src="getAuthorAvatar()" :alt="getAuthorName()" class="avatar" />
            <span class="author-name">{{ getAuthorName() }}</span>
          </div>
          <p class="question-text">{{ item.Question }}</p>
          <div class="item-footer">
            <span class="category-tag">{{ $t('Consultations.category') }} <br /> <span class="category-name">{{
              item.categorie?.Nom || 'Général'
                }}</span></span>
            <a v-if="item.reponse_consultations && item.reponse_consultations.length > 0" href="#"
              class="consultation-link" @click.prevent="$emit('view-detail', item.reponse_consultations[0])">
              {{ $t('Consultations.view_answer') }} <span class="arrow">↗</span>
            </a>
            <span v-else class="pending-tag">{{ $t('Consultations.pending_answer') }}</span>
          </div>
        </div>
      </div>
    </div>

    <PublicProfileModal :isOpen="showPublicProfile" :userId="selectedUserId" @close="showPublicProfile = false" />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import PublicProfileModal from './PublicProfileModal.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const showPublicProfile = ref(false);
const selectedUserId = ref(null);

const openProfile = () => {
  if (user.value?.IdUtilisateur) {
    selectedUserId.value = user.value.IdUtilisateur;
    showPublicProfile.value = true;
  }
};

const props = defineProps({
  sessions: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['view-detail']);

const getAuthorName = () => {
  if (!user.value?.profil) return 'Utilisateur';
  return `${user.value.profil.Prenom} ${user.value.profil.Nom}`;
};

const getAuthorAvatar = () => {
  return user.value?.profil?.PhotoProfil || '/images/Bibliothèque/Nantenaina.jpg'; // Default placeholder if no photo
};
</script>

<style scoped>
.consultation-sessions-list {
  width: 100%;
}

.list-container {
  display: flex;
  background: linear-gradient(34deg, #0E7EC3, #8A38F5);
  border-radius: 24px;
  overflow: hidden;
  height: 85vh;
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
  padding: 40px 60px 40px 60px;
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
  background: rgba(255, 255, 255, 0.4);
  border-radius: 10px;
}

.session-item {
  padding: 30px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.session-item:first-child {
  padding-top: 0;
}

.session-item:last-child {
  border-bottom: none;
}

.item-header {
  font-size: 0.8rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.8);
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
  color: #fff;
}

.question-text {
  font-size: 1.5rem;
  line-height: 1.3;
  color: #fff;
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
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.2;
}

.category-name {
  color: #fff;
  font-weight: 500;
}

.consultation-link {
  font-size: 0.8rem;
  font-weight: 700;
  color: #fff;
  text-decoration: none;
  text-transform: uppercase;
}

.pending-tag {
  font-size: 0.75rem;
  font-weight: 800;
  color: rgba(255, 255, 255, 0.5);
  background: rgba(255, 255, 255, 0.1);
  padding: 6px 12px;
  border-radius: 4px;
  letter-spacing: 0.05em;
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
    height: 300px;
  }
}

@media (max-width: 768px) {
  .sidebar {
    padding: 30px 20px;
  }

  .list-container {
    height: 70vh;
  }

  .sidebar-title {
    font-size: 2.6rem !important;
    width: 80% !important;
    margin-top: 2vh;
    margin-bottom: 0;
    margin-left: 2vw;
  }

  .main-content {
    padding: 20px;
    height: 20vh !important;
  }

  .category-tag {
    width: 3vw;
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
