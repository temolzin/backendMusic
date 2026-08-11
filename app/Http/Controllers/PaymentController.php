<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
use App\Models\ArtistSaleCashReference;
use App\Models\Artist;
use App\Models\ShoppingCard;
use App\Models\ShoppingCardDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\OpenpayKey;
use App\Models\Offer;
use App\Models\EventCancellation;
use App\Models\Card;
use App\Services\DistanceMatrixService;
use Illuminate\Support\Facades\Mail;
use App\Services\TicketPdfService;
use App\Mail\ArtistSaleRequest;
use App\Mail\EventCancelledNotification;
use App\Models\ClientRefund;

class PaymentController extends Controller
{

    public function processPayment(Request $request)
    {
        $acquiredLocks = [];

        try {
            $keys = OpenpayKey::first();
            $openpay = Openpay::getInstance($keys->openpay_id, $keys->openpay_secret, "MX", $request->ip());
            Openpay::setProductionMode(!$keys->openpay_sandbox_mode);

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
            $latitude = $request->input('latitude') ?? $request->input('order_details.latitude');
            $longitude = $request->input('longitude') ?? $request->input('order_details.longitude');
            $googlePlaceId = $request->input('google_place_id') ?? $request->input('order_details.google_place_id');

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

            $distanceService = new DistanceMatrixService();
            $totalExtraKmCostPesos = 0;

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

                $now = Carbon::now()->format('Y-m-d H:i:s');
                $activeOffer = Offer::where('artist_id', $artist->id)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now)
                    ->orderBy('discount_percentage', 'desc')
                    ->first();

                $priceHour = $activeOffer
                    ? $artist->price_hour * (1 - $activeOffer->discount_percentage / 100)
                    : $artist->price_hour;

                $baseAmount = (float) $priceHour * $hours;
                $extraKmDistance = null;
                $extraKmCost = 0;

                if ($latitude && $longitude && $artist->coverage_radius > 0 && $artist->extra_kilometre > 0) {
                    $distance = $distanceService->getDrivingDistanceInKm($artist->zone, $latitude, $longitude);
                    if ($distance !== null && $distance > $artist->coverage_radius) {
                        $extraKmDistance = $distance;
                        $extraKmCost = ($distance - $artist->coverage_radius) * (float) $artist->extra_kilometre;
                    }
                }

                $totalExtraKmCostPesos += $extraKmCost;
                $lineTotalPesos = round($baseAmount + $extraKmCost, 2);
                $calculatedTotalCents += (int) round($baseAmount * 100);

                $itemsForSales[] = [
                    'artist_id' => $artistId,
                    'offer_id' => $activeOffer?->id,
                    'amount' => $lineTotalPesos,
                    'hours' => $hours,
                    'extra_km_distance' => $extraKmDistance,
                    'extra_km_cost' => $extraKmCost,
                    'event_date' => $normalizedEventDate,
                    'event_hour' => $normalizedEventHour,
                ];
            }

            if ($calculatedTotalCents !== $clientAmountCents) {
                return response()->json([
                    'error' => 'Monto inválido: el total enviado no coincide con el calculado por el servidor',
                    'calculated_total' => $calculatedTotalCents,
                    'client_total' => $clientAmountCents,
                ], 400);
            }

            $acquiredLocks = [];
            $lockKeys = [];

            foreach ($itemsForSales as $item) {
                if (!$item['event_date']) continue;
                $lockKeys[] = 'payment-lock:artist:' . $item['artist_id'] . ':date:' . $item['event_date'];
            }

            $lockKeys = array_values(array_unique($lockKeys));

            foreach ($lockKeys as $lockKey) {
                $lock = Cache::lock($lockKey, 300);
                if (!$lock->get()) {
                    foreach ($acquiredLocks as $acquiredLock) {
                        $acquiredLock->release();
                    }
                    return response()->json([
                        'error' => 'Lo sentimos, otro cliente está completando una transacción con este artista. Inténtalo en unos minutos'
                    ], 409);
                }
                $acquiredLocks[] = $lock;
            }

