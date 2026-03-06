<template>
  <footer class="footer">
    <div class="footer-container">
      <div class="footer-columns">

        <div class="footer-section">
          <h3 class="section-title">{{ $t('Rubric') }}</h3>
          <ul class="section-list">
            <li v-for="link in linksRubrique" :key="link.label">
              <Link :href="link.url" class="link animated-link">
                <span v-for="(char, i) in splitText($t(link.label))" :key="i" class="char"
                  :style="{ 'animation-delay': `${i * 0.02}s` }">
                  {{ char === ' ' ? '\u00A0' : char }}
                </span>
              </Link>
            </li>
          </ul>
        </div>

        <div class="footer-section">
          <h3 class="section-title">{{ $t('Clients') }}</h3>
          <ul class="section-list">
            <li v-for="link in linksClients" :key="link.label">
              <Link :href="link.url" class="link animated-link">
                <span v-for="(char, i) in splitText($t(link.label))" :key="i" class="char"
                  :style="{ 'animation-delay': `${i * 0.02}s` }">
                  {{ char === ' ' ? '\u00A0' : char }}
                </span>
              </Link>
            </li>
          </ul>
        </div>

        <div class="footer-section">
          <h3 class="section-title">{{ $t('Contacts') }}</h3>
          <div class="contact-info">
            <p class="link">{{ $t('Footer.Address') }}</p>
            <a href="mailto:hello@shiftup.academy" class="link animated-link">
              <span v-for="(char, i) in splitText('hello@shiftup.academy')" :key="i" class="char"
                :style="{ 'animation-delay': `${i * 0.02}s` }">
                {{ char }}
              </span>
            </a>
            <p class="link">{{ $t('Footer.Phone') }}</p>
          </div>
        </div>
      </div>

      <div class="footer-newsletter">
        <p class="newsletter-text">
          {{ $t('NewsletterDesc') }}
        </p>

        <form class="email-input-group" @submit.prevent="submitNewsletter">
          <input type="email" v-model="form.email" :placeholder="$t('EmailPlaceholder')" class="email-input" required />
          <button type="submit" class="submit-btn" :disabled="form.processing">
            <svg viewBox="0 0 24 24" class="submit-icon-svg" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M5 12h14M12 5l7 7-7 7" />
            </svg>
          </button>
        </form>

        <p class="newsletter-disclaimer">
          {{ $t('NewsletterConsent') }} <Link href="/politique-de-confidentialite">{{ $t('PrivacyPolicy') }}</Link>.
          {{ $t('UnsubscribeText') }}
        </p>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="copyright">
        {{ $t('Footer.Copyright') }}
      </div>

      <div class="social-links">
        <a href="https://linkedin.com" target="_blank" class="social-icon" aria-label="LinkedIn">
          <i class="fa-brands fa-linkedin-in"></i>
        </a>
        <a href="https://www.facebook.com/nante.randria.gel" target="_blank" class="social-icon" aria-label="Facebook">
          <i class="fa-brands fa-facebook-f"></i>
        </a>
        <a href="https://www.youtube.com/@nr.shiftup" target="_blank" class="social-icon" aria-label="YouTube">
          <i class="fa-brands fa-youtube"></i>
        </a>
      </div>

      <div class="back-to-top">
        <a href="#" @click.prevent="scrollToTop" class="back-link animated-link">
          <span v-for="(char, i) in splitText($t('BackToTop'))" :key="i" class="char"
            :style="{ 'animation-delay': `${i * 0.02}s` }">
            {{ char === ' ' ? '\u00A0' : char }}
          </span>
          <i class="fa-solid fa-arrow-up icon-up"></i>
        </a>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const $t = (key) => page.props.translations?.[key] || key;

const form = useForm({
  email: ''
});

const submitNewsletter = () => {
  form.post('/newsletter/subscribe', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
    }
  });
};

const splitText = (text) => text.split('');

