<template>
  <div class="filters-bar">
    <div class="search-wrapper">
      <InputText 
        v-model="search" 
        placeholder="recherche par mot clé" 
        class="search-input-pv" 
      />
    </div>
    
    <div class="date-inputs">
      <DatePicker 
        v-model="dateStart" 
        placeholder="date début" 
        dateFormat="dd/mm/yy"
        showIcon
        iconDisplay="input"
        class="date-picker-pv"
      />
      <DatePicker 
        v-model="dateEnd" 
        placeholder="date fin" 
        dateFormat="dd/mm/yy"
        showIcon
        iconDisplay="input"
        class="date-picker-pv"
      />
    </div>
    
    <button class="refresh-btn" @click="$emit('refresh')">
      <i class="fas fa-sync-alt"></i>
    </button>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import InputText from 'primevue/inputtext';
import DatePicker from 'primevue/datepicker';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ search: '', dateStart: null, dateEnd: null })
  }
});

const emit = defineEmits(['update:modelValue', 'refresh']);

const search = ref(props.modelValue.search);
const dateStart = ref(props.modelValue.dateStart);
const dateEnd = ref(props.modelValue.dateEnd);

watch([search, dateStart, dateEnd], () => {
  emit('update:modelValue', {
    search: search.value,
    dateStart: dateStart.value,
    dateEnd: dateEnd.value
  });
});
</script>

<style scoped>
.filters-bar {
  display: flex;
  align-items: center;
  gap: 2rem;
}

:deep(.search-input-pv) {
  border: none !important;
  border-bottom: 2px solid #ccc !important;
  padding: 10px 0 !important;
  width: 250px !important;
  font-size: 1rem !important;
  color: #555 !important;
  outline: none !important;
  background: transparent !important;
  border-radius: 0 !important;
  box-shadow: none !important;
}

:deep(.search-input-pv:focus) {
  border-bottom-color: #111 !important;
}

:deep(.search-input-pv::placeholder) {
  color: #aaa !important;
  font-weight: 500 !important;
}

.date-inputs {
  display: flex;
  gap: 10px;
}

:deep(.date-picker-pv) {
  width: 10vw;
}

:deep(.date-picker-pv .p-inputtext) {
  border: 2px solid #7c7c7c !important;
  border-radius: 15px !important;
  padding: 8px 0 !important;
  font-size: 1.1rem !important;
  font-weight: 500 !important;
  width: 10.5vw !important;
  color: #313131 !important;
  background: transparent !important;
  text-align: center !important;
  box-shadow: none !important;
}

:deep(.date-picker-pv .p-datepicker-trigger) {
  display: none;
}

.refresh-btn {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: #111;
  color: #fff;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 1rem;
  transition: transform 0.2s;
}

.new-btn-premium {
  padding: 10px 25px !important;
  border-radius: 15px !important;
  font-size: 0.95rem !important;
  height: 42px;
}

.new-btn-premium i {
  font-size: 1.1rem;
}
</style>
