import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// Eager loading pour la page d'accueil (première visite)
import HomeView from '../views/HomeView.vue'

// Lazy loading pour toutes les autres routes
const MerchantForm = () => import('../views/MerchantForm.vue')
const FormSuccess = () => import('../views/FormSuccess.vue')
const Dashboard = () => import('../views/Dashboard.vue')
const LoginView = () => import('../views/LoginView.vue')
const RegisterView = () => import('../views/RegisterView.vue')
const ChangePasswordRequired = () => import('../views/ChangePasswordRequired.vue')
const ApplicationDetails = () => import('../views/ApplicationDetails.vue')
const NotificationPage = () => import('../views/NotificationPage.vue')
const UserManagement = () => import('../views/UserManagement.vue')
const ObjectiveManagement = () => import('../views/ObjectiveManagement.vue')

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
    path: '/change-password-required',
    name: 'ChangePasswordRequired',
    component: ChangePasswordRequired,
    meta: {
      title: 'Changement de mot de passe requis',
      requiresAuth: true
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
  },
  {
    path: '/dashboard/users',
    name: 'UserManagement',
    component: UserManagement,
    meta: {
      title: 'Gestion des utilisateurs',
      requiresAuth: true,
      requiresAdmin: true
    }
  },
  {
    path: '/dashboard/objectives',
    name: 'ObjectiveManagement',
    component: ObjectiveManagement,
    meta: {
      title: 'Gestion des objectifs',
      requiresAuth: true,
      requiresAdmin: true
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Prefetch des routes probables selon le contexte
const prefetchRoutes = {
  'Home': ['MerchantForm', 'Login'],
  'Login': ['Dashboard', 'ChangePasswordRequired'],
  'MerchantForm': ['FormSuccess', 'Dashboard'],
  'Dashboard': ['ApplicationDetails', 'NotificationPage', 'MerchantForm'],
  'ApplicationDetails': ['Dashboard', 'MerchantForm']
}

// Fonction pour prefetch une route
const prefetchRoute = (routeName) => {
  const route = routes.find(r => r.name === routeName)
  if (route && typeof route.component === 'function') {
    // Précharger le composant sans bloquer
    route.component().catch(() => {
      // Ignorer les erreurs de préchargement
    })
  }
}

// Navigation guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // Mettre à jour le titre de la page
  document.title = `${to.meta.title} - Moov Money`

  // Prefetch des routes probables après cette navigation
  if (prefetchRoutes[to.name]) {
    // Attendre que la navigation soit terminée
    setTimeout(() => {
      prefetchRoutes[to.name].forEach(routeName => {
        prefetchRoute(routeName)
      })
    }, 1000) // 1 seconde après le chargement de la page
  }

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

  // Si l'utilisateur doit changer son mot de passe et qu'il n'est pas déjà sur la page de changement
  if (authStore.isAuthenticated && authStore.mustChangePassword && to.name !== 'ChangePasswordRequired') {
    return next({ name: 'ChangePasswordRequired' })
  }

  // Si la route requiert un admin
  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    return next({ name: 'Dashboard' })
  }

  next()
})

export default router