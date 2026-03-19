<template>
  <header>
    <div class="liquidGlass-wrapper menu" :class="{ 'menu-shrunk-mobile': showFooter }"
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
          @click="setActive(index)" :class="{ 'is-active': activeIndex === index, 'hide-on-mobile': index === 0 }">
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
      <div class="robot-messages-area">
        <ModalAssistant :is-open="showNotif" :notifications="notifications" @close.stop="showNotif = false"
          @refresh="fetchNotifications" />

        <div class="robot-bubble hide-on-mobile" v-if="!showNotif">
          {{ $t('Header.RobotBubble') }}
        </div>
      </div>

      <div v-if="!isRobotReady" class="robot-loader"></div>
      <div class="spline-wrapper" @click.stop="showNotif = !showNotif"
        style="pointer-events: auto; cursor: pointer; display: flex; align-items: center; justify-content: center;">
        <spline-viewer url="https://prod.spline.design/1NFsNvYihV-D5oUI/scene.splinecode" loading-anim-type="none"
          events-target="global" @load="() => { isRobotReady = true; sessionStorage.setItem('robot_loaded', 'true'); }"
          :class="{ 'is-ready': isRobotReady }" class="robot-spline-viewer"
          style="pointer-events: none;"></spline-viewer>

        <transition name="scale">
          <div v-if="notifications.length > 0" class="robot-notif-badge">
            {{ notifications.length }}
          </div>
        </transition>
      </div>
    </div>

    <div class="lang-switcher-wrapper" :class="{
      'is-visible': isMobile ? showFooter : true,
      'menu-open': isLangMenuOpen,
      'desktop-view': !isMobile,
      'icon-hidden': isMobile && mobileIconState === 'cart'
    }" :style="{ '--mouse-x': langFlash.x, '--mouse-y': langFlash.y, '--flash-opacity': langFlash.opacity }"
      @mousemove="handleFlashMove($event, 'lang')" @mouseleave="handleFlashLeave('lang')"
      @click.stop="isMobile ? null : (isLangMenuOpen = !isLangMenuOpen)">

      <div class="lang-icon-display">
        <img :src="availableLanguages.find(l => l.code === currentLocale)?.gif" class="lang-gif-main" />
      </div>

      <div class="lang-dropdown" v-if="isLangMenuOpen">
        <div v-for="lang in availableLanguages" :key="lang.code" class="lang-option gif-bg"
          :style="{ backgroundImage: `url(${lang.gif})` }" :class="{ 'active': currentLocale === lang.code }"
          @click.stop="changeLocale(lang.code)">
          <span class="lang-name">{{ lang.name }}</span>
        </div>
      </div>
    </div>

    <div @click="isMobile ? null : (!user ? openLogin($event) : router.visit('/panier'))"
      class="cart-icon-wrapper-fixed profile-icon-wrapper"
      :class="{ 'is-visible': showFooter, 'icon-hidden': mobileIconState === 'lang' && isMobile }"
      :style="{ '--mouse-x': cartFlash.x, '--mouse-y': cartFlash.y, '--flash-opacity': cartFlash.opacity }"
      @mousemove="handleFlashMove($event, 'cart')" @mouseleave="handleFlashLeave('cart')" style="cursor: pointer;">

      <div class="profile-icon">
        <i class="fas fa-shopping-basket"></i>
      </div>
      <span class="cart-label hide-on-mobile">{{ $t('Cart') }}</span>
    </div>

    <div v-if="isMobile && showFooter" class="mobile-icon-container" @touchstart="handleTouchStart"
      @touchend="handleTouchEnd">

      <!-- Swipe Hint Bubble -->
      <Transition name="fade-scale">
        <div v-if="showSwipeHint" class="swipe-hint-bubble">
          {{ $t('Header.SwipeHint') }}
        </div>
      </Transition>

      <div class="mobile-icon-active-area">
        <div v-show="mobileIconState === 'cart'" class="mobile-direct-action"
          @click.stop="!user ? openLogin($event) : router.visit('/panier')"></div>
        <div v-show="mobileIconState === 'lang'" class="mobile-direct-action"
          @click.stop="isLangMenuOpen = !isLangMenuOpen"></div>
      </div>
    </div>

    <Profil :is-open="isProfilOpen" :user="user" @close="isProfilOpen = false" />
    <LoginModal :is-open="isLoginOpen" :origin="loginButtonCoords" @close="isLoginOpen = false" />
  </header>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed, nextTick, reactive, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import Profil from './Profil.vue';
