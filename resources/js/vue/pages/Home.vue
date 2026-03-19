<script setup>
import { computed } from 'vue';
import Heropage2 from '../components/sections/Heropage2.vue';
import SeminaireSection from '../components/sections/SeminaireSection.vue';
import RessourceGratuites from '../components/sections/RessourceGratuites.vue';
import NosEvenements from '../components/sections/NosEvenements.vue';
import VideoGrid from '../components/sections/VideoGrid.vue';
import TestimonialSection from '../components/sections/TestimonialSection.vue';
import FounderSection from '../components/sections/FounderSection.vue';

const props = defineProps({
  programmes: {
    type: Array,
    default: () => []
  },
  temoignages: {
    type: Array,
    default: () => []
  },
  heroVideo: String,
  homeLives: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  }
});

const seminaires = computed(() => {
  return props.programmes.filter(p => p.Type === 'Seminaire');
});

const freeResources = computed(() => {
  return props.programmes
    .filter(p => (!p.Prix || Number(p.Prix) === 0) && p.Type !== 'Seminaire')
    .slice(0, 6)
    .map(p => ({
      id: p.IdProgrammeFormation,
      title: p.Titre,
      image: p.LienPhoto || '/images/Programmes/Plan de travail1.png',
      progression: p.progression,
      categoryId: p.IdCategorie
    }));
});
</script>

<template>
  <Heropage2 :hero-video="props.heroVideo" />
  <SeminaireSection v-if="seminaires.length > 0" :seminaires="seminaires" />
  <RessourceGratuites :resources="freeResources" :categories="props.categories" />
  <NosEvenements :home-lives="props.homeLives" :categories="props.categories" />
  <TestimonialSection :temoignages="props.temoignages" />
  <VideoGrid />
  <FounderSection />
</template>
