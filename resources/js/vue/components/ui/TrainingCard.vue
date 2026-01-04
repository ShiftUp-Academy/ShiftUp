<template>
  <div 
    class="training-card" 
    ref="cardRef"
    :style="{ '--mouse-x': flash.x, '--mouse-y': flash.y, '--flash-opacity': flash.opacity }"
    @mousemove="handleFlashMove"
    @mouseleave="handleFlashLeave"
  >
    <div class="content">
        
      <div class="tags">
        <span class="tag tag-type" :class="type.includes('Présentiel') ? 'tag-in-person' : 'tag-online'">{{ type }}</span>
        <span class="tag tag-primary">{{ date }}</span>
        <span class="tag tag-secondary">{{ location }}</span>
      </div>

      <div class="title-wrapper">
        <h3 class="card-title">{{ title }}</h3>
      </div>

      <div class="author-info">
        <div class="author-image-wrapper">
            <img :src="authorImage" alt="Photo de profil de l'auteur" class="author-image">
            <div class="avatar-border-glow"></div>
        </div>
        <p class="author-name">{{ authorName }}</p>
      </div>
      
      <div class="separator"></div>

      <div class="hidden-content">
        <p class="description">{{ description }}</p>
      </div>
      
      <div class="action-footer">
        <p class="price">{{ price }}</p>
  
        <button class="action-btn">
          <span class="btn-text">En savoir plus</span>
          <img :src="ArrowIcon" alt="Flèche lien" class="btn-icon">
        </button>
      </div>
    </div>
    
    <div class="image-section">
      <div 
        class="bg-image" 
        :style="{ backgroundImage: `url('${image}')` }"
      ></div>
      
      <div class="overlay"></div>
      <div class="image-border-glow"></div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import ArrowIcon from '../../../assets/images/fleche-lien.svg';

defineProps({
  title: String,
  date: String,
  location: String,
  type: String,
  image: String,
  description: String,
  authorName: {
    type: String,
    default: 'Par Nantenaina Randria'
  },
  authorImage: {
    type: String,
    default: '/images/Bibliothèque/Nantenaina.jpg'
  },
  price: {
    type: String,
    default: '200K Ar'
  },
});

const flash = reactive({ x: '50%', y: '50%', opacity: 0 });
const cardRef = ref(null);

function handleFlashMove(event) {
  const rect = event.currentTarget.getBoundingClientRect();
  flash.x = `${(event.clientX - rect.left)}px`;
  flash.y = `${(event.clientY - rect.top)}px`;
  flash.opacity = 1;
}

function handleFlashLeave() {
  flash.opacity = 0;
}
</script>

<style scoped>
.training-card {
  position: relative;
  height: 60vh; 
  border-radius: 0; 
  overflow: hidden;
  cursor: pointer;
  background-color: #202020; 
  border: none; 
  border-right: 1px solid #333; 
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  display: flex;
  justify-content: flex-start;
  /* CORRECTION CLÉ: Assure que l'image et le contenu s'empilent verticalement */
  flex-direction: column; 
}
.training-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 1px;
    background-color: #333;
}

.training-card:hover {
  transform: translateY(-5px); 
  box-shadow: 0 10px 20px rgba(83, 83, 83, 0.06);
}

.content {
  flex: 1;
  padding: 24px;
  display: flex;
  flex-direction: column;
}

/* Tags (Inchangé) */
.tags {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.tag {
  padding: 4px 12px;
  border-radius: 5px;
  font-size: 0.8rem;
  font-weight: 500;
  text-transform: uppercase;
  backdrop-filter: blur(2px); 
}

.tag-primary {
  background: linear-gradient(to right, #0E7EC3, #8A38F5);
  color: white;
}

.tag-secondary {
  background-color: rgba(240, 240, 240, 0.2);
  color: #f8f8f8;
}

.tag-type {
  color: #ffffff;
  font-weight: 700;
}

.tag-in-person {
  background-color: #1A888D;
}

.tag-online {
  background-color: #A71543;
}

.card-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #f8f8f8;
  margin: 0 0 10px 0;
  line-height: 1.2;
}
.author-info {
    display: flex;
    align-items: center;
}

.author-image-wrapper {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 10px;
    border: 1px solid rgba(255, 255, 255, 0.1); 
    position: relative;
}

.avatar-border-glow {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 3;
  border-radius: inherit;
  padding: 1.2px;
  background: radial-gradient(
    100px circle at var(--mouse-x) var(--mouse-y), 
    rgba(255, 255, 255, 0.8), 
    transparent 60%
  );
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  opacity: var(--flash-opacity, 0);
  transition: opacity 0.3s ease;
}

.author-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.author-name {
    font-size: 0.9rem;
    /* Couleur ajustée pour être visible */
    color: #fbfbfb; 
    margin: 0;
}

.separator {
  width: 100%;
  height: 1px;
  background-color: #c9c9c9; 
  margin: 5px 0;
}
.hidden-content {
  max-height: 0; 
  overflow: hidden;
  opacity: 0;
  transition: max-height 0.5s ease-out, opacity 0.5s ease-out 0.2s;
}

.training-card:hover .hidden-content {
  max-height: 200px; 
  opacity: 1;
}

.description {
  color: #f6f6f6;
  font-size: 1rem;
  line-height: 1.5;
  margin: 0 0 15px 0; 
}

/* ACTION FOOTER (TOUJOURS VISIBLE) */
.action-footer {
    opacity: 1; 
    transform: translateY(0);
    transition: none; 
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
}

/* Prix */
.price {
    font-size: 1.2rem;
    font-weight: 500;
    color: white;
    margin: 0;
}

/* Bouton CTA */
.action-btn {
    display: flex;
    align-items: center;
    gap: 1px;
    background: none; 
    border: none;
    padding: 0;
    cursor: pointer;
    color: white;
    transition: transform 0.3s ease;
}

.btn-text {
    font-weight: 500;
    font-size: 1rem;
    margin-right: 0.5vw;
    text-transform: uppercase; 
}

.btn-icon {
    width: 13px;
    height: 13px;
    margin-bottom: 5px;
    transition: transform 0.3s ease;
    filter: invert(1);
}

.action-btn:hover {
    transform: translateX(5px); 
}

.action-btn:hover .btn-icon {
    transform: translateX(3px);
}

/* -------------------- Section Image - 50% de la carte -------------------- */
.image-section {
  position: relative;
  height: 50%; /* Hauteur de l'image comme défini dans votre dernier fichier */
  min-height: 190px;
  overflow: hidden;
  border-radius: 0 0 15px 15px; /* Optionnel: arrondir le bas pour correspondre au design */
}

.image-border-glow {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 6;
  border-radius: inherit;
  padding: 1.5px;
  background: radial-gradient(
    300px circle at var(--mouse-x) var(--mouse-y), 
    rgba(255, 255, 255, 0.5), 
    transparent 60%
  );
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  opacity: var(--flash-opacity, 0);
  transition: opacity 0.3s ease;
}

/* Background Image et Overlay */
.bg-image {
  position: absolute;
  top: 0; 
  left: 0;
  width: 100%; 
  height: 100%;
  background-size: cover;
  background-position: center;
  transition: transform 0.7s ease;
}

.training-card:hover .bg-image {
  transform: scale(1.05);
}
</style>