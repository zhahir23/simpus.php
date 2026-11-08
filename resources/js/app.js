import {
    createIcons,
    ArrowDownLeft,
    ArrowLeft,
    ArrowRight,
    ArrowUpRight,
    Barcode,
    Bell,
    BookCheck,
    BookDown,
    BookOpen,
    BookOpenCheck,
    BookUp2,
    Bookmark,
    CalendarClock,
    CalendarDays,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    ChevronsUpDown,
    CircleCheck,
    Clock3,
    Download,
    FileDown,
    FileText,
    Info,
    KeyRound,
    LayoutDashboard,
    Library,
    MapPin,
    Menu,
    MoreHorizontal,
    Pencil,
    Plus,
    Printer,
    Radio,
    Repeat2,
    ScanLine,
    Search,
    ShieldAlert,
    ShieldCheck,
    SlidersHorizontal,
    Trash2,
    TriangleAlert,
    Upload,
    UserPlus,
    UsersRound,
    X,
} from 'lucide';
import { Html5Qrcode } from 'html5-qrcode';
import QRCode from 'qrcode';

const iconSet = {
    ArrowDownLeft,
    ArrowLeft,
    ArrowRight,
    ArrowUpRight,
    Barcode,
    Bell,
    BookCheck,
    BookDown,
    BookOpen,
    BookOpenCheck,
    BookUp2,
    Bookmark,
    CalendarClock,
    CalendarDays,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    ChevronsUpDown,
    CircleCheck,
    Clock3,
    Download,
    FileDown,
    FileText,
    Info,
    KeyRound,
    LayoutDashboard,
    Library,
    MapPin,
    Menu,
    MoreHorizontal,
    Pencil,
    Plus,
    Printer,
    Radio,
    Repeat2,
    ScanLine,
    Search,
    ShieldAlert,
    ShieldCheck,
    SlidersHorizontal,
    Trash2,
    TriangleAlert,
    Upload,
    UserPlus,
    UsersRound,
    X,
};

createIcons({ icons: iconSet });

document.querySelector('[data-menu-toggle]')?.addEventListener('click', () => {
    document.querySelector('#sidebar')?.classList.toggle('open');
});

document.querySelectorAll('.toggle-password').forEach((button) => {
    button.addEventListener('click', () => {
        const input = button.parentElement.querySelector('input');
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        button.textContent = isPassword ? 'Sembunyikan' : 'Tampilkan';
    });
});

let toastTimer;
function showToast(message) {
    const toast = document.querySelector('.toast');
    if (!toast) return;

    toast.textContent = message;
    toast.classList.add('show');
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => toast.classList.remove('show'), 2600);
}

document.querySelectorAll('[data-toast]').forEach((button) => {
    button.addEventListener('click', () => showToast(button.dataset.toast));
});

document.querySelectorAll('[data-dialog-open]').forEach((button) => {
    button.addEventListener('click', () => {
        const dialog = document.getElementById(button.dataset.dialogOpen);
        if (dialog instanceof HTMLDialogElement) dialog.showModal();
    });
});

document.querySelectorAll('[data-dialog-close]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
});

document.querySelectorAll('[data-receipt-dialog]').forEach((dialog) => {
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });
});

const clock = document.querySelector('[data-clock]');
if (clock) {
    const updateClock = () => {
        const now = new Date();
        const date = new Intl.DateTimeFormat('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            timeZone: 'Asia/Jakarta',
        }).format(now);
        const time = new Intl.DateTimeFormat('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
            timeZone: 'Asia/Jakarta',
        }).format(now);
        clock.textContent = `${date} · ${time} WIB`;
    };

    updateClock();
    window.setInterval(updateClock, 60_000);
}

document.querySelectorAll('.pin-inputs input').forEach((input, index, inputs) => {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '').slice(-1);
        if (input.value && inputs[index + 1]) inputs[index + 1].focus();
    });
});

