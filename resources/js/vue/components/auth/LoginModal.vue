<template>
    <PremiumModal :isOpen="isOpen" header="" width="73vw" :origin="origin" noPadding @close="close">
        <div class="auth-page-modal">
            <button class="close-btn" @click="close">
                <i class="fa-solid fa-times"></i>
            </button>
            <Loader v-if="isLoading" />
            <div class="auth-image-col">
                <img :src="authCoverImg" alt="Auth Cover" class="cover-image" />
            </div>

            <div class="auth-form-col">
                <div class="auth-header form-element">
                    <div class="toggle-container">
                        <button class="toggle-btn" :class="{ active: mode === 'login' }" @click="setMode('login')">
                            Connexion
                        </button>
                        <button class="toggle-btn" :class="{ active: mode === 'signup' }" @click="setMode('signup')">
                            Inscription
                        </button>
                        <div class="toggle-indicator" ref="indicator"></div>
                    </div>
                </div>

                <div class="form-wrapper">
                    <transition @before-enter="beforeFormEnter" @enter="enterForm" @leave="leaveForm" :css="false"
                        mode="out-in">
                        <!-- LOGIN FORM -->
                        <form v-if="mode === 'login'" key="login" @submit.prevent="submitLogin" class="auth-form">
                            <div class="input-group form-element">
                                <label>Email</label>
                                <input type="email" v-model="loginForm.email" placeholder="exemple@gmail.com" required>
                            </div>
                            <div class="input-group form-element">
                                <label>Mot de passe</label>
                                <input type="password" v-model="loginForm.password" placeholder="votre mot de passe ici"
                                    required>
                            </div>

                            <div class="form-actions form-element">
                                <a href="#" class="forgot-pass">Mot de passe oublié ?</a>
                            </div>

                            <div class="auth-buttons-column form-element">
                                <PremiumButton type="submit" text="Se Connecter" />
                                <a href="/auth/google/redirect" class="google-btn">
                                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google"
                                        class="google-icon">
                                    <span>Continuer avec Google</span>
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
                                    <input type="text" v-model="signupForm.firstName" placeholder="Votre prénom"
                                        required>
                                </div>
                            </div>

                            <div class="input-group form-element">
                                <label>Email</label>
                                <input type="email" v-model="signupForm.email" placeholder="exemple@mail.com" required>
                            </div>
                            <div class="input-group form-element">
                                <label>Mot de passe</label>
                                <input type="password" v-model="signupForm.password" placeholder="Votre mot de passe"
                                    required>
                            </div>

                            <div class="auth-buttons-column form-element">
                                <PremiumButton type="submit" text="S'inscrire" />
                                <a href="/auth/google/redirect" class="google-btn">
                                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google"
                                        class="google-icon">
                                    <span class="google-text">Continuer avec Google</span>
                                </a>
                            </div>
                        </form>
                    </transition>
                </div>
            </div>
        </div>
    </PremiumModal>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import PremiumButton from '../ui/PremiumButton.vue';
import PremiumModal from '../ui/PremiumModal.vue';
import Loader from '../ui/Loader.vue';

const props = defineProps({
    isOpen: Boolean,
    origin: {
        type: Object,
        default: () => ({ x: 0, y: 0 })
    }
});
const emit = defineEmits(['close']);

const authCoverImg = '/images/Bibliothèque/Zoom.jpg';
const indicator = ref(null);
const mode = ref('login');
const isLoading = ref(false);

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

const close = () => {
    emit('close');
};

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

/* --- Form transition hooks --- */
const beforeFormEnter = (el) => {
    gsap.set(el.querySelectorAll('.form-element'), {
        y: 20,
        opacity: 0
    });
};

const enterForm = (el, done) => {
    gsap.to(el.querySelectorAll('.form-element'), {
        y: 0,
        opacity: 1,
        duration: 0.6,
        stagger: 0.1,
        ease: "power3.out",
        onComplete: done
    });
};

const leaveForm = (el, done) => {
    gsap.to(el.querySelectorAll('.form-element'), {
        y: -10,
        opacity: 0,
        duration: 0.4,
        stagger: 0.05,
        ease: "power3.in",
        onComplete: done
    });
};

