<?php
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

namespace Modules\WebForms\Services;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Modules\Contacts\Models\Company;
use Modules\Contacts\Models\Contact;
use Modules\Contacts\Models\Source;
use Modules\Core\Facades\ChangeLogger;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Fields\Field;
use Modules\Core\Fields\User;
use Modules\Core\Models\Changelog;
use Modules\Deals\Models\Deal;
use Modules\Users\Models\User as UserModel;
use Modules\WebForms\Http\Requests\WebFormRequest;
use Modules\WebForms\Mail\WebFormSubmitted;
use Modules\WebForms\Models\WebForm;
use Plank\Mediable\Exceptions\MediaUploadException;
use Plank\Mediable\Facades\MediaUploader;

class FormSubmissionService
{
    /**
     * Process the web form submission.
     */
    public function submit(WebFormRequest $request): void
    {
        ChangeLogger::disable();
        User::setAssigneer($request->webForm()->creator);

        // Handle contact fields
        $request->setResource('contacts');
        $contact = $this->handleContactFields($request);

        // Update the display name fallback name with the actual contact display name,
        // if the form has the first name and lastname fields, the full name will be used as fallback
        $fallbackName = $contact->full_name;

        // Then handle deal fields
        $request->setResource('deals');
        $deal = $this->handleDealFields($request, $fallbackName, $contact);
        $deal->contacts()->attach($contact);

        // Last, handle company fields (if any)
        $request->setResource('companies');
        if ($request->getFields()->isNotEmpty() || $request->webForm()->getFileSectionsForResource('companies')->isNotEmpty()) {
            $company = $this->handleCompanyFields($request, $fallbackName, $deal, $contact);
            // It can be possible the contact to be already attached to the company e.q. in case the same form
            // is submitted twice, in this case, the company will exists as well the contact.
            $company->contacts()->syncWithoutDetaching($contact);
            $company->deals()->attach($deal);
        }

        $this->logSubmissionAndSendNotifications($request, $contact, $deal, $company ?? null);

        $request->webForm()->increment('total_submissions');

        ChangeLogger::enable();
    }

    /**
     * Log the submission and send notifications.
     */
    protected function logSubmissionAndSendNotifications(WebFormRequest $request, Contact $contact, Deal $deal, ?Company $company)
    {
        $changelog = (new FormSubmissionLogger(array_filter([
            'contacts' => $contact,
            'deals' => $deal,
            'companies' => $company ?? null,
        ]), $request))->log();

        $this->sendNotifications($request->webForm(), $changelog);
    }

    /**
     * Find duplicate company.
     */
    protected function findDuplicateCompany(WebFormRequest $request, string $companyName, ?string $email): ?Company
    {
        /** @var \Modules\Contacts\Resources\Company */
        $resource = $request->resource();

        if ($company = $request->findRecordFromUniqueCustomFields()) {
            return $company;
        }

        if ($email && $company = $resource->findByEmail($email, $resource->newQuery())) {
            return $company;
        }

        return $resource->findByName($companyName, $resource->newQuery());
    }

    /**
     * Find duplicate contact.
     */
    protected function findDuplicateContact(WebFormRequest $request, ?Field $emailField, ?Field $phoneField): ?Contact
    {
        /** @var \Modules\Contacts\Resources\Contact */
        $resource = $request->resource();

        if ($contact = $request->findRecordFromUniqueCustomFields()) {
            return $contact;
        }

        if ($emailField && ! empty($email = $request[$emailField->requestAttribute()])) {
            if ($contact = $resource->findByEmail($email, $resource->newQuery())) {
                return $contact;
            }
        }

        if ($phoneField && ! empty($phones = $request[$phoneField->requestAttribute()])) {
            if ($contact = $resource->findByPhones($phones)) {
                if (! $contact->trashed()) {
                    return $contact;
                }
            }
        }

        return null;
    }

    /**
     * Handle the contact fields.
     */
    protected function handleContactFields(WebFormRequest $request): Contact
    {
        $phoneField = $request->findField('phones');
        $emailField = $request->findField('email');
        $firstNameField = $request->findField('first_name');

        if ($firstNameField) {
            $firstName = $request[$firstNameField->requestAttribute()];
        } elseif ($emailField) {
            $firstName = $request[$emailField->requestAttribute()];
        } else {
            $firstName = $request[$phoneField->requestAttribute()][0]['number'] ?? 'Unknown';
        }

        if ($contact = $this->findDuplicateContact($request, $emailField, $phoneField)) {
            // Track updated fields
            ChangeLogger::enable();

            // Merge new phones with old phones.
            if ($phoneField) {
                $this->mergePhones($request, $phoneField->requestAttribute(), $contact->phones);
            }

            $contact = $request->resource()->update($contact, $request->asUpdateRequest($contact));

            ChangeLogger::disable();
        } else {
            $contact = $request->resource()->create($request->resource()->newModel()->forceFill([
                'first_name' => $firstName,
                'user_id' => $request->webForm()->user_id,
                'source_id' => $this->getSource()->getKey(),
                'owner_assigned_date' => now(),
            ]), $request->asCreateRequest());
        }

        $this->handleResourceUploadedFiles($request, $contact);

        return $contact;
    }

