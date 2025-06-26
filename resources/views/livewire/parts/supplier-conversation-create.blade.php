<div>
    <!-- HEADER -->
    <x-header title="Start Supplier Conversation" separator>
        <x-slot:actions>
            <x-button 
                label="Back to Results" 
                icon="o-arrow-left" 
                link="{{ route('parts.search-history.results', $searchHistory->id) }}"
                class="btn-ghost"
            />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Part & Supplier Info -->
        <div class="lg:col-span-1">
            <x-card title="Part Information">
                <div class="space-y-3">
                    <div>
                        <label class="text-sm text-gray-500">Part Number</label>
                        <p class="font-semibold">{{ $partNumber }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Supplier</label>
                        <p class="font-semibold">{{ $supplierName }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Search Date</label>
                        <p class="font-semibold">{{ $searchHistory->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Message Form -->
        <div class="lg:col-span-2">
            <x-card title="Compose Message">
                <x-form wire:submit="sendMessage">
                    <x-input 
                        label="Supplier Email" 
                        wire:model="supplierEmail" 
                        icon="o-envelope"
                        hint="Please verify the supplier's email address"
                    />
                    
                    <x-input 
                        label="Subject" 
                        wire:model="subject" 
                        icon="o-document-text"
                    />
                    
                    <x-textarea 
                        label="Message" 
                        wire:model="message" 
                        rows="10"
                        hint="Compose your inquiry to the supplier"
                        placeholder="Dear {{ $supplierName }},

I am interested in purchasing part number {{ $partNumber }}.

Please provide me with:
- Current availability
- Unit price and minimum order quantity
- Lead time
- Shipping options
- Any required certifications (8130, COC, etc.)

Thank you for your assistance.

Best regards,"
                    />
                    
                    <x-slot:actions>
                        <x-button 
                            label="Cancel" 
                            link="{{ route('parts.search-history.results', $searchHistory->id) }}"
                        />
                        <x-button 
                            label="Send Message" 
                            icon="o-paper-airplane" 
                            type="submit" 
                            class="btn-primary" 
                            spinner
                        />
                    </x-slot:actions>
                </x-form>
            </x-card>
        </div>
    </div>
</div>
