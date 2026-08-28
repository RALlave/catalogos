/* ============================================================
   DATOS DE EJEMPLO — hardcodeados, sin backend.
   La forma imita lo que devolvería la API (tienda + categorías +
   productos), para que el reemplazo por fetch sea directo.
   Las fotos salen de assets/img/<categoria>/.
   ============================================================ */

const TIENDA = {
    nombre: 'Tienda Nube',
    rubro: 'Celulares, moda y accesorios',
    moneda: 'Bs',
    whatsapp: '59170012345'
};

const CATEGORIAS = [
    { slug: 'todos',        nombre: 'Todos' },
    { slug: 'celulares',    nombre: 'Celulares' },
    { slug: 'zapatillas',   nombre: 'Zapatillas' },
    { slug: 'ropa-hombre',  nombre: 'Ropa de hombre' },
    { slug: 'perfumes',     nombre: 'Perfumes' },
    { slug: 'gafas',        nombre: 'Gafas' },
    { slug: 'unas',         nombre: 'Uñas' }
];

/* estado: 'nuevo' | 'destacado' | 'agotado' | null */
const PRODUCTOS = [

    /* --- Celulares ------------------------------------------------ */
    {
        id: 1,
        nombre: 'Redmi 14C 256 GB',
        descripcion: 'Cámara de 50 MP, batería de 5160 mAh. Azul o negro.',
        precio: 1190,
        precio_antes: 1390,
        categoria: 'celulares',
        estado: 'destacado',
        imagen: 'assets/img/celulares/001a8d8969d3b8f8761d2ec099307059.jpg'
    },
    {
        id: 2,
        nombre: 'Redmi Note 13 Pro',
        descripcion: 'Cámara principal de 200 MP y pantalla AMOLED de 6,67".',
        precio: 1890,
        precio_antes: null,
        categoria: 'celulares',
        estado: 'nuevo',
        imagen: 'assets/img/celulares/1e62ae41aac2041a531652e044d726d2.jpg'
    },
    {
        id: 3,
        nombre: 'Redmi 14C en cuatro colores',
        descripcion: 'Negro, verde oliva, lila y azul. Consultá stock por color.',
        precio: 890,
        precio_antes: null,
        categoria: 'celulares',
        estado: 'agotado',
        imagen: 'assets/img/celulares/1ef923b330600054d512e9dca3ec7042.jpg'
    },

    /* --- Zapatillas ----------------------------------------------- */
    {
        id: 4,
        nombre: 'Zapatillas running con cámara de aire',
        descripcion: 'Blanco y negro, malla transpirable. Talles 38 al 44.',
        precio: 320,
        precio_antes: 390,
        categoria: 'zapatillas',
        estado: 'destacado',
        imagen: 'assets/img/zapatillas/3cc30e01ea95d52d3da72e62ec0aec24.jpg'
    },
    {
        id: 5,
        nombre: 'Zapatillas urbanas blanco y negro',
        descripcion: 'Detalles dorados y suela reforzada. Talles 39 al 43.',
        precio: 290,
        precio_antes: null,
        categoria: 'zapatillas',
        estado: 'nuevo',
        imagen: 'assets/img/zapatillas/409e0145ab54c8f9da31fa6f682e7ac8.jpg'
    },
    {
        id: 6,
        nombre: 'Adidas Samba blancas',
        descripcion: 'Cuero blanco, suela de goma. Talles 38 al 42.',
        precio: 620,
        precio_antes: null,
        categoria: 'zapatillas',
        estado: 'agotado',
        imagen: 'assets/img/zapatillas/5513f3f9b6b8cb2679fe5e1d52b03fc3.jpg'
    },

    /* --- Ropa de hombre ------------------------------------------- */
    {
        id: 7,
        nombre: 'Jean semi-skinny azul oscuro',
        descripcion: 'Denim elastizado con lavado. Talles 28 al 38.',
        precio: 260,
        precio_antes: null,
        categoria: 'ropa-hombre',
        estado: null,
        imagen: 'assets/img/ropa-hombre/0cfb33efcc5b8aaaaa9275d91a08df58.jpg'
    },
    {
        id: 8,
        nombre: 'Short de jean',
        descripcion: 'Cuatro tonos de azul disponibles. Talles 30 al 40.',
        precio: 180,
        precio_antes: 215,
        categoria: 'ropa-hombre',
        estado: 'nuevo',
        imagen: 'assets/img/ropa-hombre/154d5ab010b1d01b206185012189345d.jpg'
    },
    {
        id: 9,
        nombre: 'Pantalón chino elastizado',
        descripcion: 'Gabardina con puño doblado. Vino, gris y negro.',
        precio: 230,
        precio_antes: null,
        categoria: 'ropa-hombre',
        estado: 'destacado',
        imagen: 'assets/img/ropa-hombre/2592623453a33ff3f7b3d8b89e3d2cea.jpg'
    },

    /* --- Perfumes -------------------------------------------------- */
    {
        id: 10,
        nombre: 'Bleu de Chanel EDT 100 ml',
        descripcion: 'Amaderado aromático. Original con caja sellada.',
        precio: 980,
        precio_antes: null,
        categoria: 'perfumes',
        estado: 'destacado',
        imagen: 'assets/img/perfumes/20a042aeda5d71a15cd59636d6c6ff21.jpg'
    },
    {
        id: 11,
        nombre: 'Dior Sauvage EDP 100 ml',
        descripcion: 'Fresco y especiado, larga duración. Original.',
        precio: 1150,
        precio_antes: 1290,
        categoria: 'perfumes',
        estado: null,
        imagen: 'assets/img/perfumes/4a9ae195600fcc540c7ecebcc8300ad3.jpg'
    },
    {
        id: 12,
        nombre: 'Azzaro The Most Wanted 100 ml',
        descripcion: 'Parfum intenso. Incluye estuche original.',
        precio: 720,
        precio_antes: null,
        categoria: 'perfumes',
        estado: 'agotado',
        imagen: 'assets/img/perfumes/510acd2d06d402f5060df446cd806400.jpg'
    },

    /* --- Gafas ----------------------------------------------------- */
    {
        id: 13,
        nombre: 'Lentes de descanso montura redonda',
        descripcion: 'Filtro de luz azul, acetato negro. Unisex.',
        precio: 180,
        precio_antes: null,
        categoria: 'gafas',
        estado: 'destacado',
        imagen: 'assets/img/gafas/16d17c0be9c65b16892fbbd33584f7f9.jpg'
    },
    {
        id: 14,
        nombre: 'Lentes con filtro azul montura celeste',
        descripcion: 'Armazón liviano translúcido. Incluye estuche rígido.',
        precio: 150,
        precio_antes: 190,
        categoria: 'gafas',
        estado: 'nuevo',
        imagen: 'assets/img/gafas/2648e52382e8646c57a6c285aac83277.jpg'
    },
    {
        id: 15,
        nombre: 'Gafas de sol polarizadas',
        descripcion: 'Lente negra cuadrada con protección UV400.',
        precio: 220,
        precio_antes: null,
        categoria: 'gafas',
        estado: null,
        imagen: 'assets/img/gafas/536fc65b9f713f6367dfad85df1b649b.jpg'
    },

    /* --- Uñas ------------------------------------------------------ */
    {
        id: 16,
        nombre: 'Uñas press-on negro mate',
        descripcion: 'Set de 24 piezas con detalle de hoja dorada. Incluye pegamento.',
        precio: 95,
        precio_antes: null,
        categoria: 'unas',
        estado: 'nuevo',
        imagen: 'assets/img/unas/14239f2725d52d2e1389c73698d3a50d.jpg'
    },
    {
        id: 17,
        nombre: 'Uñas press-on azul con mariposas',
        descripcion: 'Set de 24 piezas con pedrería. Cinco medidas incluidas.',
        precio: 110,
        precio_antes: 135,
        categoria: 'unas',
        estado: 'destacado',
        imagen: 'assets/img/unas/25a5c7c085e06f8e5801e0fe7d8a9d45.jpg'
    },
    {
        id: 18,
        nombre: 'Set de esmaltes burdeos',
        descripcion: 'Tres esmaltes semipermanentes más stickers de corazones.',
        precio: 85,
        precio_antes: null,
        categoria: 'unas',
        estado: null,
        imagen: 'assets/img/unas/2818b870874bb1be6771619fc1b8a1a9.jpg'
    }
];
