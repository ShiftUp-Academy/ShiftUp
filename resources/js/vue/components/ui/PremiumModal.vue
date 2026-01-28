<template>
  <teleport to="body">
    <transition @before-enter="beforeModalEnter" @enter="enterModal" @leave="leaveModal" :css="false">
      <div v-if="isOpen" class="premium-modal-overlay" @click.self="close" data-lenis-prevent>
        <div class="premium-modal-content" :style="{ width: width }" :class="{ 'dark-theme': dark }">
          <div class="modal-header" v-if="header">
            <h2 class="modal-title">{{ header }}</h2>
            <div class="modal-header-right">
              <slot name="header-actions" />
              <button v-if="showClose" class="modal-close-btn" @click="close">
                <i class="fa-solid fa-times"></i>
              </button>
            </div>
          </div>

          <div class="modal-body" :class="{ 'no-padding': noPadding }" data-lenis-prevent>
            <slot />
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { ref } from 'vue';
import { gsap } from 'gsap';

const props = defineProps({
  isOpen: Boolean,
  header: {
    type: String,
    default: ''
  },
  width: {
    type: String,
    default: '500px'
  },
  noPadding: {
    type: Boolean,
    default: false
  },
  origin: {
    type: Object,
    default: () => ({ x: window.innerWidth / 2, y: window.innerHeight / 2 })
  },
  dark: {
    type: Boolean,
    default: false
  },
  showClose: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['close']);

const close = () => emit('close');

/* --- Animations GSAP --- */
const beforeModalEnter = (el) => {
  gsap.set(el, {
    opacity: 0,
    '--overlay-blur': '0px',
    '--overlay-opacity': 0
  });

  const content = el.querySelector('.premium-modal-content');
  if (content) {
    const originX = props.origin.x || window.innerWidth / 2;
    const originY = props.origin.y || window.innerHeight / 2;

    gsap.set(content, {
      x: originX - window.innerWidth / 2,
      y: originY - window.innerHeight / 2,
      scaleX: 0.05,
      scaleY: 0.02,
      opacity: 0,
      borderRadius: '60% 40% 70% 30% / 60% 30% 70% 40%',
      transformOrigin: 'center center',
      force3D: true
    });
  }
};

const enterModal = (el, done) => {
  const content = el.querySelector('.premium-modal-content');
  const tl = gsap.timeline({
    onComplete: done,
    defaults: { ease: "expo.out" }
  });

  tl.to(el, {
    opacity: 1,
    duration: 0.3,
    ease: "power2.inOut"
  });

  tl.to(el, {
    '--overlay-blur': '10px',
    '--overlay-opacity': 1,
    duration: 0.5,
    ease: "sine.inOut"
  }, 0);

  if (content) {
    tl.to(content, {
      x: 0,
      y: 0,
      scaleX: 1,
      scaleY: 1,
      opacity: 1,
      borderRadius: '24px',
      duration: 0.8,
      ease: "expo.out"
    }, 0.05);
  }
};

const leaveModal = (el, done) => {
  const content = el.querySelector('.premium-modal-content');
  const tl = gsap.timeline({ onComplete: done });

  if (content) {
    const originX = props.origin.x || window.innerWidth / 2;
    const originY = props.origin.y || window.innerHeight / 2;

    tl.to(content, {
      x: originX - window.innerWidth / 2,
      y: originY - window.innerHeight / 2,
      scaleX: 0.05,
      scaleY: 0.02,
      opacity: 0,
      borderRadius: '60% 40% 70% 30% / 60% 30% 70% 40%',
      duration: 0.6,
      ease: "expo.in"
    });
  }

  tl.to(el, {
    '--overlay-blur': '0px',
    '--overlay-opacity': 0,
    opacity: 0,
    duration: 0.5,
    ease: "power2.inOut"
  }, 0.1);
};
</script>

<style scoped>
.premium-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 20000;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px 20px;
  --overlay-blur: 0px;
  --overlay-opacity: 0;
}

.premium-modal-overlay::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(64, 0, 110, calc(0.3 * var(--overlay-opacity)));
  backdrop-filter: blur(var(--overlay-blur));
  filter: hue-rotate(180deg);
  z-index: -1;
}

.premium-modal-content {
  background: white;
  border-radius: 24px;
  max-width: 100%;
  max-height: 90vh;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  position: relative;
  will-change: transform, opacity;
  display: flex;
  flex-direction: column;
  transition: background 0.3s ease, color 0.3s ease;
}

.premium-modal-content.dark-theme {
  background: #000;
  color: #fff;
  border: 1px solid #333;
}

.premium-modal-content.dark-theme .modal-title {
  color: #fff;
}

.premium-modal-content.dark-theme .modal-header {
  border-bottom-color: #222;
}

.premium-modal-content.dark-theme .modal-close-btn {
  background: #333;
}

.modal-header {
  padding: 24px 50px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f0f0f0;
  flex-shrink: 0;
}

.modal-header-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.modal-title {
  font-size: 2rem;
  font-weight: 500;
  line-height: 1.2;
  color: #111;
  margin: 0;
}

.modal-close-btn {
  background: #000;
  border: none;
  font-size: 0.9rem;
  cursor: pointer;
  color: #fff;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.modal-close-btn:hover {
  transform: rotate(90deg) scale(1.1);
  background: #222;
}

.modal-body {
  padding: 0px 50px 30px 50px;
  overflow-y: auto;
  flex: 1;
  min-height: 0;
  overscroll-behavior: contain;
  -webkit-overflow-scrolling: touch;
}

.modal-body.no-padding {
  padding: 0;
}

.modal-body::-webkit-scrollbar {
  width: 8px;
}

.modal-body::-webkit-scrollbar-track {
  background: transparent;
}

.modal-body::-webkit-scrollbar-thumb {
  background: rgba(138, 56, 245, 0.3);
  border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: rgba(138, 56, 245, 0.5);
}

.dark-theme .modal-body::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
}

.dark-theme .modal-body::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}

@media (max-width: 640px) {
  .modal-header {
    padding: 20px;
  }

  .modal-body {
    padding: 0 20px 20px 20px;
  }

  .modal-title {
    font-size: 1.5rem;
  }
}
</style>
