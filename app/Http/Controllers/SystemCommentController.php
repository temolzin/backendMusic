<?php

namespace App\Http\Controllers;

use App\Models\ArtistSale;
use App\Models\SystemComment;
use Illuminate\Http\Request;

class SystemCommentController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', SystemComment::FILTER_ALL);

        $comments = SystemComment::with('user')
            ->when($filter === SystemComment::FILTER_GOOD, fn ($query) => $query->where('rating', '>=', 4))
            ->when($filter === SystemComment::FILTER_BAD, fn ($query) => $query->where('rating', '<=', 3))
            ->latest()
            ->limit(50)
            ->get()
            ->map(function (SystemComment $comment) {
                return $this->buildCommentResponse($comment);
            });

        return response()->json(['data' => $comments]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'body' => 'required|string|min:5|max:1000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $userId = $request->user()->id;

        abort_unless(
            ArtistSale::where('customer_id', $userId)
                ->whereIn('status', [
                    ArtistSale::PAYMENT_STATUS_AUTHORIZED,
                    ArtistSale::PAYMENT_STATUS_COMPLETED,
                    ArtistSale::PAYMENT_STATUS_LIQUIDATED,
                ])
                ->exists(),
            403,
            'Solo los usuarios con al menos una venta pueden comentar.'
        );

        $comment = SystemComment::create([
            'user_id' => $userId,
            'body' => $request->body,
            'rating' => $request->rating,
        ]);

        return response()->json([
            'data' => $this->buildCommentResponse($comment->load('user')),
        ], 201);
    }

    public function checkCanComment(Request $request)
    {
        $userId = $request->user()->id;

        $can = ArtistSale::where('customer_id', $userId)
            ->whereIn('status', [
                ArtistSale::PAYMENT_STATUS_AUTHORIZED,
                ArtistSale::PAYMENT_STATUS_COMPLETED,
                ArtistSale::PAYMENT_STATUS_LIQUIDATED,
            ])
            ->exists();

        return response()->json(['can' => $can]);
    }

    private function buildCommentResponse(SystemComment $comment)
    {
        $user = $comment->user;

        return [
            'id' => $comment->id,
            'body' => $comment->body,
            'rating' => $comment->rating,
            'created_at' => $comment->created_at,
            'user' => $user
                ? [
                    'name' => $user->name,
                    'image' => $user->image_profile,
                ]
                : null,
        ];
    }
}
