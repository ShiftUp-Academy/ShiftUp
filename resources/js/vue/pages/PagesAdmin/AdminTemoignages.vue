<template>
  <div class="admin-content">
    <div class="page-header">
      <h1 class="page-title">Gestion des Témoignages</h1>
      <PremiumButton @click="openCreateModal">
        <i class="fas fa-plus"></i> Ajouter un témoignage
      </PremiumButton>
    </div>

    <div class="main-toggles">
      <StatusToggle leftLabel="Tous les témoignages" rightLabel="En attente / Masqués" v-model="tab" />
    </div>

    <div class="stats-section">
      <div class="stats-grid">
        <div class="stat-card total" @click="resetFilters">
          <div class="stat-header">
            <span class="stat-label">Total</span>
          </div>
          <div class="stat-value">{{ stats.total }}</div>
        </div>

        <div class="stat-card written" @click="filterBy('Texte')">
          <div class="stat-header">
            <span class="stat-label">Témoignages écrits</span>
          </div>
          <div class="stat-value">{{ stats.written }}</div>
        </div>

        <div class="stat-card media" @click="filterBy('Media')">
          <div class="stat-header">
            <span class="stat-label">Photos / vidéos</span>
          </div>
          <div class="stat-value">{{ stats.media }}</div>
        </div>
      </div>
    </div>

    <div class="content-area">
      <div class="filters-container">
        <AdminFilters v-model="filters" @refresh="onRefresh" />
      </div>

      <div class="type-filters-bar">
        <div class="type-filters">
          <button v-for="type in ['Tous', 'Texte', 'Photo', 'Video', 'Media']" :key="type"
            :class="{ active: filterType === type }" @click="filterType = type">
            {{ type === 'Media' ? 'Photos/Vidéos' : type }}
          </button>
        </div>
      </div>

      <div class="testimonials-list" v-if="filteredTemoignages.length > 0">
        <transition-group name="list" tag="div" class="testimonials-grid">
          <div v-for="t in filteredTemoignages" :key="t.IdTemoignage" class="admin-testimonial-card shadow-sm">
            <div class="card-status">
              <PremiumSlideToggle v-model="t.Statut" checkedValue="Publié" uncheckedValue="Masqué"
                @update:modelValue="updateStatus(t)" activeColor="#22c55e" />
            </div>

            <div class="card-author">
              <div class="avatar">
                <img v-if="t.utilisateur?.profil?.PhotoProfil" :src="t.utilisateur.profil.PhotoProfil" alt="Avatar" />
                <div v-else class="avatar-placeholder">{{ t.utilisateur?.profil?.Prenom?.[0] || 'U' }}</div>
              </div>
              <div class="author-info">
                <span class="name">{{ t.utilisateur?.profil ? `${t.utilisateur.profil.Prenom}
                  ${t.utilisateur.profil.Nom}` : 'Utilisateur inconnu' }}</span>
                <span class="date">{{ formatDate(t.DateCreation) }}</span>
              </div>
            </div>

            <div class="card-body">
              <p class="content-preview" :title="t.ContenuTexte">{{ t.ContenuTexte || 'Aucun texte' }}</p>

              <div class="media-preview" v-if="t.Type !== 'Texte' && t.CheminFichier">
                <div v-if="t.Type === 'Photo'" class="image-preview">
                  <img :src="t.CheminFichier" alt="Preview" />
                </div>
                <div v-else-if="t.Type === 'Video'" class="video-preview">
                  <iframe v-if="isYoutube(t.CheminFichier)" :src="getYoutubeEmbed(t.CheminFichier)" frameborder="0"
                    allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
                  </iframe>
                  <video v-else controls class="video-player">
                    <source :src="t.CheminFichier" type="video/mp4">
                    Votre navigateur ne supporte pas la vidéo.
                  </video>
                </div>
              </div>
            </div>

            <div class="card-footer">
              <button class="action-btn edit" @click="openEditModal(t)">
                <i class="fas fa-pen"></i> Modifier
              </button>
              <button class="action-btn delete" @click="confirmDelete(t)">
                <i class="fas fa-trash"></i> Supprimer
              </button>
            </div>
          </div>
        </transition-group>
      </div>

      <div v-else class="empty-state">
        <i class="fas fa-comment-slash"></i>
        <p>Aucun témoignage trouvé.</p>
      </div>
    </div>

    <PremiumModal :isOpen="showModal" :header="editingId ? 'Modifier le témoignage' : 'Ajouter un témoignage'"
      @close="closeModal" width="600px">
      <form @submit.prevent="submitForm" class="admin-form">
        <div class="form-group" v-if="!editingId">
          <label>Utilisateur</label>
          <select v-model="form.IdUtilisateur" class="admin-select" required>
            <option v-for="u in users" :key="u.IdUtilisateur" :value="u.IdUtilisateur">
              {{ u.profil ? `${u.profil.Prenom} ${u.profil.Nom}` : u.Email }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label>Type</label>
          <div class="type-selector">
            <button type="button" v-for="type in ['Texte', 'Photo', 'Video']" :key="type"
              :class="{ active: form.Type === type }" @click="form.Type = type">
              <i :class="getTypeIcon(type)"></i> {{ type }}
            </button>
          </div>
        </div>

        <div class="form-group">
          <label>Contenu Texte</label>
          <textarea v-model="form.ContenuTexte" rows="4" placeholder="Le témoignage..."
            class="admin-textarea"></textarea>
        </div>

        <div class="form-group" v-if="form.Type !== 'Texte'">
          <label>Lien du fichier (Photo ou Vidéo)</label>
          <input type="text" v-model="form.CheminFichier" placeholder="URL du fichier..." class="admin-input" />
          <small>Vous pouvez coller un lien YouTube ou Cloudinary</small>
        </div>

        <div class="form-group">
          <PremiumSlideToggle v-model="form.Statut" checkedValue="Publié" uncheckedValue="Masqué"
            activeColor="#22c55e" />
        </div>

        <div class="form-actions">
          <PremiumButton type="submit" :loading="form.processing">
            {{ editingId ? 'Enregistrer' : 'Ajouter' }}
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
import PremiumButton from '../../components/ui/PremiumButton.vue';
import StatusToggle from '../../components/ui/StatusToggle.vue';
import PremiumSlideToggle from '../../components/ui/PremiumSlideToggle.vue';
import PremiumModal from '../../components/ui/PremiumModal.vue';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";
import AdminFilters from '../../components/admin/AdminFilters.vue';
import ConfirmModal from '../../components/ui/ConfirmModal.vue';

const props = defineProps({
  temoignages: Array,
  users: Array // Added users for creation
});

const toast = useToast();
const tab = ref('left');
const filters = ref({ search: '', dateStart: null, dateEnd: null });
const filterType = ref('Tous');
const showModal = ref(false);
const editingId = ref(null);

const form = useForm({
  IdUtilisateur: '',
  Type: 'Texte',
  ContenuTexte: '',
  CheminFichier: '',
  Statut: 'Publié'
});

const stats = computed(() => {
  const all = props.temoignages || [];
  return {
    total: all.length,
    written: all.filter(t => t.Type === 'Texte').length,
    media: all.filter(t => t.Type === 'Photo' || t.Type === 'Video').length
  };
});

const resetFilters = () => {
  tab.value = 'left';
  filterType.value = 'Tous';
  filters.value = { search: '', dateStart: null, dateEnd: null };
};

const filterBy = (type) => {
  tab.value = 'left';
  filterType.value = type;
};

const filteredTemoignages = computed(() => {
  let list = props.temoignages || [];

  if (tab.value === 'right') {
    list = list.filter(t => t.Statut === 'Masqué' || t.Statut === 'En attente');
  }

  if (filterType.value !== 'Tous') {
    if (filterType.value === 'Media') {
      list = list.filter(t => t.Type === 'Photo' || t.Type === 'Video');
    } else {
      list = list.filter(t => t.Type === filterType.value);
    }
  }

  if (filters.value.search) {
    const s = filters.value.search.toLowerCase();
    list = list.filter(t =>
      (t.ContenuTexte?.toLowerCase().includes(s)) ||
      (t.utilisateur?.profil?.Prenom?.toLowerCase().includes(s)) ||
      (t.utilisateur?.profil?.Nom?.toLowerCase().includes(s))
    );
  }

  if (filters.value.dateStart) {
    const start = new Date(filters.value.dateStart);
    start.setHours(0, 0, 0, 0);
    list = list.filter(t => new Date(t.DateCreation) >= start);
  }

  if (filters.value.dateEnd) {
    const end = new Date(filters.value.dateEnd);
    end.setHours(23, 59, 59, 999);
    list = list.filter(t => new Date(t.DateCreation) <= end);
  }

  return list;
});

const onRefresh = () => {
  router.reload();
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'short', year: 'numeric'
  });
};

