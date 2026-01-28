<template>
  <button :type="type" class="premium-btn" :style="{ width: width }" :disabled="disabled || loading"
    @click="handleClick">
    <div class="btn-shine"></div>
    <span class="btn-content">
      <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
      <slot>{{ text }}</slot>
    </span>
  </button>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
  text: {
    type: String,
    default: 'Button'
  },
  width: {
    type: String,
    default: 'auto'
  },
  type: {
    type: String,
    default: 'button'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  href: {
    type: String,
    default: null
  }
});

const emit = defineEmits(['click']);

const handleClick = (event) => {
  if (props.disabled || props.loading) return;

  if (props.href) {
    router.visit(props.href);
  }

  emit('click', event);
};
</script>

<style scoped>
.premium-btn {
  position: relative;
  padding: 14px 28px;
  background: var(--btn-bg, #111);
  color: #fff;
  border: none;
  border-radius: 15px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  overflow: hidden;
  transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.premium-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-content {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  gap: 8px;
}

.premium-btn::before {
  content: '';
  position: absolute;
  inset: 0;
  background: var(--btn-gradient, linear-gradient(90deg, #0E7EC3, #8A38F5, #0E7EC3));
  background-size: 200% 100%;
  opacity: 0;
  transition: opacity 0.5s ease;
  z-index: 1;
}

.premium-btn:not(:disabled):hover {
  transform: translateY(-3px) scale(1.02);
  box-shadow: 0 10px 20px rgba(138, 56, 245, 0.3);
}

.premium-btn:not(:disabled):hover::before {
  opacity: 1;
  animation: gradient-move 2s linear infinite;
}

.btn-shine {
  position: absolute;
  top: 0;
  left: -100%;
  width: 60%;
  height: 100%;
  background: linear-gradient(90deg,
      transparent,
      rgba(255, 255, 255, 0.4),
      transparent);
  transform: skewX(-20deg);
  transition: 0.6s;
  z-index: 3;
}

.premium-btn:not(:disabled):hover .btn-shine {
  left: 140%;
}

@keyframes gradient-move {
  0% {
    background-position: 0% 50%;
  }

  100% {
    background-position: 200% 50%;
  }
}

.mr-2 {
  margin-right: 0.5rem;
}
</style>
