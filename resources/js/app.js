import Alpine from 'alpinejs';
import html2canvas from 'html2canvas-pro';
import { jsPDF } from 'jspdf';

window.Alpine = Alpine;
window.html2canvas = html2canvas;
window.jsPDF = jsPDF;

/**
 * Capture receipt DOM element and download as high-res PNG image.
 */
window.downloadReceiptAsImage = async function (elementId, filename = 'struk-sipa.png') {
    const el = document.getElementById(elementId);
    if (!el) {
        console.error('Element not found:', elementId);
        alert('Struk tidak ditemukan di halaman.');
        return null;
    }

    try {
        const canvas = await html2canvas(el, {
            scale: 2.5,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
        });

        const dataUrl = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.href = dataUrl;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: 'Gambar struk berhasil diunduh!', type: 'success' }
        }));

        return dataUrl;
    } catch (err) {
        console.error('Gagal mengunduh gambar struk:', err);
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: 'Gagal mengunduh gambar: ' + (err.message || err), type: 'error' }
        }));
        return null;
    }
};

/**
 * Capture receipt DOM element and download as crisp thermal PDF.
 */
window.downloadReceiptAsPdf = async function (elementId, filename = 'struk-sipa.pdf') {
    const el = document.getElementById(elementId);
    if (!el) {
        console.error('Element not found:', elementId);
        alert('Struk tidak ditemukan di halaman.');
        return;
    }

    try {
        const canvas = await html2canvas(el, {
            scale: 2.5,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
        });

        const imgData = canvas.toDataURL('image/png');

        // Standard width for thermal slip: 72mm
        const pdfWidth = 72;
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: [pdfWidth, pdfHeight],
        });

        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
        pdf.save(filename);

        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: 'PDF struk berhasil diunduh!', type: 'success' }
        }));
    } catch (err) {
        console.error('Gagal membuat PDF struk:', err);
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: 'Gagal membuat PDF: ' + (err.message || err), type: 'error' }
        }));
    }
};

/**
 * Copy receipt DOM element directly to clipboard as PNG image.
 */
window.copyReceiptAsImage = async function (elementId) {
    const el = document.getElementById(elementId);
    if (!el) {
        console.error('Element not found:', elementId);
        return false;
    }

    try {
        const canvas = await html2canvas(el, {
            scale: 2.5,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
        });

        return new Promise((resolve) => {
            canvas.toBlob(async (blob) => {
                if (!blob) return resolve(false);
                try {
                    await navigator.clipboard.write([
                        new ClipboardItem({ 'image/png': blob })
                    ]);
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: 'Gambar struk tersalin ke clipboard! Tekan Ctrl+V / Cmd+V di WhatsApp.', type: 'success' }
                    }));
                    resolve(true);
                } catch (e) {
                    console.warn('Clipboard image write not permitted:', e);
                    resolve(false);
                }
            }, 'image/png');
        });
    } catch (err) {
        console.error('Gagal menyalin gambar struk:', err);
        return false;
    }
};

/**
 * Share receipt DOM element as PDF via Web Share API (Mobile / Tablet / Safari).
 */
window.shareReceiptAsPdf = async function (elementId, filename = 'struk-sipa.pdf') {
    const el = document.getElementById(elementId);
    if (!el) return false;

    try {
        const canvas = await html2canvas(el, {
            scale: 2.5,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
        });

        const imgData = canvas.toDataURL('image/png');
        const pdfWidth = 72;
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

        const pdf = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: [pdfWidth, pdfHeight],
        });

        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
        const blob = pdf.output('blob');
        const file = new File([blob], filename, { type: 'application/pdf' });

        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({
                files: [file],
                title: 'Struk SIPA Merch',
                text: 'Struk Pembelian SIPA Merch'
            });
            return true;
        }

        // Fallback: download the PDF
        pdf.save(filename);
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: { message: 'File PDF terunduh. Silakan lampirkan (attach) di WhatsApp.', type: 'success' }
        }));
        return false;
    } catch (err) {
        console.error('Gagal membagikan PDF struk:', err);
        return false;
    }
};

Alpine.start();