const isYoutube = (url) => {
  if (!url) return false;
  return url.includes('youtube.com') || url.includes('youtu.be');
};

const getYoutubeEmbed = (url) => {
  if (!url) return '';
  let videoId = '';
  if (url.includes('youtu.be')) {
    videoId = url.split('/').pop();
  } else if (url.includes('v=')) {
    videoId = url.split('v=')[1].split('&')[0];
  }
  return `https://www.youtube.com/embed/${videoId}`;
};

const getTypeIcon = (type) => {
  switch (type) {
    case 'Texte': return 'fas fa-align-left';
    case 'Photo': return 'fas fa-image';
    case 'Video': return 'fas fa-video';
    default: return 'fas fa-comment';
  }
};

const updateStatus = (t) => {
  router.post(`/admin/temoignages/${t.IdTemoignage}/update`, {
    Statut: t.Statut
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toast.add({ severity: 'success', summary: 'Mis à jour', detail: 'Le statut a été mis à jour', life: 2000 });
    }
  });
};

const openCreateModal = () => {
  editingId.value = null;
  form.reset();
  showModal.value = true;
};

const openEditModal = (t) => {
  editingId.value = t.IdTemoignage;
  form.IdUtilisateur = t.IdUtilisateur;
  form.Type = t.Type;
  form.ContenuTexte = t.ContenuTexte;
  form.CheminFichier = t.CheminFichier;
  form.Statut = t.Statut;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingId.value = null;
  form.reset();
};

