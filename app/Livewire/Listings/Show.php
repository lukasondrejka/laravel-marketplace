<?php

namespace App\Livewire\Listings;

use App\Enums\ListingStatus;
use App\Models\Listing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    public Listing $listing;

    public function mount(Listing $listing): void
    {
        // Drafts are only visible to their owner; for everybody else they do not exist.
        abort_unless(Gate::allows('view', $listing), 404);

        $this->listing = $listing->load(['category', 'user', 'media']);
    }

    /**
     * @return Collection<int, Listing>
     */
    #[Computed]
    public function related(): Collection
    {
        return Listing::query()
            ->active()
            ->with(['category', 'media'])
            ->where('category_id', $this->listing->category_id)
            ->where('user_id', '!=', $this->listing->user_id)
            ->whereKeyNot($this->listing->id)
            ->sortBy('newest')
            ->limit(4)
            ->get();
    }

    /**
     * @return Collection<int, Listing>
     */
    #[Computed]
    public function moreFromSeller(): Collection
    {
        return Listing::query()
            ->active()
            ->with(['category', 'media'])
            ->where('user_id', $this->listing->user_id)
            ->whereKeyNot($this->listing->id)
            ->sortBy('newest')
            ->limit(4)
            ->get();
    }

    /**
     * schema.org Product structured data.
     *
     * @return array<string, mixed>
     */
    public function structuredData(): array
    {
        $listing = $this->listing;
        $images = $listing->getMedia(Listing::IMAGES)->map(fn ($media) => $media->getUrl('large'))->all();

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $listing->title,
            'description' => $listing->teaser,
            'image' => $images ?: null,
            'category' => $listing->category->name,
            'itemCondition' => $listing->condition->schemaUrl(),
            'offers' => [
                '@type' => 'Offer',
                'url' => route('listings.show', $listing),
                'price' => $listing->price,
                'priceCurrency' => $listing->currency,
                'availability' => $listing->status === ListingStatus::Active ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut',
            ],
        ]);
    }

    public function render()
    {
        $listing = $this->listing;

        return view('livewire.listings.show')->layout('components.layouts.app', [
            'title' => $listing->title,
            'description' => $listing->formattedPrice().' - '.$listing->teaser,
            'canonical' => route('listings.show', $listing),
            'image' => $listing->coverUrl('large'),
            'type' => 'product',
        ]);
    }
}
