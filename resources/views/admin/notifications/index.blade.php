@extends('layouts.admin')
@section('title', 'Templates email')
@section('header', 'Templates de notifications email')

@section('content')
<div class="max-w-4xl space-y-2">

    <p class="text-sm text-gray-500 mb-6">
        Personnalisez le sujet et le corps de chaque email automatique. Utilisez les variables
        <code class="bg-gray-100 px-1 rounded text-xs font-mono">&#123;&#123;variable&#125;&#125;</code>
        pour insérer des données dynamiques.
    </p>

    @foreach($templates as $key => $tpl)
    @php
        $def = $tpl['def'];
    @endphp
    <details class="card overflow-hidden group" {{ $loop->first ? 'open' : '' }}>
        <summary class="flex items-center gap-3 px-6 py-4 cursor-pointer select-none list-none hover:bg-gray-50 transition-colors">
            <span class="text-xl">{{ $def['icon'] }}</span>
            <div class="flex-1 min-w-0">
                <div class="font-semibold text-gray-900 text-sm">{{ $def['label'] }}</div>
                <div class="text-xs text-gray-400 mt-0.5">{{ $def['desc'] }}</div>
            </div>
            <svg class="w-4 h-4 text-gray-400 transition-transform group-open:rotate-180 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </summary>

        <form method="POST" action="{{ route('admin.notifications.update', $key) }}" class="border-t border-gray-100">
            @csrf @method('PATCH')

            <div class="p-6 space-y-5">

                {{-- Variables disponibles --}}
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-2">Variables disponibles :</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($def['vars'] as $var)
                        @php $varToken = '{{' . $var . '}}'; @endphp
                        <button type="button"
                                onclick="insertVar(this, '{{ $key }}')"
                                data-var="{{ $varToken }}"
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors cursor-pointer border border-indigo-200">
                            &#123;&#123;{{ $var }}&#125;&#125;
                        </button>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">Cliquez sur une variable pour l'insérer dans le champ actif (sujet ou corps).</p>
                </div>

                {{-- Sujet --}}
                <div>
                    <label class="form-label">Sujet</label>
                    <input type="text"
                           name="subject"
                           id="subject-{{ $key }}"
                           value="{{ old('subject', $tpl['subject']) }}"
                           class="form-input font-mono text-sm"
                           placeholder="Objet de l'email…">
                </div>

                {{-- Corps --}}
                <div>
                    <label class="form-label">Corps (HTML)</label>
                    <textarea name="body"
                              id="body-{{ $key }}"
                              rows="12"
                              class="form-input font-mono text-xs leading-relaxed"
                              placeholder="<p>Votre contenu HTML…</p>">{{ old('body', $tpl['body']) }}</textarea>
                    <p class="mt-1 text-xs text-gray-400">HTML basique supporté. L'email sera enveloppé dans un template avec entête colorée et pied de page automatique.</p>
                </div>

                {{-- Aperçu rapide --}}
                <div>
                    <button type="button"
                            onclick="togglePreview('{{ $key }}')"
                            class="text-xs text-indigo-600 hover:text-indigo-800 underline">
                        Aperçu HTML
                    </button>
                    <div id="preview-{{ $key }}" class="hidden mt-3 border border-gray-200 rounded-xl overflow-hidden">
                        <div id="preview-frame-{{ $key }}" class="bg-white p-4 text-sm prose max-w-none"></div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                    <form method="POST" action="{{ route('admin.notifications.reset', $key) }}"
                          onsubmit="return confirm('Réinitialiser ce template aux valeurs par défaut ?')">
                        @csrf
                        <button type="submit" class="text-xs text-gray-400 hover:text-red-500 transition-colors">
                            Réinitialiser aux valeurs par défaut
                        </button>
                    </form>
                    <button type="submit" class="btn-primary px-5 py-2 text-sm">Enregistrer</button>
                </div>

            </div>
        </form>
    </details>
    @endforeach
</div>

<script>
let lastFocused = {};

document.querySelectorAll('[id^="subject-"], [id^="body-"]').forEach(el => {
    el.addEventListener('focus', () => {
        const parts = el.id.split('-');
        const type  = parts[0];
        const key   = parts.slice(1).join('-');
        lastFocused[key] = type;
    });
});

function insertVar(btn, key) {
    const varText = btn.dataset.var;
    const type    = lastFocused[key] || 'body';
    const el      = document.getElementById(type + '-' + key);
    if (!el) return;

    const start = el.selectionStart ?? el.value.length;
    const end   = el.selectionEnd   ?? el.value.length;
    el.value = el.value.substring(0, start) + varText + el.value.substring(end);
    el.focus();
    el.selectionStart = el.selectionEnd = start + varText.length;
}

function togglePreview(key) {
    const container = document.getElementById('preview-' + key);
    const frame     = document.getElementById('preview-frame-' + key);
    const body      = document.getElementById('body-' + key);

    if (container.classList.contains('hidden')) {
        frame.innerHTML = body.value;
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}
</script>
@endsection
