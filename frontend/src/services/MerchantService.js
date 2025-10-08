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
    console.log('=== SUBMIT APPLICATION START ===')
    console.log('Form data received:', formData)
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
      
      console.log('getFile input:', fileObj)
      
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
        console.log('Found dataUrl property, converting base64 to File')
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
          console.log('Successfully converted dataUrl to File:', file)
          return file
        } catch (error) {
          console.error('Failed to convert dataUrl to File:', error)
        }
      }
      
      // Check for Vue proxy objects with 'file' property (most common case)
      if (fileObj && typeof fileObj === 'object' && fileObj.file) {
        console.log('Found file property:', fileObj.file)
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
            console.log(`Found File in property '${key}':`, fileObj[key])
            return fileObj[key]
          }
          if (fileObj[key] instanceof Blob) {
            console.log(`Found Blob in property '${key}':`, fileObj[key])
            return new File([fileObj[key]], fileObj.name || `document.${key}`, { 
              type: fileObj.type || fileObj[key].type,
              lastModified: fileObj.lastModified || Date.now()
            })
          }
        }
      }
      
      console.log('getFile failed to extract file from:', fileObj)
      return null
    }

    console.log('=== ABOUT TO PROCESS DOCUMENTS ===')
    console.log('formData.documents check:', formData.documents)
    console.log('formData.documents truthy?:', !!formData.documents)

    // Documents - S'assurer que ce sont des objets File valides
    if (formData.documents) {
      console.log('Processing documents section...')
      console.log('Documents object:', formData.documents)
      
      const file_idCard = getFile(formData.documents.idCard)
      console.log('ID Card extraction result:', file_idCard ? `File(${file_idCard.name}, ${file_idCard.size} bytes)` : 'null')
      if (file_idCard) {
        submitData.append('id_card', file_idCard)
      }
      
      const file_anidCard = getFile(formData.documents.anidCard)
      console.log('ANID Card extraction result:', file_anidCard ? `File(${file_anidCard.name}, ${file_anidCard.size} bytes)` : 'null')
      if (file_anidCard) {
        submitData.append('anid_card', file_anidCard)
      }
      
      const file_cfeCard = getFile(formData.documents.cfeCard)
      console.log('CFE Card extraction result:', file_cfeCard ? `File(${file_cfeCard.name}, ${file_cfeCard.size} bytes)` : 'null')
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
    console.log('=== Document Processing Debug ===')
    console.log('formData.documents exists:', !!formData.documents)
    if (formData.documents) {
      console.log('Available documents:', Object.keys(formData.documents))
      Object.entries(formData.documents).forEach(([key, doc]) => {
        console.log(`\n${key} analysis:`)
        if (!doc) {
          console.log('  - Value is null/undefined')
        } else {
          console.log('  - Type:', typeof doc)
          console.log('  - Is File:', doc instanceof File)
          console.log('  - Has file property:', doc && typeof doc === 'object' && doc.file instanceof File)
          console.log('  - Keys in object:', Object.keys(doc))
          console.log('  - Full structure:', JSON.parse(JSON.stringify(doc)))
          
          // Try different property access patterns
          console.log('  - doc.file:', doc.file)
          console.log('  - doc.data:', doc.data)
          console.log('  - doc.value:', doc.value)
          console.log('  - doc[0]:', doc[0])
          
          // Test the getFile function
          const extractedFile = getFile(doc)
          console.log('  - Extracted file:', extractedFile)
          console.log('  - Extracted file is File:', extractedFile instanceof File)
        }
      })
      
      // Test individual extractions
      const testIdCard = getFile(formData.documents.idCard)
      const testAnidCard = getFile(formData.documents.anidCard) 
      const testCfeCard = getFile(formData.documents.cfeCard)
      
      console.log('\n=== Individual Extractions ===')
      console.log('ID Card extracted:', testIdCard ? testIdCard.name : 'null')
      console.log('ANID Card extracted:', testAnidCard ? testAnidCard.name : 'null')
      console.log('CFE Card extracted:', testCfeCard ? testCfeCard.name : 'null')
    } else {
      console.log('No formData.documents object found!')
    }
    
    // Log what's being appended to FormData
    console.log('\n=== FormData Entries ===')
    for (let [key, value] of submitData.entries()) {
      if (value instanceof File) {
        console.log(`${key}: File(${value.name}, ${value.size} bytes)`)
      } else {
        console.log(`${key}: ${value}`)
      }
    }
    
    console.log('=== FormData being sent ===')
    for (let [key, value] of submitData.entries()) {
      if (value instanceof File) {
        console.log(`${key}: File(${value.name}, ${value.size} bytes)`)
      } else {
        console.log(`${key}: ${value}`)
      }
    }
    
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
    // For updates, use JSON instead of FormData to avoid Laravel PUT+FormData issues
    return this.updateApplicationJSON(applicationId, formData)
  }

  // Version JSON de la mise à jour (plus fiable avec Laravel PUT)
  async updateApplicationJSON(applicationId, formData) {
    console.log('Updating application with JSON:', applicationId, formData)
    
    const jsonData = {
      // Informations personnelles
      first_name: formData.firstName || '',
      last_name: formData.lastName || '',
      birth_date: formData.birthDate || '',
      birth_place: formData.birthPlace || '',
      gender: formData.gender || '',
      nationality: formData.nationality || '',
      phone: formData.personalPhone || '',
      merchant_phone: formData.merchantPhone || '',
      email: formData.email || '',
      address: formData.address || '',
      
      // Informations d'identité
      id_type: formData.idType || '',
      id_number: formData.idNumber || '',
      id_expiry_date: formData.idExpiryDate || '',
      has_anid_card: formData.hasAnidCard ? 1 : 0,
      anid_number: formData.anidNumber || null,
      is_foreigner: formData.isForeigner ? 1 : 0,
      
      // Informations business
      business_name: formData.businessName || '',
      business_type: formData.businessType || '',
      business_phone: formData.businessPhone || null,
      business_email: formData.businessEmail || null,
      business_address: formData.businessAddress || '',
      usage_type: formData.usageType || '',
      has_cfe: formData.hasCfe ? 1 : 0,
      cfe_number: formData.cfeNumber || null,
      has_nif: formData.hasNif ? 1 : 0,
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
      console.log('Sending JSON update data:', jsonData)
      
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
    console.log('Updating application:', applicationId, formData)
    console.log('formData type:', typeof formData)
    console.log('formData keys:', Object.keys(formData))
    console.log('formData.firstName:', formData.firstName)
    console.log('formData.lastName:', formData.lastName)
    
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
    submitData.append('merchant_phone', (formData.merchantPhone && formData.merchantPhone.trim()) || '')
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
    submitData.append('business_address', (formData.businessAddress && formData.businessAddress.trim()) || '')
    submitData.append('usage_type', formData.usageType || '')
    submitData.append('has_cfe', formData.hasCfe ? '1' : '0')
    if (formData.cfeNumber && formData.cfeNumber.trim()) {
      submitData.append('cfe_number', formData.cfeNumber.trim())
    }
    submitData.append('has_nif', formData.hasNif ? '1' : '0')
    if (formData.nifNumber && formData.nifNumber.trim()) {
      submitData.append('nif_number', formData.nifNumber.trim())
    }

    // Localisation (optional fields)
    if (formData.latitude) {
      submitData.append('latitude', formData.latitude)
    }
    if (formData.longitude) {
      submitData.append('longitude', formData.longitude)
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
      // Helper function to extract file from object or return direct file
      const getFile = (fileObj) => {
        if (!fileObj) return null
        if (fileObj instanceof File) return fileObj
        if (fileObj.file instanceof File) return fileObj.file
        return null
      }
      
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
      // Log des données envoyées pour débogage
      console.log('Données envoyées pour la mise à jour:', {
        applicationId,
        formDataEntries: Array.from(submitData.entries()),
        timestamp: new Date().toISOString()
      });

      // Laravel doesn't parse FormData well for PUT requests, so we'll add _method for method spoofing
      submitData.append('_method', 'PUT')
      
      console.log('DEBUG: Making POST request with method spoofing to:', `/merchant-applications/${applicationId}/full`)
      console.log('DEBUG: FormData entries:', Array.from(submitData.entries()))

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
}

export default new MerchantService()