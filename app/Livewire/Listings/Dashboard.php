<?php

namespace App\Livewire\Listings;

use App\Actions\Listings\ChangeListingStatus;
use App\Actions\Listings\DeleteListing;
use App\Enums\ListingStatus;
use App\Models\Listing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $status = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Resolve a listing owned by the current user; other users' ids are a 404/403.
     */
    private function ownedListing(int $id): Listing
    {
        $listing = Listing::findOrFail($id);
        $this->authorize('update', $listing);

        return $listing;
    }

    public function setStatus(int $id, string $status, ChangeListingStatus $action): void
    {
        $target = ListingStatus::tryFrom($status);
        abort_if($target === null, 422);

        $action->handle($this->ownedListing($id), $target);

        $this->dispatch('notify', type: 'success', message: __('Listing marked as :status.', ['status' => mb_strtolower($target->label())]));
    }

    public ?int $deleting = null;

    public function confirmDelete(int $id): void
    {
        $this->deleting = $id;
    }

    public function delete(DeleteListing $action): void
    {
        abort_if($this->deleting === null, 422);

        $listing = $this->ownedListing($this->deleting);
        $this->deleting = null;
        $this->authorize('delete', $listing);

        $action->handle($listing);

        $this->dispatch('notify', type: 'success', message: __('Listing deleted.'));
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function counts(): array
    {
        $counts = auth()->user()->listings()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            '' => (int) $counts->sum(),
            ...collect(ListingStatus::cases())->mapWithKeys(fn ($s) => [$s->value => (int) ($counts[$s->value] ?? 0)])->all(),
        ];
    }

    /**
     * @return LengthAwarePaginator<int, Listing>
     */
    #[Computed]
    public function listings(): LengthAwarePaginator
    {
        return auth()->user()->listings()
            ->with(['category', 'media'])
            ->when(ListingStatus::tryFrom($this->status), fn ($query, $status) => $query->where('status', $status))
            ->latest('updated_at')
            ->paginate(10)
            ->withPath(route('dashboard'));
    }

    public function paginationView(): string
    {
        return 'pagination.daisyui';
    }

    public function render()
    {
        return view('livewire.listings.dashboard')->title(__('My listings'));
    }
}
