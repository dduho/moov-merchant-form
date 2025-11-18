import axios from 'axios'

class MerchantService {
  constructor() {
    this.baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
    
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
    if (formData.merchantPhone || formData.personalPhone || formData.businessPhone) {
      const merchantPhone = formData.merchantPhone || formData.personalPhone || formData.businessPhone
      if (merchantPhone && merchantPhone.trim()) {
        submitData.append('merchant_phone', merchantPhone.trim())
      }
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
    submitData.append('has_anid_card', formData.hasAnidCard ? '1' : '0')
    if (formData.anidNumber && formData.anidNumber.trim()) {
      submitData.append('anid_number', formData.anidNumber.trim())
    }
    if (formData.anidExpiryDate) {
      submitData.append('anid_expiry_date', formData.anidExpiryDate)
    }
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
    
    // Helper function to extract file from object or return direct file
    const getFile = (fileObj) => {
      if (!fileObj) return null
      
      
      // Direct File check
      if (fileObj instanceof File) return fileObj
      if (fileObj instanceof Blob && fileObj.name) {
        return new File([fileObj], fileObj.name, { 
          type: fileObj.type,
          lastModified: fileObj.lastModified || Date.now()
        })
      }
      
      // Check for Vue proxy objects with 'dataUrl' property (base64 encoded files)
      
      if (fileObj && typeof fileObj === 'object' && fileObj.dataUrl && typeof fileObj.dataUrl === 'string') {
        try {
          // Convert base64 data URL to Blob synchronously
          const dataUrl = fileObj.dataUrl
          const arr = dataUrl.split(',')
          const mime = arr[0].match(/:(.*?);/)[1]
          const bstr = atob(arr[1])
          let n = bstr.length
          const u8arr = new Uint8Array(n)
          while (n--) {
            u8arr[n] = bstr.charCodeAt(n)
          }
          const blob = new Blob([u8arr], { type: mime })
          
          // Create File from Blob with metadata
          const file = new File([blob], fileObj.name || 'document', {
            type: fileObj.type || mime,
            lastModified: fileObj.lastModified || Date.now()
          })
          return file
        } catch (error) {
          console.error('Failed to convert dataUrl to File:', error)
        }
      }
      
      // Check for Vue proxy objects with 'file' property (most common case)
      if (fileObj && typeof fileObj === 'object' && fileObj.file) {
        if (fileObj.file instanceof File) {
          return fileObj.file
        }
        if (fileObj.file instanceof Blob) {
          // Use metadata from the wrapper object
          return new File([fileObj.file], fileObj.name || 'document', { 
            type: fileObj.type || fileObj.file.type,
            lastModified: fileObj.lastModified || Date.now()
          })
        }
      }
      
      // Check if it's a File-like object with data property
      if (fileObj.data instanceof File) return fileObj.data
      if (fileObj.data instanceof Blob && fileObj.name) {
        return new File([fileObj.data], fileObj.name, { 
          type: fileObj.type,
          lastModified: fileObj.lastModified || Date.now()
        })
      }
      if (fileObj.value instanceof File) return fileObj.value
      
      // Try array access (in case it's wrapped in an array)
      if (Array.isArray(fileObj) && fileObj.length > 0) {
        if (fileObj[0] instanceof File) return fileObj[0]
        if (fileObj[0] instanceof Blob && fileObj.name) {
          return new File([fileObj[0]], fileObj.name, { 
            type: fileObj.type,
            lastModified: fileObj.lastModified || Date.now()
          })
        }
      }
      
      // Try accessing properties dynamically for any File or Blob
      if (typeof fileObj === 'object') {
        for (const key of Object.keys(fileObj)) {
          if (fileObj[key] instanceof File) {
            return fileObj[key]
          }
          if (fileObj[key] instanceof Blob) {
            return new File([fileObj[key]], fileObj.name || `document.${key}`, { 
              type: fileObj.type || fileObj[key].type,
              lastModified: fileObj.lastModified || Date.now()
            })
          }
        }
      }
      return null
    }
    

    // Documents - S'assurer que ce sont des objets File valides
    if (formData.documents) {
      
      const file_idCard = getFile(formData.documents.idCard)
      if (file_idCard) {
        submitData.append('id_card', file_idCard)
      }
      
      const file_anidCard = getFile(formData.documents.anidCard)
      if (file_anidCard) {
        submitData.append('anid_card', file_anidCard)
      }
      
      const file_cfeCard = getFile(formData.documents.cfeCard)
      if (file_cfeCard) {
        submitData.append('cfe_document', file_cfeCard)
      }
      
      // Legacy field names for backward compatibility
      const file_residenceCard = getFile(formData.documents.residenceCard)
      if (file_residenceCard) {
        submitData.append('residence_card', file_residenceCard)
      }
      
      const file_residenceProof = getFile(formData.documents.residenceProof)
      if (file_residenceProof) {
        submitData.append('residence_proof', file_residenceProof)
      }
      
      const file_businessDocument = getFile(formData.documents.businessDocument)
      if (file_businessDocument) {
        submitData.append('business_document', file_businessDocument)
      }
      
      const file_nifDocument = getFile(formData.documents.nifDocument)
      if (file_nifDocument) {
        submitData.append('nif_document', file_nifDocument)
      }
    }

    // Debug: Log document processing and FormData being sent
    // Debug logs removed
    
    const response = await this.client.post('/merchant-applications', submitData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    return response.data
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
      merchant_phone: formData.merchantPhone || formData.personalPhone || '',
      email: formData.email || '',
      address: formData.address || '',
      
      // Informations d'identité
      id_type: formData.idType || '',
      id_number: formData.idNumber || '',
      id_expiry_date: formData.idExpiryDate || '',
      has_anid_card: formData.hasAnidCard ? 1 : 0,
      anid_number: formData.anidNumber || null,
      anid_expiry_date: formData.anidExpiryDate || null,
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
    submitData.append('merchant_phone', (formData.merchantPhone && formData.merchantPhone.trim()) || (formData.personalPhone && formData.personalPhone.trim()) || '')
    if (formData.email && formData.email.trim()) {
      submitData.append('email', formData.email.trim())
    }
    submitData.append('address', (formData.address && formData.address.trim()) || '')

    // Informations d'identité (required fields must always be included)
    submitData.append('id_type', formData.idType || '')
    submitData.append('id_number', (formData.idNumber && formData.idNumber.trim()) || '')
    submitData.append('id_expiry_date', formData.idExpiryDate || '')
    submitData.append('has_anid_card', formData.hasAnidCard ? '1' : '0')
    if (formData.anidNumber && formData.anidNumber.trim()) {
      submitData.append('anid_number', formData.anidNumber.trim())
    }
    if (formData.anidExpiryDate) {
      submitData.append('anid_expiry_date', formData.anidExpiryDate)
    }
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
      console.log('[DEBUG] Documents trouvés dans formData:', Object.keys(formData.documents));
      console.log('[DEBUG] Type de documents:', Array.isArray(formData.documents) ? 'Array' : 'Object');
      console.log('[DEBUG] Contenu complet de documents:', formData.documents);
      
      // Helper function to extract file from object or return direct file
      // Only return files that need to be uploaded (skip already uploaded documents)
      const getFile = (fileObj) => {
        if (!fileObj) return null
        
        // Skip already uploaded documents (they have uploaded: true property)
        if (fileObj.uploaded === true) {
          console.log('[DEBUG] Document déjà uploadé, ignoré:', fileObj.name || fileObj.id);
          return null
        }
        
        // Direct File check
        if (fileObj instanceof File) {
          console.log('[DEBUG] Document direct File:', fileObj.name);
          return fileObj
        }
        
        // Check for wrapped file in 'file' property (most common case from FileUpload component)
        if (fileObj.file instanceof File) {
          console.log('[DEBUG] Document avec propriété file:', fileObj.file.name);
          return fileObj.file
        }
        
        console.log('[DEBUG] Aucun fichier trouvé dans:', JSON.stringify(fileObj));
        return null
      }
      
      console.log('[DEBUG] Traitement des documents individuels...');
      
      const file_idCard = getFile(formData.documents.idCard)
      if (file_idCard) {
        console.log('[DEBUG] Ajout de id_card:', file_idCard.name);
        submitData.append('id_card', file_idCard)
      }
      
      const file_anidCard = getFile(formData.documents.anidCard)
      if (file_anidCard) {
        console.log('[DEBUG] Ajout de anid_card:', file_anidCard.name);
        submitData.append('anid_card', file_anidCard)
      }
      
      const file_cfeCard = getFile(formData.documents.cfeCard)
      if (file_cfeCard) {
        console.log('[DEBUG] Ajout de cfe_document:', file_cfeCard.name);
        submitData.append('cfe_document', file_cfeCard)
      }
      
      const file_businessDocument = getFile(formData.documents.businessDocument)
      if (file_businessDocument) {
        submitData.append('business_document', file_businessDocument)
      }
      
      const file_residenceCard = getFile(formData.documents.residenceCard)
      if (file_residenceCard) {
        submitData.append('residence_card', file_residenceCard)
      }
      
      const file_residenceProof = getFile(formData.documents.residenceProof)
      if (file_residenceProof) {
        submitData.append('residence_proof', file_residenceProof)
      }
      
      const file_nifDocument = getFile(formData.documents.nifDocument)
      if (file_nifDocument) {
        submitData.append('nif_document', file_nifDocument)
      }
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
}

export default new MerchantService()