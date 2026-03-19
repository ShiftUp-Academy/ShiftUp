<template>
  <div class="video-sessions-list no-global-reveal" id="nos-evenements-section" @mouseenter="onMouseEnter" @mouseleave="onMouseLeave"
    @mousedown="onMouseDown" @mouseup="onMouseUp" @click="goToFacebook">
    <div ref="cursorRef" class="fb-isolated-marquee-cursor">
      <div class="marquee-wrapper">
        <div class="marquee-content">
          <span v-for="n in 4" :key="n"><i class="fab fa-facebook"></i> {{ $t('NosEvenements.voir_la_page') }}</span>
        </div>
      </div>
    </div>

    <div class="list-container-bg">
      <ShaderBackground :colors="themeColors" class="shader-inner-bg">
        <div class="list-container-content">
        <div class="sidebar">
          <h2 class="sidebar-title">{{ $t('NosEvenements.nos_evenements') }}</h2>
          <p class="description-text">
            {{ $t('NosEvenements.multipliez_votre_chiffre') }}
          </p>
        </div>

        <div class="main-content scrollable" data-lenis-prevent @click.stop>
          <div v-for="(item, index) in videoSessions" :key="index" class="session-item">
            <div class="item-header">
              <span>{{ $t('Sequence') }} {{ index + 1 }}</span>
              <span class="category-tag">{{ $t('NosEvenements.catgorie') }} <span class="category-name">{{
                $t(item.category) }}</span></span>
            </div>

            <div class="video-wrapper" @mouseenter="onVideoEnter" @mouseleave="onVideoLeave">
              <div v-if="!activatedVideos[index]" class="video-manual-trigger"
                :style="{ backgroundImage: `url(${item.thumbnail})` }" @click.stop="activateVideo(index)">
                <LiquidGlass class="play-btn-glass" border-radius="50%" center> <i class="fas fa-play"></i>
                </LiquidGlass>
              </div>

              <iframe v-if="activatedVideos[index]"
                :src="`https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(item.videoUrl)}&show_text=0&autoplay=1&loop=1&muted=0`"
                width="100%" height="100%" style="border:none;overflow:hidden; pointer-events: auto;" scrolling="no"
                frameborder="0" allowfullscreen="true"
                allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
            </div>

            <div class="item-footer">
              <a :href="item.videoUrl" target="_blank" class="session-link" @click.stop @mouseenter="onLinkEnter"
                @mouseleave="onLinkLeave">
                {{ $t('Coachings.voir_la_session') }}
                <img :src="ArrowIcon" alt="Flèche" class="arrow-icon" />
              </a>
            </div>
          </div>
        </div>
      </div>
      </ShaderBackground>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { gsap } from 'gsap';
import ArrowIcon from '../../../assets/images/fleche-lien.svg';
import ShaderBackground from '../ui/ShaderBackground.vue';
import LiquidGlass from '../ui/LiquidGlass.vue';

const cursorRef = ref(null);
const isInsideSection = ref(false);
const isCursorForcedHidden = ref(false);

const videoSessions = [
  {
    videoUrl: 'https://www.facebook.com/nante.randria.gel/videos/387180633829552/?t=18',
    category: 'Categories.finances',
    thumbnail: 'https://res.cloudinary.com/dzgdjei0h/image/upload/v1767346336/s%C3%A9minaire_3_vchxqz.jpg'
  },
  {
    videoUrl: 'https://www.facebook.com/nante.randria.gel/videos/736429424790754/?t=48',
    category: 'Categories.entrepreneuriat',
    thumbnail: 'https://res.cloudinary.com/dzgdjei0h/image/upload/v1767346253/s%C3%A9minaire_1_szvxfh.jpg'
  },
  {
    videoUrl: 'https://www.facebook.com/nante.randria.gel/videos/1426325668316950/?t=83',
    category: 'Categories.developpement',
    thumbnail: 'https://res.cloudinary.com/dzgdjei0h/image/upload/v1767346335/s%C3%A9minaire_2_zutvnp.jpg'
  }
];

const activatedVideos = ref(videoSessions.map(() => false));

const activateVideo = (index) => {
  activatedVideos.value[index] = true;
};

