<template>
  <div class="auth-page">
    <!-- LEFT SIDE IMAGE -->
    <div class="auth-image-col">
        <img :src="authCoverImg" alt="Auth Cover" class="cover-image" />
    </div>

    <!-- RIGHT SIDE FORM -->
    <div class="auth-form-col">
      <Toast />
      <div class="auth-header">
        <!-- ... existing header ... -->
        <div class="toggle-container">
            <button 
            class="toggle-btn" 
            :class="{ active: mode === 'login' }" 
            @click="setMode('login')"
            >
            Connexion
        </button>
        <button 
            class="toggle-btn" 
            :class="{ active: mode === 'signup' }" 
            @click="setMode('signup')"
            >
            Inscription
        </button>
            <div class="toggle-indicator" ref="indicator"></div>
        </div>
      </div>

      <div class="form-wrapper">
         <transition 
            @before-enter="beforeEnter"
            @enter="enter"
            @leave="leave"
            :css="false"
            mode="out-in"
         >
            
            <!-- LOGIN FORM -->
            <form v-if="mode === 'login'" key="login" @submit.prevent="submitLogin" class="auth-form">
                <div class="input-group form-element">
                    <label>Email</label>
                    <input type="email" v-model="loginForm.email" placeholder="exemple@gmail.com" required>
                </div>
                <div class="input-group form-element">
                    <label>Mot de passe</label>
                    <input type="password" v-model="loginForm.password" placeholder="votre mot de passe ici" required>
                </div>

                 <div class="form-actions form-element">
                    <a href="#" class="forgot-pass">Mot de passe oublié ?</a>
                </div>

                <div class="auth-buttons-row form-element">
                    <button type="submit" class="primary-btn">
                        <span class="btn-text">Se Connecter</span>
                        <div class="btn-shine"></div>
                    </button>
                    <a href="/auth/google/redirect" class="google-btn">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="google-icon">
                        <span>via Google</span>
                    </a>
                </div>
            </form>

            <form v-else key="signup" @submit.prevent="submitSignup" class="auth-form">
                <div class="input-row form-element">
                    <div class="input-group">
                        <label>Nom</label>
                        <input type="text" v-model="signupForm.lastName" placeholder="Votre nom" required>
                    </div>
                    <div class="input-group">
                        <label>Prénom</label>
                        <input type="text" v-model="signupForm.firstName" placeholder="Votre prénom" required>
                    </div>
                </div>

                <div class="input-group form-element">
                    <label>Email</label>
                    <input type="email" v-model="signupForm.email" placeholder="exemple@mail.com" required>
                </div>
                <div class="input-group form-element">
                    <label>Mot de passe</label>
                    <input type="password" v-model="signupForm.password" placeholder="Votre mot de passe" required>
                </div>

                <div class="auth-buttons-row form-element">
                    <button type="submit" class="primary-btn">
                        <span class="btn-text">S'inscrire</span>
                        <div class="btn-shine"></div>
                    </button>
                    <a href="/auth/google/redirect" class="google-btn">
                        <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="google-icon">
                        <span> via Google</span>
                    </a>
                </div>
            </form>
         </transition>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const authCoverImg = '/images/Bibliothèque/Zoom.jpg';
const toast = useToast();

const indicator = ref(null);
const mode = ref('login'); 

const loginForm = useForm({
    email: '',
    password: ''
});

const signupForm = useForm({
    firstName: '',
    lastName: '',
    email: '',
    password: ''
});

const setMode = (newMode) => {
    mode.value = newMode;
    animateIndicator(newMode);
};

const animateIndicator = (currentMode) => {
    if (!indicator.value) return;
    const isLogin = currentMode === 'login';
    gsap.to(indicator.value, {
        left: isLogin ? '4px' : '50%',
        duration: 0.5,
        ease: "power4.out"
    });
};

const beforeEnter = (el) => {
    gsap.set(el.querySelectorAll('.form-element'), {
        y: 20,
        opacity: 0
    });
};

const enter = (el, done) => {
    gsap.to(el.querySelectorAll('.form-element'), {
        y: 0,
        opacity: 1,
        duration: 0.6,
        stagger: 0.1,
        ease: "power3.out",
        onComplete: done
    });
};

const leave = (el, done) => {
    gsap.to(el.querySelectorAll('.form-element'), {
        y: -20,
        opacity: 0,
        duration: 0.4,
        stagger: 0.05,
        ease: "power3.in",
        onComplete: done
    });
};

