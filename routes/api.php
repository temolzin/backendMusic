<?php

use App\Http\Controllers\Admin\MusicalsGenders;
use App\Http\Controllers\Admin\MusicalsGendersController;
use App\Http\Controllers\Artist\ArtistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuotationsController;
use App\Http\Controllers\Client\ClientController;
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
use App\Http\Controllers\ArtistRatingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Admin\DashboardStatsController;
use App\Http\Controllers\Client\ClientDashboardController;

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
// Routes protected by session middleware
Route::group(["middleware" => "auth:api"], function () {
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
    //Route for artist
    Route::post('/artist-new/up-date/{id}', [ArtistController::class, 'updateDetails']);
    Route::get('/artist-new/gallery', [ArtistController::class, 'artistGalleryIndex']);
    Route::post('/artist-new/gallery-artist', [ArtistController::class, 'storeGaleryArtist']);
    Route::post('/artist-new/gallery-artist-update', [ArtistController::class, 'updateGaleryArtist']);
    Route::delete('/artist-new/gallery-artist-delete', [ArtistController::class, 'deleteGaleryArtist']);
    Route::resource('/artist-new', ArtistController::class);
    //Route for client
    Route::get('/client/dashboard-overview', [ClientDashboardController::class, 'index']);
    Route::resource('/client-card', ClientController::class);
    Route::get('/client/profile', [ClientController::class, 'getProfile']);
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
    Route::post('/client/artists/{id}/rate', [ArtistRatingController::class, 'rateArtist']);
    Route::get('/client/artists/{id}/my-rating', [ArtistRatingController::class, 'getUserRating']);
    Route::get('/artist/ratings/average', [ArtistRatingController::class, 'averageRating']);
    Route::get('/chat/messages/{artistSaleId}', [ChatController::class, 'getMessages']);
    Route::post('/chat/messages', [ChatController::class, 'sendMessage']);
});


//Route for General
Route::get('/latest-artists', [ArtistsGeneralController::class, 'latestArtists']);
// Test route
Route::resource('/product', ProductController::class);

Route::get('/artist/getArtist', [ArtistController::class, 'getArtist']);

Route::post('/users-subscribe/send', [UsersSubscribeController::class, 'sendEmailToSubscribers'])
    ->middleware(['auth:api', 'can:send-newsletters']);
Route::post('/users-subscribe/new', [UsersSubscribeController::class, 'store']);

Route::post('/quotations', [QuotationsController::class, 'addQuotation']);
Route::group(["middleware" => "auth:api"], function () {
    Route::get('/artist/quotations/count', [QuotationsController::class, 'countByArtist']);
});

Route::group(["middleware" => "auth:api"], function () {
    Route::post('/process-payment', [ClientPaymentController::class, 'processPayment']);
});

Route::get('/artist-sales', [PaymentController::class, 'getSalesByArtist']);
