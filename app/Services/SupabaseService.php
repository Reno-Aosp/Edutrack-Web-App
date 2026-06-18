<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SupabaseService {

    private static function url(): string {
        return env('SUPABASE_URL');
    }

    private static function key(): string {
        return env('SUPABASE_SERVICE_KEY');
    }

    private static function bucket(): string {
        return env('SUPABASE_BUCKET', 'surat-izin');
    }

    public static function uploadFile(string $fileContent, string $fileName, string $mimeType = 'image/jpeg'): ?string {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . self::key(),
            'Content-Type'  => $mimeType,
        ])->withBody($fileContent, $mimeType)
          ->post(self::url() . '/storage/v1/object/' . self::bucket() . '/' . $fileName);

        if ($response->successful()) {
            return self::url() . '/storage/v1/object/public/' . self::bucket() . '/' . $fileName;
        }

        return null;
    }
}