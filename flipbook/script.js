// ===== Elemen DOM =====
const fileInput = document.getElementById('pdf-upload');
const book = document.getElementById('book');
const nextBtn = document.getElementById('next-btn');
const prevBtn = document.getElementById('prev-btn');
const searchInput = document.getElementById('search-input');
const searchBtn = document.getElementById('search-btn');
const resultsPanel = document.getElementById('search-results-panel');
const resultsList = document.getElementById('search-results-list');
const resultsCount = document.getElementById('search-results-count');

// ===== Zoom & Fullscreen =====
const zoomInBtn = document.getElementById('zoom-in');
const zoomOutBtn = document.getElementById('zoom-out');
const fullscreenBtn = document.getElementById('fullscreen-btn');
let isFullscreen = false;

// tambahan pembesar khusus fullscreen (jangan terlalu besar)
const FULLSCREEN_BOOST = 1.1; // 1.15 – 1.25 aman


// untuk besar nya tampilan pada saat halaman dimuat pertama
let zoomScale = 1.1;

const ZOOM_CONFIG = {
    STEP: 0.2,
    MAX: 5.0, //nilai maximal zoom in
    MIN: 1.2 //nilai maximal zoom out
};

zoomInBtn.addEventListener('click', () => {
    if (zoomScale < ZOOM_CONFIG.MAX) {
        zoomScale = Math.round((zoomScale + ZOOM_CONFIG.STEP) * 10) / 10;
        applyZoom();
    }
});

zoomOutBtn.addEventListener('click', () => {
    if (zoomScale > ZOOM_CONFIG.MIN) {
        zoomScale = Math.round((zoomScale - ZOOM_CONFIG.STEP) * 10) / 10;
        applyZoom();
    }
});

fullscreenBtn.addEventListener('click', toggleFullscreen);

function applyZoom() {
    const wrappers = document.querySelectorAll('.zoom-wrapper');

    // jika fullscreen, tambahkan boost
    const effectiveScale = isFullscreen
        ? zoomScale * FULLSCREEN_BOOST
        : zoomScale;

    wrappers.forEach(wrapper => {
        wrapper.style.transform = `scale(${effectiveScale})`;
        wrapper.style.transformOrigin = "0 0";

        const parentContent = wrapper.parentElement;
        const sideParent = parentContent.parentElement;
        const canvas = wrapper.querySelector('canvas');

        if (canvas) {
            const scaledWidth = canvas.offsetWidth * effectiveScale;
            const scaledHeight = canvas.offsetHeight * effectiveScale;

            parentContent.style.width = `${scaledWidth}px`;
            parentContent.style.height = `${scaledHeight}px`;

            parentContent.style.display = "block";
            sideParent.style.overflow = "auto";
        }
    });
}


function toggleFullscreen() {
    const elem = document.documentElement;
    if (!document.fullscreenElement) {
        elem.requestFullscreen().catch(err => {
            alert(`Fullscreen gagal: ${err.message}`);
        });
    } else {
        document.exitFullscreen();
    }
}

pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js";

// ===== Variabel Global =====
let current = 0;
let papers = [];
let N = 0;
let pageTexts = {}; // Simpan teks per halaman
let currentQuery = "";
let pdfInstance = null; // ← TAMBAHKAN BARIS INI


const DURATION = 900;
document.documentElement.style.setProperty('--dur', DURATION + 'ms');

// ===== Navigasi tombol =====
nextBtn.addEventListener('click', () => flipForward());
prevBtn.addEventListener('click', () => flipBackward());

// ===== Pencarian =====
searchBtn.addEventListener('click', () => {
    const query = searchInput.value.trim().toLowerCase();
    if (!query) {
        resultsPanel.style.display = 'none';
        currentQuery = "";
        clearAllHighlights();
        return;
    }
    currentQuery = query;
    resultsList.innerHTML = '';
    const pageNumber = parseInt(query);
    if (!isNaN(pageNumber)) {
        resultsPanel.style.display = 'none';
        goToPage(pageNumber);
    } else {
        const allResults = findAllOccurrences(currentQuery);
        if (allResults.length > 0) {
            resultsCount.textContent = `${allResults.length} hasil`;
            allResults.forEach(result => {
                const li = document.createElement('li');
                li.dataset.page = result.pageNum;
                const snippet = generateSnippet(
                    pageTexts[result.pageNum],
                    currentQuery,
                    result.index
                );
                li.innerHTML = `
                    <strong>Halaman ${result.pageNum}</strong>
                    <p>${snippet}</p>
                `;
                li.addEventListener('click', () => {
                    goToPage(result.pageNum);
                });
                resultsList.appendChild(li);
            });
            resultsPanel.style.display = 'flex';
            goToPage(allResults[0].pageNum);
        } else {
            resultsPanel.style.display = 'none';
            alert(`Teks "${query}" tidak ditemukan di PDF.`);
        }
    }
});

