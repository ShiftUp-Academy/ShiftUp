<script setup>
import ChildHeropage from '../components/ui/ChildHeropage.vue';
import CategorySection from '../components/sections/CategorySection.vue';
import TrainingProgramsSection from '../components/sections/TrainingProgramsSection.vue';
</script>

<template>
  <ChildHeropage
    title="Toutes les <br/>Categories<br/> de programmes " 
    description="Explorez toutes les offres, programmes et consultations proposés par ShiftUp regroupés en une seule page."
    cursorText="Découvrir"
  />
  <CategorySection />
  <TrainingProgramsSection />
</template>
