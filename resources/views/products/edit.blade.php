<x-app-layout>
    @section('page-title', 'Edit ' . $product->name)
    @section('header-actions')
        <a href="{{ route('products.show', $product) }}" class="btn-secondary btn-sm">&larr; Back to Product</a>
    @endsection

    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="space-y-6">

                {{-- Basic Info --}}
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-4">Basic Information</h2>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="form-label">Product Name <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}"
                                   class="form-input @error('name') border-red-400 @enderror">
                            @error('name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="sku" class="form-label">SKU</label>
                            <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}"
                                   class="form-input font-mono @error('sku') border-red-400 @enderror">
                            @error('sku') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="barcode" class="form-label">Barcode</label>
                            <input type="text" id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}"
                                   class="form-input font-mono @error('barcode') border-red-400 @enderror">
                            @error('barcode') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="category_id" class="form-label">Category</label>
                            <select id="category_id" name="category_id" class="form-select @error('category_id') border-red-400 @enderror">
                                <option value="">No category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="form-input @error('description') border-red-400 @enderror">{{ old('description', $product->description) }}</textarea>
                            @error('description') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Pricing --}}
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-4">Pricing</h2>
                    <p class="text-xs text-gray-400 mb-4">Stock quantity is managed through inventory adjustments, not here.</p>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="price" class="form-label">Selling Price <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">$</span>
                                <input type="number" id="price" name="price"
                                       value="{{ old('price', number_format($product->price, 2, '.', '')) }}"
                                       step="0.01" min="0"
                                       class="form-input pl-7 @error('price') border-red-400 @enderror">
                            </div>
                            @error('price') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="cost" class="form-label">Cost Price</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">$</span>
                                <input type="number" id="cost" name="cost"
                                       value="{{ old('cost', $product->cost ? number_format($product->cost, 2, '.', '') : '') }}"
                                       step="0.01" min="0"
                                       class="form-input pl-7 @error('cost') border-red-400 @enderror">
                            </div>
                            @error('cost') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="min_stock_level" class="form-label">Min Stock Level <span class="text-red-500">*</span></label>
                            <input type="number" id="min_stock_level" name="min_stock_level"
                                   value="{{ old('min_stock_level', $product->min_stock_level) }}"
                                   min="0" step="1"
                                   class="form-input @error('min_stock_level') border-red-400 @enderror">
                            @error('min_stock_level') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Status & Image --}}
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-4">Status & Media</h2>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="status" class="form-label">Status <span class="text-red-500">*</span></label>
                            <select id="status" name="status" class="form-select @error('status') border-red-400 @enderror">
                                <option value="active"   {{ old('status', $product->status) === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="draft"    {{ old('status', $product->status) === 'draft'    ? 'selected' : '' }}>Draft</option>
                            </select>
                            @error('status') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="hidden" name="is_featured" value="0">
                                <input type="checkbox" name="is_featured" value="1"
                                       {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">Featured product</span>
                            </label>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="image" class="form-label">Product Image</label>

                            @if($product->image)
                                <div class="mb-3 flex items-center gap-3">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                         class="h-16 w-16 rounded-lg object-cover border border-gray-200">
                                    <p class="text-sm text-gray-500">Upload a new image to replace the current one.</p>
                                </div>
                            @endif

                            <input type="file" id="image" name="image" accept="image/jpg,image/jpeg,image/png,image/webp"
                                   class="block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4 file:rounded-lg
                                          file:border-0 file:text-sm file:font-medium
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100
                                          @error('image') border border-red-400 rounded-lg p-2 @enderror">
                            <p class="mt-1 text-xs text-gray-400">JPG, PNG or WebP — max 2 MB.</p>
                            @error('image') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3">
                    <a href="{{ route('products.show', $product) }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>

            </div>
        </form>
    </div>
</x-app-layout>