const submitLogin = () => {
    if (!loginForm.email || !loginForm.password) {
         toast.add({ severity: 'warn', summary: 'Attention', detail: 'Veuillez remplir tous les champs', life: 3000 });
         return;
    }

    loginForm.post('/login', {
        onSuccess: () => {
            // Usually redirects, but just in case
            console.log("Login success");
        },
        onError: (errors) => {
            console.error("Login failed", errors);
            toast.add({ severity: 'error', summary: 'Erreur', detail: 'Identifiants incorrects ou erreur serveur', life: 3000 });
        }
    });
};

const submitSignup = () => {
    if (!signupForm.firstName || !signupForm.lastName || !signupForm.email || !signupForm.password) {
        toast.add({ severity: 'warn', summary: 'Attention', detail: 'Veuillez remplir tous les champs', life: 3000 });
        return;
    }

    signupForm.post('/inscription', {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Compte créé avec succès ! Connectez-vous.', life: 3000 });
            signupForm.reset();
            setTimeout(() => {
                setMode('login');
            }, 1500);
        },
        onError: (errors) => {
            console.error("Signup failed", errors);
            let msg = 'Erreur lors de l\'inscription';
            if (errors.email) msg = errors.email;
            toast.add({ severity: 'error', summary: 'Erreur', detail: msg, life: 3000 });
        }
    });
};
</script>

<style scoped>
.auth-page {
  width: 100%;
  height: 100vh;
  display: flex;
  background-color: #ffffff; 
}

.auth-image-col {
  flex: 1.2; 
  position: relative;
  overflow: hidden;
  background: #000;
}

.cover-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.8;
}

.image-overlay {
    position: absolute;
    bottom: 50px;
    left: 40px;
    right: 40px;
    color: white;
}

.overlay-text {
    font-size: 2.5rem;
    line-height: 1.1;
    font-weight: 700;
}

.auth-form-col {
  flex: 1;
  padding: 150px 85px 20px 80px;
  display: flex;
  flex-direction: column;
  background: #ffffff;
  position: relative;
}

.auth-header {
  display: flex;
  justify-content: center;
  margin-bottom: 50px;
  flex-shrink: 0;
}

.toggle-container {
    display: flex;
    background: #f0f0f0;
    border-radius: 15px;
    position: relative;
    padding: 4px;
    width: 280px;
}

.toggle-btn {
    flex: 1;
    background: none;
    border: none;
    padding: 10px 0;
    font-size: 0.85rem;
    font-weight: 600;
    z-index: 2;
    cursor: pointer;
    color: #888;
    transition: color 0.3s;
}

.toggle-btn.active {
    color: #000;
}

.toggle-indicator {
    position: absolute;
    top: 4px;
    width: calc(50% - 4px);
    bottom: 4px;
    background: #fff;
    border-radius: 11px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    z-index: 1;
}

.form-wrapper {
    width: 100%;
    flex: 1;
}

.auth-form {
    display: flex;
    flex-direction: column;
}

.input-row {
    display: flex;
    gap: 20px;
}

.input-group {
    margin-bottom: 25px;
    flex: 1;
}

.input-group label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #111;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
}

.input-group input {
    width: 100%;
    background: transparent;
    border: none;
    border-bottom: 1px solid #ccc;
    padding: 15px 0;
    font-size: 1.2rem;
    color: #111;
    outline: none;
    transition: border-bottom-color 0.4s ease;
}

.input-group input:focus {
    border-bottom-color: #111;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 25px;
}

.forgot-pass {
    font-size: 0.85rem;
    color: #666;
    text-decoration: none;
}

.auth-buttons-row {
    display: flex;
    gap: 15px;
}

.primary-btn {
    flex: 1.5;
    padding: 16px;
    background: #111;
    color: #fff;
    border: none;
    border-radius: 15px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    z-index: 1;
}

.btn-text {
    position: relative;
    z-index: 2;
}

.primary-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, #0E7EC3, #8A38F5, #0E7EC3);
    background-size: 200% 100%;
    opacity: 0;
    transition: opacity 0.5s ease;
    z-index: 1;
}

.primary-btn:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 10px 20px rgba(138, 56, 245, 0.3);
}

.primary-btn:hover::before {
    opacity: 1;
    animation: gradient-move 2s linear infinite;
}

.btn-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 60%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.4),
        transparent
    );
    transform: skewX(-20deg);
    transition: 0.6s;
    z-index: 3;
}

.primary-btn:hover .btn-shine {
    left: 140%;
}

@keyframes gradient-move {
    0% { background-position: 0% 50%; }
    100% { background-position: 200% 50%; }
}

.google-btn {
    flex: 1;
    padding: 12px;
    background: #fff;
    border: 1px solid #979797;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1rem;
    color: #444;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}

.google-icon {
    width: 20px;
    height: 20px;
}

@media (max-width: 1024px) {
    .auth-form-col {
        padding: 40px 40px;
    }
}

@media (max-width: 900px) {
    .auth-image-col {
        display: none;
    }
}
</style>
