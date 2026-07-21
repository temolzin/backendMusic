<?php

use App\Http\Controllers\Admin\MusicalsGenders;
use App\Http\Controllers\Admin\MusicalsGendersController;
use App\Http\Controllers\Artist\ArtistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuotationsController;
use App\Http\Controllers\Client\CardController;
use App\Http\Controllers\Client\FavouriteArtist\FavouriteArtistsController;
use App\Http\Controllers\Client\PaymentController as ClientPaymentController;
use App\Http\Controllers\Client\GendersController;
use App\Http\Controllers\Client\ShoppingCart\ShoppingCardController;
use App\Http\Controllers\General\ArtistsGeneralController;
use App\Http\Controllers\PermissionsApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RolesApiController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\UserApiController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UsersSubscribeController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Artist\ArtistSalesController;
use App\Http\Controllers\Artist\OfferController;
use App\Http\Controllers\ArtistRatingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Admin\DashboardStatsController;
use App\Http\Controllers\Admin\OpenpayKeysController;
use App\Http\Controllers\GoogleMapsController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\ArtistPayoutMethodController;
use App\Http\Controllers\Admin\AdminPayoutController;
use App\Http\Controllers\Artist\ApprovalController;
use App\Http\Controllers\Admin\UserSanctionController;
use App\Http\Middleware\CheckAccountStatus;
use App\Http\Controllers\WebhookController;

