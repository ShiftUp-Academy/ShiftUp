<template>
  <div class="status-toggle-container">
    <div class="toggle-track">
      <div 
        class="toggle-indicator" 
        :class="{ 'is-right': modelValue === 'right' }"
      ></div>
      <button 
        class="toggle-btn" 
        :class="{ active: modelValue === 'left' }" 
        @click="$emit('update:modelValue', 'left')"
      >
        {{ leftLabel }}
      </button>
      <button 
        class="toggle-btn" 
        :class="{ active: modelValue === 'right' }" 
        @click="$emit('update:modelValue', 'right')"
      >
        {{ rightLabel }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: String,
    default: 'left'
  },
  leftLabel: {
    type: String,
    default: 'Options A'
  },
  rightLabel: {
    type: String,
    default: 'Options B'
  }
});

defineEmits(['update:modelValue']);
</script>

<style scoped>
.status-toggle-container {
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 30px 0;
  width: 100%;
}

.toggle-track {
  display: flex;
  background: #f8f9fa;
  border-radius: 20px;
  position: relative;
  padding: 6px;
  width: 100%;
  border: 1px solid #e9ecef;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
}

.toggle-btn {
  flex: 1;
  background: none;
  border: none;
  padding: 16px 0;
  font-size: 1rem;
  font-weight: 700;
  z-index: 2;
  cursor: pointer;
  color: #adb5bd;
  transition: all 0.4s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  letter-spacing: 0.5px;
}

.toggle-btn.active {
  color: #ffffff;
}

.toggle-indicator {
  position: absolute;
  top: 6px;
  left: 6px;
  width: calc(50% - 6px);
  bottom: 6px;
  background: linear-gradient(90deg, #0E7EC3, #8A38F5);
  border-radius: 16px;
  box-shadow: 0 8px 15px rgba(138, 56, 245, 0.25);
  z-index: 1;
  transition: transform 0.6s cubic-bezier(0.19, 1, 0.22, 1);
}

.toggle-indicator.is-right {
  transform: translateX(100%);
}
</style>
