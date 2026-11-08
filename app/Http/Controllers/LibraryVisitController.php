<?php

namespace App\Http\Controllers;

use App\Actions\LibraryVisitQr;
use App\Models\LibraryVisit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LibraryVisitController extends Controller
{
    public function kiosk(): View
    {
        return view('simpus', [
            'page' => 'visit-kiosk',
            'qrTokenUrl' => route('admin.visits.tokens'),
        ]);
    }

    public function tokens(LibraryVisitQr $qr): JsonResponse
    {
        return response()->json([
            'token' => $qr->issue(),
            'expires_at' => now()->addSeconds(LibraryVisitQr::TTL_SECONDS)->timestamp,
        ])->header('Cache-Control', 'no-store, private');
    }

    public function scan(Request $request, LibraryVisitQr $qr): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:1024'],
        ]);
        $payload = $qr->verify($validated['token']);

        if ($payload === null) {
            return response()->json([
                'message' => 'QR tidak valid atau sudah kedaluwarsa. Pindai QR terbaru.',
            ], 422);
        }

        $student = $request->user();
        $nonceKey = 'library-visit-scan:'.$student->getAuthIdentifier().':'.$payload['nonce'];

        if (! Cache::add($nonceKey, true, now()->addSeconds(LibraryVisitQr::TTL_SECONDS))) {
            return response()->json([
                'message' => 'QR ini sudah pernah digunakan. Pindai QR terbaru.',
            ], 409);
        }

        $result = DB::transaction(function () use ($student): array {
            User::query()->whereKey($student->getAuthIdentifier())->lockForUpdate()->firstOrFail();

            $openVisit = LibraryVisit::query()
                ->where('user_id', $student->getAuthIdentifier())
                ->whereNull('check_out_at')
                ->latest('check_in_at')
                ->latest('id')
                ->first();

            if ($openVisit === null) {
                $visit = LibraryVisit::query()->create([
                    'user_id' => $student->getAuthIdentifier(),
                    'check_in_at' => now(),
                    'visit_date' => now()->toDateString(),
                ]);

                return ['action' => 'check_in', 'visit' => $visit];
            }

            $openVisit->forceFill(['check_out_at' => now()])->save();

            return ['action' => 'check_out', 'visit' => $openVisit->refresh()];
        });

        $visit = $result['visit'];
        $actionLabel = $result['action'] === 'check_in' ? 'Check-in berhasil. Selamat datang di perpustakaan.' : 'Check-out berhasil. Terima kasih sudah berkunjung.';

        return response()->json([
            'message' => $actionLabel,
            'visit' => [
                'check_in_at' => $visit->check_in_at?->toIso8601String(),
                'check_out_at' => $visit->check_out_at?->toIso8601String(),
                'visit_date' => $visit->visit_date?->toDateString(),
            ],
        ]);
    }
}
