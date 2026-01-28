<template>
  <header>
    <div class="liquidGlass-wrapper menu"
      :style="{ '--mouse-x': menuFlash.x, '--mouse-y': menuFlash.y, '--flash-opacity': menuFlash.opacity }"
      @mousemove="handleFlashMove($event, 'menu')" @mouseleave="handleFlashLeave('menu')">
      <div class="liquidGlass-effect"></div>
      <div class="liquidGlass-tint"></div>
      <div class="liquidGlass-shine"></div>
      <div class="menu-background" ref="menuBackground"></div>

      <div class="liquidGlass-text menu-items">
        <div class="menu-logo">
          <Link href="/">
            <img :src="logoSrc" alt="Logo" class="logo-image" />
          </Link>
        </div>

        <Link v-for="(item, index) in renderedMenuItems" :key="index" :href="item.route" :data-index="index"
          @click="setActive(index)" :class="{ 'is-active': activeIndex === index }">
          {{ item.label }}
        </Link>
      </div>
    </div>

    <svg style="display: none">
      <filter id="glass-distortion" x="0%" y="0%" width="100%" height="100%" filterUnits="objectBoundingBox">
        <feTurbulence type="fractalNoise" baseFrequency="0.01 0.01" numOctaves="1" seed="5" result="turbulence" />
        <feComponentTransfer in="turbulence" result="mapped">
          <feFuncR type="gamma" amplitude="1" exponent="10" offset="0.5" />
          <feFuncG type="gamma" amplitude="0" exponent="1" offset="0" />
          <feFuncB type="gamma" amplitude="0" exponent="1" offset="0.5" />
        </feComponentTransfer>
        <feGaussianBlur in="turbulence" stdDeviation="3" result="softMap" />
        <feSpecularLighting in="softMap" surfaceScale="5" specularConstant="1" specularExponent="100"
          lighting-color="white" result="specLight">
          <fePointLight x="-200" y="-200" z="300" />
        </feSpecularLighting>
        <feComposite in="specLight" operator="arithmetic" k1="0" k2="1" k3="1" k4="0" result="litImage" />
        <feDisplacementMap in="SourceGraphic" in2="softMap" scale="200" xChannelSelector="R" yChannelSelector="G" />
      </filter>
    </svg>

    <div class="footer-menu" :class="{ 'invert-colors': isLightBackground, 'is-visible': showFooter }">
      <div class="action-buttons">
        <template v-if="user">
          <div class="user-display" @click.stop="toggleProfil" @mousedown.stop>
            <img v-if="user.profil?.PhotoProfil" :src="user.profil.PhotoProfil" class="user-avatar" />
            <div v-else class="user-avatar-placeholder">
              <i class="fa-solid fa-user"></i>
            </div>

            <div class="action-button user-name-btn">
              <span v-for="(char, index) in userNameChars" :key="`user-${index}`" class="char"
                :style="{ 'animation-delay': `${index * 0.03}s` }">
                {{ char === ' ' ? '&nbsp;' : char }}
              </span>
            </div>
          </div>
        </template>
        <button v-else @click="openLogin($event)" class="action-button login-btn">
          <span v-for="(char, index) in loginChars" :key="`login-${index}`" class="char"
            :style="{ 'animation-delay': `${index * 0.03}s` }">
            {{ char === ' ' ? '&nbsp;' : char }}
          </span>
        </button>

        <Link href="/programmes" class="action-button programs-btn">
          <span v-for="(char, index) in programsChars" :key="`programs-${index}`" class="char"
            :style="{ 'animation-delay': `${index * 0.03}s` }">
            {{ char === ' ' ? '&nbsp;' : char }}
          </span>
        </Link>

        <Link href="/coaching" class="action-button coaching-btn">
          <span v-for="(char, index) in coachingChars" :key="`coaching-${index}`" class="char"
            :style="{ 'animation-delay': `${index * 0.03}s` }">
            {{ char === ' ' ? '&nbsp;' : char }}
          </span>
        </Link>
      </div>

      <Link href="/contact" class="profile-icon-wrapper"
        :style="{ '--mouse-x': profileFlash.x, '--mouse-y': profileFlash.y, '--flash-opacity': profileFlash.opacity }"
        @mousemove="handleFlashMove($event, 'profile')" @mouseleave="handleFlashLeave('profile')">
        <div class="profile-icon"><i class="far fa-envelope"></i></div>
      </Link>

      <div class="top-icon-wrapper"
        :style="{ '--mouse-x': topFlash.x, '--mouse-y': topFlash.y, '--flash-opacity': topFlash.opacity }"
        @mousemove="handleFlashMove($event, 'top')" @mouseleave="handleFlashLeave('top')" @click.prevent="scrollToTop">
        <div class="top-icon"><i class="fa-solid fa-arrow-up icon-up"></i></div>
      </div>
    </div>

    <div class="robot-container" :class="{ 'is-visible': showFooter }">
      <spline-viewer url="https://prod.spline.design/1NFsNvYihV-D5oUI/scene.splinecode" loading-anim-type="none"
        events-target="global" style="pointer-events: none"></spline-viewer>
    </div>

    <Profil :is-open="isProfilOpen" :user="user" @close="isProfilOpen = false" />
    <LoginModal :is-open="isLoginOpen" :origin="loginButtonCoords" @close="isLoginOpen = false" />
  </header>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed, nextTick, reactive, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Profil from './Profil.vue';
