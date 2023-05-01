import { createApp } from 'vue'
import BladeDevtools from './BladeDevtools.vue'

export { parseComponentTree } from './lib/blade'

export function mountDevtools() {
  const target = document.createElement('div')
  document.body.appendChild(target)

  const app = createApp(BladeDevtools)
  app.mount(target)
  console.log('Mounted devtools', target)
}