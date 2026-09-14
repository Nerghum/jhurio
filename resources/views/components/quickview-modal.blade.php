<!-- Global Quickview Modal -->
<div x-data="quickviewModal" x-show="isOpen" x-cloak class="relative z-[90]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 backdrop-blur-none"
         x-transition:enter-end="opacity-100 backdrop-blur-sm"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 backdrop-blur-sm"
         x-transition:leave-end="opacity-0 backdrop-blur-none"
         class="fixed inset-0 transition-all" style="background-color: oklch(0 0 0 / 0.47);"
         @click="close()"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="isOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl">
                
                <!-- Close Button -->
                <div class="absolute right-4 top-4 z-10">
                    <button type="button" @click="close()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none bg-white/80 dark:bg-gray-800/80 rounded-full p-1 backdrop-blur-sm shadow-sm">
                        <span class="sr-only">Close panel</span>
                        <i data-lucide="x" class="h-6 w-6"></i>
                    </button>
                </div>

                <div class="bg-white dark:bg-gray-900 w-full min-h-[400px] flex items-center justify-center relative">
                    <!-- Loading Spinner -->
                    <div x-show="loading" class="absolute inset-0 flex flex-col items-center justify-center bg-white/90 dark:bg-gray-900/90 z-20 backdrop-blur-sm">
                        <div class="animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-primary dark:border-gray-700 dark:border-t-primary"></div>
                        <p class="mt-4 text-gray-500 font-medium">Loading details...</p>
                    </div>

                    <!-- Content Container -->
                    <div x-ref="contentContainer" class="w-full">
                        <!-- Content injected via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('quickviewModal', () => ({
            isOpen: false,
            loading: false,
            slug: null,

            init() {
                window.addEventListener('open-quickview', (e) => {
                    this.slug = e.detail.slug;
                    this.open();
                });
                window.addEventListener('close-quickview', () => {
                    this.close();
                });
            },

            open() {
                this.isOpen = true;
                this.loading = true;
                this.$refs.contentContainer.innerHTML = '';
                this.fetchProduct();
            },

            close() {
                this.isOpen = false;
                setTimeout(() => {
                    this.$refs.contentContainer.innerHTML = '';
                }, 300);
            },

            async fetchProduct() {
                try {
                    const response = await fetch(`/product/${this.slug}/quickview`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (!response.ok) throw new Error('Failed to load');
                    
                    const html = await response.text();
                    this.$refs.contentContainer.innerHTML = html;
                    
                    // Evaluate scripts from injected HTML so Alpine.data registers
                    Array.from(this.$refs.contentContainer.querySelectorAll('script')).forEach(oldScript => {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });
                    
                    // Re-initialize lucide icons for injected content
                    if(window.lucide) {
                        window.lucide.createIcons();
                    }
                } catch(error) {
                    console.error('Quickview error:', error);
                    this.$refs.contentContainer.innerHTML = '<div class="p-12 text-center text-red-500"><p>Failed to load product details.</p></div>';
                } finally {
                    this.loading = false;
                }
            }
        }));

        Alpine.data('quickviewProduct', (initialData) => ({
            product: initialData.product,
            basePrice: initialData.product.price,
            currentPrice: initialData.product.price,
            baseStock: initialData.product.stock_quantity,
            currentStock: initialData.product.stock_quantity,
            quantity: 1,
            loading: false,
            images: initialData.images || [],
            activeImage: initialData.images && initialData.images.length > 0 ? initialData.images[0] : '',
            variations: initialData.variations || [],
            activeVariation: null,
            uniqueColors: [],
            uniqueSizes: [],
            selectedColor: null,
            selectedSize: null,

            init() {
                if (this.variations.length > 0) {
                    const colors = new Map();
                    const sizes = new Set();
                    
                    this.variations.forEach(v => {
                        if (v.name) colors.set(v.name, v.color_code);
                        if (v.size) sizes.add(v.size);
                    });
                    
                    this.uniqueColors = Array.from(colors, ([name, code]) => ({ name, code }));
                    this.uniqueSizes = Array.from(sizes);
                    
                    if (this.uniqueColors.length > 0) {
                        this.selectedColor = this.uniqueColors[0].name;
                    }
                    if (this.uniqueSizes.length > 0) {
                        this.selectedSize = this.uniqueSizes[0];
                    }
                    
                    this.updateSelection();
                }
            },
            
            updateSelection() {
                let match = this.variations.find(v => {
                    let colorMatch = this.uniqueColors.length > 0 ? (v.name === this.selectedColor) : true;
                    let sizeMatch = this.uniqueSizes.length > 0 ? (v.size === this.selectedSize) : true;
                    return colorMatch && sizeMatch;
                });

                if (match) {
                    this.activeVariation = match;
                    this.currentPrice = match.price ? parseFloat(match.price) : this.basePrice;
                    this.currentStock = match.stock_quantity;
                    this.quantity = 1;
                    if (match.media_url) this.activeImage = match.media_url;
                } else {
                    this.activeVariation = null;
                    this.currentStock = 0;
                }
            },

            selectColor(name) {
                this.selectedColor = name;
                this.updateSelection();
            },

            selectSize(size) {
                this.selectedSize = size;
                this.updateSelection();
            },
            
            openExpress() {
                window.dispatchEvent(new CustomEvent('close-quickview'));
                
                setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('open-checkout', {
                        detail: {
                            product_id: this.product.id,
                            variation_id: this.activeVariation ? this.activeVariation.id : null,
                            product_name: this.product.name,
                            price: this.currentPrice,
                            quantity: this.quantity,
                            product_image: this.activeImage
                        }
                    }));
                }, 300);
            },

            addToCart() {
                if (this.variations.length > 0 && !this.activeVariation) {
                    alert('Please select valid options before adding to cart.');
                    return;
                }

                this.loading = true;

                window.dispatchEvent(new CustomEvent('add-to-cart', {
                    detail: {
                        product_id: this.product.id,
                        variation_id: this.activeVariation ? this.activeVariation.id : null,
                        quantity: this.quantity
                    }
                }));

                setTimeout(() => {
                    this.loading = false;
                    window.dispatchEvent(new CustomEvent('close-quickview'));
                }, 500);
            }
        }));
    });
</script>
