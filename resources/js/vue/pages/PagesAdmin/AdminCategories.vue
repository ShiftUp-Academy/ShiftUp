<template>
    <div class="admin-content">
        <div class="page-header">
            <h1 class="page-title">Gérer les Catégories</h1>
            <PremiumButton @click="openCreateModal" class="new-btn-premium">
                <i class="fas fa-plus-circle"></i> Nouvelle catégorie
            </PremiumButton>
        </div>

        <!-- TABLE -->
        <div class="categories-list-container">
            <div class="categories-table">
                <div class="table-header-row">
                    <div class="col-name">Nom</div>
                    <div class="col-description">Description</div>
                    <div class="col-status">Statut</div>
                    <div class="col-actions"></div>
                </div>

                <div v-for="cat in categories" :key="cat.IdCategorie" class="table-row">
                    <div class="col-name font-bold">{{ cat.Nom }}</div>
                    <div class="col-description text-gray-600">{{ cat.Descriptions || 'Pas de description' }}</div>
                    <div class="col-status">
                        <PremiumSlideToggle v-model="cat.Statut" checkedValue="Publié" uncheckedValue="Dépublié"
                            activeColor="#22c55e" @update:modelValue="updateStatus(cat)" />
                    </div>
                    <div class="col-actions">
                        <button class="edit-btn" @click="openEditModal(cat)">
                            <i class="fas fa-pen"></i> Modifier
                        </button>
                        <button class="delete-btn-simple" @click="deleteCategory(cat)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <div v-if="categories.length === 0" class="empty-state">
                    Aucune catégorie trouvée.
                </div>
            </div>
        </div>

        <!-- MODAL -->
        <PremiumModal :isOpen="showModal"
            :header="editingCategory ? 'Modification d\'une catégorie' : 'Nouvelle catégorie'" width="35rem"
            @close="closeModal">
            <form @submit.prevent="submitForm" class="create-form">
                <div class="field-group">
                    <label>Nom</label>
                    <InputText v-model="form.Nom" placeholder="Nom de la catégorie..." required />
                </div>

                <div class="field-group mt-4">
                    <label>Description</label>
                    <Textarea v-model="form.Descriptions" rows="3" placeholder="Description de la catégorie..." />
                </div>

                <div class="field-group mt-4">
                    <PremiumSlideToggle v-model="form.Statut" checkedValue="Publié" uncheckedValue="Dépublié"
                        activeColor="#22c55e" />
                </div>

                <div class="modal-footer">
                    <PremiumButton type="submit" :loading="form.processing">Enregistrer</PremiumButton>
                </div>
            </form>
        </PremiumModal>
        <ConfirmModal :isOpen="confirmData.isOpen" :title="confirmData.title" :message="confirmData.message"
            :type="confirmData.type" @confirm="onModalConfirm" @cancel="confirmData.isOpen = false" />
        <Toast />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import PremiumButton from '../../components/ui/PremiumButton.vue';
import PremiumModal from '../../components/ui/PremiumModal.vue';
import PremiumSlideToggle from '../../components/ui/PremiumSlideToggle.vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import ConfirmModal from '../../components/ui/ConfirmModal.vue';
import Toast from 'primevue/toast';
import { useToast } from "primevue/usetoast";

const props = defineProps({
    categories: Array
});

const toast = useToast();
const showModal = ref(false);
const editingCategory = ref(null);

const form = useForm({
    Nom: '',
    Descriptions: '',
    Statut: 'Dépublié'
});

const openCreateModal = () => {
    editingCategory.value = null;
    form.reset();
    form.Statut = 'Dépublié';
    showModal.value = true;
};

const openEditModal = (cat) => {
    editingCategory.value = cat;
    form.Nom = cat.Nom;
    form.Descriptions = cat.Descriptions || '';
    form.Statut = cat.Statut;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
};

