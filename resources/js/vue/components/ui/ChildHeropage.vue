<template>
    <div class="monopo-root" ref="rootContainer">
      <div class="grain-overlay"></div>
      <canvas ref="canvas" id="gl"></canvas>
  
      <div class="hero-content">
        <div class="title-container">
          <h1 class="main-title">
            <div v-for="(line, index) in title.split('<br />')" :key="index">
              <span class="anim-opacity" v-html="line"></span>
            </div>
          </h1>
          <div class="scroll-arrow anim-opacity">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 5V19M12 19L5 12M12 19L19 12" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
        </div>
        
        <p class="sub-text anim-opacity">
          {{ description }}
        </p>
      </div>
    </div>
  </template>
  
  <script setup>
  import { onMounted, onBeforeUnmount, ref } from 'vue'
  import * as THREE from 'three'
  import { gsap } from 'gsap'
  
  const props = defineProps({
    title: {
      type: String,
      default: 'We choose a <br />different &rarr;<br />starting point'
    },
    description: {
      type: String,
      default: 'Every project is a chance to try something new. Look at something with a fresh perspective.'
    }
  })
  
  const canvas = ref(null)
  const rootContainer = ref(null)
  
  let renderer, scene, camera, uniforms, animationId
  
  const getCol = (cssVar) =>
    new THREE.Color(
      getComputedStyle(document.documentElement)
        .getPropertyValue(cssVar)
        .trim()
    )
  
  onMounted(() => {
    // --- LOGIQUE SHADER (VOTRE CODE) ---
    THREE.ColorManagement.enabled = false 
    renderer = new THREE.WebGLRenderer({ canvas: canvas.value, antialias: true })
    renderer.outputColorSpace = THREE.LinearSRGBColorSpace
  
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
  
    const mesh = new THREE.Mesh(new THREE.PlaneGeometry(2, 2), material)
    scene.add(mesh)
  
    // --- ANIMATION D'APPARITION (FONDU SUR PLACE) ---
    gsap.from(".anim-opacity", {
      opacity: 0,
      duration: 1.5,
      stagger: 0.3, // Délai entre chaque ligne/élément
      ease: "power2.inOut",
      delay: 0.2
    })
  
    // --- LOGIQUE INTERACTION (VOTRE CODE) ---
    const resize = () => {
      if (!rootContainer.value) return
      const width = rootContainer.value.clientWidth
      const height = rootContainer.value.clientHeight
      renderer.setSize(width, height)
      uniforms.resolution.value.set(width, height)
    }
  
    window.addEventListener('resize', resize)
    resize()
  
    const onMouseMove = (e) => {
      const rect = rootContainer.value.getBoundingClientRect()
      const x = (e.clientX - rect.left) / rect.width
      const y = 1.0 - (e.clientY - rect.top) / rect.height
      
      gsap.to(uniforms.uMouse.value, {
        x: x,
        y: y,
        duration: 1.5,
        ease: 'expo.out'
      })
    }
  
    window.addEventListener('mousemove', onMouseMove)
  
    const animate = (t) => {
      uniforms.time.value = t * 0.001
      renderer.render(scene, camera)
      animationId = requestAnimationFrame(animate)
    }
  
    animate(0)
  
    onBeforeUnmount(() => {
      cancelAnimationFrame(animationId)
      window.removeEventListener('resize', resize)
      window.removeEventListener('mousemove', onMouseMove)
      renderer.dispose()
    })
  })
  
  const vertShader = `
  void main() {
    gl_Position = vec4(position, 1.0);
  }
  `
  
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
    float magnet = smoothstep(0.9, 0.2, mDist);
    
    float n1 = snoise(vec2(centeredUv.x * 0.9 + time * 0.04, centeredUv.y * 0.8));
    float n2 = snoise(vec2(centeredUv.x * 0.7 - time * 0.02, centeredUv.y * 0.8)) * 0.6;
    
    vec2 pull = (mouseUv - centeredUv) * magnet * 0.2;
    
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
  /* TOUS VOS CSS D'ORIGINE SONT CONSERVÉS ICI */
  :global(:root) {
    --c-primary: #8A38F5; --c-secondary: #0E7EC3;
    --c-accent: #981e12; --c-dark: #000000;
  }
  
  .monopo-root {
    position: relative;
    width: 100%;
    height: 60vh; 
    min-height: 500px;
    overflow: hidden;
    background-color: #000000;
  }
  
  canvas { position: absolute; inset: 0; width: 100%; height: 100%; z-index: 1; }
  
  .grain-overlay {
    position: fixed; inset: 0; opacity: 0.14; pointer-events: none; z-index: 5;
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    mix-blend-mode: overlay;
  }
  
  .hero-content {
    position: relative; z-index: 10; color: #ffffff;
    padding: 0 60px 50px 60px;
    height: 100%; display: flex; flex-direction: row;
    justify-content: space-between; align-items: flex-end;
    box-sizing: border-box; pointer-events: none;
  }
  
  .title-container {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
  }
  
  .main-title {
    font-size: 6.5vw; font-weight: 500; line-height: 0.85;
    word-spacing: 0.2em;
    letter-spacing: -0.05em;
    font-family: 'Roboto', sans-serif;
    margin: 0;
  }
  
  .scroll-arrow {
    margin-top: 5vh;
    margin-left: 0;
    width: 45px; height: 45px;
    color: white; opacity: 1;
    animation: bounce 2s infinite ease-in-out;
  }
  
  .sub-text {
    font-size: 1.05rem; max-width: 360px; line-height: 1.5;
    border-left: 1px solid rgba(255,255,255,0.4);
    padding-left: 20px; margin-bottom: 40px;
  }
  
  /* Nouvelles classes pour supporter l'animation sans casser le style */
  .anim-opacity {
    opacity: 1;
    display: inline-block;
  }
  
  @keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(15px); }
  }
  
  @media (max-width: 768px) {
    .hero-content { flex-direction: column; justify-content: center; padding: 30px; }
    .main-title { font-size: 12vw; }
    .scroll-arrow { width: 35px; margin-top: 20px; }
  }
  </style>