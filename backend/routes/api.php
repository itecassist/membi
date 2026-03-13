<?php

use App\Http\Controllers\Api\AccountingCodeController;
use App\Http\Controllers\Api\Admin\OrganisationSuperAdminController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\ArticleCategoryController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\EmailTemplateController;
use App\Http\Controllers\Api\FaqCategoryController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrganisationConfigController;
use App\Http\Controllers\Api\OrganisationController;
use App\Http\Controllers\Api\OrganisationListController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TaxRateController;
use App\Http\Controllers\Api\VatController;
use App\Http\Controllers\Api\VirtualFormController;
use App\Http\Controllers\Api\VirtualRecordController;
use Illuminate\Support\Facades\Route;

// ── Reference data (public — no auth) ───────────────────────────────────────
Route::get('countries',                        [CountryController::class, 'index']);
Route::get('countries/{country}',              [CountryController::class, 'show']);
Route::get('countries/{country}/zones',        [CountryController::class, 'zones']);

// ── Unauthenticated payment gateway webhooks ─────────────────────────────────
// These must be outside auth middleware — gateways post here without tokens.
Route::prefix('webhooks')->group(function () {
    Route::post('gocardless/{organisation}', [PaymentWebhookController::class, 'gocardless']);
    Route::post('worldpay/{organisation}',   [PaymentWebhookController::class, 'worldpay']);
});

// ── Auth ─────────────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me',            [AuthController::class, 'me']);
        Route::get('organisations', [AuthController::class, 'organisations']);
        Route::post('logout',       [AuthController::class, 'logout']);
        Route::post('logout-all',   [AuthController::class, 'logoutAll']);
    });
});

// ── Membix Admin ─────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'membix.admin'])->prefix('admin')->group(function () {

    // Permission management (Membix admins only)
    Route::apiResource('permissions', PermissionController::class);
    Route::patch('permissions/{permission}/toggle-visibility', [PermissionController::class, 'toggleVisibility']);

    // super-admin assignment per organisation (Membix admins only)
    Route::get('organisations/{organisation}/super-admins',             [OrganisationSuperAdminController::class, 'index']);
    Route::post('organisations/{organisation}/super-admins',            [OrganisationSuperAdminController::class, 'store']);
    Route::delete('organisations/{organisation}/super-admins/{member}', [OrganisationSuperAdminController::class, 'destroy']);

    // Content management (admin write)
    Route::post('article-categories',                         [ArticleCategoryController::class, 'store']);
    Route::put('article-categories/{articleCategory}',        [ArticleCategoryController::class, 'update']);
    Route::delete('article-categories/{articleCategory}',     [ArticleCategoryController::class, 'destroy']);
    Route::post('articles',                                   [ArticleController::class, 'store']);
    Route::put('articles/{article}',                          [ArticleController::class, 'update']);
    Route::delete('articles/{article}',                       [ArticleController::class, 'destroy']);
    Route::post('faq-categories',                             [FaqCategoryController::class, 'store']);
    Route::put('faq-categories/{faqCategory}',                [FaqCategoryController::class, 'update']);
    Route::delete('faq-categories/{faqCategory}',             [FaqCategoryController::class, 'destroy']);
    Route::post('faqs',                                       [FaqController::class, 'store']);
    Route::put('faqs/{faq}',                                  [FaqController::class, 'update']);
    Route::delete('faqs/{faq}',                               [FaqController::class, 'destroy']);

    // Virtual forms (admin manage)
    Route::post('virtual-forms',                                                   [VirtualFormController::class, 'store']);
    Route::put('virtual-forms/{virtualForm}',                                      [VirtualFormController::class, 'update']);
    Route::delete('virtual-forms/{virtualForm}',                                   [VirtualFormController::class, 'destroy']);
    Route::post('virtual-forms/{virtualForm}/fields',                              [VirtualFormController::class, 'storeField']);
    Route::put('virtual-forms/{virtualForm}/fields/{virtualField}',                [VirtualFormController::class, 'updateField']);
    Route::delete('virtual-forms/{virtualForm}/fields/{virtualField}',             [VirtualFormController::class, 'destroyField']);

    // Tax rates (platform-level configuration)
    Route::post('tax-rates',                                  [TaxRateController::class, 'store']);
    Route::put('tax-rates/{taxRate}',                         [TaxRateController::class, 'update']);
    Route::delete('tax-rates/{taxRate}',                      [TaxRateController::class, 'destroy']);
});

