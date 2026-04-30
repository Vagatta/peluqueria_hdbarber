<?php

namespace App\Http\Controllers\Api;

use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Service::query();
        if (! $request->user() || ! $request->user()->isAdmin()) {
            $q->where('active', true);
        }
        return response()->json($q->orderBy('name')->get());
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json($service);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $service = Service::create($data);
        return response()->json($service, 201);
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        $data = $this->validated($request, true);
        $service->update($data);
        return response()->json($service);
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();
        return response()->json(['message' => 'ok']);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $req = $partial ? 'sometimes' : 'required';
        return $request->validate([
            'name' => [$req, 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'duration_minutes' => [$req, 'integer', 'min:5', 'max:600'],
            'price_cents' => [$req, 'integer', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
        ]);
    }
}
