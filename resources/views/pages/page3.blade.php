@extends('layouts.app')

@section('content')
{{-- NOTE (change NOT in the worksheet instructions): Page 3 is the custom cake-shop frontend requested for the midterm. It keeps the existing app shell and uses Alpine for the preview interaction while Laravel supplies the data. --}}
<div x-data="cakeBuilder(@js($builderProducts), @js($options))" class="slicecraft-page min-h-screen bg-[#fffaf5] px-3 py-4 text-[#3c2924] sm:px-4 lg:px-6">
    <div class="mx-auto max-w-7xl">
        @if (session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <header :class="isCondensed ? 'cake-page-header--condensed' : ''" class="cake-page-header mb-4 flex flex-col gap-3 border-b border-[#eaded6] pb-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="cake-page-kicker mb-1 text-xs font-bold uppercase tracking-[0.24em] text-[#b85c5c]">SliceCraft · Custom Cake Shop</p>
                <h1 class="font-serif text-3xl font-bold tracking-tight text-[#3c2924] sm:text-4xl">SliceCraft · Custom Cake Shop</h1>
                <p x-show="!isCondensed" class="cake-page-description mt-2 max-w-2xl text-sm leading-6 text-[#78645d]">Choose a cake base, then customize its size, filling, frosting, decoration, and toppings.</p>
            </div>
            @include('pages._slicecraft-nav')
        </header>

        <div class="cake-studio-grid grid gap-5 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
            <section x-ref="optionsPane" @scroll="updateHeader($event)" class="cake-options-pane min-h-0 space-y-4" aria-labelledby="builder-heading">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-[#b85c5c]">Build the look</p>
                    <h2 id="builder-heading" class="mt-1 text-xl font-bold text-[#3c2924]">Select your cake base</h2>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <template x-for="product in products" :key="product.id">
                        <button type="button" @click="selectProduct(product.id)" :aria-pressed="selectedProductId === product.id" :class="selectedProductId === product.id ? 'border-[#b85c5c] bg-[#fff0ed] ring-2 ring-[#b85c5c]/20' : 'border-[#eaded6] bg-white hover:border-[#d9a59c]'" class="rounded-2xl border p-3 text-left shadow-sm transition focus:outline-none focus:ring-2 focus:ring-[#b85c5c] focus:ring-offset-2">
                            <span class="block text-base font-bold text-[#3c2924]" x-text="product.name"></span>
                            <span class="mt-1 block text-sm leading-5 text-[#78645d]" x-text="product.description"></span>
                            <span class="mt-3 block text-sm font-bold text-[#b85c5c]" x-text="formatPrice(product.base_price_cents) + ' base' "></span>
                        </button>
                    </template>
                </div>


                <div class="grid gap-4 rounded-3xl border border-[#eaded6] bg-white p-4 shadow-sm sm:grid-cols-2 sm:p-5">
                    <div>
                        <span class="mb-2 block text-sm font-bold">Size / servings</span>
                        <div class="grid gap-2">
                            @foreach ($options['sizes'] as $option)
                                <button type="button" @click="selectedSize = '{{ $option['code'] }}'" :aria-pressed="selectedSize === '{{ $option['code'] }}'" :class="selectedSize === '{{ $option['code'] }}' ? 'border-[#b85c5c] bg-[#fff0ed] text-[#984b4b]' : 'border-[#eee3dd] bg-[#fffaf8] text-[#5b3a31] hover:border-[#d9a59c]'" class="rounded-xl border px-3 py-2 text-left text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-[#b85c5c] focus:ring-offset-2">{{ $option['label'] }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <span class="mb-2 block text-sm font-bold">Filling</span>
                        <div class="grid gap-2">
                            @foreach ($options['fillings'] as $option)
                                <button type="button" @click="selectedFilling = '{{ $option['code'] }}'" :aria-pressed="selectedFilling === '{{ $option['code'] }}'" :class="selectedFilling === '{{ $option['code'] }}' ? 'border-[#b85c5c] bg-[#fff0ed] text-[#984b4b]' : 'border-[#eee3dd] bg-[#fffaf8] text-[#5b3a31] hover:border-[#d9a59c]'" class="rounded-xl border px-3 py-2 text-left text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-[#b85c5c] focus:ring-offset-2">{{ $option['label'] }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <span class="mb-2 block text-sm font-bold">Frosting</span>
                        <div class="grid gap-2">
                            @foreach ($options['frostings'] as $option)
                                <button type="button" @click="selectedFrosting = '{{ $option['code'] }}'" :aria-pressed="selectedFrosting === '{{ $option['code'] }}'" :class="selectedFrosting === '{{ $option['code'] }}' ? 'border-[#b85c5c] bg-[#fff0ed] text-[#984b4b]' : 'border-[#eee3dd] bg-[#fffaf8] text-[#5b3a31] hover:border-[#d9a59c]'" class="rounded-xl border px-3 py-2 text-left text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-[#b85c5c] focus:ring-offset-2">{{ $option['label'] }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <span class="mb-2 block text-sm font-bold">Decoration</span>
                        <div class="grid gap-2">
                            @foreach ($options['decorations'] as $option)
                                <button type="button" @click="selectedDecoration = '{{ $option['code'] }}'" :aria-pressed="selectedDecoration === '{{ $option['code'] }}'" :class="selectedDecoration === '{{ $option['code'] }}' ? 'border-[#b85c5c] bg-[#fff0ed] text-[#984b4b]' : 'border-[#eee3dd] bg-[#fffaf8] text-[#5b3a31] hover:border-[#d9a59c]'" class="rounded-xl border px-3 py-2 text-left text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-[#b85c5c] focus:ring-offset-2">{{ $option['label'] }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="mb-2 block text-sm font-bold">Toppings</span>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($options['toppings'] as $option)
                                @php($icon = ['fresh-fruit' => 'fa-lemon-o', 'coconut' => 'fa-leaf', 'chocolate-drip' => 'fa-tint', 'macarons' => 'fa-circle-o'][ $option['code']] ?? 'fa-star-o')
                                <button type="button" @click="toggleTopping('{{ $option['code'] }}')" :aria-pressed="selectedToppings.includes('{{ $option['code'] }}')" :class="selectedToppings.includes('{{ $option['code'] }}') ? 'border-[#b85c5c] bg-[#fff0ed] text-[#984b4b]' : 'border-[#eee3dd] bg-[#fffaf8] text-[#5b3a31] hover:border-[#d9a59c]'" class="flex items-center justify-between rounded-xl border px-3 py-2.5 text-left text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-[#b85c5c] focus:ring-offset-2">
                                    <span class="flex items-center gap-2"><i class="fa {{ $icon }} text-[#b85c5c]" aria-hidden="true"></i><span>{{ $option['label'] }}</span></span>
                                    <span class="font-semibold text-[#b85c5c]">+{{ number_format($option['price_adjustment_cents'] / 100, 0) }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="cake-message" class="mb-2 block text-sm font-bold">Message on cake <span class="font-normal text-[#917d73]">(optional)</span></label>
                        <input id="cake-message" type="text" maxlength="80" x-model="message" placeholder="Happy birthday!" class="w-full rounded-xl border-[#d9c5b9] bg-[#fffaf8] px-3 py-2.5 text-sm focus:border-[#b85c5c] focus:ring-[#b85c5c]">
                    </div>
                </div>
            </section>

            <aside class="cake-preview-pane lg:self-start" aria-labelledby="preview-heading">
                <div class="cake-preview-card overflow-hidden rounded-3xl border border-[#eaded6] bg-white shadow-lg shadow-[#6e4930]/10">
                    <div class="relative bg-[#f7e8dc] p-4 sm:p-5">
                        <div class="absolute left-4 top-4 rounded-full bg-white/80 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] text-[#b85c5c] backdrop-blur sm:left-5 sm:top-5">Live preview</div>
                        <div class="relative flex min-h-[17rem] items-center justify-center overflow-hidden rounded-2xl bg-[#fdf8f3] p-4 sm:min-h-[20rem] sm:p-5 lg:min-h-[22rem]">
                            <img :src="previewImage" :alt="selectedPreview?.label ?? selectedProduct?.name" class="cake-preview-image max-h-[20rem] w-full object-contain drop-shadow-2xl transition duration-300" />
                            <div x-show="!selectedPreview?.image_url" class="absolute bottom-6 max-w-[85%] rounded-2xl bg-white/95 px-4 py-2 text-center text-xs font-semibold text-[#78645d] shadow-sm">We can customize this for you. Cake preview not available right now.</div>
                        </div>
                    </div>
                    <div class="space-y-4 p-4 sm:p-5">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-[#b85c5c]" x-text="selectedProduct?.name"></p>
                            <h2 id="preview-heading" class="mt-1 text-2xl font-bold text-[#3c2924]">Your custom cake</h2>
                        </div>
                        <div class="grid gap-3 rounded-2xl bg-[#fffaf8] p-4 text-sm sm:grid-cols-2">
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-[0.14em] text-[#917d73]">Size</span>
                                <span class="mt-1 block font-semibold" x-text="selectedSizeOption?.label"></span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-[0.14em] text-[#917d73]">Filling</span>
                                <span class="mt-1 block font-semibold" x-text="selectedFillingOption?.label"></span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-[0.14em] text-[#917d73]">Frosting</span>
                                <span class="mt-1 block font-semibold" x-text="selectedFrostingOption?.label"></span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-[0.14em] text-[#917d73]">Decoration</span>
                                <span class="mt-1 block font-semibold" x-text="selectedDecorationOption?.label"></span>
                            </div>
                        </div>
                        <div class="flex items-end justify-between border-t border-[#eaded6] pt-5">
                            <div>
                                <span class="block text-sm text-[#78645d]">Estimated starting total</span>
                                <span class="mt-1 block text-2xl font-bold text-[#b85c5c]" x-text="formatPrice(estimatedPriceCents)"></span>
                            </div>
                            <a href="{{ route('cart.index') }}" class="rounded-full bg-[#3c2924] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#5b3a31]"><i class="fa fa-shopping-basket mr-1" aria-hidden="true"></i> View cart</a>
                        </div>

                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