import LoginModal from '../auth/LoginModal.vue';
import ModalAssistant from '../ui/ModalAssistant.vue';
import gsap from 'gsap';

const page = usePage();
const $t = (key) => page.props.translations?.[key] || key;
const user = computed(() => page.props.auth.user);
const isRobotReady = ref(typeof window !== 'undefined' && sessionStorage.getItem('robot_loaded') === 'true');
const showNotif = ref(false);
const notifications = ref([]);

const fetchNotifications = async () => {
  if (!user.value) {
    notifications.value = [];
    return;
  }
  try {
    const response = await fetch('/notifications', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    if (response.ok) {
      const data = await response.json();
      notifications.value = data;
    } else if (response.status === 401) {
      notifications.value = [];
    }
  } catch (error) {
    console.error("Erreur lors du chargement des notifications", error);
    notifications.value = [];
  }
};

onMounted(() => {
  if (user.value) {
    fetchNotifications();
    const interval = setInterval(fetchNotifications, 60000);
    onBeforeUnmount(() => clearInterval(interval));
  }
});

watch(user, (newUser) => {
  if (newUser) fetchNotifications();
});

if (typeof window !== 'undefined' && !window.customElements.get('spline-viewer')) {
  const script = document.createElement('script');
  script.src = 'https://cdn.jsdelivr.net/npm/@splinetool/viewer@1.0.28/build/spline-viewer.js';
  script.type = 'module';
  script.async = true;
  document.head.appendChild(script);
}

const menuItemsData = computed(() => [
  { label: 'Home', route: '/' },
  { label: 'Menus', route: '/menu' },
]);

const dynamicHomeLabel = computed(() => {
  const currentPath = page.url;
  if (currentPath === '/organisme') return 'Header.LOrganisme';
  if (currentPath === '/toutcategorie') return 'Header.ToutesLesCategories';
  if (currentPath === '/login') return 'Identification';
  if (currentPath === '/contact') return 'Header.ContactezMoi';
  if (currentPath === '/consultations') return 'Consultations';
  if (currentPath === '/programmes') return 'Header.LesProgrammesDeFormation';
  if (currentPath.includes('/programmes/')) return 'Header.ProgrammeDetail';
  if (currentPath === '/coaching') return 'Coachings';
  if (currentPath.includes('/seminaires')) return 'Header.SeminaireDetail';
  if (currentPath === '/temoignages') return 'Testimonials';
  if (currentPath === '/offres') return 'Offers';
  if (currentPath === '/live') return 'Live';
  if (currentPath === '/panier') return 'MyCart';
  if (currentPath === '/articles-conseils') return 'Header.ArticlesTips';
  if (currentPath === '/reservations') return 'MyReservations';
  if (currentPath === '/politique-de-confidentialite') return 'PrivacyPolicy';

  return 'Home';
});

const renderedMenuItems = computed(() => {
  return menuItemsData.value.map((item, index) => {
    if (index === 0) {
      return { ...item, label: $t(dynamicHomeLabel.value) };
    }
    return { ...item, label: $t(item.label) };
  });
});

const findActiveIndex = () => {
  const currentPath = page.url;
  if (currentPath === '/' || currentPath === '/organisme' || currentPath === '/toutcategorie') return 0;

  const index = menuItemsData.value.findIndex(item => item.route !== '/' && currentPath.startsWith(item.route));
  return index !== -1 ? index : 0;
};

const activeIndex = ref(findActiveIndex());

watch([() => page.url, dynamicHomeLabel], () => {
  activeIndex.value = findActiveIndex();
  nextTick(() => {
    updateBackground();
  });
});

// Login/Programs animations setup
const loginChars = computed(() => String(page.props.translations['Login'] || 'Se connecter').split(''));
const programsChars = computed(() => String(page.props.translations['Programmes'] || 'Les programmes').split(''));
const userNameChars = computed(() => {
  const name = user.value?.profil?.Prenom || 'User';
  return name.split('');
});

const menuBackground = ref(null);
const logoSrc = ref('/images/logo-site-blanc.png');

const menuFlash = reactive({ x: '50%', y: '50%', opacity: 0 });
const profileFlash = reactive({ x: '50%', y: '50%', opacity: 0 });
const topFlash = reactive({ x: '50%', y: '50%', opacity: 0 });
const cartFlash = reactive({ x: '50%', y: '50%', opacity: 0 });

const isLightBackground = ref(false);
const showFooter = ref(false);
const isLangMenuOpen = ref(false);
const mobileIconState = ref('cart'); // 'cart' or 'lang'
const isMobile = ref(false);
const showSwipeHint = ref(false);
let mobileIconInterval = null;
let swipeHintInterval = null;

const toggleMobileIcon = () => {
  const newState = mobileIconState.value === 'cart' ? 'lang' : 'cart';

  const cartElem = document.querySelector('.cart-icon-wrapper-fixed');
  const langElem = document.querySelector('.lang-switcher-wrapper');

  const currentElem = mobileIconState.value === 'cart' ? cartElem : langElem;
  const nextElem = newState === 'cart' ? cartElem : langElem;

  if (currentElem && nextElem) {
    gsap.to(currentElem, {
      y: 0,
      opacity: 0,
      scale: 0.7,
      duration: 0.6,
      ease: "power2.inOut"
    });

    mobileIconState.value = newState;
    nextTick(() => {
      gsap.fromTo(nextElem,
        { y: 0, opacity: 0, scale: 0.7 },
        { y: 0, opacity: 1, scale: 1.1, duration: 0.6, ease: "power2.out", delay: 0.1, clearProps: "y,opacity,scale" }
      );
    });
  } else {
    mobileIconState.value = newState;
  }
};

const manualToggle = () => {
  if (mobileIconInterval) {
    clearInterval(mobileIconInterval);
    startMobileIconTimer(); // Reset timer on manual interaction
  }
  toggleMobileIcon();
};

let touchStartX = 0;
let touchStartY = 0;

const handleTouchStart = (e) => {
  touchStartX = e.touches[0].clientX;
  touchStartY = e.touches[0].clientY;
};

const handleTouchEnd = (e) => {
  const dx = e.changedTouches[0].clientX - touchStartX;
  const dy = e.changedTouches[0].clientY - touchStartY;

  if (Math.abs(dx) > 30 || Math.abs(dy) > 30) {
    manualToggle();
  }
};

const startMobileIconTimer = () => {
  mobileIconInterval = setInterval(() => {
    if (isMobile.value && showFooter.value && !isLangMenuOpen.value) {
      toggleMobileIcon();
    }
  }, 4000);
};

const startSwipeHintTimer = () => {
  const triggerHint = () => {
    showSwipeHint.value = true;
    setTimeout(() => {
      showSwipeHint.value = false;
    }, 7000);
  };

  triggerHint();
  swipeHintInterval = setInterval(triggerHint, 60000);
};

const currentLocale = computed(() => page.props.locale || 'fr');
const availableLanguages = [
  { code: 'fr', name: 'Français', gif: 'https://res.cloudinary.com/dzgdjei0h/image/upload/v1772994723/france-flag-gif_lqygo7.gif' },
  { code: 'en', name: 'English', gif: 'https://res.cloudinary.com/dzgdjei0h/image/upload/v1772798239/United_Kingdom_United_Kingdom_Flag_GIF_United_Kingdom_United_Kingdom_Flag_Flag_Waving_discover_and_share_GIFs_1_ennasn.gif' },
  { code: 'mg', name: 'Malagasy', gif: 'https://res.cloudinary.com/dzgdjei0h/image/upload/v1772798282/Madagascar_Flag_Gif_GIF_-_Madagascar_Flag_Gif_Africa_-_Discover_Share_GIFs_1_xfbaa4.gif' }
];

const changeLocale = (locale) => {
  router.post('/locale', { locale }, {
    preserveScroll: true,
    onSuccess: () => {
      isLangMenuOpen.value = false;
      window.location.reload();
    }
  });
};

const langFlash = reactive({ x: '50%', y: '50%', opacity: 0 });

watch(showFooter, () => {
  nextTick(() => {
    updateBackground();
    setTimeout(updateBackground, 600);
  });
});

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
  else if (target === 'cart') state = cartFlash;
  else if (target === 'lang') state = langFlash;

  state.x = `${(event.clientX - rect.left) / rect.width * 100}%`;
  state.y = `${(event.clientY - rect.top) / rect.height * 100}%`;
  state.opacity = 1;
}

