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
use App\Models\OpenpayKey;

class PaymentController extends Controller
{

    public function processPayment(Request $request)
    {
        try {
            $keys = OpenpayKey::first();
            $openpay = Openpay::getInstance($keys->openpay_id, $keys->openpay_secret, "MX");
            Openpay::setProductionMode(false);
            
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

            $userId = Auth::user()?->id ?? $request->input('customer_id');
            if (!$userId) {
                return response()->json([
                    'error' => 'Usuario no autenticado o no se proporcionó un ID de cliente válido'
                ], 401);
            }

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
                    'hours' => $hours,
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
                    $sale->customer_id = $userId;
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
                    $sale->event_hours = $item['hours'] ?? null;
                    $sale->event_status = 'pending';
                    $sale->save();
                }

                $userId = Auth::user()?->id ?? $request->input("customer_id");
                if (!$userId) {
                    throw new \Exception('No user ID available for cart cleanup');
                }

                $shoppingCards = ShoppingCard::where('user_id', $userId)
                    ->where('status', 1)
                    ->get();

                foreach ($shoppingCards as $shoppingCard) {
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
                ->select('id', 'artist_id', 'event_date', 'event_hour', 'event_hours', 'event_status', 'created_at')
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
            
            $sales = ArtistSale::where('artist_id', $artist->id)->where('status', 'completed')->get();
            
            $sales = $sales->map(function ($sale) {
                $this->computeStatus($sale);
                $sale->can_complete = $this->canComplete($sale);
                return $sale;
            });
            
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
    
    public function markAsCompleted($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $artist = Artist::where('user_id', $user->id)->first();
            if (!$artist) {
                return response()->json(['success' => false, 'message' => 'Artist profile not found'], 404);
            }

            $sale = ArtistSale::where('id', $id)->where('artist_id', $artist->id)->first();
            if (!$sale) {
                return response()->json(['success' => false, 'message' => 'Sale not found'], 404);
            }

            if ($sale->event_status === 'completed') {
                return response()->json(['success' => false, 'message' => 'Evento ya completado'], 400);
            }

            if ($sale->event_status === 'expired') {
                return response()->json(['success' => false, 'message' => 'Evento expirado, no se puede marcar como completado'], 400);
            }

            $sale->event_status = 'completed';
            $sale->save();

            return response()->json(['success' => true, 'message' => 'Evento marcado como completado']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function checkExpiredStatuses()
    {
        try {
            $now = Carbon::now();
            $cutoff = $now->copy()->subDay();

            $expiredSales = ArtistSale::where('event_status', 'pending')
                ->whereNotNull('event_date')
                ->whereNotNull('event_hour')
                ->get()
                ->filter(function ($sale) use ($cutoff) {
                    $eventDateStr = $sale->event_date instanceof Carbon ? $sale->event_date->format('Y-m-d') : $sale->event_date;
                    $eventHourStr = $sale->event_hour instanceof Carbon ? $sale->event_hour->format('H:i:s') : $sale->event_hour;
                    $eventEnd = Carbon::parse($eventDateStr . ' ' . $eventHourStr);
                    $hours = $sale->event_hours ?? 0;
                    $eventEnd->addHours($hours);
                    return $eventEnd < $cutoff;
                });

            $count = 0;
            foreach ($expiredSales as $sale) {
                $sale->event_status = 'expired';
                $sale->save();
                $count++;
            }

            return response()->json(['success' => true, 'expired_count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getEventStatus($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $artist = Artist::where('user_id', $user->id)->first();
            if (!$artist) {
                return response()->json(['success' => false, 'message' => 'Artist profile not found'], 404);
            }

            $sale = ArtistSale::where('id', $id)->where('artist_id', $artist->id)->first();
            if (!$sale) {
                return response()->json(['success' => false, 'message' => 'Sale not found'], 404);
            }

            $computedStatus = $this->computeStatus($sale);

            return response()->json([
                'success' => true,
                'event_status' => $computedStatus,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function computeStatus($sale)
    {
        if ($sale->event_status === 'completed') {
            return 'completed';
        }

        if ($sale->event_status === 'expired') {
            return 'expired';
        }

        if (!$sale->event_date || !$sale->event_hour) {
            return 'pending';
        }

        $now = Carbon::now();
        $eventDateStr = $sale->event_date instanceof Carbon ? $sale->event_date->format('Y-m-d') : $sale->event_date;
        $eventHourStr = $sale->event_hour instanceof Carbon ? $sale->event_hour->format('H:i:s') : $sale->event_hour;
        $eventEnd = Carbon::parse($eventDateStr . ' ' . $eventHourStr);
        $hours = $sale->event_hours ?? 0;
        $eventEnd->addHours($hours);

        if ($eventEnd < $now) {
            $cutoff = $eventEnd->copy()->addDay();
            if ($now > $cutoff) {
                $sale->event_status = 'expired';
                $sale->save();
                return 'expired';
            }
        }

        return $sale->event_status;
    }

    private function canComplete($sale)
    {
        if ($sale->event_status !== 'pending') return false;
        if (!$sale->event_date || !$sale->event_hour) return false;

        $now = Carbon::now();
        $eventDateStr = $sale->event_date instanceof Carbon ? $sale->event_date->format('Y-m-d') : $sale->event_date;
        $eventHourStr = $sale->event_hour instanceof Carbon ? $sale->event_hour->format('H:i:s') : $sale->event_hour;
        $eventEnd = Carbon::parse($eventDateStr . ' ' . $eventHourStr);
        $hours = $sale->event_hours ?? 0;
        $eventEnd->addHours($hours);

        return $eventEnd < $now;
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

            $salesQuery = ArtistSale::where('artist_id', $artist->id)->where('status', 'completed');
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

    public function processCashPayment(Request $request)
    {
        try {
            $keys = OpenpayKey::first();
            $openpay = Openpay::getInstance($keys->openpay_id, $keys->openpay_secret, "MX");
            Openpay::setProductionMode(false);

            $dueDateInstance = Carbon::now()->addHours(24);

            $name        = $request->input('order_details.first_name') ?? $request->input('customer_name');
            $last_name   = $request->input('order_details.last_name', '');
            $email       = $request->input('order_details.email') ?? $request->input('customer_email');
            $phone       = $request->input('order_details.phone') ?? $request->input('customer_phone');
            $address     = $request->input('order_details.address', '');
            $city        = $request->input('order_details.city', '');
            $state       = $request->input('order_details.state', '');
            $zip_code    = $request->input('order_details.zip_code', '');
            $eventDate   = $request->input('order_details.event_date') ?? $request->input('event_date');
            $eventHour   = $request->input('order_details.event_hour') ?? $request->input('event_hour');
            $store       = $request->input('store'); 

            $userId = Auth::user()?->id ?? $request->input('customer_id');
            if (!$userId) {
                return response()->json([
                    'error' => 'Usuario no autenticado o no se proporcionó un ID de cliente válido'
                ], 401);
            }

            $clientAmountCents = (int) $request->input('amount');
            $artistList        = $request->input('artistList', []);

            $calculatedTotalCents = 0;
            $itemsForSales = [];

            foreach ($artistList as $element) {
                if (!is_array($element) || !array_key_exists('artist_id', $element)) {
                    return response()->json(['error' => 'Formato inválido en artistList'], 400);
                }

                $artistId = (int) $element['artist_id'];
                $hours    = isset($element['hours']) ? (int) $element['hours'] : 1;
                $normalizedEventDate = $eventDate ? Carbon::parse($eventDate)->toDateString() : null;
                $normalizedEventHour = $eventHour ? Carbon::parse($eventHour)->format('H:i:s') : null;

                $artist = Artist::find($artistId);
                if (!$artist) {
                    return response()->json(['error' => 'Artista no encontrado', 'artist_id' => $artistId], 404);
                }

                $lineTotalPesos        = (float) $artist->price_hour * $hours;
                $calculatedTotalCents += (int) round($lineTotalPesos * 100);
                $itemsForSales[]       = [
                    'artist_id'    => $artistId,
                    'amount'       => $lineTotalPesos,
                    'event_date'   => $normalizedEventDate,
                    'event_hour'   => $normalizedEventHour,
                ];
            }

            if ($calculatedTotalCents !== $clientAmountCents) {
                return response()->json([
                    'error'            => 'Monto inválido: el total enviado no coincide con el calculado',
                    'calculated_total' => $calculatedTotalCents,
                    'client_total'     => $clientAmountCents,
                ], 400);
            }

            $amount = $calculatedTotalCents / 100;

            $customerData = [
                'name'             => $name,
                'last_name'        => $last_name,
                'email'            => $email,
                'requires_account' => false,
                'address'          => [
                    'line1'        => $address,
                    'state'        => $state,
                    'city'         => $city,
                    'postal_code'  => $zip_code,
                    'country_code' => 'MX',
                ],
            ];

            $chargeRequest = [
                'method'      => 'store',
                'amount'      => $amount,
                'currency'    => 'MXN',
                'description' => 'Reserva artista - Pago en efectivo (' . $store . ')',
                'customer'    => $customerData,
                'due_date'    => Carbon::now()->addHours(24)->format('Y-m-d\TH:i:s'),
            ];

            $charge = $openpay->charges->create($chargeRequest);

            DB::beginTransaction();
            try {
                foreach ($itemsForSales as $item) {
                    $sale = new ArtistSale();
                    $sale->openpay_transaction_id = $charge->id;
                    $sale->status = 'pending';
                    $sale->artist_id              = $item['artist_id'];
                    $sale->customer_id            = $userId;
                    $sale->amount                 = $item['amount'];
                    $sale->customer_first_name    = $name;
                    $sale->customer_last_name     = $last_name;
                    $sale->customer_email         = $email;
                    $sale->customer_phone         = $phone;
                    $sale->customer_address       = $address;
                    $sale->customer_city          = $city;
                    $sale->customer_state         = $state;
                    $sale->customer_zip_code      = $zip_code;
                    $sale->event_date             = $item['event_date'];
                    $sale->event_hour             = $item['event_hour'];
                    $sale->payment_method = 'cash';
                    $sale->store = $store;
                    $sale->save();
                }

                $shoppingCard = ShoppingCard::where('user_id', $userId)->where('status', 1)->first();

                if ($shoppingCard) {
                    ShoppingCardDetail::where('shopping_card_id', $shoppingCard->id)->delete();
                    $shoppingCard->status = 2;
                    $shoppingCard->total  = 0;
                    $shoppingCard->save();
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            return response()->json([
                'data'      => [
                    'id'        => $charge->id,
                    'amount'    => $charge->amount,
                    'status'    => $charge->status,
                    'store'     => $store,
                    'reference' => $charge->payment_method->reference ?? null,
                    'barcode'   => $charge->payment_method->barcode_url ?? null,
                    'due_date'  => $dueDateInstance->toDateTimeString(),
                ],
                'message'   => 'Referencia de pago generada correctamente',
            ]);

        } catch (OpenpayApiTransactionError $e) {
            return response()->json(['error' => ['category' => $e->getCategory(), 'error_code' => $e->getErrorCode(), 'description' => $e->getMessage()]], 500);
        } catch (OpenpayApiRequestError $e) {
            return response()->json(['error' => ['category' => $e->getCategory(), 'error_code' => $e->getErrorCode(), 'description' => $e->getMessage()]], 500);
        } catch (OpenpayApiConnectionError $e) {
            return response()->json(['error' => ['category' => $e->getCategory(), 'error_code' => $e->getErrorCode(), 'description' => $e->getMessage()]], 500);
        } catch (OpenpayApiAuthError $e) {
            return response()->json(['error' => ['category' => $e->getCategory(), 'error_code' => $e->getErrorCode(), 'description' => $e->getMessage()]], 500);
        } catch (OpenpayApiError $e) {
            return response()->json(['error' => ['category' => $e->getCategory(), 'error_code' => $e->getErrorCode(), 'description' => $e->getMessage()]], 500);
        } catch (Exception $e) {
            return response()->json(['error' => ['category' => 'Generic Error', 'error_code' => 'GENERIC_ERROR', 'description' => $e->getMessage()]], 500);
        }
    }

    public function regenerateCashReference(Request $request)
    {
        try {
            $saleId = $request->input('artist_sale_id');
            if (!$saleId) {
                return response()->json(['error' => 'Se requiere el ID de la venta'], 400);
            }

            $sale = ArtistSale::where('id', $saleId)
                ->where('payment_method', 'cash')
                ->first();

            if (!$sale) {
                return response()->json(['error' => 'Venta no encontrada o no es un pago en efectivo'], 404);
            }

            if ($sale->status === 'completed') {
                return response()->json(['error' => 'Esta venta ya fue pagada'], 400);
            }

            $keys = OpenpayKey::first();
            $openpay = Openpay::getInstance($keys->openpay_id, $keys->openpay_secret, "MX");
            Openpay::setProductionMode(false);

            $dueDateInstance = Carbon::now()->addHours(24);

            $customerData = [
                'name'             => $sale->customer_first_name,
                'last_name'        => $sale->customer_last_name,
                'email'            => $sale->customer_email,
                'requires_account' => false,
                'address'          => [
                    'line1'        => $sale->customer_address ?? 'Sin dirección',
                    'city'         => $sale->customer_city ?? 'Ciudad',
                    'state'        => $sale->customer_state ?? 'Estado',
                    'postal_code'  => $sale->customer_zip_code ?? '00000',
                    'country_code' => 'MX',
                ],
            ];

            $chargeRequest = [
                'method'      => 'store',
                'amount'      => (float) $sale->amount,
                'currency'    => 'MXN',
                'description' => 'Re-generación referencia - Reserva artista',
                'customer'    => $customerData,
                'due_date'    => $dueDateInstance->format('Y-m-d\TH:i:s'),
            ];

            $charge = $openpay->charges->create($chargeRequest);

            $cashRef = $charge->payment_method->reference ?? null;
            $barcodeUrl = $charge->payment_method->barcode_url ?? null;
            $dueDateStr = $dueDateInstance->toDateTimeString();

            $relatedSales = ArtistSale::where('customer_id', $sale->customer_id)
                ->where('openpay_transaction_id', $sale->openpay_transaction_id)
                ->get();

            foreach ($relatedSales as $related) {
                $related->openpay_transaction_id = $charge->id;
                $related->save();
            }

            return response()->json([
                'data' => [
                    'id'        => $charge->id,
                    'amount'    => $charge->amount,
                    'status'    => $charge->status,
                    'store'     => $sale->store ?? 'Tienda',
                    'reference' => $cashRef,
                    'barcode'   => $barcodeUrl,
                    'due_date'  => $dueDateStr,
                ],
                'message' => 'Nueva referencia generada correctamente',
            ]);
        } catch (OpenpayApiTransactionError $e) {
            return response()->json(['error' => ['category' => $e->getCategory(), 'error_code' => $e->getErrorCode(), 'description' => $e->getMessage()]], 500);
        } catch (OpenpayApiRequestError $e) {
            return response()->json(['error' => ['category' => $e->getCategory(), 'error_code' => $e->getErrorCode(), 'description' => $e->getMessage()]], 500);
        } catch (OpenpayApiConnectionError $e) {
            return response()->json(['error' => ['category' => $e->getCategory(), 'error_code' => $e->getErrorCode(), 'description' => $e->getMessage()]], 500);
        } catch (OpenpayApiAuthError $e) {
            return response()->json(['error' => ['category' => $e->getCategory(), 'error_code' => $e->getErrorCode(), 'description' => $e->getMessage()]], 500);
        } catch (OpenpayApiError $e) {
            return response()->json(['error' => ['category' => $e->getCategory(), 'error_code' => $e->getErrorCode(), 'description' => $e->getMessage()]], 500);
        } catch (Exception $e) {
            return response()->json(['error' => ['category' => 'Generic Error', 'error_code' => 'GENERIC_ERROR', 'description' => $e->getMessage()]], 500);
        }
    }

    public function confirmPayment(string $transactionId)
    {
        $sale = ArtistSale::where('openpay_transaction_id', $transactionId)->first();

        if (!$sale) {
            return response()->json(['message' => 'No se encontró la transacción en el sistema'], 404);
        }

        if ($sale->status === 'completed') {
            return response()->json([
                'message' => 'El pago ya había sido confirmado previamente',
                'updated' => 0,
            ], 200);
        }

        $sale->status = 'completed';
        $sale->save();

        return response()->json([
            'message' => 'Pago confirmado correctamente',
            'updated' => 1,
        ]);
    }
}
