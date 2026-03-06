<template>
  <div class="contact-page">
    <div class="contact-left">
      <div class="contact-list">

        <div class="contact-item">
          <div class="icon-circle">
            <i class="fa-solid fa-envelope-open-text"></i>
          </div>
          <div class="item-content">
            <p class="content-value">{{ $t('Contact.helloshiftupacademy') }}</p>
            <p class="content-label">{{ $t('Contact.email') }}</p>
          </div>
        </div>

        <div class="contact-item">
          <div class="icon-circle">
            <i class="fa-solid fa-phone-volume"></i>
          </div>
          <div class="item-content">
            <p class="content-value">038 34 540 81</p>
            <p class="content-label">{{ $t('Contact.tlphone') }}</p>
          </div>
        </div>

        <div class="map-wrapper shadow-lg">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3775.622799368237!2d47.51436257475886!3d-18.859432405865626!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x21f087006c4452df%3A0xd971034fc8be97e!2sEpik%20Brand%20Madagascar!5e0!3m2!1sfr!2sus!4v1767361271740!5m2!1sfr!2sus"
            class="dark-map" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>


        <div class="working-hours">
          <div class="hours-row">
            <span class="day-label">{{ $t('Contact.jours_de_travail') }}</span>
            <span class="dots-spacer"></span>
            <span class="time-value">{{ $t('Contact.9h00__18h00') }}</span>
          </div>
          <div class="hours-row">
            <span class="day-label">{{ $t('Contact.samedi') }}</span>
            <span class="dots-spacer"></span>
            <span class="time-value">{{ $t('Contact.12h00__18h00') }}</span>
          </div>
        </div>
      </div>

      <div class="pnj-container">
        <div class="comic-bubble-wrapper" ref="bubbleWrapper">
          <div class="bubble-relative-container">
            <div class="paper-bubble-rectangular shadow-2xl" ref="paperBubble">
              <div class="bubble-inner">
                <p>{{ $t('Contact.le_coaching') }} <br> {{ $t('Contact.vous_intresse') }}<br>{{
                  $t('Contact.contactez_moi') }}</p>
              </div>

              <!-- 4 Folded corners -->
              <div class="paper-fold fold-tl"></div>
              <div class="paper-fold fold-tr"></div>
              <div class="paper-fold fold-bl"></div>
              <div class="paper-fold fold-br"></div>
            </div>
            <div class="comic-tail-bottom"></div>
          </div>
        </div>
        <div class="pnj-display">
          <img :src="contactImg" alt="ShiftUp Contact" class="pnj-img" />
        </div>
      </div>

      <div class="social-links-container">
        <div class="social-links">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-linkedin-in"></i></a>
          <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
    </div>

    <div class="contact-right-outer">
      <div class="contact-gradient-box shadow-2xl">
        <ShaderBackground :colors="customShaderColors" class="full-box-shader">
          <form @submit.prevent="submitForm" class="contact-custom-form">
            <div class="form-row">
              <div class="form-line-group">
                <label>{{ $t('Contact.nom') }}</label>
                <input type="text" v-model="form.nom" :placeholder="$t('Contact.votre_nom')" required />
              </div>
              <div class="form-line-group">
                <label>{{ $t('Contact.prnom') }}</label>
                <input type="text" v-model="form.prenom" :placeholder="$t('Contact.votre_prnom')" required />
              </div>
            </div>

            <div class="form-line-group">
              <label>{{ $t('Contact.sujet') }}</label>
              <input type="text" v-model="form.sujet" :placeholder="$t('Contact.de_quoi_sagitil')" required />
            </div>

            <div class="form-line-group">
              <label>{{ $t('Contact.email_2') }}</label>
              <input type="email" v-model="form.email" :placeholder="$t('Contact.exemplegmailcom')" required />
            </div>

            <div class="form-line-group">
              <label>{{ $t('Contact.message') }}</label>
              <textarea v-model="form.message" rows="2" :placeholder="$t('Contact.ditesnous_tout')" required></textarea>
            </div>

            <div class="form-footer">
              <button type="submit" class="contact-submit-btn" :disabled="isLoading">
                {{ isLoading ? $t('Contact.envoi_en_cours') : $t('Contact.envoyer_le_message') }}
              </button>
            </div>
          </form>
        </ShaderBackground>
      </div>
    </div>
    <Toast />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { gsap } from 'gsap';
import ShaderBackground from '../components/ui/ShaderBackground.vue';
import axios from 'axios';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const contactImg = '/images/contact/Contact.png';
const logoSrc = '/images/logo-site-blanc.png';

