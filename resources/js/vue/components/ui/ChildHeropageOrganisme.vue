<template>
  <div class="monopo-root interaction-scroll-cursor" ref="rootContainer" @mouseenter="isInsideSection = true"
    @mouseleave="isInsideSection = false">
    <div class="custom-cursor" ref="cursorRef">
      <span>{{ displayCursorText }}</span>
    </div>

    <ShaderBackground :colors="themeColors" class="shader-bg-wrapper">
      <div class="hero-content">
        <div class="title-container">
          <h1 class="main-title">
            <div v-for="(line, idx) in formattedLines" :key="idx">
              <span class="anim-opacity">
                <span v-for="(part, pIdx) in line" :key="pIdx" :class="{ 'gt-alpina-font': part.isHighlight }">
                  {{ part.text }}<span v-if="pIdx < line.length - 1">&nbsp;</span>
                </span>
              </span>
            </div>
          </h1>

          <div class="hero-subtitle anim-opacity" @click.stop="goToHowItWorks" @mouseenter="isHoveringLink = true"
            @mouseleave="isHoveringLink = false">
            {{ $t('ChildHeropageOrganisme.howItWorks') }}
          </div>

          <div class="scroll-arrow anim-opacity">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 5V19M12 19L5 12M12 19L19 12" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
          </div>
        </div>
      </div>
    </ShaderBackground>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, computed } from 'vue'
import { gsap } from 'gsap'
import { router, usePage } from '@inertiajs/vue3'
import ShaderBackground from './ShaderBackground.vue'

const page = usePage();
const $t = (key) => page.props.translations[key] ?? key;

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  mobileTitle: {
    type: String,
    default: ''
  },
  cursorText: {
    type: String,
    default: ''
  }
})

const displayTitle = computed(() => {
  return props.title || $t('ChildHeropageOrganisme.title');
});

const displayMobileTitle = computed(() => {
  return props.mobileTitle || $t('ChildHeropageOrganisme.mobileTitle');
});

const displayCursorText = computed(() => {
  return props.cursorText || $t('ChildHeropageOrganisme.cursorText');
});


const themeColors = {
  primary: '#F7B455',
  secondary: '#0E7EC3',
  accent: '#981e12',
  dark: '#000000'
}

const isMobile = ref(false);

const updateIsMobile = () => {
  if (typeof window !== 'undefined') {
    isMobile.value = window.innerWidth <= 768;
  }
};

const highlights = ['ShiftUp', 'reconvertir', 'dirigeants', 'TPE / PME', 'Entrepreneur Libre', 'liberté', 'entrepreneuriale'];
const formattedLines = computed(() => {
  const textToUse = (isMobile.value && displayMobileTitle.value) ? displayMobileTitle.value : displayTitle.value;

  return (textToUse || '').split('<br />').map(line => {
    let words = line.trim().split(/\s+/);
    let result = [];
    let i = 0;
    while (i < words.length) {
      let matched = false;
      if (i + 2 < words.length) {
        const threeWords = words[i] + ' ' + words[i + 1] + ' ' + words[i + 2];
        if (highlights.some(h => threeWords.toLowerCase().includes(h.toLowerCase()))) {
          result.push({ text: threeWords, isHighlight: true });
          i += 3;
          matched = true;
        }
      }
      if (!matched && i + 1 < words.length) {
        const twoWords = words[i] + ' ' + words[i + 1];
        if (highlights.some(h => twoWords.toLowerCase().includes(h.toLowerCase()))) {
          result.push({ text: twoWords, isHighlight: true });
          i += 2;
          matched = true;
        }
      }
      if (!matched) {
        const word = words[i];
        const isHighlight = highlights.some(h => word.toLowerCase().includes(h.toLowerCase()));
        result.push({ text: word, isHighlight });
        i++;
      }
    }
    return result;
  });
});

const rootContainer = ref(null)
const cursorRef = ref(null)

const goToHowItWorks = () => {
  router.visit('/comment-ca-fonctionne');
}

const isInsideSection = ref(false)
const isHoveringLink = ref(false)
let cursorTimeout = null

const scrollToContent = () => {
  const target = document.getElementById('organisme-content')
  if (target) target.scrollIntoView({ behavior: 'smooth' })
}

