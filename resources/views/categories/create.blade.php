<x-app-layout>
    @section('page-title', 'New Category')
    @section('header-actions')
        <a href="{{ route('categories.index') }}" class="btn-secondary btn-sm">&larr; Back to Categories</a>
    @endsection

    <div class="mx-auto max-w-xl">
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf

            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm space-y-4">

                <div>
                    <label for="name" class="form-label">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           class="form-input @error('name') border-red-400 @enderror"
                           placeholder="e.g. Electronics">
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="form-input @error('description') border-red-400 @enderror"
                              placeholder="Optional description…">{{ old('description') }}</textarea>
                    @error('description') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           {{ old('is_active', '1') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm text-gray-700 cursor-pointer">Active</label>
                </div>

            </div>

            <div class="mt-4 flex justify-end gap-3">
                <a href="{{ route('categories.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create Category</button>
            </div>
        </form>
    </div>
</x-app-layout>
