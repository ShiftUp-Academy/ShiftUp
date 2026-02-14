<template>
  <div class="top-bar">
    <Link href="/" class="logo">
      <img :src="logo" alt="ShiftUp Logo" class="logo-img" />
    </Link>
    <div class="user-actions">
      <div class="action-item relative-container" ref="notifRef">
        <div class="notif-trigger" @click.stop="showNotifDropdown = !showNotifDropdown">
          <i class="fas fa-bell action-icon" :class="{ 'ringing': unreadCount > 0 }"></i>
          <span>Notifications</span>
          <div v-if="unreadCount > 0" class="badge">{{ unreadCount }}</div>
        </div>

        <transition name="dropdown-anim">
          <div v-show="showNotifDropdown" class="profile-dropdown notif-dropdown" @click.stop>
            <div class="dropdown-header">
              <span>Activités récentes</span>
              <button v-if="unreadCount > 0" @click="markAllAsRead" class="mark-read-btn">Tout lu</button>
            </div>

            <div class="dropdown-menu-list custom-scrollbar">
              <div v-if="notifications.length === 0" class="empty-state">
                <i class="fas fa-check-circle"></i>
                <p>Tout est calme.</p>
              </div>

              <div v-else v-for="notif in notifications" :key="notif.Id"
                :class="['dropdown-item notif-item', !notif.DateLecture ? 'unread' : '']"
                @click="handleNotifClick(notif)">
                <div class="notif-icon-wrapper">
                  <i :class="notif.Donnees.icone || 'fas fa-info'"></i>
                </div>
                <div class="notif-content">
                  <p class="notif-msg">{{ notif.Donnees.message }}</p>
                  <span class="notif-date">{{ formatDate(notif.DateCreation) }}</span>
                </div>
                <div v-if="!notif.DateLecture" class="unread-dot"></div>
              </div>
            </div>
          </div>
        </transition>
      </div>

      <div class="action-item relative-container" ref="profilRef">
        <div class="profile-trigger" @click.stop="dropdownOpen = !dropdownOpen">
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
const showNotifDropdown = ref(false);
const profilRef = ref(null);
const notifRef = ref(null);
const showTrashModal = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);

const fetchNotifications = async () => {
  try {
    const response = await fetch('/notifications', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    if (response.ok) {
      notifications.value = await response.json();
      unreadCount.value = notifications.value.filter(n => !n.DateLecture).length;
    } else if (response.status === 401) {
      notifications.value = [];
      unreadCount.value = 0;
    }
  } catch (error) {
    console.error("Failed to load notifications", error);
    notifications.value = [];
    unreadCount.value = 0;
  }
};

const handleNotifClick = async (notif) => {
  if (!notif.DateLecture) {
    try {
      await fetch(`/notifications/${notif.Id}/read`, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
          'Content-Type': 'application/json'
        }
      });
      notif.DateLecture = new Date().toISOString();
      unreadCount.value = Math.max(0, unreadCount.value - 1);
    } catch (e) { console.error(e); }
  }

  if (notif.Donnees.lien) {
    window.location.href = notif.Donnees.lien;
  }
  showNotifDropdown.value = false;
};

const markAllAsRead = async () => {
  try {
    await fetch(`/notifications/read-all`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        'Content-Type': 'application/json'
      }
    });
    notifications.value.forEach(n => n.DateLecture = new Date().toISOString());
    unreadCount.value = 0;
  } catch (e) { console.error(e); }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
};

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
  if (notifRef.value && !notifRef.value.contains(e.target)) {
    showNotifDropdown.value = false;
  }
};

const handleLogout = () => {
  router.post('/logout');
};

onMounted(() => {
  document.addEventListener('click', closeDropdown);
  fetchNotifications();
  // Poll toutes les 60s
  const interval = setInterval(fetchNotifications, 60000);
  onUnmounted(() => clearInterval(interval));
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

/* Notification Styles */
.notif-trigger {
  display: flex;
  align-items: center;
  gap: 8px;
  position: relative;
}

.notif-dropdown {
  width: 320px;
  right: -50px;
  padding: 0;
  overflow: hidden;
}

.dropdown-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #f0f0f0;
  font-weight: 600;
  font-size: 0.9rem;
}

.mark-read-btn {
  background: none;
  border: none;
  font-size: 0.75rem;
  color: #8A38F5;
  cursor: pointer;
}

.dropdown-menu-list.custom-scrollbar {
  max-height: 300px;
  overflow-y: auto;
}

.notif-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 16px;
  border-bottom: 1px solid #f8f8f8;
}

.notif-item.unread {
  background: rgba(138, 56, 245, 0.05);
}

.notif-item:hover {
  background: #fdfdfd;
}

.notif-icon-wrapper {
  background: #f0f0f0;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #555;
  font-size: 0.8rem;
  flex-shrink: 0;
}

.notif-content {
  flex: 1;
}

.notif-msg {
  font-size: 0.85rem;
  margin: 0 0 4px;
  line-height: 1.3;
  color: #333;
}

.notif-date {
  font-size: 0.75rem;
  color: #999;
}

.badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ff4757;
  color: white;
  border-radius: 50%;
  width: 16px;
  height: 16px;
  font-size: 0.65rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid white;
}

.ringing {
  animation: bell-ring 4s infinite;
  transform-origin: top center;
}

@keyframes bell-ring {

  0%,
  90% {
    transform: rotate(0);
  }

  91% {
    transform: rotate(15deg);
  }

  93% {
    transform: rotate(-15deg);
  }

  95% {
    transform: rotate(10deg);
  }

  97% {
    transform: rotate(-10deg);
  }

  99% {
    transform: rotate(0);
  }
}

.empty-state {
  padding: 30px;
  text-align: center;
  color: #ccc;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.unread-dot {
  width: 8px;
  height: 8px;
  background: #8A38F5;
  border-radius: 50%;
  align-self: center;
}
</style>
