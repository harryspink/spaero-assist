<div class="flex flex-col h-screen">
    <!-- HEADER -->
    <div class="bg-base-100 border-b border-base-300 p-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <x-button icon="o-arrow-left" wire:click="backToSearch" class="btn-ghost" tooltip="Back to search" />
            <div>
                <h1 class="text-lg font-semibold">{{ $path ?: 'Slide Viewer' }}</h1>
                <p class="text-sm text-base-content/70">{{ $caseNo ?: 'No case number' }}</p>
            </div>
        </div>
        <div>
            <x-button icon="o-arrows-pointing-out" onclick="toggleFullscreen()" class="btn-ghost" tooltip="Toggle fullscreen" />
        </div>
    </div>
    
    <!-- IFRAME CONTAINER -->
    <div class="flex-grow w-full bg-base-200 relative" id="iframe-container">
        <iframe 
            src="{{ $slideUrl }}" 
            class="w-full h-full border-0" 
            id="slide-iframe"
            allowfullscreen
        ></iframe>
    </div>
    
    <script>
        function toggleFullscreen() {
            const container = document.getElementById('iframe-container');
            
            if (!document.fullscreenElement) {
                if (container.requestFullscreen) {
                    container.requestFullscreen();
                } else if (container.webkitRequestFullscreen) { /* Safari */
                    container.webkitRequestFullscreen();
                } else if (container.msRequestFullscreen) { /* IE11 */
                    container.msRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) { /* Safari */
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) { /* IE11 */
                    document.msExitFullscreen();
                }
            }
        }
    </script>
</div>
