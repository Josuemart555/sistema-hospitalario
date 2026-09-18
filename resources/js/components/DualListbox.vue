<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    items: { type: Array, required: true },
    selected: { type: Array, default: () => [] },
    name: { type: String, required: true },
    leftLabel: { type: String, default: 'Disponibles' },
    rightLabel: { type: String, default: 'Asignados' },
});

const assignedIds = ref(new Set(props.selected.map(Number)));
const availableFilter = ref('');
const assignedFilter = ref('');
const highlightedAvailable = ref(new Set());
const highlightedAssigned = ref(new Set());

const matches = (item, filter) => item.name.toLowerCase().includes(filter.trim().toLowerCase());

const available = computed(() => props.items.filter((item) => !assignedIds.value.has(item.id) && matches(item, availableFilter.value)));
const assigned = computed(() => props.items.filter((item) => assignedIds.value.has(item.id) && matches(item, assignedFilter.value)));

const toggleHighlight = (set, id) => {
    set.has(id) ? set.delete(id) : set.add(id);
};

const moveToAssigned = (ids) => {
    ids.forEach((id) => assignedIds.value.add(id));
    highlightedAvailable.value.clear();
};

const moveToAvailable = (ids) => {
    ids.forEach((id) => assignedIds.value.delete(id));
    highlightedAssigned.value.clear();
};

const moveHighlightedRight = () => moveToAssigned([...highlightedAvailable.value]);
const moveAllFilteredRight = () => moveToAssigned(available.value.map((item) => item.id));
const moveHighlightedLeft = () => moveToAvailable([...highlightedAssigned.value]);
const moveAllFilteredLeft = () => moveToAvailable(assigned.value.map((item) => item.id));
</script>

<template>
    <div class="dual-listbox">
        <div class="row g-3 align-items-stretch">
            <div class="col">
                <input v-model="availableFilter" type="search" class="form-control form-control-sm mb-2" :placeholder="`Buscar en ${leftLabel.toLowerCase()}...`">
                <div class="list-group dual-listbox-pane">
                    <button v-for="item in available" :key="item.id" type="button" class="list-group-item list-group-item-action" :class="{ 'dual-listbox-item-active': highlightedAvailable.has(item.id) }" @click="toggleHighlight(highlightedAvailable, item.id)">
                        {{ item.name }}
                    </button>
                </div>
                <div class="d-flex gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="moveToAssigned(available.map((item) => item.id))">Seleccionar todos</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="highlightedAvailable.clear()">Ninguno</button>
                </div>
            </div>

            <div class="col-auto dual-listbox-controls">
                <button type="button" class="btn btn-sm btn-primary" title="Mover seleccionados" @click="moveHighlightedRight"><i class="bi bi-chevron-right"></i></button>
                <button type="button" class="btn btn-sm btn-outline-primary" title="Mover todos" @click="moveAllFilteredRight"><i class="bi bi-chevron-double-right"></i></button>
                <button type="button" class="btn btn-sm btn-outline-primary" title="Quitar todos" @click="moveAllFilteredLeft"><i class="bi bi-chevron-double-left"></i></button>
                <button type="button" class="btn btn-sm btn-primary" title="Quitar seleccionados" @click="moveHighlightedLeft"><i class="bi bi-chevron-left"></i></button>
            </div>

            <div class="col">
                <input v-model="assignedFilter" type="search" class="form-control form-control-sm mb-2" :placeholder="`Buscar en ${rightLabel.toLowerCase()}...`">
                <div class="list-group dual-listbox-pane">
                    <button v-for="item in assigned" :key="item.id" type="button" class="list-group-item list-group-item-action" :class="{ 'dual-listbox-item-active': highlightedAssigned.has(item.id) }" @click="toggleHighlight(highlightedAssigned, item.id)">
                        {{ item.name }}
                    </button>
                </div>
                <div class="d-flex gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="moveToAvailable(assigned.map((item) => item.id))">Seleccionar todos</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="highlightedAssigned.clear()">Ninguno</button>
                </div>
            </div>
        </div>

        <input v-for="id in assignedIds" :key="id" type="hidden" :name="name" :value="id">
    </div>
</template>
