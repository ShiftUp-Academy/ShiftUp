<template>
  <section class="photo-scroll-section infinite-photo-scroll" ref="sectionRef">
    <div class="marquee-wrapper">
      <div class="marquee-content" ref="marqueeContent">
        <div v-for="(photo, idx) in [...photos, ...photos]" :key="idx" class="photo-item">
          <img :src="photo" alt="Organisme Activity" class="scroll-img" loading="lazy" />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const sectionRef = ref(null);
const marqueeContent = ref(null);

const photos = [
  '/images/photoOrganisme/Séminaire MVC 2025 - Jour 2-130.jpg',
  '/images/photoOrganisme/Séminaire MVC 2025 - Jour 2-136.jpg',
  '/images/photoOrganisme/Séminaire MVC 2025 - Jour 2-52.jpg',
  '/images/photoOrganisme/Séminaire MVC 2025 - Jour 2-54.jpg',
  '/images/photoOrganisme/Séminaire MVC 2025 - Jour 2-67.jpg',
  '/images/photoOrganisme/Séminaire MVC 2025 - Jour 2-7.jpg'
];

let loop; // La timeline GSAP
let scrollTriggerInstance;

onMounted(() => {
  if (!marqueeContent.value) return;

  // 1. Configuration de la boucle infinie avec GSAP (Plus performant que CSS)
  // On déplace de 0% à -50% (car on a doublé le contenu).
  loop = gsap.to(marqueeContent.value, {
    xPercent: -50, 
    ease: "none",
    duration: 40, // Vitesse de base (plus le nombre est grand, plus c'est lent)
    repeat: -1,
  });

  // 2. Proxy pour gérer l'accélération au scroll
  // On utilise ScrollTrigger pour détecter la vitesse du scroll (velocity)
  // et on ajuste le timeScale de la boucle.
  scrollTriggerInstance = ScrollTrigger.create({
    trigger: sectionRef.value,
    start: "top bottom",
    end: "bottom top",
    onUpdate: (self) => {
      // self.getVelocity() donne la vitesse du scroll en px/s
      // On divise pour obtenir un facteur raisonnable (ex: 0 à 5)
      const velocity = Math.abs(self.getVelocity());
      const timeScale = 1 + (velocity / 2000); 

      // On lisse le changement de vitesse pour éviter les à-coups
      gsap.to(loop, { 
        timeScale: timeScale, 
        duration: 0.5, 
        overwrite: true 
      });

      // Retour à la vitesse normale (1) quand on arrête de scroller
      gsap.to(loop, { 
        timeScale: 1, 
        duration: 1, 
        delay: 0.5, // Attendre un peu avant de ralentir
        overwrite: "auto"
      });
    }
  });
});

onUnmounted(() => {
  if (loop) loop.kill();
  if (scrollTriggerInstance) scrollTriggerInstance.kill();
});
</script>

<style scoped>
.photo-scroll-section {
  background-color: #000000;
  width: 100%;
  overflow: hidden;
  padding: 4vh 0;
  /* Masque le contenu qui dépasse */
}

.marquee-wrapper {
  width: 100%;
  overflow: hidden;
}

.marquee-content {
  display: flex;
  gap: 20px;
  width: fit-content; /* S'assure que la div prend toute la largeur des images */
  /* IMPORTANT: will-change dit au navigateur de préparer le GPU */
  will-change: transform; 
}

.photo-item {
  flex: 0 0 auto;
  width: 35vw;
  height: 45vh;
  overflow: hidden;
  border-radius: 15px;
  /* Optimisation: évite les repeints */
  transform: translateZ(0); 
}

.scroll-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
  display: block;
}

.photo-item:hover .scroll-img {
  transform: scale(1.05);
}

@media (max-width: 1024px) {
  .photo-item {
    width: 60vw;
    height: 35vh;
  }
}

@media (max-width: 768px) {
  .photo-item {
    width: 85vw;
    height: 30vh;
  }
}
</style>