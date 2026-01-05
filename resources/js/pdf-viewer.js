import * as pdfjsLib from 'pdfjs-dist';

// Set worker path (sesuaikan path ini jika perlu)
pdfjsLib.GlobalWorkerOptions.workerSrc = '/js/pdf.worker.min.js';

export class PDFViewer {
   constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        this.pdfCanvas = null;
        this.overlayCanvas = null;
        this.pdfDoc = null;
        this.currentPage = 1;
        this.totalPages = 0;
        this.scale = 1.5;
        this.signatureAreas = [];
        this.renderTask = null;

        // Data Dimensi Halaman (PENTING untuk akurasi)
        this.pageWidthMM = 210; // Default A4, akan diupdate otomatis
        this.pageHeightMM = 297;

        // State Mouse
        this.isDrawing = false;
        this.startPos = { x: 0, y: 0 };
        this.tempArea = null;

        this.options = {
            onAreaAdded: options.onAreaAdded || (() => {}),
            onAreaUpdated: options.onAreaUpdated || (() => {}),
            onAreaDeleted: options.onAreaDeleted || (() => {}),
            ...options
        };

        this.container.style.position = 'relative';
    }

    async loadPDF(url) {
        try {
            const loadingTask = pdfjsLib.getDocument(url);
            this.pdfDoc = await loadingTask.promise;
            this.totalPages = this.pdfDoc.numPages;
            this.initCanvases();
            await this.renderPage(this.currentPage);
            this.setupEventListeners();
            return true;
        } catch (error) {
            console.error('Error loading PDF:', error);
            throw error;
        }
    }

    initCanvases() {
        this.container.innerHTML = '';

        // Layer 1: PDF Background
        this.pdfCanvas = document.createElement('canvas');
        this.pdfCanvas.style.display = 'block';
        this.pdfCanvas.style.width = '100%';

        // Layer 2: Overlay (Tempat Menggambar)
        this.overlayCanvas = document.createElement('canvas');
        this.overlayCanvas.style.position = 'absolute';
        this.overlayCanvas.style.top = '0';
        this.overlayCanvas.style.left = '0';
        // Pastikan border/padding 0 di style agar tidak mengganggu koordinat
        this.overlayCanvas.style.border = 'none';
        this.overlayCanvas.style.margin = '0';
        this.overlayCanvas.style.padding = '0';
        this.overlayCanvas.style.cursor = 'crosshair';

        this.overlayContext = this.overlayCanvas.getContext('2d');

        this.container.appendChild(this.pdfCanvas);
        this.container.appendChild(this.overlayCanvas);
    }
