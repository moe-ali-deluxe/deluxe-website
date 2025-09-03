<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Models\Product; // 👈 needed for the 301 redirect from old IDs
use App\Http\Controllers\{
    CartController,
    ProductController,
    HomeController,
    ContactController,
    ProfileController,
    CheckoutController,
    ProductImageController,
    CategoryController,
    WishlistController,
    OrderController,
    NewsletterController,
    PageController
};
use App\Http\Controllers\Admin\{
    AdminDashboardController,
    AdminController,
    VendorController,
    BrandController
};
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\SitemapController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// About page
Route::get('/about', fn () => view('about'))->name('about');

// Categories
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show');

// Public Categories (grid of all categories) — added 30-8-2025
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

// Returns page
Route::get('/returns', [PageController::class, 'returns'])->name('returns');

//sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap.xml');
// Newsletter subscription
Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.subscribe');

// Public product browsing
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

// ✅ New pretty product URL by slug (keeps the same route name)
Route::get('/p/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// ♻️ Backward-compat: 301 redirect old numeric URLs → new slug URL
Route::get('/products/{id}', function ($id) {
    $product = Product::findOrFail($id);
    // Redirect to /p/{slug}
    return redirect()->route('products.show', $product)->setStatusCode(301);
})->whereNumber('id'); // important: only match numeric IDs, not /products/category/*

// Product filters
Route::get('/products/category/{slug}', [ProductController::class, 'productsByCategory'])->name('products.byCategory');
// ->where('slug', '[A-Za-z0-9\-]+')
Route::get('/products/brand/{brandSlug}', [ProductController::class, 'byBrand'])->name('products.byBrand');
Route::get('/products/vendor/{vendor}', [ProductController::class, 'productsByVendor'])->name('products.byVendor');

// Product import (⚠️ maybe should be admin-only, but left public for now)
Route::get('/products/import', fn () => view('products.import'))->name('products.import.form');
Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
Route::post('/products/import-images', [ProductController::class, 'importProductsWithImages'])->name('products.import.images');

// Contact
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Thank you page
Route::get('/checkout/thank-you/{oid}', [CheckoutController::class, 'thankYou'])
    ->middleware('signed')
    ->name('checkout.thankyou');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}/add', [WishlistController::class, 'add'])->name('wishlist.add');
    Route::delete('/wishlist/{product}/remove', [WishlistController::class, 'remove'])->name('wishlist.remove');

    // Optional: single-button UX (toggle)
    Route::post('/wishlist/{product}/toggle', [WishlistController::class, 'toggle'])
        ->whereNumber('product')->name('wishlist.toggle');

    // Orders
    Route::get('/my-orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/my-orders/completed', [OrderController::class, 'completed'])->name('orders.completed');
    Route::get('/my-orders/cancelled', [OrderController::class, 'cancelled'])->name('orders.cancelled');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // User Profile & Dashboard
    Route::middleware('verified')->group(function () {
        Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Cart Routes
|--------------------------------------------------------------------------
*/
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

/*
|--------------------------------------------------------------------------
| Admin Routes (protected by AdminMiddleware)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', AdminMiddleware::class])->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Product CRUD
    Route::get('/products', [ProductController::class, 'adminIndex'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Quick stock update
    Route::post('/products/{product}/update-stock', [ProductController::class, 'updateStock'])->name('products.updateStock');

    // Product images
    Route::delete('/products/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Vendors
    Route::resource('vendors', VendorController::class);

    // Brands
    Route::resource('brands', BrandController::class);

    // Quick inline update of product (category, brand, price, discount_price, stock, is_active)
    Route::put('/products/{product}/quick-update', [ProductController::class, 'quickUpdate'])
        ->name('products.quickUpdate');

    // Payments CRUD
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
    Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
});

/*
|--------------------------------------------------------------------------
| Debug / Test Routes
|--------------------------------------------------------------------------
*/
Route::get('/send-test-email', function () {
    try {
        Mail::raw('This is a test email sent from Laravel using Gmail SMTP.', function ($message) {
            $message->to('deluxeplusmohammad@gmail.com')->subject('Laravel Gmail SMTP Test');
        });
        return '✅ Email sent successfully!';
    } catch (\Exception $e) {
        return '❌ Failed to send email: ' . $e->getMessage();
    }
});
Route::get('/check-mail-config', fn () => config('mail.mailers.smtp.password') ? 'Password loaded' : 'Password not loaded');
Route::get('/whatsapp-test', [CheckoutController::class, 'sendWhatsAppTest']);

/*
|--------------------------------------------------------------------------
| Auth Scaffolding (Laravel Breeze / Jetstream / UI)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';
