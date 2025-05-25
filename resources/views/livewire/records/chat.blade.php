<div class="space-y-2">
    @foreach($chats as $chat)
        <div class="break-words flex">
            <flux:heading class="w-20">
                {{ $chat->sender_id == auth()->id() ? 'Anda' : 'Pasien' }}:
            </flux:heading>
            <flux:text class="flex-1">
                {{ $chat->message }}
            </flux:text>
        </div>
    @endforeach
</div>
