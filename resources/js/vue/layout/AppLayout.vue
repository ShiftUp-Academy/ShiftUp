<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppHeader from '../components/headerfooter/AppHeader.vue';
import AppFooter from '../components/headerfooter/AppFooter.vue';
import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

gsap.registerPlugin(ScrollTrigger);

import IntroLoader from '../components/ui/IntroLoader.vue';
import PageTransitionOverlay from '../components/ui/PageTransitionOverlay.vue';

const toast = useToast();
const page = usePage();

// Watch for errors and flash messages
watch(() => page.props.errors, (errors) => {
  if (errors && Object.keys(errors).length > 0) {
    Object.values(errors).forEach(error => {
      toast.add({ severity: 'error', summary: 'Erreur', detail: error, life: 5000 });
    });
  }
}, { immediate: true, deep: true });

watch(() => page.props.flash, (flash) => {
  if (flash?.success) {
    toast.add({ severity: 'success', summary: 'Succès', detail: flash.success, life: 3000 });
  }
  if (flash?.error) {
    toast.add({ severity: 'error', summary: 'Erreur', detail: flash.error, life: 5000 });
  }
  if (flash?.warning) {
    toast.add({ severity: 'warn', summary: 'Attention', detail: flash.warning, life: 4000 });
  }
  if (flash?.info) {
    toast.add({ severity: 'info', summary: 'Information', detail: flash.info, life: 3000 });
  }
  if (flash?.Bonjour) {
    toast.add({ severity: 'success', summary: 'Bonjour', detail: flash.Bonjour, life: 4000 });
  }
}, { deep: true });

// --- Smooth Scroll (Lenis) ---
let lenis;

onMounted(() => {
  lenis = new Lenis({
    duration: 1.2,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    smoothWheel: true,
  });

  lenis.on('scroll', ScrollTrigger.update);
  window.lenis = lenis;

  gsap.ticker.add((time) => {
    lenis.raf(time * 1000);
  });

  gsap.ticker.lagSmoothing(0);

  setupScrollAnimations();
});

onUnmounted(() => {
  if (lenis) {
    lenis.destroy();
    gsap.ticker.remove((time) => lenis.raf(time * 1000));
  }
  if (window.scrollTimer) clearTimeout(window.scrollTimer);
  document.body.classList.remove('is-scrolling'); // Cleanup

  if (mutationObserver) mutationObserver.disconnect();
  if (resizeObserver) resizeObserver.disconnect();
  if (unbindRouter) unbindRouter();
  if (unbindTransitionStart) unbindTransitionStart();
  if (unbindTransitionFinish) unbindTransitionFinish();
});

const mainRef = ref(null);
const cursorRef = ref(null);
const footerRef = ref(null);
const transitionRef = ref(null);
const showIntro = ref(false);
let mutationObserver;
let resizeObserver;
let unbindRouter;
let unbindTransitionStart;
let unbindTransitionFinish;