import LoginModal from '../auth/LoginModal.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const menuItemsData = [
  { label: 'Accueil', route: '/' },
  { label: 'Menus', route: '/menu' },
];

const dynamicHomeLabel = computed(() => {
  const currentPath = page.url;
  if (currentPath === '/organisme') return 'L\'organisme';
  if (currentPath === '/toutcategorie') return 'Toutes les Catégories';
  if (currentPath === '/login') return 'Identification';
  if (currentPath === '/contact') return 'Contactez-moi !';
  if (currentPath === '/consultations') return 'Consultations';
  if (currentPath === '/programmes') return 'Les programmes de formation';
  if (currentPath === '/coaching') return 'Coachings';
  return 'Accueil';
});

const renderedMenuItems = computed(() => {
  return menuItemsData.map((item, index) => {
    if (index === 0) {
      return { ...item, label: dynamicHomeLabel.value };
    }
    return item;
  });
});

const findActiveIndex = () => {
  const currentPath = page.url;
  if (currentPath === '/' || currentPath === '/organisme' || currentPath === '/toutcategorie') return 0;

  const index = menuItemsData.findIndex(item => item.route !== '/' && currentPath.startsWith(item.route));
  return index !== -1 ? index : 0;
};

const activeIndex = ref(findActiveIndex());

watch([() => page.url, dynamicHomeLabel], () => {
  activeIndex.value = findActiveIndex();
  nextTick(() => {
    updateBackground();
  });
});

const loginText = 'Se connecter';
const programsText = 'Les programmes';
const loginChars = computed(() => loginText.split(''));
const programsChars = computed(() => programsText.split(''));
const userNameChars = computed(() => {
  const name = user.value?.profil?.Prenom || 'User';
  return name.split('');
});

const menuBackground = ref(null);
const logoSrc = ref('/images/logo-site-blanc.png');

const menuFlash = reactive({ x: '50%', y: '50%', opacity: 0 });
const profileFlash = reactive({ x: '50%', y: '50%', opacity: 0 });
const topFlash = reactive({ x: '50%', y: '50%', opacity: 0 });

const isLightBackground = ref(false);
const showFooter = ref(false);

const isProfilOpen = ref(false);
const isLoginOpen = ref(false);
const loginButtonCoords = reactive({ x: 0, y: 0 });

const openLogin = (event) => {
  const rect = event.currentTarget.getBoundingClientRect();
  loginButtonCoords.x = rect.left + rect.width / 2;
  loginButtonCoords.y = rect.top + rect.height / 2;
  isLoginOpen.value = true;
};

