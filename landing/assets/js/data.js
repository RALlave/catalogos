/* ==========================================================================
   DATA — todo el contenido editable de la landing vive acá.
   La estructura visual no toca estos datos: render.js los transforma en HTML.
   Para cambiar textos, precios o funcionalidades se edita SOLO este archivo.
   ========================================================================== */

const LANDING = {

    /* Métricas del social proof.
       PLACEHOLDER: reemplazar por datos reales cuando existan. */
    stats: [
        { value: "+1.000", label: "catálogos creados" },
        { value: "+10.000", label: "productos publicados" },
        { value: "24/7", label: "catálogo siempre disponible" },
        { value: "100%", label: "adaptado a celulares" },
    ],

    /* Sección "Problema" */
    problemas: [
        {
            icono: "file",
            titulo: "El PDF interminable",
            texto: "Tus clientes tienen que descargar un archivo pesado para ver lo que vendés.",
        },
        {
            icono: "images",
            titulo: "Fotos desordenadas",
            texto: "Tus productos están repartidos entre WhatsApp, Facebook e Instagram.",
        },
        {
            icono: "refresh",
            titulo: "Información desactualizada",
            texto: "Cambiar un precio significa rehacer las imágenes y volver a mandarlas.",
        },
        {
            icono: "store",
            titulo: "Presentación poco profesional",
            texto: "Una vidriera descuidada hace que tu negocio parezca más chico de lo que es.",
        },
    ],

    /* Sección "Solución / Beneficios" */
    beneficios: [
        {
            icono: "grid",
            titulo: "Catálogo digital",
            texto: "Todos tus productos ordenados en un solo lugar, con categorías y buscador.",
        },
        {
            icono: "box",
            titulo: "Tus productos, completos",
            texto: "Imágenes, precios, descripciones, ficha técnica y categorías.",
        },
        {
            icono: "share",
            titulo: "Se comparte con un enlace",
            texto: "Un solo link para tu catálogo y otro para cada producto.",
        },
        {
            icono: "sparkles",
            titulo: "Diseño profesional",
            texto: "Elegís el diseño y los colores de tu marca. Se ve bien sin diseñador.",
        },
        {
            icono: "bolt",
            titulo: "Cambios al instante",
            texto: "Editás un precio y tus clientes lo ven al momento. Sin volver a mandar nada.",
        },
        {
            icono: "phone",
            titulo: "Pensado para el celular",
            texto: "Tus clientes navegan cómodos desde el teléfono, que es donde te escriben.",
        },
    ],

    /* Sección "Para quién es" */
    audiencias: [
        {
            icono: "rocket",
            titulo: "Emprendedores",
            texto: "Para quienes están arrancando y necesitan mostrarse en serio desde el día uno.",
        },
        {
            icono: "store",
            titulo: "Tiendas",
            texto: "Para mostrar todo lo que tenés, ordenado por categorías.",
        },
        {
            icono: "whatsapp",
            titulo: "Vendedores online",
            texto: "Para quienes venden por WhatsApp, Instagram o Facebook.",
        },
        {
            icono: "layers",
            titulo: "Distribuidores",
            texto: "Para presentar muchos productos de forma clara y profesional.",
        },
    ],

    /* Sección "Características".
       estado: "listo" (implementado) o "pronto" (placeholder, todavía no existe). */
    features: [
        { icono: "box", titulo: "Gestión de productos", texto: "Altas, bajas, orden y duplicado.", estado: "listo" },
        { icono: "tag", titulo: "Categorías", texto: "Ordená tu catálogo como quieras.", estado: "listo" },
        { icono: "images", titulo: "Biblioteca de imágenes", texto: "Subí una vez y reutilizá en varios productos.", estado: "listo" },
        { icono: "price", titulo: "Precios y ofertas", texto: "Precio, precio de oferta y moneda de tu país.", estado: "listo" },
        { icono: "grid", titulo: "Catálogo público", texto: "Tu vidriera online, siempre disponible.", estado: "listo" },
        { icono: "link", titulo: "URL personalizada", texto: "tucatalogo.com/tunegocio, con tu nombre.", estado: "listo" },
        { icono: "sparkles", titulo: "Diseños y colores", texto: "Elegís el diseño y la paleta de tu marca.", estado: "listo" },
        { icono: "phone", titulo: "Diseño responsive", texto: "Se ve bien en celular, tablet y computadora.", estado: "listo" },
        { icono: "share", titulo: "Compartir catálogo", texto: "Enlace directo del catálogo y de cada producto.", estado: "listo" },
        { icono: "search", titulo: "Buscador y filtros", texto: "Tus clientes encuentran rápido lo que buscan.", estado: "listo" },
        { icono: "dashboard", titulo: "Panel de control", texto: "Todo tu catálogo administrado desde un solo lugar.", estado: "listo" },
        { icono: "chart", titulo: "Estadísticas", texto: "Visitas y productos más vistos.", estado: "pronto" },
    ],

    /* Sección "Precios".
       PLACEHOLDER: precios y límites tentativos, ajustar antes de publicar.
       destacado: true marca visualmente el plan recomendado. */
    planes: [
        {
            nombre: "Gratis",
            tagline: "Para empezar hoy mismo.",
            precio: "$0",
            periodo: "/ mes",
            destacado: false,
            cta: "Comenzar gratis",
            incluye: [
                "Hasta 20 productos",
                "3 categorías",
                "1 imagen por producto",
                "URL de catálogo",
                "Compartir por WhatsApp",
            ],
            nota: "Sin tarjeta de crédito.",
        },
        {
            nombre: "Pro",
            tagline: "Para vendedores que quieren crecer.",
            precio: "$XX",
            periodo: "/ mes",
            destacado: true,
            cta: "Comenzar ahora",
            incluye: [
                "Hasta 300 productos",
                "Categorías ilimitadas",
                "Hasta 10 imágenes por producto",
                "Diseños y paletas de color",
                "Productos destacados y etiquetas",
                "Sin marca de la plataforma",
            ],
            nota: "Cancelás cuando quieras.",
        },
        {
            nombre: "Negocio",
            tagline: "Para negocios ya establecidos.",
            precio: "$XX",
            periodo: "/ mes",
            destacado: false,
            cta: "Elegir plan",
            incluye: [
                "Productos ilimitados",
                "Todas las funciones de Pro",
                "Mayor capacidad de imágenes",
                "Estadísticas del catálogo",
                "Soporte prioritario",
            ],
            nota: "Facturación mensual.",
        },
    ],

    /* Sección "Testimonios".
       PLACEHOLDER: contenido de ejemplo, no son clientes reales. */
    testimonios: [
        {
            texto: "Antes mandaba las fotos de mis productos una por una por WhatsApp. Ahora paso el enlace de mi catálogo y listo.",
            nombre: "Nombre Apellido",
            negocio: "Indumentaria",
        },
        {
            texto: "Cambiar un precio me llevaba toda la tarde entre las imágenes y las publicaciones. Hoy lo edito una vez.",
            nombre: "Nombre Apellido",
            negocio: "Perfumería",
        },
        {
            texto: "Mis clientes ven todo lo que tengo sin tener que preguntarme por cada producto.",
            nombre: "Nombre Apellido",
            negocio: "Accesorios",
        },
    ],

    /* Sección "Preguntas frecuentes" */
    faq: [
        {
            pregunta: "¿Necesito conocimientos técnicos?",
            respuesta: "No. Creás tu cuenta, cargás tus productos desde un panel simple y tu catálogo queda publicado. No hay que instalar ni programar nada.",
        },
        {
            pregunta: "¿Puedo usarlo desde el celular?",
            respuesta: "Sí. Tanto el panel para cargar productos como el catálogo que ven tus clientes están pensados para el celular.",
        },
        {
            pregunta: "¿Puedo compartir mi catálogo por WhatsApp?",
            respuesta: "Sí. Tu catálogo tiene un enlace propio que podés mandar por WhatsApp, pegar en tu biografía de Instagram o compartir donde quieras. Cada producto también tiene su enlace.",
        },
        {
            pregunta: "¿Puedo cambiar mis productos después de publicarlos?",
            respuesta: "Sí, cuando quieras. Editás precios, fotos o descripciones y el cambio se ve al instante en tu catálogo.",
        },
        {
            pregunta: "¿Puedo personalizar el catálogo?",
            respuesta: "Sí. Cargás tu logo y tu portada, y elegís entre distintos diseños y paletas de color para que se parezca a tu marca.",
        },
        {
            pregunta: "¿Se cobran los pedidos desde el catálogo?",
            respuesta: "No. El catálogo es una vidriera: tus clientes ven los productos y te escriben por WhatsApp para comprar. No hay carrito ni pagos online.",
        },
        {
            pregunta: "¿Hay un plan gratis?",
            respuesta: "Sí. Podés crear tu catálogo y publicarlo gratis, con un límite de productos. Si necesitás más, pasás a un plan pago cuando quieras.",
        },
    ],
}
