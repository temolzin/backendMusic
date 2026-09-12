<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookVerificationCode;
use Illuminate\Http\Request;

class WebhookVerificationController extends Controller
{
    public function index()
    {
        $codes = WebhookVerificationCode::orderBy('created_at', 'desc')->get();
        $webhookUrl = request()->getSchemeAndHttpHost() . '/api/webhook/openpay';
        return response()->json(['success' => true, 'data' => $codes, 'webhook_url' => $webhookUrl]);
    }

    public function destroy($id)
    {
        $code = WebhookVerificationCode::findOrFail($id);
        $code->delete();
        return response()->json(['success' => true, 'message' => 'Código eliminado']);
    }

    public function clearAll()
    {
        WebhookVerificationCode::truncate();
        return response()->json(['success' => true, 'message' => 'Todos los códigos fueron eliminados']);
    }
}
