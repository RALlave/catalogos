export interface Category {
    name: string
    slug: string
    description: string | null
}

export interface Spec {
    label: string
    type?: string
    value?: string
    values?: string[]
}

export interface Badge {
    type?: string
    text: string
    detail?: string
}

export interface Schedule {
    days: string
    hours: string
}

export interface Product {
    name: string
    slug: string
    sku: string | null
    description: string | null
    specs: Spec[] | null
    benefits: string[] | null
    badges: Badge[] | null
    price: string | null
    sale_price: string | null
    featured: boolean
    sold_out: boolean
    category: Category | null
    images: string[]
}

export interface Hero {
    image_url: string | null
    eyebrow: string | null
    title: string
    text: string | null
}

export interface Theme {
    palette: string
    colors: Record<string, string>
    radius: string
    nav: string
    banner: string
}

export interface Store {
    name: string
    slug: string
    logo_url: string | null
    cover_url: string | null
    theme: Theme
    hero_effect: string
    heroes: Hero[]
    description: string | null
    meta_title: string | null
    meta_description: string | null
    industry: string | null
    whatsapp: string | null
    phone: string | null
    email: string | null
    facebook: string | null
    instagram: string | null
    tiktok: string | null
    website: string | null
    address: string | null
    map_url: string | null
    city: string | null
    country: string | null
    currency: string | null
    schedules: Schedule[] | null
    categories: Category[]
    cart_enabled: boolean
    waitlist_enabled: boolean
}

export interface Paginated<T> {
    data: T[]
    meta: {
        current_page: number
        last_page: number
        total: number
    }
}
