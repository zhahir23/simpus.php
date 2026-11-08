<?php

namespace Tests\Feature;

use App\Actions\LibraryVisitQr;
use App\Models\LibraryVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_the_kiosk_and_fetch_one_signed_dynamic_qr_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.visits.kiosk'))
            ->assertOk()
            ->assertSeeText('Satu QR untuk masuk dan keluar')
            ->assertSeeText('Scan pertama untuk masuk. Scan berikutnya untuk keluar')
            ->assertDontSeeText('Check-in mahasiswa')
            ->assertDontSeeText('Check-out mahasiswa');

        $codes = $this->getJson(route('admin.visits.tokens'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonStructure(['token', 'expires_at'])
            ->json();

        $qr = app(LibraryVisitQr::class);
        $this->assertNotNull($qr->verify($codes['token']));
        $this->assertSame(2, count($codes));
    }

    public function test_only_students_can_scan_and_only_staff_can_open_the_kiosk(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($student)->get(route('admin.visits.kiosk'))->assertForbidden();
        $this->actingAs($admin)->postJson(route('student.visits.scan'), ['token' => 'invalid'])->assertForbidden();
    }

    public function test_guests_are_redirected_to_the_student_login_before_scanning(): void
    {
        $this->postJson(route('student.visits.scan'), ['token' => 'invalid'])
            ->assertRedirect(route('student.login'));
    }

    public function test_student_portal_shows_the_camera_scanner_at_the_mobile_breakpoint(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get(route('student.portal'))
            ->assertOk()
            ->assertSee('class="panel visit-scanner block md:hidden"', false)
            ->assertSeeText('Presensi perpustakaan');
    }

    public function test_one_shared_qr_toggles_student_between_check_in_and_check_out(): void
    {
        $this->travelTo('2026-11-09 10:00:00');
        $student = User::factory()->create(['role' => 'student']);
        $qr = app(LibraryVisitQr::class);
        $firstQrToken = $qr->issue();

        $this->actingAs($student)
            ->postJson(route('student.visits.scan'), ['token' => $firstQrToken])
            ->assertOk()
            ->assertJsonPath('message', 'Check-in berhasil. Selamat datang di perpustakaan.');

        $visit = LibraryVisit::query()->sole();
        $this->assertSame($student->id, $visit->user_id);
        $this->assertSame('2026-11-09', $visit->visit_date->toDateString());
        $this->assertNotNull($visit->check_in_at);
        $this->assertNull($visit->check_out_at);

        $this->postJson(route('student.visits.scan'), ['token' => $firstQrToken])
            ->assertConflict()
            ->assertJsonPath('message', 'QR ini sudah pernah digunakan. Pindai QR terbaru.');

        $this->postJson(route('student.visits.scan'), ['token' => $qr->issue()])
            ->assertOk()
            ->assertJsonPath('message', 'Check-out berhasil. Terima kasih sudah berkunjung.');

        $this->assertNotNull($visit->refresh()->check_out_at);

        $this->postJson(route('student.visits.scan'), ['token' => $qr->issue()])
            ->assertOk()
            ->assertJsonPath('message', 'Check-in berhasil. Selamat datang di perpustakaan.')
            ->assertJsonPath('visit.check_out_at', null);

        $this->assertDatabaseCount('library_visits', 2);
        $this->assertNull(LibraryVisit::query()->latest('check_in_at')->latest('id')->firstOrFail()->check_out_at);
    }

    public function test_invalid_or_expired_qr_codes_do_not_record_a_visit(): void
    {
        $this->travelTo('2026-11-09 10:00:00');
        $student = User::factory()->create(['role' => 'student']);
        $qr = app(LibraryVisitQr::class);
        $validToken = $qr->issue();
        $tamperedToken = substr($validToken, 0, -1).(str_ends_with($validToken, '0') ? '1' : '0');

        $this->actingAs($student)
            ->postJson(route('student.visits.scan'), ['token' => $tamperedToken])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'QR tidak valid atau sudah kedaluwarsa. Pindai QR terbaru.');

        $this->travel(30)->seconds();
        $this->assertNotNull($qr->verify($validToken));
        $this->travel(1)->seconds();

        $this->postJson(route('student.visits.scan'), ['token' => $validToken])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'QR tidak valid atau sudah kedaluwarsa. Pindai QR terbaru.');

        $this->assertDatabaseCount('library_visits', 0);
    }

    public function test_student_without_an_open_visit_is_checked_in_and_scan_requires_a_token(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $qr = app(LibraryVisitQr::class);

        $this->actingAs($student)
            ->postJson(route('student.visits.scan'), ['token' => $qr->issue()])
            ->assertOk()
            ->assertJsonPath('message', 'Check-in berhasil. Selamat datang di perpustakaan.');

        $this->postJson(route('student.visits.scan'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('token');

        $this->assertDatabaseCount('library_visits', 1);
    }

    public function test_dashboard_shows_today_visitors_and_monday_to_saturday_totals(): void
    {
        $this->travelTo('2026-11-09 10:00:00');
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'name' => 'Sarah Mahasiswa',
            'student_number' => '2106728910',
        ]);
        LibraryVisit::query()->create([
            'user_id' => $student->id,
            'check_in_at' => now()->subHour(),
            'check_out_at' => now()->subMinutes(20),
            'visit_date' => now()->toDateString(),
        ]);
        LibraryVisit::query()->create([
            'user_id' => $student->id,
            'check_in_at' => now()->subMinutes(10),
            'visit_date' => now()->toDateString(),
        ]);
        LibraryVisit::query()->create([
            'user_id' => $student->id,
            'check_in_at' => now()->addDay(),
            'visit_date' => now()->addDay()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('Tren kunjungan')
            ->assertSeeText('Mahasiswa berkunjung hari ini')
            ->assertSeeText('Sarah Mahasiswa')
            ->assertSeeText('2106728910')
            ->assertSeeText('Di perpustakaan')
            ->assertViewHas('metrics', fn (array $metrics): bool => $metrics['visits_today'] === 2)
            ->assertViewHas('visitChartData', function ($chart): bool {
                $this->assertSame(
                    [
                        ['label' => 'Sen', 'count' => 2, 'is_today' => true],
                        ['label' => 'Sel', 'count' => 1, 'is_today' => false],
                        ['label' => 'Rab', 'count' => 0, 'is_today' => false],
                        ['label' => 'Kam', 'count' => 0, 'is_today' => false],
                        ['label' => 'Jum', 'count' => 0, 'is_today' => false],
                        ['label' => 'Sab', 'count' => 0, 'is_today' => false],
                    ],
                    $chart->all(),
                );

                return true;
            });
    }
}
