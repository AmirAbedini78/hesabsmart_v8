<x-core::layouts.guest>
    @section('title', 'Patch Application')

    <div class="h-screen min-h-screen bg-neutral-100 dark:bg-neutral-800">
        <action-panel title="Patch Application"
            description="A few more steps are required in order to patch the application with the latest changes."
            redirect-to="{{ url('settings/update') }}" action="{{ url('update/finalize') }}" button-text="Apply">
        </action-panel>
    </div>
</x-core::layouts.guest>
