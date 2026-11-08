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
