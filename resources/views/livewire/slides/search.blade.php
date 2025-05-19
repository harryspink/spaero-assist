<div>
    <!-- HEADER -->
    <x-header title="Slide Search" subtitle="Search for slides and view them in the slide viewer" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <form wire:submit="searchSlides" class="flex gap-2 w-full max-w-md">
                <x-input 
                    placeholder="Enter case ID..." 
                    wire:model="search" 
                    clearable 
                    icon="o-magnifying-glass" 
                    class="w-full"
                />
                <x-button type="submit" label="Search" icon="o-magnifying-glass" class="btn-primary" spinner />
            </form>
        </x-slot:middle>
    </x-header>

    <!-- CONTENT -->
    <div class="space-y-6">
        <!-- ERROR MESSAGE -->
        @if($error)
            <x-card shadow>
                <div class="text-center py-6">
                    <x-icon name="o-exclamation-triangle" class="w-16 h-16 mx-auto text-warning" />
                    <h3 class="text-xl font-semibold mt-4">Error</h3>
                    <p class="text-base-content/70 mt-2">{{ $error }}</p>
                    @if(str_contains($error, 'slide viewer URL'))
                        <div class="mt-6">
                            <x-button 
                                label="Configure Slide Viewer" 
                                link="{{ auth()->user()->currentTeam ? route('teams.settings', auth()->user()->currentTeam->id) : route('teams.index') }}" 
                                icon="o-cog-6-tooth" 
                                class="btn-primary" 
                            />
                        </div>
                    @endif
                </div>
            </x-card>
        @endif

        <!-- LOADING STATE -->
        @if($isLoading)
            <x-card shadow>
                <div class="flex justify-center items-center py-12">
                    <span class="loading loading-spinner loading-lg text-primary"></span>
                </div>
            </x-card>
        @endif

        <!-- SEARCH RESULTS -->
        @if(!$isLoading && $hasSearched && empty($results) && !$error)
            <x-card shadow>
                <div class="text-center py-8">
                    <x-icon name="o-magnifying-glass" class="w-16 h-16 mx-auto text-base-content/30" />
                    <h3 class="text-xl font-semibold mt-4">No Results Found</h3>
                    <p class="text-base-content/70 mt-2">Try a different search term or check your slide viewer configuration.</p>
                </div>
            </x-card>
        @endif

        @if(!$isLoading && !empty($results))
            <x-card shadow>
                <h3 class="text-lg font-semibold mb-4">Search Results</h3>
                
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Case No</th>
                                <th>Path</th>
                                <th>Scanned Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $slide)
                                <tr>
                                    <td>{{ $slide['id'] ?? 'N/A' }}</td>
                                    <td>{{ $slide['case_no'] ?? 'N/A' }}</td>
                                    <td>{{ $slide['path'] ?? 'N/A' }}</td>
                                    <td>{{ $slide['scanned_date'] ?? 'N/A' }}</td>
                                    <td class="flex gap-2">
                                        <x-button 
                                            label="View Slide" 
                                            wire:click="viewSlide('{{ $slide['slide_url_path'] ?? '' }}', '{{ $slide['id'] ?? '' }}', '{{ $slide['case_no'] ?? '' }}', '{{ $slide['path'] ?? '' }}')" 
                                            icon="o-eye" 
                                            class="btn-primary btn-sm" 
                                            spinner 
                                        />
                                        
                                        @if(isset($slide['thumbnail_url_path']))
                                            <a href="{{ $slide['thumbnail_url_path'] }}" target="_blank" class="btn btn-sm btn-outline">
                                                <x-icon name="o-photo" class="w-4 h-4 mr-1" />
                                                Thumbnail
                                            </a>
                                        @endif
                                        
                                        @if(isset($slide['label_url_path']))
                                            <a href="{{ $slide['label_url_path'] }}" target="_blank" class="btn btn-sm btn-ghost">
                                                <x-icon name="o-tag" class="w-4 h-4 mr-1" />
                                                Label
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
            
            <!-- Slide Tray Visualization -->
            <x-card shadow class="mt-6">
                <h3 class="text-lg font-semibold mb-4">Slide Tray</h3>
                <div class="bg-base-200 p-4 rounded-lg">
                    <div class="tray">
                        <div class="tray-right">
                            @foreach($results as $index => $slide)
                                <div class="cutout">
                                    <div class="slide" wire:click="viewSlide('{{ $slide['slide_url_path'] ?? '' }}', '{{ $slide['id'] ?? '' }}', '{{ $slide['case_no'] ?? '' }}', '{{ $slide['path'] ?? '' }}')">
                                        <div class="slide-label">
                                            <img src="{{ $slide['thumbnail_url_path'] ?? '' }}" alt="Slide Thumbnail" class="w-full h-full object-cover rounded" />
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <style>
                    .tray {
                      display: flex;
                      background: #d3c9b8;
                      width: 100%;
                      height: 300px;
                      border: 2px solid #aaa;
                      border-radius: 6px;
                      box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                      padding: 20px;
                    }
                
                    .tray-right {
                      display: flex;
                      justify-content: flex-start;
                      align-items: center;
                      gap: 20px;
                      width: 100%;
                      overflow-x: auto;
                      padding: 10px;
                    }
                
                    .cutout {
                        width: 90px;
                        height: 230px;
                        border: 2px dashed #aaa;
                        border-radius: 6px;
                        background: #f5f5f5;
                        position: relative;
                        flex-shrink: 0;
                    }
                
                    .slide {
                        width: 82px;
                        height: 208px;
                        background: rgba(180, 230, 250, 0.6);
                        border: 1px solid #555;
                        border-radius: 3px;
                        position: absolute;
                        top: 9px;
                        left: 2px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        overflow: hidden;
                    }
                    
                    .slide-label {
                      writing-mode: vertical-rl;
                      text-orientation: mixed;
                      font-size: 10px;
                      padding: 5px;
                      width: 100%;
                      height: 100%;
                      display: flex;
                      align-items: center;
                      justify-content: center;
                    }
                </style>
            </x-card>
        @endif

        <!-- INITIAL STATE -->
        @if(!$hasSearched && !$isLoading)
            <x-card shadow>
                <div class="text-center py-12">
                    <x-icon name="o-document-magnifying-glass" class="w-24 h-24 mx-auto text-base-content/20" />
                    <h3 class="text-xl font-semibold mt-6">Search for Slides</h3>
                    <p class="text-base-content/70 mt-2 max-w-md mx-auto">
                        Enter a case ID in the search box above to find slides. You'll be able to view them in your team's configured slide viewer.
                    </p>
                </div>
            </x-card>
        @endif
    </div>
</div>