// Routes for login without sesion
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [UsersController::class, 'create']);
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [ForgotPasswordController::class, 'resetPassword']);
Route::get('/refresh', [AuthController::class, 'refresh']);
// Routes for login with google
Route::get('/authorize/google/redirect', [SocialAuthController::class, 'redirectToProvider']);
Route::get('/authorize/google/callback', [SocialAuthController::class, 'handlesProviderCallback']);
// Routes for login with facebook
Route::get('/authorize/facebook/redirect', [SocialAuthController::class, 'redirectToFacebookProvider']);
Route::get('/authorize/facebook/callback', [SocialAuthController::class, 'handleFacebookProviderCallback']);
Route::group(["middleware" => ["auth:api", CheckAccountStatus::class]], function () {
    Route::get('/me', [UsersController::class, 'me']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::put('/user/change-details', [UsersController::class, 'updateDetails']);
    Route::put('/user/change-password', [UsersController::class, 'updatePassword']);
    Route::post('/user/change-image-profile', [UsersController::class, 'updateImageProfile']);
    Route::put('/user/dark-mode', [UsersController::class, 'updateDarkMode']);
    
    //Route for admin
    Route::resource('/admin/users', UserApiController::class);
    Route::resource('/admin/roles', RolesApiController::class);
    Route::resource('/admin/permissions', PermissionsApiController::class);
    Route::resource('/admin/musical-genders', MusicalsGendersController::class);
    Route::get('/admin/dashboard-overview', [DashboardStatsController::class, 'index']);
    Route::get('/admin/payouts/pending', [AdminPayoutController::class, 'pendingPayouts']);
    Route::post('/admin/payouts/{saleId}/release', [AdminPayoutController::class, 'releasePayout']);
    Route::get('/admin/user-sanctions', [UserSanctionController::class, 'index']);
    Route::post('/admin/user-sanctions', [UserSanctionController::class, 'store']);
    Route::get('/admin/user-sanctions/{id}/tickets', [UserSanctionController::class, 'getUserTickets']);
    Route::get('/admin/user-sanctions/{id}', [UserSanctionController::class, 'show']);
    Route::put('/admin/user-sanctions/{id}/revoke', [UserSanctionController::class, 'revoke']);
    Route::post('/user-sanctions/evaluate-cancellation', [UserSanctionController::class, 'evaluateCancellation']);

    //Route for artist
    Route::post('/artist-new/up-date/{id}', [ArtistController::class, 'updateDetails']);
    Route::get('/artist-new/gallery', [ArtistController::class, 'artistGalleryIndex']);
    Route::get('/artist-new/videos', [ArtistController::class, 'artistVideosIndex']);
    Route::post('/artist-new/videos', [ArtistController::class, 'storeArtistVideo']);
    Route::delete('/artist-new/videos/{id}', [ArtistController::class, 'deleteArtistVideo']);
    Route::post('/artist-new/gallery-artist', [ArtistController::class, 'storeGaleryArtist']);
    Route::post('/artist-new/gallery-artist-update', [ArtistController::class, 'updateGaleryArtist']);
    Route::delete('/artist-new/gallery-artist-delete', [ArtistController::class, 'deleteGaleryArtist']);
    Route::get('/artist/offers', [OfferController::class, 'index']);
    Route::post('/artist/offers', [OfferController::class, 'store']);
    Route::put('/artist/offers/{id}', [OfferController::class, 'update']);
    Route::delete('/artist/offers/{id}', [OfferController::class, 'destroy']);
    Route::resource('/artist-new', ArtistController::class);
    Route::get('/artist/payout-method', [ArtistPayoutMethodController::class, 'show']);
    Route::post('/artist/payout-method', [ArtistPayoutMethodController::class, 'store']);
    Route::put('/artist/payout-method', [ArtistPayoutMethodController::class, 'update']);
    Route::get('/artist/approval/pending', [ApprovalController::class, 'pendingRequests']);
    Route::get('/artist/approval/history', [ApprovalController::class, 'history']);
    Route::put('/artist/approval/{id}/accept', [ApprovalController::class, 'accept']);
    Route::put('/artist/approval/{id}/reject', [ApprovalController::class, 'reject']);
    //Route for client
    Route::resource('/cards', CardController::class);
    Route::get('/client/profile', [CardController::class, 'getProfile']);
    Route::get('/client/musical-genders', [GendersController::class, 'index']);
    Route::get('/client/musical-genders/{slug}', [GendersController::class, 'artistsGenders']);
    Route::get('/client/musical-genders/artist/{slug}', [GendersController::class, 'artistGender']);
    Route::post('/cliente/shopping_card/create', [ShoppingCardController::class, 'create_order']);
    Route::get('/cliente/shopping_card/listShopingCardDetails', [ShoppingCardController::class, 'list_shopping_card_details']);
    Route::get('/cliente/shopping_card/countListShopingCardDetails', [ShoppingCardController::class, 'count_list_shopping_card_details']);
    Route::get('/cliente/shopping_card/purchaseHistory', [ShoppingCardController::class, 'list_purchase_history']);
    Route::delete('/cliente/shopping_card/deleteItemShoppingCardDetails/{id}', [ShoppingCardController::class, 'delete_item_shopping_card_details']);
    Route::post('/cliente/shopping_card/updateHourItemShoppingCart', [ShoppingCardController::class, 'update_item_shopping_card_details']);
    Route::post('/client/save-address', [ShoppingCardController::class, 'save_address']);
    Route::get('/client/favourite_artists/list', [FavouriteArtistsController::class, 'index']);
    Route::post('/client/favourite_artists/new',[FavouriteArtistsController::class, 'store']);
    Route::delete('/client/favourite_artists/destroy/{id}', [FavouriteArtistsController::class, 'destroyFavourite']);
    Route::get('/artist/favourite_artists/count', [FavouriteArtistsController::class, 'countByArtist']);
    Route::get('/artist/ratings/average', [ArtistRatingController::class, 'averageRating']);
    Route::get('/artist/sales/stats', [PaymentController::class, 'statsByArtist']);
    Route::get('/artist/favourite_artists/count', [FavouriteArtistsController::class, 'countByArtist']);
    Route::post('/client/sales/{saleId}/rate', [ArtistRatingController::class, 'rateArtist']);
    Route::get('/client/sales/{saleId}/my-rating', [ArtistRatingController::class, 'getUserRating']);
    Route::get('/artist/ratings/average', [ArtistRatingController::class, 'averageRating']);
    Route::get('/chat/messages/{artistSaleId}', [ChatController::class, 'getMessages']);
    Route::post('/chat/messages', [ChatController::class, 'sendMessage']);
    Route::put('/artist/sales/{id}/complete', [PaymentController::class, 'markAsCompleted']);
    Route::post('/artist/sales/{id}/cancel', [PaymentController::class, 'cancelEvent']);
    Route::post('/client/sales/{id}/cancel', [PaymentController::class, 'cancelClientEvent']);
    Route::put('/artist/sales/check-expired', [PaymentController::class, 'checkExpiredStatuses']);
    Route::get('/admin/openpay-keys', [OpenpayKeysController::class, 'getKeys']);
    Route::put('/admin/openpay-keys', [OpenpayKeysController::class, 'updateKeys']);
    Route::get('/admin/webhook-verification-codes', [App\Http\Controllers\Admin\WebhookVerificationController::class, 'index']);
    Route::delete('/admin/webhook-verification-codes/{id}', [App\Http\Controllers\Admin\WebhookVerificationController::class, 'destroy']);
    Route::delete('/admin/webhook-verification-codes', [App\Http\Controllers\Admin\WebhookVerificationController::class, 'clearAll']);
});

Route::get('/openpay-keys/public', [OpenpayKeysController::class, 'getPublicKeys']);
Route::get('/google-maps-key', [GoogleMapsController::class, 'getKey']);
//Route for General
Route::get('/latest-artists', [ArtistsGeneralController::class, 'latestArtists']);
// Test route
Route::resource('/product', ProductController::class);

Route::get('/artist/getArtist', [ArtistController::class, 'getArtist']);

Route::post('/users-subscribe/send', [UsersSubscribeController::class, 'sendEmailToSubscribers'])
    ->middleware(['auth:api', 'can:send-newsletters']);
Route::post('/users-subscribe/new', [UsersSubscribeController::class, 'store']);

Route::post('/quotations', [QuotationsController::class, 'addQuotation']);
Route::get('/artist-sales/public', [PaymentController::class, 'getSalesByArtist']);
Route::group(["middleware" => ["auth:api", CheckAccountStatus::class]], function () {
    Route::get('/artist/quotations/count', [QuotationsController::class, 'countByArtist']);
});

Route::group(["middleware" => ["auth:api", CheckAccountStatus::class]], function () {
    Route::post('/process-payment', [ClientPaymentController::class, 'processPayment']);
    Route::get('/artist-sales', [PaymentController::class, 'getSalesByArtist']);
    Route::post('/payment/cash', [PaymentController::class, 'processCashPayment']);
    Route::get('/payment/preview-extra-km', [PaymentController::class, 'previewExtraKm']);
    Route::post('/payment/cash/regenerate', [PaymentController::class, 'regenerateCashReference']);
    Route::post('/payment/confirm/{transactionId}', [PaymentController::class, 'confirmPayment']);
    Route::get('/client/last-order', [PaymentController::class, 'getLastClientOrder']);
    Route::get('/artist/sales/details', [PaymentController::class, 'getArtistSalesDetails']);
    Route::post('/support-tickets', [SupportTicketController::class, 'store']);
    Route::post('/support-tickets/{ticket}/evidences', [SupportTicketController::class, 'uploadEvidence']);
    Route::get('/support-tickets/my', [SupportTicketController::class, 'myTickets']);
    Route::get('/support-tickets/{ticket}/logs', [SupportTicketController::class, 'myTicketLogs']);
    Route::get('/admin/support-tickets', [SupportTicketController::class, 'index']);
    Route::get('/admin/support-tickets/{ticket}', [SupportTicketController::class, 'show']);
    Route::patch('/admin/support-tickets/{ticket}/status', [SupportTicketController::class, 'updateStatus']);
    Route::get('/admin/support-tickets/{ticket}/logs', [SupportTicketController::class, 'logs']);
});

Route::post('/webhook/openpay', [WebhookController::class, 'handleOpenpay']);
