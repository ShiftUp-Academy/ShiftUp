<template>
  <div class="monopo-root interaction-scroll-cursor" ref="rootContainer" @mouseenter="isInsideSection = true" @mouseleave="isInsideSection = false">
    <div class="custom-cursor" ref="cursorRef">
      <span>{{ cursorText }}</span>
    </div>

    <div class="grain-overlay"></div>
    <canvas ref="canvas" id="gl"></canvas>

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

        <div class="hero-subtitle anim-opacity" @click.stop="scrollToContent" @mouseenter="isHoveringLink = true"
          @mouseleave="isHoveringLink = false">
          Comment l'entreprise fonctionne
        </div>

        <div class="scroll-arrow anim-opacity">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 5V19M12 19L5 12M12 19L19 12" stroke="currentColor" stroke-width="3" stroke-linecap="round"
              stroke-linejoin="round" />
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, computed } from 'vue'
import * as THREE from 'three'
import { gsap } from 'gsap'

// --- PROPS ---
const props = defineProps({
  title: {
    type: String,
    default: 'L’organisme ShiftUp a pour vocation <br />d’aider les cadres qui veulent se reconvertir <br />et les dirigeants de TPE / PME à devenir <br />Un Entrepreneur Libre.'
  },
  cursorText: {
    type: String,
    default: 'Regardez des vidéos'
  }
})

