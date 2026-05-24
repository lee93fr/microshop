<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\Client;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('catalog.index'));
Route::middleware(['auth', 'user.active'])->group(function () {
    Route::get('/catalogue', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/catalogue/{product:slug}', [CatalogController::class, 'show'])->name('catalog.show');
});

// ── Webhook Stripe (hors CSRF) ────────────────────────────────
Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])
    ->name('webhooks.stripe');

// ── Invitations (public) ──────────────────────────────────────
Route::get('/invitation/{token}', [\App\Http\Controllers\Auth\InvitationController::class, 'show'])->name('invitation.show');
Route::post('/invitation/{token}', [\App\Http\Controllers\Auth\InvitationController::class, 'accept'])->name('invitation.accept');

// ── Auth (Breeze) ─────────────────────────────────────────────
require __DIR__.'/auth.php';

// ── Changement de mot de passe forcé ──────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/mot-de-passe-requis', [\App\Http\Controllers\Auth\ForcedPasswordController::class, 'show'])->name('password.forced.show');
    Route::post('/mot-de-passe-requis', [\App\Http\Controllers\Auth\ForcedPasswordController::class, 'update'])->name('password.forced.update');
});

// ── Vérification changement d'email (lien signé) ──────────────
Route::get('/email/verifier-changement/{id}', [\App\Http\Controllers\Fournisseur\ProfileController::class, 'verifyEmailChange'])
    ->name('profile.verify-email-change');

