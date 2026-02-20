<script setup>
import Form from './Form.vue';

defineProps({
    product: Object,
    categories: Array,
    brands: Array
});
</script>

<template>
    <Form :product="product" :categories="categories" :brands="brands" />
</template>
