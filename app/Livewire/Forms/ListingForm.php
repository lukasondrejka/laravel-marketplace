<?php

namespace App\Livewire\Forms;

use App\Enums\ListingCondition;
use App\Enums\ListingType;
use App\Models\Listing;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ListingForm extends Form
{
    public string $type = 'offer';

    public string $title = '';

    public string $category_id = '';

    public string $description = '';

    public string $price = '';

    public string $condition = '';

    public string $city = '';

    public bool $show_phone = false;

    public function fillFromListing(Listing $listing): void
    {
        $this->type = $listing->type->value;
        $this->title = $listing->title;
        $this->category_id = (string) $listing->category_id;
        $this->description = $listing->description;
        $this->price = (string) $listing->price;
        $this->condition = $listing->condition->value;
        $this->city = (string) $listing->city;
        $this->show_phone = $listing->show_phone;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ListingType::class)],
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'condition' => ['required', Rule::enum(ListingCondition::class)],
            'city' => ['nullable', 'string', 'max:100'],
            'show_phone' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'category_id' => __('category'),
        ];
    }

    /**
     * Validated attributes in the shape the actions expect.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $data = $this->validate();
        $data['city'] = $data['city'] ?: null;
        $data['currency'] = config('marketplace.currency');

        return $data;
    }
}
