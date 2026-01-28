<template>
  <ShaderBackground :colors="themeColors" class="monopo-root" ref="rootContainer">
    <div class="hero-content">
      <div class="title-container">
        <h1 class="main-title">
          <div v-for="(line, index) in title.split('<br />')" :key="index">
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
          {{ description }}
        </p>
      </div>
    </div>
  </ShaderBackground>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { gsap } from 'gsap'
import ShaderBackground from './ShaderBackground.vue'

const props = defineProps({
  title: {
    type: String,
    default: 'We choose a <br />different &rarr;<br />starting point'
  },
  description: {
    type: String,
    default: 'Every project is a chance to try something new. Look at something with a fresh perspective.'
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
  font-size: 6.5vw;
  font-weight: 500;
  line-height: 0.85;
  word-spacing: 0.2em;
  letter-spacing: -0.05em;
  font-family: 'Roboto', sans-serif;
  margin: 0;
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
  .hero-content {
    flex-direction: column;
    justify-content: center;
    padding: 30px;
  }

  .main-title {
    font-size: 12vw;
  }

  .scroll-arrow {
    width: 35px;
    margin-top: 20px;
  }
}</style>