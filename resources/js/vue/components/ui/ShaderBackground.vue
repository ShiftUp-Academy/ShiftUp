<template>
  <div class="shader-background" ref="container">
    <div class="grain-overlay"></div>
    <canvas ref="canvas"></canvas>
    <slot></slot>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref, watch } from 'vue'
import * as THREE from 'three'
import { gsap } from 'gsap'

const props = defineProps({
  colors: {
    type: Object,
    default: () => ({
      primary: '#F7B455',
      secondary: '#0E7EC3',
      accent: '#981e12',
      dark: '#000000'
    })
  }
})

const container = ref(null)
const canvas = ref(null)

// --- THREE.JS ---
let renderer, scene, camera, uniforms, animationId

const getThreeColor = (colorStr) => new THREE.Color(colorStr)

onMounted(() => {
  if (!container.value || !canvas.value) return

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
    uPrimary: { value: getThreeColor(props.colors.primary) },
    uSecondary: { value: getThreeColor(props.colors.secondary) },
    uAccent: { value: getThreeColor(props.colors.accent) },
    uDark: { value: getThreeColor(props.colors.dark) }
  }

  const material = new THREE.ShaderMaterial({
    vertexShader: vertShader,
    fragmentShader: fragShader,
    uniforms
  })
  scene.add(new THREE.Mesh(new THREE.PlaneGeometry(2, 2), material))

  // 2. RESIZE
  const resize = () => {
    if (!container.value) return
    const width = container.value.clientWidth
    const height = container.value.clientHeight
    renderer.setSize(width, height)
    uniforms.resolution.value.set(width, height)
  }
  window.addEventListener('resize', resize)
  resize()

  // 3. GESTION SOURIS
  const onMouseMove = (e) => {
    if (!container.value) return
    const rect = container.value.getBoundingClientRect()
    const x = (e.clientX - rect.left) / rect.width
    const y = 1.0 - (e.clientY - rect.top) / rect.height

    // Smooth transition for mouse movement
    if (x >= 0 && x <= 1 && y >= 0 && y <= 1) {
      gsap.to(uniforms.uMouse.value, {
        x: x, y: y, duration: 1.5, ease: 'expo.out', overwrite: 'auto'
      })
    }
  }
  window.addEventListener('mousemove', onMouseMove)

  // 4. ANIMATION LOOP
  const animate = (t) => {
    uniforms.time.value = t * 0.001
    renderer.render(scene, camera)
    animationId = requestAnimationFrame(animate)
  }
  animate(0)

  // Clean up
  onBeforeUnmount(() => {
    cancelAnimationFrame(animationId)
    window.removeEventListener('resize', resize)
    window.removeEventListener('mousemove', onMouseMove)
    renderer && renderer.dispose()
  })
})

// Update colors if props change
watch(() => props.colors, (newColors) => {
  if (uniforms) {
    uniforms.uPrimary.value.set(newColors.primary)
    uniforms.uSecondary.value.set(newColors.secondary)
    uniforms.uAccent.value.set(newColors.accent)
    uniforms.uDark.value.set(newColors.dark)
  }
}, { deep: true })

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
  float magnet = smoothstep(1.2, 0.1, mDist);
  
  float n1 = snoise(vec2(centeredUv.x * 0.9 + time * 0.04, centeredUv.y * 0.8));
  float n2 = snoise(vec2(centeredUv.x * 0.7 - time * 0.02, centeredUv.y * 0.8)) * 0.6;
  
  vec2 pull = (mouseUv - centeredUv) * magnet * 0.5;
  
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
.shader-background {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
  background-color: #000000;
}

canvas {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
}

.grain-overlay {
  position: absolute;
  inset: 0;
  opacity: 0.14;
  pointer-events: none;
  z-index: 2;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
  mix-blend-mode: overlay;
}

:deep(> *:not(.grain-overlay):not(canvas)) {
  position: relative;
  z-index: 10;
}
</style>
