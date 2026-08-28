<!--
    Sprite de íconos. Va una sola vez al principio del <body>: los
    componentes lo usan con <use href="#i-x">. SVG inline, nunca emojis.
-->

<template>
    <svg class="sprite" aria-hidden="true" hidden>
        <symbol id="i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="13" r="8"></circle>
            <path d="M12 9.5V13l2.5 1.5"></path>
            <path d="M5 4.5 7.5 2.5M19 4.5 16.5 2.5"></path>
        </symbol>
        <symbol id="i-phone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15.5 21a13 13 0 0 1-12.5-12.5 2 2 0 0 1 2-2.5h2.5a1 1 0 0 1 1 .8l.7 3a1 1 0 0 1-.5 1.1l-1.4.8a11 11 0 0 0 5 5l.8-1.4a1 1 0 0 1 1.1-.5l3 .7a1 1 0 0 1 .8 1V19a2 2 0 0 1-2.5 2Z"></path>
        </symbol>
        <symbol id="i-location" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 21s7-5.6 7-11a7 7 0 1 0-14 0c0 5.4 7 11 7 11Z"></path>
            <circle cx="12" cy="10" r="2.6"></circle>
        </symbol>
        <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="7"></circle>
            <path d="m16.5 16.5 4 4"></path>
        </symbol>
        <symbol id="i-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <path d="M4 7h16M4 12h16M4 17h16"></path>
        </symbol>
        <symbol id="i-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <path d="m6 6 12 12M18 6 6 18"></path>
        </symbol>
        <symbol id="i-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m6 9 6 6 6-6"></path>
        </symbol>
        <symbol id="i-logo" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="16" cy="16" r="14"></circle>
            <path d="M11 22V10h6a4 4 0 0 1 0 8h-6"></path>
        </symbol>
        <symbol id="i-whatsapp" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.96L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.15h-.01a8.2 8.2 0 0 1-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.19 8.19 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.25-8.24a8.2 8.2 0 0 1 8.24 8.25c0 4.54-3.7 8.23-8.24 8.23Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.13-.16.24-.64.8-.79.97-.14.16-.29.18-.54.06-.25-.13-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.01-.38.11-.5.11-.11.25-.29.37-.44.13-.14.17-.24.25-.41.09-.16.04-.31-.02-.43-.06-.12-.56-1.35-.76-1.84-.2-.49-.4-.42-.56-.43h-.47c-.16 0-.43.06-.65.31-.22.25-.85.83-.85 2.03s.87 2.35.99 2.51c.12.17 1.71 2.61 4.15 3.66.58.25 1.03.4 1.38.51.58.19 1.11.16 1.53.1.47-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.15-1.18-.06-.11-.22-.17-.47-.29Z"></path>
        </symbol>
        <symbol id="i-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
            <path d="m5 12.5 4.5 4.5L19 7"></path>
        </symbol>
        <symbol id="i-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 12h15m-6-6 6 6-6 6"></path>
        </symbol>
        <symbol id="i-new" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 3.5 13.9 9l5.6 1.9-5.6 1.9L12 18.5l-1.9-5.7L4.5 10.9 10.1 9 12 3.5Z"></path>
            <path d="M18.5 15.5l.7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7.7-2Z"></path>
        </symbol>
        <symbol id="i-star" viewBox="0 0 24 24" fill="currentColor">
            <path d="m12 3.6 2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.3-4.1 5.9-.9L12 3.6Z"></path>
        </symbol>
        <symbol id="i-sold-out" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="8.5"></circle>
            <path d="m6.5 6.5 11 11"></path>
        </symbol>
        <symbol id="i-mail" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="5" width="18" height="14" rx="2"></rect>
            <path d="m3.6 6.7 8.4 5.8 8.4-5.8"></path>
        </symbol>
        <symbol id="i-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 21.5c4.4-4.3 6.6-7.7 6.6-10.4a6.6 6.6 0 1 0-13.2 0c0 2.7 2.2 6.1 6.6 10.4Z"></path>
            <circle cx="12" cy="10.6" r="2.5"></circle>
        </symbol>
        <symbol id="i-youtube" viewBox="0 0 24 24" fill="currentColor">
            <path d="M21.6 7.2a2.5 2.5 0 0 0-1.76-1.77C18.28 5 12 5 12 5s-6.28 0-7.84.43A2.5 2.5 0 0 0 2.4 7.2 26 26 0 0 0 2 12c0 1.61.13 3.22.4 4.8a2.5 2.5 0 0 0 1.76 1.77C5.72 19 12 19 12 19s6.28 0 7.84-.43a2.5 2.5 0 0 0 1.76-1.77c.27-1.58.4-3.19.4-4.8 0-1.61-.13-3.22-.4-4.8ZM10.2 15.02V8.98L15.4 12l-5.2 3.02Z"></path>
        </symbol>
        <symbol id="i-facebook" viewBox="0 0 24 24" fill="currentColor">
            <path d="M13.6 21.5v-8.2h2.8l.4-3.2h-3.2V8.05c0-.93.26-1.56 1.59-1.56h1.7V3.61a22.6 22.6 0 0 0-2.48-.13c-2.45 0-4.13 1.5-4.13 4.25v2.37H7.44v3.2h2.84v8.2h3.32Z"></path>
        </symbol>
        <symbol id="i-instagram" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3.2" y="3.2" width="17.6" height="17.6" rx="5"></rect>
            <circle cx="12" cy="12" r="4"></circle>
            <circle cx="16.9" cy="7.1" r="1.2" fill="currentColor" stroke="none"></circle>
        </symbol>
        <symbol id="i-tiktok" viewBox="0 0 24 24" fill="currentColor">
            <path d="M16.2 2h-2.9v13.4a2.5 2.5 0 1 1-2.1-2.47v-2.96a5.45 5.45 0 1 0 5 5.43V9.5a6.8 6.8 0 0 0 3.6 1.05V7.6a3.9 3.9 0 0 1-3.6-3.87V2Z"></path>
        </symbol>
        <symbol id="i-tag" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 3h7.2L21 13.8 13.8 21 3 10.2V3Z"/>
            <circle cx="7.3" cy="7.3" r="1.3"/>
        </symbol>
        <symbol id="i-share" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="18" cy="5" r="2.6"/>
            <circle cx="6" cy="12" r="2.6"/>
            <circle cx="18" cy="19" r="2.6"/>
            <path d="m8.4 13.4 7.2 4.2M15.6 6.4 8.4 10.6"/>
        </symbol>
        <symbol id="i-truck" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2.8 6h11v9.5h-11z"/>
            <path d="M13.8 9.5h3.9l3.5 3.5v2.5h-7.4z"/>
            <circle cx="7" cy="18" r="2"/>
            <circle cx="17.4" cy="18" r="2"/>
        </symbol>
        <symbol id="i-exchange" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3.5 9h14l-3.6-3.6"/>
            <path d="M20.5 15h-14l3.6 3.6"/>
        </symbol>
        <symbol id="i-shield" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="m12 3 7 2.8V12c0 4.2-2.9 7.8-7 9-4.1-1.2-7-4.8-7-9V5.8L12 3Z"/>
            <path d="m9 12 2.2 2.2L15.2 10"/>
        </symbol>
        <symbol id="i-cart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 4h2.2l2.1 10.2a1.5 1.5 0 0 0 1.5 1.2h7.6a1.5 1.5 0 0 0 1.5-1.2L19.5 7H6"/>
            <circle cx="9.5" cy="19" r="1.4"/>
            <circle cx="17" cy="19" r="1.4"/>
        </symbol>
        <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <path d="M12 5v14M5 12h14"/>
        </symbol>
        <symbol id="i-minus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
            <path d="M5 12h14"/>
        </symbol>
        <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 6.5h16M9.5 6.5V4.5h5v2"/>
            <path d="M6.5 6.5 7.4 19a1.5 1.5 0 0 0 1.5 1.4h6.2a1.5 1.5 0 0 0 1.5-1.4l.9-12.5"/>
            <path d="M10.5 10v6.5M13.5 10v6.5"/>
        </symbol>
        <symbol id="i-bell" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 16.5H6l1.2-2V11a4.8 4.8 0 0 1 9.6 0v3.5Z"/>
            <path d="M10.2 19.5a2 2 0 0 0 3.6 0"/>
        </symbol>
    </svg>
</template>
