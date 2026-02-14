import globalVue from '@concordcrm/vite-plugin-global-vue'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import unfonts from 'unplugin-fonts/vite'
import { defineConfig } from 'vite'

const moduleAliasRegex = /@\/([a-zA-Z]+)\/(.*)/

export default defineConfig({
  resolve: {
    alias: [
      {
        find: moduleAliasRegex,
        replacement: '/modules/$1/resources/js/$2',
      },
    ],
  },
  //   css: {
  //     preprocessorOptions: {
  //       // https://sass-lang.com/documentation/breaking-changes/legacy-js-api/#bundlers
  //       scss: {
  //         api: 'modern',
  //       },
  //     },
  //   },
  plugins: [
    laravel(['resources/js/app.js', 'resources/css/contentbuilder/theme.css']),
    unfonts({
      custom: {
        families: [
          {
            name: 'Dancing Script',
            local: 'Dancing Script',
            src: './public/fonts/DancingScript-Regular.ttf',
          },
        ],
      },
    }),
    globalVue(),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],
  //   server: {
  //     hmr: {
  //       host: 'localhost',
  //     },
  //   },
})
