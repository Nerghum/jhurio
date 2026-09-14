@extends('layouts.app')

@section('title', 'My Addresses')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col md:flex-row gap-8" x-data="addressManager()">
    
    <!-- Sidebar -->
    <div class="w-full md:w-64 flex-shrink-0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden sticky top-24">
            <div class="p-5 border-b border-gray-50 dark:border-gray-700 text-center">
                <div class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center text-2xl font-bold mx-auto mb-3">
                    {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->phone, 0, 1)) }}
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white truncate">{{ auth()->user()->name !== 'Customer' ? auth()->user()->name : auth()->user()->phone }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ auth()->user()->phone }}</p>
            </div>
            <div class="p-2">
                <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <i data-lucide="package" class="w-5 h-5"></i> My Orders
                </a>
                <a href="{{ route('account.addresses') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors bg-primary/5 text-primary">
                    <i data-lucide="map-pin" class="w-5 h-5"></i> Saved Addresses
                </a>
                <form action="{{ route('account.logout') }}" method="POST" class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <i data-lucide="log-out" class="w-5 h-5"></i> Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Saved Addresses</h1>
            <button @click="openModal('add')" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition-colors shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i> Add New Address
            </button>
        </div>

        @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-green-700 dark:text-green-400 text-sm flex items-center gap-3 shadow-sm">
            <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($addresses as $address)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border transition-all {{ $address->is_default ? 'border-primary shadow-md shadow-primary/5' : 'border-gray-200 dark:border-gray-700 shadow-sm hover:border-gray-300' }} relative group p-6">
                @if($address->is_default)
                <span class="absolute top-4 right-4 bg-primary/10 text-primary text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Default
                </span>
                @else
                <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button @click="setDefault({{ $address->id }})" class="text-xs font-semibold text-gray-500 hover:text-primary transition-colors bg-gray-50 dark:bg-gray-700 px-3 py-1 rounded-full border border-gray-200 dark:border-gray-600 hover:border-primary">
                        Set Default
                    </button>
                </div>
                @endif

                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500">
                        @if(strtolower($address->label) === 'home') <i data-lucide="home" class="w-4 h-4"></i>
                        @elseif(strtolower($address->label) === 'work') <i data-lucide="briefcase" class="w-4 h-4"></i>
                        @else <i data-lucide="map-pin" class="w-4 h-4"></i> @endif
                    </div>
                    <h3 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider text-sm">{{ $address->label }}</h3>
                </div>

                <div class="space-y-1.5 mb-6 text-sm text-gray-600 dark:text-gray-300">
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $address->name }}</p>
                    <p class="flex items-center gap-1.5"><i data-lucide="phone" class="w-3.5 h-3.5 text-gray-400"></i> {{ $address->phone }}</p>
                    <p class="flex items-start gap-1.5 mt-2"><i data-lucide="map" class="w-3.5 h-3.5 text-gray-400 mt-0.5"></i> <span>{{ $address->address }}<br>{{ $address->district }}</span></p>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <button @click="openModal('edit', {{ json_encode($address) }})" class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1.5 transition-colors">
                        <i data-lucide="edit" class="w-4 h-4"></i> Edit
                    </button>
                    <form action="{{ route('account.addresses.destroy', $address) }}" method="POST" onsubmit="return confirm('Delete this address?');" class="ml-auto">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 flex items-center gap-1.5 transition-colors">
                            <i data-lucide="trash-2" class="w-4 h-4"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="md:col-span-2 text-center py-16 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-700 border-dashed">
                <div class="w-16 h-16 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                    <i data-lucide="map" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">No Saved Addresses</h3>
                <p class="text-gray-500 text-sm mb-6 max-w-sm mx-auto">Save your delivery addresses here for a faster checkout experience.</p>
                <button @click="openModal('add')" class="inline-flex items-center gap-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-white px-5 py-2.5 rounded-xl font-medium text-sm border border-gray-200 dark:border-gray-600 shadow-sm hover:border-primary hover:text-primary transition-all">
                    <i data-lucide="plus" class="w-4 h-4"></i> Add First Address
                </button>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Address Modal -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative z-10 inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form :action="formAction" method="POST">
                    @csrf
                    <template x-if="modalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="modalMode === 'add' ? 'Add New Address' : 'Edit Address'"></h3>
                        <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    
                    <div class="px-6 py-5 space-y-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Label</label>
                                <select name="label" x-model="form.label" required class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary">
                                    <option value="Home">Home</option>
                                    <option value="Work">Work</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">District/City</label>
                                <select name="district" x-model="form.district" required class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary appearance-none">
                                    <option value="" disabled selected>Select District</option>
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
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Recipient Name</label>
                            <input type="text" name="name" x-model="form.name" required class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Phone Number</label>
                            <input type="text" name="phone" x-model="form.phone" required class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Full Address</label>
                            <textarea name="address" x-model="form.address" required rows="3" class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary resize-none" placeholder="House/Apartment no., Street, Area..."></textarea>
                        </div>

                        <div class="flex items-center" x-show="modalMode === 'add'">
                            <input id="is_default" name="is_default" type="checkbox" value="1" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <label for="is_default" class="ml-2 block text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                Set as default address
                            </label>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                        <button type="button" @click="closeModal()" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-primary rounded-xl hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all shadow-sm">
                            Save Address
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('addressManager', () => ({
        modalOpen: false,
        modalMode: 'add',
        formAction: '{{ route('account.addresses.store') }}',
        form: { id: '', label: 'Home', name: '', phone: '', address: '', district: '' },
        
        openModal(mode, data = null) {
            this.modalMode = mode;
            if (mode === 'edit' && data) {
                this.form = { ...data };
                this.formAction = `/account/addresses/${data.id}`;
            } else {
                this.form = { id: '', label: 'Home', name: '{{ auth()->user()->name !== "Customer" ? auth()->user()->name : "" }}', phone: '{{ auth()->user()->phone }}', address: '', district: '' };
                this.formAction = '{{ route('account.addresses.store') }}';
            }
            this.modalOpen = true;
            document.body.style.overflow = 'hidden';
        },
        closeModal() {
            this.modalOpen = false;
            document.body.style.overflow = 'auto';
        },
        async setDefault(id) {
            try {
                const res = await fetch(`/account/addresses/${id}/default`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) window.location.reload();
            } catch (e) { console.error('Error setting default', e); }
        }
    }));
});
</script>
@endsection