// ===== Fungsi Snippet =====
function generateSnippet(text, query, index) {
    if (index === -1) return text.substring(0, 150) + "..."; // Fallback
    const start = Math.max(0, index - 50);
    const end = Math.min(text.length, index + query.length + 50);
    let snippet = text.substring(start, end);
    snippet = snippet.replace(new RegExp(escapeRegExp(query), 'gi'), (match) => {
        return `<mark>${match}</mark>`;
    });
    return (start > 0 ? "..." : "") + snippet + (end < text.length ? "..." : "");
}

function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// ===== Go to Page =====
function goToPage(pageNumber) {
    let targetIndex = 0;
    if (pageNumber > 1) {
        targetIndex = Math.ceil((pageNumber - 1) / 2);
    }
    if (targetIndex < 0 || targetIndex > N) {
        alert("Halaman tidak tersedia!");
        return;
    }
    while (current < targetIndex) flipForward(true);
    while (current > targetIndex) flipBackward(true);
    highlightTextOnVisiblePages(currentQuery);
}

// ===== Flip book =====
function flipForward(silent = false) {
    if (current >= N) return;
    papers[current].classList.add('flipped');
    current++;
    updateZIndices();
    if (!silent) highlightTextOnVisiblePages(currentQuery);
    applyZoom();
}

function flipBackward(silent = false) {
    if (current <= 0) return;
    papers[current - 1].classList.remove('flipped');
    current--;
    updateZIndices();
    if (!silent) highlightTextOnVisiblePages(currentQuery);
    applyZoom();
}

function updateZIndices() {
    papers.forEach((p, i) => {
        if (p.classList.contains('flipped')) {
            // Sisi KIRI: Halaman yang lebih besar (akhir) harus di depan
            p.style.zIndex = 10 + i;
        } else {
            // Sisi KANAN: Halaman yang lebih kecil (awal) harus di depan
            p.style.zIndex = 10 + (N - i);
        }
    });
}

// ===== Load PDF =====
window.addEventListener('DOMContentLoaded', async () => {
    const params = new URLSearchParams(window.location.search);
    let pdfFile = params.get('file');
    if (!pdfFile) {
        console.warn("⚠️ Tidak ada parameter file di URL, menunggu input manual...");
        return;
    }
    if (!pdfFile.startsWith('../') && !pdfFile.startsWith('./')) pdfFile = '../' + pdfFile;
    try {
        await loadPDF(pdfFile);
    } catch (e) {
        console.error("❌ Gagal memuat PDF:", e);
        alert("Gagal membuka PDF. Pastikan file ada di folder upload/ dan path-nya benar.");
    }
});

if (fileInput) {
    fileInput.addEventListener('change', async (event) => {
        const file = event.target.files[0];
        if (!file) return;
        const fileURL = URL.createObjectURL(file);
        await loadPDF(fileURL);
    });
}

