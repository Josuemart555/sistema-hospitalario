<script setup>
import { reactive } from 'vue';
import OptionsTreeNode from './OptionsTreeNode.vue';

const props = defineProps({
    nodes: { type: Array, required: true },
    selected: { type: Array, default: () => [] },
    name: { type: String, required: true },
});

const checkedIds = reactive(new Set(props.selected.map(Number)));
</script>

<template>
    <div class="options-tree">
        <OptionsTreeNode v-for="node in nodes" :key="node.id" :node="node" :checked-ids="checkedIds" />

        <input v-for="id in checkedIds" :key="id" type="hidden" :name="name" :value="id">
    </div>
</template>