const submitForm = () => {
  if (editingId.value) {
    form.post(`/admin/temoignages/${editingId.value}/update`, {
      onSuccess: () => {
        closeModal();
        toast.add({ severity: 'success', summary: 'Succès', detail: 'Témoignage modifié', life: 3000 });
      }
    });
  } else {
    form.post('/temoignages', { // reuse existing store
      onSuccess: () => {
        closeModal();
        toast.add({ severity: 'success', summary: 'Succès', detail: 'Témoignage ajouté', life: 3000 });
      }
    });
  }
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

const confirmDelete = (t) => {
  triggerConfirm(
    "Supprimer le témoignage",
    `Êtes-vous sûr de vouloir supprimer ce témoignage ?`,
    'danger',
    () => {
      router.delete(`/admin/temoignages/${t.IdTemoignage}`, {
        onSuccess: () => {
          toast.add({ severity: 'warn', summary: 'Supprimé', detail: 'Témoignage supprimé avec succès', life: 3000 });
        }
      });
    }
  );
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

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  border-radius: 25px;
  padding: 20px;
  color: white;
  cursor: pointer;
  transition: transform 0.3s, box-shadow 0.3s;
  display: flex;
  flex-direction: column;
  height: 200px;
  position: relative;
  overflow: hidden;
}

.stat-card.total {
  background-color: #1c1c1c;
}

.stat-card.written {
  background-color: #8A38F5;
}

.stat-card.media {
  background-color: #0081f1;
}

.stat-header {
  margin-bottom: auto;
}

.stat-label {
  font-size: 1.5rem;
  font-weight: 300;
}

.stat-value {
  font-size: 5rem;
  font-weight: 500;
  text-align: center;
  padding-bottom: 3vh;
  margin: auto 0;
  line-height: 1;
}

.stat-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  font-size: 1rem;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.stat-footer i {
  font-size: 1.2rem;
}

.filters-container {
  margin-bottom: 20px;
  display: flex;
  justify-content: center;
}

.type-filters-bar {
  display: flex;
  justify-content: center;
  margin-bottom: 30px;
}

.type-filters {
  display: flex;
  background: #eee;
  padding: 4px;
  border-radius: 10px;
  gap: 4px;
}

.type-filters button {
  padding: 8px 16px;
  border-radius: 8px;
  border: none;
  background: transparent;
  cursor: pointer;
  font-weight: 600;
  color: #666;
  transition: all 0.2s;
}

.type-filters button.active {
  background: white;
  color: #000;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 25px;
}

.admin-testimonial-card {
  background: white;
  border-radius: 20px;
  padding: 25px;
  position: relative;
  display: flex;
  flex-direction: column;
  border: 1px solid #eee;
  transition: transform 0.3s;
}

.card-status {
  position: absolute;
  top: 20px;
  right: 20px;
}

.card-author {
  display: flex;
  align-items: center;
  gap: 15px;
}

.avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  overflow: hidden;
  background: #f0f0f0;
  flex-shrink: 0;
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #8A38F5;
  color: white;
  font-weight: 700;
  font-size: 1.2rem;
}

