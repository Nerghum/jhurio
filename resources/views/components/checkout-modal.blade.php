<!-- Global Express Checkout Modal -->
<div x-data="checkoutModal" x-show="isOpen" x-cloak class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
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
                 x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 w-full max-w-4xl border border-gray-100 dark:border-gray-700">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="modal-title">ক্যাশ অন ডেলিভারিতে অর্ডার করতে আপনার তথ্য দিন</h3>
                    <button type="button" @click="close()" class="text-gray-400 hover:text-gray-500 focus:outline-none transition-colors">
                        <span class="sr-only">Close</span>
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <div class="flex flex-col md:flex-row">
                    <!-- Left Column: Form -->
                    <div class="w-full md:w-3/5 p-6 md:border-r border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
                        <form @submit.prevent="submit" class="space-y-5">
                            
                            <!-- Address Selection for Logged-in Users -->
                            <template x-if="isLoggedIn && savedAddresses.length > 0">
                                <div class="mb-5 border border-primary/20 bg-primary/5 rounded-xl p-4">
                                    <label class="block text-sm font-semibold text-primary mb-3">Saved Addresses</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <template x-for="addr in savedAddresses" :key="addr.id">
                                            <div @click="selectAddress(addr)"
                                                 class="cursor-pointer rounded-lg border p-3 transition-all relative overflow-hidden"
                                                 :class="selectedAddressId === addr.id ? 'border-primary bg-white shadow-sm ring-1 ring-primary' : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 hover:border-primary/50'">
                                                
                                                <div x-show="selectedAddressId === addr.id" class="absolute top-0 right-0 w-8 h-8 flex items-start justify-end p-1">
                                                    <div class="w-0 h-0 border-t-[20px] border-l-[20px] border-t-primary border-l-transparent absolute top-0 right-0"></div>
                                                    <i data-lucide="check" class="w-3 h-3 text-white absolute top-0.5 right-0.5 z-10"></i>
                                                </div>
                                                
                                                <div class="flex items-center gap-1.5 mb-1 text-sm font-bold text-gray-900 dark:text-white">
                                                    <i :data-lucide="addr.label.toLowerCase() === 'home' ? 'home' : (addr.label.toLowerCase() === 'work' ? 'briefcase' : 'map-pin')" class="w-3.5 h-3.5 text-gray-400"></i>
                                                    <span x-text="addr.label"></span>
                                                    <span x-show="addr.is_default" class="ml-auto text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded font-semibold">Default</span>
                                                </div>
                                                <div class="text-xs text-gray-600 dark:text-gray-300 space-y-0.5">
                                                    <p class="truncate" x-text="addr.name"></p>
                                                    <p class="truncate" x-text="addr.phone"></p>
                                                    <p class="truncate text-gray-400" x-text="addr.district"></p>
                                                </div>
                                            </div>
                                        </template>
                                        <!-- Custom Address Option -->
                                        <div @click="selectAddress(null)"
                                             class="cursor-pointer rounded-lg border p-3 transition-all flex flex-col items-center justify-center text-center gap-1"
                                             :class="selectedAddressId === null ? 'border-primary bg-white shadow-sm ring-1 ring-primary text-primary' : 'border-gray-200 border-dashed dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-500 hover:border-primary/50'">
                                            <i data-lucide="plus" class="w-5 h-5"></i>
                                            <span class="text-xs font-semibold">New Address</span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">আপনার নাম <span class="text-red-500">*</span></label>
                                <div class="flex rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus-within:ring-2 focus-within:ring-inset focus-within:ring-primary overflow-hidden"
                                     :class="isUsingSavedAddress ? 'opacity-70 bg-gray-50' : ''">
                                    <span class="flex select-none items-center pl-3 pr-3 bg-gray-100 dark:bg-gray-700 text-gray-500">
                                        <i data-lucide="user" class="w-5 h-5"></i>
                                    </span>
                                    <input type="text" x-model="name" required :readonly="isUsingSavedAddress"
                                           class="block flex-1 border-0 bg-transparent py-2.5 pl-2 text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6" placeholder="আপনার নাম">
                                </div>
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">মোবাইল নাম্বার <span class="text-red-500">*</span></label>
                                <div class="flex rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus-within:ring-2 focus-within:ring-inset focus-within:ring-primary overflow-hidden"
                                     :class="isUsingSavedAddress ? 'opacity-70 bg-gray-50' : ''">
                                    <span class="flex select-none items-center pl-3 pr-3 bg-gray-100 dark:bg-gray-700 text-gray-500">
                                        <i data-lucide="phone" class="w-5 h-5"></i>
                                    </span>
                                    <input type="tel" x-model="phone" required :readonly="isUsingSavedAddress"
                                           class="block flex-1 border-0 bg-transparent py-2.5 pl-2 text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6" placeholder="মোবাইল নাম্বার">
                                </div>
                            </div>

                            <!-- Address -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">এড্রেস <span class="text-red-500">*</span></label>
                                <div class="flex rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus-within:ring-2 focus-within:ring-inset focus-within:ring-primary overflow-hidden"
                                     :class="isUsingSavedAddress ? 'opacity-70 bg-gray-50' : ''">
                                    <span class="flex select-none items-center pl-3 pr-3 bg-gray-100 dark:bg-gray-700 text-gray-500">
                                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                                    </span>
                                    <input type="text" x-model="address" required :readonly="isUsingSavedAddress"
                                           class="block flex-1 border-0 bg-transparent py-2.5 pl-2 text-gray-900 dark:text-white placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6" placeholder="আপনার পূর্ণ ঠিকানা">
                                </div>
                            </div>

                            <!-- District -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">জেলা <span class="text-red-500">*</span></label>
                                <div class="flex rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus-within:ring-2 focus-within:ring-inset focus-within:ring-primary overflow-hidden relative"
                                     :class="isUsingSavedAddress ? 'opacity-70 bg-gray-50 pointer-events-none' : ''">
                                    <select x-model="district" required :disabled="isUsingSavedAddress"
                                            class="block w-full border-0 bg-transparent py-2.5 pl-3 pr-10 text-gray-900 dark:text-white focus:ring-0 sm:text-sm sm:leading-6 appearance-none">
                                        <option value="" disabled selected>জেলা নির্বাচন করুন</option>
                                        <option value="Bagerhat">Bagerhat</option>
                                        <option value="Bandarban">Bandarban</option>
                                        <option value="Barguna">Barguna</option>
                                        <option value="Barishal">Barishal</option>
                                        <option value="Bhola">Bhola</option>
                                        <option value="Bogura">Bogura</option>
                                        <option value="Brahmanbaria">Brahmanbaria</option>
                                        <option value="Chandpur">Chandpur</option>
                                        <option value="Chattogram">Chattogram</option>
                                        <option value="Chuadanga">Chuadanga</option>
                                        <option value="Cox's Bazar">Cox's Bazar</option>
                                        <option value="Cumilla">Cumilla</option>
                                        <option value="Dhaka">Dhaka</option>
                                        <option value="Dinajpur">Dinajpur</option>
                                        <option value="Faridpur">Faridpur</option>
                                        <option value="Feni">Feni</option>
                                        <option value="Gaibandha">Gaibandha</option>
                                        <option value="Gazipur">Gazipur</option>
                                        <option value="Gopalganj">Gopalganj</option>
                                        <option value="Habiganj">Habiganj</option>
                                        <option value="Jamalpur">Jamalpur</option>
                                        <option value="Jashore">Jashore</option>
                                        <option value="Jhalokati">Jhalokati</option>
                                        <option value="Jhenaidah">Jhenaidah</option>
                                        <option value="Joypurhat">Joypurhat</option>
                                        <option value="Khagrachhari">Khagrachhari</option>
                                        <option value="Khulna">Khulna</option>
                                        <option value="Kishoreganj">Kishoreganj</option>
                                        <option value="Kurigram">Kurigram</option>
                                        <option value="Kushtia">Kushtia</option>
                                        <option value="Lakshmipur">Lakshmipur</option>
                                        <option value="Lalmonirhat">Lalmonirhat</option>
                                        <option value="Madaripur">Madaripur</option>
                                        <option value="Magura">Magura</option>
                                        <option value="Manikganj">Manikganj</option>
                                        <option value="Meherpur">Meherpur</option>
                                        <option value="Moulvibazar">Moulvibazar</option>
                                        <option value="Munshiganj">Munshiganj</option>
                                        <option value="Mymensingh">Mymensingh</option>
                                        <option value="Naogaon">Naogaon</option>
                                        <option value="Narail">Narail</option>
                                        <option value="Narayanganj">Narayanganj</option>
                                        <option value="Narsingdi">Narsingdi</option>
                                        <option value="Natore">Natore</option>
                                        <option value="Netrokona">Netrokona</option>
                                        <option value="Nilphamari">Nilphamari</option>
                                        <option value="Noakhali">Noakhali</option>
                                        <option value="Pabna">Pabna</option>
                                        <option value="Panchagarh">Panchagarh</option>
                                        <option value="Patuakhali">Patuakhali</option>
                                        <option value="Pirojpur">Pirojpur</option>
                                        <option value="Rajbari">Rajbari</option>
                                        <option value="Rajshahi">Rajshahi</option>
                                        <option value="Rangamati">Rangamati</option>
                                        <option value="Rangpur">Rangpur</option>
                                        <option value="Satkhira">Satkhira</option>
                                        <option value="Shariatpur">Shariatpur</option>
                                        <option value="Sherpur">Sherpur</option>
                                        <option value="Sirajganj">Sirajganj</option>
                                        <option value="Sunamganj">Sunamganj</option>
                                        <option value="Sylhet">Sylhet</option>
                                        <option value="Tangail">Tangail</option>
                                        <option value="Thakurgaon">Thakurgaon</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <i data-lucide="chevron-down" class="h-4 w-4 text-gray-400"></i>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Right Column: Order Summary -->
                    <div class="w-full md:w-2/5 p-6 bg-gray-50/50 dark:bg-gray-800/80 flex flex-col h-full">
                        <h4 class="text-base font-bold text-gray-900 dark:text-white mb-4 uppercase tracking-wider text-sm border-b border-gray-200 dark:border-gray-700 pb-2">Order Summary</h4>
                        
                        <!-- Product Item -->
                        <template x-if="!isCart">
                            <div class="flex items-start gap-4 mb-6 group">
                                <template x-if="productImage">
                                    <div class="w-16 h-16 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden flex-shrink-0 bg-white">
                                        <img :src="productImage" class="w-full h-full object-cover" :alt="productName">
                                    </div>
                                </template>
                                <div class="flex-1">
                                    <h5 class="text-sm font-bold text-gray-900 dark:text-white leading-snug" x-text="productName"></h5>
                                    
                                    <template x-if="bundleInfo && bundleInfo.is_bundle">
                                        <div class="mt-1">
                                            <span class="inline-block px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-full bg-primary/10 text-primary border border-primary/20" x-text="bundleInfo.bundle_name"></span>
                                            <template x-if="bundleInfo.items && bundleInfo.items.length > 0">
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 space-y-0.5">
                                                    <template x-for="item in bundleInfo.items" :key="item.item_number">
                                                        <div x-show="item.variation_name" x-text="`Item #${item.item_number}: ${item.variation_name}`"></div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    
                                    <template x-if="!bundleInfo && variationId">
                                        <p class="text-xs text-gray-500 mt-1">Variation selected</p>
                                    </template>
                                    
                                    <template x-if="!bundleInfo || !bundleInfo.is_bundle">
                                        <div class="flex items-center mt-3 border border-gray-200 dark:border-gray-600 rounded-md inline-flex bg-white dark:bg-gray-700 shadow-sm">
                                            <button type="button" @click="decrementQuantity()" class="px-2 py-1 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors rounded-l-md font-medium"> - </button>
                                            <input type="number" x-model.number="quantity" class="w-10 text-center text-sm font-medium border-0 focus:ring-0 p-0 dark:bg-gray-700 dark:text-white" min="1" readonly>
                                            <button type="button" @click="incrementQuantity()" class="px-2 py-1 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors rounded-r-md font-medium"> + </button>
                                        </div>
                                    </template>

                                    <template x-if="bundleInfo && bundleInfo.is_bundle">
                                        <div class="mt-2.5 text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700/60 px-2 py-1 rounded inline-block">
                                            Qty: <span x-text="quantity" class="font-bold"></span> pcs (Bundle Offer)
                                        </div>
                                    </template>
                                </div>
                                <div class="ml-4 text-right flex flex-col items-end">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white"><span x-text="Number(price * quantity).toFixed(0)"></span> Tk</span>
                                </div>
                            </div>
                        </template>

                        <!-- Cart Items -->
                        <template x-if="isCart">
                            <div class="mb-6 space-y-4 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                                <template x-for="item in cartItems" :key="item.id">
                                    <div class="flex items-start gap-3">
                                        <div class="w-12 h-12 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden flex-shrink-0 bg-white">
                                            <img :src="item.image || 'https://via.placeholder.com/150'" class="w-full h-full object-cover">
                                        </div>
                                        <div class="flex-1">
                                            <h5 class="text-xs font-bold text-gray-900 dark:text-white leading-snug" x-text="item.name"></h5>
                                            <template x-if="item.variation_name">
                                                <p class="text-[10px] text-gray-500 mt-0.5" x-text="item.variation_name"></p>
                                            </template>
                                            <p class="text-xs text-gray-500 mt-1">Qty: <span x-text="item.quantity"></span></p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-bold text-gray-900 dark:text-white"><span x-text="Number(item.price * item.quantity).toFixed(0)"></span> Tk</span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <hr class="border-gray-200 dark:border-gray-700 mb-4">

                        <!-- Coupon Code -->
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Discount Code</label>
                            <div class="flex mt-1 rounded-lg shadow-sm">
                                <input type="text" x-model="couponCode" :disabled="couponApplied" class="block w-full rounded-none rounded-l-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white uppercase sm:text-sm" placeholder="Enter code">
                                <button type="button" @click="applyCoupon" :disabled="couponApplied || !couponCode" class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-500 disabled:opacity-50">
                                    <span x-show="!couponLoading" x-text="couponApplied ? 'Applied' : 'Apply'"></span>
                                    <span x-show="couponLoading"><i data-lucide="loader" class="w-4 h-4 animate-spin"></i></span>
                                </button>
                            </div>
                            <template x-if="couponMessage">
                                <p class="mt-2 text-xs" :class="couponApplied ? 'text-green-600' : 'text-red-500'" x-text="couponMessage"></p>
                            </template>
                        </div>

                        @php
                            $orderBumpProductId = \App\Models\Setting::get('order_bump_product_id');
                            $orderBumpProduct = $orderBumpProductId ? \App\Models\Product::find($orderBumpProductId) : null;
                        @endphp

                        @if($orderBumpProduct)
                        <!-- Order Bump -->
                        <div class="mb-4 bg-primary/5 border border-primary/20 border-dashed rounded-xl p-4">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <div class="pt-1">
                                    <input type="checkbox" x-model="acceptOrderBump" class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary">
                                </div>
                                <div>
                                    <h5 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <i data-lucide="sparkles" class="w-4 h-4 text-primary"></i> 
                                        {{ \App\Models\Setting::get('order_bump_title', 'Yes, add this to my order!') }}
                                    </h5>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                        {{ \App\Models\Setting::get('order_bump_description', 'Special offer.') }}
                                    </p>
                                    <div class="mt-2 flex items-center gap-2">
                                        @if($orderBumpProduct->thumbnail)
                                        <img src="{{ $orderBumpProduct->thumbnail->url }}" class="w-8 h-8 rounded object-cover">
                                        @endif
                                        <span class="text-xs font-medium text-gray-900 dark:text-gray-300">{{ $orderBumpProduct->name }}</span>
                                        <span class="text-sm font-bold text-primary ml-auto">+{{ number_format($orderBumpProduct->price, 0) }} Tk</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @endif

                        <!-- Totals -->
                        <div class="space-y-2 mt-auto">
                            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                                <span>Subtotal</span>
                                <span><span x-text="isCart ? Number(cartTotal).toFixed(0) : Number(price * quantity).toFixed(0)"></span> Tk</span>
                            </div>
                            
                            <template x-if="couponApplied">
                                <div class="flex justify-between text-sm text-green-600 font-medium">
                                    <span>Discount (<span x-text="couponCode"></span>)</span>
                                    <span>- <span x-text="Number(discountAmount).toFixed(0)"></span> Tk</span>
                                </div>
                            </template>

                            <div class="flex flex-col gap-2 mb-3">
                                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Delivery Method <span class="text-red-500">*</span></label>
                                <template x-if="deliveryMethodsLoading">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i data-lucide="loader" class="w-4 h-4 animate-spin mr-2"></i> Loading delivery methods...
                                    </div>
                                </template>
                                <template x-if="!deliveryMethodsLoading && deliveryMethods.length > 0">
                                    <div class="space-y-2">
                                        <template x-for="method in deliveryMethods" :key="method.id">
                                            <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors"
                                                   :class="selectedDeliveryMethodId === method.id ? 'border-primary bg-primary/5' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-primary/50'">
                                                <input type="radio" x-model="selectedDeliveryMethodId" :value="method.id" name="delivery_method" class="w-4 h-4 text-primary bg-gray-100 border-gray-300 focus:ring-primary">
                                                <div class="ml-3 flex-1 flex justify-between">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="method.name"></span>
                                                    <span class="text-sm font-bold text-gray-900 dark:text-white"><span x-text="Number(method.cost).toFixed(0)"></span> Tk</span>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!deliveryMethodsLoading && deliveryMethods.length === 0">
                                    <div class="text-sm text-red-500">No delivery methods available.</div>
                                </template>
                            </div>

                            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 mt-2">
                                <span>Delivery</span>
                                <span>
                                    <template x-if="selectedDeliveryCost !== null">
                                        <span><span x-text="Number(selectedDeliveryCost).toFixed(0)"></span> Tk</span>
                                    </template>
                                    <template x-if="selectedDeliveryCost === null">
                                        <span>Calculated at next step</span>
                                    </template>
                                </span>
                            </div>

                            <div class="flex justify-between text-lg font-extrabold text-gray-900 dark:text-white pt-3 border-t border-gray-200 dark:border-gray-700 mt-3">
                                <span>Total</span>
                                <span><span x-text="Number((isCart ? cartTotal : (price * quantity)) - discountAmount + (selectedDeliveryCost || 0) + (acceptOrderBump ? Number(orderBumpPrice) : 0)).toFixed(0)"></span> Tk</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="p-6 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 space-y-3">
                    <button type="button" @click="submit" :disabled="loading" class="w-full flex justify-center items-center rounded-xl bg-green-600 hover:bg-green-700 px-4 py-3.5 text-base font-bold text-white shadow-lg shadow-green-600/30 transition-all transform hover:-translate-y-0.5 focus:ring-4 focus:ring-green-600/50 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!loading">আপনার অর্ডার কনফার্ম করতে ক্লিক করুন</span>
                        <span x-show="loading" class="flex items-center"><i data-lucide="loader" class="w-5 h-5 mr-2 animate-spin"></i> Processing...</span>
                    </button>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('checkoutModal', () => ({
            isOpen: false,
            loading: false,
            
            // Product data
            productId: null,
            variationId: null,
            productName: '',
            price: 0,
            quantity: 1,
            productImage: '',
            bundleInfo: null,
            
            // Cart data
            isCart: false,
            cartItems: [],
            cartTotal: 0,
            
            // Order bump data
            orderBumpProductId: @json($orderBumpProduct ? $orderBumpProduct->id : null),
            orderBumpPrice: @json($orderBumpProduct ? $orderBumpProduct->price : 0),
            acceptOrderBump: false,
            
            // User data
            isLoggedIn: @json(auth()->check()),
            savedAddresses: [],
            selectedAddressId: null,
            isUsingSavedAddress: false,

            name: @json(auth()->user()->name ?? '') || localStorage.getItem('checkout_name') || '',
            phone: @json(auth()->user()->phone ?? '') || localStorage.getItem('checkout_phone') || '',
            address: localStorage.getItem('checkout_address') || '',
            district: localStorage.getItem('checkout_district') || '',
            
            // Coupon data
            couponCode: '',
            couponApplied: false,
            couponMessage: '',
            couponLoading: false,
            discountAmount: 0,
            
            // Delivery data
            deliveryMethods: [],
            selectedDeliveryMethodId: null,
            deliveryMethodsLoading: false,
            
            get selectedDeliveryCost() {
                if (!this.selectedDeliveryMethodId) return null;
                const method = this.deliveryMethods.find(m => m.id === this.selectedDeliveryMethodId);
                return method ? parseFloat(method.cost) : 0;
            },

            init() {
                window.addEventListener('open-checkout', (e) => {
                    this.isCart = e.detail.is_cart || false;
                    
                    if (this.isCart) {
                        this.cartItems = e.detail.cart_items;
                        this.cartTotal = e.detail.cart_total;
                    } else {
                        this.productId = e.detail.product_id;
                        this.variationId = e.detail.variation_id || null;
                        this.productName = e.detail.product_name;
                        this.price = e.detail.price;
                        this.quantity = e.detail.quantity || 1;
                        this.productImage = e.detail.product_image || '';
                        this.bundleInfo = e.detail.bundle_info || null;
                    }
                    
                    this.resetForm();
                    
                    if (this.isLoggedIn) {
                        this.fetchAddresses();
                    }
                    
                    this.fetchDeliveryMethods();
                    
                    this.isOpen = true;
                    setTimeout(() => {
                        lucide.createIcons();
                    }, 50);
                    
                    // Fire frontend InitiateCheckout event
                    if (typeof window.trackEvent === 'function') {
                        window.trackEvent('InitiateCheckout', {
                            content_ids: [this.productId],
                            content_type: 'product',
                            value: this.price * this.quantity,
                            currency: 'BDT',
                            num_items: this.quantity
                        });
                    }
                });

                this.$watch('quantity', () => {
                    if (this.couponApplied) this.applyCoupon();
                });

                let phoneTimeout;
                this.$watch('phone', (val) => {
                    clearTimeout(phoneTimeout);
                    if (val && val.length >= 10) {
                        phoneTimeout = setTimeout(() => {
                            this.saveAbandonedCart();
                        }, 1000);
                    }
                });
            },
            
            async saveAbandonedCart() {
                try {
                    await fetch('/checkout/abandoned-cart', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            phone: this.phone,
                            name: this.name,
                            address: this.address,
                            district: this.district,
                            cart_data: this.isCart ? this.cartItems : {
                                product_id: this.productId,
                                variation_id: this.variationId,
                                product_name: this.productName,
                                price: this.price,
                                quantity: this.quantity,
                                image: this.productImage
                            }
                        })
                    });
                } catch(e) {
                    console.error('Failed to save abandoned cart', e);
                }
            },
            
            async fetchAddresses() {
                try {
                    const res = await fetch('{{ route("account.default_address") }}');
                    const addresses = await res.json();
                    if (addresses && addresses.length > 0) {
                        this.savedAddresses = addresses;
                        const defaultAddr = addresses.find(a => a.is_default) || addresses[0];
                        this.selectAddress(defaultAddr);
                    }
                } catch(e) {
                    console.error('Failed to fetch addresses', e);
                }
            },
            
            async fetchDeliveryMethods() {
                this.deliveryMethodsLoading = true;
                try {
                    const res = await fetch('/checkout/delivery-methods');
                    const methods = await res.json();
                    this.deliveryMethods = methods;
                    if (methods.length > 0 && !this.selectedDeliveryMethodId) {
                        this.selectedDeliveryMethodId = methods[0].id;
                    }
                } catch(e) {
                    console.error('Failed to fetch delivery methods', e);
                }
                this.deliveryMethodsLoading = false;
            },

            selectAddress(addr) {
                if (addr) {
                    this.selectedAddressId = addr.id;
                    this.isUsingSavedAddress = true;
                    this.name = addr.name;
                    this.phone = addr.phone;
                    this.address = addr.address;
                    this.district = addr.district;
                } else {
                    this.selectedAddressId = null;
                    this.isUsingSavedAddress = false;
                    this.name = @json(auth()->user()->name ?? '') || localStorage.getItem('checkout_name') || '';
                    this.phone = @json(auth()->user()->phone ?? '') || localStorage.getItem('checkout_phone') || '';
                    this.address = localStorage.getItem('checkout_address') || '';
                    this.district = localStorage.getItem('checkout_district') || '';
                }
                setTimeout(() => lucide.createIcons(), 50);
            },

            incrementQuantity() { if(!this.isCart) this.quantity++; },
            decrementQuantity() { if (!this.isCart && this.quantity > 1) this.quantity--; },

            resetForm() {
                this.name = @json(auth()->user()->name ?? '') || localStorage.getItem('checkout_name') || '';
                this.phone = @json(auth()->user()->phone ?? '') || localStorage.getItem('checkout_phone') || '';
                this.address = localStorage.getItem('checkout_address') || '';
                this.district = localStorage.getItem('checkout_district') || '';
                this.selectedAddressId = null;
                this.isUsingSavedAddress = false;
                this.couponCode = '';
                this.couponApplied = false;
                this.couponMessage = '';
                this.discountAmount = 0;
                // Don't reset delivery methods, but we can keep the selected one or default
                if (this.deliveryMethods.length > 0) {
                    this.selectedDeliveryMethodId = this.deliveryMethods[0].id;
                }
            },

            close() { this.isOpen = false; },

            async applyCoupon() {
                if(!this.couponCode) return;
                
                this.couponLoading = true;
                this.couponMessage = '';
                
                try {
                    const response = await fetch('/checkout/apply-coupon', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            code: this.couponCode,
                            order_total: this.isCart ? this.cartTotal : (this.price * this.quantity)
                        })
                    });
                    
                    const data = await response.json();
                    
                    if(data.success) {
                        this.couponApplied = true;
                        this.discountAmount = data.discount_amount;
                        this.couponMessage = data.message;
                    } else {
                        this.couponApplied = false;
                        this.discountAmount = 0;
                        this.couponMessage = data.message;
                    }
                } catch(error) {
                    this.couponMessage = 'Failed to verify coupon.';
                }
                
                this.couponLoading = false;
            },

            async submit() {
                if(!this.name || !this.phone || !this.address || !this.district) {
                    alert('Please fill in all required fields');
                    return;
                }
                if (!this.selectedDeliveryMethodId) {
                    alert('Please select a delivery method');
                    return;
                }
                
                this.loading = true;
                
                const endpoint = this.isCart ? '/checkout/cart' : '/checkout/express';
                const bodyData = {
                    name: this.name,
                    phone: this.phone,
                    address: this.address,
                    district: this.district,
                    coupon_code: this.couponApplied ? this.couponCode : null,
                    delivery_method_id: this.selectedDeliveryMethodId,
                    order_bump_product_id: this.acceptOrderBump ? this.orderBumpProductId : null
                };
                
                if (!this.isCart) {
                    bodyData.product_id = this.productId;
                    bodyData.variation_id = this.variationId;
                    bodyData.quantity = this.quantity;
                    if (this.bundleInfo) {
                        bodyData.bundle_info = this.bundleInfo;
                    }
                }
                
                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(bodyData)
                    });
                    
                    const data = await response.json();
                    
                    if(data.success) {
                        localStorage.setItem('checkout_name', this.name);
                        localStorage.setItem('checkout_phone', this.phone);
                        localStorage.setItem('checkout_address', this.address);
                        localStorage.setItem('checkout_district', this.district);
                        
                        window.location.href = data.redirect_url;
                    } else {
                        alert('Error during checkout');
                        this.loading = false;
                    }
                } catch(error) {
                    console.error('Checkout error:', error);
                    alert('An error occurred. Please try again.');
                    this.loading = false;
                }
            }
        }));
    });
</script>