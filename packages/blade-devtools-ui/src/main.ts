import { createApp } from 'vue'
import App from './App.vue'

import './assets/main.css'
export { getAllComments } from './lib/blade'

export function mountDevtools() {
  const target = document.createElement('div')
  target.id = 'blade-devtools'
  document.body.appendChild(target)

  console.log('mounting blade devtools to', target)

  const app = createApp(App)
  app.mount(target)
}
