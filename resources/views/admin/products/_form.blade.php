<div class="max-w-3xl">
    <form method="POST"
          action="{{ $product ? route('admin.produits.update', $product) : route('admin.produits.store') }}"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @if($product) @method('PUT') @endif

        {{-- Informations générales --}}
        <div class="card p-6 space-y-4">
            <h2 class="font-semibold text-gray-900 pb-3 border-b border-gray-100">Informations générales</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Nom du produit *</label>
                    <input type="text" name="name" value="{{ old('name', $product?->name) }}"
                           class="form-input @error('name') border-red-500 @enderror" required>
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Catégorie *</label>
                    <select name="category_id" class="form-input @error('category_id') border-red-500 @enderror" required>
                        <option value="">Sélectionner...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $product?->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">SKU / Référence</label>
                    <input type="text" name="sku" value="{{ old('sku', $product?->sku) }}"
                           class="form-input font-mono" placeholder="REF-001">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-input">{{ old('description', $product?->description) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Prix --}}
        <div class="card p-6 space-y-4">
            <h2 class="font-semibold text-gray-900 pb-3 border-b border-gray-100">Prix</h2>
            <div class="grid grid-cols-4 gap-4">
                <div id="field-purchase-price" class="col-purchase">
                    <label class="form-label">Prix d'achat (€) *</label>
                    <input type="number" name="purchase_price" step="0.01" min="0"
                           value="{{ old('purchase_price', $product?->purchase_price) }}"
                           class="form-input @error('purchase_price') border-red-500 @enderror" required>
                    @error('purchase_price')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Prix de vente (€) *</label>
                    <input type="number" name="sale_price" step="0.01" min="0"
                           value="{{ old('sale_price', $product?->sale_price) }}"
                           class="form-input @error('sale_price') border-red-500 @enderror" required>
                    @error('sale_price')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">
                        Prix conseillé (€)
                        <span class="text-gray-400 font-normal text-xs ml-1">barré dans le catalogue</span>
                    </label>
                    <input type="number" name="suggested_price" step="0.01" min="0"
                           value="{{ old('suggested_price', $product?->suggested_price) }}"
                           class="form-input" placeholder="Facultatif">
                </div>
                <div id="field-margin" class="col-margin">
                    <label class="form-label">Marge calculée</label>
                    <div id="margin-display" class="form-input bg-gray-50 text-gray-600">—</div>
                </div>
            </div>
        </div>

        {{-- Caractéristiques --}}
        <div class="card p-6 space-y-4">
            <h2 class="font-semibold text-gray-900 pb-3 border-b border-gray-100">Caractéristiques</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Unité</label>
                    <select name="unit" class="form-input">
                        @foreach(['bouteille', 'caisse', 'pack', 'magnum', 'demi-bouteille', 'jéroboam'] as $u)
                            <option value="{{ $u }}" @selected(old('unit', $product?->unit ?? 'bouteille') === $u)>{{ ucfirst($u) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Volume (ml)</label>
                    <input type="number" name="volume_ml" value="{{ old('volume_ml', $product?->volume_ml) }}"
                           class="form-input" placeholder="750">
                </div>
                <div>
                    <label class="form-label">Degré alcool (%)</label>
                    <input type="number" name="alcohol_degree" step="0.1" min="0" max="100"
                           value="{{ old('alcohol_degree', $product?->alcohol_degree) }}"
                           class="form-input" placeholder="12.5">
                </div>
            </div>

            <div>
                <label class="form-label">Photo produit</label>
                <div id="drop-zone"
                     class="relative mt-1 flex flex-col items-center justify-center w-full rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 cursor-pointer transition-colors hover:border-indigo-400 hover:bg-indigo-50 overflow-hidden h-44"
                     onclick="document.getElementById('image-input').click()">

                    {{-- Prévisualisation --}}
                    <img id="img-preview"
                         src="{{ $product?->image ? $product->image_url : '' }}"
                         alt=""
                         class="{{ $product?->image ? '' : 'hidden' }} w-full h-full object-contain p-2">

                    {{-- Placeholder --}}
                    <div id="drop-placeholder" class="{{ $product?->image ? 'hidden' : '' }} flex flex-col items-center gap-2 py-8 pointer-events-none select-none">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p class="text-sm text-gray-500 font-medium">Glissez une image ici</p>
                        <p class="text-xs text-gray-400">ou cliquez pour parcourir</p>
                    </div>

                    {{-- Nom du fichier --}}
                    <p id="drop-filename" class="absolute bottom-2 text-xs text-gray-400"></p>
                </div>

                <input type="file" id="image-input" name="image" accept="image/*" class="hidden">
                <input type="hidden" id="image-remote-url" name="image_remote_url" value="">
                @error('image')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                @error('image_remote_url')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror

                <div class="mt-2 flex items-center gap-3 text-xs">
                    <button type="button" id="open-image-search"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                        🔍 Rechercher une image en ligne
                    </button>
                    <span id="image-remote-hint" class="hidden text-gray-500 truncate"></span>
                </div>

                {{-- Modal recherche image en ligne --}}
                <div id="image-search-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl flex flex-col max-h-[85vh]">
                        <div class="p-5 border-b border-gray-100 flex items-center justify-between gap-3">
                            <h3 class="font-semibold text-gray-900">Rechercher une image en ligne</h3>
                            <button type="button" id="close-image-search" class="text-gray-400 hover:text-gray-700 text-xl leading-none">✕</button>
                        </div>
                        <div class="p-5 border-b border-gray-100 flex gap-2">
                            <input type="text" id="image-search-query" placeholder="ex : Château Margaux 2015 75cl"
                                   value="{{ old('name', $product?->name) }}"
                                   class="flex-1 min-w-0 form-input">
                            <button type="button" id="image-search-go" class="btn-primary rounded-xl px-4 shrink-0">Chercher</button>
                        </div>
                        <div id="image-search-results" class="flex-1 overflow-y-auto p-5 min-h-[200px]">
                            <p class="text-sm text-gray-400 text-center py-10">Saisis une requête puis clique sur <strong>Chercher</strong>.</p>
                        </div>
                        <div class="p-4 border-t border-gray-100 text-xs text-gray-400 flex items-center justify-between gap-3">
                            <span>L'image sera téléchargée et sauvegardée à l'enregistrement du produit.</span>
                            <span>Source : Google Images</span>
                        </div>
                    </div>
                </div>

                <script>
                (function() {
                    var zone      = document.getElementById('drop-zone');
                    var input     = document.getElementById('image-input');
                    var preview   = document.getElementById('img-preview');
                    var placeholder = document.getElementById('drop-placeholder');
                    var filename  = document.getElementById('drop-filename');

                    function showFile(file) {
                        if (!file || !file.type.startsWith('image/')) return;
                        filename.textContent = file.name;
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                            preview.classList.remove('hidden');
                            placeholder.classList.add('hidden');
                        };
                        reader.readAsDataURL(file);
                    }

                    input.addEventListener('change', function() { showFile(this.files[0]); });

                    zone.addEventListener('dragover', function(e) {
                        e.preventDefault();
                        zone.classList.add('border-indigo-500', 'bg-indigo-50');
                    });
                    zone.addEventListener('dragleave', function() {
                        zone.classList.remove('border-indigo-500', 'bg-indigo-50');
                    });
                    zone.addEventListener('drop', function(e) {
                        e.preventDefault();
                        zone.classList.remove('border-indigo-500', 'bg-indigo-50');
                        var file = e.dataTransfer.files[0];
                        if (file) {
                            var dt = new DataTransfer();
                            dt.items.add(file);
                            input.files = dt.files;
                            showFile(file);
                        }
                    });

                    // ── Recherche image en ligne ─────────────────────
                    var modal      = document.getElementById('image-search-modal');
                    var openBtn    = document.getElementById('open-image-search');
                    var closeBtn   = document.getElementById('close-image-search');
                    var queryInput = document.getElementById('image-search-query');
                    var goBtn      = document.getElementById('image-search-go');
                    var resultsEl  = document.getElementById('image-search-results');
                    var remoteHint = document.getElementById('image-remote-hint');
                    var remoteUrl  = document.getElementById('image-remote-url');

                    function openModal() {
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                        setTimeout(function() { queryInput.focus(); queryInput.select(); }, 50);
                    }
                    function closeModal() {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }
                    openBtn.addEventListener('click', openModal);
                    closeBtn.addEventListener('click', closeModal);
                    modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
                    });

                    function escapeHtml(s) {
                        return String(s).replace(/[&<>"']/g, function(c) {
                            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];
                        });
                    }

                    function renderResults(items) {
                        if (!items.length) {
                            resultsEl.innerHTML = '<p class="text-sm text-gray-400 text-center py-10">Aucun résultat.</p>';
                            return;
                        }
                        resultsEl.innerHTML = '<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">' +
                            items.map(function(it) {
                                return '<button type="button" data-pick-url="' + escapeHtml(it.link) +
                                       '" data-pick-thumb="' + escapeHtml(it.thumbnail || it.link) +
                                       '" class="group block rounded-lg overflow-hidden border border-gray-200 hover:border-indigo-500 hover:shadow-md transition-all bg-gray-50">' +
                                       '<div class="aspect-square overflow-hidden">' +
                                       '<img src="' + escapeHtml(it.thumbnail || it.link) + '" alt="" loading="lazy" class="w-full h-full object-contain group-hover:scale-105 transition-transform">' +
                                       '</div>' +
                                       '<div class="px-2 py-1.5 text-[10px] text-gray-500 truncate text-left">' + escapeHtml(it.title || '') + '</div>' +
                                       '</button>';
                            }).join('') + '</div>';
                    }

                    function runSearch() {
                        var q = (queryInput.value || '').trim();
                        if (q.length < 2) return;
                        resultsEl.innerHTML = '<p class="text-sm text-gray-400 text-center py-10">Recherche en cours…</p>';
                        var token = document.querySelector('meta[name="csrf-token"]').content;
                        fetch('{{ route('admin.products.image-search') }}?q=' + encodeURIComponent(q), {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                        })
                        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
                        .then(function(res) {
                            if (!res.ok) {
                                resultsEl.innerHTML = '<p class="text-sm text-red-600 text-center py-10">⚠ ' + escapeHtml(res.data.error || 'Erreur de recherche.') + '</p>';
                                return;
                            }
                            renderResults(res.data.items || []);
                        })
                        .catch(function() {
                            resultsEl.innerHTML = '<p class="text-sm text-red-600 text-center py-10">⚠ Erreur réseau.</p>';
                        });
                    }
                    goBtn.addEventListener('click', runSearch);
                    queryInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') { e.preventDefault(); runSearch(); }
                    });

                    resultsEl.addEventListener('click', function(e) {
                        var btn = e.target.closest('[data-pick-url]');
                        if (!btn) return;
                        e.preventDefault();
                        var url   = btn.dataset.pickUrl;
                        var thumb = btn.dataset.pickThumb || url;
                        // Vider l'éventuel upload fichier pour ne pas créer de conflit
                        input.value = '';
                        remoteUrl.value = url;
                        preview.src = thumb;
                        preview.classList.remove('hidden');
                        placeholder.classList.add('hidden');
                        filename.textContent = '';
                        remoteHint.textContent = '🌐 ' + url;
                        remoteHint.classList.remove('hidden');
                        closeModal();
                    });
                })();
                </script>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                       @checked(old('is_active', $product?->is_active ?? true))>
                <label for="is_active" class="text-sm font-medium text-gray-700">
                    Produit actif (visible dans le catalogue client)
                </label>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_new" id="is_new" value="1"
                       class="rounded border-gray-300 text-amber-500 focus:ring-amber-500"
                       @checked(old('is_new', $product?->is_new ?? false))>
                <label for="is_new" class="text-sm font-medium text-gray-700 flex items-center gap-2">
                    Afficher le badge
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-400 text-white tracking-wide">NEW</span>
                    sur ce produit
                </label>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary px-6 py-2.5">
                {{ $product ? 'Enregistrer les modifications' : 'Créer le produit' }}
            </button>
            <a href="{{ route('admin.produits.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
        </div>
    </form>
</div>

