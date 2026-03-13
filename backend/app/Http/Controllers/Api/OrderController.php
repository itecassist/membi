<?php

namespace App\Http\Controllers\Api;

use App\Domain\Order\Models\Order;
use App\Domain\Organisation\Models\Organisation;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function index(Request $request, Organisation $organisation): AnonymousResourceCollection
    {
        $orders = Order::where('organisation_id', $organisation->id)
            ->latest('date_placed')
            ->paginate(20);

        return OrderResource::collection($orders);
    }

    public function show(Organisation $organisation, Order $order): JsonResponse
    {
        abort_unless($order->organisation_id === $organisation->id, 404);

        $order->load('items');

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }
}