// ── Protected API v1 ─────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Organisations (no org.team/org.member needed — no {organisation} param here)
    Route::apiResource('organisations', OrganisationController::class);

    // ── Reference / content reads (auth required, no org scope) ───────────────
    Route::get('tax-rates',                                      [TaxRateController::class, 'index']);
    Route::get('tax-rates/{taxRate}',                            [TaxRateController::class, 'show']);
    Route::get('virtual-forms',                                  [VirtualFormController::class, 'index']);
    Route::get('virtual-forms/{virtualForm}',                    [VirtualFormController::class, 'show']);
    Route::get('virtual-forms/{virtualForm}/fields',             [VirtualFormController::class, 'indexFields']);
    Route::get('article-categories',                             [ArticleCategoryController::class, 'index']);
    Route::get('article-categories/{articleCategory}',           [ArticleCategoryController::class, 'show']);
    Route::get('article-categories/{articleCategory}/articles',  [ArticleCategoryController::class, 'articles']);
    Route::get('articles/{article}',                             [ArticleController::class, 'show']);
    Route::get('faq-categories',                                 [FaqCategoryController::class, 'index']);
    Route::get('faq-categories/{faqCategory}',                   [FaqCategoryController::class, 'show']);
    Route::get('faq-categories/{faqCategory}/faqs',              [FaqCategoryController::class, 'faqs']);
    Route::get('faqs/{faq}',                                     [FaqController::class, 'show']);

    // ── Org-scoped routes (team context + member resolved automatically) ────────
    Route::middleware(['org.team', 'org.member'])
        ->prefix('organisations/{organisation}')
        ->group(function () {

            // Permissions visible to this organisation (for building custom roles)
            Route::get('available-permissions', function () {
                $permissions = \Spatie\Permission\Models\Permission::where('visible_to_organisations', true)
                    ->orderBy('name')
                    ->get();

                return \App\Http\Resources\PermissionResource::collection($permissions);
            });

            // Roles
            Route::get('roles',                               [RoleController::class, 'index']);
            Route::post('roles',                              [RoleController::class, 'store']);
            Route::get('roles/{role}',                        [RoleController::class, 'show']);
            Route::put('roles/{role}',                        [RoleController::class, 'update']);
            Route::delete('roles/{role}',                     [RoleController::class, 'destroy']);
            Route::put('roles/{role}/permissions',            [RoleController::class, 'syncPermissions']);
            Route::post('roles/{role}/members',               [RoleController::class, 'assignToMember']);
            Route::delete('roles/{role}/members',             [RoleController::class, 'removeFromMember']);

            // Members
            Route::get('members',                             [MemberController::class, 'index']);
            Route::post('members',                            [MemberController::class, 'store']);
            Route::get('members/{member}',                    [MemberController::class, 'show']);
            Route::put('members/{member}',                    [MemberController::class, 'update']);
            Route::delete('members/{member}',                 [MemberController::class, 'destroy']);

            // Groups
            Route::get('groups',                              [GroupController::class, 'index']);
            Route::post('groups',                             [GroupController::class, 'store']);
            Route::get('groups/{group}',                      [GroupController::class, 'show']);
            Route::put('groups/{group}',                      [GroupController::class, 'update']);
            Route::delete('groups/{group}',                   [GroupController::class, 'destroy']);
            Route::get('groups/{group}/members',              [GroupController::class, 'members']);
            Route::post('groups/{group}/members',             [GroupController::class, 'addMember']);
            Route::delete('groups/{group}/members/{memberId}', [GroupController::class, 'removeMember']);

            // Subscriptions + price options
            Route::get('subscriptions/stats',                 [SubscriptionController::class, 'stats']);
            Route::get('subscriptions',                       [SubscriptionController::class, 'index']);
            Route::post('subscriptions',                      [SubscriptionController::class, 'store']);
            Route::get('subscriptions/{subscription}',        [SubscriptionController::class, 'show']);
            Route::put('subscriptions/{subscription}',        [SubscriptionController::class, 'update']);
            Route::delete('subscriptions/{subscription}',     [SubscriptionController::class, 'destroy']);
            Route::get('subscriptions/{subscription}/price-options',               [SubscriptionController::class, 'priceOptions']);
            Route::post('subscriptions/{subscription}/price-options',              [SubscriptionController::class, 'storePriceOption']);
            Route::put('subscriptions/{subscription}/price-options/{priceOption}', [SubscriptionController::class, 'updatePriceOption']);
            Route::delete('subscriptions/{subscription}/price-options/{priceOption}', [SubscriptionController::class, 'destroyPriceOption']);

            // Subscription renewal + auto-renewal settings
            Route::get('subscriptions/{subscription}/renewal-settings',                          [SubscriptionController::class, 'showRenewalSettings']);
            Route::put('subscriptions/{subscription}/renewal-settings',                          [SubscriptionController::class, 'updateRenewalSettings']);
            Route::get('subscriptions/{subscription}/auto-renewal-settings',                     [SubscriptionController::class, 'showAutoRenewalSettings']);
            Route::put('subscriptions/{subscription}/auto-renewal-settings',                     [SubscriptionController::class, 'updateAutoRenewalSettings']);
            Route::get('subscriptions/{subscription}/price-options/{priceOption}/new-member-settings',    [SubscriptionController::class, 'showNewMemberSettings']);
            Route::put('subscriptions/{subscription}/price-options/{priceOption}/new-member-settings',    [SubscriptionController::class, 'updateNewMemberSettings']);
            Route::get('subscriptions/{subscription}/price-options/{priceOption}/late-fees',              [SubscriptionController::class, 'indexLateFees']);
            Route::post('subscriptions/{subscription}/price-options/{priceOption}/late-fees',             [SubscriptionController::class, 'storeLateFee']);
            Route::delete('subscriptions/{subscription}/price-options/{priceOption}/late-fees/{lateFee}', [SubscriptionController::class, 'destroyLateFee']);

            // ── Organisation config ───────────────────────────────────────────
            Route::get('config/member',                       [OrganisationConfigController::class, 'showMember']);
            Route::put('config/member',                       [OrganisationConfigController::class, 'updateMember']);
            Route::get('config/subscription',                 [OrganisationConfigController::class, 'showSubscription']);
            Route::put('config/subscription',                 [OrganisationConfigController::class, 'updateSubscription']);
            Route::get('config/financial',                    [OrganisationConfigController::class, 'showFinancial']);
            Route::put('config/financial',                    [OrganisationConfigController::class, 'updateFinancial']);

            // Financial sub-resources
            Route::get('accounting-codes',                    [AccountingCodeController::class, 'index']);
            Route::post('accounting-codes',                   [AccountingCodeController::class, 'store']);
            Route::get('accounting-codes/{accountingCode}',   [AccountingCodeController::class, 'show']);
            Route::put('accounting-codes/{accountingCode}',   [AccountingCodeController::class, 'update']);
            Route::delete('accounting-codes/{accountingCode}', [AccountingCodeController::class, 'destroy']);
            Route::get('vats',                                [VatController::class, 'index']);
            Route::post('vats',                               [VatController::class, 'store']);
            Route::get('vats/{vat}',                          [VatController::class, 'show']);
            Route::put('vats/{vat}',                          [VatController::class, 'update']);
            Route::delete('vats/{vat}',                       [VatController::class, 'destroy']);

            // Lookups and lists
            Route::get('lookups',                             [LookupController::class, 'index']);
            Route::post('lookups',                            [LookupController::class, 'store']);
            Route::get('lookups/{lookup}',                    [LookupController::class, 'show']);
            Route::put('lookups/{lookup}',                    [LookupController::class, 'update']);
            Route::delete('lookups/{lookup}',                 [LookupController::class, 'destroy']);
            Route::get('lists',                               [OrganisationListController::class, 'index']);
            Route::post('lists',                              [OrganisationListController::class, 'store']);
            Route::get('lists/{list}',                        [OrganisationListController::class, 'show']);
            Route::put('lists/{list}',                        [OrganisationListController::class, 'update']);
            Route::delete('lists/{list}',                     [OrganisationListController::class, 'destroy']);

            // Communication
            Route::get('email-templates',                     [EmailTemplateController::class, 'index']);
            Route::post('email-templates',                    [EmailTemplateController::class, 'store']);
            Route::get('email-templates/{emailTemplate}',     [EmailTemplateController::class, 'show']);
            Route::put('email-templates/{emailTemplate}',     [EmailTemplateController::class, 'update']);
            Route::delete('email-templates/{emailTemplate}',  [EmailTemplateController::class, 'destroy']);

            // Virtual records (org form submissions)
            Route::get('virtual-records',                     [VirtualRecordController::class, 'index']);
            Route::post('virtual-records',                    [VirtualRecordController::class, 'store']);
            Route::get('virtual-records/{virtualRecord}',     [VirtualRecordController::class, 'show']);
            Route::put('virtual-records/{virtualRecord}',     [VirtualRecordController::class, 'update']);
            Route::delete('virtual-records/{virtualRecord}',  [VirtualRecordController::class, 'destroy']);

            // Product categories and products
            Route::get('product-categories',                                        [ProductCategoryController::class, 'index']);
            Route::post('product-categories',                                       [ProductCategoryController::class, 'store']);
            Route::get('product-categories/{productCategory}',                      [ProductCategoryController::class, 'show']);
            Route::put('product-categories/{productCategory}',                      [ProductCategoryController::class, 'update']);
            Route::delete('product-categories/{productCategory}',                   [ProductCategoryController::class, 'destroy']);
            Route::get('products',                                                  [ProductController::class, 'index']);
            Route::post('products',                                                 [ProductController::class, 'store']);
            Route::get('products/{product}',                                        [ProductController::class, 'show']);
            Route::put('products/{product}',                                        [ProductController::class, 'update']);
            Route::delete('products/{product}',                                     [ProductController::class, 'destroy']);
            Route::get('products/{product}/options',                                [ProductController::class, 'indexOptions']);
            Route::post('products/{product}/options',                               [ProductController::class, 'storeOption']);
            Route::put('products/{product}/options/{option}',                       [ProductController::class, 'updateOption']);
            Route::delete('products/{product}/options/{option}',                    [ProductController::class, 'destroyOption']);

            // ── Payment Methods (admin management) ───────────────────────────
            Route::get('payment-methods',                         [PaymentMethodController::class, 'index']);
            Route::post('payment-methods',                        [PaymentMethodController::class, 'store']);
            Route::get('payment-methods/{paymentMethod}',         [PaymentMethodController::class, 'show']);
            Route::put('payment-methods/{paymentMethod}',         [PaymentMethodController::class, 'update']);
            Route::delete('payment-methods/{paymentMethod}',      [PaymentMethodController::class, 'destroy']);

            // ── Orders (read-only; created via checkout flow) ────────────
            Route::get('orders',              [OrderController::class, 'index']);
            Route::get('orders/{order}',      [OrderController::class, 'show']);

            // ── Checkout ─────────────────────────────────────────────────────
            Route::post('baskets',                            [CheckoutController::class, 'createBasket']);
            Route::get('baskets/{basket}',                    [CheckoutController::class, 'showBasket']);
            Route::post('baskets/{basket}/items',             [CheckoutController::class, 'addItem']);
            Route::delete('baskets/{basket}/items/{item}',    [CheckoutController::class, 'removeItem']);
            Route::post('baskets/{basket}/checkout',          [CheckoutController::class, 'startCheckout']);
            Route::post('baskets/{basket}/checkout/email',    [CheckoutController::class, 'captureEmail']);
            Route::put('baskets/{basket}/checkout/allocations', [CheckoutController::class, 'setAllocations']);
            Route::put('baskets/{basket}/checkout/forms',     [CheckoutController::class, 'setFormData']);
            Route::put('baskets/{basket}/checkout/payment-method', [CheckoutController::class, 'setPaymentMethod']);
            Route::post('baskets/{basket}/checkout/order',    [CheckoutController::class, 'createOrder']);
            Route::post('baskets/{basket}/checkout/finalize', [CheckoutController::class, 'finalizeCheckout']);

            // ── Payments ─────────────────────────────────────────────────────
            Route::post('order-payments/{payment}/initiate',              [PaymentController::class, 'initiate']);
            Route::get('members/{memberId}/payment-methods',              [PaymentController::class, 'memberMethods']);
            Route::delete('member-payment-methods/{memberMethod}',        [PaymentController::class, 'cancelMemberMethod']);
            Route::post('gateway-configs',                                [PaymentController::class, 'storeGatewayConfig']);
        });
});
