<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ItemController extends Controller
{
    /**
     * Number of items per page.
     */
    private const ITEMS_PER_PAGE = 10;

    /**
     * Display items.
     */
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'category_id' => 'nullable|integer',
            'price_min' => 'nullable|integer',
            'price_max' => 'nullable|integer',
        ]);

        $words = preg_split("/[\s,]+/", $request->search) ?? [];

        $items = Item::with('category')
            ->when($words, fn ($query, $words) => $query->where(function ($query) use ($words) {
                foreach ($words as $word) {
                    $query->where(function ($sub_query) use ($word) {
                        $sub_query->where('title', 'like', '%'.$word.'%')
                            ->orWhere('description', 'like', '%'.$word.'%');
                    });
                }
            }))
            ->when($request->category_id, fn ($query, $category_id) => $query->where('category_id', $category_id))
            ->when($request->price_min, fn ($query, $price_min) => $query->where('price', '>=', $price_min))
            ->when($request->price_max, fn ($query, $price_max) => $query->where('price', '<=', $price_max))
            ->orderBy('created_at', 'desc')
            ->paginate(self::ITEMS_PER_PAGE);

        return Inertia::render('Items/Items', [
            'items' => $items,
            'categories' => Category::all(),
        ]);
    }

    /**
     * Show the form for creating new item.
     */
    public function create()
    {
        return Inertia::render('Items/ItemForm', [
            'categories' => Category::all(),
        ]);
    }

    /**
     * Store newly created item.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:64',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ], [], [
            'category_id' => 'category',
        ]);

        $item = Auth::user()->items()->create($request->all());
        $item->save();

        return redirect()->route('items.show', ['id' => $item->id]);
    }

    /**
     * Display item with the specified id.
     */
    public function show(int $id)
    {
        return Inertia::render('Items/Item', [
            'item' => Item::with(['category', 'user.items'])->find($id)->append('rich_text_description'),
        ]);
    }

    /**
     * Show the form for editing item with the specified id.
     */
    public function edit(int $id)
    {
        $item = Item::with('category')->find($id);

        return Inertia::render('Items/ItemForm', [
            'item' => $item,
            'categories' => Category::all(),
        ]);
    }

    /**
     * Update item with the specified id.
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'title' => 'required|string|max:64',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ], [], [
            'category_id' => 'category',
        ]);

        $item = Auth::user()->items()->findOrFail($id);
        $item->update($request->all());
        $item->save();

        return redirect()->route('items.show', ['id' => $item->id]);
    }

    /**
     * Remove item with the specified id.
     */
    public function destroy(int $id)
    {
        $item = Auth::user()->items()->findOrFail($id);
        $item->delete();

        return Redirect::to('/');
    }
}