const hideCursor = () => {
  if (cursorRef.value) {
    gsap.to(cursorRef.value, { scale: 0, opacity: 0, duration: 0.15, overwrite: 'auto' });
  }
};

const showCursor = () => {
  if (cursorRef.value && isInsideSection.value && !isCursorForcedHidden.value) {
    gsap.to(cursorRef.value, { scale: 1, opacity: 1, duration: 0.2, overwrite: 'auto' });
  }
};

const onVideoEnter = () => hideCursor();
const onVideoLeave = () => showCursor();

const onLinkEnter = () => {
  isCursorForcedHidden.value = true;
  hideCursor();
};

const onLinkLeave = () => {
  isCursorForcedHidden.value = false;
  showCursor();
};

onMounted(() => {
  if (cursorRef.value) {
    gsap.set(cursorRef.value, { display: 'none', opacity: 0 });
  }
  window.addEventListener('mousemove', updateMouse);
});

onUnmounted(() => {
  window.removeEventListener('mousemove', updateMouse);
});

const updateMouse = (e) => {
  if (!isInsideSection.value || !cursorRef.value) return;

  gsap.set(cursorRef.value, {
    x: e.clientX - 75,
    y: e.clientY - 20
  });
};

const onMouseEnter = (e) => {
  isInsideSection.value = true;
  if (!cursorRef.value || isCursorForcedHidden.value) return;

  gsap.set(cursorRef.value, {
    x: e.clientX - 75,
    y: e.clientY - 20,
    display: 'flex',
    scale: 1
  });

  gsap.to(cursorRef.value, { opacity: 1, duration: 0.2 });
};

const onMouseLeave = () => {
  isInsideSection.value = false;
  if (!cursorRef.value) return;

  gsap.to(cursorRef.value, {
    opacity: 0,
    duration: 0.2,
    onComplete: () => {
      if (!isInsideSection.value && cursorRef.value) {
        gsap.set(cursorRef.value, { display: 'none' });
      }
    }
  });
};

const onMouseDown = () => {
  if (cursorRef.value && !isCursorForcedHidden.value) {
    gsap.to(cursorRef.value, { scale: 0.9, duration: 0.1 });
  }
};

const onMouseUp = () => {
  if (cursorRef.value && !isCursorForcedHidden.value) {
    gsap.to(cursorRef.value, { scale: 1, duration: 0.2, ease: "back.out(2)" });
  }
};

const goToFacebook = () => {
  window.open('https://www.facebook.com/nante.randria.gel', '_blank');
};

const themeColors = {
  primary: '#F7B455',
  secondary: '#0E7EC3',
  accent: '#981e12',
  dark: '#000000'
};
</script>

<style scoped>
.fb-isolated-marquee-cursor {
  position: fixed;
  top: 0;
  left: 0;
  width: 150px;
  height: 40px;
  background: rgb(59, 20, 255);
  border-radius: 50px;
  pointer-events: none;
  z-index: 1000000;
  display: none;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  opacity: 0;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  will-change: transform, opacity;
}

.marquee-wrapper {
  overflow: hidden;
  width: 100%;
}

.marquee-content {
  display: flex;
  white-space: nowrap;
  animation: marquee-animation 3s linear infinite;
}

.marquee-content span {
  font-family: sans-serif;
  font-size: 0.6vw;
  font-weight: 600;
  color: #ffffff;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  flex-shrink: 0;
  padding: 0 10px;
}

.marquee-content i {
  margin-right: 5px;
  font-size: 0.9rem;
}

@keyframes marquee-animation {
  0% {
    transform: translateX(0%);
  }

  100% {
    transform: translateX(-25%);
  }
}

.video-sessions-list {
  width: 100%;
  display: flex;
  justify-content: center;
  background: #000;
  padding-bottom: 9vh;
}

.session-link,
.video-manual-trigger,
.video-wrapper iframe {
  cursor: pointer !important;
}

.session-link,
.video-manual-trigger,
.video-wrapper iframe {
  cursor: pointer !important;
}

.list-container-bg {
  border-radius: 24px;
  margin-top: 4vh;
  overflow: hidden !important;
  height: 85vh;
  min-height: 85vh;
  max-height: 90%;
  width: 90%;
  position: relative;
}

.list-container-content {
  display: flex;
  width: 100%;
  height: 100%;
  position: relative;
  z-index: 10;
}

