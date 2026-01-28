<template>
  <div class="glass-slide-container" 
       @click="toggle" 
       @mouseenter="handleHover(true)" 
       @mouseleave="handleHover(false)">
    <div class="status-label" ref="labelText">
      {{ modelValue === checkedValue ? checkedLabel : uncheckedLabel }}
    </div>
    <div class="track" :class="{ 'is-active': modelValue === checkedValue }" ref="track">
      <div class="knob" ref="knob"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { gsap } from 'gsap';

const props = defineProps({
  modelValue: {
    type: [String, Boolean],
    default: 'Dépublié'
  },
  checkedValue: {
    type: [String, Boolean],
    default: 'Publié'
  },
  uncheckedValue: {
    type: [String, Boolean],
    default: 'Dépublié'
  },
  checkedLabel: {
    type: String,
    default: 'Statut Publié'
  },
  uncheckedLabel: {
    type: String,
    default: 'Statut Brouillon'
  },
  activeColor: {
    type: String,
    default: '#22c55e'
  }
});

const emit = defineEmits(['update:modelValue']);

const track = ref(null);
const knob = ref(null);
const labelText = ref(null);

const toggle = () => {
  const newValue = props.modelValue === props.checkedValue ? props.uncheckedValue : props.checkedValue;
  emit('update:modelValue', newValue);
};

const animate = (isChecked, immediate = false) => {
  if (!knob.value || !labelText.value) return;

  const duration = immediate ? 0 : 0.6;
  const ease = "elastic.out(1, 0.75)";

  if (isChecked) {
    gsap.to(knob.value, {
      x: 27,
      backgroundColor: 'rgba(255, 255, 255, 1)',
      duration: duration,
      ease: ease
    });
    gsap.to(labelText.value, {
      color: props.activeColor,
      duration: 0.5
    });
    gsap.to(track.value, {
      backgroundColor: props.activeColor,
      duration: 0.3
    });
  } else {
    gsap.to(knob.value, {
      x: 0,
      backgroundColor: '#ffffff',
      duration: duration,
      ease: ease
    });
    gsap.to(labelText.value, {
      color: '#444444',
      duration: 0.5
    });
    gsap.to(track.value, {
      backgroundColor: '#444444',
      duration: 0.3
    });
  }
};

const handleHover = (isHovering) => {
  if (!knob.value) return;
  
  if (isHovering) {
    gsap.to(knob.value, {
      scale: 1.2,
      duration: 0.4,
      ease: "power2.out"
    });
  } else {
    gsap.to(knob.value, {
      scale: 1,
      duration: 0.3,
      ease: "power2.in"
    });
  }
};

onMounted(() => {
  animate(props.modelValue === props.checkedValue, true);
});

watch(() => props.modelValue, (newVal) => {
  animate(newVal === props.checkedValue);
});
</script>

<style scoped>
.glass-slide-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
  cursor: pointer;
  user-select: none;
  width: fit-content;
}

.status-label {
  font-size: 1.1rem;
  font-weight: 600;
  color: #444444;
  transition: all 0.3s ease;
}

.track {
  width: 78px;
  height: 36px;
  background: #444444;
  border-radius: 15px;
  padding: 4px;
  position: relative;
  display: flex;
  align-items: center;
  transition: background-color 0.4s ease;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.knob {
  width: 42px;
  height: 28px;
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  position: relative;
  will-change: transform, scale;
  border: 1px solid rgba(255, 255, 255, 0.5);
}
</style>
