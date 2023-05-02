import {inject, type InjectionKey, provide, type Ref} from "vue";

export interface Overlay {
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

interface ComponentHighlighting {
    /**
     * Highlights the given nodes.
     *
     * It does this, by appending a new element to the dom, which is positioned
     * over those nodes and spans their bounding box.
     */
    highlight(nodes: Node[] | undefined): void;

    /**
     * Removes the current highlight if one exists.
     */
    clear(): void;
}

const INJECTION_KEY = Symbol('blade-devtools-component-highlighting') as InjectionKey<ComponentHighlighting>

export function provideComponentHighlighting(ref: Ref<Overlay|null>): void {
    provide(INJECTION_KEY, {
        highlight(nodes: Node[] | undefined): void {
            this.clear()
            if (!nodes) {
                return
            }

            const overlay = computeOverlay(nodes);
            if (!overlay) {
                return
            }

            ref.value = overlay
        },

        clear(): void {
            ref.value = null
        },
    })
}

export function injectComponentHighlighting(): ComponentHighlighting {
    return inject(INJECTION_KEY, {
        highlight() {},
        clear() {},
    })
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