<!--
    SHARE MODAL — lets the visitor pick where to share a product.

    Built on the native <dialog>: `showModal()` already brings the veil,
    closes on Escape and keeps the focus inside, with no manual trap.

    The share is counted when a network is picked, not when the modal
    opens: opening and closing it without choosing is not sharing.
-->

<script setup lang="ts">
const props = defineProps<{
    name: string
    url: string
    storeSlug: string
    productSlug: string
}>()

const dialog = ref<HTMLDialogElement | null>(null)
const copied = ref(false)

let copiedTimer: ReturnType<typeof setTimeout> | undefined

const whatsappShareUrl = computed(() => `https://wa.me/?text=${encodeURIComponent(`${props.name} — ${props.url}`)}`)

const facebookShareUrl = computed(() => `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(props.url)}`)

function open(): void {
    copied.value = false
    dialog.value?.showModal()
}

function close(): void {
    dialog.value?.close()
}

/* A click on the dialog itself (not on its content) is a click on the veil. */
function onDialogClick(event: MouseEvent): void {
    if (event.target === dialog.value) {
        close()
    }
}

function openNetwork(url: string): void {
    trackShare(props.storeSlug, props.productSlug)
    window.open(url, '_blank', 'noopener')
    close()
}

/* Instagram has no web share link. On a phone the system share sheet
   offers it; on a desktop the link is copied to paste it by hand. */
async function shareInstagram(): Promise<void> {
    trackShare(props.storeSlug, props.productSlug)

    const isTouch = window.matchMedia('(pointer: coarse)').matches

    if (isTouch && typeof navigator.share === 'function') {
        close()

        try {
            await navigator.share({ title: props.name, url: props.url })
        } catch {
            /* The visitor closed the share sheet: nothing to do. */
        }

        return
    }

    copyLink()
}

/* The Clipboard API only exists on secure origins; the fallback keeps
   the copy working over plain HTTP (development). */
async function copyLink(): Promise<void> {
    try {
        await navigator.clipboard.writeText(props.url)
    } catch {
        const field = document.createElement('textarea')

        field.value = props.url
        field.setAttribute('readonly', '')
        field.style.position = 'fixed'
        field.style.opacity = '0'
        document.body.appendChild(field)
        field.select()
        document.execCommand('copy')
        field.remove()
    }

    copied.value = true
    clearTimeout(copiedTimer)
    copiedTimer = setTimeout(() => {
        copied.value = false
    }, 2000)
}

onBeforeUnmount(() => clearTimeout(copiedTimer))

defineExpose({ open })
</script>

<template>
    <dialog
        ref="dialog"
        class="share-modal"
        aria-labelledby="share-title"
        @click="onDialogClick"
    >
        <div class="share-modal-inner">
            <header class="share-modal-header">
                <div class="share-modal-title">
                    <h2 id="share-title">Compartir</h2>
                </div>

                <button class="share-modal-close" type="button" @click="close">
                    <AppIcon name="close" class="share-modal-close-icon" />
                    <span class="visually-hidden">Cerrar</span>
                </button>
            </header>

            <div class="share-modal-options">
                <button class="btn btn-border share-option" type="button" @click="openNetwork(whatsappShareUrl)">
                    <AppIcon name="whatsapp" class="btn-icon" />
                    WhatsApp
                </button>

                <button class="btn btn-border share-option" type="button" @click="openNetwork(facebookShareUrl)">
                    <AppIcon name="facebook" class="btn-icon" />
                    Facebook
                </button>

                <button class="btn btn-border share-option" type="button" @click="shareInstagram">
                    <AppIcon :name="copied ? 'check' : 'instagram'" class="btn-icon" />
                    <span aria-live="polite">{{ copied ? 'Enlace copiado' : 'Instagram' }}</span>
                </button>
            </div>
        </div>
    </dialog>
</template>
