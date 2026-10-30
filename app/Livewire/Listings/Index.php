<?php

namespace App\Livewire\Listings;

use App\Enums\ListingCondition;
use App\Enums\ListingType;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public const PER_PAGE = 12;

    public const SORTS = ['newest', 'price_asc', 'price_desc'];

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: '')]
    public string $condition = '';

    #[Url(except: '')]
    public string $type = '';

    #[Url(except: 'grid')]
    public string $view = 'grid';

    #[Url(except: '')]
    public string $min = '';

    #[Url(except: '')]
    public string $max = '';

    #[Url(except: 'newest')]
    public string $sort = 'newest';

    public function updated(string $name): void
    {
        if (! str_starts_with($name, 'paginators')) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('q', 'category', 'condition', 'type', 'min', 'max', 'sort');
        $this->resetPage();
    }

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()->orderBy('sort_order')->get();
    }

    #[Computed]
    public function hasFilters(): bool
    {
        return $this->q !== '' || $this->category !== '' || $this->condition !== '' || $this->type !== ''
            || $this->min !== '' || $this->max !== '' || $this->sort !== 'newest';
    }

    /**
     * Non-numeric or negative price bounds are ignored; 0 is a valid bound.
     */
    private function bound(string $value): ?float
    {
        return is_numeric($value) && (float) $value >= 0 ? (float) $value : null;
    }

    /**
     * @return LengthAwarePaginator<int, Listing>
     */
    #[Computed]
    public function listings(): LengthAwarePaginator
    {
        $category = $this->category !== '' ? $this->categories->firstWhere('slug', $this->category) : null;
        $condition = ListingCondition::tryFrom($this->condition);

        return Listing::query()
            ->active()
            ->with(['category', 'media'])
            ->ofType(ListingType::tryFrom($this->type))
            ->search($this->q)
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->when($condition, fn ($query) => $query->where('condition', $condition))
            ->priceBetween($this->bound($this->min), $this->bound($this->max))
            ->sortBy(in_array($this->sort, self::SORTS, true) ? $this->sort : 'newest')
            ->paginate(self::PER_PAGE)
            ->withPath(route('listings.index'))
            ->appends(array_filter([
                'q' => $this->q,
                'category' => $this->category,
                'condition' => $this->condition,
                'type' => $this->type,
                'view' => $this->view !== 'grid' ? $this->view : null,
                'min' => $this->min,
                'max' => $this->max,
                'sort' => $this->sort !== 'newest' ? $this->sort : null,
            ], fn ($value) => $value !== '' && $value !== null));
    }

    public function paginationView(): string
    {
        return 'pagination.daisyui';
    }

    public function render()
    {
        return view('livewire.listings.index')->title(__('Browse listings'));
    }
}