const toggleProfil = () => {
  isProfilOpen.value = !isProfilOpen.value;
};


const scrollToTop = () => {
  if (window.lenis) {
    window.lenis.scrollTo(0);
  } else {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

function handleFlashMove(event, target) {
  const rect = event.currentTarget.getBoundingClientRect();
  let state;
  if (target === 'menu') state = menuFlash;
  else if (target === 'profile') state = profileFlash;
  else if (target === 'top') state = topFlash;

  state.x = `${(event.clientX - rect.left) / rect.width * 100}%`;
  state.y = `${(event.clientY - rect.top) / rect.height * 100}%`;
  state.opacity = 1;
}

function handleFlashLeave(target) {
  if (target === 'menu') menuFlash.opacity = 0;
  else if (target === 'profile') profileFlash.opacity = 0;
  else if (target === 'top') topFlash.opacity = 0;
}

function setActive(index) {
  activeIndex.value = index;
  updateBackground();
}

function updateBackground() {
  requestAnimationFrame(() => {
    const activeItem = document.querySelector(`.menu-items > a[data-index="${activeIndex.value}"]`);
    const parent = activeItem ? activeItem.parentElement : null;
    if (activeItem && parent && menuBackground.value) {
      const rect = activeItem.getBoundingClientRect();
      const parentRect = parent.getBoundingClientRect();
      menuBackground.value.style.left = `${rect.left - parentRect.left}px`;
      menuBackground.value.style.width = `${rect.width}px`;
      menuBackground.value.style.height = `${rect.height}px`;
      menuBackground.value.classList.add('active');
      setTimeout(() => { menuBackground.value.classList.remove('active'); }, 500);
    }
  });
}

const initScrollLogic = async () => {
  const { ScrollTrigger } = await import("gsap/ScrollTrigger");

  ScrollTrigger.getAll().forEach(t => {
    if (t.vars.id === 'floating-menu-trigger') t.kill();
  });

  ScrollTrigger.create({
    id: 'floating-menu-trigger',
    trigger: "body",
    start: "100px top",
    endTrigger: "footer",
    end: "top bottom-=100px",
    onEnter: () => { showFooter.value = true; },
    onLeave: () => { showFooter.value = false; },
    onEnterBack: () => { showFooter.value = true; },
    onLeaveBack: () => { showFooter.value = false; },
    refreshPriority: -1
  });
};

let resizeObserver = null;

onMounted(async () => {
  // Load Spline Viewer script
  if (!window.SplineViewer) {
    const script = document.createElement('script');
    script.src = 'https://unpkg.com/@splinetool/viewer@1.0.28/build/spline-viewer.js';
    script.type = 'module';
    document.head.appendChild(script);
  }

  const menuItems = document.querySelector('.menu-items');
  if (menuItems) {
    resizeObserver = new ResizeObserver(() => {
      updateBackground();
    });
    resizeObserver.observe(menuItems);
  }

  setTimeout(() => {
    updateBackground();
  }, 50);

  window.addEventListener('resize', updateBackground);

  await initScrollLogic();

  // Force hide Spline logo - intense version
  const forceRemoveSplineLogo = () => {
    const viewer = document.querySelector('spline-viewer');
    if (viewer && viewer.shadowRoot) {
      const logo = viewer.shadowRoot.querySelector('#logo') ||
        viewer.shadowRoot.querySelector('a[href*="spline.design"]') ||
        viewer.shadowRoot.querySelector('.spline-watermark');
      if (logo) {
        logo.remove();
        return true;
      }
    }
    return false;
  };

  const logoCheckInterval = setInterval(forceRemoveSplineLogo, 50);

  setTimeout(() => {
    clearInterval(logoCheckInterval);
    setInterval(forceRemoveSplineLogo, 1000);
  }, 5000);

  setTimeout(() => {
    import("gsap/ScrollTrigger").then(({ ScrollTrigger }) => {
      ScrollTrigger.refresh();
    });
  }, 1000);
});

onBeforeUnmount(() => {
  if (resizeObserver) resizeObserver.disconnect();
  window.removeEventListener('resize', updateBackground);

  import("gsap/ScrollTrigger").then(({ ScrollTrigger }) => {
    ScrollTrigger.getAll().forEach(t => {
      if (t.vars.id === 'floating-menu-trigger') t.kill();
    });
  });
});

watch(() => page.url, () => {
  nextTick(async () => {
    await initScrollLogic();
    setTimeout(() => {
      import("gsap/ScrollTrigger").then(({ ScrollTrigger }) => {
        ScrollTrigger.refresh();
      });
    }, 500);
  });
});
</script>

<style scoped>
.liquidGlass-wrapper {
  position: relative;
  display: flex;
  background: transparent;
  font-weight: 600;
  overflow: hidden;
  color: black;
  cursor: pointer;
  box-shadow: 0.5px 0.5px 1px rgba(255, 255, 255, 1) inset, -0.5px -0.5px 0.5px rgba(255, 255, 255, 0.112) inset, 1px 1px 5px rgba(0, 0, 0, 0.018) inset;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 2.2);
}

.liquidGlass-effect {
  position: absolute;
  z-index: 0;
  inset: 0;
  backdrop-filter: blur(4px);
  filter: url(#glass-distortion);
  overflow: hidden;
  isolation: isolate;
}

.liquidGlass-tint {
  z-index: 1;
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(0, 0, 0, 0.3) 100%, rgba(0, 0, 0, 0.08) 100%);
}

.liquidGlass-shine {
  position: absolute;
  inset: 0;
  z-index: 2;
  overflow: hidden;
  box-shadow:
    inset 1.5px 1.5px 3px rgba(255, 255, 255, 0.221),
    inset -1.5px -1.5px 3px rgba(0, 0, 0, 0.15),
    0 0 0 1px rgba(255, 255, 255, 0.05);
}

/* Bordure lumineuse dynamique (Réactive à la souris) */
.liquidGlass-wrapper::after {
  content: "";
  position: absolute;
  inset: -1px;
  border-radius: inherit;
  /* S'adapte au container */
  padding: 1.2px;
  /* Largeur de la micro-bordure dynamique */
  background: radial-gradient(100px circle at var(--mouse-x) var(--mouse-y),
      rgba(255, 255, 255, 0.9),
      transparent 100%);
  -webkit-mask:
    linear-gradient(#fff 0 0) content-box,
    linear-gradient(#fff 0 0);
  mask:
    linear-gradient(#fff 0 0) content-box,
    linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
  z-index: 10;
  opacity: 1;
}

.menu {
  padding: 0.3rem;
  border-radius: 25px;
  position: fixed;
  top: 3vh;
  left: 50%;
  transform: translateX(-50%);
  z-index: 100;
  width: fit-content;
  display: flex;
  align-items: center;
  gap: 8px;
}

.menu:hover {
  padding: 0.5rem 0.3rem;
  border-radius: 40px;
}

.menu-items {
  display: flex;
  gap: 8px;
  position: relative;
  z-index: 3;
  align-items: center;
}

.menu-items>a {
  font-size: 1.2rem;
  color: white;
  padding: 0.8rem 1.8rem;
  border-radius: 20px;
  transition: color 0.3s ease-in;
  position: relative;
  z-index: 3;
  cursor: pointer;
  text-decoration: none;
}

.menu-logo {
  display: flex;
  align-items: center;
}

.logo-image {
  width: 8vw;
  height: 90%;
  margin-top: 1vh;
  margin-left: 1vw;
  margin-right: 2vw;
  object-fit: cover;
}

.menu-background {
  position: absolute;
  top: 50%;
  margin-left: 0.3rem;
  transform: translateY(-50%) scale(1);
  background: #202020;
  border-radius: 20px;
  z-index: 2;
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s ease-in-out, left 0.5s, width 0.5s;
  box-shadow: 0.5px 0.5px 1px rgba(255, 255, 255, 0.503) inset, -0.5px -0.5px 0.5px rgba(255, 255, 255, 0.167) inset, 1px 1px 5px rgba(0, 0, 0, 0.2) inset;
  overflow: hidden;
}

.menu-background::before {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle 100px at var(--mouse-x) var(--mouse-y), rgba(255, 255, 255, 0.104) 0%, rgba(255, 255, 255, 0) 60%);
  opacity: var(--flash-opacity, 0);
  z-index: 3;
  transition: opacity 0.3s ease;
  pointer-events: none;
}

/* Styles Footer menu */
.footer-menu {
  position: fixed;
  bottom: 20px;
  left: 50%;
  z-index: 100;
  display: flex;
  align-items: center;
  gap: 10px;
  opacity: 0;
  transform: translateX(-50%) translateY(50px) scale(0.95);
  transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.footer-menu.is-visible {
  opacity: 1;
  transform: translateX(-50%) translateY(0) scale(1);
}

.action-buttons {
  display: flex;
  gap: 15px;
  padding: 13px 20px;
  border-radius: 30px;
  background-color: #ffffff;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.action-button {
  cursor: pointer;
  color: #090909;
  font-size: 1.1rem;
  overflow: hidden;
  height: 1.3rem;
  line-height: 1.3rem;
  text-decoration: none;
  display: inline-block;
  background: none;
  border: none;
  padding: 0;
  font-family: inherit;
}

.user-name-btn {
  font-weight: 500;
}

@keyframes letter-fall {
  0% {
    transform: translateY(0);
    opacity: 1;
  }

  49% {
    transform: translateY(100%);
    opacity: 0;
  }

  50% {
    transform: translateY(-100%);
    opacity: 0;
  }

  100% {
    transform: translateY(0);
    opacity: 1;
  }
}

.action-button .char {
  display: inline-block;
  transition: color 0.3s ease;
}

.action-button:hover .char {
  animation: letter-fall 0.7s forwards;
  color: #8A38F5;
}

.profile-icon-wrapper,
.top-icon-wrapper {
  position: relative;
  width: 55px;
  height: 55px;
  border-radius: 50%;
  background-color: #202020;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  overflow: hidden;
  text-decoration: none;
}

.profile-icon-wrapper::before,
.top-icon-wrapper::before {
  content: '';
  position: absolute;
  left: var(--mouse-x, 50%);
  top: var(--mouse-y, 50%);
  width: 200%;
  height: 200%;
  background: radial-gradient(circle at center, rgba(255, 255, 255, 0.497) 0%, rgba(255, 255, 255, 0) 60%);
  opacity: var(--flash-opacity, 0);
  transform: translate(-50%, -50%);
  transition: opacity 0.3s ease;
  z-index: 0;
}

.profile-icon,
.top-icon {
  font-size: 1.3rem;
  color: #ffffff;
  z-index: 2;
  position: relative;
}

.profile-icon-wrapper:hover {
  background-color: #8A38F5;
}

.top-icon-wrapper:hover {
  background-color: #1A888D;
}

.user-display {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.user-avatar-placeholder {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #f0f0f0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  color: #333;
}

.robot-container {
  width: 120px;
  height: 120px;
  position: fixed;
  bottom: 0px;
  right: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 100;
  opacity: 0;
  transform: translateY(50px) scale(0.8);
  transition: all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
  pointer-events: none;
  /* Capture only if needed, but here it's decorative */
}

.robot-container.is-visible {
  opacity: 1;
  transform: translateY(0) scale(1);
  pointer-events: auto;
}

.robot-container:hover {
  transform: scale(1.05) translateY(-5px);
}

spline-viewer {
  width: 100%;
  height: 100%;
  clip-path: inset(0 0 15px 0);
  /* Crop the bottom 15px where the logo lives */
}

.action-buttons {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 10px 20px;
  border-radius: 30px;
  background-color: #ffffff;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.action-button:hover .char {
  animation: letter-fall 0.7s forwards;
  color: #8A38F5;
}
</style>