// ===== Load PDF =====
async function loadPDF(url) {
    try {
        const loadingTask = pdfjsLib.getDocument(url);
        const pdf = await loadingTask.promise;
        pdfInstance = pdf;
        book.innerHTML = '';
        current = 0;
        papers = [];
        pageTexts = {};
        currentQuery = "";
        resultsPanel.style.display = 'none';
        // BUAT KERANGKA (DOM) KOSONG
        for (let i = 1; i <= pdf.numPages; i += 2) {
            const paper = document.createElement('div');
            paper.className = 'paper';
            // Sisi Depan
            const front = document.createElement('div');
            front.className = 'side front';
            const frontContent = document.createElement('div');
            frontContent.className = 'content';
            frontContent.dataset.pageNum = i;
            front.appendChild(frontContent);
            paper.appendChild(front);
            // Sisi Belakang
            const back = document.createElement('div');
            back.className = 'side back';
            const backContent = document.createElement('div');
            backContent.className = 'content';
            if (i + 1 <= pdf.numPages) {
                backContent.dataset.pageNum = i + 1;
            }
            back.appendChild(backContent);
            paper.appendChild(back);
            book.appendChild(paper);
            papers.push(paper);
        }
        N = papers.length;
        updateZIndices();
        console.log(`✅ Kerangka buku siap: ${N} lembar.`);
        // RENDER HALAMAN PERTAMA SAJA
        await renderPaper(0, pdf);
        // RENDER SISA HALAMAN DI BACKGROUND
        renderBackgroundPages(1, pdf);
    } catch (e) {
        console.error("❌ Gagal memuat PDF:", e);
        alert("Gagal membuka PDF. Pastikan file ada dan path benar.");
    }
}
async function rerenderVisiblePages() {
    if (!pdfInstance) return;

    // halaman yang sedang terlihat
    const visibleIndexes = [];

    if (current === 0) {
        visibleIndexes.push(0);
    } else {
        visibleIndexes.push(current - 1);
        if (current < papers.length) {
            visibleIndexes.push(current);
        }
    }

    for (const index of visibleIndexes) {
        await renderPaper(index, pdfInstance);
    }
}


// ===== Helper: Render Satu Lembar Kertas (Depan & Belakang) =====
async function renderPaper(index, pdf) {
    if (index >= papers.length) return;
    const paper = papers[index];
    const frontContent = paper.querySelector('.side.front .content');
    const backContent = paper.querySelector('.side.back .content');
    const pageNumFront = parseInt(frontContent.dataset.pageNum);
    // Render Depan
    if (pageNumFront) {
        try {
            const page = await pdf.getPage(pageNumFront);
            await renderPageTo(frontContent, page);
            await extractText(page, pageNumFront);
        } catch (err) {
            console.error(`Error rendering page ${pageNumFront}`, err);
        }
    }
    // Render Belakang
    const pageNumBack = backContent.dataset.pageNum ? parseInt(backContent.dataset.pageNum) : null;
    if (pageNumBack) {
        try {
            const page = await pdf.getPage(pageNumBack);
            await renderPageTo(backContent, page);
            await extractText(page, pageNumBack);
        } catch (err) {
            console.error(`Error rendering page ${pageNumBack}`, err);
        }
    }
}

// ===== Helper: Render Halaman Sisa Secara Bertahap =====
async function renderBackgroundPages(startIndex, pdf) {
    for (let i = startIndex; i < papers.length; i++) {
        await renderPaper(i, pdf);
        await new Promise(resolve => setTimeout(resolve, 100));
    }
    console.log("✅ Semua halaman selesai dimuat di background.");
}

async function renderPageTo(container, page) {
    // clarity 3-5 itu udah maximal, jika pakai 6+ dapat mengakibatkan crash pada halaman
    const clarity = 6;
    const bookHeight = book.clientHeight;
    const viewportOriginal = page.getViewport({ scale: 1 });
    const baseScale = bookHeight / viewportOriginal.height;
    const renderViewport = page.getViewport({ scale: baseScale * clarity });
    const canvas = document.createElement("canvas");
    const context = canvas.getContext("2d", { alpha: false });
    context.imageSmoothingEnabled = false;
    context.webkitImageSmoothingEnabled = false;
    context.mozImageSmoothingEnabled = false;
    canvas.width = renderViewport.width;
    canvas.height = renderViewport.height;
    const displayWidth = viewportOriginal.width * baseScale;
    const displayHeight = viewportOriginal.height * baseScale;
    canvas.style.width = `${displayWidth}px`;
    canvas.style.height = `${displayHeight}px`;
    canvas.style.display = "block";
    const textLayerDiv = document.createElement("div");
    textLayerDiv.className = "textLayer";
    textLayerDiv.style.position = "absolute";
    textLayerDiv.style.top = "0";
    textLayerDiv.style.left = "0";
    textLayerDiv.style.width = `${displayWidth}px`;
    textLayerDiv.style.height = `${displayHeight}px`;
    const zoomWrapper = document.createElement("div");
    zoomWrapper.className = "zoom-wrapper";
    zoomWrapper.style.position = "relative";
    zoomWrapper.style.width = `${displayWidth}px`;
    zoomWrapper.style.height = `${displayHeight}px`;
    zoomWrapper.appendChild(canvas);
    zoomWrapper.appendChild(textLayerDiv);
    container.style.display = "block";
    container.style.overflow = "visible";
    container.innerHTML = "";
    container.appendChild(zoomWrapper);
    await page.render({
        canvasContext: context,
        viewport: renderViewport,
        intent: 'print'
    }).promise;
    const textContent = await page.getTextContent();
    await pdfjsLib.renderTextLayer({
        textContent: textContent,
        container: textLayerDiv,
        viewport: page.getViewport({ scale: baseScale }),
        textDivs: []
    }).promise;
    applyZoom();
}

