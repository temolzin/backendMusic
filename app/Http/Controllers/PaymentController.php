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
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{

    public function processPayment(Request $request)
    {
        try {
            $openpay = Openpay::getInstance(env('OPENPAY_ID'), env('OPENPAY_SECRET'), "MX");
            
            Openpay::setProductionMode(env('OPENPAY_PRODUCTION_MODE'));
            
            $customerData = array(
                'name' => $request->input("name"),
                'last_name' => $request->input("last_name"),
                'email' => $request->input("email"),
                'requires_account' => false,
                'address' => array(
                    'line1' => $request->input("address"),
                    'state' => $request->input("city"),
                    'city' => $request->input("state"),
                    'postal_code' => $request->input("zip_code"),
                    'country_code' => 'MX'
                )
            );

            $chargeRequest =  array(
                'method' => 'card',
                'source_id' => $request->input("token"),
                'amount' => $request->input("amount"),
                'currency' => 'MXN',
                'description' => 'Cargo de reserva de artista',
                'device_session_id' => $request->input("deviceSessionId"),
                'customer' => $customerData,
                'redirect_url' => 'http://www.openpay.mx/index.html'
            );

            $charge = $openpay->charges->create($chargeRequest);

            foreach ($request->artistList as $element) {
                $venta = new ArtistSale();
                $venta->openpay_transaction_id = $charge->id;
                $venta->artist_id = $element[0];
                $venta->customer_id = Auth::user()->id;
                $venta->amount = $element[1];
                $venta->save();
            }

            return response()->json([
                'data' => $charge
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
            return $e;
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
