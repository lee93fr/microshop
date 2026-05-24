@php
    $p = $prefix ?? 'create';
    $isEdit = $p === 'edit';
@endphp
<div>
    <label class="form-label">Code *</label>
    <input type="text" id="{{ $p }}-code" name="code"
           value="{{ $isEdit ? '' : old('code') }}"
           placeholder="WELCOME10"
           class="form-input font-mono uppercase @error('code') border-red-500 @enderror"
           maxlength="50" required>
    @if(!$isEdit) @error('code')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror @endif
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="form-label">Remise (€) *</label>
        <input type="number" id="{{ $p }}-discount_amount" name="discount_amount" step="0.01" min="0.01"
               value="{{ $isEdit ? '' : old('discount_amount') }}"
               class="form-input @error('discount_amount') border-red-500 @enderror" required>
        @if(!$isEdit) @error('discount_amount')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror @endif
    </div>
    <div>
        <label class="form-label">À partir de (€) *</label>
        <input type="number" id="{{ $p }}-min_purchase" name="min_purchase" step="0.01" min="0"
               value="{{ $isEdit ? '' : old('min_purchase', 0) }}"
               class="form-input @error('min_purchase') border-red-500 @enderror" required>
        @if(!$isEdit) @error('min_purchase')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror @endif
    </div>
</div>

<div>
    <label class="form-label">Nombre max d'utilisations</label>
    <input type="number" id="{{ $p }}-max_uses" name="max_uses" min="1"
           value="{{ $isEdit ? '' : old('max_uses') }}"
           placeholder="Illimité si vide"
           class="form-input @error('max_uses') border-red-500 @enderror">
    @if(!$isEdit) @error('max_uses')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror @endif
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="form-label">Début</label>
        <input type="datetime-local" id="{{ $p }}-starts_at" name="starts_at"
               value="{{ $isEdit ? '' : old('starts_at') }}"
               class="form-input @error('starts_at') border-red-500 @enderror">
    </div>
    <div>
        <label class="form-label">Expiration</label>
        <input type="datetime-local" id="{{ $p }}-expires_at" name="expires_at"
               value="{{ $isEdit ? '' : old('expires_at') }}"
               class="form-input @error('expires_at') border-red-500 @enderror">
        @if(!$isEdit) @error('expires_at')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror @endif
    </div>
</div>

<label class="flex items-center gap-2 cursor-pointer">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" id="{{ $p }}-is_active" name="is_active" value="1"
           class="rounded text-indigo-600 focus:ring-indigo-500"
           {{ $isEdit ? '' : (old('is_active', '1') ? 'checked' : '') }}>
    <span class="text-sm text-gray-700">Activer ce code</span>
</label>