const submitLogin = () => {
    isLoading.value = true;
    loginForm.post('/login', {
        onSuccess: () => {
            setTimeout(() => {
                close();
                isLoading.value = false;
            }, 500);
        },
        onError: () => {
            isLoading.value = false;
        },
        onFinish: () => {
            if (loginForm.hasErrors) isLoading.value = false;
        }
    });
};

const submitSignup = () => {
    isLoading.value = true;
    signupForm.post('/inscription', {
        onSuccess: () => {
            setTimeout(() => {
                signupForm.reset();
                setMode('login');
                isLoading.value = false;
            }, 500);
        },
        onError: () => {
            isLoading.value = false;
        },
        onFinish: () => {
            if (signupForm.hasErrors) isLoading.value = false;
        }
    });
};

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        mode.value = 'login';
        setTimeout(() => animateIndicator('login'), 100);
    }
});
</script>

<style scoped>
.auth-page-modal {
    width: 100%;
    height: 600px;
    /* Taille fixe pour éviter l'étirement excessif */
    display: flex;
    background-color: #ffffff;
    position: relative;
    overflow: hidden;
}

.close-btn {
    position: absolute;
    top: 20px;
    right: 25px;
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
    z-index: 100;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.close-btn:hover {
    transform: rotate(90deg) scale(1.1);
    background: #222;
}

.auth-image-col {
    flex: 1;
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

.auth-form-col {
    flex: 1;
    padding: 11vh 5vw;
    padding-bottom: 0;
    display: flex;
    flex-direction: column;
    background: #ffffff;
    position: relative;
}

.auth-header {
    display: flex;
    justify-content: center;
    margin-bottom: 30px;
    flex-shrink: 0;
}

.toggle-container {
    display: flex;
    background: #f0f0f0;
    border-radius: 15px;
    position: relative;
    padding: 4px;
    width: 250px;
}

.toggle-btn {
    flex: 1;
    background: none;
    border: none;
    padding: 8px 0;
    font-size: 0.85rem;
    font-weight: 600;
    z-index: 2;
    cursor: pointer;
    color: #888;
    transition: color 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
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
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    z-index: 1;
}

.form-wrapper {
    width: 100%;
}

.auth-form {
    display: flex;
    flex-direction: column;
    padding-bottom: 20px;
}

.input-row {
    display: flex;
    gap: 15px;
}

.input-group {
    margin-bottom: 20px;
    flex: 1;
}

.input-group label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #111;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 6px;
}

.input-group input {
    width: 100%;
    background: transparent;
    border: none;
    border-bottom: 1px solid #ccc;
    padding: 10px 0;
    font-size: 1.1rem;
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
    margin-bottom: 20px;
}

.forgot-pass {
    font-size: 1.1rem;
    color: #8A38F5;
    text-decoration: none;
}

.auth-buttons-column {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 10px;
}

.google-btn {
    width: 100%;
    padding: 12px;
    background: #ffffff;
    border: 1px solid #979797;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.9rem;
    color: #444;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
    overflow: visible;
}

.google-btn::before {
    content: '';
    position: absolute;
    inset: -2px;
    background: linear-gradient(90deg, #4285f4, #db4437, #f4b400, #4285f4);
    background-size: 400% 400%;
    border-radius: 14px;
    z-index: -2;
    opacity: 0;
    filter: blur(12px);
    transition: opacity 0.4s ease;
}

.google-btn::after {
    content: '';
    position: absolute;
    inset: -1px;
    background: #ffffff;
    background-size: 400% 400%;
    border-radius: 13px;
    z-index: -1;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.google-btn:hover {
    transform: translateY(-2px);
    border-color: transparent;
    color: #111;
}

.google-btn:hover::before,
.google-btn:hover::after {
    opacity: 1;
    animation: google-shadow-glow 3s linear infinite;
}

.google-icon {
    width: 18px;
    height: 18px;
    position: relative;
    z-index: 2;
}

@keyframes google-shadow-glow {
    0% {
        background-position: 0% 50%;
    }

    50% {
        background-position: 100% 50%;
    }

    100% {
        background-position: 0% 50%;
    }
}

@media (max-width: 768px) {
    .auth-image-col {
        display: none;
    }

    .auth-form-col {
        padding: 5vh 5vw;
    }
}
</style>
