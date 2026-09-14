@extends('layouts.admin')

@section('title', 'Manage Banners')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Banners</h2>
        <p class="text-sm text-gray-500 mt-1">Manage homepage slider and promotional banners</p>
    </div>
    <button type="button" @click="$dispatch('open-banner-modal')" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm inline-flex items-center gap-2">
        <i data-lucide="plus" class="w-4 h-4"></i> Add Banner
    </button>
</div>
@endsection

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium border-b border-gray-100 dark:border-gray-700">
                <tr>
                    <th class="px-6 py-4">Preview</th>
                    <th class="px-6 py-4">Title</th>
                    <th class="px-6 py-4">Type</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Order</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($banners as $banner)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4">
                        @if($banner->media)
                        <img src="{{ $banner->media->url }}" alt="Banner" class="h-12 w-20 object-cover rounded border border-gray-200 dark:border-gray-600">
                        @else
                        <div class="h-12 w-20 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center text-gray-400">
                            <i data-lucide="image" class="w-5 h-5"></i>
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $banner->title ?? 'Untitled' }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            {{ Str::headline($banner->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($banner->is_active)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> Inactive
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $banner->order }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" @click="$dispatch('edit-banner', {{ json_encode($banner) }})" class="p-2 text-gray-400 hover:text-primary transition-colors">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </button>
                            <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this banner?');" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i data-lucide="image" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                        <p>No banners found. Add one to get started.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Banner Modal -->
<div x-data="bannerModal" 
     @open-banner-modal.window="openModal()"
     @edit-banner.window="editModal($event.detail)"
     x-show="isOpen" 
     x-cloak 
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="isOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 backdrop-blur-sm transition-opacity" style="background-color: oklch(0 0 0 / 0.47);" @click="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div x-show="isOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form :action="formAction" method="POST">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="isEdit ? 'Edit Banner' : 'Add New Banner'"></h3>
                    <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                
                <div class="px-6 py-5 space-y-4 text-left">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Banner Image (Media ID)</label>
                        <input type="number" name="media_id" x-model="form.media_id" required class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary">
                        <p class="text-xs text-gray-500 mt-1">Upload in Media library first and paste the ID here</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Title (Optional)</label>
                        <input type="text" name="title" x-model="form.title" class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Banner Type</label>
                        <select name="type" x-model="form.type" required class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary appearance-none">
                            <option value="slider">Main Slider</option>
                            <option value="right_top">Right Top</option>
                            <option value="right_bottom">Right Bottom</option>
                            <option value="bottom_strip">Bottom Strip</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Link URL (Optional)</label>
                        <input type="url" name="link_url" x-model="form.link_url" placeholder="https://" class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Sort Order</label>
                            <input type="number" name="order" x-model="form.order" required class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>
                        <div class="flex-1 pt-6">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 rounded-b-2xl">
                    <button type="button" @click="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary border border-transparent rounded-lg shadow-sm hover:bg-primary-dark focus:outline-none">
                        <span x-text="isEdit ? 'Update Banner' : 'Create Banner'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('bannerModal', () => ({
            isOpen: false,
            isEdit: false,
            formAction: '{{ route('admin.banners.store') }}',
            form: {
                id: null,
                media_id: '',
                title: '',
                type: 'slider',
                link_url: '',
                order: 0,
                is_active: true
            },
            
            openModal() {
                this.isEdit = false;
                this.formAction = '{{ route('admin.banners.store') }}';
                this.form = { id: null, media_id: '', title: '', type: 'slider', link_url: '', order: 0, is_active: true };
                this.isOpen = true;
                setTimeout(() => lucide.createIcons(), 50);
            },
            
            editModal(banner) {
                this.isEdit = true;
                this.formAction = `/admin/banners/${banner.id}`;
                this.form = {
                    id: banner.id,
                    media_id: banner.media_id,
                    title: banner.title || '',
                    type: banner.type,
                    link_url: banner.link_url || '',
                    order: banner.order,
                    is_active: banner.is_active == 1
                };
                this.isOpen = true;
                setTimeout(() => lucide.createIcons(), 50);
            },
            
            closeModal() {
                this.isOpen = false;
            }
        }));
    });
</script>
@endpush
@endsection