const toast = useToast();
const isLoading = ref(false);
const paperBubble = ref(null);
const bubbleWrapper = ref(null);

const customShaderColors = {
  primary: '#F7B455',
  secondary: '#F7B455',
  accent: '#A71543',
  dark: '#000000'
};

const form = reactive({
  nom: '',
  prenom: '',
  sujet: '',
  email: '',
  message: ''
});

onMounted(() => {
  // Bubbles Animations
  if (paperBubble.value) {
    gsap.to(paperBubble.value, {
      duration: 0.12,
      repeat: -1,
      yoyo: true,
      skewX: "1.2deg",
      skewY: "0.8deg",
      scale: 1.001,
      ease: "none"
    });

    gsap.to(bubbleWrapper.value, {
      y: -15,
      duration: 3,
      repeat: -1,
      yoyo: true,
      ease: "power1.inOut"
    });
  }
});

const submitForm = async () => {
  isLoading.value = true;
  try {
    const response = await axios.post('/contact/send', form);
    toast.add({
      severity: 'success',
      summary: $t('Success'),
      detail: response.data.message || $t('Contact.message_envoye'),
      life: 5000
    });
    Object.assign(form, { nom: '', prenom: '', sujet: '', email: '', message: '' });
  } catch (error) {
    console.error("Error sending contact email", error);
    toast.add({
      severity: 'error',
      summary: $t('Error'),
      detail: error.response?.data?.message || $t('Contact.erreur_envoi'),
      life: 5000
    });
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');

.contact-page {
  height: 100vh;
  width: 100%;
  display: flex;
  background-color: #0c0c0c;
  /* Dark Mode Background */
  overflow: hidden;
  font-family: 'Plus Jakarta Sans', sans-serif;
  align-items: center;
}

.contact-left {
  flex: 1;
  padding: 60px 80px;
  display: flex;
  flex-direction: column;
  position: relative;
  height: 100%;
  z-index: 10;
}

.contact-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: 4vh;
  /* Reduced from 10vh to fix layout */
  margin-left: 0;
  margin-bottom: auto;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 15px;
}

.icon-circle {
  width: 40px;
  height: 40px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  color: #fff;
  background: rgba(255, 255, 255, 0.05);
}

.item-content {
  display: flex;
  flex-direction: column;
}

.content-value {
  font-size: 1.1rem;
  font-weight: 600;
  color: #ffffff;
  margin: 0;
}

.content-label {
  font-size: 0.65rem;
  font-weight: 700;
  color: #aaa;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0;
}

.working-hours {
  margin-top: 25px;
  /* Fixed: was 30vh */
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-width: 400px;
}

.hours-row {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #ccc;
  font-size: 0.9rem;
  font-weight: 500;
}

.dots-spacer {
  flex: 1;
  border-bottom: 2px dotted rgb(255 255 255 / 33%);
  height: 1px;
}

.pnj-container {
  position: absolute;
  right: -60px;
  bottom: 0px;
  width: 480px;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  pointer-events: none;
  z-index: 1;
}

.comic-bubble-wrapper {
  width: 100%;
  margin-bottom: -55px;
  z-index: 20;
  display: flex;
  justify-content: flex-end;
}

.bubble-relative-container {
  position: relative;
  transform: rotate(10deg);
}

.paper-bubble-rectangular {
  background: #8A38F5;
  color: white;
  padding: 30px 45px;
  position: relative;
  min-width: 270px;
  clip-path: polygon(
      /* Top jagged */
      2% 5%, 35% 2%, 65% 5%, 98% 3%,
      /* Right jagged */
      99% 65%, 100% 65%, 100% 80%,
      /* Bottom jagged */
      100% 94%, 55% 98%, 25% 95%, 0% 97%,
      /* Left jagged */
      1% 65%, 4% 35%);
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  z-index: 2;
}

.bubble-inner p {
  font-size: 1.25rem;
  font-weight: 900;
  margin-right: -5vw;
  margin: 0;
  line-height: 1.4;
  text-transform: uppercase;
  letter-spacing: -0.5px;
  font-style: italic;
}

.paper-fold {
  position: absolute;
  width: 25px;
  height: 25px;
  background: #8A38F5;
  z-index: 3;
}

.fold-tl {
  top: 0;
  left: 0;
  clip-path: polygon(0 0, 100% 0, 0 100%);
}

.fold-tr {
  top: 0;
  right: 0;
  clip-path: polygon(0 0, 100% 100%, 100% 0);
}

.fold-bl {
  bottom: 0;
  left: 0;
  clip-path: polygon(0 0, 0 100%, 100% 100%);
}

.fold-br {
  bottom: 0;
  right: 0;
  clip-path: polygon(100% 0, 100% 100%, 0 100%);
}

.comic-tail-bottom {
  position: absolute;
  bottom: -10px;
  right: 50px;
  width: 0;
  height: 0;
  border-left: 20px solid transparent;
  border-right: 20px solid transparent;
  border-top: 40px solid #8A38F5;
  z-index: 1;
}

.pnj-display {
  width: 100%;
  margin-right: -4.3vw;
  margin-bottom: -2vh;
}

.pnj-img {
  width: 100%;
  height: auto;
  filter: drop-shadow(-20px 20px 50px rgba(0, 0, 0, 0.15));
}

.social-links-container {
  padding-top: 20px;
}

.social-links {
  display: flex;
  gap: 15px;
}

.social-links a {
  width: 40px;
  height: 40px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  transition: all 0.3s ease;
}

.social-links a:hover {
  color: #8A38F5;
  transform: translateY(-3px);
  border-color: #8A38F5;
}

.map-wrapper {
  width: 81%;
  height: 42vh;
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.1);
  margin: 0;
  position: relative;
  z-index: 0;
}

