<?php

namespace App\Livewire\Parts;

use App\Models\SupplierConversation;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class SupplierConversations extends Component
{
    use Toast, WithPagination;

    public string $search = '';
    public string $statusFilter = 'all'; // all, active, closed
    public array $sortBy = ['column' => 'last_message_at', 'direction' => 'desc'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function headers(): array
    {
        return [
            ['key' => 'part_number', 'label' => 'Part Number', 'class' => 'w-32'],
            ['key' => 'supplier_name', 'label' => 'Supplier', 'class' => 'w-48'],
            ['key' => 'subject', 'label' => 'Subject', 'class' => 'w-64'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'w-24'],
            ['key' => 'unread', 'label' => 'Unread', 'class' => 'w-20'],
            ['key' => 'last_message_at', 'label' => 'Last Message', 'class' => 'w-32'],
            ['key' => 'actions', 'label' => '', 'class' => 'w-20'],
        ];
    }

    public function deleteConversation($id)
    {
        $conversation = SupplierConversation::find($id);
        if ($conversation && $conversation->team_id === auth()->user()->current_team_id) {
            $conversation->delete();
            $this->success('Conversation deleted successfully.', position: 'toast-bottom');
        }
    }

    public function render()
    {
        $query = SupplierConversation::with(['user', 'latestMessage'])
            ->where('team_id', auth()->user()->current_team_id);

        // Apply search
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('part_number', 'like', '%' . $this->search . '%')
                  ->orWhere('supplier_name', 'like', '%' . $this->search . '%')
                  ->orWhere('supplier_email', 'like', '%' . $this->search . '%')
                  ->orWhere('subject', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortBy['column'], $this->sortBy['direction']);

        $conversations = $query->paginate(15);

        // Transform conversations for table display
        $conversations->through(function ($conversation) {
            return [
                'id' => $conversation->id,
                'part_number' => $conversation->part_number,
                'supplier_name' => $conversation->supplier_name,
                'subject' => $conversation->subject,
                'status' => $conversation->status,
                'unread' => $conversation->unread_count,
                'last_message_at' => $conversation->last_message_at,
                'user' => $conversation->user,
            ];
        });

        return view('livewire.parts.supplier-conversations', [
            'conversations' => $conversations,
            'headers' => $this->headers(),
        ]);
    }
}