onMounted(() => {
  updateIsMobile();
  window.addEventListener('resize', updateIsMobile);

  gsap.set(cursorRef.value, {
    xPercent: -50,
    yPercent: -50,
    scale: 0,
    opacity: 0
  });

  const onMouseMove = (e) => {
    gsap.to(cursorRef.value, {
      x: e.clientX,
      y: e.clientY,
      duration: 0.1,
      overwrite: 'auto'
    })

    const isOverHeader = e.clientY < 110;

    const targetScale = (isHoveringLink.value || isOverHeader || !isInsideSection.value) ? 0 : 1;
    const targetOpacity = (isHoveringLink.value || isOverHeader || !isInsideSection.value) ? 0 : 1;

    gsap.to(cursorRef.value, {
      scale: targetScale,
      opacity: targetOpacity,
      duration: 0.3,
      ease: "power2.out"
    })

    // Auto-hide si on arrête de bouger
    clearTimeout(cursorTimeout)
    cursorTimeout = setTimeout(() => {
      gsap.to(cursorRef.value, {
        scale: 0,
        opacity: 0,
        duration: 0.5
      })
    }, 2000)
  }

  window.addEventListener('mousemove', onMouseMove)

  // 3. ANIMATION ENTREE
  gsap.from(".anim-opacity", {
    opacity: 0, duration: 1.5, stagger: 0.3, ease: "power2.inOut", delay: 0.2
  })

  // 4. CLICK GLOBAL
  const handleGlobalClick = (e) => {
    if (e.target.closest('.hero-subtitle')) return;
    window.open('https://www.youtube.com/@nr.shiftup', '_blank')
  }
  if (rootContainer.value) rootContainer.value.addEventListener('click', handleGlobalClick)

  onBeforeUnmount(() => {
    window.removeEventListener('resize', updateIsMobile);
    window.removeEventListener('mousemove', onMouseMove)
    if (rootContainer.value) rootContainer.value.removeEventListener('click', handleGlobalClick)
    clearTimeout(cursorTimeout)
  })
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');

.monopo-root {
  position: relative;
  width: 100%;
  height: 85vh;
  overflow: hidden;
  background-color: #000000;
  cursor: none;
}

.shader-bg-wrapper {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
}

.hero-content {
  position: relative;
  z-index: 10;
  color: #ffffff;
  padding: 0 5vw;
  height: 100%;
  display: flex;
  flex-direction: column;
  margin-top: 10vh;
  justify-content: center;
  align-items: center;
  text-align: center;
  box-sizing: border-box;
  pointer-events: none;
}

.title-container {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.main-title {
  font-size: clamp(2rem, 3.5vw, 4rem);
  font-weight: 400;
  line-height: 1.1;
  letter-spacing: -0.02em;
  font-family: 'Roboto', sans-serif;
  margin: 0;
  max-width: 90vw;
  text-transform: none;
  word-wrap: break-word;
  word-break: break-word;
  overflow-wrap: break-word;
  hyphens: auto;
}

.gt-alpina-font {
  font-family: 'GT Alpina Fine Trial', serif;
  font-weight: 300;
  font-style: italic;
  letter-spacing: 0.02em;
}

.hero-subtitle {
  font-family: 'Roboto', sans-serif;
  font-size: 1.2rem;
  font-weight: 400;
  text-transform: uppercase;
  margin-top: 3rem;
  letter-spacing: 0.15em;
  color: rgba(255, 255, 255, 0.8);
  position: relative;
  cursor: pointer;
  pointer-events: auto;
  padding-bottom: 5px;
}

.hero-subtitle::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 1px;
  background: white;
  transform: scaleX(0);
  transform-origin: right;
  transition: transform 0.6s cubic-bezier(0.19, 1, 0.22, 1);
}

.hero-subtitle:hover::after {
  transform: scaleX(1);
  transform-origin: left;
}

/* STYLE DU CURSEUR CORRIGÉ */
.custom-cursor {
  position: fixed;
  top: 0;
  left: 0;
  width: 135px;
  height: 135px;
  background: #f4f4f4;
  border-radius: 50%;
  z-index: 9999;
  pointer-events: none;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 10px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  will-change: transform, opacity;
}

.custom-cursor span {
  color: #000000 !important;
  font-family: 'Roboto', sans-serif;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  line-height: 1.2;
}

.scroll-arrow {
  margin-top: 3vh;
  width: 45px;
  height: 45px;
  color: white;
  opacity: 1;
  animation: bounce 2s infinite ease-in-out;
}

.anim-opacity {
  opacity: 1;
  display: inline-block;
}

@keyframes bounce {

  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(15px);
  }
}

@media (max-width: 1024px) {
  .main-title {
    font-size: 6vw;
  }
}

@media (max-width: 768px) {
  .main-title {
    font-size: clamp(1.8rem, 8.5vw, 3rem);
    padding-top: 0 !important;
    width: 95% !important;
    max-width: 95vw;
  }

  .scroll-arrow {
    width: 35px;
    margin-top: 5vh;
  }

  .hero-subtitle {
    font-size: 1.2rem;
    margin-top: 0 !important;
  }

  .custom-cursor {
    display: none;
  }

  .monopo-root {
    cursor: auto;
  }

  .title-container {
    display: flex;
    flex-direction: column;
  }

  .hero-subtitle {
    order: -1;
    margin-top: 0;
    margin-bottom: 20px;
  }

  .hero-content {
    margin-top: 5vh;
  }

  .main-title {
    order: 0;
  }

  .scroll-arrow {
    order: 1;
  }
}
</style>