.author-info {
  display: flex;
  flex-direction: column;
}

.author-info .name {
  font-weight: 500;
  width: 60%;
  line-height: 1.1;
  font-size: 1.1rem;
}

.author-info .date {
  font-size: 0.85rem;
  color: #888;
}

.card-body {
  flex: 1;
}

.content-preview {
  font-size: 1.1rem;
  line-height: 1.1;
  color: #444;
  display: -webkit-box;
  -webkit-line-clamp: 4;
  line-clamp: 4;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.media-preview {
  margin-top: 15px;
  border-radius: 12px;
  overflow: hidden;
  background: #f8f8f8;
  border: 1px solid #eee;
  aspect-ratio: 16/9;
}

.image-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.video-preview {
  width: 100%;
  height: 100%;
  position: relative;
  background: #000;
}

.video-preview iframe,
.video-preview video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-footer {
  display: flex;
  gap: 10px;
  padding-top: 15px;
  border-top: 1px solid #f5f5f5;
}

.action-btn {
  flex: 1;
  padding: 20px 20px;
  border-radius: 20px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.action-btn.edit {
  background: #262626;
  color: #f6f6f6;
}

.action-btn.delete {
  background: #fff1f2;
  color: #e11d48;
}

.action-btn.delete:hover {
  background: #ffe4e6;
}

/* Modal Form Styles */
.admin-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-weight: 700;
  color: #111;
  font-size: 1.2rem;
}

.admin-select,
.admin-input,
.admin-textarea {
  width: 100%;
  padding: 15px;
  border-radius: 12px;
  border: 1px solid #e0e0e0;
  outline: none;
  font-family: inherit;
  font-size: 1.1rem;
}

.admin-select:focus,
.admin-input:focus,
.admin-textarea:focus {
  border-color: #8A38F5;
}

.type-selector {
  display: flex;
  gap: 10px;
}

.type-selector button {
  flex: 1;
  padding: 15px;
  border-radius: 12px;
  border: 1px solid #e0e0e0;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 600;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.type-selector button.active {
  background: #8A38F5;
  color: white;
  border-color: #8A38F5;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 10px;
}

.empty-state {
  text-align: center;
  padding: 100px;
  color: #aaa;
}

.empty-state i {
  font-size: 4rem;
  margin-bottom: 20px;
  opacity: 0.2;
}

.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(30px);
}
</style>
