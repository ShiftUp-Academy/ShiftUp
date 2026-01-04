<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppHeader from '../components/headerfooter/AppHeader.vue';
import AppFooter from '../components/headerfooter/AppFooter.vue';
import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

// --- Smooth Scroll (Lenis) ---
let lenis;

onMounted(() => {
  lenis = new Lenis({
    duration: 1.2,
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    smoothWheel: true,
  });

  // GSAP + Lenis Integration
  lenis.on('scroll', ScrollTrigger.update);
  window.lenis = lenis;

  gsap.ticker.add((time) => {
    lenis.raf(time * 1000); // Lenis requires time in ms
  });

  gsap.ticker.lagSmoothing(0);
  
  // Initialize observer
  setupScrollAnimations();
});

onUnmounted(() => {
  if (lenis) {
    lenis.destroy();
    gsap.ticker.remove((time) => lenis.raf(time * 1000));
  }
  if (mutationObserver) mutationObserver.disconnect();
  if (resizeObserver) resizeObserver.disconnect();
  if (unbindRouter) unbindRouter();
});

// --- Scroll Zoom/Breathing Animation ---
const mainRef = ref(null);
const cursorRef = ref(null);
const footerRef = ref(null);
let mutationObserver;
let resizeObserver;
let unbindRouter;

const setupScrollAnimations = () => {
  if (!mainRef.value) return;

  const animateNode = (node) => {
    if (node.nodeType !== 1) return; // Ensure element node
    
    // Ensure we don't double-animate
    if (node.dataset.hasScrollAnim) return;

    // EXCLUSION UPDATED: 
    // Added .no-global-reveal to prevent AppLayout from hijacking animations in Heropage/LiveTrainings
    // Added .infinite-photo-scroll and .video-grid to exclude these components from scroll animations
    // Added .founder-section to prevent opacity issues
    // Added .resource-card and .program-card to prevent buggy scale animations
    if (node.closest('[data-lenis-prevent]') || node.closest('.consultation-lists') || node.closest('.no-global-reveal') || node.closest('.infinite-photo-scroll') || node.closest('.video-grid') || node.closest('.founder-section')) {
      return;
    }

    // NEW: Prevent double animation if a parent is already being animated
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
    debouncedRefresh();
  });
  resizeObserver.observe(mainRef.value);

  window.addEventListener('load', () => debouncedRefresh());

  unbindRouter = router.on('finish', () => {
    debouncedRefresh();
    // Un deuxième refresh un peu plus tard pour les contenus asynchrones type images
    setTimeout(debouncedRefresh, 800);
  });

  // --- Custom Cursor Logic ---
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
};
</script>

<template>
  <div class="min-h-screen relative bg-[#FDFDFC] dark:bg-[#0a0a0a]">
    
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
/* Global Cursor Styles */
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
  box-shadow: 0 4px 15px rgba(0,0,0,0.15);
  mix-blend-mode: normal; 
}

.training-card, .category-card {
  cursor: none !important;
}

.training-card button, .training-card a, .training-card [role="button"],
.category-card button, .category-card a, .category-card [role="button"],
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