function handleFlashLeave(target) {
  if (target === 'menu') menuFlash.opacity = 0;
  else if (target === 'profile') profileFlash.opacity = 0;
  else if (target === 'top') topFlash.opacity = 0;
  else if (target === 'cart') cartFlash.opacity = 0;
  else if (target === 'lang') langFlash.opacity = 0;
}

function setActive(index) {
  activeIndex.value = index;
  updateBackground();
}

function updateBackground() {
  requestAnimationFrame(() => {
    const activeItem = document.querySelector(`.menu-items > a[data-index="${activeIndex.value}"]`);
    if (activeItem && menuBackground.value) {
      const parent = menuBackground.value.parentElement;
      const rect = activeItem.getBoundingClientRect();
      const parentRect = parent.getBoundingClientRect();

      const isMobile = window.innerWidth <= 768;
      const xOffset = isMobile ? 0 : 0;

      menuBackground.value.style.left = `${rect.left - parentRect.left + xOffset}px`;
      menuBackground.value.style.width = `${rect.width}px`;
      menuBackground.value.style.height = `${rect.height}px`;
      menuBackground.value.classList.add('active');
      setTimeout(() => { menuBackground.value.classList.remove('active'); }, 500);
    }
  });
}

const closeNotifOutside = (e) => {
  if (showNotif.value && !e.target.closest('.robot-container')) {
    showNotif.value = false;
  }
};

