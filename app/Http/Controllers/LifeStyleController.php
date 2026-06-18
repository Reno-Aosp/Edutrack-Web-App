<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LifeStyleController extends Controller
{
    /**
     * Proxy request to the Python API running on localhost:5003
     */
    public function suggest(Request $request)
    {
        // The python API expects specific JSON payload.
        // We validate that we have some data, but pass it through.
        
        $data = $request->validate([
            'sleep_hours' => 'required|numeric',
            'study_hours' => 'required|numeric',
            'gaming_hours' => 'required|numeric',
            'social_media_hours' => 'required|numeric',
            'mental_health_score' => 'required|numeric',
            'burnout_level' => 'required|numeric',
            'exercise_hours' => 'required|numeric',
            'caffeine_intake' => 'required|numeric',
            'screen_time' => 'required|numeric',
        ]);

        try {
            // Forward the request to the internal Python API
            $response = Http::timeout(10)->post('http://127.0.0.1:5003/api/lifestyle-suggest', $data);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            Log::error('LifeStyle Python API Error', ['status' => $response->status(), 'body' => $response->body()]);
            return response()->json(['error' => 'Gagal mendapatkan saran dari AI. Coba lagi nanti.'], 500);

        } catch (\Exception $e) {
            Log::error('LifeStyle Python API Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Koneksi ke AI gagal. Pastikan service berjalan.'], 500);
        }
    }
}
