declare module 'primevue/config' {
  import { Plugin } from 'vue'
  const PrimeVue: Plugin
  export default PrimeVue
}

declare module 'primevue/toastservice' {
  import { Plugin } from 'vue'
  const ToastService: Plugin
  export default ToastService
}

declare module 'primevue/toast' {
  import { Component } from 'vue'
  const Toast: Component
  export default Toast
}

declare module 'primevue/usetoast' {
  import { Ref } from 'vue'
  export interface ToastMessage {
    severity?: string
    summary?: string
    detail?: string
    life?: number
  }
  export interface ToastMethods {
    add: (message: ToastMessage) => void
    remove: (message: ToastMessage) => void
    removeGroup: (group: string) => void
    removeAllGroups: () => void
  }
  export function useToast(): ToastMethods
}

declare module 'primevue/button' {
  import { Component } from 'vue'
  const Button: Component
  export default Button
}

declare module 'primevue/dialog' {
  import { Component } from 'vue'
  const Dialog: Component
  export default Dialog
}

declare module 'primevue/dropdown' {
  import { Component } from 'vue'
  const Dropdown: Component
  export default Dropdown
}

declare module 'primevue/datatable' {
  import { Component } from 'vue'
  const DataTable: Component
  export default DataTable
}

declare module 'primevue/column' {
  import { Component } from 'vue'
  const Column: Component
  export default Column
}

declare module 'primevue/badge' {
  import { Component } from 'vue'
  const Badge: Component
  export default Badge
}

declare module 'primevue/chart' {
  import { Component } from 'vue'
  const Chart: Component
  export default Chart
}

declare module 'primevue/textarea' {
  import { Component } from 'vue'
  const Textarea: Component
  export default Textarea
}

// Generic declaration for other PrimeVue components
declare module 'primevue/*' {
  import { Component } from 'vue'
  const ComponentImpl: Component
  export default ComponentImpl
}