            foreach ($itemsForSales as $item) {
                if (!$item['event_date']) continue;
                $alreadyBooked = DB::table('artist_sales')
                    ->where('artist_id', $item['artist_id'])
                    ->where('event_date', $item['event_date'])
                    ->whereIn('event_status', [ArtistSale::EVENT_STATUS_PENDING, ArtistSale::EVENT_STATUS_COMPLETED])
                    ->exists();

                if ($alreadyBooked) {
                    foreach ($acquiredLocks as $acquiredLock) {
                        $acquiredLock->release();
                    }
                    return response()->json([
                        'error' => 'Uno o más artistas de tu lista ya no están disponibles para la fecha seleccionada.'
                    ], 422);
                }
            }

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
                'phone_number' => $phone,
                'address' => array(
                    'line1' => $address,
                    'state' => $state,
                    'city' => $city,
                    'postal_code' => $zip_code,
                    'country_code' => 'MX'
                )
            );

            $createdCharges = [];
            $openpayCustomerId = null;
            $deviceSessionId = $request->input("deviceSessionId") ?? Str::uuid()->toString();

            try {
                $openpayCustomer = $openpay->customers->add($customerData);

                $card = $openpayCustomer->cards->add([
                    'token_id' => $token,
                    'device_session_id' => $deviceSessionId,
                ]);

                $openpayCustomerId = $openpayCustomer->id;

                foreach ($itemsForSales as $item) {
                    $charge = $openpayCustomer->charges->create([
                        'method' => 'card',
                        'source_id' => $card->id,
                        'amount' => round((float) $item['amount'], 2),
                        'currency' => 'MXN',
                        'description' => 'Cargo de reserva - Artista #' . $item['artist_id'],
                        'capture' => false,
                        'device_session_id' => $deviceSessionId,
                    ]);

                    $createdCharges[] = $charge;
                }
            } catch (\Exception $e) {
                foreach ($createdCharges as $createdCharge) {
                    try {
                        $openpay->charges->get($createdCharge->id)->refund(['description' => 'Reembolso por fallo en cargo múltiple']);
                    } catch (\Exception $refundErr) {
                        Log::warning('No se pudo reembolsar cargo ' . $createdCharge->id . ': ' . $refundErr->getMessage());
                    }
                }
                foreach ($acquiredLocks as $acquiredLock) {
                    $acquiredLock->release();
                }
                throw $e;
            }

            DB::beginTransaction();
            try {
                foreach ($itemsForSales as $index => $item) {
                    $charge = $createdCharges[$index];
                    $sale = new ArtistSale();
                    $sale->openpay_transaction_id = $charge->id;
                    $sale->artist_id = $item['artist_id'];
                    $sale->offer_id = $item['offer_id'] ?? null;
                    $sale->customer_id = $userId;
                    $sale->amount = $item['amount'];
                    $sale->openpay_fee = $this->resolveOpenpayFee($charge, (float) $item['amount']);
                    $sale->customer_first_name = $name;
                    $sale->customer_last_name = $last_name;
                    $sale->customer_email = $email;
                    $sale->customer_phone = $phone;
                    $sale->customer_address = $address;
                    $sale->customer_city = $city;
                    $sale->customer_state = $state;
                    $sale->customer_zip_code = $zip_code;
                    $sale->event_date = $item['event_date'];
                    $sale->event_hour = $item['event_hour'];
                    $sale->event_hours = $item['hours'] ?? null;
                    $sale->event_status = ArtistSale::EVENT_STATUS_PENDING;
                    $sale->status = ArtistSale::PAYMENT_STATUS_AUTHORIZED;
                    $sale->payment_method = ArtistSale::PAYMENT_METHOD_CARD;
                    $sale->latitude = $latitude;
                    $sale->longitude = $longitude;
                    $sale->google_place_id = $googlePlaceId;
                    $sale->extra_km_distance = $item['extra_km_distance'];
                    $sale->extra_km_cost = $item['extra_km_cost'];
                    $sale->approval_status = ArtistSale::APPROVAL_STATUS_PENDING;
                    $sale->approval_deadline = Carbon::now()->addHours(24);
                    $sale->openpay_customer_id = $openpayCustomerId;
                    $sale->save();

                    $this->sendSaleRequestEmail($sale);
                }

                $currentUserId = Auth::user()?->id ?? $request->input("customer_id");
                if (!$currentUserId) {
                    throw new \Exception('No user ID available for cart cleanup');
                }

                $shoppingCards = ShoppingCard::where('user_id', $currentUserId)
                    ->where('status', ShoppingCard::STATUS_ACTIVE)
                    ->get();

                foreach ($shoppingCards as $shoppingCard) {
                    ShoppingCardDetail::where('shopping_card_id', $shoppingCard->id)->delete();
                    $shoppingCard->status = ShoppingCard::STATUS_PAID;
                    $shoppingCard->total = 0;
                    $shoppingCard->save();
                }

                DB::commit();

                foreach ($acquiredLocks as $acquiredLock) {
                    $acquiredLock->release();
                }
            } catch (\Exception $e) {
                DB::rollBack();

                foreach ($createdCharges as $createdCharge) {
                    try {
                        $openpay->charges->get($createdCharge->id)->refund(['description' => 'Reembolso por fallo en transacción']);
                    } catch (\Exception $refundErr) {
                        Log::warning('No se pudo reembolsar cargo ' . $createdCharge->id . ': ' . $refundErr->getMessage());
                    }
                }

                foreach ($acquiredLocks as $acquiredLock) {
                    $acquiredLock->release();
                }

                throw $e;
            }

            return response()->json([
                'message' => 'Pago procesado correctamente',
                'charges_count' => count($createdCharges),
            ]);
        } catch (OpenpayApiTransactionError $e) {
            Log::warning('OpenPay transaction error: ' . $e->getMessage(), ['error_code' => $e->getErrorCode()]);
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => self::translateOpenpayError($e->getErrorCode(), $e->getMessage()),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ], $e->getHttpCode() ?: 402);
        } catch (OpenpayApiRequestError $e) {
            Log::warning('OpenPay request error: ' . $e->getMessage(), ['error_code' => $e->getErrorCode()]);
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => self::translateOpenpayError($e->getErrorCode(), $e->getMessage()),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ], $e->getHttpCode() ?: 400);
        } catch (OpenpayApiConnectionError $e) {
            Log::error('OpenPay connection error: ' . $e->getMessage());
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => self::translateOpenpayError($e->getErrorCode(), 'No pudimos conectar con el procesador de pagos. Intenta de nuevo en unos minutos.'),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ], $e->getHttpCode() ?: 503);
        } catch (OpenpayApiAuthError $e) {
            Log::error('OpenPay auth error: ' . $e->getMessage());
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => self::translateOpenpayError($e->getErrorCode(), $e->getMessage()),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ], $e->getHttpCode() ?: 401);
        } catch (OpenpayApiError $e) {
            Log::error('OpenPay error: ' . $e->getMessage());
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => self::translateOpenpayError($e->getErrorCode(), 'Ocurrió un error al procesar el pago. Intenta de nuevo.'),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ], $e->getHttpCode() ?: 500);
        } catch (Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage());
            return response()->json([
                'error' => [
                    'category' => 'Error genérico',
                    'error_code' => 'GENERIC_ERROR',
                    'description' => 'Ocurrió un error inesperado al procesar el pago. Intenta de nuevo.',
                ]
            ], 500);
        } finally {
            foreach ($acquiredLocks as $acquiredLock) {
                try {
                    $acquiredLock->release();
                } catch (\Throwable $e) {
                    Log::warning("Failed to release lock: " . $e->getMessage());
                }
            }
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
                ->whereIn('event_status', [ArtistSale::EVENT_STATUS_PENDING, ArtistSale::EVENT_STATUS_COMPLETED])
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

            $sales = ArtistSale::where('artist_id', $artist->id)
                ->where(function ($query) {
                    $query->whereNull('approval_status')
                        ->orWhere('approval_status', '!=', ArtistSale::APPROVAL_STATUS_PENDING);
                })
                ->get();

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
            $user = Auth::user();
            $userId = $user->id;

            $lastOrder = ArtistSale::where('customer_id', $userId)
                ->orderBy('created_at', 'desc')
                ->first();

            $fullName = explode(' ', $user->name, 2);
            (is_null($lastOrder)) ? $orderData = [
                'first_name' => $fullName[0] ?? '',
                'last_name' => $fullName[1] ?? '',
                'email' => $user->email,
                'phone' => '',
                'address' => $user->address ?? '',
                'city' => $user->city ?? '',
                'state' => $user->state ?? '',
                'zip_code' => $user->zip_code ?? '',
                'latitude' => $user->latitude,
                'longitude' => $user->longitude,
            ] : $orderData = [
                'first_name' => $lastOrder->customer_first_name,
                'last_name' => $lastOrder->customer_last_name,
                'email' => $lastOrder->customer_email,
                'phone' => $lastOrder->customer_phone,
                'address' => $lastOrder->customer_address,
                'city' => $lastOrder->customer_city,
                'state' => $lastOrder->customer_state,
                'zip_code' => $lastOrder->customer_zip_code,
                'latitude' => $user->latitude,
                'longitude' => $user->longitude,
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

            if ($sale->event_status === ArtistSale::EVENT_STATUS_COMPLETED) {
                return response()->json(['success' => false, 'message' => 'Evento ya completado'], 400);
            }

            if ($sale->event_status === ArtistSale::EVENT_STATUS_EXPIRED) {
                return response()->json(['success' => false, 'message' => 'Evento expirado, no se puede marcar como completado'], 400);
            }

            if (!$this->canComplete($sale)) {
                return response()->json(['success' => false, 'message' => 'El evento aún no ha terminado. Debes esperar a la hora de finalización.'], 400);
            }

            $sale->event_status = ArtistSale::EVENT_STATUS_COMPLETED;
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

            $expiredSales = ArtistSale::where('event_status', ArtistSale::EVENT_STATUS_PENDING)
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
                $sale->event_status = ArtistSale::EVENT_STATUS_EXPIRED;
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
        if ($sale->event_status === ArtistSale::EVENT_STATUS_COMPLETED) {
            return ArtistSale::EVENT_STATUS_COMPLETED;
        }

        if ($sale->event_status === ArtistSale::EVENT_STATUS_EXPIRED) {
            return ArtistSale::EVENT_STATUS_EXPIRED;
        }

        if ($sale->event_status === ArtistSale::EVENT_STATUS_CANCELLED) {
            return ArtistSale::EVENT_STATUS_CANCELLED;
        }

        if (!$sale->event_date || !$sale->event_hour) {
            return ArtistSale::EVENT_STATUS_PENDING;
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
                $sale->event_status = ArtistSale::EVENT_STATUS_EXPIRED;
                $sale->save();
                return ArtistSale::EVENT_STATUS_EXPIRED;
            }
        }

        return $sale->event_status;
    }

    private function canComplete($sale)
    {
        if ($sale->event_status !== ArtistSale::EVENT_STATUS_PENDING) return false;
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

            $salesQuery = ArtistSale::where('artist_id', $artist->id)->where('status', ArtistSale::PAYMENT_STATUS_LIQUIDATED);
            $total = $salesQuery->get()->sum(function ($sale) {
                $netPayout = floatval($sale->amount) - floatval($sale->openpay_fee) - (floatval($sale->amount) * 0.10);
                return max(0, $netPayout);
            });
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
            $latitude    = $request->input('latitude') ?? $request->input('order_details.latitude');
            $longitude   = $request->input('longitude') ?? $request->input('order_details.longitude');
            $googlePlaceId = $request->input('google_place_id') ?? $request->input('order_details.google_place_id');

            $userId = Auth::user()?->id ?? $request->input('customer_id');
            if (!$userId) {
                return response()->json([
                    'error' => 'Usuario no autenticado o no se proporcionó un ID de cliente válido'
                ], 401);
            }

            $clientAmountCents = (int) $request->input('amount');
            $artistList        = $request->input('artistList', []);

            $distanceService = new DistanceMatrixService();
            $calculatedTotalCents = 0;
            $totalExtraKmCostPesos = 0;
            $itemsForSales = [];

            foreach ($artistList as $element) {
                if (!is_array($element) || !array_key_exists('artist_id', $element)) {
                    return response()->json(['error' => 'Formato inválido en artistList'], 400);
                }

                $artistId = (int) $element['artist_id'];
                $hours    = isset($element['hours']) ? (int) $element['hours'] : 1;
                $itemEventDate = $element['event_date'] ?? $eventDate;
                $itemEventHour = $element['event_hour'] ?? $eventHour;
                $normalizedEventDate = $itemEventDate ? Carbon::parse($itemEventDate)->toDateString() : null;
                $normalizedEventHour = $itemEventHour ? Carbon::parse($itemEventHour)->format('H:i:s') : null;

                $artist = Artist::find($artistId);
                if (!$artist) {
                    return response()->json(['error' => 'Artista no encontrado', 'artist_id' => $artistId], 404);
                }

                $now = Carbon::now()->format('Y-m-d H:i:s');
                $activeOffer = Offer::where('artist_id', $artist->id)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now)
                    ->orderBy('discount_percentage', 'desc')
                    ->first();

                $priceHour = $activeOffer
                    ? $artist->price_hour * (1 - $activeOffer->discount_percentage / 100)
                    : $artist->price_hour;

                $baseAmount = (float) $priceHour * $hours;
                $extraKmDistance = null;
                $extraKmCost = 0;

                if ($latitude && $longitude && $artist->coverage_radius > 0 && $artist->extra_kilometre > 0) {
                    $distance = $distanceService->getDrivingDistanceInKm($artist->zone, $latitude, $longitude);
                    if ($distance !== null && $distance > $artist->coverage_radius) {
                        $extraKmDistance = $distance;
                        $extraKmCost = ($distance - $artist->coverage_radius) * (float) $artist->extra_kilometre;
                    }
                }

                $totalExtraKmCostPesos += $extraKmCost;
                $lineTotalPesos = round($baseAmount + $extraKmCost, 2);
                $calculatedTotalCents += (int) round($baseAmount * 100);

                $itemsForSales[] = [
                    'artist_id' => $artistId,
                    'offer_id' => $activeOffer?->id,
                    'amount' => $lineTotalPesos,
                    'event_date' => $normalizedEventDate,
                    'event_hour' => $normalizedEventHour,
                    'hours' => $hours,
                    'extra_km_distance' => $extraKmDistance,
                    'extra_km_cost' => $extraKmCost,
                ];
            }

            if ($calculatedTotalCents !== $clientAmountCents) {
                return response()->json([
                    'error'            => 'Monto inválido: el total enviado no coincide con el calculado',
                    'calculated_total' => $calculatedTotalCents,
                    'client_total'     => $clientAmountCents,
                ], 400);
            }

            $amount = round(($calculatedTotalCents + (int) round($totalExtraKmCostPesos * 100)) / 100, 2);

            $acquiredLocks = [];
            $lockKeys = [];

            foreach ($itemsForSales as $item) {
                if (!$item['event_date']) continue;
                $lockKeys[] = 'payment-lock:artist:' . $item['artist_id'] . ':date:' . $item['event_date'];
            }

            $lockKeys = array_values(array_unique($lockKeys));

            foreach ($lockKeys as $lockKey) {
                $lock = Cache::lock($lockKey, 300);
                if (!$lock->get()) {
                    foreach ($acquiredLocks as $acquiredLock) {
                        $acquiredLock->release();
                    }
                    return response()->json([
                        'error' => 'Lo sentimos, otro cliente está completando una transacción con este artista. Inténtalo en unos minutos'
                    ], 409);
                }
                $acquiredLocks[] = $lock;
            }

            foreach ($itemsForSales as $item) {
                if (!$item['event_date']) continue;
                $alreadyBooked = DB::table('artist_sales')
                    ->where('artist_id', $item['artist_id'])
                    ->where('event_date', $item['event_date'])
                    ->whereIn('event_status', [ArtistSale::EVENT_STATUS_PENDING, ArtistSale::EVENT_STATUS_COMPLETED])
                    ->exists();

                if ($alreadyBooked) {
                    foreach ($acquiredLocks as $acquiredLock) {
                        $acquiredLock->release();
                    }
                    return response()->json([
                        'error' => 'Uno o más artistas de tu lista ya no están disponibles para la fecha seleccionada.'
                    ], 422);
                }
            }


            DB::beginTransaction();
            try {
                foreach ($itemsForSales as $item) {
                    $sale = new ArtistSale();
                    $sale->status = ArtistSale::PAYMENT_STATUS_PENDING;
                    $sale->event_status = ArtistSale::EVENT_STATUS_PENDING;
                    $sale->artist_id              = $item['artist_id'];
                    $sale->offer_id               = $item['offer_id'] ?? null;
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
                    $sale->event_hours            = $item['hours'] ?? null;
                    $sale->payment_method = ArtistSale::PAYMENT_METHOD_CASH;
                    $sale->store = $store;
                    $sale->latitude = $latitude;
                    $sale->longitude = $longitude;
                    $sale->google_place_id = $googlePlaceId;
                    $sale->extra_km_distance = $item['extra_km_distance'];
                    $sale->extra_km_cost = $item['extra_km_cost'];
                    $sale->approval_status = ArtistSale::APPROVAL_STATUS_PENDING;
                    $sale->approval_deadline = Carbon::now()->addHours(24);
                    $sale->save();

                    $this->sendSaleRequestEmail($sale);
                }

                $shoppingCard = ShoppingCard::where('user_id', $userId)->where('status', ShoppingCard::STATUS_ACTIVE)->first();

                if ($shoppingCard) {
                    ShoppingCardDetail::where('shopping_card_id', $shoppingCard->id)->delete();
                    $shoppingCard->status = ShoppingCard::STATUS_PAID;
                    $shoppingCard->total  = 0;
                    $shoppingCard->save();
                }

                DB::commit();

                foreach ($acquiredLocks as $acquiredLock) {
                    $acquiredLock->release();
                }
            } catch (\Exception $e) {
                DB::rollBack();

                foreach ($acquiredLocks as $acquiredLock) {
                    $acquiredLock->release();
                }

                throw $e;
            }

            return response()->json([
                'message' => 'Reserva registrada correctamente. Pendiente de aprobación.',
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
                ->where('payment_method', ArtistSale::PAYMENT_METHOD_CASH)
                ->first();

            if (!$sale) {
                return response()->json(['error' => 'Venta no encontrada o no es un pago en efectivo'], 404);
            }

            if ($sale->status === ArtistSale::PAYMENT_STATUS_COMPLETED) {
                return response()->json(['error' => 'Esta venta ya fue pagada'], 400);
            }

            if ($sale->approval_status !== ArtistSale::APPROVAL_STATUS_ACCEPTED) {
                return response()->json(['error' => 'El artista aún no ha aceptado esta solicitud'], 422);
            }

            $keys = OpenpayKey::first();
            $openpay = Openpay::getInstance($keys->openpay_id, $keys->openpay_secret, "MX", request()->ip());
            Openpay::setProductionMode(!$keys->openpay_sandbox_mode);

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
                'amount'      => round((float) $sale->amount, 2),
                'currency'    => 'MXN',
                'description' => 'Re-generación referencia - Reserva artista',
                'customer'    => $customerData,
                'due_date'    => $dueDateInstance->format('Y-m-d\TH:i:s'),
            ];

            $charge = $openpay->charges->create($chargeRequest);

            $cashRef = $charge->payment_method->reference ?? null;
            $barcodeUrl = $charge->payment_method->barcode_url ?? null;
            $dueDateStr = $dueDateInstance->toDateTimeString();

            $sale->openpay_transaction_id = $charge->id;
            $sale->save();

            ArtistSaleCashReference::updateOrCreate(
                ['artist_sale_id' => $sale->id],
                [
                    'cash_reference'   => $cashRef,
                    'cash_barcode_url' => $barcodeUrl,
                    'cash_due_date'    => $dueDateInstance,
                ]
            );

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

    public function previewExtraKm(Request $request)
    {
        try {
            $artistId = (int) $request->input('artist_id');
            $hours    = (int) ($request->input('hours', 1));
            $latitude  = (float) $request->input('latitude');
            $longitude = (float) $request->input('longitude');

            if (!$artistId || !$latitude || !$longitude) {
                return response()->json(['success' => false, 'message' => 'Faltan parámetros'], 400);
            }

            $artist = Artist::find($artistId);
            if (!$artist) {
                return response()->json(['success' => false, 'message' => 'Artista no encontrado'], 404);
            }

            $baseAmount  = (float) $artist->price_hour * $hours;
            $extraKmDistance = null;
            $extraKmCost = 0;

            if ($artist->coverage_radius > 0 && $artist->extra_kilometre > 0) {
                $service = new DistanceMatrixService();
                $distance = $service->getDrivingDistanceInKm($artist->zone, $latitude, $longitude);

                if ($distance !== null && $distance > $artist->coverage_radius) {
                    $extraKmDistance = $distance;
                    $extraKmCost = ($distance - $artist->coverage_radius) * (float) $artist->extra_kilometre;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'artist_name' => $artist->name,
                    'base_amount' => round($baseAmount, 2),
                    'coverage_radius' => (int) $artist->coverage_radius,
                    'extra_kilometre' => (float) $artist->extra_kilometre,
                    'total_distance' => $extraKmDistance,
                    'extra_km_distance' => $extraKmDistance !== null ? round($extraKmDistance - $artist->coverage_radius, 2) : null,
                    'extra_km_cost' => round($extraKmCost, 2),
                    'total' => round($baseAmount + $extraKmCost, 2),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function confirmPayment(string $transactionId)
    {
        $sale = ArtistSale::where('openpay_transaction_id', $transactionId)->first();

        if (!$sale) {
            return response()->json(['message' => 'No se encontró la transacción en el sistema'], 404);
        }

        if ($sale->status === ArtistSale::PAYMENT_STATUS_COMPLETED) {
            return response()->json([
                'message' => 'El pago ya había sido confirmado previamente',
                'updated' => 0,
            ], 200);
        }

        $sale->status = ArtistSale::PAYMENT_STATUS_COMPLETED;
        $sale->save();

        return response()->json([
            'message' => 'Pago confirmado correctamente',
            'updated' => 1,
        ]);
    }

    public function cancelEvent(Request $request, $id)
    {
        try {
            $request->validate(['reason' => 'required|string|max:500']);

            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            $artist = Artist::where('user_id', $user->id)->first();
            if (!$artist) {
                return response()->json(['success' => false, 'message' => 'Perfil de artista no encontrado'], 404);
            }

            $sale = ArtistSale::where('id', $id)->where('artist_id', $artist->id)->first();
            if (!$sale) {
                return response()->json(['success' => false, 'message' => 'Venta no encontrada'], 404);
            }

            if ($sale->event_status === ArtistSale::EVENT_STATUS_COMPLETED) {
                return response()->json(['success' => false, 'message' => 'El evento ya fue completado'], 400);
            }

            if ($sale->event_status === ArtistSale::EVENT_STATUS_CANCELLED) {
                return response()->json(['success' => false, 'message' => 'El evento ya fue cancelado'], 400);
            }

            if (!$sale->event_date) {
                return response()->json(['success' => false, 'message' => 'El evento no tiene fecha asignada'], 400);
            }

            $now = Carbon::now()->startOfDay();
            $eventDate = Carbon::parse($sale->event_date)->startOfDay();
            $daysUntilEvent = $now->diffInDays($eventDate, false);

            if ($daysUntilEvent < 0) {
                return response()->json(['success' => false, 'message' => 'La fecha del evento ya pasó'], 400);
            }

            if ($daysUntilEvent == 0) {
                return response()->json(['success' => false, 'message' => 'No puedes cancelar el evento el mismo día'], 400);
            }

            $amount = floatval($sale->amount);
            $penaltyPercentage = $this->resolveArtistPenalty($daysUntilEvent);

            $penaltyAmount = round($amount * ($penaltyPercentage / 100), 2);



            $sale->event_status = ArtistSale::EVENT_STATUS_CANCELLED;
            $sale->save();

            $cancellation = EventCancellation::create([
                'artist_sale_id' => $sale->id,
                'user_id' => $user->id,
                'cancellation_reason' => $request->reason,
                'penalty_percentage' => $penaltyPercentage,
                'penalty_amount' => $penaltyAmount,
                'refunded_at' => null,
                'penalty_paid' => false,
            ]);

            ClientRefund::create([
                'event_cancellation_id' => $cancellation->id,
                'customer_id' => $sale->customer_id,
                'refund_percentage' => 100,
                'refund_amount' => $amount,
                'status' => ClientRefund::STATUS_PENDING,
            ]);

            $this->sendCancellationEmails($sale, $request->reason, 'artist', $amount, $penaltyAmount, $penaltyPercentage);

            return response()->json([
                'success' => true,
                'message' => 'Evento cancelado correctamente. La solicitud de reembolso fue enviada al administrador.',
                'data' => [
                    'refund_amount' => $amount,
                    'penalty_percentage' => $penaltyPercentage,
                    'penalty_amount' => $penaltyAmount,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cancelClientEvent(Request $request, $id)
    {
        try {
            $request->validate(['reason' => 'required|string|max:500']);

            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }

            $sale = ArtistSale::where('id', $id)->where('customer_id', $user->id)->first();
            if (!$sale) {
                return response()->json(['success' => false, 'message' => 'Compra no encontrada'], 404);
            }

            if ($sale->event_status === ArtistSale::EVENT_STATUS_COMPLETED) {
                return response()->json(['success' => false, 'message' => 'El evento ya fue completado'], 400);
            }

            if ($sale->event_status === ArtistSale::EVENT_STATUS_CANCELLED) {
                return response()->json(['success' => false, 'message' => 'El evento ya fue cancelado'], 400);
            }

            if (!$sale->event_date) {
                return response()->json(['success' => false, 'message' => 'El evento no tiene fecha asignada'], 400);
            }

            $now = Carbon::now()->startOfDay();
            $eventDate = Carbon::parse($sale->event_date)->startOfDay();
            $daysUntilEvent = $now->diffInDays($eventDate, false);

            if ($daysUntilEvent < 0) {
                return response()->json(['success' => false, 'message' => 'La fecha del evento ya pasó'], 400);
            }

            if ($daysUntilEvent == 0) {
                return response()->json(['success' => false, 'message' => 'No puedes cancelar el evento el mismo día'], 400);
            }

            $amount = floatval($sale->amount);
            $penaltyPercentage = $sale->approval_status === ArtistSale::APPROVAL_STATUS_ACCEPTED
                ? $this->resolveClientPenalty($daysUntilEvent)
                : 0;

            $penaltyAmount = round($amount * ($penaltyPercentage / 100), 2);
            $refundAmount = $amount - $penaltyAmount;

            $originalPenaltyPercentage = $penaltyPercentage;
            $originalPenaltyAmount = $penaltyAmount;
            $originalRefundAmount = $refundAmount;

            $penaltyPercentage = $originalPenaltyPercentage;
            $penaltyAmount = $originalPenaltyAmount;
            $refundAmount = $originalRefundAmount;

            $originalApprovalStatus = $sale->approval_status;

            $sale->event_status = ArtistSale::EVENT_STATUS_CANCELLED;
            if (in_array($sale->approval_status, [ArtistSale::APPROVAL_STATUS_PENDING, ArtistSale::APPROVAL_STATUS_ACCEPTED])) {
                $sale->approval_status = ArtistSale::APPROVAL_STATUS_CANCELLED;
                $sale->approval_responded_at = Carbon::now();
            }
            $sale->save();

            $cancellation = EventCancellation::create([
                'artist_sale_id' => $sale->id,
                'user_id' => $user->id,
                'cancellation_reason' => $request->reason,
                'penalty_percentage' => $penaltyPercentage,
                'penalty_amount' => $penaltyAmount,
                'refunded_at' => null,
                'penalty_paid' => true,
            ]);

            if ($refundAmount > 0) {
                ClientRefund::create([
                    'event_cancellation_id' => $cancellation->id,
                    'customer_id' => $sale->customer_id,
                    'refund_percentage' => (100 - $penaltyPercentage),
                    'refund_amount' => $refundAmount,
                    'status' => 'pending',
                ]);
            }

            $this->sendCancellationEmails($sale, $request->reason, 'client', $refundAmount, $penaltyAmount, $penaltyPercentage, $originalApprovalStatus);

            return response()->json([
                'success' => true,
                'message' => 'Evento cancelado correctamente. La solicitud de reembolso fue enviada al administrador.',
                'data' => [
                    'refund_amount' => $refundAmount,
                    'penalty_percentage' => $penaltyPercentage,
                    'penalty_amount' => $penaltyAmount,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function sendCancellationEmails(ArtistSale $sale, string $reason, string $cancelledBy, float $refundAmount, float $penaltyAmount, int $penaltyPercentage = 0, ?string $originalApprovalStatus = null)
    {
        $clientEmail = $sale->customer_email;

        $artistUser = Artist::where('id', $sale->artist_id)->with('user')->first()?->user;
        $artistEmail = $artistUser ? $artistUser->email : null;

        $isBeforeApproval = $cancelledBy === 'client' && $originalApprovalStatus !== ArtistSale::APPROVAL_STATUS_ACCEPTED;

        if ($clientEmail) {
            try {
                Mail::to($clientEmail)->send(new EventCancelledNotification($sale, $reason, $cancelledBy, $refundAmount, $penaltyAmount, $penaltyPercentage, 'client', $isBeforeApproval));
            } catch (\Exception $e) {
                Log::warning('Error enviando correo de cancelación al cliente: ' . $e->getMessage());
            }
        }

        if ($artistEmail) {
            try {
                Mail::to($artistEmail)->send(new EventCancelledNotification($sale, $reason, $cancelledBy, $refundAmount, $penaltyAmount, $penaltyPercentage, 'artist', $isBeforeApproval));
            } catch (\Exception $e) {
                Log::warning('Error enviando correo de cancelación al artista: ' . $e->getMessage());
            }
        }
    }

    private function resolveArtistPenalty(int $daysUntilEvent): int
    {
        if ($daysUntilEvent >= 1 && $daysUntilEvent < EventCancellation::CANCEL_PENALTY_DAYS_SHORT) {
            return EventCancellation::PENALTY_SHORT_TERM;
        }

        if ($daysUntilEvent >= EventCancellation::CANCEL_PENALTY_DAYS_SHORT && $daysUntilEvent < EventCancellation::CANCEL_PENALTY_DAYS_MEDIUM) {
            return EventCancellation::PENALTY_MEDIUM_TERM;
        }

        return EventCancellation::PENALTY_LONG_TERM;
    }

    private function resolveClientPenalty(int $daysUntilEvent): int
    {
        if ($daysUntilEvent >= EventCancellation::CANCEL_PENALTY_DAYS_MEDIUM) {
            return EventCancellation::PENALTY_LONG_TERM;
        }

        if ($daysUntilEvent >= EventCancellation::CANCEL_PENALTY_DAYS_SHORT && $daysUntilEvent < EventCancellation::CANCEL_PENALTY_DAYS_MEDIUM) {
            return EventCancellation::PENALTY_MEDIUM_TERM;
        }

        return EventCancellation::PENALTY_SHORT_TERM;
    }

    public function cancellationPreview($id)
    {
        try {
            $sale = ArtistSale::find($id);
            if (!$sale || !$sale->event_date) {
                return response()->json(['success' => false, 'message' => 'Venta no encontrada'], 404);
            }

            $now = Carbon::now()->startOfDay();
            $eventDate = Carbon::parse($sale->event_date)->startOfDay();
            $daysUntilEvent = $now->diffInDays($eventDate, false);

            $role = request('role', 'client');

            $penaltyPercentage = $role === 'artist'
                ? $this->resolveArtistPenalty(max(0, $daysUntilEvent))
                : ($sale->approval_status === ArtistSale::APPROVAL_STATUS_ACCEPTED
                    ? $this->resolveClientPenalty(max(0, $daysUntilEvent))
                    : 0);

            $amount = floatval($sale->amount);
            $penaltyAmount = round($amount * ($penaltyPercentage / 100), 2);
            $refundAmount = $amount - $penaltyAmount;

            return response()->json([
                'success' => true,
                'data' => [
                    'days_until_event' => max(0, $daysUntilEvent),
                    'penalty_percentage' => $penaltyPercentage,
                    'penalty_amount' => $penaltyAmount,
                    'refund_amount' => $refundAmount,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function resolveOpenpayFee($charge, float $amount): float
    {
        if (isset($charge->fee) && is_object($charge->fee) && isset($charge->fee->amount)) {
            return (float) $charge->fee->amount;
        }

        $baseCommissionRate = 0.029;
        $fixedCharge = 2.50;
        $taxRate = 1.16;

        return round((($amount * $baseCommissionRate) + $fixedCharge) * $taxRate, 2);
    }

    private function sendSaleRequestEmail(ArtistSale $sale)
    {
        $artistUser = Artist::where('id', $sale->artist_id)->with('user')->first()?->user;
        $artistEmail = $artistUser ? $artistUser->email : null;
        if ($artistEmail) {
            try {
                Mail::to($artistEmail)->send(new ArtistSaleRequest($sale));
            } catch (\Exception $e) {
                Log::warning('Error enviando correo de solicitud al artista: ' . $e->getMessage());
            }
        }
    }

    private const OPENPAY_ERROR_MESSAGES = [
        3001 => 'La tarjeta fue rechazada por el banco.',
        3002 => 'La tarjeta ha expirado.',
        3003 => 'La tarjeta no tiene fondos suficientes.',
        3004 => 'La tarjeta ha sido identificada como una tarjeta robada.',
        3005 => 'La tarjeta fue rechazada por el sistema antifraude.',
        3006 => 'La tarjeta fue rechazada por coincidir con registros en lista negra.',
        3008 => 'La tarjeta fue reportada como perdida.',
        3009 => 'El banco ha restringido esta tarjeta.',
        3010 => 'El banco ha solicitado retener la tarjeta. Contacta a tu banco.',
        3011 => 'Se requiere autorización del banco para realizar este pago.',
        1002 => 'Los datos enviados para procesar el pago están incompletos o son incorrectos.',
        1004 => 'El código de seguridad (CVV2) de la tarjeta es inválido.',
        1005 => 'La fecha de vencimiento de la tarjeta es inválida.',
        1006 => 'El código de seguridad (CVV2) de la tarjeta no fue proporcionado.',
        1013 => 'El número de tarjeta no es válido.',
    ];

    private function getCardDataForSale(ArtistSale $sale, $user): ?array
    {
        try {
            $keys = OpenpayKey::first();
            $openpay = Openpay::getInstance($keys->openpay_id, $keys->openpay_secret, 'MX', request()->ip());
            Openpay::setProductionMode(!$keys->openpay_sandbox_mode);
            $charge = $openpay->charges->get($sale->openpay_transaction_id);
            if (isset($charge->payment_method->brand)) {
                $brand = $charge->payment_method->brand;
                $cardNumber = $charge->payment_method->card_number ?? '';
                return [
                    'brand' => $brand,
                    'last_digits' => substr($cardNumber, -4),
                ];
            }
        } catch (\Exception $e) {
            Log::warning('No se pudo obtener datos de tarjeta desde OpenPay: ' . $e->getMessage());
        }

        $card = Card::where('user_id', $user->id)->orderBy('id', 'desc')->first();
        if ($card) {
            $cleanNumber = preg_replace('/[\s-]/', '', $card->number_card);
            return [
                'brand' => strtolower($card->card_type),
                'last_digits' => substr($cleanNumber, -4),
            ];
        }

        return null;
    }

    private static function translateOpenpayError(?int $errorCode, string $fallbackMessage): string
    {
        return self::OPENPAY_ERROR_MESSAGES[$errorCode] ?? $fallbackMessage;
    }

    public function downloadReceipt($id)
    {
        try {
            $user = Auth::user();
            $sale = ArtistSale::where('id', $id)
                ->where('customer_id', $user->id)
                ->firstOrFail();

            if ($sale->approval_status !== ArtistSale::APPROVAL_STATUS_ACCEPTED) {
                return response()->json([
                    'success' => false,
                    'message' => 'El recibo solo esta disponible despues de que el artista acepte la solicitud.'
                ], 403);
            }

            if ($sale->payment_method === ArtistSale::PAYMENT_METHOD_CASH && $sale->status !== ArtistSale::PAYMENT_STATUS_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => 'El recibo estara disponible cuando el pago en efectivo sea confirmado.'
                ], 403);
            }

            if ($sale->payment_method === ArtistSale::PAYMENT_METHOD_CARD) {
                $cardData = $this->getCardDataForSale($sale, $user);
                if ($cardData) {
                    $sale->setAttribute('_card_brand', $cardData['brand']);
                    $sale->setAttribute('_card_last_digits', $cardData['last_digits']);
                }
            }

            return app(TicketPdfService::class)->download($sale);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar el recibo: ' . $e->getMessage()
            ], 500);
        }
    }
}
