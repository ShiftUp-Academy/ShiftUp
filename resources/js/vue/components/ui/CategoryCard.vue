<template>
  <div class="category-card" ref="cardRef">
    
    <!-- IMAGE SECTION (MOVED TO TOP) -->
    <div class="image-section">
      <div 
        class="bg-image" 
        :style="{ backgroundImage: `url('${image}')` }"
      ></div>
      <div class="overlay"></div>
    </div>

    <!-- CONTENT SECTION (MOVED TO BOTTOM) -->
    <div class="content">
      
      <!-- REMOVED TAGS (Date/Location) as requested -->

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

      <!-- Hidden content for description if needed, or keep it visible -->
      <div class="description-wrapper">
        <p class="description">{{ description }}</p>
      </div>
      
      <div class="action-footer">
        <p class="price">{{ price }}</p>
  
        <button class="action-btn">
          <span class="btn-text">AJOUTER AU PANIER</span>
          <svg viewBox="0 0 16 16" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon">
             <line x1="0" y1="12" x2="12" y2="0"></line>
             <polyline points="2 0 12 0 12 10"></polyline>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
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
  flex-direction: column; /* Image Top, Content Bottom */
}

/* -------------------- Section Image (65%) -------------------- */
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
  top: 0; left: 0; width: 100%; height: 100%;
  background: linear-gradient(to bottom, rgba(0,0,0,0) 0%, rgba(0,0,0,0.6) 100%);
}

.category-card:hover .bg-image {
  transform: scale(1.05);
}

/* -------------------- Contenu (35%) -------------------- */
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

/* Description Transition */
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

/* ACTION FOOTER */
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
</style>
