import { registerSW } from 'virtual:pwa-register'

const updateSW = registerSW({
  onNeedRefresh() {
    console.log('[PWA] New content available, please reload.')
  },
  onOfflineReady() {
    console.log('[PWA] App ready to work offline')
  },
  immediate: true
})

export default updateSW
