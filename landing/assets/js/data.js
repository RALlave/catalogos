/* ==========================================================================
   DATA — todo el contenido editable de la landing vive acá.
   La estructura visual no toca estos datos: render.js los transforma en HTML.
   Para cambiar textos, precios o funcionalidades se edita SOLO este archivo.
   ========================================================================== */

const LANDING = {

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

    /* Sección "Precios".
       PLACEHOLDER: precios y límites tentativos, ajustar antes de publicar.
       destacado: true marca visualmente el plan recomendado. */
    planes: [
        {
            nombre: "Gratis",
            tagline: "Para empezar hoy mismo.",
            precio: "Bs. 0",
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
        },
        {
            nombre: "Pro",
            tagline: "Para vendedores que quieren crecer.",
            precio: "Bs. 50",
            periodo: "/ mes",
            destacado: true,
            cta: "Comenzar ahora",
            incluye: [
                "Hasta 60 productos",
                "Categorías ilimitadas",
                "Hasta 3 imágenes por producto",
                "Diseños y paletas de color",
                "Productos destacados y etiquetas",
                "Sin marca de la plataforma",
            ],
        },
        {
            nombre: "Negocio",
            tagline: "Para negocios ya establecidos.",
            precio: "Bs. 130",
            periodo: "/ mes",
            destacado: false,
            cta: "Elegir plan",
            incluye: [
                "Hasta 150 productos",
                "Todas las funciones de Pro",
                "Hasta 8 imágenes por producto",
                "Estadísticas del catálogo",
                "Soporte prioritario",
            ],
        },
    ],

    /* Sección "Testimonios".
       PLACEHOLDER: contenido de ejemplo, no son clientes reales. */
    testimonios: [
        {
            texto: "Antes mandaba las fotos de mis productos una por una por WhatsApp. Ahora paso el enlace de mi catálogo y listo.",
            nombre: "Camila Rojas",
            negocio: "Indumentaria",
            foto: "assets/img/persona-15.jpg",
        },
        {
            texto: "Cambiar un precio me llevaba toda la tarde entre las imágenes y las publicaciones. Hoy lo edito una vez.",
            nombre: "Verónica Paz",
            negocio: "Perfumería",
            foto: "assets/img/persona-16.jpg",
        },
        {
            texto: "Mis clientes ven todo lo que tengo sin tener que preguntarme por cada producto.",
            nombre: "Diego Mamani",
            negocio: "Accesorios",
            foto: "assets/img/persona-19.jpg",
        },
    ],

    /* Sección "Preguntas frecuentes" */
    faq: [
        {
            pregunta: "¿Hay un plan gratis?",
            respuesta: "Sí. Podés crear tu catálogo y publicarlo gratis, con un límite de productos. Si necesitás más, pasás a un plan pago cuando quieras.",
        },
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
            respuesta: "No. El catálogo es como una vitrina: tus clientes ven los productos y te escriben por WhatsApp para comprar. No hay carrito ni pagos online.",
        },
    ],
}