const visitKiosk = document.querySelector('[data-visit-kiosk]');
if (visitKiosk) {
    const status = visitKiosk.querySelector('[data-visit-kiosk-status]');
    const expiryLabel = visitKiosk.querySelector('[data-visit-expiry]');
    let expiresAt = 0;
    let isRefreshing = false;

    const updateExpiryLabels = () => {
        const remaining = Math.max(0, expiresAt - Math.floor(Date.now() / 1000));
        expiryLabel.textContent = expiresAt
            ? `Berlaku ${remaining} detik · QR diperbarui otomatis tiap 15 detik`
            : 'Memuat QR...';
    };

    const refreshVisitCodes = async () => {
        if (isRefreshing) return;
        isRefreshing = true;

        try {
            const response = await fetch(visitKiosk.dataset.tokenUrl, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
                credentials: 'same-origin',
            });
            const tokens = await response.json();

            if (!response.ok) {
                throw new Error(tokens.message ?? 'QR tidak dapat diperbarui.');
            }

            const canvas = visitKiosk.querySelector('[data-visit-qr]');
            await QRCode.toCanvas(canvas, tokens.token, {
                width: 280,
                margin: 1,
                color: { dark: '#0b132b', light: '#ffffff' },
            });

            expiresAt = tokens.expires_at;
            status.textContent = 'QR aktif dan siap dipindai.';
            updateExpiryLabels();
        } catch (error) {
            status.textContent = `QR gagal diperbarui: ${error.message}`;
            expiryLabel.textContent = 'QR tidak tersedia. Coba muat ulang halaman.';
        } finally {
            isRefreshing = false;
        }
    };

    void refreshVisitCodes();
    window.setInterval(refreshVisitCodes, 15_000);
    window.setInterval(updateExpiryLabels, 1_000);
}

const visitScanner = document.querySelector('[data-visit-scanner]');
if (visitScanner) {
    const startButton = visitScanner.querySelector('[data-scanner-start]');
    const reader = visitScanner.querySelector('#library-visit-reader');
    const status = visitScanner.querySelector('[data-scanner-status]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    let scanner = null;
    let isProcessing = false;
    let hasReadCode = false;

    startButton.addEventListener('click', async () => {
        if (!window.matchMedia('(max-width: 767px)').matches) {
            status.textContent = 'Pemindaian kamera hanya tersedia pada layar ponsel.';
            return;
        }

        if (isProcessing) return;

        isProcessing = true;
        hasReadCode = false;
        startButton.disabled = true;
        reader.hidden = false;
        status.textContent = 'Meminta izin kamera dan memulai pemindai...';

        try {
            scanner = new Html5Qrcode(reader.id);
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 230, height: 230 } },
                async (token) => {
                    if (!isProcessing || hasReadCode) return;

                    hasReadCode = true;
                    status.textContent = 'QR terbaca. Mencatat kunjungan...';
                    await scanner.stop();
                    scanner.clear();
                    reader.hidden = true;

                    try {
                        const response = await fetch(visitScanner.dataset.scanUrl, {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({ token }),
                        });
                        const result = await response.json();

                        if (!response.ok) {
                            throw new Error(result.message ?? 'Presensi gagal dicatat.');
                        }

                        status.textContent = result.message;
                    } catch (error) {
                        status.textContent = error.message;
                    } finally {
                        startButton.disabled = false;
                        startButton.innerHTML = '<i data-lucide="scan-line"></i> Pindai QR lagi';
                        createIcons({ icons: iconSet });
                        isProcessing = false;
                    }
                },
                () => {},
            );
            status.textContent = 'Arahkan kamera ke QR masuk atau keluar di meja resepsionis.';
        } catch (error) {
            reader.hidden = true;
            status.textContent = `Kamera tidak dapat dibuka: ${error.message}`;
            startButton.disabled = false;
            isProcessing = false;
        }
    });
}
