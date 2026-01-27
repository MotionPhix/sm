import { ref } from 'vue'

interface Toast {
    id: string
    message: string
    type: 'success' | 'error' | 'info' | 'warning'
}

const toasts = ref<Toast[]>([])

export function useToast() {
    const show = (message: string, type: 'success' | 'error' | 'info' | 'warning' = 'info') => {
        const id = Math.random().toString(36).substring(7)
        const toast: Toast = { id, message, type }
        
        toasts.value.push(toast)
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            remove(id)
        }, 5000)
        
        return id
    }

    const remove = (id: string) => {
        toasts.value = toasts.value.filter(t => t.id !== id)
    }

    const error = (message: string) => show(message, 'error')
    const success = (message: string) => show(message, 'success')
    const info = (message: string) => show(message, 'info')
    const warning = (message: string) => show(message, 'warning')

    return {
        toasts,
        show,
        remove,
        error,
        success,
        info,
        warning,
    }
}