const initScrollLogic = async () => {
  const { ScrollTrigger } = await import("gsap/ScrollTrigger");
  gsap.registerPlugin(ScrollTrigger);

  ScrollTrigger.getAll().forEach(t => {
    if (t.vars.id === 'floating-menu-trigger' || t.vars.id === 'mobile-icon-switcher') t.kill();
  });

  const isMobileView = window.innerWidth <= 768;
  isMobile.value = isMobileView;
  const startThreshold = isMobileView ? "200px top" : "100px top";

  ScrollTrigger.create({
    id: 'floating-menu-trigger',
    trigger: "body",
    start: startThreshold,
    endTrigger: ".footer",
    end: "top bottom-=100px",
    onToggle: (self) => { showFooter.value = self.isActive; },
    onEnter: () => { showFooter.value = true; },
    onLeave: () => { showFooter.value = false; },
    onEnterBack: () => { showFooter.value = true; },
    onLeaveBack: () => { showFooter.value = false; },
    refreshPriority: -1
  });

  if (isMobileView) {
    startMobileIconTimer();
  }
};

let resizeObserver = null;

const forceRemoveSplineLogo = () => {
  const viewers = document.querySelectorAll('spline-viewer');
  viewers.forEach(viewer => {
    if (viewer && viewer.shadowRoot) {
      const logo = viewer.shadowRoot.querySelector('#logo') ||
        viewer.shadowRoot.querySelector('a[href*="spline.design"]') ||
        viewer.shadowRoot.querySelector('.spline-watermark') ||
        viewer.shadowRoot.querySelector('#spline-logo') ||
        viewer.shadowRoot.querySelector('div[style*="position: absolute"][style*="bottom: 0px"]') ||
        viewer.shadowRoot.querySelector('div[style*="z-index: 10000"]') ||
        viewer.shadowRoot.querySelector('div[style*="pointer-events: none"] > a');
      if (logo) {
        logo.style.display = 'none';
        logo.style.visibility = 'hidden';
        logo.style.opacity = '0';
        logo.remove();
      }
    }
  });
};

