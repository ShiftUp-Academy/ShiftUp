<template>
  <div class="top-bar">
    <div class="logo">
      <img :src="logo" alt="ShiftUp Logo" class="logo-img" />
    </div>
    <div class="user-actions">
      <div class="action-item">
        <i class="fas fa-bell action-icon"></i>
        <span>Notifications</span>
      </div>

      <div class="action-item relative-container" ref="profilRef">
        <div class="profile-trigger" @click.stop="toggleDropdown">
          <i class="fas fa-user action-icon"></i>
          <span>Profil</span>
          <i class="fas fa-chevron-down ml-2" :class="{ 'rotate-180': dropdownOpen }"></i>
        </div>

        <transition name="dropdown-anim">
          <div v-show="dropdownOpen" class="profile-dropdown" @click.stop>
            <div class="dropdown-menu-list">
              <div class="dropdown-item">
                <i class="fas fa-trophy item-icon"></i>
                <span>Gérer les réussites</span>
              </div>
              <Link href="/admin/categories" class="dropdown-item" @click="dropdownOpen = false">
                <i class="fas fa-tags item-icon"></i>
                <span>Gérer les Catégories</span>
              </Link>
              <div class="dropdown-item" @click="openTrash">
                <i class="fas fa-trash-alt item-icon"></i>
                <span>Corbeille</span>
              </div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-item danger" @click="handleLogout">
                <i class="fas fa-sign-out-alt item-icon"></i>
                <span>Déconnexion</span>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>

    <TrashModal :visible="showTrashModal" @close="showTrashModal = false" />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import TrashModal from './TrashModal.vue';

const logo = ref('/images/logo-site-blanc.png');
const dropdownOpen = ref(false);
const profilRef = ref(null);
const showTrashModal = ref(false);

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value;
};

const openTrash = () => {
  dropdownOpen.value = false;
  showTrashModal.value = true;
};

const closeDropdown = (e) => {
  if (profilRef.value && !profilRef.value.contains(e.target)) {
    dropdownOpen.value = false;
  }
};

const handleLogout = () => {
  router.post('/logout');
};

onMounted(() => {
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
});
</script>

<style scoped>
.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4vh;
  position: relative;
  z-index: 2000;
}

.logo-img {
  height: 3.2vw;
  filter: invert();
}

.user-actions {
  display: flex;
  gap: 2rem;
}

.action-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 1rem;
  font-weight: 500;
  color: #111;
  cursor: pointer;
  transition: opacity 0.2s;
  position: relative;
}

.profile-trigger {
  display: flex;
  align-items: center;
  gap: 8px;
}

.action-icon {
  font-size: 1rem;
}

.relative-container {
  position: relative;
}

.profile-dropdown {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: 250px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  border: 1px solid #f0f0f0;
  padding: 8px;
  z-index: 9999;
  transform-origin: top right;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 15px;
  border-radius: 8px;
  color: #444;
  font-size: 0.95rem;
  transition: all 0.2s;
  text-decoration: none;
  cursor: pointer;
}

.dropdown-item:hover {
  background: #f5f5f5;
  color: #8A38F5;
}

.dropdown-item.danger:hover {
  background: #fff0f0;
  color: #ef4444;
}

.item-icon {
  width: 20px;
  text-align: center;
  font-size: 1rem;
}

.dropdown-divider {
  height: 1px;
  background: #eee;
  margin: 6px 0;
}

.ml-2 {
  margin-left: 0.5rem;
  font-size: 0.8rem;
  transition: transform 0.3s;
}

.rotate-180 {
  transform: rotate(180deg);
}

/* Animations */
.dropdown-anim-enter-active,
.dropdown-anim-leave-active {
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.dropdown-anim-enter-from,
.dropdown-anim-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}
</style>
