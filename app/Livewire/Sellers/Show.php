<?php

namespace App\Livewire\Sellers;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public User $seller;

    public function mount(User $seller): void
    {
        $this->seller = $seller;
    }

    /**
     * @return LengthAwarePaginator<int, Listing>
     */
    #[Computed]
    public function listings(): LengthAwarePaginator
    {
        return $this->seller->listings()
            ->active()
            ->with(['category', 'media', 'user'])
            ->sortBy('newest')
            ->paginate(12)
            ->withPath(route('sellers.show', $this->seller));
    }

    public function paginationView(): string
    {
        return 'pagination.daisyui';
    }

    public function render()
    {
        return view('livewire.sellers.show')->title($this->seller->name);
    }
}
