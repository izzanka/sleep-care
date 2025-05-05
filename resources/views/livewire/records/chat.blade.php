<div class="h-[440px] rounded-lg flex flex-col">
    <div class="flex-1 p-4 overflow-y-auto custom-scrollbar border dark:border-transparent" id="chat-container">
        @foreach($chats as $chat)
            @if($chat->sender_id == auth()->id())
                <div class="flex items-start space-x-2 justify-end mt-2">
                    <div class="bg-green-500 text-white p-3 rounded-lg max-w-xs">
                        <p class="text-sm break-words">{{$chat->message}}</p>
                        <span
                            class="text-xs text-gray-200 block text-right mt-1">{{$chat->created_at->format('H:i')}}</span>
                    </div>
                </div>
            @else
                <div class="flex items-start space-x-2 mt-2">
                    <div class="bg-gray-200 p-3 rounded-lg max-w-xs">
                        <p class="text-sm text-black">{{$chat->message}}</p>
                        <span
                            class="text-xs text-gray-500 block text-right mt-1">{{$chat->created_at->format('H:i')}}</span>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
    <div class="p-3 flex items-center gap-2 bg-white border dark:bg-zinc-700 dark:border-transparent rounded-b-lg">
        <input type="text"
               class="flex-1 p-2 border rounded-lg text-sm outline-none focus:ring"
               placeholder="Tulis sebuah pesan..." disabled>
    </div>
</div>
