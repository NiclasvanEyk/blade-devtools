interface BladeNode {
  type: 'START' | 'END'
  id: string
  label: string
  data: unknown
  element: Node | null
  startNode: Node
  endNode: Node
  parent: Node | null
}

interface ComponentStartTag {
  type: 'START'
  id: string
  node: Node
  parent: Node
}

interface ComponentEndTag {
  type: 'END'
  id: string
  node: Node
}

type ComponentTag = ComponentStartTag | ComponentEndTag

function* iterateComponentsTags(rootElem: Node): Generator<ComponentTag> {
  const iterator = document.createNodeIterator(
    rootElem,
    NodeFilter.SHOW_COMMENT,
    () => NodeFilter.FILTER_ACCEPT
  )

  let currentNode: Node | null
  while ((currentNode = iterator.nextNode())) {
    const content = currentNode.nodeValue?.trim() ?? ''
    if (!content.startsWith('BLADE_COMPONENT_')) {
      continue
    }

    const { type, data } = parseNode(content)

    if (type === 'START') {
      yield { type, id: data, node: currentNode, parent: currentNode.parentNode! }
      continue
    }

    if (type === 'END') {
      yield { type, id: data, node: currentNode }
    }
  }

  return
}

function parseNode(content: string) {
  const withoutPrefix = content.replace('BLADE_COMPONENT_', '')
  const dataStart = withoutPrefix.indexOf('[') + 1
  const dataEnd = withoutPrefix.lastIndexOf(']')
  const data = withoutPrefix.slice(dataStart, dataEnd)
  const type = withoutPrefix.substring(0, dataStart - 1)

  return { type, data }
}

export type BladeComponentAttributes = { [key: string]: unknown }

export interface BladeComponentTreeNode {
  /**
   * A unique identifier assigned during rendering.
   */
  id: string

  /**
   * A label representing the component tag.
   */
  label: string

  /**
   * All DOM nodes rendered by the component.
   */
  nodes: Node[]

  /**
   * The parent component.
   */
  parent: BladeComponentTreeNode | null

  /**
   * All child components rendered by this one.
   */
  children: BladeComponentTreeNode[]

  /**
   * Whether the component was rendered through `x-dynamic-component`.
   *
   * @deprecated
   */
  dynamic: boolean
}

export type ClassComponentAttribute = {
  type: 'class',
  className: string
  properties: {
    [name: string]: {
      name: string;
      value: ComponentAttribute
    }
  }
 }

export type ArrayComponentAttribute =
  | { type: 'array', list: boolean, value: ComponentAttributes }

export type ComponentAttribute =
  | { type: 'string', value: string }
  | { type: 'int', value: number }
  | { type: 'float', value: number }
  | { type: 'bool', value: boolean }
  | { type: 'null', value: null }
  | ArrayComponentAttribute
  | ClassComponentAttribute

export type ComponentAttributes = {
  [k: string]: ComponentAttribute
}

export function parseComponentTree(
  rootElem: Node | null = null
): BladeComponentTreeNode {
  rootElem ??= document.documentElement

  // @ts-ignore
  const tree: BladeComponentTreeNode = {
    parent: null,
    children: []
  }
  let current = tree

  for (const tag of iterateComponentsTags(rootElem)) {
    if (tag.type === 'START') {
      const component: BladeComponentTreeNode = {
        label: window.__BDT_CONTEXT[tag.id].tag,
        nodes: [tag.node],
        id: tag.id,
        children: [],
        parent: current,
        dynamic: false
      }

        current.children.push(component)
        current = component
    }

    if (tag.type === 'END') {
      let sibling: Node|null = current.nodes[0]

      do {
        sibling = sibling.nextSibling
        if (!sibling) break

        current.nodes.push(sibling)
      } while (sibling !== tag.node)

      // @ts-ignore
      current = current.parent
    }
  }

  return tree
}