const submitForm = () => {
    const url = editingCategory.value
        ? '/admin/categories/' + editingCategory.value.IdCategorie
        : '/admin/categories';

    form.post(url, {
        onSuccess: () => {
            closeModal();
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Opération réussie', life: 3000 });
        },
        onError: (errs) => {
            Object.values(errs).forEach(msg => {
                toast.add({ severity: 'error', summary: 'Erreur', detail: msg, life: 5000 });
            });
        }
    });
};

const updateStatus = (cat) => {
    // Instant update
    router.post('/admin/categories/' + cat.IdCategorie, {
        Nom: cat.Nom,
        Descriptions: cat.Descriptions,
        Statut: cat.Statut
    }, {
        preserveScroll: true,
        preserveState: false, // Important to refresh the categories list from backend
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Confirmé',
                detail: `Le statut a été mis à jour dans la base de données`,
                life: 2000
            });
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Erreur', detail: 'Échec de synchronisation SQL', life: 3000 });
        }
    });
};

// CONFIRM MODAL STATE
const confirmData = ref({
    isOpen: false,
    title: '',
    message: '',
    type: 'danger',
    action: null
});

const triggerConfirm = (title, message, type, action) => {
    confirmData.value = { isOpen: true, title, message, type, action };
};

const onModalConfirm = () => {
    if (confirmData.value.action) confirmData.value.action();
    confirmData.value.isOpen = false;
};

const deleteCategory = (cat) => {
    triggerConfirm(
        "Supprimer la catégorie",
        `Voulez-vous vraiment supprimer la catégorie "${cat.Nom}" ?`,
        'danger',
        () => {
            router.delete('/admin/categories/' + cat.IdCategorie, {
                onSuccess: () => {
                    toast.add({ severity: 'info', summary: 'Supprimé', detail: "La catégorie a été supprimée", life: 3000 });
                }
            });
        }
    );
};

</script>

<style scoped>
.admin-content {
    padding: 40px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
}

.categories-list-container {
    background: white;
    border-radius: 16px;
    border: 1px solid #e0e0e0;
    overflow: hidden;
}

.table-header-row {
    display: flex;
    padding: 15px 25px;
    background: white;
    border-bottom: 1px solid #eee;
    font-weight: 600;
    color: #111;
    font-size: 1rem;
}

.table-row {
    display: flex;
    padding: 20px 25px;
    align-items: center;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
}

.table-row:last-child {
    border-bottom: none;
}

.table-row:hover {
    background: #f9f9f9;
}

.col-name {
    flex: 1.2;
    font-size: 1.1rem;
    justify-content: center;
    align-content: center;
    color: #111;
}

.col-description {
    flex: 2.5;
    font-size: 1.05rem;
    color: #555;
    padding: 10px 20px 10px 0;
    line-height: 1.5;
    word-break: break-word;
}

.col-status {
    flex: 1;
    display: flex;
    justify-content: center;
    font-size: 1.2rem;
    flex-direction: column;
    gap: 5px;
}

.col-actions {
    flex: 1;
    display: flex;
    justify-content: center;
}

.status-label {
    font-size: 0.8rem;
    font-weight: 600;
}

.edit-btn {
    background: #1c1c1c;
    color: white;
    border: none;
    padding: 15px 25px;
    border-radius: 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1rem;
    font-weight: 700;
    transition: all 0.2s;
}

.edit-btn:hover {
    background: #333;
}

.delete-btn-simple {
    background: none;
    border: 1px solid #eee;
    color: #ef4444;
    width: 45px;
    height: 45px;
    border-radius: 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    margin-left: 10px;
}

.delete-btn-simple:hover {
    background: #fee2e2;
    border-color: #ef4444;
}

.create-form {
    margin-bottom: 4vh;
}

.field-group {
    display: flex;
    flex-direction: column;
    margin-top: 2vh;
    gap: 8px;
}

.field-group label {
    font-weight: 700;
    font-size: 1rem;
    color: #111;
}

.modal-footer {
    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
}
</style>