.sidebar {
  display: flex;
  flex-direction: column;
  flex: 0 0 35%;
  padding: 60px 40px;
  align-items: flex-start;
  height: 100%;
}

.sidebar-title {
  font-size: 3vw;
  margin-left: 2vw;
  margin-top: 0;
  font-weight: 300;
  line-height: 1.1;
  color: #fff;
  margin-bottom: 0;
  text-transform: uppercase;
}

.description-text {
  color: #ffffff;
  font-size: 1.2vw;
  line-height: 1.3;
  margin-left: 2.3vw;
  font-weight: 300;
  margin-bottom: auto;
}

.main-content {
  flex: 1;
  padding: 40px 60px 40px 60px;
  overflow-y: auto;
  overscroll-behavior: auto;
}

.main-content::-webkit-scrollbar {
  width: 6px;
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
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.7vw;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 15px;
}

.video-wrapper {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 9;
  background: #000;
  border-radius: 15px;
  overflow: hidden;
  margin-bottom: 25px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.video-manual-trigger {
  position: absolute;
  inset: 0;
  z-index: 10;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.video-manual-trigger::before {
  content: '';
  position: absolute;
  inset: 0;
  transition: background 0.3s ease;
}

.video-manual-trigger:hover {
  transform: scale(1.02);
}

.play-btn-glass {
  width: 100px;
  height: 100px;
  color: white;
  font-size: 1.5rem;
  z-index: 2;
  transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.video-manual-trigger:hover .play-btn-glass {
  transform: scale(1.15);
}

.item-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.category-tag {
  font-size: 0.8vw;
  color: rgba(255, 255, 255, 0.8);
}

.category-name {
  color: #fff;
  font-weight: 500;
}

.session-link {
  font-size: 0.7vw;
  font-weight: 700;
  color: #fff;
  text-decoration: none;
  text-transform: uppercase;
  pointer-events: auto;
}

.arrow-icon {
  width: 13px;
  height: 13px;
  margin-left: 8px;
  margin-bottom: 3px;
  filter: invert(1);
  transition: transform 0.3s ease;
}

.session-link:hover .arrow-icon {
  transform: translateX(3px);
}

@media (max-width: 1024px) {
  .list-container-bg {
    height: auto;
    width: 95%;
  }

  .list-container-content {
    flex-direction: column;
  }

  .sidebar {
    padding: 40px 20px;
    flex: none;
    align-items: center;
    text-align: center;
  }

  .sidebar-title {
    font-size: 2.2rem;
    margin-left: 0;
  }

  .description-text {
    font-size: 1.1rem;
    margin-left: 0;
    max-width: 90%;
  }

  .main-content {
    padding: 20px;
    height: auto;
    flex: none;
    max-height: 80vh;
  }
}

@media (max-width: 768px) {
  .video-sessions-list {
    margin-top: -2px;
    padding-bottom: 5vh;
  }

  .fb-isolated-marquee-cursor {
    display: none !important;
  }

  .list-container-bg {
    border-radius: 30px;
    max-height: 75vh;
  }

  .main-content {
    max-height: 55vh;
    padding: 15px 15px 170px 15px;
  }

  .session-item {
    padding: 10px 0;
  }

  .video-wrapper {
    margin-bottom: 5px;
  }

  .video-manual-trigger .play-btn-glass {
    width: 50px;
    height: 50px;
    font-size: 0.9rem;
  }

  .item-footer {
    margin-top: 2px;
    width: 100%;
  }

  .session-link {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    margin-top: 0 !important;
  }
}

@media (max-width: 480px) {
  .sidebar {
    padding: 30px 15px;
  }

  .sidebar-title {
    font-size: 1.8rem;
  }

  .description-text {
    font-size: 0.95rem;
    max-width: 95%;
  }

  .list-container-bg {
    width: 96%;
    border-radius: 20px;
    max-height: 80vh;
  }

  .main-content {
    max-height: 60vh;
    padding: 10px 10px 100px 10px;
  }

  .play-btn-glass {
    width: 45px;
    height: 45px;
    font-size: 0.8rem;
  }

  .session-link {
    font-size: 0.7rem;
    padding: 8px 10px;
  }

  .category-tag {
    font-size: 0.75rem;
  }
}
</style>