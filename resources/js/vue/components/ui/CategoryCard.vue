<template>
  <Link :href="detailUrl" class="category-card" ref="cardRef">

    <div class="image-section">
      <div class="bg-image" :style="{ backgroundImage: `url('${image}')` }"></div>
      <div class="overlay"></div>
    </div>

    <div class="content">
      <div class="title-wrapper">
        <h3 class="card-title">{{ title }}</h3>
      </div>

      <div class="author-info">
        <div class="author-image-wrapper">
          <img :src="authorImage" alt="Photo de profil de l'auteur" class="author-image">
        </div>
        <div class="author-details">
          <span class="author-label">par</span>
          <p class="author-name">{{ authorName }}</p>
        </div>
      </div>

      <div class="separator"></div>

      <div class="description-wrapper">
        <p class="description">{{ description }}</p>
      </div>

      <div class="action-footer">
        <p class="price">{{ displayPrice }}</p>

        <button class="action-btn">
          <span class="btn-text">AJOUTER AU PANIER</span>
          <svg viewBox="0 0 16 16" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
            <line x1="0" y1="12" x2="12" y2="0"></line>
            <polyline points="2 0 12 0 12 10"></polyline>
          </svg>
        </button>
      </div>
    </div>
  </Link>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  id: [Number, String],
  title: String,
  image: String,
  description: String,
  authorName: {
    type: String,
    default: 'NANTENAINA RANDRIA'
  },
  authorImage: {
    type: String,
    default: '/images/Bibliothèque/Nantenaina.jpg'
  },
  price: {
    type: String,
    default: '190k Ar'
  },
  isOffer: {
    type: Boolean,
    default: false
  }
});

const displayPrice = computed(() => {
  if (!props.price) return '';
  const numericValue = parseInt(props.price.replace(/[^0-9]/g, ''));
  if (!isNaN(numericValue) && numericValue >= 1000) {
    return Math.round(numericValue / 1000) + ' k';
  }
  return props.price.replace(/\s*Ar$/i, '');
});

const detailUrl = computed(() => {
  if (props.isOffer) {
    return '/offres';
  }
  return props.id ? `/programmes/${props.id}` : '#';
});
</script>

<style scoped>
.category-card {
  position: relative;
  height: 60vh;
  width: 100%;
  border-radius: 0;
  overflow: hidden;
  cursor: pointer;
  background-color: #202020;
  border: none;
  display: flex;
  flex-direction: column;
}

.image-section {
  position: relative;
  height: 65%;
  overflow: hidden;
}

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

.overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.115) 100%);
}

.category-card:hover .bg-image {
  transform: scale(1.05);
}

.content {
  flex: 1;
  padding: 20px;
  display: flex;
  flex-direction: column;
  background-color: #1a1a1a;
  color: white;
  justify-content: space-between;
}

.card-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: #f8f8f8;
  margin: 0 0 10px 0;
  line-height: 1.2;
}

.author-info {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.author-image-wrapper {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  overflow: hidden;
  margin-right: 8px;
  border: 1px solid #444;
  flex-shrink: 0;
}

.author-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.author-details {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 4px;
}

.author-label {
  font-size: 0.8rem;
  color: #888;
}

.author-name {
  font-size: 0.8rem;
  color: #ccc;
  margin: 0;
  text-transform: uppercase;
  font-weight: 500;
}

.separator {
  width: 100%;
  height: 1px;
  background-color: #444;
  margin: 10px 0;
}

.description-wrapper {
  max-height: 0;
  opacity: 0;
  overflow: hidden;
  transition: max-height 0.5s ease-out, opacity 0.5s ease-out 0.2s;
  margin-bottom: 0;
}

.category-card:hover .description-wrapper {
  max-height: 200px;
  opacity: 1;
  margin-bottom: 15px;
}

.action-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
}

.price {
  font-size: 1.1rem;
  font-weight: 600;
  color: white;
  margin: 0;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  color: #ccc;
  transition: color 0.3s ease;
}

.action-btn:hover {
  color: white;
}

.btn-text {
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
}

.btn-icon {
  transition: transform 0.3s ease;
}

.action-btn:hover .btn-icon {
  transform: translate(2px, -2px);
}

@media (max-width: 768px) {
  .category-card {
    height: auto;
    margin-left: 2vw;
    min-height: 480px;
    width: calc(100% - 4vw);
  }

  .image-section {
    height: 180px;
  }

  .content {
    padding: 20px 18px;
    background-color: #121212;
  }

  .card-title {
    font-size: 1.4rem;
    line-height: 1.1;
    margin-bottom: 5px;
  }

  .author-info {
    margin-bottom: 5px;
  }

  .description-wrapper {
    max-height: none;
    /* Auto height based on content */
    opacity: 1;
    margin-top: 5px;
    margin-bottom: 15px;
    height: auto;
  }

  .description {
    height: auto;
    max-height: none;
  }

  .separator {
    opacity: 0.3;
    margin-left: 0;
    /* Reset margin-left of separator to align with content */
    width: 100%;
  }

  .action-footer {
    padding-top: 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    gap: 10px;
  }

  .price {
    font-size: 1.5rem;
    font-weight: 600;
    white-space: nowrap;
    min-width: fit-content;
  }

  .action-btn {
    padding: 12px 20px;
    flex-grow: 1;
    justify-content: center;
  }

  .btn-text {
    font-size: 0.85rem;
    letter-spacing: 0.05em;
  }

  .btn-icon {
    width: 14px;
    height: 14px;
  }
}
</style>
