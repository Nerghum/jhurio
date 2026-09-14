<!-- Slide-over Side Cart -->
<div class="relative z-[100]" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" x-show="isOpen" x-cloak>
    <!-- Background backdrop -->
    <div x-show="isOpen"
         x-transition:enter="ease-in-out duration-500"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in-out duration-500"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 transition-opacity" style="background-color: oklch(0 0 0 / 0.47);"
         @click="isOpen = false"></div>

    <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <!-- Slide-over panel -->
                <div x-show="isOpen"
                     @click.away="isOpen = false"
                     x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="pointer-events-auto w-screen max-w-md">
                    <div class="flex h-full flex-col overflow-y-scroll bg-white dark:bg-gray-900 shadow-xl">
                        <div class="flex-1 overflow-y-auto px-4 py-6 sm:px-6">
                            <div class="flex items-start justify-between">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white" id="slide-over-title">Shopping cart</h2>
                                <div class="ml-3 flex h-7 items-center">
                                    <button type="button" class="relative -m-2 p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300" @click="isOpen = false">
                                        <span class="absolute -inset-0.5"></span>
                                        <span class="sr-only">Close panel</span>
                                        <i data-lucide="x" class="h-6 w-6"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-8">
                                <div class="flow-root">
                                    <ul role="list" class="-my-6 divide-y divide-gray-200 dark:divide-gray-800">
                                        <template x-if="items.length === 0">
                                            <li class="py-6 text-center text-gray-500 dark:text-gray-400">
                                                Your cart is empty
                                            </li>
                                        </template>

                                        <template x-for="item in items" :key="item.id">
                                            <li class="flex py-6">
                                                <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                                                    <img :src="item.image || 'https://via.placeholder.com/150'" :alt="item.name" class="h-full w-full object-cover object-center">
                                                </div>

                                                <div class="ml-4 flex flex-1 flex-col">
                                                    <div>
                                                        <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                                            <h3>
                                                                <a :href="item.url" x-text="item.name"></a>
                                                            </h3>
                                                            <p class="ml-4" x-text="Number(item.price * item.quantity).toFixed(0) + ' BDT'"></p>
                                                        </div>
                                                        <template x-if="item.variation_name">
                                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="item.variation_name"></p>
                                                        </template>
                                                    </div>
                                                    <div class="flex flex-1 items-end justify-between text-sm">
                                                        <div class="flex items-center border border-gray-300 dark:border-gray-700 rounded-xl">
                                                            <button @click="updateQuantity(item.id, item.quantity - 1)" class="px-2 py-1 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">-</button>
                                                            <span class="px-2 py-1 text-gray-900 dark:text-white" x-text="item.quantity"></span>
                                                            <button @click="updateQuantity(item.id, item.quantity + 1)" class="px-2 py-1 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">+</button>
                                                        </div>

                                                        <div class="flex">
                                                            <button type="button" class="font-medium text-primary hover:text-secondary" @click="removeItem(item.id)">Remove</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-800 px-4 py-6 sm:px-6">
                            <div class="flex justify-between text-base font-medium text-gray-900 dark:text-white">
                                <p>Subtotal</p>
                                <p x-text="Number(totalPrice).toFixed(0) + ' BDT'"></p>
                            </div>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Shipping and taxes calculated at checkout.</p>
                            <div class="mt-6">
                                <a href="#" class="flex items-center justify-center rounded-xl border border-transparent bg-primary px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-secondary">Checkout</a>
                            </div>
                            <div class="mt-6 flex justify-center text-center text-sm text-gray-500 dark:text-gray-400">
                                <p>
                                    or
                                    <button type="button" class="font-medium text-primary hover:text-secondary" @click="isOpen = false">
                                        Continue Shopping
                                        <span aria-hidden="true"> &rarr;</span>
                                    </button>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
