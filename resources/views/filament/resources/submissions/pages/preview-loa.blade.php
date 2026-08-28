<div x-data="{ print: {{ request()->has('print') ? 'true' : 'false' }} }" x-init="if (print) { setTimeout(() => { window.print(); }, 500); }">
@php
    $view = $this->getTemplateView();
@endphp

@if (view()->exists($view))
    @include($view, ['record' => $this->record])
    
    {{-- Lampiran jika author > 5 --}}
    @if(is_array($this->record->authors) && count($this->record->authors) > 5)
        @include('filament.resources.submissions.pages.loa-appendix', ['record' => $this->record])
    @endif
    
    <script>
        (function() {
            const originalArea = document.getElementById("capture-area");
            const appendix = document.getElementById("loa-appendix-container");
            
            if (originalArea && appendix) {
                // 1. Rename original capture-area to capture-page-1 and force 297mm height
                originalArea.id = "capture-page-1";
                originalArea.style.height = "297mm";
                originalArea.style.maxHeight = "297mm";
                originalArea.style.overflow = "hidden";
                
                // 2. Create wrapper capture-area
                const wrapper = document.createElement("div");
                wrapper.id = "capture-area";
                wrapper.style.width = "210mm";
                wrapper.style.margin = "0 auto";
                wrapper.style.background = "white";
                
                // 3. Insert wrapper before capture-page-1
                originalArea.parentNode.insertBefore(wrapper, originalArea);
                
                // 4. Move elements inside wrapper
                wrapper.appendChild(originalArea);
                
                // Move all appendix pages inside wrapper and show them
                const pages = appendix.querySelectorAll(".loa-appendix-page");
                pages.forEach(page => {
                    wrapper.appendChild(page);
                });
                
                // Remove the empty appendix container
                appendix.remove();
            }

            // Override downloadPDF to support multi-page printing
            const originalDownloadPDF = window.downloadPDF;
            if (originalDownloadPDF) {
                window.downloadPDF = async function() {
                    const { jsPDF } = window.jspdf;
                    const element = document.querySelector("#capture-area");
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
                            scale: 3, // High resolution scale for crisp text
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

                        const imgData = canvas.toDataURL("image/jpeg", 0.95); // High quality JPEG 95%
                        const imgWidth = 210;
                        const pageHeight = 297;
                        const imgHeight = (canvas.height * imgWidth) / canvas.width;
                        let heightLeft = imgHeight;
                        let position = 0;

                        const pdf = new jsPDF({
                            orientation: "portrait",
                            unit: "mm",
                            format: "a4"
                        });

                        pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight, undefined, "FAST");
                        heightLeft -= pageHeight;

                        // Only add pages if we have significant content left (ignore small rounding issues <= 2mm)
                        while (heightLeft > 2) {
                            position = heightLeft - imgHeight;
                            pdf.addPage();
                            pdf.addImage(imgData, "JPEG", 0, position, imgWidth, imgHeight, undefined, "FAST");
                            heightLeft -= pageHeight;
                        }
                        pdf.save(`LOA-${{{ json_encode($this->record->author_name) }}}.pdf`);
                    } catch (e) {
                        console.error(e);
                        window.print();
                    } finally {
                        if (btn) {
                            btn.style.opacity = "1";
                            btn.innerText = "Download PDF";
                        }
                    }
                };
            }
        })();
    </script>
@else
    <div class="p-8 text-center text-gray-500">
        Template LOA untuk jurnal ini ({{ $view }}) belum tersedia.
    </div>
@endif
</div>