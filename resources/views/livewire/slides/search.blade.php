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
                <div class="bg-base-200 p-4 rounded-lg overflow-hidden">
                    <div class="tray">
                        <div class="tray-left">
                            <div class="tray-label">
                                <div class="case-id">{{ $results[0]['case_no'] ?? 'Case ID' }}</div>
                                <div class="date">{{ $results[0]['scanned_date'] ?? date('Y-m-d') }}</div>
                                <div class="slide-count">Slides: {{ count($results) }}</div>
                                <div class="lab-info">{{ auth()->user()->currentTeam->name ?? 'Pathology Laboratory' }} Laboratory</div>
                            </div>
                        </div>
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
                        background-image: 
                            linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.1) 75%, transparent 75%, transparent),
                            linear-gradient(to right, #d5c5a9, #e0d2b8);
                        background-size: 10px 10px, 100% 100%;
                        width: 100%;
                        height: 300px;
                        border: 2px solid #b5a48c;
                        border-radius: 8px;
                        box-shadow: 
                            0 4px 15px rgba(0,0,0,0.2),
                            inset 0 0 30px rgba(0,0,0,0.1);
                        padding: 0;
                        position: relative;
                        overflow: hidden;
                    }
                    
                    .tray:before {
                        content: '';
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        height: 5px;
                        background: linear-gradient(to bottom, rgba(255,255,255,0.4), transparent);
                        border-radius: 6px 6px 0 0;
                    }
                    
                    .tray:after {
                        content: '';
                        position: absolute;
                        bottom: 0;
                        left: 0;
                        right: 0;
                        height: 5px;
                        background: linear-gradient(to top, rgba(0,0,0,0.2), transparent);
                        border-radius: 0 0 6px 6px;
                    }
                
                    .tray-left {
                        flex: 1;
                        border-right: 2px solid #b5a48c;
                        position: relative;
                        padding: 20px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        background: linear-gradient(135deg, #e0d2b8, #d5c5a9);
                        box-shadow: inset -5px 0 10px -5px rgba(0,0,0,0.2);
                    }
                    
                    .tray-label {
                        width: 90%;
                        height: 80%;
                        background: white;
                        border: 1px solid #ccc;
                        border-radius: 4px;
                        padding: 15px;
                        display: flex;
                        flex-direction: column;
                        justify-content: space-between;
                        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                        font-family: monospace;
                    }
                    
                    .case-id {
                        font-size: 18px;
                        font-weight: bold;
                        margin-bottom: 10px;
                    }
                    
                    .date {
                        font-size: 14px;
                        color: #555;
                        margin-bottom: 10px;
                    }
                    
                    .slide-count {
                        font-size: 14px;
                        color: #555;
                        margin-bottom: 10px;
                        font-weight: bold;
                    }
                    
                    .lab-info {
                        font-size: 12px;
                        color: #777;
                        margin-top: auto;
                        border-top: 1px dashed #ccc;
                        padding-top: 10px;
                    }
                
                    .tray-right {
                        flex: 2;
                        display: flex;
                        justify-content: flex-start;
                        align-items: center;
                        gap: 20px;
                        width: 100%;
                        overflow-x: auto;
                        padding: 20px;
                        background: linear-gradient(135deg, #d5c5a9, #e0d2b8);
                        box-shadow: inset 5px 0 10px -5px rgba(0,0,0,0.1);
                    }
                    
                    .tray-right::-webkit-scrollbar {
                        height: 8px;
                    }
                    
                    .tray-right::-webkit-scrollbar-track {
                        background: rgba(0,0,0,0.05);
                        border-radius: 4px;
                    }
                    
                    .tray-right::-webkit-scrollbar-thumb {
                        background: rgba(0,0,0,0.2);
                        border-radius: 4px;
                    }
                
                    .cutout {
                        width: 90px;
                        height: 230px;
                        border: 2px solid #aaa;
                        border-radius: 6px;
                        background: #f5f5f5;
                        position: relative;
                        flex-shrink: 0;
                        box-shadow: 
                            inset 0 0 10px rgba(0,0,0,0.1),
                            0 2px 5px rgba(0,0,0,0.1);
                        overflow: hidden;
                    }
                    
                    .cutout:before {
                        content: '';
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        border: 2px dashed rgba(0,0,0,0.1);
                        border-radius: 4px;
                        pointer-events: none;
                    }
                
                    .slide {
                        width: 82px;
                        height: 208px;
                        background: rgba(180, 230, 250, 0.8);
                        border: 1px solid #555;
                        border-radius: 3px;
                        position: absolute;
                        top: 9px;
                        left: 3px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        overflow: hidden;
                        box-shadow: 
                            0 2px 5px rgba(0,0,0,0.2),
                            inset 0 0 10px rgba(255,255,255,0.5);
                        transition: transform 0.2s ease, box-shadow 0.2s ease;
                    }
                    
                    .slide:hover {
                        transform: translateY(-3px);
                        box-shadow: 
                            0 5px 10px rgba(0,0,0,0.3),
                            inset 0 0 10px rgba(255,255,255,0.5);
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