// ── Client ────────────────────────────────────────────────────
Route::middleware(['auth', 'user.active', 'role:client,admin,super_admin'])
    ->prefix('mon-compte')
    ->name('client.')
    ->group(function () {

    // Panier
    Route::get('/panier', [Client\CartController::class, 'index'])->name('cart');
    Route::post('/panier/{product}', [Client\CartController::class, 'add'])->name('cart.add');
    Route::patch('/panier/{productId}', [Client\CartController::class, 'update'])->name('cart.update');
    Route::delete('/panier/{productId}', [Client\CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/panier', [Client\CartController::class, 'clear'])->name('cart.clear');

    // Code promo
    Route::post('/panier/code-promo', [Client\CartController::class, 'applyPromo'])->name('cart.promo.apply');
    Route::delete('/panier/code-promo', [Client\CartController::class, 'removePromo'])->name('cart.promo.remove');

    // Checkout
    Route::get('/checkout', [Client\OrderController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [Client\OrderController::class, 'store'])->name('checkout.store');

    // Commandes
    Route::get('/commandes', [Client\OrderController::class, 'index'])->name('orders.index');
    Route::get('/commandes/{order}', [Client\OrderController::class, 'show'])->name('orders.show');
    Route::post('/commandes/{order}/annuler', [Client\OrderController::class, 'cancel'])->name('orders.cancel');

    // Profil
    Route::get('/profil', [Client\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [Client\ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profil/mot-de-passe', [Client\ProfileController::class, 'updatePassword'])->name('profile.password');
});

// ── Admin ─────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin,super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Produits
    Route::get('produits/import', [Admin\ProductController::class, 'importForm'])->name('products.import.form');
    Route::post('produits/import', [Admin\ProductController::class, 'import'])->name('products.import');
    Route::get('produits/export', [Admin\ProductController::class, 'export'])->name('products.export');
    Route::get('produits/catalogue-pdf', [Admin\ProductController::class, 'catalogPdf'])->name('products.catalog-pdf');
    Route::get('produits/image-search', [Admin\ImageSearchController::class, 'search'])->name('products.image-search');
    Route::patch('produits/{product}/toggle-active', [Admin\ProductController::class, 'toggleActive'])->name('products.toggle-active');
    Route::patch('produits/{product}/toggle-new', [Admin\ProductController::class, 'toggleNew'])->name('products.toggle-new');
    Route::patch('produits/{product}/quick-update', [Admin\ProductController::class, 'quickUpdate'])->name('products.quick-update');
    Route::resource('produits', Admin\ProductController::class)
         ->parameters(['produits' => 'product']);

    // Catégories
    Route::resource('categories', Admin\CategoryController::class);

    // Codes promo
    Route::get('codes-promo', [Admin\PromoCodeController::class, 'index'])->name('promo-codes.index');
    Route::post('codes-promo', [Admin\PromoCodeController::class, 'store'])->name('promo-codes.store');
    Route::patch('codes-promo/{promoCode}', [Admin\PromoCodeController::class, 'update'])->name('promo-codes.update');
    Route::patch('codes-promo/{promoCode}/toggle', [Admin\PromoCodeController::class, 'toggle'])->name('promo-codes.toggle');
    Route::delete('codes-promo/{promoCode}', [Admin\PromoCodeController::class, 'destroy'])->name('promo-codes.destroy');

    // Commandes
    Route::get('commandes/{order}/bon-livraison', [Admin\OrderController::class, 'deliveryNote'])->name('orders.delivery-note');
    Route::get('commandes/{order}/modifier', [Admin\OrderController::class, 'editItems'])->name('orders.edit-items');
    Route::patch('commandes/{order}/modifier', [Admin\OrderController::class, 'updateItems'])->name('orders.update-items');
    Route::patch('commandes/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::patch('commandes/{order}/payment', [Admin\OrderController::class, 'updatePayment'])->name('orders.update-payment');
    Route::post('commandes/{order}/stripe-link', [Admin\OrderController::class, 'generateStripeLink'])->name('orders.stripe-link');
    Route::delete('commandes/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('commandes/purge', [Admin\OrderController::class, 'purge'])->name('orders.purge');
    Route::resource('commandes', Admin\OrderController::class)
         ->parameters(['commandes' => 'order'])
         ->except(['edit', 'update', 'destroy']);

    // Utilisateurs
    Route::get('utilisateurs', [Admin\UserController::class, 'index'])->name('users.index');
    Route::patch('utilisateurs/{user}/toggle-active', [Admin\UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::patch('utilisateurs/{user}/role', [Admin\UserController::class, 'updateRole'])->name('users.update-role');
    Route::patch('utilisateurs/{user}/email', [Admin\UserController::class, 'updateEmail'])->name('users.update-email');
    Route::post('utilisateurs/{user}/forcer-mot-de-passe', [Admin\UserController::class, 'forcePasswordChange'])->name('users.force-password');
    Route::delete('utilisateurs/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::post('utilisateurs/inviter', [Admin\InvitationController::class, 'store'])->name('users.invite');
    Route::post('utilisateurs/{user}/reinviter', [Admin\InvitationController::class, 'resend'])->name('users.reinvite');

    // Notifications / Templates email
    Route::get('notifications', [Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{key}', [Admin\NotificationController::class, 'update'])->name('notifications.update');
    Route::post('notifications/{key}/reset', [Admin\NotificationController::class, 'reset'])->name('notifications.reset');

    // Bons fournisseur
    Route::get('bons-fournisseur/{supplierOrder}/pdf', [Admin\SupplierOrderController::class, 'downloadPdf'])->name('supplier-orders.pdf');
    Route::post('bons-fournisseur/{supplierOrder}/send', [Admin\SupplierOrderController::class, 'send'])->name('supplier-orders.send');
    Route::post('bons-fournisseur/{supplierOrder}/confirm', [Admin\SupplierOrderController::class, 'confirm'])->name('supplier-orders.confirm');
    Route::delete('bons-fournisseur/{supplierOrder}', [Admin\SupplierOrderController::class, 'destroy'])->name('supplier-orders.destroy');
    Route::resource('bons-fournisseur', Admin\SupplierOrderController::class)
         ->parameters(['bons-fournisseur' => 'supplierOrder'])
         ->names([
             'index'  => 'supplier-orders.index',
             'create' => 'supplier-orders.create',
             'store'  => 'supplier-orders.store',
             'show'   => 'supplier-orders.show',
         ])
         ->except(['edit', 'update', 'destroy']);
});

// ── Fournisseur ───────────────────────────────────────────────
Route::middleware(['auth', 'role:fournisseur,admin,super_admin'])
    ->prefix('fournisseur')
    ->name('fournisseur.')
    ->group(function () {
        Route::get('/', fn () => redirect()->route('fournisseur.products.index'));

        // Profil fournisseur
        Route::get('profil', [\App\Http\Controllers\Fournisseur\ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profil', [\App\Http\Controllers\Fournisseur\ProfileController::class, 'update'])->name('profile.update');
        Route::patch('profil/mot-de-passe', [\App\Http\Controllers\Fournisseur\ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::patch('profil/email', [\App\Http\Controllers\Fournisseur\ProfileController::class, 'updateEmail'])->name('profile.email');

        Route::get('produits', [\App\Http\Controllers\Fournisseur\ProductController::class, 'index'])->name('products.index');
        Route::get('produits/import', [\App\Http\Controllers\Fournisseur\ProductController::class, 'importForm'])->name('products.import.form');
        Route::post('produits/import', [\App\Http\Controllers\Fournisseur\ProductController::class, 'import'])->name('products.import');
        Route::get('produits/export', [\App\Http\Controllers\Fournisseur\ProductController::class, 'export'])->name('products.export');
        Route::get('produits/catalogue-pdf', [\App\Http\Controllers\Fournisseur\ProductController::class, 'catalogPdf'])->name('products.catalog-pdf');
        Route::get('produits/nouveau', [\App\Http\Controllers\Fournisseur\ProductController::class, 'create'])->name('products.create');
        Route::post('produits', [\App\Http\Controllers\Fournisseur\ProductController::class, 'store'])->name('products.store');
        Route::get('produits/{product}/modifier', [\App\Http\Controllers\Fournisseur\ProductController::class, 'edit'])->name('products.edit');
        Route::patch('produits/{product}', [\App\Http\Controllers\Fournisseur\ProductController::class, 'update'])->name('products.update');
        Route::post('produits/{product}/stock', [\App\Http\Controllers\Fournisseur\ProductController::class, 'toggleStock'])->name('products.toggle-stock');
        Route::post('produits/{product}/new', [\App\Http\Controllers\Fournisseur\ProductController::class, 'toggleNew'])->name('products.toggle-new');
        Route::patch('produits/{product}/quick-update', [\App\Http\Controllers\Fournisseur\ProductController::class, 'quickUpdate'])->name('products.quick-update');
    });

// ── Super Admin ───────────────────────────────────────────────
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('admin/parametres')
    ->name('admin.settings.')
    ->group(function () {
    Route::get('/', [SettingsController::class, 'edit'])->name('edit');
    Route::patch('/', [SettingsController::class, 'update'])->name('update');
    Route::post('/test-smtp', [SettingsController::class, 'testSmtp'])->name('test-smtp');
});
