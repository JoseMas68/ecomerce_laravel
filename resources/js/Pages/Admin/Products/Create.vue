<script setup>
import Form from './Form.vue';

defineProps({
    categories: Array,
    brands: Array
});
</script>

<template>
    <Form :categories="categories" :brands="brands" />
</template>
