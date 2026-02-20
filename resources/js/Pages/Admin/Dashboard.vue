<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Components/Layout/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

// KPIs de prueba para el dashboard
const stats = [
    { name: 'Ingresos Totales', value: '12,450 €', increment: '+12.5%', color: 'text-green-500', icon: 'payments' },
    { name: 'Pedidos Activos', value: '45', increment: '+5', color: 'text-blue-500', icon: 'shopping_bag' },
    { name: 'Clientes Nuevos', value: '128', increment: '+22.4%', color: 'text-purple-500', icon: 'group' },
    { name: 'Productos Bajo Stock', value: '12', increment: '-2', color: 'text-red-500', icon: 'warning' },
];

const recentOrders = [
    { id: '#ORD-0092', customer: 'Carlos Díaz', date: 'Hace 2 horas', amount: '125.00 €', status: 'Pendiente' },
    { id: '#ORD-0091', customer: 'Ana Gómez', date: 'Hace 5 horas', amount: '45.50 €', status: 'Procesando' },
    { id: '#ORD-0090', customer: 'Luis López', date: 'Ayer', amount: '89.99 €', status: 'Completado' },
    { id: '#ORD-0089', customer: 'Elena Martín', date: 'Ayer', amount: '210.00 €', status: 'Enviado' },
];

const getStatusColor = (status) => {
    switch(status) {
        case 'Pendiente': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'Procesando': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        case 'Completado': return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'Enviado': return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
        default: return 'bg-slate-100 text-slate-800';
    }
};
</script>

<template>
    <Head title="Admin Dashboard - PawfectShop" />

    <div class="py-6 px-4 sm:px-6 lg:px-8">
        <!-- Dashboard Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Dashboard Principal</h1>
                <p class="text-sm text-slate-500">Resumen y estado de la tienda</p>
            </div>
            <div class="flex gap-3">
                <button class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-4 py-2 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">download</span> Informe
                </button>
            </div>
        </div>

        <!-- KPIs Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div v-for="stat in stats" :key="stat.name" class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="h-12 w-12 rounded-xl bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl" :class="stat.color">{{ stat.icon }}</span>
                    </div>
                    <span class="text-sm font-bold text-green-500 flex items-center">
                        <span class="material-symbols-outlined text-xs mr-1">trending_up</span>
                        {{ stat.increment }}
                    </span>
                </div>
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-medium">{{ stat.name }}</h3>
                <p class="text-3xl font-extrabold text-slate-900 dark:text-white mt-1">{{ stat.value }}</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Columna Izquierda (Pedidos Recientes) -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
                <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Últimos Pedidos</h2>
                    <button class="text-sm text-primary font-semibold hover:underline">Ver todos</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">
                                <th class="p-4 border-b border-slate-100 dark:border-slate-700">Pedido</th>
                                <th class="p-4 border-b border-slate-100 dark:border-slate-700">Cliente</th>
                                <th class="p-4 border-b border-slate-100 dark:border-slate-700">Fecha</th>
                                <th class="p-4 border-b border-slate-100 dark:border-slate-700">Monto</th>
                                <th class="p-4 border-b border-slate-100 dark:border-slate-700">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <tr v-for="order in recentOrders" :key="order.id" class="border-b border-slate-100 dark:border-slate-700 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                <td class="p-4 font-bold text-slate-900 dark:text-white">{{ order.id }}</td>
                                <td class="p-4 text-slate-600 dark:text-slate-300">{{ order.customer }}</td>
                                <td class="p-4 text-slate-500">{{ order.date }}</td>
                                <td class="p-4 font-semibold text-slate-900 dark:text-white">{{ order.amount }}</td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold" :class="getStatusColor(order.status)">
                                        {{ order.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Columna Derecha (Acciones Rápidas) -->
            <div class="space-y-6">
                <!-- Tareas Pendientes -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 p-6">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Acciones Rápidas</h2>
                    <div class="space-y-3">
                        <button class="w-full flex items-center justify-between p-3 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-primary hover:bg-primary/5 transition group">
                            <div class="flex items-center gap-3 text-slate-700 dark:text-slate-200 font-medium group-hover:text-primary">
                                <span class="material-symbols-outlined text-slate-400 group-hover:text-primary">add_box</span>
                                Añadir Producto
                            </div>
                            <span class="material-symbols-outlined text-sm text-slate-400">arrow_forward_ios</span>
                        </button>
                        <button class="w-full flex items-center justify-between p-3 rounded-xl border border-slate-200 dark:border-slate-600 hover:border-primary hover:bg-primary/5 transition group">
                            <div class="flex items-center gap-3 text-slate-700 dark:text-slate-200 font-medium group-hover:text-primary">
                                <span class="material-symbols-outlined text-slate-400 group-hover:text-primary">local_shipping</span>
                                Procesar Pedidos
                            </div>
                            <div class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">5</div>
                        </button>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-gradient-to-br from-primary to-[#5cdb42] rounded-2xl shadow-lg p-6 text-background-dark">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined">rocket_launch</span>
                        <h3 class="font-extrabold text-lg">Tienda Operativa</h3>
                    </div>
                    <p class="text-sm font-medium opacity-90 mb-4">PawfectShop está funcionando correctamente y los sistemas están actualizados.</p>
                    <a href="#" class="text-sm font-bold bg-background-dark/20 hover:bg-background-dark/30 px-4 py-2 rounded-xl transition inline-block">Ver Status Global</a>
                </div>
            </div>
        </div>
    </div>
</template>
