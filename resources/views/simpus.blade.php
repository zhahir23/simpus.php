@php
    $isAuth = in_array($page, ['login', 'register', 'admin-login'], true);
    $isStudent = in_array($page, ['portal', 'self-checkout'], true);
    $titles = [
        'dashboard' => 'Dashboard',
        'visit-kiosk' => 'Kunjungan & QR',
        'circulation' => 'Sirkulasi POS',
        'catalog' => 'Katalog & Eksemplar',
        'members' => 'Anggota & Laporan',
        'portal' => 'Portal Mahasiswa',
        'self-checkout' => 'Pinjam Mandiri',
        'login' => 'Masuk sebagai mahasiswa',
        'register' => 'Daftar akun mahasiswa',
        'admin-login' => 'Masuk sebagai admin',
    ];
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b132b">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titles[$page] }} · SIMPUS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@if ($isAuth)
    @php
        $authCopy = [
            'login' => ['PORTAL MAHASISWA', 'Buku berikutnya, inspirasi berikutnya.', 'Temukan koleksi dari mana saja', 'Pantau pinjaman dan jatuh tempo', 'Reservasi buku tanpa antre'],
            'register' => ['PENDAFTARAN MAHASISWA', 'Mulai langkahmu. Temukan pengetahuan.', 'Lengkapi profil akademikmu', 'Verifikasi akun dan data akademik', 'Mulai jelajahi koleksi SIMPUS'],
            'admin-login' => ['PORTAL ADMIN / PETUGAS', 'Layanan tertata. Perpustakaan terkelola.', 'Kelola sirkulasi dengan lebih cepat', 'Pantau koleksi dan ketersediaan', 'Layani anggota dalam satu workspace'],
        ][$page];
    @endphp
    <main class="auth-layout">
        <aside class="auth-brand">
            <a class="brand brand-light" href="{{ route('student.login') }}">
                <span class="brand-mark"><i data-lucide="book-open"></i></span>
                <span>SIMPUS<small>Sistem Informasi Perpustakaan</small></span>
            </a>
            <div class="auth-story">
                <span class="eyebrow eyebrow-light">{{ $authCopy[0] }}</span>
                <h1>{{ $authCopy[1] }}</h1>
                <div class="story-points">
                    @foreach (array_slice($authCopy, 2) as $index => $point)
                        <div class="story-point"><span>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span><p>{{ $point }}</p></div>
                    @endforeach
                </div>
            </div>
            <div class="brand-foot"><span class="status-dot"></span> Perpustakaan Universitas · Layanan digital 24/7</div>
        </aside>
        <section class="auth-main">
            <div class="auth-toplinks">
                <a href="{{ route('student.login') }}"><i data-lucide="arrow-left"></i> Kembali ke beranda</a>
                <a href="mailto:bantuan@kampus.ac.id">Butuh bantuan? <i data-lucide="arrow-up-right"></i></a>
            </div>
            <div class="auth-card">
                @if (session('status'))
                    <div class="status-message">{{ session('status') }}</div>
                @endif
                @if ($page === 'register')
                    <span class="eyebrow">AKUN BARU</span>
                    <h2>Daftar akun mahasiswa</h2>
                    <p class="muted">Data akun akan disimpan dan digunakan untuk mengakses SIMPUS.</p>
                    <form class="demo-form" action="{{ route('student.register.store') }}" method="POST">
                        @csrf
                        <div class="form-grid">
                            <label>Nama lengkap<input name="name" value="{{ old('name') }}" autocomplete="name" required>@error('name')<small class="error-message">{{ $message }}</small>@enderror</label>
                            <label>NIM<input name="student_number" value="{{ old('student_number') }}" inputmode="numeric" required>@error('student_number')<small class="error-message">{{ $message }}</small>@enderror</label>
                            <label class="span-2">Email mahasiswa<input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>@error('email')<small class="error-message">{{ $message }}</small>@enderror</label>
                            <label>Program studi<select name="study_program" required><option value="">Pilih program studi</option>@foreach (['Sistem Informasi', 'Ilmu Komputer', 'Manajemen', 'Akuntansi', 'Teknik Informatika'] as $program)<option value="{{ $program }}" @selected(old('study_program') === $program)>{{ $program }}</option>@endforeach</select>@error('study_program')<small class="error-message">{{ $message }}</small>@enderror</label>
                            <label>Angkatan<select name="cohort" required><option value="">Pilih angkatan</option>@foreach (range(now()->year, now()->year - 8) as $year)<option value="{{ $year }}" @selected((string) old('cohort') === (string) $year)>{{ $year }}</option>@endforeach</select>@error('cohort')<small class="error-message">{{ $message }}</small>@enderror</label>
                            <label>Kata sandi<input type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter" required>@error('password')<small class="error-message">{{ $message }}</small>@enderror</label>
                            <label>Konfirmasi kata sandi<input type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required></label>
                        </div>
                        <label class="check-row"><input type="checkbox" name="terms" value="1" required @checked(old('terms'))> Saya menyetujui Ketentuan Layanan dan Kebijakan Privasi SIMPUS.</label>
                        @error('terms')<small class="error-message">{{ $message }}</small>@enderror
                        <button class="button button-primary button-wide" type="submit">Daftar sebagai mahasiswa <i data-lucide="arrow-right"></i></button>
                    </form>
                    <p class="auth-switch">Sudah memiliki akun? <a href="{{ route('student.login') }}">Masuk di sini</a></p>
                @else
                    <span class="eyebrow">{{ $page === 'login' ? 'PORTAL DIGITAL MAHASISWA' : 'WORKSPACE OPERASIONAL' }}</span>
                    <h2>{{ $page === 'login' ? 'Selamat datang kembali' : 'Masuk sebagai admin' }}</h2>
                    <p class="muted">{{ $page === 'login' ? 'Masuk untuk mengelola aktivitas perpustakaanmu.' : 'Gunakan akun petugas perpustakaan yang terdaftar.' }}</p>
                    <form class="demo-form auth-form" action="{{ $page === 'login' ? route('student.login.store') : route('admin.login.store') }}" method="POST">
                        @csrf
                        <label>{{ $page === 'login' ? 'NIM / email mahasiswa' : 'Email admin' }}<input name="identity" value="{{ old('identity') }}" autocomplete="username" required>@error('identity')<small class="error-message">{{ $message }}</small>@enderror</label>
                        <label>Kata sandi<div class="password-field"><input type="password" name="password" autocomplete="current-password" required><button class="text-button toggle-password" type="button">Tampilkan</button></div>@error('password')<small class="error-message">{{ $message }}</small>@enderror</label>
                        @if ($page === 'login')
                            <label class="check-row"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
                        @endif
                        <button class="button button-primary button-wide" type="submit">{{ $page === 'login' ? 'Masuk sebagai mahasiswa' : 'Masuk ke workspace' }} <i data-lucide="arrow-right"></i></button>
                    </form>
                    @if ($page === 'login')
                        <p class="auth-switch">Belum menjadi anggota? <a href="{{ route('student.register') }}">Daftar mahasiswa</a></p>
                        <p class="auth-switch">Admin / petugas? <a href="{{ route('admin.login') }}">Masuk di sini</a></p>
                    @else
                        <div class="info-banner"><i data-lucide="shield-check"></i><span>Login staf terpisah dari Portal Mahasiswa.</span></div>
                        <p class="auth-switch">Mahasiswa? <a href="{{ route('student.login') }}">Masuk ke portal mahasiswa</a></p>
                    @endif
                @endif
            </div>
            <footer class="auth-footer"><span>© {{ now()->year }} SIMPUS · Perpustakaan Universitas</span><span>Privasi &nbsp;·&nbsp; Bantuan</span></footer>
        </section>
    </main>
