<template>
  <div class="user-reussites-page">
    <div class="video-bg-container">
      <video autoplay loop muted playsinline class="video-bg">
        <source src="https://res.cloudinary.com/dzgdjei0h/video/upload/v1771586509/Reussite_hkqnxr.mp4"
          type="video/mp4">
      </video>
      <div class="video-overlay"></div>
    </div>

    <div class="content-wrapper">
      <div class="hero-section">
        <h1 class="page-title animate-title">
          <span class="glow-text">Vos réussites</span>
        </h1>
        <p class="subtitle animate-fade-in">Votre parcours de transformation mérite d'être célébré. Voici vos badges et
          trophées débloqués.</p>

        <div class="points-summary animate-fade-in" v-if="totalPoints >= 0">
          <div class="points-card">
            <span class="points-count">{{ Math.floor(animatedPoints) }}</span>
            <span class="points-label">POINTS CUMULÉS</span>
          </div>
        </div>
      </div>

      <div class="badges-grid-container" v-if="reussitesObtenues.length > 0">
        <div class="badge-card-container" v-for="(reussite, idx) in reussitesObtenues" :key="reussite.id">
          <LiquidGlass border-radius="35px" class="glass-card">
            <div class="badge-content-horizontal">
              <div class="video-circle-wrapper">
                <video :src="reussite.video_link" autoplay loop muted playsinline class="badge-video-circle"></video>
                <div class="glow-ring"></div>
              </div>

              <div class="badge-details-right">
                <div class="title-row">
                  <div class="title-column">
                    <h3 class="badge-title">{{ reussite.nom }}</h3>
                    <div v-if="reussite.context_title" class="context-subtitle">{{ reussite.context_title }}</div>
                  </div>
                  <div class="points-badge" v-if="reussite.points_recompense">
                    +{{ reussite.points_recompense }} pts
                  </div>
                </div>

                <p class="badge-desc">{{ reussite.description }}</p>

                <div class="badge-footer">
                  <span class="date-info">
                    <i class="far fa-calendar-check meta-icon"></i>
                    Obtenu le {{ formatDate(reussite.pivot?.date_obtention || reussite.created_at) }}
                  </span>
                  <div class="type-pill">
                    <i :class="getActionIcon(reussite.type_action)"></i>
                  </div>
                </div>
              </div>
            </div>
          </LiquidGlass>
        </div>
      </div>

      <div class="empty-state-card" v-else>
        <LiquidGlass border-radius="40px" class="glass-empty">
          <div class="empty-content">
            <div class="empty-visual">
              <i class="fas fa-award"></i>
            </div>
            <h2>Pas encore de badges ?</h2>
            <p>La persévérance est la clé. Terminez vos premières leçons pour débloquer votre collection !</p>
            <PremiumButton text="COMMENCER MAINTENANT" @click="$inertia.visit('/programmes')" width="280" />
          </div>
        </LiquidGlass>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, ref, onMounted, watch } from 'vue';
import { gsap } from 'gsap';
import LiquidGlass from '../components/ui/LiquidGlass.vue';
import PremiumButton from '../components/ui/PremiumButton.vue';

const props = defineProps({
  reussitesObtenues: {
    type: Array,
    default: () => []
  },
  totalPoints: {
    type: Number,
    default: 0
  }
});

const animatedPoints = ref(0);

onMounted(() => {
  gsap.to(animatedPoints, {
    duration: 2.5,
    value: props.totalPoints,
    ease: "power4.out",
    delay: 0.5
  });
});

watch(() => props.totalPoints, (newVal) => {
  gsap.to(animatedPoints, {
    duration: 1.5,
    value: newVal,
    ease: "power3.out"
  });
});

const formatDate = (dateString) => {
  if (!dateString) return 'Récemment';
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

const getActionIcon = (type) => {
  const icons = {
    lecon_terminee: 'fas fa-book-open',
    chapitre_fini: 'fas fa-clone',
    etape_passee: 'fas fa-check-double',
    seuil_points: 'fas fa-star',
    reservation_evenement: 'fas fa-calendar-alt',
    temoignage_laisse: 'fas fa-comment-alt',
    offre_achetee: 'fas fa-shopping-bag'
  };
  return icons[type] || 'fas fa-medal';
};
</script>

<style scoped>
.user-reussites-page {
  position: relative;
  min-height: 100vh;
  color: #fff;
  padding-bottom: 120px;
  overflow-x: hidden;
}

/* Specific Page Overrides */
</style>

<style>
/* Global override for this page to hide main footer ONLY, keeping header floating menu working */
body:has(.user-reussites-page) footer:not(.footer-menu),
body:has(.user-reussites-page) .app-footer {
  visibility: hidden;
  height: 0;
  overflow: hidden;
  margin: 0;
  padding: 0;
}
</style>

<style scoped>
.video-bg-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
  overflow: hidden;
}

