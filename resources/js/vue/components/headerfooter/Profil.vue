<template>
  <div ref="menuOverlay" class="user-menu-overlay" :class="{ 'is-open': isOpen }">
    <div class="global-close-btn" @click="closeMenu">
      <i class="fa-solid fa-times"></i>
    </div>
    <div class="menu-columns">
      <div class="menu-column first-col col-1">
        <img :src="logoSrc" alt="ShiftUp" class="menu-brand-logo" />
        <div class="vertical-title cube-scene">
          <div class="cube-container">
            <Link href="#" class="cube-face face-front">
              <span class="small-text">Mes</span>
              <span class="rotate-text">OFFRES</span>
            </Link>
            <Link href="#" class="cube-face face-right">
              <span class="small-text">Mes</span>
              <span class="rotate-text bold-italic">OFFRES</span>
            </Link>
          </div>
        </div>
      </div>

      <div class="menu-column border-left col-2">
        <div class="vertical-title full-center cube-scene">
          <div class="cube-container">
            <Link href="#" class="cube-face face-front">
              <span class="rotate-text">CONSULTATIONS</span>
            </Link>
            <Link href="#" class="cube-face face-right">
              <span class="rotate-text bold-italic">CONSULTATIONS</span>
            </Link>
          </div>
        </div>
      </div>

      <div class="menu-column border-left col-3">
        <div class="vertical-title full-center cube-scene">
          <div class="cube-container">
            <Link href="#" class="cube-face face-front">
              <span class="rotate-text">COACHINGS</span>
            </Link>
            <Link href="#" class="cube-face face-right">
              <span class="rotate-text bold-italic">COACHINGS</span>
            </Link>
          </div>
        </div>
      </div>

      <div class="menu-column border-left col-4">
        <div class="vertical-title full-center cube-scene">
          <div class="cube-container">
            <Link href="#" class="cube-face face-front">
              <span class="rotate-text">TEMOIGNAGES</span>
            </Link>
            <Link href="#" class="cube-face face-right">
              <span class="rotate-text bold-italic">TEMOIGNAGES</span>
            </Link>
          </div>
        </div>
      </div>

      <div class="menu-column user-profile-column border-left" v-if="user">
        <div class="profile-header-section">
          <img v-if="user.profil?.PhotoProfil" :src="user.profil.PhotoProfil" class="large-avatar" />
          <div v-else class="large-avatar-placeholder">
            <i class="fa-solid fa-user fa-3x"></i>
          </div>
          <div class="disconnect-btn" @click="logout">
            SE DECONNECTER <i class="fa-solid fa-arrow-right"></i>
          </div>
        </div>

        <div class="profile-info-content">
          <h2 class="user-nom">{{ user.profil?.Nom || 'NOM' }}</h2>
          <h3 class="user-prenom">{{ user.profil?.Prenom || 'Prénom' }}</h3>

          <div class="editable-field metier-field">
            <span class="field-value metier-color" :class="{ 'placeholder': !user.profil?.Metier }">
              {{ user.profil?.Metier || 'Pas encore de métier enregistré' }}
            </span>
            <i class="fa-solid fa-pencil edit-icon"
              @click="openEdit('profiles', 'Metier', 'Métier', user.profil?.Metier)"></i>
          </div>

          <div class="editable-field mt-2">
            <p class="field-value bio-text" :class="{ 'placeholder': !user.profil?.Biographie }">
              {{ user.profil?.Biographie || 'Ajouter votre biographie ici' }}
            </p>
            <i class="fa-solid fa-pencil edit-icon"
              @click="openEdit('profiles', 'Biographie', 'Biographie', user.profil?.Biographie)"></i>
          </div>

          <div class="separator-line"></div>

          <div class="contact-info">
            <div class="info-row">
              <span class="text-bold">{{ user.Email }}</span>
            </div>

            <div class="editable-field">
              <span class="field-value" :class="{ 'placeholder': !user.profil?.NumeroTelephone }">
                {{ user.profil?.NumeroTelephone || 'Ajouter un numéro de téléphone' }}
              </span>
              <i class="fa-solid fa-pencil edit-icon"
                @click="openEdit('profiles', 'NumeroTelephone', 'Numéro de téléphone', user.profil?.NumeroTelephone)"></i>
            </div>

            <div class="editable-field">
              <span class="field-value" :class="{ 'placeholder': !user.profil?.Adresse }">
                {{ user.profil?.Adresse || 'Ajouter une adresse' }}
              </span>
              <i class="fa-solid fa-pencil edit-icon"
                @click="openEdit('profiles', 'Adresse', 'Adresse', user.profil?.Adresse)"></i>
            </div>
          </div>

          <div class="social-icons">
            <div class="social-circle"><i class="fa-brands fa-facebook-f"></i></div>
            <div class="social-circle"><i class="fa-brands fa-whatsapp"></i></div>
            <div class="social-circle"><i class="fa-brands fa-linkedin-in"></i></div>
            <div class="social-circle"><i class="fa-brands fa-youtube"></i></div>
            <div class="social-circle"><i class="fa-brands fa-instagram"></i></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <Dialog v-model:visible="isEditModalOpen" modal :header="'Modifier ' + editingField.label" :style="{ width: '30rem' }"
    :pt="{ root: { class: 'custom-dialog' }, content: { style: 'overflow: visible' } }">
    <div class="p-field">
      <div v-if="editingField.attribute === 'NumeroTelephone'" class="w-full">
        <vue-tel-input v-model="editingField.value" mode="international" class="minimal-tel-input mb-3" :dropdownOptions="{
          showSearchBox: true,
          showFlags: true,
          showDialCodeInList: true,
          searchPlaceholder: 'recherchez votre pays'
        }" :inputOptions="{ placeholder: 'Exemple: +261 34 00 000 00' }">
        </vue-tel-input>
      </div>
      <div v-else-if="editingField.attribute === 'Biographie'">
        <Textarea id="editValue" v-model="editingField.value" rows="3" class="w-full mb-3 login-style-input" autofocus
          maxlength="100" placeholder="Décrivez-vous..." />
        <div class="text-right text-xs text-gray-400">{{ editingField.value ? editingField.value.length : 0 }}/100</div>
      </div>
      <div v-else>
        <InputText id="editValue" v-model="editingField.value" class="w-full mb-3 login-style-input" autofocus
          :placeholder="'Entrez votre ' + editingField.label.toLowerCase()" />
      </div>
    </div>
    <div class="flex justify-end gap-2 mt-4">
      <Button label="Annuler" text severity="secondary" @click="isEditModalOpen = false" />
      <Button label="Enregistrer" @click="saveField" autofocus />
    </div>
  </Dialog>

  <Toast />
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import gsap from 'gsap';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';

