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
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            $phone = $request->input("phone") ?? $request->input("order_details.phone");
            $eventDate = $request->input('event_date') ?? $request->input('order_details.event_date');
            $eventHour = $request->input('event_hour') ?? $request->input('order_details.event_hour');

            $clientAmountCents = (int) $request->input("amount");
            $artistList = $request->input('artistList', []);

            $calculatedTotalCents = 0;
            $itemsForSales = [];

            foreach ($artistList as $element) {
                if (!is_array($element) || !array_key_exists('artist_id', $element)) {
                    return response()->json([
                        'error' => 'Formato inválido en artistList'
                    ], 400);
                }

                $artistId = (int) $element['artist_id'];
                $hours = isset($element['hours']) ? (int) $element['hours'] : 1;
                $itemEventDate = $element['event_date'] ?? $eventDate;
                $itemEventHour = $element['event_hour'] ?? $eventHour;
                $normalizedEventDate = $itemEventDate ? Carbon::parse($itemEventDate)->toDateString() : null;
                $normalizedEventHour = $itemEventHour ? Carbon::parse($itemEventHour)->format('H:i:s') : null;

                $artist = Artist::find($artistId);
                if (!$artist) {
                    return response()->json([
                        'error' => 'Artista no encontrado',
                        'artist_id' => $artistId
                    ], 404);
                }

                $lineTotalPesos = (float) $artist->price_hour * $hours;
                $calculatedTotalCents += (int) round($lineTotalPesos * 100);
                $itemsForSales[] = [
                    'artist_id' => $artistId,
                    'amount' => $lineTotalPesos,
                ];
            }

            if ($calculatedTotalCents !== $clientAmountCents) {
                return response()->json([
                    'error' => 'Monto inválido: el total enviado no coincide con el calculado por el servidor',
                    'calculated_total' => $calculatedTotalCents,
                    'client_total' => $clientAmountCents,
                ], 400);
            }

            $amount = $calculatedTotalCents / 100;
            
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
            
            DB::beginTransaction();
            try {
                foreach ($itemsForSales as $item) {
                    $sale = new ArtistSale();
                    $sale->openpay_transaction_id = $charge->id;
                    $sale->artist_id = $item['artist_id'];
                    $sale->customer_id = Auth::user()?->id ?? $request->input("customer_id") ?? 1;
                    $sale->amount = $item['amount'];
                    $sale->customer_first_name = $name;
                    $sale->customer_last_name = $last_name;
                    $sale->customer_email = $email;
                    $sale->customer_phone = $phone;
                    $sale->customer_address = $address;
                    $sale->customer_city = $city;
                    $sale->customer_state = $state;
                    $sale->customer_zip_code = $zip_code;
                    $sale->event_date = $normalizedEventDate;
                    $sale->event_hour = $normalizedEventHour;
                    $sale->save();
                }

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

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                throw $e;
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

    public function getSalesByArtist(Request $request)
    {
        try {
            $artistId = $request->query('artist_id');

            if (!$artistId) {
                return response()->json([
                    'success' => false,
                    'message' => 'artist_id query parameter is required'
                ], 400);
            }
            
            $artist = Artist::find($artistId);
            if (!$artist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Artist not found'
                ], 404);
            }
            
            $sales = ArtistSale::where('artist_id', $artistId)
                ->select('id', 'artist_id', 'event_date', 'event_hour', 'created_at')
                ->get();
            
            return response()->json([
                'success' => true,
                'sales' => $sales,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    public function getArtistSalesDetails(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }
            
            $artist = Artist::where('user_id', $user->id)->first();
            if (!$artist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Artist profile not found for this user'
                ], 404);
            }
            
            $sales = ArtistSale::where('artist_id', $artist->id)->get();
            
            return response()->json([
                'success' => true,
                'sales' => $sales,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getLastClientOrder()
    {
        try {
            $userId = Auth::user()->id;
            
            $lastOrder = ArtistSale::where('customer_id', $userId)
                ->orderBy('created_at', 'desc')
                ->first();
            
            (is_null($lastOrder)) ? $orderData = null : $orderData = [
                'first_name' => $lastOrder->customer_first_name,
                'last_name' => $lastOrder->customer_last_name,
                'email' => $lastOrder->customer_email,
                'phone' => $lastOrder->customer_phone,
                'address' => $lastOrder->customer_address,
                'city' => $lastOrder->customer_city,
                'state' => $lastOrder->customer_state,
                'zip_code' => $lastOrder->customer_zip_code,
            ];

            return response()->json([
                'success' => true,
                'order' => $orderData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    
    public function statsByArtist()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $artist = Artist::where('user_id', $user->id)->first();

            if (!$artist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Artist profile not found for this user'
                ], 404);
            }

            $salesQuery = ArtistSale::where('artist_id', $artist->id);
            $total = (float) $salesQuery->sum('amount');
            $count = (clone $salesQuery)->count();

            return response()->json([
                'success' => true,
                'total' => $total,
                'count' => $count,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
