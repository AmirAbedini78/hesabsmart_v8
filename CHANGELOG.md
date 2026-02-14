# Version 1.7.0 - 2025-09-01

- Add Twilio IVR settings
- Add ability to provide deal status during import
- Add ability to add collapsable menu groups
- Auto create companies/contacts during import if provided does not exist.
- Auto associate contact email to company by email domain
- Updated: export numeric field raw column
- Updated: auto add document contact associations as recipient when document doesn't require signature
- Updated: show country for contacts/companies in deal webhook
- Updated: auto assicate document deal create to related contacts/companies
- Updated: add ISO in country resource
- Updated: remember pagination page when navigating between pages
- Updated: remember sort when navigating between pages
- Fix disabled notification error 404
- Fix export when using filters
- Fix deal create on document not associating products
- Fix contacts import creation
- Fix phone field trashed search
- Fix email text on dark mode
- Fix template logo dimensions

# Version 1.6.0 - 2025-02-12

- Add dialpad during call to send number keys
- Add horizontal scroll on email tables exceeding width
- Add Italian translation
- Add Slovak translation
- Add Czech translation
- Updated: Show tags on deal board
- Updated: Use translation for document sign button
- Updated: Make edit/preview modals not static
- Updated: Enhance table UI colors
- Updated: Make deal mini pipeline scrollable
- Fix filters when rule exists multiple time in a group
- Fix accept invitation form UI
- Fix mail client sync command
- Fix non-admin user cannot select IMAP account
- Fix companies field display
- Fix HasFilter unknown and known conditions for company phone
- Fix workflow webhook and form submission with custom fields
- Fix unknown filter operator
- Fix long product names in table select
- Fix web form unique fields
- Fix views switching filters not applied
- Fix Gmail email sync history incorrect
- Fix issues with exporting data
- Fix alias email not considered in email account preview display

# Version 1.5.1 - 2024-09-22

- Improve development environment for Windows
- Fix text selection on drag and drop
- Fix 403 error thrown when visiting record profile
- Fix workflow trigger "Deal Stage Changed" not triggering correctly
- Fix translation not applied on menu items and quick create
- Fix email action button placeholder URL

# Version 1.5.0 - 2024-08-06

**Requires PHP Version >= 8.2**