const props = defineProps({
  isOpen: Boolean,
  user: Object
});

const toast = useToast();
const logoSrc = ref('images/logo-site-blanc.png');
const emit = defineEmits(['close']);

const isEditModalOpen = ref(false);
const editingField = ref({ table: '', attribute: '', label: '', value: '' });

// Fonction pour fermer le menu
const closeMenu = () => {
  emit('close');
};

const openEdit = (table, attribute, label, currentValue) => {
  editingField.value = { table, attribute, label, value: currentValue || '' };
  isEditModalOpen.value = true;
};

const saveField = () => {
  router.post('/user/update-attribute', {
    table: editingField.value.table,
    attribute: editingField.value.attribute,
    value: editingField.value.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      isEditModalOpen.value = false;
      toast.add({ severity: 'success', summary: 'Succès', detail: 'Information mise à jour avec succès', life: 3000 });
    },
    onError: (errors) => {
      console.error("Update failed", errors);
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la mise à jour', life: 3000 });
    }
  });
};

const logout = () => {
  router.post('/logout', {}, {
    onError: () => {
      toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de la déconnexion', life: 3000 });
    }
  });
};

const menuOverlay = ref(null);

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    gsap.to(menuOverlay.value, {
      height: '85vh',
      duration: 0.8,
      ease: 'expo.out'
    });

    gsap.fromTo('.menu-column',
      { y: -50, opacity: 0 },
      { y: 0, opacity: 1, duration: 0.8, stagger: 0.1, ease: 'power3.out', delay: 0.2 }
    );

    gsap.fromTo('.profile-info-content > *',
      { x: 30, opacity: 0 },
      { x: 0, opacity: 1, duration: 0.6, stagger: 0.05, ease: 'power2.out', delay: 0.5 }
    );
  } else {
    gsap.to(menuOverlay.value, {
      height: 0,
      duration: 0.6,
      ease: 'expo.inOut'
    });
  }
});

const handleOutsideClick = (event) => {
  if (!props.isOpen) return;

  if (event.target.closest('.p-dialog') || event.target.closest('.p-dialog-mask')) return;

  if (menuOverlay.value && (event.target === menuOverlay.value || !menuOverlay.value.contains(event.target))) {
    closeMenu();
  }
};

onMounted(() => window.addEventListener('mousedown', handleOutsideClick));
onUnmounted(() => window.removeEventListener('mousedown', handleOutsideClick));
</script>

<style scoped>
.user-menu-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 0;
  background-color: #F4F4F4;
  z-index: 1000;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  pointer-events: none;
}

.user-menu-overlay.is-open {
  pointer-events: auto;
}

.menu-columns {
  display: flex;
  height: 100%;
  width: 100%;
}

.menu-column {
  flex: 1;
  position: relative;
  display: flex;
  flex-direction: column;
  padding: 40px 20px;
  background-color: #F4F4F4;
  transition: background-color 0.5s ease;
}

