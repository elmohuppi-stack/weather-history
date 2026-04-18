import { createRouter, createWebHistory } from 'vue-router'
import DashboardView from '../views/DashboardView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'dashboard',
      component: DashboardView
    },
    {
      path: '/stations',
      name: 'stations',
      component: () => import('../views/StationsView.vue')
    },
    {
      path: '/stations/:id',
      name: 'station-detail',
      component: () => import('../views/StationDetailView.vue')
    },
    {
      path: '/charts',
      name: 'charts',
      component: () => import('../views/ChartsView.vue')
    },
    {
      path: '/maps',
      name: 'maps',
      component: () => import('../views/MapsView.vue')
    },
    {
      path: '/export',
      name: 'export',
      component: () => import('../views/ExportView.vue')
    }
  ]
})

export default router