onMounted(async () => {
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

  window.addEventListener('resize', () => {
    updateBackground();
    isMobile.value = window.innerWidth <= 768;
    if (isMobile.value) {
      if (!swipeHintInterval) startSwipeHintTimer();
    }
  });

  // Initial check
  isMobile.value = window.innerWidth <= 768;
  if (isMobile.value) {
    startSwipeHintTimer();
  }

  window.addEventListener('click', (e) => {
    closeNotifOutside(e);
    if (isLangMenuOpen.value && !e.target.closest('.lang-switcher-wrapper')) {
      isLangMenuOpen.value = false;
    }
  });

  setTimeout(() => {
    isRobotReady.value = true;
  }, 6000);

  await initScrollLogic();

  const logoCheckInterval = setInterval(forceRemoveSplineLogo, 200);
  window.logoCheckInterval = logoCheckInterval;

  setTimeout(() => {
    clearInterval(window.logoCheckInterval);
    window.logoCheckInterval = setInterval(forceRemoveSplineLogo, 1000);
  }, 10000);

  setTimeout(() => {
    import("gsap/ScrollTrigger").then(({ ScrollTrigger }) => {
      ScrollTrigger.refresh();
    });
  }, 1000);
});

onBeforeUnmount(() => {
  if (mobileIconInterval) clearInterval(mobileIconInterval);
  if (swipeHintInterval) clearInterval(swipeHintInterval);
  if (resizeObserver) resizeObserver.disconnect();
  window.removeEventListener('resize', updateBackground);
  window.removeEventListener('click', closeNotifOutside);

  import("gsap/ScrollTrigger").then(({ ScrollTrigger }) => {
    ScrollTrigger.getAll().forEach(t => {
      if (t.vars.id === 'floating-menu-trigger') t.kill();
    });
  });
});