const setupScrollAnimations = () => {
  if (!mainRef.value) return;

  const animateNode = (node) => {
    if (node.nodeType !== 1) return; // Ensure element node

    if (node.dataset.hasScrollAnim) return;
    if (node.closest('[data-lenis-prevent]') || node.closest('.consultation-lists') || node.closest('.no-global-reveal') || node.closest('.infinite-photo-scroll') || node.closest('.video-grid') || node.closest('.founder-section') || node.closest('.programmes-page') || node.closest('.how-it-works-page') || node.closest('.seminaire-detail-page') || node.closest('.program-detail-page') || node.closest('.panier-page') || node.closest('header') || node.closest('nav')) {
      return;
    }

    let parent = node.parentElement;
    while (parent) {
      if (parent.dataset && parent.dataset.hasScrollAnim) {
        return;
      }
      parent = parent.parentElement;
    }

    node.dataset.hasScrollAnim = "true";

    const isFooter = node.closest('footer');

    const tl = gsap.timeline({
      scrollTrigger: {
        trigger: node,
        start: "top bottom",
        end: isFooter ? "center 90%" : "bottom top",
        scrub: 0.5,
      }
    });

    if (isFooter) {
      tl.fromTo(node,
        { scale: 0.9, y: 0, opacity: 1, transformOrigin: "center center" },
        { scale: 1, y: 0, opacity: 1, duration: 1, ease: "power2.out" }
      );
    } else {
      tl.fromTo(node,
        { scale: 0.85, y: 0, opacity: 0, transformOrigin: "center center" },
        { scale: 1, y: 0, opacity: 1, duration: 0.3, ease: "power2.out" }
      )
        .to(node,
          { scale: 1, y: 0, opacity: 1, duration: 0.4 }
        )
        .to(node,
          { scale: 0.85, y: 0, opacity: 0, duration: 0.3, ease: "power1.in" }
        );
    }
  };

  const scanAndAnimate = (root) => {
    if (!root || !root.querySelectorAll) return;

    const selector = 'article, h1, h2, h3, h4, h5, h6, p, ul, ol, li, figure, img, button, a, .training-card, .asset-card, .card-wrapper, .vision-line, .program-card, .resource-card';

    const elements = root.querySelectorAll(selector);
    elements.forEach(animateNode);

    if (root !== mainRef.value && root.matches && root.matches(selector)) {
      animateNode(root);
    }
  };

  scanAndAnimate(mainRef.value);

  const footerEl = footerRef.value?.$el || footerRef.value;
  scanAndAnimate(footerEl);

  let refreshTimer;
  const debouncedRefresh = () => {
    if (document.body.classList.contains('is-hovering-expandable')) return;
    clearTimeout(refreshTimer);
    refreshTimer = setTimeout(() => {
      ScrollTrigger.refresh();
    }, 200);
  };

  mutationObserver = new MutationObserver((mutations) => {
    let shouldRefresh = false;
    mutations.forEach((mutation) => {
      if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
        mutation.addedNodes.forEach((node) => {
          if (node.nodeType === 1) {
            scanAndAnimate(node);
            shouldRefresh = true;
          }
        });
      }
    });
    if (shouldRefresh) debouncedRefresh();
  });

  mutationObserver.observe(mainRef.value, { childList: true, subtree: true });

  resizeObserver = new ResizeObserver(() => {
    if (document.body.classList.contains('is-hovering-expandable')) return;
    debouncedRefresh();
  });
  resizeObserver.observe(mainRef.value);

  window.addEventListener('load', () => debouncedRefresh());

  unbindRouter = router.on('finish', () => {
    debouncedRefresh();
    setTimeout(debouncedRefresh, 800);
  });

  const cursor = cursorRef.value;
  if (cursor) {
    const xTo = gsap.quickSetter(cursor, "x", "px");
    const yTo = gsap.quickSetter(cursor, "y", "px");

    window.addEventListener("mousemove", (e) => {
      xTo(e.clientX);
      yTo(e.clientY);
    });

    document.addEventListener("mouseover", (e) => {
      const target = e.target;
      if (target.closest('button') || target.closest('a') || target.closest('[role="button"]') || target.closest('.action-btn')) {
        gsap.to(cursor, { scale: 0, opacity: 0, duration: 0.3 });
        return;
      }

      if (target.closest('.interaction-scroll-cursor') || target.closest('.training-card') || target.closest('.category-card')) {
        if (target.closest('.monopo-root') || target.closest('.hero-page-container')) {
          gsap.to(cursor, { scale: 0, opacity: 0, duration: 0.3 });
          return;
        }
        gsap.to(cursor, { scale: 1, opacity: 1, duration: 0.3 });
      }
    });

    document.addEventListener("mouseout", (e) => {
      const target = e.target;
      const related = e.relatedTarget;
      const insideCard = related && (related.closest('.interaction-scroll-cursor') || related.closest('.training-card') || related.closest('.category-card'));

      if (!insideCard) {
        gsap.to(cursor, { scale: 0, opacity: 0, duration: 0.3 });
      }
    });
  }

  // --- Intro Loader Logic ---
  const hasSeenIntro = sessionStorage.getItem('hasSeenIntro');
  if (!hasSeenIntro) {
    showIntro.value = true;
    // Disable scrolling while loading
    document.body.style.overflow = 'hidden';
  }
};

const onIntroComplete = () => {
  showIntro.value = false;
  sessionStorage.setItem('hasSeenIntro', 'true');
  document.body.style.overflow = '';
}

// --- Page Transition Logic ---
let transitionPromise = null;
let activeRequests = 0;

unbindTransitionStart = router.on('start', (event) => {
  activeRequests++;
  if (transitionRef.value) {
    // Always start/restart transition on new request
    transitionPromise = transitionRef.value.startTransition();
  }
});

unbindTransitionFinish = router.on('finish', (event) => {
  activeRequests--;
  // Only end transition if no more pending requests
  if (activeRequests <= 0) {
    activeRequests = 0;
    if (transitionRef.value && transitionPromise) {
      transitionPromise.then(() => {
        // Ensure we don't uncover if a new request started while we were waiting
        if (activeRequests === 0) {
          setTimeout(() => {
            transitionRef.value.endTransition();
          }, 50);
        }
      });
    }
  }
});

</script>

<template>
  <div class="min-h-screen relative bg-[#FDFDFC] dark:bg-[#0a0a0a]">
    <IntroLoader v-if="showIntro" @complete="onIntroComplete" />
    <PageTransitionOverlay ref="transitionRef" />
    <Toast />

    <div class="custom-scroll-cursor" ref="cursorRef">
      <span class="cursor-text">FAITES<br>DÉFILER</span>
    </div>

    <AppHeader class="absolute top-0 inset-x-0 z-50" />

    <main class="flex-1" ref="mainRef">
      <slot />
    </main>

    <AppFooter ref="footerRef" />
  </div>
</template>

<style>
.custom-scroll-cursor {
  position: fixed;
  top: 0;
  left: 0;
  width: 90px;
  height: 90px;
  background-color: #ffffff;
  border-radius: 50%;
  pointer-events: none;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  transform: translate(-50%, -50%) scale(0);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
  mix-blend-mode: normal;
}


.training-card,
.category-card {
  cursor: none !important;
}

.training-card button,
.training-card a,
.training-card [role="button"],
.category-card button,
.category-card a,
.category-card [role="button"],
.action-btn {
  cursor: pointer !important;
}

.cursor-text {
  font-size: 0.8rem;
  font-weight: 500;
  color: #000;
  line-height: 1.2;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
</style>