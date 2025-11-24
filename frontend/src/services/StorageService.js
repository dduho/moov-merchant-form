class StorageService {
  constructor() {
    this.dbName = 'MoovMerchantApp'
    this.dbVersion = 1
    this.db = null
    this.init()
  }
  
  async init() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open(this.dbName, this.dbVersion)
      
      request.onerror = () => reject(request.error)
      request.onsuccess = () => {
        this.db = request.result
        resolve(this.db)
      }
      
      request.onupgradeneeded = (event) => {
        const db = event.target.result
        
        // Store pour les données de formulaire
        if (!db.objectStoreNames.contains('formData')) {
          db.createObjectStore('formData', { keyPath: 'id' })
        }
        
        // Store pour les soumissions en attente
        if (!db.objectStoreNames.contains('pendingSubmissions')) {
          const store = db.createObjectStore('pendingSubmissions', { keyPath: 'id', autoIncrement: true })
          store.createIndex('timestamp', 'timestamp', { unique: false })
        }
      }
    })
  }
  
  async saveForm(formData) {
    await this.init()
    return new Promise((resolve, reject) => {
      try {
        // Clone formData and prepare it for storage
        const storageData = JSON.parse(JSON.stringify({
          id: 'current',
          data: this.prepareDataForStorage(formData),
          timestamp: new Date().getTime()
        }))
        
        const transaction = this.db.transaction(['formData'], 'readwrite')
        const store = transaction.objectStore('formData')
        const request = store.put(storageData)
        
        request.onsuccess = () => resolve()
        request.onerror = () => reject(request.error)
      } catch (error) {
        reject(error)
      }
    })
  }

  prepareDataForStorage(data) {
    // Deep clone the data while handling special objects
    const preparedData = { ...data }
    
    // Handle documents if they exist
    if (preparedData.documents) {
      preparedData.documents = Object.entries(preparedData.documents).reduce((acc, [key, file]) => {
        // If the file is a File/Blob object, just store metadata
        if (file instanceof File || file instanceof Blob) {
          acc[key] = {
            name: file.name,
            type: file.type,
            size: file.size,
            lastModified: file.lastModified
          }
        } else if (typeof file === 'object') {
          // If it's already metadata, keep it as is
          acc[key] = file
        }
        return acc
      }, {})
    }
    
    return preparedData
  }
  
  async loadForm() {
    await this.init()
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction(['formData'], 'readonly')
      const store = transaction.objectStore('formData')
      const request = store.get('current')
      
      request.onsuccess = () => {
        const result = request.result
        resolve(result ? result.data : null)
      }
      request.onerror = () => reject(request.error)
    })
  }
  
  async clearForm() {
    await this.init()
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction(['formData'], 'readwrite')
      const store = transaction.objectStore('formData')
      const request = store.delete('current')
      
      request.onsuccess = () => resolve()
      request.onerror = () => reject(request.error)
    })
  }
  
  async savePendingSubmission(formData) {
    await this.init()
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction(['pendingSubmissions'], 'readwrite')
      const store = transaction.objectStore('pendingSubmissions')
      const request = store.add({
        data: formData,
        timestamp: new Date().getTime(),
        status: 'pending'
      })
      
      request.onsuccess = () => resolve(request.result)
      request.onerror = () => reject(request.error)
    })
  }
  
  async getPendingSubmissions() {
    await this.init()
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction(['pendingSubmissions'], 'readonly')
      const store = transaction.objectStore('pendingSubmissions')
      const request = store.getAll()
      
      request.onsuccess = () => resolve(request.result || [])
      request.onerror = () => reject(request.error)
    })
  }
  
  async removePendingSubmission(id) {
    await this.init()
    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction(['pendingSubmissions'], 'readwrite')
      const store = transaction.objectStore('pendingSubmissions')
      const request = store.delete(id)
      
      request.onsuccess = () => resolve()
      request.onerror = () => reject(request.error)
    })
  }
}

export default new StorageService()