// --- LOGIQUE TEXTE ---
const highlights = ['ShiftUp', 'reconvertir', 'dirigeants', 'TPE / PME', 'Entrepreneur Libre'];
const formattedLines = computed(() => {
  return props.title.split('<br />').map(line => {
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

// --- REFS ---
const canvas = ref(null)
const rootContainer = ref(null)
const cursorRef = ref(null)

// État curseur
const isInsideSection = ref(false)
const isHoveringLink = ref(false)
let cursorTimeout = null

// --- THREE.JS ---
let renderer, scene, camera, uniforms, animationId

const getCol = (cssVar) =>
  new THREE.Color(getComputedStyle(document.documentElement).getPropertyValue(cssVar).trim())

const scrollToContent = () => {
  const target = document.getElementById('organisme-content')
  if (target) target.scrollIntoView({ behavior: 'smooth' })
}

onMounted(() => {
  // 1. SETUP THREE.JS
  THREE.ColorManagement.enabled = false
  renderer = new THREE.WebGLRenderer({ canvas: canvas.value, antialias: true, alpha: true })
  renderer.outputColorSpace = THREE.LinearSRGBColorSpace
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))

  scene = new THREE.Scene()
  camera = new THREE.Camera()

  uniforms = {
    time: { value: 0 },
    resolution: { value: new THREE.Vector2() },
    uMouse: { value: new THREE.Vector2(0.5, 0.5) },
    uPrimary: { value: getCol('--c-primary') },
    uSecondary: { value: getCol('--c-secondary') },
    uAccent: { value: getCol('--c-accent') },
    uDark: { value: getCol('--c-dark') }
  }

  const material = new THREE.ShaderMaterial({
    vertexShader: vertShader,
    fragmentShader: fragShader,
    uniforms
  })
  scene.add(new THREE.Mesh(new THREE.PlaneGeometry(2, 2), material))

  // 2. RESIZE
  const resize = () => {
    if (!rootContainer.value) return
    const width = rootContainer.value.clientWidth
    const height = rootContainer.value.clientHeight
    renderer.setSize(width, height)
    uniforms.resolution.value.set(width, height)
  }
  window.addEventListener('resize', resize)
  resize()

  // 3. INITIALISATION CURSEUR (GSAP Set)
  // On le place au centre au début et on le cache (scale 0)
  gsap.set(cursorRef.value, {
    xPercent: -50,
    yPercent: -50,
    scale: 0,
    opacity: 0
  });

  // 4. GESTION SOURIS
  const onMouseMove = (e) => {
    // A. Shader
    if (rootContainer.value) {
      const rect = rootContainer.value.getBoundingClientRect()
      const x = (e.clientX - rect.left) / rect.width
      const y = 1.0 - (e.clientY - rect.top) / rect.height
      gsap.to(uniforms.uMouse.value, {
        x: x, y: y, duration: 1.5, ease: 'expo.out', overwrite: 'auto'
      })
    }

    // B. Curseur HTML
    // On déplace le curseur
    gsap.to(cursorRef.value, {
      x: e.clientX,
      y: e.clientY,
      duration: 0.1, // Très réactif
      overwrite: 'auto'
    })

    // On l'affiche (Scale 1 = Taille Originale Définie en CSS)
    // EXCEPTION: On le cache si on approche du menu (haut de page)
    const isOverHeader = e.clientY < 110;

    const targetScale = (isHoveringLink.value || isOverHeader || !isInsideSection.value) ? 0 : 1;
    const targetOpacity = (isHoveringLink.value || isOverHeader || !isInsideSection.value) ? 0 : 1;

    gsap.to(cursorRef.value, {
      scale: targetScale,
      opacity: targetOpacity,
      duration: 0.3,
      ease: "power2.out"
    })

    // C. Auto-hide si on arrête de bouger
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

  // 5. ANIMATION LOOP
  const animate = (t) => {
    uniforms.time.value = t * 0.001
    renderer.render(scene, camera)
    animationId = requestAnimationFrame(animate)
  }
  animate(0)

  // 6. ANIMATION ENTREE
  gsap.from(".anim-opacity", {
    opacity: 0, duration: 1.5, stagger: 0.3, ease: "power2.inOut", delay: 0.2
  })

  // 7. CLICK GLOBAL
  const handleGlobalClick = (e) => {
    if (e.target.closest('.hero-subtitle')) return;
    window.open('https://www.youtube.com/@nr.shiftup', '_blank')
  }
  if (rootContainer.value) rootContainer.value.addEventListener('click', handleGlobalClick)

  onBeforeUnmount(() => {
    cancelAnimationFrame(animationId)
    window.removeEventListener('resize', resize)
    window.removeEventListener('mousemove', onMouseMove)
    if (rootContainer.value) rootContainer.value.removeEventListener('click', handleGlobalClick)
    renderer.dispose()
    clearTimeout(cursorTimeout)
  })
})

const vertShader = `void main() { gl_Position = vec4(position, 1.0); }`
const fragShader = `
uniform float time;
uniform vec2 resolution;
uniform vec2 uMouse;
uniform vec3 uPrimary;
uniform vec3 uSecondary;
uniform vec3 uAccent;
uniform vec3 uDark;

vec3 mod289(vec3 x) { return x - floor(x * (1.0 / 289.0)) * 289.0; }
vec2 mod289(vec2 x) { return x - floor(x * (1.0 / 289.0)) * 289.0; }
vec3 permute(vec3 x) { return mod289(((x*34.0)+1.0)*x); }

float snoise(vec2 v) {
  const vec4 C = vec4(0.211324865405187, 0.366025403784439, -0.577350269189626, 0.024390243902439);
  vec2 i  = floor(v + dot(v, C.yy));
  vec2 x0 = v - i + dot(i, C.xx);
  vec2 i1 = (x0.x > x0.y) ? vec2(1.0, 0.0) : vec2(0.0, 1.0);
  vec4 x12 = x0.xyxy + C.xxzz;
  x12.xy -= i1;
  i = mod289(i);
  vec3 p = permute(permute(i.y + vec3(0.0, i1.y, 1.0)) + i.x + vec3(0.0, i1.x, 1.0));
  vec3 m = max(0.5 - vec3(dot(x0,x0), dot(x12.xy,x12.xy), dot(x12.zw,x12.zw)), 0.0);
  m = m*m; m = m*m;
  vec3 x = 2.0 * fract(p * C.www) - 1.0;
  vec3 h = abs(x) - 0.5;
  vec3 a0 = x - floor(x + 0.5);
  vec3 g = a0 * vec3(x0.x, x12.xz) + h * vec3(x0.y, x12.yw);
  return 130.0 * dot(m, g);
}

void main() {
  vec2 uv = gl_FragCoord.xy / resolution.xy;
  float ratio = resolution.x / resolution.y;
  vec2 centeredUv = (uv - 0.5) * vec2(ratio, 1.0);
  vec2 mouseUv = (uMouse - 0.5) * vec2(ratio, 1.0);

  float mDist = distance(centeredUv, mouseUv);
  float magnet = smoothstep(1.2, 0.1, mDist); // Zone d'influence plus grande
  
  float n1 = snoise(vec2(centeredUv.x * 0.9 + time * 0.04, centeredUv.y * 0.8));
  float n2 = snoise(vec2(centeredUv.x * 0.7 - time * 0.02, centeredUv.y * 0.8)) * 0.6;
  
  vec2 pull = (mouseUv - centeredUv) * magnet * 0.5; // Pull plus fort
  
  float combinedNoise = (n1 + n2) * (0.45 + magnet * 0.7);
  float dist = abs(centeredUv.y + combinedNoise + pull.y);

  vec3 color = uDark;
  float maskPrimary = smoothstep(0.99 + magnet * 0.2, 0.9, dist);
  color = mix(color, uPrimary, maskPrimary * 0.9);
  float maskSecondary = smoothstep(0.20 + magnet * 0.7, 0.8, dist + 0.05);
  color = mix(color, uSecondary, maskSecondary * 0.8);
  float edge = abs(dist - 0.09);
  float maskAccent = smoothstep(0.39 + magnet * 0.1, 0.0, edge);
  color = mix(color, uAccent, maskAccent * (0.9 + magnet * 0.3));
  float hole = smoothstep(0.0, 0.3, dist + (1.0 - magnet) * 0.03);
  color *= hole;
  float grain = (fract(sin(dot(uv, vec2(12.9898,78.233))) * 43758.5453) - 0.5) * 0.07;
  color += grain;
  gl_FragColor = vec4(color, 1.0);
}
`
</script>

<style scoped>
:global(:root) {
  --c-primary: #F7B455;
  --c-secondary: #0E7EC3;
  --c-accent: #981e12;
  --c-dark: #000000;
}

.monopo-root {
  position: relative;
  width: 100%;
  height: 85vh;
  overflow: hidden;
  background-color: #000000;
  cursor: none;
  /* Cache curseur natif */
}

canvas {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
}

.grain-overlay {
  position: fixed;
  inset: 0;
  opacity: 0.14;
  pointer-events: none;
  z-index: 5;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
  mix-blend-mode: overlay;
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
  font-size: 3.5vw;
  font-weight: 500;
  line-height: 1.1;
  letter-spacing: -0.02em;
  font-family: 'Roboto', sans-serif;
  margin: 0;
  max-width: 80vw;
  text-transform: none;
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
  /* Taille augmentée pour bien contenir "Regardez des vidéos" */
  width: 135px;
  height: 135px;
  background: #000000;
  border-radius: 50%;
  z-index: 1000;
  pointer-events: none;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 10px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  /* Pas de transition CSS sur transform, GSAP gère tout */
  will-change: transform, opacity;
}

.custom-cursor span {
  color: #ffffff;
  font-family: 'Roboto', sans-serif;
  font-size: 0.85rem;
  /* Ajusté pour tenir dans le cercle */
  font-weight: 500;
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
    font-size: 8vw;
  }

  .scroll-arrow {
    width: 35px;
    margin-top: 5vh;
  }

  .custom-cursor {
    display: none;
  }

  .monopo-root {
    cursor: auto;
  }
}</style>