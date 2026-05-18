<?php
// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->getData($request->user());
        return response()->json(['data' => $data]);
    }
}
