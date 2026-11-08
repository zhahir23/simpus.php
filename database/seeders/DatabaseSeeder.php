<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = config('simpus.admin_email');
        $adminPassword = config('simpus.admin_password');

        if (! $adminEmail || ! $adminPassword) {
            throw new RuntimeException('Set SIMPUS_ADMIN_EMAIL and SIMPUS_ADMIN_PASSWORD before seeding.');
        }

        User::query()->firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Dwi Annisa',
                'password' => $adminPassword,
                'role' => 'admin',
                'member_status' => 'active',
            ],
        );

        foreach ($this->sampleBooks() as $bookData) {
            $copies = $bookData['copies'];
            unset($bookData['copies']);

            $book = Book::query()->firstOrCreate(
                ['isbn' => $bookData['isbn']],
                $bookData,
            );

            foreach ($copies as $copyData) {
                BookCopy::query()->firstOrCreate(
                    ['barcode' => $copyData['barcode']],
                    [
                        ...$copyData,
                        'book_id' => $book->id,
                        'status' => $book->reference_only
                            ? BookCopy::STATUS_REFERENCE_ONLY
                            : BookCopy::STATUS_AVAILABLE,
                    ],
                );
            }
        }
    }

    /**
     * @return list<array{
     *     title: string,
     *     author: string,
     *     isbn: string,
     *     publisher: string,
     *     published_year: int,
     *     synopsis: string,
     *     reference_only: bool,
     *     copies: list<array{barcode: string, shelf_location: string}>
     * }>
     */
    private function sampleBooks(): array
    {
        return [
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9780132350884',
                'publisher' => 'Prentice Hall',
                'published_year' => 2008,
                'synopsis' => 'A practical guide to writing clear, maintainable software.',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-918', 'shelf_location' => 'B-07 · Lantai 2'],
                    ['barcode' => 'SL-2024-919', 'shelf_location' => 'B-07 · Lantai 2'],
                ],
            ],
            [
                'title' => 'Pengantar Sistem Informasi',
                'author' => 'Abdul Kadir',
                'isbn' => '9789797564281',
                'publisher' => 'Andi Publisher',
                'published_year' => 2014,
                'synopsis' => 'Pengantar konsep dan penerapan sistem informasi.',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-731', 'shelf_location' => 'A-03 · Lantai 1'],
                    ['barcode' => 'SL-2024-732', 'shelf_location' => 'A-03 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Basis Data',
                'author' => 'Fathansyah',
                'isbn' => '9786021514558',
                'publisher' => 'Informatika Bandung',
                'published_year' => 2018,
                'synopsis' => 'Dasar-dasar perancangan dan pengelolaan basis data.',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2023-118', 'shelf_location' => 'A-05 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Ensiklopedia Sains',
                'author' => 'Tim Redaksi',
                'isbn' => '9786024817894',
                'publisher' => 'Pustaka Ilmu',
                'published_year' => 2020,
                'synopsis' => 'Koleksi referensi sains untuk dibaca di tempat.',
                'reference_only' => true,
                'copies' => [
                    ['barcode' => 'SL-2024-920', 'shelf_location' => 'R-01 · Lantai 2'],
                ],
            ],
            [
                'title' => 'Algoritma dan Pemrograman',
                'author' => 'Rinaldi Munir',
                'isbn' => '9786026232243',
                'publisher' => 'Informatika Bandung',
                'published_year' => 2021,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-301', 'shelf_location' => 'R-02 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Struktur Data Terapan',
                'author' => 'Bambang Wahyudi',
                'isbn' => '9789792943210',
                'publisher' => 'Andi Publisher',
                'published_year' => 2020,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-302', 'shelf_location' => 'R-02 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Rekayasa Perangkat Lunak',
                'author' => 'Roger S. Pressman',
                'isbn' => '9789792931200',
                'publisher' => 'McGraw-Hill / Andi',
                'published_year' => 2019,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-303', 'shelf_location' => 'R-02 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Pemrograman Web dengan PHP & MySQL',
                'author' => 'Betha Sidik',
                'isbn' => '9786026232502',
                'publisher' => 'Informatika Bandung',
                'published_year' => 2022,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-304', 'shelf_location' => 'R-03 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Belajar Otodidak Flask Framework',
                'author' => 'Budi Raharjo',
                'isbn' => '9786237131458',
                'publisher' => 'Informatika Bandung',
                'published_year' => 2021,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-305', 'shelf_location' => 'R-03 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Jaringan Komputer Berbasis Mikrotik',
                'author' => 'Iwan Sofana',
                'isbn' => '9786026232380',
                'publisher' => 'Informatika Bandung',
                'published_year' => 2020,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-306', 'shelf_location' => 'R-04 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Keamanan Sistem Informasi',
                'author' => 'Budi Rahardjo',
                'isbn' => '9789791834209',
                'publisher' => 'PT Insan Infonesia',
                'published_year' => 2019,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-307', 'shelf_location' => 'R-04 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Interaksi Manusia dan Komputer',
                'author' => 'Insap Santosa',
                'isbn' => '9789792901111',
                'publisher' => 'Andi Publisher',
                'published_year' => 2018,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-308', 'shelf_location' => 'R-05 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Kecerdasan Buatan (Artificial Intelligence)',
                'author' => 'Suyanto',
                'isbn' => '9786026232496',
                'publisher' => 'Informatika Bandung',
                'published_year' => 2021,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-309', 'shelf_location' => 'R-05 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Machine Learning Tingkat Dasar & Lanjut',
                'author' => 'Teguh Wahyono',
                'isbn' => '9786237498117',
                'publisher' => 'Gava Media',
                'published_year' => 2022,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-310', 'shelf_location' => 'R-05 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Sistem Operasi Modern',
                'author' => 'Andrew S. Tanenbaum',
                'isbn' => '9780133591620',
                'publisher' => 'Pearson Education',
                'published_year' => 2018,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-311', 'shelf_location' => 'R-01 · Lantai 2'],
                ],
            ],
            [
                'title' => 'Teori Bahasa dan Otomata',
                'author' => 'Firrar Utdirartatmo',
                'isbn' => '9789797560232',
                'publisher' => 'Graha Ilmu',
                'published_year' => 2019,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-312', 'shelf_location' => 'R-01 · Lantai 2'],
                ],
            ],
            [
                'title' => 'Metodologi Penelitian Bidang Komputer',
                'author' => 'Romi Satria Wahono',
                'isbn' => '9786021234567',
                'publisher' => 'Brainmedia',
                'published_year' => 2020,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-313', 'shelf_location' => 'R-06 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Desain UI/UX untuk Pemula',
                'author' => 'Eko Khannedy',
                'isbn' => '9786230021345',
                'publisher' => 'Media Komputindo',
                'published_year' => 2023,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-314', 'shelf_location' => 'R-06 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Dasar Perancangan Arsitektur Komputer',
                'author' => 'Abdul Kadir',
                'isbn' => '9789792951116',
                'publisher' => 'Andi Publisher',
                'published_year' => 2019,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-315', 'shelf_location' => 'R-01 · Lantai 2'],
                ],
            ],
            [
                'title' => 'Pemrograman Berorientasi Objek dengan Java',
                'author' => 'Indrajani',
                'isbn' => '9786020489998',
                'publisher' => 'Elex Media Komputindo',
                'published_year' => 2020,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-316', 'shelf_location' => 'R-03 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Cloud Computing: Teori dan Implementasi',
                'author' => 'Onno W. Purbo',
                'isbn' => '9789792962808',
                'publisher' => 'Andi Publisher',
                'published_year' => 2021,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-317', 'shelf_location' => 'R-04 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Data Mining: Konsep dan Aplikasi',
                'author' => 'Kusrini & Emha T. Luthfi',
                'isbn' => '9789792909180',
                'publisher' => 'Andi Publisher',
                'published_year' => 2019,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-318', 'shelf_location' => 'R-05 · Lantai 1'],
                ],
            ],
            [
                'title' => 'Manajemen Proyek Sistem Informasi',
                'author' => 'Jogiyanto Hartono',
                'isbn' => '9789792965434',
                'publisher' => 'Andi Publisher',
                'published_year' => 2020,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [
                    ['barcode' => 'SL-2024-319', 'shelf_location' => 'R-06 · Lantai 2'],
                ],
            ],
            [
                'title' => 'Logika Informatika dan Himpunan',
                'author' => 'Suprapto',
                'isbn' => '9789797563127',
                'publisher' => 'Graha Ilmu',
                'published_year' => 2018,
                'synopsis' => '',
                'reference_only' => false,
                'copies' => [],
            ],
        ];
    }
}
