/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */
import { computed, toValue } from 'vue'
import { useStore } from 'vuex'

export function useDataViews(identifier) {
  const store = useStore()

  /**
   * The available views for the identifier.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const views = computed({
    set(newValue) {
      store.commit('dataViews/SET', {
        identifier: toValue(identifier),
        views: newValue,
      })
    },
    get() {
      return store.getters['dataViews/getAll'](toValue(identifier))
    },
  })

  /**
   * The active view for the identifier.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const activeView = computed({
    set(newValue) {
      store.commit('dataViews/SET_ACTIVE', {
        identifier: toValue(identifier),
        id: newValue,
      })
    },
    get() {
      return store.getters['dataViews/getActive'](toValue(identifier))
    },
  })

  /**
   * Indicates if there is an active view for the identifier.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const hasActiveView = computed(() => Boolean(activeView.value))

  /**
   * Get the first view that is open for the user.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const firstOpenView = computed(() =>
    views.value.find(view => view.is_open_for_user)
  )

  /**
   * Indicates whether the filters rules are visible.
   *
   * @type {import('vue').ComputedRef<boolean>}
   */
  const filtersBuilderVisible = computed({
    set(newValue) {
      store.commit('dataViews/SET_BUILDER_VISIBLE', {
        identifier: toValue(identifier),
        viewId: activeView.value.id,
        visible: newValue,
      })
    },
    get() {
      return store.getters['dataViews/filtersBuilderVisible'](
        toValue(identifier),
        activeView.value.id
      )
    },
  })

  /**
   * Update view in store.
   */
  function patchView(updatedView) {
    store.commit('dataViews/UPDATE', {
      identifier: toValue(identifier),
      view: updatedView,
    })
  }

  /**
   * Remove view from store.
   */
  function removeView(id) {
    store.commit('dataViews/REMOVE', {
      identifier: toValue(identifier),
      id,
    })
  }

  /**
   * Add view in store.
   */
  function addView(view) {
    store.commit('dataViews/PUSH', {
      identifier: toValue(identifier),
      view,
    })
  }

  /**
   * Add view by given id.
   */
  function findView(id) {
    return views.value.find(view => parseInt(view.id) === parseInt(id))
  }

  return {
    views,
    filtersBuilderVisible,
    activeView,
    hasActiveView,
    firstOpenView,
    patchView,
    removeView,
    addView,
    findView,
  }
}