const scrollToTop = () => {
  if (window.lenis) {
    window.lenis.scrollTo(0);
  } else {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const linksRubrique = [
  { label: "Footer.Temoignages", url: "/temoignages" },
  { label: "Footer.Organisme", url: "/organisme" },
  { label: "Footer.CommentCaFonctionne", url: "/comment-ca-fonctionne" },
  { label: "Footer.ArticlesConseils", url: "/articles-conseils" },
  { label: "Footer.ContactezNous", url: "/contact" }
];

const linksClients = [
  { label: "Footer.NosProgrammes", url: "/programmes" },
  { label: "Footer.NosCoachings", url: "/coaching" },
  { label: "Footer.NosOffres", url: "/offres" },
  { label: "PrivacyPolicy", url: "/politique-de-confidentialite" }
];
</script>

<style scoped>
.footer {
  background-color: #1a1a1a;
  color: #ffffff;
  padding: 6rem 4rem 2rem 4rem;
}

.footer-container {
  max-width: 1400px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 4rem;
  margin-bottom: 8rem;
}

.footer-columns {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}

.footer-newsletter {
  display: flex;
  flex-direction: column;
}

.newsletter-text {
  margin-top: 1.5rem;
  font-size: 1.6rem;
  color: #e7e7e7;
  line-height: 1.3;
  max-width: 35vw;
}

.email-input-group {
  display: flex;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.4);
  padding-bottom: 0.5rem;
  transition: border-color 0.3s;
}

.email-input-group:focus-within {
  border-color: #ffffff;
}

.email-input {
  background: transparent;
  border: none;
  color: #ffffff;
  flex-grow: 1;
  outline: none;
  font-size: 2.5rem;
  font-weight: 300;
  letter-spacing: -1px;
}

.email-input::placeholder {
  color: rgba(255, 255, 255, 0.2);
}

.submit-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: white;
}

.submit-icon-svg {
  width: 2rem;
  height: 2rem;
}

.newsletter-disclaimer {
  margin-top: 1.5rem;
  font-size: 1rem;
  color: #888;
  line-height: 1.5;
  max-width: 40vw;
}

.newsletter-disclaimer a {
  color: #888;
  text-decoration: underline;
}

/* Sections de liens & Animation de chute */
.section-title {
  font-size: 1.3rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.section-list {
  list-style: none;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.link {
  text-decoration: none;
  color: #999;
  font-size: 1.3rem;
  transition: color 0.2s;
}

.animated-link {
  display: inline-flex;
  overflow: hidden;
  height: 1.4rem;
  line-height: 1.4rem;
  cursor: pointer;
}

.animated-link .char {
  display: inline-block;
  transition: color 0.3s ease;
}

@keyframes letter-fall {
  0% {
    transform: translateY(0);
    opacity: 1;
  }

  49% {
    transform: translateY(100%);
    opacity: 0;
  }

  50% {
    transform: translateY(-100%);
    opacity: 0;
  }

  100% {
    transform: translateY(0);
    opacity: 1;
  }
}

.animated-link:hover .char {
  animation: letter-fall 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
  color: #ffffff;
}

.footer-bottom {
  max-width: 1400px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1rem;
  color: #ffffff;
  padding-top: 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.social-links {
  display: flex;
  gap: 1.5rem;
}

.social-icon {
  color: white;
  font-size: 1.2rem;
  transition: transform 0.3s ease;
}

.social-icon:hover {
  transform: translateY(-3px);
  color: #8A38F5;
}

.back-link {
  color: white;
  display: flex;
  text-decoration: none;
  align-items: center;
}

.fa-arrow-up {
  font-size: 0.9rem;
  margin-left: 1.5vw;
}

@media (max-width: 1024px) {
  .footer-container {
    grid-template-columns: 1fr;
    gap: 2rem;
    margin-bottom: 4rem;
  }

  .newsletter-text {
    max-width: 80%;
    font-size: 1.6rem;
    line-height: 1.1;
  }

  .link {
    font-size: 1.5rem;
    line-height: 1.1;
    padding-top: 0;
    padding-bottom: 0;
  }

  .email-input {
    font-size: 1.6rem;
  }

  .newsletter-disclaimer {
    max-width: 100%;
  }

  .footer {
    padding: 4rem 2rem;
  }
}

@media (max-width: 768px) {
  .footer-columns {
    grid-template-columns: 1fr;
    gap: 2rem;
  }

  .footer-bottom {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
    padding-bottom: 20vh;
  }

  .social-links {
    justify-content: center;
  }

  .back-to-top {
    margin-top: 10px;
  }
}
</style>