.dark-map {
  width: 100%;
  height: 100%;
  border: 0;
  filter: invert(100%) hue-rotate(180deg) brightness(1.2) contrast(0.7);
  display: block;
}



/* ASYMMETRICAL HEIGHT (85vh) */
.contact-right-outer {
  flex: 1;
  padding: 40px;
  height: 85vh;
  margin-right: 2vw;
  margin-top: 9vh;
  display: flex;
  align-items: center;
}

.contact-gradient-box {
  width: 100%;
  height: 100%;
  background: #000;
  /* Fallback */
  border-radius: 50px;
  color: white;
  display: flex;
  flex-direction: column;
  justify-content: center;
  overflow: hidden;
  /* Important for shader corners */
  position: relative;
}

.full-box-shader {
  padding: 60px 70px;
  width: 100%;
  height: 100%;
}


.form-title-right {
  font-size: 2.2rem;
  font-weight: 800;
  margin-bottom: 30px;
}

.contact-custom-form {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-row {
  display: flex;
  gap: 20px;
}

.form-line-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
  flex: 1;
}

.form-line-group label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #ffffff;
}

.form-line-group input,
.form-line-group textarea {
  background: transparent;
  border: none;
  border-bottom: 1px solid rgba(255, 255, 255, 0.4);
  padding: 10px 0;
  color: white;
  font-size: 1.1rem;
  outline: none;
  font-family: inherit;
}

.form-line-group input::placeholder,
.form-line-group textarea::placeholder {
  color: white;
  opacity: 0.8;
}

.form-line-group input:focus,
.form-line-group textarea:focus {
  border-bottom-color: white;
}

.form-footer {
  margin-top: 15px;
}

.contact-submit-btn {
  width: 100%;
  background: #111;
  color: white;
  border: none;
  padding: 16px;
  border-radius: 15px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
}

@media (max-width: 1024px) {
  .contact-page {
    flex-direction: column-reverse;
    height: auto;
    overflow-y: auto;
    padding-top: 100px;
    align-items: stretch;
  }

  .contact-left {
    padding: 20px 5vw;
    height: auto;
    flex: none;
  }

  .contact-list {
    margin-top: 20px;
  }

  .map-wrapper {
    width: 100%;
    height: 300px;
    margin-top: 20px;
  }

  .contact-right-outer {
    flex: none;
    padding: 20px 5vw 60px;
    height: auto;
    margin-right: 0;
    margin-top: 0;
  }

  .contact-gradient-box {
    border-radius: 30px;
  }

  .full-box-shader {
    padding: 40px 30px;
  }

  .pnj-container {
    position: relative;
    right: auto;
    width: 100%;
    max-width: 350px;
    margin: 40px auto 0;
    align-items: center;
  }

  .pnj-display {
    margin-right: 0;
  }

  .bubble-inner p {
    font-size: 1rem;
  }

  .paper-bubble-rectangular {
    min-width: 200px;
    padding: 20px 30px;
  }
}

@media (max-width: 768px) {
  .form-row {
    flex-direction: column;
    gap: 15px;
  }
}
</style>
