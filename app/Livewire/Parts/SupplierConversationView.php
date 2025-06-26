<?php

namespace App\Livewire\Parts;

use App\Models\SupplierConversation;
use App\Models\SupplierMessage;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SupplierConversationView extends Component
{
    use Toast;

    public ?SupplierConversation $conversation = null;
    public string $newMessage = '';
    
    protected $rules = [
        'newMessage' => 'required|min:10',
    ];
    
    public function mount($id)
    {
        $this->conversation = SupplierConversation::with(['user', 'team', 'searchHistory'])
            ->where('id', $id)
            ->where('team_id', auth()->user()->current_team_id)
            ->firstOrFail();
            
        $this->markMessagesAsRead();
    }
    
    public function getMessages()
    {
        return $this->conversation->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
    }
    
    public function markMessagesAsRead()
    {
        $this->conversation->messages()
            ->where('direction', 'received')
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
    
    public function sendMessage()
    {
        // Manual validation to avoid conflicts
        if (strlen(trim($this->newMessage)) < 10) {
            $this->addError('newMessage', 'The message must be at least 10 characters.');
            return;
        }
        
        try {
            // Create the message
            $message = SupplierMessage::create([
                'conversation_id' => $this->conversation->id,
                'user_id' => auth()->id(),
                'direction' => 'sent',
                'message' => $this->newMessage,
                'is_read' => true,
            ]);
            
            // Update conversation last message time
            $this->conversation->update(['last_message_at' => now()]);
            
            // Send the email
            $this->sendEmail($message);
            
            // Clear the input
            $this->newMessage = '';
            
            $this->success('Message sent successfully!', position: 'toast-bottom');
            
        } catch (\Exception $e) {
            Log::error('Failed to send supplier message: ' . $e->getMessage());
            $this->error('Failed to send message. Please try again.', position: 'toast-bottom');
        }
    }
    
    private function sendEmail(SupplierMessage $message)
    {
        $user = auth()->user();
        $team = $user->currentTeam;
        
        // Build email data
        $emailData = [
            'to' => $this->conversation->supplier_email,
            'from' => $user->email,
            'fromName' => $user->name,
            'subject' => "Re: " . $this->conversation->subject,
            'message' => $message->message,
            'partNumber' => $this->conversation->part_number,
            'supplierName' => $this->conversation->supplier_name,
            'teamName' => $team->name,
            'conversationId' => $this->conversation->id,
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
    
    public function closeConversation()
    {
        $this->conversation->update(['status' => 'closed']);
        $this->success('Conversation closed.', position: 'toast-bottom');
        return redirect()->route('parts.supplier-conversations');
    }
    
    public function reopenConversation()
    {
        $this->conversation->update(['status' => 'active']);
        $this->success('Conversation reopened.', position: 'toast-bottom');
    }
    
    public function render()
    {
        return view('livewire.parts.supplier-conversation-view');
    }
}
