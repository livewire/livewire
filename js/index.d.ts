
export interface Livewire {
    // Register a custom Livewire directive
    directive: (
        name: string,
        callback: (ctx: {
            el: HTMLElement
            directive: Directive
            component: Component
            $wire: any
            cleanup: (cb: () => void) => void
        }) => void
    ) => void

    // Dispatch an event to components by name
    dispatchTo: (componentName: string, name: string, params?: any) => void

    // Interception hooks from request layer
    interceptAction: (
        callback: (ctx: {
            action: any
            onSend: (cb: (ctx: { call: any }) => void) => void
            onCancel: (cb: () => void) => void
            onSuccess: (cb: (ctx: { result: any }) => void) => void
            onError: (cb: (ctx: { response: Response; body: string; preventDefault: () => void }) => void) => void
            onFailure: (cb: (ctx: { error: any }) => void) => void
            onFinish: (cb: () => void) => void
        }) => void
    ) => () => void

    interceptMessage: (
        callback: (ctx: {
            message: any
            cancel: () => void
            onSend: (cb: (ctx: { payload: any }) => void) => void
            onCancel: (cb: () => void) => void
            onFailure: (cb: (ctx: { error: any }) => void) => void
            onError: (cb: (ctx: { response: Response; body: string; preventDefault: () => void }) => void) => void
            onStream: (cb: (ctx: { json: any }) => void) => void
            onSuccess: (cb: (ctx: {
                payload: any
                onSync: (cb: () => void) => void
                onEffect: (cb: () => void) => void
                onMorph: (cb: () => Promise<void> | void) => void
                onRender: (cb: () => void) => void
            }) => void) => void
            onSkipped: (cb: () => void) => void
            onFinish: (cb: () => void) => void
        }) => void
    ) => () => void

    interceptRequest: (
        callback: (ctx: {
            request: any
            onSend: (cb: (ctx: { responsePromise: Promise<Response> }) => void) => void
            onCancel: (cb: () => void) => void
            onFailure: (cb: (ctx: { error: any }) => void) => void
            onResponse: (cb: (ctx: { response: Response }) => void) => void
            onParsed: (cb: (ctx: { response: Response; body: string }) => void) => void
            onError: (cb: (ctx: { response: Response; body: string; preventDefault: () => void }) => void) => void
            onStream: (cb: (ctx: { response: Response }) => void) => void
            onRedirect: (cb: (ctx: { url: string; preventDefault: () => void }) => void) => void
            onDump: (cb: (ctx: { html: string; preventDefault: () => void }) => void) => void
            onSuccess: (cb: (ctx: { response: Response; body: string; json: any }) => void) => void
            onFinish: (cb: () => void) => void
        }) => void
    ) => () => void

    // Fire a server action on a component instance
    fireAction: (
        component: Component,
        method: string,
        params?: any[],
        metadata?: Record<string, any>
    ) => Promise<any>

    // Lifecycle
    start: () => void

    // Store helpers
    first: () => Component | undefined
    find: (id: string) => Component | undefined
    getByName: (name: string) => Component[]
    all: () => Component[]

    // Hooks (internal event bus)
    hook: <T extends keyof LivewireHooks>(name: T, callback: LivewireHooks[T]) => () => void
    trigger: <T extends keyof LivewireHooks>(name: T, ...params: any[]) => (result: any) => any
    triggerAsync: <T extends keyof LivewireHooks>(name: T, ...params: any[]) => Promise<(result: any) => any>

    // Global events utils
    dispatch: (name: string, params?: any) => void
    on: (eventName: string, callback: (detail: any) => void) => () => void

    // Alpine navigate plugin proxy (type left broad intentionally)
    readonly navigate: any
}

export interface Component {
    el: HTMLElement
    id: string
    name: string
    effects: any
    canonical: any
    ephemeral: any
    snapshot: any
    snapshotEncoded: string
    $wire: any
    cleanup: (cb: () => void) => void
    addCleanup: (cb: () => void) => void

    get children(): Component[]

    get parent(): Component | undefined
}

export interface Directive {
    rawName: string
    raw: string
    el: HTMLElement
    eventContext: any
    wire: any
    value: string
    modifiers: string[]
    expression: string | null

    readonly method: string
    readonly methods: Array<{ method: string; params: any[] }>
    readonly params: any[]
}

export interface LivewireHooks {
    'component.init': (ctx: { component: Component; cleanup: (cb: () => void) => void }) => void
    'element.init': (ctx: { el: HTMLElement; component: Component }) => void
    'directive.init': (ctx: {
        el: HTMLElement;
        component: Component;
        directive: Directive;
        cleanup: (cb: (cb: () => void) => void) => void
    }) => void
    'directive.global.init': (ctx: {
        el: HTMLElement;
        directive: Directive;
        cleanup: (cb: (cb: () => void) => void) => void
    }) => void
    'effects': (component: Component, effects: any) => void
    'effect': (ctx: { component: Component; effects: any; cleanup: (cb: () => void) => void; request?: any }) => void
    'morph': (ctx: { el: HTMLElement; toEl: HTMLElement; component: Component }) => void
    'morphed': (ctx: { el: HTMLElement; component: Component }) => void
    'island.morph': (ctx: { startNode: Node; endNode: Node; component: Component }) => void
    'island.morphed': (ctx: { startNode: Node; endNode: Node; component: Component }) => void
    'morph.updating': (ctx: {
        el: HTMLElement;
        toEl: HTMLElement;
        component: Component;
        skip: () => void;
        childrenOnly: () => void;
        skipChildren: () => void;
        skipUntil: (el: HTMLElement) => void
    }) => void
    'morph.updated': (ctx: { el: HTMLElement; component: Component }) => void
    'morph.removing': (ctx: { el: HTMLElement; component: Component; skip: () => void }) => void
    'morph.removed': (ctx: { el: HTMLElement; component: Component }) => void
    'morph.adding': (ctx: { el: HTMLElement; component: Component }) => void
    'morph.added': (ctx: { el: HTMLElement }) => void
    'stream': (json: any) => void
    'navigate.request': (ctx: { uri: string; options: RequestInit }) => void
    /**
     * @deprecated use Livewire.interceptRequest instead
     */
    'request': (ctx: {
        url: string;
        options: any;
        payload: any;
        respond: (cb: (ctx: { status: number; response: Response }) => void) => void;
        succeed: (cb: (ctx: { status: number; json: any }) => void) => void;
        fail: (cb: (ctx: { status: number; content: any; preventDefault: () => void }) => void) => void
    }) => void
    /**
     * @deprecated use Livewire.interceptMessage instead
     */
    'commit': (ctx: {
        component: Component;
        commit: any;
        respond: (cb: () => void) => void;
        succeed: (cb: (ctx: { snapshot: any; effects: any }) => void) => void;
        fail: (cb: () => void) => void
    }) => void
    'payload.intercept': (responseJson: any) => void
}

export const Livewire: Livewire
export { Alpine } from 'alpinejs'

declare global {
    const Livewire: Livewire
    const Alpine: import('alpinejs').Alpine
}

