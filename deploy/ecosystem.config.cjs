/**
 * PM2 — mantiene vivo el catálogo (Nuxt SSR).
 *
 * Nuxt no es un archivo que nginx pueda servir: es un servidor Node que tiene
 * que estar corriendo. PM2 lo levanta, lo reinicia si se cae y lo vuelve a
 * arrancar cuando se reinicia el VPS.
 *
 *   cd /var/www/catalogos
 *   pm2 start deploy/ecosystem.config.cjs
 *   pm2 save
 *   pm2 startup          # deja el arranque automático configurado
 */
module.exports = {
    apps: [
        {
            name: 'catalogos-web',
            cwd: '/var/www/catalogos/web',
            script: '.output/server/index.mjs',

            instances: 1,
            exec_mode: 'fork',

            env: {
                NODE_ENV: 'production',
                NITRO_PORT: 3000,
                NITRO_HOST: '127.0.0.1',
                NUXT_PUBLIC_API_BASE: 'https://diseprog.com/api',
                NUXT_PUBLIC_SITE_URL: 'https://diseprog.com',
            },

            max_memory_restart: '400M',
            error_file: '/var/log/pm2/catalogos-web.error.log',
            out_file: '/var/log/pm2/catalogos-web.out.log',
        },
    ],
}
