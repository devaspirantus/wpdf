<div wire:ignore x-data="signaturePad()">
    <p class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}
    </p>
    <div class="rounded-md border-2 border-dashed border-gray-300 bg-gray-50 overflow-hidden">
        <canvas
            x-ref="canvas"
            width="600"
            height="150"
            @pointerdown="start"
            @pointermove="move"
            @pointerup="end"
            @pointercancel="end"
            class="block w-full h-36 touch-none cursor-crosshair select-none"
        ></canvas>
    </div>
    <input type="hidden" ref="input" name="{{ $name }}" wire:model="{{ $name }}">
    @error($errorKey ?? $name)
        <p class="text-red-600 text-sm mb-1">{{ $message }}</p>
    @enderror
    <div class="flex items-center justify-end mt-1">
        <button type="button" @click="clear" class="text-sm text-gray-500 hover:text-gray-700 underline">
            مسح التوقيع
        </button>
    </div>
    <p class="text-xs text-gray-400 mt-1">وقّع في المساحة أعلاه</p>
</div>
