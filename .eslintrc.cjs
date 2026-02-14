module.exports = {
  env: {
    browser: true,
    es6: true,
  },
  plugins: ['simple-import-sort', 'import'],
  extends: [
    'eslint:recommended',
    'plugin:vue/vue3-recommended',
    'plugin:prettier/recommended',
  ],
  ignorePatterns: [
    '*.d.ts',
    'public/*',
    'node_modules/*',
    'vendor/*',
    '_build/*',
    '_patch/*',
  ],
  reportUnusedDisableDirectives: true,
  globals: {
    _app_: true,
    Innoclapps: true,
    config: true,
    tinymce: true,
    lang: true,
    process: true,

    useFetch: true,
    useI18n: true,
    requireConfirmation: true,
    showAlert: true,
    scriptConfig: true,
  },
  rules: {
    'vue/require-default-prop': 'off',
    'vue/require-prop-types': 'off',
    'vue/multi-word-component-names': [
      'error',
      {
        ignores: [
          'Icon',
          'Anchor',
          'Editor',
          'Settings',
          'Error403',
          'Error404',
        ],
      },
    ],
    'vue/attributes-order': [
      'warn',
      {
        order: [
          'DEFINITION',
          'LIST_RENDERING',
          'CONDITIONALS',
          'RENDER_MODIFIERS',
          'GLOBAL',
          ['UNIQUE', 'SLOT'],
          'TWO_WAY_BINDING',
          'OTHER_DIRECTIVES',
          'ATTR_STATIC',
          'ATTR_DYNAMIC',
          'ATTR_SHORTHAND_BOOL',
          'EVENTS',
          'CONTENT',
        ],
        alphabetical: false,
      },
    ],
    'vue/no-restricted-syntax': 'error',
    'vue/custom-event-name-casing': 'error',
    'vue/valid-define-options': 'error',
    'vue/no-template-target-blank': 'error',
    'vue/no-required-prop-with-default': 'error',
    'vue/new-line-between-multi-line-property': 'error',
    'vue/html-button-has-type': 'error',
    'import/newline-after-import': 'error',
    'vue/no-duplicate-attr-inheritance': 'error',
    'vue/padding-line-between-blocks': 'error',
    'vue/padding-line-between-tags': [
      'error',
      [{ blankLine: 'always', prev: '*', next: '*' }],
    ],
    'vue/define-macros-order': [
      'error',
      {
        order: ['defineOptions', 'defineProps', 'defineEmits', 'defineModel'],
        defineExposeLast: true,
      },
    ],
    'simple-import-sort/imports': [
      'warn',
      {
        groups: [
          // Packages `vue` related packages come first.
          ['^vue', '^@?\\w'],
          // Core imports
          ['^@/Core(/.*|$)'],
          // Modules import going after core imports
          ['^(@(?!/Core))(/.*|$)'],
          // Side effect imports.
          ['^\\u0000'],
          // Parent imports. Put `..` last.
          ['^\\.\\.(?!/?$)', '^\\.\\./?$'],
          // Other relative imports. Put same-folder imports and `.` last.
          ['^\\./(?=.*/)(?!/?$)', '^\\.(?!/?$)', '^\\./?$'],
          // Style imports.
          ['^.+\\.?(css)$'],
        ],
      },
    ],
    'padding-line-between-statements': [
      'error',
      { blankLine: 'always', prev: '*', next: 'return' },
      { blankLine: 'always', prev: '*', next: 'multiline-const' },
      { blankLine: 'always', prev: '*', next: 'multiline-let' },
      { blankLine: 'always', prev: 'multiline-const', next: '*' },
      { blankLine: 'always', prev: 'multiline-let', next: '*' },
      { blankLine: 'always', prev: '*', next: 'multiline-block-like' },
      { blankLine: 'always', prev: '*', next: 'multiline-expression' },
      { blankLine: 'always', prev: '*', next: 'function' },
      { blankLine: 'always', prev: 'function', next: '*' },
    ],
    'no-useless-escape': 'off',
    'lines-between-class-members': 'error',
  },
}
