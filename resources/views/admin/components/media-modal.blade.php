<div x-data="mediaModal()" x-show="isOpen" @open-media-modal.window="open($event.detail)" @keydown.escape.window="close()" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="isOpen" x-transition.opacity class="fixed inset-0 transition-opacity" style="background-color: oklch(0 0 0 / 0.47);" @click="close()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="isOpen" x-transition.scale.origin.bottom class="relative z-10 inline-block w-full max-w-4xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 rounded-xl shadow-xl sm:align-middle">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Media Library</h3>
                <button @click="close()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700 mb-4">
                <nav class="-mb-px flex space-x-8">
                    <button @click="tab = 'library'" :class="tab === 'library' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'" class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                        Library
                    </button>
                    <button @click="tab = 'upload'" :class="tab === 'upload' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'" class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                        Upload Files
                    </button>
                </nav>
            </div>

            <!-- Upload Tab -->
            <div x-show="tab === 'upload'" class="py-6">
                <form @submit.prevent="uploadFile" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 dark:hover:bg-bray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 dark:hover:bg-gray-600 relative">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <i data-lucide="upload-cloud" class="w-10 h-10 mb-3 text-gray-400"></i>
                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG or GIF (MAX. 10MB)</p>
                    </div>
                    <input type="file" x-ref="fileInput" @change="uploadFile" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                    
                    <div x-show="uploading" class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 flex items-center justify-center rounded-xl">
                        <div class="animate-spin rounded-xl h-10 w-10 border-b-2 border-primary"></div>
                    </div>
                </form>
            </div>

            <!-- Library Tab -->
            <div x-show="tab === 'library'" class="py-2">
                <div x-show="loading" class="flex justify-center items-center h-64">
                    <div class="animate-spin rounded-xl h-10 w-10 border-b-2 border-primary"></div>
                </div>
                
                <div x-show="!loading" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 max-h-96 overflow-y-auto p-1">
                    <template x-for="item in media" :key="item.id">
                        <div @click="selectMedia(item)" :class="selected && selected.id === item.id ? 'ring-2 ring-primary ring-offset-2 dark:ring-offset-gray-800' : ''" class="relative group aspect-square rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer bg-gray-100 dark:bg-gray-900">
                            <template x-if="item.file_type && item.file_type.startsWith('image/')">
                                <img :src="item.url" class="w-full h-full object-cover" />
                            </template>
                            <template x-if="!item.file_type || !item.file_type.startsWith('image/')">
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <i data-lucide="file" class="w-8 h-8"></i>
                                </div>
                            </template>
                        </div>
                    </template>
                    <div x-show="media.length === 0" class="col-span-full py-12 text-center text-gray-500">
                        No media found.
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="mt-6 flex justify-end space-x-3 border-t border-gray-200 dark:border-gray-700 pt-4">
                <button @click="close()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Cancel
                </button>
                <button @click="confirmSelection()" :disabled="!selected" :class="!selected ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-lg hover:shadow-primary/30 transform hover:-translate-y-0.5'" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-xl transition-all">
                    Select Media
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function mediaModal() {
        return {
            isOpen: false,
            tab: 'library',
            media: [],
            loading: false,
            uploading: false,
            selected: null,
            callbackEvent: null,

            open(detail) {
                this.callbackEvent = detail.callbackEvent;
                this.isOpen = true;
                this.tab = 'library';
                this.selected = null;
                this.fetchMedia();
                
                // re-initialize lucide icons inside modal if needed
                setTimeout(() => lucide.createIcons(), 50);
            },

            close() {
                this.isOpen = false;
            },

            async fetchMedia() {
                this.loading = true;
                try {
                    const res = await fetch('{{ route("admin.media.index") }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    this.media = await res.json();
                } catch (e) {
                    console.error("Failed to fetch media", e);
                }
                this.loading = false;
            },

            async uploadFile() {
                const file = this.$refs.fileInput.files[0];
                if (!file) return;

                this.uploading = true;
                const formData = new FormData();
                formData.append('file', file);
                formData.append('_token', '{{ csrf_token() }}');

                try {
                    const res = await fetch('{{ route("admin.media.store") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    
                    if (res.ok) {
                        const newMedia = await res.json();
                        this.media.unshift(newMedia);
                        this.tab = 'library';
                        this.selected = newMedia;
                    } else {
                        alert('Upload failed.');
                    }
                } catch (e) {
                    console.error("Upload error", e);
                }
                this.uploading = false;
                this.$refs.fileInput.value = ''; // reset
            },

            selectMedia(item) {
                this.selected = item;
            },

            confirmSelection() {
                if (this.selected && this.callbackEvent) {
                    window.dispatchEvent(new CustomEvent(this.callbackEvent, { detail: this.selected }));
                    this.close();
                }
            }
        }
    }
</script>
@endpush
