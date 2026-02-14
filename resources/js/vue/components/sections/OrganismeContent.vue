<template>
  <section id="organisme-content" class="organisme-content no-global-reveal">
    <div class="content-container">
      <h2 class="impact-title">
        Nous atteindrons cet objectif en partageant des conseils, stratégies, ressources et outils concrets sur tous les
        médias nécessaires pour créer ce « déclic » chez les chefs d’entreprise et les entrepreneurs en herbe
      </h2>

      <div class="supporting-text">
        <p>
          La seule façon d’être libre c’est d’avoir une entreprise qui a un système en place et qui génère des ventes en
          pilote automatique, sans votre intervention.
        </p>
        <p>
          Pour cela, il faut apporter davantage de valeur que quiconque sur votre marché – et il faut mettre en place
          quelques systèmes simples pour vous libérer du temps et pouvoir le consacrer à votre famille et à vos
          passions.
        </p>
        <p class="highlight-intro">
          Les cinq atouts qui feront le succès de notre Organisme sont les suivants :
        </p>
      </div>

      <div class="assets-grid" ref="gridRef" @mousemove="handleCardMouseMove($event, idx)"
        @mouseleave="handleCardMouseLeave(idx)">
        <div v-for="(asset, idx) in assets" :key="idx" class="asset-card" @mousemove="handleCardMouseMove($event, idx)"
          @mouseleave="handleCardMouseLeave(idx)" :style="{
            '--gradient-start': asset.gradient[0],
            '--gradient-end': asset.gradient[1],
            '--mouse-x': cardStates[idx].x + 'px',
            '--mouse-y': cardStates[idx].y + 'px',
            '--opacity': cardStates[idx].opacity
          }">
          <div class="card-header">
            <div class="number-circle">{{ idx + 1 }}</div>
          </div>
          <p class="asset-text">
            <span class="bold-intro">{{ splitText(asset.text).intro }}</span>
            <span class="remaining-text">{{ splitText(asset.text).rest }}</span>
          </p>
          <div class="flashlight"></div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from 'vue';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const assets = [
  {
    text: "La clarté absolue de notre objectif, et notre approche « One Thing » : Nous déclinons les tentations et restons concentrés sur l’objectif stratégique ci-dessus,",
    gradient: ["#F7B455", "#FF8C00"]
  },
  {
    text: "Un marketing bienveillant, qui met en valeur notre expertise : Nous appliquons nos propres conseils !",
    gradient: ["#8A38F5", "#4B0082"]
  },
  {
    text: "Une qualité sans faille : Le contenu de nos formations doit s’appuyer sur des cas d’études concrets.",
    gradient: ["#0E7EC3", "#004B8D"]
  },
  {
    text: "Une pédagogie irréprochable : À la pointe des outils et stratégies permettant d’obtenir les meilleurs résultats pour les entrepreneurs.",
    gradient: ["#1A888D", "#005F61"]
  },
  {
    text: "Une grande réactivité : Répondre aux interrogations des entrepreneurs et les aiguiller avec expertise.",
    gradient: ["#A71543", "#7D0022"]
  }
];

// Initialize states for each card
const cardStates = reactive(assets.map(() => ({ x: -1000, y: -1000, opacity: 0 })));

const sectionId = 'organisme-content';

onMounted(() => {
  // Staggered reveal for cards
  gsap.from('.asset-card', {
    scrollTrigger: {
      trigger: '.assets-grid',
      start: 'top 85%',
      toggleActions: 'play none none reverse'
    },
    y: 50,
    opacity: 0,
    duration: 1,
    stagger: 0.15,
    ease: 'power3.out'
  });

  // Fade up for text elements (if not handled by global layout)
  gsap.from(['.impact-title', '.supporting-text p'], {
    scrollTrigger: {
      trigger: '.content-container',
      start: 'top 80%',
    },
    y: 30,
    opacity: 0,
    duration: 1.2,
    stagger: 0.2,
    ease: 'power2.out'
  });
});

