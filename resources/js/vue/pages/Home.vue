<script setup>
import { computed } from 'vue';
import Heropage from '../components/sections/Heropage.vue';
import LiveTrainings from '../components/sections/LiveTrainings.vue';
import RessourceGratuites from '../components/sections/RessourceGratuites.vue';
import NosEvenements from '../components/sections/NosEvenements.vue';
import VideoGrid from '../components/sections/VideoGrid.vue';
import TestimonialSection from '../components/sections/TestimonialSection.vue';
import FounderSection from '../components/sections/FounderSection.vue';

const props = defineProps({
  programmes: {
    type: Array,
    default: () => []
  }
});

const freeResources = computed(() => {
  return props.programmes
    .filter(p => !p.Prix || Number(p.Prix) === 0)
    .slice(0, 6)
    .map(p => ({
      id: p.IdProgrammeFormation,
      title: p.Titre,
      image: p.LienPhoto || '/images/Programmes/Plan de travail1.png',
      progression: p.progression
    }));
});
</script>

<template>
  <Heropage />
  <LiveTrainings />
  <RessourceGratuites :resources="freeResources" />
  <NosEvenements />
  <TestimonialSection />
  <VideoGrid />
  <FounderSection />
</template>