[Upgrade guide](https://www.concordcrm.com/blog/concord-crm-v1-5-upgrade-to-laravel-11-and-php-8-2)

- Added ability to clone web form
- Added ability to when deleting a record to choose whether to permanently delete or move to trash
- Add option to disable sync event guests as contacts
- Added "Last Contacted Date" readonly field for contacts and companies
- Add ability to choose default landing page for user
- Add option to apply all patches at once
- Updated: Laravel 11
- Updated: Add brand column to document index view
- Updated: Replace filters with views
- Updated: Make caller person display name clickable
- Updated: Show a list of all fields in form field dropdown instead only creation fields
- Fix: Notifications not sent after web form submission
- Fix: Mailable template placeholder url not properly decoded
- Fix: Sidebar close button does not work on mobile
- Fix: Comment content not updated after save
- Fix: time attribute in HTML content causing error

# Version 1.4.1 - 2024-03-16

- Updated: Disable dark mode on document public view
- Updated: Do not remove previous phone numbers on web form submission
- Updated: Implement "back-off" logic on email client sync
- Update: Export will include a local date time for DateTime fields
- Fix: ID fields doesn't have value in export
- Fix: Cannot reorder pipelines
- Fix: Cannot delete user via API
- Fix: Radio filter not working correctly
- Fix: Fix web form phone field not updating
- Fix: Web form submission does not set owner assigned date

# Version 1.4.0 - 2024-03-04

- Add ability to filter deals by contacts and companies
- Add ability to filter contacts by companies
- Add ability to filter companies by contacts
- Add ability to paste images from clipboard in editor
- Add ability to navigate to deal, contact, company via activity
- Add ability to enter negative deal amount
- Updated: Re-designed global search results
- Updated: Keep global search results until page refresh
- Updated: Improve filters builder and general UI
- Updated: Improve dark mode colors
- Updated: Improve activities calendar and UI
- Updated: Improve deals board performance
- Updated: New modules structure
- Updated: Mobile UI fixes and enhancements
- API: Add ability to provide deal "swatch_color" via API
- Fix notifications are not displayed
- Fix activities calendar incorrect filter by user
- Fix cannot change operand filter
- Fix delete notification performs redirection
- Minor fixes

# Version 1.3.5 - 2024-01-06

- Add ability to schedule emails
- Add ability to resize table headings
- Updated: Selecting a contact or company auto-populates related entities in the deal form.
- Updated: Enhanced tables UI
- Updated: Hide date range when export range is set to all
- Fix email message trimmed content not toggling
- Fix email message cannot be globally searched
- Fix cannot delete document with associations
- Fix count index columns not displaying value
- Fix email forward/reply with full HTML body causes error
- Fix twice click is required to show collapsed comments
- Fix cannot select scheduled document send date in future

# Version 1.3.4 - 2023-11-27

- Add ability to select export date range field
- Add ability to select "In 1 Week" when creating follow up task
- Changed: Excude mentions from bulk edit action
- Updated: Open resources directly on "Insights" view instead of performing redirect
- Updated: Make DatePicker compatible with UI colors
- Updated: Create follow up task will skip weekends for week and month selection
- Updated: Search to use wildcard
- UI Enhancements
- Performance & code improvements
- Fixed associations endpoint show all results instead of only associated
- Fixed deals board does not search custom fields
- Fixed activity table not refreshed whe activity marked as complete from modal
- Fix cannot edit lost reason
- Fix email message error when message is empty.
- Fixed in detail view date custom fields always included in updated attributes
- Fix filter not always applied

# Version 1.3.3 - 2023-10-16

- Add tags feature for inbox
- Add ability to filter inbox messages by tags
- Improve filters UI
- Fix total products filter showing error
- Fix Pusher.js integration fails with 500 error
- Fix cannot mark message as unread
- Fix deal amount field value not updated when adding products
- Performance improvements

# Version 1.3.2 - 2023-10-09

- Add ability for list view (table) polling
- Add green background on deals board won deals
- Add red background on deals board lost deals
- Add yellow background on deals board open deals behind expected close date
- Use batch requests to empty trash
- Fix deals board preview action in dropdown, changed to edit
- Fix Pusher.js integration
- Fix web form submission with country field
- Fix create new workflow form not showing on first click
- Fix bulk delete actions not properly authorized
- Fix server error on update view when purchase key is empty
- Performance improvement
- Minor fixes.

# Version 1.3.1 - 2023-10-01

- Improve deal board card drag
- Fix fields values not always updated in the UI
- Fix detail textarea field new lines
- Fix import fails when a lot records exists in database
- Fix moment locale not properly determined

# Version 1.3.0 - 2023-09-28

- Add ability to inline edit fields
- Add proper detail view on record profiles
- Add ability to choose which fields to export when exporting a resource
- Add ability to empty trash
- Add ability to change activity status directly from edit view
- Add ability to edit deal amount via deal board
- Add total amount in record detail deals card
- Add color custom field
- Add ability to revert imported record
- Add bulk edit index view action for specific resources
- Add new logged calls card for super admins
- Add red background on required fields without value
- Updated: New table inline edit actions
- Updated: Make parent company clickable
- Updated: Allow to select associated records via edit modal
- Updated: Remove tags field availability from web form
- Updated: Removed deal Products tabs in favor of new inline edit features
- Fix cannot receive Twilio calls
- Fix error when deleting a user
- Fix issue where records cannot be updated
- Fix tags when exporting data
- Code enhancements
- Minor fixes

# Version 1.2.2 - 2023-07-11

- Add tags feature for deals, contacts and companies
- Add document content settings
- Add ability to re-order products
- Add Russian translation
- Improved: Ensure deal create does not fail when pipeline has no stages
- Improved: Add ability to directly re-order table headings
- Improved: Logs view UI
- Updated: Remove border from document sidebar active items
- Fix queued emails associations
- Fix compose message modal not displayed properly with many templates
- Fix duplicate attachments failing mailclient
- Fix privacy policy view
- Fix placeholders for subject not always parsed

# Version 1.2.1 - 2023-06-27

- Add URL custom field
- Add ability to translate brand config values
- Show user teams in sidebar dropdown
- Show the teams the user is managing in user profile
- Enhance UI on products table
- Improved: Disable dark mode on web forms
- Improved: Display a warning for empty placeholders before sending a message
- Improved: When sending message use placeholders from first associated resource or first recipient
- Performance improvements
- Fix billable calculations with 100% discount on line item
- Fix Twilio activation text not in translation file
- Fix textarea placeholder new lines
- Fix document error when non authenticated user view
- Fix message not associated with contact during synchronization
- Fix cannot search company and contacts by email
- Fix filter next month not working properly
- Fix cannot update role
- Fix cannot delete user with imports
- Fix navbar command text showing when searching
- Fix issues with incorrect card results
- Fix deals board stage sorting not applied
- Fix incorrect date format

# Version 1.2.0 - 2023-05-09

- Add ability to open preview modal via details view
- Add ability for unique custom fields to be globally searchable
- Add ability to add placeholders in email subject
- Add ability to resize document content columns
- Add ability to specify PDF padding per document
- Improved: Keep last (saved) filter active after navigating from list view
- Improved: Search field won't be disabled when searching to retain focus
- Improved: Document and mail templates tags names
- Fix product description not shown on document products table
- Fix when adding document placeholder the focus is lost
- Fix dashboard cards enabled state is not reflected on insights view
- Fix select field cleared when searching
- Fix document templates not loaded in dropdown

# Version 1.1.9 - 2023-04-26

- Add lightbox for uploaded images
- Add lightbox for email message content
- Add filters for products list view
- Add bulk action to update product tax label
- Add missing translation text
- Updated: Portuguese translation
- Updated: Clear rules when deleting filter
- Updated: Add timezone indicator in document PDF signature date
- Updated: Do not record email views when the user is logged-in
- Fix cannot upload images via editor
- Fix font for signature not loaded on HTML
- Fix email sync not always working properly

# Version 1.1.8 - 2023-04-23

- Add ability to change theme colors
- Fix issue with updater
- Fix pusher channels not registered
- Fix infinity loop when using multi select custom field

# Version 1.1.7 - 2023-04-21

- Add deals kanban pagination load
- Add brand visibility option
- Add document type visibility option
- Add ability to create inline record on create/preview modals
- Add ability to unmark phone field as unique
- Add document clone feature
- Add won and lost date for deals filters
- Add document template clone feature
- Add Spanish and Portuguese translation
- Add condensed table list settings option
- Add product clone feature
- Add ability to display logo on web forms
- Updated: Laravel 10
- Updated: for new users by default disable API
- Updated: Drop proc_open and proc_close requirements
- Updated: Enhance UI to show on what stage deal is lost
- Updated: Directory structure
- Updated: Build assets with Vite
- Fix resource updated_at updated when syncing next activity
- Fix incorrect notification sent on owner change
- Fix reset password notification
- Fix issue with permissions checks
- Refactor Vue components in Composition API
- Front-end performance improvements
- Reduce build size
- Minor fixes

# Version 1.1.6 - 2023-01-17

- Add ability to define team manager
- Add ability role capability "Team Only" for most of the applicable resources
- Added ability to define unique custom field (Text, Number, Email) for contacts, companies and products
- Updated: Adjust deal pipeline switcher to fit stages in one line
- Improve parsing mail client stale classes
- Fix: Regular user cannot mark activity as complete via "My Activities" dashboard card
- Fix: Navbar CSS z-index
- Fix: Editor locale not loaded correctly
- Fix: connect shared email account shows 404 error

# Version 1.1.5 - 2023-01-12

- Added option to specify FROM header for personal email accounts
- Added deal board view weighted value per stage based on win probability
- Added option to make the lost reason field required
- Added roles and teams users table fields
- Updated: When exporting contacts, the associated companies names will be exported
- Updated: When exporting companies, the associated contacts names will be exported
- Updated: Product performance card will take into account won deals instead of accepted documents
- Fix: phone field not properly parses calling prefix during import
- Fix: conflict with Plesk mod security
- Fix: deal amount not saved on create
- Fix: pipeline teams and users visibility field not shown after save
- Fix: deal board stage total not properly calculated

# Version 1.1.4 - 2022-12-29

- Improved: New email automatic associations will be checked from to, cc and bcc addresses
- Add ability to provide owner name instead of ID via Zapier/API
- Fixed issue where Gmail emails sync fails
- Fixed issue where message_id database length is too short for some emails

# Version 1.1.3 - 2022-12-28

- Added placeholders for documents and documents templates
- Add quick create icons in sidebar
- Added ability to order custom field options
- Add ability to choose color for custom field options
- Add ability to choose document HTML view type
- Add ability to track open emails
- Add quick create for emails
- Add total products badge on document create/edit
- Updated: Allow providing phone number type via pipe (|) separator (API, Zapier, Import)
- Document PDF alignments fixes
- Fixed calendar not working with certain locales
- Fixed regular user cannot edit resources
- Fixed import phone imported as 1 phone number
- Fixed phone field requires calling prefix even when calling prefix requirement is turned off

# Version 1.1.2 - 2022-11-16

- Add ability to reload document templates from content section
- Add ability authorized users to view activities from all users on calendar view
- Added "Updated At" filter for contacts, deals, companies, documents
- Improved: Add additional PHP memory limit checks
- Improved: date parsing on email client when invalid dates are used
- Improved: Email acccount sync
- Updated: Only allow to sync primary calendars
- Fixed select search input blur on Firefox
- Fixed duplicate activities created via calendar
- Fixed document PDF fonts not loaded correctly
- Fixed exit on document not always working properly
- Fixed cannot align image via document editor
- Fixed editor fonts are not loaded on HTML views
- Fixed sometimes cannot add multiple snippets via the document editor

# Version 1.1.1 - 2022-10-03

- Fixed typo in translator
- Fixed new translations not applied

# Version 1.1.0 - 2022-10-03

- Added documents feature
- Added documents types
- Added brands feature
- Added "Export" permission
- Added ability to add deal products when creating new deal
- Updated: Import feature now consist if a "Skip file" when validation failure happens
- Updated: Import performance improved
- Fix cannot create contact with phone via Zapier
- Fixed product performance card amount formatting for specific currencies
- Fixed when changing order of wigdets on dashboard all disabled widgets become enabled again
- Fixed cannot load comments for notes and calls via resource record profile
- Performance improvements
- Minor fixes

# Version 1.0.7 - 2022-08-12

- Add dashboard cards loading placeholders
- Allow providing phone number as string via API
- Fixed resource send email workflow action
- Fixed field attribute name in validation messages
- Fixed cannot create records via preview modal
- Fixed date picker not working with different locales
- Fixed cannot sort aggregate column in cards table
- Fixed cannot connect mail account with empty folder delimiter
- Fixed Gmail BCC not applied when sending mail

# Version 1.0.6 - 2022-07-13

- Added confetti when marking deal as lost
- Added keyboard shortcuts for quick create
- Added red background on deals kanban if the deal falls behind the expected close date
- Added red border left indicator on deals table if the deal falls behind the expected close date
- Updated: The auto updater code is optimized to use less memory
- Updated: Rate limiter to 90 requests per minute
- Updated: Packages and dependencies
- Updated: Added field labels to call outcome and date
- Code improvements and cleanup
- Fixed cannot preview deal via kanban on mobile devices
- Fixed deals board default filter not applied on second navigation
- Fixed mail account folders collapsed when tree
- Fixed cannot clear deal expected close date

# Version 1.0.5 - 2022-07-05

- Added ability sales agents to choose from predefined list of lost reasons when marking the deal as lost
- Added ability to restrict sales agents the ability to write lost reason manually
- Added "Deal Status Changed" workflow trigger
- Added "Mark Associated Activities as Completed" workflow action
- Added "Delete Associated Activities" workflow action
- Added unread count badges for email account folders
- Fixed deals cards not reloaded after pipeline change
- Fixed activity note and description style from editor not applied
- Fixed HTML entities not encoded in mail message

# Version 1.0.4 - 2022-06-29

- Added teams feature
- Added ability to restrict pipelines visibility, per user or team
- Added ability to change pipelines order via kanban
- Added "Open", "Closed", "Won" and "Lost" deals count table column for contacts and companies
- Added ability to separately customize Edit/Preview fields
- Added ability to create activity for deals, companies, contacts via table elipsis dropdown
- Added ability to specify teams when inviting user
- Added overdue indicator on "My Activities" dashboard card
- Calendar will be automatically refreshed after synchronization when Pusher is configured
- Updated: Make users, contacts, companies, products and activities table created at column hidden by default
- Updated: Remove company name and owner fields from detail view (use top header fields to update)
- Updated: Remove contact firstname, lastname and owner fields from detail view (use top header fields to update)
- Updated: Remove deal name and owner fields from detail view (use top header fields to update)
- UI Improvements
- Fixed cannot connect Microsoft work/school account
- Fixed new break lines on editor
- Fixed cannot connect email account with sub draft folders
- Fixed resource date **was** and **is** filter not working properly
- Fixed import note field shown in mailables merge fields

# Version 1.0.3 - 2022-06-15

- Added option to disable password forgot feature
- Added option to block bad visitors based on IP, referrer and user agent
- Added new dashboard card "Won deals by month"
- Added new dashboard card "Won deals stage"
- Added ability the installer to apply [patches](https://www.concordcrm.com/docs/1.0/update#patcher) after the installation is finished
- Updated: Better validation on locale name when creating new locale
- Fixed resource export not working properly
- Fixed mail sync fails if the attachment extension is not allowed
- Fixed cannot send an email after new locale creation

# Version 1.0.2 - 2022-06-13

- Added stage ID in Settings->Deals pipeline for API usage
- Updated: Disallow making custom field as not visible when required
- Fixed issue where custom field cannot be unmarked as required
- Fixed auto updater
- Fixed numeric input field does not work properly with specific currencies
- Fixed calendar sync when syncing via webhooks
- Fixed casting on properties on some specific servers
- Fixed installer does not work with global ENV variables exists

# Version 1.0.1 - 2022-06-09

- Added ability to re-order record profile sidebar sections
- Added Trigger Webhook action on deal stage changed workflow trigger
- Redesigned activity calendar
- Replaced filter condition dropdown with select dropdown
- Exclude assign owner action on contact, company, deal detail view
- Apply proper sorting on deals, companies and contacts details cards
- Update: Comments will be eager loaded on the front-end
- UI Improvements
- Responsive design improvements
- Fix child companies navigation from company detail view
- Fixed cannot edit activity owner from detail view
- Fixed dashboard cards edit cannot be saved properly

# Version 1.0.0 - 2022-06-01

**Requires PHP Version >= 8.1**

- Deals management feature
- Contacts management feature
- Companies management feature
- Deals kan-ban with multiple pipelines
- Products management
- Activities managements, reminders, calendar
- 2-way calendar synchronization (Outlook Calendar, Google Calendar)
- Email client (Gmail, Outlook, IMAP)
- Advanced filters and filtering
- Import and export feature with fields mapping
- Custom fields
- Advanced tables with orderable fields and custom sorting
- Automation workflows
- Call logs & in-app calling
- Notes feature for the core resources
- Deal/Lead web form
- Roles & Permissions
- Zapier integration
- Customizable dashboard
- Integrated API
- 1 Click update & Patcher
- Add sale reps in different timezones
- Send sale reps invite
- API & API keys generation
- Fully responsive
