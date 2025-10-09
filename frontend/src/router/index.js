import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import HomeView from '../views/HomeView.vue'
import MerchantForm from '../views/MerchantForm.vue'
import FormSuccess from '../views/FormSuccess.vue'
import Dashboard from '../views/Dashboard.vue'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import ApplicationDetails from '../views/ApplicationDetails.vue'
import NotificationPage from '../views/NotificationPage.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: HomeView,
    meta: { title: 'Accueil' }
  },
  {
    path: '/formulaire',
    name: 'MerchantForm',
    component: MerchantForm,
    meta: { title: 'Formulaire Marchand' }
  },
  {
    path: '/success',
    name: 'FormSuccess',
    component: FormSuccess,
    meta: { title: 'Demande envoyée' }
  },
  {
    path: '/login',
    name: 'Login',
    component: LoginView,
    meta: { 
      title: 'Connexion',
      requiresGuest: true
    }
  },
  {
    path: '/register',
    name: 'Register',
    component: RegisterView,
    meta: { 
      title: 'Créer un utilisateur',
      requiresAuth: true,
      requiresAdmin: true
    }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { 
      title: 'Tableau de bord',
      requiresAuth: true
    }
  },
  {
    path: '/applications/:id',
    name: 'ApplicationDetails',
    component: ApplicationDetails,
    meta: {
      title: 'Détails de la candidature',
      requiresAuth: true
    }
  },
  {
    path: '/applications/:id/edit',
    name: 'EditApplication',
    component: MerchantForm,
    meta: {
      title: 'Modifier la candidature',
      requiresAuth: true
    }
  },
  {
    path: '/notifications',
    name: 'notifications',
    component: NotificationPage,
    meta: {
      title: 'Notifications',
      requiresAuth: true
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // Mettre à jour le titre de la page
  document.title = `${to.meta.title} - Moov Money`

  // Si la route requiert un invité (non connecté)
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    return next({ name: 'Dashboard' })
  }

  // Si la route requiert l'authentification
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ 
      name: 'Login',
      query: { redirect: to.fullPath }
    })
  }

  // Si la route requiert un admin
  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    return next({ name: 'Dashboard' })
  }

  next()
})

export default router