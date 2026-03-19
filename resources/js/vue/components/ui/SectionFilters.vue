<template>
  <div class="filters-container" :class="{ 'is-vertical': vertical }">
    <div class="filter-inputs">
      <div class="filter-item search-wrapper">
        <AutoComplete :modelValue="searchValue" @update:modelValue="$emit('update:searchValue', $event)"
          :suggestions="suggestions" @complete="$emit('complete', $event)"
          @item-select="$emit('select-suggestion', $event.value)" :placeholder="$t('SearchPlaceholder')"
          class="search-autocomplete full-width-prime" />
      </div>

      <div class="filter-item category-wrapper">
        <FloatLabel variant="on" class="full-width-float">
          <Select :modelValue="selectedCategory" @update:modelValue="$emit('update:selectedCategory', $event)"
            inputId="category_label" :placeholder="$t('Category')" :options="categories" optionLabel="name" editable
            class="search-select full-width-prime" />
        </FloatLabel>
      </div>
    </div>

    <div class="actions-wrapper">
      <button class="reset-button" @click="$emit('reset')" :title="$t('Reset')">
        <svg viewBox="0 0 24 24" fill="none" class="reset-icon">
          <path
            d="M4 4V9H4.58152M19.9381 11C19.446 7.05361 16.0796 4 12 4C8.64262 4 5.76829 6.06817 4.58152 9M4.58152 9H9M20 20V15H19.4185M19.4185 15C18.2317 17.9318 15.3574 20 12 20C7.92038 20 4.55399 16.9464 4.06189 13M19.4185 15H15"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>

      <button class="text-reset-button" @click="$emit('reset')">
        {{ $t('Refresh') }}
      </button>

      <button v-if="showViewAll" class="view-all-button" @click="$emit('view-all')">
        <span class="btn-text">{{ $t('ViewAll') }}</span>
        <svg viewBox="0 0 24 24" fill="none" class="arrow-icon">
          <path d="M7 17L17 7M17 7H7M17 7V17" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import AutoComplete from 'primevue/autocomplete';
import Select from 'primevue/select';
import FloatLabel from 'primevue/floatlabel';

defineProps({
  searchValue: String,
  selectedCategory: [Object, String],
  categories: Array,
  suggestions: Array,
  vertical: {
    type: Boolean,
    default: false
  },
  showViewAll: {
    type: Boolean,
    default: true
  }
});

defineEmits(['update:searchValue', 'update:selectedCategory', 'complete', 'reset', 'view-all', 'select-suggestion']);
</script>

<style scoped>
.filters-container {
  display: flex;
  align-items: flex-end;
  gap: 15px;
  width: 100%;
  max-width: 800px;
  padding: 10px 0;
}

.filter-inputs {
  display: contents;
}

.is-vertical {
  flex-direction: column;
  align-items: flex-start;
  gap: 20px;
}

.is-vertical .filter-inputs {
  display: flex;
  flex-direction: column;
  gap: 15px;
  width: 100%;
}

.is-vertical .actions-wrapper {
  width: 100%;
  justify-content: flex-start;
  padding-bottom: 0;
}

.filter-item {
  flex: 1;
}

.actions-wrapper {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
  padding-bottom: 5px;
}

:deep(.full-width-prime),
:deep(.full-width-float) {
  width: 100%;
}

:deep(.p-autocomplete-input),
:deep(.p-select-input) {
  width: 100% !important;
  background: transparent !important;
  border: none !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.6) !important;
  border-radius: 0 !important;
  color: #fff !important;
  font-family: 'Plus Jakarta Sans', sans-serif !important;
  font-size: 0.9vw !important;
  padding: 10px 0 !important;
  box-shadow: none !important;
}

:deep(.p-autocomplete-input::placeholder) {
  font-size: 0.9vw;
  font-family: 'Plus Jakarta Sans', sans-serif;
  color: rgba(255, 255, 255, 0.4);
}

:deep(.p-select) {
  background: transparent !important;
  border: none !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.6) !important;
  border-radius: 0 !important;
  box-shadow: none !important;
}

:deep(.p-select-label) {
  background: transparent !important;
  color: #fff !important;
  font-family: 'Plus Jakarta Sans', sans-serif !important;
  font-size: 0.9vw !important;
}

:deep(.p-select-option) {
  background: transparent !important;
  color: #fff !important;
  font-family: 'Plus Jakarta Sans', sans-serif;
}

:deep(.p-select-option.p-select-option-selected),
:deep(.p-select-option:not(.p-select-option-selected):not(.p-disabled).p-focus) {
  background: rgba(255, 255, 255, 0.1) !important;
  color: #fff !important;
}

:deep(.p-floatlabel label) {
  left: 0 !important;
  font-family: 'Plus Jakarta Sans', sans-serif;
  color: rgba(255, 255, 255, 0.6) !important;
}

:deep(.p-floatlabel:has(.p-filled) label),
:deep(.p-floatlabel:has(input:focus) label) {
  color: #fff !important;
  transform: translateY(-130%);
}

.reset-button {
  background: transparent;
  border: none;
  color: #fff;
  cursor: pointer;
  transition: transform 0.4s;
}

.reset-button:hover {
  transform: rotate(180deg);
  opacity: 0.7;
}

.text-reset-button {
  background: transparent;
  border: none;
  color: #fff;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.8vw;
  font-weight: 600;
  cursor: pointer;
  padding: 12px 10px 0 0px;
  transition: opacity 0.4s;
  text-transform: uppercase;
}

.text-reset-button:hover {
  opacity: 0.7;
}

.view-all-button {
  padding: 12px 0 0px 0px;
  background: transparent;
  color: #ffffff;
  border: none;
  font-family: 'Plus Jakarta Sans', sans-serif;
  font-size: 0.9vw;
  font-weight: 800;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: all 0.4s cubic-bezier(0.2, 0, 0.2, 1);
  white-space: nowrap;
}

.view-all-button .btn-text {
  font-weight: 600;
  font-size: 0.8vw;
  transition: transform 0.4s;
}

.view-all-button .arrow-icon {
  width: 25px;
  height: 25px;
  transition: transform 0.4s;
}

.view-all-button:hover {
  /* Removed translateY animation */
  opacity: 0.8;
}

.view-all-button:hover .btn-text {
  transform: translateX(-4px);
}

.view-all-button:hover .arrow-icon {
  transform: translateX(4px);
}

@media (max-width: 768px) {
  .filters-container {
    flex-direction: column;
    align-items: stretch;
    max-width: 100%;
  }
}
</style>