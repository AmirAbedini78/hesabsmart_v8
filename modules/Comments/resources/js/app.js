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
import CollapsableCommentsList from './components/CollapsableCommentsList.vue'
import CollapseableCommentsLink from './components/CollapseableCommentsLink.vue'
import CommentsAdd from './components/CommentsAdd.vue'
import CommentsList from './components/CommentsList.vue'
import CommentsStore from './store/Comments'

if (window.Innoclapps) {
  Innoclapps.booting(function (app, router, store) {
    app.component('CollapseableCommentsLink', CollapseableCommentsLink)
    app.component('CommentsAdd', CommentsAdd)
    app.component('CollapsableCommentsList', CollapsableCommentsList)
    app.component('CommentsList', CommentsList)

    store.registerModule('comments', CommentsStore)
  })
}