@elseif ($isStudent)
    @php($student = auth()->user())
    <header class="student-header">
        <a class="brand brand-light" href="{{ route('student.portal') }}"><span class="brand-mark"><i data-lucide="book-open"></i></span><span>SIMPUS<small>PORTAL MAHASISWA</small></span></a>
        <nav class="student-nav">
            <a class="{{ $page === 'portal' ? 'active' : '' }}" href="{{ route('student.portal') }}">Katalog & Akun</a>
            <a href="{{ route('student.portal') }}#loans">Pinjaman</a>
            <a href="{{ route('student.checkout') }}">Pinjam Mandiri</a>
        </nav>
        <div class="student-profile"><span class="avatar avatar-sky">{{ $student->name_initials }}</span><span>{{ $student->name }}<small>{{ $student->student_number }}</small></span>
            <form action="{{ route('student.logout') }}" method="POST">@csrf<button class="text-button" type="submit">Keluar</button></form>
        </div>
    </header>
    <main class="student-page">
        @if ($page === 'portal')
            <section class="welcome-banner">
                <div><span class="eyebrow eyebrow-light">PORTAL MAHASISWA</span><h1>Selamat datang, {{ $student->name }} <span>✦</span></h1><p>Temukan bacaan berikutnya dan kelola aktivitas perpustakaanmu.</p></div>
                <div class="banner-book"><i data-lucide="book-open"></i><span>{{ number_format($books->sum('available_copies_count'), 0, ',', '.') }} eksemplar tersedia</span></div>
            </section>
            <section class="panel visit-scanner block md:hidden" data-visit-scanner data-scan-url="{{ route('student.visits.scan') }}">
                <div class="panel-head"><div><h2>Presensi perpustakaan</h2><p>Pindai QR masuk atau keluar yang tampil di meja resepsionis.</p></div><span class="badge badge-blue">Kamera ponsel</span></div>
                <div class="visit-scanner-content"><button class="button button-primary" type="button" data-scanner-start><i data-lucide="scan-line"></i> Buka pemindai QR</button><div id="library-visit-reader" class="visit-reader" hidden></div></div>
                <p class="visit-scanner-status" data-scanner-status role="status" aria-live="polite">Kamera hanya digunakan ketika kamu memulai pemindaian.</p>
                <small class="visit-camera-note">Izinkan akses kamera dan gunakan halaman HTTPS atau localhost.</small>
            </section>
            @if (session('status'))<div class="status-message">{{ session('status') }}</div>@endif
            <div class="section-heading"><div><h2>Koleksi perpustakaan</h2><p>Ketersediaan buku diperbarui langsung dari katalog.</p></div><a class="button button-primary" href="{{ route('student.checkout') }}">Pinjam mandiri <i data-lucide="arrow-right"></i></a></div>
            <div class="student-book-list" aria-label="Daftar koleksi perpustakaan">
                @forelse ($books as $book)
                    <article class="student-book-row {{ $viewedBook?->is($book) ? 'selected' : '' }}">
                        <div class="student-book-cover" aria-hidden="true"><span>{{ $book->title }}</span><small>{{ $book->author }}</small></div>
                        <div class="student-book-copy">
                            <div class="student-book-title">
                                <h3>{{ $book->title }}</h3>
                                <span class="badge {{ $book->reference_only ? 'badge-orange' : ($book->available_copies_count ? 'badge-green' : 'badge-red') }}">{{ $book->reference_only ? 'Khusus baca di tempat' : ($book->available_copies_count ? 'Tersedia' : 'Tidak tersedia') }}</span>
                            </div>
                            <p class="student-book-byline">{{ $book->author }} · {{ $book->publisher ?: 'Penerbit belum tercatat' }}</p>
                            <p class="student-book-synopsis">{{ $book->synopsis ?: 'Deskripsi buku belum tersedia.' }}</p>
                            <div class="student-book-meta"><span>{{ $book->available_copies_count }} dari {{ $book->copies_count }} eksemplar tersedia</span><span>{{ $book->published_year ?: 'Tahun terbit belum tercatat' }}</span></div>
                            <div class="student-book-actions">
                                <a class="inline-link" href="{{ route('student.portal', ['book' => $book->id]) }}#recommendations">Lihat rekomendasi <i data-lucide="arrow-right"></i></a>
                                @if (! $book->reference_only && $book->available_copies_count > 0)
                                    <a class="inline-link" href="{{ route('student.checkout', ['barcode' => $book->copies->first()?->barcode]) }}">Pinjam buku <i data-lucide="book-open-check"></i></a>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="panel">Belum ada koleksi di katalog.</div>
                @endforelse
            </div>
            @if ($viewedBook)
                <section class="panel student-recommendations" id="recommendations">
                    <div class="panel-head"><div><h2>Rekomendasi berdasarkan buku ini</h2><p>Karena kamu melihat “{{ $viewedBook->title }}”.</p></div><a href="{{ route('student.portal') }}">Hapus pilihan</a></div>
                    @forelse ($recommendedBooks as $recommendation)
                        @php($recommendedBook = $recommendation['book'])
                        <article class="student-recommendation-row">
                            <div class="student-book-cover" aria-hidden="true"><span>{{ $recommendedBook->title }}</span><small>{{ $recommendedBook->author }}</small></div>
                            <div class="student-recommendation-copy">
                                <span class="eyebrow">{{ $recommendation['reason'] }}</span>
                                <h3>{{ $recommendedBook->title }}</h3>
                                <p>{{ $recommendedBook->author }} · {{ $recommendedBook->publisher ?: 'Penerbit belum tercatat' }}</p>
                                <span class="badge {{ $recommendedBook->reference_only ? 'badge-orange' : ($recommendedBook->available_copies_count ? 'badge-green' : 'badge-red') }}">{{ $recommendedBook->reference_only ? 'Khusus baca di tempat' : ($recommendedBook->available_copies_count ? $recommendedBook->available_copies_count.' eksemplar tersedia' : 'Tidak tersedia') }}</span>
                            </div>
                            <a class="inline-link" href="{{ route('student.portal', ['book' => $recommendedBook->id]) }}#recommendations">Lihat rekomendasi <i data-lucide="arrow-right"></i></a>
                        </article>
                    @empty
                        <p class="muted">Belum ada buku terkait atau alternatif yang tersedia di katalog.</p>
                    @endforelse
                </section>
            @endif
            <section class="panel student-loans" id="loans">
                <div class="panel-head"><div><h3>Pinjaman & bukti saya</h3><p>{{ $activeLoans->count() }} dari 3 buku sedang dipinjam. Bukti transaksi tersimpan di sini.</p></div><span class="badge badge-blue">{{ $activeLoans->count() }} aktif</span></div>
                @forelse ($loanHistory as $loan)
                    @php($loanStatus = $loan->returned_at ? 'returned' : ($loan->fineAmount() ? 'overdue' : 'active'))
                    <div class="active-loan">
                        <span class="book-mini-icon"><i data-lucide="book-open"></i></span>
                        <div><b>{{ $loan->bookCopy->book->title }}</b><small>{{ $loan->bookCopy->barcode }} · dipinjam {{ $loan->borrowed_at->format('d M Y') }} · jatuh tempo {{ $loan->due_at->format('d M Y') }}</small></div>
                        <span class="badge {{ $loanStatus === 'returned' ? 'badge-blue' : ($loanStatus === 'overdue' ? 'badge-red' : 'badge-green') }}">{{ $loanStatus === 'returned' ? 'Dikembalikan' : ($loanStatus === 'overdue' ? 'Terlambat · Rp '.number_format($loan->fineAmount(), 0, ',', '.') : 'Aktif') }}</span>
                        <div class="loan-actions">
                            <button class="button button-outline button-small" type="button" data-dialog-open="loan-receipt-{{ $loan->id }}"><i data-lucide="file-text"></i> Bukti pinjam</button>
                            @if (! $loan->returned_at)
                                <form action="{{ route('student.loans.return', $loan) }}" method="POST">@csrf<button class="button button-outline button-small" type="submit">Kembalikan</button></form>
                            @endif
                        </div>
                    </div>
                    <dialog class="receipt-dialog" id="loan-receipt-{{ $loan->id }}" aria-labelledby="loan-receipt-title-{{ $loan->id }}" data-receipt-dialog>
                        <div class="receipt-dialog-content">
                            <header class="receipt-dialog-header">
                                <span class="receipt-icon"><i data-lucide="file-text"></i></span>
                                <div><h2 id="loan-receipt-title-{{ $loan->id }}">Bukti peminjaman</h2><p>Nomor transaksi #{{ $loan->id }}</p></div>
                                <button class="icon-button" type="button" data-dialog-close aria-label="Tutup bukti peminjaman"><i data-lucide="x"></i></button>
                            </header>
                            <div class="receipt-dialog-body">
                                <div class="receipt-status-line"><span>Status transaksi</span><span class="badge {{ $loanStatus === 'returned' ? 'badge-blue' : ($loanStatus === 'overdue' ? 'badge-red' : 'badge-green') }}">{{ $loanStatus === 'returned' ? 'Dikembalikan' : ($loanStatus === 'overdue' ? 'Terlambat' : 'Aktif') }}</span></div>
                                <div class="receipt-book-summary"><span class="book-mini-icon"><i data-lucide="book-open"></i></span><div><b>{{ $loan->bookCopy->book->title }}</b><small>{{ $loan->bookCopy->book->author }}</small></div></div>
                                <dl class="receipt-details">
                                    <div><dt>Nama anggota</dt><dd>{{ $student->name }}</dd></div>
                                    <div><dt>NIM</dt><dd>{{ $student->student_number }}</dd></div>
                                    <div><dt>Barcode eksemplar</dt><dd>{{ $loan->bookCopy->barcode }}</dd></div>
                                    <div><dt>Tanggal pinjam</dt><dd>{{ $loan->borrowed_at->format('d M Y · H:i') }} WIB</dd></div>
                                    <div><dt>Batas pengembalian</dt><dd>{{ $loan->due_at->format('d M Y') }}</dd></div>
                                    @if ($loan->returned_at)
                                        <div><dt>Tanggal dikembalikan</dt><dd>{{ $loan->returned_at->format('d M Y · H:i') }} WIB</dd></div>
                                    @endif
                                </dl>
                                <p class="receipt-note"><i data-lucide="circle-check"></i> Bukti ini dibuat dari transaksi yang tersimpan di akun SIMPUS.</p>
                            </div>
                            <footer class="receipt-dialog-footer"><button class="button button-primary" type="button" data-dialog-close>Tutup</button></footer>
                        </div>
                    </dialog>
                @empty
                    <div class="empty-loan-state"><span class="receipt-icon"><i data-lucide="file-text"></i></span><div><b>Belum ada bukti peminjaman</b><p>Bukti peminjaman akan tersedia setelah transaksi berhasil disimpan.</p></div><button class="button button-outline button-small" type="button" data-dialog-open="missing-receipt-dialog">Lihat penjelasan</button></div>
                    <dialog class="receipt-dialog" id="missing-receipt-dialog" aria-labelledby="missing-receipt-title" data-receipt-dialog>
                        <div class="receipt-dialog-content">
                            <header class="receipt-dialog-header">
                                <span class="receipt-icon receipt-icon-muted"><i data-lucide="info"></i></span>
                                <div><h2 id="missing-receipt-title">Bukti belum tersedia</h2><p>Belum ada transaksi peminjaman tercatat</p></div>
                                <button class="icon-button" type="button" data-dialog-close aria-label="Tutup penjelasan"><i data-lucide="x"></i></button>
                            </header>
                            <div class="receipt-dialog-body">
                                <p class="receipt-empty-message">Bukti peminjaman baru dibuat setelah transaksi berhasil dikonfirmasi dan tersimpan di SIMPUS. Setelah itu, bukti bisa dibuka kembali dari bagian Pinjaman & bukti saya.</p>
                            </div>
                            <footer class="receipt-dialog-footer"><a class="button button-primary" href="{{ route('student.portal') }}">Lihat katalog</a><button class="button button-outline" type="button" data-dialog-close>Tutup</button></footer>
                        </div>
                    </dialog>
                @endforelse
            </section>
        @else
            @php($state = $state ?? request('state', 'confirm'))
            <div class="section-heading checkout-heading">
                <div><span class="eyebrow">PINJAM MANDIRI</span><h1>{{ $state === 'success' ? 'Peminjaman berhasil' : ($state === 'blocked' ? 'Buku ini tidak dapat dipinjam' : 'Konfirmasi peminjaman') }}</h1><p>Transaksi akan tercatat ke akun dan database perpustakaan.</p></div>
                <span class="badge badge-blue">AKUN TERVERIFIKASI</span>
            </div>
            <div class="stepper"><div class="step {{ $state === 'blocked' ? 'current' : 'complete' }}"><span>1</span><b>Pilih buku</b></div><i></i><div class="step {{ $state === 'confirm' ? 'current' : ($state === 'success' ? 'complete' : '') }}"><span>2</span><b>Verifikasi</b></div><i></i><div class="step {{ $state === 'success' ? 'current' : '' }}"><span>3</span><b>Selesai</b></div></div>
            <div class="checkout-grid">
                <section class="panel checkout-book">
                    @if ($state === 'success' && $loan)
                        <div class="success-header"><i data-lucide="circle-check"></i><div><b>Bukti peminjaman</b><span>Transaksi #{{ $loan->id }} tersimpan · {{ $loan->borrowed_at->format('d M Y H:i') }}</span></div><span class="badge badge-green">TERCATAT</span></div>
                        @php($book = $loan->bookCopy->book)
                    @elseif ($copy)
                        @php($book = $copy->book)
                    @endif
                    @if (isset($book))
                        <div class="book-detail"><div class="book-cover cover-blue large">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::limit($book->title, 16, '')) }}</div><div><span class="eyebrow">EKSEMPLAR {{ $loan?->bookCopy->barcode ?? $copy?->barcode }}</span><h2>{{ $book->title }}</h2><p>{{ $book->author }} · {{ $book->publisher }}</p><span class="badge {{ $book->reference_only ? 'badge-red' : 'badge-green' }}">{{ $book->reference_only ? 'Khusus baca di tempat' : ($copy?->status === 'available' || $loan ? 'Tersedia untuk dipinjam' : 'Tidak tersedia') }}</span></div></div>
                    @endif
                    @if ($loan)
                        <div class="book-meta"><div><span>Nomor transaksi</span><b>#{{ $loan->id }}</b></div><div><span>Tanggal pinjam</span><b>{{ $loan->borrowed_at->format('d M Y') }}</b></div><div><span>Jatuh tempo</span><b>{{ $loan->due_at->format('d M Y') }}</b></div></div>
                    @elseif ($copy)
                        <div class="book-meta"><div><span>Durasi pinjam</span><b>14 hari</b></div><div><span>Lokasi</span><b>{{ $copy->shelf_location ?? 'Perpustakaan' }}</b></div><div><span>Kuota digunakan</span><b>{{ $activeLoanCount }} / 3</b></div></div>
                    @else
                        <div class="book-detail"><div><span class="eyebrow">PINJAM BUKU</span><h2>Pindai atau masukkan barcode</h2><p>Barcode tersedia pada label di sampul belakang buku.</p></div></div>
                    @endif
                    @if ($state !== 'success')
                        <form class="data-form" action="{{ route('student.checkout.store') }}" method="POST">
                            @csrf
                            <label>Barcode eksemplar<input name="barcode" value="{{ old('barcode', $copy?->barcode ?? request('barcode')) }}" placeholder="Contoh: SL-2024-918" required>@error('barcode')<small class="error-message">{{ $message }}</small>@enderror</label>
                            <label>Konfirmasi kata sandi akun<input type="password" name="password" autocomplete="current-password" required>@error('password')<small class="error-message">{{ $message }}</small>@enderror</label>
                            <p class="field-help">Peminjaman langsung tersimpan dengan jatuh tempo 14 hari. Maksimal 3 buku.</p>
                            <button class="button button-primary button-wide" type="submit">Proses peminjaman <i data-lucide="arrow-right"></i></button>
                        </form>
                    @endif
                </section>
                <aside class="panel verify-panel">
                    @if ($state === 'blocked')
                        <span class="quick-icon red"><i data-lucide="shield-alert"></i></span><span class="eyebrow">PEMBATASAN KOLEKSI</span><h2>Tidak boleh dibawa keluar</h2><p class="muted">Eksemplar referensi hanya dapat dibaca di tempat dan tidak tersedia untuk peminjaman.</p><a class="button button-primary button-wide" href="{{ route('student.checkout') }}">Scan buku lain <i data-lucide="scan-line"></i></a>
                    @elseif ($state === 'success' && $loan)
                        <span class="quick-icon mint"><i data-lucide="circle-check"></i></span><span class="eyebrow">TRANSAKSI TERSIMPAN</span><h2>Buku sudah masuk ke pinjamanmu</h2><p class="muted">Data dapat dilihat kembali melalui daftar pinjaman pada portal mahasiswa.</p><div class="due-reminder"><span>BATAS PENGEMBALIAN</span><b>{{ $loan->due_at->format('d M Y') }}</b></div><a class="button button-primary button-wide" href="{{ route('student.portal') }}#loans">Lihat pinjaman saya <i data-lucide="arrow-right"></i></a>
                    @else
                        <span class="eyebrow">RINGKASAN PINJAMAN</span><h2>Kuota akun</h2><p class="muted">Peminjaman memerlukan buku tersedia dan akun tanpa keterlambatan.</p><div class="due-reminder"><span>PINJAMAN AKTIF</span><b>{{ $activeLoanCount }} / 3 buku</b></div><a class="button button-outline button-wide" href="{{ route('student.portal') }}">Kembali ke katalog</a>
                    @endif
                </aside>
            </div>
        @endif
    </main>
