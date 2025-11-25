import axios from 'axios'

class MerchantService {
  constructor() {
    const apiBaseUrl = import.meta.env.VITE_API_URL || ''
    this.baseURL = apiBaseUrl ? `${apiBaseUrl}/api` : '/api'
    
    this.client = axios.create({
      baseURL: this.baseURL,
      withCredentials: true, // Important pour l'authentification Sanctum
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    // Intercepteur pour les erreurs
    this.client.interceptors.response.use(
      response => response,
      error => {
        console.error('API Error:', error)
        return Promise.reject(error)
      }
    )
  }
  
  // Soumission d'une candidature marchand
  async submitApplication(formData) {
    const submitData = new FormData()
    
    // Informations personnelles - N'ajouter que si les valeurs existent et ne sont pas vides
    if (formData.firstName && formData.firstName.trim()) {
      submitData.append('first_name', formData.firstName.trim())
    }
    if (formData.lastName && formData.lastName.trim()) {
      submitData.append('last_name', formData.lastName.trim())
    }
    if (formData.birthDate) {
      submitData.append('birth_date', formData.birthDate)
    }
    if (formData.birthPlace && formData.birthPlace.trim()) {
      submitData.append('birth_place', formData.birthPlace.trim())
    }
    if (formData.gender) {
      submitData.append('gender', formData.gender)
    }
    if (formData.nationality && formData.nationality.trim()) {
      submitData.append('nationality', formData.nationality.trim())
    }
    if (formData.personalPhone && formData.personalPhone.trim()) {
      submitData.append('phone', formData.personalPhone.trim())
    }
    if (formData.email && formData.email.trim()) {
      submitData.append('email', formData.email.trim())
    }
    if (formData.address && formData.address.trim()) {
      submitData.append('address', formData.address.trim())
    }
    
    // Identité
    if (formData.idType) {
      submitData.append('id_type', formData.idType)
    }
    if (formData.idNumber && formData.idNumber.trim()) {
      submitData.append('id_number', formData.idNumber.trim())
    }
    if (formData.idExpiryDate) {
      submitData.append('id_expiry_date', formData.idExpiryDate)
    }
    // ANID fields removed: handled as a regular id_type ('carte_anid')
    submitData.append('is_foreigner', formData.isForeigner ? '1' : '0')
    
    // Informations commercial
    if (formData.commercialLastName && formData.commercialLastName.trim()) {
      submitData.append('commercial_last_name', formData.commercialLastName.trim())
    }
    if (formData.commercialFirstName && formData.commercialFirstName.trim()) {
      submitData.append('commercial_first_name', formData.commercialFirstName.trim())
    }
    if (formData.commercialPhone && formData.commercialPhone.trim()) {
      submitData.append('commercial_phone', formData.commercialPhone.trim())
    }
    if (formData.merchantPhone && formData.merchantPhone.trim()) {
      submitData.append('merchant_phone', formData.merchantPhone.trim())
    }
    
    // Informations commerciales
    if (formData.businessName && formData.businessName.trim()) {
      submitData.append('business_name', formData.businessName.trim())
    }
    if (formData.businessType) {
      submitData.append('business_type', formData.businessType)
    }
    // Région et ville - seulement si elles ont des valeurs
    if (formData.region && formData.region.trim()) {
      submitData.append('region', formData.region.trim())
    }
    if (formData.city && formData.city.trim()) {
      submitData.append('city', formData.city.trim())
    }
    
    // Téléphone du commerce (valeur unique)
    if (formData.businessPhone && formData.businessPhone.trim()) {
      submitData.append('business_phone', formData.businessPhone.trim())
    }
    
    if (formData.businessEmail) {
      submitData.append('business_email', formData.businessEmail.trim())
    }
    if (formData.businessAddress && formData.businessAddress.trim()) {
      submitData.append('business_address', formData.businessAddress.trim())
    } else if (formData.address && formData.address.trim()) {
      submitData.append('business_address', formData.address.trim())
    }
    if (formData.usageType) {
      submitData.append('usage_type', formData.usageType)
    }
    submitData.append('has_cfe', formData.hasCFE ? '1' : '0')
    if (formData.cfeNumber && formData.cfeNumber.trim()) {
      submitData.append('cfe_number', formData.cfeNumber.trim())
    }
    if (formData.cfeExpiryDate) {
      submitData.append('cfe_expiry_date', formData.cfeExpiryDate)
    }
    submitData.append('has_nif', formData.hasNIF ? '1' : '0')
    if (formData.nifNumber && formData.nifNumber.trim()) {
      submitData.append('nif_number', formData.nifNumber.trim())
    }
    
    // Localisation
    if (formData.location) {
      const shopAddress = formData.location.address || formData.businessAddress || ''
      if (shopAddress.trim()) {
        submitData.append('shop_address', shopAddress.trim())
      }
      if (formData.location.city && formData.location.city.trim()) {
        submitData.append('shop_city', formData.location.city.trim())
      }
      if (formData.location.lat) {
        submitData.append('latitude', formData.location.lat.toString())
      }
      if (formData.location.lng) {
        submitData.append('longitude', formData.location.lng.toString())
      }
    } else if (formData.businessAddress && formData.businessAddress.trim()) {
      submitData.append('shop_address', formData.businessAddress.trim())
    }
    if (formData.locationDescription && formData.locationDescription.trim()) {
      submitData.append('location_description', formData.locationDescription.trim())
    }
    
    // Signature
    if (formData.signature && formData.signature.trim()) {
      submitData.append('signature', formData.signature.trim())
    }
    if (formData.acceptTerms) {
      submitData.append('accept_terms', '1')
    }
    
    // Helper: convert wrapper or File into File instance
    const toFile = (fileObj) => {
      if (!fileObj) return null
      if (fileObj instanceof File) return fileObj
      if (fileObj instanceof Blob && fileObj.name) return new File([fileObj], fileObj.name, { type: fileObj.type, lastModified: fileObj.lastModified || Date.now() })
      if (fileObj && typeof fileObj === 'object' && fileObj.file instanceof File) return fileObj.file
      if (fileObj && typeof fileObj === 'object' && fileObj.dataUrl && fileObj.name) {
        try {
          const arr = fileObj.dataUrl.split(',')
          const mime = arr[0].match(/:(.*?);/)[1]
          const bstr = atob(arr[1])
          let n = bstr.length
          const u8arr = new Uint8Array(n)
          while (n--) u8arr[n] = bstr.charCodeAt(n)
          const blob = new Blob([u8arr], { type: mime })
          return new File([blob], fileObj.name, { type: fileObj.type || mime, lastModified: fileObj.lastModified || Date.now() })
        } catch (e) {
          console.error('Failed to convert dataUrl to File:', e)
        }
      }
      return null
    }

    const appendFiles = (key, fileWrapper) => {
      if (!fileWrapper) return
      // If it's an array of wrappers
      if (Array.isArray(fileWrapper)) {
        fileWrapper.forEach((fw) => {
          const f = toFile(fw)
          if (f) submitData.append(`${key}[]`, f)
        })
        return
      }
      // single wrapper
      const f = toFile(fileWrapper)
      if (f) submitData.append(key, f)
    }

    // Documents - may be arrays
    if (formData.documents) {
      appendFiles('id_card', formData.documents.idCard)
      appendFiles('cfe_document', formData.documents.cfeCard)
      appendFiles('residence_card', formData.documents.residenceCard)
      appendFiles('residence_proof', formData.documents.residenceProof)
      appendFiles('business_document', formData.documents.businessDocument)
      appendFiles('nif_document', formData.documents.nifDocument)
    }

    // Debug: Log document processing and FormData being sent
    // Debug logs removed

    try {
      const response = await this.client.post('/merchant-applications', submitData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })

      return response.data
    } catch (error) {
      // Log detailed validation errors
      if (error.response?.status === 422 && error.response?.data) {
        console.error('❌ Validation errors:', error.response.data)
        console.error('❌ Failed fields:', error.response.data.errors)
      }
      throw error
    }
  }
  
  // Récupération du statut d'une candidature
  async getApplicationStatus(applicationId) {
    const response = await this.client.get(`/merchant-applications/${applicationId}`)
    return response.data
  }
  
  // Upload d'un document individuel
  async uploadDocument(file, type) {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('type', type)
    
    const response = await this.client.post('/upload-document', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    return response.data
  }

  // Récupérer une candidature pour édition
  async getApplicationForEdit(applicationId) {
    try {
      const response = await this.client.get(`/merchant-applications/${applicationId}`)
      return response.data
    } catch (error) {
      console.error('Erreur lors de la récupération de la candidature:', error)
      if (error.response) {
        throw new Error(`Erreur ${error.response.status}: ${error.response.data.message || 'Candidature introuvable'}`)
      } else if (error.request) {
        throw new Error('Problème de connexion au serveur')
      } else {
        throw new Error('Erreur inattendue lors du chargement')
      }
    }
  }

  // Mise à jour complète d'une candidature
  async updateApplication(applicationId, formData) {
    // For updates, use FormData to support file uploads
    return this.updateApplicationFormData(applicationId, formData)
  }

  // Version JSON de la mise à jour (plus fiable avec Laravel PUT)
  async updateApplicationJSON(applicationId, formData) {
    
    
    const jsonData = {
      // Informations personnelles
      first_name: formData.firstName || '',
      last_name: formData.lastName || '',
      birth_date: formData.birthDate || '',
      birth_place: formData.birthPlace || '',
      gender: formData.gender || '',
      nationality: formData.nationality || '',
      phone: formData.personalPhone || '',
      merchant_phone: formData.merchantPhone || null,
      email: formData.email || '',
      address: formData.address || '',
      
      // Informations d'identité
      id_type: formData.idType || '',
      id_number: formData.idNumber || '',
      id_expiry_date: formData.idExpiryDate || '',
      // ANID fields removed: use id_type === 'carte_anid' and `id_number` (no document upload)
      is_foreigner: formData.isForeigner ? 1 : 0,
      
      // Informations business
      business_name: formData.businessName || '',
      business_type: formData.businessType || '',
      business_phone: formData.businessPhone || null,
      business_email: formData.businessEmail || null,
      business_address: formData.businessAddress || null,
      region: formData.region || null,
      city: formData.city || null,
      usage_type: formData.usageType || null,
      has_cfe: formData.hasCFE ? 1 : 0,
      cfe_number: formData.cfeNumber || null,
      cfe_expiry_date: formData.cfeExpiryDate || null,
      has_nif: formData.hasNIF ? 1 : 0,
      nif_number: formData.nifNumber || null,
      
      // Localisation
      latitude: formData.latitude || null,
      longitude: formData.longitude || null,
      location_accuracy: formData.locationAccuracy || null,
      location_description: formData.locationDescription || null,
      
      // Signature et conditions
      signature: formData.signature || '',
      accept_terms: formData.acceptTerms ? 1 : 0,
    }

    try {
      
      
      const response = await this.client.put(`/merchant-applications/${applicationId}/full`, jsonData, {
        headers: {
          'Content-Type': 'application/json'
        }
      })

      return response.data
    } catch (error) {
      console.error('Erreur lors de la mise à jour JSON:', error);
      
      if (error.response && error.response.status === 422) {
        console.error('Erreurs de validation JSON:', error.response.data);
      }
      
      throw error;
    }
  }

  // Version FormData de la mise à jour (pour référence)
  async updateApplicationFormData(applicationId, formData) {
    
    
    const submitData = new FormData()
    
    // Utiliser la même logique que submitApplication pour construire les données
    // Informations personnelles (required fields must always be included)
    submitData.append('first_name', (formData.firstName && formData.firstName.trim()) || '')
    submitData.append('last_name', (formData.lastName && formData.lastName.trim()) || '')
    submitData.append('birth_date', formData.birthDate || '')
    submitData.append('birth_place', (formData.birthPlace && formData.birthPlace.trim()) || '')
    submitData.append('gender', formData.gender || '')
    submitData.append('nationality', (formData.nationality && formData.nationality.trim()) || '')
    submitData.append('phone', (formData.personalPhone && formData.personalPhone.trim()) || '')
    if (formData.merchantPhone && formData.merchantPhone.trim()) {
      submitData.append('merchant_phone', formData.merchantPhone.trim())
    }
    if (formData.email && formData.email.trim()) {
      submitData.append('email', formData.email.trim())
    }
    submitData.append('address', (formData.address && formData.address.trim()) || '')

    // Informations d'identité (required fields must always be included)
    submitData.append('id_type', formData.idType || '')
    submitData.append('id_number', (formData.idNumber && formData.idNumber.trim()) || '')
    submitData.append('id_expiry_date', formData.idExpiryDate || '')
    // ANID fields removed from FormData
    submitData.append('is_foreigner', formData.isForeigner ? '1' : '0')

    // Informations business (required fields must always be included)
    submitData.append('business_name', (formData.businessName && formData.businessName.trim()) || '')
    submitData.append('business_type', formData.businessType || '')
    if (formData.businessPhone && formData.businessPhone.trim()) {
      submitData.append('business_phone', formData.businessPhone.trim())
    }
    if (formData.businessEmail && formData.businessEmail.trim()) {
      submitData.append('business_email', formData.businessEmail.trim())
    }
    if (formData.businessAddress && formData.businessAddress.trim()) {
      submitData.append('business_address', formData.businessAddress.trim())
    }
    if (formData.region && formData.region.trim()) {
      submitData.append('region', formData.region.trim())
    }
    if (formData.city && formData.city.trim()) {
      submitData.append('city', formData.city.trim())
    }
    if (formData.usageType) {
      submitData.append('usage_type', formData.usageType)
    }
    submitData.append('has_cfe', formData.hasCFE ? '1' : '0')
    if (formData.cfeNumber && formData.cfeNumber.trim()) {
      submitData.append('cfe_number', formData.cfeNumber.trim())
    }
    if (formData.cfeExpiryDate) {
      submitData.append('cfe_expiry_date', formData.cfeExpiryDate)
    }
    submitData.append('has_nif', formData.hasNIF ? '1' : '0')
    if (formData.nifNumber && formData.nifNumber.trim()) {
      submitData.append('nif_number', formData.nifNumber.trim())
    }

    // Localisation - support both location object and direct lat/lng fields
    if (formData.location) {
      if (formData.location.lat) {
        submitData.append('latitude', formData.location.lat.toString())
      }
      if (formData.location.lng) {
        submitData.append('longitude', formData.location.lng.toString())
      }
    } else {
      // Fallback to direct latitude/longitude fields if location object not present
      if (formData.latitude) {
        submitData.append('latitude', formData.latitude.toString())
      }
      if (formData.longitude) {
        submitData.append('longitude', formData.longitude.toString())
      }
    }
    if (formData.locationAccuracy) {
      submitData.append('location_accuracy', formData.locationAccuracy)
    }
    if (formData.locationDescription && formData.locationDescription.trim()) {
      submitData.append('location_description', formData.locationDescription.trim())
    }

    // Signature (required field)
    submitData.append('signature', (formData.signature && formData.signature.trim()) || '')

    // Conditions (required field)
    submitData.append('accept_terms', formData.acceptTerms ? '1' : '0')

    // Documents - Individual fields (matching backend expectations)
    if (formData.documents) {
      const toFile = (fileObj) => {
        if (!fileObj) return null
        if (fileObj.uploaded === true) return null
        if (fileObj instanceof File) return fileObj
        if (fileObj && typeof fileObj === 'object' && fileObj.file instanceof File) return fileObj.file
        if (fileObj && typeof fileObj === 'object' && fileObj.dataUrl && fileObj.name) {
          try {
            const arr = fileObj.dataUrl.split(',')
            const mime = arr[0].match(/:(.*?);/)[1]
            const bstr = atob(arr[1])
            let n = bstr.length
            const u8arr = new Uint8Array(n)
            while (n--) u8arr[n] = bstr.charCodeAt(n)
            const blob = new Blob([u8arr], { type: mime })
            return new File([blob], fileObj.name, { type: fileObj.type || mime, lastModified: fileObj.lastModified || Date.now() })
          } catch (e) {
            console.error('Error reconstructing file:', e)
          }
        }
        return null
      }

      const appendFiles = (key, wrapper) => {
        if (!wrapper) return
        if (Array.isArray(wrapper)) {
          wrapper.forEach((w) => {
            const f = toFile(w)
            if (f) submitData.append(`${key}[]`, f)
          })
          return
        }
        const f = toFile(wrapper)
        if (f) submitData.append(key, f)
      }

      appendFiles('id_card', formData.documents.idCard)
      appendFiles('cfe_document', formData.documents.cfeCard)
      appendFiles('business_document', formData.documents.businessDocument)
      appendFiles('residence_card', formData.documents.residenceCard)
      appendFiles('residence_proof', formData.documents.residenceProof)
      appendFiles('nif_document', formData.documents.nifDocument)
    }

    try {
      // Debug logs removed

      // Laravel doesn't parse FormData well for PUT requests, so we'll add _method for method spoofing
      submitData.append('_method', 'PUT')
      
      

      const response = await this.client.post(`/merchant-applications/${applicationId}/full`, submitData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })

      return response.data
    } catch (error) {
      console.error('Erreur lors de la mise à jour de la candidature:', error);
      
      // Log détaillé de l'erreur de validation
      if (error.response && error.response.status === 422) {
        console.error('Erreurs de validation détaillées:', error.response.data);
      }
      
      throw error;
    }
  }

  // Récupérer toutes les candidatures approuvées pour l'export SP
  async getApprovedApplicationsForExport() {
    try {
      const response = await this.client.get('/merchant-applications', {
        params: {
          status: 'approved',
          per_page: 1000 // Récupérer toutes les candidatures approuvées
        }
      })

      return response.data.data || []
    } catch (error) {
      console.error('Erreur lors de la récupération des candidatures approuvées:', error)
      throw error
    }
  }

  // Récupérer les candidatures approuvées + exportées pour création (pour Update SP)
  async getApprovedAndExportedForCreationApplications() {
    try {
      // Récupérer les deux types de candidatures en parallèle
      const [approvedResponse, exportedResponse] = await Promise.all([
        this.client.get('/merchant-applications', {
          params: {
            status: 'approved',
            per_page: 1000
          }
        }),
        this.client.get('/merchant-applications', {
          params: {
            status: 'exported_for_creation',
            per_page: 1000
          }
        })
      ])

      // Combiner les deux listes
      const approved = approvedResponse.data.data || []
      const exported = exportedResponse.data.data || []

      return [...approved, ...exported]
    } catch (error) {
      console.error('Erreur lors de la récupération des candidatures pour update:', error)
      throw error
    }
  }

  // Marquer les candidatures comme exportées pour création dans SP
  async markApplicationsAsExported(applicationIds) {
    try {
      const response = await this.client.post('/merchant-applications/mark-as-exported', {
        application_ids: applicationIds
      })

      return response.data
    } catch (error) {
      console.error('Erreur lors du marquage des candidatures comme exportées:', error)
      throw error
    }
  }

  // Marquer les candidatures comme exportées pour modification dans SP
  async markApplicationsAsExportedForUpdate(applicationIds) {
    try {
      const response = await this.client.post('/merchant-applications/mark-as-exported-for-update', {
        application_ids: applicationIds
      })

      return response.data
    } catch (error) {
      console.error('Erreur lors du marquage des candidatures comme exportées pour modification:', error)
      throw error
    }
  }
}

export default new MerchantService()