    /**
     * Handle the web form deal fields.
     *
     * @param  string  $fallbackName
     */
    protected function handleDealFields(WebFormRequest $request, $fallbackName): Deal
    {
        $nameField = $request->findField('name');
        $name = $this->determineDealName($request, $fallbackName, $nameField);

        $deal = $request->resource()->newModel()->forceFill([
            'pipeline_id' => $request->webForm()->submit_data['pipeline_id'],
            'stage_id' => $request->webForm()->submit_data['stage_id'],
            'user_id' => $request->webForm()->user_id,
            'web_form_id' => $request->webForm()->id,
            'owner_assigned_date' => now(),
        ]);

        if ($nameField) {
            $request[$nameField->requestAttribute()] = $name;
        } else {
            $deal->fill(['name' => $name]);
        }

        $request->resource()->create($deal, $request->asCreateRequest());

        $this->handleResourceUploadedFiles($request, $deal);

        return $deal;
    }

    /**
     * Determine the deal name.
     */
    protected function determineDealName(WebFormRequest $request, $fallbackName, ?Field $nameField): string
    {
        $name = $nameField ?
            $nameField->attributeFromRequest($request, $nameField->requestAttribute()) :
            $fallbackName.' Deal';

        if (! empty($request->webForm()->title_prefix)) {
            $name = $request->webForm()->title_prefix.$name;
        }

        return $name;
    }

    /**
     * Handle the company fields.
     *
     * @param  string  $fallbackName
     */
    protected function handleCompanyFields(WebFormRequest $request, $fallbackName): Company
    {
        $resource = $request->resource();
        $nameField = $request->findField('name');
        $name = $fallbackName.' Company';

        if ($nameField) {
            $name = $request[$nameField->requestAttribute()];
        }

        if ($emailField = $request->findField('email')) {
            $email = $request[$emailField->requestAttribute()];
        }

        if ($company = $this->findDuplicateCompany($request, $name, $email ?? null)) {
            // Track updated fields
            ChangeLogger::enable();

            // Merge new phones with old phones.
            if ($phoneField = $request->findField('phones')) {
                $this->mergePhones($request, $phoneField->requestAttribute(), $company->phones);
            }

            $resource->update($company, $request->asUpdateRequest($company));

            ChangeLogger::disable();
        } else {
            $company = $resource->create($request->resource()->newModel()->forceFill([
                $nameField?->attribute ?? 'name' => $name,
                'user_id' => $request->webForm()->user_id,
                'owner_assigned_date' => now(),
                'source_id' => $this->getSource()->getKey(),
            ]), $request->asCreateRequest());
        }

        $this->handleResourceUploadedFiles($request, $company);

        return $company;
    }

    /**
     * Merge the given request phones attribute with the old phones.
     */
    protected function mergePhones(Request $request, string $requestAttribute, EloquentCollection|array $oldPhones): void
    {
        $request->merge([
            $requestAttribute => (new PhoneMerger)->merge(
                $oldPhones,
                $request[$requestAttribute]
            ),
        ]);
    }

    /**
     * Handle the resource uploaded files.
     * NOTE: Before this function is called, the resource must be set for the request
     * via the WebFormRequest method "setResource".
     *
     * @param  \Modules\Core\Models\Model  $model
     */
    protected function handleResourceUploadedFiles(WebFormRequest $request, $model): void
    {
        $files = $request->webForm()->getFileSectionsForResource($request->resource()->name());

        $files->each(function (array $section) use ($request, $model) {
            foreach (Arr::wrap($request[$section['requestAttribute']]) as $uploadedFile) {
                // try {
                $media = MediaUploader::fromSource($uploadedFile)
                    ->toDirectory($model->getMediaDirectory())
                    ->setAllowedExtensions(Innoclapps::allowedUploadExtensions())
                    ->upload();
                // } catch (MediaUploadException $e) {
                // $exception = $this->transformMediaUploadException($e);
                /*
                            return $this->response(
                                ['message' => $exception->getMessage()],
                                $exception->getStatusCode()
                            );*/
                //  }
                $model->attachMedia($media, $model->getMediaTags());
            }
        });
    }

    /**
     * Get the web form source.
     */
    protected function getSource(): Source
    {
        return Source::where('flag', 'web-form')->first();
    }

    /**
     * Handle the web form notification.
     */
    protected function sendNotifications(WebForm $form, Changelog $changelog): void
    {
        if (count($form->notifications) > 0) {
            foreach ($this->getNotificationRecipients($form) as $recipient) {
                Mail::to($recipient)->send(
                    new WebFormSubmitted($form, new FormSubmission($changelog))
                );
            }
        }
    }

    /**
     * Get the notification recipients.
     */
    protected function getNotificationRecipients(WebForm $form): Collection
    {
        $users = UserModel::whereIn('email', $form->notifications)->get()->toBase();

        $usersEmails = $users->pluck('email')->all();

        if ($usersEmails != $form->notifications) {
            $nonUsersEmails = array_diff($form->notifications, $usersEmails);
        }

        return $users->merge($nonUsersEmails ?? []);
    }
}
