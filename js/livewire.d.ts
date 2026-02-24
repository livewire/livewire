export type SyncCodec<TClient = unknown, TServer = unknown> = {
    toServer?: (value: TClient) => TServer
    fromServer?: (value: TServer) => TClient
}

export interface LivewireGlobal {
    sync<TClient = unknown, TServer = unknown>(
        strategy: string,
        codec: SyncCodec<TClient, TServer>
    ): () => void
    registerSyncCodec<TClient = unknown, TServer = unknown>(
        strategy: string,
        codec: SyncCodec<TClient, TServer>
    ): () => void
    removeSyncCodec(strategy: string): void
    [key: string]: any
}

export const Livewire: LivewireGlobal
export const Alpine: any

declare global {
    interface Window {
        Livewire: LivewireGlobal
    }
}
