<template>
  <section class="founder-section">
    <div class="founder-container">
      <div class="image-wrapper" ref="imageWrapper">
        <div class="animated-bg-rect" ref="bgRect"></div>
        <img :src="founderImg" alt="Nantenaina Randria" class="founder-img" />
      </div>

      <div class="bio-content">
        <div class="bio-text">
          <p>Entrepreneur et conseiller de dirigeants de TPE/PME, passionné par le marketing et l’audiovisuel,
            Nantenaina
            bouscule le paysage du business coaching et du digital Marketing.</p>
          <p>Fondateur et dirigeant de ShiftUp, comptant une équipe de 5 personnes, Nantenaina compte pour clients des
            dirigeant de TPE/PME en quête de croissance et des cadres d’entreprise qui veulent entrer dans le monde de
            l’entrepreneuriat.</p>
          <p>Il accompagne ces personnes à améliorer leur performance ainsi que la croissance de leur business.</p>
          <p>Fasciné par l’optimisation constante sur différente aspect qui peuvent radicalement transformer notre vie
            ou
            nos résultats, il apprend des meilleurs entrepreneurs dans plusieurs domaine. Son obsession réside dans le
            fait de partager les outils concrets de ses mentors de la manière la plus captivante et pédagogique
            possible.
          </p>
        </div>

        <div class="signature">
          <span class="signature-name">Nantenaina Randria</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

const founderImg = '/images/Bibliothèque/Nantenaina.png';

gsap.registerPlugin(ScrollTrigger);

const imageWrapper = ref(null);
const bgRect = ref(null);

onMounted(() => {
  if (bgRect.value && imageWrapper.value) {
    gsap.fromTo(bgRect.value,
      {
        y: '20%',
        scaleY: 0.8,
        opacity: 0
      },
      {
        y: '0%',
        scaleY: 1,
        opacity: 1,
        duration: 2,
        ease: 'power3.out',
        scrollTrigger: {
          trigger: imageWrapper.value,
          start: 'top 80%',
          end: 'bottom 20%',
          scrub: 1
        }
      }
    );
  }
});

onBeforeUnmount(() => {
  // Kill only ScrollTriggers related to this section
  ScrollTrigger.getAll().forEach(trigger => {
    if (trigger.vars && trigger.vars.trigger === imageWrapper.value) {
      trigger.kill();
    }
  });
});
</script>

<style scoped>
.founder-section {
  background: #000000;
  color: #ffffff;
  padding: 12vh 10vw;
  min-height: 80vh;
  display: flex;
  align-items: center;
  position: relative;
  overflow: hidden;
}

.founder-container {
  max-width: 1400px;
  width: 100%;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8vw;
}

.bio-content {
  flex: 1;
  max-width: 700px;
}

.bio-text p {
  font-size: 1.2rem;
  margin: 0;
  margin-bottom: 1.8vh;
  font-weight: 300;
  color: #ececec;
}

@font-face {
  font-family: 'Signature December';
  src: url('/font/Signature December.otf') format('opentype');
  font-weight: normal;
  font-style: normal;
}

.title {
  font-size: 2.22rem !important;
  font-weight: 700;
  margin-bottom: 2rem;
  letter-spacing: -0.02em;
}

.signature {
  margin-top: 3rem;
  display: flex;
  margin-right: 2vw;
  justify-content: flex-end;
}

.signature-name {
  font-family: 'Signature December', cursive;
  font-size: 1.2rem;
  line-height: 1.3;
  color: #fff;
  margin-left: 20px;
  margin-top: -10px;
  transform: rotate(-5deg);
  display: inline-block;
  letter-spacing: -0.05em;
  opacity: 0.9;
}

.image-wrapper {
  position: relative;
  display: flex;
  width: 35vw;
  margin-left: -5vw;
  align-items: flex-end;
  justify-content: center;
}

.founder-img {
  width: 100%;
  height: auto;
  position: relative;
  z-index: 2;
}

.animated-bg-rect {
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 80%;
  height: 60%;
  background: linear-gradient(135deg, #8A38F5, #0E7EC3, #8A38F5);
  z-index: 1;
  opacity: 1;
}

.image-wrapper::after {
  content: '';
  position: absolute;
  bottom: -2vh;
  left: -10%;
  width: 120%;
  height: 15vh;
  background: linear-gradient(to bottom, transparent, #000000 90%);
  z-index: 3;
  pointer-events: none;
}

@media (max-width: 1024px) {
  .founder-section {
    padding: 8vh 5vw;
  }

  .founder-container {
    flex-direction: column;
    text-align: center;
    gap: 6vh;
    align-items: center;
  }

  .image-wrapper {
    width: 100%;
    margin-left: 0;
    max-width: 400px;
  }

  .bio-content {
    max-width: 90%;
  }

  .signature {
    justify-content: center;
    height: 100%;
    margin-top: 3rem;
  }

  .signature-name {
    margin-left: 0;
    margin-top: 0;
    font-size: 2rem;
    transform: rotate(-5deg);
  }

  .bio-text p {
    font-size: 1.1rem;
    line-height: 1.1;
  }
}

@media (max-width: 480px) {
  .signature-name {
    font-size: 1.4rem;
    line-height: 1;
  }
}
</style>