onBeforeUnmount(() => {
  ScrollTrigger.getAll().forEach(st => {
    if (st.vars.trigger === '.assets-grid' || st.vars.trigger === '.content-container') {
      st.kill();
    }
  });
});

const handleCardMouseMove = (e, idx) => {
  const rect = e.currentTarget.getBoundingClientRect();
  cardStates[idx].x = e.clientX - rect.left;
  cardStates[idx].y = e.clientY - rect.top;
  cardStates[idx].opacity = 1;
};

const handleCardMouseLeave = (idx) => {
  cardStates[idx].opacity = 0;
};

const splitText = (text) => {
  const parts = text.split(':');
  if (parts.length > 1) {
    return {
      intro: parts[0] + ' :',
      rest: ' ' + parts.slice(1).join(':')
    };
  }
  return { intro: text, rest: '' };
};
</script>

<style scoped>
.organisme-content {
  background-color: #0d0616;
  color: #ffffff;
  padding: 6vh 8vw;
  min-height: auto;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.content-container {
  max-width: 1600px;
  width: 100%;
  margin: 0 auto;
}

.impact-title {
  font-size: 2.5rem;
  width: 160vh;
  font-weight: 500;
  line-height: 1.2;
  text-align: left;
  margin-bottom: 5vh;
  font-family: 'Roboto', sans-serif;
  letter-spacing: -0.01em;
}

.supporting-text {
  max-width: 100%;
  margin-bottom: 6vh;
}

.supporting-text p {
  font-size: 1.25rem;
  font-weight: 400;
  line-height: 1.6;
  margin: 0;
  margin-bottom: 1.2rem;
  opacity: 0.8;
}

.highlight-intro {
  margin-top: 2.5rem;
  font-weight: 500 !important;
  opacity: 1 !important;
  color: #8A38F5;
}

/* ASSETS GRID */
.assets-grid {
  display: flex;
  grid-template-columns: repeat(5, 2fr);
  gap: 15px;
  margin-top: 2vh;
  flex-direction: row;
}

.asset-card {
  position: relative;
  background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
  /* border-radius: 15px; */
  padding: 1.2rem 1.4rem;
  height: 40vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  cursor: default;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  width: 30vw;
}

.card-header {
  margin-bottom: 0.5rem;
}

.number-circle {
  width: 40px;
  height: 40px;
  background-color: #000000;
  color: #ffffff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  font-weight: 600;
}

.asset-text {
  font-size: 1rem;
  line-height: 1.3;
  color: #ffffff;
  margin: 0;
  position: relative;
  z-index: 2;
}

.bold-intro {
  font-weight: 800;
  display: block;
  margin-bottom: 0.5rem;
  font-size: 1.1rem;
}

.remaining-text {
  font-weight: 400;
  opacity: 0.95;
}

/* Flashlight Effect */
.flashlight {
  position: absolute;
  inset: 0;
  pointer-events: none;
  background: radial-gradient(300px circle at var(--mouse-x) var(--mouse-y),
      rgba(0, 0, 0, 0.25),
      transparent 70%);
  opacity: var(--opacity);
  z-index: 1;
  transition: opacity 0.3s ease;
}

/* RESPONSIVE */
@media (max-width: 1440px) {
  .assets-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 1024px) {
  .impact-title {
    font-size: 2rem;
    width: 100%;
  }

  .assets-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .impact-title {
    font-size: 1.8rem;
  }

  .supporting-text p {
  line-height: 1.3;
}

  .assets-grid {
    grid-template-columns: 1fr;
  }

  .number-circle {
    width: 40px;
    height: 40px;
    margin-bottom: 1vh;
    font-size: 1.2rem;
  }

  .asset-card {
    padding: 1.5rem;
    width: 100%;
    height: auto;
  }

  .bold-intro {
    font-size: 1.2rem;
  }
  .asset-text {
    font-size: 1.2rem;
  }

  .assets-grid {
    flex-direction: column;
  }
}
</style>
