interface Overlay {
    position: {
        x: number
        y: number
    }
    dimensions: {
        height: number
        width: number
    }
    borderRadius?: {
        topLeft: string
        topRight: string
        bottomLeft: string
        bottomRight: string
    }
}

type OverlayElement = HTMLDivElement

/**
 * Highlights the DOM nodes rendered by a Blade component.
 */
export class BladeComponentHighlighter {
    #current: null | { overlay: Overlay, element: OverlayElement } = null

    private get root(): HTMLElement {
        return document.getElementById('blade-devtools')!
    }

    /**
     * Highlights the given nodes.
     *
     * It does this, by appending a new element to the dom, which is positioned
     * over those nodes and spans their bounding box.
     */
    public highlight(nodes: Node[]|undefined): void {
        this.clear()
        if (!nodes) {
            return
        }

        const overlay = computeOverlay(nodes);
        if (!overlay) {
            return
        }

        this.setOverlay(overlay)
    }

    public setOverlay(overlay: Overlay) {
        const element = createOverlayElement(overlay);
        this.root.appendChild(element)
        this.#current = { overlay, element }
    }

    /**
     * Removes the current highlight if one exists.
     */
    public clear(): void {
        if (!this.#current) return

        this.root.removeChild(this.#current.element)
        this.#current = null
    }
}

interface Bounds {
    top: number
    right: number
    bottom: number
    left: number
}

/** @internal */
export function computeOverlay(nodes: Node[]): Overlay|null {
    const bounds = computeBounds(nodes)
    const overlay = toOverlay(bounds)

    // Looks way better for single elements with a border radius, since the
    // overlay does not generate "sharp" edges
    const elements = nodes.filter((node): node is HTMLElement => node instanceof HTMLElement)
    if (overlay && elements.length === 1) {
        const style = getComputedStyle(elements[0])
        overlay.borderRadius = {
            topLeft: style.borderTopLeftRadius,
            topRight: style.borderTopRightRadius,
            bottomLeft: style.borderBottomLeftRadius,
            bottomRight: style.borderBottomRightRadius,
        }
    }

    return overlay
}

/** @internal */
export function computeBounds(nodes: Node[]): Bounds {
    const bounds: Bounds = {
        top: document.body.clientHeight,
        right: 0,
        bottom: 0,
        left: document.body.clientWidth,
    }

    for (let element of nodes) {
        if (!(element instanceof HTMLElement)) continue

        const rect = element.getBoundingClientRect()

        bounds.top = Math.min(bounds.top, rect.top)
        bounds.right = Math.max(bounds.right, rect.right)
        bounds.bottom = Math.max(bounds.bottom, rect.bottom)
        bounds.left = Math.min(bounds.left, rect.left)
    }

    return bounds
}

/** @internal */
export function toOverlay(bounds: Bounds): Overlay|null {
    const height = bounds.bottom - bounds.top
    const width = bounds.right - bounds.left

    const area = height * width
    if (area <= 0) return null

    return {
        position: {
            x: bounds.left,
            y: bounds.top
        },
        dimensions: {height, width},
    }
}

function createOverlayElement(overlay: Overlay): OverlayElement {
    const element = document.createElement('div')

    style(element, {
        zIndex: '999999998',
        position: 'fixed',
        width: `${overlay.dimensions.width}px`,
        height: `${overlay.dimensions.height}px`,
        left: `${overlay.position.x}px`,
        top: `${overlay.position.y}px`,
        background: 'var(--red-500)',
        opacity: '0.3',

        fontFamily: 'monospace',
        pointerEvents: 'none',
    })

    if (overlay.borderRadius) {
        style(element, {
            borderTopLeftRadius: overlay.borderRadius.topLeft,
            borderTopRightRadius: overlay.borderRadius.topRight,
            borderBottomLeftRadius: overlay.borderRadius.bottomLeft,
            borderBottomRightRadius: overlay.borderRadius.bottomRight,
        })
    }

    return element
}

function style(element: HTMLDivElement, styles: Partial<HTMLElement['style']>): void {
    for (let [name, value] of Object.entries(styles)) {
        element.style[name] = value
    }
}