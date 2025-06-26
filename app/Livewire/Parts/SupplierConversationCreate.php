<?php

namespace App\Livewire\Parts;

use App\Models\SearchHistory;
use App\Models\SupplierConversation;
use App\Models\SupplierMessage;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SupplierConversationCreate extends Component
{
    use Toast;

    public ?SearchHistory $searchHistory = null;
    public string $partNumber = '';
    public string $supplierName = '';
    public string $supplierEmail = '';
    public string $subject = '';
    public string $message = '';
    
    public function mount()
    {
        $searchHistoryId = request()->get('search_history_id');
        $partNumber = request()->get('part_number');
        $supplierName = request()->get('supplier_name') ?? 'xsaviation';
        
        if (!$searchHistoryId || !$partNumber || !$supplierName) {
            abort(404, 'Missing required parameters');
        }
        
        $this->searchHistory = SearchHistory::where('id', $searchHistoryId)
            ->where('team_id', auth()->user()->current_team_id)
            ->firstOrFail();
            
        $this->partNumber = $partNumber;
        $this->supplierName = $supplierName;
        
        // Generate a default subject
        $this->subject = "Inquiry for Part Number: {$this->partNumber}";
        
        // Try to extract supplier email from the stored results
        // This is a placeholder - you'll need to adjust based on your actual data structure
        $this->supplierEmail = $this->extractSupplierEmail();
    }
    
    private function extractSupplierEmail(): string
    {
        // For now, we'll use a placeholder email format
        // In a real implementation, you would extract this from the search results
        // or have it stored in a supplier database
        $cleanSupplierName = strtolower(str_replace(' ', '', $this->supplierName));
        return "{$cleanSupplierName}@example.com";
    }
    
    public function sendMessage()
    {
        $this->validate([
            'supplierEmail' => 'required|email',
            'subject' => 'required|min:5',
            'message' => 'required|min:20',
        ]);
        
        try {
            // Create the conversation
            $conversation = SupplierConversation::create([
                'user_id' => auth()->id(),
                'team_id' => auth()->user()->current_team_id,
                'search_history_id' => $this->searchHistory->id,
                'part_number' => $this->partNumber,
                'supplier_name' => $this->supplierName,
                'supplier_email' => $this->supplierEmail,
                'subject' => $this->subject,
                'status' => 'active',
                'last_message_at' => now(),
            ]);
            
            // Create the first message
            $supplierMessage = SupplierMessage::create([
                'conversation_id' => $conversation->id,
                'user_id' => auth()->id(),
                'direction' => 'sent',
                'message' => $this->message,
                'is_read' => true,
            ]);
            
            // Send the email
            $this->sendEmail($conversation, $supplierMessage);
            
            $this->success('Message sent successfully!', position: 'toast-bottom');
            
            // Redirect to the conversation view
            return redirect()->route('parts.supplier-conversation.view', $conversation->id);
            
        } catch (\Exception $e) {
            Log::error('Failed to send supplier message: ' . $e->getMessage());
            $this->error('Failed to send message. Please try again.', position: 'toast-bottom');
        }
    }
    
    private function sendEmail(SupplierConversation $conversation, SupplierMessage $message)
    {
        $user = auth()->user();
        $team = $user->currentTeam;
        
        // Build email data
        $emailData = [
            'to' => $conversation->supplier_email,
            'from' => $user->email,
            'fromName' => $user->name,
            'subject' => $conversation->subject,
            'message' => $message->message,
            'partNumber' => $conversation->part_number,
            'supplierName' => $conversation->supplier_name,
            'teamName' => $team->name,
            'conversationId' => $conversation->id,
        ];
        
        // Send email using Laravel's Mail facade
        Mail::raw($message->message, function ($mail) use ($emailData) {
            $mail->to($emailData['to'])
                ->from($emailData['from'], $emailData['fromName'])
                ->subject($emailData['subject'])
                ->replyTo($emailData['from']);
            
            // Add a custom header to track responses
            $mail->getHeaders()->addTextHeader('X-Conversation-ID', $emailData['conversationId']);
        });
    }
    
    public function render()
    {
        return view('livewire.parts.supplier-conversation-create');
    }
}
