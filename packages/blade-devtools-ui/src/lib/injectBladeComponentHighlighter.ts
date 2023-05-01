import {BladeComponentHighlighter} from "@/lib/highlight-dom-element";
import {inject, type InjectionKey, provide} from "vue";

const INJECTION_KEY = Symbol() as InjectionKey<BladeComponentHighlighter>

export function injectBladeComponentHighlighter(): BladeComponentHighlighter {
    return inject(INJECTION_KEY)!
}

export function provideBladeComponentHighlighter(): void {
    provide(INJECTION_KEY, new BladeComponentHighlighter())
}