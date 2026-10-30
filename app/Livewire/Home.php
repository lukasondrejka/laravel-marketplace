<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Home extends Component
{
    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()
            ->withCount(['listings' => fn ($query) => $query->active()])
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return Collection<int, Listing>
     */
    #[Computed]
    public function latest(): Collection
    {
        return Listing::query()->active()->with(['category', 'media'])->sortBy('newest')->limit(8)->get();
    }

    public function render()
    {
        return view('livewire.home');
    }
}
