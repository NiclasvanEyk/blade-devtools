interface BladeNode {
  type: 'START' | 'END' | 'DATA'
  id: string
  label: string
  data: unknown
  element: Node | null
  parent: Node | null
}

function* iterateComponentsTags(rootElem: Node) {
  const iterator = document.createNodeIterator(
    rootElem,
    NodeFilter.SHOW_COMMENT,
    () => NodeFilter.FILTER_ACCEPT
  )

  let currentNode: Node | null
  let startedNode: Partial<BladeNode> | null = null
  while ((currentNode = iterator.nextNode())) {
    const content = currentNode.nodeValue?.trim() ?? ''
    if (!content.startsWith('BLADE_COMPONENT_')) {
      continue
    }

    const { type, data } = parseNode(content)

    if (type === 'START') {
      startedNode = { type, id: data, parent: currentNode.parentNode }
      continue
    }

    if (type === 'DATA') {
      if (!startedNode) {
        throw new Error('No current node!')
      }
      startedNode.data = JSON.parse(data)
      startedNode.element = nextHtmlTagSibling(currentNode)

      yield startedNode

      continue
    }

    if (type === 'END') {
      startedNode = null

      yield { type, id: data }
    }
  }

  return
}

function nextHtmlTagSibling(node: Node): Node | null {
  let next = node.nextSibling

  while (next && ['#comment', '#text'].includes(next.nodeName)) {
    next = next.nextSibling
  }

  return next
}

function parseNode(content: string) {
  const withoutPrefix = content.replace('BLADE_COMPONENT_', '')
  const dataStart = withoutPrefix.indexOf('[') + 1
  const dataEnd = withoutPrefix.lastIndexOf(']')
  const data = withoutPrefix.slice(dataStart, dataEnd)
  const type = withoutPrefix.substring(0, dataStart - 1)

  return { type, data }
}

/**
 * @param {string} name
 */
function displayLabel(name: string, attributes: BladeComponentAttributes): string {
  const namespace = name.includes('::') ? name.split('::')[0] : null
  if (namespace === '__components') {
    const dynamicView = attributes.componentName
    const name = attributes.component
    if (typeof dynamicView === 'string') {
      if (dynamicView === 'dynamic-component' && typeof name === 'string') {
        return displayLabel(attributes.componentName, attributes)
      } else {
        return displayLabel(dynamicView, attributes)
      }
    }
  }

  let view = name.includes('::') ? name.split('::')[1] : name

  if (view.startsWith('components.')) {
    view = view.substring('components.'.length)
  }

  if (namespace) {
    return `x-${namespace}::${view}`
  }

  return `x-${view}`
}

function cleanData(data) {
  const cleaned = structuredClone(data)

  delete cleaned['__laravel_slots']
  delete cleaned['slot']

  if (Object.keys(cleaned['attributes']).length === 0) {
    delete cleaned['attributes']
  }

  return cleaned
}

export type BladeComponentAttributes = { [key: string]: unknown }

export interface BladeComponentTreeNode {
  /**
   * A unique identifier assigned during rendering.
   */
  id: string

  /**
   * Of all
   */
  globalIndex: number

  /**
   * A label representing the component tag.
   */
  label: string

  /**
   * Data passed and available to the component.
   */
  data: BladeComponentAttributes

  /**
   * The closest DOM node rendered by the component.
   */
  element: Node

  /**
   * The parent component.
   */
  parent: BladeComponentTreeNode | null

  /**
   * All child components rendered by this one.
   */
  children: BladeComponentTreeNode[]
}

export function getAllComments(rootElem: Node): [BladeComponentTreeNode, BladeComponentTreeNode[]] {
  rootElem ??= document.documentElement

  const tree: BladeComponentTreeNode = {
    element: rootElem,
    parent: null,
    data: {},
    children: []
  }
  let current = tree
  const list = [tree]

  for (const componentTag of iterateComponentsTags(rootElem)) {
    if (componentTag.type === 'START') {
      const component = {
        label: displayLabel(componentTag.data.name, componentTag.data.data),
        element: componentTag.element,
        id: componentTag.id,
        data: cleanData(componentTag.data.data),
        children: [],
        parent: current
      }
      current.children.push(component)
      current = component
      list.push(component)
    }

    if (componentTag.type === 'END') {
      current = current.parent
    }
  }

  return [tree, list]
}
