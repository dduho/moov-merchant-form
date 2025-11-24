import { defineStore } from 'pinia'
import { ref } from 'vue'
import MerchantService from '../services/MerchantService'
import StorageService from '../services/StorageService'

export const useMerchantStore = defineStore('merchant', () => {
  const formData = ref({})
  const submissions = ref([])
  const isOnline = ref(navigator.onLine)
  
  // Sauvegarde des données de formulaire
  const saveFormData = async (data) => {
    // Clone the data and prepare it for storage
    const storableData = JSON.parse(JSON.stringify({
      ...data,
      // Handle special objects that need serialization
      documents: Object.entries(data.documents || {}).reduce((acc, [key, file]) => {
        if (file instanceof File || file instanceof Blob) {
          acc[key] = {
            name: file.name,
            type: file.type,
            size: file.size,
            lastModified: file.lastModified,
            dataUrl: file.dataUrl || null // Sauvegarder l'URL des données si disponible
          }
        } else {
          acc[key] = file
        }
        return acc
      }, {}),
      // Handle signature if it exists (it might be a canvas or data URL)
      signature: typeof data.signature === 'string' ? data.signature : null,
      // Handle location object if it exists
      location: data.location ? {
        lat: data.location.lat,
        lng: data.location.lng,
        accuracy: data.location.accuracy
      } : null
    }))

    formData.value = storableData
    await StorageService.saveForm(storableData)
  }
  
  // Chargement des données sauvegardées
  const loadFormData = async () => {
    const savedData = await StorageService.loadForm()
    if (savedData) {
      // Restore the data structure
      const restoredData = {
        ...savedData,
        documents: savedData.documents || {},
        signature: savedData.signature || null,
        location: savedData.location || null
      }
      formData.value = restoredData
      return restoredData
    }
    return null
  }
  
  // Soumission de candidature
  const submitApplication = async (data) => {
    try {
      // Préparer les données pour l'envoi
      const submitData = {
        // Informations personnelles
        firstName: data.firstName,
        lastName: data.lastName,
        birthDate: data.birthDate,
        birthPlace: data.birthPlace,
        gender: data.gender,
        nationality: data.nationality,
        personalPhone: data.personalPhone,
        merchantPhone: data.merchantPhone || data.personalPhone, // Fallback sur personalPhone
        email: data.email, // Correction: utiliser 'email' au lieu de 'personalEmail'
        address: data.address,
        
        // Identité
        idType: data.idType,
        idNumber: data.idNumber,
        idExpiryDate: data.idExpiryDate,
        hasAnidCard: data.hasAnidCard,
        anidNumber: data.anidNumber,
        anidExpiryDate: data.anidExpiryDate,
        isForeigner: data.isForeigner,
        
        // Informations commercial
        commercialLastName: data.commercialLastName,
        commercialFirstName: data.commercialFirstName,
        commercialPhone: data.commercialPhone,
        
        // Informations commerciales
        businessName: data.businessName,
        businessType: data.businessType,
        businessPhone: data.businessPhone, // Envoyer businessPhone directement
        businessEmail: data.businessEmail,
        businessAddress: data.businessAddress,
        region: data.region,
        city: data.city,
        usageType: data.usageType, // TRADER, MERC, TRADERWNIF, CORP
        hasCFE: data.hasCFE,
        cfeNumber: data.cfeNumber,
        cfeExpiryDate: data.cfeExpiryDate,
        hasNIF: data.hasNIF,
        nifNumber: data.nifNumber,
        
        // Localisation - utiliser les données de location
        location: data.location,
        locationDescription: data.locationDescription,
        
        // Signature
        signature: data.signature,
        acceptTerms: data.acceptTerms,
        
        // Documents
        documents: data.documents
      }
      
      if (isOnline.value) {
        // Envoi en ligne immédiat
        const response = await MerchantService.submitApplication(submitData)
        
        // Supprimer les données sauvegardées après envoi réussi
        await StorageService.clearForm()
        
        return response
      } else {
        // Stockage pour envoi ultérieur
        await StorageService.savePendingSubmission(submitData)
        throw new Error('Pas de connexion. Votre demande sera envoyée automatiquement dès que vous serez en ligne.')
      }
    } catch (error) {
      if (!isOnline.value) {
        // Sauvegarder pour envoi ultérieur
        await StorageService.savePendingSubmission(data)
      }
      throw error
    }
  }
  
  // Synchronisation des données en attente
  const syncPendingSubmissions = async () => {
    if (!isOnline.value) return
    
    const pendingSubmissions = await StorageService.getPendingSubmissions()
    
    for (const submission of pendingSubmissions) {
      try {
        await MerchantService.submitApplication(submission.data)
        await StorageService.removePendingSubmission(submission.id)
      } catch (error) {
        console.error('Erreur sync submission:', error)
      }
    }
  }
  
  // Mise à jour du statut de connexion
  const updateOnlineStatus = (status) => {
    isOnline.value = status
    if (status) {
      // Synchroniser quand on revient en ligne
      syncPendingSubmissions()
    }
  }

  // Récupérer une candidature pour édition
  const getApplicationForEdit = async (applicationId) => {
    try {
      return await MerchantService.getApplicationForEdit(applicationId)
    } catch (error) {
      console.error('Erreur lors de la récupération de la candidature:', error)
      throw error
    }
  }

  // Mettre à jour une candidature
  const updateApplication = async (applicationId, data) => {
    try {
      if (isOnline.value) {
        return await MerchantService.updateApplication(applicationId, data)
      } else {
        // En mode hors ligne, on pourrait sauvegarder la modification localement
        // Pour l'instant, on lève une erreur
        throw new Error('Modification impossible en mode hors ligne')
      }
    } catch (error) {
      console.error('Erreur lors de la mise à jour:', error)
      throw error
    }
  }
  
  // Effacement des données de formulaire
  const clearFormData = async () => {
    formData.value = {}
    await StorageService.clearForm()
  }
  
  return {
    formData,
    submissions,
    isOnline,
    saveFormData,
    loadFormData,
    clearFormData,
    submitApplication,
    syncPendingSubmissions,
    updateOnlineStatus,
    getApplicationForEdit,
    updateApplication
  }
})