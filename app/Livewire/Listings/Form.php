<?php

namespace App\Livewire\Listings;

use App\Actions\Listings\CreateListing;
use App\Actions\Listings\UpdateListing;
use App\Livewire\Forms\ListingForm;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public const CREATE_LIMIT_PER_HOUR = 20;

    public ListingForm $form;

    public ?Listing $listing = null;

    /** Files chosen in the browser; moved into $newImages right away. */
    public array $uploads = [];

    /** @var array<string, TemporaryUploadedFile> */
    public array $newImages = [];

    /** Ordered image keys: "m:<media id>" for saved images, "n:<temp name>" for new uploads. First is the cover. */
    public array $imageOrder = [];

    public function mount(?Listing $listing = null): void
    {
        if ($listing?->exists) {
            $this->authorize('update', $listing);
            $this->listing = $listing->load('media');
            $this->form->fillFromListing($listing);
            $this->imageOrder = $listing->getMedia(Listing::IMAGES)->map(fn ($m) => 'm:'.$m->id)->all();

            return;
        }

        $this->authorize('create', Listing::class);
        $user = auth()->user();
        $this->form->city = (string) $user->city;
        $this->form->show_phone = (bool) $user->phone;
        $this->form->condition = 'good';
    }

    /**
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::query()->orderBy('sort_order')->get();
    }

    public function updatedUploads(): void
    {
        $this->resetErrorBag('uploads');

        $validator = $this->imageValidator('uploads', $this->uploads);

        if ($validator->fails()) {
            $this->addError('uploads', $validator->errors()->first());
            $this->uploads = [];

            return;
        }

        foreach ($this->uploads as $file) {
            if (count($this->imageOrder) >= Listing::MAX_IMAGES) {
                $this->addError('uploads', __('You can add at most :max images.', ['max' => Listing::MAX_IMAGES]));
                break;
            }

            $key = 'n:'.$file->getFilename();
            $this->newImages[$key] = $file;
            $this->imageOrder[] = $key;
        }

        $this->uploads = [];
    }

    /**
     * @param  array<int|string, mixed>  $files
     */
    private function imageValidator(string $field, array $files): ValidatorContract
    {
        return Validator::make(
            [$field => $files],
            ["$field.*" => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('marketplace.images.max_size_kb')]],
            [],
            ["$field.*" => __('image')],
        );
    }

    public function removeImage(string $key): void
    {
        $this->imageOrder = array_values(array_diff($this->imageOrder, [$key]));
        unset($this->newImages[$key]);
    }

    /**
     * Drag and drop: place an image at a zero-based position.
     */
    public function moveImageTo(string $key, int $position): void
    {
        if (! in_array($key, $this->imageOrder, true)) {
            return;
        }

        $rest = array_values(array_diff($this->imageOrder, [$key]));
        array_splice($rest, max(0, min($position, count($rest))), 0, [$key]);
        $this->imageOrder = $rest;
    }

    public function makeCover(string $key): void
    {
        if (in_array($key, $this->imageOrder, true)) {
            $this->imageOrder = [$key, ...array_values(array_diff($this->imageOrder, [$key]))];
        }
    }

    /**
     * Ordered images for the actions: existing media ids (int) and new uploads.
     *
     * @return array<int, int|TemporaryUploadedFile>
     */
    private function orderedImages(): array
    {
        $images = [];

        foreach ($this->imageOrder as $key) {
            if (str_starts_with($key, 'm:')) {
                $images[] = (int) substr($key, 2);
            } elseif (isset($this->newImages[$key])) {
                $images[] = $this->newImages[$key];
            }
        }

        return $images;
    }

    public function save(string $status = 'draft')
    {
        $data = $this->form->payload();

        // $newImages is client-writable state, so never trust it past the upload step.
        $this->imageValidator('newImages', $this->newImages)->validate();

        if ($this->listing) {
            $this->authorize('update', $this->listing);
            $listing = app(UpdateListing::class)->handle($this->listing, $data, $this->orderedImages());
            session()->flash('success', __('Listing saved.'));
        } else {
            $this->authorize('create', Listing::class);

            $key = 'listing-create:'.auth()->id();

            if (RateLimiter::tooManyAttempts($key, self::CREATE_LIMIT_PER_HOUR)) {
                $this->addError('form.title', __('You are posting too fast. Please try again later.'));

                return null;
            }

            RateLimiter::hit($key, 3600);
            $data['status'] = $status === 'active' ? 'active' : 'draft';
            $listing = app(CreateListing::class)->handle(
                auth()->user(),
                $data,
                array_values(array_filter($this->orderedImages(), fn ($i) => ! is_int($i))),
            );
            session()->flash('success', $data['status'] === 'active' ? __('Listing published.') : __('Listing saved as draft.'));
        }

        return $this->redirectRoute('listings.show', $listing, navigate: true);
    }

    public function render()
    {
        return view('livewire.listings.form')->title($this->listing ? __('Edit listing') : __('Post a listing'));
    }
}
