<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Openpay\Data\Openpay;
use OpenpayChargeRequest;
use Exception;
use Openpay\Data\OpenpayApiError;
use Openpay\Data\OpenpayApiAuthError;
use Openpay\Data\OpenpayApiRequestError;
use Openpay\Data\OpenpayApiConnectionError;
use Openpay\Data\OpenpayApiTransactionError;
use Illuminate\Http\JsonResponse;
use App\Models\ArtistSale;
use App\Models\Artist;
use App\Models\ShoppingCard;
use App\Models\ShoppingCardDetail;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{

    public function processPayment(Request $request)
    {
        try {
            $openpay = Openpay::getInstance(env('OPENPAY_ID'), env('OPENPAY_SECRET'), "MX");
            Openpay::setProductionMode(env('OPENPAY_PRODUCTION_MODE'));
            
            $token = $request->input("token");
            $name = $request->input("name") ?? $request->input("order_details.first_name");
            $last_name = $request->input("last_name") ?? $request->input("order_details.last_name");
            $email = $request->input("email") ?? $request->input("customer_email");
            $address = $request->input("address") ?? $request->input("order_details.address");
            $city = $request->input("city") ?? $request->input("order_details.city");
            $state = $request->input("state") ?? $request->input("order_details.state");
            $zip_code = $request->input("zip_code") ?? $request->input("order_details.zip_code");
            $amount = $request->input("amount");
            
            $amount = $amount / 100;
            
            if ($request->input("transaction_id")) {
                $charge = new \stdClass();
                $charge->id = $request->input("transaction_id");
                $charge->amount = $amount;
                $charge->status = "completed";
            } else {
                if (!$token) {
                    return response()->json([
                        'error' => [
                            'category' => 'VALIDATION_ERROR',
                            'error_code' => 'MISSING_TOKEN',
                            'description' => 'El token de OpenPay es requerido'
                        ]
                    ], 400);
                }
                
                $customerData = array(
                    'name' => $name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'requires_account' => false,
                    'address' => array(
                        'line1' => $address,
                        'state' => $state,
                        'city' => $city,
                        'postal_code' => $zip_code,
                        'country_code' => 'MX'
                    )
                );

                $chargeRequest = array(
                    'method' => 'card',
                    'source_id' => $token,
                    'amount' => $amount,
                    'currency' => 'MXN',
                    'description' => 'Cargo de reserva de artista',
                    'customer' => $customerData,
                    'redirect_url' => 'http://www.openpay.mx/index.html'
                );
                
                $deviceSessionId = $request->input("deviceSessionId");
                if ($deviceSessionId) {
                    $chargeRequest['device_session_id'] = $deviceSessionId;
                }

                $charge = $openpay->charges->create($chargeRequest);
            }
            
            foreach ($request->artistList as $element) {
                $venta = new ArtistSale();
                $venta->openpay_transaction_id = $charge->id;
                $venta->artist_id = $element[0];
                $venta->customer_id = Auth::user()?->id ?? $request->input("customer_id") ?? 1;
                $venta->amount = $element[1];
                $venta->save();
            }

            try {
                $user_id = Auth::user()?->id ?? $request->input("customer_id");
                if (!$user_id) {
                    throw new \Exception('No user ID available for cart cleanup');
                }

                $shoppingCard = ShoppingCard::where('user_id', $user_id)
                    ->where('status', 1)
                    ->first();

                if ($shoppingCard) {
                    ShoppingCardDetail::where('shopping_card_id', $shoppingCard->id)->delete();
                    $shoppingCard->status = 2;
                    $shoppingCard->total = 0;
                    $shoppingCard->save();
                }
            } catch (\Exception $e) {
                // ignore cleanup errors
            }

            return response()->json([
                'data' => $charge,
                'message' => 'Pago procesado correctamente'
            ]);

        } catch (OpenpayApiTransactionError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ]);
        } catch (OpenpayApiRequestError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ]);
        } catch (OpenpayApiConnectionError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ]);
        } catch (OpenpayApiAuthError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ]);
        } catch (OpenpayApiError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'category' => 'Generic Error',
                    'error_code' => 'GENERIC_ERROR',
                    'description' => $e->getMessage(),
                ]
            ]);
        }
       
    }

    public function getSalesByArtist()
    {
        try {
            $user = Auth::user();
            $artist = Artist::where('user_id', $user->id)->first();
            $artistId = $artist->id;
            $sales = ArtistSale::where('artist_id', $artistId)->get();
            return response()->json([
                'success' => true,
                'sales' => $sales,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);        }
    }
}
