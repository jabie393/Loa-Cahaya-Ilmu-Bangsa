<div x-data="{ print: {{ request()->has('print') ? 'true' : 'false' }} }" x-init="if (print) { setTimeout(() => { window.print(); }, 500); }">
@php
    $view = $this->record->getAcTemplateView();
@endphp

@if (view()->exists($view))
    @include($view, ['record' => $this->record])
    
    {{-- Lampiran jika author > 5 --}}
    @if(is_array($this->record->authors) && count($this->record->authors) > 5)
        @include('filament.resources.submissions.pages.ac-appendix', ['record' => $this->record])
    @endif
    
    <script>
        (function() {
            const originalArea = document.querySelector('[data-purpose="main-certificate-frame"]');
            const appendix = document.getElementById("ac-appendix-container");
            
            if (originalArea && appendix) {
                // 1. Rename original and lock page 1 height/width
                originalArea.removeAttribute("data-purpose");
                originalArea.setAttribute("data-purpose", "main-certificate-frame-page-1");
                originalArea.style.width = "297mm";
                originalArea.style.height = "210mm";
                originalArea.style.maxHeight = "210mm";
                originalArea.style.overflow = "hidden";
                
                // 2. Create wrapper
                const wrapper = document.createElement("div");
                wrapper.setAttribute("data-purpose", "main-certificate-frame");
                wrapper.style.width = "297mm";
                wrapper.style.margin = "0 auto";
                wrapper.style.background = "white";
                
                // 3. Insert wrapper before page 1
                originalArea.parentNode.insertBefore(wrapper, originalArea);
                
                // 4. Move elements inside wrapper
                wrapper.appendChild(originalArea);
                
                const pages = appendix.querySelectorAll(".ac-appendix-page");
                pages.forEach(page => {
                    wrapper.appendChild(page);
                });
                
                appendix.remove();
            }

            // Override downloadPDF to support multi-page printing
            const originalDownloadPDF = window.downloadPDF;
            if (originalDownloadPDF) {
                window.downloadPDF = async function() {
                    const { jsPDF } = window.jspdf;
                    const element = document.querySelector('[data-purpose="main-certificate-frame"]');
                    const btn = document.querySelector("#download-btn");

                    if (btn) {
                        btn.style.opacity = "0.5";
                        btn.innerText = "Processing...";
                    }

                    try {
                        const body = document.querySelector("body");
                        const originalBodyMaxHeight = body ? body.style.maxHeight : "";
                        const originalBodyBoxShadow = body ? body.style.boxShadow : "";
                        const originalBodyMargin = body ? body.style.margin : "";
                        
                        if (body) {
                            body.style.maxHeight = "none";
                            body.style.boxShadow = "none";
                            body.style.margin = "0";
                            body.classList.remove("max-h-[297mm]");
                        }

                        const canvas = await html2canvas(element, {
                            scale: 3, // Keep scale 3 for ultra-sharpness
                            useCORS: true,
                            allowTaint: true,
                            backgroundColor: "#ffffff",
                            onclone: (clonedDoc) => {
                                const downloadBtn = clonedDoc.querySelector("#download-btn");
                                if (downloadBtn) downloadBtn.style.display = "none";
                            }
                        });

                        // Revert body styles
                        if (body) {
                            body.style.maxHeight = originalBodyMaxHeight;
                            body.style.boxShadow = originalBodyBoxShadow;
                            body.style.margin = originalBodyMargin;
                        }

                        const imgData = canvas.toDataURL("image/jpeg", 0.95); // High quality JPEG
                        const imgWidth = 297; // Landscape A4 width
                        const pageHeight = 210; // Landscape A4 height
                        const imgHeight = (canvas.height * imgWidth) / canvas.width;
                        let heightLeft = imgHeight;
                        let position = 0;

                        const pdf = new jsPDF({
                            orientation: "landscape",
                            unit: "mm",
                            format: "a4"
                        });

                        pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight, undefined, "FAST");
                        heightLeft -= pageHeight;

                        while (heightLeft > 2) {
                            position = heightLeft - imgHeight;
                            pdf.addPage("a4", "landscape");
                            pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight, undefined, "FAST");
                            heightLeft -= pageHeight;
                        }
                        pdf.save("Certificate-" + ' . json_encode($this->record->author_name) . ' + ".pdf");
                    } catch (e) {
                        console.error(e);
                        window.print();
                    } finally {
                        if (btn) {
                            btn.style.opacity = "1";
                            btn.innerText = "Download Certificate";
                        }
                    }
                };
            }
        })();
    </script>
@else
    <div class="p-8 text-center text-gray-500">
        Template Sertifikat untuk jurnal ini belum tersedia.
    </div>
@endif
</div>