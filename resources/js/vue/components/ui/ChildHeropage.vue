<template>
  <ShaderBackground :colors="themeColors" class="monopo-root" ref="rootContainer">
    <div class="hero-content">
      <div class="title-container">
        <h1 class="main-title">
          <div v-for="(line, index) in (displayTitle || '').split('<br />')" :key="index">
            <span class="anim-opacity" v-html="line"></span>
          </div>
        </h1>


        <div class="scroll-arrow anim-opacity">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 5V19M12 19L5 12M12 19L19 12" stroke="currentColor" stroke-width="3" stroke-linecap="round"
              stroke-linejoin="round" />
          </svg>
        </div>
      </div>

      <div class="right-column">
        <div class="action-slot anim-opacity">
          <slot name="action"></slot>
        </div>
        <p class="sub-text anim-opacity">
          {{ displayDescription }}
        </p>
      </div>
    </div>
  </ShaderBackground>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import { gsap } from 'gsap'
import { usePage } from '@inertiajs/vue3'
import ShaderBackground from './ShaderBackground.vue'

const page = usePage();
const $t = (key) => page.props.translations[key] ?? key;

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  cursorText: {
    type: String,
    default: ''
  },
  colors: {
    type: Object,
    default: () => ({
      primary: '#8A38F5',
      secondary: '#0E7EC3',
      accent: '#981e12',
      dark: '#000000'
    })
  }
})

const displayTitle = computed(() => {
  return props.title || $t('ChildHeropage.title');
});

const displayDescription = computed(() => {
  return props.description || $t('ChildHeropage.description');
});

const displayCursorText = computed(() => {
  return props.cursorText || $t('ChildHeropage.cursorText');
});

const rootContainer = ref(null)
const themeColors = props.colors;

onMounted(() => {
  gsap.from(".anim-opacity", {
    opacity: 0,
    duration: 1.5,
    stagger: 0.3,
    ease: "power2.inOut",
    delay: 0.2
  })
})
</script>

<style scoped>
.monopo-root {
  position: relative;
  width: 100%;
  height: 60vh;
  min-height: 500px;
  overflow: hidden;
  background-color: #000000;
}

.hero-content {
  position: relative;
  z-index: 10;
  color: #ffffff;
  padding: 0 60px 50px 60px;
  height: 100%;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: flex-end;
  box-sizing: border-box;
  pointer-events: none;
}

.title-container {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.right-column {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 1.5rem;
}

.main-title {
  font-size: clamp(2.5rem, 6.5vw, 6rem);
  font-weight: 400;
  line-height: 0.95;
  word-spacing: normal;
  letter-spacing: -0.02em;
  font-family: 'Roboto', sans-serif;
  margin: 0;
  word-wrap: break-word;
  hyphens: auto;
  max-width: 90vw;
}

.scroll-arrow {
  margin-top: 5vh;
  margin-left: 0;
  width: 45px;
  height: 45px;
  color: white;
  opacity: 1;
  animation: bounce 2s infinite ease-in-out;
}

.sub-text {
  font-size: 1.05rem;
  max-width: 360px;
  line-height: 1.5;
  border-left: 1px solid rgba(255, 255, 255, 0.4);
  padding-left: 20px;
  margin-bottom: 40px;
}

:deep(.hero-subtitle) {
  font-family: 'Roboto', sans-serif;
  font-size: 1.1rem;
  font-weight: 450;
  text-transform: uppercase;
  margin-top: 2rem;
  letter-spacing: 0.15em;
  color: rgb(255, 255, 255);
  position: relative;
  cursor: pointer;
  pointer-events: auto;
  padding-bottom: 5px;
  display: inline-block;
  text-decoration: none;
}

:deep(.hero-subtitle::after) {
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

:deep(.hero-subtitle:hover::after) {
  transform: scaleX(1);
  transform-origin: left;
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

@media (max-width: 768px) {
  .monopo-root {
    height: auto;
    min-height: 80vh;
    display: flex;
    align-items: center;
  }

  .hero-content {
    flex-direction: column-reverse;
    justify-content: center;
    align-items: flex-start;
    padding: 120px 25px 60px 25px;
    gap: 3rem;
    height: auto;
    width: 100%;
  }

  .right-column {
    gap: 1.2rem;
    width: 100%;
  }

  .title-container {
    padding-left: 0 !important;
    width: 100%;
  }

  .main-title {
    font-size: clamp(2rem, 12vw, 4.5rem);
    padding-left: 0 !important;
    margin-bottom: 10px;
    margin-left: -4px;
    line-height: 1.05;
    word-break: break-word;
    max-width: 100%;
  }

  .sub-text {
    font-size: 1.2rem;
    line-height: 1.1;
    padding-left: 20px;
    margin-bottom: 0;
    max-width: 100%;
  }

  .scroll-arrow {
    width: 35px;
    margin-top: 15px;
  }

  .action-slot {
    margin-bottom: 0.5rem;

  }
}
</style>