watch(() => page.url, () => {
  showFooter.value = false;
  showNotif.value = false;
  nextTick(async () => {
    forceRemoveSplineLogo();
    setTimeout(async () => {
      await initScrollLogic();
    }, 100);
    setTimeout(() => {
      import("gsap/ScrollTrigger").then(({ ScrollTrigger }) => {
        ScrollTrigger.refresh();
      });
    }, 500);
    setTimeout(() => {
      import("gsap/ScrollTrigger").then(({ ScrollTrigger }) => {
        ScrollTrigger.refresh();
      });
    }, 1200);
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

.liquidGlass-wrapper::after {
  content: "";
  position: absolute;
  inset: -1px;
  border-radius: inherit;
  padding: 1.2px;
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

.flag-fr {
  background-image: url('https://flagcdn.com/w40/fr.png');
}

.flag-en {
  background-image: url('https://flagcdn.com/w40/gb.png');
}

.flag-mg {
  background-image: url('https://flagcdn.com/w40/mg.png');
}

.flag-fr,
.flag-en,
.flag-mg {
  display: inline-block;
  width: 24px;
  height: 24px;
  background-size: cover;
  background-position: center;
  border-radius: 50%;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
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

.lang-flag-current {
  display: flex !important;
  align-items: center;
  justify-content: center;
  transition: transform 0.3s ease;
}

.menu-logo {
  display: flex;
  align-items: center;
}

.logo-image {
  width: 8vw;
  min-width: 80px;
  height: auto;
  margin-top: 1vh;
  margin-left: 1vw;
  margin-right: 2vw;
  object-fit: contain;
}

.menu-background {
  position: absolute;
  top: 50%;
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

.lang-flag {
  display: flex !important;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform 0.3s ease;
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

.cart-icon-wrapper-fixed {
  position: fixed;
  bottom: 25px;
  left: 5vw;
  z-index: 1000;
  opacity: 0;
  transform: translateY(50px) scale(0.8);
  transition: opacity 0.5s ease, transform 0.8s cubic-bezier(0.34, 1.56, 0.64, 1), width 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), background 0.3s ease;
  pointer-events: none;
  width: 55px;
  height: 55px;
  border-radius: 30px !important;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  padding-left: 15px;
  overflow: hidden;
  background: linear-gradient(135deg, #1a73e8, #8A38F5);
}

.cart-label {
  color: #ffffff;
  font-weight: 500;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  pointer-events: none;
  opacity: 0;
  width: 0;
  transform: translateX(-20px);
  transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
  white-space: nowrap;
}

.cart-icon-wrapper-fixed:hover {
  width: 125px;
  background: linear-gradient(135deg, #1a73e8, #8A38F5) !important;
}

.cart-icon-wrapper-fixed:hover .cart-label {
  opacity: 1;
  width: auto;
  margin-left: 12px;
  transform: translateX(0);
}

.cart-icon-wrapper-fixed.is-visible {
  opacity: 1;
  transform: translateY(0) scale(1);
  pointer-events: auto;
}

.cart-icon-wrapper-fixed.icon-hidden {
  opacity: 0 !important;
  pointer-events: none !important;
  transform: translateY(20px) scale(0.8) !important;
}

.lang-switcher-wrapper {
  position: fixed;
  top: 25px;
  right: 5vw;
  z-index: 1001;
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background-color: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  overflow: visible;
  transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
  box-shadow: none;
  font-size: 1.6rem !important;
}

.lang-gif-main {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.lang-flag-current {
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (min-width: 769px) {
  .lang-switcher-wrapper {
    opacity: 1 !important;
    transform: none !important;
    pointer-events: auto !important;
  }
}

.lang-switcher-wrapper.desktop-view {
  top: 25px;
  right: 5vw;
}

.lang-icon-display {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  color: white;
  font-size: 1.3rem;
}

.lang-label {
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 1px;
}

.lang-dropdown {
  position: absolute;
  top: calc(100% + 15px);
  right: 0;
  background: rgba(32, 32, 32, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 5px;
  width: 160px;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  flex-direction: column;
  gap: 5px;
  animation: dropdown-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  z-index: 1002;
}

@keyframes dropdown-in {
  from {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
  }

  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.lang-option {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 12px 15px;
  border-radius: 15px;
  color: white;
  transition: all 0.3s ease;
  cursor: pointer;
  background-size: cover;
  background-position: center;
  position: relative;
  overflow: hidden;
}

.lang-option::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.3);
  transition: background 0.3s ease;
}

.lang-option:hover::before {
  background: rgba(138, 56, 245, 0.3);
}

.lang-option.active::before {
  background: rgba(138, 56, 245, 0.3);
}

.lang-flag {
  font-size: 1.2rem;
}

.lang-name {
  font-size: 0.95rem;
  font-weight: 700;
  z-index: 1;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

@media (max-width: 768px) {
  .lang-switcher-wrapper {
    position: fixed;
    top: 22px;
    right: 5%;
    width: 60px;
    height: 60px;
    border-radius: 50% !important;
    display: flex;
    align-items: center;
    justify-content: center !important;
    padding: 0 !important;
    opacity: 0;
    transform: translateY(-50px) scale(0.8);
    pointer-events: none;
    background-color: transparent !important;
    box-shadow: none !important;
  }

  .lang-switcher-wrapper.is-visible {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
  }

  .lang-switcher-wrapper.icon-hidden {
    opacity: 0 !important;
    pointer-events: none !important;
    transform: translateY(20px) scale(0.8) !important;
  }
}

.robot-container {
  width: auto;
  height: auto;
  position: fixed;
  bottom: 10px;
  right: 30px;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  justify-content: flex-end;
  cursor: pointer;
  z-index: 1000;
  opacity: 0;
  transform: translateY(50px) scale(0.8);
  transition: all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
  pointer-events: none;
}


.robot-container.is-visible,
.robot-container.always-visible-mobile {
  opacity: 1;
  transform: translateY(0) scale(1);
  pointer-events: auto;
}

@media (min-width: 769px) {
  .robot-container.always-visible-mobile {
    opacity: 0;
    transform: translateY(50px) scale(0.8);
    pointer-events: none;
  }

  .robot-container.is-visible {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
  }
}

.robot-container:hover {
  transform: scale(1.02) translateY(-5px);
}

.robot-container:hover .robot-bubble {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}

.robot-messages-area {
  display: grid;
  grid-template-areas: "msg";
  align-items: end;
  justify-items: end;
  width: auto;
  pointer-events: auto;
}

.robot-messages-area>* {
  grid-area: msg;
}

.robot-bubble {
  background: #8A38F5;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 12px 18px;
  border-radius: 18px 18px 0 18px;
  color: white;
  font-size: 1rem;
  font-weight: 400;
  width: 220px;
  text-align: center;
  margin-right: 4vw;
  line-height: 1.2;
  box-shadow: 0 10px 25px rgba(138, 56, 245, 0.3);
  opacity: 0;
  pointer-events: auto;
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  transform: translateY(10px);
  position: relative;
  z-index: 10;
}

.robot-bubble::after {
  content: '';
  position: absolute;
  top: 100%;
  right: 0;
  border-left: 12px solid transparent;
  border-right: 0px solid transparent;
  border-top: 12px solid #8A38F5;
}

spline-viewer {
  width: 120px;
  height: 120px;
  clip-path: inset(0 0 15px 0);
  opacity: 0;
  transition: opacity 1s ease;
}

spline-viewer.is-ready {
  opacity: 1;
}

.robot-loader {
  position: absolute;
  width: 30px;
  height: 30px;
  border: 3px solid rgba(138, 56, 245, 0.2);
  border-top-color: #8A38F5;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  z-index: 10;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.robot-notif-badge {
  position: absolute;
  top: 17vh;
  right: 2vw;
  background: linear-gradient(135deg, #1a73e8, #8A38F5);
  color: white;
  font-size: 0.75rem;
  font-weight: 800;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(26, 115, 232, 0.4);
  z-index: 20;
}

.scale-enter-active,
.scale-leave-active {
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s;
}

.scale-enter-from,
.scale-leave-to {
  transform: scale(0);
  opacity: 0;
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

@media (max-width: 1024px) {
  .menu-items>a {
    font-size: 1rem;
    padding: 0.6rem 1.2rem;
  }

  .logo-image {
    min-width: 100px;
  }
}

@media (max-width: 768px) {
  .liquidGlass-wrapper {
    display: block;
  }

  .menu {
    top: 15px;
    width: 90%;
    max-width: none;
    justify-content: center;
    border-radius: 30px;
    transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  .menu.menu-shrunk-mobile {
    width: 75%;
    left: 5%;
    transform: none;
  }

  .menu-items {
    width: 100%;
    gap: 0;
    justify-content: space-between;
  }

  .user-avatar,
  .user-avatar-placeholder {
    display: none !important;
  }

  .menu-items>a {
    font-size: 1.5rem;
    padding: 0.8rem 1rem;
    flex: none;
    text-align: center;
    white-space: nowrap;
  }

  .menu-items>a.hide-on-mobile {
    display: none;
  }

  .footer-menu.is-visible {
    opacity: 1;
    transform: translateX(-50%) translateY(0) scale(1);
  }

  .footer-menu {
    bottom: 10px;
    left: 50%;
    right: auto;
    transform: translateX(-50%) translateY(30px);
    padding: 0;
    gap: 3px;
    width: auto;
    max-width: 98vw;
    justify-content: center;
    opacity: 0;
    transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  .logo-image {
    width: 120px;
    min-width: 120px;
    margin-right: 8px;
  }

  .action-buttons {
    padding: 10px 20px !important;
    gap: 15px;
    flex: none;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
  }

  .action-button {
    font-size: 1.5rem;
    height: 1.5rem;
    line-height: 1.5rem;
    font-weight: 400;
  }

  .profile-icon-wrapper,
  .top-icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .profile-icon,
  .top-icon {
    font-size: 2rem;
  }

  :is(.cart-icon-wrapper-fixed, .lang-switcher-wrapper) :is(.profile-icon, .lang-gif-main) {
    font-size: 1.5rem;
  }

  .lang-switcher-wrapper {
    position: fixed;
    top: 22px;
    right: 5%;
    width: 50px;
    height: 50px;
    border-radius: 50% !important;
    display: flex;
    align-items: center;
    justify-content: center !important;
    padding: 0 !important;
    opacity: 0;
    transform: translateY(-50px) scale(0.8);
    pointer-events: none;
    background-color: transparent !important;
    box-shadow: none !important;
  }

  .cart-icon-wrapper-fixed {
    top: 22px;
    bottom: auto;
    right: 5%;
    left: auto !important;
    width: 50px;
    height: 50px;
    border-radius: 50% !important;
    display: flex;
    align-items: center;
    justify-content: center !important;
    padding: 0 !important;
    background: linear-gradient(135deg, #1a73e8, #8A38F5) !important;
  }

  .lang-gif-main {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
  }

  .robot-container {
    right: -5px;
    bottom: 50px;
    transform: none;
  }

  .robot-container.is-visible,
  .robot-container.always-visible-mobile {
    opacity: 1;
    transform: none;
    pointer-events: auto;
  }

  .robot-bubble.hide-on-mobile {
    display: none;
  }

  .robot-spline-viewer,
  .robot-messages-area {
    pointer-events: auto;
  }

  .robot-notif-badge {
    position: absolute !important;
    top: -6vh !important;
    right: 5px !important;
    min-width: 20px !important;
    height: 20px !important;
    font-size: 0.75rem !important;
    padding: 2px !important;
    border: none !important;
    display: flex !important;
    z-index: 9999 !important;
    background: linear-gradient(135deg, #1a73e8, #8A38F5) !important;
    opacity: 1 !important;
    transform: none !important;
  }

  .mobile-icon-container {
    position: fixed;
    top: 22px;
    right: 5%;
    width: 60px;
    height: 60px;
    z-index: 1002;
    cursor: pointer;
  }

  .mobile-icon-active-area {
    width: 100%;
    height: 100%;
    position: relative;
  }

  .swipe-hint-bubble {
    position: absolute;
    right: -5px;
    top: 75px;
    background: linear-gradient(135deg, #202020 0%, #1A888D 50%, #F7B455 100%);
    color: white;
    padding: 10px 18px;
    border-radius: 20px;
    font-size: 0.95rem;
    font-weight: 600;
    white-space: nowrap;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
    pointer-events: none;
    z-index: 1005;
    animation: floatBubbleHint 2.5s ease-in-out infinite;
  }

  .swipe-hint-bubble::after {
    content: '';
    position: absolute;
    top: -8px;
    right: 28px;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
  }

  @keyframes floatBubbleHint {

    0%,
    100% {
      transform: translateY(0);
    }

    50% {
      transform: translateY(-6px);
    }
  }

  .fade-scale-enter-active,
  .fade-scale-leave-active {
    transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  .fade-scale-enter-from {
    opacity: 0;
    transform: translateY(-15px) scale(0.8) !important;
  }

  .fade-scale-leave-to {
    opacity: 0;
    transform: translateY(15px) scale(0.8) !important;
  }

  .mobile-direct-action {
    position: absolute;
    inset: 0;
    z-index: 10;
  }

  .robot-notif-badge {
    position: absolute !important;
    top: 4vh !important;
    right: 10vw !important;
    min-width: 22px !important;
    height: 22px !important;
    font-size: 0.85rem !important;
    background: linear-gradient(135deg, #1a73e8, #8A38F5) !important;
    display: flex !important;
    opacity: 1 !important;
    transform: none !important;
    z-index: 9999 !important;
    box-shadow: 0 4px 10px rgba(138, 56, 245, 0.5) !important;
  }
}

@media (max-width: 480px) {
  .menu {
    width: 95%;
    border-radius: 20px;
    gap: 4px;
  }

  .menu-items>a {
    font-size: 0.95rem;
    padding: 0.8rem 1rem;
  }

  .logo-image {
    min-width: 60px;
    margin-right: 2vw;
  }

  .action-buttons {
    padding: 12px 15px;
    gap: 8px;
  }

  .action-button {
    font-size: 0.95rem;
    height: 1.4rem;
    line-height: 1.4rem;
  }

  .profile-icon-wrapper,
  .top-icon-wrapper {
    width: 50px;
    height: 50px;
  }

  .profile-icon,
  .top-icon {
    font-size: 1.1rem;
  }

  spline-viewer {
    width: 130px;
    height: 130px;
  }

  .robot-bubble {
    bottom: 120px;
    font-size: 0.8rem;
  }
}
</style>