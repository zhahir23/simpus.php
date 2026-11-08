<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_student_portals_have_separate_login_routes(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('student.portal'))
            ->assertRedirect(route('student.login'));
    }

    public function test_students_cannot_open_admin_pages_and_admins_cannot_open_student_pages(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('student.portal'))->assertForbidden();
    }

    public function test_student_catalog_recommends_books_by_the_same_author(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $viewedBook = Book::query()->create([
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'synopsis' => 'A practical guide to maintainable software.',
        ]);
        $recommendedBook = Book::query()->create([
            'title' => 'Clean Architecture',
            'author' => 'Robert C. Martin',
            'synopsis' => 'An introduction to software architecture.',
        ]);
        Book::query()->create([
            'title' => 'World History',
            'author' => 'A. Historian',
            'synopsis' => 'A study of history across many regions.',
        ]);

        $response = $this->actingAs($student)
            ->get(route('student.portal', ['book' => $viewedBook->id]));

        $response->assertOk()
            ->assertSeeText('Rekomendasi berdasarkan buku ini')
            ->assertSeeText('Penulis yang sama')
            ->assertSeeText('Topik serupa')
            ->assertViewHas('recommendedBooks', fn ($recommendations): bool => $recommendations
                ->pluck('book.id')
                ->all() === [$recommendedBook->id]);
    }

    public function test_student_catalog_shows_available_alternatives_when_no_similar_book_exists(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $viewedBook = Book::query()->create([
            'title' => 'Matematika Diskrit',
            'author' => 'A. Akademisi',
            'publisher' => 'Penerbit Utama',
        ]);
        $alternativeBook = Book::query()->create([
            'title' => 'Kebudayaan Nusantara',
            'author' => 'B. Peneliti',
            'publisher' => 'Penerbit Lain',
        ]);
        $alternativeBook->copies()->create([
            'barcode' => 'SL-TEST-001',
            'status' => BookCopy::STATUS_AVAILABLE,
        ]);

        $response = $this->actingAs($student)
            ->get(route('student.portal', ['book' => $viewedBook->id]));

        $response->assertOk()
            ->assertSeeText('Alternatif lain yang tersedia')
            ->assertViewHas('recommendedBooks', fn ($recommendations): bool => $recommendations
                ->first()['book']
                ->is($alternativeBook));
    }

    public function test_student_can_open_the_receipt_for_a_saved_loan(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'name' => 'Sarah Mahasiswa',
            'student_number' => '2106728910',
        ]);
        $loan = $this->createLoanFor($student);

        $response = $this->actingAs($student)->get(route('student.portal'));

        $response->assertOk()
            ->assertSeeText('Bukti pinjam')
            ->assertSeeText('Bukti peminjaman')
            ->assertSeeText('Nomor transaksi #'.$loan->id)
            ->assertSeeText('Clean Code')
            ->assertSeeText($loan->bookCopy->barcode)
            ->assertSeeText('Sarah Mahasiswa')
            ->assertSeeText('2106728910');
    }

    public function test_student_portal_explains_when_no_saved_loan_receipt_exists(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get(route('student.portal'));

        $response->assertOk()
            ->assertSeeText('Belum ada bukti peminjaman')
            ->assertSeeText('Bukti peminjaman akan tersedia setelah transaksi berhasil disimpan.');
    }

    public function test_student_receipt_history_includes_returned_loans_but_excludes_other_students(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $returnedLoan = $this->createLoanFor($student);
        $returnedLoan->update(['returned_at' => now()]);
        $otherLoan = $this->createLoanFor($otherStudent);
        $otherLoan->bookCopy->update(['barcode' => 'SL-PRIVATE-001']);

        $response = $this->actingAs($student)->get(route('student.portal'));

        $response->assertOk()
            ->assertSeeText('Dikembalikan')
            ->assertSeeText($returnedLoan->bookCopy->barcode)
            ->assertDontSeeText('SL-PRIVATE-001');
    }

    public function test_student_registration_creates_a_real_student_account(): void
    {
        $response = $this->post(route('student.register.store'), [
            'name' => 'Sarah Mahasiswa',
            'student_number' => '2106728910',
            'email' => 'sarah@campus.test',
            'study_program' => 'Sistem Informasi',
            'cohort' => now()->year,
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('student.portal'));
        $this->assertDatabaseHas('users', [
            'student_number' => '2106728910',
            'role' => 'student',
            'member_status' => 'active',
        ]);
        $this->assertAuthenticated();
    }

    public function test_admin_login_does_not_accept_student_accounts(): void
    {
        $student = User::factory()->create([
            'email' => 'student@campus.test',
            'password' => 'StrongPassword123!',
            'role' => 'student',
        ]);

        $this->post(route('admin.login.store'), [
            'identity' => $student->email,
            'password' => 'StrongPassword123!',
        ])->assertSessionHasErrors('identity');
    }

    public function test_admin_login_opens_the_admin_workspace(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@campus.test',
            'password' => 'StrongPassword123!',
            'role' => 'admin',
        ]);

        $this->post(route('admin.login.store'), [
            'identity' => $admin->email,
            'password' => 'StrongPassword123!',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_student_login_accepts_their_student_number(): void
    {
        $student = User::factory()->create([
            'student_number' => '2106728910',
            'password' => 'StrongPassword123!',
            'role' => 'student',
        ]);

        $this->post(route('student.login.store'), [
            'identity' => '2106728910',
            'password' => 'StrongPassword123!',
        ])->assertRedirect(route('student.portal'));

        $this->assertAuthenticatedAs($student);
    }

    public function test_staff_checkout_and_return_update_the_copy_and_loan_records(): void
    {
        $staff = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'student']);
        $copy = $this->availableCopy();

        $this->actingAs($staff)
            ->post(route('admin.loans.store'), [
                'student_number' => $member->student_number,
                'barcode' => $copy->barcode,
            ])
            ->assertRedirect(route('admin.circulation', ['nim' => $member->student_number]));

        $this->assertDatabaseHas('book_copies', [
            'id' => $copy->id,
            'status' => BookCopy::STATUS_BORROWED,
        ]);
        $this->assertDatabaseHas('loans', [
            'user_id' => $member->id,
            'book_copy_id' => $copy->id,
            'processed_by' => $staff->id,
        ]);

        $loan = Loan::query()->where('book_copy_id', $copy->id)->firstOrFail();

        $this->post(route('admin.loans.return', $loan))->assertRedirect();

        $this->assertNotNull($loan->fresh()->returned_at);
        $this->assertDatabaseHas('book_copies', [
            'id' => $copy->id,
            'status' => BookCopy::STATUS_AVAILABLE,
        ]);
    }

    public function test_student_checkout_persists_the_loan_and_marks_the_copy_borrowed(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'password' => 'password',
        ]);
        $copy = $this->availableCopy();

        $this->actingAs($student)
            ->post(route('student.checkout.store'), [
                'barcode' => $copy->barcode,
                'password' => 'password',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('loans', [
            'user_id' => $student->id,
            'book_copy_id' => $copy->id,
        ]);
        $this->assertDatabaseHas('book_copies', [
            'id' => $copy->id,
            'status' => BookCopy::STATUS_BORROWED,
        ]);
    }

    public function test_reference_books_cannot_be_checked_out(): void
    {
        $staff = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'student']);
        $copy = $this->availableCopy(referenceOnly: true);

        $this->actingAs($staff)
            ->post(route('admin.loans.store'), [
                'student_number' => $member->student_number,
                'barcode' => $copy->barcode,
            ])
            ->assertSessionHasErrors('barcode');

        $this->assertDatabaseCount('loans', 0);
        $this->assertDatabaseHas('book_copies', [
            'id' => $copy->id,
            'status' => BookCopy::STATUS_REFERENCE_ONLY,
        ]);
    }

    public function test_students_can_only_return_their_own_loans(): void
    {
        $owner = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $loan = $this->createLoanFor($owner);

        $this->actingAs($otherStudent)
            ->post(route('student.loans.return', $loan))
            ->assertNotFound();

        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'returned_at' => null,
        ]);
    }

    private function availableCopy(bool $referenceOnly = false): BookCopy
    {
        $book = Book::query()->create([
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'reference_only' => $referenceOnly,
        ]);

        return $book->copies()->create([
            'barcode' => 'SL-'.fake()->unique()->numerify('######'),
            'status' => $referenceOnly
                ? BookCopy::STATUS_REFERENCE_ONLY
                : BookCopy::STATUS_AVAILABLE,
        ]);
    }

    private function createLoanFor(User $member): Loan
    {
        $copy = $this->availableCopy();
        $copy->update(['status' => BookCopy::STATUS_BORROWED]);

        return $member->loans()->create([
            'book_copy_id' => $copy->id,
            'borrowed_at' => now(),
            'due_at' => now()->addDays(14),
        ]);
    }
}