async renderPage(pageNum) {
    if (!this.pdfDoc) return;
    if (this.renderTask) {
        await this.renderTask.cancel();
        this.renderTask = null;
    }

    const page = await this.pdfDoc.getPage(pageNum);
    const unscaledViewport = page.getViewport({ scale: 1.0 });

    const PT_TO_MM = 25.4 / 72;
    let detectedWidth = unscaledViewport.width * PT_TO_MM;
    let detectedHeight = unscaledViewport.height * PT_TO_MM;

    // 🔧 AUTO-CORRECTION: Jika mendekati A4, paksa ke A4
    const isNearA4Portrait =
        Math.abs(detectedWidth - 210) < 10 &&
        Math.abs(detectedHeight - 297) < 10;

    const isNearA4Landscape =
        Math.abs(detectedWidth - 297) < 10 &&
        Math.abs(detectedHeight - 210) < 10;

    if (isNearA4Portrait) {
        console.warn('⚠️ Page size near A4 Portrait, normalizing to standard A4');
        this.pageWidthMM = 210;
        this.pageHeightMM = 297;
    } else if (isNearA4Landscape) {
        console.warn('⚠️ Page size near A4 Landscape, normalizing to standard A4');
        this.pageWidthMM = 297;
        this.pageHeightMM = 210;
    } else {
        this.pageWidthMM = detectedWidth;
        this.pageHeightMM = detectedHeight;
    }

    console.log('📏 Page Size:', {
        'Detected (mm)': {
            width: detectedWidth.toFixed(2),
            height: detectedHeight.toFixed(2)
        },
        'Used (mm)': {
            width: this.pageWidthMM.toFixed(2),
            height: this.pageHeightMM.toFixed(2)
        },
        'Corrected': isNearA4Portrait || isNearA4Landscape
    });

    const viewport = page.getViewport({ scale: this.scale });

    this.pdfCanvas.width = viewport.width;
    this.pdfCanvas.height = viewport.height;
    this.overlayCanvas.width = viewport.width;
    this.overlayCanvas.height = viewport.height;

    const renderContext = {
        canvasContext: this.pdfCanvas.getContext('2d'),
        viewport: viewport
    };

    try {
        this.renderTask = page.render(renderContext);
        await this.renderTask.promise;
        this.currentPage = pageNum;
        this.redrawAreas();
    } catch (error) {
        if (error.name !== 'RenderingCancelledException') {
            console.error('Render error:', error);
        }
    }
}

   setupEventListeners() {
        // --- FUNGSI POSISI MOUSE YANG AMAN DARI ZOOM/SKALA ---
        const getPos = (e) => {
            // Ambil ukuran & posisi elemen canvas SAAT INI di layar
            const rect = this.overlayCanvas.getBoundingClientRect();

            // Hitung posisi mouse relatif terhadap pojok kiri-atas elemen visual
            // clientX adalah koordinat global mouse di browser
            // rect.left adalah jarak kanvas dari kiri browser
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top,
                // Kita kembalikan juga lebar/tinggi visual saat klik terjadi
                visualWidth: rect.width,
                visualHeight: rect.height
            };
        };

        this.overlayCanvas.addEventListener('mousedown', (e) => {
            if (e.button !== 0) return; // Hanya klik kiri
            const pos = getPos(e);
            this.isDrawing = true;
            this.startPos = pos;
            this.tempArea = { x: pos.x, y: pos.y, width: 0, height: 0 };
        });

        this.overlayCanvas.addEventListener('mousemove', (e) => {
            if (!this.isDrawing) return;
            const pos = getPos(e);

            this.tempArea.width = pos.x - this.startPos.x;
            this.tempArea.height = pos.y - this.startPos.y;

            this.redrawAreas();
            // Gambar kotak hijau transparan saat drag
            this.drawArea(this.tempArea, 'rgba(0, 255, 0, 0.3)', '#00FF00', true);
        });

        this.overlayCanvas.addEventListener('mouseup', (e) => {
            if (this.isDrawing && this.tempArea) {
                // Ambil ukuran visual TERAKHIR saat mouse dilepas
                // Ini penting jika user melakukan resize window saat drag (jarang, tapi aman)
                const finalPos = getPos(e);

                // Normalisasi arah drag (jika tarik ke atas/kiri)
                let finalX = this.tempArea.x;
                let finalY = this.tempArea.y;
                let finalW = this.tempArea.width;
                let finalH = this.tempArea.height;

                if (finalW < 0) { finalX += finalW; finalW = Math.abs(finalW); }
                if (finalH < 0) { finalY += finalH; finalH = Math.abs(finalH); }

                if (finalW < 5 || finalH < 5) {
                    this.isDrawing = false;
                    this.redrawAreas();
                    return;
                }

                // --- PANGGIL FUNGSI KONVERSI DENGAN UKURAN VISUAL ---
                const pdfCoords = this.pixelsToPDFCoords(
                    { x: finalX, y: finalY, width: finalW, height: finalH },
                    finalPos.visualWidth,  // Kirim lebar visual
                    finalPos.visualHeight  // Kirim tinggi visual
                );

                const newArea = {
                    id: Date.now(),
                    page: this.currentPage,
                    x: finalX, y: finalY, width: finalW, height: finalH,
                    pdfX: pdfCoords.x,
                    pdfY: pdfCoords.y,
                    pdfWidth: pdfCoords.width,
                    pdfHeight: pdfCoords.height
                };

                this.signatureAreas.push(newArea);
                this.options.onAreaAdded(newArea);

                this.isDrawing = false;
                this.tempArea = null;
                this.redrawAreas();
            }
        });
    }

