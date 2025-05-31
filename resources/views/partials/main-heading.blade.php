<div class="relative mb-6 w-full">
    @if(session('status'))
        <flux:callout icon="{{session('status')['success'] ? 'check-circle' : 'x-circle'}}" variant="{{session('status')['success'] ? 'success' : 'danger'}}" inline
                      x-data="{ visible: true }" x-show="visible"
                      class="mb-4"
                      heading="{{ session('status')['message'] }}"
        >
            <x-slot name="controls">
                <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false"/>
            </x-slot>
        </flux:callout>
    @endif
    @if($title)
    <div class="flex justify-between items-center">
        <flux:heading size="xl" level="1">{{$title}}</flux:heading>
    </div>
    <flux:subheading size="lg" class="mb-6"></flux:subheading>
    <flux:separator variant="subtle"/>
        @endif
</div>