@else
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <a class="brand brand-light" href="{{ route('admin.dashboard') }}"><span class="brand-mark"><i data-lucide="book-open"></i></span><span>SIMPUS<small>PERPUSTAKAAN UNIVERSITAS</small></span></a>
            <div class="workspace-label">WORKSPACE PETUGAS</div>
            <nav class="side-nav">
                <a class="{{ $page === 'dashboard' ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i data-lucide="layout-dashboard"></i>Dashboard</a>
                    <a class="{{ $page === 'visit-kiosk' ? 'active' : '' }}" href="{{ route('admin.visits.kiosk') }}"><i data-lucide="scan-line"></i>Kunjungan & QR</a>
                    <a class="{{ $page === 'circulation' ? 'active' : '' }}" href="{{ route('admin.circulation') }}"><i data-lucide="repeat-2"></i>Sirkulasi POS</a>
                <a class="{{ $page === 'catalog' ? 'active' : '' }}" href="{{ route('admin.books.index') }}"><i data-lucide="library"></i>Katalog & Eksemplar</a>
                <a class="{{ $page === 'members' ? 'active' : '' }}" href="{{ route('admin.members.index') }}"><i data-lucide="users-round"></i>Anggota & Laporan</a>
            </nav>
            <div class="sidebar-spacer"></div>
            <div class="service-card"><span class="service-symbol"><i data-lucide="radio"></i></span><div><b>Layanan sirkulasi</b><small><span class="status-dot"></span> Online · Meja 01</small></div></div>
            <div class="staff-profile"><span class="avatar avatar-violet">{{ auth()->user()->name_initials }}</span><span><b>{{ auth()->user()->name }}</b><small>Administrator</small></span><form action="{{ route('admin.logout') }}" method="POST">@csrf<button class="text-button" type="submit">Keluar</button></form></div>
        </aside>
        <div class="app-main">
            <header class="topbar">
                <button class="icon-button mobile-menu" data-menu-toggle aria-label="Buka navigasi"><i data-lucide="menu"></i></button>
                <div class="breadcrumbs"><a href="{{ route('admin.dashboard') }}">Workspace</a><i data-lucide="chevron-right"></i><b>{{ $titles[$page] }}</b></div>
                <div class="topbar-actions"><span class="live-clock"><i data-lucide="clock-3"></i><span data-clock></span></span><a class="button button-outline button-small pos-dropdown" href="{{ route('admin.circulation') }}"><i data-lucide="scan-line"></i> Mode POS <i data-lucide="chevron-down"></i></a></div>
            </header>
            <main class="page-content">
                @if (session('status'))<div class="status-message">{{ session('status') }}</div>@endif
                @if (session('temporary_password'))<div class="info-banner"><i data-lucide="key-round"></i><span>Kata sandi sementara anggota baru: <b>{{ session('temporary_password') }}</b>. Salin sekarang karena tidak ditampilkan lagi.</span></div>@endif
                @if ($page === 'visit-kiosk')
                    <div class="page-heading"><div><span class="eyebrow">PRESENSI PERPUSTAKAAN</span><h1>Kunjungan & QR</h1><p>Satu QR untuk masuk dan keluar. QR dinamis diperbarui otomatis setiap 15 detik.</p></div><a class="button button-outline" href="{{ route('admin.dashboard') }}"><i data-lucide="arrow-left"></i> Kembali ke dashboard</a></div>
                    <div class="visit-kiosk-grid" data-visit-kiosk data-token-url="{{ $qrTokenUrl }}">
                        <section class="panel visit-qr-card">
                            <div class="panel-head"><div><h2>QR kunjungan mahasiswa</h2><p>Scan pertama untuk masuk. Scan berikutnya untuk keluar; setelah keluar, scan lagi untuk masuk kembali.</p></div><span class="badge badge-green">QR dinamis</span></div>
                            <canvas class="visit-qr-canvas" data-visit-qr aria-label="QR kunjungan mahasiswa"></canvas>
                            <p class="visit-qr-expiry" data-visit-expiry aria-live="polite">Memuat QR...</p>
                        </section>
                        <p class="visit-kiosk-status" data-visit-kiosk-status role="status" aria-live="polite">Menghubungkan ke server...</p>
                    </div>
                @elseif ($page === 'dashboard')
                    <div class="page-heading"><div><span class="eyebrow">{{ now()->translatedFormat('l, d F Y') }}</span><h1>Dashboard</h1><p>Data sirkulasi dan kunjungan tersinkron langsung dari database.</p></div><div class="heading-actions"><a class="button button-outline" href="{{ route('admin.visits.kiosk') }}"><i data-lucide="scan-line"></i> Layar QR kunjungan</a><a class="button button-primary" href="{{ route('admin.circulation') }}"><i data-lucide="plus"></i> Buka transaksi POS</a></div></div>
                    <div class="metric-grid">
                        @foreach ([['Peminjaman hari ini', 'borrowed_today', 'book-up-2', 'blue'], ['Pengembalian hari ini', 'returned_today', 'book-down', 'mint'], ['Pinjaman aktif', 'active_loans', 'book-open-check', 'violet'], ['Eksemplar terlambat', 'overdue_copies', 'triangle-alert', 'gold'], ['Kunjungan hari ini', 'visits_today', 'users-round', 'mint']] as [$label, $key, $icon, $color])
                            <article class="metric-card"><div class="metric-top"><span>{{ $label }}</span><span class="metric-icon {{ $color }}"><i data-lucide="{{ $icon }}"></i></span></div><strong>{{ number_format($metrics[$key], 0, ',', '.') }}</strong><div class="metric-foot"><span class="badge {{ $key === 'overdue_copies' && $metrics[$key] ? 'badge-orange' : 'badge-green' }}">{{ $key === 'overdue_copies' ? 'Data aktual' : 'Tersimpan' }}</span></div></article>
                        @endforeach
                    </div>
                    <div class="dashboard-grid">
                        <section class="panel trend-panel"><div class="panel-head"><div><h2>Tren sirkulasi</h2><p>Jumlah transaksi pinjam · 7 hari terakhir</p></div><span class="badge badge-blue">Database</span></div><div class="chart"><div class="chart-y"><span>{{ max(1, $chartData->max('count')) }}</span><span>{{ (int) ($chartData->max('count') / 2) }}</span><span>0</span></div><div class="chart-area"><div class="chart-gridlines"><i></i><i></i><i></i></div><div class="bars">@foreach ($chartData as $point)<div class="bar-item"><div class="bar {{ $loop->last ? 'bar-current' : '' }}" style="height: {{ $chartData->max('count') ? max(5, $point['count'] / $chartData->max('count') * 100) : 5 }}%"><span>{{ $point['count'] }}</span></div><small>{{ $point['label'] }}</small></div>@endforeach</div></div></div></section>
                        <section class="panel trend-panel visit-trend-panel"><div class="panel-head"><div><h2>Tren kunjungan</h2><p>Jumlah mahasiswa · Senin–Sabtu</p></div><span class="badge badge-green">{{ number_format($metrics['visits_today'], 0, ',', '.') }} hari ini</span></div><div class="chart"><div class="chart-y"><span>{{ max(1, $visitChartData->max('count')) }}</span><span>{{ (int) ($visitChartData->max('count') / 2) }}</span><span>0</span></div><div class="chart-area"><div class="chart-gridlines"><i></i><i></i><i></i></div><div class="bars visit-bars">@foreach ($visitChartData as $point)<div class="bar-item"><div class="bar {{ $point['is_today'] ? 'bar-current' : '' }}" style="height: {{ $visitChartData->max('count') ? max(5, $point['count'] / $visitChartData->max('count') * 100) : 5 }}%"><span>{{ $point['count'] }}</span></div><small>{{ $point['label'] }}</small></div>@endforeach</div></div></div></section>
                        <section class="panel attention-panel"><div class="panel-head"><div><h2>Perlu perhatian</h2><p>Pinjaman mendekati tenggat</p></div><span class="badge badge-orange">{{ $attentionLoans->count() }} item</span></div>@forelse ($attentionLoans as $loan)<div class="attention-item"><span class="attention-icon gold"><i data-lucide="calendar-clock"></i></span><div><b>{{ $loan->user->name }}</b><small>{{ $loan->bookCopy->book->title }} · jatuh tempo {{ $loan->due_at->format('d M') }}</small></div></div>@empty<p class="muted">Belum ada pinjaman yang jatuh tempo dalam dua hari.</p>@endforelse<a class="button button-outline button-wide button-small" href="{{ route('admin.members.index') }}">Lihat direktori anggota <i data-lucide="arrow-right"></i></a></section>
                        <section class="panel activity-panel"><div class="panel-head"><div><h2>Sirkulasi terbaru</h2><p>Transaksi yang benar-benar tercatat</p></div><a href="{{ route('admin.circulation') }}" class="inline-link">Buka POS <i data-lucide="arrow-right"></i></a></div><div class="table-wrap"><table><thead><tr><th>Waktu</th><th>Anggota</th><th>Judul / Eksemplar</th><th>Status</th><th>Petugas</th></tr></thead><tbody>@forelse ($recentLoans as $loan)<tr><td>{{ $loan->borrowed_at->format('H:i') }}</td><td><b>{{ $loan->user->name }}</b><small>{{ $loan->user->student_number }}</small></td><td><b>{{ $loan->bookCopy->book->title }}</b><small>{{ $loan->bookCopy->barcode }}</small></td><td><span class="badge {{ $loan->returned_at ? 'badge-green' : 'badge-blue' }}">{{ $loan->returned_at ? 'Kembali' : 'Dipinjam' }}</span></td><td>{{ $loan->processedBy?->name ?? 'Mandiri' }}</td></tr>@empty<tr><td colspan="5">Belum ada transaksi sirkulasi.</td></tr>@endforelse</tbody></table></div></section>
                        <section class="panel activity-panel visit-activity-panel"><div class="panel-head"><div><h2>Mahasiswa berkunjung hari ini</h2><p>Daftar check-in perpustakaan</p></div><span class="badge badge-blue">{{ $todayVisits->count() }} kunjungan</span></div><div class="table-wrap"><table><thead><tr><th>Nama</th><th>NIM</th><th>Jam masuk</th><th>Jam keluar</th><th>Status</th></tr></thead><tbody>@forelse ($todayVisits as $visit)<tr><td><b>{{ $visit->user->name }}</b></td><td>{{ $visit->user->student_number ?: '—' }}</td><td>{{ $visit->check_in_at?->format('H:i') ?? '—' }}</td><td>{{ $visit->check_out_at?->format('H:i') ?? '—' }}</td><td><span class="badge {{ $visit->check_out_at ? 'badge-green' : 'badge-blue' }}">{{ $visit->check_out_at ? 'Selesai' : 'Di perpustakaan' }}</span></td></tr>@empty<tr><td colspan="5">Belum ada mahasiswa yang berkunjung hari ini.</td></tr>@endforelse</tbody></table></div></section>
                        <section class="panel room-panel"><div class="panel-head"><div><h2>Ketersediaan koleksi</h2><p>Jumlah eksemplar yang siap dipinjam</p></div><span class="badge badge-green">Aktual</span></div><div class="room-occupancy"><div><span>Eksemplar tersedia</span><b>{{ number_format($availableCopies, 0, ',', '.') }}</b></div><div  class="progress"><i style="width:{{ $availablePercentage }}%"></i></div><small>{{ $availablePercentage }}% dari seluruh eksemplar tersedia.</small></div><div class="room-stats"><div><span>Pinjaman aktif</span><b>{{ number_format($metrics['active_loans'], 0, ',', '.') }}</b></div><div><span>Terlambat</span><b>{{ number_format($metrics['overdue_copies'], 0, ',', '.') }}</b></div></div><a href="{{ route('admin.books.index') }}" class="inline-link">Kelola katalog <i data-lucide="arrow-right"></i></a></section>
                    </div>
                @elseif ($page === 'circulation')
                    <div class="page-heading"><div><span class="eyebrow">MEJA SIRKULASI · MEJA 01</span><h1>Sirkulasi POS</h1><p>Proses pinjaman dan pengembalian yang langsung tercatat.</p></div><span class="badge badge-green"><i data-lucide="radio"></i> Siap melayani</span></div>
                    <div class="tabs"><a class="selected" href="{{ route('admin.circulation') }}">Peminjaman / Pengembalian</a><a href="#transactions">Transaksi terbaru</a></div>
                    <div class="pos-grid"><div class="pos-main">
                        <section class="panel"><div class="panel-head"><div><h2>1. Identifikasi anggota</h2><p>Cari data anggota terdaftar dengan NIM.</p></div><span class="step-number">01</span></div><form class="search-field scan-field" action="{{ route('admin.circulation') }}" method="GET"><i data-lucide="scan-line"></i><input aria-label="NIM anggota" name="nim" value="{{ $member?->student_number ?? request('nim') }}" placeholder="Masukkan NIM mahasiswa"><button class="button button-outline button-small" type="submit">Cari anggota</button></form>@error('student_number')<small class="error-message">{{ $message }}</small>@enderror
                            @if ($member)<div class="member-ident"><span class="avatar avatar-sky avatar-large">{{ $member->name_initials }}</span><div><b>{{ $member->name }}</b><small>{{ $member->student_number }} · {{ $member->study_program }}</small></div><span class="badge {{ $member->member_status === 'active' ? 'badge-green' : 'badge-red' }}">{{ $member->member_status === 'active' ? 'Aktif' : 'Diblokir' }}</span></div><div class="quota-grid"><div><span>Pinjaman aktif</span><b>{{ $activeLoans->count() }} <small>/ 3 buku</small></b></div><div><span>Sisa kapasitas</span><b>{{ max(0, 3 - $activeLoans->count()) }} <small>buku</small></b></div><div><span>Denda berjalan</span><b>Rp {{ number_format($activeLoans->sum(fn ($loan) => $loan->fineAmount()), 0, ',', '.') }}</b></div></div>@endif
                        </section>
                        <section class="panel"><div class="panel-head"><div><h2>2. Pindai eksemplar fisik</h2><p>Masukkan barcode pada label buku yang akan dipinjam.</p></div><span class="step-number">02</span></div><form class="data-form" action="{{ route('admin.loans.store') }}" method="POST">@csrf<input type="hidden" name="student_number" value="{{ $member?->student_number ?? request('nim') }}"><label>Barcode eksemplar<input class="scan-strong" name="barcode" value="{{ old('barcode') }}" placeholder="Contoh: SL-2024-918" required>@error('barcode')<small class="error-message">{{ $message }}</small>@enderror</label><p class="field-help">Setiap peminjaman langsung memperbarui status eksemplar.</p><button class="button button-primary" type="submit" @disabled(! $member)>Simpan peminjaman <i data-lucide="arrow-right"></i></button></form>@if ($member)<div class="table-wrap"><table><thead><tr><th>Pinjaman aktif</th><th>Barcode</th><th>Jatuh tempo</th><th>Aksi</th></tr></thead><tbody>@forelse ($activeLoans as $loan)<tr><td><b>{{ $loan->bookCopy->book->title }}</b><small>{{ $loan->borrowed_at->format('d M Y') }}</small></td><td>{{ $loan->bookCopy->barcode }}</td><td>{{ $loan->due_at->format('d M Y') }}</td><td><form action="{{ route('admin.loans.return', $loan) }}" method="POST">@csrf<button class="button button-outline button-small" type="submit">Catat kembali</button></form></td></tr>@empty<tr><td colspan="4">Belum ada buku aktif untuk anggota ini.</td></tr>@endforelse</tbody></table></div>@endif</section>
                    </div><aside class="panel summary-panel"><div class="panel-head"><div><h2>Aturan peminjaman</h2><p>Validasi dilakukan sebelum transaksi disimpan.</p></div></div><div class="policy-card"><i data-lucide="info"></i><div><b>Kuota mahasiswa</b><p>Maksimal 3 buku dengan durasi pinjam 14 hari.</p></div></div><div class="policy-card"><i data-lucide="shield-check"></i><div><b>Kelayakan akun</b><p>Akun harus aktif dan bebas dari pinjaman terlambat.</p></div></div><div class="policy-card"><i data-lucide="barcode"></i><div><b>Eksemplar fisik</b><p>Hanya status tersedia yang dapat dipinjam; koleksi referensi tetap di tempat.</p></div></div><hr><h3 id="transactions">Transaksi terbaru</h3><div class="table-wrap"><table><tbody>@forelse ($recentLoans->take(5) as $loan)<tr><td><b>{{ $loan->user->name }}</b><small>{{ $loan->bookCopy->book->title }}</small></td><td><span class="badge {{ $loan->returned_at ? 'badge-green' : 'badge-blue' }}">{{ $loan->returned_at ? 'Kembali' : 'Dipinjam' }}</span></td></tr>@empty<tr><td>Belum ada transaksi.</td></tr>@endforelse</tbody></table></div></aside></div>
                @elseif ($page === 'catalog')
                    <div class="page-heading"><div><span class="eyebrow">MANAJEMEN KOLEKSI</span><h1>Katalog & Eksemplar</h1><p>Bibliografi dan salinan fisik disimpan di database.</p></div></div>
                    <div class="stock-summary"><span><b>{{ number_format($books->total(), 0, ',', '.') }}</b> bibliografi ditampilkan</span><span><b>{{ number_format($books->sum(fn ($book) => $book->copies->count()), 0, ',', '.') }}</b> eksemplar di halaman ini</span><span><i class="status-dot"></i> Koleksi aktif</span></div>
                    <div class="catalog-grid">
                        <section class="panel bibliography-panel"><div class="panel-head"><div><h2>Bibliografi</h2><p>Pilih judul untuk melihat data.</p></div></div><form class="search-field" action="{{ route('admin.books.index') }}" method="GET"><i data-lucide="search"></i><input name="search" value="{{ request('search') }}" placeholder="Cari judul, ISBN, atau penulis..." aria-label="Cari bibliografi"><button class="text-button" type="submit">Cari</button></form><div class="bibliography-list">@forelse ($books as $book)<a href="{{ route('admin.books.index', ['book' => $book->id, 'search' => request('search')]) }}" class="bibliography-row {{ $selectedBook?->id === $book->id ? 'chosen' : '' }}"><span class="book-thumb"><i data-lucide="book-open"></i></span><span><b>{{ $book->title }}</b><small>{{ $book->author }} · {{ $book->copies->count() }} eksemplar</small></span><span class="badge {{ $book->available_copies_count ? 'badge-green' : 'badge-orange' }}">{{ $book->available_copies_count }} tersedia</span></a>@empty<p class="muted">Belum ada bibliografi yang cocok.</p>@endforelse</div>{{ $books->links() }}</section>
                        <div class="catalog-detail">
                            @if ($selectedBook)<section class="panel metadata-panel"><div class="panel-head"><div><span class="eyebrow">ISBN {{ $selectedBook->isbn ?? 'BELUM DIISI' }}</span><h2>{{ $selectedBook->title }}</h2><p>{{ $selectedBook->author }} · {{ $selectedBook->publisher }}</p></div><span class="badge {{ $selectedBook->reference_only ? 'badge-orange' : 'badge-green' }}">{{ $selectedBook->reference_only ? 'Referensi' : 'Sirkulasi' }}</span></div><div class="metadata-body"><div class="book-cover large">SIMPUS</div><div class="metadata-copy"><div class="metadata-facts"><div><small>Tahun terbit</small><b>{{ $selectedBook->published_year ?? '—' }}</b></div><div><small>Eksemplar</small><b>{{ $selectedBook->copies->count() }}</b></div><div><small>Tersedia</small><b>{{ $selectedBook->available_copies_count }}</b></div></div><p>{{ $selectedBook->synopsis ?: 'Belum ada sinopsis.' }}</p></div></div></section>
                            <section class="panel"><div class="panel-head"><div><h2>Eksemplar fisik</h2><p>Barcode dan lokasi rak.</p></div></div><div class="table-wrap"><table><thead><tr><th>Barcode</th><th>Status</th><th>Lokasi</th></tr></thead><tbody>@forelse ($selectedBook->copies as $copy)<tr><td><b>{{ $copy->barcode }}</b></td><td><span class="badge {{ $copy->status === 'available' ? 'badge-green' : ($copy->status === 'borrowed' ? 'badge-blue' : 'badge-orange') }}">{{ str_replace('_', ' ', strtoupper($copy->status)) }}</span></td><td>{{ $copy->shelf_location ?? 'Belum ditentukan' }}</td></tr>@empty<tr><td colspan="3">Belum ada eksemplar.</td></tr>@endforelse</tbody></table></div><form class="data-form inline-create" action="{{ route('admin.copies.store') }}" method="POST">@csrf<input type="hidden" name="book_id" value="{{ $selectedBook->id }}"><label>Barcode eksemplar baru<input name="barcode" placeholder="Contoh: SL-2026-001" required></label><label>Lokasi rak<input name="shelf_location" placeholder="Rak B-07 · Lantai 2"></label><button class="button button-outline button-small" type="submit">Tambah eksemplar</button></form></section>@endif
                            <details class="panel create-panel"><summary><i data-lucide="plus"></i> Tambah bibliografi dan eksemplar</summary><form class="data-form" action="{{ route('admin.books.store') }}" method="POST">@csrf<div class="form-grid"><label>Judul buku<input name="title" value="{{ old('title') }}" required>@error('title')<small class="error-message">{{ $message }}</small>@enderror</label><label>Penulis<input name="author" value="{{ old('author') }}" required>@error('author')<small class="error-message">{{ $message }}</small>@enderror</label><label>ISBN<input name="isbn" value="{{ old('isbn') }}">@error('isbn')<small class="error-message">{{ $message }}</small>@enderror</label><label>Penerbit<input name="publisher" value="{{ old('publisher') }}"></label><label>Tahun terbit<input type="number" name="published_year" min="1000" max="{{ now()->year }}" value="{{ old('published_year') }}"></label><label>Barcode eksemplar pertama<input name="barcode" value="{{ old('barcode') }}" required>@error('barcode')<small class="error-message">{{ $message }}</small>@enderror</label><label class="span-2">Lokasi rak<input name="shelf_location" value="{{ old('shelf_location') }}"></label><label class="span-2">Sinopsis<textarea name="synopsis" rows="3">{{ old('synopsis') }}</textarea></label></div><label class="check-row"><input type="checkbox" name="reference_only" value="1" @checked(old('reference_only'))> Koleksi referensi, hanya baca di tempat</label><button class="button button-primary" type="submit">Simpan bibliografi</button></form></details>
                        </div>
                    </div>
                @elseif ($page === 'members')
                    <div class="page-heading"><div><span class="eyebrow">DATA & ANALITIK</span><h1>Anggota & Laporan</h1><p>Daftar anggota, status blokir, dan sirkulasi aktual.</p></div></div>
                    <div class="tabs"><a class="{{ request('status') !== 'blocked' ? 'selected' : '' }}" href="{{ route('admin.members.index') }}">Anggota ({{ number_format($members->total(), 0, ',', '.') }})</a><a class="{{ request('status') === 'blocked' ? 'selected' : '' }}" href="{{ route('admin.members.index', ['status' => 'blocked']) }}">Diblokir ({{ $blockedCount }})</a></div>
                    <section class="panel directory-panel"><div class="panel-head"><div><h2>Daftar anggota</h2><p>Data anggota terdaftar dan status akun.</p></div></div><form class="filter-row" action="{{ route('admin.members.index') }}" method="GET"><div class="search-field"><i data-lucide="search"></i><input name="search" value="{{ request('search') }}" placeholder="Cari nama, NIM, atau email..." aria-label="Cari anggota"></div><button class="button button-outline button-small" type="submit"><i data-lucide="search"></i> Cari</button></form><div class="table-wrap"><table><thead><tr><th>Nama anggota</th><th>NIM / Email</th><th>Program studi</th><th>Pinjaman</th><th>Denda</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@forelse ($members as $member)<tr><td><div class="table-person"><span class="avatar avatar-small {{ $member->member_status === 'active' ? 'avatar-sky' : 'avatar-red' }}">{{ $member->name_initials }}</span><b>{{ $member->name }}</b></div></td><td>{{ $member->student_number }}<small>{{ $member->email }}</small></td><td>{{ $member->study_program }}</td><td>{{ $member->loans->count() }} / 3</td><td>Rp {{ number_format($member->loans->sum(fn ($loan) => $loan->fineAmount()), 0, ',', '.') }}</td><td><span class="badge {{ $member->member_status === 'active' ? 'badge-green' : 'badge-red' }}">{{ $member->member_status === 'active' ? 'Aktif' : 'Diblokir' }}</span></td><td><form action="{{ route('admin.members.status', $member) }}" method="POST">@csrf<button class="text-button" type="submit">{{ $member->member_status === 'active' ? 'Blokir' : 'Aktifkan' }}</button></form></td></tr>@empty<tr><td colspan="7">Belum ada anggota yang cocok.</td></tr>@endforelse</tbody></table></div><div class="pagination"><span>Menampilkan {{ $members->firstItem() ?? 0 }}–{{ $members->lastItem() ?? 0 }} dari {{ $members->total() }} anggota</span>{{ $members->links() }}</div></section>
                    <div class="members-lower"><div class="lower-stack"><section class="panel"><div class="panel-head"><div><h2>Keterlambatan & denda</h2><p>Denda berjalan dihitung Rp 1.000 per hari setelah tenggat.</p></div><span class="badge badge-orange">{{ $activeLoans->count() }} pinjaman</span></div>@forelse ($activeLoans as $loan)<div class="late-row"><div><b>{{ $loan->user->name }}</b><small>{{ $loan->bookCopy->book->title }} · lewat {{ $loan->due_at->format('d M Y') }}</small></div><b class="text-red">Rp {{ number_format($loan->fineAmount(), 0, ',', '.') }}</b></div>@empty<p class="muted">Tidak ada pinjaman terlambat.</p>@endforelse<div class="fine-note"><i data-lucide="info"></i> Denda ilustratif dihitung per hari kalender untuk pinjaman yang masih aktif.</div></section>
                        <details class="panel create-panel"><summary><i data-lucide="user-plus"></i> Tambah anggota baru</summary><p class="muted">Kata sandi sementara dibuat otomatis dan hanya ditampilkan sekali setelah akun disimpan.</p><form class="data-form" action="{{ route('admin.members.store') }}" method="POST">@csrf<div class="form-grid"><label>Nama lengkap<input name="name" required></label><label>NIM<input name="student_number" required>@error('student_number')<small class="error-message">{{ $message }}</small>@enderror</label><label>Email<input type="email" name="email" required>@error('email')<small class="error-message">{{ $message }}</small>@enderror</label><label>Program studi<input name="study_program" required></label><label>Angkatan<input type="number" name="cohort" min="2000" max="{{ now()->year }}" required></label></div><button class="button button-primary" type="submit">Simpan anggota</button></form></details></div>
                        <section class="panel reports-panel"><div class="panel-head"><div><h2>Laporan sirkulasi</h2><p>Transaksi bulan ini tersimpan di database.</p></div><span class="badge badge-blue">{{ now()->format('F Y') }}</span></div><div class="report-metrics"><div><span>Peminjaman</span><b>{{ number_format($loanTotals['borrowed'], 0, ',', '.') }}</b></div><div><span>Pengembalian</span><b>{{ number_format($loanTotals['returned'], 0, ',', '.') }}</b></div></div><h3>Ringkasan anggota</h3><div class="room-stats"><div><span>Mahasiswa terdaftar</span><b>{{ number_format(\App\Models\User::query()->where('role', 'student')->count(), 0, ',', '.') }}</b></div><div><span>Akun diblokir</span><b>{{ number_format($blockedCount, 0, ',', '.') }}</b></div></div></section></div>
                @endif
            </main>
        </div>
    </div>
    <div class="toast" role="status" aria-live="polite"></div>
@endif
</body>
</html>