pixelsToPDFCoords(area, visualWidth, visualHeight) {
    if (!visualWidth || !visualHeight) {
        const rect = this.overlayCanvas.getBoundingClientRect();
        visualWidth = rect.width;
        visualHeight = rect.height;
    }

    // Hitung rasio dari canvas
    const ratioX = area.x / visualWidth;
    const ratioY = area.y / visualHeight;
    const ratioW = area.width / visualWidth;
    const ratioH = area.height / visualHeight;

    // 🔧 RUMUS BARU: Konversi langsung TANPA inversi
    // Karena kita akan inversi di sisi PHP saja
    return {
        x: parseFloat((ratioX * this.pageWidthMM).toFixed(2)),
        y: parseFloat((ratioY * this.pageHeightMM).toFixed(2)), // Y dari atas
        width: parseFloat((ratioW * this.pageWidthMM).toFixed(2)),
        height: parseFloat((ratioH * this.pageHeightMM).toFixed(2))
    };
}

    // ... (fungsi drawArea, redrawAreas, deleteArea, dll TETAP SAMA seperti sebelumnya) ...

    redrawAreas() {
        this.overlayContext.clearRect(0, 0, this.overlayCanvas.width, this.overlayCanvas.height);
        this.signatureAreas
            .filter(area => area.page === this.currentPage)
            .forEach(area => {
                this.drawArea(area, 'rgba(79, 70, 229, 0.2)', '#4F46E5', false);
            });
    }

    drawArea(area, fillColor, strokeColor, isDashed) {
        const ctx = this.overlayContext;
        ctx.fillStyle = fillColor;
        ctx.fillRect(area.x, area.y, area.width, area.height);
        ctx.strokeStyle = strokeColor;
        ctx.lineWidth = 2;
        if (isDashed) ctx.setLineDash([5, 5]);
        else ctx.setLineDash([]);
        ctx.strokeRect(area.x, area.y, area.width, area.height);
    }


    loadExistingAreas(areas) {
        // Saat loading awal, data dari DB dalam mm, perlu dikonversi ke pixels nanti saat render
        // Tapi karena ukuran canvas belum tahu saat class di-init, kita simpan raw data dulu.
        // Konversi pixel dilakukan dinamis atau saat renderPage selesai.

        // Sederhananya, kita simpan dulu dan logic renderPage/redrawAreas perlu sedikit penyesuaian
        // untuk menghitung ulang pixel dari mm setiap kali render (responsiveness).

        // Untuk sekarang, asumsikan area yang masuk sudah punya properti PDF coords,
        // kita perlu kalkulasi ulang x,y,w,h pixel saat renderPage.
        this.signatureAreas = areas;

        // *Improvement*: Panggil fungsi recalculatePixelsFromPDFCoords() setiap renderPage selesai
    }


      deleteArea(areaId) {
        const index = this.signatureAreas.findIndex(a => a.id === areaId);
        if (index !== -1) {
            const area = this.signatureAreas[index];
            this.signatureAreas.splice(index, 1);
            this.options.onAreaDeleted(area);
            this.redrawAreas();
        }
    }

        async changePage(pageNum) {
        if (pageNum >= 1 && pageNum <= this.totalPages) {
            await this.renderPage(pageNum);
        }
    }

     findAreaAtPoint(x, y) {
        return this.signatureAreas
            .filter(area => area.page === this.currentPage)
            .find(area =>
                x >= area.x &&
                x <= area.x + area.width &&
                y >= area.y &&
                y <= area.y + area.height
            );
    }
}