document.addEventListener('fullscreenchange', async () => {
    isFullscreen = !!document.fullscreenElement;

    // tunggu ukuran buku berubah dulu
    await new Promise(r => setTimeout(r, 350));

    await rerenderVisiblePages(); // ← INI KUNCINYA
    applyZoom();
});


async function extractText(page, pageNum) {
    const textContent = await page.getTextContent();
    const strings = textContent.items.map(item => item.str);
    pageTexts[pageNum] = strings.join(' ').toLowerCase();
}

// ===== Cari teks =====
function findAllOccurrences(query) {
    const q = query.toLowerCase().trim();
    if (!q) return [];
    const results = [];
    const keywords = q.split(/\s+/).filter(Boolean);
    for (const [pageNum, text] of Object.entries(pageTexts)) {
        let startIndex = text.indexOf(q);
        while (startIndex !== -1) {
            results.push({ pageNum: parseInt(pageNum), index: startIndex });
            startIndex = text.indexOf(q, startIndex + 1);
        }
        if (results.length === 0 && keywords.length > 1) {
            for (const word of keywords) {
                let i = text.indexOf(word);
                while (i !== -1) {
                    results.push({ pageNum: parseInt(pageNum), index: i });
                    i = text.indexOf(word, i + 1);
                }
            }
        }
    }
    return results.sort((a, b) => a.pageNum - b.pageNum || a.index - b.index);
}

// ===== FUNGSI HIGHLIGHT =====
function clearAllHighlights() {
    const highlightedSpans = document.querySelectorAll('.textLayer > span[data-original-text]');
    highlightedSpans.forEach(span => {
        span.innerHTML = span.dataset.originalText;
        delete span.dataset.originalText;
    });
}

function highlightTextOnVisiblePages(query) {
    clearAllHighlights();
    if (!query) return;
    const keywords = query.toLowerCase().trim().split(/\s+/).filter(Boolean);
    let leftPageContainer = null;
    let rightPageContainer = null;
    if (current === 0) {
        rightPageContainer = papers[0].querySelector('.side.front .content');
    } else {
        leftPageContainer = papers[current - 1].querySelector('.side.back .content');
        if (current < N) {
            rightPageContainer = papers[current].querySelector('.side.front .content');
        }
    }
    if (leftPageContainer) highlightSpansOnPage(leftPageContainer, keywords);
    if (rightPageContainer) highlightSpansOnPage(rightPageContainer, keywords);
}

function highlightSpansOnPage(container, keywords) {
    const textLayer = container.querySelector('.textLayer');
    if (!textLayer) return;
    const spans = Array.from(textLayer.querySelectorAll('span'));
    if (spans.length === 0) return;
    spans.forEach(span => {
        const originalText = span.dataset.originalText || span.textContent;
        const lowerText = originalText.toLowerCase();
        const matchedWords = keywords.filter(word => lowerText.includes(word));
        if (matchedWords.length > 0) {
            if (!span.dataset.originalText) span.dataset.originalText = originalText;
            let newHtml = originalText;
            matchedWords.forEach(word => {
                const regex = new RegExp(`(${escapeRegExp(word)})`, 'gi');
                newHtml = newHtml.replace(regex, `<mark class="highlight-match">$1</mark>`);
            });
            span.innerHTML = newHtml;
        }
    });
}

searchInput.addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
        e.preventDefault();
        searchBtn.click();
    }
});