.video-bg {
  min-width: 100%;
  min-height: 100%;
  width: auto;
  height: auto;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  object-fit: cover;
}

.video-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle at center, rgba(0, 0, 0, 0.099) 0%, rgba(0, 0, 0, 0.066) 100%);
}

.content-wrapper {
  position: relative;
  z-index: 1;
  max-width: 1400px;
  margin: 0 auto;
  padding: 120px 20px 60px;
}

.hero-section {
  text-align: center;
  margin-bottom: 80px;
}

.page-title {
  font-size: 5rem;
  font-weight: 500;
  line-height: 0.95;
  margin-bottom: 25px;
  letter-spacing: -3px;
}

.glow-text {
  color: #fff;
  text-shadow: 0 0 30px rgba(255, 255, 255, 0.3);
}

.gradient-text {
  background: linear-gradient(135deg, #8A38F5 0%, #00D2FF 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  display: inline-block;
}

.subtitle {
  font-size: 1.4rem;
  color: rgba(255, 255, 255, 0.89);
  max-width: 700px;
  margin: 0 auto;
  line-height: 1.1;
  font-weight: 400;
}

.badges-grid-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
  gap: 30px;
}

.glass-card {
  transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.badge-card-container:hover .glass-card {
  transform: translateY(-8px);
}

.badge-content-horizontal {
  display: flex;
  align-items: center;
  padding: 24px;
  gap: 30px;
}

.video-circle-wrapper {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  position: relative;
  flex-shrink: 0;
  background: #080808;
  padding: 4px;
}

.badge-video-circle {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  z-index: 2;
  position: relative;
}

.glow-ring {
  position: absolute;
  inset: -2px;
  border-radius: 50%;
  background: linear-gradient(135deg, #F7B455, #00D2FF);
  z-index: 1;
  opacity: 0.5;
  filter: blur(8px);
}

.badge-details-right {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.points-summary {
  margin-top: 40px;
  display: flex;
  justify-content: center;
}

.points-card {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.points-count {
  font-size: 5rem;
  font-weight: 900;
  color: #8A38F5;
  line-height: 1;
  text-shadow: 0 0 20px rgba(138, 56, 245, 0.4);
}

.points-label {
  font-size: 0.85rem;
  font-weight: 500;
  letter-spacing: 3px;
  color: rgba(255, 255, 255, 0.6);
  margin-top: 8px;
}

.title-column {
  display: flex;
  flex-direction: column;
}

.badge-title {
  font-size: 1.6rem;
  font-weight: 800;
  margin: 0;
  letter-spacing: -0.5px;
}

.context-subtitle {
  font-size: 1.1rem;
  color: #8A38F5;
  font-weight: 600;
  margin-top: 4px;
}

.points-badge {
  color: #F7B455;
  font-size: 1.2rem;
  font-weight: 700;
  background: rgba(247, 180, 85, 0.1);
  padding: 6px 14px;
  border-radius: 12px;
}

.badge-desc {
  font-size: 1.2rem;
  color: rgba(255, 255, 255, 0.803);
  line-height: 1.5;
  margin: 0 0 16px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.badge-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.date-info {
  font-size: 1rem;
  color: rgb(255, 255, 255);
  display: flex;
  align-items: center;
  gap: 6px;
}

.type-pill {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.6);
}

.empty-state-card {
  max-width: 800px;
  margin: 40px auto;
}

.glass-empty {
  padding: 80px 40px;
}

.empty-content {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.empty-visual {
  font-size: 5rem;
  margin-bottom: 30px;
  background: linear-gradient(135deg, #8A38F5, #00D2FF);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  filter: drop-shadow(0 0 15px rgba(138, 56, 245, 0.4));
}

.empty-content h2 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 15px;
}

/* Animations */
.animate-title {
  animation: slideDown 1s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
}

.animate-fade-in {
  animation: fadeIn 1.5s ease-out 0.5s both;
}

@keyframes slideDown {
  0% {
    transform: translateY(-50px);
    opacity: 0;
  }

  100% {
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes fadeIn {
  0% {
    opacity: 0;
  }

  100% {
    opacity: 1;
  }
}

@media (max-width: 768px) {
  .page-title {
    font-size: 3rem;
  }

  .badges-grid-container {
    grid-template-columns: 1fr;
  }

  .badge-content-horizontal {
    flex-direction: column;
    text-align: center;
    gap: 20px;
  }

  .video-circle-wrapper {
    width: 100px;
    height: 100px;
  }

  .title-row {
    flex-direction: column;
    gap: 10px;
  }
}
</style>