.border-left {
  border-left: 1px solid #000;
}

.cube-scene {
  perspective: 1000px;
  height: 100%;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cube-container {
  position: relative;
  width: 120px;
  height: 450px;
  transform-style: preserve-3d;
  transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.cube-face {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-decoration: none;
}

.face-front {
  transform: rotateY(0deg) translateZ(60px);
}

.face-right {
  transform: rotateY(90deg) translateZ(60px);
}

.menu-column:hover .cube-container {
  transform: rotateY(-90deg);
}

.rotate-text {
  writing-mode: vertical-rl;
  transform: rotate(180deg);
  font-size: 3.5rem;
  letter-spacing: 2px;
  font-weight: 400;
  text-transform: uppercase;
  white-space: nowrap;
  color: #000;
  transition: color 0.3s ease;
}

.small-text {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  font-weight: 400;
  color: #000;
}

.bold-italic {
  font-weight: 900 !important;
  font-style: italic !important;
}

.col-1:hover {
  background-color: #F7B455 !important;
}

.col-2:hover {
  background-color: #1A237E !important;
}

.col-2:hover .face-right .rotate-text {
  color: #00E5FF;
}

.col-3:hover {
  background-color: #8A38F5 !important;
}

.col-3:hover .face-right .rotate-text {
  color: #FFF;
}

.col-4:hover {
  background-color: #1A888D !important;
}

.col-4:hover .face-right .rotate-text {
  color: #ffffff;
}

.menu-column.user-profile-column {
  flex: 2;
  background-color: #F4F4F4;
  padding: 40px 60px;
  overflow-y: auto;
}

.profile-header-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  position: relative;
}

.global-close-btn {
  position: absolute;
  top: 30px;
  right: 40px;
  z-index: 1100;
  cursor: pointer;
  font-size: 1.8rem;
  color: #000;
  padding: 10px;
  transition: transform 0.3s ease, opacity 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.text-bold
{
  font-weight: 500;
  font-size: large;
}

.global-close-btn:hover {
  transform: scale(1.1) rotate(90deg);
  opacity: 0.7;
}

.large-avatar,
.large-avatar-placeholder {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  background-color: #D9D9D9;
}

.large-avatar-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  margin-bottom: 1rem;
}

.disconnect-btn {
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  text-transform: uppercase;
  display: flex;
  align-items: center;
  gap: 8px;
  color: #000;
  margin-right: 3vw;
  transition: opacity 0.3s;
}

.disconnect-btn:hover {
  opacity: 0.7;
}

.user-nom {
  font-size: 2.2rem;
  font-weight: 600;
  text-transform: uppercase;
  margin: 0;
  line-height: 1.1;
}

.user-prenom {
  font-size: 2.2rem;
  font-weight: 400;
  margin: 0 0 10px 0;
  line-height: 1.1;
}

.editable-field {
  display: flex;
  align-items: center;
  gap: 15px;
}

.field-value {
  font-size: 1.3rem;
  font-weight: 350;
  color: #333;
}

.field-value.placeholder {
  color: #888;
}

.metier-color {
  color: #8A38F5 !important;
  font-weight: 500;
  font-size: 1.1rem;
}

.bio-text {
  color: #040404;
  font-size: 1.2rem;
  line-height: 1.2;
  margin: 0;
}

.edit-icon {
  font-size: 0.9rem;
  cursor: pointer;
  color: #333;
  opacity: 0.5;
  transition: all 0.2s;
}

.edit-icon:hover {
  opacity: 1;
  transform: scale(1.1);
}

.separator-line {
  height: 1px;
  background-color: #212121;
  margin: 20px 0;
  width: 80%;
}

.social-icons {
  display: flex;
  gap: 12px;
  margin-top: 25px;
}

.social-circle {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background-color: #D9D9D9;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #1a1a1a;
  cursor: pointer;
  transition: 0.3s;
}

.social-circle:hover {
  background-color: #8A38F5;
  color: white;
  transform: translateY(-3px);
}

.login-style-input {
  width: 100%;
  background: transparent !important;
  border: none !important;
  border-bottom: 1px solid #ccc !important;
  border-radius: 0 !important;
  padding: 5px 0 !important;
  font-size: 1.1rem !important;
  box-shadow: none !important;
}

.login-style-input:focus {
  border-bottom-color: #111 !important;
}

:deep(.minimal-tel-input) {
  border: none !important;
  border-bottom: 1px solid #ccc !important;
  border-radius: 0 !important;
  background: transparent !important;
}

:deep(.p-dialog-mask) {
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 10000 !important;
}

:deep(.p-dialog) {
  z-index: 10001 !important;
}

.menu-brand-logo {
  width: 10vw;
  margin-left: 1.8vw;
  align-self: flex-start;
  filter: invert();
  margin-bottom: 2rem;
}
</style>